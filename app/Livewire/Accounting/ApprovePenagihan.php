<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\ApprovalPenagihan as ApprovalPenagihanModel;
use App\Models\InvoicePenagihan;
use App\Livewire\Accounting\Traits\WithInvoiceShared;
use App\Livewire\Accounting\Traits\WithInvoiceCalculations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovePenagihan extends Component
{
    use WithInvoiceShared, WithInvoiceCalculations;

    public $approvalId, $approval, $invoice, $pengiriman, $approvalHistory, $notes = '';
    public $editMode = false, $canManage = false;
    public $expenseForm = ['truk' => 0, 'kuli' => 0, 'fee' => 0, 'others' => []];
    public $refraksiForm = ['type' => 'qty', 'value' => 0];
    public $itemRefraksi = [], $refraksiPerItem = [], $expensePerItem = [];
    public $invoiceDate, $dueDate, $invoiceNumber = '', $customerName = '', $customerAddress = '', $customerPhone = '', $customerEmail = '', $invoiceNotes = '';
    public $selectedBank = 'mandiri';
    public $bankOptions = [
        'mandiri' => ['name' => 'Bank Mandiri', 'account_number' => '141-0080998883', 'account_name' => 'PT. KAMIL MAJU PERSADA'],
        'bca' => ['name' => 'BCA', 'account_number' => '429-3468888', 'account_name' => 'PT KAMIL MAJU PERSADA'],
        'mandiri2' => ['name' => 'Bank Mandiri', 'account_number' => '141-0008899098', 'account_name' => 'PT KAMIL MAJU PERSADA'],
    ];

    public function mount($approvalId, $editMode = false)
    {
        $this->approvalId = $approvalId;
        $this->editMode = $editMode;
        $this->canManage = in_array(Auth::user()->role, ['staff_accounting', 'manager_accounting', 'direktur', 'superadmin']);
        $this->loadApproval(true);
    }

    public function loadApproval(bool $syncRefraksi = false)
    {
        $this->approval = ApprovalPenagihanModel::with(['invoice.pengirimans.pengirimanDetails.bahanBakuSupplier.supplier', 'pengiriman.pengirimanDetails', 'histories.user', 'staff', 'manager'])->findOrFail($this->approvalId);
        $this->invoice = $this->approval->invoice;
        $this->pengiriman = $this->approval->pengiriman;
        $this->approvalHistory = $this->approval->histories;

        if ($syncRefraksi) $this->syncRefraksiFromPembayaran();

        $this->refraksiForm = ['type' => $this->invoice->refraksi_type ?? 'qty', 'value' => $this->invoice->refraksi_value ?? 0];
        $rawItems = $this->invoice->items ?? [];
        $this->itemRefraksi = []; $this->refraksiPerItem = []; $this->expensePerItem = [];
        
        foreach ($rawItems as $i => $item) {
            $this->itemRefraksi[$i] = (float) ($item['refraksi_kg'] ?? 0);
            $this->refraksiPerItem[$i] = ['type' => $item['refraksi_type'] ?? 'qty', 'value' => (float) ($item['refraksi_value'] ?? 0), 'amount' => (float) ($item['amount'] ?? 0)];
            $truk = 0; $kuli = 0; $fee = 0; $others = [];
            foreach ($item['expenses'] ?? [] as $e) {
                if ($e['type'] === 'truk') $truk = $e['amount']; elseif ($e['type'] === 'kuli') $kuli = $e['amount']; elseif ($e['type'] === 'fee') $fee = $e['amount']; else $others[] = $e;
            }
            $this->expensePerItem[$i] = ['truk' => $truk, 'kuli' => $kuli, 'fee' => $fee, 'others' => $others ?: [['type' => '', 'amount' => 0]]];
        }

        $this->invoiceDate = $this->invoice->invoice_date?->format('Y-m-d'); $this->dueDate = $this->invoice->due_date?->format('Y-m-d');
        $this->invoiceNumber = $this->invoice->invoice_number ?? ''; $this->customerName = $this->invoice->customer_name ?? '';
        $this->customerAddress = $this->invoice->customer_address ?? ''; $this->customerPhone = $this->invoice->customer_phone ?? '';
        $this->customerEmail = $this->invoice->customer_email ?? ''; $this->invoiceNotes = $this->invoice->notes ?? '';
        
        if ($this->invoice->bank_name) {
            foreach ($this->bankOptions as $key => $bank) if ($bank['name'] === $this->invoice->bank_name && $bank['account_number'] === $this->invoice->bank_account_number) $this->selectedBank = $key;
        } else {
            $this->invoice->update(['bank_name' => $this->bankOptions['mandiri']['name'], 'bank_account_number' => $this->bankOptions['mandiri']['account_number'], 'bank_account_name' => $this->bankOptions['mandiri']['account_name']]);
        }
        $this->loadExpenses();
    }

    private function syncRefraksiFromPembayaran()
    {
        if (!$this->pengiriman || ($this->invoice && $this->invoice->pengirimans->count() > 0) || !empty($this->invoice->refraksi_value) || !$this->pengiriman->approvalPembayaran || $this->pengiriman->approvalPembayaran->refraksi_value <= 0) return;
        
        $ap = $this->pengiriman->approvalPembayaran;
        $totalSelling = collect($this->pengiriman->pengirimanDetails)->sum(fn($d) => floatval($d->qty_kirim) * floatval($d->purchaseOrderBahanBaku->harga_jual ?? $d->orderDetail->harga_jual ?? 0));
        
        $subtotal = $totalSelling;
        $refraksiAmount = ($ap->refraksi_type === 'qty') ? ($this->pengiriman->total_qty_kirim * ($ap->refraksi_value / 100) * ($this->pengiriman->total_qty_kirim > 0 ? $subtotal / $this->pengiriman->total_qty_kirim : 0)) : (($ap->refraksi_type === 'rupiah') ? $ap->refraksi_value * $this->pengiriman->total_qty_kirim : $ap->refraksi_value);
        
        $this->invoice->update(['refraksi_type' => $ap->refraksi_type, 'refraksi_value' => $ap->refraksi_value, 'refraksi_amount' => $refraksiAmount, 'amount_before_refraksi' => $totalSelling, 'amount_after_refraksi' => $subtotal - $refraksiAmount, 'subtotal' => $subtotal - $refraksiAmount, 'total_amount' => $subtotal - $refraksiAmount + ($this->invoice->tax_amount ?? 0) - ($this->invoice->discount_amount ?? 0)]);
    }

    private function loadExpenses(): void
    {
        $this->expenseForm = ['truk' => 0, 'kuli' => 0, 'fee' => 0, 'others' => []];
        foreach ($this->invoice->expenses()->get() as $e) {
            if (in_array($e->type, ['truk', 'kuli', 'fee'])) $this->expenseForm[$e->type] = floatval($e->amount);
            else $this->expenseForm['others'][] = ['type' => $e->type, 'amount' => floatval($e->amount)];
        }
        if (empty($this->expenseForm['others'])) $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
    }

    public function approve()
    {
        if (!$this->approval || !$this->ensureCanManage()) return;
        if (!$this->invoice->bank_name) { session()->flash('error', 'Silakan pilih bank terlebih dahulu'); return; }
        
        $this->validate(['invoiceNumber' => 'required|max:191', 'customerName' => 'required', 'customerAddress' => 'required']);
        if (InvoicePenagihan::where('invoice_number', $this->invoiceNumber)->where('id', '!=', $this->invoice->id)->exists()) { session()->flash('error', 'Nomor invoice sudah digunakan.'); return; }
        
        DB::beginTransaction();
        try {
            if ($this->approval->status !== 'pending') throw new \Exception('Approval tidak valid');
            $this->invoice->update(['invoice_number' => $this->invoiceNumber, 'customer_name' => $this->customerName, 'customer_address' => $this->customerAddress, 'customer_phone' => $this->customerPhone, 'customer_email' => $this->customerEmail, 'notes' => $this->invoiceNotes]);
            
            $role = $this->getApprovalUserRole();
            $this->approval->update(['status' => 'completed', in_array($role, ['manager_keuangan', 'superadmin']) ? 'manager_id' : 'staff_id' => Auth::id(), in_array($role, ['manager_keuangan', 'superadmin']) ? 'manager_approved_at' : 'staff_approved_at' => now()]);
            $this->logInvoiceHistory($this->approval->id, $this->approval->pengiriman_id, $this->approval->invoice_id, 'approved', $this->notes);
            
            DB::commit();
            session()->flash('message', 'Approval berhasil disimpan');
            return redirect()->route('accounting.approval-penagihan');
        } catch (\Exception $e) { DB::rollBack(); Log::error("Approve Error: " . $e->getMessage()); session()->flash('error', $e->getMessage()); }
    }
    public function updateInvoiceDates()
    {
        if (!$this->ensureCanManage()) return;

        $this->validate([
            'invoiceDate' => 'required|date',
            'dueDate'     => 'required|date|after_or_equal:invoiceDate',
        ]);

        try {
            $this->invoice->update([
                'invoice_date' => $this->invoiceDate,
                'due_date'     => $this->dueDate,
            ]);

            if ($this->editMode && $this->approval->status === 'completed') {
                $this->logInvoiceHistory($this->approval->id, $this->approval->pengiriman_id, $this->invoice->id, 'edited', 'Tanggal invoice diubah');
            }

            $this->invoice->refresh();
            session()->flash('message', 'Tanggal invoice berhasil diupdate');
        } catch (\Exception $e) {
            Log::error('Update Invoice Dates Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal update tanggal invoice');
        }
    }
    // Metode update khusus statis disederhanakan
    public function updateBankSelection()
    {
        if (!$this->ensureCanManage()) return;
        $this->validate(['selectedBank' => 'required']);
        try {
            $this->invoice->update(['bank_name' => $this->bankOptions[$this->selectedBank]['name'], 'bank_account_number' => $this->bankOptions[$this->selectedBank]['account_number'], 'bank_account_name' => $this->bankOptions[$this->selectedBank]['account_name']]);
            if ($this->editMode && $this->approval->status === 'completed') $this->logInvoiceHistory($this->approval->id, $this->approval->pengiriman_id, $this->approval->invoice_id, 'edited', 'Bank diubah');
            $this->invoice->refresh(); session()->flash('message', 'Bank berhasil diupdate');
        } catch (\Exception $e) { Log::error($e->getMessage()); session()->flash('error', 'Gagal update bank'); }
    }

    public function render()
    {
        $shipments = ($this->invoice && $this->invoice->pengirimans->count() > 0) ? $this->invoice->pengirimans : collect($this->pengiriman ? [$this->pengiriman] : []);
        $totalSelling = $shipments->sum(fn($s) => collect($s->pengirimanDetails)->sum(fn($d) => floatval($d->qty_kirim) * floatval($d->purchaseOrderBahanBaku->harga_jual ?? $d->orderDetail->harga_jual ?? 0)));
        $totalSupplierCost = $shipments->sum(fn($s) => $s->approvalPembayaran ? (floatval($s->approvalPembayaran->subtotal) > 0 ? floatval($s->approvalPembayaran->subtotal) : (floatval($s->approvalPembayaran->amount_after_refraksi) > 0 ? floatval($s->approvalPembayaran->amount_after_refraksi) : floatval($s->total_harga_kirim))) : floatval($s->total_harga_kirim));
        
        return view('livewire.accounting.approve-penagihan', [
            'order' => $this->pengiriman->purchaseOrder ?? null,
            'totalSupplierCost' => $totalSupplierCost,
            'totalSelling' => $totalSelling,
            'totalMargin' => $totalSelling - $totalSupplierCost,
            'marginPercentage' => $totalSelling > 0 ? (($totalSelling - $totalSupplierCost) / $totalSelling) * 100 : 0,
            'isMerged' => ($this->invoice && $this->invoice->pengirimans->count() > 0),
            'shipments' => $shipments,
        ]);
    }
    public function updateAllInvoiceFields()
    {
        if (!$this->ensureCanManage()) return;

        $this->validate([
            'invoiceNumber'   => 'required|max:191',
            'customerName'    => 'required',
            'customerAddress' => 'required',
            'customerEmail'   => 'nullable|email',
            'invoiceDate'     => 'nullable|date',
            'dueDate'         => 'nullable|date|after_or_equal:invoiceDate',
        ]);

        if (InvoicePenagihan::where('invoice_number', $this->invoiceNumber)->where('id', '!=', $this->invoice->id)->exists()) {
            session()->flash('error', 'Nomor invoice sudah digunakan.');
            return;
        }

        try {
            $this->invoice->update([
                'invoice_number'   => $this->invoiceNumber,
                'customer_name'    => $this->customerName,
                'customer_address' => $this->customerAddress,
                'customer_phone'   => $this->customerPhone,
                'customer_email'   => $this->customerEmail,
                'notes'            => $this->invoiceNotes,
                'invoice_date'     => $this->invoiceDate ?: $this->invoice->invoice_date,
                'due_date'         => $this->dueDate ?: $this->invoice->due_date,
            ]);

            if ($this->editMode && $this->approval->status === 'completed') {
                $this->logInvoiceHistory($this->approval->id, $this->approval->pengiriman_id, $this->invoice->id, 'edited', 'Data invoice diubah (simpan semua)');
            }

            $this->invoice->refresh();
            session()->flash('message', 'Semua perubahan berhasil disimpan');
        } catch (\Exception $e) {
            Log::error('Update All Invoice Fields Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal menyimpan perubahan');
        }
    }
}