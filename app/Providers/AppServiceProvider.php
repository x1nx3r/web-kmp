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
        // Register Observers
        \App\Models\Pengiriman::observe(\App\Observers\PengirimanObserver::class);

        // Register Event Listeners for Cache Invalidation
        \App\Models\InvoicePenagihan::saved(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard', 'charts'])->flush());
        \App\Models\InvoicePenagihan::deleted(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard', 'charts'])->flush());

        \App\Models\ApprovalPembayaran::saved(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard', 'charts'])->flush());
        \App\Models\ApprovalPembayaran::deleted(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard', 'charts'])->flush());

        \App\Models\OrderDetail::saved(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard', 'charts'])->flush());
        \App\Models\OrderDetail::deleted(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard', 'charts'])->flush());

        \App\Models\Order::saved(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard', 'charts'])->flush());
        \App\Models\Order::deleted(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard', 'charts'])->flush());

        \App\Models\Klien::saved(fn() => \Illuminate\Support\Facades\Cache::tags(['ref', 'charts'])->flush());
        \App\Models\Klien::deleted(fn() => \Illuminate\Support\Facades\Cache::tags(['ref', 'charts'])->flush());

        \App\Models\Supplier::saved(fn() => \Illuminate\Support\Facades\Cache::tags(['ref', 'charts'])->flush());
        \App\Models\Supplier::deleted(fn() => \Illuminate\Support\Facades\Cache::tags(['ref', 'charts'])->flush());

        \App\Models\User::saved(fn() => \Illuminate\Support\Facades\Cache::tags(['ref'])->flush());
        \App\Models\User::deleted(fn() => \Illuminate\Support\Facades\Cache::tags(['ref'])->flush());

        \App\Models\TargetOmset::saved(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard'])->flush());
        \App\Models\TargetOmset::deleted(fn() => \Illuminate\Support\Facades\Cache::tags(['dashboard'])->flush());

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
            database_path('migrations/Forecast Detail'),
            database_path('migrations/Pengiriman Detail'),
        ]);
    }
}
