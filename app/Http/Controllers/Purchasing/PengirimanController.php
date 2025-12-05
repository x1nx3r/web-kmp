<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\PengirimanDetail;
use App\Models\Order;
use App\Models\Klien;
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
    public function index(Request $request): View
    {
        // Base query dengan eager loading
        $baseQuery = function ($status) use ($request) {
            $query = Pengiriman::with([
                "order:id,po_number,klien_id",
                "order.klien:id,nama,cabang",
                "purchasing:id,nama",
                "pengirimanDetails",
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

        return view(
            "pages.purchasing.pengiriman",
            compact(
                "pengirimanMasuk",
                "menungguVerifikasi",
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
                    "bukti_foto_bongkar" =>
                        "nullable|image|mimes:jpeg,png,jpg|max:2048",
                    "foto_tanda_terima" =>
                        "nullable|image|mimes:jpeg,png,jpg|max:2048",
                    "catatan" => "nullable|string",
                    "details" => "required|array|min:1",
                    "details.*.bahan_baku_supplier_id" =>
                        "required|exists:bahan_baku_supplier,id",
                    "details.*.qty_kirim" => "required|numeric|min:0",
                    "details.*.harga_satuan" => "required|numeric|min:0",
                    "details.*.total_harga" => "required|numeric|min:0",
                ],
                [
                    "pengiriman_id.required" => "ID pengiriman diperlukan",
                    "pengiriman_id.exists" => "Pengiriman tidak ditemukan",
                    "tanggal_kirim.required" => "Tanggal kirim harus diisi",
                    "tanggal_kirim.date" => "Format tanggal kirim tidak valid",
                    "total_qty_kirim.required" => "Total qty kirim harus diisi",
                    "total_harga_kirim.required" =>
                        "Total harga kirim harus diisi",
                    "details.required" => "Detail barang harus diisi",
                    "details.min" => "Minimal satu detail barang harus diisi",
                    "details.*.bahan_baku_supplier_id.required" =>
                        "Bahan baku harus dipilih",
                    "details.*.qty_kirim.required" => "Qty kirim harus diisi",
                    "details.*.harga_satuan.required" =>
                        "Harga satuan harus diisi",
                ],
            );

            // Begin transaction
            DB::beginTransaction();

            // Update pengiriman
            $pengiriman = Pengiriman::findOrFail(
                $validatedData["pengiriman_id"],
            );

            // Generate nomor pengiriman jika belum ada
            if (empty($pengiriman->no_pengiriman)) {
                $noPengiriman = Pengiriman::generateNoPengiriman();
            } else {
                $noPengiriman = $pengiriman->no_pengiriman;
            }

            // Handle bukti foto bongkar upload with old file deletion
            $buktiFileName = $pengiriman->bukti_foto_bongkar;
            $buktiFotoUploadedAt = $pengiriman->bukti_foto_bongkar_uploaded_at;

            if ($request->hasFile("bukti_foto_bongkar")) {
                // Delete old photo if exists
                if (
                    $pengiriman->bukti_foto_bongkar &&
                    Storage::disk("public")->exists(
                        "pengiriman/bukti/" . $pengiriman->bukti_foto_bongkar,
                    )
                ) {
                    Storage::disk("public")->delete(
                        "pengiriman/bukti/" . $pengiriman->bukti_foto_bongkar,
                    );
                }

                // Upload new file
                $file = $request->file("bukti_foto_bongkar");
                if ($file && $file->isValid()) {
                    $buktiFileName =
                        "bukti_" .
                        $pengiriman->id .
                        "_" .
                        time() .
                        "." .
                        $file->getClientOriginalExtension();
                    $file->storeAs(
                        "pengiriman/bukti",
                        $buktiFileName,
                        "public",
                    );
                    $buktiFotoUploadedAt = now(); // Set timestamp saat upload
                }
            }

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
                "catatan" => $validatedData["catatan"],
                "status" => "menunggu_verifikasi",
            ]);

            // Update existing details (don't delete and recreate)
            foreach ($validatedData["details"] as $index => $detail) {
                // Find existing detail by index (assuming index matches existing detail order)
                $existingDetail = $pengiriman->pengirimanDetails->get($index);

                if ($existingDetail) {
                    // Update existing detail
                    $existingDetail->update([
                        "qty_kirim" => $detail["qty_kirim"],
                        "harga_satuan" => $detail["harga_satuan"],
                        "total_harga" => $detail["total_harga"],
                    ]);
                } else {
                    // If detail doesn't exist (shouldn't happen in this case), create new
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
                                $query->where("nama", $bahanBakuSupplier->nama);
                            })
                            ->first();

                        if (!$poDetail) {
                            $poDetail = \App\Models\OrderDetail::where(
                                "order_id",
                                $pengiriman->purchase_order_id,
                            )->first();
                        }
                    }

                    PengirimanDetail::create([
                        "pengiriman_id" => $pengiriman->id,
                        "purchase_order_bahan_baku_id" => $poDetail
                            ? $poDetail->id
                            : null,
                        "bahan_baku_supplier_id" =>
                            $detail["bahan_baku_supplier_id"],
                        "qty_kirim" => $detail["qty_kirim"],
                        "harga_satuan" => $detail["harga_satuan"],
                        "total_harga" => $detail["total_harga"],
                    ]);
                }
            }

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
     * Get detail for pengiriman berhasil
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
            // Pengiriman created (pending)
            $timeline[] = [
                "type" => "pengiriman",
                "status" => "pending",
                "title" => "Pengiriman Dibuat",
                "description" => "Pengiriman {$pengiriman->no_pengiriman} telah dibuat",
                "timestamp" => $pengiriman->created_at,
                "formatted_time" => $pengiriman->created_at
                    ? Carbon::parse($pengiriman->created_at)->format(
                        "d M Y, H:i",
                    )
                    : "-",
                "icon" => "fa-box",
                "color" => "gray",
            ];

            // Pengiriman updated (menunggu verifikasi)
            if (
                $pengiriman->updated_at &&
                $pengiriman->updated_at != $pengiriman->created_at
            ) {
                $timeline[] = [
                    "type" => "pengiriman",
                    "status" => "menunggu_verifikasi",
                    "title" => "Menunggu Verifikasi",
                    "description" => "Pengiriman {$pengiriman->no_pengiriman} menunggu verifikasi",
                    "timestamp" => $pengiriman->updated_at,
                    "formatted_time" => $pengiriman->updated_at
                        ? Carbon::parse($pengiriman->updated_at)->format(
                            "d M Y, H:i",
                        )
                        : "-",
                    "icon" => "fa-clock",
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
     * Get detail for pengiriman gagal
     */
    public function getDetailGagal($id)
    {
        try {
            $pengiriman = Pengiriman::with([
                "order",
                "order.klien",
                "purchasing",
                "pengirimanDetails.bahanBakuSupplier",
                "pengirimanDetails.bahanBakuSupplier.supplier",
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
     * Get detail for pengiriman verifikasi
     */
    public function getDetailVerifikasi($id)
    {
        try {
            // Load pengiriman dengan relasi basic terlebih dahulu
            $pengiriman = Pengiriman::where(
                "status",
                "menunggu_verifikasi",
            )->findOrFail($id);

            // Load relasi satu per satu untuk menghindari timeout
            $pengiriman->load("purchasing");
            $pengiriman->load("forecast");
            $pengiriman->load("order.klien");
            $pengiriman->load("pengirimanDetails");

            // Debug logging untuk troubleshoot
            Log::info("Debug Pengiriman Detail:", [
                "pengiriman_id" => $pengiriman->id,
                "purchase_order_id" => $pengiriman->purchase_order_id,
                "has_order" => $pengiriman->order ? "YES" : "NO",
                "qty_total" => $pengiriman->order?->qty_total,
                "total_amount" => $pengiriman->order?->total_amount,
            ]);

            return view(
                "pages.purchasing.pengiriman.menunggu-verifikasi.detail",
                compact("pengiriman"),
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Pengiriman not found with ID: " . $id);
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Pengiriman tidak ditemukan atau bukan status menunggu verifikasi",
                ],
                404,
            );
        } catch (\Exception $e) {
            Log::error(
                "Error in getDetailVerifikasi for ID " .
                    $id .
                    ": " .
                    $e->getMessage(),
            );
            Log::error("Stack trace: " . $e->getTraceAsString());

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
     * Verifikasi pengiriman - ubah status menjadi berhasil dan kurangi qty Order
     */
    public function verifikasiPengiriman($id)
    {
        // Check user role authorization
        $user = Auth::user();
        if (!in_array($user->role, ["direktur", "manager_purchasing"])) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Anda tidak memiliki akses untuk memverifikasi pengiriman. Hanya Direktur dan Manager Purchasing yang dapat melakukan verifikasi.",
                ],
                403,
            );
        }

        try {
            Log::info("Starting verifikasi pengiriman for ID: {$id}");

            DB::beginTransaction();

            $pengiriman = Pengiriman::with([
                "order",
                "pengirimanDetails.orderDetail",
            ])
                ->where("status", "menunggu_verifikasi")
                ->findOrFail($id);

            Log::info("Found pengiriman", [
                "pengiriman_id" => $pengiriman->id,
                "order_id" => $pengiriman->purchase_order_id,
                "details_count" => $pengiriman->pengirimanDetails->count(),
                "total_qty_kirim" => $pengiriman->total_qty_kirim,
            ]);

            // Update status pengiriman menjadi berhasil (tanpa mengubah catatan)
            $pengiriman->update([
                "status" => "berhasil",
                // Catatan tetap sama seperti sebelumnya
            ]);

            Log::info("Updated pengiriman status to berhasil");

            // Kurangi qty dan total_harga di order_details untuk setiap detail
            // PENTING: Gunakan withoutEvents() agar tidak trigger observer yang update order totals
            foreach ($pengiriman->pengirimanDetails as $detail) {
                $orderDetail = $detail->orderDetail;
                if ($orderDetail) {
                    $oldQty = $orderDetail->qty;
                    $oldTotalHarga = $orderDetail->total_harga;
                    $newQty = $orderDetail->qty - $detail->qty_kirim;

                    // Hitung total_harga baru berdasarkan qty baru
                    $newTotalHarga = max(0, $newQty) * $orderDetail->harga_jual;

                    // Update tanpa trigger events (agar tidak update total_qty & total_amount di order)
                    $orderDetail->withoutEvents(function () use (
                        $orderDetail,
                        $newQty,
                        $newTotalHarga,
                    ) {
                        $orderDetail->update([
                            "qty" => max(0, $newQty),
                            "total_harga" => $newTotalHarga,
                        ]);
                    });

                    Log::info(
                        "Updated order detail qty and total_harga (without triggering order totals update)",
                        [
                            "order_detail_id" => $orderDetail->id,
                            "old_qty" => $oldQty,
                            "qty_kirim" => $detail->qty_kirim,
                            "new_qty" => max(0, $newQty),
                            "old_total_harga" => $oldTotalHarga,
                            "new_total_harga" => $newTotalHarga,
                            "harga_jual" => $orderDetail->harga_jual,
                        ],
                    );
                } else {
                    Log::warning(
                        "OrderDetail not found for pengiriman detail",
                        [
                            "pengiriman_detail_id" => $detail->id,
                            "purchase_order_bahan_baku_id" =>
                                $detail->purchase_order_bahan_baku_id,
                        ],
                    );
                }
            }

            // Update status order jika diperlukan (tanpa mengubah qty)
            if ($pengiriman->order) {
                $oldStatus = $pengiriman->order->status;

                // Jika status order adalah 'dikonfirmasi', ubah menjadi 'diproses'
                if ($pengiriman->order->status === "dikonfirmasi") {
                    $pengiriman->order->update([
                        "status" => "diproses",
                    ]);

                    Log::info("Updated order status", [
                        "order_id" => $pengiriman->order->id,
                        "old_status" => $oldStatus,
                        "new_status" => "diproses",
                    ]);
                }
            } else {
                Log::warning("Order not found for pengiriman", [
                    "pengiriman_id" => $pengiriman->id,
                    "purchase_order_id" => $pengiriman->purchase_order_id,
                ]);
            }

            DB::commit();
            Log::info("Verifikasi pengiriman completed successfully");

            // After commit, check if order is nearing fulfillment and notify Marketing
            if ($pengiriman->order) {
                // Reload order to get fresh data after commit
                $order = Order::with([
                    "creator",
                    "klien",
                    "orderDetails",
                ])->find($pengiriman->order->id);

                if ($order && $order->status === "diproses") {
                    $fulfillmentPercentage = $order->getFulfillmentPercentage();

                    Log::info("Checking order fulfillment for notification", [
                        "order_id" => $order->id,
                        "fulfillment_percentage" => $fulfillmentPercentage,
                    ]);

                    // Notify if order is at 95-105% fulfillment
                    if (
                        $fulfillmentPercentage >= 95 &&
                        $fulfillmentPercentage <= 105
                    ) {
                        $notificationCount = NotificationService::notifyOrderNearingFulfillment(
                            $order,
                            $fulfillmentPercentage,
                            $pengiriman,
                        );

                        Log::info(
                            "Sent order nearing fulfillment notifications to Marketing",
                            [
                                "order_id" => $order->id,
                                "notification_count" => $notificationCount,
                                "fulfillment_percentage" => $fulfillmentPercentage,
                            ],
                        );
                    }
                }
            }

            return response()->json([
                "success" => true,
                "message" => "Pengiriman berhasil diverifikasi",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in verifikasiPengiriman: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Gagal memverifikasi pengiriman: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Revisi pengiriman - ubah status menjadi pending dan update catatan
     */
    public function revisiPengiriman(Request $request, $id)
    {
        // Check user role authorization
        $user = Auth::user();
        if (!in_array($user->role, ["direktur", "manager_purchasing"])) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Anda tidak memiliki akses untuk merevisi pengiriman. Hanya Direktur dan Manager Purchasing yang dapat melakukan revisi.",
                ],
                403,
            );
        }

        try {
            $request->validate([
                "catatan" => "required|string|max:1000",
                "hapus_foto" => "nullable|boolean",
            ]);

            $pengiriman = Pengiriman::where(
                "status",
                "menunggu_verifikasi",
            )->findOrFail($id);

            // Jika diminta untuk hapus foto, hapus dari storage
            if (
                $request->get("hapus_foto", false) &&
                $pengiriman->bukti_foto_bongkar
            ) {
                $oldPhotos = is_array($pengiriman->bukti_foto_bongkar)
                    ? $pengiriman->bukti_foto_bongkar
                    : [$pengiriman->bukti_foto_bongkar];

                foreach ($oldPhotos as $oldPhoto) {
                    if (
                        Storage::disk("public")->exists(
                            "pengiriman/bukti/" . $oldPhoto,
                        )
                    ) {
                        Storage::disk("public")->delete(
                            "pengiriman/bukti/" . $oldPhoto,
                        );
                    }
                }

                $pengiriman->update([
                    "status" => "pending",
                    "catatan" => $request->catatan,
                    "bukti_foto_bongkar" => null,
                ]);
            } else {
                $pengiriman->update([
                    "status" => "pending",
                    "catatan" => $request->catatan,
                ]);
            }

            return response()->json([
                "success" => true,
                "message" =>
                    "Pengiriman berhasil direvisi dan dikembalikan ke status pending",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Gagal merevisi pengiriman: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get revisi modal for pengiriman verifikasi
     */
    public function getRevisiModal($id)
    {
        try {
            // First, get the pengiriman with basic info
            $pengiriman = Pengiriman::where(
                "status",
                "menunggu_verifikasi",
            )->findOrFail($id);

            // Load relations step by step with error handling
            try {
                $pengiriman->load("purchasing");
            } catch (\Exception $e) {
                Log::error(
                    "Error loading purchasing relation in revisi modal: " .
                        $e->getMessage(),
                );
            }

            try {
                $pengiriman->load([
                    "order" => function ($query) {
                        $query->with("klien");
                    },
                ]);
            } catch (\Exception $e) {
                Log::error(
                    "Error loading order relation in revisi modal: " .
                        $e->getMessage(),
                );
            }

            return view(
                "pages.purchasing.pengiriman.menunggu-verifikasi.revisi",
                compact("pengiriman"),
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Pengiriman not found for revisi modal with ID: " . $id);
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Pengiriman tidak ditemukan atau bukan status menunggu verifikasi",
                ],
                404,
            );
        } catch (\Exception $e) {
            Log::error(
                "Error in getRevisiModal for ID " .
                    $id .
                    ": " .
                    $e->getMessage(),
            );
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Gagal memuat modal revisi: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get verifikasi modal for pengiriman verifikasi
     */
    public function getVerifikasiModal($id)
    {
        try {
            // First, get the pengiriman with basic info
            $pengiriman = Pengiriman::where(
                "status",
                "menunggu_verifikasi",
            )->findOrFail($id);

            // Load relations step by step with error handling
            try {
                $pengiriman->load("purchasing");
            } catch (\Exception $e) {
                Log::error(
                    "Error loading purchasing relation in verifikasi modal: " .
                        $e->getMessage(),
                );
            }

            try {
                $pengiriman->load([
                    "order" => function ($query) {
                        $query->with("klien");
                    },
                ]);
            } catch (\Exception $e) {
                Log::error(
                    "Error loading order relation in verifikasi modal: " .
                        $e->getMessage(),
                );
            }

            // Load pengiriman details with safer approach
            try {
                $pengiriman->load([
                    "pengirimanDetails" => function ($query) {
                        $query->with([
                            "orderDetail",
                            "bahanBakuSupplier" => function ($q) {
                                $q->with("supplier");
                            },
                        ]);
                    },
                ]);
            } catch (\Exception $e) {
                Log::error(
                    "Error loading pengirimanDetails relation in verifikasi modal: " .
                        $e->getMessage(),
                );
                // Load pengirimanDetails tanpa relasi bersarang sebagai fallback
                $pengiriman->load("pengirimanDetails");
            }

            return view(
                "pages.purchasing.pengiriman.menunggu-verifikasi.verifikasi",
                compact("pengiriman"),
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error(
                "Pengiriman not found for verifikasi modal with ID: " . $id,
            );
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Pengiriman tidak ditemukan atau bukan status menunggu verifikasi",
                ],
                404,
            );
        } catch (\Exception $e) {
            Log::error(
                "Error in getVerifikasiModal for ID " .
                    $id .
                    ": " .
                    $e->getMessage(),
            );
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Gagal memuat modal verifikasi: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Upload foto tanda terima for pengiriman
     */
    public function uploadFotoTandaTerima(Request $request, $id)
    {
        // Check user role authorization - Only Direktur, Manager Purchasing, and Staff Purchasing can upload
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
                        "Anda tidak memiliki akses untuk mengupload foto tanda terima. Hanya Direktur, Manager Purchasing, dan Staff Purchasing yang dapat melakukan aksi ini.",
                ],
                403,
            );
        }

        // For Staff Purchasing, ensure they are the PIC
        if ($user->role === "staff_purchasing") {
            $pengiriman = Pengiriman::where(
                "status",
                "menunggu_verifikasi",
            )->find($id);
            if ($pengiriman && $pengiriman->purchasing_id !== $user->id) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Anda hanya dapat mengupload foto tanda terima untuk pengiriman yang Anda tangani sebagai PIC.",
                    ],
                    403,
                );
            }
        }

        try {
            // Validate
            $request->validate([
                "foto_tanda_terima" =>
                    "required|image|mimes:jpeg,png,jpg|max:2048",
            ]);

            // Find pengiriman (menunggu verifikasi only)
            $pengiriman = Pengiriman::where(
                "status",
                "menunggu_verifikasi",
            )->findOrFail($id);

            // Handle file upload
            if ($request->hasFile("foto_tanda_terima")) {
                // Delete old photo if exists
                if (
                    $pengiriman->foto_tanda_terima &&
                    Storage::disk("public")->exists(
                        "pengiriman/tanda-terima/" .
                            $pengiriman->foto_tanda_terima,
                    )
                ) {
                    Storage::disk("public")->delete(
                        "pengiriman/tanda-terima/" .
                            $pengiriman->foto_tanda_terima,
                    );
                }

                // Upload new file
                $file = $request->file("foto_tanda_terima");
                if ($file && $file->isValid()) {
                    $tandaTerimaFileName =
                        "tanda_terima_" .
                        $pengiriman->id .
                        "_" .
                        time() .
                        "." .
                        $file->getClientOriginalExtension();
                    $file->storeAs(
                        "pengiriman/tanda-terima",
                        $tandaTerimaFileName,
                        "public",
                    );

                    // Update pengiriman
                    $pengiriman->update([
                        "foto_tanda_terima" => $tandaTerimaFileName,
                        "foto_tanda_terima_uploaded_at" => now(),
                    ]);

                    return response()->json([
                        "success" => true,
                        "message" => "Foto tanda terima berhasil diupload",
                        "foto_url" => asset(
                            "storage/pengiriman/tanda-terima/" .
                                $tandaTerimaFileName,
                        ),
                        "uploaded_at" => now()->format("d M Y, H:i") . " WIB",
                    ]);
                }
            }

            return response()->json(
                [
                    "success" => false,
                    "message" => "File tidak valid",
                ],
                400,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Validasi gagal: " . $e->validator->errors()->first(),
                ],
                422,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Pengiriman tidak ditemukan atau bukan status menunggu verifikasi",
                ],
                404,
            );
        } catch (\Exception $e) {
            Log::error(
                "Error uploading foto tanda terima for ID " .
                    $id .
                    ": " .
                    $e->getMessage(),
            );

            return response()->json(
                [
                    "success" => false,
                    "message" => "Gagal mengupload foto: " . $e->getMessage(),
                ],
                500,
            );
        }
    }
}
