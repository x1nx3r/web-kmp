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
            ->select('pengiriman.*', 
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
                  ->orWhereHas('purchasing', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $pengirimanData = $pengirimanQuery->orderBy('pengiriman.tanggal_kirim', 'desc')->paginate(15);
        
        // Get purchasing users for filter dropdown (including direktur)
        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing', 'direktur'])->get();
        
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
            'startDate',
            'endDate',
            'status',
            'purchasing',
            'search'
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
        
        return [
            'min_year' => (int) $minYear,
            'max_year' => (int) $maxYear
        ];
    }
    
    private function getWeeklyStats($weekStart, $weekEnd)
    {
        $dateField = 'pengiriman.tanggal_kirim';
        $testQuery = Pengiriman::whereNotNull('tanggal_kirim')->first();
        if (!$testQuery) {
            $dateField = 'pengiriman.created_at';
        }
        
        // Tampilkan pengiriman dengan status menunggu_fisik, menunggu_verifikasi, dan berhasil
        $weeklyData = Pengiriman::whereBetween($dateField, [$weekStart, $weekEnd])
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('
                COUNT(DISTINCT pengiriman.id) as total_pengiriman,
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.qty_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.qty_after_refraksi 
                        ELSE pengiriman.total_qty_kirim 
                    END
                ), 0) as total_tonase,
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.amount_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.amount_after_refraksi 
                        ELSE pengiriman.total_harga_kirim 
                    END
                ), 0) as total_harga
            ')
            ->first();
            
        return [
            'total_pengiriman' => $weeklyData->total_pengiriman ?? 0,
            'total_tonase' => $weeklyData->total_tonase ?? 0,
            'total_harga' => $weeklyData->total_harga ?? 0,
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
        
        // Tampilkan pengiriman dengan status menunggu_fisik, menunggu_verifikasi, dan berhasil
        $yearlyData = Pengiriman::whereYear($dateField, $year)
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('
                COUNT(DISTINCT pengiriman.id) as total_pengiriman,
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.qty_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.qty_after_refraksi 
                        ELSE pengiriman.total_qty_kirim 
                    END
                ), 0) as total_tonase,
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.amount_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.amount_after_refraksi 
                        ELSE pengiriman.total_harga_kirim 
                    END
                ), 0) as total_harga
            ')
            ->first();
            
        return [
            'total_pengiriman' => $yearlyData->total_pengiriman ?? 0,
            'total_tonase' => $yearlyData->total_tonase ?? 0,
            'total_harga' => $yearlyData->total_harga ?? 0,
            'year' => $year
        ];
    }
    
    private function getTotalStats()
    {
        // Tampilkan pengiriman dengan status menunggu_fisik, menunggu_verifikasi, dan berhasil
        $totalData = Pengiriman::whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('
                COUNT(DISTINCT pengiriman.id) as total_pengiriman,
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.qty_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.qty_after_refraksi 
                        ELSE pengiriman.total_qty_kirim 
                    END
                ), 0) as total_tonase,
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.amount_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.amount_after_refraksi 
                        ELSE pengiriman.total_harga_kirim 
                    END
                ), 0) as total_harga
            ')
            ->first();
            
        return [
            'total_pengiriman' => $totalData->total_pengiriman ?? 0,
            'total_tonase' => $totalData->total_tonase ?? 0,
            'total_harga' => $totalData->total_harga ?? 0
        ];
    }
    
    private function getYearlyHargaStats($year)
    {
        $dateField = 'pengiriman.tanggal_kirim';
        $testQuery = Pengiriman::whereNotNull('tanggal_kirim')->first();
        if (!$testQuery) {
            $dateField = 'pengiriman.created_at';
        }
        
        // Tampilkan pengiriman dengan status menunggu_fisik, menunggu_verifikasi, dan berhasil
        $yearlyData = Pengiriman::whereYear($dateField, $year)
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('
                COALESCE(SUM(
                    CASE 
                        WHEN pengiriman.status = "berhasil" AND invoice_penagihan.amount_after_refraksi IS NOT NULL 
                        THEN invoice_penagihan.amount_after_refraksi 
                        ELSE pengiriman.total_harga_kirim 
                    END
                ), 0) as total_harga_tahun
            ')
            ->first();
            
        return [
            'total_harga_tahun' => $yearlyData->total_harga_tahun ?? 0,
            'year' => $year
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
        
        // Get monthly data for the specified year - status menunggu_fisik, menunggu_verifikasi, dan berhasil
        // Untuk status berhasil, ambil qty dan harga dari invoice_penagihan
        $monthlyData = Pengiriman::whereYear($dateField, $year)
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
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

        // Get purchasing users for filter information
        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing'])->get();

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
            new PengirimanExport($startDate, $endDate, $status, $purchasing, $search, $purchasingUsers),
            $filename
        );
        
    } catch (\Exception $e) {
        Log::error('Export Error: ' . $e->getMessage());
        
        // Redirect back dengan pesan error (bukan JSON response)
        return redirect()->back()->with('error', 'Error saat export: ' . $e->getMessage());
    }
}
}