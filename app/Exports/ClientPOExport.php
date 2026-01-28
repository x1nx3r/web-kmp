<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClientPOExport implements FromView, WithStyles, WithColumnWidths, WithTitle
{
    protected $poByClient;
    protected $poDetailsByClient;
    protected $totalKlien;
    protected $totalPO;
    protected $totalNilai;
    protected $totalOutstanding;
    protected $avgPerPO;
    protected $filterInfo;

    public function __construct($poByClient, $poDetailsByClient, $totals, $filterInfo = null)
    {
        $this->poByClient = $poByClient;
        $this->poDetailsByClient = $poDetailsByClient;
        $this->totalKlien = $totals['totalKlien'];
        $this->totalPO = $totals['totalPO'];
        $this->totalNilai = $totals['totalNilai'];
        $this->totalOutstanding = $totals['totalOutstanding'];
        $this->avgPerPO = $totals['avgPerPO'];
        $this->filterInfo = $filterInfo;
    }

    public function view(): View
    {
        return view('exports.client-po', [
            'poByClient' => $this->poByClient,
            'poDetailsByClient' => $this->poDetailsByClient,
            'totalKlien' => $this->totalKlien,
            'totalPO' => $this->totalPO,
            'totalNilai' => $this->totalNilai,
            'totalOutstanding' => $this->totalOutstanding,
            'avgPerPO' => $this->avgPerPO,
            'filterInfo' => $this->filterInfo,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title row
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            
            // Summary section header
           4 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
            ],
            
            // Column header row
            7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,    // No
            'B' => 25,   // Klien
            'C' => 18,   // Cabang
            'D' => 10,   // Total PO
            'E' => 18,   // Total Nilai
            'F' => 18,   // Outstanding
            'G' => 15,   // Avg/PO
            'H' => 12,   // Kontribusi
            'I' => 12,   // Dikonfirmasi
            'J' => 12,   // Diproses
            'K' => 12,   // Selesai
            'L' => 15,   // Last Order
        ];
    }

    public function title(): string
    {
        return 'PO Berdasarkan Klien';
    }
}
