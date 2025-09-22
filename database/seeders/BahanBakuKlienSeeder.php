<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BahanBakuKlien;

class BahanBakuKlienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bahanBakuKlien = [
            [
                'nama' => 'Tepung Terigu',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Gula Pasir',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Minyak Goreng',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Telur Ayam',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Susu Bubuk',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Vanilla Extract',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Baking Powder',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Cokelat Chips',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Keju Parut',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Mentega',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Garam Halus',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Bawang Putih',
                'status' => 'non-aktif',
            ],
        ];

        foreach ($bahanBakuKlien as $item) {
            BahanBakuKlien::create($item);
        }

        echo "BahanBakuKlien seeding completed!\n";
    }
}
