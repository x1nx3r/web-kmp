<?php

namespace App\Exports;

use App\Models\Pengiriman;
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

    public function __construct($startDate, $endDate, $status = null, $purchasing = null, $search = null, $purchasingUsers = null, $pabrik = null, $pabrikName = null, $supplier = null, $supplierName = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->purchasing = $purchasing;
        $this->search = $search;
        $this->purchasingUsers = $purchasingUsers;
        $this->pabrik = $pabrik;
        $this->pabrikName = $pabrikName;
        $this->supplier = $supplier;
        $this->supplierName = $supplierName;
    }

    public function array(): array
    {
        $data = [];

        // Header informasi (mirip PengirimanExport)
        $data[] = ['EVALUASI PROCUREMENT', '', '', '', '', '', '', '', '', ''];
        $data[] = ['Periode: ' . date('d/m/Y', strtotime($this->startDate)) . ' - ' . date('d/m/Y', strtotime($this->endDate)), '', '', '', '', '', '', '', '', ''];

        $filterInfo = [];
        if ($this->status) {
            $filterInfo[] = 'Status: ' . ucfirst($this->status);
        }
        if ($this->purchasing) {
            $purchasingName = $this->purchasingUsers ? ($this->purchasingUsers->find($this->purchasing)->nama ?? 'Unknown') : ('ID: ' . $this->purchasing);
            $filterInfo[] = 'PIC Purchasing: ' . $purchasingName;
        }
        if ($this->pabrik && $this->pabrikName) {
            $filterInfo[] = 'Pabrik: ' . $this->pabrikName;
        }
        if ($this->supplier && $this->supplierName) {
            $filterInfo[] = 'Supplier: ' . $this->supplierName;
        }
        if ($this->search) {
            $filterInfo[] = 'Pencarian: ' . $this->search;
        }

        $data[] = [
            'Filter: ' . (!empty($filterInfo) ? implode(' | ', $filterInfo) : 'Semua Data'),
            '', '', '', '', '', '', '', '', ''
        ];
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '', '', ''];

        // Header tabel
        $data[] = [
            'Tanggal',
            'Hari',
            'Purchasing',
            'Supplier',
            'Klien - Cabang',
            'Produk',
            'Qty',
            'Harga Jual',
            'Jumlah',
            'Keterangan',
        ];

        $pengirimanData = $this->getPengirimanData();

        foreach ($pengirimanData as $pengiriman) {
            $details = collect($pengiriman->pengirimanDetails ?? []);

            $supplierNames = $details->map(fn($d) => optional(optional($d->bahanBakuSupplier)->supplier)->nama)->filter()->unique()->values();
            $produkNames = $details->map(fn($d) => optional($d->bahanBakuSupplier)->nama)->filter()->unique()->values();

            $supplierDisplay = $supplierNames->isNotEmpty() ? $supplierNames->implode(', ') : 'N/A';
            $produkDisplay = $produkNames->isNotEmpty() ? $produkNames->implode(', ') : ($details->isNotEmpty() ? 'N/A' : 'Tidak ada detail');

            // Hitung total harga jual (mengikuti pola PengirimanExport)
            $totalQtyPengiriman = (float) $details->sum('qty_kirim');
            $totalHargaJualDetails = (float) $details->map(function ($detail) use ($pengiriman) {
                $hargaJual = 0;
                if ($detail->orderDetail) {
                    $hargaJual = $detail->orderDetail->harga_jual ?? 0;
                }
                if ($hargaJual == 0 && $pengiriman->order && $detail->bahanBakuSupplier) {
                    $namaBahanBaku = $detail->bahanBakuSupplier->nama;
                    $matchingOrderDetail = $pengiriman->order->orderDetails->first(function ($od) use ($namaBahanBaku) {
                        return $od->bahanBakuKlien && $od->bahanBakuKlien->nama === $namaBahanBaku;
                    });
                    if ($matchingOrderDetail) {
                        $hargaJual = $matchingOrderDetail->harga_jual ?? 0;
                    }
                }
                return ((float)($detail->qty_kirim ?? 0)) * (float)$hargaJual;
            })->sum();

            $hargaJualPerUnit = $totalQtyPengiriman > 0 ? ($totalHargaJualDetails / $totalQtyPengiriman) : 0;

            // Qty & jumlah yang ditampilkan mengikuti logic pengiriman export (invoice override jika berhasil)
            $displayQty = (float)($pengiriman->total_qty_kirim ?? 0);
            $displayJumlah = $totalHargaJualDetails;

            if ($pengiriman->status === 'berhasil' && $pengiriman->invoicePenagihan) {
                if ($pengiriman->invoicePenagihan->qty_after_refraksi !== null) {
                    $displayQty = (float)$pengiriman->invoicePenagihan->qty_after_refraksi;
                }
                if ($pengiriman->invoicePenagihan->amount_after_refraksi !== null) {
                    $displayJumlah = (float)$pengiriman->invoicePenagihan->amount_after_refraksi;
                }
            }

            // Tanggal/hari display: ambil dari forecast (tanggal_forecast min/terawal)
            $displayTanggal = 'N/A';
            $displayHari = 'N/A';
            if ($pengiriman->tanggal_forecast_min) {
                $displayTanggal = \Carbon\Carbon::parse($pengiriman->tanggal_forecast_min)->format('d/m/Y');
            }
            if ($pengiriman->hari_kirim_forecast_min) {
                $displayHari = $pengiriman->hari_kirim_forecast_min;
            } elseif ($pengiriman->tanggal_forecast_min) {
                // fallback hitung dari tanggal forecast
                $displayHari = \Carbon\Carbon::parse($pengiriman->tanggal_forecast_min)->locale('id')->isoFormat('dddd');
            }

            // Keterangan: selain gagal => default 'Done' (kalau tidak ada catatan)
            if (($pengiriman->status ?? null) === 'gagal') {
                $keterangan = $pengiriman->catatan ?: '-';
            } else {
                $keterangan = $pengiriman->catatan ?: 'Done';
            }

            $data[] = [
                $displayTanggal,
                $displayHari,
                optional($pengiriman->purchasing)->nama ?? 'N/A',
                $supplierDisplay,
                (optional(optional($pengiriman->order)->klien)->nama ?? 'N/A') . ' - ' . (optional(optional($pengiriman->order)->klien)->cabang ?? 'N/A'),
                $produkDisplay,
                number_format($displayQty, 2, ',', '.'),
                (float)$hargaJualPerUnit,
                (float)$displayJumlah,
                $keterangan,
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $pengirimanData = $this->getPengirimanData();
        $lastRow = 6 + $pengirimanData->count();

        // Title
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        foreach ([2, 3, 4] as $row) {
            $sheet->mergeCells("A{$row}:J{$row}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // Table header at row 6
        $sheet->getStyle('A6:J6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(30);

        if ($lastRow > 6) {
            $sheet->getStyle("A7:J{$lastRow}")->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]],
            ]);

            // Alignments
            $sheet->getStyle("A7:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            foreach (['G', 'H', 'I'] as $col) {
                $sheet->getStyle("{$col}7:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Currency format
            $sheet->getStyle("H7:H{$lastRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("I7:I{$lastRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

            // Zebra striping
            for ($row = 7; $row <= $lastRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                    ]);
                }
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 14,
            'C' => 18,
            'D' => 22,
            'E' => 32,
            'F' => 32,
            'G' => 12,
            'H' => 16,
            'I' => 18,
            'J' => 40,
        ];
    }

    public function title(): string
    {
        return 'Evaluasi Procurement';
    }

    private function getPengirimanData()
    {
        $forecastMinSub = DB::table('forecasts')
            ->join('pengiriman', 'pengiriman.forecast_id', '=', 'forecasts.id')
            ->selectRaw('pengiriman.id as pengiriman_id, MIN(forecasts.tanggal_forecast) as tanggal_forecast_min, MIN(forecasts.hari_kirim_forecast) as hari_kirim_forecast_min')
            ->groupBy('pengiriman.id');

        $query = Pengiriman::with([
            'purchasing',
            'order.klien',
            'order.orderDetails.bahanBakuKlien',
            'pengirimanDetails.bahanBakuSupplier.supplier.picPurchasing',
            'pengirimanDetails.orderDetail',
            'invoicePenagihan',
        ])
            ->leftJoinSub($forecastMinSub, 'forecast_min', function ($join) {
                $join->on('pengiriman.id', '=', 'forecast_min.pengiriman_id');
            })
            ->addSelect([
                'pengiriman.*',
                'forecast_min.tanggal_forecast_min as tanggal_forecast_min',
                'forecast_min.hari_kirim_forecast_min as hari_kirim_forecast_min',
            ])
            ->whereBetween('forecast_min.tanggal_forecast_min', [$this->startDate, $this->endDate]);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->purchasing) {
            $query->where('purchasing_id', $this->purchasing);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('no_pengiriman', 'like', "%{$this->search}%")
                    ->orWhereHas('order', function ($q2) {
                        $q2->where('po_number', 'like', "%{$this->search}%");
                    });
            });
        }

        if ($this->pabrik) {
            $query->whereHas('order.klien', function ($q) {
                $q->where('id', $this->pabrik);
            });
        }

        if ($this->supplier) {
            $query->whereHas('pengirimanDetails.bahanBakuSupplier.supplier', function ($q) {
                $q->where('id', $this->supplier);
            });
        }

        return $query->orderBy('forecast_min.tanggal_forecast_min', 'asc')->orderBy('pengiriman.id', 'asc')->get();
    }
}
