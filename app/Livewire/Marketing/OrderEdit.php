<?php

namespace App\Livewire\Marketing;

use App\Models\Order;
use App\Models\BahanBakuKlien;
use Illuminate\Support\Carbon;

class OrderEdit extends OrderCreate
{
    public function mount(?Order $order = null): void
    {
        if (!$order || !$order->exists) {
            abort(404, "Order tidak ditemukan");
        }

        // Load relationships
        $order->load([
            "klien",
            "orderDetails.bahanBakuKlien",
            "orderDetails.orderSuppliers.supplier.picPurchasing",
            "orderDetails.orderSuppliers.bahanBakuSupplier",
            "winner",
        ]);

        // Initialize default values first
        $this->tanggalOrder = now()->format("Y-m-d");
        $this->poStartDate = $this->tanggalOrder;
        $this->poEndDate = now()->addDays(14)->format("Y-m-d");
        $this->resetTotals();
        $this->loadAvailableWinners();

        // Set editing mode
        $this->isEditing = true;
        $this->editingOrderId = $order->id;
        $this->editingOrderNumber =
            $order->po_number ?? ($order->no_order ?? "Order #" . $order->id);
        $this->currentStatus = $order->status ?? "draft";

        // Load client data
        $this->selectedKlien = optional($order->klien)->nama;
        $this->selectedKlienCabang = optional($order->klien)->cabang;
        $this->selectedKlienId = $order->klien_id;

        // Load order data
        $this->tanggalOrder = $order->tanggal_order
            ? Carbon::parse($order->tanggal_order)->format("Y-m-d")
            : $this->tanggalOrder;
        $this->poNumber = $order->po_number;
        $this->poStartDate = $order->po_start_date
            ? Carbon::parse($order->po_start_date)->format("Y-m-d")
            : $this->poStartDate;
        $this->poEndDate = $order->po_end_date
            ? Carbon::parse($order->po_end_date)->format("Y-m-d")
            : $this->poEndDate;
        // Prefer the canonical `priority` column
        $this->priority = $order->priority ?? "sedang";
        $this->catatan = $order->catatan;

        // Load PO document data
        $this->existingPoDocumentPath = $order->po_document_path;
        $this->existingPoDocumentName = $order->po_document_original_name;
        $this->existingPoDocumentUrl = $order->po_document_url;

        // Load PO winner
        if ($order->winner) {
            $this->poWinnerId = $order->winner->user_id;
        }

        // Load single material data from first order detail
        $firstDetail = $order->orderDetails->first();
        if ($firstDetail) {
            $this->selectedMaterial = $firstDetail->bahan_baku_klien_id;
            $this->namaMaterialPO = $firstDetail->nama_material_po;
            $this->quantity = (float) $firstDetail->qty;
            $this->satuan = $firstDetail->satuan;
            $this->hargaJual = (float) $firstDetail->harga_jual;
            $this->spesifikasiKhusus = $firstDetail->spesifikasi_khusus ?? "";
            $this->catatanMaterial = $firstDetail->catatan ?? "";

            // Load auto suppliers for this material
            $material = BahanBakuKlien::find($this->selectedMaterial);
            if ($material) {
                $this->autoPopulateSuppliers($material);
            }
        }

        $this->updateTotals();
        $this->updatePriorityFromSchedule();

        // Reset upload field to avoid showing old file state
        $this->poDocument = null;
    }
}
