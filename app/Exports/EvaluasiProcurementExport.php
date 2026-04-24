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

        // ---------------------------------------------------------
        // Summary — identik dengan controller
        // ---------------------------------------------------------
        $omsetForecasting = $forecastData->sum('computed_total_forecast');

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

        // Judul & metadata
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

        // Summary angka
        $data[] = ['OMSET FORECASTING',   'Rp ' . number_format($omsetForecasting, 2, ',', '.')] + array_fill(2, 10, '');
        $data[] = ['OMSET REALISASI',     'Rp ' . number_format($omsetRealisasi,   2, ',', '.')] + array_fill(2, 10, '');
        $data[] = ['PENGIRIMAN TAMBAHAN', 'Rp ' . number_format($omsetTambahan,    2, ',', '.')] + array_fill(2, 10, '');
        $data[] = array_fill(0, 12, '');

        // Header tabel
        $data[] = [
            'Tgl', 'Hari', 'PIC Procurement', 'Nama Supplier', 'Bahan Baku',
            'Klien - Cabang', 'Qty Forecast', 'Harga Jual', 'Total Harga Forecast',
            'Qty Kirim', 'Total Harga Kirim', 'Keterangan',
        ];

        // Baris data
        foreach ($forecastData as $f) {
            $displayTanggal = $f->display_tanggal
                ? \Carbon\Carbon::parse($f->display_tanggal)->format('d/m/Y')
                : 'N/A';
            $displayHari = $f->display_tanggal
                ? \Carbon\Carbon::parse($f->display_tanggal)->locale('id')->isoFormat('dddd')
                : 'N/A';

            $details     = $f->forecastDetails ?? collect();
            $firstDetail = $details->first();

            $supplierNama  = optional(optional(optional($firstDetail)->bahanBakuSupplier)->supplier)->nama ?? 'N/A';
            $bahanBakuNama = optional(optional($firstDetail)->bahanBakuSupplier)->nama ?? 'N/A';

            $klien       = optional(optional($f->purchaseOrder)->klien);
            $klienNama   = $klien->nama   ?? 'N/A';
            $klienCabang = $klien->cabang ?? 'N/A';

            // Harga jual dari order_detail (first detail)
            $hargaJual = (float) (optional(optional($firstDetail)->orderDetail)->harga_jual ?? 0);

            // Qty & total forecast — dari computed column subquery
            $qtyForecast       = (float) ($f->computed_qty_forecast    ?? 0);
            $totalHargaForecast = (float) ($f->computed_total_forecast  ?? 0);

            $pStatus     = $f->pengiriman_status ?? null;
            $isRealisasi = in_array($pStatus, $statusRealisasi);

            if ($isRealisasi) {
                // Gunakan nilai dari subquery (COALESCE invoice / sum detail) — selaras dengan Laporan Omset
                $qtyKirim        = (float) ($f->realisasi_qty    ?? 0);
                $totalHargaKirim = (float) ($f->realisasi_amount ?? 0);
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
                $qtyForecast,
                (float) $hargaJual,
                $totalHargaForecast,
                is_string($qtyKirim)        ? $qtyKirim        : (float) $qtyKirim,
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

    // ------------------------------------------------------------------
    // Data retrieval — reuse buildQuery dari controller lewat static cache
    // ------------------------------------------------------------------
    private function getForecastData()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        // Subquery 1: total forecast per forecast_id
        $forecastTotalsSub = DB::table('forecast_details as fd')
            ->join('order_details as od', 'fd.purchase_order_bahan_baku_id', '=', 'od.id')
            ->select(
                'fd.forecast_id',
                DB::raw('SUM(fd.qty_forecast * od.harga_jual) as total_forecast_computed'),
                DB::raw('SUM(fd.qty_forecast)               as total_qty_forecast')
            )
            ->groupBy('fd.forecast_id');

        // Subquery 2: omset realisasi per pengiriman.id (identik dengan Laporan Omset)
        // Sertakan p_tanggal_kirim agar bisa dipakai COALESCE display_tanggal di query utama.
        $pengirimanOmsetSub = DB::table('pengiriman as p')
            ->leftJoin('invoice_penagihan as ip', 'p.id', '=', 'ip.pengiriman_id')
            ->leftJoin('pengiriman_details as pd', 'p.id', '=', 'pd.pengiriman_id')
            ->leftJoin('order_details as od', 'pd.purchase_order_bahan_baku_id', '=', 'od.id')
            ->whereNull('p.deleted_at')
            ->select(
                'p.id as pengiriman_id',
                'p.forecast_id',
                'p.tanggal_kirim as p_tanggal_kirim',
                'p.status as p_status',
                'p.catatan as p_catatan',
                'p.total_harga_kirim as p_total_harga_kirim',
                'p.total_qty_kirim as p_total_qty_kirim',
                DB::raw('COALESCE(
                    MAX(ip.amount_after_refraksi),
                    SUM(pd.qty_kirim * od.harga_jual)
                ) as realisasi_amount'),
                DB::raw('COALESCE(
                    MAX(ip.qty_after_refraksi),
                    SUM(pd.qty_kirim)
                ) as realisasi_qty')
            )
            ->groupBy(
                'p.id', 'p.forecast_id', 'p.tanggal_kirim', 'p.status',
                'p.catatan', 'p.total_harga_kirim', 'p.total_qty_kirim'
            );

        $query = Forecast::with([
            'purchasing',
            'forecastDetails.bahanBakuSupplier.supplier',
            'forecastDetails.orderDetail',
            'purchaseOrder.klien',
        ])
        ->leftJoinSub($pengirimanOmsetSub, 'po', function ($join) {
            $join->on('po.forecast_id', '=', 'forecasts.id');
        })
        ->leftJoinSub($forecastTotalsSub, 'ft', function ($join) {
            $join->on('ft.forecast_id', '=', 'forecasts.id');
        })
        ->leftJoin('orders', 'forecasts.purchase_order_id', '=', 'orders.id')
        ->leftJoin('kliens', 'kliens.id', '=', 'orders.klien_id')
        ->select(
            'forecasts.*',
            DB::raw('COALESCE(po.p_tanggal_kirim, forecasts.tanggal_forecast) as display_tanggal'),
            'po.pengiriman_id',
            'po.p_tanggal_kirim as pengiriman_tanggal_kirim',
            'po.p_status        as pengiriman_status',
            'po.p_catatan       as pengiriman_catatan',
            'po.p_total_harga_kirim as pengiriman_total_harga_kirim',
            'po.p_total_qty_kirim   as pengiriman_total_qty_kirim',
            'po.realisasi_amount',
            'po.realisasi_qty',
            DB::raw('COALESCE(ft.total_forecast_computed, 0) as computed_total_forecast'),
            DB::raw('COALESCE(ft.total_qty_forecast,      0) as computed_qty_forecast'),
        )
        ->whereRaw('COALESCE(po.p_tanggal_kirim, forecasts.tanggal_forecast) between ? and ?', [$this->startDate, $this->endDate]);

        if ($this->status) {
            $query->whereNotNull('po.pengiriman_id')
                  ->where('po.p_status', $this->status);
        }
        if ($this->purchasing) {
            $query->where('forecasts.purchasing_id', $this->purchasing);
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('orders.po_number', 'like', "%{$this->search}%")
                  ->orWhere('kliens.nama',    'like', "%{$this->search}%");
            });
        }
        if ($this->pabrik) {
            $query->where('kliens.id', $this->pabrik);
        }
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

    /**
     * Hitung nilai realisasi — selaras dengan OmsetController & EvaluasiProcurementController.
     */
    private function hitungRealisasi($forecast, array $statusRealisasi): float
    {
        $pStatus = $forecast->pengiriman_status ?? null;

        if (! in_array($pStatus, $statusRealisasi)) {
            return 0.0;
        }

        // realisasi_amount sudah COALESCE(invoice, SUM detail) dari subquery
        return (float) ($forecast->realisasi_amount ?? 0);
    }
}