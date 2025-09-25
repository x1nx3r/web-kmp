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

        // Hardcoded mapping derived from mapping_output.csv to provide factual spec/pabrik data
        // Keys are normalized using normalizeForMatch()
        $mapping = [
            'mie kuning' => ['material' => 'Mie Kuning', 'matched_name' => 'Mie Kuning', 'pabrik' => 'PT. CJ Feed', 'spec' => "Moisture <12%\nProtein >8%\nFat > 12%\nAsh < 7%\nFiber < 2%"],
            'mie merah' => ['material' => 'Mie Merah', 'matched_name' => 'Mie Merah', 'pabrik' => 'PT. Haida Agriculture', 'spec' => "Moist <5%\nC-P >7%\nFat > 23%\nAsh <3%\nBubuk sesuai warna bahan\nBerbau normal dan tidak ada warna campuran lain"],
            'tepung biskuit' => ['material' => 'Tepung Biskuit', 'matched_name' => 'Tepung Biskuit', 'pabrik' => 'PT. Charoen Pokphand Indonesia (Krian)', 'spec' => "Moist (Refraksi 25)\nC-P (Refraksi 25)\nFiber (Refraksi 25)\nFat (Refraksi 5)\nAsh < 2% ( 2-3% Refraksi 25; 3-4% Refraksi 50)"],
            'katul' => ['material' => 'Katul', 'matched_name' => 'Katul', 'pabrik' => 'PT. Dinamika Megatama Citra', 'spec' => "Moisture < 11%\nProtein > 12%\nFiber < 10%\nFat > 12%"],
            'bone meal' => ['material' => 'Bone Meal', 'matched_name' => 'Bone Meal', 'pabrik' => 'PT. Sreeya Sewu Indonesia (Wonoayu)', 'spec' => "Protein >20%\nCalsium < 26%\nPhospor >9%\nMoisture <12%\nAsh <6%\nFree of mold, insect, foreign material\nColour light green to white \nOdor specific"],
            'cfm/ftm' => ['material' => 'CFM/FTM', 'matched_name' => 'CFM', 'pabrik' => 'PT Charoen Pokphand Indonesia (Cikande)', 'spec' => "Moist < 10% (Reject >12%)\nC-P > 80% (Reject <75%)\nPepsin Digestibility (0,2%) >75% (Reject <68%)\nAsh < 5% (Reject >10%)\nFat <12% (Reject >12%)\nFiber < 3% (Reject >3%)\nTekture >2.0mm, Max 2%\nFine Pass 1.00mm, Min 80%"],
            'garam' => ['material' => 'Garam', 'matched_name' => 'Garam Halus', 'pabrik' => 'PT. Mulia Harvest', 'spec' => "Anticaking\nM <1%\nNaCl >95,00\nMicroscopy: No Lumpiness\nPartikel Size: Min 95.00"],
            'molases' => ['material' => 'Molases', 'matched_name' => 'Molases', 'pabrik' => 'PT. Central Proteina Prima', 'spec' => "KA <20%\nBrix >75\nWarna: Coklat mengarah ke Coklat kehitaman"],
            'shm' => ['material' => 'SHM', 'matched_name' => 'SHM', 'pabrik' => 'PT. Pasuruan Evergreen Indonesia', 'spec' => "Moist <12%\nC-P >45\nFat <5%\nSH <22\nTvBN < 100\nKulit kepala udang cacah"],
            'dss' => ['material' => 'DSS', 'matched_name' => 'DSS', 'pabrik' => 'PT. Central Proteina Prima', 'spec' => "Moist <12%\nC-P > 33\nTvBN < 100"],
            'menir' => ['material' => 'Menir', 'matched_name' => 'Menir', 'pabrik' => 'PT. Thai Union Kharisma Lestari', 'spec' => "Moist < 12%\nC-P > 6%\nMilky White/Light Yelow\nFresh, Not Musty\nSmall Grain"],
            'copra' => ['material' => 'Copra', 'matched_name' => 'Bungkil Copra', 'pabrik' => 'PT. Cargill', 'spec' => "CP >18%\nMoist <13%\nFat 8-20%\nFiber (ADF 27%) <16%"],
            'pkd' => ['material' => 'PKD', 'matched_name' => 'PKD', 'pabrik' => 'PT. Sreeya Sewu Indonesia (Wonoayu)', 'spec' => "Moisture < 12%\nC-P > 25%\nFree of Mold,insect,foreign material\nColour brown\nOdor specific PKD and free sour odor"],
            'tepun batu' => ['material' => 'Tepung Batu', 'matched_name' => 'Tepung Biskuit', 'pabrik' => 'PT. Charoen Pokphand Indonesia (Krian)', 'spec' => "Moist (Refraksi 25)\nC-P (Refraksi 25)\nFiber (Refraksi 25)\nFat (Refraksi 5)\nAsh < 2% ( 2-3% Refraksi 25; 3-4% Refraksi 50)"],
            'pkm' => ['material' => 'PKM', 'matched_name' => 'PKM', 'pabrik' => 'PT. CJ Feed', 'spec' => "Moist < 12%\nC-P > 13%\nFat > 7%\nFiber < 20%\nAsh < 5%\nWarna Coklat\nBatok#10 + #18: <6%"],
            'corn gem' => ['material' => 'Corn Gem', 'matched_name' => 'Corn Germ', 'pabrik' => 'PT. Wonokoyo Jaya Corp', 'spec' => "Moist 0.00 - 10.00%\nC-P 10.00 - 100.00%\nFat 40.00 - 100.00%\nFiber 0.00 - 100.00%\nAsh 0.00 - 100.00\nCampuran Bahan Lain: Tidak ada\nSelisih Suhu (C) 0.00-15.00\nJamur: Tidak Berjamur\nHama (ekor) 0.00 - 0.00 (Refraksi 0,583941605%)\nBerat Jenis (gr/I) 400.00 - 600.00\nBau Normal: Normal"],
            'tepun gaplek' => ['material' => 'Tepung Gaplek', 'matched_name' => 'Tepung Biskuit', 'pabrik' => 'PT. Charoen Pokphand Indonesia (Krian)', 'spec' => "Moist (Refraksi 25)\nC-P (Refraksi 25)\nFiber (Refraksi 25)\nFat (Refraksi 5)\nAsh < 2% ( 2-3% Refraksi 25; 3-4% Refraksi 50)"],
            'kebi' => ['material' => 'Kebi', 'matched_name' => 'Kebi', 'pabrik' => 'PT. Sreeya Sewu Indonesia (Wonoayu)', 'spec' => "Moist <14%\nC-P >12%\nAsh <7%\nFiber <2%\nTemperatur <40º\nBau Beras\nTidak menggumpal\nTidak Jamuran\nTidak Basah\nTidak Asam"],
            'fish meal' => ['material' => 'Fish Meal', 'matched_name' => 'Bone Meal', 'pabrik' => 'PT. Sreeya Sewu Indonesia (Wonoayu)', 'spec' => "Protein >20%\nCalsium < 26%\nPhospor >9%\nMoisture <12%\nAsh <6%\nFree of mold, insect, foreign material\nColour light green to white \nOdor specific"],
            'mbm' => ['material' => 'MBM', 'matched_name' => 'MBM', 'pabrik' => 'PT. Sreeya Sewu Indonesia (Wonoayu)', 'spec' => "Moisture [%] < 10:00\nCrude Protein [%] > 5000\n Fat [%] < 12:00\nAsh [%] < 3500\nCalsium [%] 2,2 x P\nPhosphor  [%] > 4:00\nWool and hair [%] < 1:00\nSalmonella/E.Coli negative\nGrind 95% pass through 2.0 mm screen (tolerance 5%) 100 % pass through 8.0 mm (tolerance 2%)\nFree of mold, insect, foreign (blood meal / feather meal )\nColour light brown to brown\nOdor specific MBM, free rancidity and burnt odor"],
            'poultry meal' => ['material' => 'Poultry Meal', 'matched_name' => 'Bone Meal', 'pabrik' => 'PT. Sreeya Sewu Indonesia (Wonoayu)', 'spec' => "Protein >20%\nCalsium < 26%\nPhospor >9%\nMoisture <12%\nAsh <6%\nFree of mold, insect, foreign material\nColour light green to white \nOdor specific"],
            'gaplek chip' => ['material' => 'Gaplek Chip', 'matched_name' => 'Gaplek Chip', 'pabrik' => 'PT. Dinamika Megatama Citra', 'spec' => "KA max 12%\nProtein min 3,3%"],
            'tepung roti bread waste' => ['material' => 'Tepung Roti/Bread  Waste', 'matched_name' => 'Tepung Biskuit', 'pabrik' => 'PT. Charoen Pokphand Indonesia (Krian)', 'spec' => "Moist (Refraksi 25)\nC-P (Refraksi 25)\nFiber (Refraksi 25)\nFat (Refraksi 5)\nAsh < 2% ( 2-3% Refraksi 25; 3-4% Refraksi 50)"],
            'bran gluten feed bgf' => ['material' => 'Bran Gluten Feed (BGF)', 'matched_name' => 'Corn Bran', 'pabrik' => 'PT. Sreeya Sewu Indonesia (Wonoayu)', 'spec' => "Moist <12\nC-P > 8\nFat > 4\nFiber < 6\nFree of Mold, insect, foreign Material\nColour yellow to tight yellow\nOdor spesific, Free Sour odor and rancidity"],
            'cgm' => ['material' => 'CGM', 'matched_name' => 'CGM', 'pabrik' => 'PT. Sreeya Sewu Indonesia (Wonoayu)', 'spec' => "Moist <10% \nC-P > 10% \nFat > 38% \nFiber < 10%\nAflatoxin < 50%\nNo Admix by Microscopy\nNo Moldy\nNo Insect"],
            'cgf' => ['material' => 'CGF', 'matched_name' => 'CGF', 'pabrik' => 'PT. Wonokoyo Jaya Corp', 'spec' => "Moisture (%) 0.00 - 12.00\nProtein (%) 18.00 - 100.00\nXhanthophyl 0.00 - 100.00\nFiber (%) 0.00 - 100.00\nAbu (%) 0.00 - 100.00\nFat (%) 0.00 - 100.00\nTekstur: Tidak Ada Gumpalan\nKotoran: Tidak ada Kotoran\nJamur: Tidak Berjamur\nHama (Ekor) 0.00 - 0.00\nBerat Jenis (gr/I) 300.00 - 550.00\nBau: Normal"],
            'tepung industri' => ['material' => 'Tepung Industri', 'matched_name' => 'Tepung Biskuit', 'pabrik' => 'PT. Charoen Pokphand Indonesia (Krian)', 'spec' => "Moist (Refraksi 25)\nC-P (Refraksi 25)\nFiber (Refraksi 25)\nFat (Refraksi 5)\nAsh < 2% ( 2-3% Refraksi 25; 3-4% Refraksi 50)"],
            // entries with missing spec/pabrik left empty
            'biji batu' => ['material' => 'Biji Batu', 'matched_name' => '', 'pabrik' => '', 'spec' => ''],
            'cangkang kemiri' => ['material' => 'Cangkang Kemiri', 'matched_name' => '', 'pabrik' => '', 'spec' => ''],
            'kepala udang utuh' => ['material' => 'Kepala Udang Utuh', 'matched_name' => '', 'pabrik' => '', 'spec' => ''],
            'sg sekam giling' => ['material' => 'SG (Sekam Giling)', 'matched_name' => '', 'pabrik' => '', 'spec' => ''],
            'cpo' => ['material' => 'CPO', 'matched_name' => '', 'pabrik' => '', 'spec' => ''],
            'ampok' => ['material' => 'Ampok', 'matched_name' => '', 'pabrik' => '', 'spec' => ''],
            'marketing' => ['material' => 'Marketing', 'matched_name' => '', 'pabrik' => '', 'spec' => ''],
            'purchasing' => ['material' => 'Purchasing', 'matched_name' => '', 'pabrik' => '', 'spec' => ''],
        ];

        // Material templates with different specifications per client type
        $materialTemplates = [
            // Bakery materials
            'bakery' => [
                'Tepung Terigu' => ['satuan' => 'kg', 'price_range' => [25000, 35000]],
                'Gula Pasir' => ['satuan' => 'kg', 'price_range' => [12000, 18000]],
                'Mentega' => ['satuan' => 'kg', 'price_range' => [45000, 60000]],
                'Telur Ayam' => ['satuan' => 'kg', 'price_range' => [28000, 35000]],
                'Susu Bubuk' => ['satuan' => 'kg', 'price_range' => [80000, 120000]],
                'Baking Powder' => ['satuan' => 'kg', 'price_range' => [15000, 25000]],
                'Vanilla Extract' => ['satuan' => 'ml', 'price_range' => [150, 250]],
            ],
            
            // Food manufacturing materials  
            'food' => [
                'Tepung Terigu' => ['satuan' => 'kg', 'price_range' => [22000, 32000]],
                'Minyak Goreng' => ['satuan' => 'liter', 'price_range' => [18000, 25000]],
                'Gula Pasir' => ['satuan' => 'kg', 'price_range' => [11000, 16000]],
                'Garam Halus' => ['satuan' => 'kg', 'price_range' => [8000, 12000]],
                'Telur Ayam' => ['satuan' => 'kg', 'price_range' => [26000, 33000]],
                'Cokelat Chips' => ['satuan' => 'kg', 'price_range' => [65000, 85000]],
            ],
            
            // Feed/Animal nutrition materials
            'feed' => [
                'Tepung Terigu' => ['satuan' => 'kg', 'price_range' => [20000, 28000]],
                'Susu Bubuk' => ['satuan' => 'kg', 'price_range' => [70000, 100000]],
                'Keju Parut' => ['satuan' => 'kg', 'price_range' => [85000, 110000]],
                'Minyak Goreng' => ['satuan' => 'liter', 'price_range' => [16000, 22000]],
            ]
        ];

        $clientTypes = ['bakery', 'food', 'feed'];
        $materialCount = 0;

        // Build Klien lookup by normalized name for matching mapping pabrik -> klien id
        $klienLookup = [];
        foreach ($kliens as $k) {
            $key = $this->normalizeForMatch($k->nama);
            if (!isset($klienLookup[$key])) $klienLookup[$key] = [];
            $klienLookup[$key][] = $k->id;
        }

        foreach ($kliens as $klien) {
            // Assign client type based on name patterns
            $clientType = 'food'; // default
            if (str_contains(strtolower($klien->nama), 'feed')) {
                $clientType = 'feed';
            } elseif (str_contains(strtolower($klien->nama), 'bakery') || str_contains(strtolower($klien->nama), 'roti')) {
                $clientType = 'bakery';
            }
            
            // Build factual materials list from mapping filtered by current Klien's pabrik (when possible)
            // Skip rows that are not actual materials (empty matched_name or non-material rows)
            $available = [];
            $klienNorm = $this->normalizeForMatch($klien->nama);
            foreach ($mapping as $key => $m) {
                $name = $m['matched_name'] ?: $m['material'];
                if (!$name) continue;
                $low = mb_strtolower(trim($name));
                if (in_array($low, ['marketing','purchasing'])) continue;

                // If mapping provides a pabrik, only include it if it matches this klien (or is empty)
                $pabrik = trim($m['pabrik'] ?? '');
                if ($pabrik !== '') {
                    $pabrikNorm = $this->normalizeForMatch($pabrik);
                    // match if names are equal or one contains the other
                    if ($pabrikNorm === $klienNorm || strpos($pabrikNorm, $klienNorm) !== false || strpos($klienNorm, $pabrikNorm) !== false) {
                        $available[$key] = $m;
                    } else {
                        // not matching this client, skip
                        continue;
                    }
                } else {
                    // no pabrik provided — include as general material
                    $available[$key] = $m;
                }
            }

            // If after filtering there are no available mapped materials for this client, fall back to templates
            $useTemplates = empty($available);

            echo "Creating materials for {$klien->nama} ({$klien->cabang}) - Type: {$clientType}\n";

            // Choose how many materials to create per client deterministically (up to 8)
            $numMaterials = $useTemplates ? min(8, count($materialTemplates[$clientType])) : min(8, count($available));

            if ($useTemplates) {
                $chosenMaterials = array_keys($materialTemplates[$clientType]);
            } else {
                // pick random keys from available
                $keys = array_keys($available);
                // deterministic: take the first N keys rather than shuffling
                $chosenKeys = array_slice($keys, 0, min($numMaterials, count($keys)));
            }

            if ($useTemplates) {
                // limit chosen templates deterministically
                $chosenMaterials = array_slice(array_keys($materialTemplates[$clientType]), 0, $numMaterials);
                foreach ($chosenMaterials as $idx => $materialName) {
                    $config = $materialTemplates[$clientType][$materialName];
                    // If a mapping exists for this material name, prefer the mapped spec/pabrik
                    $norm = $this->normalizeForMatch($materialName);
                    $mapped = $mapping[$norm] ?? null;

                    // Determine display name and specification
                    $displayName = $materialName;
                    $specification = $this->generateSpecification($materialName, $klien->nama, $clientType);
                    $satuan = $config['satuan'] ?? 'kg';
                    $priceRange = $config['price_range'] ?? [1000, 10000];

                    if ($mapped) {
                        if (!empty($mapped['matched_name'])) {
                            $displayName = $mapped['matched_name'];
                        }
                        if (!empty($mapped['spec'])) {
                            $specification = trim($mapped['spec']);
                        }
                        // If pabrik present, append it to the specification for traceability
                        if (!empty($mapped['pabrik'])) {
                            $specification .= "\nPabrik: " . $mapped['pabrik'];
                        }

                        // Try to supply sensible price ranges for common feed materials
                        $lower = 2000; $upper = 20000;
                        $commonRanges = [
                            'mie' => [2000, 7000],
                            'tepung' => [20000, 40000],
                            'bone' => [8000, 20000],
                            'cfm' => [8000, 22000],
                            'garam' => [3000, 8000],
                            'molases' => [2000, 8000],
                            'copra' => [4000, 12000],
                            'pkd' => [6000, 14000],
                            'mbm' => [8000, 18000],
                            'fish' => [15000, 45000],
                        ];
                        foreach ($commonRanges as $k => $rng) {
                            if (strpos(mb_strtolower($displayName), $k) !== false) { $lower = $rng[0]; $upper = $rng[1]; break; }
                        }
                        $priceRange = [$lower, $upper];
                    }

                    // Create client-specific material with deterministic price (midpoint)
                    $approvedPrice = intval(($priceRange[0] + $priceRange[1]) / 2);
                    // deterministic approved_at per material index
                    $approvedAt = now()->subDays(10 + ($idx % 7));

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

                    // Create deterministic initial price history and two deterministic updates
                    RiwayatHargaKlien::createPriceHistory(
                        $bahanBakuKlien->id,
                        $approvedPrice,
                        $marketingUser->id,
                        "Harga awal untuk klien {$klien->nama}"
                    );

                    $currentPrice = $approvedPrice;
                    $extraPoints = 2;
                    for ($p = 0; $p < $extraPoints; $p++) {
                        // deterministic percent changes: alternate -5% then +3%
                        $pct = ($p % 2 === 0) ? -0.05 : 0.03;
                        $newPrice = max(100, round($currentPrice * (1 + $pct), 0));
                        $bahanBakuKlien->update([
                            'harga_approved' => $newPrice,
                            'approved_at' => now()->subDays(8 + $p)
                        ]);

                        RiwayatHargaKlien::createPriceHistory(
                            $bahanBakuKlien->id,
                            $newPrice,
                            $marketingUser->id,
                            "Auto-generated update for seeding"
                        );

                        $currentPrice = $newPrice;
                    }

                    $materialCount++;
                }
            } else {
                // Create from mapping selection
                foreach ($chosenKeys as $ck) {
                    $m = $available[$ck];
                    $displayName = $m['matched_name'] ?: $m['material'];
                    $specification = trim($m['spec'] ?? '') ?: $this->generateSpecification($displayName, $klien->nama, $clientType);
                    if (!empty($m['pabrik'])) {
                        $specification .= "\nPabrik: " . $m['pabrik'];
                    }

                    // pick satuan heuristically
                    $satuan = 'kg';
                    $ln = mb_strtolower($displayName);
                    if (str_contains($ln, 'liter') || str_contains($ln, 'minyak') || str_contains($ln, 'oil')) $satuan = 'liter';
                    if (str_contains($ln, 'ml') || str_contains($ln, 'extract')) $satuan = 'ml';

                    // price ranges heuristics
                    $lower = 2000; $upper = 20000;
                    if (str_contains($ln, 'mie') || str_contains($ln, 'biskuit')) { $lower = 2000; $upper = 7000; }
                    if (str_contains($ln, 'tepung') || str_contains($ln, 'terigu')) { $lower = 20000; $upper = 40000; }
                    if (str_contains($ln, 'bone') || str_contains($ln, 'mbm') || str_contains($ln, 'meal')) { $lower = 8000; $upper = 20000; }
                    if (str_contains($ln, 'garam')) { $lower = 3000; $upper = 8000; }

                    // deterministic approved price = midpoint
                    $approvedPrice = intval(($lower + $upper) / 2);

                    $bahanBakuKlien = BahanBakuKlien::create([
                        'klien_id' => $klien->id,
                        'nama' => $displayName,
                        'satuan' => $satuan,
                        'spesifikasi' => $specification,
                        'harga_approved' => $approvedPrice,
                        'approved_at' => now()->subDays(12),
                        'approved_by_marketing' => $marketingUser->id,
                        'status' => 'aktif',
                    ]);

                    // Create initial history and deterministic updates for this material
                    RiwayatHargaKlien::createPriceHistory(
                        $bahanBakuKlien->id,
                        $approvedPrice,
                        $marketingUser->id,
                        "Harga awal untuk klien {$klien->nama}"
                    );

                    $currentPrice = $approvedPrice;
                    $extraPoints = 2;
                    for ($p = 0; $p < $extraPoints; $p++) {
                        // deterministic percent changes: alternate -5% then +3%
                        $pct = ($p % 2 === 0) ? -0.05 : 0.03;
                        $newPrice = max(100, round($currentPrice * (1 + $pct), 0));

                        // Update material to new price and record history
                        $bahanBakuKlien->update([
                            'harga_approved' => $newPrice,
                            'approved_at' => now()->subDays(6 + $p)
                        ]);

                        RiwayatHargaKlien::createPriceHistory(
                            $bahanBakuKlien->id,
                            $newPrice,
                            $marketingUser->id,
                            "Auto-generated update for seeding"
                        );

                        $currentPrice = $newPrice;
                    }

                    $materialCount++;
                }
            }
        }

        echo "BahanBakuKlien seeding completed!\n";
        echo "Created {$materialCount} client-specific materials with approved pricing.\n";
        echo "Price history records created for all materials.\n";
    }

    private function generateSpecification($materialName, $clientName, $clientType)
    {
        $specifications = [
            'Tepung Terigu' => [
                'bakery' => 'Tepung terigu protein tinggi (>12%) untuk produksi roti dan kue',
                'food' => 'Tepung terigu protein sedang untuk produksi makanan olahan',
                'feed' => 'Tepung terigu grade feed untuk nutrisi pakan ternak'
            ],
            'Gula Pasir' => [
                'bakery' => 'Gula pasir kristal halus, warna putih bersih untuk bakery',
                'food' => 'Gula pasir SNI, tidak beranti untuk industri makanan',
                'feed' => 'Gula pasir grade industri untuk pakan'
            ],
            'Minyak Goreng' => [
                'bakery' => 'Minyak goreng kelapa sawit, refined untuk deep frying',
                'food' => 'Minyak goreng fortifikasi vitamin A untuk produk makanan',
                'feed' => 'Minyak sawit crude untuk nutrisi lemak pakan'
            ],
            // Add more as needed...
        ];

        $baseSpec = $specifications[$materialName][$clientType] ?? "Spesifikasi khusus untuk {$materialName}";
        return $baseSpec . " - Sesuai kebutuhan {$clientName}";
    }

    private function normalizeForMatch($s)
    {
        if (!$s) return '';
        $s = trim(mb_strtolower($s));
        $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }
}
