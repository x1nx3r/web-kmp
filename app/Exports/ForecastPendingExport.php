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
        $data[] = ['LAPORAN FORECAST PENDING', '', '', '', '', '', '', '', ''];
        
        // Baris 2: Tanggal Export
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', ''];
        
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
            $data[] = ['Filter: ' . implode(' | ', $filterInfo), '', '', '', '', '', '', '', ''];
        } else {
            $data[] = ['Filter: Semua Data Forecast Pending', '', '', '', '', '', '', '', ''];
        }
        
        // Baris 4: Kosong
        $data[] = ['', '', '', '', '', '', '', '', ''];
        
        // Header tabel
        $data[] = [
            'No PO',
            'Tanggal Perkiraan Kirim',
            'Hari Perkiraan Kirim',
            'PIC Supplier',
            'Supplier',
            'Bahan Baku PO',
            'Nama Pabrik',
            'QTY Forecasting',
            'Harga Beli',
            'Total Harga Forecasting'
        ];

        // Data forecasts
        $forecasts = $this->getForecastData();
        
        foreach ($forecasts as $forecast) {
            // Ambil data forecast details
            $forecastDetails = collect($forecast->forecastDetails ?? []);
            
            if ($forecastDetails->isEmpty()) {
                // Jika tidak ada details, tampilkan data forecast saja
                $data[] = [
                    optional($forecast->order)->po_number ?? 'N/A',
                    $forecast->tanggal_forecast ? Carbon::parse($forecast->tanggal_forecast)->format('d/m/Y') : 'N/A',
                    $forecast->hari_kirim_forecast ?? 'N/A',
                    'N/A',
                    'N/A',
                    'Tidak ada detail',
                    optional(optional($forecast->order)->klien)->nama ?? 'N/A',
                    0,
                    0,
                    0
                ];
            } else {
                // Loop untuk setiap detail forecast
                foreach ($forecastDetails as $detail) {
                    $bahanBaku = $detail->bahanBakuSupplier;
                    $supplier = optional($bahanBaku)->supplier;
                    $picSupplier = optional($supplier)->picPurchasing;

                    $data[] = [
                        optional($forecast->order)->po_number ?? 'N/A',
                        $forecast->tanggal_forecast ? Carbon::parse($forecast->tanggal_forecast)->format('d/m/Y') : 'N/A',
                        $forecast->hari_kirim_forecast ?? 'N/A',
                        optional($picSupplier)->nama ?? 'N/A',
                        optional($supplier)->nama ?? 'N/A',
                        optional($bahanBaku)->nama ?? 'N/A',
                        optional(optional($forecast->order)->klien)->nama ?? 'N/A',
                        (float)($detail->qty_forecast ?? 0),
                        (float)($detail->harga_satuan_forecast ?? 0),
                        (float)($detail->total_harga_forecast ?? 0)
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        
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
        for ($row = 6; $row <= $lastRow; $row++) {
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
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

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
        for ($row = 6; $row <= $lastRow; $row++) {
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

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
            'forecastDetails.bahanBakuSupplier.supplier.picPurchasing'
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

        return $query->orderBy('tanggal_forecast', 'desc')
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18,  // No PO
            'B' => 20,  // Tanggal Perkiraan Kirim
            'C' => 20,  // Hari Perkiraan Kirim
            'D' => 25,  // PIC Supplier
            'E' => 30,  // Supplier
            'F' => 40,  // Bahan Baku PO
            'G' => 35,  // Nama Pabrik
            'H' => 18,  // QTY Forecasting
            'I' => 20,  // Harga Beli
            'J' => 25,  // Total Harga Forecasting
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
