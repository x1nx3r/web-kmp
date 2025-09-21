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
        // Get purchasing users
        $purchasingUsers = \App\Models\User::where('role', 'purchasing')->pluck('id')->toArray();
        
        if (empty($purchasingUsers)) {
            // If no purchasing users exist, create some first
            $this->call(UserSeeder::class);
            $purchasingUsers = \App\Models\User::where('role', 'purchasing')->pluck('id')->toArray();
        }

        $suppliers = [
            [
                'nama' => 'PT Sumber Alam Jaya',
                'slug' => 'pt-sumber-alam-jaya',
                'alamat' => 'Jl. Raya Industri No. 123, Jakarta Timur',
                'no_hp' => '081234567890',
                'pic_purchasing_id' => $purchasingUsers[array_rand($purchasingUsers)],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CV Mitra Bangunan',
                'slug' => 'cv-mitra-bangunan',
                'alamat' => 'Jl. Sudirman No. 45, Bandung',
                'no_hp' => '081298765432',
                'pic_purchasing_id' => $purchasingUsers[array_rand($purchasingUsers)],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'UD Sentosa Makmur',
                'slug' => 'ud-sentosa-makmur',
                'alamat' => 'Jl. Diponegoro No. 67, Surabaya',
                'no_hp' => '081356789012',
                'pic_purchasing_id' => $purchasingUsers[array_rand($purchasingUsers)],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Karya Utama',
                'slug' => 'pt-karya-utama',
                'alamat' => 'Jl. Gatot Subroto No. 89, Medan',
                'no_hp' => '081445678901',
                'pic_purchasing_id' => $purchasingUsers[array_rand($purchasingUsers)],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CV Berkah Sejahtera',
                'slug' => 'cv-berkah-sejahtera',
                'alamat' => 'Jl. Ahmad Yani No. 12, Yogyakarta',
                'no_hp' => '081567890123',
                'pic_purchasing_id' => $purchasingUsers[array_rand($purchasingUsers)],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Global Supply',
                'slug' => 'pt-global-supply',
                'alamat' => 'Jl. Thamrin No. 234, Jakarta Pusat',
                'no_hp' => '081678901234',
                'pic_purchasing_id' => $purchasingUsers[array_rand($purchasingUsers)],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'UD Sumber Rejeki',
                'slug' => 'ud-sumber-rejeki',
                'alamat' => 'Jl. Malioboro No. 56, Yogyakarta',
                'no_hp' => '081789012345',
                'pic_purchasing_id' => $purchasingUsers[array_rand($purchasingUsers)],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CV Mandiri Jaya',
                'slug' => 'cv-mandiri-jaya',
                'alamat' => 'Jl. Pahlawan No. 78, Semarang',
                'no_hp' => '081890123456',
                'pic_purchasing_id' => $purchasingUsers[array_rand($purchasingUsers)],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
