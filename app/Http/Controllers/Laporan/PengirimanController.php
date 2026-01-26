<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\PengirimanDetail;
use App\Models\User;
use App\Exports\PengirimanExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Pengiriman';
        $activeTab = 'pengiriman';
        
        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status');
        $purchasing = $request->get('purchasing');
        $search = $request->get('search');
        $pabrik = $request->get('pabrik');
        $supplier = $request->get('supplier');
        
        // Calculate weekly statistics - mengikuti logic dari dashboard (pembagian bulan menjadi 4 minggu)
        $startOfMonth = Carbon::now()->startOfMonth();
        $currentWeekOfMonth = 1;
        $tempDate = $startOfMonth->copy();
        
        while ($tempDate->addDays(7)->lte(Carbon::now()->startOfWeek())) {
            $currentWeekOfMonth++;
        }
        $currentWeekOfMonth = min($currentWeekOfMonth, 4);
        
        // Calculate date range for this week based on month divisions
        if ($currentWeekOfMonth == 1) {
            $weekStart = $startOfMonth->copy();
        } else {
            $weekStart = $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);
        }
        
        if ($currentWeekOfMonth == 4) {
            $weekEnd = $startOfMonth->copy()->endOfMonth();
        } else {
            $weekEnd = $weekStart->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
        }
        
        $weeklyStats = $this->getWeeklyStats($weekStart, $weekEnd);
        $yearlyStats = $this->getYearlyStats(now()->year);
        $totalStats = $this->getTotalStats();
        
        // Get pie chart filter parameter (default: bulan_ini)
        $pieChartFilter = $request->get('pie_filter', 'bulan_ini');
        $pieChartStartDate = null;
        $pieChartEndDate = null;
        
        // Determine date range for pie chart based on filter
        switch ($pieChartFilter) {
            case 'semua':
                // All data - no date filter
                break;
            case 'bulan_ini':
                $pieChartStartDate = now()->startOfMonth()->format('Y-m-d');
                $pieChartEndDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'tahun_ini':
                $pieChartStartDate = now()->startOfYear()->format('Y-m-d');
                $pieChartEndDate = now()->endOfYear()->format('Y-m-d');
                break;
            case 'range':
                $pieChartStartDate = $request->get('pie_start_date', now()->startOfMonth()->format('Y-m-d'));
                $pieChartEndDate = $request->get('pie_end_date', now()->endOfMonth()->format('Y-m-d'));
                break;
        }
        
        // Get pie chart data
        $pieChartData = $this->getPieChartData($pieChartFilter, $pieChartStartDate, $pieChartEndDate);
        
        // Get year range from tanggal_kirim
        $yearRange = $this->getYearRange();
        
        // Get selected year or default to current year
        $selectedYear = $request->get('year', now()->year);
        
        // Get yearly chart data for purchasing PIC
        $chartData = $this->getYearlyChartData($selectedYear);
        
        // Get paginated pengiriman data with filters
        // Untuk status berhasil, join dengan invoice_penagihan untuk ambil qty dan harga setelah refraksi
        $pengirimanQuery = Pengiriman::with(['purchasing', 'purchaseOrder', 'pengirimanDetails.bahanBakuSupplier', 'invoicePenagihan'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->select('pengiriman.*', 
                'orders.po_number',
                DB::raw('CASE 
                    WHEN pengiriman.status = "berhasil" AND invoice_penagihan.qty_after_refraksi IS NOT NULL 
                    THEN invoice_penagihan.qty_after_refraksi 
                    ELSE pengiriman.total_qty_kirim 
                END as display_qty'),
                DB::raw('CASE 
                    WHEN pengiriman.status = "berhasil" AND invoice_penagihan.amount_after_refraksi IS NOT NULL 
                    THEN invoice_penagihan.amount_after_refraksi 
                    ELSE pengiriman.total_harga_kirim 
                END as display_harga')
            )
            ->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
        
        // Apply filters
        if ($status) {
            $pengirimanQuery->where('pengiriman.status', $status);
        }
        
        if ($purchasing) {
            $pengirimanQuery->where('pengiriman.purchasing_id', $purchasing);
        }
        
        if ($search) {
            $pengirimanQuery->where(function($q) use ($search) {
                $q->where('pengiriman.no_pengiriman', 'like', "%{$search}%")
                  ->orWhere('orders.po_number', 'like', "%{$search}%");
            });
        }
        
        // Filter by pabrik (klien)
        if ($pabrik) {
            $pengirimanQuery->whereHas('order.klien', function($q) use ($pabrik) {
                $q->where('id', $pabrik);
            });
        }
        
        // Filter by supplier
        if ($supplier) {
            $pengirimanQuery->whereHas('pengirimanDetails.bahanBakuSupplier.supplier', function($q) use ($supplier) {
                $q->where('id', $supplier);
            });
        }
        
        $pengirimanData = $pengirimanQuery->orderBy('pengiriman.tanggal_kirim', 'desc')->paginate(15);
        
        // Get purchasing users for filter dropdown (including direktur)
        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing', 'direktur'])->get();
        
        // Get pabrik (klien) list for filter dropdown
        $pabrikList = \App\Models\Klien::orderBy('nama', 'asc')->get();
        
        // Get supplier list for filter dropdown
        $supplierList = \App\Models\Supplier::orderBy('nama', 'asc')->get();
        
        return view('pages.laporan.pengiriman', compact(
            'title', 
            'activeTab',
            'weeklyStats',
            'yearlyStats',
            'totalStats',
            'chartData',
            'yearRange',
            'pengirimanData',
            'purchasingUsers',
            'pabrikList',
            'supplierList',
            'startDate',
            'endDate',
            'status',
            'purchasing',
            'search',
            'pabrik',
            'supplier',
            'pieChartFilter',
            'pieChartStartDate',
            'pieChartEndDate',
            'pieChartData'
        ));
    }
    
    private function getYearRange()
    {
        // Get min and max year from tanggal_kirim
        $minYear = Pengiriman::whereNotNull('tanggal_kirim')
            ->min(DB::raw('YEAR(tanggal_kirim)'));
        
        $maxYear = Pengiriman::whereNotNull('tanggal_kirim')
            ->max(DB::raw('YEAR(tanggal_kirim)'));
        
        // Fallback if no data
        if (!$minYear || !$maxYear) {
            $minYear = now()->year - 2;
            $maxYear = now()->year;
        }
        
        // Ensure maxYear is at least current year (to allow navigating to current year even if no data yet)
        $maxYear = max((int) $maxYear, now()->year);
        
        return [
            'min_year' => (int) $minYear,
            'max_year' => (int) $maxYear
        ];
    }
    
    private function getPieChartData($filter, $startDate, $endDate)
    {
        $dateField = 'pengiriman.tanggal_kirim';
        $testQuery = Pengiriman::whereNotNull('tanggal_kirim')->first();
        if (!$testQuery) {
            $dateField = 'pengiriman.created_at';
        }
        
        // Build base query for status berhasil, menunggu_fisik, menunggu_verifikasi
        $query = Pengiriman::with('forecast')
            ->whereIn('pengiriman.status', ['berhasil', 'menunggu_fisik', 'menunggu_verifikasi']);
        
        // Apply date filter if not 'semua'
        if ($filter !== 'semua' && $startDate && $endDate) {
            $query->whereBetween($dateField, [$startDate, $endDate]);
        }
        
        $pengirimanData = $query->get();
        
        $normal = 0;      // >70%
        $bongkar = 0;     // ≤70%
        $gagal = 0;
        
        // Count normal and bongkar sebagian
        foreach ($pengirimanData as $pengiriman) {
            if ($pengiriman->forecast) {
                $totalQtyForecast = (float) $pengiriman->forecast->total_qty_forecast;
                $totalQtyKirim = (float) $pengiriman->total_qty_kirim;
                
                if ($totalQtyForecast > 0) {
                    $percentage = ($totalQtyKirim / $totalQtyForecast) * 100;
                    
                    if ($percentage > 70) {
                        $normal++;
                    } else {
                        $bongkar++;
                    }
                } else {
                    // If no forecast data, count as normal
                    $normal++;
                }
            } else {
                // If no forecast, count as normal
                $normal++;
            }
        }
        
        // Count gagal (cancelled/failed pengiriman)
        $gagalQuery = Pengiriman::where('pengiriman.status', 'gagal');
        
        if ($filter !== 'semua' && $startDate && $endDate) {
            $gagalQuery->whereBetween($dateField, [$startDate, $endDate]);
        }
        
        $gagal = $gagalQuery->count();
        
        $total = $normal + $bongkar + $gagal;
        
        return [
            'normal' => $normal,
            'bongkar' => $bongkar,
            'gagal' => $gagal,
            'total' => $total,
            'normal_percentage' => $total > 0 ? round(($normal / $total) * 100, 1) : 0,
            'bongkar_percentage' => $total > 0 ? round(($bongkar / $total) * 100, 1) : 0,
            'gagal_percentage' => $total > 0 ? round(($gagal / $total) * 100, 1) : 0,
        ];
    }
    
    private function getWeeklyStats($weekStart, $weekEnd)
    {
        $dateField = 'pengiriman.tanggal_kirim';
        $testQuery = Pengiriman::whereNotNull('tanggal_kirim')->first();
        if (!$testQuery) {
            $dateField = 'pengiriman.created_at';
        }
        
        // Untuk pengiriman: menunggu_fisik, menunggu_verifikasi, berhasil, gagal
        $countData = Pengiriman::whereBetween($dateField, [$weekStart, $weekEnd])
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil', 'gagal'])
            ->selectRaw('COUNT(DISTINCT pengiriman.id) as total_pengiriman')
            ->first();
        
        // Untuk tonase: menunggu_fisik, menunggu_verifikasi, berhasil (tanpa gagal & pending)
        $tonaseData = Pengiriman::whereBetween($dateField, [$weekStart, $weekEnd])
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.qty_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.qty_after_refraksi 
                        ELSE pengiriman.total_qty_kirim 
                    END
                ), 0) as total_tonase
            ')
            ->first();
            
        return [
            'total_pengiriman' => $countData->total_pengiriman ?? 0,
            'total_tonase' => $tonaseData->total_tonase ?? 0,
            'week_start' => $weekStart->format('d M'),
            'week_end' => $weekEnd->format('d M Y')
        ];
    }
    
    private function getYearlyStats($year)
    {
        $dateField = 'pengiriman.tanggal_kirim';
        $testQuery = Pengiriman::whereNotNull('tanggal_kirim')->first();
        if (!$testQuery) {
            $dateField = 'pengiriman.created_at';
        }
        
        // Untuk pengiriman: menunggu_fisik, menunggu_verifikasi, berhasil, gagal
        $countData = Pengiriman::whereYear($dateField, $year)
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil', 'gagal'])
            ->selectRaw('COUNT(DISTINCT pengiriman.id) as total_pengiriman')
            ->first();
        
        // Untuk tonase: menunggu_fisik, menunggu_verifikasi, berhasil (tanpa gagal & pending)
        $tonaseData = Pengiriman::whereYear($dateField, $year)
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.qty_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.qty_after_refraksi 
                        ELSE pengiriman.total_qty_kirim 
                    END
                ), 0) as total_tonase
            ')
            ->first();
            
        return [
            'total_pengiriman' => $countData->total_pengiriman ?? 0,
            'total_tonase' => $tonaseData->total_tonase ?? 0,
            'year' => $year
        ];
    }
    
    private function getTotalStats()
    {
        // Untuk pengiriman: menunggu_fisik, menunggu_verifikasi, berhasil, gagal
        $countData = Pengiriman::whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil', 'gagal'])
            ->selectRaw('COUNT(DISTINCT pengiriman.id) as total_pengiriman')
            ->first();
        
        // Untuk tonase: menunggu_fisik, menunggu_verifikasi, berhasil (tanpa gagal & pending)
        $tonaseData = Pengiriman::whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.qty_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.qty_after_refraksi 
                        ELSE pengiriman.total_qty_kirim 
                    END
                ), 0) as total_tonase
            ')
            ->first();
            
        return [
            'total_pengiriman' => $countData->total_pengiriman ?? 0,
            'total_tonase' => $tonaseData->total_tonase ?? 0
        ];
    }
    
    
    private function getYearlyChartData($year)
    {
        $totalPengiriman = Pengiriman::count();
        
        $dateField = 'pengiriman.tanggal_kirim';
        
        $testQuery = Pengiriman::whereNotNull('tanggal_kirim')->first();
        if (!$testQuery) {
            $dateField = 'pengiriman.created_at';
        }
        
        // Get monthly data for the specified year - status menunggu_fisik, menunggu_verifikasi, berhasil, gagal
        // Untuk status berhasil, ambil qty dan harga dari invoice_penagihan
        $monthlyData = Pengiriman::whereYear($dateField, $year)
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil', 'gagal'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw("
                MONTH({$dateField}) as month,
                pengiriman.purchasing_id,
                COUNT(DISTINCT pengiriman.id) as total_pengiriman,
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = 'berhasil' AND invoice_penagihan.qty_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.qty_after_refraksi 
                        ELSE pengiriman.total_qty_kirim 
                    END
                ), 0) as total_tonase
            ")
            ->groupBy(['month', 'pengiriman.purchasing_id'])
            ->with('purchasing')
            ->get();
            
        // Get all purchasing users (both manager and staff) + direktur
        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing', 'direktur'])->get();
        
      
        
        // Initialize chart data structure
        $chartData = [];
        foreach ($purchasingUsers as $user) {
            $userName = $user->nama ?? $user->name ?? 'Unknown User';
            $chartData[$userName] = [
                'pengiriman' => array_fill(0, 12, 0),
                'tonase' => array_fill(0, 12, 0)
            ];
        }
        
        // Process real data
        foreach ($monthlyData as $data) {
            if ($data->purchasing) {
                $month = $data->month - 1; // Convert to 0-based index
                $userName = $data->purchasing->nama ?? $data->purchasing->name ?? 'Unknown User';
                
                if (isset($chartData[$userName])) {
                    $chartData[$userName]['pengiriman'][$month] = (int) $data->total_pengiriman;
                    $chartData[$userName]['tonase'][$month] = (float) $data->total_tonase;
                }
            }
        }
        
        return [
            'data' => $chartData,
            'year' => $year,
            'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        ];
    }
    
    public function export(Request $request)
{
    try {
        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status');
        $purchasing = $request->get('purchasing');
        $search = $request->get('search');
        $pabrik = $request->get('pabrik');
        $supplier = $request->get('supplier');

        // Get purchasing users for filter information
        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing'])->get();
        
        // Get pabrik and supplier names for filter information
        $pabrikName = null;
        if ($pabrik) {
            $pabrikModel = \App\Models\Klien::find($pabrik);
            $pabrikName = $pabrikModel ? $pabrikModel->nama : null;
        }
        
        $supplierName = null;
        if ($supplier) {
            $supplierModel = \App\Models\Supplier::find($supplier);
            $supplierName = $supplierModel ? $supplierModel->nama : null;
        }

        // Debug: Check if data exists
        $pengirimanCount = Pengiriman::whereBetween('tanggal_kirim', [$startDate, $endDate])->count();
        
        if ($pengirimanCount === 0) {
            // Redirect back dengan pesan error (bukan JSON response)
            return redirect()->back()->with('error', 'Tidak ada data pengiriman pada periode ' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)));
        }

        // Generate filename with current datetime
        $filename = 'Laporan_Pengiriman_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        // Export menggunakan Laravel Excel
        return Excel::download(
            new PengirimanExport($startDate, $endDate, $status, $purchasing, $search, $purchasingUsers, $pabrik, $pabrikName, $supplier, $supplierName),
            $filename
        );
        
    } catch (\Exception $e) {
        Log::error('Export Error: ' . $e->getMessage());
        
        // Redirect back dengan pesan error (bukan JSON response)
        return redirect()->back()->with('error', 'Error saat export: ' . $e->getMessage());
    }
}
    
    /**
     * Get pie chart details based on time filter
     */
    public function getPieChartDetails(Request $request)
    {
        try {
            $filter = $request->get('filter', 'bulan_ini');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            $dateField = 'pengiriman.tanggal_kirim';
            
            // Determine date range based on filter
            if ($filter !== 'semua') {
                switch ($filter) {
                    case 'bulan_ini':
                        $startDate = now()->startOfMonth()->format('Y-m-d');
                        $endDate = now()->endOfMonth()->format('Y-m-d');
                        break;
                    case 'tahun_ini':
                        $startDate = now()->startOfYear()->format('Y-m-d');
                        $endDate = now()->endOfYear()->format('Y-m-d');
                        break;
                    // 'range' uses the provided start/end dates
                }
            }
            
            $details = [];
            
            // Get all pengiriman with joins - supplier diambil dari pengiriman_details
            $query = Pengiriman::leftJoin('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->leftJoin('forecasts', 'pengiriman.forecast_id', '=', 'forecasts.id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->leftJoin('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->select(
                    'pengiriman.id',
                    'pengiriman.status',
                    'pengiriman.total_qty_kirim',
                    'pengiriman.tanggal_kirim',
                    'orders.po_number',
                    DB::raw('GROUP_CONCAT(DISTINCT suppliers.nama SEPARATOR ", ") as supplier_nama'),
                    'forecasts.total_qty_forecast'
                )
                ->groupBy(
                    'pengiriman.id',
                    'pengiriman.status',
                    'pengiriman.total_qty_kirim',
                    'pengiriman.tanggal_kirim',
                    'orders.po_number',
                    'forecasts.total_qty_forecast'
                );
            
            if ($filter !== 'semua' && $startDate && $endDate) {
                $query->whereBetween($dateField, [$startDate, $endDate]);
            }
            
            $pengirimanData = $query->get();
            
            foreach ($pengirimanData as $pengiriman) {
                $kategori = '';
                $statusLabel = '';
                
                if ($pengiriman->status === 'gagal') {
                    $kategori = 'gagal';
                    $statusLabel = 'Ditolak';
                } else if (in_array($pengiriman->status, ['berhasil', 'menunggu_fisik', 'menunggu_verifikasi'])) {
                    if ($pengiriman->total_qty_forecast) {
                        $totalQtyForecast = (float) $pengiriman->total_qty_forecast;
                        $totalQtyKirim = (float) $pengiriman->total_qty_kirim;
                        
                        if ($totalQtyForecast > 0) {
                            $percentage = ($totalQtyKirim / $totalQtyForecast) * 100;
                            
                            if ($percentage > 70) {
                                $kategori = 'normal';
                                $statusLabel = 'Normal (' . round($percentage, 1) . '%)';
                            } else {
                                $kategori = 'bongkar';
                                $statusLabel = 'Bongkar Sebagian (' . round($percentage, 1) . '%)';
                            }
                        } else {
                            $kategori = 'normal';
                            $statusLabel = 'Normal (No Forecast)';
                        }
                    } else {
                        $kategori = 'normal';
                        $statusLabel = 'Normal (No Forecast)';
                    }
                } else {
                    // Skip pending status
                    continue;
                }
                
                $details[] = [
                    'id' => $pengiriman->id,
                    'po_number' => $pengiriman->po_number ?? '-',
                    'supplier' => $pengiriman->supplier_nama ?? '-',
                    'tanggal_kirim' => $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('Y-m-d') : null,
                    'qty_forecast' => $pengiriman->total_qty_forecast ? (float) $pengiriman->total_qty_forecast : 0,
                    'qty_pengiriman' => (float) $pengiriman->total_qty_kirim,
                    'status_label' => $statusLabel,
                    'kategori' => $kategori,
                    'status_pengiriman' => $pengiriman->status
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $details
            ]);
            
        } catch (\Exception $e) {
            Log::error('Pie Chart Details Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    
    /**
     * Export pie chart details to PDF
     */
    public function exportPieChartPDF(Request $request)
    {
        try {
            $filter = $request->get('filter', 'bulan_ini');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            $dateField = 'pengiriman.tanggal_kirim';
            
            // Determine date range based on filter
            if ($filter !== 'semua') {
                switch ($filter) {
                    case 'bulan_ini':
                        $startDate = now()->startOfMonth()->format('Y-m-d');
                        $endDate = now()->endOfMonth()->format('Y-m-d');
                        break;
                    case 'tahun_ini':
                        $startDate = now()->startOfYear()->format('Y-m-d');
                        $endDate = now()->endOfYear()->format('Y-m-d');
                        break;
                    // 'range' uses the provided start/end dates
                }
            }
            
            // Get all pengiriman with joins
            $query = Pengiriman::leftJoin('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->leftJoin('forecasts', 'pengiriman.forecast_id', '=', 'forecasts.id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->leftJoin('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->select(
                    'pengiriman.id',
                    'pengiriman.status',
                    'pengiriman.total_qty_kirim',
                    'pengiriman.tanggal_kirim',
                    'orders.po_number',
                    DB::raw('GROUP_CONCAT(DISTINCT suppliers.nama SEPARATOR ", ") as supplier_nama'),
                    'forecasts.total_qty_forecast'
                )
                ->groupBy(
                    'pengiriman.id',
                    'pengiriman.status',
                    'pengiriman.total_qty_kirim',
                    'pengiriman.tanggal_kirim',
                    'orders.po_number',
                    'forecasts.total_qty_forecast'
                );
            
            if ($filter !== 'semua' && $startDate && $endDate) {
                $query->whereBetween($dateField, [$startDate, $endDate]);
            }
            
            $pengirimanData = $query->get();
            
            $details = [];
            $normalCount = 0;
            $bongkarCount = 0;
            $gagalCount = 0;
            
            foreach ($pengirimanData as $pengiriman) {
                $kategori = '';
                $statusLabel = '';
                
                if ($pengiriman->status === 'gagal') {
                    $kategori = 'gagal';
                    $statusLabel = 'Ditolak';
                    $gagalCount++;
                } else if (in_array($pengiriman->status, ['berhasil', 'menunggu_fisik', 'menunggu_verifikasi'])) {
                    if ($pengiriman->total_qty_forecast) {
                        $totalQtyForecast = (float) $pengiriman->total_qty_forecast;
                        $totalQtyKirim = (float) $pengiriman->total_qty_kirim;
                        
                        if ($totalQtyForecast > 0) {
                            $percentage = ($totalQtyKirim / $totalQtyForecast) * 100;
                            
                            if ($percentage > 70) {
                                $kategori = 'normal';
                                $statusLabel = 'Normal (' . round($percentage, 1) . '%)';
                                $normalCount++;
                            } else {
                                $kategori = 'bongkar';
                                $statusLabel = 'Bongkar Sebagian (' . round($percentage, 1) . '%)';
                                $bongkarCount++;
                            }
                        } else {
                            $kategori = 'normal';
                            $statusLabel = 'Normal (No Forecast)';
                            $normalCount++;
                        }
                    } else {
                        $kategori = 'normal';
                        $statusLabel = 'Normal (No Forecast)';
                        $normalCount++;
                    }
                } else {
                    // Skip pending status
                    continue;
                }
                
                $details[] = [
                    'id' => $pengiriman->id,
                    'po_number' => $pengiriman->po_number ?? '-',
                    'supplier' => $pengiriman->supplier_nama ?? '-',
                    'tanggal_kirim' => $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d/m/Y') : '-',
                    'qty_forecast' => $pengiriman->total_qty_forecast ? (float) $pengiriman->total_qty_forecast : 0,
                    'qty_pengiriman' => (float) $pengiriman->total_qty_kirim,
                    'status_label' => $statusLabel,
                    'kategori' => $kategori,
                    'status_pengiriman' => $pengiriman->status
                ];
            }
            
            // Determine report title based on filter
            $reportTitle = '';
            $reportPeriod = '';
            
            switch ($filter) {
                case 'semua':
                    $reportTitle = 'Detail Pengiriman - Semua Data';
                    $reportPeriod = 'Semua Periode';
                    break;
                case 'bulan_ini':
                    $reportTitle = 'Detail Pengiriman - Bulan ' . now()->translatedFormat('F Y');
                    $reportPeriod = now()->translatedFormat('F Y');
                    break;
                case 'tahun_ini':
                    $reportTitle = 'Detail Pengiriman - Tahun ' . now()->year;
                    $reportPeriod = 'Tahun ' . now()->year;
                    break;
                case 'range':
                    $reportTitle = 'Detail Pengiriman - Custom Range';
                    $reportPeriod = Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');
                    break;
            }
            
            // Summary data
            $summary = [
                'normal' => $normalCount,
                'bongkar' => $bongkarCount,
                'gagal' => $gagalCount,
                'total' => count($details)
            ];
            
            // Generate PDF
            $pdf = Pdf::loadView('pages.laporan.pengiriman-pie-chart-pdf', [
                'details' => $details,
                'summary' => $summary,
                'reportTitle' => $reportTitle,
                'reportPeriod' => $reportPeriod,
                'generatedAt' => now()->translatedFormat('d F Y H:i')
            ]);
            
            // Set paper size and orientation
            $pdf->setPaper('a4', 'landscape');
            
            // Generate filename
            $filename = 'Detail_Pengiriman_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Export Pie Chart PDF Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }
}