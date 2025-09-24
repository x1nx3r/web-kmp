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
Route::post('/klien', [KlienController::class, 'store'])->name('klien.store');
Route::get('/klien/{klien}', [KlienController::class, 'show'])->name('klien.show');
Route::put('/klien/{klien}', [KlienController::class, 'update'])->name('klien.update');
Route::delete('/klien/{klien}', [KlienController::class, 'destroy'])->name('klien.destroy');

// Pengelolaan Akun routes
Route::resource('pengelolaan-akun', PengelolaanAkunController::class)->parameters([
    'pengelolaan-akun' => 'user'
]);
