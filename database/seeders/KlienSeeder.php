<?php

namespace Database\Seeders;

use App\Models\Klien;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KlienSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $kliens = [
            [
                'nama' => 'PT Sreya Sewu',
                'cabang' => 'Sidoarjo',
                'alamat_lengkap' => 'Jl. Raya Sidoarjo–Krian, Ketimang, Wonoayu, Sidoarjo, Jawa Timur',
            ],
            [
                'nama' => 'PT Central Proteina',
                'cabang' => 'Balaraja',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT Central Proteina',
                'cabang' => 'Dupak',
                'alamat_lengkap' => 'Dupak Rukun No. 81, Surabaya, Jawa Timur',
            ],
            [
                'nama' => 'PT Central Proteina',
                'cabang' => 'Sepanjang',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'CJ Feed',
                'cabang' => 'Jombang',
                'alamat_lengkap' => 'Jl. Raya Brantas KM 3.5, Jatigedang, Ploso, Jombang, Jawa Timur',
            ],
            [
                'nama' => 'CJ Feed',
                'cabang' => 'Semarang',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'CJ Feed',
                'cabang' => 'Serang',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT Charoen Pokpahand Indonesia',
                'cabang' => 'Sidoarjo',
                'alamat_lengkap' => 'Jl. Raya Surabaya–Mojokerto KM 26, Keboharan, Krian, Sidoarjo, Jawa Timur',
            ],
            [
                'nama' => 'PT Charoen Pokpahand Indonesia',
                'cabang' => 'Semarang',
                'alamat_lengkap' => 'Jl. Semarang–Demak KM 8–9, Genuk/Sayung, Demak, Jawa Tengah',
            ],
            [
                'nama' => 'PT Charoen Pokpahand Indonesia',
                'cabang' => 'Jombang',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT. Dinamika Megatama Citra',
                'cabang' => 'Pasuruan',
                'alamat_lengkap' => 'Jl. Raya Mojosari–Ngoro KM 3, Mojokerto, Jawa Timur',
            ],
            [
                'nama' => 'PT Haida',
                'cabang' => 'Pasuruan',
                'alamat_lengkap' => 'Jl. Kraton Industri Raya No. 4, Kraton, Pasuruan, Jawa Timur',
            ],
            [
                'nama' => 'PT Cargill',
                'cabang' => 'Margomulyo',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT Matahari Sakti',
                'cabang' => 'Sidoarjo',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT New Hope',
                'cabang' => 'Mojosari',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT Wonokoyo',
                'cabang' => 'Jawa Timur',
                'alamat_lengkap' => 'Jl. Taman Bungkul No.1, Darmo, Wonokromo, Surabaya, East Java 60241',
            ],
            [
                'nama' => 'PT Japfa',
                'cabang' => 'Cikande',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT Thai Union',
                'cabang' => 'Grobogan',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT De Heus',
                'cabang' => 'Lamongan',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT CPgP',
                'cabang' => 'Jakarta',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT Evergreen',
                'cabang' => 'Cikampek',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT East Hope',
                'cabang' => 'Pasuruan',
                'alamat_lengkap' => 'Ngoro Industrial Park Blok U-2, Lolawang, Mojokerto, Jawa Timur',
            ],
            [
                'nama' => 'PT. Malindo',
                'cabang' => 'Gresik', // Corrected from Pasuruan
                'alamat_lengkap' => 'Plant Gresik, Gresik, Jawa Timur',
            ],
            [
                'nama' => 'PT. Havindo',
                'cabang' => 'Gresik',
                'alamat_lengkap' => null,
            ],
            [
                'nama' => 'PT. Panca Patriot Prima',
                'cabang' => 'Sidoarjo', // Corrected from Grobogan
                'alamat_lengkap' => 'Jl. Muncul Industri II No. 11, Keboansikep, Gedangan, Sidoarjo, Jawa Timur',
            ],
            [
                'nama' => 'PT Sido Agung Farm',
                'cabang' => 'Magelang', // Corrected from Grobogan
                'alamat_lengkap' => 'Jl. Magelang–Purworejo KM 10, Sidomukti 2, Tempuran, Magelang, Jawa Tengah',
            ],
            [
                'nama' => 'PT Gold Coin',
                'cabang' => 'Surabaya', // Corrected from Grobogan
                'alamat_lengkap' => 'Jl. Margomulyo Industri Kav. G 1–3, Asemrowo, Surabaya, Jawa Timur',
            ],
            [
                'nama' => 'PT Mulia Harvest',
                'cabang' => 'Grobogan', // Corrected from Magelang
                'alamat_lengkap' => 'Jl. Raya Purwodadi–Blora KM 6, Mayahan, Grobogan, Jawa Tengah',
            ],
            // New Entries from CSV
            [
                'nama' => 'PT Sinta Prima Feedmill',
                'cabang' => 'Bogor',
                'alamat_lengkap' => 'Jl. Narogong KM 18, Limusnunggal, Cileungsi, Bogor, Jawa Barat',
            ],
            [
                'nama' => 'PT Sinta Prima Feedmill',
                'cabang' => 'Nganjuk',
                'alamat_lengkap' => 'Jl. Raya Madiun–Surabaya, Plimping, Baron, Nganjuk, Jawa Timur',
            ],
            [
                'nama' => 'PT Sari Rosa',
                'cabang' => 'Sleman',
                'alamat_lengkap' => 'Bangsan, Sindumartani, Ngemplak, Sleman, Yogyakarta',
            ],
            [
                'nama' => 'PT Central Pangan Pertiwi',
                'cabang' => 'Karawang',
                'alamat_lengkap' => 'Jl. Raya Karawang–Cikampek KM 17, Purwasari, Karawang, Jawa Barat',
            ],
            [
                'nama' => 'CV Sinar Mentari Indonesia',
                'cabang' => 'Blitar',
                'alamat_lengkap' => 'Gulungan, Jimbe, Kademangan, Blitar, Jawa Timur',
            ],
            [
                'nama' => 'CV Karya Carma Gemilang',
                'cabang' => 'Malang',
                'alamat_lengkap' => 'Malang Industrial Area, Malang, Jawa Timur',
            ],
            [
                'nama' => 'PT Citra Ina Feedmill',
                'cabang' => 'Jakarta Timur',
                'alamat_lengkap' => 'Jl. Suci KM 24, Susukan, Ciracas, Jakarta Timur',
            ],
        ];

        foreach ($kliens as $klien) {
            Klien::updateOrCreate(
                [
                    'nama' => $klien['nama'],
                    'cabang' => $klien['cabang'],
                ],
                [
                    'alamat_lengkap' => $klien['alamat_lengkap'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}