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
    public $editMode = false;

    public $canManage = false;

    // Bank selection
    public $selectedBank = 'mandiri';
    public $bankOptions = [
        'mandiri' => [
            'name' => 'Bank Mandiri',
            'account_number' => '141-0080998883',
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

    // Customer information
    public $customerName = '';
    public $customerAddress = '';
    public $customerPhone = '';
    public $customerEmail = '';

    // Invoice notes
    public $invoiceNotes = '';

    public function mount($approvalId, $editMode = false)
    {
        $this->approvalId = $approvalId;
        $this->editMode = $editMode;
        $this->canManage = in_array(Auth::user()->role, ['staff_accounting', 'manager_accounting', 'direktur', 'superadmin']);
        $this->loadApproval();
    }

    public function updatedSelectedBank($value)
    {
        $this->updateBankSelection();
    }

    /**
     * Auto-update refraksi when form values change
     */
    public function updatedRefraksiForm()
    {
        // Only auto-update if user can manage and (approval is not completed OR we're in edit mode)
        if (!$this->canManage) {
            return;
        }

        if (!$this->editMode && ($this->approval->status === 'completed' || $this->approval->status === 'rejected')) {
            return;
        }

        $this->updateRefraksi();
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
            'pengiriman.approvalPembayaran', // Untuk mengambil refraksi dari pembayaran
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

        // Auto-sync refraksi dari approval pembayaran jika invoice belum punya refraksi
        $this->syncRefraksiFromPembayaran();

        // Load refraksi values from invoice
        $this->refraksiForm['type'] = $this->invoice->refraksi_type ?? 'qty';
        $this->refraksiForm['value'] = $this->invoice->refraksi_value ?? 0;

        // Load invoice dates
        $this->invoiceDate = $this->invoice->invoice_date?->format('Y-m-d');
        $this->dueDate = $this->invoice->due_date?->format('Y-m-d');
        $this->invoiceNumber = $this->invoice->invoice_number ?? '';

        // Load customer information
        $this->customerName = $this->invoice->customer_name ?? '';
        $this->customerAddress = $this->invoice->customer_address ?? '';
        $this->customerPhone = $this->invoice->customer_phone ?? '';
        $this->customerEmail = $this->invoice->customer_email ?? '';

        // Load invoice notes
        $this->invoiceNotes = $this->invoice->notes ?? '';

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
            // Set default bank to mandiri and save to database
            $this->selectedBank = 'mandiri';
            $defaultBank = $this->bankOptions['mandiri'];
            $this->invoice->update([
                'bank_name' => $defaultBank['name'],
                'bank_account_number' => $defaultBank['account_number'],
                'bank_account_name' => $defaultBank['account_name'],
            ]);
            $this->invoice->refresh();
        }
    }

    /**
     * Sync refraksi dari approval pembayaran ke invoice penagihan secara otomatis
     */
    private function syncRefraksiFromPembayaran()
    {
        // Cek apakah pengiriman punya approval pembayaran dengan refraksi
        $approvalPembayaran = $this->pengiriman->approvalPembayaran;

        if (!$approvalPembayaran || $approvalPembayaran->refraksi_value <= 0) {
            return; // Tidak ada refraksi dari pembayaran
        }

        // Cek apakah invoice sudah punya refraksi yang sama
        if ($this->invoice->refraksi_type === $approvalPembayaran->refraksi_type
            && $this->invoice->refraksi_value == $approvalPembayaran->refraksi_value) {
            return; // Sudah sync, tidak perlu update
        }

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

        // Apply refraksi dari pembayaran
        $refraksiType = $approvalPembayaran->refraksi_type;
        $refraksiValue = floatval($approvalPembayaran->refraksi_value);

        $qtyBeforeRefraksi = $this->pengiriman->total_qty_kirim;
        $amountBeforeRefraksi = $totalSelling;
        $qtyAfterRefraksi = $qtyBeforeRefraksi;
        $refraksiAmount = 0;
        $subtotal = $amountBeforeRefraksi;

        if ($refraksiType === 'qty') {
            // Refraksi Qty
            $refraksiQty = $qtyBeforeRefraksi * ($refraksiValue / 100);
            $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;

            $hargaPerKg = $qtyBeforeRefraksi > 0 ? $subtotal / $qtyBeforeRefraksi : 0;
            $refraksiAmount = $refraksiQty * $hargaPerKg;
            $subtotal = $subtotal - $refraksiAmount;
        } elseif ($refraksiType === 'rupiah') {
            // Refraksi Rupiah
            $refraksiAmount = $refraksiValue * $qtyBeforeRefraksi;
            $subtotal = $subtotal - $refraksiAmount;
        } elseif ($refraksiType === 'lainnya') {
            // Refraksi Lainnya
            $refraksiAmount = $refraksiValue;
            $subtotal = $subtotal - $refraksiAmount;
        }

        // Update invoice dengan refraksi dari pembayaran
        $this->invoice->update([
            'refraksi_type' => $refraksiType,
            'refraksi_value' => $refraksiValue,
            'refraksi_amount' => $refraksiAmount,
            'qty_before_refraksi' => $qtyBeforeRefraksi,
            'qty_after_refraksi' => $qtyAfterRefraksi,
            'amount_before_refraksi' => $amountBeforeRefraksi,
            'amount_after_refraksi' => $subtotal,
            'subtotal' => $subtotal,
            'total_amount' => $subtotal + ($this->invoice->tax_amount ?? 0) - ($this->invoice->discount_amount ?? 0),
        ]);

        // Refresh invoice data
        $this->invoice->refresh();
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

            // Log change if in edit mode for completed invoice
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id' => $this->approval->invoice_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Bank diubah menjadi ' . $bankInfo['name'],
                ]);
            }

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

    public function updateInvoice()
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
            session()->flash('error', 'Silakan pilih bank terlebih dahulu');
            return;
        }

        DB::beginTransaction();
        try {
            $role = $this->getUserRole($user);

            if (!$role) {
                throw new \Exception('Anda tidak memiliki akses untuk melakukan update');
            }

            // Log the edit to history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id' => $this->approval->invoice_id,
                'role' => $role,
                'user_id' => $user->id,
                'action' => 'edited',
                'notes' => $this->notes ?: 'Invoice telah diupdate',
            ]);

            DB::commit();

            session()->flash('message', 'Invoice berhasil diperbarui');
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

            // Log change if in edit mode for completed invoice
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id' => $this->approval->invoice_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Refraksi diubah: ' . ($this->invoice->refraksi_type ?? 'tidak ada') . ' - ' . ($this->invoice->refraksi_value ?? 0),
                ]);
            }

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

        // Check if invoice number already exists (excluding current invoice)
        $exists = InvoicePenagihan::where('invoice_number', $this->invoiceNumber)
            ->where('id', '!=', $this->invoice->id)
            ->exists();

        if ($exists) {
            session()->flash('error', 'Nomor invoice "' . $this->invoiceNumber . '" sudah digunakan. Silakan gunakan nomor invoice yang berbeda.');
            return;
        }

        try {
            $oldNumber = $this->invoice->invoice_number;

            $this->invoice->update([
                'invoice_number' => $this->invoiceNumber,
            ]);

            // Log change if in edit mode for completed invoice
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id' => $this->approval->invoice_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Nomor invoice diubah dari ' . $oldNumber . ' menjadi ' . $this->invoiceNumber,
                ]);
            }

            session()->flash('message', 'Nomor invoice berhasil diperbarui');
            $this->loadApproval();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                session()->flash('error', 'Nomor invoice "' . $this->invoiceNumber . '" sudah digunakan. Silakan gunakan nomor invoice yang berbeda.');
            } else {
                session()->flash('error', 'Gagal memperbarui nomor invoice: ' . $e->getMessage());
            }
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

            // Log change if in edit mode for completed invoice
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id' => $this->approval->invoice_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Tanggal invoice diubah menjadi ' . $this->invoiceDate . ' dengan jatuh tempo ' . $this->dueDate,
                ]);
            }

            session()->flash('message', 'Tanggal invoice berhasil diupdate');
            $this->loadApproval();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate tanggal: ' . $e->getMessage());
        }
    }

    public function updateCustomerInfo()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerAddress' => 'required|string',
            'customerPhone' => 'nullable|string|max:20',
            'customerEmail' => 'nullable|email|max:255',
        ], [
            'customerName.required' => 'Nama customer harus diisi',
            'customerAddress.required' => 'Alamat customer harus diisi',
            'customerEmail.email' => 'Format email tidak valid',
        ]);

        try {
            $this->invoice->update([
                'customer_name' => $this->customerName,
                'customer_address' => $this->customerAddress,
                'customer_phone' => $this->customerPhone,
                'customer_email' => $this->customerEmail,
            ]);

            // Log change if in edit mode for completed invoice
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id' => $this->approval->invoice_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Informasi customer diubah: ' . $this->customerName,
                ]);
            }

            session()->flash('message', 'Informasi customer berhasil diupdate');
            $this->loadApproval();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate informasi customer: ' . $e->getMessage());
        }
    }

    public function updateInvoiceNotes()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        try {
            $this->invoice->update([
                'notes' => $this->invoiceNotes,
            ]);

            // Log change if in edit mode for completed invoice
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id' => $this->approval->invoice_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Catatan invoice diperbarui',
                ]);
            }

            session()->flash('message', 'Catatan invoice berhasil diupdate');
            $this->loadApproval();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate catatan: ' . $e->getMessage());
        }
    }

    public function updateAllInvoiceFields()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        // Validate all fields at once
        $this->validate([
            'invoiceNumber' => 'required|string|max:191',
            'invoiceDate' => 'required|date',
            'dueDate' => 'required|date|after_or_equal:invoiceDate',
            'customerName' => 'required|string|max:255',
            'customerAddress' => 'required|string',
            'customerPhone' => 'nullable|string|max:20',
            'customerEmail' => 'nullable|email|max:255',
        ], [
            'invoiceNumber.required' => 'Nomor invoice harus diisi',
            'invoiceDate.required' => 'Tanggal invoice harus diisi',
            'dueDate.required' => 'Tanggal jatuh tempo harus diisi',
            'dueDate.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal invoice',
            'customerName.required' => 'Nama customer harus diisi',
            'customerAddress.required' => 'Alamat customer harus diisi',
            'customerEmail.email' => 'Format email tidak valid',
        ]);

        // Check if invoice number already exists (excluding current invoice)
        $exists = InvoicePenagihan::where('invoice_number', $this->invoiceNumber)
            ->where('id', '!=', $this->invoice->id)
            ->exists();

        if ($exists) {
            session()->flash('error', 'Nomor invoice "' . $this->invoiceNumber . '" sudah digunakan. Silakan gunakan nomor invoice yang berbeda.');
            return;
        }

        DB::beginTransaction();
        try {
            // Update all invoice fields at once
            $this->invoice->update([
                'invoice_number' => $this->invoiceNumber,
                'invoice_date' => $this->invoiceDate,
                'due_date' => $this->dueDate,
                'customer_name' => $this->customerName,
                'customer_address' => $this->customerAddress,
                'customer_phone' => $this->customerPhone,
                'customer_email' => $this->customerEmail,
                'notes' => $this->invoiceNotes,
            ]);

            // Log change if in edit mode for completed invoice
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id' => $this->approval->invoice_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Invoice diperbarui: ' . $this->invoiceNumber,
                ]);
            }

            DB::commit();

            session()->flash('message', 'Semua perubahan berhasil disimpan');
            return redirect()->route('accounting.approval-penagihan');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan perubahan: ' . $e->getMessage());
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
