<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {

    // Dashboard - accessible by all authenticated users
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/purchase-order', [LaporanPOController::class, 'index'])->name('po');
            Route::post('/purchase-order/export', [LaporanPOController::class, 'export'])->name('po.export');

            Route::get('/omset', [LaporanOmsetController::class, 'index'])->name('omset');
            Route::post('/omset/export', [LaporanOmsetController::class, 'export'])->name('omset.export');

            Route::get('/pengiriman', [LaporanPengirimanController::class, 'index'])->name('pengiriman');
            Route::match(['GET', 'POST'], '/pengiriman/export', [LaporanPengirimanController::class, 'export'])->name('pengiriman.export');
            Route::get('/penagihan', [LaporanPenagihanController::class, 'index'])->name('penagihan');
            Route::post('/penagihan/export', [LaporanPenagihanController::class, 'export'])->name('penagihan.export');
    });

    // Pengaturan - accessible by all authenticated users
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan');
    Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

    // Kontak Klien routes
    Route::get('/kontak-klien/{klien}', function($klien) {
        return view('pages.marketing.daftar-kontak-livewire', compact('klien'));
    })->name('kontak-klien.index');

    // Spesifikasi routes
    Route::get('/spesifikasi', function() {
        return view('pages.marketing.spesifikasi');
    })->name('spesifikasi.index');

    Route::get('/marketing/spesifikasi', function() {
        return view('pages.marketing.spesifikasi');
    })->name('marketing.spesifikasi');

    // Order routes
    Route::resource('orders', OrderController::class);
    // API: get top suppliers for a client material (used in order creation UI)
    Route::get('/orders/material/{material}/suppliers', [OrderController::class, 'getSuppliersForMaterial'])->name('orders.material.suppliers');
    Route::prefix('orders/{order}')->group(function () {
        Route::post('/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
        Route::post('/start-processing', [OrderController::class, 'startProcessing'])->name('orders.start-processing');
        Route::post('/complete', [OrderController::class, 'complete'])->name('orders.complete');
        Route::post('/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });
    
    // Evaluasi Supplier routes
    Route::get('/pengiriman/{pengiriman}/evaluasi', function(App\Models\Pengiriman $pengiriman) {
        return view('procurement.evaluate-supplier', ['pengiriman' => $pengiriman]);
    })->name('pengiriman.evaluasi');

    Route::get('/pengiriman/{pengiriman}/review', function(App\Models\Pengiriman $pengiriman) {
        $pengiriman->load([
            'details.bahanBakuSupplier.supplier',
            'purchasing',
        ]);
        
        $evaluation = App\Models\SupplierEvaluation::where('pengiriman_id', $pengiriman->id)
            ->with(['details', 'evaluator', 'supplier'])
            ->first();
            
        $evaluationDetails = $evaluation ? $evaluation->details->groupBy('kriteria') : collect();
        $criteriaStructure = App\Models\SupplierEvaluation::getCriteriaStructure();
        
        return view('procurement.view-evaluation-static', compact('pengiriman', 'evaluation', 'evaluationDetails', 'criteriaStructure'));
    })->name('pengiriman.review');

    // Penawaran routes
    Route::get('/penawaran', function() {
        return view('pages.marketing.riwayat-penawaran');
    })->name('penawaran.index');
    Route::get('/penawaran/buat', function() {
        return view('pages.marketing.penawaran');
    })->name('penawaran.create');
    Route::get('/penawaran/{penawaran}/edit', function(App\Models\Penawaran $penawaran) {
        return view('pages.marketing.penawaran', compact('penawaran'));
    })->name('penawaran.edit');

    // Klien routes
    Route::get('/klien/create', [KlienController::class, 'create'])->name('klien.create');
    Route::post('/klien', [KlienController::class, 'store'])->name('klien.store');
    Route::get('/klien/{klien}/edit', function(App\Models\Klien $klien) {
        return view('pages.marketing.klien.edit-livewire', compact('klien'));
    })->name('klien.edit');
    Route::get('/klien/{klien}', [KlienController::class, 'show'])->name('klien.show');
    Route::put('/klien/{klien}', [KlienController::class, 'update'])->name('klien.update');
    Route::delete('/klien/{klien}', [KlienController::class, 'destroy'])->name('klien.destroy');
    // Klien material price history page
    Route::get('/klien/{klien}/bahan-baku/{material}/riwayat-harga', [KlienController::class, 'riwayatHarga'])->name('klien.riwayat-harga');

    // Procurement routes - only for purchasing roles
    Route::prefix('procurement')->group(function () {

    // Supplier routes
    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
    Route::get('/supplier/create', [SupplierController::class, 'create'])->name('supplier.create');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
    Route::get('/supplier/{supplier:slug}', [SupplierController::class, 'show'])->name('supplier.show');
    Route::get('/supplier/{supplier:slug}/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
    Route::put('/supplier/{supplier:slug}', [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('/supplier/{supplier:slug}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
    Route::get('/supplier/{supplier:slug}/reviews', [SupplierController::class, 'reviews'])->name('supplier.reviews');
    Route::get('/supplier/{supplier:slug}/bahan-baku/{bahanBaku:slug}/riwayat-harga', [SupplierController::class, 'riwayatHarga'])->name('supplier.riwayat-harga');

    // Forecasting routes
    Route::get('/forecasting', [ForecastingController::class, 'index'])->name('forecasting.index');
    Route::get('/forecasting/bahan-baku-suppliers/{purchaseOrderBahanBakuId}', [ForecastingController::class, 'getBahanBakuSuppliers'])->name('forecasting.get-bahan-baku-suppliers');
    Route::post('/forecasting/create', [ForecastingController::class, 'createForecast'])->name('forecasting.create');
    Route::get('/forecasting/{id}/detail', [ForecastingController::class, 'getForecastDetail'])->name('forecasting.detail');
    Route::post('/forecasting/{id}/kirim', [ForecastingController::class, 'kirimForecast'])->name('forecasting.kirim');
    Route::post('/forecasting/{id}/batal', [ForecastingController::class, 'batalkanForecast'])->name('forecasting.batal');
    Route::get('/forecasting/export-pending', [ForecastingController::class, 'exportPending'])->name('forecast.export-pending');

    // Pengiriman routes
    // Routes tanpa parameter harus diletakkan sebelum resource routes
    Route::get('pengiriman/submit-modal', [PengirimanController::class, 'getSubmitModal'])->name('purchasing.pengiriman.submit-modal');
    Route::post('pengiriman/submit', [PengirimanController::class, 'submitPengiriman'])->name('purchasing.pengiriman.submit');
    Route::get('pengiriman/batal-modal', [PengirimanController::class, 'getBatalModal'])->name('purchasing.pengiriman.batal-modal');
    Route::post('pengiriman/batal', [PengirimanController::class, 'batalPengiriman'])->name('purchasing.pengiriman.batal');

    Route::resource('pengiriman', PengirimanController::class)->names([
        'index' => 'purchasing.pengiriman.index',
        'create' => 'purchasing.pengiriman.create',
        'store' => 'purchasing.pengiriman.store',
        'show' => 'purchasing.pengiriman.show',
        'edit' => 'purchasing.pengiriman.edit',
        'update' => 'purchasing.pengiriman.update',
        'destroy' => 'purchasing.pengiriman.destroy',
    ]);
    Route::put('pengiriman/{pengiriman}/status', [PengirimanController::class, 'updateStatus'])->name('purchasing.pengiriman.update-status');
    Route::get('pengiriman/{pengiriman}/detail', [PengirimanController::class, 'getDetail'])->name('purchasing.pengiriman.get-detail');
    Route::get('pengiriman/{pengiriman}/detail-berhasil', [PengirimanController::class, 'getDetailBerhasil'])->name('purchasing.pengiriman.detail-berhasil');
    Route::get('pengiriman/{pengiriman}/detail-gagal', [PengirimanController::class, 'getDetailGagal'])->name('purchasing.pengiriman.detail-gagal');
    Route::get('pengiriman/{pengiriman}/detail-verifikasi', [PengirimanController::class, 'getDetailVerifikasi'])->name('purchasing.pengiriman.detail-verifikasi');
    Route::get('pengiriman/{pengiriman}/revisi-modal', [PengirimanController::class, 'getRevisiModal'])->name('purchasing.pengiriman.revisi-modal');
    Route::get('pengiriman/{pengiriman}/verifikasi-modal', [PengirimanController::class, 'getVerifikasiModal'])->name('purchasing.pengiriman.verifikasi-modal');
    Route::get('pengiriman/{pengiriman}/modal/revisi', [PengirimanController::class, 'getRevisiModal'])->name('purchasing.pengiriman.modal.revisi');
    Route::get('pengiriman/{pengiriman}/modal/verifikasi', [PengirimanController::class, 'getVerifikasiModal'])->name('purchasing.pengiriman.modal.verifikasi');
    Route::post('pengiriman/{pengiriman}/verifikasi', [PengirimanController::class, 'verifikasiPengiriman'])->name('purchasing.pengiriman.verifikasi');
    Route::post('pengiriman/{pengiriman}/revisi', [PengirimanController::class, 'revisiPengiriman'])->name('purchasing.pengiriman.revisi');
    Route::post('pengiriman/{pengiriman}/upload-foto-tanda-terima', [PengirimanController::class, 'uploadFotoTandaTerima'])->name('purchasing.pengiriman.upload-foto-tanda-terima');
    Route::get('pengiriman/{pengiriman}/aksi-modal', [PengirimanController::class, 'getAksiModal'])->name('purchasing.pengiriman.aksi-modal');
        Route::get('bahan-baku-supplier/{id}/harga', [PengirimanController::class, 'getBahanBakuHarga'])->name('purchasing.bahan-baku-supplier.harga');
    });

    // Accounting routes - for accounting staff, manager, direktur, and superadmin
    Route::middleware(['role:staff_accounting,manager_accounting,direktur,superadmin'])->prefix('accounting')->name('accounting.')->group(function () {
        // Approval Pembayaran
        Route::get('/approval-pembayaran', function() {
            return view('pages.accounting.approval-pembayaran');
        })->name('approval-pembayaran');

        // Approve Approval Pembayaran
        Route::get('/approval-pembayaran/{id}/approve', function($id) {
            return view('pages.accounting.approval-pembayaran.approve', ['approvalId' => $id]);
        })->name('approval-pembayaran.approve');

        // Detail Approval Pembayaran
        Route::get('/approval-pembayaran/{id}/detail', function($id) {
            return view('pages.accounting.approval-pembayaran.detail', ['approvalId' => $id]);
        })->name('approval-pembayaran.detail');

        // Approval Penagihan
        Route::get('/approval-penagihan', function() {
            return view('pages.accounting.approval-penagihan');
        })->name('approval-penagihan');

        Route::get('/approval-penagihan/{approvalId}/approve', function($approvalId) {
            return view('pages.accounting.approve-penagihan', ['approvalId' => $approvalId]);
        })->name('approval-penagihan.detail');

        Route::get('/approval-penagihan/{approvalId}/detail', function($approvalId) {
            return view('pages.accounting.detail-penagihan', ['approvalId' => $approvalId]);
        })->name('approval-penagihan.view');

        // Company Settings
        Route::get('/company-settings', function() {
            return view('pages.accounting.company-settings');
        })->name('company-settings');

        // Catatan Piutang
        Route::get('/catatan-piutang', function() {
            return view('pages.accounting.catatan-piutang');
        })->name('catatan-piutang');
    });



    // Pengelolaan Akun routes - only for direktur
    Route::middleware(['role:direktur'])->group(function () {
        Route::resource('pengelolaan-akun', PengelolaanAkunController::class)->parameters([
            'pengelolaan-akun' => 'user'
        ]);
    });

    // Marketing routes - only for marketing and direktur (moved inside auth middleware)
    Route::get('/klien', function() {
        return view('pages.marketing.daftar-klien-livewire');
    })->name('klien.index');

    // Company-level CRUD routes
    Route::post('/klien/company/store', [KlienController::class, 'storeCompany'])->name('klien.company.store');
    Route::put('/klien/company/update', [KlienController::class, 'updateCompany'])->name('klien.company.update');
    Route::delete('/klien/company/destroy', [KlienController::class, 'destroyCompany'])->name('klien.company.destroy');

    // Client Materials API routes
    Route::prefix('api/klien-materials')->group(function () {
        Route::post('/', [KlienController::class, 'storeMaterial'])->name('api.klien-materials.store');
        Route::put('/{material}', [KlienController::class, 'updateMaterial'])->name('api.klien-materials.update');
        Route::delete('/{material}', [KlienController::class, 'destroyMaterial'])->name('api.klien-materials.destroy');
        Route::get('/{material}/price-history', [KlienController::class, 'getMaterialPriceHistory'])->name('api.klien-materials.price-history');
    });

});
        // Company-level CRUD routes
        Route::post('/klien/company/store', [KlienController::class, 'storeCompany'])->name('klien.company.store');
        Route::put('/klien/company/update', [KlienController::class, 'updateCompany'])->name('klien.company.update');
        Route::delete('/klien/company/destroy', [KlienController::class, 'destroyCompany'])->name('klien.company.destroy');



        // Client Materials API routes
        Route::prefix('api/klien-materials')->group(function () {
            Route::post('/', [KlienController::class, 'storeMaterial'])->name('api.klien-materials.store');
            Route::put('/{material}', [KlienController::class, 'updateMaterial'])->name('api.klien-materials.update');
            Route::delete('/{material}', [KlienController::class, 'destroyMaterial'])->name('api.klien-materials.destroy');
            Route::get('/{material}/price-history', [KlienController::class, 'getMaterialPriceHistory'])->name('api.klien-materials.price-history');
        });
