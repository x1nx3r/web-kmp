<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register additional migration paths
        $this->loadMigrationsFrom([
            database_path('migrations'),
            database_path('migrations/Klien'),
            database_path('migrations/Supplier'),
            database_path('migrations/Bahan Baku Klien'),
            database_path('migrations/Bahan Baku Supplier'),
            database_path('migrations/PO'),
            database_path('migrations/Verifikasi PO'),
            database_path('migrations/Forecast'),
            database_path('migrations/Pengiriman'),
            database_path('migrations/Pembayaran'),
            database_path('migrations/Detail Bahan Baku PO'),
        ]);
    }
}
