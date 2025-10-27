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
        $data[] = ['LAPORAN PENGIRIMAN', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 2: Periode
        $data[] = ['Periode: ' . date('d/m/Y', strtotime($this->startDate)) . ' - ' . date('d/m/Y', strtotime($this->endDate)), '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
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
            $data[] = ['Filter: ' . implode(' | ', $filterInfo), '', '', '', '', '', '', '', '', '', '', '', '', ''];
        } else {
            $data[] = ['Filter: Semua Data', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        }
        
        // Baris 4: Waktu ekspor
        $data[] = ['Diekspor pada: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Baris 5: Kosong
        $data[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Header tabel
        $data[] = [
            'No PO',
            'Nama Pabrik',
            'Bahan Baku PO', 
            'PIC Supplier',
            'Tanggal Forecasting',
            'Hari Forecasting',
            'QTY Forecasting',
            'Total Harga Forecasting',
            'Tanggal Pengiriman',
            'Hari Pengiriman', 
            'No Pengiriman',
            'QTY Pengiriman',
            'Total Harga Pengiriman',
            'Keterangan'
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

            
            
           

            $data[] = [
                optional($pengiriman->purchaseOrder)->no_po ?? 'N/A',
                optional(optional($pengiriman->purchaseOrder)->klien)->nama ?? 'N/A', 
                $bahanBakuPO ?: 'Tidak ada detail',
                $picSuppliers ?: 'N/A',
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
                $pengiriman->catatan ?: 'Tidak ada catatan'
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
            'B' => 25,  // Nama Pabrik  
            'C' => 40,  // Bahan Baku PO
            'D' => 25,  // PIC Supplier
            'E' => 18,  // Tanggal Forecasting
            'F' => 18,  // Hari Forecasting
            'G' => 15,  // QTY Forecasting
            'H' => 20,  // Total Harga Forecasting
            'I' => 18,  // Tanggal Pengiriman
            'J' => 18,  // Hari Pengiriman
            'K' => 20,  // No Pengiriman
            'L' => 15,  // QTY Pengiriman
            'M' => 20,  // Total Harga Pengiriman
            'N' => 40,  // Keterangan (Catatan)
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
