<?php

namespace App\Livewire\Accounting;

use App\Models\ApprovalPenagihan;
use App\Models\InvoicePenagihan;
use App\Models\CompanySetting;
use App\Livewire\Accounting\Traits\WithInvoiceShared;
use App\Livewire\Accounting\Traits\WithInvoiceCalculations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DetailPenagihan extends Component
{
    use WithInvoiceShared, WithInvoiceCalculations;

    public $approvalId, $approval, $invoice, $pengiriman, $pengirimans, $approvalHistory, $companySetting, $editMode = false, $canManage = false;
    public $expenseForm = ['truk' => 0, 'kuli' => 0, 'fee' => 0, 'others' => []];
    public $refraksiPerItem = [], $expensePerItem = [], $refraksiForm = ['type' => 'qty', 'value' => 0];
    public $customerForm = ['customer_name' => '', 'customer_address' => '', 'customer_phone' => '', 'customer_email' => ''];
    public $dateForm = ['invoice_date' => '', 'due_date' => ''];
    public $bankForm = ['bank_name' => '', 'bank_account_number' => '', 'bank_account_name' => ''];
    public $invoiceNumberForm = '', $invoiceNotesForm = '', $selectedBank = null;

    public $bankOptions = [
        'mandiri' => ['name' => 'Bank Mandiri', 'account_number' => '141-0080998883', 'account_name' => 'PT. KAMIL MAJU PERSADA'],
        'bca' => ['name' => 'BCA', 'account_number' => '429-3468888', 'account_name' => 'PT KAMIL MAJU PERSADA'],
        'mandiri2' => ['name' => 'Bank Mandiri', 'account_number' => '141-0008899098', 'account_name' => 'PT KAMIL MAJU PERSADA'],
    ];

    public function mount($approvalId, $editMode = false)
    {
        $this->approvalId = $approvalId; $this->editMode = $editMode;
        $this->canManage = in_array(Auth::user()->role, ['manager_accounting', 'direktur', 'superadmin', 'staff_accounting']);
        $this->loadDetail();
    }

    public function loadDetail()
    {
        $this->approval = ApprovalPenagihan::with(['staff', 'manager', 'invoice.pengirimans.details.bahanBakuSupplier', 'pengiriman.pengirimanDetails', 'histories.user'])->findOrFail($this->approvalId);
        $this->invoice = $this->approval->invoice; $this->pengiriman = $this->approval->pengiriman;
        $this->pengirimans = $this->invoice ? ($this->invoice->pengirimans->count() > 1 ? $this->invoice->pengirimans : collect([$this->pengiriman])) : collect([$this->pengiriman]);
        $this->approvalHistory = $this->approval->histories()->orderByDesc('created_at')->get();
        $this->companySetting = CompanySetting::first();

        if ($this->invoice) {
            $this->customerForm = ['customer_name' => $this->invoice->customer_name, 'customer_address' => $this->invoice->customer_address, 'customer_phone' => $this->invoice->customer_phone, 'customer_email' => $this->invoice->customer_email];
            $this->dateForm = ['invoice_date' => $this->invoice->invoice_date?->format('Y-m-d'), 'due_date' => $this->invoice->due_date?->format('Y-m-d')];
            $this->bankForm = ['bank_name' => $this->invoice->bank_name, 'bank_account_number' => $this->invoice->bank_account_number, 'bank_account_name' => $this->invoice->bank_account_name];
            $this->refraksiForm = ['type' => $this->invoice->refraksi_type ?? 'qty', 'value' => (float) ($this->invoice->refraksi_value ?? 0)];
            $this->invoiceNumberForm = $this->invoice->invoice_number; $this->invoiceNotesForm = $this->invoice->notes;
            
            $this->selectedBank = null;
            foreach ($this->bankOptions as $key => $bank) if ($bank['account_number'] === $this->invoice->bank_account_number) $this->selectedBank = $key;

            $this->refraksiPerItem = []; $this->expensePerItem = [];
            foreach ($this->invoice->items ?? [] as $i => $item) {
                $this->refraksiPerItem[$i] = ['type' => $item['refraksi_type'] ?? 'qty', 'value' => (float) ($item['refraksi_value'] ?? 0), 'amount' => (float) ($item['amount'] ?? 0)];
                $truk = 0; $kuli = 0; $fee = 0; $others = [];
                foreach ($item['expenses'] ?? [] as $e) {
                    if ($e['type'] === 'truk') $truk = $e['amount']; elseif ($e['type'] === 'kuli') $kuli = $e['amount']; elseif ($e['type'] === 'fee') $fee = $e['amount']; else $others[] = $e;
                }
                $this->expensePerItem[$i] = ['truk' => $truk, 'kuli' => $kuli, 'fee' => $fee, 'others' => $others ?: [['type' => '', 'amount' => 0]]];
            }
        }
    }

    public function updatedSelectedBank($value)
    {
        if (array_key_exists($value, $this->bankOptions)) $this->bankForm = ['bank_name' => $this->bankOptions[$value]['name'], 'bank_account_number' => $this->bankOptions[$value]['account_number'], 'bank_account_name' => $this->bankOptions[$value]['account_name']];
    }

    public function updateCustomerInfo() { $this->executeFieldUpdate('customerForm', ['customerForm.customer_name'=>'required', 'customerForm.customer_address'=>'required'], 'Update informasi customer'); }
    public function updateInvoiceDates() { $this->executeFieldUpdate('dateForm', ['dateForm.invoice_date'=>'required|date', 'dateForm.due_date'=>'required|date'], 'Update tanggal invoice'); }
    public function updateBankInfo() { $this->executeFieldUpdate('bankForm', ['bankForm.bank_name'=>'required', 'bankForm.bank_account_number'=>'required', 'bankForm.bank_account_name'=>'required'], 'Update informasi bank'); }
    
    private function executeFieldUpdate($formName, $rules, $logNote)
    {
        $this->validate($rules);
        if (!$this->invoice) return;
        DB::beginTransaction();
        try {
            $this->invoice->update($this->{$formName});
            $this->logInvoiceHistory($this->approval->id, $this->approval->pengiriman_id, $this->invoice->id, 'edited', $logNote);
            DB::commit();
            session()->flash('message', "$logNote berhasil");
            $this->invoice->refresh();
        } catch (\Exception $e) { DB::rollBack(); Log::error("Update Error: " . $e->getMessage()); session()->flash('error', 'Gagal update: ' . $e->getMessage()); }
    }

    public function updateInvoiceNumber()
    {
        $this->validate(['invoiceNumberForm' => 'required|string|max:50|unique:invoice_penagihan,invoice_number,' . $this->invoice->id]);
        DB::beginTransaction();
        try {
            $this->invoice->update(['invoice_number' => $this->invoiceNumberForm]);
            $this->logInvoiceHistory($this->approval->id, $this->approval->pengiriman_id, $this->invoice->id, 'edited', 'Update nomor invoice');
            DB::commit();
            session()->flash('message', 'Nomor invoice berhasil diupdate');
            $this->invoice->refresh();
        } catch (\Exception $e) { DB::rollBack(); Log::error("Update Invoice Number Error: " . $e->getMessage()); session()->flash('error', 'Gagal update: ' . $e->getMessage()); }
    }

    public function updateInvoiceNotes()
    {
        DB::beginTransaction();
        try {
            $this->invoice->update(['notes' => $this->invoiceNotesForm]);
            $this->logInvoiceHistory($this->approval->id, $this->approval->pengiriman_id, $this->invoice->id, 'edited', 'Update catatan invoice');
            DB::commit(); session()->flash('message', 'Catatan berhasil diupdate'); $this->invoice->refresh();
        } catch (\Exception $e) { DB::rollBack(); Log::error("Update Notes Error: " . $e->getMessage()); session()->flash('error', 'Gagal update catatan'); }
    }

    public function generatePdf()
    {
        try {
            $pdf = Pdf::loadView('pdf.invoice-penagihan', ['invoice' => $this->invoice, 'pengiriman' => $this->pengiriman, 'pengirimans' => $this->pengirimans, 'approval' => $this->approval, 'company' => $this->companySetting])->setPaper('a4', 'portrait');
            return response()->streamDownload(fn() => print($pdf->output()), 'Invoice-' . str_replace(['/', '\\'], '-', $this->invoice->invoice_number) . '.pdf');
        } catch (\Exception $e) { Log::error("PDF Error: " . $e->getMessage()); session()->flash('error', 'Gagal generate PDF'); }
    }

    public function render()
    {
        $subtotalPenagihan = floatval($this->invoice?->subtotal ?? $this->invoice?->amount_after_refraksi ?? 0);
        $subtotalPembayaran = $this->pengirimans->sum(fn($p) => $p->approvalPembayaran ? (floatval($p->approvalPembayaran->subtotal) > 0 ? floatval($p->approvalPembayaran->subtotal) : (floatval($p->approvalPembayaran->amount_after_refraksi) > 0 ? floatval($p->approvalPembayaran->amount_after_refraksi) : floatval($p->total_harga_kirim))) : floatval($p->total_harga_kirim));
        
        return view('livewire.accounting.detail-penagihan', [
            'order' => $this->pengiriman->purchaseOrder ?? null,
            'subtotalPenagihan' => $subtotalPenagihan,
            'subtotalPembayaran' => $subtotalPembayaran,
            'totalMargin' => $subtotalPenagihan - $subtotalPembayaran,
            'marginPercentage' => $subtotalPenagihan > 0 ? (($subtotalPenagihan - $subtotalPembayaran) / $subtotalPenagihan) * 100 : 0,
            'pengirimans' => $this->pengirimans,
        ]);
    }
}