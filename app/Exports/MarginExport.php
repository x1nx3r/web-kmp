<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MarginExport implements FromView, WithStyles, WithColumnWidths, WithTitle
{
    protected $marginData;
    protected $totalQty;
    protected $totalHargaBeli;
    protected $totalHargaJual;
    protected $totalMargin;
    protected $grossMarginPercentage;
    protected $profitCount;
    protected $lossCount;
    protected $filters;

    public function __construct($marginData, $totals, $filters = [])
    {
        $this->marginData = $marginData;
        $this->totalQty = $totals['totalQty'];
        $this->totalHargaBeli = $totals['totalHargaBeli'];
        $this->totalHargaJual = $totals['totalHargaJual'];
        $this->totalMargin = $totals['totalMargin'];
        $this->grossMarginPercentage = $totals['grossMarginPercentage'];
        $this->profitCount = $totals['profitCount'];
        $this->lossCount = $totals['lossCount'];
        $this->filters = $filters;
    }

    public function view(): View
    {
        return view('exports.margin', [
            'marginData' => $this->marginData,
            'totalQty' => $this->totalQty,
            'totalHargaBeli' => $this->totalHargaBeli,
            'totalHargaJual' => $this->totalHargaJual,
            'totalMargin' => $this->totalMargin,
            'grossMarginPercentage' => $this->grossMarginPercentage,
            'profitCount' => $this->profitCount,
            'lossCount' => $this->lossCount,
            'filters' => $this->filters,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Hitung jumlah baris filter yang ada
        $filterRows = 0;
        if (!empty($this->filters['start_date']) || !empty($this->filters['end_date'])) $filterRows++;
        if (!empty($this->filters['pic_purchasing_name'])) $filterRows++;
        if (!empty($this->filters['pic_marketing_name'])) $filterRows++;
        if (!empty($this->filters['klien_name'])) $filterRows++;
        if (!empty($this->filters['supplier_name'])) $filterRows++;
        if (!empty($this->filters['bahan_baku_name'])) $filterRows++;
        
        // Header (3) + Filter ($filterRows) + Empty (1) + Summary (2) + Empty (1) + Column Header (1) + Data + Footer (1)
        $headerRows = 3 + $filterRows + 1 + 2 + 1 + 1;
        $lastRow = $headerRows + count($this->marginData) + 1; // +1 for footer
        
        return [
            // Header Info (Title)
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            
            // Filter Info
            '3:5' => [
                'font' => ['size' => 10],
            ],
            
            // Summary Section
            7 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
            ],
            
            // Column Headers (row 9)
            $headerRows => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Data rows (not bold, normal rows)
            ($headerRows + 1) . ':' . ($lastRow - 1) => [
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            
            // Total row (footer - only this should be bold)
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 12,  // Tanggal
            'C' => 18,  // No Pengiriman
            'D' => 20,  // PIC Procurement
            'E' => 20,  // PIC Marketing
            'F' => 25,  // Klien
            'G' => 25,  // Supplier
            'H' => 20,  // Bahan Baku
            'I' => 12,  // Qty
            'J' => 15,  // Harga Beli/kg
            'K' => 18,  // Total Beli
            'L' => 15,  // Harga Jual/kg
            'M' => 18,  // Total Jual
            'N' => 18,  // Margin (Rp)
            'O' => 12,  // Margin (%)
        ];
    }

    public function title(): string
    {
        return 'Analisis Margin';
    }
}
