<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketing\KlienController;
use App\Http\Controllers\PengelolaanAkunController;
use App\Http\Controllers\Purchasing\SupplierController;
use App\Http\Controllers\Purchasing\ForecastingController;

Route::get('/', function () {
    return view('auth.login');
});
Route::get('/dashboard', function () {
    return view('pages.dashboard');
})->name('dashboard');

// Supplier routes
Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
Route::get('/supplier/create', [SupplierController::class, 'create'])->name('supplier.create');
Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
Route::get('/supplier/{supplier:slug}', [SupplierController::class, 'show'])->name('supplier.show');
Route::get('/supplier/{supplier:slug}/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
Route::put('/supplier/{supplier:slug}', [SupplierController::class, 'update'])->name('supplier.update');
Route::delete('/supplier/{supplier:slug}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
Route::get('/supplier/{supplier:slug}/bahan-baku/{bahanBaku:slug}/riwayat-harga', [SupplierController::class, 'riwayatHarga'])->name('supplier.riwayat-harga');

// Forecasting routes
Route::get('/forecasting', [ForecastingController::class, 'index'])->name('forecasting.index');
Route::get('/forecasting/bahan-baku-suppliers/{purchaseOrderBahanBakuId}', [ForecastingController::class, 'getBahanBakuSuppliers'])->name('forecasting.get-bahan-baku-suppliers');
Route::post('/forecasting/create', [ForecastingController::class, 'createForecast'])->name('forecasting.create');

// Klien routes
Route::get('/klien', [KlienController::class, 'index'])->name('klien.index');
Route::get('/klien/create', [KlienController::class, 'create'])->name('klien.create');
Route::post('/klien', [KlienController::class, 'store'])->name('klien.store');
Route::get('/klien/{klien}/edit', [KlienController::class, 'edit'])->name('klien.edit');
Route::get('/klien/{klien}', [KlienController::class, 'show'])->name('klien.show');
Route::put('/klien/{klien}', [KlienController::class, 'update'])->name('klien.update');
Route::delete('/klien/{klien}', [KlienController::class, 'destroy'])->name('klien.destroy');

// Company-level CRUD routes
Route::post('/klien/company/store', [KlienController::class, 'storeCompany'])->name('klien.company.store');
Route::put('/klien/company/update', [KlienController::class, 'updateCompany'])->name('klien.company.update');
Route::delete('/klien/company/destroy', [KlienController::class, 'destroyCompany'])->name('klien.company.destroy');

// Test route for debugging
Route::get('/test-klien-modal', function() {
    return response()->json(['success' => true, 'message' => 'Test route working', 'csrf' => csrf_token()]);
});

// Pengelolaan Akun routes
Route::resource('pengelolaan-akun', PengelolaanAkunController::class)->parameters([
    'pengelolaan-akun' => 'user'
]);
