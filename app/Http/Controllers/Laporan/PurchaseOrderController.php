<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Exports\ClientPOExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

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
        
        // ========== SUMMARY STATISTICS - NO FILTER ==========
        
        // Total Outstanding (dikonfirmasi & diproses — termasuk yg closed internal)
        $totalOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.total_harga');
        
        // Total Qty Outstanding
        $totalQtyOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.qty');
        
        // PO Berjalan (dikonfirmasi & diproses — termasuk yg closed internal)
        $poBerjalan = Order::whereIn('status', ['dikonfirmasi', 'diproses'])
            ->count();
        
        // Rata-rata Nilai per PO (untuk PO yang berjalan)
        $totalNilaiPOBerjalan = Order::whereIn('status', ['dikonfirmasi', 'diproses'])
            ->sum(DB::raw('COALESCE(original_total_amount, total_amount)'));
        $avgNilaiPerPO = $poBerjalan > 0 ? $totalNilaiPOBerjalan / $poBerjalan : 0;
        
        // For percentage calculations (dikonfirmasi, diproses, selesai) - WITH FILTER for Client & Winner charts
        $totalNilaiPOForPercentage = Order::whereIn('status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where($dateFilterQuery)
            ->sum(DB::raw('COALESCE(original_total_amount, total_amount)'));
        
        // ========== PO BY STATUS (All statuses) - NO FILTER ==========
        $poByStatus = Order::select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(COALESCE(original_total_amount, total_amount)) as nilai'))
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
        $poByPriority = Order::select('priority', DB::raw('COUNT(*) as total'), DB::raw('SUM(COALESCE(original_total_amount, total_amount)) as nilai'))
            ->whereIn('status', ['dikonfirmasi', 'diproses'])
            ->groupBy('priority')
            ->get();
        
        // Get PO details for each priority — per item (matching outstanding detail logic)
        $poDetailsByPriority = [];
        foreach ($poByPriority as $priorityData) {
            $itemRows = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
                ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
                ->where('orders.priority', $priorityData->priority)
                ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
                ->whereNotIn('order_details.status', ['selesai'])
                ->whereNull('order_details.deleted_at')
                ->select(
                    'orders.id as order_id',
                    DB::raw("COALESCE(orders.po_number, orders.no_order) as po_number"),
                    'kliens.nama as klien_nama',
                    'kliens.cabang',
                    'orders.tanggal_order',
                    'orders.status',
                    DB::raw("COALESCE(bahan_baku_klien.nama, order_details.nama_material_po, '-') as bahan_baku"),
                    'order_details.qty as total_qty',
                    'order_details.harga_jual',
                    DB::raw("COALESCE(NULLIF(order_details.total_harga, 0), order_details.qty * order_details.harga_jual) as total_amount")
                )
                ->orderBy('orders.po_number')
                ->orderBy('kliens.nama')
                ->get()
                ->map(function($row) {
                    return [
                        'po_number'    => $row->po_number,
                        'klien_nama'   => $row->klien_nama,
                        'cabang'       => $row->cabang ?? '-',
                        'tanggal_order'=> $row->tanggal_order ? Carbon::parse($row->tanggal_order)->format('d/m/Y') : '-',
                        'bahan_baku'   => $row->bahan_baku,
                        'total_qty'    => (float) $row->total_qty,
                        'harga_jual'   => (float) $row->harga_jual,
                        'total_amount' => (float) $row->total_amount,
                        'status'       => $row->status,
                    ];
                })
                ->toArray();

            $poDetailsByPriority[$priorityData->priority] = $itemRows;
        }

        // Recalculate poByPriority->nilai from order_details (to match detail rows total)
        foreach ($poByPriority as $priorityData) {
            $nilaiFromDetails = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->where('orders.priority', $priorityData->priority)
                ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
                ->whereNotIn('order_details.status', ['selesai'])
                ->whereNull('order_details.deleted_at')
                ->sum(DB::raw("COALESCE(NULLIF(order_details.total_harga, 0), order_details.qty * order_details.harga_jual)"));
            $priorityData->nilai = (float) $nilaiFromDetails;
        }
        
        // ========== PO BY CLIENT (dikonfirmasi, diproses, selesai) - WITH FILTER ==========
        $poByClient = Order::select(
                'kliens.id as klien_id',
                'kliens.nama as klien_nama',
                'kliens.cabang',
                DB::raw('COUNT(orders.id) as total_po'),
                DB::raw('SUM(COALESCE(orders.original_total_amount, orders.total_amount)) as total_nilai'),
                DB::raw('SUM((SELECT SUM(COALESCE(od.original_qty, od.qty)) FROM order_details od WHERE od.order_id = orders.id AND od.deleted_at IS NULL)) as total_qty'),
                DB::raw('MAX(orders.tanggal_order) as last_order_date'),
                DB::raw('MIN(orders.tanggal_order) as first_order_date'),
                DB::raw("SUM(CASE WHEN orders.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as status_dikonfirmasi"),
                DB::raw("SUM(CASE WHEN orders.status = 'diproses' THEN 1 ELSE 0 END) as status_diproses"),
                DB::raw("SUM(CASE WHEN orders.status = 'selesai' THEN 1 ELSE 0 END) as status_selesai"),
                DB::raw("SUM(CASE WHEN orders.status IN ('dikonfirmasi', 'diproses') THEN COALESCE(orders.original_total_amount, orders.total_amount) ELSE 0 END) as outstanding_amount"),
                DB::raw("SUM(CASE WHEN orders.status IN ('dikonfirmasi', 'diproses') THEN orders.total_qty ELSE 0 END) as outstanding_qty")
            )
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where($dateFilterQuery)
            ->groupBy('kliens.id', 'kliens.nama', 'kliens.cabang')
            ->orderBy('total_nilai', 'desc')
            ->get()
            ->map(function($item) use ($totalNilaiPOForPercentage) {
                $item->percentage = $totalNilaiPOForPercentage > 0 ? ($item->total_nilai / $totalNilaiPOForPercentage) * 100 : 0;
                $item->avg_nilai_per_po = $item->total_po > 0 ? $item->total_nilai / $item->total_po : 0;
                return $item;
            });
        
        // ========== PO DETAILS BY CLIENT (for expandable list) ==========
        $poDetailsByClient = [];
        foreach ($poByClient as $client) {
            $poDetails = Order::with(['orderDetails.bahanBakuKlien'])
                ->where('klien_id', $client->klien_id)
                ->whereIn('status', ['dikonfirmasi', 'diproses', 'selesai'])
                ->where($dateFilterQuery)
                ->orderBy('tanggal_order', 'desc')
                ->get()
                ->map(function($order) {
                    // Get materials list
                    $materials = $order->orderDetails
                        ->pluck('bahanBakuKlien.nama')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();
                    
                    return [
                        'id' => $order->id,
                        'po_number' => $order->po_number ?: $order->no_order,
                        'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d/m/Y') : '-',
                        'status' => $order->status,
                        'priority' => $order->priority,
                        'total_amount' => $order->contract_amount,
                        'total_qty' => $order->original_qty_sum,
                        'materials' => implode(', ', $materials) ?: '-',
                        'materials_count' => count($materials),
                    ];
                })
                ->toArray();
            
            // Get unique materials for this client
            $clientMaterials = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
                ->where('orders.klien_id', $client->klien_id)
                ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
                ->where($dateFilterQuery)
                ->select('bahan_baku_klien.nama', DB::raw('SUM(order_details.qty) as total_qty'), DB::raw('SUM(order_details.total_harga) as total_nilai'))
                ->groupBy('bahan_baku_klien.id', 'bahan_baku_klien.nama')
                ->orderBy('total_nilai', 'desc')
                ->get()
                ->toArray();
            
            $poDetailsByClient[$client->klien_id] = [
                'orders' => $poDetails,
                'materials' => $clientMaterials,
            ];
        }
        
        // ========== PO TREND BY MONTH (dikonfirmasi, diproses & selesai) - NO FILTER ==========
        // Uses the new original_total_amount field which is immutable during shipments.
        $poTrendByMonth = [];
        $monthLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabels[] = $date->format('M Y');
            
            $data = DB::table('orders')
                ->whereNull('orders.deleted_at')
                ->whereYear('orders.tanggal_order', $date->year)
                ->whereMonth('orders.tanggal_order', $date->month)
                ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
                ->select(
                    DB::raw('COUNT(DISTINCT orders.id) as total_po'),
                    DB::raw('SUM(COALESCE(orders.original_total_amount, orders.total_amount)) as total_nilai')
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
                DB::raw('SUM(COALESCE(orders.original_total_amount, orders.total_amount)) as total_nilai'),
                DB::raw('AVG(COALESCE(orders.original_total_amount, orders.total_amount)) as avg_nilai')
            )
            ->groupBy('users.id', 'users.nama')
            ->orderBy('total_nilai', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) use ($totalNilaiPOForPercentage) {
                $item->percentage = $totalNilaiPOForPercentage > 0 ? ($item->total_nilai / $totalNilaiPOForPercentage) * 100 : 0;
                return $item;
            });
        
        // ========== OUTSTANDING CHART (dikonfirmasi & diproses only — untuk chart) - NO FILTER ==========
        $outstandingChartData = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->whereNotIn('order_details.status', ['selesai'])
            ->whereNull('order_details.deleted_at')
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
            'poDetailsByClient',
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
        $flag = self::CLOSED_INTERNAL_FLAG;

        // Get outstanding order details (dikonfirmasi, diproses, dan selesai-closed-internal)
        $outstandingDetails = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
            ->where(function($q) use ($flag) {
                $q->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
                  ->orWhere(function($q2) use ($flag) {
                      $q2->where('orders.status', 'selesai')
                         ->where('orders.alasan_pembatalan', $flag);
                  });
            })
            ->whereNotIn('order_details.status', ['selesai'])
            ->whereNull('order_details.deleted_at')
            ->select(
                'orders.id as order_id',
                'orders.po_number',
                'orders.no_order',
                'orders.alasan_pembatalan',
                'kliens.nama as klien_nama',
                'kliens.cabang as klien_cabang',
                'bahan_baku_klien.nama as material_nama',
                'order_details.qty',
                'order_details.harga_jual',
                'order_details.total_harga',
                'order_details.status as detail_status'
            )
            ->orderByRaw("CASE WHEN orders.alasan_pembatalan = ? THEN 1 ELSE 0 END", [$flag])
            ->orderBy('orders.po_number')
            ->orderBy('kliens.nama')
            ->get()
            ->map(function ($item) use ($flag) {
                $item->is_closed_internal = ($item->alasan_pembatalan === $flag);
                return $item;
            });
        
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
        
        // Get PO by client data with enhanced metrics
        $poByClient = Order::select(
                'kliens.id as klien_id',
                'kliens.nama as klien_nama',
                'kliens.cabang',
                DB::raw('COUNT(orders.id) as total_po'),
                DB::raw('SUM(COALESCE(orders.original_total_amount, orders.total_amount)) as total_nilai'),
                DB::raw('SUM((SELECT SUM(COALESCE(od.original_qty, od.qty)) FROM order_details od WHERE od.order_id = orders.id AND od.deleted_at IS NULL)) as total_qty'),
                DB::raw('MAX(orders.tanggal_order) as last_order_date'),
                DB::raw("SUM(CASE WHEN orders.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as status_dikonfirmasi"),
                DB::raw("SUM(CASE WHEN orders.status = 'diproses' THEN 1 ELSE 0 END) as status_diproses"),
                DB::raw("SUM(CASE WHEN orders.status = 'selesai' THEN 1 ELSE 0 END) as status_selesai"),
                DB::raw("SUM(CASE WHEN orders.status IN ('dikonfirmasi', 'diproses') THEN COALESCE(orders.original_total_amount, orders.total_amount) ELSE 0 END) as outstanding_amount")
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
        $totalOutstanding = $poByClient->sum('outstanding_amount');
        $avgPerPO = $totalPO > 0 ? $totalNilai / $totalPO : 0;
        
        // Calculate percentages for each client
        $poByClient = $poByClient->map(function($item) use ($totalNilai) {
            $item->percentage = $totalNilai > 0 ? ($item->total_nilai / $totalNilai) * 100 : 0;
            $item->avg_nilai_per_po = $item->total_po > 0 ? $item->total_nilai / $item->total_po : 0;
            return $item;
        });
        
        // Get detailed PO list per client
        $poDetailsByClient = [];
        foreach ($poByClient as $client) {
            $poDetails = Order::with(['orderDetails.bahanBakuKlien'])
                ->where('klien_id', $client->klien_id)
                ->whereIn('status', ['dikonfirmasi', 'diproses', 'selesai'])
                ->where($dateFilterQuery)
                ->orderBy('tanggal_order', 'desc')
                ->get()
                ->map(function($order) {
                    $materials = $order->orderDetails
                        ->pluck('bahanBakuKlien.nama')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();
                    
                    return [
                        'po_number' => $order->po_number ?: $order->no_order,
                        'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d/m/Y') : '-',
                        'status' => $order->status,
                        'priority' => $order->priority,
                        'total_amount' => $order->contract_amount,
                        'total_qty' => $order->original_qty_sum,
                        'materials' => implode(', ', $materials) ?: '-',
                    ];
                })
                ->toArray();
            
            $poDetailsByClient[$client->klien_id] = $poDetails;
        }
        
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
            'poDetailsByClient' => $poDetailsByClient,
            'totalKlien' => $totalKlien,
            'totalPO' => $totalPO,
            'totalNilai' => $totalNilai,
            'totalOutstanding' => $totalOutstanding,
            'avgPerPO' => $avgPerPO,
            'filterInfo' => $filterInfo,
            'generatedAt' => now()->format('d/m/Y H:i')
        ]);
        
        // Set paper size and orientation (landscape for more data)
        $pdf->setPaper('A4', 'landscape');
        
        // Generate filename with timestamp
        $filename = 'PO_By_Client_' . now()->format('Ymd_His') . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
    }
    
    /**
     * Export PO by Client to Excel
     */
    public function exportClientExcel(Request $request)
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
        };
        
        // Get PO by client data with enhanced metrics
        $poByClient = Order::select(
                'kliens.id as klien_id',
                'kliens.nama as klien_nama',
                'kliens.cabang',
                DB::raw('COUNT(orders.id) as total_po'),
                DB::raw('SUM(COALESCE(orders.original_total_amount, orders.total_amount)) as total_nilai'),
                DB::raw('SUM((SELECT SUM(COALESCE(od.original_qty, od.qty)) FROM order_details od WHERE od.order_id = orders.id AND od.deleted_at IS NULL)) as total_qty'),
                DB::raw('MAX(orders.tanggal_order) as last_order_date'),
                DB::raw("SUM(CASE WHEN orders.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as status_dikonfirmasi"),
                DB::raw("SUM(CASE WHEN orders.status = 'diproses' THEN 1 ELSE 0 END) as status_diproses"),
                DB::raw("SUM(CASE WHEN orders.status = 'selesai' THEN 1 ELSE 0 END) as status_selesai"),
                DB::raw("SUM(CASE WHEN orders.status IN ('dikonfirmasi', 'diproses') THEN COALESCE(orders.original_total_amount, orders.total_amount) ELSE 0 END) as outstanding_amount")
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
        $totalOutstanding = $poByClient->sum('outstanding_amount');
        $avgPerPO = $totalPO > 0 ? $totalNilai / $totalPO : 0;
        
        // Calculate percentages for each client
        $poByClient = $poByClient->map(function($item) use ($totalNilai) {
            $item->percentage = $totalNilai > 0 ? ($item->total_nilai / $totalNilai) * 100 : 0;
            $item->avg_nilai_per_po = $item->total_po > 0 ? $item->total_nilai / $item->total_po : 0;
            return $item;
        });
        
        // Get detailed PO list per client
        $poDetailsByClient = [];
        foreach ($poByClient as $client) {
            $poDetails = Order::with(['orderDetails.bahanBakuKlien'])
                ->where('klien_id', $client->klien_id)
                ->whereIn('status', ['dikonfirmasi', 'diproses', 'selesai'])
                ->where($dateFilterQuery)
                ->orderBy('tanggal_order', 'desc')
                ->get()
                ->map(function($order) {
                    $materials = $order->orderDetails
                        ->pluck('bahanBakuKlien.nama')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();
                    
                    return [
                        'po_number' => $order->po_number ?: $order->no_order,
                        'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d/m/Y') : '-',
                        'status' => $order->status,
                        'priority' => $order->priority,
                        'total_amount' => $order->contract_amount,
                        'total_qty' => $order->original_qty_sum,
                        'materials' => implode(', ', $materials) ?: '-',
                    ];
                })
                ->toArray();
            
            $poDetailsByClient[$client->klien_id] = $poDetails;
        }
        
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
        
        $totals = [
            'totalKlien' => $totalKlien,
            'totalPO' => $totalPO,
            'totalNilai' => $totalNilai,
            'totalOutstanding' => $totalOutstanding,
            'avgPerPO' => $avgPerPO,
        ];
        
        $filename = 'PO_By_Client_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new ClientPOExport($poByClient, $poDetailsByClient, $totals, $filterInfo), $filename);
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
                DB::raw('COALESCE(orders.original_total_amount, orders.total_amount) as total_nilai'),
                DB::raw('(SELECT SUM(COALESCE(od.original_qty, od.qty)) FROM order_details od WHERE od.order_id = orders.id AND od.deleted_at IS NULL) as total_qty')
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
                DB::raw('COALESCE(orders.original_total_amount, orders.total_amount) as total_nilai'),
                DB::raw('(SELECT SUM(COALESCE(od.original_qty, od.qty)) FROM order_details od WHERE od.order_id = orders.id AND od.deleted_at IS NULL) as total_qty')
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
        // Uses SUM(order_details.total_harga) for accuracy (see index() comment)
        $poTrendByMonth = [];
        $monthLabels = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabels[] = $date->format('M Y');
            
            $data = DB::table('orders')
                ->leftJoin('order_details', function ($join) {
                    $join->on('order_details.order_id', '=', 'orders.id')
                         ->whereNull('order_details.deleted_at');
                })
                ->whereNull('orders.deleted_at')
                ->whereYear('orders.tanggal_order', $date->year)
                ->whereMonth('orders.tanggal_order', $date->month)
                ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
                ->select(
                    DB::raw('COUNT(DISTINCT orders.id) as total_po'),
                    DB::raw('SUM(order_details.total_harga) as total_nilai')
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
        
        // Get PO details for each priority — per item (matching outstanding detail logic)
        $poDetailsByPriority = [];
        foreach ($poByPriority as $priorityData) {
            $itemRows = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
                ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
                ->where('orders.priority', $priorityData->priority)
                ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
                ->whereNotIn('order_details.status', ['selesai'])
                ->whereNull('order_details.deleted_at')
                ->select(
                    DB::raw("COALESCE(orders.po_number, orders.no_order) as po_number"),
                    'kliens.nama as klien_nama',
                    'kliens.cabang',
                    'orders.tanggal_order',
                    'orders.status',
                    DB::raw("COALESCE(bahan_baku_klien.nama, order_details.nama_material_po, '-') as bahan_baku"),
                    'order_details.qty as total_qty',
                    'order_details.harga_jual',
                    DB::raw("COALESCE(NULLIF(order_details.total_harga, 0), order_details.qty * order_details.harga_jual) as total_amount")
                )
                ->orderBy('orders.po_number')
                ->orderBy('kliens.nama')
                ->get()
                ->map(function($row) {
                    return [
                        'po_number'    => $row->po_number,
                        'klien_nama'   => $row->klien_nama,
                        'cabang'       => $row->cabang ?? '-',
                        'tanggal_order'=> $row->tanggal_order ? Carbon::parse($row->tanggal_order)->format('d/m/Y') : '-',
                        'bahan_baku'   => $row->bahan_baku,
                        'total_qty'    => (float) $row->total_qty,
                        'harga_jual'   => (float) $row->harga_jual,
                        'total_amount' => (float) $row->total_amount,
                        'status'       => $row->status,
                    ];
                })
                ->toArray();

            $poDetailsByPriority[$priorityData->priority] = $itemRows;
        }

        // Recalculate poByPriority->nilai from order_details (to match detail rows total)
        foreach ($poByPriority as $priorityData) {
            $nilaiFromDetails = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->where('orders.priority', $priorityData->priority)
                ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
                ->whereNotIn('order_details.status', ['selesai'])
                ->whereNull('order_details.deleted_at')
                ->sum(DB::raw("COALESCE(NULLIF(order_details.total_harga, 0), order_details.qty * order_details.harga_jual)"));
            $priorityData->nilai = (float) $nilaiFromDetails;
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

    // Penanda close internal (disimpan di kolom alasan_pembatalan, tanpa ubah ENUM status)
    const CLOSED_INTERNAL_FLAG = '[CLOSED_INTERNAL]';

    /**
     * Close Pabrik: set order status to 'selesai' → removed from outstanding permanently
     */
    public function closePabrik(Request $request, Order $order)
    {
        if (!in_array($order->status, ['dikonfirmasi', 'diproses'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dapat di-close karena statusnya bukan dikonfirmasi/diproses.'
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->status    = 'selesai';
            $order->selesai_at = now();
            // Hapus flag internal jika ada
            if ($order->alasan_pembatalan === self::CLOSED_INTERNAL_FLAG) {
                $order->alasan_pembatalan = null;
            }
            $order->save();
        });

        $poLabel = $order->po_number ?: $order->no_order;
        return response()->json([
            'success' => true,
            'message' => "Order {$poLabel} berhasil di-close (Closed Pabrik). Status diubah menjadi Selesai."
        ]);
    }

    /**
     * Close Internal: tandai order dengan flag di alasan_pembatalan.
     * Status tetap 'diproses', order masih tampil di outstanding dengan badge "Internal".
     */
    public function closeInternal(Request $request, Order $order)
    {
        if (!in_array($order->status, ['dikonfirmasi', 'diproses'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dapat di-close karena statusnya bukan dikonfirmasi/diproses.'
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->status            = 'selesai';
            $order->selesai_at        = now();
            $order->alasan_pembatalan = self::CLOSED_INTERNAL_FLAG;
            $order->save();
        });

        $poLabel = $order->po_number ?: $order->no_order;
        return response()->json([
            'success' => true,
            'message' => "Order {$poLabel} berhasil di-close secara internal. Order masih tampil di outstanding dan dapat dikembalikan."
        ]);
    }

    /**
     * Reopen: hapus flag closed internal, kembalikan ke diproses
     */
    public function reopenOrder(Request $request, Order $order)
    {
        if ($order->alasan_pembatalan !== self::CLOSED_INTERNAL_FLAG) {
            return response()->json([
                'success' => false,
                'message' => 'Order ini tidak sedang dalam status Closed Internal.'
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->status            = 'diproses';
            $order->selesai_at        = null;
            $order->alasan_pembatalan = null;
            $order->save();
        });

        $poLabel = $order->po_number ?: $order->no_order;
        return response()->json([
            'success' => true,
            'message' => "Order {$poLabel} berhasil dikembalikan ke status aktif."
        ]);
    }
}
