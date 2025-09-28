<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BahanBakuSupplier;
use App\Models\RiwayatHargaBahanBaku;
use Carbon\Carbon;

class RiwayatHargaBahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Ambil semua bahan baku supplier yang ada
        $bahanBakuSuppliers = BahanBakuSupplier::all();

        if ($bahanBakuSuppliers->isEmpty()) {
            $this->command->info('No BahanBakuSupplier found. Please run BahanBakuSupplierSeeder first.');
            return;
        }

        $this->command->info('Creating price history for ' . $bahanBakuSuppliers->count() . ' bahan baku suppliers...');

        foreach ($bahanBakuSuppliers as $bahanBaku) {
            $this->createPriceHistoryForBahanBaku($bahanBaku);
        }

        $this->command->info('RiwayatHargaBahanBaku seeding completed!');
    }

    private function createPriceHistoryForBahanBaku(BahanBakuSupplier $bahanBaku)
    {
        // Hapus riwayat yang sudah ada untuk bahan baku ini
        RiwayatHargaBahanBaku::where('bahan_baku_supplier_id', $bahanBaku->id)->delete();

        $currentPrice = (float) $bahanBaku->harga_per_satuan;

        // Start from 14 days ago to create more data points
        $startDate = Carbon::now()->subDays(14);

        // Create 12 price history entries for better charts
        $totalEntries = 12;
        $priceHistory = [];

        // deterministic pattern of deltas to cycle through (more varied)
        $pattern = [-0.03, 0.02, 0.01, 0.04, -0.02, 0.03, -0.01, 0.02];

        for ($i = 0; $i < $totalEntries; $i++) {
            $date = $startDate->copy()->addDays($i);

            if ($i == 0) {
                // Entry pertama menggunakan harga yang lebih rendah dari harga saat ini (85%)
                $price = round($currentPrice * 0.85, 0);
                $hargaLama = null;
                $tipe = 'awal';
                $keterangan = "Data riwayat awal untuk bahan baku '{$bahanBaku->nama}'";
            } else {
                $hargaLama = $priceHistory[$i - 1]['harga_baru'];
                $delta = $pattern[($i - 1) % count($pattern)];
                if ($delta > 0) {
                    $tipe = 'naik';
                    $keterangan = "Kenaikan harga bahan baku '{$bahanBaku->nama}' sebesar " . number_format($delta * 100, 1) . "%";
                    $price = $hargaLama * (1 + $delta);
                } elseif ($delta < 0) {
                    $tipe = 'turun';
                    $keterangan = "Penurunan harga bahan baku '{$bahanBaku->nama}' sebesar " . number_format(abs($delta) * 100, 1) . "%";
                    $price = $hargaLama * (1 + $delta);
                } else {
                    $tipe = 'tetap';
                    $keterangan = "Harga bahan baku '{$bahanBaku->nama}' tetap stabil";
                    $price = $hargaLama;
                }
            }

            // Pastikan harga tidak negatif dan tidak terlalu ekstrem
            $price = max($price, $currentPrice * 0.6); // Minimal 60% dari harga saat ini
            $price = min($price, $currentPrice * 1.4); // Maksimal 140% dari harga saat ini

            // Round ke rupiah
            $price = round($price, 0);

            $priceHistory[] = [
                'harga_baru' => $price,
                'harga_lama' => $hargaLama,
                'tipe' => $tipe,
                'tanggal' => $date,
                'keterangan' => $keterangan
            ];
        }

        // Pastikan entry terakhir sesuai dengan harga saat ini di database
        $lastEntry = &$priceHistory[$totalEntries - 1];
        if ($lastEntry['harga_baru'] != $currentPrice) {
            // Tambah satu entry lagi untuk menyesuaikan harga saat ini
            $finalDate = $startDate->copy()->addDays($totalEntries);
            $hargaLama = $lastEntry['harga_baru'];

            if ($currentPrice > $hargaLama) {
                $tipe = 'naik';
                $keterangan = "Penyesuaian harga naik bahan baku '{$bahanBaku->nama}' ke harga saat ini";
            } elseif ($currentPrice < $hargaLama) {
                $tipe = 'turun';
                $keterangan = "Penyesuaian harga turun bahan baku '{$bahanBaku->nama}' ke harga saat ini";
            } else {
                $tipe = 'tetap';
                $keterangan = "Harga bahan baku '{$bahanBaku->nama}' tetap pada harga saat ini";
            }

            $priceHistory[] = [
                'harga_baru' => $currentPrice,
                'harga_lama' => $hargaLama,
                'tipe' => $tipe,
                'tanggal' => $finalDate,
                'keterangan' => $keterangan
            ];
        }

        // Insert semua data ke database
        foreach ($priceHistory as $history) {
            $selisih = $history['harga_lama'] ? abs($history['harga_baru'] - $history['harga_lama']) : 0;
            $persentase = $history['harga_lama'] && $history['harga_lama'] > 0 ?
                (($history['harga_baru'] - $history['harga_lama']) / $history['harga_lama']) * 100 : 0;

            RiwayatHargaBahanBaku::create([
                'bahan_baku_supplier_id' => $bahanBaku->id,
                'harga_lama' => $history['harga_lama'],
                'harga_baru' => $history['harga_baru'],
                'selisih_harga' => $selisih,
                'persentase_perubahan' => round($persentase, 2),
                'tipe_perubahan' => $history['tipe'],
                'keterangan' => $history['keterangan'],
                'tanggal_perubahan' => $history['tanggal'],
                'created_at' => $history['tanggal'],
                'updated_at' => $history['tanggal'],
            ]);
        }

        $this->command->info("Created " . count($priceHistory) . " price history entries for: {$bahanBaku->nama}");
    }
}
