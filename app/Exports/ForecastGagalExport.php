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

class ForecastGagalExport implements
    FromArray,
    WithColumnWidths,
    WithTitle,
    WithStyles
{
    protected $tanggalMulai;
    protected $tanggalAkhir;
    protected $purchasing;
    protected $search;

    public function __construct($tanggalMulai = null, $tanggalAkhir = null, $purchasing = null, $search = null)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->purchasing = $purchasing;
        $this->search = $search;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $data = [];

        // Baris 1: Judul
        $data[] = ['LAPORAN FORECAST GAGAL', '', '', '', '', '', '', '', '', ''];

        // Baris 2: Tanggal Export
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', ''];

        // Baris 3: Filter
        $filterInfo = [];
        if ($this->tanggalMulai && $this->tanggalAkhir) {
            $filterInfo[] = 'Periode: ' . date('d/m/Y', strtotime($this->tanggalMulai)) . ' - ' . date('d/m/Y', strtotime($this->tanggalAkhir));
        } elseif ($this->tanggalMulai) {
            $filterInfo[] = 'Dari Tanggal: ' . date('d/m/Y', strtotime($this->tanggalMulai));
        } elseif ($this->tanggalAkhir) {
            $filterInfo[] = 'Sampai Tanggal: ' . date('d/m/Y', strtotime($this->tanggalAkhir));
        }
        if ($this->purchasing) {
            $filterInfo[] = 'PIC Purchasing ID: ' . $this->purchasing;
        }
        if ($this->search) {
            $filterInfo[] = 'Pencarian: ' . $this->search;
        }

        $data[] = [!empty($filterInfo) ? 'Filter: ' . implode(' | ', $filterInfo) : 'Filter: Semua Data Forecast Gagal', '', '', '', '', '', '', '', '', ''];

        // Baris 4: Kosong
        $data[] = ['', '', '', '', '', '', '', '', '', ''];

        // Header tabel
        $data[] = [
            'Tanggal',
            'Hari',
            'PIC',
            'Supplier',
            'Bahan Baku',
            'Klien',
            'Qty Forecast',
            'Harga Jual',
            'Total Forecast',
            'Keterangan'
        ];

        $forecasts = $this->getForecastData();
        $grandTotal = 0;

        foreach ($forecasts as $forecast) {
            $forecastDetails = collect($forecast->forecastDetails ?? []);

            // Ambil keterangan dari pengiriman (status gagal) terkait forecast ini
            $pengirimanGagal = $forecast->pengiriman
                ->where('status', 'gagal')
                ->sortByDesc('created_at')
                ->first();
            $keterangan = optional($pengirimanGagal)->catatan ?? 'N/A';

            if ($forecastDetails->isEmpty()) {
                $data[] = [
                    $forecast->tanggal_forecast ? Carbon::parse($forecast->tanggal_forecast)->format('d/m/Y') : 'N/A',
                    $forecast->hari_kirim_forecast ?? 'N/A',
                    optional($forecast->purchasing)->nama ?? 'N/A',
                    'N/A',
                    'Tidak ada detail',
                    optional(optional($forecast->order)->klien)->nama ?? 'N/A',
                    0,
                    0,
                    0,
                    $keterangan
                ];
            } else {
                foreach ($forecastDetails as $detail) {
                    $bahanBaku = $detail->bahanBakuSupplier;
                    $supplier = optional($bahanBaku)->supplier;

                    $orderDetail = $detail->orderDetail;
                    $hargaJual = $orderDetail ? (float) $orderDetail->harga_jual : 0;

                    $totalForecastDetail = (float) ($detail->qty_forecast ?? 0) * $hargaJual;
                    $grandTotal += $totalForecastDetail;

                    $data[] = [
                        $forecast->tanggal_forecast ? Carbon::parse($forecast->tanggal_forecast)->format('d/m/Y') : 'N/A',
                        $forecast->hari_kirim_forecast ?? 'N/A',
                        optional($forecast->purchasing)->nama ?? 'N/A',
                        optional($supplier)->nama ?? 'N/A',
                        optional($bahanBaku)->nama ?? 'N/A',
                        optional(optional($forecast->order)->klien)->nama ?? 'N/A',
                        (float) ($detail->qty_forecast ?? 0),
                        $hargaJual,
                        $totalForecastDetail,
                        $keterangan
                    ];
                }
            }
        }

        $data[] = ['', '', '', '', '', '', '', '', '', ''];

        // Baris TOTAL
        $data[] = ['', '', '', '', '', '', '', '', $grandTotal, ''];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $totalRow = $lastRow;
        $dataEndRow = $lastRow - 2;

        // Judul
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EF4444']], // Red-500
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        $sheet->mergeCells('A3:J3');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        // Header tabel (row 5)
        $sheet->getStyle('A5:J5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(25);

        for ($row = 6; $row <= $dataEndRow; $row++) {
            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']]
                ]);
            }
        }

        for ($row = 6; $row <= $dataEndRow; $row++) {
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

        // Baris TOTAL
        $sheet->mergeCells("A{$totalRow}:H{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", "TOTAL");
        $sheet->getStyle("A{$totalRow}:J{$totalRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ]);
        $sheet->getStyle("I{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("I{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getRowDimension($totalRow)->setRowHeight(25);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(false);
        }

        return [];
    }

    /**
     * Get forecast data based on filters (sama seperti getForecastsByStatus('gagal') di controller, tanpa pagination)
     */
    private function getForecastData()
    {
        $query = Forecast::with([
            'order.klien',
            'purchasing',
            'forecastDetails.bahanBakuSupplier.supplier',
            'forecastDetails.orderDetail',
            'forecastDetails.purchaseOrderBahanBaku.bahanBakuKlien',
            'pengiriman'
        ])->where('status', 'gagal');

        if ($this->tanggalMulai && $this->tanggalAkhir) {
            $query->whereBetween('tanggal_forecast', [$this->tanggalMulai, $this->tanggalAkhir]);
        } elseif ($this->tanggalMulai) {
            $query->whereDate('tanggal_forecast', '>=', $this->tanggalMulai);
        } elseif ($this->tanggalAkhir) {
            $query->whereDate('tanggal_forecast', '<=', $this->tanggalAkhir);
        }

        if ($this->purchasing) {
            $query->where('purchasing_id', $this->purchasing);
        }

        if ($this->search) {
            $term = $this->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('order', function ($orderQuery) use ($term) {
                        $orderQuery->where('po_number', 'like', "%{$term}%")
                                ->orWhereHas('klien', function ($klienQuery) use ($term) {
                                    $klienQuery->where('nama', 'like', "%{$term}%");
                                });
                    })
                    ->orWhereHas('purchasing', function ($userQuery) use ($term) {
                        $userQuery->where('nama', 'like', "%{$term}%");
                    })
                    ->orWhereHas('forecastDetails.purchaseOrderBahanBaku.bahanBakuKlien', function ($bbQuery) use ($term) {
                        $bbQuery->where('nama', 'like', "%{$term}%");
                    });
            });
        }

        return $query->orderBy('tanggal_forecast', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Tanggal
            'B' => 20,  // Hari
            'C' => 25,  // PIC
            'D' => 30,  // Supplier
            'E' => 40,  // Bahan Baku
            'F' => 30,  // Klien
            'G' => 15,  // Qty Forecast
            'H' => 20,  // Harga Jual
            'I' => 22,  // Total Forecast
            'J' => 45,  // Keterangan
        ];
    }

    public function title(): string
    {
        return 'Forecast Gagal';
    }
}