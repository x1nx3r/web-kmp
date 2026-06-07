<?php

namespace App\Livewire\Accounting;

use App\Models\Pengiriman;
use App\Models\InvoicePenagihan;
use App\Models\ApprovalPenagihan as ApprovalPenagihanModel;
use App\Models\ApprovalHistory;
use App\Models\CompanySetting;
use App\Services\Notifications\ApprovalPenagihanNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class ApprovalPenagihan extends Component
{
    use WithPagination;

    public $search = '';
    public $customerFilter = 'all';
    public $supplierFilter = 'all';
    public $activeTab = 'pending'; // pending or approved
    public $selectedData = null;
    public $selectedShipment = null;      
    public $selectedShipments = null;     
    public $isMergedInvoice = false;      
    public $showDetailModal = false;
    public $showCreateInvoiceModal = false;
    public $notes = '';
    public $editMode = false;
    public $canManage = false;
    public $approvalHistory = [];
    public $approvalId = null;
    public $selectedApprovalIds = [];

    public function getIsMergeValidProperty()
    {
        if (empty($this->selectedApprovalIds)) {
            return false;
        }

        $approvals = ApprovalPenagihanModel::with('invoice')->whereIn('id', $this->selectedApprovalIds)->get();

        if ($approvals->isEmpty()) {
            return false;
        }

        $customerNames = $approvals->map(fn($a) => $a->invoice?->customer_name)->filter()->unique();
        return $customerNames->count() === 1;
    }

    // Invoice form
    public $invoiceForm = [
        'customer_name' => '',
        'customer_address' => '',
        'customer_phone' => '',
        'customer_email' => '',
        'refraksi_type' => 'qty', // 'qty' or 'rupiah'
        'refraksi_value' => 0,
        'notes' => '',
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

    public $invoiceNotesForm = '';
    public $invoiceNumberForm = '';
    public $totalHargaJualForm = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'customerFilter' => ['except' => 'all'],
        'supplierFilter' => ['except' => 'all'],
        'activeTab' => ['except' => 'pending'],
    ];

    protected $rules = [
        'invoiceForm.customer_name' => 'required|string|max:255',
        'invoiceForm.customer_address' => 'required|string',
        'invoiceForm.customer_phone' => 'nullable|string|max:20',
        'invoiceForm.customer_email' => 'nullable|email|max:255',
        'invoiceForm.refraksi_type' => 'required|in:qty,rupiah,lainnya',
        'invoiceForm.refraksi_value' => 'required|numeric|min:0',
        'invoiceForm.notes' => 'nullable|string',
        'customerForm.customer_name' => 'required|string|max:255',
        'customerForm.customer_address' => 'required|string',
        'customerForm.customer_phone' => 'nullable|string|max:20',
        'customerForm.customer_email' => 'nullable|email|max:255',
        'dateForm.invoice_date' => 'required|date',
        'dateForm.due_date' => 'required|date',
        'bankForm.bank_name' => 'required|string|max:255',
        'bankForm.bank_account_number' => 'required|string|max:50',
        'bankForm.bank_account_name' => 'required|string|max:255',
        'invoiceNumberForm' => 'required|string|max:191',
    ];

    public function mount($approvalId = null, $editMode = false)
    {
        $this->editMode = $editMode;
        $this->approvalId = $approvalId;

        // Check permissions - manager_accounting, direktur, or superadmin can manage
        $user = Auth::user();
        $this->canManage = in_array($user->role, ['manager_accounting', 'direktur', 'superadmin','staff_accounting']);

        // If we have approvalId, auto-open detail modal
        if ($approvalId) {
            $this->showDetail($approvalId);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->resetPage('page_without_invoice');
        $this->selectedApprovalIds = [];
    }

    public function updatingCustomerFilter()
    {
        $this->resetPage();
        $this->resetPage('page_without_invoice');
        $this->selectedApprovalIds = [];
    }

    public function updatingSupplierFilter()
    {
        $this->resetPage();
        $this->resetPage('page_without_invoice');
        $this->selectedApprovalIds = [];
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->resetPage('page_without_invoice');
        $this->selectedApprovalIds = [];
    }

    public function gotoPage($page, $pageName = 'page_approval')
    {
        // Use Livewire's setPage method from WithPagination trait
        $this->setPage($page, $pageName);
    }

    public function render()
    {
        // Get pengiriman with status 'berhasil' OR 'menunggu_verifikasi' AND approval_pembayaran completed
        // Show in 'pending' tab - untuk buat invoice
        $pengirimansWithoutInvoice = null;

        if ($this->activeTab === 'pending') {
            $pengirimansWithoutInvoice = Pengiriman::whereIn('status', ['berhasil', 'menunggu_verifikasi'])
                ->doesntHave('invoicePenagihan')
                ->whereHas('approvalPembayaran', function($q) {
                    $q->where('status', 'completed');
                })
                ->with(['purchaseOrder.klien', 'forecast', 'purchasing'])
                ->when($this->search, function ($q) {
                    $q->where('no_pengiriman', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10, ['*'], 'page_without_invoice');
        }

        // Get approval penagihan based on active tab
        $query = ApprovalPenagihanModel::with([
            'invoice.pengirimans.pengirimanDetails.bahanBakuSupplier.supplier',
            'invoice.pengirimans.purchaseOrder.klien',
            'pengiriman.purchaseOrder.klien',
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'staff',
            'manager'
        ])
        ->whereHas('pengiriman'); // Only show approvals that have pengiriman

        // Filter by tab
        if ($this->activeTab === 'pending') {
            $query->where('status', 'pending')
                  ->whereHas('pengiriman', function($q) {
                      $q->whereIn('status', ['berhasil', 'menunggu_verifikasi']);
                  });
        } else {
            // approved tab - hanya yang completed
            $query->where('status', 'completed');
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('pengiriman', function ($subQ) {
                    $subQ->where('no_pengiriman', 'like', '%' . $this->search . '%');
                })->orWhereHas('invoice', function ($subQ) {
                    $subQ->where('invoice_number', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Filter by customer
        if ($this->customerFilter !== 'all') {
            $query->whereHas('invoice', function ($q) {
                $q->where('customer_name', $this->customerFilter);
            });
        }

        // Filter by supplier - ensure pengiriman exists
        if ($this->supplierFilter !== 'all') {
            $query->whereHas('pengiriman', function($q) {
                $q->whereHas('pengirimanDetails.bahanBakuSupplier.supplier', function ($subQ) {
                    $subQ->where('nama', $this->supplierFilter);
                });
            });
        }

        $approvals = $query->latest('updated_at')->paginate(10, ['*'], 'page_approval');

        // Get unique customers and suppliers for filters
        $allApprovals = ApprovalPenagihanModel::with([
            'invoice',
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier'
        ])
        ->where('status', $this->activeTab === 'pending' ? 'pending' : 'completed')
        ->get();

        $customers = $allApprovals->pluck('invoice.customer_name')->unique()->filter()->sort()->values();

        // Safely get suppliers - handle null pengiriman
        $suppliers = collect();
        foreach ($allApprovals as $approval) {
            if ($approval->pengiriman && $approval->pengiriman->pengirimanDetails) {
                $supplierNames = $approval->pengiriman->pengirimanDetails
                    ->pluck('bahanBakuSupplier.supplier.nama')
                    ->filter();
                $suppliers = $suppliers->merge($supplierNames);
            }
        }
        $suppliers = $suppliers->unique()->sort()->values();

        return view('livewire.accounting.approval-penagihan', [
            'pengirimansWithoutInvoice' => $pengirimansWithoutInvoice,
            'approvals' => $approvals,
            'customers' => $customers,
            'suppliers' => $suppliers,
        ]);
    }

    public function showCreateInvoice($pengirimanId)
    {
        $pengiriman = Pengiriman::with([
            'purchaseOrder.klien',
            'forecast',
            'pengirimanDetails.bahanBakuSupplier',
            'approvalPembayaran.histories' => function($query) {
                $query->where('approval_type', 'pembayaran')
                    ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($pengirimanId);

        $klien = $pengiriman->purchaseOrder->klien ?? null;
        $approvalPembayaran = $pengiriman->approvalPembayaran;
        $refraksiType = 'qty';
        $refraksiValue = 0;
        $notes = '';

        if ($approvalPembayaran) {
            $refraksiType = $approvalPembayaran->refraksi_type ?? 'qty';
            $refraksiValue = $approvalPembayaran->refraksi_value ?? 0;
            $latestHistory = $approvalPembayaran->histories->first();
            if ($latestHistory && $latestHistory->notes) {
                $notes = 'Catatan dari Pembayaran: ' . $latestHistory->notes;
            }
        }

        $this->invoiceForm = [
            'customer_name'    => $klien->nama ?? '',
            'customer_address' => $klien->alamat_lengkap ?? '',
            'customer_phone'   => $klien->no_hp ?? '',
            'customer_email'   => '',
            'refraksi_type'    => $refraksiType,
            'refraksi_value'   => $refraksiValue,
            'notes'            => $notes,
        ];

        // State baru
        $this->selectedShipment  = $pengiriman;
        $this->selectedShipments = collect([$pengiriman]);
        $this->isMergedInvoice   = false;

        $this->showCreateInvoiceModal = true;
    }


    public function showCreateMergedInvoice()
    {
        if (empty($this->selectedApprovalIds)) {
            session()->flash('error', 'Silakan pilih minimal 1 invoice.');
            return;
        }

        $approvals = ApprovalPenagihanModel::with([
            'invoice',
            'pengiriman.purchaseOrder.klien',
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'pengiriman.approvalPembayaran.histories' => function($query) {
                $query->where('approval_type', 'pembayaran')
                    ->orderBy('created_at', 'desc');
            }
        ])->whereIn('id', $this->selectedApprovalIds)->get();

        // Validasi: semua harus customer yang sama
        $customerNames = $approvals->map(fn($a) => $a->invoice?->customer_name)->filter()->unique();
        if ($customerNames->count() > 1) {
            session()->flash('error', 'Gagal menggabungkan invoice: Customer dari invoice terpilih harus sama.');
            return;
        }

        // Kumpulkan semua pengiriman unik dari invoice-invoice tersebut
        $invoiceIds = $approvals->pluck('invoice_id')->filter()->unique();
        $shipments = Pengiriman::with([
            'purchaseOrder.klien',
            'forecast',
            'pengirimanDetails.bahanBakuSupplier',
            'approvalPembayaran.histories' => function($query) {
                $query->where('approval_type', 'pembayaran')
                    ->orderBy('created_at', 'desc');
            }
        ])->whereIn('invoice_penagihan_id', $invoiceIds)
        ->orWhereIn('id', $approvals->pluck('pengiriman_id'))
        ->get()
        ->unique('id');

        if ($shipments->isEmpty()) {
            session()->flash('error', 'Tidak ada data pengiriman yang ditemukan untuk digabungkan.');
            return;
        }

        $firstShipment = $shipments->first();
        $klien = $firstShipment->purchaseOrder->klien ?? null;

        // Gabungkan catatan dari semua pengiriman
        $combinedNotes = [];
        foreach ($shipments as $s) {
            $ap = $s->approvalPembayaran;
            if ($ap) {
                $latestHistory = $ap->histories->first();
                if ($latestHistory && $latestHistory->notes) {
                    $combinedNotes[] = $s->no_pengiriman . ': ' . $latestHistory->notes;
                }
            }
        }
        $notes = !empty($combinedNotes)
            ? "Catatan dari Pembayaran:\n" . implode("\n", $combinedNotes)
            : '';

        // Ambil refraksi pertama yang non-zero
        $refraksiType  = 'qty';
        $refraksiValue = 0;
        foreach ($shipments as $s) {
            if ($s->approvalPembayaran && $s->approvalPembayaran->refraksi_value > 0) {
                $refraksiType  = $s->approvalPembayaran->refraksi_type ?? 'qty';
                $refraksiValue = $s->approvalPembayaran->refraksi_value ?? 0;
                break;
            }
        }

        $this->invoiceForm = [
            'customer_name'    => $klien->nama ?? $approvals->first()->invoice?->customer_name ?? '',
            'customer_address' => $klien->alamat_lengkap ?? $approvals->first()->invoice?->customer_address ?? '',
            'customer_phone'   => $klien->no_hp ?? $approvals->first()->invoice?->customer_phone ?? '',
            'customer_email'   => $approvals->first()->invoice?->customer_email ?? '',
            'refraksi_type'    => $refraksiType,
            'refraksi_value'   => $refraksiValue,
            'notes'            => $notes,
        ];

        // State baru
        $this->selectedShipment  = $firstShipment;   // representative untuk kalkulasi
        $this->selectedShipments = $shipments;        // semua untuk tampilan & simpan
        $this->isMergedInvoice   = true;

        $this->showCreateInvoiceModal = true;
    }

    public function mergeInvoices()
    {
        if (empty($this->selectedApprovalIds)) {
            session()->flash('error', 'Silakan pilih minimal 1 invoice.');
            return;
        }

        $approvals = ApprovalPenagihanModel::with([
            'invoice',
            'pengiriman.purchaseOrder.klien',
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'pengiriman.approvalPembayaran.histories' => function($query) {
                $query->where('approval_type', 'pembayaran')
                    ->orderBy('created_at', 'desc');
            }
        ])->whereIn('id', $this->selectedApprovalIds)->get();

        $customerNames = $approvals->map(fn($a) => $a->invoice?->customer_name)->filter()->unique();
        if ($customerNames->count() > 1) {
            session()->flash('error', 'Gagal menggabungkan invoice: Customer dari invoice terpilih harus sama.');
            return;
        }

        $invoiceIds = $approvals->pluck('invoice_id')->filter()->unique();
        $shipments = Pengiriman::with([
            'purchaseOrder.klien',
            'forecast',
            'pengirimanDetails.bahanBakuSupplier',
            'approvalPembayaran.histories' => function($query) {
                $query->where('approval_type', 'pembayaran')
                    ->orderBy('created_at', 'desc');
            }
        ])->whereIn('invoice_penagihan_id', $invoiceIds)
        ->orWhereIn('id', $approvals->pluck('pengiriman_id'))
        ->get()
        ->unique('id');

        if ($shipments->isEmpty()) {
            session()->flash('error', 'Tidak ada data pengiriman yang ditemukan untuk digabungkan.');
            return;
        }

        $firstShipment = $shipments->first();
        $klien = $firstShipment->purchaseOrder->klien ?? null;

        $combinedNotes = [];
        foreach ($shipments as $s) {
            $ap = $s->approvalPembayaran;
            if ($ap) {
                $latestHistory = $ap->histories->first();
                if ($latestHistory && $latestHistory->notes) {
                    $combinedNotes[] = $s->no_pengiriman . ': ' . $latestHistory->notes;
                }
            }
        }
        $notes = !empty($combinedNotes)
            ? "Catatan dari Pembayaran:\n" . implode("\n", $combinedNotes)
            : '';

        $refraksiType  = 'qty';
        $refraksiValue = 0;
        foreach ($shipments as $s) {
            if ($s->approvalPembayaran && $s->approvalPembayaran->refraksi_value > 0) {
                $refraksiType  = $s->approvalPembayaran->refraksi_type ?? 'qty';
                $refraksiValue = $s->approvalPembayaran->refraksi_value ?? 0;
                break;
            }
        }

        $this->invoiceForm = [
            'customer_name'    => $klien->nama ?? $approvals->first()->invoice?->customer_name ?? '',
            'customer_address' => $klien->alamat_lengkap ?? $approvals->first()->invoice?->customer_address ?? '',
            'customer_phone'   => $klien->no_hp ?? $approvals->first()->invoice?->customer_phone ?? '',
            'customer_email'   => $approvals->first()->invoice?->customer_email ?? '',
            'refraksi_type'    => $refraksiType,
            'refraksi_value'   => $refraksiValue,
            'notes'            => $notes,
        ];

        $this->selectedShipment  = $firstShipment;
        $this->selectedShipments = $shipments;
        $this->isMergedInvoice   = true;

        $this->createInvoice();
    }

    public function createInvoice()
    {
        $this->validate([
            'invoiceForm.customer_name' => 'required|string|max:255',
            'invoiceForm.customer_address' => 'required|string',
            'invoiceForm.customer_phone' => 'nullable|string|max:20',
            'invoiceForm.customer_email' => 'nullable|email|max:255',
            'invoiceForm.refraksi_type' => 'required|in:qty,rupiah,lainnya',
            'invoiceForm.refraksi_value' => 'required|numeric|min:0',
            'invoiceForm.notes' => 'nullable|string',
        ]);

        // Gunakan $selectedShipments (selalu Collection setelah refactor)
        $shipments = $this->selectedShipments;

        if (!$shipments || $shipments->isEmpty()) {
            session()->flash('error', 'Data pengiriman tidak ditemukan');
            return;
        }

        // Primary shipment untuk kalkulasi (selalu ada karena di-set di showCreate*)
        $primaryShipment = $this->selectedShipment;

        DB::beginTransaction();
        try {
            $companySetting = CompanySetting::getSettings();
            $invoiceNumber  = InvoicePenagihan::generateInvoiceNumber();

            // Hitung total harga jual dari SEMUA pengiriman yang digabung
            $totalSellingPrice = 0;
            $items = [];

            foreach ($shipments as $pengiriman) {
                $pengiriman->load(
                    'pengirimanDetails.purchaseOrderBahanBaku',
                    'pengirimanDetails.orderDetail'
                );

                $shipmentTotal = 0;
                $itemDetails   = [];

                foreach ($pengiriman->pengirimanDetails as $detail) {
                    $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                    $hargaJual   = $orderDetail ? floatval($orderDetail->harga_jual) : 0;
                    $qtyKirim    = floatval($detail->qty_kirim);
                    $itemTotal   = $qtyKirim * $hargaJual;
                    $shipmentTotal += $itemTotal;

                    $bahanBakuName = $detail->bahanBakuSupplier->nama
                        ?? ($orderDetail->bahanBakuKlien->nama ?? 'Bahan Baku');

                    $itemDetails[] = [
                        'name'      => $bahanBakuName,
                        'qty'       => $qtyKirim,
                        'harga_jual'=> $hargaJual,
                        'total'     => $itemTotal,
                    ];
                }

                $totalSellingPrice += $shipmentTotal;

                $items[] = [
                    'item_name'   => 'Pengiriman ' . $pengiriman->no_pengiriman,
                    'description' => 'No. Pengiriman: ' . $pengiriman->no_pengiriman
                        . '\nTanggal Kirim: ' . $pengiriman->tanggal_kirim->format('d M Y')
                        . '\nTotal Qty: ' . number_format($pengiriman->total_qty_kirim, 2, ',', '.') . ' kg',
                    'quantity'    => 1,
                    'unit'        => 'paket',
                    'unit_price'  => $shipmentTotal,
                    'amount'      => $shipmentTotal,
                    'details'     => $itemDetails,
                ];
            }

            // Hitung refraksi dari total gabungan semua pengiriman
            $totalQty        = $shipments->sum(fn($s) => floatval($s->total_qty_kirim));
            $qtyBeforeRefraksi = $totalQty;
            $qtyAfterRefraksi  = $totalQty;
            $refraksiAmount    = 0;
            $subtotal          = $totalSellingPrice;

            if ($this->invoiceForm['refraksi_type'] === 'qty') {
                $refraksiQty      = $qtyBeforeRefraksi * ($this->invoiceForm['refraksi_value'] / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;
                $hargaPerKg       = $qtyBeforeRefraksi > 0 ? $subtotal / $qtyBeforeRefraksi : 0;
                $refraksiAmount   = $refraksiQty * $hargaPerKg;
                $subtotal         = $subtotal - $refraksiAmount;
            } elseif ($this->invoiceForm['refraksi_type'] === 'rupiah') {
                $refraksiAmount = $this->invoiceForm['refraksi_value'] * $qtyBeforeRefraksi;
                $subtotal       = $subtotal - $refraksiAmount;
            } elseif ($this->invoiceForm['refraksi_type'] === 'lainnya') {
                $refraksiAmount = $this->invoiceForm['refraksi_value'];
                $subtotal       = $subtotal - $refraksiAmount;
            }

            // Kumpulkan additional expenses dari seluruh approval pembayaran
            $expensesTotal = 0;
            $expenseRows   = collect();

            foreach ($shipments as $s) {
                $s->loadMissing('approvalPembayaran.expenses');
                $ap = $s->approvalPembayaran;
                if ($ap) {
                    $expensesTotal += floatval($ap->additional_expenses_total ?? 0);
                    foreach ($ap->expenses as $e) {
                        $expenseRows->push($e);
                    }
                }
            }

            $subtotal += $expensesTotal;

            $taxAmount   = $subtotal * ($companySetting->tax_percentage / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Buat invoice
            $invoice = InvoicePenagihan::create([
                'pengiriman_id'          => $primaryShipment->id,
                'invoice_number'         => $invoiceNumber,
                'invoice_date'           => now(),
                'due_date'               => now()->addDays($companySetting->invoice_due_days),
                'customer_name'          => $this->invoiceForm['customer_name'],
                'customer_address'       => $this->invoiceForm['customer_address'],
                'customer_phone'         => $this->invoiceForm['customer_phone'],
                'customer_email'         => $this->invoiceForm['customer_email'],
                'items'                  => $items,
                'refraksi_type'          => $this->invoiceForm['refraksi_type'],
                'refraksi_value'         => $this->invoiceForm['refraksi_value'],
                'refraksi_amount'        => $refraksiAmount,
                'qty_before_refraksi'    => $qtyBeforeRefraksi,
                'qty_after_refraksi'     => $qtyAfterRefraksi,
                'amount_before_refraksi' => $totalSellingPrice,
                'amount_after_refraksi'  => $subtotal - $expensesTotal,
                'subtotal'               => $subtotal,
                'additional_expenses_total' => $expensesTotal,
                'tax_percentage'         => $companySetting->tax_percentage,
                'tax_amount'             => $taxAmount,
                'discount_amount'        => 0,
                'total_amount'           => $totalAmount,
                'notes'                  => $this->invoiceForm['notes'],
                'payment_status'         => 'unpaid',
                'created_by'             => Auth::id(),
            ]);

            // Copy expense rows ke invoice
            foreach ($expenseRows as $e) {
                $invoice->expenses()->create([
                    'type'   => $e->type,
                    'amount' => $e->amount,
                ]);
            }

            // Link semua pengiriman ke invoice baru
            foreach ($shipments as $s) {
                $s->update(['invoice_penagihan_id' => $invoice->id]);
            }

            // Kalau ini merge: bersihkan approval & invoice lama
            if ($this->isMergedInvoice && !empty($this->selectedApprovalIds)) {
                $oldApprovals  = ApprovalPenagihanModel::whereIn('id', $this->selectedApprovalIds)->get();
                $oldInvoiceIds = $oldApprovals->pluck('invoice_id')->filter()->unique();

                // Putus referensi ke invoice lama dulu
                Pengiriman::whereIn('invoice_penagihan_id', $oldInvoiceIds)
                    ->where('invoice_penagihan_id', '!=', $invoice->id)
                    ->update(['invoice_penagihan_id' => null]);

                // Tandai approval & invoice lama sebagai digabung (bukan dihapus)
                ApprovalPenagihanModel::whereIn('id', $this->selectedApprovalIds)
                    ->update(['status' => 'digabung']);
                InvoicePenagihan::whereIn('id', $oldInvoiceIds)
                    ->update(['status' => 'digabung']);
            }

            // Buat approval penagihan baru
            $approvalPenagihan = ApprovalPenagihanModel::create([
                'invoice_id'    => $invoice->id,
                'pengiriman_id' => $primaryShipment->id,
                'status'        => 'pending',
            ]);

            if ($approvalPenagihan) {
                ApprovalPenagihanNotificationService::notifyPendingApproval($approvalPenagihan);
            }

            DB::commit();

            $isMerged = $this->isMergedInvoice;
            $count    = $shipments->count();

            session()->flash('message', $isMerged
                ? "Invoice berhasil digabungkan ({$count} pengiriman digabung ke 1 invoice)"
                : 'Invoice berhasil dibuat');

            $this->closeModal();
            $this->selectedApprovalIds = [];

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal membuat invoice: ' . $e->getMessage());
        }
    }

    public function showDetail($approvalId)
    {
        $approval = ApprovalPenagihanModel::with([
            'invoice',
            'pengiriman.purchaseOrder.klien',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'staff',
            'manager',
            'histories.user'
        ])->findOrFail($approvalId);

        $this->selectedData = $approval;
        $this->showDetailModal = true;
        $this->notes = '';

        // Load refraksi values to form for editing
        if ($approval->invoice) {
            $this->invoiceForm['refraksi_type'] = $approval->invoice->refraksi_type ?? 'qty';
            $this->invoiceForm['refraksi_value'] = $approval->invoice->refraksi_value ?? 0;
        }

        // Populate customerForm with existing customer data
        $this->customerForm = [
            'customer_name' => $approval->invoice->customer_name ?? '',
            'customer_address' => $approval->invoice->customer_address ?? '',
            'customer_phone' => $approval->invoice->customer_phone ?? '',
            'customer_email' => $approval->invoice->customer_email ?? '',
        ];

        // Populate dateForm with existing date data
        $this->dateForm = [
            'invoice_date' => $approval->invoice->invoice_date ? $approval->invoice->invoice_date->format('Y-m-d') : '',
            'due_date' => $approval->invoice->due_date ? $approval->invoice->due_date->format('Y-m-d') : '',
        ];

        // Populate bankForm with existing bank data
        $this->bankForm = [
            'bank_name' => $approval->invoice->bank_name ?? '',
            'bank_account_number' => $approval->invoice->bank_account_number ?? '',
            'bank_account_name' => $approval->invoice->bank_account_name ?? '',
        ];

        // Populate invoice notes
        $this->invoiceNotesForm = $approval->invoice->notes ?? '';

        // Populate invoice number
        $this->invoiceNumberForm = $approval->invoice->invoice_number ?? '';

        // Populate total harga jual form
        $this->totalHargaJualForm = $approval->invoice->subtotal ?? 0;

        // Load approval history
        $this->approvalHistory = ApprovalHistory::where('approval_type', 'penagihan')
            ->where('approval_id', $approvalId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function updateCustomerInfo()
    {
        $this->validate([
            'customerForm.customer_name' => 'required|string|max:255',
            'customerForm.customer_address' => 'required|string',
            'customerForm.customer_phone' => 'nullable|string|max:20',
            'customerForm.customer_email' => 'nullable|email|max:255',
        ]);

        if (!$this->selectedData || !$this->selectedData->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $invoice = $this->selectedData->invoice;
            $user = Auth::user();

            // Collect changes
            $changes = [
                'before' => [
                    'customer_name' => $invoice->customer_name,
                    'customer_address' => $invoice->customer_address,
                    'customer_phone' => $invoice->customer_phone,
                    'customer_email' => $invoice->customer_email,
                ],
                'after' => $this->customerForm,
            ];

            // Update invoice
            $invoice->update($this->customerForm);

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->selectedData->id,
                'pengiriman_id' => $this->selectedData->pengiriman_id,
                'invoice_id' => $invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update informasi customer',
            ]);

            DB::commit();
            session()->flash('message', 'Informasi customer berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update informasi customer: ' . $e->getMessage());
        }
    }

    public function updateInvoiceDates()
    {
        $this->validate([
            'dateForm.invoice_date' => 'required|date',
            'dateForm.due_date' => 'required|date',
        ]);

        if (!$this->selectedData || !$this->selectedData->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $invoice = $this->selectedData->invoice;
            $user = Auth::user();

            // Collect changes
            $changes = [
                'before' => [
                    'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : null,
                    'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                ],
                'after' => $this->dateForm,
            ];

            // Update invoice
            $invoice->invoice_date = $this->dateForm['invoice_date'];
            $invoice->due_date = $this->dateForm['due_date'];
            $invoice->save();

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->selectedData->id,
                'pengiriman_id' => $this->selectedData->pengiriman_id,
                'invoice_id' => $invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update tanggal invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Tanggal invoice berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update tanggal invoice: ' . $e->getMessage());
        }
    }

    public function updateBankInfo()
    {
        $this->validate([
            'bankForm.bank_name' => 'required|string|max:255',
            'bankForm.bank_account_number' => 'required|string|max:50',
            'bankForm.bank_account_name' => 'required|string|max:255',
        ]);

        if (!$this->selectedData || !$this->selectedData->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $invoice = $this->selectedData->invoice;
            $user = Auth::user();

            // Collect changes
            $changes = [
                'before' => [
                    'bank_name' => $invoice->bank_name,
                    'bank_account_number' => $invoice->bank_account_number,
                    'bank_account_name' => $invoice->bank_account_name,
                ],
                'after' => $this->bankForm,
            ];

            // Update invoice
            $invoice->update($this->bankForm);

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->selectedData->id,
                'pengiriman_id' => $this->selectedData->pengiriman_id,
                'invoice_id' => $invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update informasi bank',
            ]);

            DB::commit();
            session()->flash('message', 'Informasi bank berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update informasi bank: ' . $e->getMessage());
        }
    }

    public function updateInvoiceNotes()
    {
        if (!$this->selectedData || !$this->selectedData->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $invoice = $this->selectedData->invoice;
            $user = Auth::user();

            // Collect changes
            $changes = [
                'before' => [
                    'notes' => $invoice->notes,
                ],
                'after' => [
                    'notes' => $this->invoiceNotesForm,
                ],
            ];

            // Update invoice
            $invoice->notes = $this->invoiceNotesForm;
            $invoice->save();

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->selectedData->id,
                'pengiriman_id' => $this->selectedData->pengiriman_id,
                'invoice_id' => $invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update catatan invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Catatan invoice berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update catatan invoice: ' . $e->getMessage());
        }
    }

    public function updateInvoiceNumber()
    {
        $this->validate([
            'invoiceNumberForm' => 'required|string|max:191',
        ], [
            'invoiceNumberForm.required' => 'Nomor invoice harus diisi',
            'invoiceNumberForm.max' => 'Nomor invoice maksimal 191 karakter',
        ]);

        if (!$this->selectedData || !$this->selectedData->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        // Check if invoice number already exists (excluding current invoice)
        $exists = InvoicePenagihan::where('invoice_number', $this->invoiceNumberForm)
            ->where('id', '!=', $this->selectedData->invoice->id)
            ->exists();

        if ($exists) {
            session()->flash('error', 'Nomor invoice "' . $this->invoiceNumberForm . '" sudah digunakan. Silakan gunakan nomor invoice yang berbeda.');
            return;
        }

        DB::beginTransaction();
        try {
            $invoice = $this->selectedData->invoice;
            $user = Auth::user();

            // Collect changes
            $changes = [
                'before' => [
                    'invoice_number' => $invoice->invoice_number,
                ],
                'after' => [
                    'invoice_number' => $this->invoiceNumberForm,
                ],
            ];

            // Update invoice
            $invoice->invoice_number = $this->invoiceNumberForm;
            $invoice->save();

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->selectedData->id,
                'pengiriman_id' => $this->selectedData->pengiriman_id,
                'invoice_id' => $invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update nomor invoice dari "' . $changes['before']['invoice_number'] . '" menjadi "' . $this->invoiceNumberForm . '"',
            ]);

            DB::commit();
            session()->flash('message', 'Nomor invoice berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update nomor invoice: ' . $e->getMessage());
        }
    }

    public function updateDiscount()
    {
        if (!$this->selectedData || !$this->selectedData->invoice) {
            return;
        }

        DB::beginTransaction();
        try {
            $invoice = $this->selectedData->invoice;
            $pengiriman = $invoice->pengiriman;
            $user = Auth::user();

            // Store old values for history
            $oldValues = [
                'refraksi_type' => $invoice->refraksi_type,
                'refraksi_value' => $invoice->refraksi_value,
                'refraksi_amount' => $invoice->refraksi_amount,
            ];

            // Update refraksi values
            $invoice->refraksi_type = $this->invoiceForm['refraksi_type'];
            $invoice->refraksi_value = floatval($this->invoiceForm['refraksi_value']);

            // Recalculate base using harga JUAL (same as createInvoice)
            $pengiriman->load('pengirimanDetails.purchaseOrderBahanBaku', 'pengirimanDetails.orderDetail');
            $totalSellingPrice = 0;
            foreach ($pengiriman->pengirimanDetails as $detail) {
                $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                $hargaJual = $orderDetail ? floatval($orderDetail->harga_jual) : 0;
                $totalSellingPrice += floatval($detail->qty_kirim) * $hargaJual;
            }

            $qtyBeforeRefraksi = $pengiriman->total_qty_kirim;
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $refraksiAmount = 0;
            $subtotal = $totalSellingPrice; // ← harga JUAL, bukan harga beli

            if ($invoice->refraksi_type === 'qty') {
                // Refraksi Qty
                $refraksiQty = $qtyBeforeRefraksi * ($invoice->refraksi_value / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;

                $hargaPerKg = $qtyBeforeRefraksi > 0 ? $subtotal / $qtyBeforeRefraksi : 0;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
                $subtotal = $subtotal - $refraksiAmount;
            } elseif ($invoice->refraksi_type === 'rupiah') {
                // Refraksi Rupiah
                $refraksiAmount = $invoice->refraksi_value * $qtyBeforeRefraksi;
                $subtotal = $subtotal - $refraksiAmount;
            } elseif ($invoice->refraksi_type === 'lainnya') {
                // Refraksi Lainnya: input manual langsung nominal total
                $refraksiAmount = $invoice->refraksi_value;
                $subtotal = $subtotal - $refraksiAmount;
            }

            // Update invoice
            $invoice->refraksi_amount = $refraksiAmount;
            $invoice->qty_before_refraksi = $qtyBeforeRefraksi;
            $invoice->qty_after_refraksi = $qtyAfterRefraksi;
            $invoice->subtotal = $subtotal;

            $invoice->recalculateTotal();

            // Save history
            $changes = [
                'before' => $oldValues,
                'after' => [
                    'refraksi_type' => $invoice->refraksi_type,
                    'refraksi_value' => $invoice->refraksi_value,
                    'refraksi_amount' => $invoice->refraksi_amount,
                ],
            ];

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->selectedData->id,
                'pengiriman_id' => $this->selectedData->pengiriman_id,
                'invoice_id' => $invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update refraksi invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Refraksi berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update refraksi: ' . $e->getMessage());
        }
    }

    public function approve()
    {
        $user = Auth::user();
        $approval = $this->selectedData;

        if (!$approval) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        // Validate customer information from customerForm
        $this->validate([
            'customerForm.customer_name' => 'required|string|max:255',
            'customerForm.customer_address' => 'required|string',
            'customerForm.customer_phone' => 'nullable|string|max:20',
            'customerForm.customer_email' => 'nullable|email|max:255',
        ], [
            'customerForm.customer_name.required' => 'Nama customer harus diisi',
            'customerForm.customer_address.required' => 'Alamat customer harus diisi',
            'customerForm.customer_email.email' => 'Format email tidak valid',
        ]);

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

            // Save customer information changes BEFORE approving
            if ($approval->invoice) {
                $approval->invoice->update($this->customerForm);
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

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $approval->id,
                'pengiriman_id' => $approval->pengiriman_id,
                'invoice_id' => $approval->invoice_id,
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
        } elseif ($user->role === 'staff_accounting') {
            return 'staff_accounting';
        }

        return null;
    }
    public function closeModal()
    {
        $this->showDetailModal         = false;
        $this->showCreateInvoiceModal  = false;
        $this->selectedData            = null;
        $this->selectedShipment        = null;   // <-- tambah
        $this->selectedShipments       = null;   // <-- tambah
        $this->isMergedInvoice         = false;  // <-- tambah
        $this->notes                   = '';
        $this->invoiceForm = [
            'customer_name'    => '',
            'customer_address' => '',
            'customer_phone'   => '',
            'customer_email'   => '',
            'refraksi_type'    => 'qty',
            'refraksi_value'   => 0,
            'notes'            => '',
        ];
        $this->customerForm = [
            'customer_name'    => '',
            'customer_address' => '',
            'customer_phone'   => '',
            'customer_email'   => '',
        ];
        $this->dateForm = [
            'invoice_date' => '',
            'due_date'     => '',
        ];
        $this->bankForm = [
            'bank_name'            => '',
            'bank_account_number'  => '',
            'bank_account_name'    => '',
        ];
        $this->invoiceNotesForm   = '';
        $this->invoiceNumberForm  = '';
        $this->totalHargaJualForm = 0;
        $this->approvalHistory    = [];
    }

    public function updateTotalHargaJual()
    {
        $this->validate([
            'totalHargaJualForm' => 'required|numeric|min:0',
        ], [
            'totalHargaJualForm.required' => 'Total harga jual harus diisi',
            'totalHargaJualForm.numeric' => 'Total harga jual harus berupa angka',
            'totalHargaJualForm.min' => 'Total harga jual tidak boleh negatif',
        ]);

        if (!$this->selectedData || !$this->selectedData->invoice) {
            session()->flash('error', 'Data invoice tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $invoice = $this->selectedData->invoice;
            $user = Auth::user();

            // Store old values for history
            $oldSubtotal = $invoice->subtotal;
            $oldTotal = $invoice->total_amount;

            // Update subtotal
            $invoice->subtotal = floatval($this->totalHargaJualForm);

            // Recalculate total with tax
            $invoice->tax_amount = $invoice->subtotal * ($invoice->tax_percentage / 100);
            $invoice->total_amount = $invoice->subtotal + $invoice->tax_amount - $invoice->discount_amount;
            $invoice->save();

            // Collect changes
            $changes = [
                'before' => [
                    'subtotal' => number_format($oldSubtotal, 2, ',', '.'),
                    'total_amount' => number_format($oldTotal, 2, ',', '.'),
                ],
                'after' => [
                    'subtotal' => number_format($invoice->subtotal, 2, ',', '.'),
                    'total_amount' => number_format($invoice->total_amount, 2, ',', '.'),
                ],
            ];

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->selectedData->id,
                'pengiriman_id' => $this->selectedData->pengiriman_id,
                'invoice_id' => $invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update total harga jual dari Rp ' . number_format($oldSubtotal, 0, ',', '.') .
                          ' menjadi Rp ' . number_format($invoice->subtotal, 0, ',', '.'),
            ]);

            DB::commit();
            session()->flash('message', 'Total harga jual berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update total harga jual: ' . $e->getMessage());
        }
    }
}
