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

        // Data bahan baku demo
        $bahanBakuData = [
            [
                'nama' => 'Bahan Baku A',
                'satuan' => 'KG',
                'harga_min' => 20000,
                'harga_max' => 30000,
                'stok_min' => 100,
                'stok_max' => 200
            ],
            [
                'nama' => 'Bahan Baku B', 
                'satuan' => 'KG',
                'harga_min' => 12000,
                'harga_max' => 18000,
                'stok_min' => 150,
                'stok_max' => 250
            ],
            [
                'nama' => 'Bahan Baku C',
                'satuan' => 'KG', 
                'harga_min' => 25000,
                'harga_max' => 35000,
                'stok_min' => 50,
                'stok_max' => 100
            ],
            [
                'nama' => 'Bahan Baku D',
                'satuan' => 'LITER',
                'harga_min' => 15000,
                'harga_max' => 22000,
                'stok_min' => 80,
                'stok_max' => 150
            ],
        ];

        foreach ($suppliers as $supplier) {
            // Setiap supplier memiliki 2-3 bahan baku secara random
            $selectedBahanBaku = collect($bahanBakuData)->random(rand(2, 3));
            
            foreach ($selectedBahanBaku as $bahanBaku) {
                // Cek apakah bahan baku ini sudah ada untuk supplier ini
                $existingBahanBaku = BahanBakuSupplier::where('supplier_id', $supplier->id)
                    ->where('nama', $bahanBaku['nama'])
                    ->first();
                
                if (!$existingBahanBaku) {
                    // Generate unique slug for this bahan baku
                    $slug = BahanBakuSupplier::generateUniqueSlug($bahanBaku['nama'], $supplier->id);
                    
                    BahanBakuSupplier::create([
                        'supplier_id' => $supplier->id,
                        'nama' => $bahanBaku['nama'],
                        'slug' => $slug,
                        'satuan' => $bahanBaku['satuan'],
                        'harga_per_satuan' => rand($bahanBaku['harga_min'], $bahanBaku['harga_max']),
                        'stok' => rand($bahanBaku['stok_min'], $bahanBaku['stok_max']),
                    ]);
                } else if (empty($existingBahanBaku->slug)) {
                    // Update slug jika bahan baku sudah ada tapi belum memiliki slug
                    $slug = BahanBakuSupplier::generateUniqueSlug($existingBahanBaku->nama, $existingBahanBaku->supplier_id, $existingBahanBaku->id);
                    $existingBahanBaku->update(['slug' => $slug]);
                }
            }
        }
    }
}
