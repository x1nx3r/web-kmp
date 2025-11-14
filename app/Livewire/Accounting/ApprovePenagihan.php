<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\ApprovalPenagihan as ApprovalPenagihanModel;
use App\Models\InvoicePenagihan;
use App\Models\ApprovalHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovePenagihan extends Component
{
    public $approvalId;
    public $approval;
    public $invoice;
    public $pengiriman;
    public $approvalHistory;
    public $notes = '';

    // Refraksi form
    public $refraksiForm = [
        'type' => 'qty',
        'value' => 0,
    ];

    public function mount($approvalId)
    {
        $this->approvalId = $approvalId;
        $this->loadApproval();
    }

    public function loadApproval()
    {
        $this->approval = ApprovalPenagihanModel::with([
            'invoice',
            'pengiriman.pengirimanDetails.bahanBakuKlien.bahanBaku',
            'pengiriman.purchaseOrder.klien',
            'histories' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'histories.user',
            'staff',
            'manager'
        ])->findOrFail($this->approvalId);

        $this->invoice = $this->approval->invoice;
        $this->pengiriman = $this->approval->pengiriman;
        $this->approvalHistory = $this->approval->histories;

        // Load refraksi values from invoice
        $this->refraksiForm['type'] = $this->invoice->refraksi_type ?? 'qty';
        $this->refraksiForm['value'] = $this->invoice->refraksi_value ?? 0;
    }

    public function approve()
    {
        $user = Auth::user();

        if (!$this->approval) {
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
            if ($this->approval->status !== 'pending') {
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

            $this->approval->update($updateData);

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id' => $this->approval->invoice_id,
                'role' => $role,
                'user_id' => $user->id,
                'action' => 'approved',
                'notes' => $this->notes,
            ]);

            DB::commit();

            session()->flash('message', 'Approval berhasil disimpan');
            return redirect()->route('accounting.approval-penagihan');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function reject()
    {
        $user = Auth::user();

        if (!$this->approval) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        if (empty($this->notes)) {
            session()->flash('error', 'Catatan penolakan harus diisi');
            return;
        }

        DB::beginTransaction();
        try {
            $role = $this->getUserRole($user);

            if (!$role) {
                throw new \Exception('Anda tidak memiliki akses untuk melakukan approval');
            }

            $this->approval->update(['status' => 'rejected']);

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id' => $this->approval->invoice_id,
                'role' => $role,
                'user_id' => $user->id,
                'action' => 'rejected',
                'notes' => $this->notes,
            ]);

            DB::commit();

            session()->flash('message', 'Approval berhasil ditolak');
            return redirect()->route('accounting.approval-penagihan');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function updateRefraksi()
    {
        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            // Update refraksi values
            $this->invoice->refraksi_type = $this->refraksiForm['type'];
            $this->invoice->refraksi_value = floatval($this->refraksiForm['value']);

            // Recalculate refraksi
            $qtyBeforeRefraksi = $this->pengiriman->total_qty_kirim;
            $amountBeforeRefraksi = $this->pengiriman->total_harga_kirim;
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $refraksiAmount = 0;
            $subtotal = $amountBeforeRefraksi;

            if ($this->invoice->refraksi_type === 'qty') {
                // Refraksi Qty
                $refraksiQty = $qtyBeforeRefraksi * ($this->invoice->refraksi_value / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;

                $hargaPerKg = $subtotal / $qtyBeforeRefraksi;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
                $subtotal = $subtotal - $refraksiAmount;
            } elseif ($this->invoice->refraksi_type === 'rupiah') {
                // Refraksi Rupiah
                $refraksiAmount = $this->invoice->refraksi_value * $qtyBeforeRefraksi;
                $subtotal = $subtotal - $refraksiAmount;
            } elseif ($this->invoice->refraksi_type === 'lainnya') {
                // Refraksi Lainnya
                $refraksiAmount = $this->invoice->refraksi_value;
                $subtotal = $subtotal - $refraksiAmount;
            }

            $this->invoice->refraksi_amount = $refraksiAmount;
            $this->invoice->qty_before_refraksi = $qtyBeforeRefraksi;
            $this->invoice->qty_after_refraksi = $qtyAfterRefraksi;
            $this->invoice->subtotal = $subtotal;

            // Recalculate total using the model method
            $this->invoice->recalculateTotal();

            DB::commit();

            session()->flash('message', 'Refraksi berhasil diupdate');
            $this->loadApproval();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mengupdate refraksi: ' . $e->getMessage());
        }
    }

    private function getUserRole($user)
    {
        if ($user->role === 'manager_accounting') {
            return 'manager_keuangan';
        } elseif ($user->role === 'staff_accounting') {
            return 'staff';
        }

        return null;
    }

    public function render()
    {
        return view('livewire.accounting.approve-penagihan');
    }
}
