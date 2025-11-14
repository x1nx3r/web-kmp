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
        $data[] = ['LAPORAN PENGIRIMAN', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 2: Periode
        $data[] = ['Periode: ' . date('d/m/Y', strtotime($this->startDate)) . ' - ' . date('d/m/Y', strtotime($this->endDate)), '', '', '', '', '', '', '', '', '', '', ''];
        
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
            $data[] = ['Filter: ' . implode(' | ', $filterInfo), '', '', '', '', '', '', '', '', '', '', ''];
        } else {
            $data[] = ['Filter: Semua Data', '', '', '', '', '', '', '', '', '', '', ''];
        }
        
        // Baris 4: Waktu ekspor
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 5: Kosong
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Header tabel
        $data[] = [
            'Tanggal Kirim',
            'Hari Kirim',
            'PIC Supplier',
            'Supplier',
            'Bahan Baku PO', 
            'Nama Pabrik',
            'QTY Forecasting',
            'Harga Jual',
            'Total Harga Forecasting',
            'QTY Pengiriman',
            'Total Harga Pengiriman',
            'Keterangan'
        ];

        // Data pengiriman
        $pengirimanData = $this->getPengirimanData();
        
        foreach ($pengirimanData as $pengiriman) {
            // Ambil data pengiriman details
            $pengirimanDetails = collect($pengiriman->pengirimanDetails ?? []);
            
            // Gabungkan bahan baku dari pengiriman details
            $bahanBakuPO = $pengirimanDetails->map(function($detail) {
                return optional($detail->bahanBakuSupplier)->nama ?? 'N/A';
            })->implode(', ');

            // PIC Supplier dari pengiriman details
            $picSuppliers = $pengirimanDetails->map(function($detail) {
                return optional(optional(optional($detail->bahanBakuSupplier)->supplier)->picPurchasing)->nama ?? 'N/A';
            })->unique()->implode(', ');

            // Supplier dari pengiriman details
            $suppliers = $pengirimanDetails->map(function($detail) {
                return optional(optional($detail->bahanBakuSupplier)->supplier)->nama ?? 'N/A';
            })->unique()->implode(', ');

            // Harga jual dari pengiriman details (harga_satuan)
            $hargaJual = $pengirimanDetails->map(function($detail) {
                return (float)($detail->harga_satuan ?? 0);
            })->sum();

            $data[] = [
                $pengiriman->tanggal_kirim ? 
                    \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d/m/Y') : 'N/A',
                $pengiriman->hari_kirim ?? 'N/A',
                $picSuppliers ?: 'N/A',
                $suppliers ?: 'N/A',
                $bahanBakuPO ?: 'Tidak ada detail',
                (optional(optional($pengiriman->order)->klien)->nama ?? 'N/A') . ' - ' . (optional(optional($pengiriman->order)->klien)->cabang ?? 'N/A'),
                number_format((float)(($pengiriman->forecast && $pengiriman->forecast->total_qty_forecast) ? $pengiriman->forecast->total_qty_forecast : 0), 2),
                (float)$hargaJual,
                (float)(($pengiriman->forecast && $pengiriman->forecast->total_harga_forecast) ? $pengiriman->forecast->total_harga_forecast : 0),
                number_format((float)($pengiriman->total_qty_kirim ?? 0), 2),
                (float)($pengiriman->total_harga_kirim ?? 0),
                $pengiriman->catatan ?: '-'
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
        $sheet->mergeCells('A1:L1');
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
            $sheet->mergeCells("A{$row}:L{$row}");
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
        $sheet->getStyle('A6:L6')->applyFromArray([
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
            $sheet->getStyle("A7:L{$lastRow}")->applyFromArray([
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
            // Kolom tanggal (A) - rata tengah
            $sheet->getStyle("A7:A{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Kolom hari (B) - rata tengah
            $sheet->getStyle("B7:B{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Kolom angka (G, H, I, J, K) - rata kanan
            foreach (['G', 'H', 'I', 'J', 'K'] as $col) {
                $sheet->getStyle("{$col}7:{$col}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Format currency untuk kolom harga (H, I, K)
            $sheet->getStyle("H7:H{$lastRow}")->getNumberFormat()
                ->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("I7:I{$lastRow}")->getNumberFormat()
                ->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("K7:K{$lastRow}")->getNumberFormat()
                ->setFormatCode('"Rp "#,##0');

            // Zebra striping - baris bergantian
            for ($row = 7; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
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
            'order.klien', 
            'forecast.forecastDetails.bahanBakuSupplier.supplier',
            'pengirimanDetails.bahanBakuSupplier.supplier.picPurchasing'
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
            'A' => 18,  // Tanggal Kirim
            'B' => 18,  // Hari Kirim
            'C' => 25,  // PIC Supplier
            'D' => 25,  // Supplier
            'E' => 40,  // Bahan Baku PO
            'F' => 35,  // Nama Pabrik
            'G' => 18,  // QTY Forecasting
            'H' => 20,  // Harga Jual
            'I' => 22,  // Total Harga Forecasting
            'J' => 18,  // QTY Pengiriman
            'K' => 22,  // Total Harga Pengiriman
            'L' => 40,  // Keterangan
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