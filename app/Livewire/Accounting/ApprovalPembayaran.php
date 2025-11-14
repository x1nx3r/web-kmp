<?php

namespace App\Livewire\Accounting;

use App\Models\Pengiriman;
use App\Models\ApprovalPembayaran as ApprovalPembayaranModel;
use App\Models\ApprovalHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalPembayaran extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $activeTab = 'pending'; // Tab for pending approval or approved
    public $selectedPengiriman = null;
    public $showDetailModal = false;
    public $notes = '';

    // Refraksi form
    public $refraksiForm = [
        'type' => 'qty',
        'value' => 0,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'activeTab' => ['except' => 'pending'],
    ];

    public function render()
    {
        $query = ApprovalPembayaranModel::with([
            'pengiriman.purchaseOrder',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'staff',
            'manager'
        ]);

        // Filter by active tab
        if ($this->activeTab === 'pending') {
            // Pending approval: show items with status pending
            $query->where('status', 'pending');
        } elseif ($this->activeTab === 'approved') {
            // Approved: show only completed items
            $query->where('status', 'completed');
        }

        // Filter by search
        if ($this->search) {
            $query->whereHas('pengiriman', function ($q) {
                $q->where('no_pengiriman', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by status (optional, can be removed or kept for additional filtering)
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $approvals = $query->latest()->paginate(10);

        return view('livewire.accounting.approval-pembayaran', [
            'approvals' => $approvals,
        ]);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage(); // Reset pagination when switching tabs
    }

    public function showDetail($approvalId)
    {
        $approval = ApprovalPembayaranModel::with([
            'pengiriman.purchaseOrder.klien',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'pengiriman.details.bahanBakuKlien',
            'pengiriman.invoicePenagihan.approvalPenagihan',
            'staff',
            'manager',
            'histories.user'
        ])->findOrFail($approvalId);

        $this->selectedPengiriman = $approval;
        $this->showDetailModal = true;
        $this->notes = '';

        // Load refraksi values from approval pembayaran (not from invoice)
        $this->refraksiForm['type'] = $approval->refraksi_type ?? 'qty';
        $this->refraksiForm['value'] = $approval->refraksi_value ?? 0;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedPengiriman = null;
        $this->notes = '';
        $this->refraksiForm = ['type' => 'qty', 'value' => 0];
    }

    public function approve()
    {
        $user = Auth::user();
        $approval = $this->selectedPengiriman;

        if (!$approval) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $role = $this->getUserRole($user);

            if (!$role) {
                throw new \Exception('Anda tidak memiliki akses untuk melakukan approval');
            }

            // Check if approval can be processed
            if ($approval->status !== 'pending') {
                throw new \Exception('Approval ini sudah diproses atau tidak dapat diapprove');
            }

            // Langsung complete untuk semua anggota keuangan
            $updateData = [
                'status' => 'completed',
            ];

            // Set approver based on role
            if ($role === 'manager_keuangan') {
                $updateData['manager_id'] = $user->id;
                $updateData['manager_approved_at'] = now();
            } else {
                $updateData['staff_id'] = $user->id;
                $updateData['staff_approved_at'] = now();
            }

            $approval->update($updateData);

            // Update status pengiriman ke 'berhasil'
            $approval->pengiriman->update([
                'status' => 'berhasil',
            ]);

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'pembayaran',
                'approval_id' => $approval->id,
                'pengiriman_id' => $approval->pengiriman_id,
                'role' => $role,
                'user_id' => $user->id,
                'action' => 'approved',
                'notes' => $this->notes,
            ]);

            DB::commit();

            session()->flash('message', 'Approval berhasil disimpan');
            $this->closeModal();
            $this->render();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function updateRefraksi()
    {
        if (!$this->selectedPengiriman) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $approval = $this->selectedPengiriman;
            $pengiriman = $approval->pengiriman;

            // Update refraksi values untuk approval pembayaran
            $approval->refraksi_type = $this->refraksiForm['type'];
            $approval->refraksi_value = floatval($this->refraksiForm['value']);

            // Calculate refraksi untuk pembayaran
            $qtyBeforeRefraksi = $pengiriman->total_qty_kirim;
            $amountBeforeRefraksi = $pengiriman->total_harga_kirim;
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $amountAfterRefraksi = $amountBeforeRefraksi;
            $refraksiAmount = 0;

            if ($approval->refraksi_type === 'qty') {
                // Refraksi Qty: potong berdasarkan persentase qty
                $refraksiQty = $qtyBeforeRefraksi * ($approval->refraksi_value / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;

                // Hitung potongan amount berdasarkan qty refraksi
                $hargaPerKg = $amountBeforeRefraksi / $qtyBeforeRefraksi;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
                $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
            } elseif ($approval->refraksi_type === 'rupiah') {
                // Refraksi Rupiah: potongan harga per kg
                $refraksiAmount = $approval->refraksi_value * $qtyBeforeRefraksi;
                $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
            }

            // Update approval pembayaran
            $approval->refraksi_amount = $refraksiAmount;
            $approval->qty_before_refraksi = $qtyBeforeRefraksi;
            $approval->qty_after_refraksi = $qtyAfterRefraksi;
            $approval->amount_before_refraksi = $amountBeforeRefraksi;
            $approval->amount_after_refraksi = $amountAfterRefraksi;
            $approval->save();

            DB::commit();
            session()->flash('message', 'Refraksi Pembayaran berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedPengiriman->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update refraksi: ' . $e->getMessage());
        }
    }

    private function getUserRole($user)
    {
        // Tentukan role berdasarkan user
        // Sesuaikan dengan sistem role yang ada
        if ($user->role === 'direktur') {
            return 'superadmin';
        } elseif ($user->role === 'manager_accounting') {
            return 'manager_keuangan';
        } elseif ($user->role === 'staff_accounting') {
            return 'staff';
        }

        return null;
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
}
