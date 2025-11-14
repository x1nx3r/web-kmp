<?php

namespace Database\Seeders;

use App\Models\BahanBakuSupplier;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BahanBakuSupplierSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Ensure suppliers are created first
        $this->call(SupplierSeeder::class);
        
        // Get suppliers by slug for mapping
        $supplierIdMap = Supplier::pluck('id', 'slug')->toArray();

        $bahanBakuSuppliers = [
            // pak Giarto
            [
                'supplier_slug' => 'pak-giarto',
                'nama' => 'biskuit',
                'harga_per_satuan' => 3800,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Pak Rul
            [
                'supplier_slug' => 'pak-rul',
                'nama' => 'katul',
                'harga_per_satuan' => 4700,
                'satuan' => 'Kg',
                'stok' => 200000, // 200 ton = 200,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Solikin
            [
                'supplier_slug' => 'pak-solikin',
                'nama' => 'katul',
                'harga_per_satuan' => 4500,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // abah ariff
            [
                'supplier_slug' => 'abah-ariff',
                'nama' => 'shm',
                'harga_per_satuan' => 7100,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Hasan
            [
                'supplier_slug' => 'pak-hasan',
                'nama' => 'bone meal',
                'harga_per_satuan' => 3500,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Widodo (multiple products)
            [
                'supplier_slug' => 'pak-widodo',
                'nama' => 'pkd',
                'harga_per_satuan' => 3700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-widodo',
                'nama' => 'pkm',
                'harga_per_satuan' => 2200,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-widodo',
                'nama' => 'biskuit',
                'harga_per_satuan' => 3550,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Heri
            [
                'supplier_slug' => 'pak-heri',
                'nama' => 'biskuit',
                'harga_per_satuan' => 3600,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Wawan
            [
                'supplier_slug' => 'pak-wawan',
                'nama' => 'mie kuning',
                'harga_per_satuan' => 5200,
                'satuan' => 'Kg',
                'stok' => 60000, // 60 ton = 60,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Kusnadi
            [
                'supplier_slug' => 'pak-kusnadi',
                'nama' => 'mie merah',
                'harga_per_satuan' => 4650,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Adit (multiple products)
            [
                'supplier_slug' => 'pak-adit-nganjuk',
                'nama' => 'corn germ',
                'harga_per_satuan' => 6450,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-adit-nganjuk',
                'nama' => 'cgm',
                'harga_per_satuan' => 9000,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-adit-nganjuk',
                'nama' => 'pkd',
                'harga_per_satuan' => 3700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-adit-nganjuk',
                'nama' => 'cgf',
                'harga_per_satuan' => 3500,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Toni
            [
                'supplier_slug' => 'pak-toni',
                'nama' => 'katul',
                'harga_per_satuan' => 4700,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // bu Hadiah
            [
                'supplier_slug' => 'bu-hadiah',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Fitra
            [
                'supplier_slug' => 'pak-fitra',
                'nama' => 'biskuit',
                'harga_per_satuan' => 3900,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Yuda (PT. Sorini)
            [
                'supplier_slug' => 'pak-yuda-pt-sorini',
                'nama' => 'corn germ',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Wandi
            [
                'supplier_slug' => 'pak-wandi',
                'nama' => 'biskuit',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // bu Lestari
            [
                'supplier_slug' => 'bu-lestari',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Yohannes
            [
                'supplier_slug' => 'pak-yohannes',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // ibu Anis
            [
                'supplier_slug' => 'ibu-anis',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // bu Ulil CV Sumber Pangan (multiple products)
            [
                'supplier_slug' => 'bu-ulil-cv-sumber-pangan',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'bu-ulil-cv-sumber-pangan',
                'nama' => 'kebi',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Pak Haris
            [
                'supplier_slug' => 'pak-haris',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Ade
            [
                'supplier_slug' => 'pak-ade',
                'nama' => 'cgf',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Iwan (multiple products)
            [
                'supplier_slug' => 'pak-iwan',
                'nama' => 'cfm',
                'harga_per_satuan' => 6400,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-iwan',
                'nama' => 'bone meal',
                'harga_per_satuan' => 3400,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Benny
            [
                'supplier_slug' => 'pak-benny',
                'nama' => 'biskuit',
                'harga_per_satuan' => 3600,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Akbar
            [
                'supplier_slug' => 'pak-akbar',
                'nama' => 'cangkang sawit',
                'harga_per_satuan' => 1300,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Ibu Silvia PT Garuda Perkasa Jaya (multiple products)
            [
                'supplier_slug' => 'ibu-silvia-pt-garuda-perkasa-jaya',
                'nama' => 'fish meal 60%',
                'harga_per_satuan' => 17700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'ibu-silvia-pt-garuda-perkasa-jaya',
                'nama' => 'mbm 50%',
                'harga_per_satuan' => 7800,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'ibu-silvia-pt-garuda-perkasa-jaya',
                'nama' => 'pmm 65%',
                'harga_per_satuan' => 14900,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'ibu-silvia-pt-garuda-perkasa-jaya',
                'nama' => 'pmm 60%',
                'harga_per_satuan' => 14100,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Rudi (multiple products)
            [
                'supplier_slug' => 'pak-rudi',
                'nama' => 'pkm',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-rudi',
                'nama' => 'cpo',
                'harga_per_satuan' => 15850, // 15.85 * 1000 untuk konversi ke per Kg
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Lutfi
            [
                'supplier_slug' => 'pak-lutfi',
                'nama' => 'biji batu',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 200000, // 200 ton = 200,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Daniel PT Miwon
            [
                'supplier_slug' => 'pak-daniel-pt-miwon',
                'nama' => 'corn germ',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Korun PT Bungasari
            [
                'supplier_slug' => 'pak-korun-pt-bungasari',
                'nama' => 'tepung industri',
                'harga_per_satuan' => 5100,
                'satuan' => 'Kg',
                'stok' => 400000, // 400 ton = 400,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // abah Julio
            [
                'supplier_slug' => 'abah-julio',
                'nama' => 'molases',
                'harga_per_satuan' => 1400,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // abah Zainal
            [
                'supplier_slug' => 'abah-zainal',
                'nama' => 'cfm',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Darsono
            [
                'supplier_slug' => 'pak-darsono',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // ibu Taslima
            [
                'supplier_slug' => 'ibu-taslima',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Tris
            [
                'supplier_slug' => 'pak-tris',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // ibu Romtini
            [
                'supplier_slug' => 'ibu-romtini',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Fani (multiple products)
            [
                'supplier_slug' => 'pak-fani',
                'nama' => 'cfm',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-fani',
                'nama' => 'bone meal',
                'harga_per_satuan' => 3700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Pri
            [
                'supplier_slug' => 'pak-pri',
                'nama' => 'katul',
                'harga_per_satuan' => 4700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak San
            [
                'supplier_slug' => 'pak-san',
                'nama' => 'katul',
                'harga_per_satuan' => 4550,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Iman
            [
                'supplier_slug' => 'pak-iman',
                'nama' => 'biskuit',
                'harga_per_satuan' => 3900,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Wahyu
            [
                'supplier_slug' => 'pak-wahyu',
                'nama' => 'biskuit',
                'harga_per_satuan' => 3700,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Bambang (multiple products)
            [
                'supplier_slug' => 'pak-bambang',
                'nama' => 'katul',
                'harga_per_satuan' => 4350,
                'satuan' => 'Kg',
                'stok' => 150000, // 150 ton = 150,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-bambang',
                'nama' => 'gaplek meal',
                'harga_per_satuan' => 4000,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Ali
            [
                'supplier_slug' => 'pak-ali',
                'nama' => 'gaplek meal',
                'harga_per_satuan' => 4000,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Adit (Prambong) - multiple products
            [
                'supplier_slug' => 'pak-adit-prambong',
                'nama' => 'kebi',
                'harga_per_satuan' => 5200,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-adit-prambong',
                'nama' => 'katul',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // ibu Hilma
            [
                'supplier_slug' => 'ibu-hilma',
                'nama' => 'tepung batu',
                'harga_per_satuan' => 1100,
                'satuan' => 'Kg',
                'stok' => 200000, // 200 ton = 200,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Yamin
            [
                'supplier_slug' => 'pak-yamin',
                'nama' => 'bone meal',
                'harga_per_satuan' => 3550,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Alim
            [
                'supplier_slug' => 'pak-alim',
                'nama' => 'katul',
                'harga_per_satuan' => 5200,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Aldy
            [
                'supplier_slug' => 'pak-aldy',
                'nama' => 'biskuit',
                'harga_per_satuan' => 3850,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Ridho
            [
                'supplier_slug' => 'pak-ridho',
                'nama' => 'katul',
                'harga_per_satuan' => 5300,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Budi
            [
                'supplier_slug' => 'pak-budi',
                'nama' => 'cfm',
                'harga_per_satuan' => 6800,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Heru
            [
                'supplier_slug' => 'pak-heru',
                'nama' => 'cfm',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Adit (Karawang)
            [
                'supplier_slug' => 'pak-adit-karawang',
                'nama' => 'biskuit',
                'harga_per_satuan' => 3900,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // abah Mashuri (multiple products)
            [
                'supplier_slug' => 'abah-mashuri',
                'nama' => 'cfm',
                'harga_per_satuan' => 6600,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'abah-mashuri',
                'nama' => 'bone meal',
                'harga_per_satuan' => 3550,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Abi (multiple products)
            [
                'supplier_slug' => 'pak-abi',
                'nama' => 'gaplek meal',
                'harga_per_satuan' => 4000,
                'satuan' => 'Kg',
                'stok' => 200000, // 200 ton = 200,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-abi',
                'nama' => 'gaplek chip',
                'harga_per_satuan' => null,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Wahyudi
            [
                'supplier_slug' => 'pak-wahyudi',
                'nama' => 'katul',
                'harga_per_satuan' => 5200,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Nafar
            [
                'supplier_slug' => 'pak-nafar',
                'nama' => 'shm',
                'harga_per_satuan' => 7000,
                'satuan' => 'Kg',
                'stok' => 30000, // 30 ton = 30,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Create bahan baku supplier records
        foreach ($bahanBakuSuppliers as $bahanBaku) {
            // Get supplier ID from slug mapping
            $supplierId = isset($supplierIdMap[$bahanBaku['supplier_slug']]) ? $supplierIdMap[$bahanBaku['supplier_slug']] : null;
            
            if ($supplierId) {
                // Remove supplier_slug from array and set supplier_id
                unset($bahanBaku['supplier_slug']);
                $bahanBaku['supplier_id'] = $supplierId;
                
                // Generate slug for bahan baku
                $bahanBaku['slug'] = \App\Models\BahanBakuSupplier::generateUniqueSlug($bahanBaku['nama'], $supplierId);
                
                BahanBakuSupplier::updateOrCreate(
                    [
                        'supplier_id' => $supplierId,
                        'nama' => $bahanBaku['nama']
                    ], // Check by supplier_id and nama (unique combination)
                    $bahanBaku // Update or create with this data
                );
            }
        }
    }
}
