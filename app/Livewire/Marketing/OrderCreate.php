<?php

namespace App\Livewire\Marketing;

use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\Supplier;
use App\Models\BahanBakuSupplier;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\AuthFallbackService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderCreate extends Component
{
    use WithFileUploads;

    public $selectedKlien = null;
    public $selectedKlienCabang = null;
    public $selectedKlienId = null;
    public $selectedOrderItems = [];

    // Mode state
    public bool $isEditing = false;
    public ?int $editingOrderId = null;
    public ?string $editingOrderNumber = null;
    public ?string $currentStatus = 'draft';

    // Existing PO metadata (edit mode)
    public ?string $existingPoDocumentPath = null;
    public ?string $existingPoDocumentName = null;
    public ?string $existingPoDocumentUrl = null;
    
    // Order info
    public $tanggalOrder;
    public $poNumber = '';
    public $poStartDate;
    public $poEndDate;
    public $poDocument;
    public $priority = 'normal';
    public $catatan = '';
    
    // Search and filter properties
    public $klienSearch = "";
    public $klienSort = "nama_asc";
    public $selectedKota = "";
    
    // Modal state
    public $showAddItemModal = false;
    public $currentMaterial = null;
    public $currentQuantity = 1;
    public $currentSatuan = '';
    public $currentHargaJual = 0;
    public $currentSpesifikasi = '';
    public $currentCatatan = '';
    
    // Auto-populated supplier data (read-only)
    public $autoSuppliers = [];
    public $bestMargin = 0;
    public $recommendedPrice = 0;
    
    // Totals
    public $totalAmount = 0;
    public $totalMargin = 0;
    
    public function mount(?Order $order = null)
    {
        $this->tanggalOrder = now()->format('Y-m-d');
        $this->poStartDate = $this->tanggalOrder;
        $this->poEndDate = now()->addDays(14)->format('Y-m-d');
        $this->resetTotals();
        $this->updatePriorityFromSchedule();
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

        $this->poNumber = '';
        $this->poDocument = null;
        if ($this->isEditing) {
            $this->existingPoDocumentPath = null;
            $this->existingPoDocumentName = null;
            $this->existingPoDocumentUrl = null;
        }
        $this->poStartDate = $this->tanggalOrder;
        $this->poEndDate = now()->addDays(14)->format('Y-m-d');
        $this->updatePriorityFromSchedule();
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
        $this->currentQuantity = 1;
        $this->currentSatuan = '';
        $this->currentHargaJual = 0;
        $this->currentSpesifikasi = '';
        $this->currentCatatan = '';
        $this->autoSuppliers = [];
        $this->bestMargin = 0;
        $this->recommendedPrice = 0;
    }
    
    public function selectMaterial($materialId)
    {
        $material = BahanBakuKlien::find($materialId);
        if ($material) {
            $this->currentMaterial = $materialId;
            $this->currentSatuan = $material->satuan;
            
            // Auto-populate all suppliers for this material
            $this->autoPopulateSuppliers($material);
        }
    }
    
    private function autoPopulateSuppliers($material)
    {
        // Get all suppliers for this material using name matching (like in OrderDetail model)
        $suppliers = Supplier::with([
            'bahanBakuSuppliers' => function ($q) use ($material) {
                $q->where('nama', 'like', '%' . $material->nama . '%')
                    ->whereNotNull('harga_per_satuan')
                    ->where('harga_per_satuan', '>', 0)
                    ->orderBy('harga_per_satuan', 'asc');
            },
            'picPurchasing'
        ])->get();

        $this->autoSuppliers = [];
        $bestPrice = PHP_INT_MAX;

        foreach ($suppliers as $supplier) {
            foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
                // Calculate margin with a default selling price (20% markup)
                $suggestedPrice = $bahanBaku->harga_per_satuan * 1.2;
                $margin = ($suggestedPrice - $bahanBaku->harga_per_satuan) / $suggestedPrice * 100;
                
                $this->autoSuppliers[] = [
                    'supplier_id' => $supplier->id,
                    'bahan_baku_supplier_id' => $bahanBaku->id,
                    'supplier_name' => $supplier->nama,
                    'supplier_location' => $supplier->alamat ?? 'Address not specified',
                    'pic_name' => $supplier->picPurchasing ? $supplier->picPurchasing->nama : null,
                    'material_name' => $bahanBaku->nama,
                    'harga_supplier' => $bahanBaku->harga_per_satuan,
                    'satuan' => $bahanBaku->satuan,
                    'stok' => $bahanBaku->stok ?? 0,
                    'suggested_price' => $suggestedPrice,
                    'margin_percentage' => $margin,
                    'is_recommended' => false, // Will set best one later
                ];

                if ($bahanBaku->harga_per_satuan < $bestPrice) {
                    $bestPrice = $bahanBaku->harga_per_satuan;
                }
            }
        }

        // Sort by price and mark the best one as recommended
        usort($this->autoSuppliers, function ($a, $b) {
            return $a['harga_supplier'] <=> $b['harga_supplier'];
        });

        // Mark the cheapest supplier as recommended and calculate suggested selling price
        if (!empty($this->autoSuppliers)) {
            $this->autoSuppliers[0]['is_recommended'] = true;
            $this->recommendedPrice = $this->autoSuppliers[0]['suggested_price'];
            $this->bestMargin = $this->autoSuppliers[0]['margin_percentage'];
            
            // Set current selling price to recommended price
            $this->currentHargaJual = $this->recommendedPrice;
        }
    }
    
    protected function updateTotals()
    {
        $this->totalAmount = collect($this->selectedOrderItems)->sum('total_harga');
        $this->totalMargin = collect($this->selectedOrderItems)->sum('total_margin');
    }
    
    protected function resetTotals()
    {
        $this->totalAmount = 0;
        $this->totalMargin = 0;
    }

    public function updatedPoStartDate()
    {
        $this->updatePriorityFromSchedule();
    }

    public function updatedPoEndDate()
    {
        $this->updatePriorityFromSchedule();
    }

    public function updatedPoDocument()
    {
        if ($this->poDocument) {
            $this->validateOnly('poDocument', [
                'poDocument' => 'file|mimes:jpg,jpeg,png|max:5120',
            ]);
        }
    }

    private function updatePriorityFromSchedule(): void
    {
        if (!$this->poEndDate) {
            return;
        }

        $end = Carbon::parse($this->poEndDate);
        $days = now()->diffInDays($end, false);

        if ($days <= 3) {
            $this->priority = 'mendesak';
        } elseif ($days <= 7) {
            $this->priority = 'tinggi';
        } elseif ($days <= 14) {
            $this->priority = 'normal';
        } else {
            $this->priority = 'rendah';
        }
    }

    public function getCanSubmitProperty(): bool
    {
        if ($this->isEditing) {
            return (bool) (
                $this->selectedKlienId
                && count($this->selectedOrderItems) > 0
                && !empty($this->poNumber)
                && !empty($this->poStartDate)
                && !empty($this->poEndDate)
            );
        }

        return (bool) (
            $this->selectedKlienId
            && count($this->selectedOrderItems) > 0
            && !empty($this->poNumber)
            && !empty($this->poStartDate)
            && !empty($this->poEndDate)
            && $this->poDocument
        );
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
            'currentQuantity' => 'required|numeric|min:0.01',
            'currentSatuan' => 'required|string',
            'currentHargaJual' => 'required|numeric|min:0',
        ]);
        
        $material = BahanBakuKlien::find($this->currentMaterial);
        
        if (!$material) {
            session()->flash('error', 'Material tidak ditemukan');
            return;
        }

        if (empty($this->autoSuppliers)) {
            session()->flash('error', 'Tidak ada supplier tersedia untuk material ini');
            return;
        }
        
        $totalHarga = $this->currentQuantity * $this->currentHargaJual;
        
        // Calculate best margin from available suppliers
        $bestSupplier = collect($this->autoSuppliers)->sortBy('harga_supplier')->first();
        $bestHpp = $this->currentQuantity * $bestSupplier['harga_supplier'];
        $totalMargin = $totalHarga - $bestHpp;
        $marginPercentage = $totalHarga > 0 ? ($totalMargin / $totalHarga) * 100 : 0;
        
        $this->selectedOrderItems[] = [
            'id' => uniqid(),
            'bahan_baku_klien_id' => $this->currentMaterial,
            'material_name' => $material->nama,
            'qty' => $this->currentQuantity,
            'satuan' => $this->currentSatuan,
            'harga_jual' => $this->currentHargaJual,
            'total_harga' => $totalHarga,
            'best_supplier_price' => $bestSupplier['harga_supplier'],
            'best_hpp' => $bestHpp,
            'total_margin' => $totalMargin,
            'margin_percentage' => $marginPercentage,
            'suppliers_count' => count($this->autoSuppliers),
            'spesifikasi_khusus' => $this->currentSpesifikasi,
            'catatan' => $this->currentCatatan,
            'auto_suppliers' => $this->autoSuppliers, // Store all supplier options
            'recommended_bahan_baku_supplier_id' => $bestSupplier['bahan_baku_supplier_id'] ?? null,
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

    public function createOrder()
    {
        $this->validate([
            'selectedKlienId' => 'required',
            'tanggalOrder' => 'required|date',
            'poNumber' => 'required|string|max:50',
            'poStartDate' => 'required|date',
            'poEndDate' => 'required|date|after_or_equal:poStartDate',
            'poDocument' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'priority' => 'required|in:rendah,normal,tinggi,mendesak',
            'selectedOrderItems' => 'required|min:1',
        ]);
        
        $this->updatePriorityFromSchedule();

        try {
            DB::beginTransaction();
            
            // Get user ID with fallback (hardcoded bypass for now)
            $userId = AuthFallbackService::id() ?? 1; // Fallback to user ID 1 if auth fails

            $poDocumentPath = null;
            $poOriginalName = null;

            if ($this->poDocument) {
                $poOriginalName = $this->poDocument->getClientOriginalName();
                $extension = $this->poDocument->getClientOriginalExtension();
                $baseName = pathinfo($poOriginalName, PATHINFO_FILENAME);
                $safeBaseName = Str::slug($baseName);
                if ($safeBaseName === '') {
                    $safeBaseName = 'po-document';
                }
                $fileName = $safeBaseName . '-' . now()->format('YmdHis') . '.' . strtolower($extension);

                $poDocumentPath = $this->poDocument->storePubliclyAs(
                    'po-documents',
                    $fileName,
                    'public'
                );
            }
            
            // Create order
            $order = Order::create([
                'klien_id' => $this->selectedKlienId,
                'created_by' => $userId, // Use created_by instead of user_id
                'tanggal_order' => $this->tanggalOrder,
                'po_number' => $this->poNumber,
                'po_start_date' => $this->poStartDate,
                'po_end_date' => $this->poEndDate,
                'po_document_path' => $poDocumentPath,
                'po_document_original_name' => $poOriginalName,
                'priority' => $this->priority,
                'status' => 'draft',
                'catatan' => $this->catatan,
                'total_amount' => 0, // Will be calculated by model
                'total_margin' => 0, // Will be calculated by model
            ]);
            
            // Create order details with auto-supplier population
            foreach ($this->selectedOrderItems as $item) {
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'bahan_baku_klien_id' => $item['bahan_baku_klien_id'],
                    'qty' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_jual' => $item['harga_jual'],
                    'total_harga' => $item['total_harga'],
                    'spesifikasi_khusus' => $item['spesifikasi_khusus'] ?? null,
                    'catatan' => $item['catatan'] ?? null,
                    'status' => 'menunggu',
                ]);

                // Automatically populate all suppliers for this material
                $orderDetail->populateSupplierOptions();

                $this->setRecommendedSupplier($orderDetail, $item);
            }
            
            DB::commit();
            
            // TODO: Integrate notification broadcast for purchasing based on priority.
            session()->flash('success', 'Order berhasil dibuat dengan ID: ' . $order->id);
            
            return redirect()->route('orders.show', $order);
            
        } catch (\Exception $e) {
            DB::rollback();

            if (isset($poDocumentPath)) {
                Storage::disk('public')->delete($poDocumentPath);
            }
            session()->flash('error', 'Gagal membuat order: ' . $e->getMessage());
        }
    }

    public function updateOrder()
    {
        if (!$this->isEditing || !$this->editingOrderId) {
            session()->flash('error', 'Order tidak ditemukan untuk diperbarui.');
            return;
        }

        $this->validate([
            'selectedKlienId' => 'required',
            'tanggalOrder' => 'required|date',
            'poNumber' => 'required|string|max:50',
            'poStartDate' => 'required|date',
            'poEndDate' => 'required|date|after_or_equal:poStartDate',
            'poDocument' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'priority' => 'required|in:rendah,normal,tinggi,mendesak',
        ]);

        if (count($this->selectedOrderItems) === 0) {
            session()->flash('error', 'Tambahkan minimal satu item sebelum menyimpan.');
            return;
        }

        $this->updatePriorityFromSchedule();

        try {
            DB::beginTransaction();

            /** @var Order $order */
            $order = Order::with(['orderDetails.orderSuppliers'])->findOrFail($this->editingOrderId);

            $poDocumentPath = $order->po_document_path;
            $poOriginalName = $order->po_document_original_name;

            if ($this->poDocument) {
                if ($poDocumentPath && Storage::disk('public')->exists($poDocumentPath)) {
                    Storage::disk('public')->delete($poDocumentPath);
                }

                $poOriginalName = $this->poDocument->getClientOriginalName();
                $extension = $this->poDocument->getClientOriginalExtension();
                $baseName = pathinfo($poOriginalName, PATHINFO_FILENAME);
                $safeBaseName = Str::slug($baseName) ?: 'po-document';
                $fileName = $safeBaseName . '-' . now()->format('YmdHis') . '.' . strtolower($extension);

                $poDocumentPath = $this->poDocument->storePubliclyAs(
                    'po-documents',
                    $fileName,
                    'public'
                );
            }

            $order->update([
                'klien_id' => $this->selectedKlienId,
                'tanggal_order' => $this->tanggalOrder,
                'po_number' => $this->poNumber,
                'po_start_date' => $this->poStartDate,
                'po_end_date' => $this->poEndDate,
                'po_document_path' => $poDocumentPath,
                'po_document_original_name' => $poOriginalName,
                'priority' => $this->priority,
                'catatan' => $this->catatan,
            ]);

            foreach ($order->orderDetails as $detail) {
                $detail->orderSuppliers()->delete();
            }
            $order->orderDetails()->delete();

            foreach ($this->selectedOrderItems as $item) {
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'bahan_baku_klien_id' => $item['bahan_baku_klien_id'],
                    'qty' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_jual' => $item['harga_jual'],
                    'total_harga' => $item['total_harga'],
                    'spesifikasi_khusus' => $item['spesifikasi_khusus'] ?? null,
                    'catatan' => $item['catatan'] ?? null,
                    'status' => 'menunggu',
                ]);

                $orderDetail->populateSupplierOptions();

                $this->setRecommendedSupplier($orderDetail, $item);
            }

            $order->calculateTotals();

            DB::commit();

            $this->existingPoDocumentPath = $poDocumentPath;
            $this->existingPoDocumentName = $poOriginalName;
            $this->existingPoDocumentUrl = $order->po_document_url;

            session()->flash('success', 'Order berhasil diperbarui.');

            return redirect()->route('orders.show', $order->id);
        } catch (\Exception $e) {
            DB::rollBack();

            session()->flash('error', 'Gagal memperbarui order: ' . $e->getMessage());
        }
    }

    protected function setRecommendedSupplier(OrderDetail $orderDetail, array $item): void
    {
        $autoSuppliers = collect($item['auto_suppliers'] ?? []);

        $recommended = $autoSuppliers->firstWhere('is_recommended', true);

        $bahanBakuSupplierId = $recommended['bahan_baku_supplier_id']
            ?? $item['recommended_bahan_baku_supplier_id']
            ?? null;
        $supplierId = $recommended['supplier_id'] ?? null;

        if (!$bahanBakuSupplierId && !$supplierId) {
            $orderDetail->updateSupplierSummary();
            return;
        }

        $selectedSupplier = $orderDetail->orderSuppliers()
            ->when($bahanBakuSupplierId, fn($query) => $query->where('bahan_baku_supplier_id', $bahanBakuSupplierId))
            ->when(!$bahanBakuSupplierId && $supplierId, fn($query) => $query->where('supplier_id', $supplierId))
            ->first();

        if ($selectedSupplier) {
            $orderDetail->orderSuppliers()->update(['is_recommended' => false]);

            $selectedSupplier->is_recommended = true;
            $selectedSupplier->price_rank = 1;
            $selectedSupplier->save();
        }

        $orderDetail->updateSupplierSummary();
    }

    protected function transformDetailToSelectedItem(OrderDetail $detail): array
    {
        $detail->loadMissing(['bahanBakuKlien', 'orderSuppliers.supplier.picPurchasing', 'orderSuppliers.bahanBakuSupplier']);

        $qty = (float) ($detail->qty ?? 0);
        $sellingPrice = (float) ($detail->harga_jual ?? 0);
        $totalHarga = (float) ($detail->total_harga ?? ($qty * $sellingPrice));

        $autoSuppliers = $detail->orderSuppliers->map(function ($orderSupplier) use ($detail, $sellingPrice) {
            $supplier = $orderSupplier->supplier;
            $bahanBakuSupplier = $orderSupplier->bahanBakuSupplier;
            $unitPrice = (float) ($orderSupplier->unit_price ?? 0);
            $margin = $orderSupplier->calculated_margin;

            if ($margin === null && $sellingPrice > 0) {
                $margin = (($sellingPrice - $unitPrice) / $sellingPrice) * 100;
            }

            return [
                'supplier_id' => $orderSupplier->supplier_id,
                'bahan_baku_supplier_id' => $orderSupplier->bahan_baku_supplier_id,
                'supplier_name' => $supplier->nama ?? 'Supplier',
                'supplier_location' => $supplier->alamat ?? 'Alamat tidak tersedia',
                'pic_name' => optional($supplier->picPurchasing)->nama,
                'material_name' => $bahanBakuSupplier->nama ?? optional($detail->bahanBakuKlien)->nama,
                'harga_supplier' => $unitPrice,
                'satuan' => $bahanBakuSupplier->satuan ?? $detail->satuan,
                'stok' => $bahanBakuSupplier->stok ?? 0,
                'suggested_price' => $unitPrice > 0 ? $unitPrice * 1.2 : $sellingPrice,
                'margin_percentage' => $margin ?? 0,
                'is_recommended' => (bool) $orderSupplier->is_recommended,
            ];
        })->sortBy('harga_supplier')->values()->toArray();

        $bestSupplier = collect($autoSuppliers)->first();
        $recommended = collect($autoSuppliers)->firstWhere('is_recommended', true) ?? $bestSupplier;

        $bestPrice = (float) ($bestSupplier['harga_supplier'] ?? 0);
        $bestHpp = $qty * $bestPrice;
        $totalMargin = $totalHarga - $bestHpp;
        $marginPercentage = $totalHarga > 0 ? ($totalMargin / $totalHarga) * 100 : 0;

        return [
            'id' => 'detail-' . $detail->id,
            'order_detail_id' => $detail->id,
            'bahan_baku_klien_id' => $detail->bahan_baku_klien_id,
            'material_name' => optional($detail->bahanBakuKlien)->nama ?? 'Material',
            'qty' => $qty,
            'satuan' => $detail->satuan,
            'harga_jual' => $sellingPrice,
            'total_harga' => $totalHarga,
            'best_supplier_price' => $bestPrice,
            'best_hpp' => $bestHpp,
            'total_margin' => $totalMargin,
            'margin_percentage' => $marginPercentage,
            'suppliers_count' => count($autoSuppliers),
            'spesifikasi_khusus' => $detail->spesifikasi_khusus,
            'catatan' => $detail->catatan,
            'auto_suppliers' => $autoSuppliers,
            'recommended_bahan_baku_supplier_id' => $recommended['bahan_baku_supplier_id'] ?? null,
        ];
    }
}
