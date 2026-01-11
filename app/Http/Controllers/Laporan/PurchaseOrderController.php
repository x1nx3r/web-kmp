<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Klien;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
        
        // Get PO details for each status (for modal) - hanya nomor PO, klien, dan tanggal
        $poDetailsByStatus = [];
        foreach ($poByStatus as $statusData) {
            $poDetails = Order::with('klien')
                ->where('status', $statusData->status)
                ->orderBy('po_number')
                ->get()
                ->map(function($order) {
                    return [
                        'po_number' => $order->po_number ?: $order->no_order,
                        'klien_nama' => $order->klien->nama ?? '-',
                        'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d/m/Y') : '-'
                    ];
                })
                ->toArray();
            
            $poDetailsByStatus[$statusData->status] = $poDetails;
        }
        
        // ========== PO BY PRIORITY (dikonfirmasi & diproses only) - NO FILTER ==========
        $poByPriority = Order::select('priority', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_amount) as nilai'))
            ->whereIn('status', ['dikonfirmasi', 'diproses'])
            ->groupBy('priority')
            ->get();
        
        // Get PO details for each priority
        $poDetailsByPriority = [];
        foreach ($poByPriority as $priorityData) {
            $poDetails = Order::with('klien')
                ->where('priority', $priorityData->priority)
                ->whereIn('status', ['dikonfirmasi', 'diproses'])
                ->orderBy('po_number')
                ->get()
                ->map(function($order) {
                    return [
                        'po_number' => $order->po_number ?: $order->no_order,
                        'klien_nama' => $order->klien->nama ?? '-',
                        'cabang' => $order->klien->cabang ?? '-',
                        'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d/m/Y') : '-',
                        'total_amount' => $order->total_amount,
                        'total_qty' => $order->total_qty,
                        'status' => $order->status
                    ];
                })
                ->toArray();
            
            $poDetailsByPriority[$priorityData->priority] = $poDetails;
        }
        
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
            'poDetailsByStatus',
            'poByPriority',
            'poDetailsByPriority',
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
    
    public function exportOutstandingPdf()
    {
        // Get outstanding order details
        $outstandingDetails = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->whereNotIn('order_details.status', ['selesai'])
            ->select(
                'orders.po_number',
                'orders.no_order',
                'kliens.nama as klien_nama',
                'kliens.cabang as klien_cabang',
                'bahan_baku_klien.nama as material_nama',
                'order_details.qty',
                'order_details.harga_jual',
                'order_details.total_harga',
                'order_details.status as detail_status'
            )
            ->orderBy('orders.po_number')
            ->orderBy('kliens.nama')
            ->get();
        
        // Calculate totals
        $totalQty = $outstandingDetails->sum('qty');
        $totalNilai = $outstandingDetails->sum('total_harga');
        $totalPO = $outstandingDetails->pluck('po_number')->unique()->count();
        
        // Load PDF view
        $pdf = Pdf::loadView('pages.laporan.pdf.outstanding', [
            'outstandingDetails' => $outstandingDetails,
            'totalQty' => $totalQty,
            'totalNilai' => $totalNilai,
            'totalPO' => $totalPO,
            'generatedAt' => now()->format('d/m/Y H:i')
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');
        
        // Generate filename with timestamp
        $filename = 'Outstanding_PO_' . now()->format('Ymd_His') . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
    }
    
    public function exportClientPdf(Request $request)
    {
        // Get filter parameters
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
        
        // Get PO by client data
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
            ->get();
        
        // Calculate totals
        $totalKlien = $poByClient->count();
        $totalPO = $poByClient->sum('total_po');
        $totalNilai = $poByClient->sum('total_nilai');
        
        // Build filter info text
        $filterInfo = null;
        if ($periode === 'tahun_ini') {
            $filterInfo = 'Tahun ' . Carbon::now()->year;
        } elseif ($periode === 'bulan_ini') {
            $filterInfo = Carbon::now()->isoFormat('MMMM YYYY');
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $filterInfo = Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');
        } else {
            $filterInfo = 'Semua Data';
        }
        
        // Load PDF view
        $pdf = Pdf::loadView('pages.laporan.pdf.client', [
            'poByClient' => $poByClient,
            'totalKlien' => $totalKlien,
            'totalPO' => $totalPO,
            'totalNilai' => $totalNilai,
            'filterInfo' => $filterInfo,
            'generatedAt' => now()->format('d/m/Y H:i')
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Generate filename with timestamp
        $filename = 'PO_By_Client_' . now()->format('Ymd_His') . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
    }
    
    /**
     * Get Order Winner Details for AJAX
     * Grouped by Marketing > Klien > Cabang > PO
     */
    public function orderWinnerDetails(Request $request)
    {
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Build date filter query
        $query = DB::table('order_winners')
            ->join('orders', 'order_winners.order_id', '=', 'orders.id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->select(
                'users.id as user_id',
                'users.nama as marketing_nama',
                'kliens.id as klien_id',
                'kliens.nama as klien_nama',
                'kliens.cabang as klien_cabang',
                'orders.id as order_id',
                'orders.po_number',
                'orders.tanggal_order',
                'orders.status as order_status',
                'orders.total_amount as total_nilai',
                'orders.total_qty'
            )
            ->orderBy('users.nama')
            ->orderBy('kliens.nama')
            ->orderBy('kliens.cabang')
            ->orderBy('orders.tanggal_order', 'desc');
        
        // Apply date filter
        if ($periode === 'tahun_ini') {
            $query->whereYear('orders.tanggal_order', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('orders.tanggal_order', Carbon::now()->year)
                  ->whereMonth('orders.tanggal_order', Carbon::now()->month);
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $query->whereBetween('orders.tanggal_order', [$startDate, $endDate]);
        }
        
        $details = $query->get();
        
        // Group by Marketing > Klien > Cabang > PO
        $groupedData = [];
        
        foreach ($details as $item) {
            $marketingKey = $item->marketing_nama;
            $klienKey = $item->klien_nama;
            $cabangKey = $item->klien_cabang ?: 'Tanpa Cabang';
            
            if (!isset($groupedData[$marketingKey])) {
                $groupedData[$marketingKey] = [
                    'marketing_nama' => $item->marketing_nama,
                    'total_nilai' => 0,
                    'total_po' => 0,
                    'kliens' => []
                ];
            }
            
            if (!isset($groupedData[$marketingKey]['kliens'][$klienKey])) {
                $groupedData[$marketingKey]['kliens'][$klienKey] = [
                    'klien_nama' => $item->klien_nama,
                    'total_nilai' => 0,
                    'total_po' => 0,
                    'cabangs' => []
                ];
            }
            
            if (!isset($groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey])) {
                $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey] = [
                    'cabang_nama' => $cabangKey,
                    'total_nilai' => 0,
                    'total_po' => 0,
                    'orders' => []
                ];
            }
            
            // Add order to cabang
            $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey]['orders'][] = [
                'po_number' => $item->po_number,
                'tanggal_order' => Carbon::parse($item->tanggal_order)->format('d M Y'),
                'order_status' => $item->order_status,
                'total_nilai' => $item->total_nilai,
                'total_qty' => $item->total_qty
            ];
            
            // Update totals
            $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey]['total_nilai'] += $item->total_nilai;
            $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey]['total_po']++;
            
            $groupedData[$marketingKey]['kliens'][$klienKey]['total_nilai'] += $item->total_nilai;
            $groupedData[$marketingKey]['kliens'][$klienKey]['total_po']++;
            
            $groupedData[$marketingKey]['total_nilai'] += $item->total_nilai;
            $groupedData[$marketingKey]['total_po']++;
        }
        
        // Convert associative arrays to indexed arrays for JSON
        foreach ($groupedData as &$marketing) {
            $marketing['kliens'] = array_values($marketing['kliens']);
            foreach ($marketing['kliens'] as &$klien) {
                $klien['cabangs'] = array_values($klien['cabangs']);
            }
        }
        
        return response()->json(array_values($groupedData));
    }
    
    /**
     * Export Order Winner to PDF
     * Grouped by Marketing > Klien > Cabang > PO
     */
    public function exportOrderWinnerPdf(Request $request)
    {
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Get data
        $query = DB::table('order_winners')
            ->join('orders', 'order_winners.order_id', '=', 'orders.id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->select(
                'users.id as user_id',
                'users.nama as marketing_nama',
                'kliens.id as klien_id',
                'kliens.nama as klien_nama',
                'kliens.cabang as klien_cabang',
                'orders.id as order_id',
                'orders.po_number',
                'orders.tanggal_order',
                'orders.status as order_status',
                'orders.total_amount as total_nilai',
                'orders.total_qty'
            )
            ->orderBy('users.nama')
            ->orderBy('kliens.nama')
            ->orderBy('kliens.cabang')
            ->orderBy('orders.tanggal_order', 'desc');
        
        // Apply date filter
        if ($periode === 'tahun_ini') {
            $query->whereYear('orders.tanggal_order', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('orders.tanggal_order', Carbon::now()->year)
                  ->whereMonth('orders.tanggal_order', Carbon::now()->month);
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $query->whereBetween('orders.tanggal_order', [$startDate, $endDate]);
        }
        
        $details = $query->get();
        
        // Group by Marketing > Klien > Cabang > PO
        $groupedData = [];
        $totalNilai = 0;
        $totalPO = 0;
        
        foreach ($details as $item) {
            $marketingKey = $item->marketing_nama;
            $klienKey = $item->klien_nama;
            $cabangKey = $item->klien_cabang ?: 'Tanpa Cabang';
            
            if (!isset($groupedData[$marketingKey])) {
                $groupedData[$marketingKey] = [
                    'marketing_nama' => $item->marketing_nama,
                    'total_nilai' => 0,
                    'total_po' => 0,
                    'kliens' => []
                ];
            }
            
            if (!isset($groupedData[$marketingKey]['kliens'][$klienKey])) {
                $groupedData[$marketingKey]['kliens'][$klienKey] = [
                    'klien_nama' => $item->klien_nama,
                    'total_nilai' => 0,
                    'total_po' => 0,
                    'cabangs' => []
                ];
            }
            
            if (!isset($groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey])) {
                $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey] = [
                    'cabang_nama' => $cabangKey,
                    'total_nilai' => 0,
                    'total_po' => 0,
                    'orders' => []
                ];
            }
            
            // Add order to cabang
            $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey]['orders'][] = [
                'po_number' => $item->po_number,
                'tanggal_order' => Carbon::parse($item->tanggal_order)->format('d/m/Y'),
                'order_status' => $item->order_status,
                'total_nilai' => $item->total_nilai,
                'total_qty' => $item->total_qty
            ];
            
            // Update totals
            $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey]['total_nilai'] += $item->total_nilai;
            $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey]['total_po']++;
            
            $groupedData[$marketingKey]['kliens'][$klienKey]['total_nilai'] += $item->total_nilai;
            $groupedData[$marketingKey]['kliens'][$klienKey]['total_po']++;
            
            $groupedData[$marketingKey]['total_nilai'] += $item->total_nilai;
            $groupedData[$marketingKey]['total_po']++;
            
            $totalNilai += $item->total_nilai;
            $totalPO++;
        }
        
        // Filter info
        if ($periode === 'tahun_ini') {
            $filterInfo = 'Tahun ' . Carbon::now()->year;
        } elseif ($periode === 'bulan_ini') {
            $filterInfo = Carbon::now()->format('F Y');
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $filterInfo = Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');
        } else {
            $filterInfo = 'Semua Data';
        }
        
        // Load PDF view
        $pdf = Pdf::loadView('pages.laporan.pdf.order-winner', [
            'groupedData' => $groupedData,
            'totalPO' => $totalPO,
            'totalNilai' => $totalNilai,
            'filterInfo' => $filterInfo,
            'generatedAt' => now()->format('d/m/Y H:i')
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Generate filename with timestamp
        $filename = 'Order_Winners_' . now()->format('Ymd_His') . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
    }
    
    /**
     * Export PO Trend to PDF
     */
    public function exportTrendPdf()
    {
        // Get trend data (12 months)
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
        
        // Calculate totals
        $totalPO = array_sum(array_column($poTrendByMonth, 'total_po'));
        $totalNilai = array_sum(array_column($poTrendByMonth, 'total_nilai'));
        $avgPerPO = $totalPO > 0 ? $totalNilai / $totalPO : 0;
        
        // Load PDF view
        $pdf = Pdf::loadView('pages.laporan.pdf.po-trend', [
            'poTrendByMonth' => $poTrendByMonth,
            'totalPO' => $totalPO,
            'totalNilai' => $totalNilai,
            'avgPerPO' => $avgPerPO,
            'generatedAt' => now()->format('d/m/Y H:i')
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Generate filename with timestamp
        $filename = 'PO_Trend_12_Bulan_' . now()->format('Ymd_His') . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
    }
    
    /**
     * Export PO Priority to PDF
     */
    public function exportPriorityPdf()
    {
        // Get priority data
        $poByPriority = Order::select('priority', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_amount) as nilai'))
            ->whereIn('status', ['dikonfirmasi', 'diproses'])
            ->groupBy('priority')
            ->get();
        
        // Get PO details for each priority
        $poDetailsByPriority = [];
        foreach ($poByPriority as $priorityData) {
            $poDetails = Order::with('klien')
                ->where('priority', $priorityData->priority)
                ->whereIn('status', ['dikonfirmasi', 'diproses'])
                ->orderBy('po_number')
                ->get()
                ->map(function($order) {
                    return [
                        'po_number' => $order->po_number ?: $order->no_order,
                        'klien_nama' => $order->klien->nama ?? '-',
                        'cabang' => $order->klien->cabang ?? '-',
                        'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d/m/Y') : '-',
                        'total_amount' => $order->total_amount,
                        'total_qty' => $order->total_qty,
                        'status' => $order->status
                    ];
                })
                ->toArray();
            
            $poDetailsByPriority[$priorityData->priority] = $poDetails;
        }
        
        // Calculate totals
        $totalPO = $poByPriority->sum('total');
        $totalNilai = $poByPriority->sum('nilai');
        $avgPerPO = $totalPO > 0 ? $totalNilai / $totalPO : 0;
        
        // Load PDF view
        $pdf = Pdf::loadView('pages.laporan.pdf.po-priority', [
            'poByPriority' => $poByPriority,
            'poDetailsByPriority' => $poDetailsByPriority,
            'totalPO' => $totalPO,
            'totalNilai' => $totalNilai,
            'avgPerPO' => $avgPerPO,
            'generatedAt' => now()->format('d/m/Y H:i')
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');
        
        // Generate filename with timestamp
        $filename = 'PO_Berdasarkan_Prioritas_' . now()->format('Ymd_His') . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
    }
    
    /**
     * Export PO Status to PDF
     */
    public function exportStatusPdf()
    {
        // Get status data
        $poByStatus = Order::select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_amount) as nilai'))
            ->groupBy('status')
            ->get();
        
        // Get PO details for each status
        $poDetailsByStatus = [];
        foreach ($poByStatus as $statusData) {
            $poDetails = Order::with('klien')
                ->where('status', $statusData->status)
                ->orderBy('po_number')
                ->get()
                ->map(function($order) {
                    return [
                        'po_number' => $order->po_number ?: $order->no_order,
                        'klien_nama' => $order->klien->nama ?? '-',
                        'cabang' => $order->klien->cabang ?? '-',
                        'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d/m/Y') : '-',
                        'total_amount' => $order->total_amount,
                        'total_qty' => $order->total_qty,
                        'priority' => $order->priority ?? '-'
                    ];
                })
                ->toArray();
            
            $poDetailsByStatus[$statusData->status] = $poDetails;
        }
        
        // Calculate totals
        $totalPO = $poByStatus->sum('total');
        $totalNilai = $poByStatus->sum('nilai');
        
        // Load PDF view
        $pdf = Pdf::loadView('pages.laporan.pdf.po-status', [
            'poByStatus' => $poByStatus,
            'poDetailsByStatus' => $poDetailsByStatus,
            'totalPO' => $totalPO,
            'totalNilai' => $totalNilai,
            'generatedAt' => now()->format('d/m/Y H:i')
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');
        
        // Generate filename with timestamp
        $filename = 'PO_Berdasarkan_Status_' . now()->format('Ymd_His') . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
    }
}
