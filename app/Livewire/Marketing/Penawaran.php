<?php

namespace App\Livewire\Marketing;

use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\BahanBaku;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class Penawaran extends Component
{
    use WithPagination;

    public $selectedKlien = null;
    public $selectedKlienCabang = null;
    public $selectedMaterials = [];
    public $showAddMaterialModal = false;
    public $currentMaterial = null;
    public $currentQuantity = 1;

    // Analysis data
    public $marginAnalysis = [];
    public $totalRevenue = 0;
    public $totalCost = 0;
    public $totalProfit = 0;
    public $overallMargin = 0;

    public function mount()
    {
        $this->resetAnalysis();
    }

    public function render()
    {
        $kliens = Klien::with('bahanBakuKliens')
            ->orderBy('nama')
            ->orderBy('cabang')
            ->get()
            ->groupBy('nama')
            ->map(function ($group) {
                return $group->map(function ($klien) {
                    $klien->display_name = $klien->nama . ' - ' . $klien->cabang;
                    $klien->unique_key = $klien->nama . '|' . $klien->cabang;
                    return $klien;
                });
            });

        return view('livewire.marketing.penawaran', [
            'kliens' => $kliens,
            'availableMaterials' => $this->getAvailableMaterials(),
        ]);
    }

    public function selectKlien($uniqueKey)
    {
        [$klienNama, $klienCabang] = explode('|', $uniqueKey);
        $this->selectedKlien = $klienNama;
        $this->selectedKlienCabang = $klienCabang;
        $this->resetAnalysis();
        $this->selectedMaterials = [];
    }

    public function openAddMaterialModal()
    {
        if (!$this->selectedKlien || !$this->selectedKlienCabang) {
            session()->flash('error', 'Pilih klien terlebih dahulu');
            return;
        }

        $this->showAddMaterialModal = true;
        $this->currentMaterial = null;
        $this->currentQuantity = 1;
    }

    public function closeAddMaterialModal()
    {
        $this->showAddMaterialModal = false;
        $this->currentMaterial = null;
        $this->currentQuantity = 1;
    }

    public function addMaterial()
    {
        if (!$this->currentMaterial || !$this->currentQuantity) {
            return;
        }

        // Check if material already exists
        $exists = collect($this->selectedMaterials)->contains('material_id', $this->currentMaterial);
        if ($exists) {
            session()->flash('error', 'Material sudah ditambahkan');
            return;
        }

        // Get material data
        $material = BahanBakuKlien::with('klien')
            ->where('id', $this->currentMaterial)
            ->whereHas('klien', function($q) {
                $q->where('nama', $this->selectedKlien)
                  ->where('cabang', $this->selectedKlienCabang);
            })
            ->first();

        if (!$material) {
            session()->flash('error', 'Material tidak ditemukan untuk klien ini');
            return;
        }

        $this->selectedMaterials[] = [
            'id' => uniqid(),
            'material_id' => $material->id,
            'nama' => $material->nama,
            'satuan' => $material->satuan,
            'quantity' => $this->currentQuantity,
            'klien_price' => $material->harga_approved ?? 0,
        ];

        $this->calculateMarginAnalysis();
        $this->closeAddMaterialModal();
        session()->flash('message', 'Material berhasil ditambahkan');
    }

    public function removeMaterial($index)
    {
        unset($this->selectedMaterials[$index]);
        $this->selectedMaterials = array_values($this->selectedMaterials);
        $this->calculateMarginAnalysis();
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity > 0) {
            $this->selectedMaterials[$index]['quantity'] = $quantity;
            $this->calculateMarginAnalysis();
        }
    }

    public function calculateMarginAnalysis()
    {
        $this->marginAnalysis = [];
        $this->totalRevenue = 0;
        $this->totalCost = 0;

        foreach ($this->selectedMaterials as $material) {
            $revenue = $material['klien_price'] * $material['quantity'];

            // Get best supplier price for this material
            $bestSupplier = $this->getBestSupplierPrice($material['nama']);
            $cost = $bestSupplier['price'] * $material['quantity'];
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            // Get price history for both client and supplier
            $klienPriceHistory = $this->getKlienPriceHistory($material['material_id']);
            $supplierPriceHistory = $this->getSupplierPriceHistory($material['nama']);

            $this->marginAnalysis[] = [
                'nama' => $material['nama'],
                'satuan' => $material['satuan'],
                'quantity' => $material['quantity'],
                'klien_price' => $material['klien_price'],
                'revenue' => $revenue,
                'best_supplier' => $bestSupplier['supplier'],
                'supplier_price' => $bestSupplier['price'],
                'cost' => $cost,
                'profit' => $profit,
                'margin_percent' => $margin,
                'klien_price_history' => $klienPriceHistory,
                'supplier_price_history' => $supplierPriceHistory,
            ];

            $this->totalRevenue += $revenue;
            $this->totalCost += $cost;
        }

        $this->totalProfit = $this->totalRevenue - $this->totalCost;
        $this->overallMargin = $this->totalRevenue > 0 ? ($this->totalProfit / $this->totalRevenue) * 100 : 0;

        // Dispatch event to update charts when analysis is recalculated
        $this->dispatch('margin-analysis-updated', [
            'analysisData' => $this->marginAnalysis
        ]);
    }

    private function getBestSupplierPrice($materialName)
    {
        // Find the best supplier price for materials with matching names
        $suppliers = Supplier::with(['bahanBakuSuppliers' => function($q) use ($materialName) {
            $q->where('nama', 'like', '%' . $materialName . '%')
              ->whereNotNull('harga_per_satuan')
              ->orderBy('harga_per_satuan', 'asc');
        }])->get();

        $bestPrice = PHP_INT_MAX;
        $bestSupplierName = 'N/A';

        foreach ($suppliers as $supplier) {
            foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
                if ($bahanBaku->harga_per_satuan < $bestPrice) {
                    $bestPrice = $bahanBaku->harga_per_satuan;
                    $bestSupplierName = $supplier->nama;
                }
            }
        }

        return [
            'supplier' => $bestSupplierName,
            'price' => $bestPrice === PHP_INT_MAX ? 0 : $bestPrice,
        ];
    }

    private function getKlienPriceHistory($materialId)
    {
        // Get client price history from RiwayatHargaKlien for the specific material
        $klienMaterial = BahanBakuKlien::with(['riwayatHarga' => function($q) {
            $q->orderBy('tanggal_perubahan', 'asc')->limit(30); // Last 30 records
        }])
            ->where('id', $materialId)
            ->first();

        if (!$klienMaterial || !$klienMaterial->riwayatHarga) {
            return [];
        }

        return $klienMaterial->riwayatHarga->map(function($history) {
            return [
                'tanggal' => $history->tanggal_perubahan->format('Y-m-d'),
                'harga' => (float) $history->harga_approved_baru,
                'formatted_tanggal' => $history->tanggal_perubahan->format('d M'),
            ];
        })->toArray();
    }

    private function getSupplierPriceHistory($materialName)
    {
        // Get supplier price history from materials with matching names
        $supplierMaterial = \App\Models\BahanBakuSupplier::with(['riwayatHarga' => function($q) {
            $q->orderBy('tanggal_perubahan', 'asc')->limit(30); // Last 30 records
        }])
            ->where('nama', 'like', '%' . $materialName . '%')
            ->whereNotNull('harga_per_satuan')
            ->orderBy('harga_per_satuan', 'asc')
            ->first();

        if (!$supplierMaterial || !$supplierMaterial->riwayatHarga) {
            return [];
        }

        return $supplierMaterial->riwayatHarga->map(function($history) {
            return [
                'tanggal' => $history->tanggal_perubahan->format('Y-m-d'),
                'harga' => (float) $history->harga_baru,
                'formatted_tanggal' => $history->tanggal_perubahan->format('d M'),
            ];
        })->toArray();
    }

    private function getAvailableMaterials()
    {
        if (!$this->selectedKlien || !$this->selectedKlienCabang) {
            return collect();
        }

        return BahanBakuKlien::with('klien')
            ->whereHas('klien', function($q) {
                $q->where('nama', $this->selectedKlien)
                  ->where('cabang', $this->selectedKlienCabang);
            })
            ->where('status', 'aktif')
            ->whereNotNull('harga_approved')
            ->get();
    }

    public function exportPdf()
    {
        if (empty($this->selectedMaterials)) {
            session()->flash('error', 'Tidak ada data untuk diekspor');
            return;
        }

        // Generate PDF content
        $pdfData = [
            'klien' => $this->selectedKlien . ' - ' . $this->selectedKlienCabang,
            'analysis' => $this->marginAnalysis,
            'summary' => [
                'total_revenue' => $this->totalRevenue,
                'total_cost' => $this->totalCost,
                'total_profit' => $this->totalProfit,
                'overall_margin' => $this->overallMargin,
            ],
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];

        // For now, download as JSON (can be replaced with actual PDF library)
        $fileName = 'analisis-penawaran-' . str_replace(' ', '-', strtolower($this->selectedKlien)) . '-' . now()->format('Y-m-d-H-i-s') . '.json';

        session()->flash('message', 'Analisis berhasil diekspor sebagai ' . $fileName);

        // In a real implementation, you would use a PDF library like DomPDF or wkhtmltopdf
        $this->dispatch('download-analysis', $pdfData, $fileName);
    }

    public function buatOrder()
    {
        if (empty($this->selectedMaterials)) {
            session()->flash('error', 'Tidak ada material untuk membuat order');
            return;
        }

        // Create purchase order based on analysis
        $orderData = [];

        foreach ($this->marginAnalysis as $analysis) {
            $orderData[] = [
                'material' => $analysis['nama'],
                'quantity' => $analysis['quantity'],
                'supplier' => $analysis['best_supplier'],
                'price' => $analysis['supplier_price'],
                'total_cost' => $analysis['cost'],
                'expected_revenue' => $analysis['revenue'],
                'expected_profit' => $analysis['profit'],
            ];
        }

        // Store order data in session for order creation page
        session(['pending_order' => [
            'klien' => $this->selectedKlien . ' - ' . $this->selectedKlienCabang,
            'materials' => $orderData,
            'total_cost' => $this->totalCost,
            'expected_revenue' => $this->totalRevenue,
            'expected_profit' => $this->totalProfit,
            'created_from_analysis' => true,
        ]]);

        session()->flash('message', 'Order siap dibuat. Data telah disimpan untuk proses selanjutnya.');

        // Redirect to order creation page (when implemented)
        // return redirect()->route('order.create');
    }

    private function resetAnalysis()
    {
        $this->marginAnalysis = [];
        $this->totalRevenue = 0;
        $this->totalCost = 0;
        $this->totalProfit = 0;
        $this->overallMargin = 0;
    }
}