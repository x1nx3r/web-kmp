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
            // CJ Feed contacts
            [
                'nama' => 'Budi Santoso',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '081234567890',
                'jabatan' => 'Procurement Manager',
                'catatan' => 'Kontak utama untuk pembelian bahan baku',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Sari Dewi',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '082345678901',
                'jabatan' => 'Quality Control Supervisor',
                'catatan' => 'Bertanggung jawab untuk kontrol kualitas produk',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Ahmad Rahman',
                'klien_nama' => 'CJ Feed',
                'nomor_hp' => '083456789012',
                'jabatan' => 'Plant Manager',
                'catatan' => 'Manager plant Jombang',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Central Proteina contacts
            [
                'nama' => 'Linda Wijaya',
                'klien_nama' => 'PT Central Proteina',
                'nomor_hp' => '084567890123',
                'jabatan' => 'Head of Purchasing',
                'catatan' => 'Kepala divisi pembelian untuk semua plant',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Rudi Hartono',
                'klien_nama' => 'PT Central Proteina',
                'nomor_hp' => '085678901234',
                'jabatan' => 'Operations Director',
                'catatan' => 'Direktur operasional, kontak untuk keputusan strategis',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Maya Sari',
                'klien_nama' => 'PT Central Proteina',
                'nomor_hp' => '086789012345',
                'jabatan' => 'Supply Chain Coordinator',
                'catatan' => 'Koordinator supply chain plant Balaraja',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Charoen Pokpahand Indonesia contacts
            [
                'nama' => 'Dewi Lestari',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '087890123456',
                'jabatan' => 'Procurement Specialist',
                'catatan' => 'Spesialis pengadaan bahan baku pakan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Joko Widodo',
                'klien_nama' => 'PT Charoen Pokpahand Indonesia',
                'nomor_hp' => '088901234567',
                'jabatan' => 'Regional Manager',
                'catatan' => 'Manager regional untuk area Jawa Timur',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Sreya Sewu contacts
            [
                'nama' => 'Andi Pratama',
                'klien_nama' => 'PT Sreya Sewu',  
                'nomor_hp' => '089012345678',
                'jabatan' => 'General Manager',
                'catatan' => 'GM plant Sidoarjo, kontak utama untuk negosiasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Fitri Handayani',
                'klien_nama' => 'PT Sreya Sewu',
                'nomor_hp' => '090123456789',
                'jabatan' => 'Finance Manager',
                'catatan' => 'Manager keuangan, kontak untuk pembayaran dan invoice',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT. Dinamika Megatama Citra contacts
            [
                'nama' => 'Hendra Kusuma',
                'klien_nama' => 'PT. Dinamika Megatama Citra',
                'nomor_hp' => '091234567890',
                'jabatan' => 'Production Manager',
                'catatan' => 'Manager produksi plant Pasuruan',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // PT Haida contacts
            [
                'nama' => 'Sri Mulyani',
                'klien_nama' => 'PT Haida',
                'nomor_hp' => '092345678901',
                'jabatan' => 'Purchasing Officer',
                'catatan' => 'Officer pembelian, kontak untuk order rutin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($contacts as $contact) {
            KontakKlien::create($contact);
        }
    }
}
