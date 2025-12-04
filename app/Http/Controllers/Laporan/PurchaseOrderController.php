<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Klien;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Purchase Order';
        $activeTab = 'po';
        
        // Periode filter
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Build date filter query
        $dateFilterQuery = function($query) use ($periode, $startDate, $endDate) {
            if ($periode === 'tahun_ini') {
                $query->whereYear('tanggal_order', Carbon::now()->year);
            } elseif ($periode === 'bulan_ini') {
                $query->whereYear('tanggal_order', Carbon::now()->year)
                      ->whereMonth('tanggal_order', Carbon::now()->month);
            } elseif ($periode === 'custom' && $startDate && $endDate) {
                $query->whereBetween('tanggal_order', [$startDate, $endDate]);
            }
            // 'all' = no date filter
        };
        
        // ========== SUMMARY STATISTICS (Untuk status dikonfirmasi dan diproses) - NO FILTER ==========
        
        // Total Outstanding (nilai dari order details dengan status dikonfirmasi & diproses)
        $totalOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.total_harga');
        
        // Total Qty Outstanding
        $totalQtyOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.qty');
        
        // PO Berjalan (jumlah PO dengan status dikonfirmasi & diproses)
        $poBerjalan = Order::whereIn('status', ['dikonfirmasi', 'diproses'])
            ->count();
        
        // Rata-rata Nilai per PO (untuk PO yang berjalan)
        $totalNilaiPOBerjalan = Order::whereIn('status', ['dikonfirmasi', 'diproses'])
            ->sum('total_amount');
        $avgNilaiPerPO = $poBerjalan > 0 ? $totalNilaiPOBerjalan / $poBerjalan : 0;
        
        // For percentage calculations (dikonfirmasi, diproses, selesai) - WITH FILTER for Client & Winner charts
        $totalNilaiPOForPercentage = Order::whereIn('status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where($dateFilterQuery)
            ->sum('total_amount');
        
        // ========== PO BY STATUS (All statuses) - NO FILTER ==========
        $poByStatus = Order::select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_amount) as nilai'))
            ->groupBy('status')
            ->get();
        
        // ========== PO BY PRIORITY (dikonfirmasi & diproses only) - NO FILTER ==========
        $poByPriority = Order::select('priority', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_amount) as nilai'))
            ->whereIn('status', ['dikonfirmasi', 'diproses'])
            ->groupBy('priority')
            ->get();
        
        // ========== PO BY CLIENT (dikonfirmasi, diproses, selesai) - WITH FILTER ==========
        $poByClient = Order::select(
                'kliens.id as klien_id',
                'kliens.nama as klien_nama',
                'kliens.cabang',
                DB::raw('COUNT(orders.id) as total_po'),
                DB::raw('SUM(orders.total_amount) as total_nilai'),
                DB::raw('SUM(orders.total_qty) as total_qty')
            )
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where($dateFilterQuery)
            ->groupBy('kliens.id', 'kliens.nama', 'kliens.cabang')
            ->orderBy('total_nilai', 'desc')
            ->get()
            ->map(function($item) use ($totalNilaiPOForPercentage) {
                $item->percentage = $totalNilaiPOForPercentage > 0 ? ($item->total_nilai / $totalNilaiPOForPercentage) * 100 : 0;
                return $item;
            });
        
        // ========== PO TREND BY MONTH (dikonfirmasi & diproses only) - NO FILTER ==========
        $poTrendByMonth = [];
        $monthLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabels[] = $date->format('M Y');
            
            $data = Order::whereYear('tanggal_order', $date->year)
                ->whereMonth('tanggal_order', $date->month)
                ->whereIn('status', ['dikonfirmasi', 'diproses'])
                ->select(
                    DB::raw('COUNT(*) as total_po'),
                    DB::raw('SUM(total_amount) as total_nilai')
                )
                ->first();
            
            $poTrendByMonth[] = [
                'month' => $date->format('M Y'),
                'total_po' => $data->total_po ?? 0,
                'total_nilai' => floatval($data->total_nilai ?? 0)
            ];
        }
        
        // ========== RECENT POs (dikonfirmasi & diproses only) - NO FILTER ==========
        $recentPOs = Order::with(['klien', 'creator'])
            ->whereIn('status', ['dikonfirmasi', 'diproses'])
            ->orderBy('tanggal_order', 'desc')
            ->limit(10)
            ->get();
        
        // ========== ORDER WINNERS (dikonfirmasi, diproses, selesai) - WITH FILTER ==========
        $orderWinners = DB::table('order_winners')
            ->join('orders', 'order_winners.order_id', '=', 'orders.id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where(function($query) use ($dateFilterQuery) {
                $dateFilterQuery($query);
            })
            ->select(
                'users.id as user_id',
                'users.nama as marketing_nama',
                DB::raw('COUNT(DISTINCT orders.id) as total_po'),
                DB::raw('SUM(orders.total_amount) as total_nilai'),
                DB::raw('AVG(orders.total_amount) as avg_nilai')
            )
            ->groupBy('users.id', 'users.nama')
            ->orderBy('total_nilai', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) use ($totalNilaiPOForPercentage) {
                $item->percentage = $totalNilaiPOForPercentage > 0 ? ($item->total_nilai / $totalNilaiPOForPercentage) * 100 : 0;
                return $item;
            });
        
        // ========== OUTSTANDING (dikonfirmasi & diproses only) - NO FILTER ==========
        $outstandingChartData = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->whereNotIn('order_details.status', ['selesai'])
            ->select(
                'orders.po_number',
                'orders.no_order',
                'kliens.nama as klien_nama',
                'orders.status as order_status',
                DB::raw('COUNT(order_details.id) as total_items'),
                DB::raw('SUM(order_details.total_harga) as total_nilai'),
                DB::raw('SUM(order_details.qty) as total_qty'),
                DB::raw('GROUP_CONCAT(DISTINCT bahan_baku_klien.nama SEPARATOR ", ") as nama_material')
            )
            ->groupBy('orders.id', 'orders.po_number', 'orders.no_order', 'kliens.nama', 'orders.status')
            ->orderBy('total_nilai', 'desc')
            ->get()
            ->map(function($item) {
                $item->display_name = $item->po_number ?: $item->no_order;
                return $item;
            });
        
        $totalOutstandingChart = $outstandingChartData->sum('total_nilai');
        
        return view('pages.laporan.purchase-order', compact(
            'title', 
            'activeTab',
            'periode',
            'startDate',
            'endDate',
            'totalOutstanding',
            'totalQtyOutstanding',
            'poBerjalan',
            'avgNilaiPerPO',
            'poByStatus',
            'poByPriority',
            'poByClient',
            'poTrendByMonth',
            'monthLabels',
            'recentPOs',
            'orderWinners',
            'outstandingChartData',
            'totalOutstandingChart'
        ));
    }
    
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
}
