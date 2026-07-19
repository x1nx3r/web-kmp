<?php

namespace App\Exports;

use App\Models\Pengiriman;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PengirimanBerhasilExport implements
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
        $data[] = ['LAPORAN PENGIRIMAN BERHASIL', '', '', '', '', '', '', '', ''];

        // Baris 2: Tanggal Export
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', ''];

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

        $data[] = [!empty($filterInfo) ? 'Filter: ' . implode(' | ', $filterInfo) : 'Filter: Semua Data Pengiriman Berhasil', '', '', '', '', '', '', '', ''];

        // Baris 4: Kosong
        $data[] = ['', '', '', '', '', '', '', '', ''];

        // Header tabel (tanpa kolom Keterangan)
        $data[] = [
            'Tanggal',
            'Hari',
            'PIC',
            'Supplier',
            'Bahan Baku',
            'Klien',
            'Qty Kirim',
            'Harga Jual',
            'Total Kirim'
        ];

        $pengirimanList = $this->getPengirimanData();
        $grandTotal = 0;

        foreach ($pengirimanList as $pengiriman) {
            $pengirimanDetails = collect($pengiriman->pengirimanDetails ?? []);

            if ($pengirimanDetails->isEmpty()) {
                $data[] = [
                    $pengiriman->tanggal_kirim ? Carbon::parse($pengiriman->tanggal_kirim)->format('d/m/Y') : 'N/A',
                    $pengiriman->hari_kirim ?? 'N/A',
                    optional($pengiriman->purchasing)->nama ?? 'N/A',
                    'N/A',
                    'Tidak ada detail',
                    optional(optional($pengiriman->order)->klien)->nama ?? 'N/A',
                    0,
                    0,
                    0
                ];
            } else {
                foreach ($pengirimanDetails as $detail) {
                    $bahanBaku = $detail->bahanBakuSupplier;
                    $supplier = optional($bahanBaku)->supplier;

                    // Qty Kirim diambil langsung dari total_qty_kirim di pengiriman detail (bukan dari forecast)
                    $qtyKirim = (float) ($detail->qty_kirim ?? 0);

                    $orderDetail = $detail->orderDetail;
                    $hargaJual = $orderDetail ? (float) $orderDetail->harga_jual : 0;

                    $totalKirim = $qtyKirim * $hargaJual;
                    $grandTotal += $totalKirim;

                    $data[] = [
                        $pengiriman->tanggal_kirim ? Carbon::parse($pengiriman->tanggal_kirim)->format('d/m/Y') : 'N/A',
                        $pengiriman->hari_kirim ?? 'N/A',
                        optional($pengiriman->purchasing)->nama ?? 'N/A',
                        optional($supplier)->nama ?? 'N/A',
                        optional($bahanBaku)->nama ?? 'N/A',
                        optional(optional($pengiriman->order)->klien)->nama ?? 'N/A',
                        $qtyKirim,
                        $hargaJual,
                        $totalKirim
                    ];
                }
            }
        }

        $data[] = ['', '', '', '', '', '', '', '', ''];

        // Baris TOTAL
        $data[] = ['', '', '', '', '', '', '', $grandTotal, ''];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $totalRow = $lastRow;
        $dataEndRow = $lastRow - 2;

        // Judul
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']], // Green-500
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        $sheet->mergeCells('A3:I3');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        // Header tabel (row 5)
        $sheet->getStyle('A5:I5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(25);

        for ($row = 6; $row <= $dataEndRow; $row++) {
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
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
        $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", "TOTAL");
        $sheet->getStyle("A{$totalRow}:I{$totalRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ]);
        $sheet->getStyle("H{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("H{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getRowDimension($totalRow)->setRowHeight(25);

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(false);
        }

        return [];
    }

    /**
     * Get pengiriman data based on filters (sama pola dengan buildIndexQuery status 'berhasil' di PengirimanController)
     */
    private function getPengirimanData()
    {
        $query = Pengiriman::with([
            'order.klien',
            'purchasing',
            'pengirimanDetails.bahanBakuSupplier.supplier',
            'pengirimanDetails.orderDetail',
        ])->whereNotNull('purchase_order_id')
          ->whereNotNull('purchasing_id')
          ->where('status', 'berhasil');

        if ($this->tanggalMulai && $this->tanggalAkhir) {
            $query->whereBetween('tanggal_kirim', [$this->tanggalMulai, $this->tanggalAkhir]);
        } elseif ($this->tanggalMulai) {
            $query->whereDate('tanggal_kirim', '>=', $this->tanggalMulai);
        } elseif ($this->tanggalAkhir) {
            $query->whereDate('tanggal_kirim', '<=', $this->tanggalAkhir);
        }

        if ($this->purchasing) {
            $query->where('purchasing_id', $this->purchasing);
        }

        if ($this->search) {
            $term = $this->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('order', function ($orderQuery) use ($term) {
                        $orderQuery->where('po_number', 'like', "%{$term}%");
                    })
                    ->orWhereHas('purchasing', function ($userQuery) use ($term) {
                        $userQuery->where('nama', 'like', "%{$term}%");
                    })
                    ->orWhere('no_pengiriman', 'like', "%{$term}%");
            });
        }

        return $query->orderBy('tanggal_kirim', 'asc')
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
            'G' => 15,  // Qty Kirim
            'H' => 20,  // Harga Jual
            'I' => 22,  // Total Kirim
        ];
    }

    public function title(): string
    {
        return 'Pengiriman Berhasil';
    }
}