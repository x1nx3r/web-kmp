<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketing\KlienController;
use App\Http\Controllers\PengelolaanAkunController;
use App\Http\Controllers\Purchasing\SupplierController;
use App\Http\Controllers\Purchasing\ForecastingController;
use App\Http\Controllers\Purchasing\PengirimanController;

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
Route::get('/purchasing/forecast/{id}/detail', [ForecastingController::class, 'getForecastDetail'])->name('forecasting.detail');
Route::post('/purchasing/forecast/{id}/kirim', [ForecastingController::class, 'kirimForecast'])->name('forecasting.kirim');
Route::post('/purchasing/forecast/{id}/batal', [ForecastingController::class, 'batalkanForecast'])->name('forecasting.batal');

// Pengiriman routes
Route::prefix('purchasing')->group(function () {
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
    Route::get('pengiriman/{pengiriman}/aksi-modal', [PengirimanController::class, 'getAksiModal'])->name('purchasing.pengiriman.aksi-modal');
    Route::get('pengiriman/submit-modal', [PengirimanController::class, 'getSubmitModal'])->name('purchasing.pengiriman.submit-modal');
    Route::post('pengiriman/submit', [PengirimanController::class, 'submitPengiriman'])->name('purchasing.pengiriman.submit');
    Route::get('bahan-baku-supplier/{id}/harga', [PengirimanController::class, 'getBahanBakuHarga'])->name('purchasing.bahan-baku-supplier.harga');
});

// Klien routes
Route::get('/klien', function() {
    return view('pages.marketing.daftar-klien-livewire');
})->name('klien.index');

// Penawaran routes
Route::get('/penawaran', function() {
    return view('pages.marketing.penawaran');
})->name('penawaran');
Route::get('/riwayat-penawaran', function() {
    return view('pages.marketing.riwayat-penawaran');
})->name('riwayat-penawaran');
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

// Company-level CRUD routes
Route::post('/klien/company/store', [KlienController::class, 'storeCompany'])->name('klien.company.store');
Route::put('/klien/company/update', [KlienController::class, 'updateCompany'])->name('klien.company.update');
Route::delete('/klien/company/destroy', [KlienController::class, 'destroyCompany'])->name('klien.company.destroy');

// Test route for debugging
Route::get('/test-klien-modal', function() {
    return response()->json(['success' => true, 'message' => 'Test route working', 'csrf' => csrf_token()]);
});

// Client Materials API routes
Route::prefix('api/klien-materials')->group(function () {
    Route::post('/', [KlienController::class, 'storeMaterial'])->name('api.klien-materials.store');
    Route::put('/{material}', [KlienController::class, 'updateMaterial'])->name('api.klien-materials.update');
    Route::delete('/{material}', [KlienController::class, 'destroyMaterial'])->name('api.klien-materials.destroy');
    Route::get('/{material}/price-history', [KlienController::class, 'getMaterialPriceHistory'])->name('api.klien-materials.price-history');
});

// Pengelolaan Akun routes
Route::resource('pengelolaan-akun', PengelolaanAkunController::class)->parameters([
    'pengelolaan-akun' => 'user'
]);
