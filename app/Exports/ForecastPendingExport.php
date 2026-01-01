<?php

namespace App\Exports;

use App\Models\Forecast;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ForecastPendingExport implements 
    FromArray, 
    WithColumnWidths, 
    WithTitle,
    WithStyles
{
    protected $dateRange;
    protected $purchasing;
    protected $search;
    protected $hariKirim;

    public function __construct($dateRange = null, $purchasing = null, $search = null, $hariKirim = null)
    {
        $this->dateRange = $dateRange;
        $this->purchasing = $purchasing;
        $this->search = $search;
        $this->hariKirim = $hariKirim;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Header informasi
        $data = [];
        
        // Baris 1: Judul
        $data[] = ['LAPORAN FORECAST PENDING', '', '', '', '', '', '', '', '', ''];
        
        // Baris 2: Tanggal Export
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', ''];
        
        // Baris 3: Filter
        $filterInfo = [];
        if ($this->dateRange) {
            $filterInfo[] = 'Tanggal Perkiraan Kirim: ' . date('d/m/Y', strtotime($this->dateRange));
        }
        if ($this->purchasing) {
            $filterInfo[] = 'PIC Purchasing ID: ' . $this->purchasing;
        }
        if ($this->search) {
            $filterInfo[] = 'Pencarian: ' . $this->search;
        }
        if ($this->hariKirim) {
            $filterInfo[] = 'Hari Kirim: ' . ucfirst($this->hariKirim);
        }
        
        if (!empty($filterInfo)) {
            $data[] = ['Filter: ' . implode(' | ', $filterInfo), '', '', '', '', '', '', '', '', ''];
        } else {
            $data[] = ['Filter: Semua Data Forecast Pending', '', '', '', '', '', '', '', '', ''];
        }
        
        // Baris 4: Kosong
        $data[] = ['', '', '', '', '', '', '', '', '', ''];
        
        // Header tabel
        $data[] = [
            'Tanggal Perkiraan Kirim',
            'Hari Perkiraan Kirim',
            'No PO',
            'Supplier',
            'Bahan Baku PO',
            'Nama Pabrik',
            'QTY Forecasting',
            'Harga Jual',
            'Total Harga Forecasting',
            'PIC Supplier'
        ];

        // Data forecasts
        $forecasts = $this->getForecastData();
        
        // Variable untuk menghitung total
        $grandTotal = 0;
        
        foreach ($forecasts as $forecast) {
            // Ambil data forecast details
            $forecastDetails = collect($forecast->forecastDetails ?? []);
            
            if ($forecastDetails->isEmpty()) {
                // Jika tidak ada details, tampilkan data forecast saja
                $data[] = [
                    $forecast->tanggal_forecast ? Carbon::parse($forecast->tanggal_forecast)->format('d/m/Y') : 'N/A',
                    $forecast->hari_kirim_forecast ?? 'N/A',
                    optional($forecast->order)->po_number ?? 'N/A',
                    'N/A',
                    'Tidak ada detail',
                    optional(optional($forecast->order)->klien)->nama ?? 'N/A',
                    0,
                    0,
                    0,
                    'N/A'
                ];
            } else {
                // Loop untuk setiap detail forecast
                foreach ($forecastDetails as $detail) {
                    $bahanBaku = $detail->bahanBakuSupplier;
                    $supplier = optional($bahanBaku)->supplier;
                    $picSupplier = optional($supplier)->picPurchasing;
                    
                    // Ambil harga jual dari order detail
                    $orderDetail = $detail->orderDetail;
                    $hargaJual = $orderDetail ? (float)$orderDetail->harga_jual : 0;
                    
                    // Hitung total harga menggunakan qty forecast dan harga jual dari PO
                    $totalHargaDetail = (float)($detail->qty_forecast ?? 0) * $hargaJual;
                    $grandTotal += $totalHargaDetail;

                    $data[] = [
                        $forecast->tanggal_forecast ? Carbon::parse($forecast->tanggal_forecast)->format('d/m/Y') : 'N/A',
                        $forecast->hari_kirim_forecast ?? 'N/A',
                        optional($forecast->order)->po_number ?? 'N/A',
                        optional($supplier)->nama ?? 'N/A',
                        optional($bahanBaku)->nama ?? 'N/A',
                        optional(optional($forecast->order)->klien)->nama ?? 'N/A',
                        (float)($detail->qty_forecast ?? 0),
                        $hargaJual,
                        $totalHargaDetail,
                        optional($picSupplier)->nama ?? 'N/A'
                    ];
                }
            }
        }
        
        // Tambahkan baris kosong
        $data[] = ['', '', '', '', '', '', '', '', '', ''];
        
        // Tambahkan baris TOTAL
        $data[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $grandTotal,
            ''
        ];

        return $data;
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $totalRow = $lastRow; // Baris total
        $dataEndRow = $lastRow - 2; // Baris terakhir data (sebelum baris kosong dan total)
        
        // Style for title
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F59E0B'] // Yellow-500
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Style for export date
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        // Style for filter info
        $sheet->mergeCells('A3:J3');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        // Style for header row (row 5)
        $sheet->getStyle('A5:J5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B82F6'] // Blue-500
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Style for data rows
        for ($row = 6; $row <= $dataEndRow; $row++) {
            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Center align specific columns
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Alternating row colors
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'] // Gray-50
                    ]
                ]);
            }
        }

        // Number format for currency columns
        for ($row = 6; $row <= $dataEndRow; $row++) {
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }
        
        // Style untuk baris TOTAL
        $sheet->mergeCells("A{$totalRow}:H{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", "TOTAL");
        $sheet->getStyle("A{$totalRow}:J{$totalRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10B981'] // Green-500
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        $sheet->getStyle("I{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("I{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getRowDimension($totalRow)->setRowHeight(25);

        // Auto-size all columns except merged cells
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(false);
        }

        return [];
    }

    /**
     * Get forecast data based on filters
     */
    private function getForecastData()
    {
        $query = Forecast::with([
            'order.klien',
            'purchasing',
            'forecastDetails.bahanBakuSupplier.supplier.picPurchasing',
            'forecastDetails.orderDetail'
        ])
        ->where('status', 'pending');

        // Apply filters
        if ($this->dateRange) {
            $query->whereDate('tanggal_forecast', $this->dateRange);
        }

        if ($this->purchasing) {
            $query->where('purchasing_id', $this->purchasing);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('no_forecast', 'like', "%{$this->search}%")
                  ->orWhereHas('order', function($orderQuery) {
                      $orderQuery->where('po_number', 'like', "%{$this->search}%")
                                 ->orWhereHas('klien', function($klienQuery) {
                                     $klienQuery->where('nama', 'like', "%{$this->search}%");
                                 });
                  })
                  ->orWhereHas('purchasing', function($userQuery) {
                      $userQuery->where('nama', 'like', "%{$this->search}%");
                  });
            });
        }

        if ($this->hariKirim) {
            $query->whereRaw('LOWER(hari_kirim_forecast) LIKE ?', ['%' . strtolower($this->hariKirim) . '%']);
        }

        return $query->orderBy('tanggal_forecast', 'asc')
                     ->orderBy('created_at', 'asc')
                     ->get();
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Tanggal Perkiraan Kirim
            'B' => 20,  // Hari Perkiraan Kirim
            'C' => 18,  // No PO
            'D' => 30,  // Supplier
            'E' => 40,  // Bahan Baku PO
            'F' => 35,  // Nama Pabrik
            'G' => 18,  // QTY Forecasting
            'H' => 20,  // Harga Beli
            'I' => 25,  // Total Harga Forecasting
            'J' => 25,  // PIC Supplier
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Forecast Pending';
    }
}
