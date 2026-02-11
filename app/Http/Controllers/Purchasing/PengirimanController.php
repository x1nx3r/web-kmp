<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\PengirimanDetail;
use App\Models\Order;
use App\Models\Klien;
use App\Models\Forecast;
use App\Models\ForecastDetail;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PengirimanController extends Controller
{
    /**
     * Check if pengiriman is partial delivery (<=70% of forecast)
     * Returns array with percentage and isPartial flag
     * Uses direct fields from pengiriman and forecast tables (no details needed)
     */
    private function checkPartialDelivery(Pengiriman $pengiriman)
    {
        try {
            // Load forecast if not already loaded
            if (!$pengiriman->relationLoaded('forecast')) {
                $pengiriman->load('forecast');
            }
            
            // If no forecast, return not partial
            if (!$pengiriman->forecast) {
                return [
                    'isPartial' => false,
                    'percentage' => 0,
                    'totalQtyKirim' => 0,
                    'totalQtyForecast' => 0
                ];
            }
            
            // Get total qty from forecast table directly
            $totalQtyForecast = (float) $pengiriman->forecast->total_qty_forecast;
            
            // Get total qty kirim from pengiriman table directly
            $totalQtyKirim = (float) $pengiriman->total_qty_kirim;
            
            // Calculate percentage
            $percentage = 0;
            if ($totalQtyForecast > 0) {
                $percentage = ($totalQtyKirim / $totalQtyForecast) * 100;
            }
            
            // Check if partial (<=70%)
            $isPartial = $percentage > 0 && $percentage <= 70;
            
            return [
                'isPartial' => $isPartial,
                'percentage' => round($percentage, 2),
                'totalQtyKirim' => $totalQtyKirim,
                'totalQtyForecast' => $totalQtyForecast
            ];
        } catch (\Exception $e) {
            Log::error('Error in checkPartialDelivery: ' . $e->getMessage());
            return [
                'isPartial' => false,
                'percentage' => 0,
                'totalQtyKirim' => 0,
                'totalQtyForecast' => 0
            ];
        }
    }
    
    /**
     * Reduce qty on OrderDetail - ONLY ONCE per pengiriman
     * This method should be called whenever status changes (except to 'gagal')
     * It ensures qty is only reduced once using the qty_reduced flag
     */
    private function reduceOrderDetailQty(Pengiriman $pengiriman)
    {
        // Check if qty has already been reduced
        if ($pengiriman->qty_reduced) {
            Log::info("Qty already reduced for Pengiriman ID: {$pengiriman->id}, skipping reduction");
            return false;
        }

        // Load details with orderDetail relationship if not loaded
        if (!$pengiriman->relationLoaded('details')) {
            $pengiriman->load('details.orderDetail');
        }

        $detailsUpdated = 0;
        
        // Update related OrderDetail records
        foreach ($pengiriman->details as $detail) {
            if ($detail->orderDetail) {
                $orderDetail = $detail->orderDetail;
                
                // Decrease qty by qty_kirim
                $oldQty = (float)$orderDetail->qty;
                $newQty = $oldQty - (float)$detail->qty_kirim;
                $orderDetail->qty = max(0, $newQty); // Ensure qty doesn't go negative
                
                // Recalculate total_harga based on new qty and harga_jual
                $orderDetail->total_harga = (float)$orderDetail->qty * (float)$orderDetail->harga_jual;
                
                // Save quietly to prevent triggering the 'saved' event that updates parent Order
                $orderDetail->saveQuietly();
                
                $detailsUpdated++;
                
                Log::info("Reduced OrderDetail ID: {$orderDetail->id}, Old Qty: {$oldQty}, Reduced by: {$detail->qty_kirim}, New Qty: {$orderDetail->qty}, New Total: {$orderDetail->total_harga}");
            }
        }

        // Mark as qty_reduced
        $pengiriman->qty_reduced = true;
        $pengiriman->saveQuietly(); // Use saveQuietly to not trigger observers
        
        Log::info("Marked Pengiriman ID: {$pengiriman->id} as qty_reduced. Updated {$detailsUpdated} order details.");
        
        return true;
    }

    /**
     * Restore qty on OrderDetail when pengiriman is cancelled/failed
     * This should only be called if qty was previously reduced
     */
    private function restoreOrderDetailQty(Pengiriman $pengiriman)
    {
        // Only restore if qty was previously reduced
        if (!$pengiriman->qty_reduced) {
            Log::info("Qty was never reduced for Pengiriman ID: {$pengiriman->id}, skipping restore");
            return false;
        }

        // Load details with orderDetail relationship if not loaded
        if (!$pengiriman->relationLoaded('details')) {
            $pengiriman->load('details.orderDetail');
        }

        $detailsRestored = 0;
        
        // Restore related OrderDetail records
        foreach ($pengiriman->details as $detail) {
            if ($detail->orderDetail) {
                $orderDetail = $detail->orderDetail;
                
                // Increase qty by qty_kirim (restore)
                $oldQty = (float)$orderDetail->qty;
                $newQty = $oldQty + (float)$detail->qty_kirim;
                $orderDetail->qty = $newQty;
                
                // Recalculate total_harga based on new qty and harga_jual
                $orderDetail->total_harga = (float)$orderDetail->qty * (float)$orderDetail->harga_jual;
                
                // Save quietly to prevent triggering the 'saved' event
                $orderDetail->saveQuietly();
                
                $detailsRestored++;
                
                Log::info("Restored OrderDetail ID: {$orderDetail->id}, Old Qty: {$oldQty}, Restored by: {$detail->qty_kirim}, New Qty: {$orderDetail->qty}, New Total: {$orderDetail->total_harga}");
            }
        }

        // Mark as qty NOT reduced anymore
        $pengiriman->qty_reduced = false;
        $pengiriman->saveQuietly();
        
        Log::info("Marked Pengiriman ID: {$pengiriman->id} as qty NOT reduced. Restored {$detailsRestored} order details.");
        
        return true;
    }

    public function index(Request $request): View
    {
        // Base query dengan eager loading
        $baseQuery = function ($status) use ($request) {
            $query = Pengiriman::with([
                "order:id,po_number,klien_id",
                "order.klien:id,nama,cabang",
                "purchasing:id,nama",
                "pengirimanDetails",
                "forecast:id,total_qty_forecast",
                "approvalPembayaran:id,pengiriman_id,refraksi_type,refraksi_value,refraksi_amount,qty_before_refraksi,qty_after_refraksi,amount_before_refraksi,amount_after_refraksi,bukti_pembayaran",
            ])
                ->whereNotNull("purchase_order_id")
                ->whereNotNull("purchasing_id")
                ->where("status", $status);

            // Apply search filter for pengiriman masuk
            if ($status === "pending" && $request->filled("search_masuk")) {
                $search = $request->get("search_masuk");
                $query->where(function ($q) use ($search) {
                    $q->whereHas("order", function ($orderQuery) use ($search) {
                        $orderQuery->where("po_number", "LIKE", "%{$search}%");
                    })
                        ->orWhereHas("purchasing", function (
                            $purchasingQuery,
                        ) use ($search) {
                            $purchasingQuery->where(
                                "nama",
                                "LIKE",
                                "%{$search}%",
                            );
                        })
                        ->orWhere("no_pengiriman", "LIKE", "%{$search}%");
                });
            }

            // Apply search filter for pengiriman berhasil
            if ($status === "berhasil" && $request->filled("search_berhasil")) {
                $search = $request->get("search_berhasil");
                $query->where(function ($q) use ($search) {
                    $q->whereHas("order", function ($orderQuery) use ($search) {
                        $orderQuery->where("po_number", "LIKE", "%{$search}%");
                    })
                        ->orWhereHas("purchasing", function (
                            $purchasingQuery,
                        ) use ($search) {
                            $purchasingQuery->where(
                                "nama",
                                "LIKE",
                                "%{$search}%",
                            );
                        })
                        ->orWhere("no_pengiriman", "LIKE", "%{$search}%");
                });
            }

            // Apply search filter for pengiriman gagal
            if ($status === "gagal" && $request->filled("search_gagal")) {
                $search = $request->get("search_gagal");
                $query->where(function ($q) use ($search) {
                    $q->whereHas("order", function ($orderQuery) use ($search) {
                        $orderQuery->where("po_number", "LIKE", "%{$search}%");
                    })
                        ->orWhereHas("purchasing", function (
                            $purchasingQuery,
                        ) use ($search) {
                            $purchasingQuery->where(
                                "nama",
                                "LIKE",
                                "%{$search}%",
                            );
                        })
                        ->orWhere("no_pengiriman", "LIKE", "%{$search}%");
                });
            }

            // Apply search filter for menunggu verifikasi
            if (
                $status === "menunggu_verifikasi" &&
                $request->filled("search_verifikasi")
            ) {
                $search = $request->get("search_verifikasi");
                $query->where(function ($q) use ($search) {
                    $q->whereHas("order", function ($orderQuery) use ($search) {
                        $orderQuery->where("po_number", "LIKE", "%{$search}%");
                    })
                        ->orWhereHas("purchasing", function (
                            $purchasingQuery,
                        ) use ($search) {
                            $purchasingQuery->where(
                                "nama",
                                "LIKE",
                                "%{$search}%",
                            );
                        })
                        ->orWhere("no_pengiriman", "LIKE", "%{$search}%");
                });
            }

            // Apply search filter for menunggu fisik
            if (
                $status === "menunggu_fisik" &&
                $request->filled("search_fisik")
            ) {
                $search = $request->get("search_fisik");
                $query->where(function ($q) use ($search) {
                    $q->whereHas("order", function ($orderQuery) use ($search) {
                        $orderQuery->where("po_number", "LIKE", "%{$search}%");
                    })
                        ->orWhereHas("purchasing", function (
                            $purchasingQuery,
                        ) use ($search) {
                            $purchasingQuery->where(
                                "nama",
                                "LIKE",
                                "%{$search}%",
                            );
                        })
                        ->orWhere("no_pengiriman", "LIKE", "%{$search}%");
                });
            }

            // Apply purchasing filter for pengiriman masuk
            if (
                $status === "pending" &&
                $request->filled("filter_purchasing")
            ) {
                $query->where(
                    "purchasing_id",
                    $request->get("filter_purchasing"),
                );
            }

            // Apply purchasing filter for pengiriman berhasil
            if (
                $status === "berhasil" &&
                $request->filled("filter_purchasing_berhasil")
            ) {
                $query->where(
                    "purchasing_id",
                    $request->get("filter_purchasing_berhasil"),
                );
            }

            // Apply purchasing filter for pengiriman gagal
            if (
                $status === "gagal" &&
                $request->filled("filter_purchasing_gagal")
            ) {
                $query->where(
                    "purchasing_id",
                    $request->get("filter_purchasing_gagal"),
                );
            }

            // Apply purchasing filter for menunggu verifikasi
            if (
                $status === "menunggu_verifikasi" &&
                $request->filled("filter_purchasing_verifikasi")
            ) {
                $query->where(
                    "purchasing_id",
                    $request->get("filter_purchasing_verifikasi"),
                );
            }

            // Apply purchasing filter for menunggu fisik
            if (
                $status === "menunggu_fisik" &&
                $request->filled("filter_purchasing_fisik")
            ) {
                $query->where(
                    "purchasing_id",
                    $request->get("filter_purchasing_fisik"),
                );
            }

            // Apply date range filter for pengiriman berhasil
            if (
                $status === "berhasil" &&
                $request->filled("date_range_berhasil")
            ) {
                $query->whereDate(
                    "tanggal_kirim",
                    $request->get("date_range_berhasil"),
                );
            }

            // Apply date range filter for pengiriman gagal
            if ($status === "gagal" && $request->filled("date_range_gagal")) {
                $query->whereDate(
                    "tanggal_kirim",
                    $request->get("date_range_gagal"),
                );
            }

            // Apply date sorting for pengiriman masuk
            if ($status === "pending" && $request->filled("sort_date_masuk")) {
                $sortOrder =
                    $request->get("sort_date_masuk") === "oldest"
                        ? "asc"
                        : "desc";
                $query->orderBy("created_at", $sortOrder);
            }
            // Apply date sorting for pengiriman berhasil
            elseif (
                $status === "berhasil" &&
                $request->filled("sort_order_berhasil")
            ) {
                $sortOrder =
                    $request->get("sort_order_berhasil") === "oldest"
                        ? "asc"
                        : "desc";
                $query->orderBy("created_at", $sortOrder);
            }
            // Apply date sorting for pengiriman gagal
            elseif (
                $status === "gagal" &&
                $request->filled("sort_order_gagal")
            ) {
                $sortOrder =
                    $request->get("sort_order_gagal") === "oldest"
                        ? "asc"
                        : "desc";
                $query->orderBy("created_at", $sortOrder);
            }
            // Apply date sorting for menunggu verifikasi
            elseif (
                $status === "menunggu_verifikasi" &&
                $request->filled("sort_date_verifikasi")
            ) {
                $sortOrder =
                    $request->get("sort_date_verifikasi") === "oldest"
                        ? "asc"
                        : "desc";
                $query->orderBy("created_at", $sortOrder);
            }
            // Apply date sorting for menunggu fisik
            elseif (
                $status === "menunggu_fisik" &&
                $request->filled("sort_date_fisik")
            ) {
                $sortOrder =
                    $request->get("sort_date_fisik") === "oldest"
                        ? "asc"
                        : "desc";
                $query->orderBy("created_at", $sortOrder);
            } else {
                $query->orderBy("created_at", "desc");
            }

            return $query;
        };
        // Get data for each status
        $pengirimanMasuk = $baseQuery("pending")->paginate(
            10,
            ["*"],
            "masuk_page",
        );
        $menungguVerifikasi = $baseQuery("menunggu_verifikasi")->paginate(
            10,
            ["*"],
            "verifikasi_page",
        );
        $menungguFisik = $baseQuery("menunggu_fisik")->paginate(
            10,
            ["*"],
            "fisik_page",
        );
        $pengirimanBerhasil = $baseQuery("berhasil")->paginate(
            10,
            ["*"],
            "berhasil_page",
        );
        $pengirimanGagal = $baseQuery("gagal")->paginate(
            10,
            ["*"],
            "gagal_page",
        );
        
        // Add partial delivery info to each item
        foreach ($menungguVerifikasi as $pengiriman) {
            $pengiriman->partialInfo = $this->checkPartialDelivery($pengiriman);
        }
        
        foreach ($menungguFisik as $pengiriman) {
            $pengiriman->partialInfo = $this->checkPartialDelivery($pengiriman);
        }
        
        foreach ($pengirimanBerhasil as $pengiriman) {
            $pengiriman->partialInfo = $this->checkPartialDelivery($pengiriman);
        }

        return view(
            "pages.purchasing.pengiriman",
            compact(
                "pengirimanMasuk",
                "menungguVerifikasi",
                "menungguFisik",
                "pengirimanBerhasil",
                "pengirimanGagal",
            ),
        );
    }

    /**
     * Show the form for creating a new pengiriman.
     */
    public function create(): View
    {
        $klien = Klien::all();
        $orders = Order::where("status", ["dikonfirmasi", "diproses"])->get();

        return view(
            "pages.purchasing.pengiriman-create",
            compact("klien", "orders"),
        );
    }

    /**
     * Store a newly created pengiriman in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "purchase_order_id" => "required|exists:orders,id",
            "klien_id" => "required|exists:klien,id",
            "tanggal_pengiriman" => "required|date",
            "status" => "required|in:pending,in_transit,delivered,cancelled",
            "keterangan" => "nullable|string",
            "details" => "required|array",
            "details.*.bahan_baku_id" => "required|exists:bahan_baku_klien,id",
            "details.*.jumlah" => "required|numeric|min:0",
            "details.*.harga_satuan" => "required|numeric|min:0",
        ]);

        $pengiriman = Pengiriman::create([
            "purchase_order_id" => $validated["purchase_order_id"],
            "klien_id" => $validated["klien_id"],
            "tanggal_pengiriman" => $validated["tanggal_pengiriman"],
            "status" => $validated["status"],
            "keterangan" => $validated["keterangan"],
            "total_amount" => 0, // Will be calculated after adding details
        ]);

        $totalAmount = 0;

        foreach ($validated["details"] as $detail) {
            $subtotal = $detail["jumlah"] * $detail["harga_satuan"];
            $totalAmount += $subtotal;

            PengirimanDetail::create([
                "pengiriman_id" => $pengiriman->id,
                "bahan_baku_id" => $detail["bahan_baku_id"],
                "jumlah" => $detail["jumlah"],
                "harga_satuan" => $detail["harga_satuan"],
                "subtotal" => $subtotal,
            ]);
        }

        $pengiriman->update(["total_amount" => $totalAmount]);

        return redirect()
            ->route("purchasing.pengiriman.index")
            ->with("success", "Data pengiriman berhasil dibuat.");
    }

    /**
     * Display the specified pengiriman.
     */
    public function show(Pengiriman $pengiriman): View
    {
        $pengiriman->load(["klien", "order", "details.bahanBaku"]);

        return view("pages.purchasing.pengiriman-show", compact("pengiriman"));
    }

    /**
     * Show the form for editing the specified pengiriman.
     */
    public function edit(Pengiriman $pengiriman): View
    {
        $pengiriman->load(["details"]);
        $klien = Klien::all();
        $orders = Order::where("status", "approved")->get();

        return view(
            "pages.purchasing.pengiriman-edit",
            compact("pengiriman", "klien", "orders"),
        );
    }

    /**
     * Update the specified pengiriman in storage.
     */
    public function update(
        Request $request,
        Pengiriman $pengiriman,
    ): RedirectResponse {
        $validated = $request->validate([
            "purchase_order_id" => "required|exists:orders,id",
            "klien_id" => "required|exists:klien,id",
            "tanggal_pengiriman" => "required|date",
            "status" => "required|in:pending,in_transit,delivered,cancelled",
            "keterangan" => "nullable|string",
            "details" => "required|array",
            "details.*.bahan_baku_id" => "required|exists:bahan_baku_klien,id",
            "details.*.jumlah" => "required|numeric|min:0",
            "details.*.harga_satuan" => "required|numeric|min:0",
        ]);

        $pengiriman->update([
            "purchase_order_id" => $validated["purchase_order_id"],
            "klien_id" => $validated["klien_id"],
            "tanggal_pengiriman" => $validated["tanggal_pengiriman"],
            "status" => $validated["status"],
            "keterangan" => $validated["keterangan"],
        ]);

        // Delete existing details
        $pengiriman->details()->delete();

        $totalAmount = 0;

        // Create new details
        foreach ($validated["details"] as $detail) {
            $subtotal = $detail["jumlah"] * $detail["harga_satuan"];
            $totalAmount += $subtotal;

            PengirimanDetail::create([
                "pengiriman_id" => $pengiriman->id,
                "bahan_baku_id" => $detail["bahan_baku_id"],
                "jumlah" => $detail["jumlah"],
                "harga_satuan" => $detail["harga_satuan"],
                "subtotal" => $subtotal,
            ]);
        }

        $pengiriman->update(["total_amount" => $totalAmount]);

        return redirect()
            ->route("purchasing.pengiriman.index")
            ->with("success", "Data pengiriman berhasil diperbarui.");
    }

    /**
     * Remove the specified pengiriman from storage.
     */
    public function destroy(Pengiriman $pengiriman): RedirectResponse
    {
        $pengiriman->details()->delete();
        $pengiriman->delete();

        return redirect()
            ->route("purchasing.pengiriman.index")
            ->with("success", "Data pengiriman berhasil dihapus.");
    }

    /**
     * Update status pengiriman
     */
    public function updateStatus(Request $request, Pengiriman $pengiriman)
    {
        $validated = $request->validate([
            "status" =>
                "required|in:pending,menunggu_verifikasi,berhasil,gagal",
            "catatan" => "nullable|string",
        ]);

        $pengiriman->status = $validated["status"];
        if (isset($validated["catatan"])) {
            $pengiriman->catatan = $validated["catatan"];
        }
        $pengiriman->save();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                "success" => true,
                "message" => "Status pengiriman berhasil diperbarui",
                "data" => $pengiriman,
            ]);
        }

        return redirect()
            ->back()
            ->with("success", "Status pengiriman berhasil diperbarui.");
    }

    /**
     * Get pengiriman detail via AJAX
     */
    public function getDetail(Request $request, $id)
    {
        try {
            $pengiriman = Pengiriman::with([
                "order",
                "order.klien",
                "purchasing",
                "pengirimanDetails",
            ])->findOrFail($id);

            return response()->json([
                "success" => true,
                "pengiriman" => $pengiriman,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Gagal memuat detail: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get modal aksi content for pengiriman
     */
    public function getAksiModal(Request $request, $id)
    {
        try {
            Log::info("Loading aksi modal for pengiriman ID: {$id}");

            // Load step by step to debug relationship issues
            $pengiriman = Pengiriman::with([
                "order",
                "order.klien",
                "purchasing",
                "forecast",
                "pengirimanDetails.bahanBakuSupplier",
                "pengirimanDetails.bahanBakuSupplier.supplier",
                "pengirimanDetails.orderDetail", // Add orderDetail to get harga_jual for margin calculation
            ])->findOrFail($id);

            Log::info(
                "Pengiriman loaded with " .
                    $pengiriman->pengirimanDetails->count() .
                    " details",
            );

            // Load picPurchasing separately to avoid chain issues
            foreach ($pengiriman->pengirimanDetails as $detail) {
                if (
                    $detail->bahanBakuSupplier &&
                    $detail->bahanBakuSupplier->supplier
                ) {
                    $detail->bahanBakuSupplier->supplier->load("picPurchasing");
                }
            }

            // Load riwayat harga
            foreach ($pengiriman->pengirimanDetails as $detail) {
                if ($detail->bahanBakuSupplier) {
                    $detail->bahanBakuSupplier->load([
                        "riwayatHarga" => function ($query) {
                            $query->latest("tanggal_perubahan")->limit(1);
                        },
                    ]);
                }
            }

            // Debug: Log pengiriman details
            Log::info("Pengiriman details data:", [
                "id" => $pengiriman->id,
                "no_pengiriman" => $pengiriman->no_pengiriman,
                "tanggal_kirim" => $pengiriman->tanggal_kirim,
                "hari_kirim" => $pengiriman->hari_kirim,
                "details_count" => $pengiriman->pengirimanDetails->count(),
                "first_detail" => $pengiriman->pengirimanDetails->first()
                    ? [
                        "id" => $pengiriman->pengirimanDetails->first()->id,
                        "qty_kirim" => $pengiriman->pengirimanDetails->first()
                            ->qty_kirim,
                        "bahan_baku" =>
                            $pengiriman->pengirimanDetails->first()
                                ->bahanBakuSupplier->nama ?? "N/A",
                    ]
                    : null,
            ]);

            // Return HTML content for modal
            return view(
                "pages.purchasing.pengiriman.pengiriman-masuk.detail",
                compact("pengiriman"),
            );
        } catch (\Exception $e) {
            Log::error("Error in getAksiModal: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return response(
                '<div class="text-center py-8 text-red-500">Error: ' .
                    $e->getMessage() .
                    "<br><small>" .
                    $e->getFile() .
                    ":" .
                    $e->getLine() .
                    "</small></div>",
                500,
            );
        }
    }

    /**
     * Show submit modal for pengiriman confirmation
     */
    public function getSubmitModal(Request $request)
    {
        try {
            $pengiriman = Pengiriman::with([
                "order",
                "order.klien",
                "purchasing",
                "forecast",
            ])->findOrFail($request->get("pengiriman_id", 1)); // Default to 1 for testing

            return view(
                "pages.purchasing.pengiriman.pengiriman-masuk.submit",
                compact("pengiriman"),
            );
        } catch (\Exception $e) {
            return response(
                '<div class="text-center py-8 text-red-500">Error: ' .
                    $e->getMessage() .
                    "</div>",
                500,
            );
        }
    }

    /**
     * Store pengiriman data (Submit for verification)
     */
    public function submitPengiriman(Request $request)
    {
        // Check user role authorization - Only Direktur, Manager Purchasing, and Staff Purchasing can submit
        $user = Auth::user();
        if (
            !in_array($user->role, [
                "direktur",
                "manager_purchasing",
                "staff_purchasing",
            ])
        ) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Anda tidak memiliki akses untuk mengajukan verifikasi pengiriman. Hanya Direktur, Manager Purchasing, dan Staff Purchasing yang dapat melakukan aksi ini.",
                ],
                403,
            );
        }

        // For Staff Purchasing, ensure they are the PIC
        if ($user->role === "staff_purchasing") {
            $pengiriman = Pengiriman::find($request->pengiriman_id);
            if ($pengiriman && $pengiriman->purchasing_id !== $user->id) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Anda hanya dapat mengajukan verifikasi untuk pengiriman yang Anda tangani sebagai PIC.",
                    ],
                    403,
                );
            }
        }

        try {
            // Validate request
            $validatedData = $request->validate(
                [
                    "pengiriman_id" => "required|exists:pengiriman,id",
                    "tanggal_kirim" => "required|date",
                    "hari_kirim" => "required|string",
                    "total_qty_kirim" => "required|numeric|min:0",
                    "total_harga_kirim" => "required|numeric|min:0",
                    "bukti_foto_bongkar" => "nullable|array",
                    "bukti_foto_bongkar.*" =>
                        "file|mimes:jpeg,png,jpg,pdf|max:10240",
                    "foto_tanda_terima" =>
                        "nullable|file|mimes:jpeg,png,jpg,pdf|max:10240",
                    "catatan" => "nullable|string",
                    "catatan_refraksi" => "nullable|string",
                    "details" => "required|array|min:1",
                    "details.*.bahan_baku_supplier_id" =>
                        "required|exists:bahan_baku_supplier,id",
                    "details.*.qty_kirim" => "required|numeric|min:0",
                    // ❌ JANGAN validasi harga dari request - harga sudah frozen di database
                    // "details.*.harga_satuan" => "nullable|numeric|min:0", // Optional for display only
                    // "details.*.total_harga" => "nullable|numeric|min:0", // Will be calculated
                ],
                [
                    "pengiriman_id.required" => "ID pengiriman diperlukan",
                    "pengiriman_id.exists" => "Pengiriman tidak ditemukan",
                    "tanggal_kirim.required" => "Tanggal kirim harus diisi",
                    "tanggal_kirim.date" => "Format tanggal kirim tidak valid",
                    "total_qty_kirim.required" => "Total qty kirim harus diisi",
                    "total_harga_kirim.required" =>
                        "Total harga kirim harus diisi",
                    "bukti_foto_bongkar.*.mimes" => "Format file harus jpeg, png, jpg, atau pdf",
                    "bukti_foto_bongkar.*.max" => "Ukuran file maksimal 10MB",
                    "details.required" => "Detail barang harus diisi",
                    "details.min" => "Minimal satu detail barang harus diisi",
                    "details.*.bahan_baku_supplier_id.required" =>
                        "Bahan baku harus dipilih",
                    "details.*.qty_kirim.required" => "Qty kirim harus diisi",
                ],
            );

            // Begin transaction
            DB::beginTransaction();

            // Update pengiriman - with eager loading
            $pengiriman = Pengiriman::with(['order', 'pengirimanDetails'])
                ->findOrFail($validatedData["pengiriman_id"]);

            // Generate nomor pengiriman jika belum ada
            if (empty($pengiriman->no_pengiriman)) {
                $noPengiriman = Pengiriman::generateNoPengiriman();
            } else {
                $noPengiriman = $pengiriman->no_pengiriman;
            }

            // Handle multiple bukti foto bongkar uploads
            $existingPhotos = $pengiriman->bukti_foto_bongkar_array ?? [];
            $buktiFileNames = $existingPhotos; // Start with existing photos
            $buktiFotoUploadedAt = $pengiriman->bukti_foto_bongkar_uploaded_at;

            if ($request->hasFile("bukti_foto_bongkar")) {
                $uploadedFiles = $request->file("bukti_foto_bongkar");
                
                // Handle both single file and array of files
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                foreach ($uploadedFiles as $file) {
                    if ($file && $file->isValid()) {
                        // Generate unique filename
                        $buktiFileName =
                            "bukti_" .
                            $pengiriman->id .
                            "_" .
                            time() .
                            "_" .
                            uniqid() .
                            "." .
                            $file->getClientOriginalExtension();
                        
                        // Store file
                        $file->storeAs(
                            "pengiriman/bukti",
                            $buktiFileName,
                            "public",
                        );
                        
                        // Add to array
                        $buktiFileNames[] = $buktiFileName;
                        
                        Log::info("Uploaded bukti foto: {$buktiFileName}");
                    }
                }
                
                // Update timestamp only if new files were uploaded
                if (count($buktiFileNames) > count($existingPhotos)) {
                    $buktiFotoUploadedAt = now();
                }
            }
            
            // Convert array to JSON for storage (handled by model mutator)
            $buktiFileName = !empty($buktiFileNames) ? $buktiFileNames : null;

            // Note: Foto tanda terima now uploaded separately via menunggu-verifikasi view
            // Keep existing foto_tanda_terima data if it exists
            $tandaTerimaFileName = $pengiriman->foto_tanda_terima;
            $tandaTerimaUploadedAt = $pengiriman->foto_tanda_terima_uploaded_at;

            // Update pengiriman data
            $pengiriman->update([
                "no_pengiriman" => $noPengiriman,
                "tanggal_kirim" => $validatedData["tanggal_kirim"],
                "hari_kirim" => $validatedData["hari_kirim"],
                "total_qty_kirim" => $validatedData["total_qty_kirim"],
                "total_harga_kirim" => $validatedData["total_harga_kirim"],
                "bukti_foto_bongkar" => $buktiFileName,
                "bukti_foto_bongkar_uploaded_at" => $buktiFotoUploadedAt,
                "foto_tanda_terima" => $tandaTerimaFileName,
                "foto_tanda_terima_uploaded_at" => $tandaTerimaUploadedAt,
                "catatan" => $validatedData["catatan"] ?? null,
                "catatan_refraksi" => $validatedData["catatan_refraksi"] ?? null,
                "status" => "menunggu_fisik",
            ]);

            // Update existing details (don't delete and recreate)
            foreach ($validatedData["details"] as $index => $detail) {
                // Find existing detail by index (assuming index matches existing detail order)
                $existingDetail = $pengiriman->pengirimanDetails->get($index);

                if ($existingDetail) {
                    // Get frozen price from bahan_baku_supplier_klien (client-specific price)
                    $klienId = $pengiriman->order->klien_id ?? null;
                    $bahanBakuSupplier = \App\Models\BahanBakuSupplier::find($detail["bahan_baku_supplier_id"]);
                    
                    $hargaSatuan = 0;
                    
                    if ($bahanBakuSupplier && $klienId) {
                        // Try to get client-specific price from bahan_baku_supplier_klien
                        $bahanBakuSupplierKlien = \App\Models\BahanBakuSupplierKlien::where('bahan_baku_supplier_id', $bahanBakuSupplier->id)
                            ->where('klien_id', $klienId)
                            ->first();
                        
                        if ($bahanBakuSupplierKlien) {
                            $hargaSatuan = $bahanBakuSupplierKlien->harga_per_satuan;
                        }
                    }
                    
                    // Fallback to default supplier price if client-specific price not found
                    if ($hargaSatuan == 0 && $bahanBakuSupplier) {
                        $hargaSatuan = $bahanBakuSupplier->harga_per_satuan ?? 0;
                    }
                    
                    // Update detail dengan harga yang didapat
                    $totalHarga = $detail["qty_kirim"] * $hargaSatuan;
                    $existingDetail->update([
                        "qty_kirim" => $detail["qty_kirim"],
                        "harga_satuan" => $hargaSatuan,
                        "total_harga" => $totalHarga,
                    ]);
                } else {
                    // If detail doesn't exist (shouldn't happen in submit flow), create new
                    // This is fallback - normally all details should exist from forecast creation
                    $bahanBakuSupplier = \App\Models\BahanBakuSupplier::find(
                        $detail["bahan_baku_supplier_id"],
                    );

                    $poDetail = null;
                    if ($bahanBakuSupplier) {
                        $poDetail = \App\Models\OrderDetail::where(
                            "order_id",
                            $pengiriman->purchase_order_id,
                        )
                            ->whereHas("bahanBakuKlien", function ($query) use (
                                $bahanBakuSupplier,
                            ) {
                                $query->where("nama", $bahanBakuSupplier->nama ?? '');
                            })
                            ->first();

                        if (!$poDetail) {
                            $poDetail = \App\Models\OrderDetail::where(
                                "order_id",
                                $pengiriman->purchase_order_id,
                            )->first();
                        }
                    }

                    // Get frozen harga from bahan_baku_supplier_klien (client-specific price)
                    $hargaSatuan = 0;
                    $klienId = $pengiriman->order->klien_id ?? null;
                    
                    if ($bahanBakuSupplier && $klienId) {
                        // Try to get client-specific price from bahan_baku_supplier_klien
                        $bahanBakuSupplierKlien = \App\Models\BahanBakuSupplierKlien::where('bahan_baku_supplier_id', $bahanBakuSupplier->id)
                            ->where('klien_id', $klienId)
                            ->first();
                        $hargaSatuan = $bahanBakuSupplierKlien ? $bahanBakuSupplierKlien->harga_per_satuan : 0;
                    }
                    
                    // Fallback to default supplier price if client-specific price not found
                    if ($hargaSatuan == 0 && $bahanBakuSupplier) {
                        $hargaSatuan = $bahanBakuSupplier->harga_per_satuan ?? 0;
                    }

                    PengirimanDetail::create([
                        "pengiriman_id" => $pengiriman->id,
                        "purchase_order_bahan_baku_id" => $poDetail
                            ? $poDetail->id
                            : null,
                        "bahan_baku_supplier_id" =>
                            $detail["bahan_baku_supplier_id"],
                        "qty_kirim" => $detail["qty_kirim"],
                        "harga_satuan" => $hargaSatuan, // Use frozen harga (client-specific or default)
                        "total_harga" => $detail["qty_kirim"] * $hargaSatuan,
                    ]);
                }
            }

      
            $this->reduceOrderDetailQty($pengiriman);

            // Commit transaction
            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Pengiriman berhasil diajukan untuk verifikasi",
                "no_pengiriman" => $pengiriman->no_pengiriman,
                "pengiriman" => $pengiriman,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal",
                    "errors" => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get bahan baku harga for AJAX requests
     */
    public function getBahanBakuHarga($id)
    {
        try {
            $bahanBaku = \App\Models\BahanBakuSupplier::with([
                "riwayatHarga" => function ($query) {
                    $query->latest("tanggal_perubahan")->limit(1);
                },
            ])->findOrFail($id);

            $latestHarga = $bahanBaku->riwayatHarga->first();
            $harga = $latestHarga
                ? $latestHarga->harga_baru
                : $bahanBaku->harga_per_satuan;

            return response()->json([
                "success" => true,
                "harga" => $harga,
                "nama_bahan_baku" => $bahanBaku->nama,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Bahan baku tidak ditemukan",
                ],
                404,
            );
        }
    }

    /**
     * Show batal modal for pengiriman confirmation
     */
    public function getBatalModal(Request $request)
    {
        try {
            $pengiriman = Pengiriman::with([
                "order",
                "order.klien",
                "purchasing",
                "forecast",
            ])->findOrFail($request->get("pengiriman_id"));

            return view(
                "pages.purchasing.pengiriman.pengiriman-masuk.batal",
                compact("pengiriman"),
            );
        } catch (\Exception $e) {
            return response(
                '<div class="text-center py-8 text-red-500">Error: ' .
                    $e->getMessage() .
                    "</div>",
                500,
            );
        }
    }

    /**
     * Cancel pengiriman with catatan only
     */
    public function batalPengiriman(Request $request)
    {
        // Check user role authorization - Only Direktur, Manager Purchasing, and Staff Purchasing can cancel
        $user = Auth::user();
        if (
            !in_array($user->role, [
                "direktur",
                "manager_purchasing",
                "staff_purchasing",
            ])
        ) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Anda tidak memiliki akses untuk membatalkan pengiriman. Hanya Direktur, Manager Purchasing, dan Staff Purchasing yang dapat melakukan aksi ini.",
                ],
                403,
            );
        }

        // For Staff Purchasing, ensure they are the PIC
        if ($user->role === "staff_purchasing") {
            $pengiriman = Pengiriman::find($request->pengiriman_id);
            if ($pengiriman && $pengiriman->purchasing_id !== $user->id) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Anda hanya dapat membatalkan pengiriman yang Anda tangani sebagai PIC.",
                    ],
                    403,
                );
            }
        }

        try {
            // Validate request - only catatan is allowed to be updated
            $validatedData = $request->validate(
                [
                    "pengiriman_id" => "required|exists:pengiriman,id",
                    "catatan" => "required|string|max:1000",
                    "alasan_batal" => "required|string|max:500",
                ],
                [
                    "pengiriman_id.required" => "ID pengiriman diperlukan",
                    "pengiriman_id.exists" => "Pengiriman tidak ditemukan",
                    "catatan.required" => "Catatan pembatalan harus diisi",
                    "catatan.max" =>
                        "Catatan tidak boleh lebih dari 1000 karakter",
                    "alasan_batal.required" => "Alasan pembatalan harus diisi",
                    "alasan_batal.max" =>
                        "Alasan pembatalan tidak boleh lebih dari 500 karakter",
                ],
            );

            // Begin transaction
            DB::beginTransaction();

            // Update only catatan and status to 'batal'
            $pengiriman = Pengiriman::findOrFail(
                $validatedData["pengiriman_id"],
            );

            // Combine existing catatan with cancellation reason
            $newCatatan =
                $validatedData["catatan"] .
                "\n\n[PEMBATALAN]\n" .
                $validatedData["alasan_batal"] .
                "\n[Dibatalkan pada: " .
                now()->format("d M Y H:i") .
                "]";

            $pengiriman->update([
                "catatan" => $newCatatan,
                "status" => "gagal",
            ]);

            // IMPORTANT: Restore qty to order_detail when pengiriman is cancelled
            // This will only restore if qty was previously reduced
            $this->restoreOrderDetailQty($pengiriman);

            // Commit transaction
            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Pengiriman berhasil dibatalkan",
                "no_pengiriman" => $pengiriman->no_pengiriman,
                "pengiriman" => $pengiriman,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal",
                    "errors" => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get detail for pengiriman menunggu fisik
     */
    public function getDetailFisik($id)
    {
        try {
            $pengiriman = Pengiriman::with([
                "order",
                "order.klien",
                "purchasing",
                "forecast",
                "pengirimanDetails.bahanBakuSupplier",
                "pengirimanDetails.bahanBakuSupplier.supplier",
                "pengirimanDetails.orderDetail",
                "approvalPembayaran",
                "invoicePenagihan",
            ])
                ->where("status", "menunggu_fisik")
                ->findOrFail($id);

            // Return HTML view for modal
            return view(
                "pages.purchasing.pengiriman.menunggu-fisik.detail",
                compact("pengiriman"),
            );
        } catch (\Exception $e) {
            Log::error("Error in getDetailFisik: " . $e->getMessage());
            return response(
                '<div class="text-center py-8 text-red-500">Error: ' .
                    $e->getMessage() .
                    "<br><small>" .
                    $e->getFile() .
                    ":" .
                    $e->getLine() .
                    "</small></div>",
                500,
            );
        }
    }

    /**
     * Get detail for pengiriman menunggu verifikasi
     */
    public function getDetailVerifikasi($id)
    {
        try {
            $pengiriman = Pengiriman::with([
                "order",
                "order.klien",
                "purchasing",
                "forecast",
                "pengirimanDetails.bahanBakuSupplier",
                "pengirimanDetails.bahanBakuSupplier.supplier",
                "pengirimanDetails.orderDetail",
                "approvalPembayaran",
                "invoicePenagihan",
            ])
                ->where("status", "menunggu_verifikasi")
                ->findOrFail($id);

            // Return HTML view for modal
            return view(
                "pages.purchasing.pengiriman.menunggu-verifikasi.detail",
                compact("pengiriman"),
            );
        } catch (\Exception $e) {
            Log::error("Error in getDetailVerifikasi: " . $e->getMessage());
            return response(
                '<div class="text-center py-8 text-red-500">Error: ' .
                    $e->getMessage() .
                    "<br><small>" .
                    $e->getFile() .
                    ":" .
                    $e->getLine() .
                    "</small></div>",
                500,
            );
        }
    }

    /**
     * Get detail for pengiriman gagal - returns JSON for AJAX
     */
    public function getDetailGagal($id)
    {
        try {
            $pengiriman = Pengiriman::with([
                "order",
                "order.klien",
                "purchasing",
                "forecast",
                "pengirimanDetails.bahanBakuSupplier",
                "pengirimanDetails.bahanBakuSupplier.supplier",
                "pengirimanDetails.orderDetail",
                "approvalPembayaran",
                "invoicePenagihan",
            ])
                ->where("status", "gagal")
                ->findOrFail($id);

            // Format data for response
            $data = [
                "id" => $pengiriman->id,
                "no_pengiriman" => $pengiriman->no_pengiriman,
                "status" => ucfirst($pengiriman->status),
                "no_po" => $pengiriman->order->po_number ?? "-",
                "pic_purchasing" => $pengiriman->purchasing->nama ?? "-",
                "tanggal_kirim" => $pengiriman->tanggal_kirim
                    ? Carbon::parse($pengiriman->tanggal_kirim)->format("d F Y")
                    : "-",
                "hari_kirim" => $pengiriman->hari_kirim ?? "-",
                "total_qty" =>
                    number_format(
                        $pengiriman->total_qty_kirim ?? 0,
                        0,
                        ",",
                        ".",
                    ) . " kg",
                "total_harga" =>
                    "Rp " .
                    number_format(
                        $pengiriman->total_harga_kirim ?? 0,
                        0,
                        ",",
                        ".",
                    ),
                "total_items" => $pengiriman->pengirimanDetails
                    ? $pengiriman->pengirimanDetails->count()
                    : 0,
                "catatan" => $pengiriman->catatan,
                "alasan_gagal" => $pengiriman->alasan_gagal,
                "catatan_refraksi" => $pengiriman->catatan_refraksi,
                "details" => $pengiriman->pengirimanDetails
                    ? $pengiriman->pengirimanDetails->map(function ($detail) {
                        return [
                            "bahan_baku" =>
                                $detail->bahanBakuSupplier->nama ?? "-",
                            "supplier" =>
                                $detail->bahanBakuSupplier->supplier->nama ??
                                "-",
                            "qty_kirim" => $detail->qty_kirim,
                            "harga_satuan" => $detail->harga_satuan,
                            "total_harga" => $detail->total_harga,
                        ];
                    })
                    : [],
            ];

            return response()->json([
                "success" => true,
                "pengiriman" => $data,
            ]);
        } catch (\Exception $e) {
            Log::error("Error in getDetailGagal: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Gagal memuat detail pengiriman: " . $e->getMessage()
                ],
                500
            );
        }
    }

    /**
     * Get detail for pengiriman berhasil - this returns JSON for AJAX
     */
    public function getDetailBerhasil($id)
    {
        try {
            $pengiriman = Pengiriman::with([
                "order",
                "order.klien",
                "purchasing",
                "forecast",
                "pengirimanDetails.bahanBakuSupplier",
                "pengirimanDetails.bahanBakuSupplier.supplier",
                "pengirimanDetails.orderDetail",
                "approvalPembayaran",
                "invoicePenagihan",
            ])
                ->where("status", "berhasil")
                ->findOrFail($id);

            // Build timeline
            $timeline = [];

            // Forecast timeline
            if ($pengiriman->forecast) {
                $forecast = $pengiriman->forecast;

                // Forecast created
                $timeline[] = [
                    "type" => "forecast",
                    "status" => "created",
                    "title" => "Forecast Dibuat",
                    "description" => "Forecast {$forecast->no_forecast} telah dibuat",
                    "timestamp" => $forecast->created_at,
                    "formatted_time" => $forecast->created_at
                        ? Carbon::parse($forecast->created_at)->format(
                            "d M Y, H:i",
                        )
                        : "-",
                    "icon" => "fa-plus-circle",
                    "color" => "blue",
                ];

                // Forecast updated if different from created
                if (
                    $forecast->updated_at &&
                    $forecast->updated_at != $forecast->created_at
                ) {
                    $timeline[] = [
                        "type" => "forecast",
                        "status" => "updated",
                        "title" => "Forecast Diperbarui",
                        "description" => "Forecast {$forecast->no_forecast} telah diperbarui",
                        "timestamp" => $forecast->updated_at,
                        "formatted_time" => $forecast->updated_at
                            ? Carbon::parse($forecast->updated_at)->format(
                                "d M Y, H:i",
                            )
                            : "-",
                        "icon" => "fa-edit",
                        "color" => "yellow",
                    ];
                }

                // Forecast success (when pengiriman created)
                if ($forecast->status === "sukses") {
                    $timeline[] = [
                        "type" => "forecast",
                        "status" => "sukses",
                        "title" => "Forecast Berhasil",
                        "description" => "Forecast {$forecast->no_forecast} berhasil diproses",
                        "timestamp" => $pengiriman->created_at,
                        "formatted_time" => $pengiriman->created_at
                            ? Carbon::parse($pengiriman->created_at)->format(
                                "d M Y, H:i",
                            )
                            : "-",
                        "icon" => "fa-check-circle",
                        "color" => "green",
                    ];
                }
            }

            // Pengiriman timeline
            // Pengiriman created (pending - menunggu fisik)
            $timeline[] = [
                "type" => "pengiriman",
                "status" => "pending",
                "title" => "Pengiriman Dibuat",
                "description" => "Pengiriman {$pengiriman->no_pengiriman} telah dibuat dan menunggu verifikasi fisik",
                "timestamp" => $pengiriman->created_at,
                "formatted_time" => $pengiriman->created_at
                    ? Carbon::parse($pengiriman->created_at)->format(
                        "d M Y, H:i",
                    )
                    : "-",
                "icon" => "fa-box",
                "color" => "gray",
            ];

            // Fisik Diterima (estimate from file upload time or between created_at and updated_at)
            $fisikVerifiedAt = null;
            if ($pengiriman->foto_tanda_terima_uploaded_at) {
                $fisikVerifiedAt = Carbon::parse($pengiriman->foto_tanda_terima_uploaded_at);
            }
            if ($pengiriman->bukti_foto_bongkar_uploaded_at) {
                $buktiFotoAt = Carbon::parse($pengiriman->bukti_foto_bongkar_uploaded_at);
                if (!$fisikVerifiedAt || $buktiFotoAt->lt($fisikVerifiedAt)) {
                    $fisikVerifiedAt = $buktiFotoAt;
                }
            }
            
            // If no file upload time, estimate as midpoint between created and updated
            if (!$fisikVerifiedAt && $pengiriman->created_at && $pengiriman->updated_at && $pengiriman->created_at != $pengiriman->updated_at) {
                $createdTimestamp = Carbon::parse($pengiriman->created_at)->timestamp;
                $updatedTimestamp = Carbon::parse($pengiriman->updated_at)->timestamp;
                $midTimestamp = ($createdTimestamp + $updatedTimestamp) / 2;
                $fisikVerifiedAt = Carbon::createFromTimestamp($midTimestamp);
            }

            if ($fisikVerifiedAt) {
                $timeline[] = [
                    "type" => "pengiriman",
                    "status" => "fisik_diterima",
                    "title" => "Fisik Diterima",
                    "description" => "Barang telah diterima secara fisik dan dokumen telah diverifikasi oleh Direktur/Manager Purchasing",
                    "timestamp" => $fisikVerifiedAt,
                    "formatted_time" => $fisikVerifiedAt->format("d M Y, H:i"),
                    "icon" => "fa-box-check",
                    "color" => "purple",
                ];
            }

            // Pengiriman updated (menunggu verifikasi dokumen oleh accounting)
            if (
                $pengiriman->updated_at &&
                $pengiriman->updated_at != $pengiriman->created_at
            ) {
                $timeline[] = [
                    "type" => "pengiriman",
                    "status" => "menunggu_verifikasi",
                    "title" => "Menunggu Verifikasi Dokumen",
                    "description" => "Pengiriman {$pengiriman->no_pengiriman} menunggu verifikasi dokumen oleh Accounting",
                    "timestamp" => $pengiriman->updated_at,
                    "formatted_time" => $pengiriman->updated_at
                        ? Carbon::parse($pengiriman->updated_at)->format(
                            "d M Y, H:i",
                        )
                        : "-",
                    "icon" => "fa-file-invoice",
                    "color" => "yellow",
                ];
            }

            // Pengiriman success
            $timeline[] = [
                "type" => "pengiriman",
                "status" => "berhasil",
                "title" => "Pengiriman Berhasil",
                "description" => "Pengiriman {$pengiriman->no_pengiriman} telah berhasil diverifikasi",
                "timestamp" => $pengiriman->updated_at,
                "formatted_time" => $pengiriman->updated_at
                    ? Carbon::parse($pengiriman->updated_at)->format(
                        "d M Y, H:i",
                    )
                    : "-",
                "icon" => "fa-check-double",
                "color" => "green",
            ];

            // Sort timeline by timestamp
            usort($timeline, function ($a, $b) {
                return $a["timestamp"] <=> $b["timestamp"];
            });

            // Format data for response
            $data = [
                "id" => $pengiriman->id,
                "no_pengiriman" => $pengiriman->no_pengiriman,
                "status" => ucfirst($pengiriman->status),
                "no_po" => $pengiriman->order->po_number ?? "-",
                "pic_purchasing" => $pengiriman->purchasing->nama ?? "-",
                "tanggal_kirim" => $pengiriman->tanggal_kirim
                    ? Carbon::parse($pengiriman->tanggal_kirim)->format("d F Y")
                    : "-",
                "hari_kirim" => $pengiriman->hari_kirim ?? "-",
                "total_qty" =>
                    number_format(
                        $pengiriman->total_qty_kirim ?? 0,
                        0,
                        ",",
                        ".",
                    ) . " kg",
                "total_harga" =>
                    "Rp " .
                    number_format(
                        $pengiriman->total_harga_kirim ?? 0,
                        0,
                        ",",
                        ".",
                    ),
                "total_items" => $pengiriman->pengirimanDetails
                    ? $pengiriman->pengirimanDetails->count()
                    : 0,
                "catatan" => $pengiriman->catatan,
                "rating" => $pengiriman->rating,
                "ulasan" => $pengiriman->ulasan,
                "bukti_foto_bongkar" =>
                    $pengiriman->bukti_foto_bongkar_array ?? [],
                "bukti_foto_urls" => $pengiriman->bukti_foto_bongkar_url ?? [],
                "bukti_foto_bongkar_uploaded_at" => $pengiriman->bukti_foto_bongkar_uploaded_at
                    ? Carbon::parse(
                            $pengiriman->bukti_foto_bongkar_uploaded_at,
                        )->format("d M Y, H:i") . " WIB"
                    : null,
                "foto_tanda_terima" => $pengiriman->foto_tanda_terima,
                "foto_tanda_terima_url" => $pengiriman->foto_tanda_terima
                    ? asset(
                        "storage/pengiriman/tanda-terima/" .
                            $pengiriman->foto_tanda_terima,
                    )
                    : null,
                "foto_tanda_terima_uploaded_at" => $pengiriman->foto_tanda_terima_uploaded_at
                    ? Carbon::parse(
                            $pengiriman->foto_tanda_terima_uploaded_at,
                        )->format("d M Y, H:i") . " WIB"
                    : null,
                "timeline" => $timeline,
                "details" => $pengiriman->pengirimanDetails
                    ? $pengiriman->pengirimanDetails->map(function ($detail) {
                        return [
                            "bahan_baku" =>
                                $detail->bahanBakuSupplier->nama ?? "-",
                            "supplier" =>
                                $detail->bahanBakuSupplier->supplier->nama ??
                                "-",
                            "qty_kirim" => $detail->qty_kirim,
                            "harga_satuan" => $detail->harga_satuan,
                            "total_harga" => $detail->total_harga,
                        ];
                    })
                    : [],
            ];

            // Add refraksi and price info if approval pembayaran exists
            if ($pengiriman->approvalPembayaran) {
                $approval = $pengiriman->approvalPembayaran;
                $data["approval_pembayaran"] = [
                    "refraksi_type" => $approval->refraksi_type,
                    "refraksi_value" => $approval->refraksi_value,
                    "refraksi_amount" => $approval->refraksi_amount,
                    "qty_before_refraksi" => $approval->qty_before_refraksi,
                    "qty_after_refraksi" => $approval->qty_after_refraksi,
                    "amount_before_refraksi" =>
                        $approval->amount_before_refraksi,
                    "amount_after_refraksi" =>
                        $approval->amount_after_refraksi,
                ];

                // Add bukti pembayaran URL if exists
                if ($approval->bukti_pembayaran) {
                    $data["bukti_pembayaran_url"] = asset(
                        "storage/" . $approval->bukti_pembayaran,
                    );
                }

                // Calculate harga beli
                $totalHargaBeli =
                    $approval->amount_after_refraksi ??
                    $approval->amount_before_refraksi ??
                    $pengiriman->total_harga_kirim ??
                    0;
                $qtyAfterRefraksi =
                    $approval->qty_after_refraksi ??
                    $approval->qty_before_refraksi ??
                    $pengiriman->total_qty_kirim ??
                    1;
                $hargaBeliPerKg =
                    $qtyAfterRefraksi > 0
                        ? $totalHargaBeli / $qtyAfterRefraksi
                        : 0;

                $data["total_harga_beli"] = $totalHargaBeli;
                $data["qty_after_refraksi"] = $qtyAfterRefraksi;
                $data["harga_beli_per_kg"] = $hargaBeliPerKg;
            }

            // Calculate harga jual
            $hargaJualPerKg = 0;
            $totalHargaJual = 0;
            $qtyJual = 0;
            $source = "";

            if ($pengiriman->invoicePenagihan) {
                $invoice = $pengiriman->invoicePenagihan;
                $totalHargaJual =
                    $invoice->amount_after_refraksi ??
                    $invoice->subtotal ??
                    0;
                $qtyJual =
                    $invoice->qty_after_refraksi ??
                    $invoice->qty_before_refraksi ??
                    $pengiriman->total_qty_kirim ??
                    1;
                $hargaJualPerKg =
                    $qtyJual > 0 ? $totalHargaJual / $qtyJual : 0;
                $source = "Invoice Penagihan";
            } elseif (
                $pengiriman->pengirimanDetails &&
                $pengiriman->pengirimanDetails->count() > 0
            ) {
                foreach ($pengiriman->pengirimanDetails as $detail) {
                    if (
                        $detail->orderDetail &&
                        $detail->orderDetail->harga_jual > 0
                    ) {
                        $hargaJualPerKg += $detail->orderDetail->harga_jual;
                        $totalHargaJual +=
                            $detail->qty_kirim *
                            $detail->orderDetail->harga_jual;
                        $qtyJual += $detail->qty_kirim;
                    }
                }
                if (
                    $pengiriman->pengirimanDetails->count() > 1 &&
                    $qtyJual > 0
                ) {
                    $hargaJualPerKg = $totalHargaJual / $qtyJual;
                }
                $source = "Purchase Order";
            }

            if ($hargaJualPerKg > 0) {
                $data["harga_jual_per_kg"] = $hargaJualPerKg;
                $data["total_harga_jual"] = $totalHargaJual;
                $data["qty_jual"] = $qtyJual;
                $data["harga_jual_source"] = $source;

                // Calculate margin if harga beli is available
                if (
                    isset($data["total_harga_beli"]) &&
                    $data["total_harga_beli"] > 0
                ) {
                    $margin =
                        $totalHargaJual - $data["total_harga_beli"];
                    $marginPercentage =
                        ($margin / $data["total_harga_beli"]) * 100;
                    $data["margin"] = $margin;
                    $data["margin_percentage"] = $marginPercentage;
                }
            }

            // Add catatan refraksi
            $data["catatan_refraksi"] = $pengiriman->catatan_refraksi;

            return response()->json([
                "success" => true,
                "pengiriman" => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Gagal memuat detail pengiriman: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Update catatan for pengiriman
     */
    public function updateCatatan(Request $request, $id)
    {
        try {
            $request->validate([
                "catatan" => "nullable|string|max:1000",
            ]);

            $pengiriman = Pengiriman::findOrFail($id);
            $pengiriman->catatan = $request->catatan;
            $pengiriman->save();

            return response()->json([
                "success" => true,
                "message" => "Catatan berhasil diperbarui",
                "catatan" => $pengiriman->catatan,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal",
                    "errors" => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Gagal memperbarui catatan: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function verifikasiFisik(Request $request, $id)
    {
        try {
            $pengiriman = Pengiriman::findOrFail($id);
            
            // Validate current status
            if ($pengiriman->status !== 'menunggu_fisik') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengiriman tidak dalam status menunggu fisik'
                ], 400);
            }
            
            // Check user authorization
            $user = Auth::user();
            if (!in_array($user->role, ['direktur', 'manager_purchasing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk memverifikasi fisik pengiriman'
                ], 403);
            }
            
            // Start database transaction
            DB::beginTransaction();
            
            try {
                // Update status to menunggu_verifikasi (next stage in workflow)
                $pengiriman->status = 'menunggu_verifikasi';
                $pengiriman->save();
                
                // IMPORTANT: Reduce qty from order_detail when verified physically
                // This ensures qty is reduced only once, regardless of future status changes
                $this->reduceOrderDetailQty($pengiriman);
                
                // Commit transaction
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pengiriman berhasil diverifikasi fisik dan menunggu verifikasi dokumen',
                    'pengiriman' => $pengiriman
                ]);
                
            } catch (\Exception $e) {
                // Rollback on error
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in verifikasiFisik: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi fisik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifikasi pengiriman (menunggu_verifikasi -> berhasil)
     * This is document verification by Direktur/Manager Purchasing
     */
    public function verifikasiPengiriman(Request $request, $id)
    {
        try {
            $pengiriman = Pengiriman::with('details.orderDetail')->findOrFail($id);
            
            // Validate current status
            if ($pengiriman->status !== 'menunggu_verifikasi') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengiriman tidak dalam status menunggu verifikasi'
                ], 400);
            }
            
            // Check user authorization
            $user = Auth::user();
            if (!in_array($user->role, ['direktur', 'manager_purchasing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk memverifikasi dokumen pengiriman'
                ], 403);
            }
            
            // Start database transaction
            DB::beginTransaction();
            
            try {
                // Update pengiriman status to berhasil (successful delivery)
                $pengiriman->status = 'berhasil';
                $pengiriman->save();
                
                // IMPORTANT: Try to reduce qty if not already reduced
                // This handles edge cases where pengiriman was already in menunggu_verifikasi before the update
                // For new workflow, qty should already be reduced when status changed to menunggu_fisik
                $this->reduceOrderDetailQty($pengiriman);
                
                // Commit the transaction
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pengiriman berhasil diverifikasi',
                    'pengiriman' => $pengiriman
                ]);
                
            } catch (\Exception $e) {
                // Rollback on any error
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in verifikasiPengiriman: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verifikasi modal
     */
    public function getVerifikasiModal($id)
    {
        try {
            $pengiriman = Pengiriman::with([
                'order',
                'order.klien',
                'purchasing',
                'forecast',
                'pengirimanDetails.bahanBakuSupplier',
                'pengirimanDetails.bahanBakuSupplier.supplier',
                'pengirimanDetails.orderDetail',
                'approvalPembayaran',
                'invoicePenagihan',
            ])
            ->where('status', 'menunggu_verifikasi')
            ->findOrFail($id);

            // Return HTML view for modal
            return view(
                'pages.purchasing.pengiriman.menunggu-verifikasi.verifikasi',
                compact('pengiriman')
            );
        } catch (\Exception $e) {
            Log::error('Error in getVerifikasiModal: ' . $e->getMessage());
            return response(
                '<div class="text-center py-8 text-red-500">Error: ' .
                    $e->getMessage() .
                    '<br><small>' .
                    $e->getFile() .
                    ':' .
                    $e->getLine() .
                    '</small></div>',
                500
            );
        }
    }

    /**
     * Get revisi modal
     */
    public function getRevisiModal($id)
    {
        try {
            $pengiriman = Pengiriman::with([
                'order',
                'order.klien',
                'purchasing',
                'forecast',
                'pengirimanDetails.bahanBakuSupplier',
                'pengirimanDetails.bahanBakuSupplier.supplier',
                'pengirimanDetails.orderDetail',
                'approvalPembayaran',
                'invoicePenagihan',
            ])
            ->where('status', 'menunggu_verifikasi')
            ->findOrFail($id);

            // Return HTML view for modal
            return view(
                'pages.purchasing.pengiriman.menunggu-verifikasi.revisi',
                compact('pengiriman')
            );
        } catch (\Exception $e) {
            Log::error('Error in getRevisiModal: ' . $e->getMessage());
            return response(
                '<div class="text-center py-8 text-red-500">Error: ' .
                    $e->getMessage() .
                    '<br><small>' .
                    $e->getFile() .
                    ':' .
                    $e->getLine() .
                    '</small></div>',
                500
            );
        }
    }

    /**
     * Upload foto tanda terima for pengiriman
     */
    public function uploadFotoTandaTerima(Request $request, $id)
    {
        try {
            $request->validate([
                'foto_tanda_terima' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240',
            ], [
                'foto_tanda_terima.required' => 'File foto tanda terima harus diupload',
                'foto_tanda_terima.file' => 'File tidak valid',
                'foto_tanda_terima.mimes' => 'File harus berupa gambar (JPEG, PNG, JPG) atau PDF',
                'foto_tanda_terima.max' => 'Ukuran file maksimal 10MB',
            ]);

            $pengiriman = Pengiriman::findOrFail($id);
            
            // Check user authorization
            $user = Auth::user();
            $canUpload = in_array($user->role, ['direktur', 'manager_purchasing', 'staff_purchasing']);
            
            if (!$canUpload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk upload foto tanda terima'
                ], 403);
            }
            
            // For staff purchasing, check if they are the PIC
            if ($user->role === 'staff_purchasing' && $pengiriman->purchasing_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat upload foto untuk pengiriman yang Anda tangani'
                ], 403);
            }
            
            // Delete old file if exists
            if ($pengiriman->foto_tanda_terima && Storage::disk('public')->exists('pengiriman/tanda-terima/' . $pengiriman->foto_tanda_terima)) {
                Storage::disk('public')->delete('pengiriman/tanda-terima/' . $pengiriman->foto_tanda_terima);
            }
            
            // Upload new file
            $file = $request->file('foto_tanda_terima');
            $fileName = 'tanda_terima_' . $pengiriman->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('pengiriman/tanda-terima', $fileName, 'public');
            
            // Update pengiriman
            $pengiriman->foto_tanda_terima = $fileName;
            $pengiriman->foto_tanda_terima_uploaded_at = now();
            $pengiriman->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Foto tanda terima berhasil diupload',
                'file_name' => $fileName,
                'uploaded_at' => $pengiriman->foto_tanda_terima_uploaded_at->format('d M Y, H:i') . ' WIB'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in uploadFotoTandaTerima: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload foto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revisi pengiriman (kirim kembali ke pending untuk diperbaiki)
     * Can be used from menunggu_verifikasi OR berhasil status
     */
    public function revisiPengiriman(Request $request, $id)
    {
        try {
            $request->validate([
                'catatan' => 'required|string|min:10|max:1000',
            ], [
                'catatan.required' => 'Catatan revisi harus diisi',
                'catatan.min' => 'Catatan revisi minimal 10 karakter',
                'catatan.max' => 'Catatan revisi maksimal 1000 karakter',
            ]);

            $pengiriman = Pengiriman::findOrFail($id);
            
            // Check user authorization - Only Direktur and Manager Purchasing can revise
            $user = Auth::user();
            if (!in_array($user->role, ['direktur', 'manager_purchasing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk merevisi pengiriman. Hanya Direktur dan Manager Purchasing yang dapat melakukan revisi.'
                ], 403);
            }
            
            // Check if status is menunggu_verifikasi OR berhasil
            if (!in_array($pengiriman->status, ['menunggu_verifikasi', 'berhasil'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengiriman hanya dapat direvisi dari status menunggu verifikasi atau berhasil'
                ], 400);
            }
            
            // Build revisi catatan with timestamp
            $timestamp = now()->format('d M Y, H:i');
            $revisedBy = $user->nama;
            $oldStatus = $pengiriman->status;
            $revisiCatatan = "[REVISI dari status {$oldStatus} oleh {$revisedBy} pada {$timestamp}]\n" . $request->catatan;
            
            // Append to existing catatan if exists
            if ($pengiriman->catatan) {
                $pengiriman->catatan = $pengiriman->catatan . "\n\n" . $revisiCatatan;
            } else {
                $pengiriman->catatan = $revisiCatatan;
            }
            
            // Begin transaction
            DB::beginTransaction();
            
            try {
                // IMPORTANT: Restore qty to order_detail when pengiriman is revised back to pending
                // This will only restore if qty was previously reduced
                $this->restoreOrderDetailQty($pengiriman);
                
                // Update status back to pending
                $pengiriman->status = 'pending';
                $pengiriman->save();
                
                DB::commit();
                
                Log::info("Pengiriman ID: {$pengiriman->id} successfully revised from {$oldStatus} to pending by user ID: {$user->id}");
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pengiriman berhasil direvisi dan dikembalikan ke status pending',
                    'pengiriman' => $pengiriman
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in revisiPengiriman: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal merevisi pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete failed pengiriman and its associated forecast
     * Only for pengiriman with status 'gagal'
     */
    public function deletePengirimanGagal($id)
    {
        try {
            // Check user authorization - Only Direktur, Manager Purchasing, and Staff Purchasing can delete
            $user = Auth::user();
            if (!in_array($user->role, ['direktur', 'manager_purchasing', 'staff_purchasing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus pengiriman. Hanya Direktur, Manager Purchasing, dan Staff Purchasing yang dapat melakukan aksi ini.'
                ], 403);
            }

            // Find the pengiriman
            $pengiriman = Pengiriman::with(['forecast', 'pengirimanDetails'])->findOrFail($id);

            // Check if status is gagal
            if ($pengiriman->status !== 'gagal') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pengiriman dengan status gagal yang dapat dihapus'
                ], 400);
            }

            // For Staff Purchasing, ensure they are the PIC
            if ($user->role === 'staff_purchasing') {
                if ($pengiriman->purchasing_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda hanya dapat menghapus pengiriman yang Anda tangani sebagai PIC.'
                    ], 403);
                }
            }

            // Begin transaction
            DB::beginTransaction();

            try {
                $noPengiriman = $pengiriman->no_pengiriman;
                $forecastId = $pengiriman->forecast_id;

                // Delete pengiriman details first (cascade will not work with soft deletes)
                PengirimanDetail::where('pengiriman_id', $pengiriman->id)->delete();

                // Delete the pengiriman (soft delete)
                $pengiriman->delete();

                // If there's an associated forecast, delete it too
                if ($forecastId) {
                    $forecast = Forecast::find($forecastId);
                    if ($forecast) {
                        // Delete forecast details first
                        ForecastDetail::where('forecast_id', $forecast->id)->delete();
                        
                        // Delete the forecast (soft delete)
                        $forecast->delete();
                        
                        Log::info("Forecast #{$forecast->no_forecast} deleted along with pengiriman #{$noPengiriman}");
                    }
                }

                DB::commit();

                Log::info("Pengiriman gagal #{$noPengiriman} and its forecast successfully deleted by user #{$user->id}");

                return response()->json([
                    'success' => true,
                    'message' => "Pengiriman {$noPengiriman} dan forecasting terkait berhasil dihapus"
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in deletePengirimanGagal: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete individual bukti foto bongkar
     */
    public function deleteBuktiFoto(Request $request, $id)
    {
        try {
            $request->validate([
                'filename' => 'required|string'
            ]);

            $pengiriman = Pengiriman::findOrFail($id);
            $filename = $request->input('filename');

            // Get current photos array
            $photos = $pengiriman->bukti_foto_bongkar_array ?? [];

            // Check if photo exists in array
            if (!in_array($filename, $photos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto tidak ditemukan dalam daftar'
                ], 404);
            }

            // Delete file from storage
            $filePath = "pengiriman/bukti/" . $filename;
            if (Storage::disk("public")->exists($filePath)) {
                Storage::disk("public")->delete($filePath);
                Log::info("Deleted bukti foto file: {$filePath}");
            }

            // Remove from array
            $photos = array_values(array_filter($photos, function($photo) use ($filename) {
                return $photo !== $filename;
            }));

            // Update pengiriman with new array
            $pengiriman->bukti_foto_bongkar = !empty($photos) ? $photos : null;
            
            // Keep the uploaded_at timestamp if there are still photos
            if (empty($photos)) {
                $pengiriman->bukti_foto_bongkar_uploaded_at = null;
            }
            
            $pengiriman->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil dihapus',
                'remaining_photos' => count($photos)
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting bukti foto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus foto: ' . $e->getMessage()
            ], 500);
        }
    }
}
