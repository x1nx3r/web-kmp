<?php

namespace App\Livewire\Marketing;

use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\Supplier;
use App\Models\BahanBakuSupplier;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\AuthFallbackService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class OrderCreate extends Component
{
    public $selectedKlien = null;
    public $selectedKlienCabang = null;
    public $selectedKlienId = null;
    public $selectedOrderItems = [];
    
    // Order info
    public $tanggalOrder;
    public $priority = 'normal';
    public $catatan = '';
    
    // Search and filter properties
    public $klienSearch = "";
    public $klienSort = "nama_asc";
    public $selectedKota = "";
    
    // Modal state
    public $showAddItemModal = false;
    public $currentMaterial = null;
    public $currentSupplier = null;
    public $currentQuantity = 1;
    public $currentSatuan = '';
    public $currentHargaSupplier = 0;
    public $currentHargaJual = 0;
    public $currentSpesifikasi = '';
    public $currentCatatan = '';
    
    // Totals
    public $totalAmount = 0;
    public $totalMargin = 0;
    
    public function mount()
    {
        $this->tanggalOrder = now()->format('Y-m-d');
        $this->resetTotals();
    }
    
    public function render()
    {
        $query = Klien::with("bahanBakuKliens");

        // Apply search filter
        if ($this->klienSearch) {
            $query->where(function ($q) {
                $q->where("nama", "like", "%" . $this->klienSearch . "%")
                    ->orWhere("cabang", "like", "%" . $this->klienSearch . "%")
                    ->orWhere("no_hp", "like", "%" . $this->klienSearch . "%");
            });
        }

        // Apply city filter
        if ($this->selectedKota) {
            $query->where("cabang", $this->selectedKota);
        }

        // Apply sorting
        switch ($this->klienSort) {
            case "nama_asc":
                $query->orderBy("nama", "asc")->orderBy("cabang", "asc");
                break;
            case "nama_desc":
                $query->orderBy("nama", "desc")->orderBy("cabang", "desc");
                break;
            case "cabang_asc":
                $query->orderBy("cabang", "asc")->orderBy("nama", "asc");
                break;
            case "cabang_desc":
                $query->orderBy("cabang", "desc")->orderBy("nama", "desc");
                break;
            default:
                $query->orderBy("nama", "asc")->orderBy("cabang", "asc");
        }

        $kliens = $query
            ->get()
            ->groupBy("nama")
            ->map(function ($group) {
                return $group->map(function ($klien) {
                    $klien->display_name = $klien->nama . " - " . $klien->cabang;
                    $klien->unique_key = $klien->nama . "|" . $klien->cabang;
                    return $klien;
                });
            });

        // Get unique cities for filter dropdown
        $availableCities = Klien::select("cabang")
            ->distinct()
            ->orderBy("cabang")
            ->pluck("cabang");

        return view("livewire.marketing.order-create", [
            "kliens" => $kliens,
            "availableMaterials" => $this->availableMaterials,
            "availableCities" => $availableCities,
        ]);
    }
    
    public function selectKlien($uniqueKey)
    {
        [$klienNama, $klienCabang] = explode("|", $uniqueKey);
        $this->selectedKlien = $klienNama;
        $this->selectedKlienCabang = $klienCabang;

        // Get the actual Klien ID
        $klien = Klien::where("nama", $this->selectedKlien)
            ->where("cabang", $this->selectedKlienCabang)
            ->first();

        $this->selectedKlienId = $klien ? $klien->id : null;
        $this->selectedOrderItems = [];
        $this->resetTotals();
    }
    
    public function clearKlienSearch()
    {
        $this->klienSearch = "";
    }

    public function clearKotaFilter()
    {
        $this->selectedKota = "";
    }
    
    public function getAvailableMaterialsProperty()
    {
        if (!$this->selectedKlienId) {
            return collect();
        }

        return BahanBakuKlien::where('klien_id', $this->selectedKlienId)
            ->aktif()
            ->withApprovedPrice()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama, // Using 'nama' instead of 'nama_material'
                    'satuan' => $item->satuan,
                    'harga_approved' => $item->harga_approved,
                ];
            });
    }
    
    public function openAddItemModal()
    {
        if (!$this->selectedKlien || !$this->selectedKlienCabang) {
            session()->flash("error", "Pilih klien terlebih dahulu");
            return;
        }

        $this->showAddItemModal = true;
        $this->resetCurrentItem();
    }
    
    public function closeAddItemModal()
    {
        $this->showAddItemModal = false;
        $this->resetCurrentItem();
    }
    
    private function resetCurrentItem()
    {
        $this->currentMaterial = null;
        $this->currentSupplier = null;
        $this->currentQuantity = 1;
        $this->currentSatuan = '';
        $this->currentHargaSupplier = 0;
        $this->currentHargaJual = 0;
        $this->currentSpesifikasi = '';
        $this->currentCatatan = '';
    }
    
    public function selectMaterial($materialId)
    {
        $material = BahanBakuKlien::find($materialId);
        if ($material) {
            $this->currentMaterial = $materialId;
            $this->currentSatuan = $material->satuan;
            
            // Load suppliers for this material
            $this->loadSuppliersForMaterial($materialId);
        }
    }
    
    private function loadSuppliersForMaterial($materialId)
    {
        // Reset supplier selection
        $this->currentSupplier = null;
        $this->currentHargaSupplier = 0;
    }
    
    public function getSuppliers()
    {
        if (!$this->currentMaterial) {
            return collect();
        }
        
        $material = BahanBakuKlien::find($this->currentMaterial);
        if (!$material) {
            return collect();
        }
        
        // Get all suppliers that have this material with their prices - following penawaran pattern
        $suppliers = Supplier::with([
            'bahanBakuSuppliers' => function ($q) use ($material) {
                $q->where('nama', 'like', '%' . $material->nama . '%')
                    ->whereNotNull('harga_per_satuan')
                    ->orderBy('harga_per_satuan', 'asc');
            },
            'picPurchasing'
        ])->get();

        $supplierOptions = [];

        foreach ($suppliers as $supplier) {
            foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
                $supplierOptions[] = [
                    'supplier_id' => $bahanBaku->id, // Use BahanBakuSupplier ID like penawaran
                    'supplier_name' => $supplier->nama,
                    'pic_name' => $supplier->picPurchasing ? $supplier->picPurchasing->nama : null,
                    'harga_per_unit' => $bahanBaku->harga_per_satuan,
                    'satuan' => $bahanBaku->satuan,
                    'stok' => $bahanBaku->stok,
                ];
            }
        }

        // Sort by price (cheapest first) like penawaran
        usort($supplierOptions, function ($a, $b) {
            return $a['harga_per_unit'] <=> $b['harga_per_unit'];
        });

        return collect($supplierOptions);
    }
    
    private function getBestSupplierForMaterial($materialName)
    {
        // Find the best supplier price for materials with matching names - following penawaran pattern
        $suppliers = Supplier::with([
            'bahanBakuSuppliers' => function ($q) use ($materialName) {
                $q->where('nama', 'like', '%' . $materialName . '%')
                    ->whereNotNull('harga_per_satuan')
                    ->orderBy('harga_per_satuan', 'asc');
            },
            'picPurchasing'
        ])->get();

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
    
    public function selectSupplier($supplierId)
    {
        $this->currentSupplier = $supplierId;
        
        // Auto-fill supplier price
        $suppliers = $this->getSuppliers();
        $selectedSupplier = $suppliers->firstWhere('supplier_id', $supplierId);
        
        if ($selectedSupplier) {
            $this->currentHargaSupplier = $selectedSupplier['harga_per_unit'];
            
            // Auto-calculate selling price with default margin
            $this->currentHargaJual = $this->currentHargaSupplier * 1.2; // 20% markup
        }
    }
    
    public function addOrderItem()
    {
        $this->validate([
            'currentMaterial' => 'required',
            'currentSupplier' => 'required',
            'currentQuantity' => 'required|numeric|min:0.01',
            'currentSatuan' => 'required|string',
            'currentHargaSupplier' => 'required|numeric|min:0',
            'currentHargaJual' => 'required|numeric|min:0',
        ]);
        
        $material = BahanBakuKlien::find($this->currentMaterial);
        $bahanBakuSupplier = BahanBakuSupplier::with('supplier')->find($this->currentSupplier);
        
        if (!$material || !$bahanBakuSupplier) {
            session()->flash('error', 'Material atau supplier tidak ditemukan');
            return;
        }
        
        $totalHpp = $this->currentQuantity * $this->currentHargaSupplier;
        $totalHarga = $this->currentQuantity * $this->currentHargaJual;
        $marginPerUnit = $this->currentHargaJual - $this->currentHargaSupplier;
        $totalMargin = $this->currentQuantity * $marginPerUnit;
        $marginPercentage = $this->currentHargaJual > 0 ? ($marginPerUnit / $this->currentHargaJual) * 100 : 0;
        
        $this->selectedOrderItems[] = [
            'id' => uniqid(),
            'bahan_baku_klien_id' => $this->currentMaterial,
            'supplier_id' => $this->currentSupplier, // This is BahanBakuSupplier ID like penawaran
            'material_name' => $material->nama, // Correct field name
            'supplier_name' => $bahanBakuSupplier->supplier->nama,
            'qty' => $this->currentQuantity,
            'satuan' => $this->currentSatuan,
            'harga_supplier' => $this->currentHargaSupplier,
            'harga_jual' => $this->currentHargaJual,
            'total_hpp' => $totalHpp,
            'total_harga' => $totalHarga,
            'margin_per_unit' => $marginPerUnit,
            'total_margin' => $totalMargin,
            'margin_percentage' => $marginPercentage,
            'spesifikasi_khusus' => $this->currentSpesifikasi,
            'catatan' => $this->currentCatatan,
        ];
        
        $this->updateTotals();
        $this->closeAddItemModal();
        
        session()->flash('success', 'Item berhasil ditambahkan');
    }
    
    public function removeOrderItem($itemId)
    {
        $this->selectedOrderItems = array_filter($this->selectedOrderItems, function($item) use ($itemId) {
            return $item['id'] !== $itemId;
        });
        
        $this->selectedOrderItems = array_values($this->selectedOrderItems); // Re-index array
        $this->updateTotals();
        
        session()->flash('success', 'Item berhasil dihapus');
    }
    
    private function updateTotals()
    {
        $this->totalAmount = collect($this->selectedOrderItems)->sum('total_harga');
        $this->totalMargin = collect($this->selectedOrderItems)->sum('total_margin');
    }
    
    private function resetTotals()
    {
        $this->totalAmount = 0;
        $this->totalMargin = 0;
    }
    
    public function createOrder()
    {
        $this->validate([
            'selectedKlienId' => 'required',
            'tanggalOrder' => 'required|date',
            'priority' => 'required|in:rendah,normal,tinggi,mendesak',
            'selectedOrderItems' => 'required|min:1',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Get user ID with fallback
            $authService = new AuthFallbackService();
            $userId = $authService->getUserId();
            
            // Create order
            $order = Order::create([
                'klien_id' => $this->selectedKlienId,
                'user_id' => $userId,
                'tanggal_order' => $this->tanggalOrder,
                'priority' => $this->priority,
                'status' => 'draft',
                'catatan' => $this->catatan,
                'total_amount' => 0, // Will be calculated by model
                'total_margin' => 0, // Will be calculated by model
            ]);
            
            // Create order details
            foreach ($this->selectedOrderItems as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'bahan_baku_klien_id' => $item['bahan_baku_klien_id'],
                    'supplier_id' => $item['supplier_id'],
                    'qty' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_supplier' => $item['harga_supplier'],
                    'harga_jual' => $item['harga_jual'],
                    'spesifikasi_khusus' => $item['spesifikasi_khusus'],
                    'catatan' => $item['catatan'],
                    'status' => 'menunggu',
                ]);
            }
            
            DB::commit();
            
            session()->flash('success', 'Order berhasil dibuat dengan ID: ' . $order->id);
            
            return redirect()->route('orders.show', $order);
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Gagal membuat order: ' . $e->getMessage());
        }
    }
}
