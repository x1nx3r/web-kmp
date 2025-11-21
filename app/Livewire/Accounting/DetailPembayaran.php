<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\ApprovalPembayaran;
use App\Models\InvoicePenagihan;

class DetailPembayaran extends Component
{
    public $approvalId;
    public $approval;
    public $pengiriman;
    public $invoicePenagihan;
    public $approvalHistory;

    public function mount($approvalId)
    {
        $this->approvalId = $approvalId;
        $this->loadApproval();
    }

    public function loadApproval()
    {
        $this->approval = ApprovalPembayaran::with([
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'pengiriman.purchaseOrder',
            'histories' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'histories.user'
        ])->findOrFail($this->approvalId);

        $this->pengiriman = $this->approval->pengiriman;

        // Get invoice penagihan if exists
        $this->invoicePenagihan = InvoicePenagihan::where('pengiriman_id', $this->pengiriman->id)->first();

        $this->approvalHistory = $this->approval->histories;
    }

    public function render()
    {
        return view('livewire.accounting.detail-pembayaran');
    }
}
