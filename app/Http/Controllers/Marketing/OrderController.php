<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\BahanBakuSupplier;
use App\Models\Supplier;
use App\Models\User;
use App\Models\RiwayatHargaKlien;
use App\Models\RiwayatHargaBahanBaku;
use App\Models\OrderDetail;
use App\Services\AuthFallbackService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view("pages.marketing.orders.index");
    }

    /**
     * Return top suppliers for a given client material (limit 5), ordered by supplier price asc.
     * Used by the order create page to auto-populate supplier rows.
     */
    public function getSuppliersForMaterial($materialId)
    {
        $material = BahanBakuKlien::findOrFail($materialId);

        $suppliers = BahanBakuSupplier::with(["supplier.picPurchasing"])
            ->where("nama", "like", "%" . $material->nama . "%")
            ->whereNotNull("harga_per_satuan")
            ->orderBy("harga_per_satuan", "asc")
            ->limit(5)
            ->get();

        // Mirror Penawaran's supplier option shape but include supplier_table_id for convenience
        $result = $suppliers->map(function ($s) {
            return [
                // canonical keys:
                // - bahan_baku_supplier_id => id of the supplier-material (BahanBakuSupplier)
                // - supplier_id => id from suppliers table (for selects and validation)
                "supplier_name" => $s->supplier ? $s->supplier->nama : null,
                "pic_name" =>
                    $s->supplier && $s->supplier->picPurchasing
                        ? $s->supplier->picPurchasing->nama
                        : null,
                "bahan_baku_supplier_id" => $s->id,
                "supplier_id" => $s->supplier ? $s->supplier->id : null,
                "price" => (float) $s->harga_per_satuan,
                "satuan" => $s->satuan,
                "stok" => (float) $s->stok,
            ];
        });

        return response()->json(["data" => $result]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("pages.marketing.orders.create-livewire");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "klien_id" => "required|exists:kliens,id",
            "tanggal_order" => "required|date",
            "priority" => "required|in:rendah,normal,tinggi,mendesak",
            "po_number" => "nullable|string|max:50",
            "po_start_date" => "nullable|date",
            "po_end_date" => "nullable|date|after_or_equal:po_start_date",
            "catatan" => "nullable|string",
            "order_details" => "required|array|min:1",
            "order_details.*.bahan_baku_klien_id" =>
                "required|exists:bahan_baku_klien,id",
            "order_details.*.qty" => "required|numeric|min:0.01",
            "order_details.*.satuan" => "required|string|max:20",
            "order_details.*.harga_jual" => "required|numeric|min:0",
            "order_details.*.spesifikasi_khusus" => "nullable|string",
            "order_details.*.catatan" => "nullable|string",
            "order_details.*.recommended_supplier_id" =>
                "nullable|exists:suppliers,id",
            "order_details.*.recommended_bahan_baku_supplier_id" =>
                "nullable|exists:bahan_baku_supplier,id",
        ]);

        $order = Order::create([
            "klien_id" => $request->klien_id,
            "created_by" => AuthFallbackService::id(),
            "tanggal_order" => $request->tanggal_order,
            "priority" => $request->priority,
            "po_number" => $request->po_number,
            "po_start_date" => $request->po_start_date,
            "po_end_date" => $request->po_end_date,
            "catatan" => $request->catatan,
        ]);

        foreach ($request->order_details as $detail) {
            $detailModel = $order->orderDetails()->create([
                "bahan_baku_klien_id" => $detail["bahan_baku_klien_id"],
                "qty" => $detail["qty"],
                "satuan" => $detail["satuan"],
                "harga_jual" => $detail["harga_jual"],
                "total_harga" => $detail["qty"] * $detail["harga_jual"],
                "status" => "menunggu",
                "spesifikasi_khusus" => $detail["spesifikasi_khusus"] ?? null,
                "catatan" => $detail["catatan"] ?? null,
            ]);

            $detailModel->populateSupplierOptions();

            $this->applyRecommendedSupplierFromPayload($detailModel, $detail);
        }

        // Calculate totals
        $order->calculateTotals();

        return redirect()
            ->route("orders.index")
            ->with("success", "Order berhasil dibuat.");
    }

    private function applyRecommendedSupplierFromPayload(
        OrderDetail $detail,
        array $payload,
    ): void {
        $bahanBakuSupplierId =
            $payload["recommended_bahan_baku_supplier_id"] ?? null;
        $supplierId = $payload["recommended_supplier_id"] ?? null;

        if (!$bahanBakuSupplierId && !$supplierId) {
            $detail->updateSupplierSummary();
            return;
        }

        $selectedSupplier = $detail
            ->orderSuppliers()
            ->when($bahanBakuSupplierId, function ($query) use (
                $bahanBakuSupplierId,
            ) {
                return $query->where(
                    "bahan_baku_supplier_id",
                    $bahanBakuSupplierId,
                );
            })
            ->when(!$bahanBakuSupplierId && $supplierId, function ($query) use (
                $supplierId,
            ) {
                return $query->where("supplier_id", $supplierId);
            })
            ->first();

        if ($selectedSupplier) {
            $detail->orderSuppliers()->update(["is_recommended" => false]);

            $selectedSupplier->is_recommended = true;
            $selectedSupplier->price_rank = 1;
            $selectedSupplier->save();
        }

        $detail->updateSupplierSummary();
    }

    /**
     * Return an authenticated user id or a safe fallback user id for dev/test flows.
     * This is temporary until a full auth system is implemented.
     *
     * @return int|null
     */
    private function getFallbackUserId()
    {
        $user = auth()->user();
        if ($user) {
            return $user->id;
        }

        // Prefer a dedicated system user if present
        $fallback = User::where("email", "system@local")->first();
        if ($fallback) {
            return $fallback->id;
        }

        // Otherwise return the first existing user in the DB
        $first = User::first();
        if ($first) {
            return $first->id;
        }

        // As a last resort, create a temporary system user in non-production
        try {
            if (!app()->environment("production")) {
                $created = User::create([
                    "name" => "System (dev)",
                    "email" => "system@local",
                    "password" => bcrypt(bin2hex(random_bytes(8))),
                ]);

                return $created->id;
            }
        } catch (\Exception $e) {
            // ignore and fall through to null
        }

        return null;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with([
            "klien",
            "creator",
            "orderDetails.bahanBakuKlien",
            "orderDetails.orderSuppliers.supplier.picPurchasing",
            "orderDetails.orderSuppliers.bahanBakuSupplier",
        ])->findOrFail($id);

        // Prepare chart data for price analysis
        $chartsData = $this->prepareOrderChartsData($order);

        return view(
            "pages.marketing.orders.show",
            compact("order", "chartsData"),
        );
    }

    /**
     * Prepare charts data for order price analysis
     */
    private function prepareOrderChartsData(Order $order)
    {
        $chartsData = [];

        foreach ($order->orderDetails as $detail) {
            $material = $detail->bahanBakuKlien;
            if (!$material) {
                continue;
            }

            // Get client price history for this material
            $clientPriceHistory = RiwayatHargaKlien::where(
                "bahan_baku_klien_id",
                $material->id,
            )
                ->orderBy("tanggal_perubahan", "asc")
                ->get()
                ->map(function ($riwayat) {
                    return [
                        "tanggal" => $riwayat->tanggal_perubahan,
                        "harga" => (float) $riwayat->harga_approved_baru,
                        "formatted_tanggal" => $riwayat->tanggal_perubahan->format(
                            "d M",
                        ),
                    ];
                })
                ->toArray();

            // Prepare supplier options with their price history
            $supplierOptions = [];
            foreach ($detail->orderSuppliers as $orderSupplier) {
                $supplier = $orderSupplier->supplier;
                if (!$supplier) {
                    continue;
                }

                // Get price history for this supplier's material
                $supplierPriceHistory = RiwayatHargaBahanBaku::whereHas(
                    "bahanBakuSupplier",
                    function ($query) use ($supplier, $material) {
                        $query
                            ->where("supplier_id", $supplier->id)
                            ->where(
                                "nama",
                                "like",
                                "%" . $material->nama . "%",
                            );
                    },
                )
                    ->orderBy("tanggal_perubahan", "asc")
                    ->get()
                    ->map(function ($riwayat) {
                        return [
                            "tanggal" => $riwayat->tanggal_perubahan,
                            "harga" => (float) $riwayat->harga_baru,
                            "formatted_tanggal" => $riwayat->tanggal_perubahan->format(
                                "d M",
                            ),
                        ];
                    })
                    ->toArray();

                // Fallback: if no price history, use current price from bahan_baku_supplier
                if (
                    empty($supplierPriceHistory) &&
                    $orderSupplier->harga_supplier
                ) {
                    $supplierPriceHistory = [
                        [
                            "tanggal" => now(),
                            "harga" => (float) $orderSupplier->harga_supplier,
                            "formatted_tanggal" => now()->format("d M"),
                            "is_fallback" => true,
                        ],
                    ];
                }

                $supplierOptions[] = [
                    "supplier_name" => $supplier->nama,
                    "pic_name" => $supplier->picPurchasing->nama ?? null,
                    "current_price" => (float) $orderSupplier->harga_supplier,
                    "price_history" => $supplierPriceHistory,
                    "is_selected" => $orderSupplier->is_recommended ?? false,
                ];
            }

            // Calculate margin percentage
            $bestSupplier = $detail->orderSuppliers
                ->sortBy("price_rank")
                ->first();
            $marginPercent = 0;
            if ($bestSupplier && $bestSupplier->harga_supplier > 0) {
                $marginPercent =
                    (($detail->harga_jual - $bestSupplier->harga_supplier) /
                        $detail->harga_jual) *
                    100;
            }

            $chartsData[] = [
                "nama" => $material->nama,
                "order_quantity" => (float) $detail->qty,
                "order_price" => (float) $detail->harga_jual,
                "satuan" => $detail->satuan,
                "client_price_history" => $clientPriceHistory,
                "supplier_options" => $supplierOptions,
                "margin_percent" => $marginPercent,
            ];
        }

        return $chartsData;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $order = Order::with([
            "klien",
            "orderDetails.bahanBakuKlien",
            "orderDetails.orderSuppliers.supplier.picPurchasing",
            "orderDetails.orderSuppliers.bahanBakuSupplier",
        ])->findOrFail($id);

        if ($order->status !== "draft") {
            return redirect()
                ->route("orders.show", $order->id)
                ->with(
                    "error",
                    "Hanya order dengan status draft yang dapat diedit.",
                );
        }

        return view("pages.marketing.orders.edit-livewire", compact("order"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== "draft") {
            return redirect()
                ->route("orders.show", $order->id)
                ->with(
                    "error",
                    "Hanya order dengan status draft yang dapat diupdate.",
                );
        }

        $request->validate([
            "klien_id" => "required|exists:kliens,id",
            "tanggal_order" => "required|date",
            "priority" => "required|in:rendah,normal,tinggi,mendesak",
            "po_number" => "nullable|string|max:50",
            "po_start_date" => "nullable|date",
            "po_end_date" => "nullable|date|after_or_equal:po_start_date",
            "catatan" => "nullable|string",
            "order_details" => "required|array|min:1",
            "order_details.*.bahan_baku_klien_id" =>
                "required|exists:bahan_baku_klien,id",
            "order_details.*.qty" => "required|numeric|min:0.01",
            "order_details.*.satuan" => "required|string|max:20",
            "order_details.*.harga_jual" => "required|numeric|min:0",
            "order_details.*.spesifikasi_khusus" => "nullable|string",
            "order_details.*.catatan" => "nullable|string",
            "order_details.*.recommended_supplier_id" =>
                "nullable|exists:suppliers,id",
            "order_details.*.recommended_bahan_baku_supplier_id" =>
                "nullable|exists:bahan_baku_supplier,id",
        ]);

        $order->update([
            "klien_id" => $request->klien_id,
            "tanggal_order" => $request->tanggal_order,
            "priority" => $request->priority,
            "po_number" => $request->po_number,
            "po_start_date" => $request->po_start_date,
            "po_end_date" => $request->po_end_date,
            "catatan" => $request->catatan,
        ]);

        // Delete existing details and create new ones
        $order->orderDetails()->delete();

        foreach ($request->order_details as $detail) {
            $detailModel = $order->orderDetails()->create([
                "bahan_baku_klien_id" => $detail["bahan_baku_klien_id"],
                "qty" => $detail["qty"],
                "satuan" => $detail["satuan"],
                "harga_jual" => $detail["harga_jual"],
                "total_harga" => $detail["qty"] * $detail["harga_jual"],
                "status" => "menunggu",
                "spesifikasi_khusus" => $detail["spesifikasi_khusus"] ?? null,
                "catatan" => $detail["catatan"] ?? null,
            ]);

            $detailModel->populateSupplierOptions();

            $this->applyRecommendedSupplierFromPayload($detailModel, $detail);
        }

        // Recalculate totals
        $order->calculateTotals();

        return redirect()
            ->route("orders.show", $order->id)
            ->with("success", "Order berhasil diupdate.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== "draft") {
            return redirect()
                ->route("orders.index")
                ->with(
                    "error",
                    "Hanya order dengan status draft yang dapat dihapus.",
                );
        }

        $order->delete();

        return redirect()
            ->route("orders.index")
            ->with("success", "Order berhasil dihapus.");
    }

    /**
     * Check if current user can manage the order.
     * Only order creator, marketing users, or direktur can manage orders.
     */
    private function canManageOrder(Order $order): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $isOrderCreator = $order->created_by === $user->id;
        $isMarketing = $user->isMarketing();
        $isDirektur = $user->isDirektur();

        return $isOrderCreator || $isMarketing || $isDirektur;
    }

    /**
     * Confirm an order (change status from draft to dikonfirmasi)
     */
    public function confirm(string $id)
    {
        $order = Order::findOrFail($id);

        if (!$this->canManageOrder($order)) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Anda tidak memiliki akses untuk mengkonfirmasi order ini.",
                );
        }

        if ($order->status !== "draft") {
            return redirect()
                ->back()
                ->with("error", "Order sudah tidak dalam status draft.");
        }

        $order->confirm();

        return redirect()
            ->back()
            ->with("success", "Order berhasil dikonfirmasi.");
    }

    /**
     * Start processing an order (change status to diproses)
     */
    public function startProcessing(string $id)
    {
        $order = Order::findOrFail($id);

        if (!$this->canManageOrder($order)) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Anda tidak memiliki akses untuk memproses order ini.",
                );
        }

        if ($order->status !== "dikonfirmasi") {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Order harus dalam status dikonfirmasi untuk dapat diproses.",
                );
        }

        $order->startProcessing();

        return redirect()->back()->with("success", "Order mulai diproses.");
    }

    /**
     * Complete an order (change status to selesai)
     */
    public function complete(string $id)
    {
        $order = Order::findOrFail($id);

        if (!$this->canManageOrder($order)) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Anda tidak memiliki akses untuk menyelesaikan order ini.",
                );
        }

        if ($order->status !== "diproses") {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Order harus dalam status diproses untuk dapat diselesaikan.",
                );
        }

        $order->complete();

        return redirect()
            ->back()
            ->with("success", "Order berhasil diselesaikan.");
    }

    /**
     * Cancel an order
     */
    public function cancel(string $id)
    {
        $order = Order::findOrFail($id);

        if (!$this->canManageOrder($order)) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Anda tidak memiliki akses untuk membatalkan order ini.",
                );
        }

        if (in_array($order->status, ["selesai", "dibatalkan"])) {
            return redirect()
                ->back()
                ->with("error", "Order sudah selesai atau sudah dibatalkan.");
        }

        $order->cancel();

        return redirect()
            ->back()
            ->with("success", "Order berhasil dibatalkan.");
    }

    /**
     * Send consultation request to Direktur about order fulfillment
     */
    public function consultDirektur(Request $request, string $id)
    {
        $order = Order::with(["creator", "klien", "orderDetails"])->findOrFail(
            $id,
        );

        $user = Auth::user();

        // Authorization: Only order creator or marketing users can request consultation
        // (Direktur don't consult themselves, so exclude them here)
        $isOrderCreator = $order->created_by === $user->id;
        $isMarketing = $user->isMarketing();

        if (!$isOrderCreator && !$isMarketing) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Anda tidak memiliki akses untuk mengajukan konsultasi order ini.",
                );
        }

        // Only allow consultation for orders in 'diproses' status
        if ($order->status !== "diproses") {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Konsultasi hanya dapat dilakukan untuk order yang sedang diproses.",
                );
        }

        $note = $request->input("catatan", null);

        // Send notification to all Direktur
        $notificationCount = NotificationService::notifyDirekturOrderConsultation(
            $order,
            $user,
            $note,
        );

        if ($notificationCount > 0) {
            return redirect()
                ->back()
                ->with(
                    "success",
                    "Konsultasi berhasil dikirim ke Direktur ({$notificationCount} notifikasi terkirim).",
                );
        }

        return redirect()
            ->back()
            ->with(
                "error",
                "Gagal mengirim konsultasi. Tidak ada Direktur aktif ditemukan.",
            );
    }
}
