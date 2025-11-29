<?php

namespace App\Livewire\Accounting;

use App\Models\ApprovalPenagihan;
use App\Models\InvoicePenagihan;
use App\Models\Pengiriman;
use App\Models\CompanySetting;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class DetailPenagihan extends Component
{
    public $approvalId;
    public $approval;
    public $invoice;
    public $pengiriman;
    public $approvalHistory;
    public $companySetting;

    public function mount($approvalId)
    {
        $this->approvalId = $approvalId;
        $this->loadDetail();
    }

    public function loadDetail()
    {
        $this->approval = ApprovalPenagihan::with([
            'staff',
            'manager',
            'invoice',
            'pengiriman.details.bahanBakuSupplier',
            'pengiriman.details.purchaseOrderBahanBaku.bahanBakuKlien',
            'pengiriman.details.purchaseOrderBahanBaku', // Tambahan untuk harga_jual
            'pengiriman.details.orderDetail', // Tambahan untuk harga_jual
            'pengiriman.purchaseOrder.orderDetails.orderSuppliers.supplier',
            'histories.user'
        ])->findOrFail($this->approvalId);

        $this->invoice = $this->approval->invoice;
        $this->pengiriman = $this->approval->pengiriman;
        $this->approvalHistory = $this->approval->histories()->orderBy('created_at', 'desc')->get();
        $this->companySetting = CompanySetting::first();
    }

    public function generatePdf()
    {
        try {
            $approval = ApprovalPenagihan::with([
                'invoice',
                'pengiriman.details.bahanBakuSupplier',
                'pengiriman.details.purchaseOrderBahanBaku.bahanBakuKlien',
                'pengiriman.purchaseOrder.klien'
            ])->findOrFail($this->approvalId);

            $invoice = $approval->invoice;
            $pengiriman = $approval->pengiriman;
            $companySetting = CompanySetting::first();

            // Prepare data for PDF
            $data = [
                'invoice' => $invoice,
                'pengiriman' => $pengiriman,
                'approval' => $approval,
                'company' => $companySetting,
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.invoice-penagihan', $data);
            $pdf->setPaper('a4', 'portrait');

            // Clean invoice number for filename (remove / and \)
            $cleanInvoiceNumber = str_replace(['/', '\\'], '-', $invoice->invoice_number);

            // Download PDF
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'Invoice-' . $cleanInvoiceNumber . '.pdf');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
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
        if ($this->pengiriman->details) {
            foreach ($this->pengiriman->details as $detail) {
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

        return view('livewire.accounting.detail-penagihan', [
            'order' => $order,
            'totalSupplierCost' => $totalSupplierCost,
            'totalSelling' => $totalSelling,
            'totalMargin' => $totalMargin,
            'marginPercentage' => $marginPercentage,
        ]);
    }
}
