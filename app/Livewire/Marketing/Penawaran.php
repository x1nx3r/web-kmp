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
use App\Services\NotificationService;
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
    public $klienSearch = "";
    public $klienSort = "nama_asc";
    public $selectedKota = "";

    // Analysis data
    public $marginAnalysis = [];
    public $totalRevenue = 0;
    public $totalCost = 0;
    public $totalProfit = 0;
    public $overallMargin = 0;

    // Supplier selection per material
    public $selectedSuppliers = [];

    // Edit mode
    public $editMode = false;
    public $penawaranId = null;

    public function mount($penawaran = null)
    {
        if ($penawaran) {
            $this->loadPenawaranForEdit($penawaran);
        } else {
            $this->resetAnalysis();
        }
    }

    private function loadPenawaranForEdit($penawaran)
    {
        $this->editMode = true;
        $this->penawaranId = $penawaran->id;

        // Load client info
        $this->selectedKlien = $penawaran->klien->nama;
        $this->selectedKlienCabang = $penawaran->klien->cabang;
        $this->selectedKlienId = $penawaran->klien_id;

        // Load materials from details
        foreach ($penawaran->details as $detail) {
            $this->selectedMaterials[] = [
                "id" => uniqid(),
                "material_id" => $detail->bahan_baku_klien_id,
                "nama" => $detail->nama_material,
                "satuan" => $detail->satuan,
                "quantity" => $detail->quantity,
                "klien_price" => $detail->harga_klien,
                "is_custom_price" => $detail->is_custom_price,
                "custom_price" => $detail->is_custom_price
                    ? $detail->harga_klien
                    : null,
                "original_price" =>
                    $detail->bahanBakuKlien->harga_approved ??
                    $detail->harga_klien,
            ];

            // Load selected supplier for this material
            $this->selectedSuppliers[count($this->selectedMaterials) - 1] =
                $detail->bahan_baku_supplier_id;
        }

        $this->refreshAnalysis();
    }

    public function render()
    {
        $query = Klien::with("bahanBakuKliens");

        // Apply search filter
        if ($this->klienSearch) {
            $query->where(function ($q) {
                $q->where(
                    "nama",
                    "like",
                    "%" . $this->klienSearch . "%",
                )->orWhere("cabang", "like", "%" . $this->klienSearch . "%");
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
            case "kota_asc":
                $query->orderBy("cabang", "asc")->orderBy("nama", "asc");
                break;
            case "kota_desc":
                $query->orderBy("cabang", "desc")->orderBy("nama", "desc");
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

        return view("livewire.marketing.penawaran", [
            "kliens" => $kliens,
            "availableMaterials" => $this->getAvailableMaterials(),
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
        $this->resetAnalysis();
        $this->selectedMaterials = [];
        $this->selectedSuppliers = [];
    }

    public function clearKlienSearch()
    {
        $this->klienSearch = "";
    }

    public function clearKotaFilter()
    {
        $this->selectedKota = "";
    }

    public function openAddMaterialModal()
    {
        if (!$this->selectedKlien || !$this->selectedKlienCabang) {
            session()->flash("error", "Pilih klien terlebih dahulu");
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
        $exists = collect($this->selectedMaterials)->contains(
            "material_id",
            $this->currentMaterial,
        );
        if ($exists) {
            session()->flash("error", "Material sudah ditambahkan");
            return;
        }

        // Get material data
        $material = BahanBakuKlien::with("klien")
            ->where("id", $this->currentMaterial)
            ->whereHas("klien", function ($q) {
                $q->where("nama", $this->selectedKlien)->where(
                    "cabang",
                    $this->selectedKlienCabang,
                );
            })
            ->first();

        if (!$material) {
            session()->flash(
                "error",
                "Material tidak ditemukan untuk klien ini",
            );
            return;
        }

        // Determine final price (custom or default)
        $finalPrice =
            $this->useCustomPrice && $this->customPrice > 0
                ? $this->customPrice
                : $material->harga_approved ?? 0;

        $this->selectedMaterials[] = [
            "id" => uniqid(),
            "material_id" => $material->id,
            "nama" => $material->nama,
            "satuan" => $material->satuan,
            "quantity" => $this->currentQuantity,
            "klien_price" => $finalPrice,
            "is_custom_price" => $this->useCustomPrice,
            "custom_price" => $this->useCustomPrice ? $this->customPrice : null,
            "original_price" => $material->harga_approved ?? 0,
        ];

        $this->refreshAnalysis();
        $this->closeAddMaterialModal();
        session()->flash("message", "Material berhasil ditambahkan");
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

        // Dispatch browser event to update charts
        $this->dispatch("chart-data-updated", [
            "analysisData" => $this->marginAnalysis,
        ]);
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity > 0) {
            $this->selectedMaterials[$index]["quantity"] = $quantity;
            $this->refreshAnalysis();
        }
    }

    public function updatedSelectedSuppliers()
    {
        // Recalculate totals when supplier selection changes
        $this->recalculateTotals();

        // Dispatch browser event to update charts with current margin analysis data
        $this->dispatch("margin-analysis-updated", [
            "analysisData" => $this->marginAnalysis,
        ]);
    }

    private function recalculateTotals()
    {
        $this->totalRevenue = 0;
        $this->totalCost = 0;

        foreach ($this->marginAnalysis as $index => $analysis) {
            // Get selected supplier for this material
            $selectedSupplierId =
                $this->selectedSuppliers[$index] ??
                $analysis["best_supplier_id"];

            // Find the selected supplier's data
            $selectedSupplier = collect(
                $analysis["supplier_options"],
            )->firstWhere("supplier_id", $selectedSupplierId);

            if ($selectedSupplier) {
                $this->totalRevenue += $analysis["revenue"];
                $this->totalCost += $selectedSupplier["cost"];
            }
        }

        $this->totalProfit = $this->totalRevenue - $this->totalCost;
        $this->overallMargin =
            $this->totalRevenue > 0
                ? ($this->totalProfit / $this->totalRevenue) * 100
                : 0;
    }

    public function calculateMarginAnalysis()
    {
        $this->marginAnalysis = [];
        $this->totalRevenue = 0;
        $this->totalCost = 0;

        foreach ($this->selectedMaterials as $index => $material) {
            // Use custom price if available, otherwise use client price
            $finalPrice = $material["custom_price"] ?? $material["klien_price"];
            $revenue = $finalPrice * $material["quantity"];

            // Get all suppliers for this material with their prices and margins
            $allSuppliersData = $this->getAllSuppliersForMaterial(
                $material["nama"],
            );
            $bestSupplier = $this->getBestSupplierPrice($material["nama"]);

            $cost = $bestSupplier["price"] * $material["quantity"];
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            // Get price history for both client and best supplier
            $klienPriceHistory = $this->getKlienPriceHistory(
                $material["material_id"],
            );
            $supplierPriceHistory = $this->getSupplierPriceHistory(
                $material["nama"],
            );

            // Calculate margin analysis for each supplier
            $supplierMargins = [];
            foreach ($allSuppliersData as $supplierData) {
                $supplierCost = $supplierData["price"] * $material["quantity"];
                $supplierProfit = $revenue - $supplierCost;
                $supplierMargin =
                    $revenue > 0 ? ($supplierProfit / $revenue) * 100 : 0;

                $supplierMargins[] = [
                    "supplier_name" => $supplierData["supplier"],
                    "pic_name" => $supplierData["pic_name"],
                    "supplier_id" => $supplierData["supplier_id"],
                    "price" => $supplierData["price"],
                    "cost" => $supplierCost,
                    "profit" => $supplierProfit,
                    "margin_percent" => $supplierMargin,
                    "is_best" =>
                        $supplierData["supplier"] === $bestSupplier["supplier"],
                    "price_history" => $this->getSupplierSpecificPriceHistory(
                        $supplierData["supplier_id"],
                    ),
                ];
            }

            // Initialize selected supplier for this material if not set (default to best)
            if (!isset($this->selectedSuppliers[$index])) {
                $this->selectedSuppliers[$index] = $bestSupplier["supplier_id"];
            }

            $this->marginAnalysis[] = [
                "nama" => $material["nama"],
                "satuan" => $material["satuan"],
                "quantity" => $material["quantity"],
                "material_id" => $material["material_id"], // BahanBakuKlien ID
                "klien_price" => $material["klien_price"],
                "is_custom_price" => $material["is_custom_price"] ?? false,
                "custom_price" => $material["custom_price"] ?? null,
                "original_price" =>
                    $material["original_price"] ?? $material["klien_price"],
                "revenue" => $revenue,
                "best_supplier" => $bestSupplier["supplier"],
                "best_supplier_pic" => $bestSupplier["pic_name"],
                "best_supplier_id" => $bestSupplier["supplier_id"],
                "supplier_price" => $bestSupplier["price"],
                "cost" => $cost,
                "profit" => $profit,
                "margin_percent" => $margin,
                "supplier_options" => $supplierMargins,
                "klien_price_history" => $klienPriceHistory,
                "supplier_price_history" => $supplierPriceHistory,
            ];

            $this->totalRevenue += $revenue;

            // Use selected supplier cost if available, otherwise use best supplier
            $selectedSupplierId =
                $this->selectedSuppliers[$index] ??
                $bestSupplier["supplier_id"];
            $selectedSupplier = collect($supplierMargins)->firstWhere(
                "supplier_id",
                $selectedSupplierId,
            );
            $this->totalCost += $selectedSupplier
                ? $selectedSupplier["cost"]
                : $cost;
        }

        $this->totalProfit = $this->totalRevenue - $this->totalCost;
        $this->overallMargin =
            $this->totalRevenue > 0
                ? ($this->totalProfit / $this->totalRevenue) * 100
                : 0;

        // Dispatch browser event to update charts when analysis is recalculated
        $this->dispatch("margin-analysis-updated", [
            "analysisData" => $this->marginAnalysis,
        ]);
    }

    private function getBestSupplierPrice($materialName)
    {
        // Get klien_id for client-specific pricing lookup
        $klienId = $this->selectedKlienId;

        // Find the best supplier price for materials with matching names
        // Load hargaPerKlien relationship for client-specific pricing
        $suppliers = Supplier::with([
            "bahanBakuSuppliers" => function ($q) use ($materialName) {
                $q->where("nama", "like", "%" . $materialName . "%")
                    ->whereNotNull("harga_per_satuan");
            },
            "bahanBakuSuppliers.hargaPerKlien" => function ($q) use ($klienId) {
                if ($klienId) {
                    $q->where("klien_id", $klienId);
                }
            },
            "picPurchasing",
        ])->get();

        $bestPrice = PHP_INT_MAX;
        $bestSupplierName = "N/A";
        $bestSupplierId = null;
        $bestPicName = null;

        foreach ($suppliers as $supplier) {
            foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
                // Use client-specific price if available, otherwise fall back to global
                $price = $bahanBaku->getHargaForKlien($klienId);

                if ($price && $price < $bestPrice) {
                    $bestPrice = $price;
                    $bestSupplierName = $supplier->nama;
                    $bestSupplierId = $bahanBaku->id;
                    $bestPicName = $supplier->picPurchasing
                        ? $supplier->picPurchasing->nama
                        : null;
                }
            }
        }

        return [
            "supplier" => $bestSupplierName,
            "supplier_id" => $bestSupplierId,
            "pic_name" => $bestPicName,
            "price" => $bestPrice === PHP_INT_MAX ? 0 : $bestPrice,
        ];
    }

    private function getAllSuppliersForMaterial($materialName)
    {
        // Get all suppliers that have this material with their prices
        $suppliers = Supplier::with([
            "bahanBakuSuppliers" => function ($q) use ($materialName) {
                $q->where("nama", "like", "%" . $materialName . "%")
                    ->whereNotNull("harga_per_satuan")
                    ->orderBy("harga_per_satuan", "asc");
            },
            "picPurchasing",
        ])->get();

        $supplierOptions = [];

        foreach ($suppliers as $supplier) {
            foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
                $supplierOptions[] = [
                    "supplier" => $supplier->nama,
                    "pic_name" => $supplier->picPurchasing
                        ? $supplier->picPurchasing->nama
                        : null,
                    "supplier_id" => $bahanBaku->id,
                    "price" => $bahanBaku->harga_per_satuan,
                    "satuan" => $bahanBaku->satuan,
                    "stok" => $bahanBaku->stok,
                ];
            }
        }

        // Sort by price (cheapest first)
        usort($supplierOptions, function ($a, $b) {
            return $a["price"] <=> $b["price"];
        });

        return $supplierOptions;
    }

    private function getSupplierSpecificPriceHistory($bahanBakuSupplierId)
    {
        // Get price history for a specific supplier material
        $supplierMaterial = \App\Models\BahanBakuSupplier::with([
            "riwayatHarga" => function ($q) {
                $q->orderBy("tanggal_perubahan", "asc")->limit(30); // Last 30 records
            },
        ])
            ->where("id", $bahanBakuSupplierId)
            ->first();

        if (!$supplierMaterial) {
            return [];
        }

        // If no price history exists, fallback to current price in bahan_baku_supplier
        if ($supplierMaterial->riwayatHarga->isEmpty()) {
            if ($supplierMaterial->harga_per_satuan) {
                return [
                    [
                        "tanggal" => now()->format("Y-m-d"),
                        "harga" => (float) $supplierMaterial->harga_per_satuan,
                        "formatted_tanggal" => now()->format("d M"),
                        "is_fallback" => true,
                    ],
                ];
            }
            return [];
        }

        return $supplierMaterial->riwayatHarga
            ->map(function ($history) {
                return [
                    "tanggal" => $history->tanggal_perubahan->format("Y-m-d"),
                    "harga" => (float) $history->harga_baru,
                    "formatted_tanggal" => $history->tanggal_perubahan->format(
                        "d M",
                    ),
                ];
            })
            ->toArray();
    }

    private function getKlienPriceHistory($materialId)
    {
        // Get client price history from RiwayatHargaKlien for the specific material
        $klienMaterial = BahanBakuKlien::with([
            "riwayatHarga" => function ($q) {
                $q->orderBy("tanggal_perubahan", "asc")->limit(30); // Last 30 records
            },
        ])
            ->where("id", $materialId)
            ->first();

        $priceHistory = [];

        if ($klienMaterial && $klienMaterial->riwayatHarga) {
            $priceHistory = $klienMaterial->riwayatHarga
                ->map(function ($history) {
                    return [
                        "tanggal" => $history->tanggal_perubahan->format(
                            "Y-m-d",
                        ),
                        "harga" => (float) $history->harga_approved_baru,
                        "formatted_tanggal" => $history->tanggal_perubahan->format(
                            "d M",
                        ),
                        "is_custom" => false,
                    ];
                })
                ->toArray();
        }

        // Check if this material has a custom price and add today's data point
        $selectedMaterial = collect($this->selectedMaterials)->firstWhere(
            "material_id",
            $materialId,
        );
        if (
            $selectedMaterial &&
            ($selectedMaterial["is_custom_price"] ?? false)
        ) {
            $today = now();
            $priceHistory[] = [
                "tanggal" => $today->format("Y-m-d"),
                "harga" => (float) $selectedMaterial["custom_price"],
                "formatted_tanggal" => $today->format("d M"),
                "is_custom" => true,
            ];
        }

        return $priceHistory;
    }

    private function getSupplierPriceHistory($materialName)
    {
        // Get supplier price history from materials with matching names
        $supplierMaterial = \App\Models\BahanBakuSupplier::with([
            "riwayatHarga" => function ($q) {
                $q->orderBy("tanggal_perubahan", "asc")->limit(30); // Last 30 records
            },
        ])
            ->where("nama", "like", "%" . $materialName . "%")
            ->whereNotNull("harga_per_satuan")
            ->orderBy("harga_per_satuan", "asc")
            ->first();

        if (!$supplierMaterial) {
            return [];
        }

        // If no price history exists, fallback to current price in bahan_baku_supplier
        if ($supplierMaterial->riwayatHarga->isEmpty()) {
            if ($supplierMaterial->harga_per_satuan) {
                return [
                    [
                        "tanggal" => now()->format("Y-m-d"),
                        "harga" => (float) $supplierMaterial->harga_per_satuan,
                        "formatted_tanggal" => now()->format("d M"),
                        "is_fallback" => true,
                    ],
                ];
            }
            return [];
        }

        return $supplierMaterial->riwayatHarga
            ->map(function ($history) {
                return [
                    "tanggal" => $history->tanggal_perubahan->format("Y-m-d"),
                    "harga" => (float) $history->harga_baru,
                    "formatted_tanggal" => $history->tanggal_perubahan->format(
                        "d M",
                    ),
                ];
            })
            ->toArray();
    }

    private function getAvailableMaterials()
    {
        if (!$this->selectedKlien || !$this->selectedKlienCabang) {
            return collect();
        }

        return BahanBakuKlien::with("klien")
            ->whereHas("klien", function ($q) {
                $q->where("nama", $this->selectedKlien)->where(
                    "cabang",
                    $this->selectedKlienCabang,
                );
            })
            ->where("status", "aktif")
            ->whereNotNull("harga_approved")
            ->get();
    }

    public function exportPdf()
    {
        if (empty($this->selectedMaterials)) {
            session()->flash("error", "Tidak ada data untuk diekspor");
            return;
        }

        // Generate PDF content
        $pdfData = [
            "klien" =>
                $this->selectedKlien . " - " . $this->selectedKlienCabang,
            "analysis" => $this->marginAnalysis,
            "summary" => [
                "total_revenue" => $this->totalRevenue,
                "total_cost" => $this->totalCost,
                "total_profit" => $this->totalProfit,
                "overall_margin" => $this->overallMargin,
            ],
            "generated_at" => now()->format("d/m/Y H:i:s"),
        ];

        // For now, download as JSON (can be replaced with actual PDF library)
        $fileName =
            "analisis-penawaran-" .
            str_replace(" ", "-", strtolower($this->selectedKlien)) .
            "-" .
            now()->format("Y-m-d-H-i-s") .
            ".json";

        session()->flash(
            "message",
            "Analisis berhasil diekspor sebagai " . $fileName,
        );

        // In a real implementation, you would use a PDF library like DomPDF or wkhtmltopdf
        $this->dispatch("download-analysis", [
            "data" => $pdfData,
            "fileName" => $fileName,
        ]);
    }

    public function buatOrder()
    {
        if (empty($this->selectedMaterials)) {
            session()->flash("error", "Tidak ada material untuk membuat order");
            return;
        }

        // Create purchase order based on analysis
        $orderData = [];

        foreach ($this->marginAnalysis as $analysis) {
            $orderData[] = [
                "material" => $analysis["nama"],
                "quantity" => $analysis["quantity"],
                "supplier" => $analysis["best_supplier"],
                "price" => $analysis["supplier_price"],
                "total_cost" => $analysis["cost"],
                "expected_revenue" => $analysis["revenue"],
                "expected_profit" => $analysis["profit"],
            ];
        }

        // Store order data in session for order creation page
        session([
            "pending_order" => [
                "klien" =>
                    $this->selectedKlien . " - " . $this->selectedKlienCabang,
                "materials" => $orderData,
                "total_cost" => $this->totalCost,
                "expected_revenue" => $this->totalRevenue,
                "expected_profit" => $this->totalProfit,
                "created_from_analysis" => true,
            ],
        ]);

        session()->flash(
            "message",
            "Order siap dibuat. Data telah disimpan untuk proses selanjutnya.",
        );

        // Redirect to order creation page (when implemented)
        // return redirect()->route('order.create');
    }

    public function saveDraft()
    {
        return $this->savePenawaran("draft");
    }

    public function submitForVerification()
    {
        return $this->savePenawaran("menunggu_verifikasi");
    }

    private function savePenawaran($status = "draft")
    {
        // Validation
        if (empty($this->selectedMaterials)) {
            session()->flash(
                "error",
                "Tidak ada material untuk membuat penawaran",
            );
            \Log::error("Save penawaran failed: No materials selected");
            return;
        }

        if (!$this->selectedKlienId) {
            session()->flash("error", "Klien belum dipilih");
            \Log::error("Save penawaran failed: No client selected");
            return;
        }

        \Log::info("Starting savePenawaran", [
            "status" => $status,
            "klien_id" => $this->selectedKlienId,
            "materials_count" => count($this->selectedMaterials),
            "total_revenue" => $this->totalRevenue,
        ]);

        try {
            DB::beginTransaction();

            if ($this->editMode && $this->penawaranId) {
                // Update existing penawaran
                $penawaran = PenawaranModel::findOrFail($this->penawaranId);

                // Can only edit draft
                if ($penawaran->status !== "draft") {
                    throw new \Exception(
                        "Hanya penawaran dengan status draft yang dapat diedit",
                    );
                }

                $penawaran->update([
                    "klien_id" => $this->selectedKlienId,
                    "status" => $status,
                    "total_revenue" => $this->totalRevenue,
                    "total_cost" => $this->totalCost,
                    "total_profit" => $this->totalProfit,
                    "margin_percentage" => $this->overallMargin,
                ]);

                // Delete existing details and alternatives
                foreach ($penawaran->details as $detail) {
                    $detail->alternativeSuppliers()->delete();
                }
                $penawaran->details()->delete();
            } else {
                // Create new Penawaran record
                $penawaran = PenawaranModel::create([
                    "klien_id" => $this->selectedKlienId,
                    "status" => $status,
                    "tanggal_penawaran" => now(),
                    "tanggal_berlaku_sampai" => now()->addDays(30), // Valid for 30 days
                    "total_revenue" => $this->totalRevenue,
                    "total_cost" => $this->totalCost,
                    "total_profit" => $this->totalProfit,
                    "margin_percentage" => $this->overallMargin,
                    "created_by" => auth()->id() ?? 1, // Fallback to user ID 1 if not authenticated
                ]);
            }

            // Create PenawaranDetail records with alternative suppliers
            foreach ($this->marginAnalysis as $index => $analysis) {
                // Get selected supplier for this material
                $selectedSupplierId =
                    $this->selectedSuppliers[$index] ??
                    $analysis["best_supplier_id"];

                // Find the selected supplier's data
                $selectedSupplierData = collect(
                    $analysis["supplier_options"],
                )->firstWhere("supplier_id", $selectedSupplierId);

                if (!$selectedSupplierData) {
                    // Fallback to best supplier if not found
                    $selectedSupplierData = collect(
                        $analysis["supplier_options"],
                    )->firstWhere("is_best", true);
                    $selectedSupplierId = $selectedSupplierData["supplier_id"];
                }

                // Determine the actual BahanBakuSupplier record for the selected supplier
                $bahanBakuSupplierRecord = BahanBakuSupplier::find(
                    $selectedSupplierId,
                );

                // Calculate financials based on the selected (best) supplier
                $quantity = $analysis["quantity"];
                $revenue = $analysis["revenue"];
                $hargaSupplier = $selectedSupplierData["price"] ?? 0;
                $subtotalCost = $hargaSupplier * $quantity;
                $subtotalProfit = $revenue - $subtotalCost;
                $marginPercentage =
                    $revenue > 0 ? ($subtotalProfit / $revenue) * 100 : 0;

                // Create detail record and set the best supplier as the selected one
                $detail = PenawaranDetail::create([
                    "penawaran_id" => $penawaran->id,
                    "bahan_baku_klien_id" => $analysis["material_id"],
                    // Persist the supplier table id and the bahan_baku_supplier id so other
                    // UI/logic that expects a selected supplier continues to work.
                    "supplier_id" => $bahanBakuSupplierRecord
                        ? $bahanBakuSupplierRecord->supplier_id
                        : null,
                    "bahan_baku_supplier_id" => $bahanBakuSupplierRecord
                        ? $bahanBakuSupplierRecord->id
                        : null,
                    "nama_material" => $analysis["nama"],
                    "satuan" => $analysis["satuan"],
                    "quantity" => $quantity,
                    "harga_klien" => $analysis["klien_price"],
                    "harga_supplier" => $hargaSupplier,
                    "subtotal_cost" => $subtotalCost,
                    "subtotal_profit" => $subtotalProfit,
                    "margin_percentage" => $marginPercentage,
                    "is_custom_price" => $analysis["is_custom_price"] ?? false,
                    "subtotal_revenue" => $revenue,
                ]);

                // Persist ALL supplier offers for this detail into penawaran_alternative_suppliers
                // (including the previously 'selected' or 'best' supplier).
                foreach ($analysis["supplier_options"] as $supplierOption) {
                    // supplierOption['supplier_id'] currently holds the BahanBakuSupplier id
                    $altBahanBakuSupplier = BahanBakuSupplier::find(
                        $supplierOption["supplier_id"],
                    );

                    if ($altBahanBakuSupplier) {
                        PenawaranAlternativeSupplier::create([
                            "penawaran_detail_id" => $detail->id,
                            // Use the actual Supplier id for the uniqueness constraint
                            "supplier_id" => $altBahanBakuSupplier->supplier_id,
                            "bahan_baku_supplier_id" =>
                                $altBahanBakuSupplier->id,
                            "harga_supplier" => $supplierOption["price"],
                        ]);
                    }
                }
            }

            DB::commit();

            \Log::info("Penawaran saved successfully", [
                "penawaran_id" => $penawaran->id,
                "nomor_penawaran" => $penawaran->nomor_penawaran,
                "status" => $penawaran->status,
            ]);

            // Send notification to direktur when submitting for verification
            if ($status === "menunggu_verifikasi") {
                NotificationService::notifyPenawaranSubmitted($penawaran);
            }

            // Success message
            $statusText = $status === "draft" ? "draft" : "verifikasi";
            $actionText = $this->editMode ? "diperbarui" : "disimpan";
            session()->flash(
                "message",
                "Penawaran {$penawaran->nomor_penawaran} berhasil {$actionText} sebagai {$statusText}",
            );

            // Redirect to history page
            return redirect()->route("penawaran.index");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Save penawaran failed with exception", [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
            session()->flash(
                "error",
                "Gagal menyimpan penawaran: " . $e->getMessage(),
            );
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

        session()->flash("message", "Form berhasil direset");
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
