<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use Livewire\WithPagination;

class RiwayatPenawaran extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $sortBy = 'tanggal_desc';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function getDummyPenawaran()
    {
        // Dummy data for demonstration
        $allData = [
            [
                'id' => 1,
                'nomor_penawaran' => 'PNW-2025-001',
                'tanggal' => '2025-10-03',
                'klien' => [
                    'nama' => 'PT Maju Bersama',
                    'cabang' => 'Jakarta',
                ],
                'materials' => [
                    [
                        'nama' => 'Semen Portland',
                        'quantity' => 100,
                        'satuan' => 'sak',
                        'harga_klien' => 95000,
                        'supplier' => 'PT Sumber Alam Jaya',
                        'pic' => 'Sari Purchasing',
                        'harga_supplier' => 85000,
                    ],
                    [
                        'nama' => 'Pasir Halus',
                        'quantity' => 50,
                        'satuan' => 'm3',
                        'harga_klien' => 450000,
                        'supplier' => 'CV Mitra Bangunan',
                        'pic' => 'Ahmad Purchasing',
                        'harga_supplier' => 380000,
                    ],
                ],
                'total_revenue' => 32000000,
                'total_cost' => 27500000,
                'total_profit' => 4500000,
                'margin' => 14.1,
                'status' => 'butuh_verifikasi',
                'created_by' => 'Admin Marketing',
            ],
            [
                'id' => 2,
                'nomor_penawaran' => 'PNW-2025-002',
                'tanggal' => '2025-10-02',
                'klien' => [
                    'nama' => 'CV Sejahtera Abadi',
                    'cabang' => 'Bandung',
                ],
                'materials' => [
                    [
                        'nama' => 'Besi Beton 10mm',
                        'quantity' => 200,
                        'satuan' => 'batang',
                        'harga_klien' => 85000,
                        'supplier' => 'PT Karya Utama',
                        'pic' => 'Sari Purchasing',
                        'harga_supplier' => 75000,
                    ],
                ],
                'total_revenue' => 17000000,
                'total_cost' => 15000000,
                'total_profit' => 2000000,
                'margin' => 11.8,
                'status' => 'sudah_diverifikasi',
                'created_by' => 'Admin Marketing',
            ],
            [
                'id' => 3,
                'nomor_penawaran' => 'PNW-2025-003',
                'tanggal' => '2025-10-01',
                'klien' => [
                    'nama' => 'UD Berkah Jaya',
                    'cabang' => 'Surabaya',
                ],
                'materials' => [
                    [
                        'nama' => 'Cat Tembok Premium',
                        'quantity' => 75,
                        'satuan' => 'kaleng',
                        'harga_klien' => 125000,
                        'supplier' => 'CV Berkah Sejahtera',
                        'pic' => 'Ahmad Purchasing',
                        'harga_supplier' => 105000,
                    ],
                    [
                        'nama' => 'Keramik 40x40',
                        'quantity' => 150,
                        'satuan' => 'dus',
                        'harga_klien' => 85000,
                        'supplier' => 'UD Sentosa Makmur',
                        'pic' => 'Dewi Purchasing',
                        'harga_supplier' => 72000,
                    ],
                ],
                'total_revenue' => 22125000,
                'total_cost' => 18675000,
                'total_profit' => 3450000,
                'margin' => 15.6,
                'status' => 'butuh_verifikasi',
                'created_by' => 'Admin Marketing',
            ],
            [
                'id' => 4,
                'nomor_penawaran' => 'PNW-2025-004',
                'tanggal' => '2025-09-30',
                'klien' => [
                    'nama' => 'PT Konstruksi Modern',
                    'cabang' => 'Jakarta',
                ],
                'materials' => [
                    [
                        'nama' => 'Semen Portland',
                        'quantity' => 300,
                        'satuan' => 'sak',
                        'harga_klien' => 97000,
                        'supplier' => 'PT Sumber Alam Jaya',
                        'pic' => 'Sari Purchasing',
                        'harga_supplier' => 86000,
                    ],
                ],
                'total_revenue' => 29100000,
                'total_cost' => 25800000,
                'total_profit' => 3300000,
                'margin' => 11.3,
                'status' => 'sudah_diverifikasi',
                'created_by' => 'Admin Marketing',
            ],
            [
                'id' => 5,
                'nomor_penawaran' => 'PNW-2025-005',
                'tanggal' => '2025-09-28',
                'klien' => [
                    'nama' => 'CV Mandiri Sentosa',
                    'cabang' => 'Semarang',
                ],
                'materials' => [
                    [
                        'nama' => 'Pipa PVC 3 inch',
                        'quantity' => 120,
                        'satuan' => 'batang',
                        'harga_klien' => 65000,
                        'supplier' => 'PT Global Supply',
                        'pic' => 'Dewi Purchasing',
                        'harga_supplier' => 55000,
                    ],
                    [
                        'nama' => 'Kabel NYM 2x2.5',
                        'quantity' => 80,
                        'satuan' => 'roll',
                        'harga_klien' => 450000,
                        'supplier' => 'CV Mandiri Jaya',
                        'pic' => 'Ahmad Purchasing',
                        'harga_supplier' => 385000,
                    ],
                ],
                'total_revenue' => 43800000,
                'total_cost' => 37400000,
                'total_profit' => 6400000,
                'margin' => 14.6,
                'status' => 'sudah_diverifikasi',
                'created_by' => 'Admin Marketing',
            ],
        ];

        // Apply search filter
        if ($this->search) {
            $allData = array_filter($allData, function ($item) {
                return stripos($item['nomor_penawaran'], $this->search) !== false ||
                       stripos($item['klien']['nama'], $this->search) !== false ||
                       stripos($item['klien']['cabang'], $this->search) !== false;
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $allData = array_filter($allData, function ($item) {
                return $item['status'] === $this->statusFilter;
            });
        }

        // Apply sorting
        usort($allData, function ($a, $b) {
            switch ($this->sortBy) {
                case 'tanggal_asc':
                    return strcmp($a['tanggal'], $b['tanggal']);
                case 'tanggal_desc':
                    return strcmp($b['tanggal'], $a['tanggal']);
                case 'nomor_asc':
                    return strcmp($a['nomor_penawaran'], $b['nomor_penawaran']);
                case 'nomor_desc':
                    return strcmp($b['nomor_penawaran'], $a['nomor_penawaran']);
                default:
                    return 0;
            }
        });

        return $allData;
    }

    public function render()
    {
        $penawaran = $this->getDummyPenawaran();
        
        return view('livewire.marketing.riwayat-penawaran', [
            'penawaranList' => $penawaran,
        ]);
    }
}
