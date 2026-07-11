<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ApprovalPembayaran;
use App\Models\InvoicePenagihan;
use App\Models\ApprovalHistory;
use App\Livewire\Accounting\Traits\WithPaymentApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DetailPembayaran extends Component
{
    use WithFileUploads, WithPaymentApproval;

    public $approvalId;
    public $approval;
    public $pengiriman;
    public $invoicePenagihan;
    public $approvalHistory;
    public $editMode = false;
    public $canManage = false;
    public $totalHargaBeliForm = 0;

    public function mount($approvalId, $editMode = false)
    {
        $this->approvalId = $approvalId;
        $this->editMode = $editMode;
        $this->canManage = in_array(Auth::user()->role, ['staff_accounting', 'manager_accounting', 'direktur', 'superadmin']);
        $this->loadApproval();
    }

    public function loadApproval()
    {
        $this->approval = ApprovalPembayaran::with([
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'pengiriman.purchaseOrder', 'catatanPiutang.supplier', 'histories.user', 'expenses'
        ])->findOrFail($this->approvalId);

        $this->pengiriman = $this->approval->pengiriman;
        $this->invoicePenagihan = InvoicePenagihan::where('pengiriman_id', $this->pengiriman->id)->first();
        $this->approvalHistory = $this->approval->histories()->orderByDesc('created_at')->get();

        $this->refraksiForm = ['type' => $this->approval->refraksi_type ?? 'qty', 'value' => $this->approval->refraksi_value ?? 0];
        $this->totalHargaBeliForm = $this->approval->amount_after_refraksi ?? $this->pengiriman->total_harga_kirim;
        
        $this->existingBuktiPembayaran = json_decode($this->approval->bukti_pembayaran, true) ?? [];
        $this->buktiPembayaran = [];
        $this->filesToRemove = [];
        
        $this->piutangForm = ['catatan_piutang_id' => $this->approval->catatan_piutang_id, 'amount' => $this->approval->piutang_amount ?? 0, 'notes' => $this->approval->piutang_notes ?? ''];

        $this->expenseForm = ['truk' => 0, 'kuli' => 0, 'fee' => 0, 'others' => []];
        foreach ($this->approval->expenses as $e) {
            $type = trim((string)($e->type ?? ''));
            if (in_array($type, ['truk', 'kuli', 'fee'])) $this->expenseForm[$type] = floatval($e->amount);
            else $this->expenseForm['others'][] = ['type' => $type, 'amount' => floatval($e->amount)];
        }
        if (empty($this->expenseForm['others'])) $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
    }

    public function updateTotalHargaBeli()
    {
        if (!$this->ensureCanManage()) return;
        $this->validate(['totalHargaBeliForm' => 'required|numeric|min:0']);

        DB::beginTransaction();
        try {
            $oldValue = $this->approval->amount_after_refraksi;
            $this->approval->update(['amount_after_refraksi' => floatval($this->totalHargaBeliForm)]);

            if ($this->editMode && $this->approval->status === 'completed') {
                $this->logHistory($this->approval, 'total_harga_beli', $oldValue, $this->approval->amount_after_refraksi, 'Updated total harga beli');
            }

            DB::commit();
            session()->flash('message', 'Total harga beli berhasil diupdate');
            $this->loadApproval();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Harga Beli Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function render() { return view('livewire.accounting.detail-pembayaran'); }
}