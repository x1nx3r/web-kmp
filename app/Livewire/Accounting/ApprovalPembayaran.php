<?php

namespace App\Livewire\Accounting;

use App\Models\ApprovalPembayaran as ApprovalPembayaranModel;
use App\Models\ApprovalHistory;
use App\Livewire\Accounting\Traits\WithPaymentApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ApprovalPembayaran extends Component
{
    use WithPagination, WithFileUploads, WithPaymentApproval;

    public $search = '';
    public $statusFilter = 'all';
    public $activeTab = 'pending';
    public $selectedPengiriman = null;
    public $showDetailModal = false;
    public $notes = '';
    public $editMode = false;
    public $canManage = false;
    public $approvalHistory = [];
    public $approvalId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'activeTab' => ['except' => 'pending'],
    ];

    public function mount($approvalId = null, $editMode = false)
    {
        $this->approvalId = $approvalId;
        $this->editMode = $editMode;
        $this->canManage = in_array(Auth::user()->role, [
            'staff_accounting', 'manager_accounting', 'direktur', 'superadmin'
        ]);

        if ($this->approvalId) {
            $this->showDetail($this->approvalId);
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function setActiveTab($tab) { $this->activeTab = $tab; $this->resetPage(); }

    public function render()
    {
        $query = ApprovalPembayaranModel::with([
            'pengiriman.purchaseOrder.klien',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'staff',
            'manager'
        ])->has('pengiriman');

        $query->when($this->activeTab === 'pending', fn($q) => $q->where('status', 'pending'))
              ->when($this->activeTab === 'approved', fn($q) => $q->where('status', 'completed'))
              ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
              ->when($this->search, function ($q) {
                  $term = "%{$this->search}%";
                  $q->whereHas('pengiriman', function ($pq) use ($term) {
                      $pq->where('no_pengiriman', 'like', $term)
                         ->orWhereHas('purchaseOrder', function ($poQ) use ($term) {
                             $poQ->where('po_number', 'like', $term)
                                 ->orWhereHas('klien', fn($kQ) => $kQ->where('nama', 'like', $term));
                         });
                  });
              });

        return view('livewire.accounting.approval-pembayaran', [
            'approvals' => $query->latest()->paginate(10),
        ]);
    }

    public function showDetail($approvalId)
    {
        $this->selectedPengiriman = ApprovalPembayaranModel::with([
            'pengiriman.purchaseOrder.klien', 'pengiriman.forecast', 'pengiriman.purchasing',
            'pengiriman.details.bahanBakuKlien', 'pengiriman.invoicePenagihan.approvalPenagihan',
            'catatanPiutang', 'staff', 'manager', 'histories.user'
        ])->findOrFail($approvalId);

        $this->showDetailModal = true;
        $this->notes = '';
        
        $approval = $this->selectedPengiriman;
        $this->refraksiForm = ['type' => $approval->refraksi_type ?? 'qty', 'value' => $approval->refraksi_value ?? 0];
        $this->piutangForm = [
            'catatan_piutang_id' => $approval->catatan_piutang_id,
            'amount' => $approval->piutang_amount ?? 0,
            'notes' => $approval->piutang_notes ?? ''
        ];
        $this->existingBuktiPembayaran = json_decode($approval->bukti_pembayaran, true) ?? [];
        $this->buktiPembayaran = [];
        $this->filesToRemove = [];
        $this->approvalHistory = $approval->histories()->orderByDesc('created_at')->get();
    }

    public function closeModal()
    {
        $this->reset(['showDetailModal', 'selectedPengiriman', 'notes', 'buktiPembayaran', 'existingBuktiPembayaran', 'filesToRemove', 'approvalHistory']);
        $this->refraksiForm = ['type' => 'qty', 'value' => 0];
        $this->piutangForm = ['catatan_piutang_id' => null, 'amount' => 0, 'notes' => ''];
    }

    public function approve()
    {
        if (!$this->selectedPengiriman) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $role = $this->getApprovalUserRole();
            if (!$role) throw new \Exception('Anda tidak memiliki akses');
            if ($this->selectedPengiriman->status !== 'pending') throw new \Exception('Approval tidak valid');

            $updateData = ['status' => 'completed'];
            $updateData[in_array($role, ['manager_keuangan', 'superadmin']) ? 'manager_id' : 'staff_id'] = Auth::id();
            $updateData[in_array($role, ['manager_keuangan', 'superadmin']) ? 'manager_approved_at' : 'staff_approved_at'] = now();

            $this->selectedPengiriman->update($updateData);

            ApprovalHistory::create([
                'approval_type' => 'pembayaran',
                'approval_id' => $this->selectedPengiriman->id,
                'pengiriman_id' => $this->selectedPengiriman->pengiriman_id,
                'role' => $role,
                'user_id' => Auth::id(),
                'action' => 'approved',
                'notes' => $this->notes,
            ]);

            DB::commit();
            session()->flash('message', 'Approval berhasil disimpan');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approve Error: " . $e->getMessage());
            session()->flash('error', $e->getMessage());
        }
    }
}