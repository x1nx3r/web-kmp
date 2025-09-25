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
    // Deterministic seeding: no faker
    $purchaseOrders = PurchaseOrder::all();
    $bahanBakuKlien = BahanBakuKlien::where('status', 'aktif')->orderBy('id')->get();

        if ($purchaseOrders->isEmpty() || $bahanBakuKlien->isEmpty()) {
            echo "Error: Tidak ada purchase order atau bahan baku klien aktif. Jalankan seeder terkait terlebih dahulu.\n";
            return;
        }

        $totalCreated = 0;

        foreach ($purchaseOrders as $po) {
            // Each PO will have 3 items deterministically
            $itemCount = 3;
            $selectedBahanBaku = $bahanBakuKlien->take($itemCount);

            foreach ($selectedBahanBaku as $bahanBaku) {
                $jumlah = 10;
                $hargaSatuan = 20000;
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

         
        }

        echo "PurchaseOrderBahanBaku seeding completed! Created {$totalCreated} purchase order items.\n";
        echo "Updated " . $purchaseOrders->count() . " purchase order totals.\n";
    }
}
