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
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 3800,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Pak Rul
            [
                'supplier_slug' => 'pak-rul',
                'nama' => 'Katul',
                'harga_per_satuan' => 4700,
                'satuan' => 'Kg',
                'stok' => 200000, // 200 ton = 200,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Solikin
            [
                'supplier_slug' => 'pak-solikin',
                'nama' => 'Katul',
                'harga_per_satuan' => 4500,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // abah ariff
            [
                'supplier_slug' => 'abah-ariff',
                'nama' => 'SHM',
                'harga_per_satuan' => 7100,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Hasan
            [
                'supplier_slug' => 'pak-hasan',
                'nama' => 'Bone Meal',
                'harga_per_satuan' => 3500,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Widodo (multiple products)
            [
                'supplier_slug' => 'pak-widodo',
                'nama' => 'PKD',
                'harga_per_satuan' => 3700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-widodo',
                'nama' => 'PKM',
                'harga_per_satuan' => 2200,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-widodo',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 3550,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Heri
            [
                'supplier_slug' => 'pak-heri',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 3600,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Wawan
            [
                'supplier_slug' => 'pak-wawan',
                'nama' => 'Mie Kuning',
                'harga_per_satuan' => 5200,
                'satuan' => 'Kg',
                'stok' => 60000, // 60 ton = 60,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Kusnadi
            [
                'supplier_slug' => 'pak-kusnadi',
                'nama' => 'Mie Merah',
                'harga_per_satuan' => 4650,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Adit (multiple products)
            [
                'supplier_slug' => 'pak-adit-nganjuk',
                'nama' => 'Corn Gem',
                'harga_per_satuan' => 6450,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-adit-nganjuk',
                'nama' => 'CGM',
                'harga_per_satuan' => 9000,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-adit-nganjuk',
                'nama' => 'PKD',
                'harga_per_satuan' => 3700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-adit-nganjuk',
                'nama' => 'CGF',
                'harga_per_satuan' => 3500,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Toni
            [
                'supplier_slug' => 'pak-toni',
                'nama' => 'Katul',
                'harga_per_satuan' => 4700,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // bu Hadiah
            [
                'supplier_slug' => 'bu-hadiah',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Fitra
            [
                'supplier_slug' => 'pak-fitra',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 3900,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Yuda (PT. Sorini)
            [
                'supplier_slug' => 'pak-yuda-pt-sorini',
                'nama' => 'Corn Gem',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Wandi
            [
                'supplier_slug' => 'pak-wandi',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // bu Lestari
            [
                'supplier_slug' => 'bu-lestari',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Yohannes
            [
                'supplier_slug' => 'pak-yohannes',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // ibu Anis
            [
                'supplier_slug' => 'ibu-anis',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // bu Ulil CV Sumber Pangan (multiple products)
            [
                'supplier_slug' => 'bu-ulil-cv-sumber-pangan',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'bu-ulil-cv-sumber-pangan',
                'nama' => 'Kebi',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Pak Haris
            [
                'supplier_slug' => 'pak-haris',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Ade
            [
                'supplier_slug' => 'pak-ade',
                'nama' => 'CGF',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Iwan (multiple products)
            [
                'supplier_slug' => 'pak-iwan-krian',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 6400,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-iwan-krian',
                'nama' => 'Bone Meal',
                'harga_per_satuan' => 3400,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Benny
            [
                'supplier_slug' => 'pak-benny',
                'nama' => 'Tepung Biskuit',
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
                'nama' => 'Fish Meal',
                'harga_per_satuan' => 17700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'ibu-silvia-pt-garuda-perkasa-jaya',
                'nama' => 'MBM (Meat Bone Meal)',
                'harga_per_satuan' => 7800,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'ibu-silvia-pt-garuda-perkasa-jaya',
                'nama' => 'Poultry Meal',
                'harga_per_satuan' => 14900,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'ibu-silvia-pt-garuda-perkasa-jaya',
                'nama' => 'Poultry Meal',
                'harga_per_satuan' => 14100,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Rudi (multiple products)
            [
                'supplier_slug' => 'pak-rudi',
                'nama' => 'PKM',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-rudi',
                'nama' => 'CPO',
                'harga_per_satuan' => 15850, // 15.85 * 1000 untuk konversi ke per Kg
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Lutfi
            [
                'supplier_slug' => 'pak-lutfi',
                'nama' => 'Biji Batu',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 200000, // 200 ton = 200,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Daniel PT Miwon
            [
                'supplier_slug' => 'pak-daniel-pt-miwon',
                'nama' => 'Corn Gem',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Korun PT Bungasari
            [
                'supplier_slug' => 'pak-korun-pt-bungasari',
                'nama' => 'Tepung Industri',
                'harga_per_satuan' => 5100,
                'satuan' => 'Kg',
                'stok' => 400000, // 400 ton = 400,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // abah Julio
            [
                'supplier_slug' => 'abah-julio',
                'nama' => 'Molases',
                'harga_per_satuan' => 1400,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // abah Zainal
            [
                'supplier_slug' => 'abah-zainal',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Darsono
            [
                'supplier_slug' => 'pak-darsono',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // ibu Taslima
            [
                'supplier_slug' => 'ibu-taslima',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Tris
            [
                'supplier_slug' => 'pak-tris',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // ibu Romtini
            [
                'supplier_slug' => 'ibu-romtini',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Fani (multiple products)
            [
                'supplier_slug' => 'pak-fani',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-fani',
                'nama' => 'Bone Meal',
                'harga_per_satuan' => 3700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Pri
            [
                'supplier_slug' => 'pak-pri',
                'nama' => 'Katul',
                'harga_per_satuan' => 4700,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak San
            [
                'supplier_slug' => 'pak-san',
                'nama' => 'Katul',
                'harga_per_satuan' => 4550,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Iman
            [
                'supplier_slug' => 'pak-iman',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 3900,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Wahyu
            [
                'supplier_slug' => 'pak-wahyu',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 3700,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Bambang (multiple products)
            [
                'supplier_slug' => 'pak-bambang',
                'nama' => 'Katul',
                'harga_per_satuan' => 4350,
                'satuan' => 'Kg',
                'stok' => 150000, // 150 ton = 150,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-bambang',
                'nama' => 'Tepung Gaplek',
                'harga_per_satuan' => 4000,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Ali
            [
                'supplier_slug' => 'pak-ali',
                'nama' => 'Tepung Gaplek',
                'harga_per_satuan' => 4000,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Adit (Prambong) - multiple products
            [
                'supplier_slug' => 'pak-adit-prambong',
                'nama' => 'Kebi',
                'harga_per_satuan' => 5200,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-adit-prambong',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // ibu Hilma
            [
                'supplier_slug' => 'ibu-hilma',
                'nama' => 'Tepung Batu',
                'harga_per_satuan' => 1100,
                'satuan' => 'Kg',
                'stok' => 200000, // 200 ton = 200,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Yamin
            [
                'supplier_slug' => 'pak-yamin',
                'nama' => 'Bone Meal',
                'harga_per_satuan' => 3550,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Alim
            [
                'supplier_slug' => 'pak-alim',
                'nama' => 'Katul',
                'harga_per_satuan' => 5200,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Aldy
            [
                'supplier_slug' => 'pak-aldy',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 3850,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Ridho
            [
                'supplier_slug' => 'pak-ridho',
                'nama' => 'Katul',
                'harga_per_satuan' => 5300,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Budi
            [
                'supplier_slug' => 'pak-budi',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 6800,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Heru
            [
                'supplier_slug' => 'pak-heru',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Adi (Karawang)
            [
                'supplier_slug' => 'pak-adi-karawang',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 3900,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // abah Mashuri (multiple products)
            [
                'supplier_slug' => 'abah-mashuri',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 6600,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'abah-mashuri',
                'nama' => 'Bone Meal',
                'harga_per_satuan' => 3550,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Abi (multiple products)
            [
                'supplier_slug' => 'pak-abi',
                'nama' => 'Tepung Gaplek',
                'harga_per_satuan' => 4000,
                'satuan' => 'Kg',
                'stok' => 200000, // 200 ton = 200,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'pak-abi',
                'nama' => 'Gaplek Chip',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Wahyudi
            [
                'supplier_slug' => 'pak-wahyudi',
                'nama' => 'Katul',
                'harga_per_satuan' => 5200,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Nafar
            [
                'supplier_slug' => 'pak-nafar',
                'nama' => 'SHM',
                'harga_per_satuan' => 7000,
                'satuan' => 'Kg',
                'stok' => 30000, // 30 ton = 30,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Heri (Gresik)
            [
                'supplier_slug' => 'pak-heri',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Teguh
            [
                'supplier_slug' => 'teguh',
                'nama' => 'Garam',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Febri
            [
                'supplier_slug' => 'febri',
                'nama' => 'Garam',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Rini
            [
                'supplier_slug' => 'rini',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Yayuk
            [
                'supplier_slug' => 'yayuk',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // pak Dani
            [
                'supplier_slug' => 'pak-dani',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Rokidin
            [
                'supplier_slug' => 'rokidin',
                'nama' => 'Tepung Biskuit',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Iwan (Jombang)
            [
                'supplier_slug' => 'iwan-jombang',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Hardian
            [
                'supplier_slug' => 'hardian',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Eddy
            [
                'supplier_slug' => 'eddy',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Sutris
            [
                'supplier_slug' => 'sutris',
                'nama' => 'CFM/FTM',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Aris
            [
                'supplier_slug' => 'aris',
                'nama' => 'Gaplek Chip',
                'harga_per_satuan' => 3900,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Yudi
            [
                'supplier_slug' => 'yudi',
                'nama' => 'Gaplek Chip',
                'harga_per_satuan' => 3900,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Ladi
            [
                'supplier_slug' => 'ladi',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Yunus
            [
                'supplier_slug' => 'yunus',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Siswanto (multiple products)
            [
                'supplier_slug' => 'siswanto',
                'nama' => 'Katul',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 100000, // 100 ton = 100,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'siswanto',
                'nama' => 'Kebi',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_slug' => 'siswanto',
                'nama' => 'Menir',
                'harga_per_satuan' => 0,
                'satuan' => 'Kg',
                'stok' => 50000, // 50 ton = 50,000 Kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Heri (Gresik) - already added above as pak-heri
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
