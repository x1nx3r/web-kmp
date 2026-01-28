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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderCreate extends Component
{
    use WithFileUploads;

    public $selectedKlien = null;
    public $selectedKlienCabang = null;
    public $selectedKlienId = null;

    // Mode state
    public bool $isEditing = false;
    public ?int $editingOrderId = null;
    public ?string $editingOrderNumber = null;
    public ?string $currentStatus = "draft";

    // Existing PO metadata (edit mode)
    public ?string $existingPoDocumentPath = null;
    public ?string $existingPoDocumentName = null;
    public ?string $existingPoDocumentUrl = null;

    // Order info
    public $tanggalOrder;
    public $poNumber = "";
    public $poStartDate;
    public $poEndDate;
    public $poDocument;
    // Default to the new middle bucket 'sedang'
    public $priority = "sedang";
    public $catatan = "";
    public $poWinnerId = null;
    public $availableWinners = [];

    // Search and filter properties
    public $klienSearch = "";
    public $klienSort = "nama_asc";
    public $selectedKota = "";

    // Single Material Selection (no modal needed)
    public $selectedMaterial = null;
    public $namaMaterialPO = "";
    public $quantity = 1;
    public $satuan = "";
    public $hargaJual = 0;
    public $spesifikasiKhusus = "";
    public $catatanMaterial = "";

    // Auto-populated supplier data (read-only)
    public $autoSuppliers = [];
    public $bestMargin = 0;
    public $recommendedPrice = 0;

    // Single item totals
    public $totalAmount = 0;
    public $totalMargin = 0;

    public function mount(?Order $order = null)
    {
        // Initialize default values for CREATE mode
        $this->isEditing = false;
        $this->editingOrderId = null;
        $this->editingOrderNumber = null;
        $this->currentStatus = "draft";

        $this->tanggalOrder = now()->format("Y-m-d");
        $this->poStartDate = $this->tanggalOrder;
        $this->poEndDate = now()->addDays(14)->format("Y-m-d");
        $this->resetTotals();
        $this->updatePriorityFromSchedule();
        $this->loadAvailableWinners();

        // If editing an existing order, load its data
        if ($order && $order->exists) {
            $this->isEditing = true;
            $this->editingOrderId = $order->id;
            $this->editingOrderNumber = $order->po_number;
            $this->currentStatus = $order->status;
            $this->selectedKlienId = $order->klien_id;

            // Load klien details
            $klien = Klien::find($order->klien_id);
            if ($klien) {
                $this->selectedKlien = $klien->nama;
                $this->selectedKlienCabang = $klien->cabang;
            }

            $this->tanggalOrder = $order->tanggal_order
                ? \Illuminate\Support\Carbon::parse(
                    $order->tanggal_order,
                )->format("Y-m-d")
                : $this->tanggalOrder;
            $this->poNumber = $order->po_number;
            $this->poStartDate = $order->po_start_date
                ? \Illuminate\Support\Carbon::parse(
                    $order->po_start_date,
                )->format("Y-m-d")
                : $this->poStartDate;
            $this->poEndDate = $order->po_end_date
                ? \Illuminate\Support\Carbon::parse(
                    $order->po_end_date,
                )->format("Y-m-d")
                : $this->poEndDate;
            // Prefer the legacy column `priority` (surgical migration path)
            $this->priority = $order->priority ?? $this->priority;
            $this->catatan = $order->catatan ?? $this->catatan;
            $this->existingPoDocumentPath = $order->po_document_path;
            $this->existingPoDocumentName = $order->po_document_original_name;
            $this->existingPoDocumentUrl = $order->po_document_url;

            if ($order->winner) {
                $this->poWinnerId = $order->winner->user_id;
            }

            // Load first order detail if exists (single material order)
            $firstDetail = $order->orderDetails()->first();
            if ($firstDetail) {
                $this->selectedMaterial = $firstDetail->bahan_baku_klien_id;
                $this->namaMaterialPO = $firstDetail->nama_material_po;
                $this->quantity = $firstDetail->qty;
                $this->satuan = $firstDetail->satuan;
                $this->hargaJual = $firstDetail->harga_jual;
                $this->spesifikasiKhusus =
                    $firstDetail->spesifikasi_khusus ?? "";
                $this->catatanMaterial = $firstDetail->catatan ?? "";

                // Auto-populate suppliers for this material
                $material = BahanBakuKlien::find($this->selectedMaterial);
                if ($material) {
                    $this->autoPopulateSuppliers($material);
                }

                $this->updateTotals();
            }
        }
    }

    public function loadAvailableWinners()
    {
        $this->availableWinners = \App\Models\User::whereIn("role", [
            "direktur",
            "marketing",
        ])
            ->where("status", "aktif")
            ->orderBy("nama")
            ->get();
    }

    public function render()
    {
        $query = Klien::with("bahanBakuKliens");

        // Apply search filter (safe against legacy removed `no_hp` column)
        if ($this->klienSearch) {
            $search = $this->klienSearch;
            $query->where(function ($q) use ($search) {
                $q->where("nama", "like", "%" . $search . "%")->orWhere(
                    "cabang",
                    "like",
                    "%" . $search . "%",
                );

                // If the legacy column still exists, include it in the search.
                try {
                    if (Schema::hasColumn("kliens", "no_hp")) {
                        $q->orWhere("no_hp", "like", "%" . $search . "%");
                    }
                } catch (\Throwable $e) {
                    // Ignore schema inspection errors and continue — safer than failing the request.
                }

                // Also search related contact person fields (new canonical place for phone)
                $q->orWhereHas("contactPerson", function ($sub) use ($search) {
                    $sub->where("nama", "like", "%" . $search . "%")->orWhere(
                        "nomor_hp",
                        "like",
                        "%" . $search . "%",
                    );
                });
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
                    $klien->display_name =
                        $klien->nama . " - " . $klien->cabang;
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

        // Reset material selection when client changes
        $this->selectedMaterial = null;
        $this->namaMaterialPO = "";
        $this->quantity = 1;
        $this->hargaJual = 0;
        $this->autoSuppliers = [];
        $this->resetTotals();
        $this->resetTotals();

        $this->poNumber = "";
        $this->poDocument = null;
        if ($this->isEditing) {
            $this->existingPoDocumentPath = null;
            $this->existingPoDocumentName = null;
            $this->existingPoDocumentUrl = null;
        }
        $this->poStartDate = $this->tanggalOrder;
        $this->poEndDate = now()->addDays(14)->format("Y-m-d");
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

        return BahanBakuKlien::where("klien_id", $this->selectedKlienId)
            ->aktif()
            ->withApprovedPrice()
            ->get()
            ->map(function ($item) {
                return [
                    "id" => $item->id,
                    "nama" => $item->nama, // Using 'nama' instead of 'nama_material'
                    "satuan" => $item->satuan,
                    "harga_approved" => $item->harga_approved,
                ];
            });
    }

    public function selectMaterial($materialId)
    {
        $material = BahanBakuKlien::find($materialId);
        if ($material) {
            $this->selectedMaterial = $materialId;
            $this->satuan = $material->satuan;

            // Auto-populate all suppliers for this material
            $this->autoPopulateSuppliers($material);

            // Update totals when material changes
            $this->updateTotals();
        }
    }

    public function updatedQuantity()
    {
        $this->updateTotals();
    }

    public function updatedHargaJual()
    {
        $this->updateTotals();
    }

    protected function autoPopulateSuppliers($material)
    {
        // Get klien_id for client-specific pricing lookup
        $klienId = $this->selectedKlienId;

        // Get all suppliers for this material using name matching (like in OrderDetail model)
        // Load hargaPerKlien relationship for client-specific pricing
        $suppliers = Supplier::with([
            "bahanBakuSuppliers" => function ($q) use ($material) {
                $q->where("nama", "like", "%" . $material->nama . "%")
                    ->whereNotNull("harga_per_satuan")
                    ->where("harga_per_satuan", ">", 0);
            },
            "bahanBakuSuppliers.hargaPerKlien" => function ($q) use ($klienId) {
                if ($klienId) {
                    $q->where("klien_id", $klienId);
                }
            },
            "picPurchasing",
        ])->get();

        $this->autoSuppliers = [];
        $bestPrice = PHP_INT_MAX;

        foreach ($suppliers as $supplier) {
            foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
                // Use client-specific price if available, otherwise fall back to global price
                $hargaSupplier = $bahanBaku->getHargaForKlien($klienId);

                // Skip if no valid price
                if (!$hargaSupplier || $hargaSupplier <= 0) {
                    continue;
                }

                // Calculate margin with a default selling price (20% markup)
                $suggestedPrice = $hargaSupplier * 1.2;
                $margin =
                    (($suggestedPrice - $hargaSupplier) /
                        $suggestedPrice) *
                    100;

                $this->autoSuppliers[] = [
                    "supplier_id" => $supplier->id,
                    "bahan_baku_supplier_id" => $bahanBaku->id,
                    "supplier_name" => $supplier->nama,
                    "supplier_location" =>
                        $supplier->alamat ?? "Address not specified",
                    "pic_name" => $supplier->picPurchasing
                        ? $supplier->picPurchasing->nama
                        : null,
                    "material_name" => $bahanBaku->nama,
                    "harga_supplier" => $hargaSupplier,
                    "satuan" => $bahanBaku->satuan,
                    "stok" => $bahanBaku->stok ?? 0,
                    "suggested_price" => $suggestedPrice,
                    "margin_percentage" => $margin,
                    "is_recommended" => false, // Will set best one later
                ];

                if ($hargaSupplier < $bestPrice) {
                    $bestPrice = $hargaSupplier;
                }
            }
        }

        // Sort by price and mark the best one as recommended
        usort($this->autoSuppliers, function ($a, $b) {
            return $a["harga_supplier"] <=> $b["harga_supplier"];
        });

        // Mark the cheapest supplier as recommended and calculate suggested selling price
        if (!empty($this->autoSuppliers)) {
            $this->autoSuppliers[0]["is_recommended"] = true;
            $this->recommendedPrice =
                $this->autoSuppliers[0]["suggested_price"];
            $this->bestMargin = $this->autoSuppliers[0]["margin_percentage"];

            // Set current selling price to recommended price
            $this->hargaJual = $this->recommendedPrice;
        }
    }

    protected function updateTotals()
    {
        if (
            $this->selectedMaterial &&
            $this->quantity > 0 &&
            $this->hargaJual > 0
        ) {
            $this->totalAmount = $this->quantity * $this->hargaJual;

            // Calculate margin based on best supplier price
            if (!empty($this->autoSuppliers)) {
                $bestSupplier = collect($this->autoSuppliers)
                    ->sortBy("harga_supplier")
                    ->first();
                $bestHpp = $this->quantity * $bestSupplier["harga_supplier"];
                $this->totalMargin = $this->totalAmount - $bestHpp;
            } else {
                $this->totalMargin = 0;
            }
        } else {
            $this->totalAmount = 0;
            $this->totalMargin = 0;
        }
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
            $this->validateOnly("poDocument", [
                "poDocument" => "file|mimes:jpg,jpeg,png,pdf|max:5120",
            ]);
        }
    }

    protected function updatePriorityFromSchedule(): void
    {
        if (!$this->poEndDate) {
            return;
        }

        $end = Carbon::parse($this->poEndDate);
        $days = now()->diffInDays($end, false);

        // Inverted priority mapping (deadline urgency):
        // - tinggi: remaining days <= 30 (urgent, deadline soon!)
        // - sedang: remaining days > 30 and <= 60
        // - rendah: remaining days > 60 (plenty of time)
        if ($days <= 30) {
            $this->priority = "tinggi";
        } elseif ($days <= 60) {
            $this->priority = "sedang";
        } else {
            $this->priority = "rendah";
        }
    }

    public function getCanSubmitProperty(): bool
    {
        if ($this->isEditing) {
            return (bool) ($this->selectedKlienId &&
                $this->selectedMaterial &&
                $this->quantity > 0 &&
                $this->hargaJual > 0 &&
                !empty($this->poNumber) &&
                !empty($this->poStartDate) &&
                !empty($this->poEndDate));
        }

        return (bool) ($this->selectedKlienId &&
            $this->selectedMaterial &&
            $this->quantity > 0 &&
            $this->hargaJual > 0 &&
            !empty($this->poNumber) &&
            !empty($this->poStartDate) &&
            !empty($this->poEndDate));
    }

    // Removed unused multi-item methods - now focusing on single material orders

    public function createOrder()
    {
        $this->validate([
            "selectedKlienId" => "required",
            "selectedMaterial" => "required",
            "namaMaterialPO" => "nullable|string|max:255",
            "quantity" => "required|numeric|min:0.01",
            "hargaJual" => "required|numeric|min:0",
            "tanggalOrder" => "required|date",
            "poNumber" => "required|string|max:50",
            "poStartDate" => "required|date",
            "poEndDate" => "required|date|after_or_equal:poStartDate",
            "poDocument" => "nullable|file|mimes:jpg,jpeg,png,pdf|max:5120",
            // Use new allowed values for the UI-level priority field
            "priority" => "required|in:rendah,sedang,tinggi",
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
                if ($safeBaseName === "") {
                    $safeBaseName = "po-document";
                }
                $fileName =
                    $safeBaseName .
                    "-" .
                    now()->format("YmdHis") .
                    "." .
                    strtolower($extension);

                $poDocumentPath = $this->poDocument->storePubliclyAs(
                    "po-documents",
                    $fileName,
                    "public",
                );
            }

            // Create order
            $order = Order::create([
                "klien_id" => $this->selectedKlienId,
                "created_by" => $userId, // Use created_by instead of user_id
                "tanggal_order" => $this->tanggalOrder,
                "po_number" => $this->poNumber,
                "po_start_date" => $this->poStartDate,
                "po_end_date" => $this->poEndDate,
                "po_document_path" => $poDocumentPath,
                "po_document_original_name" => $poOriginalName,
                // Write into the legacy `priority` column (surgical migration path).
                "priority" => $this->priority,
                "status" => "draft",
                "catatan" => $this->catatan,
                "total_amount" => 0, // Will be calculated by model
                "total_margin" => 0, // Will be calculated by model
            ]);

            // Get material name for fallback
            $material = BahanBakuKlien::find($this->selectedMaterial);
            $namaMaterialPO = !empty($this->namaMaterialPO)
                ? $this->namaMaterialPO
                : ($material
                    ? $material->nama
                    : null);

            // Create single order detail with auto-supplier population
            $orderDetail = OrderDetail::create([
                "order_id" => $order->id,
                "bahan_baku_klien_id" => $this->selectedMaterial,
                "nama_material_po" => $namaMaterialPO,
                "qty" => $this->quantity,
                "satuan" => $this->satuan,
                "harga_jual" => $this->hargaJual,
                "total_harga" => $this->totalAmount,
                "spesifikasi_khusus" => $this->spesifikasiKhusus ?: null,
                "catatan" => $this->catatanMaterial ?: null,
                "status" => "menunggu",
            ]);

            // Automatically populate all suppliers for this material
            $orderDetail->populateSupplierOptions();

            // Set recommended supplier based on auto-populated data
            $this->setRecommendedSupplierFromAutoSuppliers($orderDetail);

            // Persist PO winner if selected
            if ($this->poWinnerId) {
                \App\Models\OrderWinner::updateOrCreate(
                    ["order_id" => $order->id],
                    ["user_id" => $this->poWinnerId],
                );
            }

            DB::commit();

            session()->flash(
                "success",
                "Order berhasil dibuat dengan ID: " . $order->id,
            );

            return redirect()->route("orders.show", $order);
        } catch (\Exception $e) {
            DB::rollback();

            if (isset($poDocumentPath)) {
                Storage::disk("public")->delete($poDocumentPath);
            }
            session()->flash(
                "error",
                "Gagal membuat order: " . $e->getMessage(),
            );
        }
    }

    public function updateOrder()
    {
        if (!$this->isEditing || !$this->editingOrderId) {
            session()->flash(
                "error",
                "Order tidak ditemukan untuk diperbarui.",
            );
            return;
        }

        $this->validate([
            "selectedKlienId" => "required",
            "namaMaterialPO" => "nullable|string|max:255",
            "tanggalOrder" => "required|date",
            "poNumber" => "required|string|max:50",
            "poStartDate" => "required|date",
            "poEndDate" => "required|date|after_or_equal:poStartDate",
            "poDocument" => "nullable|file|mimes:jpg,jpeg,png|max:5120",
            // validation updated to accept new priority values
            "priority" => "required|in:rendah,sedang,tinggi",
        ]);

        if (
            !$this->selectedMaterial ||
            $this->quantity <= 0 ||
            $this->hargaJual <= 0
        ) {
            session()->flash(
                "error",
                "Lengkapi data material, quantity, dan harga jual sebelum menyimpan.",
            );
            return;
        }

        $this->updatePriorityFromSchedule();

        try {
            DB::beginTransaction();

            /** @var Order $order */
            $order = Order::with(["orderDetails.orderSuppliers"])->findOrFail(
                $this->editingOrderId,
            );

            $poDocumentPath = $order->po_document_path;
            $poOriginalName = $order->po_document_original_name;

            if ($this->poDocument) {
                if (
                    $poDocumentPath &&
                    Storage::disk("public")->exists($poDocumentPath)
                ) {
                    Storage::disk("public")->delete($poDocumentPath);
                }

                $poOriginalName = $this->poDocument->getClientOriginalName();
                $extension = $this->poDocument->getClientOriginalExtension();
                $baseName = pathinfo($poOriginalName, PATHINFO_FILENAME);
                $safeBaseName = Str::slug($baseName) ?: "po-document";
                $fileName =
                    $safeBaseName .
                    "-" .
                    now()->format("YmdHis") .
                    "." .
                    strtolower($extension);

                $poDocumentPath = $this->poDocument->storePubliclyAs(
                    "po-documents",
                    $fileName,
                    "public",
                );
            }

            $order->update([
                "klien_id" => $this->selectedKlienId,
                "tanggal_order" => $this->tanggalOrder,
                "po_number" => $this->poNumber,
                "po_start_date" => $this->poStartDate,
                "po_end_date" => $this->poEndDate,
                "po_document_path" => $poDocumentPath,
                "po_document_original_name" => $poOriginalName,
                // Update the legacy `priority` enum column directly (surgical migration path).
                "priority" => $this->priority,
                "catatan" => $this->catatan,
            ]);

            // Clear existing order details and suppliers
            foreach ($order->orderDetails as $detail) {
                $detail->orderSuppliers()->delete();
            }
            $order->orderDetails()->delete();

            // Get material name for fallback
            $material = BahanBakuKlien::find($this->selectedMaterial);
            $namaMaterialPO = !empty($this->namaMaterialPO)
                ? $this->namaMaterialPO
                : ($material
                    ? $material->nama
                    : null);

            // Create single updated order detail
            $orderDetail = OrderDetail::create([
                "order_id" => $order->id,
                "bahan_baku_klien_id" => $this->selectedMaterial,
                "nama_material_po" => $namaMaterialPO,
                "qty" => $this->quantity,
                "satuan" => $this->satuan,
                "harga_jual" => $this->hargaJual,
                "total_harga" => $this->totalAmount,
                "spesifikasi_khusus" => $this->spesifikasiKhusus ?: null,
                "catatan" => $this->catatanMaterial ?: null,
                "status" => "menunggu",
            ]);

            $orderDetail->populateSupplierOptions();
            $this->setRecommendedSupplierFromAutoSuppliers($orderDetail);

            $order->calculateTotals();

            // Update or create PO winner record
            if ($this->poWinnerId) {
                \App\Models\OrderWinner::updateOrCreate(
                    ["order_id" => $order->id],
                    ["user_id" => $this->poWinnerId],
                );
            } else {
                // If null, remove any existing winner assignment
                \App\Models\OrderWinner::where(
                    "order_id",
                    $order->id,
                )->delete();
            }

            DB::commit();

            $this->existingPoDocumentPath = $poDocumentPath;
            $this->existingPoDocumentName = $poOriginalName;
            $this->existingPoDocumentUrl = $order->po_document_url;

            session()->flash("success", "Order berhasil diperbarui.");

            return redirect()->route("orders.show", $order->id);
        } catch (\Exception $e) {
            DB::rollBack();

            session()->flash(
                "error",
                "Gagal memperbarui order: " . $e->getMessage(),
            );
        }
    }

    protected function setRecommendedSupplierFromAutoSuppliers(
        OrderDetail $orderDetail,
    ): void {
        if (empty($this->autoSuppliers)) {
            $orderDetail->updateSupplierSummary();
            return;
        }

        $recommended = collect($this->autoSuppliers)->firstWhere(
            "is_recommended",
            true,
        );

        if (!$recommended) {
            $orderDetail->updateSupplierSummary();
            return;
        }

        $selectedSupplier = $orderDetail
            ->orderSuppliers()
            ->where(
                "bahan_baku_supplier_id",
                $recommended["bahan_baku_supplier_id"],
            )
            ->first();

        if ($selectedSupplier) {
            $orderDetail->orderSuppliers()->update(["is_recommended" => false]);

            $selectedSupplier->refresh();
            $selectedSupplier->is_recommended = true;
            $selectedSupplier->price_rank = 1;
            $selectedSupplier->save();
        }

        $orderDetail->updateSupplierSummary();
    }

    // Removed transformDetailToSelectedItem - no longer needed for single material orders
}
