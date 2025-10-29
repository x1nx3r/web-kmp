<?php

namespace App\Exports;

use App\Models\Pengiriman;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PengirimanExport implements 
    FromArray, 
    WithColumnWidths, 
    WithTitle,
    WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $status;
    protected $purchasing;
    protected $search;
    protected $purchasingUsers;

    public function __construct($startDate, $endDate, $status = null, $purchasing = null, $search = null, $purchasingUsers = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->purchasing = $purchasing;
        $this->search = $search;
        $this->purchasingUsers = $purchasingUsers;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Header informasi
        $data = [];
        
        // Baris 1: Judul
        $data[] = ['LAPORAN PENGIRIMAN', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 2: Periode
        $data[] = ['Periode: ' . date('d/m/Y', strtotime($this->startDate)) . ' - ' . date('d/m/Y', strtotime($this->endDate)), '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 3: Filter
        $filterInfo = [];
        if ($this->status) {
            $filterInfo[] = 'Status: ' . ucfirst($this->status);
        }
        if ($this->purchasing) {
            $purchasingName = $this->purchasingUsers ? 
                ($this->purchasingUsers->find($this->purchasing)->nama ?? 'Unknown') : 
                'ID: ' . $this->purchasing;
            $filterInfo[] = 'PIC Purchasing: ' . $purchasingName;
        }
        if ($this->search) {
            $filterInfo[] = 'Pencarian: ' . $this->search;
        }
        
        if (!empty($filterInfo)) {
            $data[] = ['Filter: ' . implode(' | ', $filterInfo), '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        } else {
            $data[] = ['Filter: Semua Data', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        }
        
        // Baris 4: Waktu ekspor
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 5: Kosong
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Header tabel (dengan 2 kolom baru)
        $data[] = [
            'No PO',
            'Nama Pabrik',
            'Bahan Baku PO', 
            'Supplier',
            'PIC Purchasing',
            'Tanggal Forecasting',
            'Hari Forecasting',
            'QTY Forecasting',
            'Total Harga Forecasting',
            'Tanggal Pengiriman',
            'Hari Pengiriman', 
            'No Pengiriman',
            'QTY Pengiriman',
            'Total Harga Pengiriman',
            'Keterangan',
            'Status Pengiriman'
        ];

        // Data pengiriman
        $pengirimanData = $this->getPengirimanData();
        
        foreach ($pengirimanData as $pengiriman) {
            // Ambil data forecast details untuk mendapatkan bahan baku PO
            $forecastDetails = collect();
            if ($pengiriman->forecast && $pengiriman->forecast->forecastDetails) {
                $forecastDetails = $pengiriman->forecast->forecastDetails;
            }
            
            // Gabungkan bahan baku dari forecast details
            $bahanBakuPO = $forecastDetails->map(function($detail) {
                $bahanBaku = optional($detail->bahanBakuSupplier)->nama ?? 'N/A';
                return "{$bahanBaku}";
            })->implode(', ');

            // PIC Supplier dari forecast details
            $picSuppliers = $forecastDetails->map(function($detail) {
                return optional(optional($detail->bahanBakuSupplier)->supplier)->nama ?? 'N/A';
            })->unique()->implode(', ');

            // PIC Purchasing name
            $picPurchasing = optional($pengiriman->purchasing)->nama ?? 'N/A';
            
            // Status label
            $statusLabel = 'N/A';
            switch($pengiriman->status) {
                case 'pending':
                    $statusLabel = 'Pending';
                    break;
                case 'menunggu_verifikasi':
                    $statusLabel = 'Menunggu Verifikasi';
                    break;
                case 'berhasil':
                    $statusLabel = 'Berhasil';
                    break;
                case 'gagal':
                    $statusLabel = 'Gagal';
                    break;
                default:
                    $statusLabel = ucfirst($pengiriman->status ?? 'N/A');
            }

            $data[] = [
                optional($pengiriman->purchaseOrder)->no_po ?? 'N/A',
                (optional(optional($pengiriman->purchaseOrder)->klien)->nama ?? 'N/A') . ' - ' . (optional(optional($pengiriman->purchaseOrder)->klien)->cabang ?? 'N/A'),
                $bahanBakuPO ?: 'Tidak ada detail',
                $picSuppliers ?: 'N/A',
                $picPurchasing,
                ($pengiriman->forecast && $pengiriman->forecast->tanggal_forecast) ? 
                    \Carbon\Carbon::parse($pengiriman->forecast->tanggal_forecast)->format('d/m/Y') : 'N/A',
                ($pengiriman->forecast && $pengiriman->forecast->hari_kirim_forecast) ? 
                    $pengiriman->forecast->hari_kirim_forecast : 'N/A',
                number_format((float)(($pengiriman->forecast && $pengiriman->forecast->total_qty_forecast) ? $pengiriman->forecast->total_qty_forecast : 0)),
                (float)(($pengiriman->forecast && $pengiriman->forecast->total_harga_forecast) ? $pengiriman->forecast->total_harga_forecast : 0),
                $pengiriman->tanggal_kirim ? 
                    \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d/m/Y') : 'N/A',
                $pengiriman->hari_kirim ?? 'N/A',
                $pengiriman->no_pengiriman ?? 'N/A',
                number_format((float)($pengiriman->total_qty_kirim ?? 0), 2),
                (float)($pengiriman->total_harga_kirim ?? 0),
                $pengiriman->catatan ?: '-',
                $statusLabel
            ];
        }

        return $data;
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        $pengirimanData = $this->getPengirimanData();
        $lastRow = 6 + $pengirimanData->count(); // 6 adalah jumlah baris header + info
        
        // Style untuk judul (baris 1)
        $sheet->mergeCells('A1:P1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style untuk info periode, filter, dan waktu ekspor (baris 2-4)
        foreach ([2, 3, 4] as $row) {
            $sheet->mergeCells("A{$row}:P{$row}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => [
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Style untuk header tabel (baris 6)
        $sheet->getStyle('A6:P6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'], // Biru seperti gambar
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Set tinggi baris header
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Style untuk data (baris 7 dan seterusnya)
        if ($lastRow > 6) {
            $sheet->getStyle("A7:P{$lastRow}")->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D0D0'],
                    ],
                ],
            ]);

            // Alignment khusus untuk kolom tertentu
            // Kolom angka (H, I, M, N) - rata kanan
            foreach (['H', 'I', 'M', 'N'] as $col) {
                $sheet->getStyle("{$col}7:{$col}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Format currency untuk kolom harga (I, N)
            $sheet->getStyle("I7:I{$lastRow}")->getNumberFormat()
                ->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("N7:N{$lastRow}")->getNumberFormat()
                ->setFormatCode('"Rp "#,##0');

            // Kolom tanggal (F, J) - rata tengah
            foreach (['F', 'J'] as $col) {
                $sheet->getStyle("{$col}7:{$col}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Kolom hari (G, K) - rata tengah
            foreach (['G', 'K'] as $col) {
                $sheet->getStyle("{$col}7:{$col}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Kolom status (P) - rata tengah
            $sheet->getStyle("P7:P{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra striping - baris bergantian
            for ($row = 7; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2'], // Abu-abu muda
                        ],
                    ]);
                }
            }
        }

        return [];
    }

    /**
     * Get pengiriman data with filters
     */
    private function getPengirimanData()
    {
        $query = Pengiriman::with([
            'purchasing', 
            'purchaseOrder.klien', 
            'forecast.forecastDetails.bahanBakuSupplier.supplier',
            'pengirimanDetails.bahanBakuSupplier.supplier'
        ])->whereBetween('tanggal_kirim', [$this->startDate, $this->endDate]);

        // Apply filters
        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->purchasing) {
            $query->where('purchasing_id', $this->purchasing);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('no_pengiriman', 'like', "%{$this->search}%")
                  ->orWhereHas('purchasing', function($q2) {
                      $q2->where('nama', 'like', "%{$this->search}%");
                  });
            });
        }

        return $query->orderBy('tanggal_kirim', 'desc')->get();
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // No PO
            'B' => 35,  // Nama Pabrik
            'C' => 40,  // Bahan Baku PO
            'D' => 25,  // PIC Supplier
            'E' => 20,  // PIC Purchasing
            'F' => 18,  // Tanggal Forecasting
            'G' => 18,  // Hari Forecasting
            'H' => 15,  // QTY Forecasting
            'I' => 20,  // Total Harga Forecasting
            'J' => 18,  // Tanggal Pengiriman
            'K' => 18,  // Hari Pengiriman
            'L' => 20,  // No Pengiriman
            'M' => 15,  // QTY Pengiriman
            'N' => 20,  // Total Harga Pengiriman
            'O' => 40,  // Keterangan
            'P' => 20,  // Status Pengiriman
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Laporan Pengiriman';
    }
}