<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\Marketing\KlienController;
use App\Http\Controllers\Marketing\OrderController;
use App\Http\Controllers\Direktur\PengelolaanAkunController;
use App\Http\Controllers\Purchasing\SupplierController;
use App\Http\Controllers\Purchasing\ForecastingController;
use App\Http\Controllers\Purchasing\PengirimanController;
use App\Http\Controllers\Laporan\PurchaseOrderController as LaporanPOController;
use App\Http\Controllers\Laporan\OmsetController as LaporanOmsetController;
use App\Http\Controllers\Laporan\PengirimanController as LaporanPengirimanController;
use App\Http\Controllers\Laporan\PenagihanController as LaporanPenagihanController;
use App\Http\Controllers\Laporan\PembayaranController as LaporanPembayaranController;

// Authentication routes
Route::middleware("guest")->group(function () {
    Route::get("/", [AuthController::class, "showLogin"])->name("login");
    Route::get("/login", [AuthController::class, "showLogin"]);
    Route::post("/login", [AuthController::class, "login"])->name(
        "login.attempt",
    );
});
Route::post("/logout", [AuthController::class, "logout"])
    ->middleware("auth")
    ->name("logout");
// Protected routes - require authentication
Route::middleware(["auth"])->group(function () {
    // Dashboard - accessible by all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/purchase-order', [LaporanPOController::class, 'index'])->name('po');
            Route::post('/purchase-order/export', [LaporanPOController::class, 'export'])->name('po.export');
            Route::post('/purchase-order/outstanding/pdf', [LaporanPOController::class, 'exportOutstandingPdf'])->name('po.outstanding.pdf');
            Route::post('/purchase-order/client/pdf', [LaporanPOController::class, 'exportClientPdf'])->name('po.client.pdf');
            Route::get('/purchase-order/order-winner/details', [LaporanPOController::class, 'orderWinnerDetails'])->name('po.orderWinnerDetails');
            Route::post('/purchase-order/order-winner/pdf', [LaporanPOController::class, 'exportOrderWinnerPdf'])->name('po.orderWinnerPDF');
            Route::post('/purchase-order/trend/pdf', [LaporanPOController::class, 'exportTrendPdf'])->name('po.trend.pdf');
            Route::post('/purchase-order/priority/pdf', [LaporanPOController::class, 'exportPriorityPdf'])->name('po.priority.pdf');
            Route::post('/purchase-order/status/pdf', [LaporanPOController::class, 'exportStatusPdf'])->name('po.status.pdf');

            Route::get('/omset', [LaporanOmsetController::class, 'index'])->name('omset');
            Route::post('/omset/export', [LaporanOmsetController::class, 'export'])->name('omset.export');
            Route::post('/omset/set-target', [LaporanOmsetController::class, 'setTarget'])->name('omset.setTarget');
            Route::post('/omset/save-omset-manual', [LaporanOmsetController::class, 'saveOmsetManual'])->name('omset.saveOmsetManual');
            Route::get('/omset/target-by-year', [LaporanOmsetController::class, 'getTargetByYear'])->name('omset.getTargetByYear');
            Route::get('/omset/available-years', [LaporanOmsetController::class, 'getAvailableYears'])->name('omset.getAvailableYears');
            Route::get('/omset/marketing-details', [LaporanOmsetController::class, 'getMarketingDetails'])->name('omset.marketingDetails');
            Route::post('/omset/marketing-pdf', [LaporanOmsetController::class, 'exportMarketingPDF'])->name('omset.marketingPDF');
            Route::get('/omset/procurement-details', [LaporanOmsetController::class, 'getProcurementDetails'])->name('omset.procurementDetails');
            Route::post('/omset/procurement-pdf', [LaporanOmsetController::class, 'exportProcurementPDF'])->name('omset.procurementPDF');

            Route::get('/pengiriman', [LaporanPengirimanController::class, 'index'])->name('pengiriman');
            Route::match(['GET', 'POST'], '/pengiriman/export', [LaporanPengirimanController::class, 'export'])->name('pengiriman.export');
            Route::get('/pengiriman/pie-chart-details', [LaporanPengirimanController::class, 'getPieChartDetails'])->name('pengiriman.pieChartDetails');
            Route::get('/pengiriman/pie-chart-pdf', [LaporanPengirimanController::class, 'exportPieChartPDF'])->name('pengiriman.pieChartPDF');

            Route::get('/pembayaran', [LaporanPembayaranController::class, 'index'])->name('pembayaran');
            Route::post('/pembayaran/export', [LaporanPembayaranController::class, 'export'])->name('pembayaran.export');

            Route::get('/penagihan', [LaporanPenagihanController::class, 'index'])->name('penagihan');
            Route::post('/penagihan/export', [LaporanPenagihanController::class, 'export'])->name('penagihan.export');
    });
    Route::get("/dashboard", [DashboardController::class, "index"])->name(
        "dashboard",
    );

    Route::prefix("laporan")
        ->name("laporan.")
        ->group(function () {
            Route::get("/purchase-order", [
                LaporanPOController::class,
                "index",
            ])->name("po");
            Route::post("/purchase-order/export", [
                LaporanPOController::class,
                "export",
            ])->name("po.export");

            Route::get("/omset", [
                LaporanOmsetController::class,
                "index",
            ])->name("omset");
            Route::post("/omset/export", [
                LaporanOmsetController::class,
                "export",
            ])->name("omset.export");
            Route::post("/omset/set-target", [
                LaporanOmsetController::class,
                "setTarget",
            ])->name("omset.setTarget");
            Route::get("/omset/target-by-year", [
                LaporanOmsetController::class,
                "getTargetByYear",
            ])->name("omset.getTargetByYear");
            Route::get("/omset/available-years", [
                LaporanOmsetController::class,
                "getAvailableYears",
            ])->name("omset.getAvailableYears");

            Route::get("/pengiriman", [
                LaporanPengirimanController::class,
                "index",
            ])->name("pengiriman");
            Route::match(["GET", "POST"], "/pengiriman/export", [
                LaporanPengirimanController::class,
                "export",
            ])->name("pengiriman.export");
            Route::get("/penagihan", [
                LaporanPenagihanController::class,
                "index",
            ])->name("penagihan");
            Route::post("/penagihan/export", [
                LaporanPenagihanController::class,
                "export",
            ])->name("penagihan.export");
        });

    // Pengaturan - accessible by all authenticated users
    Route::get("/pengaturan", [PengaturanController::class, "index"])->name(
        "pengaturan",
    );
    Route::put("/pengaturan", [PengaturanController::class, "update"])->name(
        "pengaturan.update",
    );

    // Notifications - accessible by all authenticated users
    Route::get("/notifications", function () {
        return view("pages.notifications");
    })->name("notifications.index");

    // Notification API routes
    Route::get("/api/notifications", function () {
        $user = auth()->user();
        if (!$user) {
            return response()->json(["error" => "Not authenticated"], 401);
        }

        $notifications = \App\Services\NotificationService::getNotifications(
            $user,
            10,
        )
            ->map(function ($notification) {
                return [
                    "id" => $notification->id,
                    "type" => $notification->type,
                    "title" => $notification->data["title"] ?? "Notifikasi",
                    "message" => $notification->data["message"] ?? "",
                    "icon" => $notification->data["icon"] ?? "bell",
                    "icon_bg" =>
                        $notification->data["icon_bg"] ?? "bg-blue-100",
                    "icon_color" =>
                        $notification->data["icon_color"] ?? "text-blue-600",
                    "url" => $notification->data["url"] ?? "#",
                    "read_at" => $notification->read_at,
                    "created_at" => $notification->created_at,
                    "time_ago" => \Carbon\Carbon::parse(
                        $notification->created_at,
                    )->diffForHumans(),
                ];
            })
            ->toArray();

        return response()->json([
            "notifications" => $notifications,
            "unread_count" => \App\Services\NotificationService::getUnreadCount(
                $user,
            ),
        ]);
    })->name("api.notifications.index");

    Route::post("/api/notifications/{id}/read", function ($id) {
        $user = auth()->user();
        if (!$user) {
            return response()->json(["error" => "Not authenticated"], 401);
        }

        \App\Services\NotificationService::markAsRead($id, $user);

        return response()->json(["success" => true]);
    })->name("api.notifications.read");

    Route::post("/api/notifications/read-all", function () {
        $user = auth()->user();
        if (!$user) {
            return response()->json(["error" => "Not authenticated"], 401);
        }

        $count = \App\Services\NotificationService::markAllAsRead($user);

        return response()->json(["success" => true, "count" => $count]);
    })->name("api.notifications.read-all");

    // Test route to create a sample notification (for debugging - remove in production)
    Route::get("/notifications/test", function () {
        $user = auth()->user();
        if (!$user) {
            return response()->json(["error" => "Not authenticated"], 401);
        }

        // Check if table exists
        $tableExists = \Illuminate\Support\Facades\Schema::hasTable(
            "notifications",
        );

        // Try to create notification
        $notificationId = \App\Services\NotificationService::send(
            $user,
            "test",
            [
                "title" => "Test Notification",
                "message" =>
                    "This is a test notification created at " .
                    now()->format("H:i:s"),
                "icon" => "bell",
                "icon_bg" => "bg-blue-100",
                "icon_color" => "text-blue-600",
                "url" => "/notifications",
            ],
        );

        // Count notifications for this user
        $count = \Illuminate\Support\Facades\DB::table("notifications")
            ->where("notifiable_type", \App\Models\User::class)
            ->where("notifiable_id", $user->id)
            ->count();

        $unreadCount = \Illuminate\Support\Facades\DB::table("notifications")
            ->where("notifiable_type", \App\Models\User::class)
            ->where("notifiable_id", $user->id)
            ->whereNull("read_at")
            ->count();

        return response()->json([
            "success" => $notificationId !== null,
            "table_exists" => $tableExists,
            "notification_id" => $notificationId,
            "user_id" => $user->id,
            "user_name" => $user->nama,
            "total_notifications" => $count,
            "unread_notifications" => $unreadCount,
        ]);
    })->name("notifications.test");

    // Test route for order fulfillment notification (95-105% threshold)
    // Usage: /notifications/test-order-fulfillment/{order_id}
    // This simulates the notification that would be sent when an order reaches 95-105% fulfillment
    Route::get("/notifications/test-order-fulfillment/{order}", function (
        \App\Models\Order $order,
    ) {
        $user = auth()->user();
        if (!$user) {
            return response()->json(["error" => "Not authenticated"], 401);
        }

        // Calculate current fulfillment
        $fulfillmentPercentage = $order->getFulfillmentPercentage();
        $shippedQty = $order->getShippedQty();
        $isNearingFulfillment = $order->isNearingFulfillment();

        // Send notification to order creator
        $notificationId = \App\Services\Notifications\OrderNotificationService::notifyNearingFulfillment(
            $order,
            $fulfillmentPercentage,
            null, // No pengiriman for test
        );

        return response()->json([
            "success" => $notificationId !== null,
            "notification_id" => $notificationId,
            "order" => [
                "id" => $order->id,
                "no_order" => $order->no_order,
                "po_number" => $order->po_number,
                "status" => $order->status,
                "total_qty" => $order->total_qty,
                "shipped_qty" => $shippedQty,
                "fulfillment_percentage" => $fulfillmentPercentage,
                "is_nearing_fulfillment" => $isNearingFulfillment,
            ],
            "recipient" => [
                "id" => $order->creator?->id,
                "name" => $order->creator?->nama,
            ],
            "threshold" => [
                "min" =>
                    \App\Services\Notifications\OrderNotificationService::FULFILLMENT_THRESHOLD_MIN,
                "max" =>
                    \App\Services\Notifications\OrderNotificationService::FULFILLMENT_THRESHOLD_MAX,
            ],
        ]);
    })->name("notifications.test-order-fulfillment");

    // Test route for direktur consultation notification
    // Usage: /notifications/test-direktur-consultation/{order_id}?note=your+message
    Route::get("/notifications/test-direktur-consultation/{order}", function (
        \App\Models\Order $order,
        \Illuminate\Http\Request $request,
    ) {
        $user = auth()->user();
        if (!$user) {
            return response()->json(["error" => "Not authenticated"], 401);
        }

        $note = $request->get("note", "Test konsultasi dari " . $user->nama);

        // Send notification to all direktur
        $count = \App\Services\Notifications\OrderNotificationService::notifyDirekturConsultation(
            $order,
            $user,
            $note,
        );

        // Get list of direktur who received the notification
        $direkturs = \App\Models\User::where("role", "direktur")
            ->where("status", "aktif")
            ->get(["id", "nama", "email"]);

        return response()->json([
            "success" => $count > 0,
            "notifications_sent" => $count,
            "order" => [
                "id" => $order->id,
                "no_order" => $order->no_order,
                "po_number" => $order->po_number,
                "fulfillment_percentage" => $order->getFulfillmentPercentage(),
            ],
            "requested_by" => [
                "id" => $user->id,
                "name" => $user->nama,
            ],
            "note" => $note,
            "direkturs" => $direkturs,
        ]);
    })->name("notifications.test-direktur-consultation");

    // Kontak Klien routes
    Route::get("/kontak-klien/{klien}", function ($klien) {
        return view("pages.marketing.daftar-kontak-livewire", compact("klien"));
    })->name("kontak-klien.index");

    // Spesifikasi routes
    Route::get("/spesifikasi", function () {
        return view("pages.marketing.spesifikasi");
    })->name("spesifikasi.index");

    Route::get("/marketing/spesifikasi", function () {
        return view("pages.marketing.spesifikasi");
    })->name("marketing.spesifikasi");

    // Order routes
    Route::resource("orders", OrderController::class);
    // API: get top suppliers for a client material (used in order creation UI)
    Route::get("/orders/material/{material}/suppliers", [
        OrderController::class,
        "getSuppliersForMaterial",
    ])->name("orders.material.suppliers");
    Route::prefix("orders/{order}")->group(function () {
        Route::post("/confirm", [OrderController::class, "confirm"])->name(
            "orders.confirm",
        );
        Route::post("/start-processing", [
            OrderController::class,
            "startProcessing",
        ])->name("orders.start-processing");
        Route::post("/complete", [OrderController::class, "complete"])->name(
            "orders.complete",
        );
        Route::post("/cancel", [OrderController::class, "cancel"])->name(
            "orders.cancel",
        );
        Route::post("/consult-direktur", [
            OrderController::class,
            "consultDirektur",
        ])->name("orders.consult-direktur");
        Route::post("/consultation/{consultation}/respond", [
            OrderController::class,
            "respondConsultation",
        ])->name("orders.consultation.respond");
        Route::post("/add-quantity", [
            OrderController::class,
            "addQuantity",
        ])->name("orders.add-quantity");
    });

    // Evaluasi Supplier routes
    Route::get("/pengiriman/{pengiriman}/evaluasi", function (
        App\Models\Pengiriman $pengiriman,
    ) {
        return view("procurement.evaluate-supplier", [
            "pengiriman" => $pengiriman,
        ]);
    })->name("pengiriman.evaluasi");

    Route::get("/pengiriman/{pengiriman}/review", function (
        App\Models\Pengiriman $pengiriman,
    ) {
        $pengiriman->load(["details.bahanBakuSupplier.supplier", "purchasing"]);

        $evaluation = App\Models\SupplierEvaluation::where(
            "pengiriman_id",
            $pengiriman->id,
        )
            ->with(["details", "evaluator", "supplier"])
            ->first();

        $evaluationDetails = $evaluation
            ? $evaluation->details->groupBy("kriteria")
            : collect();
        $criteriaStructure = App\Models\SupplierEvaluation::getCriteriaStructure();

        return view(
            "procurement.view-evaluation-static",
            compact(
                "pengiriman",
                "evaluation",
                "evaluationDetails",
                "criteriaStructure",
            ),
        );
    })->name("pengiriman.review");

    // Penawaran routes
    Route::get("/penawaran", function () {
        return view("pages.marketing.riwayat-penawaran");
    })->name("penawaran.index");
    Route::get("/penawaran/buat", function () {
        return view("pages.marketing.penawaran");
    })->name("penawaran.create");
    Route::get("/penawaran/{penawaran}/edit", function (
        App\Models\Penawaran $penawaran,
    ) {
        return view("pages.marketing.penawaran", compact("penawaran"));
    })->name("penawaran.edit");

    // Klien routes
    Route::get("/klien/create", [KlienController::class, "create"])->name(
        "klien.create",
    );
    Route::post("/klien", [KlienController::class, "store"])->name(
        "klien.store",
    );
    Route::get("/klien/{klien}/edit", function (App\Models\Klien $klien) {
        return view("pages.marketing.klien.edit-livewire", compact("klien"));
    })->name("klien.edit");
    Route::get("/klien/{klien}", [KlienController::class, "show"])->name(
        "klien.show",
    );
    Route::put("/klien/{klien}", [KlienController::class, "update"])->name(
        "klien.update",
    );
    Route::delete("/klien/{klien}", [KlienController::class, "destroy"])->name(
        "klien.destroy",
    );
    // Klien material price history page
    Route::get("/klien/{klien}/bahan-baku/{material}/riwayat-harga", [
        KlienController::class,
        "riwayatHarga",
    ])->name("klien.riwayat-harga");

    // Procurement routes - only for purchasing roles
    Route::prefix("procurement")->group(function () {
        // Supplier routes
        Route::get("/supplier", [SupplierController::class, "index"])->name(
            "supplier.index",
        );
        Route::get("/supplier/create", [
            SupplierController::class,
            "create",
        ])->name("supplier.create");
        Route::post("/supplier", [SupplierController::class, "store"])->name(
            "supplier.store",
        );
        Route::get("/supplier/{supplier:slug}", [
            SupplierController::class,
            "show",
        ])->name("supplier.show");
        Route::get("/supplier/{supplier:slug}/edit", [
            SupplierController::class,
            "edit",
        ])->name("supplier.edit");
        Route::put("/supplier/{supplier:slug}", [
            SupplierController::class,
            "update",
        ])->name("supplier.update");
        Route::delete("/supplier/{supplier:slug}", [
            SupplierController::class,
            "destroy",
        ])->name("supplier.destroy");
        Route::get("/supplier/{supplier:slug}/reviews", [
            SupplierController::class,
            "reviews",
        ])->name("supplier.reviews");
        Route::get(
            "/supplier/{supplier:slug}/bahan-baku/{bahanBaku:slug}/riwayat-harga",
            [SupplierController::class, "riwayatHarga"],
        )->name("supplier.riwayat-harga");

        // Forecasting routes
        Route::get("/forecasting", [
            ForecastingController::class,
            "index",
        ])->name("forecasting.index");
        Route::get(
            "/forecasting/bahan-baku-suppliers/{purchaseOrderBahanBakuId}",
            [ForecastingController::class, "getBahanBakuSuppliers"],
        )->name("forecasting.get-bahan-baku-suppliers");
        Route::post("/forecasting/create", [
            ForecastingController::class,
            "createForecast",
        ])->name("forecasting.create");
        Route::get("/forecasting/{id}/detail", [
            ForecastingController::class,
            "getForecastDetail",
        ])->name("forecasting.detail");
        Route::post("/forecasting/{id}/kirim", [
            ForecastingController::class,
            "kirimForecast",
        ])->name("forecasting.kirim");
        Route::post("/forecasting/{id}/batal", [
            ForecastingController::class,
            "batalkanForecast",
        ])->name("forecasting.batal");
        Route::post("/forecasting/{id}/delete", [
            ForecastingController::class,
            "deleteForecast",
        ])->name("forecasting.delete");
        Route::get("/forecasting/export-pending", [
            ForecastingController::class,
            "exportPending",
        ])->name("forecast.export-pending");

        // Pengiriman routes
        // Routes tanpa parameter harus diletakkan sebelum resource routes
        Route::get("pengiriman/submit-modal", [
            PengirimanController::class,
            "getSubmitModal",
        ])->name("purchasing.pengiriman.submit-modal");
        Route::post("pengiriman/submit", [
            PengirimanController::class,
            "submitPengiriman",
        ])->name("purchasing.pengiriman.submit");
        Route::get("pengiriman/batal-modal", [
            PengirimanController::class,
            "getBatalModal",
        ])->name("purchasing.pengiriman.batal-modal");
        Route::post("pengiriman/batal", [
            PengirimanController::class,
            "batalPengiriman",
        ])->name("purchasing.pengiriman.batal");

        Route::resource("pengiriman", PengirimanController::class)->names([
            "index" => "purchasing.pengiriman.index",
            "create" => "purchasing.pengiriman.create",
            "store" => "purchasing.pengiriman.store",
            "show" => "purchasing.pengiriman.show",
            "edit" => "purchasing.pengiriman.edit",
            "update" => "purchasing.pengiriman.update",
            "destroy" => "purchasing.pengiriman.destroy",
        ]);
        
        // Aksi modal route (untuk pengiriman masuk)
        Route::get("pengiriman/{pengiriman}/aksi-modal", [
            PengirimanController::class,
            "getAksiModal",
        ])->name("purchasing.pengiriman.aksi-modal");
        
        // Delete pengiriman gagal route (STRICT - hanya jika tidak ada forecast/pengiriman terkait)
        Route::delete("pengiriman/{pengiriman}/delete-gagal", [
            PengirimanController::class,
            "deletePengirimanGagal",
        ])->name("purchasing.pengiriman.delete-gagal");
        
        Route::put("pengiriman/{pengiriman}/status", [
            PengirimanController::class,
            "updateStatus",
        ])->name("purchasing.pengiriman.update-status");
        Route::get("pengiriman/{pengiriman}/detail", [
            PengirimanController::class,
            "getDetail",
        ])->name("purchasing.pengiriman.get-detail");
        Route::get("pengiriman/{pengiriman}/detail-berhasil", [
            PengirimanController::class,
            "getDetailBerhasil",
        ])->name("purchasing.pengiriman.detail-berhasil");
        Route::post("pengiriman/{pengiriman}/update-catatan", [
            PengirimanController::class,
            "updateCatatan",
        ])->name("purchasing.pengiriman.update-catatan");
        Route::get("pengiriman/{pengiriman}/detail-gagal", [
            PengirimanController::class,
            "getDetailGagal",
        ])->name("purchasing.pengiriman.detail-gagal");
        Route::get("pengiriman/{pengiriman}/detail-verifikasi", [
            PengirimanController::class,
            "getDetailVerifikasi",
        ])->name("purchasing.pengiriman.detail-verifikasi");
        Route::get("pengiriman/{pengiriman}/detail-fisik", [
            PengirimanController::class,
            "getDetailFisik",
        ])->name("purchasing.pengiriman.detail-fisik");
        Route::post("pengiriman/{pengiriman}/verifikasi-fisik", [
            PengirimanController::class,
            "verifikasiFisik",
        ])->name("purchasing.pengiriman.verifikasi-fisik");
        Route::get("pengiriman/{pengiriman}/revisi-modal", [
            PengirimanController::class,
            "getRevisiModal",
        ])->name("purchasing.pengiriman.revisi-modal");
        Route::get("pengiriman/{pengiriman}/verifikasi-modal", [
            PengirimanController::class,
            "getVerifikasiModal",
        ])->name("purchasing.pengiriman.verifikasi-modal");
        Route::get("pengiriman/{pengiriman}/modal/revisi", [
            PengirimanController::class,
            "getRevisiModal",
        ])->name("purchasing.pengiriman.modal.revisi");
        Route::get("pengiriman/{pengiriman}/modal/verifikasi", [
            PengirimanController::class,
            "getVerifikasiModal",
        ])->name("purchasing.pengiriman.modal.verifikasi");
        Route::post("pengiriman/{pengiriman}/verifikasi", [
            PengirimanController::class,
            "verifikasiPengiriman",
        ])->name("purchasing.pengiriman.verifikasi");
        Route::post("pengiriman/{pengiriman}/verifikasi-fisik", [
            PengirimanController::class,
            "verifikasiFisik",
        ])->name("purchasing.pengiriman.verifikasi-fisik");
        Route::post("pengiriman/{pengiriman}/revisi", [
            PengirimanController::class,
            "revisiPengiriman",
        ])->name("purchasing.pengiriman.revisi");
        Route::post("pengiriman/{pengiriman}/upload-foto-tanda-terima", [
            PengirimanController::class,
            "uploadFotoTandaTerima",
        ])->name("purchasing.pengiriman.upload-foto-tanda-terima");
    });

    // Accounting routes - accessible to all authenticated users
    Route::prefix("accounting")
        ->name("accounting.")
        ->group(function () {
            // Approval Pembayaran
            Route::get("/approval-pembayaran", function () {
                return view("pages.accounting.approval-pembayaran");
            })->name("approval-pembayaran");

            // Approve Approval Pembayaran
            Route::get("/approval-pembayaran/{id}/approve", function ($id) {
                return view("pages.accounting.approval-pembayaran.approve", [
                    "approvalId" => $id,
                ]);
            })->name("approval-pembayaran.approve");

            // Detail Approval Pembayaran
            Route::get("/approval-pembayaran/{id}/detail", function ($id) {
                return view("pages.accounting.approval-pembayaran.detail", [
                    "approvalId" => $id,
                ]);
            })->name("approval-pembayaran.detail");

            // Approval Penagihan
            Route::get("/approval-penagihan", function () {
                return view("pages.accounting.approval-penagihan");
            })->name("approval-penagihan");

            Route::get("/approval-penagihan/{approvalId}/approve", function (
                $approvalId,
            ) {
                return view("pages.accounting.approve-penagihan", [
                    "approvalId" => $approvalId,
                ]);
            })->name("approval-penagihan.detail");

            Route::get("/approval-penagihan/{approvalId}/detail", function (
                $approvalId,
            ) {
                return view("pages.accounting.detail-penagihan", [
                    "approvalId" => $approvalId,
                ]);
            })->name("approval-penagihan.view");

            // Company Settings
            Route::get("/company-settings", function () {
                return view("pages.accounting.company-settings");
            })->name("company-settings");

            // Catatan Piutang
            Route::get("/catatan-piutang", function () {
                return view("pages.accounting.catatan-piutang");
            })->name("catatan-piutang");
        });

    // Pengelolaan Akun routes - only for direktur
    Route::middleware(["role:direktur"])->group(function () {
        Route::resource(
            "pengelolaan-akun",
            PengelolaanAkunController::class,
        )->parameters([
            "pengelolaan-akun" => "user",
        ]);
    });

    // Marketing routes - only for marketing and direktur (moved inside auth middleware)
    Route::get("/klien", function () {
        return view("pages.marketing.daftar-klien-livewire");
    })->name("klien.index");

    // Company-level CRUD routes
    Route::post("/klien/company/store", [
        KlienController::class,
        "storeCompany",
    ])->name("klien.company.store");
    Route::put("/klien/company/update", [
        KlienController::class,
        "updateCompany",
    ])->name("klien.company.update");
    Route::delete("/klien/company/destroy", [
        KlienController::class,
        "destroyCompany",
    ])->name("klien.company.destroy");

    // Client Materials API routes
    Route::prefix("api/klien-materials")->group(function () {
        Route::post("/", [KlienController::class, "storeMaterial"])->name(
            "api.klien-materials.store",
        );
        Route::put("/{material}", [
            KlienController::class,
            "updateMaterial",
        ])->name("api.klien-materials.update");
        Route::delete("/{material}", [
            KlienController::class,
            "destroyMaterial",
        ])->name("api.klien-materials.destroy");
        Route::get("/{material}/price-history", [
            KlienController::class,
            "getMaterialPriceHistory",
        ])->name("api.klien-materials.price-history");
    });
});
// Company-level CRUD routes
Route::post("/klien/company/store", [
    KlienController::class,
    "storeCompany",
])->name("klien.company.store");
Route::put("/klien/company/update", [
    KlienController::class,
    "updateCompany",
])->name("klien.company.update");
Route::delete("/klien/company/destroy", [
    KlienController::class,
    "destroyCompany",
])->name("klien.company.destroy");

// Client Materials API routes
Route::prefix("api/klien-materials")->group(function () {
    Route::post("/", [KlienController::class, "storeMaterial"])->name(
        "api.klien-materials.store",
    );
    Route::put("/{material}", [KlienController::class, "updateMaterial"])->name(
        "api.klien-materials.update",
    );
    Route::delete("/{material}", [
        KlienController::class,
        "destroyMaterial",
    ])->name("api.klien-materials.destroy");
    Route::get("/{material}/price-history", [
        KlienController::class,
        "getMaterialPriceHistory",
    ])->name("api.klien-materials.price-history");
});
