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
    public $expenseForm = [
        'truk' => 0,
        'kuli' => 0,
        'fee' => 0,
        'others' => [],
    ];
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
        'mandiri2' => [
            'name' => 'Bank Mandiri',
            'account_number' => '141-0008899098',
            'account_name' => 'PT KAMIL MAJU PERSADA',
        ],
    ];

    // Refraksi form — TIDAK lagi pakai wire:model.live, simpan manual
    public $refraksiForm = [
        'type' => 'qty',
        'value' => 0,
    ];

    // Per-item refraksi (indexed by items JSON index)
    public $itemRefraksi = [];

    // Per-pengiriman refraksi (for merge format items)
    public $refraksiPerItem = [];

    // Per-pengiriman expenses (for merge format items)
    public $expensePerItem = [];

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
        // Hanya saat mount (pertama kali load) yang boleh sync refraksi dari pembayaran
        $this->loadApproval(syncRefraksi: true);
    }

    // =========================================================
    // DIHAPUS: updatedSelectedBank — bank disimpan lewat tombol
    // DIHAPUS: updatedRefraksiFormType — refraksi tidak live lagi
    // DIHAPUS: updatedRefraksiFormValue — refraksi tidak live lagi
    // DIHAPUS: triggerRefraksiUpdate
    // =========================================================

    /**
     * @param bool $syncRefraksi Hanya true saat mount; false saat reload biasa agar tidak overwrite perubahan user
     */
    public function loadApproval(bool $syncRefraksi = false)
    {
        $this->approval = ApprovalPenagihanModel::with([
            'invoice.pengirimans.pengirimanDetails.bahanBakuSupplier.supplier',
            'invoice.pengirimans.pengirimanDetails.purchaseOrderBahanBaku.bahanBakuKlien',
            'invoice.pengirimans.pengirimanDetails.purchaseOrderBahanBaku',
            'invoice.pengirimans.pengirimanDetails.orderDetail',
            'invoice.pengirimans.purchaseOrder.klien',
            'invoice.pengirimans.approvalPembayaran',
            'invoice',
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'pengiriman.pengirimanDetails.purchaseOrderBahanBaku.bahanBakuKlien',
            'pengiriman.pengirimanDetails.purchaseOrderBahanBaku',
            'pengiriman.pengirimanDetails.orderDetail',
            'pengiriman.purchaseOrder.klien',
            'pengiriman.purchaseOrder.orderDetails.orderSuppliers.supplier',
            'pengiriman.approvalPembayaran',
            'histories' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'histories.user',
            'staff',
            'manager'
        ])->findOrFail($this->approvalId);

        $this->invoice = $this->approval->invoice;
        $this->pengiriman = $this->approval->pengiriman;
        $this->approvalHistory = $this->approval->histories;

        // Hanya sync refraksi dari approval pembayaran saat pertama kali load (mount)
        if ($syncRefraksi) {
            $this->syncRefraksiFromPembayaran();
        }

        // Load refraksi values from invoice
        $this->refraksiForm['type'] = $this->invoice->refraksi_type ?? 'qty';
        $this->refraksiForm['value'] = $this->invoice->refraksi_value ?? 0;

        // Load per-item refraksi from invoice items JSON
        $rawItems = $this->invoice->items ?? [];
        $this->itemRefraksi = [];
        foreach ($rawItems as $i => $it) {
            $this->itemRefraksi[$i] = (float) ($it['refraksi_kg'] ?? 0);
        }

        // Initialize per-pengiriman forms from items JSON (merge format)
        $this->refraksiPerItem = [];
        $this->expensePerItem = [];
        if (!empty($rawItems) && isset($rawItems[0]['item_name'])) {
            foreach ($rawItems as $i => $item) {
                $this->refraksiPerItem[$i] = [
                    'type'   => $item['refraksi_type'] ?? 'qty',
                    'value'  => (float) ($item['refraksi_value'] ?? 0),
                    'amount' => (float) ($item['amount'] ?? 0),
                ];

                $expenses = $item['expenses'] ?? [];
                $truk = 0; $kuli = 0; $fee = 0; $others = [];
                foreach ($expenses as $e) {
                    $t = $e['type'] ?? '';
                    $a = (float) ($e['amount'] ?? 0);
                    if ($t === 'truk')      $truk = $a;
                    elseif ($t === 'kuli')   $kuli = $a;
                    elseif ($t === 'fee')    $fee = $a;
                    else                     $others[] = ['type' => $t, 'amount' => $a];
                }
                $this->expensePerItem[$i] = [
                    'truk'   => $truk,
                    'kuli'   => $kuli,
                    'fee'    => $fee,
                    'others' => $others ?: [['type' => '', 'amount' => 0]],
                ];
            }
        }

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

        // Load bank selection
        if ($this->invoice->bank_name) {
            foreach ($this->bankOptions as $key => $bank) {
                if ($bank['name'] === $this->invoice->bank_name
                    && $bank['account_number'] === $this->invoice->bank_account_number) {
                    $this->selectedBank = $key;
                    break;
                }
            }
        } else {
            $this->selectedBank = 'mandiri';
            $defaultBank = $this->bankOptions['mandiri'];
            $this->invoice->update([
                'bank_name' => $defaultBank['name'],
                'bank_account_number' => $defaultBank['account_number'],
                'bank_account_name' => $defaultBank['account_name'],
            ]);
            $this->invoice->refresh();
        }
        $this->loadExpenses();
    }

    /**
     * Sync refraksi dari approval pembayaran ke invoice penagihan secara otomatis.
     * Hanya berjalan jika invoice BELUM punya refraksi (refraksi_value masih 0/null).
     */
    private function syncRefraksiFromPembayaran()
    {
        if (!$this->pengiriman) {
            return;
        }

        $isMerged = $this->invoice && $this->invoice->pengirimans->count() > 0;
        if ($isMerged) {
            return;
        }

        $approvalPembayaran = $this->pengiriman->approvalPembayaran;

        if (!$approvalPembayaran || $approvalPembayaran->refraksi_value <= 0) {
            return;
        }

        // Hanya sync jika invoice belum punya refraksi sama sekali
        if (!empty($this->invoice->refraksi_value) && $this->invoice->refraksi_value > 0) {
            return;
        }

        $totalSelling = 0;
        if ($this->pengiriman->pengirimanDetails) {
            foreach ($this->pengiriman->pengirimanDetails as $detail) {
                $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                if ($orderDetail && $orderDetail->harga_jual) {
                    $totalSelling += floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual);
                }
            }
        }

        $refraksiType = $approvalPembayaran->refraksi_type;
        $refraksiValue = floatval($approvalPembayaran->refraksi_value);

        $qtyBeforeRefraksi = $this->pengiriman->total_qty_kirim;
        $amountBeforeRefraksi = $totalSelling;
        $qtyAfterRefraksi = $qtyBeforeRefraksi;
        $refraksiAmount = 0;
        $subtotal = $amountBeforeRefraksi;

        if ($refraksiType === 'qty') {
            $refraksiQty = $qtyBeforeRefraksi * ($refraksiValue / 100);
            $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;
            $hargaPerKg = $qtyBeforeRefraksi > 0 ? $subtotal / $qtyBeforeRefraksi : 0;
            $refraksiAmount = $refraksiQty * $hargaPerKg;
            $subtotal = $subtotal - $refraksiAmount;
        } elseif ($refraksiType === 'rupiah') {
            $refraksiAmount = $refraksiValue * $qtyBeforeRefraksi;
            $subtotal = $subtotal - $refraksiAmount;
        } elseif ($refraksiType === 'lainnya') {
            $refraksiAmount = $refraksiValue;
            $subtotal = $subtotal - $refraksiAmount;
        }

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

        $this->invoice->refresh();
    }

    /**
     * FIX: Bank disimpan via tombol, bukan auto-save dari updatedSelectedBank.
     * Ini mencegah re-render yang mereset field defer lainnya.
     */
    public function updateBankSelection()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        $this->validate([
            'selectedBank' => 'required',
        ]);

        if (!array_key_exists($this->selectedBank, $this->bankOptions)) {
            session()->flash('error', 'Bank tidak valid');
            return;
        }

        try {
            $bankInfo = $this->bankOptions[$this->selectedBank];

            $this->invoice->update([
                'bank_name' => $bankInfo['name'],
                'bank_account_number' => $bankInfo['account_number'],
                'bank_account_name' => $bankInfo['account_name'],
            ]);

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
                    'notes' => 'Bank diubah menjadi ' . $bankInfo['name'] . ' (' . $bankInfo['account_number'] . ')',
                ]);
            }

            // FIX: Hanya refresh invoice, TIDAK memanggil loadApproval()
            // agar field-field form (invoiceNumber, customerName, dll) tidak ter-reset
            $this->invoice->refresh();
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

        if (!$this->invoice->bank_name) {
            session()->flash('error', 'Silakan pilih bank terlebih dahulu sebelum approve');
            return;
        }

        $this->validate([
            'invoiceNumber' => 'required|string|max:191',
            'customerName' => 'required|string|max:255',
            'customerAddress' => 'required|string',
            'customerPhone' => 'nullable|string|max:20',
            'customerEmail' => 'nullable|email|max:255',
        ], [
            'invoiceNumber.required' => 'Nomor invoice harus diisi',
            'invoiceNumber.max' => 'Nomor invoice maksimal 191 karakter',
            'customerName.required' => 'Nama customer harus diisi',
            'customerAddress.required' => 'Alamat customer harus diisi',
            'customerEmail.email' => 'Format email tidak valid',
        ]);

        $invoiceNumberExists = InvoicePenagihan::where('invoice_number', $this->invoiceNumber)
            ->where('id', '!=', $this->invoice->id)
            ->exists();

        if ($invoiceNumberExists) {
            session()->flash('error', 'Nomor invoice "' . $this->invoiceNumber . '" sudah digunakan. Silakan gunakan nomor invoice yang berbeda.');
            return;
        }

        DB::beginTransaction();
        try {
            $role = $this->getUserRole($user);

            if (!$role) {
                throw new \Exception('Anda tidak memiliki akses untuk melakukan approval');
            }

            if ($this->approval->status !== 'pending') {
                throw new \Exception('Approval ini sudah diproses atau tidak dapat diapprove');
            }

            $this->invoice->update([
                'invoice_number' => $this->invoiceNumber,
                'customer_name' => $this->customerName,
                'customer_address' => $this->customerAddress,
                'customer_phone' => $this->customerPhone,
                'customer_email' => $this->customerEmail,
                'notes' => $this->invoiceNotes,
            ]);

            $updateData = ['status' => 'completed'];

            if ($role === 'manager_keuangan' || $role === 'direktur' || $role === 'superadmin') {
                $updateData['manager_id'] = $user->id;
                $updateData['manager_approved_at'] = now();
            } else {
                $updateData['staff_id'] = $user->id;
                $updateData['staff_approved_at'] = now();
            }

            $this->approval->update($updateData);

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

        if (!$this->invoice->bank_name) {
            session()->flash('error', 'Silakan pilih bank terlebih dahulu');
            return;
        }

        $this->validate([
            'invoiceNumber' => 'required|string|max:191',
            'customerName' => 'required|string|max:255',
            'customerAddress' => 'required|string',
            'customerPhone' => 'nullable|string|max:20',
            'customerEmail' => 'nullable|email|max:255',
        ], [
            'invoiceNumber.required' => 'Nomor invoice harus diisi',
            'invoiceNumber.max' => 'Nomor invoice maksimal 191 karakter',
            'customerName.required' => 'Nama customer harus diisi',
            'customerAddress.required' => 'Alamat customer harus diisi',
            'customerEmail.email' => 'Format email tidak valid',
        ]);

        $invoiceNumberExists = InvoicePenagihan::where('invoice_number', $this->invoiceNumber)
            ->where('id', '!=', $this->invoice->id)
            ->exists();

        if ($invoiceNumberExists) {
            session()->flash('error', 'Nomor invoice "' . $this->invoiceNumber . '" sudah digunakan. Silakan gunakan nomor invoice yang berbeda.');
            return;
        }

        DB::beginTransaction();
        try {
            $role = $this->getUserRole($user);

            if (!$role) {
                throw new \Exception('Anda tidak memiliki akses untuk melakukan update');
            }

            $this->invoice->update([
                'invoice_number' => $this->invoiceNumber,
                'customer_name' => $this->customerName,
                'customer_address' => $this->customerAddress,
                'customer_phone' => $this->customerPhone,
                'customer_email' => $this->customerEmail,
                'notes' => $this->invoiceNotes,
            ]);

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

    /**
     * FIX: updateRefraksi sekarang hanya dipanggil via tombol manual dari blade.
     * Tidak lagi dipanggil otomatis oleh updatedRefraksiForm* hooks.
     */
    public function updateRefraksi()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->editMode && ($this->approval->status === 'completed' || $this->approval->status === 'rejected')) {
            session()->flash('error', 'Tidak dapat mengubah refraksi pada approval yang sudah selesai');
            return;
        }

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        if (!$this->pengiriman) {
            session()->flash('error', 'Data pengiriman tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            // Hitung total harga jual dan qty
            $totalSelling = 0;
            $qtyBeforeRefraksi = 0;
            
            $isMerged = $this->invoice && $this->invoice->pengirimans->count() > 0;
            $shipments = $isMerged ? $this->invoice->pengirimans : collect([$this->pengiriman]);
            
            foreach ($shipments as $s) {
                $qtyBeforeRefraksi += floatval($s->total_qty_kirim);
                if ($s->pengirimanDetails) {
                    foreach ($s->pengirimanDetails as $detail) {
                        $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                        if ($orderDetail && $orderDetail->harga_jual) {
                            $totalSelling += floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual);
                        }
                    }
                }
            }

            $refraksiValue = floatval($this->refraksiForm['value'] ?? 0);

            if ($refraksiValue <= 0) {
                $this->invoice->update([
                    'refraksi_type'          => null,
                    'refraksi_value'         => 0,
                    'refraksi_amount'        => 0,
                    'qty_before_refraksi'    => $qtyBeforeRefraksi,
                    'qty_after_refraksi'     => $qtyBeforeRefraksi,
                    'amount_before_refraksi' => $totalSelling,
                    'amount_after_refraksi'  => $totalSelling,
                    'subtotal'               => $totalSelling,
                ]);
            } else {
                $refraksiType         = $this->refraksiForm['type'];
                $amountBeforeRefraksi = $totalSelling;
                $qtyAfterRefraksi     = $qtyBeforeRefraksi;
                $refraksiAmount       = 0;
                $subtotal             = $amountBeforeRefraksi;

                if ($refraksiType === 'qty') {
                    $refraksiQty      = $qtyBeforeRefraksi * ($refraksiValue / 100);
                    $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;
                    $hargaPerKg       = $qtyBeforeRefraksi > 0 ? $subtotal / $qtyBeforeRefraksi : 0;
                    $refraksiAmount   = $refraksiQty * $hargaPerKg;
                    $subtotal         = $subtotal - $refraksiAmount;
                } elseif ($refraksiType === 'rupiah') {
                    $refraksiAmount = $refraksiValue * $qtyBeforeRefraksi;
                    $subtotal       = $subtotal - $refraksiAmount;
                } elseif ($refraksiType === 'lainnya') {
                    $refraksiAmount = $refraksiValue;
                    $subtotal       = $subtotal - $refraksiAmount;
                }

                $this->invoice->update([
                    'refraksi_type'          => $refraksiType,
                    'refraksi_value'         => $refraksiValue,
                    'refraksi_amount'        => $refraksiAmount,
                    'qty_before_refraksi'    => $qtyBeforeRefraksi,
                    'qty_after_refraksi'     => $qtyAfterRefraksi,
                    'amount_before_refraksi' => $amountBeforeRefraksi,
                    'amount_after_refraksi'  => $subtotal,
                    'subtotal'               => $subtotal,
                ]);
            }

            // Recalculate total (tax, discount, dll)
            $this->invoice->recalculateTotal();

            // Log jika edit mode pada invoice completed
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id'   => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id'    => $this->approval->invoice_id,
                    'role'          => $role,
                    'user_id'       => $user->id,
                    'action'        => 'edited',
                    'notes'         => 'Refraksi diubah: ' . ($refraksiValue <= 0 ? 'tidak ada' : $this->refraksiForm['type'] . ' - ' . $refraksiValue),
                ]);
            }

            DB::commit();

            session()->flash('message', 'Refraksi berhasil diupdate');

            // FIX: Hanya refresh invoice object, TIDAK memanggil loadApproval()
            // agar nilai form (invoiceNumber, customerName, dll) tidak ter-reset
            $this->invoice->refresh();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mengupdate refraksi: ' . $e->getMessage());
        }
    }

    public function updateItemRefraksi()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if ($this->approval->status === 'completed') {
            session()->flash('error', 'Tidak dapat mengubah refraksi item pada approval yang sudah selesai');
            return;
        }

        $rawItems = $this->invoice->items ?? [];

        if (empty($rawItems) || !isset($rawItems[0]['quantity'])) {
            session()->flash('error', 'Format item tidak mendukung refraksi per-item');
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($rawItems as $i => &$it) {
                $refKg = max((float) ($this->itemRefraksi[$i] ?? 0), 0);
                $qty   = (float) ($it['quantity'] ?? 0);
                $it['refraksi_kg'] = $refKg;
                $it['total'] = max($qty - $refKg, 0) * (float) ($it['unit_price'] ?? 0);
            }

            $this->invoice->update(['items' => $rawItems]);
            $this->invoice->refresh();

            DB::commit();

            session()->flash('message', 'Refraksi per-item berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mengupdate refraksi item: ' . $e->getMessage());
        }
    }

    public function updateRefraksiPerItem()
    {
        if (!$this->ensureCanManage()) return;

        if (!$this->editMode && ($this->approval->status === 'completed' || $this->approval->status === 'rejected')) {
            session()->flash('error', 'Tidak dapat mengubah refraksi pada approval yang sudah selesai');
            return;
        }

        $this->validate([
            'refraksiPerItem.*.type'   => 'nullable|in:qty,rupiah,lainnya',
            'refraksiPerItem.*.value'  => 'nullable|numeric|min:0',
            'refraksiPerItem.*.amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $rawItems = $this->invoice->items ?? [];
            $items = $rawItems;

            $totalSellingPrice  = 0;
            $totalRefraksiAmount = 0;
            $totalRefraksiQty    = 0;
            $totalQty            = 0;

            foreach ($items as $i => &$item) {
                $type  = $this->refraksiPerItem[$i]['type']  ?? 'qty';
                $value = (float) ($this->refraksiPerItem[$i]['value'] ?? 0);

                $item['refraksi_type']  = $type;
                $item['refraksi_value'] = $value;

                $qty   = array_sum(array_column($item['details'] ?? [], 'qty'));
                $total = (float) ($this->refraksiPerItem[$i]['amount'] ?? $item['amount'] ?? 0);
                $item['amount'] = $total;
                $totalSellingPrice += $total;
                $totalQty += $qty;

                if ($value > 0) {
                    if ($type === 'qty' && $qty > 0) {
                        $refraksiQty             = $qty * ($value / 100);
                        $hargaPerKg              = $total / $qty;
                        $item['refraksi_amount']  = $refraksiQty * $hargaPerKg;
                        $totalRefraksiQty        += $refraksiQty;
                    } elseif ($type === 'rupiah' && $qty > 0) {
                        $item['refraksi_amount'] = $value * $qty;
                    } elseif ($type === 'lainnya') {
                        $item['refraksi_amount'] = $value;
                    }
                } else {
                    $item['refraksi_amount'] = 0;
                }

                $totalRefraksiAmount += $item['refraksi_amount'];
            }
            unset($item);

            $amountAfterRefraksi = $totalSellingPrice - $totalRefraksiAmount;

            // Hitung expenses dari items
            $expensesTotal = 0;
            foreach ($items as $item) {
                foreach ($item['expenses'] ?? [] as $e) {
                    $expensesTotal += (float) ($e['amount'] ?? 0);
                }
            }

            $firstItem = $this->refraksiPerItem[0] ?? ['type' => 'qty', 'value' => 0];

            $this->invoice->items = $items;
            $this->invoice->refraksi_type          = $firstItem['type'];
            $this->invoice->refraksi_value         = (float) $firstItem['value'];
            $this->invoice->refraksi_amount        = $totalRefraksiAmount;
            $this->invoice->qty_before_refraksi    = $totalQty;
            $this->invoice->qty_after_refraksi     = $totalQty - $totalRefraksiQty;
            $this->invoice->amount_before_refraksi = $totalSellingPrice;
            $this->invoice->amount_after_refraksi  = $amountAfterRefraksi;
            $this->invoice->subtotal               = $amountAfterRefraksi;
            $this->invoice->additional_expenses_total = $expensesTotal;
            $this->invoice->total_amount = max(0, $amountAfterRefraksi + $expensesTotal + floatval($this->invoice->tax_amount ?? 0) - floatval($this->invoice->discount_amount ?? 0));
            $this->invoice->save();

            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);
                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id'   => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id'    => $this->approval->invoice_id,
                    'role'          => $role,
                    'user_id'       => $user->id,
                    'action'        => 'edited',
                    'notes'         => 'Update harga jual / refraksi per pengiriman',
                ]);
            }

            DB::commit();
            session()->flash('message', 'Harga jual / refraksi per pengiriman berhasil diupdate');
            $this->invoice->refresh();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update refraksi: ' . $e->getMessage());
        }
    }

    public function updateExpensesPerItem()
    {
        if (!$this->ensureCanManage()) return;

        if (!$this->editMode && ($this->approval->status === 'completed' || $this->approval->status === 'rejected')) {
            session()->flash('error', 'Tidak dapat mengubah pengeluaran pada approval yang sudah selesai');
            return;
        }

        if (empty($this->expensePerItem)) {
            session()->flash('error', 'Tidak ada data pengeluaran');
            return;
        }

        // Validasi per-item
        foreach ($this->expensePerItem as $i => $exp) {
            foreach (['truk', 'kuli', 'fee'] as $k) {
                if (floatval($exp[$k] ?? 0) < 0) {
                    session()->flash('error', 'Item #' . ($i + 1) . ': ' . ucfirst($k) . ' tidak boleh negatif');
                    return;
                }
            }
            foreach (($exp['others'] ?? []) as $j => $row) {
                $amount = floatval($row['amount'] ?? 0);
                $type   = trim((string)($row['type'] ?? ''));
                if ($amount < 0) {
                    session()->flash('error', 'Item #' . ($i + 1) . ', baris #' . ($j + 1) . ': nominal tidak boleh negatif');
                    return;
                }
                if ($amount > 0 && $type === '') {
                    session()->flash('error', 'Item #' . ($i + 1) . ', baris #' . ($j + 1) . ': nama pengeluaran wajib diisi');
                    return;
                }
                if ($amount > 0 && in_array(strtolower($type), ['truk', 'kuli', 'fee'], true)) {
                    session()->flash('error', 'Item #' . ($i + 1) . ': "' . $type . '" sudah ada di opsi utama');
                    return;
                }
            }
        }

        DB::beginTransaction();
        try {
            $items = $this->invoice->items;
            $allExpensesFlat = [];
            $expensesTotal = 0;

            foreach ($items as $i => &$item) {
                $exp = $this->expensePerItem[$i] ?? [];
                $itemExpenses = [];

                foreach (['truk', 'kuli', 'fee'] as $type) {
                    $amount = floatval($exp[$type] ?? 0);
                    if ($amount > 0) {
                        $itemExpenses[] = ['type' => $type, 'amount' => $amount];
                        $expensesTotal += $amount;
                    }
                }

                foreach (($exp['others'] ?? []) as $row) {
                    $type   = trim((string)($row['type'] ?? ''));
                    $amount = floatval($row['amount'] ?? 0);
                    if ($type === '' || $amount <= 0) continue;
                    $itemExpenses[] = ['type' => $type, 'amount' => $amount];
                    $expensesTotal += $amount;
                }

                $item['expenses'] = $itemExpenses;
                $allExpensesFlat = array_merge($allExpensesFlat, $itemExpenses);
            }
            unset($item);

            // Recalculate invoice-level totals
            $totalRefraksiAmount = 0;
            $totalSellingPrice = 0;
            foreach ($items as $item) {
                $totalSellingPrice += (float) ($item['amount'] ?? 0);
                $totalRefraksiAmount += (float) ($item['refraksi_amount'] ?? 0);
            }
            $amountAfterRefraksi = $totalSellingPrice - $totalRefraksiAmount;

            $this->invoice->items = $items;
            $this->invoice->additional_expenses_total = $expensesTotal;
            $this->invoice->subtotal = $amountAfterRefraksi;
            $this->invoice->total_amount = max(0, $amountAfterRefraksi + $expensesTotal + floatval($this->invoice->tax_amount ?? 0) - floatval($this->invoice->discount_amount ?? 0));
            $this->invoice->save();

            // Sync expense table (backward compat)
            $this->invoice->expenses()->delete();
            foreach ($allExpensesFlat as $e) {
                $this->invoice->expenses()->create($e);
            }

            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);
                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id'   => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id'    => $this->approval->invoice_id,
                    'role'          => $role,
                    'user_id'       => $user->id,
                    'action'        => 'edited',
                    'notes'         => 'Pengeluaran tambahan per pengiriman diubah',
                ]);
            }

            DB::commit();
            session()->flash('message', 'Pengeluaran tambahan per pengiriman berhasil disimpan');
            $this->invoice->refresh();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan pengeluaran: ' . $e->getMessage());
        }
    }

    public function addPerItemExpenseRow($itemIndex): void
    {
        if (!$this->ensureCanManage()) return;
        if (!isset($this->expensePerItem[$itemIndex])) return;
        $this->expensePerItem[$itemIndex]['others'][] = ['type' => '', 'amount' => 0];
    }

    public function removePerItemExpenseRow($itemIndex, $rowIndex): void
    {
        if (!$this->ensureCanManage()) return;
        if (!isset($this->expensePerItem[$itemIndex]['others'][$rowIndex])) return;
        array_splice($this->expensePerItem[$itemIndex]['others'], $rowIndex, 1);
        if (empty($this->expensePerItem[$itemIndex]['others'])) {
            $this->expensePerItem[$itemIndex]['others'][] = ['type' => '', 'amount' => 0];
        }
        $this->updateExpensesPerItem();
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
            'invoiceNumber.string'   => 'Nomor invoice harus berupa teks',
            'invoiceNumber.max'      => 'Nomor invoice maksimal 191 karakter',
        ]);

        $exists = InvoicePenagihan::where('invoice_number', $this->invoiceNumber)
            ->where('id', '!=', $this->invoice->id)
            ->exists();

        if ($exists) {
            session()->flash('error', 'Nomor invoice "' . $this->invoiceNumber . '" sudah digunakan.');
            return;
        }

        try {
            $oldNumber = $this->invoice->invoice_number;

            $this->invoice->update(['invoice_number' => $this->invoiceNumber]);

            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id'   => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id'    => $this->approval->invoice_id,
                    'role'          => $role,
                    'user_id'       => $user->id,
                    'action'        => 'edited',
                    'notes'         => 'Nomor invoice diubah dari ' . $oldNumber . ' menjadi ' . $this->invoiceNumber,
                ]);
            }

            session()->flash('message', 'Nomor invoice berhasil diperbarui');

            // FIX: Hanya refresh invoice, tidak loadApproval()
            $this->invoice->refresh();

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                session()->flash('error', 'Nomor invoice "' . $this->invoiceNumber . '" sudah digunakan.');
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
            'dueDate'     => 'required|date|after_or_equal:invoiceDate',
        ], [
            'invoiceDate.required'      => 'Tanggal invoice harus diisi',
            'invoiceDate.date'          => 'Format tanggal invoice tidak valid',
            'dueDate.required'          => 'Tanggal jatuh tempo harus diisi',
            'dueDate.date'              => 'Format tanggal jatuh tempo tidak valid',
            'dueDate.after_or_equal'    => 'Tanggal jatuh tempo harus sama atau setelah tanggal invoice',
        ]);

        try {
            $this->invoice->update([
                'invoice_date' => $this->invoiceDate,
                'due_date'     => $this->dueDate,
            ]);

            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id'   => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id'    => $this->approval->invoice_id,
                    'role'          => $role,
                    'user_id'       => $user->id,
                    'action'        => 'edited',
                    'notes'         => 'Tanggal invoice diubah: ' . $this->invoiceDate . ', jatuh tempo: ' . $this->dueDate,
                ]);
            }

            session()->flash('message', 'Tanggal invoice berhasil diupdate');

            // FIX: Hanya refresh invoice, tidak loadApproval()
            $this->invoice->refresh();

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
            'customerName'    => 'required|string|max:255',
            'customerAddress' => 'required|string',
            'customerPhone'   => 'nullable|string|max:20',
            'customerEmail'   => 'nullable|email|max:255',
        ], [
            'customerName.required'    => 'Nama customer harus diisi',
            'customerAddress.required' => 'Alamat customer harus diisi',
            'customerEmail.email'      => 'Format email tidak valid',
        ]);

        try {
            $this->invoice->update([
                'customer_name'    => $this->customerName,
                'customer_address' => $this->customerAddress,
                'customer_phone'   => $this->customerPhone,
                'customer_email'   => $this->customerEmail,
            ]);

            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id'   => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id'    => $this->approval->invoice_id,
                    'role'          => $role,
                    'user_id'       => $user->id,
                    'action'        => 'edited',
                    'notes'         => 'Informasi customer diubah: ' . $this->customerName,
                ]);
            }

            session()->flash('message', 'Informasi customer berhasil diupdate');

            // FIX: Hanya refresh invoice, tidak loadApproval()
            $this->invoice->refresh();

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
            $this->invoice->update(['notes' => $this->invoiceNotes]);

            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id'   => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id'    => $this->approval->invoice_id,
                    'role'          => $role,
                    'user_id'       => $user->id,
                    'action'        => 'edited',
                    'notes'         => 'Catatan invoice diperbarui',
                ]);
            }

            session()->flash('message', 'Catatan invoice berhasil diupdate');

            // FIX: Hanya refresh invoice, tidak loadApproval()
            $this->invoice->refresh();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate catatan: ' . $e->getMessage());
        }
    }

    private function loadExpenses(): void
    {
        $this->expenseForm = [
            'truk' => 0,
            'kuli' => 0,
            'fee'  => 0,
            'others' => [],
        ];

        if (!$this->invoice) return;

        $this->invoice->loadMissing('expenses');

        foreach ($this->invoice->expenses as $e) {
            $type   = trim((string)($e->type ?? ''));
            $amount = floatval($e->amount ?? 0);

            if ($type === 'truk') {
                $this->expenseForm['truk'] = $amount;
            } elseif ($type === 'kuli') {
                $this->expenseForm['kuli'] = $amount;
            } elseif ($type === 'fee') {
                $this->expenseForm['fee'] = $amount;
            } else {
                $this->expenseForm['others'][] = ['type' => $type, 'amount' => $amount];
            }
        }

        if (empty($this->expenseForm['others'])) {
            $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
        }
    }

    public function addOtherExpenseRow(): void
    {
        if (!$this->ensureCanManage()) return;
        $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
    }

    public function removeOtherExpenseRow(int $index): void
    {
        if (!$this->ensureCanManage()) return;

        if (isset($this->expenseForm['others'][$index])) {
            array_splice($this->expenseForm['others'], $index, 1);
        }

        if (empty($this->expenseForm['others'])) {
            $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
        }

        $this->updateExpenses();
    }

    public function updateExpenses(): void
    {
        if (!$this->ensureCanManage()) return;

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        // Validasi fixed
        foreach (['truk', 'kuli', 'fee'] as $k) {
            if (floatval($this->expenseForm[$k] ?? 0) < 0) {
                session()->flash('error', ucfirst($k) . ' tidak boleh negatif');
                return;
            }
        }

        // Validasi others
        foreach (($this->expenseForm['others'] ?? []) as $i => $row) {
            $amount = floatval($row['amount'] ?? 0);
            $type   = trim((string)($row['type'] ?? ''));

            if ($amount < 0) {
                session()->flash('error', 'Nominal tidak boleh negatif (baris #' . ($i + 1) . ')');
                return;
            }
            if ($amount > 0 && $type === '') {
                session()->flash('error', 'Nama pengeluaran wajib diisi (baris #' . ($i + 1) . ')');
                return;
            }
            if ($amount > 0 && in_array(strtolower($type), ['truk', 'kuli', 'fee'], true)) {
                session()->flash('error', 'Nama "' . $type . '" sudah ada di opsi utama (baris #' . ($i + 1) . ')');
                return;
            }
        }

        DB::beginTransaction();
        try {
            $oldTotal = floatval($this->invoice->additional_expenses_total ?? 0);

            // Hapus semua expense lama, lalu insert ulang
            $this->invoice->expenses()->delete();

            $expensesTotal = 0;

            $fixed = [
                'truk' => floatval($this->expenseForm['truk'] ?? 0),
                'kuli' => floatval($this->expenseForm['kuli'] ?? 0),
                'fee'  => floatval($this->expenseForm['fee'] ?? 0),
            ];

            foreach ($fixed as $type => $amount) {
                if ($amount > 0) {
                    $this->invoice->expenses()->create(['type' => $type, 'amount' => $amount]);
                    $expensesTotal += $amount;
                }
            }

            foreach (($this->expenseForm['others'] ?? []) as $row) {
                $type   = trim((string)($row['type'] ?? ''));
                $amount = floatval($row['amount'] ?? 0);
                if ($type === '' || $amount <= 0) continue;
                $this->invoice->expenses()->create(['type' => $type, 'amount' => $amount]);
                $expensesTotal += $amount;
            }

            $amountAfterRefraksi = floatval($this->invoice->amount_after_refraksi ?? $this->invoice->subtotal ?? 0);
            $this->invoice->update([
                'additional_expenses_total' => $expensesTotal,
                'subtotal'                  => $amountAfterRefraksi,
                'total_amount'              => max(0, $amountAfterRefraksi + $expensesTotal - floatval($this->invoice->discount_amount ?? 0)),
            ]);

            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id'   => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id'    => $this->approval->invoice_id,
                    'role'          => $role,
                    'user_id'       => $user->id,
                    'action'        => 'edited',
                    'notes'         => 'Pengeluaran tambahan invoice diubah: Rp ' .
                                    number_format($oldTotal, 0, ',', '.') . ' → Rp ' .
                                    number_format($expensesTotal, 0, ',', '.'),
                    'changes'       => [
                        'field' => 'additional_expenses',
                        'old'   => 'Rp ' . number_format($oldTotal, 0, ',', '.'),
                        'new'   => 'Rp ' . number_format($expensesTotal, 0, ',', '.'),
                    ],
                ]);
            }

            DB::commit();
            session()->flash('message', 'Pengeluaran tambahan invoice berhasil disimpan');

            // FIX: Hanya refresh invoice + reload expenses saja
            // TIDAK memanggil loadApproval() agar field form lain tidak ter-reset
            $this->invoice->refresh();
            $this->loadExpenses();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan pengeluaran: ' . $e->getMessage());
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

        $this->validate([
            'invoiceNumber'   => 'required|string|max:191',
            'invoiceDate'     => 'required|date',
            'dueDate'         => 'required|date|after_or_equal:invoiceDate',
            'customerName'    => 'required|string|max:255',
            'customerAddress' => 'required|string',
            'customerPhone'   => 'nullable|string|max:20',
            'customerEmail'   => 'nullable|email|max:255',
        ], [
            'invoiceNumber.required'   => 'Nomor invoice harus diisi',
            'invoiceDate.required'     => 'Tanggal invoice harus diisi',
            'dueDate.required'         => 'Tanggal jatuh tempo harus diisi',
            'dueDate.after_or_equal'   => 'Tanggal jatuh tempo harus sama atau setelah tanggal invoice',
            'customerName.required'    => 'Nama customer harus diisi',
            'customerAddress.required' => 'Alamat customer harus diisi',
            'customerEmail.email'      => 'Format email tidak valid',
        ]);

        $exists = InvoicePenagihan::where('invoice_number', $this->invoiceNumber)
            ->where('id', '!=', $this->invoice->id)
            ->exists();

        if ($exists) {
            session()->flash('error', 'Nomor invoice "' . $this->invoiceNumber . '" sudah digunakan.');
            return;
        }

        DB::beginTransaction();
        try {
            $this->invoice->update([
                'invoice_number'   => $this->invoiceNumber,
                'invoice_date'     => $this->invoiceDate,
                'due_date'         => $this->dueDate,
                'customer_name'    => $this->customerName,
                'customer_address' => $this->customerAddress,
                'customer_phone'   => $this->customerPhone,
                'customer_email'   => $this->customerEmail,
                'notes'            => $this->invoiceNotes,
            ]);

            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'penagihan',
                    'approval_id'   => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'invoice_id'    => $this->approval->invoice_id,
                    'role'          => $role,
                    'user_id'       => $user->id,
                    'action'        => 'edited',
                    'notes'         => 'Invoice diperbarui: ' . $this->invoiceNumber,
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
        $order = null;
        $totalSupplierCost = 0;
        $totalSelling = 0;
        $totalMargin = 0;
        $marginPercentage = 0;

        $isMerged = $this->invoice && $this->invoice->pengirimans->count() > 0;
        $shipments = $isMerged ? $this->invoice->pengirimans : collect($this->pengiriman ? [$this->pengiriman] : []);

        foreach ($shipments as $s) {
            $s->loadMissing(['approvalPembayaran', 'pengirimanDetails.bahanBakuSupplier', 'pengirimanDetails.purchaseOrderBahanBaku.bahanBakuKlien', 'pengirimanDetails.orderDetail']);
        }

        if ($shipments->count() > 0) {
            if ($this->pengiriman) {
                $order = $this->pengiriman->purchaseOrder ?? null;
            }

            // === Calculate Subtotal Pembayaran (Supplier Cost) ===
            foreach ($shipments as $s) {
                $approvalPembayaran = $s->approvalPembayaran;
                if ($approvalPembayaran) {
                    if (floatval($approvalPembayaran->subtotal) > 0) {
                        $totalSupplierCost += floatval($approvalPembayaran->subtotal);
                    } elseif (floatval($approvalPembayaran->amount_after_refraksi) > 0) {
                        $totalSupplierCost += floatval($approvalPembayaran->amount_after_refraksi);
                    } elseif (floatval($s->total_harga_kirim) > 0) {
                        $totalSupplierCost += floatval($s->total_harga_kirim);
                    }
                } else {
                    if (floatval($s->total_harga_kirim) > 0) {
                        $totalSupplierCost += floatval($s->total_harga_kirim);
                    }
                }

                // === Calculate Total Selling ===
                if ($s->pengirimanDetails) {
                    foreach ($s->pengirimanDetails as $detail) {
                        $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                        if ($orderDetail && $orderDetail->harga_jual) {
                            $totalSelling += floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual);
                        }
                    }
                }
            }

            $totalMargin = $totalSelling - $totalSupplierCost;
            $marginPercentage = $totalSelling > 0 ? ($totalMargin / $totalSelling) * 100 : 0;
        }

        return view('livewire.accounting.approve-penagihan', [
            'order'             => $order,
            'totalSupplierCost' => $totalSupplierCost,
            'totalSelling'      => $totalSelling,
            'totalMargin'       => $totalMargin,
            'marginPercentage'  => $marginPercentage,
            'isMerged'          => $isMerged,
            'shipments'         => $shipments,
        ]);
    }
}