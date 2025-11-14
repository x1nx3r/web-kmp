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
            BahanBakuKlienSeeder::class, // Now creates client-specific materials with pricing
            BahanBakuSupplierSeeder::class,
            RiwayatHargaBahanBakuSeeder::class,
            // RiwayatHargaKlienSeeder::class, // Price history created in BahanBakuKlienSeeder
            PurchaseOrderSeeder::class,
            PurchaseOrderBahanBakuSeeder::class,
            CompanySettingSeeder::class,
            // PengirimanAccountingSeeder::class, // Old seeder
            ApprovalAccountingSeeder::class, // New comprehensive seeder with items
        ]);
    }
}
