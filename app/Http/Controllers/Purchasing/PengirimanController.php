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
    /* ======================================================================
     * PRIVATE HELPERS (BUSINESS LOGIC & SHARED FUNCTIONS)
     * ====================================================================== */

    private function authorizeAction(?Pengiriman $pengiriman = null, bool $enforcePic = true): ?array
    {
        $user = Auth::user();
        if (!in_array($user->role, ["direktur", "manager_purchasing", "staff_purchasing"])) {
            return [
                "success" => false,
                "message" => "Anda tidak memiliki akses. Hanya Direktur, Manager Purchasing, dan Staff Purchasing yang dapat melakukan aksi ini."
            ];
        }

        if ($enforcePic && $user->role === "staff_purchasing" && $pengiriman && $pengiriman->purchasing_id !== $user->id) {
            return [
                "success" => false,
                "message" => "Anda hanya dapat melakukan aksi ini untuk pengiriman yang Anda tangani sebagai PIC."
            ];
        }

        return null;
    }

    private function checkPartialDelivery(Pengiriman $pengiriman): array
    {
        try {
            if (!$pengiriman->relationLoaded('forecast')) {
                $pengiriman->load('forecast');
            }
            
            if (!$pengiriman->forecast) {
                return ['isPartial' => false, 'percentage' => 0, 'totalQtyKirim' => 0, 'totalQtyForecast' => 0];
            }
            
            $totalQtyForecast = (float) $pengiriman->forecast->total_qty_forecast;
            $totalQtyKirim = (float) $pengiriman->total_qty_kirim;
            $percentage = $totalQtyForecast > 0 ? ($totalQtyKirim / $totalQtyForecast) * 100 : 0;
            $isPartial = $percentage > 0 && $percentage <= 70;
            
            return [
                'isPartial' => $isPartial,
                'percentage' => round($percentage, 2),
                'totalQtyKirim' => $totalQtyKirim,
                'totalQtyForecast' => $totalQtyForecast
            ];
        } catch (\Exception $e) {
            Log::error('Error in checkPartialDelivery: ' . $e->getMessage());
            return ['isPartial' => false, 'percentage' => 0, 'totalQtyKirim' => 0, 'totalQtyForecast' => 0];
        }
    }
    
    private function reduceOrderDetailQty(Pengiriman $pengiriman): bool
    {
        if ($pengiriman->qty_reduced) {
            Log::info("Qty already reduced for Pengiriman ID: {$pengiriman->id}, skipping reduction");
            return false;
        }

        if (!$pengiriman->relationLoaded('details')) {
            $pengiriman->load('details.orderDetail');
        }

        $detailsUpdated = 0;
        foreach ($pengiriman->details as $detail) {
            if ($detail->orderDetail) {
                $orderDetail = $detail->orderDetail;
                $oldQty = (float)$orderDetail->qty;
                $newQty = max(0, $oldQty - (float)$detail->qty_kirim);
                
                $orderDetail->qty = $newQty;
                $orderDetail->total_harga = (float)$orderDetail->qty * (float)$orderDetail->harga_jual;
                $orderDetail->saveQuietly();
                $detailsUpdated++;
            }
        }

        $pengiriman->qty_reduced = true;
        $pengiriman->saveQuietly();
        
        Log::info("Marked Pengiriman ID: {$pengiriman->id} as qty_reduced. Updated {$detailsUpdated} order details.");
        return true;
    }

    private function restoreOrderDetailQty(Pengiriman $pengiriman): bool
    {
        if (!$pengiriman->qty_reduced) {
            return false;
        }

        if (!$pengiriman->relationLoaded('details')) {
            $pengiriman->load('details.orderDetail');
        }

        foreach ($pengiriman->details as $detail) {
            if ($detail->orderDetail) {
                $orderDetail = $detail->orderDetail;
                $orderDetail->qty = (float)$orderDetail->qty + (float)$detail->qty_kirim;
                $orderDetail->total_harga = (float)$orderDetail->qty * (float)$orderDetail->harga_jual;
                $orderDetail->saveQuietly();
            }
        }

        $pengiriman->qty_reduced = false;
        $pengiriman->saveQuietly();
        return true;
    }

    private function populateApprovalFromRequest(\App\Models\ApprovalPembayaran $approval, Pengiriman $pengiriman, Request $request): void 
    {
        $refraksiType  = $request->input('refraksi_type', 'qty');
        $refraksiValue = floatval($request->input('refraksi_value', 0));
        $qtyBefore    = floatval($pengiriman->total_qty_kirim);
        $amountBefore = floatval($pengiriman->total_harga_kirim);

        $refraksiAmount  = 0;
        $qtyAfter        = $qtyBefore;
        $amountAfter     = $amountBefore;

        if ($refraksiValue > 0) {
            if ($refraksiType === 'qty') {
                $refraksiQty    = $qtyBefore * ($refraksiValue / 100);
                $qtyAfter       = $qtyBefore - $refraksiQty;
                $hargaPerKg     = $qtyBefore > 0 ? $amountBefore / $qtyBefore : 0;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
                $amountAfter    = $amountBefore - $refraksiAmount;
            } elseif ($refraksiType === 'rupiah') {
                $refraksiAmount = $refraksiValue * $qtyBefore;
                $amountAfter    = $amountBefore - $refraksiAmount;
            } elseif ($refraksiType === 'lainnya') {
                $refraksiAmount = $refraksiValue;
                $amountAfter    = $amountBefore - $refraksiAmount;
            }

            $approval->refraksi_type          = $refraksiType;
            $approval->refraksi_value         = $refraksiValue;
            $approval->refraksi_amount        = $refraksiAmount;
            $approval->qty_after_refraksi     = $qtyAfter;
            $approval->amount_after_refraksi  = $amountAfter;
        } else {
            $approval->refraksi_type          = null;
            $approval->refraksi_value         = 0;
            $approval->refraksi_amount        = 0;
            $approval->qty_after_refraksi     = $qtyBefore;
            $approval->amount_after_refraksi  = $amountBefore;
        }

        $approval->qty_before_refraksi    = $qtyBefore;
        $approval->amount_before_refraksi = $amountBefore;
        $approval->save();

        $approval->expenses()->delete();

        $fixed = [
            'truk' => floatval($request->input('expense_truk', 0)),
            'kuli' => floatval($request->input('expense_kuli', 0)),
            'fee'  => floatval($request->input('expense_fee', 0)),
        ];

        foreach ($fixed as $type => $amount) {
            if ($amount > 0) $approval->expenses()->create(['type' => $type, 'amount' => $amount]);
        }

        foreach ((array) $request->input('expense_others', []) as $row) {
            $type   = trim((string) ($row['type'] ?? ''));
            $amount = floatval($row['amount'] ?? 0);
            if ($type !== '' && $amount > 0 && !in_array(strtolower($type), ['truk', 'kuli', 'fee'], true)) {
                $approval->expenses()->create(['type' => $type, 'amount' => $amount]);
            }
        }

        $approval->refresh();
        $expensesTotal = floatval($approval->expenses->sum('amount'));
        $subtotal      = max(0, $amountBefore - $refraksiAmount + $expensesTotal);

        $approval->additional_expenses_total = $expensesTotal;
        $approval->subtotal                  = $subtotal;
        $approval->total_dibayarkan          = $subtotal;
        $approval->save();
    }

    /**
     * Sinkronkan InvoicePenagihan (single atau merged) yang terhubung dengan $pengiriman
     * setiap kali pengiriman ini di-submit ulang (revisi). Kegagalan sinkronisasi TIDAK
     * boleh membatalkan submit pengiriman itu sendiri — cukup dicatat di log.
     */
    private function syncInvoicePenagihanIfExists(Pengiriman $pengiriman): void
    {
        try {
            $pengiriman->loadMissing(['invoicePenagihan', 'mergedInvoicePenagihan']);

            $invoice = $pengiriman->invoicePenagihan ?? $pengiriman->mergedInvoicePenagihan;

            if (!$invoice) {
                return;
            }

            $invoice->recalculateFromShipments($pengiriman);

            Log::info("Invoice Penagihan #{$invoice->id} disinkronkan otomatis setelah revisi Pengiriman #{$pengiriman->id}", [
                'invoice_id' => $invoice->id,
                'pengiriman_id' => $pengiriman->id,
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal sinkronisasi Invoice Penagihan untuk Pengiriman #{$pengiriman->id}: " . $e->getMessage());
        }
    }

    private function loadPengirimanForModal(int $id, ?string $status = null)
    {
        $query = Pengiriman::with([
            "order", "order.klien", "order.orderDetails", "order.orderDetails.bahanBakuKlien",
            "purchasing", "forecast", "pengirimanDetails.bahanBakuSupplier",
            "pengirimanDetails.bahanBakuSupplier.supplier", "pengirimanDetails.orderDetail",
            "approvalPembayaran", "approvalPembayaran.expenses", "invoicePenagihan", "invoicePenagihan.expenses"
        ]);

        if ($status) {
            $query->where("status", $status);
        }

        return $query->findOrFail($id);
    }

    private function buildIndexQuery(string $status, Request $request)
    {
        $query = Pengiriman::with([
            "order:id,po_number,klien_id", "order.klien:id,nama,cabang", "purchasing:id,nama",
            "pengirimanDetails", "forecast:id,total_qty_forecast",
            "approvalPembayaran:id,pengiriman_id,refraksi_type,refraksi_value,refraksi_amount,qty_before_refraksi,qty_after_refraksi,amount_before_refraksi,amount_after_refraksi,bukti_pembayaran",
        ])->whereNotNull("purchase_order_id")->whereNotNull("purchasing_id")->where("status", $status);

        $searchKey = $status === 'pending' ? 'search_masuk' : "search_{$status}";
        if ($request->filled($searchKey)) {
            $search = $request->get($searchKey);
            $query->where(function ($q) use ($search) {
                $q->whereHas("order", fn($orderQuery) => $orderQuery->where("po_number", "LIKE", "%{$search}%"))
                  ->orWhereHas("purchasing", fn($purchasingQuery) => $purchasingQuery->where("nama", "LIKE", "%{$search}%"))
                  ->orWhere("no_pengiriman", "LIKE", "%{$search}%");
            });
        }

        $filterKey = $status === 'pending' ? 'filter_purchasing' : "filter_purchasing_{$status}";
        if ($request->filled($filterKey)) {
            $query->where("purchasing_id", $request->get($filterKey));
        }

        if (in_array($status, ['berhasil', 'gagal']) && $request->filled("date_range_{$status}")) {
            $query->whereDate("tanggal_kirim", $request->get("date_range_{$status}"));
        }

        $sortKey = $status === 'pending' ? 'sort_date_masuk' : "sort_date_{$status}";
        if ($status === 'berhasil' || $status === 'gagal') $sortKey = "sort_order_{$status}";
        
        if ($request->filled($sortKey)) {
            $query->orderBy("created_at", $request->get($sortKey) === "oldest" ? "asc" : "desc");
        } else {
            $query->orderBy("created_at", "desc");
        }

        return $query;
    }

    /* ======================================================================
     * PUBLIC ENDPOINTS
     * ====================================================================== */

    public function index(Request $request): View
    {
        $pengirimanMasuk = $this->buildIndexQuery("pending", $request)->paginate(10, ["*"], "masuk_page");
        $menungguVerifikasi = $this->buildIndexQuery("menunggu_verifikasi", $request)->paginate(10, ["*"], "verifikasi_page");
        $menungguFisik = $this->buildIndexQuery("menunggu_fisik", $request)->paginate(10, ["*"], "fisik_page");
        $pengirimanBerhasil = $this->buildIndexQuery("berhasil", $request)->paginate(10, ["*"], "berhasil_page");
        $pengirimanGagal = $this->buildIndexQuery("gagal", $request)->paginate(10, ["*"], "gagal_page");
        
        foreach ([$menungguVerifikasi, $menungguFisik, $pengirimanBerhasil] as $collection) {
            foreach ($collection as $pengiriman) {
                $pengiriman->partialInfo = $this->checkPartialDelivery($pengiriman);
            }
        }

        return view("pages.purchasing.pengiriman", compact(
            "pengirimanMasuk", "menungguVerifikasi", "menungguFisik", "pengirimanBerhasil", "pengirimanGagal"
        ));
    }

    public function create(): View
    {
        $klien = Klien::all();
        $orders = Order::where("status", ["dikonfirmasi", "diproses"])->get();
        return view("pages.purchasing.pengiriman-create", compact("klien", "orders"));
    }

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
            "total_amount" => 0,
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
        return redirect()->route("purchasing.pengiriman.index")->with("success", "Data pengiriman berhasil dibuat.");
    }

    public function show(Pengiriman $pengiriman): View
    {
        $pengiriman->load(["klien", "order", "details.bahanBaku"]);
        return view("pages.purchasing.pengiriman-show", compact("pengiriman"));
    }

    public function edit(Pengiriman $pengiriman): View
    {
        $pengiriman->load(["details"]);
        $klien = Klien::all();
        $orders = Order::where("status", "approved")->get();
        return view("pages.purchasing.pengiriman-edit", compact("pengiriman", "klien", "orders"));
    }

    public function update(Request $request, Pengiriman $pengiriman): RedirectResponse 
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

        $pengiriman->update([
            "purchase_order_id" => $validated["purchase_order_id"],
            "klien_id" => $validated["klien_id"],
            "tanggal_pengiriman" => $validated["tanggal_pengiriman"],
            "status" => $validated["status"],
            "keterangan" => $validated["keterangan"],
        ]);

        $pengiriman->details()->delete();
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
        return redirect()->route("purchasing.pengiriman.index")->with("success", "Data pengiriman berhasil diperbarui.");
    }

    public function destroy(Pengiriman $pengiriman): RedirectResponse
    {
        $pengiriman->details()->delete();
        $pengiriman->delete();
        return redirect()->route("purchasing.pengiriman.index")->with("success", "Data pengiriman berhasil dihapus.");
    }

    public function updateStatus(Request $request, Pengiriman $pengiriman)
    {
        $validated = $request->validate([
            "status" => "required|in:pending,menunggu_verifikasi,berhasil,gagal",
            "catatan" => "nullable|string",
        ]);

        $pengiriman->status = $validated["status"];
        if (isset($validated["catatan"])) $pengiriman->catatan = $validated["catatan"];
        $pengiriman->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(["success" => true, "message" => "Status pengiriman berhasil diperbarui", "data" => $pengiriman]);
        }
        return redirect()->back()->with("success", "Status pengiriman berhasil diperbarui.");
    }

    public function getDetail(Request $request, $id)
    {
        try {
            $pengiriman = Pengiriman::with(["order", "order.klien", "purchasing", "pengirimanDetails"])->findOrFail($id);
            return response()->json(["success" => true, "pengiriman" => $pengiriman]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Gagal memuat detail: " . $e->getMessage()], 500);
        }
    }

    public function getAksiModal(Request $request, $id)
    {
        try {
            $pengiriman = $this->loadPengirimanForModal($id);
            
            foreach ($pengiriman->pengirimanDetails as $detail) {
                if ($detail->bahanBakuSupplier && $detail->bahanBakuSupplier->supplier) {
                    $detail->bahanBakuSupplier->supplier->load("picPurchasing");
                }
                if ($detail->bahanBakuSupplier) {
                    $detail->bahanBakuSupplier->load(["riwayatHarga" => fn($q) => $q->latest("tanggal_perubahan")->limit(1)]);
                }
            }

            return view("pages.purchasing.pengiriman.pengiriman-masuk.detail", compact("pengiriman"));
        } catch (\Exception $e) {
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }

    public function getSubmitModal(Request $request)
    {
        try {
            $pengiriman = Pengiriman::with(["order", "order.klien", "purchasing", "forecast"])
                ->findOrFail($request->get("pengiriman_id", 1));
            return view("pages.purchasing.pengiriman.pengiriman-masuk.submit", compact("pengiriman"));
        } catch (\Exception $e) {
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }

    /* ======================================================================
     * SUBMIT PENGIRIMAN LOGIC
     * ====================================================================== */

    private function getSubmitValidationRules(): array
    {
        return [
            "pengiriman_id" => "required|exists:pengiriman,id",
            "tanggal_kirim" => "required|date",
            "hari_kirim" => "required|string",
            "total_qty_kirim" => "required|numeric|min:0",
            "total_harga_kirim" => "required|numeric|min:0",
            "bukti_foto_bongkar" => "nullable|array",
            "bukti_foto_bongkar.*" => "file|mimes:jpeg,png,jpg,pdf|max:10240",
            "foto_tanda_terima" => "nullable|file|mimes:jpeg,png,jpg,pdf|max:10240",
            "catatan" => "nullable|string",
            "catatan_refraksi" => "nullable|string",
            'refraksi_type' => 'nullable|in:qty,rupiah,lainnya',
            'refraksi_value' => 'nullable|numeric|min:0',
            'expense_truk' => 'nullable|numeric|min:0',
            'expense_kuli' => 'nullable|numeric|min:0',
            'expense_fee' => 'nullable|numeric|min:0',
            'expense_others' => 'nullable|array',
            'expense_others.*.type' => 'nullable|string|max:100',
            'expense_others.*.amount' => 'nullable|numeric|min:0',
            "details" => "required|array|min:1",
            "details.*.bahan_baku_supplier_id" => "required|exists:bahan_baku_supplier,id",
            "details.*.qty_kirim" => "required|numeric|min:0",
            "details.*.harga_satuan" => "nullable|numeric|min:0",
            "details.*.total_harga" => "nullable|numeric|min:0",
        ];
    }

    private function handleBuktiFotoUpload(Request $request, Pengiriman $pengiriman): array
    {
        $existingPhotos = $pengiriman->bukti_foto_bongkar_array ?? [];
        $buktiFileNames = $existingPhotos;
        $buktiFotoUploadedAt = $pengiriman->bukti_foto_bongkar_uploaded_at;

        if ($request->hasFile("bukti_foto_bongkar")) {
            $uploadedFiles = is_array($request->file("bukti_foto_bongkar")) 
                ? $request->file("bukti_foto_bongkar") 
                : [$request->file("bukti_foto_bongkar")];

            foreach ($uploadedFiles as $file) {
                if ($file && $file->isValid()) {
                    $buktiFileName = "bukti_" . $pengiriman->id . "_" . time() . "_" . uniqid() . "." . $file->getClientOriginalExtension();
                    $file->storeAs("pengiriman/bukti", $buktiFileName, "public");
                    $buktiFileNames[] = $buktiFileName;
                }
            }
            
            if (count($buktiFileNames) > count($existingPhotos)) {
                $buktiFotoUploadedAt = now();
            }
        }
        
        return [
            'filenames' => !empty($buktiFileNames) ? $buktiFileNames : null,
            'uploaded_at' => $buktiFotoUploadedAt
        ];
    }

    private function processSubmitDetails(array $validatedDetails, Pengiriman $pengiriman): void
    {
        foreach ($validatedDetails as $index => $detail) {
            $existingDetail = $pengiriman->pengirimanDetails->get($index);
            $bahanBakuSupplier = \App\Models\BahanBakuSupplier::find($detail["bahan_baku_supplier_id"]);
            
            if (!$bahanBakuSupplier) continue;

            // Preserve original matching logic (by name)
            $namaBahanBaku = $bahanBakuSupplier->nama;
            $correctOrderDetail = $pengiriman->order->orderDetails->first(function($od) use ($namaBahanBaku) {
                return $od->bahanBakuKlien && $od->bahanBakuKlien->nama === $namaBahanBaku;
            });
            
            $poDetailId = $correctOrderDetail ? $correctOrderDetail->id : null;
            
            if (!$poDetailId && $existingDetail && $existingDetail->purchase_order_bahan_baku_id) {
                $poDetailId = $existingDetail->purchase_order_bahan_baku_id;
            }
            
            if (!$poDetailId) {
                $usedOrderDetailIds = $pengiriman->pengirimanDetails->pluck('purchase_order_bahan_baku_id')->filter()->unique()->toArray();
                $unusedOrderDetail = $pengiriman->order->orderDetails->whereNotIn('id', $usedOrderDetailIds)->first();
                if ($unusedOrderDetail) $poDetailId = $unusedOrderDetail->id;
            }
            
            if (!$poDetailId) {
                throw new \Exception("Tidak dapat menemukan order detail untuk bahan baku '" . $namaBahanBaku . "'. Pastikan bahan baku ini ada di PO.");
            }
            
            $hargaSatuan = 0;
            if (isset($detail["harga_satuan"]) && $detail["harga_satuan"] > 0) {
                $hargaSatuan = $detail["harga_satuan"];
            } else {
                $klienId = $pengiriman->order->klien_id ?? null;
                if ($klienId) {
                    $bahanBakuSupplierKlien = \App\Models\BahanBakuSupplierKlien::where('bahan_baku_supplier_id', $bahanBakuSupplier->id)
                        ->where('klien_id', $klienId)->first();
                    if ($bahanBakuSupplierKlien) $hargaSatuan = $bahanBakuSupplierKlien->harga_per_satuan;
                }
                if ($hargaSatuan == 0) $hargaSatuan = $bahanBakuSupplier->harga_per_satuan ?? 0;
            }
            
            $calculatedTotal = $detail["qty_kirim"] * $hargaSatuan;
            $totalHarga = (isset($detail["total_harga"]) && abs($detail["total_harga"] - $calculatedTotal) < 0.01) ? $detail["total_harga"] : $calculatedTotal;
            
            if ($existingDetail) {
                $existingDetail->update([
                    "purchase_order_bahan_baku_id" => $poDetailId,
                    "qty_kirim" => $detail["qty_kirim"],
                    "harga_satuan" => $hargaSatuan,
                    "total_harga" => $totalHarga,
                ]);
            } else {
                PengirimanDetail::create([
                    "pengiriman_id" => $pengiriman->id,
                    "purchase_order_bahan_baku_id" => $poDetailId,
                    "bahan_baku_supplier_id" => $detail["bahan_baku_supplier_id"],
                    "qty_kirim" => $detail["qty_kirim"],
                    "harga_satuan" => $hargaSatuan,
                    "total_harga" => $totalHarga,
                ]);
            }
        }
    }

    public function submitPengiriman(Request $request)
    {
        $pengirimanId = $request->input('pengiriman_id');
        $pengirimanAuth = $pengirimanId ? Pengiriman::find($pengirimanId) : null;
        
        $authError = $this->authorizeAction($pengirimanAuth, true);
        if ($authError) return response()->json($authError, 403);

        try {
            $validatedData = $request->validate($this->getSubmitValidationRules(), [
                "pengiriman_id.required" => "ID pengiriman diperlukan",
                "tanggal_kirim.required" => "Tanggal kirim harus diisi",
                "details.required" => "Detail barang harus diisi",
            ]);

            DB::beginTransaction();

            $pengiriman = Pengiriman::with(['order.orderDetails.bahanBakuKlien', 'pengirimanDetails.bahanBakuSupplier'])
                ->findOrFail($validatedData["pengiriman_id"]);

            $uploadResult = $this->handleBuktiFotoUpload($request, $pengiriman);

            $pengiriman->update([
                "no_pengiriman" => $pengiriman->no_pengiriman ?: Pengiriman::generateNoPengiriman(),
                "tanggal_kirim" => $validatedData["tanggal_kirim"],
                "hari_kirim" => $validatedData["hari_kirim"],
                "total_qty_kirim" => $validatedData["total_qty_kirim"],
                "total_harga_kirim" => $validatedData["total_harga_kirim"],
                "bukti_foto_bongkar" => $uploadResult['filenames'],
                "bukti_foto_bongkar_uploaded_at" => $uploadResult['uploaded_at'],
                "catatan" => $validatedData["catatan"] ?? null,
                "catatan_refraksi" => $validatedData["catatan_refraksi"] ?? null,
                "status" => "menunggu_fisik",
            ]);

            $this->processSubmitDetails($validatedData["details"], $pengiriman);
            $this->reduceOrderDetailQty($pengiriman);
            $pengiriman->refresh();
            
            if ($pengiriman->approvalPembayaran) {
                $this->populateApprovalFromRequest($pengiriman->approvalPembayaran, $pengiriman, $request);
            }

            $this->syncInvoicePenagihanIfExists($pengiriman);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Pengiriman berhasil diajukan untuk verifikasi",
                "no_pengiriman" => $pengiriman->no_pengiriman,
                "pengiriman" => $pengiriman,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(["success" => false, "message" => "Validasi gagal", "errors" => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["success" => false, "message" => "Terjadi kesalahan: " . $e->getMessage()], 500);
        }
    }

    public function getBahanBakuHarga($id)
    {
        try {
            $bahanBaku = \App\Models\BahanBakuSupplier::with(["riwayatHarga" => fn($q) => $q->latest("tanggal_perubahan")->limit(1)])->findOrFail($id);
            $latestHarga = $bahanBaku->riwayatHarga->first();
            return response()->json([
                "success" => true,
                "harga" => $latestHarga ? $latestHarga->harga_baru : $bahanBaku->harga_per_satuan,
                "nama_bahan_baku" => $bahanBaku->nama,
            ]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Bahan baku tidak ditemukan"], 404);
        }
    }

    public function getBatalModal(Request $request)
    {
        try {
            $pengiriman = Pengiriman::with(["order", "order.klien", "purchasing", "forecast"])->findOrFail($request->get("pengiriman_id"));
            return view("pages.purchasing.pengiriman.pengiriman-masuk.batal", compact("pengiriman"));
        } catch (\Exception $e) {
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }

    public function batalPengiriman(Request $request)
    {
        $pengirimanId = $request->input('pengiriman_id');
        $pengiriman = $pengirimanId ? Pengiriman::find($pengirimanId) : null;
        
        $authError = $this->authorizeAction($pengiriman, true);
        if ($authError) return response()->json($authError, 403);

        try {
            $validatedData = $request->validate([
                "pengiriman_id" => "required|exists:pengiriman,id",
                "catatan" => "required|string|max:1000",
                "alasan_batal" => "required|string|max:500",
            ]);

            DB::beginTransaction();

            $newCatatan = $validatedData["alasan_batal"] . "\n [Dibatalkan pada: " . now()->format("d M Y H:i") . "]";
            $pengiriman->update(["catatan" => $newCatatan, "status" => "gagal"]);
            $this->restoreOrderDetailQty($pengiriman);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Pengiriman berhasil dibatalkan",
                "no_pengiriman" => $pengiriman->no_pengiriman,
                "pengiriman" => $pengiriman,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["success" => false, "message" => "Terjadi kesalahan: " . $e->getMessage()], 500);
        }
    }

    public function getDetailFisik($id)
    {
        try {
            $pengiriman = $this->loadPengirimanForModal($id, 'menunggu_fisik');
            return view("pages.purchasing.pengiriman.menunggu-fisik.detail", compact("pengiriman"));
        } catch (\Exception $e) {
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }

    public function getDetailVerifikasi($id)
    {
        try {
            $pengiriman = $this->loadPengirimanForModal($id, 'menunggu_verifikasi');
            return view("pages.purchasing.pengiriman.menunggu-verifikasi.detail", compact("pengiriman"));
        } catch (\Exception $e) {
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }

    public function getDetailGagal($id)
    {
        try {
            $pengiriman = $this->loadPengirimanForModal($id, 'gagal');
            
            $data = [
                "id" => $pengiriman->id,
                "no_pengiriman" => $pengiriman->no_pengiriman,
                "status" => ucfirst($pengiriman->status),
                "no_po" => $pengiriman->order->po_number ?? "-",
                "pic_purchasing" => $pengiriman->purchasing->nama ?? "-",
                "tanggal_kirim" => $pengiriman->tanggal_kirim ? Carbon::parse($pengiriman->tanggal_kirim)->format("d F Y") : "-",
                "hari_kirim" => $pengiriman->hari_kirim ?? "-",
                "total_qty" => number_format($pengiriman->total_qty_kirim ?? 0, 0, ",", ".") . " kg",
                "total_harga" => "Rp " . number_format($pengiriman->total_harga_kirim ?? 0, 0, ",", "."),
                "total_items" => $pengiriman->pengirimanDetails ? $pengiriman->pengirimanDetails->count() : 0,
                "catatan" => $pengiriman->catatan,
                "alasan_gagal" => $pengiriman->alasan_gagal,
                "catatan_refraksi" => $pengiriman->catatan_refraksi,
                "details" => $pengiriman->pengirimanDetails ? $pengiriman->pengirimanDetails->map(fn($detail) => [
                    "bahan_baku" => $detail->bahanBakuSupplier->nama ?? "-",
                    "supplier" => $detail->bahanBakuSupplier->supplier->nama ?? "-",
                    "qty_kirim" => $detail->qty_kirim,
                    "harga_satuan" => $detail->harga_satuan,
                    "total_harga" => $detail->total_harga,
                ]) : [],
            ];

            return response()->json(["success" => true, "pengiriman" => $data]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Gagal memuat detail pengiriman."], 500);
        }
    }

    /* ======================================================================
     * DETAIL BERHASIL FORMATTERS
     * ====================================================================== */

    private function buildTimelineBerhasil(Pengiriman $pengiriman): array
    {
        $timeline = [];
        if ($pengiriman->forecast) {
            $forecast = $pengiriman->forecast;
            $timeline[] = [
                "type" => "forecast", "status" => "created", "title" => "Forecast Dibuat", "description" => "Forecast {$forecast->no_forecast} telah dibuat",
                "timestamp" => $forecast->created_at, "formatted_time" => $forecast->created_at ? Carbon::parse($forecast->created_at)->format("d M Y, H:i") : "-", "icon" => "fa-plus-circle", "color" => "blue",
            ];
            if ($forecast->updated_at && $forecast->updated_at != $forecast->created_at) {
                $timeline[] = [
                    "type" => "forecast", "status" => "updated", "title" => "Forecast Diperbarui", "description" => "Forecast {$forecast->no_forecast} telah diperbarui",
                    "timestamp" => $forecast->updated_at, "formatted_time" => $forecast->updated_at ? Carbon::parse($forecast->updated_at)->format("d M Y, H:i") : "-", "icon" => "fa-edit", "color" => "yellow",
                ];
            }
            if ($forecast->status === "sukses") {
                $timeline[] = [
                    "type" => "forecast", "status" => "sukses", "title" => "Forecast Berhasil", "description" => "Forecast {$forecast->no_forecast} berhasil diproses",
                    "timestamp" => $pengiriman->created_at, "formatted_time" => $pengiriman->created_at ? Carbon::parse($pengiriman->created_at)->format("d M Y, H:i") : "-", "icon" => "fa-check-circle", "color" => "green",
                ];
            }
        }

        $timeline[] = [
            "type" => "pengiriman", "status" => "pending", "title" => "Pengiriman Dibuat", "description" => "Pengiriman {$pengiriman->no_pengiriman} telah dibuat dan menunggu verifikasi fisik",
            "timestamp" => $pengiriman->created_at, "formatted_time" => $pengiriman->created_at ? Carbon::parse($pengiriman->created_at)->format("d M Y, H:i") : "-", "icon" => "fa-box", "color" => "gray",
        ];

        $fisikVerifiedAt = null;
        if ($pengiriman->foto_tanda_terima_uploaded_at) $fisikVerifiedAt = Carbon::parse($pengiriman->foto_tanda_terima_uploaded_at);
        if ($pengiriman->bukti_foto_bongkar_uploaded_at) {
            $buktiFotoAt = Carbon::parse($pengiriman->bukti_foto_bongkar_uploaded_at);
            if (!$fisikVerifiedAt || $buktiFotoAt->lt($fisikVerifiedAt)) $fisikVerifiedAt = $buktiFotoAt;
        }

        if (!$fisikVerifiedAt && $pengiriman->created_at && $pengiriman->updated_at && $pengiriman->created_at != $pengiriman->updated_at) {
            $midTimestamp = (Carbon::parse($pengiriman->created_at)->timestamp + Carbon::parse($pengiriman->updated_at)->timestamp) / 2;
            $fisikVerifiedAt = Carbon::createFromTimestamp($midTimestamp);
        }

        if ($fisikVerifiedAt) {
            $timeline[] = [
                "type" => "pengiriman", "status" => "fisik_diterima", "title" => "Fisik Diterima", "description" => "Barang telah diterima secara fisik dan dokumen telah diverifikasi oleh Direktur/Manager Purchasing",
                "timestamp" => $fisikVerifiedAt, "formatted_time" => $fisikVerifiedAt->format("d M Y, H:i"), "icon" => "fa-box-check", "color" => "purple",
            ];
        }

        if ($pengiriman->updated_at && $pengiriman->updated_at != $pengiriman->created_at) {
            $timeline[] = [
                "type" => "pengiriman", "status" => "menunggu_verifikasi", "title" => "Menunggu Verifikasi Dokumen", "description" => "Pengiriman {$pengiriman->no_pengiriman} menunggu verifikasi dokumen oleh Accounting",
                "timestamp" => $pengiriman->updated_at, "formatted_time" => $pengiriman->updated_at ? Carbon::parse($pengiriman->updated_at)->format("d M Y, H:i") : "-", "icon" => "fa-file-invoice", "color" => "yellow",
            ];
        }

        $timeline[] = [
            "type" => "pengiriman", "status" => "berhasil", "title" => "Pengiriman Berhasil", "description" => "Pengiriman {$pengiriman->no_pengiriman} telah berhasil diverifikasi",
            "timestamp" => $pengiriman->updated_at, "formatted_time" => $pengiriman->updated_at ? Carbon::parse($pengiriman->updated_at)->format("d M Y, H:i") : "-", "icon" => "fa-check-double", "color" => "green",
        ];

        usort($timeline, fn($a, $b) => $a["timestamp"] <=> $b["timestamp"]);
        return $timeline;
    }

    private function formatResponseBerhasil(Pengiriman $pengiriman, array $timeline): array
    {
        $data = [
            "id" => $pengiriman->id,
            "no_pengiriman" => $pengiriman->no_pengiriman,
            "status" => ucfirst($pengiriman->status),
            "no_po" => $pengiriman->order->po_number ?? "-",
            "pic_purchasing" => $pengiriman->purchasing->nama ?? "-",
            "tanggal_kirim" => $pengiriman->tanggal_kirim ? Carbon::parse($pengiriman->tanggal_kirim)->format("d F Y") : "-",
            "hari_kirim" => $pengiriman->hari_kirim ?? "-",
            "total_qty" => number_format($pengiriman->total_qty_kirim ?? 0, 0, ",", ".") . " kg",
            "total_harga" => "Rp " . number_format($pengiriman->total_harga_kirim ?? 0, 0, ",", "."),
            "total_items" => $pengiriman->pengirimanDetails ? $pengiriman->pengirimanDetails->count() : 0,
            "catatan" => $pengiriman->catatan,
            "rating" => $pengiriman->rating,
            "ulasan" => $pengiriman->ulasan,
            "bukti_foto_bongkar" => $pengiriman->bukti_foto_bongkar_array ?? [],
            "bukti_foto_urls" => $pengiriman->bukti_foto_bongkar_url ?? [],
            "bukti_foto_bongkar_uploaded_at" => $pengiriman->bukti_foto_bongkar_uploaded_at ? Carbon::parse($pengiriman->bukti_foto_bongkar_uploaded_at)->format("d M Y, H:i") . " WIB" : null,
            "foto_tanda_terima" => $pengiriman->foto_tanda_terima,
            "foto_tanda_terima_url" => $pengiriman->foto_tanda_terima ? asset("storage/pengiriman/tanda-terima/" . $pengiriman->foto_tanda_terima) : null,
            "foto_tanda_terima_uploaded_at" => $pengiriman->foto_tanda_terima_uploaded_at ? Carbon::parse($pengiriman->foto_tanda_terima_uploaded_at)->format("d M Y, H:i") . " WIB" : null,
            "timeline" => $timeline,
            "details" => $pengiriman->pengirimanDetails ? $pengiriman->pengirimanDetails->map(fn($detail) => [
                "bahan_baku" => $detail->bahanBakuSupplier->nama ?? "-",
                "supplier" => $detail->bahanBakuSupplier->supplier->nama ?? "-",
                "qty_kirim" => $detail->qty_kirim,
                "harga_satuan" => $detail->harga_satuan,
                "total_harga" => $detail->total_harga,
            ]) : [],
        ];

        // SISI BELI
        if ($pengiriman->approvalPembayaran) {
            $approval = $pengiriman->approvalPembayaran;
            if ($approval->subtotal > 0) $totalHargaBeli = (float) $approval->subtotal;
            elseif ($approval->amount_after_refraksi > 0) $totalHargaBeli = (float) $approval->amount_after_refraksi;
            else {
                $qtyFallback = $approval->qty_after_refraksi > 0 ? (float) $approval->qty_after_refraksi : ($approval->qty_before_refraksi > 0 ? (float) $approval->qty_before_refraksi : (float) ($pengiriman->total_qty_kirim ?? 0));
                $hargaFallback = ($pengiriman->total_qty_kirim > 0) ? (float) $pengiriman->total_harga_kirim / (float) $pengiriman->total_qty_kirim : 0;
                $totalHargaBeli = $qtyFallback * $hargaFallback;
            }

            $qtyAfterRefraksi = $approval->qty_after_refraksi > 0 ? (float) $approval->qty_after_refraksi : ($approval->qty_before_refraksi > 0 ? (float) $approval->qty_before_refraksi : (float) ($pengiriman->total_qty_kirim ?? 1));
            
            $data["approval_pembayaran"] = [
                "refraksi_type" => $approval->refraksi_type, "refraksi_value" => $approval->refraksi_value, "refraksi_amount" => $approval->refraksi_amount,
                "qty_before_refraksi" => $approval->qty_before_refraksi, "qty_after_refraksi" => $approval->qty_after_refraksi,
                "amount_before_refraksi" => $approval->amount_before_refraksi, "amount_after_refraksi" => $approval->amount_after_refraksi,
                "additional_expenses_total" => $approval->additional_expenses_total ?? 0,
                "expenses" => $approval->expenses ? $approval->expenses->map(fn($e) => ["type" => $e->type, "amount" => (float) $e->amount])->toArray() : [],
            ];
            if ($approval->bukti_pembayaran) $data["bukti_pembayaran_url"] = asset("storage/" . $approval->bukti_pembayaran);
            
            $data["total_harga_beli"] = $totalHargaBeli;
            $data["qty_after_refraksi"] = $qtyAfterRefraksi;
            $data["harga_beli_per_kg"] = $qtyAfterRefraksi > 0 ? $totalHargaBeli / $qtyAfterRefraksi : 0;
        }

        // SISI JUAL
        $hargaJualPerKg = 0; $totalHargaJual = 0; $qtyJual = 0; $source = "";
        if ($pengiriman->invoicePenagihan) {
            $invoice = $pengiriman->invoicePenagihan;
            if ((float) $invoice->subtotal > 0) $totalHargaJual = (float) $invoice->subtotal;
            elseif ((float) $invoice->amount_after_refraksi > 0) $totalHargaJual = (float) $invoice->amount_after_refraksi;

            $qtyJual = (float) $invoice->qty_after_refraksi > 0 ? (float) $invoice->qty_after_refraksi : ((float) $invoice->qty_before_refraksi > 0 ? (float) $invoice->qty_before_refraksi : (float) ($pengiriman->total_qty_kirim ?? 1));
            $hargaJualPerKg = $qtyJual > 0 ? $totalHargaJual / $qtyJual : 0;
            $source = "Invoice Penagihan";

            $data["invoice_penagihan"] = [
                "additional_expenses_total" => $invoice->additional_expenses_total ?? 0,
                "expenses" => $invoice->expenses ? $invoice->expenses->map(fn($e) => ["type" => $e->type, "amount" => (float) $e->amount])->toArray() : [],
            ];
        } elseif ($pengiriman->pengirimanDetails && $pengiriman->pengirimanDetails->count() > 0) {
            foreach ($pengiriman->pengirimanDetails as $detail) {
                if ($detail->orderDetail && $detail->orderDetail->harga_jual > 0) {
                    $totalHargaJual += (float) $detail->qty_kirim * (float) $detail->orderDetail->harga_jual;
                    $qtyJual += (float) $detail->qty_kirim;
                }
            }
            $hargaJualPerKg = $qtyJual > 0 ? $totalHargaJual / $qtyJual : 0;
            $source = "Purchase Order";
        }

        if ($hargaJualPerKg > 0 || $totalHargaJual > 0) {
            $data["harga_jual_per_kg"] = $hargaJualPerKg;
            $data["total_harga_jual"] = $totalHargaJual;
            $data["qty_jual"] = $qtyJual;
            $data["harga_jual_source"] = $source;

            if (isset($data["total_harga_beli"]) && $data["total_harga_beli"] > 0) {
                $margin = $totalHargaJual - $data["total_harga_beli"];
                $data["margin"] = $margin;
                $data["margin_percentage"] = $totalHargaJual > 0 ? ($margin / $totalHargaJual) * 100 : 0;
            }
        }
        $data["catatan_refraksi"] = $pengiriman->catatan_refraksi;

        return $data;
    }

    public function getDetailBerhasil($id)
    {
        try {
            $pengiriman = $this->loadPengirimanForModal($id, 'berhasil');
            $timeline = $this->buildTimelineBerhasil($pengiriman);
            $data = $this->formatResponseBerhasil($pengiriman, $timeline);
            return response()->json(["success" => true, "pengiriman" => $data]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Gagal memuat detail pengiriman: " . $e->getMessage()], 500);
        }
    }

    /* ======================================================================
     * UPDATE ACTIONS
     * ====================================================================== */

    public function updateCatatan(Request $request, $id)
    {
        try {
            $request->validate(["catatan" => "nullable|string|max:1000"]);
            $pengiriman = Pengiriman::findOrFail($id);
            $pengiriman->catatan = $request->catatan;
            $pengiriman->save();
            return response()->json(["success" => true, "message" => "Catatan berhasil diperbarui", "catatan" => $pengiriman->catatan]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Gagal memperbarui catatan."], 500);
        }
    }

    public function verifikasiFisik(Request $request, $id)
    {
        try {
            $pengiriman = Pengiriman::findOrFail($id);
            if ($pengiriman->status !== 'menunggu_fisik') return response()->json(['success' => false, 'message' => 'Pengiriman tidak dalam status menunggu fisik'], 400);
            
            $authError = $this->authorizeAction(null, false);
            if ($authError && !in_array(Auth::user()->role, ['direktur', 'manager_purchasing'])) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            
            DB::beginTransaction();
            $pengiriman->status = 'menunggu_verifikasi';
            $pengiriman->save();
            $this->reduceOrderDetailQty($pengiriman);
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Pengiriman berhasil diverifikasi fisik dan menunggu verifikasi dokumen', 'pengiriman' => $pengiriman]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memverifikasi fisik.'], 500);
        }
    }

    public function verifikasiPengiriman(Request $request, $id)
    {
        try {
            $pengiriman = Pengiriman::with('details.orderDetail')->findOrFail($id);
            if ($pengiriman->status !== 'menunggu_verifikasi') return response()->json(['success' => false, 'message' => 'Pengiriman tidak dalam status menunggu verifikasi'], 400);
            
            $authError = $this->authorizeAction(null, false);
            if ($authError && !in_array(Auth::user()->role, ['direktur', 'manager_purchasing'])) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            
            DB::beginTransaction();
            $pengiriman->status = 'berhasil';
            $pengiriman->save();
            $this->reduceOrderDetailQty($pengiriman);
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Pengiriman berhasil diverifikasi', 'pengiriman' => $pengiriman]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memverifikasi pengiriman.'], 500);
        }
    }

    public function getVerifikasiModal($id)
    {
        try {
            $pengiriman = $this->loadPengirimanForModal($id, 'menunggu_verifikasi');
            return view('pages.purchasing.pengiriman.menunggu-verifikasi.verifikasi', compact('pengiriman'));
        } catch (\Exception $e) {
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }

    public function getRevisiModal($id)
    {
        try {
            $pengiriman = $this->loadPengirimanForModal($id, 'menunggu_verifikasi');
            return view('pages.purchasing.pengiriman.menunggu-verifikasi.revisi', compact('pengiriman'));
        } catch (\Exception $e) {
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }

    public function uploadFotoTandaTerima(Request $request, $id)
    {
        try {
            $request->validate(['foto_tanda_terima' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240']);
            $pengiriman = Pengiriman::findOrFail($id);
            
            $authError = $this->authorizeAction($pengiriman, true);
            if ($authError) return response()->json($authError, 403);
            
            if ($pengiriman->foto_tanda_terima && Storage::disk('public')->exists('pengiriman/tanda-terima/' . $pengiriman->foto_tanda_terima)) {
                Storage::disk('public')->delete('pengiriman/tanda-terima/' . $pengiriman->foto_tanda_terima);
            }
            
            $file = $request->file('foto_tanda_terima');
            $fileName = 'tanda_terima_' . $pengiriman->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('pengiriman/tanda-terima', $fileName, 'public');
            
            $pengiriman->foto_tanda_terima = $fileName;
            $pengiriman->foto_tanda_terima_uploaded_at = now();
            $pengiriman->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Foto tanda terima berhasil diupload',
                'file_name' => $fileName,
                'uploaded_at' => $pengiriman->foto_tanda_terima_uploaded_at->format('d M Y, H:i') . ' WIB'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal upload foto.'], 500);
        }
    }

    public function revisiPengiriman(Request $request, $id)
    {
        try {
            $request->validate(['catatan' => 'required|string|min:10|max:1000']);
            $pengiriman = Pengiriman::findOrFail($id);
            
            $authError = $this->authorizeAction(null, false);
            if ($authError && !in_array(Auth::user()->role, ['direktur', 'manager_purchasing'])) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            
            if (!in_array($pengiriman->status, ['menunggu_verifikasi', 'berhasil'])) {
                return response()->json(['success' => false, 'message' => 'Status tidak valid untuk revisi'], 400);
            }
            
            $revisiCatatan = "[REVISI dari status {$pengiriman->status} oleh " . Auth::user()->nama . " pada " . now()->format('d M Y, H:i') . "]\n" . $request->catatan;
            $pengiriman->catatan = $pengiriman->catatan ? $pengiriman->catatan . "\n\n" . $revisiCatatan : $revisiCatatan;
            
            DB::beginTransaction();
            $this->restoreOrderDetailQty($pengiriman);
            $pengiriman->status = 'pending';
            $pengiriman->save();
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Pengiriman berhasil direvisi ke status pending', 'pengiriman' => $pengiriman]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal merevisi pengiriman.'], 500);
        }
    }

    public function deletePengirimanGagal($id)
    {
        try {
            $pengiriman = Pengiriman::with(['forecast', 'pengirimanDetails'])->findOrFail($id);
            
            $authError = $this->authorizeAction($pengiriman, true);
            if ($authError) return response()->json($authError, 403);

            if ($pengiriman->status !== 'gagal') return response()->json(['success' => false, 'message' => 'Hanya status gagal yang dapat dihapus'], 400);

            DB::beginTransaction();
            PengirimanDetail::where('pengiriman_id', $pengiriman->id)->delete();
            $pengiriman->delete();

            if ($pengiriman->forecast_id) {
                $forecast = Forecast::find($pengiriman->forecast_id);
                if ($forecast) {
                    ForecastDetail::where('forecast_id', $forecast->id)->delete();
                    $forecast->delete();
                }
            }
            DB::commit();

            return response()->json(['success' => true, 'message' => "Pengiriman berhasil dihapus"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus pengiriman.'], 500);
        }
    }

    public function deleteBuktiFoto(Request $request, $id)
    {
        try {
            $request->validate(['filename' => 'required|string']);
            $pengiriman = Pengiriman::findOrFail($id);
            $filename = $request->input('filename');
            $photos = $pengiriman->bukti_foto_bongkar_array ?? [];

            if (!in_array($filename, $photos)) return response()->json(['success' => false, 'message' => 'Foto tidak ditemukan'], 404);

            $filePath = "pengiriman/bukti/" . $filename;
            if (Storage::disk("public")->exists($filePath)) Storage::disk("public")->delete($filePath);

            $photos = array_values(array_filter($photos, fn($photo) => $photo !== $filename));
            $pengiriman->bukti_foto_bongkar = !empty($photos) ? $photos : null;
            if (empty($photos)) $pengiriman->bukti_foto_bongkar_uploaded_at = null;
            $pengiriman->save();

            return response()->json(['success' => true, 'message' => 'Foto berhasil dihapus', 'remaining_photos' => count($photos)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus foto.'], 500);
        }
    }
}