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
                'satuan' => 'kg',
                'spesifikasi' => 'Tepung terigu protein tinggi untuk pembuatan roti',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Gula Pasir',
                'satuan' => 'kg',
                'spesifikasi' => 'Gula pasir putih kristal halus',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Minyak Goreng',
                'satuan' => 'liter',
                'spesifikasi' => 'Minyak goreng kelapa sawit',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Telur Ayam',
                'satuan' => 'kg',
                'spesifikasi' => 'Telur ayam segar grade A',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Susu Bubuk',
                'satuan' => 'kg',
                'spesifikasi' => 'Susu bubuk full cream',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Vanilla Extract',
                'satuan' => 'ml',
                'spesifikasi' => 'Ekstrak vanilla murni',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Baking Powder',
                'satuan' => 'kg',
                'spesifikasi' => 'Pengembang kue double acting',
                'status' => 'aktif',
            ],
            [
                'nama' => 'Cokelat Chips',
                'satuan' => 'kg',
                'spesifikasi' => 'Cokelat chips dark chocolate',
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
