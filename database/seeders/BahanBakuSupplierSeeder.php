<?php

namespace Database\Seeders;

use App\Models\BahanBakuSupplier;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class BahanBakuSupplierSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Update slug untuk bahan baku yang sudah ada tapi belum memiliki slug
        $bahanBakuTanpaSlug = BahanBakuSupplier::whereNull('slug')->orWhere('slug', '')->get();
        
        foreach ($bahanBakuTanpaSlug as $bahanBaku) {
            $slug = BahanBakuSupplier::generateUniqueSlug($bahanBaku->nama, $bahanBaku->supplier_id, $bahanBaku->id);
            $bahanBaku->update(['slug' => $slug]);
        }
        
        // Ambil semua supplier
        $suppliers = Supplier::all();

        // Data bahan baku yang cocok dengan material klien
        $bahanBakuData = [
            [
                'nama' => 'Biji Batu',
                'satuan' => 'KG',
                'harga_min' => 8000,
                'harga_max' => 12000,
                'stok_min' => 100,
                'stok_max' => 200
            ],
            [
                'nama' => 'Cangkang Kemiri',
                'satuan' => 'KG',
                'harga_min' => 9000,
                'harga_max' => 13000,
                'stok_min' => 150,
                'stok_max' => 250
            ],
            [
                'nama' => 'CPO',
                'satuan' => 'KG',
                'harga_min' => 7500,
                'harga_max' => 11000,
                'stok_min' => 50,
                'stok_max' => 100
            ],
            [
                'nama' => 'Molases',
                'satuan' => 'LITER',
                'harga_min' => 6000,
                'harga_max' => 9500,
                'stok_min' => 80,
                'stok_max' => 150
            ],
            [
                'nama' => 'DSS',
                'satuan' => 'KG',
                'harga_min' => 8500,
                'harga_max' => 12500,
                'stok_min' => 120,
                'stok_max' => 180
            ],
            [
                'nama' => 'Bungkil Copra',
                'satuan' => 'KG',
                'harga_min' => 7000,
                'harga_max' => 10500,
                'stok_min' => 90,
                'stok_max' => 160
            ],
            [
                'nama' => 'Katul',
                'satuan' => 'KG',
                'harga_min' => 5500,
                'harga_max' => 8000,
                'stok_min' => 200,
                'stok_max' => 300
            ],
            [
                'nama' => 'PKM',
                'satuan' => 'KG',
                'harga_min' => 6500,
                'harga_max' => 9000,
                'stok_min' => 110,
                'stok_max' => 190
            ],
        ];

        // Create materials for each supplier ensuring multiple suppliers per material
        foreach ($bahanBakuData as $materialIndex => $bahanBaku) {
            // Each material will be offered by 3-5 random suppliers with different prices
            $suppliersForMaterial = $suppliers->random(rand(3, min(5, $suppliers->count())));
            
            foreach ($suppliersForMaterial as $supplierIndex => $supplier) {
                // Cek apakah bahan baku ini sudah ada untuk supplier ini
                $existingBahanBaku = BahanBakuSupplier::where('supplier_id', $supplier->id)
                    ->where('nama', $bahanBaku['nama'])
                    ->first();
                
                if (!$existingBahanBaku) {
                    // Generate unique slug for this bahan baku
                    $slug = BahanBakuSupplier::generateUniqueSlug($bahanBaku['nama'], $supplier->id);
                    
                    // Create price variation for each supplier
                    // First supplier gets the lowest price (best), others get progressively higher
                    $priceMultiplier = 1 + ($supplierIndex * 0.12); // 0%, 12%, 24%, 36%, 48% markup
                    $basePrice = $bahanBaku['harga_min'];
                    $finalPrice = round($basePrice * $priceMultiplier, -2); // Round to nearest 100
                    
                    // Add some randomness to make it more realistic
                    $randomVariation = rand(-5, 5) / 100; // ±5% random variation
                    $finalPrice = round($finalPrice * (1 + $randomVariation), -2);
                    
                    // Ensure price is within reasonable bounds
                    $finalPrice = max($bahanBaku['harga_min'], min($bahanBaku['harga_max'], $finalPrice));
                    
                    // Stock variation
                    $stockVariation = $bahanBaku['stok_min'] + rand(0, $bahanBaku['stok_max'] - $bahanBaku['stok_min']);
                    
                    BahanBakuSupplier::create([
                        'supplier_id' => $supplier->id,
                        'nama' => $bahanBaku['nama'],
                        'slug' => $slug,
                        'satuan' => $bahanBaku['satuan'],
                        'harga_per_satuan' => $finalPrice,
                        'stok' => $stockVariation,
                    ]);
                    
                    $this->command->info("Created: {$bahanBaku['nama']} for {$supplier->nama} at Rp " . number_format($finalPrice));
                } else if (empty($existingBahanBaku->slug)) {
                    // Update slug jika bahan baku sudah ada tapi belum memiliki slug
                    $slug = BahanBakuSupplier::generateUniqueSlug($existingBahanBaku->nama, $existingBahanBaku->supplier_id, $existingBahanBaku->id);
                    $existingBahanBaku->update(['slug' => $slug]);
                }
            }
        }
    }
}
