<?php

namespace App\Exports;

use App\Models\Pengiriman;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;

class PengirimanExport implements 
    FromArray, 
    WithColumnWidths, 
    WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $status;
    protected $purchasing;
    protected $search;
    protected $purchasingUsers;

    public function __construct($startDate, $endDate, $status = null, $purchasing = null, $search = null, $purchasingUsers = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->purchasing = $purchasing;
        $this->search = $search;
        $this->purchasingUsers = $purchasingUsers;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Header informasi
        $data = [];
        
        // Baris 1: Judul
        $data[] = ['LAPORAN PENGIRIMAN', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 2: Periode
        $data[] = ['Periode: ' . date('d/m/Y', strtotime($this->startDate)) . ' - ' . date('d/m/Y', strtotime($this->endDate)), '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
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
        if ($this->search) {
            $filterInfo[] = 'Pencarian: ' . $this->search;
        }
        
        if (!empty($filterInfo)) {
            $data[] = ['Filter: ' . implode(' | ', $filterInfo), '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        } else {
            $data[] = ['Filter: Semua Data', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        }
        
        // Baris 4: Waktu ekspor
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 5: Kosong
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Header tabel (dengan 2 kolom baru)
        $data[] = [
            'No PO',
            'Nama Pabrik',
            'Bahan Baku PO', 
            'Supplier',
            'PIC Purchasing',
            'Tanggal Forecasting',
            'Hari Forecasting',
            'QTY Forecasting',
            'Total Harga Forecasting',
            'Tanggal Pengiriman',
            'Hari Pengiriman', 
            'No Pengiriman',
            'QTY Pengiriman',
            'Total Harga Pengiriman',
            'Keterangan',
            'Status Pengiriman'
        ];

        // Data pengiriman
        $pengirimanData = $this->getPengirimanData();
        
        foreach ($pengirimanData as $pengiriman) {
            // Ambil data forecast details untuk mendapatkan bahan baku PO
            $forecastDetails = collect();
            if ($pengiriman->forecast && $pengiriman->forecast->forecastDetails) {
                $forecastDetails = $pengiriman->forecast->forecastDetails;
            }
            
            // Gabungkan bahan baku dari forecast details
            $bahanBakuPO = $forecastDetails->map(function($detail) {
                $bahanBaku = optional($detail->bahanBakuSupplier)->nama ?? 'N/A';
                return "{$bahanBaku}";
            })->implode(', ');

            // PIC Supplier dari forecast details
            $picSuppliers = $forecastDetails->map(function($detail) {
                return optional(optional($detail->bahanBakuSupplier)->supplier)->nama ?? 'N/A';
            })->unique()->implode(', ');

            // PIC Purchasing name
            $picPurchasing = optional($pengiriman->purchasing)->nama ?? 'N/A';
            
            // Status label
            $statusLabel = 'N/A';
            switch($pengiriman->status) {
                case 'pending':
                    $statusLabel = 'Pending';
                    break;
                case 'menunggu_verifikasi':
                    $statusLabel = 'Menunggu Verifikasi';
                    break;
                case 'berhasil':
                    $statusLabel = 'Berhasil';
                    break;
                case 'gagal':
                    $statusLabel = 'Gagal';
                    break;
                default:
                    $statusLabel = ucfirst($pengiriman->status ?? 'N/A');
            }

            $data[] = [
                optional($pengiriman->purchaseOrder)->no_po ?? 'N/A',
                (optional(optional($pengiriman->purchaseOrder)->klien)->nama ?? 'N/A') . ' - ' . (optional(optional($pengiriman->purchaseOrder)->klien)->cabang ?? 'N/A'),
                $bahanBakuPO ?: 'Tidak ada detail',
                $picSuppliers ?: 'N/A',
                $picPurchasing, // Kolom PIC Purchasing
                ($pengiriman->forecast && $pengiriman->forecast->tanggal_forecast) ? 
                    \Carbon\Carbon::parse($pengiriman->forecast->tanggal_forecast)->format('d/m/Y') : 'N/A',
                ($pengiriman->forecast && $pengiriman->forecast->hari_kirim_forecast) ? 
                    $pengiriman->forecast->hari_kirim_forecast : 'N/A',
                number_format((float)(($pengiriman->forecast && $pengiriman->forecast->total_qty_forecast) ? $pengiriman->forecast->total_qty_forecast : 0)),
                'Rp ' . number_format((float)(($pengiriman->forecast && $pengiriman->forecast->total_harga_forecast) ? $pengiriman->forecast->total_harga_forecast : 0)),
                $pengiriman->tanggal_kirim ? 
                    \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d/m/Y') : 'N/A',
                $pengiriman->hari_kirim ?? 'N/A',
                $pengiriman->no_pengiriman ?? 'N/A',
                number_format((float)($pengiriman->total_qty_kirim ?? 0), 2),
                'Rp ' . number_format((float)($pengiriman->total_harga_kirim ?? 0), 0),
                $pengiriman->catatan ?: '-',
                $statusLabel // Kolom Status Pengiriman
            ];
        }

        return $data;
    }

    /**
     * Get pengiriman data with filters
     */
    private function getPengirimanData()
    {
        $query = Pengiriman::with([
            'purchasing', 
            'purchaseOrder.klien', 
            'forecast.forecastDetails.bahanBakuSupplier.supplier',
            'pengirimanDetails.bahanBakuSupplier.supplier'
        ])->whereBetween('tanggal_kirim', [$this->startDate, $this->endDate]);

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

        return $query->orderBy('tanggal_kirim', 'desc')->get();
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // No PO
            'B' => 35,  // Nama Pabrik (diperlebar untuk menampung nama + cabang)
            'C' => 40,  // Bahan Baku PO
            'D' => 25,  // PIC Supplier
            'E' => 20,  // PIC Purchasing (kolom baru)
            'F' => 18,  // Tanggal Forecasting
            'G' => 18,  // Hari Forecasting
            'H' => 15,  // QTY Forecasting
            'I' => 20,  // Total Harga Forecasting
            'J' => 18,  // Tanggal Pengiriman
            'K' => 18,  // Hari Pengiriman
            'L' => 20,  // No Pengiriman
            'M' => 15,  // QTY Pengiriman
            'N' => 20,  // Total Harga Pengiriman
            'O' => 40,  // Keterangan (Catatan)
            'P' => 20,  // Status Pengiriman (kolom baru)
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