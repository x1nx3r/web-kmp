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
        
        // Calculate weekly statistics (Tuesday to Monday)
        $today = now();
        if ($today->dayOfWeek === Carbon::TUESDAY) {
            $weekStart = $today->copy()->startOfDay();
        } else {
            $weekStart = $today->copy()->previous(Carbon::TUESDAY)->startOfDay();
        }
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
        
        $weeklyStats = $this->getWeeklyStats($weekStart, $weekEnd);
        $totalStats = $this->getTotalStats();
        
        // Get year range from tanggal_kirim
        $yearRange = $this->getYearRange();
        
        // Get selected year or default to current year
        $selectedYear = $request->get('year', now()->year);
        
        // Get yearly chart data for purchasing PIC
        $chartData = $this->getYearlyChartData($selectedYear);
        
        // Get paginated pengiriman data with filters
        $pengirimanQuery = Pengiriman::with(['purchasing', 'purchaseOrder', 'pengirimanDetails.bahanBakuSupplier'])
            ->whereBetween('tanggal_kirim', [$startDate, $endDate]);
        
        // Apply filters
        if ($status) {
            $pengirimanQuery->where('status', $status);
        }
        
        if ($purchasing) {
            $pengirimanQuery->where('purchasing_id', $purchasing);
        }
        
        if ($search) {
            $pengirimanQuery->where(function($q) use ($search) {
                $q->where('no_pengiriman', 'like', "%{$search}%")
                  ->orWhereHas('purchasing', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $pengirimanData = $pengirimanQuery->orderBy('tanggal_kirim', 'desc')->paginate(15);
        
        // Get purchasing users for filter dropdown
        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing'])->get();
        
        return view('pages.laporan.pengiriman', compact(
            'title', 
            'activeTab',
            'weeklyStats',
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
        $dateField = 'tanggal_kirim';
        $testQuery = Pengiriman::whereNotNull('tanggal_kirim')->first();
        if (!$testQuery) {
            $dateField = 'created_at';
        }
        
        $weeklyData = Pengiriman::whereBetween($dateField, [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->selectRaw('
                COUNT(*) as total_pengiriman,
                COALESCE(SUM(total_qty_kirim), 0) as total_tonase
            ')
            ->first();
            
        return [
            'total_pengiriman' => $weeklyData->total_pengiriman ?? 0,
            'total_tonase' => $weeklyData->total_tonase ?? 0,
            'week_start' => $weekStart->format('d M Y') . ' (Selasa)',
            'week_end' => $weekEnd->format('d M Y') . ' (Senin)'
        ];
    }
    
    private function getTotalStats()
    {
        $totalData = Pengiriman::selectRaw('
                COUNT(*) as total_pengiriman,
                SUM(total_qty_kirim) as total_tonase
            ')
            ->first();
            
        return [
            'total_pengiriman' => $totalData->total_pengiriman ?? 0,
            'total_tonase' => $totalData->total_tonase ?? 0
        ];
    }
    
    private function getYearlyChartData($year)
    {
        $totalPengiriman = Pengiriman::count();
        
        $dateField = 'tanggal_kirim';
        
        $testQuery = Pengiriman::whereNotNull('tanggal_kirim')->first();
        if (!$testQuery) {
            $dateField = 'created_at';
        }
        
        // Get monthly data for the specified year
        $monthlyData = Pengiriman::whereYear($dateField, $year)
            ->selectRaw("
                MONTH({$dateField}) as month,
                purchasing_id,
                COUNT(*) as total_pengiriman,
                COALESCE(SUM(total_qty_kirim), 0) as total_tonase
            ")
            ->groupBy(['month', 'purchasing_id'])
            ->with('purchasing')
            ->get();
            
        // Get all purchasing users (both manager and staff)
        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing'])->get();
        
        // If no purchasing users, create sample data for demonstration
        if ($purchasingUsers->isEmpty()) {
            $purchasingUsers = collect([
                (object)['id' => 1, 'nama' => 'Sample Manager Purchasing'],
                (object)['id' => 2, 'nama' => 'Sample Staff Purchasing']
            ]);
        }
        
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