<?php

namespace App\Livewire\Accounting;

use App\Models\ApprovalPenagihan;
use App\Models\ApprovalHistory;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DetailPenagihan extends Component
{
    public $approvalId;
    public $approval;
    public $invoice;
    public $pengiriman;
    public $pengirimans;
    public $approvalHistory;
    public $companySetting;
    public $editMode = false;
    public $canManage = false;
    public $expenseForm = [
        'truk' => 0,
        'kuli' => 0,
        'fee' => 0,
        'others' => [],
    ];

    // Per-pengiriman forms (indexed by items JSON index)
    public $refraksiPerItem = [];
    public $expensePerItem = [];

    // =====================================================================
    // FIX: Tambah $refraksiForm untuk invoice tunggal (non-merged),
    // sama seperti ApprovePenagihan. Sebelumnya property ini tidak ada
    // sehingga updateRefraksi() tidak bisa dipanggil dari blade.
    // =====================================================================
    public $refraksiForm = [
        'type'  => 'qty',
        'value' => 0,
    ];

    // Edit forms
    public $customerForm = [
        'customer_name' => '',
        'customer_address' => '',
        'customer_phone' => '',
        'customer_email' => '',
    ];

    public $dateForm = [
        'invoice_date' => '',
        'due_date' => '',
    ];

    public $bankForm = [
        'bank_name' => '',
        'bank_account_number' => '',
        'bank_account_name' => '',
    ];

    public $invoiceNumberForm = '';
    public $invoiceNotesForm  = '';

    public $selectedBank = null;

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

    protected $rules = [
        'customerForm.customer_name' => 'required|string|max:255',
        'customerForm.customer_address' => 'required|string',
        'customerForm.customer_phone' => 'nullable|string|max:20',
        'customerForm.customer_email' => 'nullable|email|max:255',
        'dateForm.invoice_date' => 'required|date',
        'dateForm.due_date' => 'required|date',
        'bankForm.bank_name' => 'required|string|max:255',
        'bankForm.bank_account_number' => 'required|string|max:50',
        'bankForm.bank_account_name' => 'required|string|max:255',
    ];

    public function mount($approvalId, $editMode = false)
    {
        $this->approvalId = $approvalId;
        $this->editMode = $editMode;

        $user = Auth::user();
        $this->canManage = in_array($user->role, ['manager_accounting', 'direktur', 'superadmin', 'staff_accounting']);

        $this->loadDetail();
    }

    public function loadDetail()
    {
        $this->approval = ApprovalPenagihan::with([
            'staff',
            'manager',
            'invoice.pengirimans.details.bahanBakuSupplier',
            'invoice.pengirimans.details.purchaseOrderBahanBaku.bahanBakuKlien',
            'invoice.pengirimans.details.purchaseOrderBahanBaku',
            'invoice.pengirimans.details.orderDetail',
            'invoice.pengirimans.purchaseOrder.orderDetails.orderSuppliers.supplier',
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'pengiriman.pengirimanDetails.purchaseOrderBahanBaku.bahanBakuKlien',
            'pengiriman.pengirimanDetails.purchaseOrderBahanBaku',
            'pengiriman.pengirimanDetails.orderDetail',
            'pengiriman.purchaseOrder.orderDetails.orderSuppliers.supplier',
            'histories.user'
        ])->findOrFail($this->approvalId);

        $this->invoice    = $this->approval->invoice;
        $this->pengiriman = $this->approval->pengiriman;
        $this->pengirimans = $this->invoice
            ? ($this->invoice->pengirimans->count() > 1 ? $this->invoice->pengirimans : collect([$this->pengiriman]))
            : collect([$this->pengiriman]);
        $this->approvalHistory = $this->approval->histories()->orderBy('created_at', 'desc')->get();
        $this->companySetting  = CompanySetting::first();

        if ($this->invoice) {
            $this->customerForm = [
                'customer_name'    => $this->invoice->customer_name ?? '',
                'customer_address' => $this->invoice->customer_address ?? '',
                'customer_phone'   => $this->invoice->customer_phone ?? '',
                'customer_email'   => $this->invoice->customer_email ?? '',
            ];

            $this->dateForm = [
                'invoice_date' => $this->invoice->invoice_date ? Carbon::parse($this->invoice->invoice_date)->format('Y-m-d') : '',
                'due_date'     => $this->invoice->due_date ? Carbon::parse($this->invoice->due_date)->format('Y-m-d') : '',
            ];

            $this->bankForm = [
                'bank_name'           => $this->invoice->bank_name ?? '',
                'bank_account_number' => $this->invoice->bank_account_number ?? '',
                'bank_account_name'   => $this->invoice->bank_account_name ?? '',
            ];

            // =================================================================
            // FIX: Populate refraksiForm dari invoice — sama seperti
            // ApprovePenagihan::loadApproval() mengisi refraksiForm.
            // Sebelumnya ini tidak ada, sehingga form refraksi tunggal
            // selalu kosong (type='qty', value=0) meski invoice sudah ada data.
            // =================================================================
            $this->refraksiForm = [
                'type'  => $this->invoice->refraksi_type ?? 'qty',
                'value' => (float) ($this->invoice->refraksi_value ?? 0),
            ];

            // Preselect bank
            $this->selectedBank = null;
            if (!empty($this->invoice->bank_account_number)) {
                foreach ($this->bankOptions as $key => $bank) {
                    if ($bank['account_number'] === $this->invoice->bank_account_number) {
                        $this->selectedBank = $key;
                        break;
                    }
                }
            }
            if ($this->selectedBank === null && !empty($this->invoice->bank_name)) {
                foreach ($this->bankOptions as $key => $bank) {
                    if ($bank['name'] === $this->invoice->bank_name) {
                        $this->selectedBank = $key;
                        break;
                    }
                }
            }

            $this->invoiceNumberForm = $this->invoice->invoice_number ?? '';
            $this->invoiceNotesForm  = $this->invoice->notes ?? '';

            // Initialize per-pengiriman forms from items JSON
            $this->refraksiPerItem = [];
            $this->expensePerItem  = [];
            $invoiceItems = $this->invoice->items ?? [];

            if (!empty($invoiceItems)) {
                foreach ($invoiceItems as $i => $item) {
                    $amount = (float) ($item['amount'] ?? $item['total'] ?? 0);
                    $isNewFormat = array_key_exists('refraksi_type', $item);

                    $refraksiType  = $isNewFormat
                        ? ($item['refraksi_type'] ?? 'qty')
                        : ($this->invoice->refraksi_type ?? 'qty');

                    $refraksiValue = $isNewFormat
                        ? (float) ($item['refraksi_value'] ?? 0)
                        : (float) ($this->invoice->refraksi_value ?? 0);

                    $this->refraksiPerItem[$i] = [
                        'type'   => $refraksiType,
                        'value'  => $refraksiValue,
                        'amount' => $amount,
                    ];

                    $expenses = $item['expenses'] ?? [];
                    $truk = 0; $kuli = 0; $fee = 0; $others = [];
                    foreach ($expenses as $e) {
                        $t = strtolower(trim($e['type'] ?? ''));
                        $a = (float) ($e['amount'] ?? 0);
                        if ($t === 'truk')       $truk = $a;
                        elseif ($t === 'kuli')   $kuli = $a;
                        elseif ($t === 'fee')    $fee  = $a;
                        elseif ($t !== '')       $others[] = ['type' => $t, 'amount' => $a];
                    }

                    $this->expensePerItem[$i] = [
                        'truk'   => $truk,
                        'kuli'   => $kuli,
                        'fee'    => $fee,
                        'others' => !empty($others) ? $others : [['type' => '', 'amount' => 0]],
                    ];
                }
            } elseif ($this->invoice) {
                $this->refraksiPerItem[0] = [
                    'type'   => $this->invoice->refraksi_type ?? 'qty',
                    'value'  => (float) ($this->invoice->refraksi_value ?? 0),
                    'amount' => (float) ($this->invoice->amount_before_refraksi ?? $this->invoice->subtotal ?? 0),
                ];
                $this->expensePerItem[0] = [
                    'truk'   => 0,
                    'kuli'   => 0,
                    'fee'    => 0,
                    'others' => [['type' => '', 'amount' => 0]],
                ];
            }
        }
    }

    // =========================================================================
    // FIX: updateRefraksi() — method baru untuk invoice tunggal (non-merged).
    //
    // Sebelumnya DetailPenagihan sama sekali tidak punya method ini, sehingga
    // tombol "Simpan Refraksi" pada edit mode tidak bisa bekerja untuk invoice
    // tunggal. Logika perhitungan PERSIS sama dengan ApprovePenagihan::updateRefraksi():
    //   - qty dan totalSelling dihitung ulang dari pengirimanDetails aktual
    //   - refraksi_amount, qty_before/after, amount_before/after dihitung per tipe
    //   - Tidak memanggil loadDetail() setelah save agar field lain tidak ter-reset
    // =========================================================================
    public function updateRefraksi()
    {
        if (!$this->canManage) {
            session()->flash('error', 'Anda tidak memiliki akses');
            return;
        }

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Hitung qty dan total harga jual dari pengirimanDetails aktual
            // — sama persis dengan ApprovePenagihan::updateRefraksi()
            $totalSelling      = 0;
            $qtyBeforeRefraksi = 0;

            $isMerged  = $this->invoice->pengirimans && $this->invoice->pengirimans->count() > 0;
            $shipments = $isMerged
                ? $this->invoice->pengirimans
                : collect([$this->pengiriman]);

            foreach ($shipments as $s) {
                if (!$s) continue;

                $qtyBeforeRefraksi += floatval($s->total_qty_kirim);

                // Coba pengirimanDetails dulu, fallback ke details
                $details = $s->pengirimanDetails ?? $s->details ?? collect();
                foreach ($details as $detail) {
                    $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                    if ($orderDetail && $orderDetail->harga_jual) {
                        $totalSelling += floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual);
                    }
                }
            }

            $refraksiValue = floatval($this->refraksiForm['value'] ?? 0);

            if ($refraksiValue <= 0) {
                // Tidak ada refraksi — sama seperti ApprovePenagihan
                $this->invoice->update([
                    'refraksi_type'          => null,
                    'refraksi_value'         => 0,
                    'refraksi_amount'        => 0,
                    'qty_before_refraksi'    => $qtyBeforeRefraksi,
                    'qty_after_refraksi'     => $qtyBeforeRefraksi,
                    'amount_before_refraksi' => $totalSelling,
                    'amount_after_refraksi'  => $totalSelling,
                    'subtotal'               => $totalSelling,
                    'total_amount'           => max(0,
                        $totalSelling
                        + floatval($this->invoice->additional_expenses_total ?? 0)
                        + floatval($this->invoice->tax_amount ?? 0)
                        - floatval($this->invoice->discount_amount ?? 0)
                    ),
                ]);
            } else {
                $refraksiType         = $this->refraksiForm['type'];
                $amountBeforeRefraksi = $totalSelling;
                $qtyAfterRefraksi     = $qtyBeforeRefraksi;
                $refraksiAmount       = 0;
                $subtotal             = $amountBeforeRefraksi;

                // ============================================================
                // Logika identik dengan ApprovePenagihan::updateRefraksi()
                // ============================================================
                if ($refraksiType === 'qty') {
                    $refraksiQty      = $qtyBeforeRefraksi * ($refraksiValue / 100);
                    $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;
                    $hargaPerKg       = $qtyBeforeRefraksi > 0
                        ? $subtotal / $qtyBeforeRefraksi
                        : 0;
                    $refraksiAmount = $refraksiQty * $hargaPerKg;
                    $subtotal       = $subtotal - $refraksiAmount;
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
                    'total_amount'           => max(0,
                        $subtotal
                        + floatval($this->invoice->additional_expenses_total ?? 0)
                        + floatval($this->invoice->tax_amount ?? 0)
                        - floatval($this->invoice->discount_amount ?? 0)
                    ),
                ]);
            }

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id'   => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id'    => $this->invoice->id,
                'role'          => $this->getUserRole($user),
                'user_id'       => $user->id,
                'action'        => 'edited',
                'notes'         => 'Refraksi diubah: ' . (
                    $refraksiValue <= 0
                        ? 'tidak ada'
                        : ($this->refraksiForm['type'] . ' - ' . $refraksiValue)
                ),
            ]);

            DB::commit();
            session()->flash('message', 'Refraksi berhasil diupdate');

            // Hanya refresh invoice, TIDAK loadDetail() — mencegah reset field lain
            $this->invoice->refresh();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mengupdate refraksi: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // updateRefraksiPerItem() — untuk invoice merged (items JSON per pengiriman)
    // Logika identik dengan ApprovePenagihan::updateRefraksiPerItem()
    // =========================================================================
    public function updateRefraksiPerItem()
    {
        $this->validate([
            'refraksiPerItem.*.type'   => 'nullable|in:qty,rupiah,lainnya',
            'refraksiPerItem.*.value'  => 'nullable|numeric|min:0',
            'refraksiPerItem.*.amount' => 'nullable|numeric|min:0',
        ]);

        if (!$this->canManage) {
            session()->flash('error', 'Anda tidak memiliki akses');
            return;
        }

        DB::beginTransaction();
        try {
            $user  = Auth::user();
            $items = $this->invoice->items ?? [];

            if (empty($items)) {
                $items = [[
                    'item_name' => 'Pengiriman #1',
                    'amount'    => (float) ($this->invoice->amount_before_refraksi ?? $this->invoice->subtotal ?? 0),
                    'details'   => [],
                    'expenses'  => [],
                ]];
            }

            $totalSellingPrice   = 0;
            $totalRefraksiAmount = 0;
            $totalRefraksiQty    = 0;
            $totalQty            = 0;

            foreach ($items as $i => &$item) {
                $type  = $this->refraksiPerItem[$i]['type']  ?? 'qty';
                $value = (float) ($this->refraksiPerItem[$i]['value'] ?? 0);

                $item['refraksi_type']  = $type;
                $item['refraksi_value'] = $value;

                $total = (float) ($this->refraksiPerItem[$i]['amount'] ?? $item['amount'] ?? $item['total'] ?? 0);
                $item['amount'] = $total;
                $totalSellingPrice += $total;

                // ================================================================
                // FIX: Ambil qty dengan fallback chain
                // 1. Dari shipment aktual (paling akurat)
                // 2. Dari root item JSON key 'quantity'
                // 3. Dari nested details JSON
                // ================================================================
                $qty = 0;

                // 1. Dari relasi pengiriman aktual
                $isMerged  = $this->invoice->pengirimans && $this->invoice->pengirimans->count() > 0;
                $shipments = $isMerged
                    ? $this->invoice->pengirimans
                    : collect([$this->pengiriman]);

                $shipmentArray = $shipments->values();
                if (isset($shipmentArray[$i])) {
                    $qty = floatval($shipmentArray[$i]->total_qty_kirim ?? 0);
                }

                // 2. Fallback: root item key 'quantity'
                if ($qty <= 0) {
                    $qty = floatval($item['quantity'] ?? 0);
                }

                // 3. Fallback: nested details
                if ($qty <= 0) {
                    $details = $item['details'] ?? [];
                    foreach ($details as $d) {
                        $qty += floatval($d['qty_kirim'] ?? $d['qty'] ?? 0);
                    }
                }

                $totalQty += $qty;

                if ($value > 0) {
                    if ($type === 'qty' && $qty > 0) {
                        $refraksiQty             = $qty * ($value / 100);
                        $hargaPerKg              = $qty > 0 ? $total / $qty : 0;
                        $item['refraksi_amount'] = $refraksiQty * $hargaPerKg;
                        $totalRefraksiQty       += $refraksiQty;
                    } elseif ($type === 'rupiah' && $qty > 0) {
                        // 60 * 3520 = 211200 ✓
                        $item['refraksi_amount'] = $value * $qty;
                    } elseif ($type === 'lainnya') {
                        $item['refraksi_amount'] = $value;
                    } else {
                        $item['refraksi_amount'] = 0;
                    }
                } else {
                    $item['refraksi_amount'] = 0;
                }

                $totalRefraksiAmount += (float) ($item['refraksi_amount'] ?? 0);
            }
            unset($item);

            $amountAfterRefraksi = $totalSellingPrice - $totalRefraksiAmount;

            $expensesTotal = 0;
            foreach ($items as $item) {
                foreach ($item['expenses'] ?? [] as $e) {
                    $expensesTotal += (float) ($e['amount'] ?? 0);
                }
            }

            $firstItem = $this->refraksiPerItem[0] ?? ['type' => 'qty', 'value' => 0];

            $this->invoice->items                     = $items;
            $this->invoice->refraksi_type             = $firstItem['type'];
            $this->invoice->refraksi_value            = (float) $firstItem['value'];
            $this->invoice->refraksi_amount           = $totalRefraksiAmount;
            $this->invoice->qty_before_refraksi       = $totalQty;
            $this->invoice->qty_after_refraksi        = $totalQty - $totalRefraksiQty;
            $this->invoice->amount_before_refraksi    = $totalSellingPrice;
            $this->invoice->amount_after_refraksi     = $amountAfterRefraksi;
            $this->invoice->subtotal                  = $amountAfterRefraksi;
            $this->invoice->additional_expenses_total = $expensesTotal;
            $this->invoice->total_amount              = max(
                0,
                $amountAfterRefraksi
                + $expensesTotal
                + floatval($this->invoice->tax_amount ?? 0)
                - floatval($this->invoice->discount_amount ?? 0)
            );
            $this->invoice->save();

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id'   => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id'    => $this->invoice->id,
                'role'          => $this->getUserRole($user),
                'user_id'       => $user->id,
                'action'        => 'edited',
                'notes'         => 'Update harga jual dan refraksi per pengiriman',
            ]);

            DB::commit();
            session()->flash('message', 'Harga jual / refraksi per pengiriman berhasil diupdate');
            $this->invoice->refresh();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update refraksi: ' . $e->getMessage());
        }
    }

    public function updateCustomerInfo()
    {
        $this->validate([
            'customerForm.customer_name'    => 'required|string|max:255',
            'customerForm.customer_address' => 'required|string',
            'customerForm.customer_phone'   => 'nullable|string|max:20',
            'customerForm.customer_email'   => 'nullable|email|max:255',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => [
                    'customer_name'    => $this->invoice->customer_name,
                    'customer_address' => $this->invoice->customer_address,
                    'customer_phone'   => $this->invoice->customer_phone,
                    'customer_email'   => $this->invoice->customer_email,
                ],
                'after' => $this->customerForm,
            ];

            $this->invoice->update($this->customerForm);

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id'   => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id'    => $this->invoice->id,
                'role'          => $this->getUserRole($user),
                'user_id'       => $user->id,
                'action'        => 'edited',
                'changes'       => $changes,
                'notes'         => 'Update informasi customer',
            ]);

            DB::commit();
            session()->flash('message', 'Informasi customer berhasil diupdate');
            $this->invoice->refresh();
            $this->customerForm = [
                'customer_name'    => $this->invoice->customer_name ?? '',
                'customer_address' => $this->invoice->customer_address ?? '',
                'customer_phone'   => $this->invoice->customer_phone ?? '',
                'customer_email'   => $this->invoice->customer_email ?? '',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update informasi customer: ' . $e->getMessage());
        }
    }

    public function updateInvoiceDates()
    {
        $this->validate([
            'dateForm.invoice_date' => 'required|date',
            'dateForm.due_date'     => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => [
                    'invoice_date' => $this->invoice->invoice_date ? Carbon::parse($this->invoice->invoice_date)->format('Y-m-d') : null,
                    'due_date'     => $this->invoice->due_date ? Carbon::parse($this->invoice->due_date)->format('Y-m-d') : null,
                ],
                'after' => $this->dateForm,
            ];

            $this->invoice->invoice_date = Carbon::parse($this->dateForm['invoice_date'])->format('Y-m-d');
            $this->invoice->due_date     = Carbon::parse($this->dateForm['due_date'])->format('Y-m-d');
            $this->invoice->save();

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id'   => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id'    => $this->invoice->id,
                'role'          => $this->getUserRole($user),
                'user_id'       => $user->id,
                'action'        => 'edited',
                'changes'       => $changes,
                'notes'         => 'Update tanggal invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Tanggal invoice berhasil diupdate');
            $this->invoice->refresh();
            $this->dateForm = [
                'invoice_date' => $this->invoice->invoice_date ? Carbon::parse($this->invoice->invoice_date)->format('Y-m-d') : '',
                'due_date'     => $this->invoice->due_date ? Carbon::parse($this->invoice->due_date)->format('Y-m-d') : '',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update tanggal invoice: ' . $e->getMessage());
        }
    }

    public function updateBankInfo()
    {
        $this->validate([
            'bankForm.bank_name'           => 'required|string|max:255',
            'bankForm.bank_account_number' => 'required|string|max:50',
            'bankForm.bank_account_name'   => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => [
                    'bank_name'           => $this->invoice->bank_name,
                    'bank_account_number' => $this->invoice->bank_account_number,
                    'bank_account_name'   => $this->invoice->bank_account_name,
                ],
                'after' => $this->bankForm,
            ];

            $this->invoice->update($this->bankForm);

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id'   => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id'    => $this->invoice->id,
                'role'          => $this->getUserRole($user),
                'user_id'       => $user->id,
                'action'        => 'edited',
                'changes'       => $changes,
                'notes'         => 'Update informasi bank',
            ]);

            DB::commit();
            session()->flash('message', 'Informasi bank berhasil diupdate');
            $this->invoice->refresh();
            $this->bankForm = [
                'bank_name'           => $this->invoice->bank_name ?? '',
                'bank_account_number' => $this->invoice->bank_account_number ?? '',
                'bank_account_name'   => $this->invoice->bank_account_name ?? '',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update informasi bank: ' . $e->getMessage());
        }
    }

    public function updateInvoiceNotes()
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => ['notes' => $this->invoice->notes],
                'after'  => ['notes' => $this->invoiceNotesForm],
            ];

            $this->invoice->notes = $this->invoiceNotesForm;
            $this->invoice->save();

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id'   => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id'    => $this->invoice->id,
                'role'          => $this->getUserRole($user),
                'user_id'       => $user->id,
                'action'        => 'edited',
                'changes'       => $changes,
                'notes'         => 'Update catatan invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Catatan invoice berhasil diupdate');
            $this->invoice->refresh();
            $this->invoiceNotesForm = $this->invoice->notes ?? '';
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update catatan invoice: ' . $e->getMessage());
        }
    }

    public function updateInvoiceNumber()
    {
        $this->validate([
            'invoiceNumberForm' => 'required|string|max:50|unique:invoice_penagihan,invoice_number,' . $this->invoice->id,
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => ['invoice_number' => $this->invoice->invoice_number],
                'after'  => ['invoice_number' => $this->invoiceNumberForm],
            ];

            $this->invoice->invoice_number = $this->invoiceNumberForm;
            $this->invoice->save();

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id'   => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id'    => $this->invoice->id,
                'role'          => $this->getUserRole($user),
                'user_id'       => $user->id,
                'action'        => 'edited',
                'changes'       => $changes,
                'notes'         => 'Update nomor invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Nomor invoice berhasil diupdate');
            $this->invoice->refresh();
            $this->invoiceNumberForm = $this->invoice->invoice_number ?? '';
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update nomor invoice: ' . $e->getMessage());
        }
    }

    public function updateExpensesPerItem()
    {
        if (!$this->canManage) {
            session()->flash('error', 'Anda tidak memiliki akses');
            return;
        }

        if (!$this->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        if (empty($this->expensePerItem)) {
            session()->flash('error', 'Tidak ada data pengeluaran');
            return;
        }

        foreach ($this->expensePerItem as $i => $exp) {
            foreach (['truk', 'kuli', 'fee'] as $k) {
                if (floatval($exp[$k] ?? 0) < 0) {
                    session()->flash('error', 'Item #' . ($i + 1) . ': ' . ucfirst($k) . ' tidak boleh negatif');
                    return;
                }
            }
            foreach (($exp['others'] ?? []) as $j => $row) {
                $amount = floatval($row['amount'] ?? 0);
                $type   = trim((string) ($row['type'] ?? ''));
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
            $user  = Auth::user();
            $items = $this->invoice->items ?? [];

            if (empty($items)) {
                $items = [[
                    'item_name'       => 'Pengiriman #1',
                    'amount'          => (float) ($this->invoice->amount_before_refraksi ?? $this->invoice->subtotal ?? 0),
                    'refraksi_type'   => $this->invoice->refraksi_type ?? 'qty',
                    'refraksi_value'  => (float) ($this->invoice->refraksi_value ?? 0),
                    'refraksi_amount' => (float) ($this->invoice->refraksi_amount ?? 0),
                    'details'         => [],
                    'expenses'        => [],
                ]];
            }

            $allExpensesFlat = [];
            $expensesTotal   = 0;

            foreach ($items as $i => &$item) {
                $exp          = $this->expensePerItem[$i] ?? [];
                $itemExpenses = [];

                foreach (['truk', 'kuli', 'fee'] as $type) {
                    $amount = floatval($exp[$type] ?? 0);
                    if ($amount > 0) {
                        $itemExpenses[]  = ['type' => $type, 'amount' => $amount];
                        $expensesTotal  += $amount;
                    }
                }

                foreach (($exp['others'] ?? []) as $row) {
                    $type   = trim((string) ($row['type'] ?? ''));
                    $amount = floatval($row['amount'] ?? 0);
                    if ($type === '' || $amount <= 0) continue;
                    $itemExpenses[]  = ['type' => $type, 'amount' => $amount];
                    $expensesTotal  += $amount;
                }

                $item['expenses'] = $itemExpenses;
                $allExpensesFlat  = array_merge($allExpensesFlat, $itemExpenses);
            }
            unset($item);

            $totalRefraksiAmount = 0;
            $totalSellingPrice   = 0;
            foreach ($items as $item) {
                $totalSellingPrice   += (float) ($item['amount'] ?? $item['total'] ?? 0);
                $totalRefraksiAmount += (float) ($item['refraksi_amount'] ?? 0);
            }
            $amountAfterRefraksi = $totalSellingPrice - $totalRefraksiAmount;

            $this->invoice->items                     = $items;
            $this->invoice->additional_expenses_total = $expensesTotal;
            $this->invoice->subtotal                  = $amountAfterRefraksi;
            $this->invoice->total_amount              = max(
                0,
                $amountAfterRefraksi
                + $expensesTotal
                + floatval($this->invoice->tax_amount ?? 0)
                - floatval($this->invoice->discount_amount ?? 0)
            );
            $this->invoice->save();

            $this->invoice->expenses()->delete();
            foreach ($allExpensesFlat as $e) {
                $this->invoice->expenses()->create($e);
            }

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id'   => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id'    => $this->invoice->id,
                'role'          => $this->getUserRole($user),
                'user_id'       => $user->id,
                'action'        => 'edited',
                'notes'         => 'Pengeluaran tambahan per pengiriman diubah',
            ]);

            DB::commit();
            session()->flash('message', 'Pengeluaran tambahan per pengiriman berhasil disimpan');
            $this->invoice->refresh();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan pengeluaran: ' . $e->getMessage());
        }
    }

    public function addOtherExpenseRow($itemIndex): void
    {
        if (!$this->canManage) return;
        if (!isset($this->expensePerItem[$itemIndex])) return;
        $this->expensePerItem[$itemIndex]['others'][] = ['type' => '', 'amount' => 0];
    }

    public function removeOtherExpenseRow($itemIndex, $rowIndex): void
    {
        if (!$this->canManage) return;
        if (!isset($this->expensePerItem[$itemIndex]['others'][$rowIndex])) return;
        array_splice($this->expensePerItem[$itemIndex]['others'], $rowIndex, 1);
        if (empty($this->expensePerItem[$itemIndex]['others'])) {
            $this->expensePerItem[$itemIndex]['others'][] = ['type' => '', 'amount' => 0];
        }
        $this->updateExpensesPerItem();
    }

    public function updatedSelectedBank($value)
    {
        if (!$value || !array_key_exists($value, $this->bankOptions)) {
            return;
        }
        $bank = $this->bankOptions[$value];
        $this->bankForm = [
            'bank_name'           => $bank['name'],
            'bank_account_number' => $bank['account_number'],
            'bank_account_name'   => $bank['account_name'],
        ];
    }

    private function getUserRole($user)
    {
        if ($user->role === 'manager_accounting') return 'manager_keuangan';
        if ($user->role === 'staff_accounting')   return 'staff';
        if ($user->role === 'direktur')            return 'direktur';
        if ($user->role === 'superadmin')          return 'superadmin';
        return null;
    }

    public function generatePdf()
    {
        try {
            $approval = ApprovalPenagihan::with([
                'invoice.pengirimans.details.bahanBakuSupplier',
                'invoice.pengirimans.details.purchaseOrderBahanBaku.bahanBakuKlien',
                'invoice.pengirimans.purchaseOrder.klien',
                'pengiriman.details.bahanBakuSupplier',
                'pengiriman.details.purchaseOrderBahanBaku.bahanBakuKlien',
                'pengiriman.purchaseOrder.klien'
            ])->findOrFail($this->approvalId);

            $invoice        = $approval->invoice;
            $pengiriman     = $approval->pengiriman;
            $pengirimans    = $invoice->pengirimans->count() > 1 ? $invoice->pengirimans : collect([$pengiriman]);
            $companySetting = CompanySetting::first();

            $data = [
                'invoice'     => $invoice,
                'pengiriman'  => $pengiriman,
                'pengirimans' => $pengirimans,
                'approval'    => $approval,
                'company'     => $companySetting,
            ];

            $pdf = Pdf::loadView('pdf.invoice-penagihan', $data);
            $pdf->setPaper('a4', 'portrait');

            $cleanInvoiceNumber = str_replace(['/', '\\'], '-', $invoice->invoice_number);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'Invoice-' . $cleanInvoiceNumber . '.pdf');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $order = $this->pengiriman->purchaseOrder ?? null;

        $this->pengirimans->each(fn($p) => $p->loadMissing(['approvalPembayaran']));

        $subtotalPenagihan = 0;
        if ($this->invoice) {
            if (floatval($this->invoice->subtotal) > 0) {
                $subtotalPenagihan = floatval($this->invoice->subtotal);
            } elseif (floatval($this->invoice->amount_after_refraksi) > 0) {
                $subtotalPenagihan = floatval($this->invoice->amount_after_refraksi);
            }
        }

        $subtotalPembayaran = 0;
        foreach ($this->pengirimans as $p) {
            $approvalPembayaran = $p->approvalPembayaran;
            if ($approvalPembayaran) {
                if (floatval($approvalPembayaran->subtotal) > 0) {
                    $subtotalPembayaran += floatval($approvalPembayaran->subtotal);
                } elseif (floatval($approvalPembayaran->amount_after_refraksi) > 0) {
                    $subtotalPembayaran += floatval($approvalPembayaran->amount_after_refraksi);
                } elseif (floatval($p->total_harga_kirim) > 0) {
                    $subtotalPembayaran += floatval($p->total_harga_kirim);
                }
            }
        }

        $totalMargin      = $subtotalPenagihan - $subtotalPembayaran;
        $marginPercentage = $subtotalPenagihan > 0
            ? ($totalMargin / $subtotalPenagihan) * 100
            : 0;

        return view('livewire.accounting.detail-penagihan', [
            'order'              => $order,
            'subtotalPenagihan'  => $subtotalPenagihan,
            'subtotalPembayaran' => $subtotalPembayaran,
            'totalMargin'        => $totalMargin,
            'marginPercentage'   => $marginPercentage,
            'pengirimans'        => $this->pengirimans,
        ]);
    }
}