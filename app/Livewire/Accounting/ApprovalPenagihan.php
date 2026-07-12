<?php

namespace App\Livewire\Accounting;

use App\Models\Pengiriman;
use App\Models\InvoicePenagihan;
use App\Models\ApprovalPenagihan as ApprovalPenagihanModel;
use App\Models\CompanySetting;
use App\Services\Notifications\ApprovalPenagihanNotificationService;
use App\Livewire\Accounting\Traits\WithInvoiceShared;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalPenagihan extends Component
{
    use WithPagination, WithInvoiceShared;

    public $search = '';
    public $customerFilter = 'all';
    public $supplierFilter = 'all';
    public $activeTab = 'pending';
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

    // Forms
    public $invoiceForm = ['customer_name' => '', 'customer_address' => '', 'customer_phone' => '', 'customer_email' => '', 'refraksi_type' => 'qty', 'refraksi_value' => 0, 'notes' => ''];
    public $customerForm = ['customer_name' => '', 'customer_address' => '', 'customer_phone' => '', 'customer_email' => ''];
    public $dateForm = ['invoice_date' => '', 'due_date' => ''];
    public $bankForm = ['bank_name' => '', 'bank_account_number' => '', 'bank_account_name' => ''];
    public $invoiceNotesForm = '';
    public $invoiceNumberForm = '';
    public $totalHargaJualForm = 0;
    public $shipmentRefraksi = [];
    public $shipmentExpenses = [];

    protected $queryString = ['search' => ['except' => ''], 'customerFilter' => ['except' => 'all'], 'supplierFilter' => ['except' => 'all'], 'activeTab' => ['except' => 'pending']];

    public function mount($approvalId = null, $editMode = false)
    {
        $this->editMode = $editMode;
        $this->approvalId = $approvalId;
        $this->canManage = in_array(Auth::user()->role, ['manager_accounting', 'direktur', 'superadmin','staff_accounting']);
        if ($approvalId) $this->showDetail($approvalId);
    }

    public function updatingSearch() { $this->resetPage(); $this->resetPage('page_without_invoice'); $this->selectedApprovalIds = []; }
    public function updatingCustomerFilter() { $this->resetPage(); $this->resetPage('page_without_invoice'); $this->selectedApprovalIds = []; }
    public function updatingSupplierFilter() { $this->resetPage(); $this->resetPage('page_without_invoice'); $this->selectedApprovalIds = []; }
    public function setActiveTab($tab) { $this->activeTab = $tab; $this->resetPage(); $this->resetPage('page_without_invoice'); $this->selectedApprovalIds = []; }
    public function gotoPage($page, $pageName = 'page_approval') { $this->setPage($page, $pageName); }

    public function getIsMergeValidProperty()
    {
        if (empty($this->selectedApprovalIds)) return false;
        $approvals = ApprovalPenagihanModel::with('invoice')->whereIn('id', $this->selectedApprovalIds)->get();
        if ($approvals->isEmpty()) return false;
        return $approvals->map(fn($a) => $a->invoice?->customer_name)->filter()->unique()->count() === 1;
    }

    public function render()
    {
        $pengirimansWithoutInvoice = null;

        if ($this->activeTab === 'pending') {
            $pengirimansWithoutInvoice = Pengiriman::whereIn('status', ['berhasil', 'menunggu_verifikasi'])
                ->doesntHave('invoicePenagihan')
                ->whereHas('approvalPembayaran', fn($q) => $q->where('status', 'completed'))
                ->with(['purchaseOrder.klien', 'forecast', 'purchasing'])
                ->when($this->search, fn($q) => $q->where('no_pengiriman', 'like', '%' . $this->search . '%'))
                ->latest()->paginate(10, ['*'], 'page_without_invoice');
        }

        $query = ApprovalPenagihanModel::with([
            'invoice.pengirimans.pengirimanDetails.bahanBakuSupplier.supplier',
            'invoice.pengirimans.purchaseOrder.klien',
            'pengiriman.purchaseOrder.klien',
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'pengiriman.forecast', 'pengiriman.purchasing', 'staff', 'manager'
        ])->has('pengiriman');

        $query->when($this->activeTab === 'pending', fn($q) => $q->where('status', 'pending')->whereHas('pengiriman', fn($sq) => $sq->whereIn('status', ['berhasil', 'menunggu_verifikasi'])))
              ->when($this->activeTab !== 'pending', fn($q) => $q->where('status', 'completed'))
              ->when($this->search, function ($q) {
                  $term = '%' . $this->search . '%';
                  $q->where(fn($wq) => $wq->whereHas('pengiriman', fn($sq) => $sq->where('no_pengiriman', 'like', $term)->orWhereHas('purchaseOrder', fn($po) => $po->where('po_number', 'like', $term)))
                          ->orWhereHas('invoice', fn($sq) => $sq->where('invoice_number', 'like', $term)->orWhere('customer_name', 'like', $term))
                          ->orWhereHas('invoice.pengirimans.purchaseOrder', fn($po) => $po->where('po_number', 'like', $term)));
              })
              ->when($this->customerFilter !== 'all', fn($q) => $q->whereHas('invoice', fn($sq) => $sq->where('customer_name', $this->customerFilter)))
              ->when($this->supplierFilter !== 'all', fn($q) => $q->whereHas('pengiriman.pengirimanDetails.bahanBakuSupplier.supplier', fn($sq) => $sq->where('nama', $this->supplierFilter)));

        $allApprovals = ApprovalPenagihanModel::with(['invoice', 'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier'])
            ->where('status', $this->activeTab === 'pending' ? 'pending' : 'completed')->get();
        
        $suppliers = $allApprovals->flatMap(fn($a) => $a->pengiriman?->pengirimanDetails?->pluck('bahanBakuSupplier.supplier.nama') ?? [])->filter()->unique()->sort()->values();

        return view('livewire.accounting.approval-penagihan', [
            'pengirimansWithoutInvoice' => $pengirimansWithoutInvoice,
            'approvals' => $query->latest('updated_at')->paginate(10, ['*'], 'page_approval'),
            'customers' => $allApprovals->pluck('invoice.customer_name')->unique()->filter()->sort()->values(),
            'suppliers' => $suppliers,
        ]);
    }

    public function showCreateInvoice($pengirimanId)
    {
        $pengiriman = Pengiriman::with(['purchaseOrder.klien', 'forecast', 'pengirimanDetails.bahanBakuSupplier', 'approvalPembayaran.histories' => fn($q) => $q->where('approval_type', 'pembayaran')->orderByDesc('created_at')])->findOrFail($pengirimanId);
        $klien = $pengiriman->purchaseOrder->klien ?? null;
        $ap = $pengiriman->approvalPembayaran;

        $this->invoiceForm = [
            'customer_name'    => $klien->nama ?? '',
            'customer_address' => $klien->alamat_lengkap ?? '',
            'customer_phone'   => $klien->no_hp ?? '',
            'customer_email'   => '',
            'refraksi_type'    => $ap->refraksi_type ?? 'qty',
            'refraksi_value'   => $ap->refraksi_value ?? 0,
            'notes'            => ($ap && $ap->histories->first()?->notes) ? 'Catatan dari Pembayaran: ' . $ap->histories->first()->notes : '',
        ];

        $this->selectedShipment  = $pengiriman;
        $this->selectedShipments = collect([$pengiriman]);
        $this->isMergedInvoice   = false;
        $this->showCreateInvoiceModal = true;
    }

    public function showCreateMergedInvoice() { $this->prepareMergedInvoiceData(false); }
    public function mergeInvoices() { $this->prepareMergedInvoiceData(true); }

    private function prepareMergedInvoiceData($autoCreate = false)
    {
        if (empty($this->selectedApprovalIds)) { session()->flash('error', 'Silakan pilih minimal 1 invoice.'); return; }
        
        $approvals = ApprovalPenagihanModel::with(['invoice', 'pengiriman.purchaseOrder.klien', 'pengiriman.pengirimanDetails.bahanBakuSupplier', 'pengiriman.approvalPembayaran.histories' => fn($q) => $q->where('approval_type', 'pembayaran')->orderByDesc('created_at')])->whereIn('id', $this->selectedApprovalIds)->get();
        if ($approvals->map(fn($a) => $a->invoice?->customer_name)->filter()->unique()->count() > 1) { session()->flash('error', 'Gagal menggabungkan invoice: Customer harus sama.'); return; }

        $shipments = Pengiriman::with(['purchaseOrder.klien', 'forecast', 'pengirimanDetails.bahanBakuSupplier', 'approvalPembayaran.histories' => fn($q) => $q->where('approval_type', 'pembayaran')->orderByDesc('created_at')])
            ->whereIn('invoice_penagihan_id', $approvals->pluck('invoice_id')->filter()->unique())
            ->orWhereIn('id', $approvals->pluck('pengiriman_id'))->get()->unique('id');

        if ($shipments->isEmpty()) { session()->flash('error', 'Tidak ada data pengiriman.'); return; }

        $combinedNotes = $shipments->map(fn($s) => $s->approvalPembayaran?->histories->first()?->notes ? $s->no_pengiriman . ': ' . $s->approvalPembayaran->histories->first()->notes : null)->filter()->implode("\n");
        $firstRefraksi = $shipments->first(fn($s) => $s->approvalPembayaran && $s->approvalPembayaran->refraksi_value > 0);
        $klien = $shipments->first()->purchaseOrder->klien ?? null;

        $this->invoiceForm = [
            'customer_name'    => $klien->nama ?? $approvals->first()->invoice?->customer_name ?? '',
            'customer_address' => $klien->alamat_lengkap ?? $approvals->first()->invoice?->customer_address ?? '',
            'customer_phone'   => $klien->no_hp ?? $approvals->first()->invoice?->customer_phone ?? '',
            'customer_email'   => $approvals->first()->invoice?->customer_email ?? '',
            'refraksi_type'    => $firstRefraksi->approvalPembayaran->refraksi_type ?? 'qty',
            'refraksi_value'   => $firstRefraksi->approvalPembayaran->refraksi_value ?? 0,
            'notes'            => $combinedNotes ? "Catatan dari Pembayaran:\n" . $combinedNotes : '',
        ];

        $this->selectedShipment  = $shipments->first();
        $this->selectedShipments = $shipments;
        $this->isMergedInvoice   = true;
        
        if ($autoCreate) $this->createInvoice();
        else $this->showCreateInvoiceModal = true;
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
        ]);

        if (!$this->selectedShipments || $this->selectedShipments->isEmpty()) {
            session()->flash('error', 'Data pengiriman tidak ditemukan'); return;
        }

        DB::beginTransaction();
        try {
            $companySetting = CompanySetting::getSettings();
            $invoiceNumber  = InvoicePenagihan::generateInvoiceNumber();
            $expenseRows = collect();
            
            $calcData = $this->prepareInvoiceItems($this->selectedShipments, $expenseRows);
            $totals = $this->calculateFinalTotals($calcData['items'], $calcData['totalSellingPrice'], $expenseRows, $companySetting);
            
            $invoice = InvoicePenagihan::create(array_merge([
                'pengiriman_id'  => $this->selectedShipment->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date'   => now(),
                'due_date'       => now()->addDays($companySetting->invoice_due_days),
                'payment_status' => 'unpaid',
                'created_by'     => Auth::id(),
            ], $this->invoiceForm, $totals, ['items' => $calcData['items']]));

            foreach ($expenseRows as $e) $invoice->expenses()->create(['type' => $e->type, 'amount' => $e->amount]);
            foreach ($this->selectedShipments as $s) $s->update(['invoice_penagihan_id' => $invoice->id]);

            $this->processMergeCleanup($invoice->id);

            $approvalPenagihan = ApprovalPenagihanModel::create(['invoice_id' => $invoice->id, 'pengiriman_id' => $this->selectedShipment->id, 'status' => 'pending']);
            if ($approvalPenagihan) ApprovalPenagihanNotificationService::notifyPendingApproval($approvalPenagihan);

            DB::commit();
            session()->flash('message', $this->isMergedInvoice ? "Invoice berhasil digabungkan ({$this->selectedShipments->count()} pengiriman digabung ke 1 invoice)" : 'Invoice berhasil dibuat');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Create Invoice Error: " . $e->getMessage());
            session()->flash('error', 'Gagal membuat invoice: ' . $e->getMessage());
        }
    }

    private function prepareInvoiceItems($shipments, &$expenseRows)
    {
        $totalSellingPrice = 0; $items = [];
        foreach ($shipments as $pengiriman) {
            $pengiriman->load('pengirimanDetails.purchaseOrderBahanBaku', 'pengirimanDetails.orderDetail', 'approvalPembayaran.expenses');
            $shipmentTotal = 0; $itemDetails = [];

            foreach ($pengiriman->pengirimanDetails as $detail) {
                $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                $hargaJual   = $orderDetail ? floatval($orderDetail->harga_jual) : 0;
                $itemTotal   = floatval($detail->qty_kirim) * $hargaJual;
                $shipmentTotal += $itemTotal;
                $itemDetails[] = ['name' => $detail->bahanBakuSupplier->nama ?? ($orderDetail->bahanBakuKlien->nama ?? 'Bahan Baku'), 'qty' => floatval($detail->qty_kirim), 'harga_jual'=> $hargaJual, 'total' => $itemTotal];
            }
            $totalSellingPrice += $shipmentTotal;
            
            $ap = $pengiriman->approvalPembayaran;
            $itemRefraksiType  = ($ap && $ap->refraksi_value > 0) ? $ap->refraksi_type : ($this->invoiceForm['refraksi_value'] > 0 ? $this->invoiceForm['refraksi_type'] : 'qty');
            $itemRefraksiValue = ($ap && $ap->refraksi_value > 0) ? (float) $ap->refraksi_value : (float) $this->invoiceForm['refraksi_value'];
            
            $itemExpenses = [];
            if ($ap) { foreach ($ap->expenses as $e) { $itemExpenses[] = ['type' => $e->type, 'amount' => (float) $e->amount]; $expenseRows->push((object)['type' => $e->type, 'amount' => (float) $e->amount]); } }

            $items[] = [
                'item_name' => 'Pengiriman ' . $pengiriman->no_pengiriman,
                'description' => 'No. Pengiriman: ' . $pengiriman->no_pengiriman . '\nTanggal Kirim: ' . $pengiriman->tanggal_kirim->format('d M Y') . '\nTotal Qty: ' . number_format($pengiriman->total_qty_kirim, 2, ',', '.') . ' kg',
                'quantity' => 1, 'unit' => 'paket', 'unit_price' => $shipmentTotal, 'amount' => $shipmentTotal,
                'refraksi_type' => $itemRefraksiType, 'refraksi_value' => $itemRefraksiValue, 'refraksi_amount' => 0,
                'expenses' => $itemExpenses, 'details' => $itemDetails,
            ];
        }
        return ['items' => $items, 'totalSellingPrice' => $totalSellingPrice];
    }

    private function calculateFinalTotals(&$items, $totalSellingPrice, $expenseRows, $companySetting)
    {
        $totalRefraksiAmount = 0; $totalRefraksiQty = 0; $totalQty = 0;
        foreach ($items as &$item) {
            $qty = array_sum(array_column($item['details'], 'qty'));
            $totalQty += $qty;
            if ($item['refraksi_value'] > 0) {
                if ($item['refraksi_type'] === 'qty' && $qty > 0) {
                    $refQty = $qty * ($item['refraksi_value'] / 100);
                    $item['refraksi_amount'] = $refQty * ($item['amount'] / $qty);
                    $totalRefraksiQty += $refQty;
                } elseif ($item['refraksi_type'] === 'rupiah' && $qty > 0) $item['refraksi_amount'] = $item['refraksi_value'] * $qty;
                else $item['refraksi_amount'] = $item['refraksi_value'];
            }
            $totalRefraksiAmount += $item['refraksi_amount'];
        }
        
        $expensesTotal = $expenseRows->sum('amount');
        $amountAfterRefraksi = $totalSellingPrice - $totalRefraksiAmount;
        $subtotal = $amountAfterRefraksi + $expensesTotal;
        $taxAmount = $subtotal * ($companySetting->tax_percentage / 100);

        return [
            'qty_before_refraksi' => $totalQty,
            'qty_after_refraksi' => $totalQty - $totalRefraksiQty,
            'amount_before_refraksi' => $totalSellingPrice,
            'amount_after_refraksi' => $amountAfterRefraksi,
            'refraksi_amount' => $totalRefraksiAmount,
            'additional_expenses_total' => $expensesTotal,
            'subtotal' => $subtotal,
            'tax_percentage' => $companySetting->tax_percentage,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'total_amount' => $subtotal + $taxAmount,
        ];
    }

    private function processMergeCleanup($newInvoiceId)
    {
        if ($this->isMergedInvoice && !empty($this->selectedApprovalIds)) {
            $oldApprovals = ApprovalPenagihanModel::whereIn('id', $this->selectedApprovalIds)->get();
            $oldInvoiceIds = $oldApprovals->pluck('invoice_id')->filter()->unique();
            Pengiriman::whereIn('invoice_penagihan_id', $oldInvoiceIds)->where('invoice_penagihan_id', '!=', $newInvoiceId)->update(['invoice_penagihan_id' => null]);
            ApprovalPenagihanModel::whereIn('id', $this->selectedApprovalIds)->update(['status' => 'digabung']);
            InvoicePenagihan::whereIn('id', $oldInvoiceIds)->update(['status' => 'digabung']);
        }
    }
    
    public function showDetail($approvalId)
    {
        $this->selectedData = ApprovalPenagihanModel::with(['invoice', 'pengiriman.purchaseOrder.klien', 'pengiriman.forecast', 'pengiriman.purchasing', 'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier', 'staff', 'manager', 'histories.user'])->findOrFail($approvalId);
        $this->showDetailModal = true;
        
        if ($inv = $this->selectedData->invoice) {
            $this->customerForm = ['customer_name' => $inv->customer_name, 'customer_address' => $inv->customer_address, 'customer_phone' => $inv->customer_phone, 'customer_email' => $inv->customer_email];
            $this->dateForm = ['invoice_date' => $inv->invoice_date?->format('Y-m-d'), 'due_date' => $inv->due_date?->format('Y-m-d')];
            $this->bankForm = ['bank_name' => $inv->bank_name, 'bank_account_number' => $inv->bank_account_number, 'bank_account_name' => $inv->bank_account_name];
            $this->invoiceNotesForm = $inv->notes ?? '';
            $this->invoiceNumberForm = $inv->invoice_number ?? '';
            $this->totalHargaJualForm = $inv->subtotal ?? 0;
        }
        $this->approvalHistory = ApprovalHistory::where('approval_type', 'penagihan')->where('approval_id', $approvalId)->with('user')->orderByDesc('created_at')->get()->toArray();
    }

    public function updateCustomerInfo() { $this->executeFieldUpdate('customerForm', ['customer_name'=>'required', 'customer_address'=>'required'], 'Update informasi customer'); }
    public function updateInvoiceDates() { $this->executeFieldUpdate('dateForm', ['invoice_date'=>'required|date', 'due_date'=>'required|date'], 'Update tanggal invoice'); }
    public function updateBankInfo() { $this->executeFieldUpdate('bankForm', ['bank_name'=>'required', 'bank_account_number'=>'required', 'bank_account_name'=>'required'], 'Update informasi bank'); }
    
    private function executeFieldUpdate($formName, $rules, $logNote)
    {
        $this->validate(array_combine(array_map(fn($k) => "$formName.$k", array_keys($rules)), array_values($rules)));
        if (!$this->selectedData?->invoice) return;
        DB::beginTransaction();
        try {
            $this->selectedData->invoice->update($this->{$formName});
            $this->logInvoiceHistory($this->selectedData->id, $this->selectedData->pengiriman_id, $this->selectedData->invoice->id, 'edited', $logNote);
            DB::commit();
            session()->flash('message', "$logNote berhasil");
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) { DB::rollBack(); Log::error("Update $formName Error: " . $e->getMessage()); session()->flash('error', 'Gagal update: ' . $e->getMessage()); }
    }

    public function updateInvoiceNotes()
    {
        if (!$this->selectedData?->invoice) return;
        DB::beginTransaction();
        try {
            $this->selectedData->invoice->update(['notes' => $this->invoiceNotesForm]);
            $this->logInvoiceHistory($this->selectedData->id, $this->selectedData->pengiriman_id, $this->selectedData->invoice->id, 'edited', 'Update catatan invoice');
            DB::commit();
            session()->flash('message', 'Catatan berhasil diupdate');
        } catch (\Exception $e) { DB::rollBack(); Log::error("Update Notes Error: " . $e->getMessage()); }
    }

    public function updateInvoiceNumber()
    {
        $this->validate(['invoiceNumberForm' => 'required|string|max:191']);
        if (!$this->selectedData?->invoice) return;
        if (InvoicePenagihan::where('invoice_number', $this->invoiceNumberForm)->where('id', '!=', $this->selectedData->invoice->id)->exists()) {
            session()->flash('error', 'Nomor invoice sudah digunakan.'); return;
        }
        DB::beginTransaction();
        try {
            $this->selectedData->invoice->update(['invoice_number' => $this->invoiceNumberForm]);
            $this->logInvoiceHistory($this->selectedData->id, $this->selectedData->pengiriman_id, $this->selectedData->invoice->id, 'edited', 'Update nomor invoice');
            DB::commit();
            session()->flash('message', 'Nomor invoice berhasil diupdate');
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) { DB::rollBack(); Log::error("Update Invoice Number Error: " . $e->getMessage()); session()->flash('error', 'Gagal update: ' . $e->getMessage()); }
    }

    public function approve()
    {
        if (!$this->selectedData || !$this->ensureCanManage()) return;
        $this->validate(['customerForm.customer_name' => 'required', 'customerForm.customer_address' => 'required']);
        
        DB::beginTransaction();
        try {
            if ($this->selectedData->status !== 'pending') throw new \Exception('Approval tidak valid');
            if ($this->selectedData->invoice) $this->selectedData->invoice->update($this->customerForm);
            
            $role = $this->getApprovalUserRole();
            $updateData = ['status' => 'completed', in_array($role, ['manager_keuangan', 'superadmin']) ? 'manager_id' : 'staff_id' => Auth::id(), in_array($role, ['manager_keuangan', 'superadmin']) ? 'manager_approved_at' : 'staff_approved_at' => now()];
            $this->selectedData->update($updateData);

            $this->logInvoiceHistory($this->selectedData->id, $this->selectedData->pengiriman_id, $this->selectedData->invoice_id, 'approved', $this->notes);
            DB::commit();
            session()->flash('message', 'Approval berhasil disimpan');
            $this->closeModal();
        } catch (\Exception $e) { DB::rollBack(); Log::error("Approve Error: " . $e->getMessage()); session()->flash('error', $e->getMessage()); }
    }

    public function closeModal()
    {
        $this->reset(['showDetailModal', 'showCreateInvoiceModal', 'selectedData', 'selectedShipment', 'selectedShipments', 'isMergedInvoice', 'notes', 'approvalHistory']);
    }
}