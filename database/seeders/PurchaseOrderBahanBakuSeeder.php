<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PurchaseOrderBahanBaku;
use App\Models\PurchaseOrder;
use App\Models\BahanBakuKlien;
use Faker\Factory as Faker;

class PurchaseOrderBahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        
        // Get existing purchase orders and bahan baku klien
        $purchaseOrders = PurchaseOrder::all();
        $bahanBakuKlien = BahanBakuKlien::where('status', 'aktif')->get();

        if ($purchaseOrders->isEmpty() || $bahanBakuKlien->isEmpty()) {
            echo "Error: Tidak ada purchase order atau bahan baku klien aktif. Jalankan seeder terkait terlebih dahulu.\n";
            return;
        }

        $totalCreated = 0;

        foreach ($purchaseOrders as $po) {
            // Each PO will have 2-5 different bahan baku items
            $itemCount = $faker->numberBetween(2, 5);
            $selectedBahanBaku = $bahanBakuKlien->random($itemCount);

            foreach ($selectedBahanBaku as $bahanBaku) {
                $jumlah = $faker->randomFloat(2, 10, 100);
                $hargaSatuan = $faker->randomFloat(2, 5000, 50000);
                $totalHarga = $jumlah * $hargaSatuan;

                // Check if combination already exists (due to unique constraint)
                $existing = PurchaseOrderBahanBaku::where('purchase_order_id', $po->id)
                    ->where('bahan_baku_klien_id', $bahanBaku->id)
                    ->first();

                if (!$existing) {
                    PurchaseOrderBahanBaku::create([
                        'purchase_order_id' => $po->id,
                        'bahan_baku_klien_id' => $bahanBaku->id,
                        'jumlah' => $jumlah,
                        'harga_satuan' => $hargaSatuan,
                        'total_harga' => $totalHarga,
                        'created_at' => $po->created_at,
                        'updated_at' => $po->updated_at,
                    ]);

                    $totalCreated++;
                }
            }

            // Update PO totals based on actual bahan baku items
            $poItems = PurchaseOrderBahanBaku::where('purchase_order_id', $po->id)->get();
            $newQtyTotal = $poItems->sum('jumlah');
            $newHppTotal = $poItems->sum('total_harga');
            $newTotalAmount = $newHppTotal * 1.2; // 20% markup

            $po->update([
                'qty_total' => $newQtyTotal,
                'hpp_total' => $newHppTotal,
                'total_amount' => $newTotalAmount,
            ]);
        }

        echo "PurchaseOrderBahanBaku seeding completed! Created {$totalCreated} purchase order items.\n";
        echo "Updated " . $purchaseOrders->count() . " purchase order totals.\n";
    }
}
