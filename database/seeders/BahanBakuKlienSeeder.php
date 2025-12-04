<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BahanBakuKlien;
use App\Models\RiwayatHargaKlien;
use App\Models\Klien;
use App\Models\User;

class BahanBakuKlienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get marketing user for approval
        $marketingUser = User::where('email', 'marketing@kmp.com')->first() 
                        ?? User::where('role', 'marketing')->first() 
                        ?? User::first();

        if (!$marketingUser) {
            echo "No marketing user found! Please run UserSeeder first.\n";
            return;
        }

        // Get all clients
        $kliens = Klien::all();
        
        if ($kliens->isEmpty()) {
            echo "No clients found! Please run KlienSeeder first.\n";
            return;
        }

        // Complete list of all bahan baku materials that every client should have
        $allMaterials = [
            'Mie Kuning' => ['satuan' => 'kg', 'price_range' => [2500, 4500]],
            'Mie Merah' => ['satuan' => 'kg', 'price_range' => [3000, 5000]],
            'Tepung Biskuit' => ['satuan' => 'kg', 'price_range' => [3000, 6000]],
            'Katul' => ['satuan' => 'kg', 'price_range' => [3000, 5000]],
            'Bone Meal' => ['satuan' => 'kg', 'price_range' => [8000, 15000]],
            'CFM/FTM' => ['satuan' => 'kg', 'price_range' => [12000, 20000]],
            'Garam' => ['satuan' => 'kg', 'price_range' => [3000, 8000]],
            'Molases' => ['satuan' => 'kg', 'price_range' => [2000, 4000]],
            'SHM' => ['satuan' => 'kg', 'price_range' => [15000, 25000]],
            'DSS' => ['satuan' => 'kg', 'price_range' => [8000, 15000]],
            'Menir' => ['satuan' => 'kg', 'price_range' => [4000, 8000]],
            'Copra' => ['satuan' => 'kg', 'price_range' => [4000, 8000]],
            'PKD (Palm Kernel Dehulized)' => ['satuan' => 'kg', 'price_range' => [6000, 12000]],
            'Biji Batu' => ['satuan' => 'kg', 'price_range' => [1000, 3000]],
            'Tepung Batu' => ['satuan' => 'kg', 'price_range' => [2000, 4000]],
            'PKM' => ['satuan' => 'kg', 'price_range' => [5000, 10000]],
            'Corn Gem' => ['satuan' => 'kg', 'price_range' => [8000, 15000]],
            'Cangkang Kemiri' => ['satuan' => 'kg', 'price_range' => [1500, 3500]],
            'Tepung Gaplek' => ['satuan' => 'kg', 'price_range' => [3000, 6000]],
            'Kebi' => ['satuan' => 'kg', 'price_range' => [4000, 8000]],
            'Fish Meal' => ['satuan' => 'kg', 'price_range' => [15000, 30000]],
            'MBM (Meat Bone Meal)' => ['satuan' => 'kg', 'price_range' => [12000, 22000]],
            'Poultry Meal' => ['satuan' => 'kg', 'price_range' => [10000, 20000]],
            'Gaplek Chip' => ['satuan' => 'kg', 'price_range' => [2500, 5000]],
            'Kepala Udang Utuh' => ['satuan' => 'kg', 'price_range' => [8000, 18000]],
            'Tepung Roti/Bread Waste' => ['satuan' => 'kg', 'price_range' => [2000, 4000]],
            'SG (Sekam Giling)' => ['satuan' => 'kg', 'price_range' => [800, 2000]],
            'Bran Gluten Feed (BGF)' => ['satuan' => 'kg', 'price_range' => [4000, 8000]],
            'CGM' => ['satuan' => 'kg', 'price_range' => [6000, 12000]],
            'CGF' => ['satuan' => 'kg', 'price_range' => [5000, 10000]],
            'Tepung Industri' => ['satuan' => 'kg', 'price_range' => [15000, 25000]],
            'CPO' => ['satuan' => 'liter', 'price_range' => [12000, 18000]],
            'Ampok' => ['satuan' => 'kg', 'price_range' => [2000, 4000]]
        ];

        $materialCount = 0;

        foreach ($kliens as $klien) {
            echo "Creating ALL materials for {$klien->nama} ({$klien->cabang})\n";
            
            $materialIndex = 0;

            // Create ALL materials for this client
            foreach ($allMaterials as $materialName => $config) {
                // Use material name as display name
                $displayName = $materialName;
                $specification = ''; // Empty specification
                $satuan = $config['satuan'] ?? 'kg';
                $priceRange = $config['price_range'] ?? [1000, 10000];

                // Create client-specific material with deterministic price (midpoint)
                $approvedPrice = intval(($priceRange[0] + $priceRange[1]) / 2);

                // Spread dates deterministically so data covers a wider time range
                $historyStep = 2; // days between history points
                $extraPoints = 5; // number of history updates
                $baseStartDays = 45 + ($klien->id % 20); // base days in the past per client
                $startDays = $baseStartDays + ($materialIndex * 1); // add spacing per material index

                // Initial approved_at placed earlier than the first history point
                $initialDays = $startDays + ($extraPoints * $historyStep);
                $approvedAt = now()->subDays($initialDays);

                $bahanBakuKlien = BahanBakuKlien::create([
                    'klien_id' => $klien->id,
                    'nama' => $displayName,
                    'satuan' => $satuan,
                    'spesifikasi' => $specification,
                    'harga_approved' => $approvedPrice,
                    'approved_at' => $approvedAt,
                    'approved_by_marketing' => $marketingUser->id,
                    'status' => 'aktif',
                ]);

                // Create deterministic initial price history and several updates
                RiwayatHargaKlien::createPriceHistory(
                    $bahanBakuKlien->id,
                    $approvedPrice,
                    $marketingUser->id,
                    "Harga awal untuk klien {$klien->nama}",
                    $approvedAt
                );

                $currentPrice = $approvedPrice;
                $patterns = [-0.02, 0.015, 0.01, 0.03, -0.015]; // Varied price changes
                for ($p = 0; $p < $extraPoints; $p++) {
                    // Use deterministic pattern for varied price changes
                    $pct = $patterns[$p % count($patterns)];
                    $newPrice = max(100, round($currentPrice * (1 + $pct), 0));

                    // Calculate history timestamp: progress forward from older to newer
                    $days = $startDays + ($extraPoints - $p - 1) * $historyStep;
                    $historyAt = now()->subDays($days);

                    $bahanBakuKlien->update([
                        'harga_approved' => $newPrice,
                        'approved_at' => $historyAt
                    ]);

                    RiwayatHargaKlien::createPriceHistory(
                        $bahanBakuKlien->id,
                        $newPrice,
                        $marketingUser->id,
                        "Price update for {$klien->nama}",
                        $historyAt
                    );

                    $currentPrice = $newPrice;
                }

                $materialCount++;
                $materialIndex++;
            }
        }

        echo "BahanBakuKlien seeding completed!\n";
        echo "Created {$materialCount} client-specific materials with approved pricing.\n";
        echo "Price history records created for all materials.\n";
    }
}
