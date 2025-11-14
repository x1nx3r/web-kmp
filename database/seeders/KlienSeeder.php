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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Central Proteina',
                'cabang' => 'Balaraja',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Central Proteina',
                'cabang' => 'Dupak',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Central Proteina',
                'cabang' => 'Sepanjang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CJ Feed',
                'cabang' => 'Jombang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CJ Feed',
                'cabang' => 'Semarang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CJ Feed',
                'cabang' => 'Serang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Charoen Pokpahand Indonesia',
                'cabang' => 'Sidoarjo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Charoen Pokpahand Indonesia',
                'cabang' => 'Semarang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Charoen Pokpahand Indonesia',
                'cabang' => 'Jombang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Dinamika Megatama Citra',
                'cabang' => 'Pasuruan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Haida',
                'cabang' => 'Pasuruan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Cargill',
                'cabang' => 'Margomulyo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Matahari Sakti',
                'cabang' => 'Sidoarjo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT New Hope',
                'cabang' => 'Mojosari',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Wonokoyo',
                'cabang' => 'Jawa Timur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Japfa',
                'cabang' => 'Cikande',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Thai Union',
                'cabang' => 'Grobogan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT De Heus',
                'cabang' => 'Lamongan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT CPgP',
                'cabang' => 'Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Evergreen',
                'cabang' => 'Cikampek',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT East Hope',
                'cabang' => 'Pasuruan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Malindo',
                'cabang' => 'Pasuruan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Havindo',
                'cabang' => 'Gresik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Panca Patriot Prima',
                'cabang' => 'Grobogan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Sido Agung Farm',
                'cabang' => 'Grobogan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Gold Coin',
                'cabang' => 'Grobogan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Mulia Harvest',
                'cabang' => 'Magelang',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($kliens as $klien) {
            Klien::create($klien);
        }
    }
}