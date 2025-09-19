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
                'no_hp' => '081234567801',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Central Proteina',
                'cabang' => 'Balaraja',
                'no_hp' => '081234567802',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Central Proteina',
                'cabang' => 'Dupak',
                'no_hp' => '081234567803',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Central Proteina',
                'cabang' => 'Sepanjang',
                'no_hp' => '081234567804',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CJ Feed',
                'cabang' => 'Jombang',
                'no_hp' => '081234567805',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CJ Feed',
                'cabang' => 'Semarang',
                'no_hp' => '081234567806',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CJ Feed',
                'cabang' => 'Serang',
                'no_hp' => '081234567807',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Charoen Pokpahand Indonesia',
                'cabang' => 'Sidoarjo',
                'no_hp' => '081234567808',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Charoen Pokpahand Indonesia',
                'cabang' => 'Semarang',
                'no_hp' => '081234567809',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Charoen Pokpahand Indonesia',
                'cabang' => 'Jombang',
                'no_hp' => '081234567810',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Dinamika Megatama Citra',
                'cabang' => 'Pasuruan',
                'no_hp' => '081234567811',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Haida',
                'cabang' => 'Pasuruan',
                'no_hp' => '081234567812',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Cargill',
                'cabang' => 'Margomulyo',
                'no_hp' => '081234567813',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Matahari Sakti',
                'cabang' => 'Sidoarjo',
                'no_hp' => '081234567814',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT New Hope',
                'cabang' => 'Mojosari',
                'no_hp' => '081234567815',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Wonokoyo',
                'cabang' => 'Jawa Timur',
                'no_hp' => '081234567816',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Japfa',
                'cabang' => 'Cikande',
                'no_hp' => '081234567817',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Thai Union',
                'cabang' => 'Grobogan',
                'no_hp' => '081234567818',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT De Heus',
                'cabang' => 'Lamongan',
                'no_hp' => '081234567819',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT CPgP',
                'cabang' => 'Jakarta',
                'no_hp' => '081234567820',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Evergreen',
                'cabang' => 'Cikampek',
                'no_hp' => '081234567821',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT East Hope',
                'cabang' => 'Pasuruan',
                'no_hp' => '081234567822',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Malindo',
                'cabang' => 'Pasuruan',
                'no_hp' => '081234567823',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Havindo',
                'cabang' => 'Gresik',
                'no_hp' => '081234567824',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Panca Patriot Prima',
                'cabang' => 'Grobogan',
                'no_hp' => '081234567825',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Sido Agung Farm',
                'cabang' => 'Grobogan',
                'no_hp' => '081234567826',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Gold Coin',
                'cabang' => 'Grobogan',
                'no_hp' => '081234567827',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Mulia Harvest',
                'cabang' => 'Magelang',
                'no_hp' => '081234567828',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($kliens as $klien) {
            Klien::create($klien);
        }
    }
}