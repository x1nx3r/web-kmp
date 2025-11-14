<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanBakuSupplier;
use App\Models\RiwayatHargaBahanBaku;
use App\Models\User;
use Carbon\Carbon;

class RiwayatHargaBahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates semi-dummy price history for supplier materials
     * (BahanBakuSupplier). It will skip materials that already have history to avoid
     * duplicating data when re-run.
     */
    public function run()
    {
        $this->command->info('Starting RiwayatHargaBahanBaku seeder...');

        // Pick a purchasing user to attribute the changes to (fallback to id=1)
        $purchasingUser = User::first();
        $purchasingId = $purchasingUser->id ?? 1;

        // Target supplier materials only, deterministic order
        $materials = BahanBakuSupplier::whereNotNull('supplier_id')
            ->whereNotNull('harga_per_satuan')
            ->where('harga_per_satuan', '>', 0)
            ->orderBy('id')
            ->limit(300)
            ->get();

        $count = 0;
        foreach ($materials as $material) {
            // Skip if there is already history for this material
            if ($material->riwayatHarga()->count() > 0) {
                continue;
            }

            // Determine a base price: use current harga_per_satuan
            $basePrice = floatval($material->harga_per_satuan);

            // Create only initial price history entry
            $tanggal = Carbon::now()->subDays(rand(1, 7))->subHours(rand(1, 12));
            
            $keterangan = "Data awal riwayat harga untuk '{$material->nama}' dari supplier '{$material->supplier->nama}'";

            RiwayatHargaBahanBaku::create([
                'bahan_baku_supplier_id' => $material->id,
                'harga_lama' => null, // null for initial entry
                'harga_baru' => $basePrice,
                'selisih_harga' => $basePrice, // selisih sama dengan harga baru karena harga lama null
                'persentase_perubahan' => 0, // 0% untuk entry awal
                'tipe_perubahan' => 'awal',
                'keterangan' => $keterangan,
                'tanggal_perubahan' => $tanggal,
                'updated_by' => $purchasingId,
            ]);

            $count++;
        }

        $this->command->info("RiwayatHargaBahanBaku seeding completed: created history for {$count} materials.");
    }
}
