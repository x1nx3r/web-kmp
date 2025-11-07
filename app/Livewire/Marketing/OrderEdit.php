<?php

namespace App\Livewire\Marketing;

use App\Models\Order;

class OrderEdit extends OrderCreate
{
    public function mount(?Order $order = null): void
    {
        parent::mount($order);

        if (!$order) {
            abort(404);
        }

        $order->load([
            'klien',
            'orderDetails.bahanBakuKlien',
            'orderDetails.orderSuppliers.supplier.picPurchasing',
            'orderDetails.orderSuppliers.bahanBakuSupplier',
        ]);

        $this->isEditing = true;
        $this->editingOrderId = $order->id;
        $this->editingOrderNumber = $order->po_number ?? $order->no_order ?? ('Order #' . $order->id);
        $this->currentStatus = $order->status ?? 'draft';

        $this->selectedKlien = optional($order->klien)->nama;
        $this->selectedKlienCabang = optional($order->klien)->cabang;
        $this->selectedKlienId = $order->klien_id;

        $this->tanggalOrder = optional($order->tanggal_order)->format('Y-m-d');
        $this->poNumber = $order->po_number;
        $this->poStartDate = optional($order->po_start_date)->format('Y-m-d');
        $this->poEndDate = optional($order->po_end_date)->format('Y-m-d');
        $this->priority = $order->priority ?? 'normal';
        $this->catatan = $order->catatan;

        $this->existingPoDocumentPath = $order->po_document_path;
        $this->existingPoDocumentName = $order->po_document_original_name;
        $this->existingPoDocumentUrl = $order->po_document_url;

        $this->selectedOrderItems = $order->orderDetails
            ->map(fn ($detail) => $this->transformDetailToSelectedItem($detail))
            ->values()
            ->toArray();

        $this->updateTotals();

        // Reset upload field to avoid showing old file state
        $this->poDocument = null;
    }
}
