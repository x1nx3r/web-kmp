<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\ApprovalPembayaran as ApprovalPembayaranModel;
use App\Models\ApprovalHistory;
use App\Models\InvoicePenagihan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovePembayaran extends Component
{
    public $approvalId;
    public $approval;
    public $pengiriman;
    public $invoicePenagihan;
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
        $this->approval = ApprovalPembayaranModel::with([
            'pengiriman.pengirimanDetails.bahanBakuSupplier.bahanBaku',
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'pengiriman.purchaseOrder',
            'histories' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'histories.user',
            'staff',
            'manager',
            'superadmin'
        ])->findOrFail($this->approvalId);

        $this->pengiriman = $this->approval->pengiriman;

        // Get invoice penagihan if exists
        $this->invoicePenagihan = InvoicePenagihan::where('pengiriman_id', $this->pengiriman->id)->first();

        $this->approvalHistory = $this->approval->histories;

        // Load refraksi values from approval pembayaran
        $this->refraksiForm['type'] = $this->approval->refraksi_type ?? 'qty';
        $this->refraksiForm['value'] = $this->approval->refraksi_value ?? 0;
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

            // Check permission based on role
            if ($role === 'staff' && $this->approval->canStaffApprove()) {
                $this->approval->update([
                    'staff_id' => $user->id,
                    'staff_approved_at' => now(),
                    'status' => 'staff_approved',
                ]);
            } elseif ($role === 'manager_keuangan' && $this->approval->canManagerApprove()) {
                $this->approval->update([
                    'manager_id' => $user->id,
                    'manager_approved_at' => now(),
                    'status' => 'manager_approved',
                ]);
            } elseif ($role === 'superadmin' && $this->approval->canSuperadminApprove()) {
                $this->approval->update([
                    'superadmin_id' => $user->id,
                    'superadmin_approved_at' => now(),
                    'status' => 'completed',
                ]);

                // Update status pengiriman ke 'berhasil'
                $this->approval->pengiriman->update([
                    'status' => 'berhasil',
                ]);
            } else {
                throw new \Exception('Anda tidak dapat melakukan approval pada tahap ini');
            }

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'pembayaran',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'role' => $role,
                'user_id' => $user->id,
                'action' => 'approved',
                'notes' => $this->notes,
            ]);

            DB::commit();

            session()->flash('message', 'Approval berhasil disimpan');
            return redirect()->route('accounting.approval-pembayaran');

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
                'approval_type' => 'pembayaran',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'role' => $role,
                'user_id' => $user->id,
                'action' => 'rejected',
                'notes' => $this->notes,
            ]);

            DB::commit();

            session()->flash('message', 'Approval berhasil ditolak');
            return redirect()->route('accounting.approval-pembayaran');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function updateRefraksi()
    {
        if (!$this->approval) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            // Update refraksi values untuk approval pembayaran
            $this->approval->refraksi_type = $this->refraksiForm['type'];
            $this->approval->refraksi_value = floatval($this->refraksiForm['value']);

            // Calculate refraksi untuk pembayaran
            $qtyBeforeRefraksi = $this->pengiriman->total_qty_kirim;
            $amountBeforeRefraksi = $this->pengiriman->total_harga_kirim;
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $amountAfterRefraksi = $amountBeforeRefraksi;
            $refraksiAmount = 0;

            if ($this->approval->refraksi_type === 'qty') {
                // Refraksi Qty: potong berdasarkan persentase qty
                $refraksiQty = $qtyBeforeRefraksi * ($this->approval->refraksi_value / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;

                // Hitung potongan amount berdasarkan qty refraksi
                $hargaPerKg = $amountBeforeRefraksi / $qtyBeforeRefraksi;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
                $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
            } elseif ($this->approval->refraksi_type === 'rupiah') {
                // Refraksi Rupiah: potongan harga per kg
                $refraksiAmount = $this->approval->refraksi_value * $qtyBeforeRefraksi;
                $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
            }

            $this->approval->qty_before_refraksi = $qtyBeforeRefraksi;
            $this->approval->qty_after_refraksi = $qtyAfterRefraksi;
            $this->approval->amount_before_refraksi = $amountBeforeRefraksi;
            $this->approval->amount_after_refraksi = $amountAfterRefraksi;
            $this->approval->refraksi_amount = $refraksiAmount;

            $this->approval->save();

            DB::commit();

            session()->flash('message', 'Refraksi pembayaran berhasil diupdate');
            $this->loadApproval();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mengupdate refraksi: ' . $e->getMessage());
        }
    }

    private function getUserRole($user)
    {
        if ($user->role === 'direktur') {
            return 'superadmin';
        } elseif ($user->role === 'manager_accounting') {
            return 'manager_keuangan';
        } elseif ($user->role === 'staff_accounting') {
            return 'staff';
        }
        return null;
    }

    public function render()
    {
        return view('livewire.accounting.approve-pembayaran');
    }
}
