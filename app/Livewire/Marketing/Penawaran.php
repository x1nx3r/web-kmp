<?php

namespace App\Livewire\Marketing;

use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\BahanBaku;
use App\Models\Supplier;
use App\Models\BahanBakuSupplier;
use App\Models\Penawaran as PenawaranModel;
use App\Models\PenawaranDetail;
use App\Models\PenawaranAlternativeSupplier;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Penawaran extends Component
{
    use WithPagination;

    public $selectedKlien = null;
    public $selectedKlienCabang = null;
    public $selectedKlienId = null;
    public $selectedMaterials = [];
    public $showAddMaterialModal = false;
    public $currentMaterial = null;
    public $currentQuantity = 1;
    public $useCustomPrice = false;
    public $customPrice = null;

    // Search and filter properties
    public $klienSearch = '';
    public $klienSort = 'nama_asc';
    public $selectedKota = '';

    // Analysis data
    public $marginAnalysis = [];
    public $totalRevenue = 0;
    public $totalCost = 0;
    public $totalProfit = 0;
    public $overallMargin = 0;

    // Supplier selection per material
    public $selectedSuppliers = [];

    public function mount()
    {
        $this->resetAnalysis();
    }

    public function render()
    {
        $query = Klien::with('bahanBakuKliens');

        // Apply search filter
        if ($this->klienSearch) {
            $query->where(function($q) {
                $q->where('nama', 'like', '%' . $this->klienSearch . '%')
                  ->orWhere('cabang', 'like', '%' . $this->klienSearch . '%')
                  ->orWhere('no_hp', 'like', '%' . $this->klienSearch . '%');
            });
        }

        // Apply city filter
        if ($this->selectedKota) {
            $query->where('cabang', $this->selectedKota);
        }

        // Apply sorting
        switch ($this->klienSort) {
            case 'nama_asc':
                $query->orderBy('nama', 'asc')->orderBy('cabang', 'asc');
                break;
            case 'nama_desc':
                $query->orderBy('nama', 'desc')->orderBy('cabang', 'desc');
                break;
            case 'kota_asc':
                $query->orderBy('cabang', 'asc')->orderBy('nama', 'asc');
                break;
            case 'kota_desc':
                $query->orderBy('cabang', 'desc')->orderBy('nama', 'desc');
                break;
            case 'cabang_asc':
                $query->orderBy('cabang', 'asc')->orderBy('nama', 'asc');
                break;
            case 'cabang_desc':
                $query->orderBy('cabang', 'desc')->orderBy('nama', 'desc');
                break;
            default:
                $query->orderBy('nama', 'asc')->orderBy('cabang', 'asc');
        }

        $kliens = $query->get()
            ->groupBy('nama')
            ->map(function ($group) {
                return $group->map(function ($klien) {
                    $klien->display_name = $klien->nama . ' - ' . $klien->cabang;
                    $klien->unique_key = $klien->nama . '|' . $klien->cabang;
                    return $klien;
                });
            });;

        // Get unique cities for filter dropdown
        $availableCities = Klien::select('cabang')
            ->distinct()
            ->orderBy('cabang')
            ->pluck('cabang');

        return view('livewire.marketing.penawaran', [
            'kliens' => $kliens,
            'availableMaterials' => $this->getAvailableMaterials(),
            'availableCities' => $availableCities,
        ]);
    }

    public function selectKlien($uniqueKey)
    {
        [$klienNama, $klienCabang] = explode('|', $uniqueKey);
        $this->selectedKlien = $klienNama;
        $this->selectedKlienCabang = $klienCabang;
        
        // Get the actual Klien ID
        $klien = Klien::where('nama', $this->selectedKlien)
                     ->where('cabang', $this->selectedKlienCabang)
                     ->first();
        
        $this->selectedKlienId = $klien ? $klien->id : null;
        $this->resetAnalysis();
        $this->selectedMaterials = [];
        $this->selectedSuppliers = [];
    }

    public function clearKlienSearch()
    {
        $this->klienSearch = '';
    }

    public function clearKotaFilter()
    {
        $this->selectedKota = '';
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
        $this->useCustomPrice = false;
        $this->customPrice = null;
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

        // Determine final price (custom or default)
        $finalPrice = ($this->useCustomPrice && $this->customPrice > 0) 
            ? $this->customPrice 
            : $material->harga_approved ?? 0;

        $this->selectedMaterials[] = [
            'id' => uniqid(),
            'material_id' => $material->id,
            'nama' => $material->nama,
            'satuan' => $material->satuan,
            'quantity' => $this->currentQuantity,
            'klien_price' => $finalPrice,
            'is_custom_price' => $this->useCustomPrice,
            'custom_price' => $this->useCustomPrice ? $this->customPrice : null,
            'original_price' => $material->harga_approved ?? 0,
        ];

        $this->refreshAnalysis();
        $this->closeAddMaterialModal();
        session()->flash('message', 'Material berhasil ditambahkan');
    }

    public function removeMaterial($index)
    {
        unset($this->selectedMaterials[$index]);
        $this->selectedMaterials = array_values($this->selectedMaterials);
        $this->refreshAnalysis();
    }

    public function refreshAnalysis()
    {
        // Recalculate margin analysis
        $this->calculateMarginAnalysis();
        
        // Dispatch event to update charts
        $this->dispatch('chart-data-updated');
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity > 0) {
            $this->selectedMaterials[$index]['quantity'] = $quantity;
            $this->refreshAnalysis();
        }
    }

    public function updatedSelectedSuppliers()
    {
        // Recalculate totals when supplier selection changes
        $this->recalculateTotals();
        
        // Dispatch event to update charts with current margin analysis data
        $this->dispatch('margin-analysis-updated', [
            'analysisData' => $this->marginAnalysis
        ]);
    }

    private function recalculateTotals()
    {
        $this->totalRevenue = 0;
        $this->totalCost = 0;

        foreach ($this->marginAnalysis as $index => $analysis) {
            // Get selected supplier for this material
            $selectedSupplierId = $this->selectedSuppliers[$index] ?? $analysis['best_supplier_id'];
            
            // Find the selected supplier's data
            $selectedSupplier = collect($analysis['supplier_options'])->firstWhere('supplier_id', $selectedSupplierId);
            
            if ($selectedSupplier) {
                $this->totalRevenue += $analysis['revenue'];
                $this->totalCost += $selectedSupplier['cost'];
            }
        }

        $this->totalProfit = $this->totalRevenue - $this->totalCost;
        $this->overallMargin = $this->totalRevenue > 0 ? ($this->totalProfit / $this->totalRevenue) * 100 : 0;
    }

    public function calculateMarginAnalysis()
    {
        $this->marginAnalysis = [];
        $this->totalRevenue = 0;
        $this->totalCost = 0;

        foreach ($this->selectedMaterials as $index => $material) {
            // Use custom price if available, otherwise use client price
            $finalPrice = $material['custom_price'] ?? $material['klien_price'];
            $revenue = $finalPrice * $material['quantity'];

            // Get all suppliers for this material with their prices and margins
            $allSuppliersData = $this->getAllSuppliersForMaterial($material['nama']);
            $bestSupplier = $this->getBestSupplierPrice($material['nama']);
            
            $cost = $bestSupplier['price'] * $material['quantity'];
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            // Get price history for both client and best supplier
            $klienPriceHistory = $this->getKlienPriceHistory($material['material_id']);
            $supplierPriceHistory = $this->getSupplierPriceHistory($material['nama']);

            // Calculate margin analysis for each supplier
            $supplierMargins = [];
            foreach ($allSuppliersData as $supplierData) {
                $supplierCost = $supplierData['price'] * $material['quantity'];
                $supplierProfit = $revenue - $supplierCost;
                $supplierMargin = $revenue > 0 ? ($supplierProfit / $revenue) * 100 : 0;
                
                $supplierMargins[] = [
                    'supplier_name' => $supplierData['supplier'],
                    'pic_name' => $supplierData['pic_name'],
                    'supplier_id' => $supplierData['supplier_id'],
                    'price' => $supplierData['price'],
                    'cost' => $supplierCost,
                    'profit' => $supplierProfit,
                    'margin_percent' => $supplierMargin,
                    'is_best' => $supplierData['supplier'] === $bestSupplier['supplier'],
                    'price_history' => $this->getSupplierSpecificPriceHistory($supplierData['supplier_id'])
                ];
            }

            // Initialize selected supplier for this material if not set (default to best)
            if (!isset($this->selectedSuppliers[$index])) {
                $this->selectedSuppliers[$index] = $bestSupplier['supplier_id'];
            }

            $this->marginAnalysis[] = [
                'nama' => $material['nama'],
                'satuan' => $material['satuan'],
                'quantity' => $material['quantity'],
                'material_id' => $material['material_id'], // BahanBakuKlien ID
                'klien_price' => $material['klien_price'],
                'is_custom_price' => $material['is_custom_price'] ?? false,
                'custom_price' => $material['custom_price'] ?? null,
                'original_price' => $material['original_price'] ?? $material['klien_price'],
                'revenue' => $revenue,
                'best_supplier' => $bestSupplier['supplier'],
                'best_supplier_pic' => $bestSupplier['pic_name'],
                'best_supplier_id' => $bestSupplier['supplier_id'],
                'supplier_price' => $bestSupplier['price'],
                'cost' => $cost,
                'profit' => $profit,
                'margin_percent' => $margin,
                'supplier_options' => $supplierMargins,
                'klien_price_history' => $klienPriceHistory,
                'supplier_price_history' => $supplierPriceHistory,
            ];

            $this->totalRevenue += $revenue;
            
            // Use selected supplier cost if available, otherwise use best supplier
            $selectedSupplierId = $this->selectedSuppliers[$index] ?? $bestSupplier['supplier_id'];
            $selectedSupplier = collect($supplierMargins)->firstWhere('supplier_id', $selectedSupplierId);
            $this->totalCost += $selectedSupplier ? $selectedSupplier['cost'] : $cost;
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
        }, 'picPurchasing'])->get();

        $bestPrice = PHP_INT_MAX;
        $bestSupplierName = 'N/A';
        $bestSupplierId = null;
        $bestPicName = null;

        foreach ($suppliers as $supplier) {
            foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
                if ($bahanBaku->harga_per_satuan < $bestPrice) {
                    $bestPrice = $bahanBaku->harga_per_satuan;
                    $bestSupplierName = $supplier->nama;
                    $bestSupplierId = $bahanBaku->id;
                    $bestPicName = $supplier->picPurchasing ? $supplier->picPurchasing->nama : null;
                }
            }
        }

        return [
            'supplier' => $bestSupplierName,
            'supplier_id' => $bestSupplierId,
            'pic_name' => $bestPicName,
            'price' => $bestPrice === PHP_INT_MAX ? 0 : $bestPrice,
        ];
    }

    private function getAllSuppliersForMaterial($materialName)
    {
        // Get all suppliers that have this material with their prices
        $suppliers = Supplier::with(['bahanBakuSuppliers' => function($q) use ($materialName) {
            $q->where('nama', 'like', '%' . $materialName . '%')
              ->whereNotNull('harga_per_satuan')
              ->orderBy('harga_per_satuan', 'asc');
        }, 'picPurchasing'])->get();

        $supplierOptions = [];

        foreach ($suppliers as $supplier) {
            foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
                $supplierOptions[] = [
                    'supplier' => $supplier->nama,
                    'pic_name' => $supplier->picPurchasing ? $supplier->picPurchasing->nama : null,
                    'supplier_id' => $bahanBaku->id,
                    'price' => $bahanBaku->harga_per_satuan,
                    'satuan' => $bahanBaku->satuan,
                    'stok' => $bahanBaku->stok,
                ];
            }
        }

        // Sort by price (cheapest first)
        usort($supplierOptions, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });

        return $supplierOptions;
    }

    private function getSupplierSpecificPriceHistory($bahanBakuSupplierId)
    {
        // Get price history for a specific supplier material
        $supplierMaterial = \App\Models\BahanBakuSupplier::with(['riwayatHarga' => function($q) {
            $q->orderBy('tanggal_perubahan', 'asc')->limit(30); // Last 30 records
        }])
            ->where('id', $bahanBakuSupplierId)
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

    private function getKlienPriceHistory($materialId)
    {
        // Get client price history from RiwayatHargaKlien for the specific material
        $klienMaterial = BahanBakuKlien::with(['riwayatHarga' => function($q) {
            $q->orderBy('tanggal_perubahan', 'asc')->limit(30); // Last 30 records
        }])
            ->where('id', $materialId)
            ->first();

        $priceHistory = [];
        
        if ($klienMaterial && $klienMaterial->riwayatHarga) {
            $priceHistory = $klienMaterial->riwayatHarga->map(function($history) {
                return [
                    'tanggal' => $history->tanggal_perubahan->format('Y-m-d'),
                    'harga' => (float) $history->harga_approved_baru,
                    'formatted_tanggal' => $history->tanggal_perubahan->format('d M'),
                    'is_custom' => false,
                ];
            })->toArray();
        }
        
        // Check if this material has a custom price and add today's data point
        $selectedMaterial = collect($this->selectedMaterials)->firstWhere('material_id', $materialId);
        if ($selectedMaterial && ($selectedMaterial['is_custom_price'] ?? false)) {
            $today = now();
            $priceHistory[] = [
                'tanggal' => $today->format('Y-m-d'),
                'harga' => (float) $selectedMaterial['custom_price'],
                'formatted_tanggal' => $today->format('d M'),
                'is_custom' => true,
            ];
        }
        
        return $priceHistory;
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

    public function saveDraft()
    {
        return $this->savePenawaran('draft');
    }

    public function submitForVerification()
    {
        return $this->savePenawaran('menunggu_verifikasi');
    }

    private function savePenawaran($status = 'draft')
    {
        // Validation
        if (empty($this->selectedMaterials)) {
            session()->flash('error', 'Tidak ada material untuk membuat penawaran');
            return;
        }

        if (!$this->selectedKlienId) {
            session()->flash('error', 'Klien belum dipilih');
            return;
        }

        try {
            DB::beginTransaction();

            // Create Penawaran record
            $penawaran = PenawaranModel::create([
                'klien_id' => $this->selectedKlienId,
                'status' => $status,
                'tanggal_penawaran' => now(),
                'total_harga_klien' => $this->totalRevenue,
                'total_harga_supplier' => $this->totalCost,
                'total_profit' => $this->totalProfit,
                'margin_persen' => $this->overallMargin,
                'created_by' => auth()->id(),
            ]);

            // Create PenawaranDetail records with alternative suppliers
            foreach ($this->marginAnalysis as $index => $analysis) {
                // Get selected supplier for this material
                $selectedSupplierId = $this->selectedSuppliers[$index] ?? $analysis['best_supplier_id'];
                
                // Find the selected supplier's data
                $selectedSupplierData = collect($analysis['supplier_options'])->firstWhere('supplier_id', $selectedSupplierId);
                
                if (!$selectedSupplierData) {
                    // Fallback to best supplier if not found
                    $selectedSupplierData = collect($analysis['supplier_options'])->firstWhere('is_best', true);
                    $selectedSupplierId = $selectedSupplierData['supplier_id'];
                }

                // Get BahanBakuSupplier record to find the actual Supplier ID
                $bahanBakuSupplier = BahanBakuSupplier::find($selectedSupplierId);
                
                if (!$bahanBakuSupplier) {
                    throw new \Exception("Bahan baku supplier tidak ditemukan untuk {$analysis['nama']}");
                }

                // Create detail record
                $detail = PenawaranDetail::create([
                    'penawaran_id' => $penawaran->id,
                    'bahan_baku_klien_id' => $analysis['material_id'],
                    'supplier_id' => $bahanBakuSupplier->supplier_id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'quantity' => $analysis['quantity'],
                    'harga_per_satuan' => $analysis['klien_price'],
                    'harga_supplier' => $selectedSupplierData['price'],
                    'subtotal_harga_klien' => $analysis['revenue'],
                    'subtotal_harga_supplier' => $selectedSupplierData['cost'],
                    'subtotal_profit' => $selectedSupplierData['profit'],
                    'margin_persen' => $selectedSupplierData['margin_percent'],
                ]);

                // Save alternative suppliers (excluding the selected one)
                foreach ($analysis['supplier_options'] as $supplierOption) {
                    if ($supplierOption['supplier_id'] != $selectedSupplierId) {
                        // Get BahanBakuSupplier for alternative
                        $altBahanBakuSupplier = BahanBakuSupplier::find($supplierOption['supplier_id']);

                        if ($altBahanBakuSupplier) {
                            PenawaranAlternativeSupplier::create([
                                'penawaran_detail_id' => $detail->id,
                                'supplier_id' => $altBahanBakuSupplier->supplier_id,
                                'bahan_baku_supplier_id' => $altBahanBakuSupplier->id,
                                'harga_per_satuan' => $supplierOption['price'],
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            // Success message
            $statusText = $status === 'draft' ? 'draft' : 'verifikasi';
            session()->flash('message', "Penawaran {$penawaran->nomor_penawaran} berhasil disimpan sebagai {$statusText}");

            // Redirect to history page
            return redirect()->route('penawaran.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan penawaran: ' . $e->getMessage());
            return;
        }
    }

    public function resetForm()
    {
        $this->selectedKlien = null;
        $this->selectedKlienCabang = null;
        $this->selectedKlienId = null;
        $this->selectedMaterials = [];
        $this->selectedSuppliers = [];
        $this->resetAnalysis();
        
        session()->flash('message', 'Form berhasil direset');
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