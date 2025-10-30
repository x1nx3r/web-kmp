<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ApprovalPembayaran as ApprovalPembayaranModel;
use App\Models\ApprovalPenagihan;
use App\Models\ApprovalHistory;
use App\Models\InvoicePenagihan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ApprovePembayaran extends Component
{
    use WithFileUploads;

    public $approvalId;
    public $approval;
    public $pengiriman;
    public $invoicePenagihan;
    public $approvalHistory;
    public $notes = '';
    public $buktiPembayaran;

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
                // Validasi bukti pembayaran wajib untuk manager
                if (!$this->buktiPembayaran) {
                    throw new \Exception('Bukti pembayaran wajib diupload untuk approval manager');
                }

                // Validate file type and size
                $this->validate([
                    'buktiPembayaran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // max 5MB
                ]);

                // Upload bukti pembayaran
                $buktiPath = $this->buktiPembayaran->store('bukti-pembayaran', 'public');

                $this->approval->update([
                    'manager_id' => $user->id,
                    'manager_approved_at' => now(),
                    'status' => 'completed',
                    'bukti_pembayaran' => $buktiPath,
                ]);

                // Update status pengiriman ke 'berhasil' ketika manager approve (final approval)
                $this->approval->pengiriman->update([
                    'status' => 'berhasil',
                ]);

                // Create Invoice Penagihan and Approval Penagihan automatically
                $this->createInvoiceAndApprovalPenagihan();
            } else {
                // Detailed error message for debugging
                $currentStatus = $this->approval->status;
                $errorMsg = "Anda tidak dapat melakukan approval pada tahap ini. ";
                $errorMsg .= "Role Anda: {$role}, Status approval saat ini: {$currentStatus}. ";

                if ($role === 'staff') {
                    $errorMsg .= "Staff hanya bisa approve jika status = 'pending'.";
                } elseif ($role === 'manager_keuangan') {
                    $errorMsg .= "Manager hanya bisa approve jika status = 'staff_approved'.";
                }

                throw new \Exception($errorMsg);
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
            } elseif ($this->approval->refraksi_type === 'lainnya') {
                // Refraksi Lainnya: input manual langsung nominal total potongan
                $refraksiAmount = $this->approval->refraksi_value;
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

    private function createInvoiceAndApprovalPenagihan()
    {
        // Check if invoice already exists
        $existingInvoice = InvoicePenagihan::where('pengiriman_id', $this->approval->pengiriman_id)->first();

        if ($existingInvoice) {
            // If invoice already exists, just return
            return;
        }

        $pengiriman = $this->approval->pengiriman;
        $purchaseOrder = $pengiriman->purchaseOrder;
        $klien = $purchaseOrder->klien ?? null;

        // Generate invoice number
        $lastInvoice = InvoicePenagihan::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (intval(substr($lastInvoice->invoice_number, -4)) + 1) : 1;
        $invoiceNumber = 'INV-' . now()->format('Ym') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        // Get refraksi from approval pembayaran
        $refraksiType = $this->approval->refraksi_type;
        $refraksiValue = $this->approval->refraksi_value ?? 0;

        // Calculate amounts based on refraksi
        $qtyBeforeRefraksi = $pengiriman->total_qty_kirim;
        $amountBeforeRefraksi = $pengiriman->total_harga_kirim;
        $qtyAfterRefraksi = $qtyBeforeRefraksi;
        $refraksiAmount = 0;

        if ($refraksiType === 'qty' && $refraksiValue > 0) {
            $refraksiQty = $qtyBeforeRefraksi * ($refraksiValue / 100);
            $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;
            $hargaPerKg = $amountBeforeRefraksi / $qtyBeforeRefraksi;
            $refraksiAmount = $refraksiQty * $hargaPerKg;
        } elseif ($refraksiType === 'rupiah' && $refraksiValue > 0) {
            $refraksiAmount = $refraksiValue * $qtyBeforeRefraksi;
        } elseif ($refraksiType === 'lainnya' && $refraksiValue > 0) {
            $refraksiAmount = $refraksiValue;
        }

        $subtotal = $amountBeforeRefraksi - $refraksiAmount;
        $taxPercentage = 11; // PPN 11%
        $taxAmount = $subtotal * ($taxPercentage / 100);
        $totalAmount = $subtotal + $taxAmount;

        // Create Invoice
        $invoice = InvoicePenagihan::create([
            'pengiriman_id' => $pengiriman->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'customer_name' => $klien->nama ?? 'Customer',
            'customer_address' => $klien->cabang ?? '-',
            'customer_phone' => $klien->no_hp ?? null,
            'customer_email' => null,
            'subtotal' => $subtotal,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'total_amount' => $totalAmount,
            'refraksi_type' => $refraksiType,
            'refraksi_value' => $refraksiValue,
            'refraksi_amount' => $refraksiAmount,
            'qty_before_refraksi' => $qtyBeforeRefraksi,
            'qty_after_refraksi' => $qtyAfterRefraksi,
            'amount_before_refraksi' => $amountBeforeRefraksi,
            'amount_after_refraksi' => $subtotal,
            'status' => 'pending',
            'notes' => 'Invoice dibuat otomatis dari approval pembayaran',
        ]);

        // Create Approval Penagihan
        ApprovalPenagihan::create([
            'pengiriman_id' => $pengiriman->id,
            'invoice_id' => $invoice->id,
            'status' => 'pending',
        ]);
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
        return view('livewire.accounting.approve-pembayaran');
    }
}
