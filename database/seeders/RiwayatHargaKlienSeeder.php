<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanBakuKlien;
use App\Models\RiwayatHargaKlien;
use App\Models\User;
use Carbon\Carbon;

class RiwayatHargaKlienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates semi-dummy price history for client-specific materials
     * (BahanBakuKlien). It will skip materials that already have history to avoid
     * duplicating data when re-run.
     */
    public function run()
    {
        $this->command->info('Starting RiwayatHargaKlien seeder...');

        // Pick a marketing user to attribute the changes to (fallback to id=1)
        $marketingUser = User::first();
        $marketingId = $marketingUser->id ?? 1;

    // Target client-specific materials only, deterministic order
    $materials = BahanBakuKlien::whereNotNull('klien_id')->orderBy('id')->limit(300)->get();

        $count = 0;
        foreach ($materials as $material) {
            // Skip if there is already history for this material
            if ($material->riwayatHarga()->count() > 0) {
                continue;
            }

            // Determine a base price: prefer existing approved price, otherwise use a sensible default
            $basePrice = $material->harga_approved ? floatval($material->harga_approved) : 10000;

            // Deterministic 4-point history (older -> newer)
            $points = 4;
            $prices = [];

            // Start older price = 90% of base
            $prices[0] = max(100, round($basePrice * 0.9, 0));
            // deterministic changes: -5%, +3%, +2%
            $deltas = [-0.05, 0.03, 0.02];
            for ($i = 1; $i < $points; $i++) {
                $deltaPercent = $deltas[$i - 1];
                $prices[$i] = max(100, round($prices[$i - 1] * (1 + $deltaPercent), 0));
            }

            // Create history records (oldest first)
            for ($i = 0; $i < $points; $i++) {
                $hargaBaru = $prices[$i];
                $hargaLama = $i > 0 ? $prices[$i - 1] : 0;
                $selisih = $hargaBaru - $hargaLama;
                $persen = $hargaLama > 0 ? ($selisih / $hargaLama) * 100 : 0;

                if ($hargaLama == 0) {
                    $tipe = 'awal';
                } elseif ($selisih > 0) {
                    $tipe = 'naik';
                } elseif ($selisih < 0) {
                    $tipe = 'turun';
                } else {
                    $tipe = 'tetap';
                }

                // Deterministic timestamps: spread across last N days
                $daysAgo = $points - $i;
                $tanggal = Carbon::now()->subDays($daysAgo)->subHours(2);

                RiwayatHargaKlien::create([
                    'bahan_baku_klien_id' => $material->id,
                    'harga_lama' => $hargaLama,
                    'harga_approved_baru' => $hargaBaru,
                    'selisih_harga' => $selisih,
                    'persentase_perubahan' => round($persen, 4),
                    'tipe_perubahan' => $tipe,
                    'keterangan' => $i === 0 ? 'Initial dummy entry' : 'Auto-generated dummy change',
                    'tanggal_perubahan' => $tanggal,
                    'updated_by_marketing' => $marketingId,
                ]);
            }

            // Update material to latest price
            $latestPrice = end($prices);
            $material->harga_approved = $latestPrice;
            $material->approved_at = Carbon::now();
            $material->approved_by_marketing = $marketingId;
            $material->save();

            $count++;
        }

        $this->command->info("RiwayatHargaKlien seeding completed: created history for {$count} materials.");
    }
}
