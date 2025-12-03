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
            // PT Sreeya Sewu Indonesia
            [
                'nama' => 'PT Sreeya Sewu Indonesia',
                'cabang' => 'Sidoarjo',
                'alamat_lengkap' => 'Jl. Raya Sidoarjo–Krian, Ketimang, Wonoayu, Sidoarjo, Jawa Timur',
            ],
            [
                'nama' => 'PT Sreeya Sewu Indonesia',
                'cabang' => 'Balaraja',
                'alamat_lengkap' => 'Jl. Raya Serang KM 30, Gembong, Kec. Balaraja, Kab. Tangerang, Banten 15610',
            ],

            // PT Central Proteina Prima
            [
                'nama' => 'PT Central Proteina Prima',
                'cabang' => 'Surabaya (Dupak)',
                'alamat_lengkap' => 'Dupak Rukun No. 81, Surabaya, Jawa Timur',
            ],
            [
                'nama' => 'PT Central Proteina Prima',
                'cabang' => 'Sidoarjo',
                'alamat_lengkap' => 'Jl. Raya Surabaya–Mojokerto KM 19, Bringinbendo, Taman, Sidoarjo, Jawa Timur',
            ],

            // PT Cheil Jedang Indonesia (CJ)
            [
                'nama' => 'PT Cheil Jedang Indonesia (CJ)',
                'cabang' => 'Jombang',
                'alamat_lengkap' => 'Jl. Raya Brantas KM 3.5, Jatigedang, Ploso, Jombang, Jawa Timur',
            ],

            // PT Charoen Pokphand Indonesia (CPI/CP)
            [
                'nama' => 'PT Charoen Pokphand Indonesia (CPI/CP)',
                'cabang' => 'Krian',
                'alamat_lengkap' => 'Jl. Raya Surabaya–Mojokerto KM 26, Keboharan, Krian, Sidoarjo, Jawa Timur',
            ],
            [
                'nama' => 'PT Charoen Pokphand Indonesia (CPI/CP)',
                'cabang' => 'Sepanjang',
                'alamat_lengkap' => 'Jl. Raya Taman–Sepanjang, Sidoarjo, Jawa Timur',
            ],
            [
                'nama' => 'PT Charoen Pokphand Indonesia (CPI/CP)',
                'cabang' => 'Jawa Tengah (Demak/Semarang)',
                'alamat_lengkap' => 'Jl. Semarang–Demak KM 8–9, Genuk/Sayung, Demak, Jawa Tengah',
            ],

            // PT Dinamika Megatama Citra (DMC)
            [
                'nama' => 'PT Dinamika Megatama Citra (DMC)',
                'cabang' => 'Mojosari-Ngoro',
                'alamat_lengkap' => 'Jl. Raya Mojosari–Ngoro KM 3, Mojokerto, Jawa Timur',
            ],

            // PT. Haida Agriculture Indonesia
            [
                'nama' => 'PT. Haida Agriculture Indonesia',
                'cabang' => 'Pasuruan',
                'alamat_lengkap' => 'Jl. Kraton Industri Raya No. 4, Kraton, Pasuruan, Jawa Timur',
            ],

            // PT. Cargill Indonesia
            [
                'nama' => 'PT. Cargill Indonesia',
                'cabang' => 'Pasuruan',
                'alamat_lengkap' => 'Jl. Raya Surabaya–Malang KM 43, Gempol, Pasuruan, Jawa Timur',
            ],

            // PT. Matahari Sakti
            [
                'nama' => 'PT. Matahari Sakti',
                'cabang' => 'Surabaya',
                'alamat_lengkap' => 'Margomulyo Industri I Blok A9–13, Surabaya, Jawa Timur',
            ],
            [
                'nama' => 'PT. Matahari Sakti',
                'cabang' => 'Pasuruan',
                'alamat_lengkap' => 'Jl. Raya Beji, Beji, Pasuruan, Jawa Timur',
            ],

            // PT. New Hope Jawa Timur
            [
                'nama' => 'PT. New Hope Jawa Timur',
                'cabang' => 'Sidoarjo',
                'alamat_lengkap' => 'Jl. Sawunggaling No. 132, Taman, Sidoarjo, Jawa Timur',
            ],

            // PT East Hope Agriculture Indonesia
            [
                'nama' => 'PT East Hope Agriculture Indonesia',
                'cabang' => 'Ngoro',
                'alamat_lengkap' => 'Ngoro Industrial Park Blok U-2, Lolawang, Mojokerto, Jawa Timur',
            ],

            // PT Wonokoyo Jaya Kusuma
            [
                'nama' => 'PT Wonokoyo Jaya Kusuma',
                'cabang' => 'Serang',
                'alamat_lengkap' => 'Jl. Raya Rangkasbitung KM 2, Cikande, Serang, Banten',
            ],

            // PT. Wonokoyo Jaya Corporindo
            [
                'nama' => 'PT. Wonokoyo Jaya Corporindo',
                'cabang' => 'Pasuruan',
                'alamat_lengkap' => 'Jl. Taman Bungkul No.1, Darmo, Wonokromo, Surabaya, East Java 60241',
            ],

            // PT Japfa Comfeed Indonesia
            [
                'nama' => 'PT Japfa Comfeed Indonesia',
                'cabang' => 'Sidoarjo',
                'alamat_lengkap' => 'Jl. HRM Mangundiprojo KM 3.5, Buduran, Sidoarjo, Jawa Timur',
            ],

            // PT Thai Union Kharisma Lestari
            [
                'nama' => 'PT Thai Union Kharisma Lestari',
                'cabang' => 'Lamongan',
                'alamat_lengkap' => 'Jl. Raya Gresik–Lamongan KM 39, Deket, Lamongan, Jawa Timur',
            ],

            // PT Central Pangan Pertiwi
            [
                'nama' => 'PT Central Pangan Pertiwi',
                'cabang' => 'Karawang',
                'alamat_lengkap' => 'Jl. Raya Karawang–Cikampek KM 17, Purwasari, Karawang, Jawa Barat',
            ],

            // PT Malindo Feedmill Tbk
            [
                'nama' => 'PT Malindo Feedmill Tbk',
                'cabang' => 'Gresik',
                'alamat_lengkap' => 'Plant Gresik, Gresik, Jawa Timur',
            ],

            // PT. Panca Patriot Prima
            [
                'nama' => 'PT. Panca Patriot Prima',
                'cabang' => 'Sidoarjo',
                'alamat_lengkap' => 'Jl. Muncul Industri II No. 11, Keboansikep, Gedangan, Sidoarjo, Jawa Timur',
            ],

            // PT Sidoagung Farm
            [
                'nama' => 'PT Sidoagung Farm',
                'cabang' => 'Magelang',
                'alamat_lengkap' => 'Jl. Magelang–Purworejo KM 10, Sidomukti 2, Tempuran, Magelang, Jawa Tengah',
            ],

            // PT Gold Coin Indonesia
            [
                'nama' => 'PT Gold Coin Indonesia',
                'cabang' => 'Surabaya',
                'alamat_lengkap' => 'Jl. Margomulyo Industri Kav. G 1–3, Asemrowo, Surabaya, Jawa Timur',
            ],

            // PT Mulia Harvest Agritech
            [
                'nama' => 'PT Mulia Harvest Agritech',
                'cabang' => 'Grobogan',
                'alamat_lengkap' => 'Jl. Raya Purwodadi–Blora KM 6, Mayahan, Grobogan, Jawa Tengah',
            ],

            // PT Sinta Prima Feedmill
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

            // CV Sinar Mentari Indonesia
            [
                'nama' => 'CV Sinar Mentari Indonesia',
                'cabang' => 'Blitar',
                'alamat_lengkap' => 'Gulungan, Jimbe, Kademangan, Blitar, Jawa Timur',
            ],

            // CV Karya Carma Gemilang
            [
                'nama' => 'CV Karya Carma Gemilang',
                'cabang' => 'Malang',
                'alamat_lengkap' => 'Malang Industrial Area, Malang, Jawa Timur',
            ],

            // PT Citra Ina Feedmill
            [
                'nama' => 'PT Citra Ina Feedmill',
                'cabang' => 'Jakarta Timur',
                'alamat_lengkap' => 'Jl. Suci KM 24, Susukan, Ciracas, Jakarta Timur',
            ],

            // PT Sari Rosa
            [
                'nama' => 'PT Sari Rosa',
                'cabang' => 'Sleman',
                'alamat_lengkap' => 'Bangsan, Sindumartani, Ngemplak, Sleman, Yogyakarta',
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