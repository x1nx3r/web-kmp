<?php

namespace Database\Seeders;

use App\Models\KontakKlien;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KontakKlienSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $contacts = [
            // PT Central Proteina
            [
                'nama' => 'Pak Ichwan',
                'klien_nama' => 'PT Central Proteina',
                'nomor_hp' => '08983999513',
                'jabatan' => 'Manager Procurement',
                'catatan' => 'Semua Bahan Baku - CPP Sepanjang dan CPP Dupak',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Azhar',
                'klien_nama' => 'PT Central Proteina',
                'nomor_hp' => '0895622177789',
                'jabatan' => 'Purchasing',
                'catatan' => 'Bahan Baku Shrimp Head Meal - CPP Dupak',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Nisa',
                'klien_nama' => 'PT Central Proteina',
                'nomor_hp' => '085746889233',
                'jabatan' => 'Purchasing',
                'catatan' => 'Bahan Baku Molases - CPP Sepanjang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Sandi',
                'klien_nama' => 'PT Central Proteina',
                'nomor_hp' => '089529640923',
                'jabatan' => 'Purchasing',
                'catatan' => 'Bahan Baku PKM, Kopra - CPP Sepanjang',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Charoen Pokpahand Indonesia  
            [
                'nama' => 'Pak Aken',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '082262677767',
                'jabatan' => 'Purchasing',
                'catatan' => 'Katul - CPI Krian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Heru',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '081331636276',
                'jabatan' => 'Purchasing',
                'catatan' => 'CFM - CPI Krian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Rery',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '082257449421',
                'jabatan' => 'Purchasing',
                'catatan' => 'Biskuit - CPI Krian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Agung',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '08125248622',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - CPI Sepanjang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Arif',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '08157710930',
                'jabatan' => 'Purchasing',
                'catatan' => 'Katul - CPI Jawa Tengah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Soni',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '08122801907',
                'jabatan' => 'Purchasing',
                'catatan' => 'CFM - CPI Jawa Tengah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Juergen',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '081388886977',
                'jabatan' => 'Purchasing',
                'catatan' => 'CGM - CPI Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Dani',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '082134479628',
                'jabatan' => 'Purchasing',
                'catatan' => 'CGM - CPI Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Dickson',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '0895404967880',
                'jabatan' => 'Purchasing',
                'catatan' => 'CGM - CPI Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Sreya Sewu
            [
                'nama' => 'Pak Agus',
                'klien_nama' => 'PT Sreya Sewu',
                'nomor_hp' => '085294153435',
                'jabatan' => 'Manager Purchasing',
                'catatan' => 'Semua Bahan Baku - Sreeya Balaraja',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Sherly',
                'klien_nama' => 'PT Sreya Sewu',
                'nomor_hp' => '085811781885',
                'jabatan' => 'Purchasing',
                'catatan' => 'Molases - Sreeya Wonoayu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Uki',
                'klien_nama' => 'PT Sreya Sewu',
                'nomor_hp' => '085649711422',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Sreeya Wonoayu',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT. Dinamika Megatama Citra
            [
                'nama' => 'Bu Christin',
                'klien_nama' => 'PT. Dinamika Megatama Citra',
                'nomor_hp' => '081331220131',
                'jabatan' => 'Manager Purchasing',
                'catatan' => 'Semua Bahan Baku - DMC Ngoro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Santi',
                'klien_nama' => 'PT. Dinamika Megatama Citra',
                'nomor_hp' => '085334631883',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - DMC Ngoro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Tira',
                'klien_nama' => 'PT. Dinamika Megatama Citra',
                'nomor_hp' => '081130789368',
                'jabatan' => 'Purchasing',
                'catatan' => 'Staf - DMC Ngoro',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Wonokoyo
            [
                'nama' => 'Pak David',
                'klien_nama' => 'PT Wonokoyo',
                'nomor_hp' => '081234824620',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Wonokoyo Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Kenny',
                'klien_nama' => 'PT Wonokoyo',
                'nomor_hp' => '081230923108',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Wonokoyo Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Ati',
                'klien_nama' => 'PT Wonokoyo',
                'nomor_hp' => '081807477971',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Cikande',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Haida
            [
                'nama' => 'Mr. Yan',
                'klien_nama' => 'PT Haida',
                'nomor_hp' => '08113781868',
                'jabatan' => 'Manager Purchasing',
                'catatan' => 'Semua Bahan Baku - Haida Lampung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Ariski',
                'klien_nama' => 'PT Haida',
                'nomor_hp' => '08113785868',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Haida Pasuruan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Hendry',
                'klien_nama' => 'PT Haida',
                'nomor_hp' => '081232162521',
                'jabatan' => 'Purchasing',
                'catatan' => 'Biskuit - Haida Pasuruan',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT East Hope
            [
                'nama' => 'Bu Naely',
                'klien_nama' => 'PT East Hope',
                'nomor_hp' => '082232333255',
                'jabatan' => 'Purchasing',
                'catatan' => 'Mie Merah, Gaplek - East Hope Ngoro',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT New Hope
            [
                'nama' => 'Bu Ria',
                'klien_nama' => 'PT New Hope',
                'nomor_hp' => '085748144034',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - New Hope Mojosari',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Ms. Hu',
                'klien_nama' => 'PT New Hope',
                'nomor_hp' => '082272418432',
                'jabatan' => 'Manager Purchasing',
                'catatan' => 'Semua Bahan Baku - New Hope Kletek',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Rizki',
                'klien_nama' => 'PT New Hope',
                'nomor_hp' => '081230204013',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - New Hope Kletek',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Mulia Harvest
            [
                'nama' => 'Bu Tri',
                'klien_nama' => 'PT Mulia Harvest',
                'nomor_hp' => '082227348090',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Grobogan',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Sido Agung Farm
            [
                'nama' => 'Bu Antin',
                'klien_nama' => 'PT Sido Agung Farm',
                'nomor_hp' => '08112896872',
                'jabatan' => 'Manager Purchasing',
                'catatan' => 'Semua Bahan Baku - Magelang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Rima',
                'klien_nama' => 'PT Sido Agung Farm',
                'nomor_hp' => '08112650542',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Magelang',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Cargill
            [
                'nama' => 'Bu Mucha',
                'klien_nama' => 'PT Cargill',
                'nomor_hp' => '081285939668',
                'jabatan' => 'Purchasing',
                'catatan' => 'Katul - Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Sinta Prima Feedmill
            [
                'nama' => 'Bu Silvia',
                'klien_nama' => 'PT Sinta Prima Feedmill',
                'nomor_hp' => '087868521186',
                'jabatan' => 'Purchasing',
                'catatan' => 'Biskuit - Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Gold Coin
            [
                'nama' => 'Pak Femmy',
                'klien_nama' => 'PT Gold Coin',
                'nomor_hp' => '085932614343',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Surabaya',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Matahari Sakti
            [
                'nama' => 'Pak Yohni',
                'klien_nama' => 'PT Matahari Sakti',
                'nomor_hp' => '0816528838',
                'jabatan' => 'Manager Purchasing',
                'catatan' => 'Semua Bahan Baku - Surabaya',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Tyo',
                'klien_nama' => 'PT Matahari Sakti',
                'nomor_hp' => '0816518638',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Surabaya',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT. Malindo
            [
                'nama' => 'Bu Dwi',
                'klien_nama' => 'PT. Malindo',
                'nomor_hp' => '081333004520',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Malindo Gresik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Ashar',
                'klien_nama' => 'PT. Malindo',
                'nomor_hp' => '082245644311',
                'jabatan' => 'Purchasing',
                'catatan' => 'Biskuit - Malindo Gorobogan',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // CJ Feed
            [
                'nama' => 'Bu Widi',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '082113168292',
                'jabatan' => 'Purchasing',
                'catatan' => 'CFM, Biskuit - CJ Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Rizqi',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '08992040530',
                'jabatan' => 'Purchasing',
                'catatan' => 'Katul - CJ Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Rizki',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '085741886294',
                'jabatan' => 'Purchasing',
                'catatan' => 'Gaplek - CJ Batang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Aris',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '08998786100',
                'jabatan' => 'Purchasing',
                'catatan' => 'Biskuit - CJ Serang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bu Alfisya',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '081231278920',
                'jabatan' => 'Purchasing',
                'catatan' => 'Bone Meal - CJ Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Iqbal',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '085218928939',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - CJ Jombang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Ibnu',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '085645756825',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - CJ Serang',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT. Panca Patriot Prima
            [
                'nama' => 'Bu Tatik',
                'klien_nama' => 'PT. Panca Patriot Prima',
                'nomor_hp' => '081938198828',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Sidoarjo',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Japfa
            [
                'nama' => 'Pak William',
                'klien_nama' => 'PT Japfa',
                'nomor_hp' => '087853274453',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Sidoarjo',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Thai Union
            [
                'nama' => 'Bu Shantika',
                'klien_nama' => 'PT Thai Union',
                'nomor_hp' => '0811355442',
                'jabatan' => 'Purchasing',
                'catatan' => 'Fish Meal, Katul - Lamongan',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Sari Rosa
            [
                'nama' => 'Pak Fajar',
                'klien_nama' => 'PT Sari Rosa',
                'nomor_hp' => '081328836593',
                'jabatan' => 'Purchasing',
                'catatan' => 'Mie - Yogyakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Central Pangan Pertiwi
            [
                'nama' => 'Bu Laswati',
                'klien_nama' => 'PT Central Pangan Pertiwi',
                'nomor_hp' => '081212393805',
                'jabatan' => 'Manager Purchasing',
                'catatan' => 'Semua Bahan Baku - Cikampek',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pak Andri',
                'klien_nama' => 'PT Central Pangan Pertiwi',
                'nomor_hp' => '081357828300',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Cikampek',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // CV Sinar Mentari Indonesia
            [
                'nama' => 'Bu Rika',
                'klien_nama' => 'CV Sinar Mentari Indonesia',
                'nomor_hp' => '085739091512',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Tulungagung',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // CV Karya Carma Gemilang
            [
                'nama' => 'Bu Yety',
                'klien_nama' => 'CV Karya Carma Gemilang',
                'nomor_hp' => '082132725300',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Tulungagung',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Citra Ina Feedmill
            [
                'nama' => 'Pak Fahli',
                'klien_nama' => 'PT Citra Ina Feedmill',
                'nomor_hp' => '082387757879',
                'jabatan' => 'Purchasing',
                'catatan' => 'Semua Bahan Baku - Cikampek',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($contacts as $contact) {
            KontakKlien::create($contact);
        }
    }
}
