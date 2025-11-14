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
                // Check if mapping exists for additional specifications
                $norm = $this->normalizeForMatch($materialName);
                $mapped = $mapping[$norm] ?? null;

                // Use material name as display name, but check mapping for better specs
                $displayName = $materialName;
                $specification = $this->generateSpecification($materialName, $klien->nama);
                $satuan = $config['satuan'] ?? 'kg';
                $priceRange = $config['price_range'] ?? [1000, 10000];

                // If mapping exists, use the detailed specification
                if ($mapped && !empty($mapped['spec'])) {
                    $specification = trim($mapped['spec']);
                    
                    // If pabrik present, append it to the specification for traceability
                    if (!empty($mapped['pabrik'])) {
                        $specification .= "\nPabrik: " . $mapped['pabrik'];
                    }
                }

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

    private function generateSpecification($materialName, $clientName)
    {
        // Default specifications for materials that don't have detailed mapping
        $defaultSpecs = [
            'Mie Kuning' => 'Moisture <12%, Protein >8%, Fat >12%, Ash <7%, Fiber <2%',
            'Mie Merah' => 'Moisture <5%, Protein >7%, Fat >23%, Ash <3%, warna sesuai bahan',
            'Tepung Biskuit' => 'Moisture (sesuai standar), Protein sesuai kebutuhan, Fiber <25%, Fat <5%, Ash <2%',
            'Katul' => 'Moisture <11%, Protein >12%, Fiber <10%, Fat >12%',
            'Bone Meal' => 'Protein >20%, Calcium <26%, Phosphor >9%, Moisture <12%, Ash <6%',
            'CFM/FTM' => 'Moisture <10%, Protein >80%, Pepsin Digestibility >75%, Ash <5%, Fat <12%',
            'Garam' => 'NaCl >95%, Moisture <1%, tidak menggumpal, partikel halus',
            'Molases' => 'Kadar air <20%, Brix >75, warna coklat kehitaman',
            'SHM' => 'Moisture <12%, Protein >45%, Fat <5%, TvBN <100',
            'DSS' => 'Moisture <12%, Protein >33%, TvBN <100',
            'Menir' => 'Moisture <12%, Protein >6%, warna putih susu/kuning muda, tidak apek',
            'Copra' => 'Protein >18%, Moisture <13%, Fat 8-20%, Fiber <16%',
            'PKD (Palm Kernel Dehulized)' => 'Moisture <12%, Protein >25%, bebas jamur dan serangga',
            'Biji Batu' => 'Spesifikasi sesuai kebutuhan industri pakan ternak',
            'Tepung Batu' => 'Moisture sesuai standar, Protein sesuai kebutuhan, Ash <2%',
            'PKM' => 'Moisture <12%, Protein >13%, Fat >7%, Fiber <20%, Ash <5%',
            'Corn Gem' => 'Moisture 0-10%, Protein 10-100%, Fat 40-100%, bebas jamur dan hama',
            'Cangkang Kemiri' => 'Bahan organik untuk campuran pakan, bebas kontaminasi',
            'Tepung Gaplek' => 'Kadar air max 12%, Protein min 3.3%',
            'Kebi' => 'Moisture <14%, Protein >12%, Ash <7%, Fiber <2%, tidak menggumpal',
            'Fish Meal' => 'Protein >20%, Calcium <26%, Phosphor >9%, Moisture <12%',
            'MBM (Meat Bone Meal)' => 'Moisture <10%, Protein >50%, Fat <12%, Ash <35%',
            'Poultry Meal' => 'Protein >20%, Calcium <26%, Phosphor >9%, Moisture <12%',
            'Gaplek Chip' => 'Kadar air max 12%, Protein min 3.3%, bentuk chip',
            'Kepala Udang Utuh' => 'Segar, bebas pembusukan, kadar air sesuai standar',
            'Tepung Roti/Bread Waste' => 'Moisture sesuai standar, Protein sesuai kebutuhan, Ash <2%',
            'SG (Sekam Giling)' => 'Kadar air <12%, bebas jamur, ukuran seragam',
            'Bran Gluten Feed (BGF)' => 'Moisture <12%, Protein >8%, Fat >4%, Fiber <6%',
            'CGM' => 'Moisture <10%, Protein >10%, Fat >38%, Fiber <10%, Aflatoxin <50%',
            'CGF' => 'Moisture 0-12%, Protein 18-100%, bebas jamur dan kontaminan',
            'Tepung Industri' => 'Grade industri, sesuai spesifikasi aplikasi',
            'CPO' => 'Crude Palm Oil, sesuai standar industri',
            'Ampok' => 'Kadar air sesuai standar, bebas kontaminasi'
        ];

        $baseSpec = $defaultSpecs[$materialName] ?? "Spesifikasi khusus untuk {$materialName} sesuai kebutuhan industri";
        return $baseSpec . " - Untuk {$clientName}";
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
