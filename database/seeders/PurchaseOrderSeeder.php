<?php

namespace Database\Seeders;

use App\Models\PurchaseOrderBahanBaku;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\Klien;
use Faker\Factory as Faker;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        
        // Get existing klien and suppliers
        $kliens = Klien::all();
        $po_bahanbaku = PurchaseOrderBahanBaku::all();



        $statuses = ['siap', 'proses', 'selesai', 'gagal'];
        $spesifikasi = [
            'Produk Roti Premium Grade A',
            'Kue Tart Ulang Tahun Custom',
            'Pastry Assorted Mix',
            'Cookies & Crackers Package',
            'Bread Loaves Assorted',
            'Cake Premium Wedding',
            'Donut Variety Pack',
            'Muffin Special Edition'
        ];

        for ($i = 1; $i <= 25; $i++) {
            $qtyTotal = $faker->randomFloat(2, 50, 500);
            $totalAmount = $faker->randomFloat(2, 50000000, 5000000000);

            PurchaseOrder::create([
                'klien_id' => $kliens->random()->id,
                'no_po' => 'PO-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'qty_total' => $qtyTotal,
                'total_amount' => $totalAmount,
                'spesifikasi' => $faker->randomElement($spesifikasi),
                'catatan' => $faker->boolean(60) ? $faker->sentence(10) : null,
                'status' => $faker->randomElement($statuses),
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }

        echo "PurchaseOrder seeding completed! Created 25 purchase orders.\n";
    }
}
