<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Call Seeders
        $this->call([
            UserSeeder::class,
            SupplierSeeder::class,
            KlienSeeder::class,
            KontakKlienSeeder::class, // Contact persons for clients
            BahanBakuKlienSeeder::class, // Now creates client-specific materials with pricing
            BahanBakuSupplierSeeder::class,
            CompanySettingSeeder::class,
            RiwayatHargaBahanBakuSeeder::class,
            // PengirimanAccountingSeeder::class,
            PenawaranSeeder::class, // Penawaran with multi-supplier support
            // OrderSeeder::class, // Removed - incompatible with new multi-supplier order system
            EnhancedOrderSeeder::class, // New multi-supplier order system with auto-population
        ]);
    }
}
