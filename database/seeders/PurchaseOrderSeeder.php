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
    // Deterministic seeding: no faker randomness
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

        $klienCount = $kliens->count() ?: 1;
        for ($i = 1; $i <= 25; $i++) {
            $qtyTotal = 100;
            $totalAmount = 1000000;
            $klien = $kliens->get(($i - 1) % $klienCount);
            $status = $statuses[($i - 1) % count($statuses)];
            $spec = $spesifikasi[($i - 1) % count($spesifikasi)];

            PurchaseOrder::create([
                'klien_id' => $klien->id,
                'no_po' => 'PO-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'qty_total' => $qtyTotal,
                'total_amount' => $totalAmount,
                'spesifikasi' => $spec,
                'catatan' => null,
                'status' => $status,
                'created_at' => now()->subDays(30 - ($i % 30)),
                'updated_at' => now()->subDays(15 - ($i % 15)),
            ]);
        }

        echo "PurchaseOrder seeding completed! Created 25 purchase orders.\n";
    }
}
