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

    public $canManage = false;

    // Bank selection
    public $selectedBank = 'mandiri';
    public $bankOptions = [
        'mandiri' => [
            'name' => 'Bank Mandiri',
            'account_number' => '1680002439046',
            'account_name' => 'PT. KAMIL MAJU PERSADA',
        ],
        'bca' => [
            'name' => 'BCA',
            'account_number' => '429-3468888',
            'account_name' => 'PT KAMIL MAJU PERSADA',
        ],
    ];

    // Refraksi form
    public $refraksiForm = [
        'type' => 'qty',
        'value' => 0,
    ];

    // Invoice date form
    public $invoiceDate;
    public $dueDate;
    public $invoiceNumber = '';

    public function mount($approvalId)
    {
        $this->approvalId = $approvalId;
        $this->canManage = in_array(Auth::user()->role, ['staff_accounting', 'manager_accounting', 'direktur', 'superadmin']);
        $this->loadApproval();
    }

    public function updatedSelectedBank($value)
    {
        $this->updateBankSelection();
    }

    public function loadApproval()
    {
        $this->approval = ApprovalPenagihanModel::with([
            'invoice',
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'pengiriman.pengirimanDetails.purchaseOrderBahanBaku.bahanBakuKlien',
            'pengiriman.pengirimanDetails.purchaseOrderBahanBaku', // Tambahan untuk harga_jual
            'pengiriman.pengirimanDetails.orderDetail', // Tambahan untuk harga_jual
            'pengiriman.purchaseOrder.klien',
            'pengiriman.purchaseOrder.orderDetails.orderSuppliers.supplier',
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

        // Load invoice dates
        $this->invoiceDate = $this->invoice->invoice_date?->format('Y-m-d');
        $this->dueDate = $this->invoice->due_date?->format('Y-m-d');
        $this->invoiceNumber = $this->invoice->invoice_number ?? '';

        // Load bank selection - default to mandiri if not set
        if ($this->invoice->bank_name) {
            // Try to find matching bank key
            foreach ($this->bankOptions as $key => $bank) {
                if ($bank['name'] === $this->invoice->bank_name) {
                    $this->selectedBank = $key;
                    break;
                }
            }
        } else {
            $this->selectedBank = 'mandiri';
        }
    }

    public function updateBankSelection()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        $this->validate([
            'selectedBank' => 'required|in:mandiri,bca',
        ]);

        try {
            $bankInfo = $this->bankOptions[$this->selectedBank];

            $this->invoice->update([
                'bank_name' => $bankInfo['name'],
                'bank_account_number' => $bankInfo['account_number'],
                'bank_account_name' => $bankInfo['account_name'],
            ]);

            $this->loadApproval();

            session()->flash('message', 'Bank berhasil diupdate');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate bank: ' . $e->getMessage());
        }
    }

    public function approve()
    {
        $user = Auth::user();

        if (!$this->approval) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        if (!$this->ensureCanManage()) {
            return;
        }

        // Validate bank selection
        if (!$this->invoice->bank_name) {
            session()->flash('error', 'Silakan pilih bank terlebih dahulu sebelum approve');
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
            } elseif ($role === 'direktur' || $role === 'superadmin') {
                // Direktur dan superadmin menggunakan manager_id field
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

        if (!$this->ensureCanManage()) {
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
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            // Hitung total harga jual
            $totalSelling = 0;
            if ($this->pengiriman->pengirimanDetails) {
                foreach ($this->pengiriman->pengirimanDetails as $detail) {
                    $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                    if ($orderDetail && $orderDetail->harga_jual) {
                        $totalSelling += floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual);
                    }
                }
            }

            // Update refraksi values - bisa null/0 untuk tidak ada refraksi
            $refraksiValue = floatval($this->refraksiForm['value'] ?? 0);

            // Jika value 0 atau kosong, set refraksi menjadi null
            if ($refraksiValue <= 0) {
                $this->invoice->refraksi_type = null;
                $this->invoice->refraksi_value = null;
                $this->invoice->refraksi_amount = 0;
                $this->invoice->qty_before_refraksi = $this->pengiriman->total_qty_kirim;
                $this->invoice->qty_after_refraksi = $this->pengiriman->total_qty_kirim;
                $this->invoice->amount_before_refraksi = $totalSelling; // Harga jual sebelum refraksi
                $this->invoice->amount_after_refraksi = $totalSelling; // Harga jual setelah refraksi (sama karena tidak ada refraksi)
                $this->invoice->subtotal = $totalSelling; // Gunakan total harga jual
            } else {
                $this->invoice->refraksi_type = $this->refraksiForm['type'];
                $this->invoice->refraksi_value = $refraksiValue;

                // Recalculate refraksi
                $qtyBeforeRefraksi = $this->pengiriman->total_qty_kirim;
                $amountBeforeRefraksi = $totalSelling; // Gunakan total harga jual
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
                $this->invoice->amount_before_refraksi = $amountBeforeRefraksi; // Harga jual sebelum refraksi
                $this->invoice->amount_after_refraksi = $subtotal; // Harga jual setelah refraksi
                $this->invoice->subtotal = $subtotal;
            }

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

    public function updateInvoiceNumber()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        $this->validate([
            'invoiceNumber' => 'required|string|max:191',
        ], [
            'invoiceNumber.required' => 'Nomor invoice harus diisi',
            'invoiceNumber.string' => 'Nomor invoice harus berupa teks',
            'invoiceNumber.max' => 'Nomor invoice maksimal 191 karakter',
        ]);

        try {
            $this->invoice->update([
                'invoice_number' => $this->invoiceNumber,
            ]);

            session()->flash('message', 'Nomor invoice berhasil diperbarui');
            $this->loadApproval();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui nomor invoice: ' . $e->getMessage());
        }
    }

    public function updateInvoiceDates()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        $this->validate([
            'invoiceDate' => 'required|date',
            'dueDate' => 'required|date|after_or_equal:invoiceDate',
        ], [
            'invoiceDate.required' => 'Tanggal invoice harus diisi',
            'invoiceDate.date' => 'Format tanggal invoice tidak valid',
            'dueDate.required' => 'Tanggal jatuh tempo harus diisi',
            'dueDate.date' => 'Format tanggal jatuh tempo tidak valid',
            'dueDate.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal invoice',
        ]);

        try {
            $this->invoice->update([
                'invoice_date' => $this->invoiceDate,
                'due_date' => $this->dueDate,
            ]);

            session()->flash('message', 'Tanggal invoice berhasil diupdate');
            $this->loadApproval();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate tanggal: ' . $e->getMessage());
        }
    }

    protected function ensureCanManage(): bool
    {
        if (!$this->canManage) {
            session()->flash('error', 'Anda tidak memiliki akses untuk melakukan aksi ini');
            return false;
        }

        return true;
    }

    private function getUserRole($user)
    {
        if ($user->role === 'manager_accounting') {
            return 'manager_keuangan';
        } elseif ($user->role === 'staff_accounting') {
            return 'staff';
        } elseif ($user->role === 'direktur') {
            return 'direktur';
        } elseif ($user->role === 'superadmin') {
            return 'superadmin';
        }

        return null;
    }

    public function render()
    {
        // Calculate financial summary from order
        $order = $this->pengiriman->purchaseOrder ?? null;

        // Refresh pengiriman data untuk memastikan nilai terbaru
        $this->pengiriman->refresh();

        // Ambil total harga supplier (beli) dari total_harga_kirim pengiriman
        $totalSupplierCost = floatval($this->pengiriman->total_harga_kirim ?? 0);
        $totalSelling = 0;
        $totalMargin = 0;
        $marginPercentage = 0;

        // Hitung total harga jual berdasarkan qty kirim × harga jual
        if ($this->pengiriman->pengirimanDetails) {
            foreach ($this->pengiriman->pengirimanDetails as $detail) {
                // Ambil harga jual dari order detail
                $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                if ($orderDetail && $orderDetail->harga_jual) {
                    // Total = qty kirim × harga jual
                    $totalSelling += floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual);
                }
            }

            $totalMargin = $totalSelling - $totalSupplierCost;
            $marginPercentage = $totalSelling > 0 ? ($totalMargin / $totalSelling) * 100 : 0;
        }

        return view('livewire.accounting.approve-penagihan', [
            'order' => $order,
            'totalSupplierCost' => $totalSupplierCost,
            'totalSelling' => $totalSelling,
            'totalMargin' => $totalMargin,
            'marginPercentage' => $marginPercentage,
        ]);
    }
}
