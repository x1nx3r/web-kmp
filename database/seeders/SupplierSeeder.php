<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'nama' => 'PT Sumber Alam Jaya',
                'alamat' => 'Jl. Raya Industri No. 123, Jakarta Timur',
                'no_hp' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CV Mitra Bangunan',
                'alamat' => 'Jl. Sudirman No. 45, Bandung',
                'no_hp' => '081298765432',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'UD Sentosa Makmur',
                'alamat' => 'Jl. Diponegoro No. 67, Surabaya',
                'no_hp' => '081356789012',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Karya Utama',
                'alamat' => 'Jl. Gatot Subroto No. 89, Medan',
                'no_hp' => '081445678901',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CV Berkah Sejahtera',
                'alamat' => 'Jl. Ahmad Yani No. 12, Yogyakarta',
                'no_hp' => '081567890123',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Global Supply',
                'alamat' => 'Jl. Thamrin No. 234, Jakarta Pusat',
                'no_hp' => '081678901234',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'UD Sumber Rejeki',
                'alamat' => 'Jl. Malioboro No. 56, Yogyakarta',
                'no_hp' => '081789012345',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CV Mandiri Jaya',
                'alamat' => 'Jl. Pahlawan No. 78, Semarang',
                'no_hp' => '081890123456',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
