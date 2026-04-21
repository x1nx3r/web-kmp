<?php

namespace App\Exports;

use App\Models\Forecast;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EvaluasiProcurementExport implements FromArray, WithColumnWidths, WithTitle, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $status;
    protected $purchasing;
    protected $search;
    protected $purchasingUsers;
    protected $pabrik;
    protected $pabrikName;
    protected $supplier;
    protected $supplierName;

    public function __construct(
        $startDate,
        $endDate,
        $status          = null,
        $purchasing      = null,
        $search          = null,
        $purchasingUsers = null,
        $pabrik          = null,
        $pabrikName      = null,
        $supplier        = null,
        $supplierName    = null
    ) {
        $this->startDate       = $startDate;
        $this->endDate         = $endDate;
        $this->status          = $status;
        $this->purchasing      = $purchasing;
        $this->search          = $search;
        $this->purchasingUsers = $purchasingUsers;
        $this->pabrik          = $pabrik;
        $this->pabrikName      = $pabrikName;
        $this->supplier        = $supplier;
        $this->supplierName    = $supplierName;
    }

    public function array(): array
    {
        $forecastData    = $this->getForecastData();
        $statusRealisasi = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];

        $omsetForecasting = $forecastData->sum('total_harga_forecast');

        $omsetRealisasi = $forecastData->sum(function ($f) use ($statusRealisasi) {
            return $this->hitungRealisasi($f, $statusRealisasi);
        });

        $omsetTambahan = $forecastData
            ->filter(fn($f) => trim((string) $f->catatan) === 'Tambahan'
                && in_array($f->pengiriman_status, $statusRealisasi))
            ->sum(function ($f) use ($statusRealisasi) {
                return $this->hitungRealisasi($f, $statusRealisasi);
            });

        $data    = [];
        $colSpan = array_fill(1, 11, '');

        $data[] = array_merge(['EVALUASI PROCUREMENT'], $colSpan);
        $data[] = array_merge([
            'Periode: ' . date('d/m/Y', strtotime($this->startDate))
                . ' - ' . date('d/m/Y', strtotime($this->endDate)),
        ], $colSpan);

        $filterInfo = [];
        if ($this->status) {
            $filterInfo[] = 'Status: ' . ucfirst(str_replace('_', ' ', $this->status));
        }
        if ($this->purchasing) {
            $purchasingName = $this->purchasingUsers
                ? ($this->purchasingUsers->find($this->purchasing)->nama ?? 'Unknown')
                : 'ID: ' . $this->purchasing;
            $filterInfo[] = 'PIC: ' . $purchasingName;
        }
        if ($this->pabrik && $this->pabrikName) {
            $filterInfo[] = 'Pabrik: ' . $this->pabrikName;
        }
        if ($this->supplier && $this->supplierName) {
            $filterInfo[] = 'Supplier: ' . $this->supplierName;
        }
        if ($this->search) {
            $filterInfo[] = 'Cari: ' . $this->search;
        }

        $data[] = array_merge([
            'Filter: ' . (! empty($filterInfo) ? implode(' | ', $filterInfo) : 'Semua Data'),
        ], $colSpan);
        $data[] = array_merge(['Diekspor: ' . now()->format('d/m/Y H:i:s')], $colSpan);
        $data[] = array_fill(0, 12, '');

        $data[] = ['OMSET FORECASTING',   'Rp ' . number_format($omsetForecasting, 2, ',', '.')] + array_fill(2, 10, '');
        $data[] = ['OMSET REALISASI',     'Rp ' . number_format($omsetRealisasi,   2, ',', '.')] + array_fill(2, 10, '');
        $data[] = ['PENGIRIMAN TAMBAHAN', 'Rp ' . number_format($omsetTambahan,    2, ',', '.')] + array_fill(2, 10, '');
        $data[] = array_fill(0, 12, '');

        $data[] = [
            'Tgl', 'Hari', 'PIC Procurement', 'Nama Supplier', 'Bahan Baku',
            'Klien - Cabang', 'Qty Forecast', 'Harga Jual', 'Total Harga Forecast',
            'Qty Kirim', 'Total Harga Kirim', 'Keterangan',
        ];

        foreach ($forecastData as $f) {
            $displayTanggal = $f->display_tanggal
                ? \Carbon\Carbon::parse($f->display_tanggal)->format('d/m/Y')
                : 'N/A';
            $displayHari = $f->display_tanggal
                ? \Carbon\Carbon::parse($f->display_tanggal)->locale('id')->isoFormat('dddd')
                : 'N/A';

            $details     = $f->forecastDetails ?? collect();
            $qtyForecast = $details->sum('qty_forecast');
            $firstDetail = $details->first();

            $supplierNama  = optional(optional(optional($firstDetail)->bahanBakuSupplier)->supplier)->nama ?? 'N/A';
            $bahanBakuNama = optional(optional($firstDetail)->bahanBakuSupplier)->nama ?? 'N/A';

            $klien       = optional(optional($f->purchaseOrder)->klien);
            $klienNama   = $klien->nama   ?? 'N/A';
            $klienCabang = $klien->cabang ?? 'N/A';

            $hargaJual = (float) (optional(optional($firstDetail)->orderDetail)->harga_jual ?? 0);

            $pStatus     = $f->pengiriman_status ?? null;
            $isRealisasi = in_array($pStatus, $statusRealisasi);

            if ($isRealisasi) {
                $qtyKirim = ($pStatus === 'berhasil' && ! is_null($f->invoice_qty))
                    ? (float) $f->invoice_qty
                    : (float) ($f->pengiriman_total_qty_kirim ?? 0);
                $totalHargaKirim = ($pStatus === 'berhasil' && ! is_null($f->invoice_amount))
                    ? (float) $f->invoice_amount
                    : (float) ($f->pengiriman_total_harga_kirim ?? 0);
            } else {
                $qtyKirim        = '-';
                $totalHargaKirim = '-';
            }

            if ($isRealisasi) {
                $keterangan = 'Done';
            } elseif ($pStatus === 'gagal') {
                $keterangan = $f->pengiriman_catatan ?? '-';
            } else {
                $keterangan = '';
            }

            $isTambahan   = trim((string) $f->catatan) === 'Tambahan';
            $tambahanSufx = $isTambahan ? ' [TAMBAHAN]' : '';

            $data[] = [
                $displayTanggal,
                $displayHari . $tambahanSufx,
                optional($f->purchasing)->nama ?? 'N/A',
                $supplierNama,
                $bahanBakuNama,
                $klienNama . ' - ' . $klienCabang,
                (float) $qtyForecast,
                (float) $hargaJual,
                (float) $f->total_harga_forecast,
                is_string($qtyKirim) ? $qtyKirim : (float) $qtyKirim,
                is_string($totalHargaKirim) ? $totalHargaKirim : (float) $totalHargaKirim,
                $keterangan,
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $forecastData = $this->getForecastData();

        $headerTableRow = 10;
        $dataStartRow   = 11;
        $lastRow        = $dataStartRow + $forecastData->count() - 1;

        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        foreach ([2, 3, 4] as $r) {
            $sheet->mergeCells("A{$r}:L{$r}");
            $sheet->getStyle("A{$r}")->applyFromArray([
                'font'      => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
        }

        foreach ([6, 7, 8] as $r) {
            $sheet->getStyle("A{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF9C4']],
            ]);
            $sheet->getStyle("B{$r}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF9C4']],
            ]);
        }

        $sheet->getStyle("A{$headerTableRow}:L{$headerTableRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ]);
        $sheet->getRowDimension($headerTableRow)->setRowHeight(30);

        if ($lastRow >= $dataStartRow) {
            $sheet->getStyle("A{$dataStartRow}:L{$lastRow}")->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]],
            ]);

            $sheet->getStyle("A{$dataStartRow}:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            foreach (['G', 'H', 'I', 'J', 'K'] as $col) {
                $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$lastRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            $sheet->getStyle("G{$dataStartRow}:G{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("H{$dataStartRow}:H{$lastRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->getStyle("I{$dataStartRow}:I{$lastRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->getStyle("J{$dataStartRow}:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("K{$dataStartRow}:K{$lastRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0.00');

            $sheet->getStyle("L{$dataStartRow}:L{$lastRow}")->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                  ->setWrapText(true);

            for ($row = $dataStartRow; $row <= $lastRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                    ]);
                }
            }

            $dataIdx = 0;
            foreach ($forecastData as $f) {
                $row = $dataStartRow + $dataIdx;
                if (trim((string) $f->catatan) === 'Tambahan') {
                    $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF9C4']],
                    ]);
                }
                $dataIdx++;
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 16,
            'C' => 20,
            'D' => 24,
            'E' => 24,
            'F' => 32,
            'G' => 14,
            'H' => 18,
            'I' => 22,
            'J' => 14,
            'K' => 22,
            'L' => 30,
        ];
    }

    public function title(): string
    {
        return 'Evaluasi Procurement';
    }

    private function getForecastData()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $displayTanggalExpr = 'COALESCE(pengiriman.tanggal_kirim, forecasts.tanggal_forecast)';

        $query = Forecast::with([
            'purchasing',
            'pengiriman.invoicePenagihan',
            'forecastDetails.bahanBakuSupplier.supplier',
            'forecastDetails.orderDetail',
            'purchaseOrder.klien',
        ])
        ->leftJoin('pengiriman', 'pengiriman.forecast_id', '=', 'forecasts.id')
        ->leftJoin('invoice_penagihan', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
        ->leftJoin('orders', 'forecasts.purchase_order_id', '=', 'orders.id')
        ->leftJoin('kliens', 'kliens.id', '=', 'orders.klien_id') // ← JOIN klien langsung
        ->select(
            'forecasts.*',
            DB::raw("({$displayTanggalExpr}) as display_tanggal"),
            'pengiriman.id as pengiriman_id',
            'pengiriman.status as pengiriman_status',
            'pengiriman.catatan as pengiriman_catatan',
            'pengiriman.total_harga_kirim as pengiriman_total_harga_kirim',
            'pengiriman.total_qty_kirim as pengiriman_total_qty_kirim',
            'invoice_penagihan.amount_after_refraksi as invoice_amount',
            'invoice_penagihan.qty_after_refraksi as invoice_qty'
        )
        ->whereRaw("({$displayTanggalExpr}) between ? and ?", [$this->startDate, $this->endDate]);

        // Filter status: pastikan pengiriman ada kalau status dipilih
        if ($this->status) {
            $query->whereNotNull('pengiriman.id')
                  ->where('pengiriman.status', $this->status);
        }

        // Filter PIC purchasing
        if ($this->purchasing) {
            $query->where('forecasts.purchasing_id', $this->purchasing);
        }

        // Filter search: pakai join yang sudah ada, bukan whereHas
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('orders.po_number', 'like', "%{$this->search}%")
                  ->orWhere('kliens.nama', 'like', "%{$this->search}%");
            });
        }

        // Filter pabrik: pakai join yang sudah ada, bukan whereHas
        if ($this->pabrik) {
            $query->where('kliens.id', $this->pabrik);
        }

        // Filter supplier: tetap pakai whereHas karena relasinya 3 level dalam
        if ($this->supplier) {
            $query->whereHas('forecastDetails.bahanBakuSupplier', function ($q) {
                $q->where('supplier_id', $this->supplier);
            });
        }

        $cached = $query
            ->orderBy('display_tanggal', 'asc')
            ->orderBy('forecasts.id', 'asc')
            ->get();

        return $cached;
    }

    private function hitungRealisasi($forecast, array $statusRealisasi): float
    {
        $pStatus = $forecast->pengiriman_status ?? null;
        if (! in_array($pStatus, $statusRealisasi)) {
            return 0.0;
        }
        if ($pStatus === 'berhasil' && ! is_null($forecast->invoice_amount)) {
            return (float) $forecast->invoice_amount;
        }
        return (float) ($forecast->pengiriman_total_harga_kirim ?? 0);
    }
}