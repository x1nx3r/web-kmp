<?php

namespace App\Exports;

use App\Models\Pengiriman;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PengirimanExport implements 
    FromArray, 
    WithColumnWidths, 
    WithTitle,
    WithStyles
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

    /**
     * @return array
     */
    public function array(): array
    {
        // Header informasi
        $data = [];
        
        // Baris 1: Judul
        $data[] = ['LAPORAN PENGIRIMAN', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 2: Periode
        $data[] = ['Periode: ' . date('d/m/Y', strtotime($this->startDate)) . ' - ' . date('d/m/Y', strtotime($this->endDate)), '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 3: Filter
        $filterInfo = [];
        if ($this->status) {
            $filterInfo[] = 'Status: ' . ucfirst($this->status);
        }
        if ($this->purchasing) {
            $purchasingName = $this->purchasingUsers ? 
                ($this->purchasingUsers->find($this->purchasing)->nama ?? 'Unknown') : 
                'ID: ' . $this->purchasing;
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
        
        if (!empty($filterInfo)) {
            $data[] = ['Filter: ' . implode(' | ', $filterInfo), '', '', '', '', '', '', '', '', '', '', '', ''];
        } else {
            $data[] = ['Filter: Semua Data', '', '', '', '', '', '', '', '', '', '', '', ''];
        }
        
        // Baris 4: Waktu ekspor
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 5: Kosong
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Header tabel
        $data[] = [
            'Tanggal Kirim',
            'Hari Kirim',
            'PIC Supplier',
            'Supplier',
            'Bahan Baku PO', 
            'Nama Pabrik',
            'QTY Forecasting',
            'Harga Jual',
            'Total Harga Forecasting',
            'QTY Pengiriman',
            'Total Harga Pengiriman',
            'Keterangan',
            'Status'
        ];

        // Data pengiriman
        $pengirimanData = $this->getPengirimanData();
        
        foreach ($pengirimanData as $pengiriman) {
            // Ambil data pengiriman details
            $pengirimanDetails = collect($pengiriman->pengirimanDetails ?? []);
            
            // Gabungkan bahan baku dari pengiriman details
            $bahanBakuPO = $pengirimanDetails->map(function($detail) {
                return optional($detail->bahanBakuSupplier)->nama ?? 'N/A';
            })->implode(', ');

            // PIC Supplier dari pengiriman details
            $picSuppliers = $pengirimanDetails->map(function($detail) {
                return optional(optional(optional($detail->bahanBakuSupplier)->supplier)->picPurchasing)->nama ?? 'N/A';
            })->unique()->implode(', ');

            // Supplier dari pengiriman details
            $suppliers = $pengirimanDetails->map(function($detail) {
                return optional(optional($detail->bahanBakuSupplier)->supplier)->nama ?? 'N/A';
            })->unique()->implode(', ');

            // ✅ Hitung Harga Jual per unit (average dari semua detail)
            $totalQtyPengiriman = $pengirimanDetails->sum('qty_kirim');
            $hargaJualPerUnit = 0;
            
            // Hitung total harga jual dari semua details
            $totalHargaJualDetails = $pengirimanDetails->map(function($detail) use ($pengiriman) {
                $hargaJual = 0;
                
                // Try to get harga_jual from orderDetail relation
                if ($detail->orderDetail) {
                    $hargaJual = $detail->orderDetail->harga_jual ?? 0;
                }
                
                // ✅ FALLBACK: If orderDetail is null or harga_jual is 0, find matching order_detail by bahan baku name
                if ($hargaJual == 0 && $pengiriman->order && $detail->bahanBakuSupplier) {
                    $namaBahanBaku = $detail->bahanBakuSupplier->nama;
                    $matchingOrderDetail = $pengiriman->order->orderDetails->first(function($od) use ($namaBahanBaku) {
                        return $od->bahanBakuKlien && $od->bahanBakuKlien->nama === $namaBahanBaku;
                    });
                    
                    if ($matchingOrderDetail) {
                        $hargaJual = $matchingOrderDetail->harga_jual ?? 0;
                    }
                }
                
                // Return total harga jual untuk detail ini
                return ($detail->qty_kirim ?? 0) * $hargaJual;
            })->sum();
            
            // Hitung average harga jual per unit
            if ($totalQtyPengiriman > 0) {
                $hargaJualPerUnit = $totalHargaJualDetails / $totalQtyPengiriman;
            }

            // QTY Forecasting
            $qtyForecasting = (float)(($pengiriman->forecast && $pengiriman->forecast->total_qty_forecast) ? 
                $pengiriman->forecast->total_qty_forecast : 0);
            
            // Total Harga Forecasting = QTY Forecasting x Harga Jual per unit
            $totalHargaForecasting = $qtyForecasting * $hargaJualPerUnit;

            // Untuk status 'berhasil', tidak tampilkan catatan
            $keterangan = '-';
            if ($pengiriman->status !== 'berhasil' && $pengiriman->catatan) {
                $keterangan = $pengiriman->catatan;
            }

            // ✅ DEFAULT: Total Harga Pengiriman = Total Harga Jual dari semua details
            $displayQty = $pengiriman->total_qty_kirim ?? 0;
            $displayHarga = $totalHargaJualDetails; // ✅ Harga Jual × Qty, bukan harga beli!
            
            // ✅ OVERRIDE: Jika status 'berhasil' dan ada invoice_penagihan, gunakan data invoice
            if ($pengiriman->status === 'berhasil' && $pengiriman->invoicePenagihan) {
                if ($pengiriman->invoicePenagihan->qty_after_refraksi !== null) {
                    $displayQty = $pengiriman->invoicePenagihan->qty_after_refraksi;
                }
                if ($pengiriman->invoicePenagihan->amount_after_refraksi !== null) {
                    $displayHarga = $pengiriman->invoicePenagihan->amount_after_refraksi;
                }
            }

            // Format status untuk display
            $statusDisplay = match($pengiriman->status) {
                'berhasil' => 'Berhasil',
                'menunggu_fisik' => 'Menunggu Fisik',
                'menunggu_verifikasi' => 'Menunggu Verifikasi',
                'pending' => 'Pending',
                'gagal' => 'Gagal',
                default => ucfirst($pengiriman->status ?? 'N/A')
            };

            // Untuk tanggal: jika pengiriman gagal dan tanggal_kirim NULL, pakai updated_at
            $displayTanggal = 'N/A';
            $displayHari = $pengiriman->hari_kirim ?? 'N/A';
            
            if ($pengiriman->tanggal_kirim) {
                $displayTanggal = \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d/m/Y');
            } elseif ($pengiriman->status === 'gagal' && $pengiriman->updated_at) {
                $displayTanggal = \Carbon\Carbon::parse($pengiriman->updated_at)->format('d/m/Y');
                // Ambil nama hari dari updated_at
                $displayHari = \Carbon\Carbon::parse($pengiriman->updated_at)->locale('id')->isoFormat('dddd');
            }

            $data[] = [
                $displayTanggal,
                $displayHari,
                $picSuppliers ?: 'N/A',
                $suppliers ?: 'N/A',
                $bahanBakuPO ?: 'Tidak ada detail',
                (optional(optional($pengiriman->order)->klien)->nama ?? 'N/A') . ' - ' . (optional(optional($pengiriman->order)->klien)->cabang ?? 'N/A'),
                number_format($qtyForecasting, 2),
                (float)$hargaJualPerUnit,
                (float)$totalHargaForecasting,
                number_format((float)$displayQty, 2),
                (float)$displayHarga,
                $keterangan,
                $statusDisplay
            ];
        }

        return $data;
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        $pengirimanData = $this->getPengirimanData();
        $lastRow = 6 + $pengirimanData->count(); // 6 adalah jumlah baris header + info
        
        // Style untuk judul (baris 1)
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style untuk info periode, filter, dan waktu ekspor (baris 2-4)
        foreach ([2, 3, 4] as $row) {
            $sheet->mergeCells("A{$row}:M{$row}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => [
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Style untuk header tabel (baris 6)
        $sheet->getStyle('A6:M6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'], // Biru seperti gambar
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Set tinggi baris header
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Style untuk data (baris 7 dan seterusnya)
        if ($lastRow > 6) {
            $sheet->getStyle("A7:M{$lastRow}")->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D0D0'],
                    ],
                ],
            ]);

            // Alignment khusus untuk kolom tertentu
            // Kolom tanggal (A) - rata tengah
            $sheet->getStyle("A7:A{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Kolom hari (B) - rata tengah
            $sheet->getStyle("B7:B{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Kolom status (M) - rata tengah
            $sheet->getStyle("M7:M{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Kolom angka (G, H, I, J, K) - rata kanan
            foreach (['G', 'H', 'I', 'J', 'K'] as $col) {
                $sheet->getStyle("{$col}7:{$col}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Format currency untuk kolom harga (H, I, K)
            $sheet->getStyle("H7:H{$lastRow}")->getNumberFormat()
                ->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("I7:I{$lastRow}")->getNumberFormat()
                ->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("K7:K{$lastRow}")->getNumberFormat()
                ->setFormatCode('"Rp "#,##0');

            // Zebra striping - baris bergantian
            for ($row = 7; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:M{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2'], // Abu-abu muda
                        ],
                    ]);
                }
            }
        }

        return [];
    }

    /**
     * Get pengiriman data with filters
     */
    private function getPengirimanData()
    {
        $query = Pengiriman::with([
            'purchasing', 
            'order.klien',
            'order.orderDetails.bahanBakuKlien', // ✅ Tambahkan untuk fallback matching
            'forecast.forecastDetails.bahanBakuSupplier.supplier',
            'pengirimanDetails.bahanBakuSupplier.supplier.picPurchasing',
            'pengirimanDetails.orderDetail',  // Tambahkan relasi order detail untuk harga jual
            'invoicePenagihan'  // Tambahkan relasi invoice penagihan
        ])->where(function($q) {
            $q->where(function($subq) {
                // Pengiriman normal (bukan gagal) - pakai tanggal_kirim
                $subq->where('status', '!=', 'gagal')
                     ->whereBetween('tanggal_kirim', [$this->startDate, $this->endDate]);
            })->orWhere(function($subq) {
                // Pengiriman gagal dengan tanggal_kirim - pakai tanggal_kirim
                $subq->where('status', 'gagal')
                     ->whereNotNull('tanggal_kirim')
                     ->whereBetween('tanggal_kirim', [$this->startDate, $this->endDate]);
            })->orWhere(function($subq) {
                // Pengiriman gagal tanpa tanggal_kirim - pakai updated_at
                $subq->where('status', 'gagal')
                     ->whereNull('tanggal_kirim')
                     ->whereBetween('updated_at', [$this->startDate, $this->endDate]);
            });
        });

        // Apply filters
        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->purchasing) {
            $query->where('purchasing_id', $this->purchasing);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('no_pengiriman', 'like', "%{$this->search}%")
                  ->orWhereHas('purchasing', function($q2) {
                      $q2->where('nama', 'like', "%{$this->search}%");
                  });
            });
        }
        
        // Filter by pabrik (klien)
        if ($this->pabrik) {
            $query->whereHas('order.klien', function($q) {
                $q->where('id', $this->pabrik);
            });
        }
        
        // Filter by supplier
        if ($this->supplier) {
            $query->whereHas('pengirimanDetails.bahanBakuSupplier.supplier', function($q) {
                $q->where('id', $this->supplier);
            });
        }

        return $query->orderBy('tanggal_kirim', 'asc')->get();
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18,  // Tanggal Kirim
            'B' => 18,  // Hari Kirim
            'C' => 25,  // PIC Supplier
            'D' => 25,  // Supplier
            'E' => 40,  // Bahan Baku PO
            'F' => 35,  // Nama Pabrik
            'G' => 18,  // QTY Forecasting
            'H' => 20,  // Harga Jual
            'I' => 22,  // Total Harga Forecasting
            'J' => 18,  // QTY Pengiriman
            'K' => 22,  // Total Harga Pengiriman
            'L' => 40,  // Keterangan
            'M' => 20,  // Status
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Laporan Pengiriman';
    }
}