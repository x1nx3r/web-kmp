<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SpesifikasiExport implements FromArray, WithColumnWidths, WithTitle, WithStyles
{
    protected $materials;
    protected $filters;

    private const LAST_COLUMN = 'I';

    public function __construct($materials, array $filters = [])
    {
        $this->materials = $materials;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $empty = ['', '', '', '', '', '', '', '', ''];

        $data = [];
        $data[] = array_merge(['DAFTAR SPESIFIKASI MATERIAL'], array_slice($empty, 1));
        $data[] = array_merge(['Diekspor pada: ' . now()->format('d/m/Y H:i:s')], array_slice($empty, 1));
        $data[] = array_merge([
            !empty($this->filters) ? 'Filter: ' . implode(' | ', $this->filters) : 'Filter: Semua Data',
        ], array_slice($empty, 1));
        $data[] = $empty;
        $data[] = [
            'No',
            'Material',
            'Klien',
            'Lokasi',
            'Harga Approved',
            'Spesifikasi',
            'Satuan',
            'Status',
            'Update Terakhir',
        ];

        $statusLabels = [
            'aktif' => 'Aktif',
            'pending' => 'Pending',
            'non_aktif' => 'Non-aktif',
        ];

        $no = 1;
        foreach ($this->materials as $material) {
            $data[] = [
                $no++,
                $material->nama,
                optional($material->klien)->nama ?? '-',
                optional($material->klien)->cabang ?? '-',
                $material->harga_approved !== null ? (float) $material->harga_approved : 'Belum disetujui',
                $material->spesifikasi ?: '-',
                $material->satuan,
                $statusLabels[$material->status] ?? ucfirst((string) $material->status),
                optional($material->updated_at)->format('d/m/Y H:i') ?? '-',
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = self::LAST_COLUMN;
        $headerRow = 5;
        $lastRow = $sheet->getHighestRow();

        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->mergeCells("A3:{$lastColumn}3");
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6D28D9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(25);

        for ($row = $headerRow + 1; $row <= $lastRow; $row++) {
            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if (is_numeric($sheet->getCell("E{$row}")->getValue())) {
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                ]);
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 30,  // Material
            'C' => 28,  // Klien
            'D' => 18,  // Lokasi
            'E' => 18,  // Harga Approved
            'F' => 50,  // Spesifikasi
            'G' => 12,  // Satuan
            'H' => 12,  // Status
            'I' => 18,  // Update Terakhir
        ];
    }

    public function title(): string
    {
        return 'Spesifikasi Material';
    }
}
