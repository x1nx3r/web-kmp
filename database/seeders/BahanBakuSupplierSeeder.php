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
                BahanBakuSupplier::create([
                    'supplier_id' => $supplier->id,
                    'nama' => $bahanBaku['nama'],
                    'satuan' => $bahanBaku['satuan'],
                    'harga_per_satuan' => rand($bahanBaku['harga_min'], $bahanBaku['harga_max']),
                    'stok' => rand($bahanBaku['stok_min'], $bahanBaku['stok_max']),
                ]);
            }
        }
    }
}
