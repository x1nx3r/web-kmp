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
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    // Penanda close internal (disimpan di kolom alasan_pembatalan, tanpa ubah ENUM status)
    const CLOSED_INTERNAL_FLAG = '[CLOSED_INTERNAL]';

    /**
     * Menampilkan dashboard utama Purchase Order
     */
    public function index(Request $request): View
    {
        $title = 'Purchase Order';
        $activeTab = 'po';
        
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // ========== SUMMARY STATISTICS - NO FILTER ==========
        $totalOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.total_harga');
        
        $totalQtyOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.qty');
        
        $poBerjalan = Order::whereIn('status', ['dikonfirmasi', 'diproses'])->count();
        
        $totalNilaiPOBerjalan = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->whereNull('order_details.deleted_at')
            ->sum(DB::raw('COALESCE(order_details.original_qty, order_details.qty) * order_details.harga_jual'));
            
        $avgNilaiPerPO = $poBerjalan > 0 ? $totalNilaiPOBerjalan / $poBerjalan : 0;
        
        // Dynamic Alignment Percentage
        $totalNilaiPOForPercentage = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where(function($query) use ($periode, $startDate, $endDate) {
                $this->applyDateFilter($query, $periode, $startDate, $endDate, 'orders');
            })
            ->whereNull('order_details.deleted_at')
            ->sum(DB::raw('COALESCE(order_details.original_qty, order_details.qty) * order_details.harga_jual'));
        
        // ========== PO BY STATUS & DETAILS ==========
        $poByStatus = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->select('orders.status', 
                DB::raw('COUNT(DISTINCT orders.id) as total'), 
                DB::raw('SUM(COALESCE(order_details.original_qty, order_details.qty) * order_details.harga_jual) as nilai'))
            ->whereNull('order_details.deleted_at')
            ->groupBy('orders.status')
            ->get();
            
        $poDetailsByStatus = $this->getPoDetailsByStatus($poByStatus->pluck('status')->toArray());
        
        // ========== PO BY PRIORITY & DETAILS ==========
        $poByPriority = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->select('orders.priority', 
                DB::raw('COUNT(DISTINCT orders.id) as total'), 
                DB::raw('SUM(COALESCE(order_details.original_qty, order_details.qty) * order_details.harga_jual) as nilai'))
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->whereNull('order_details.deleted_at')
            ->groupBy('orders.priority')
            ->get();
            
        $poDetailsByPriority = $this->getPoDetailsByPriority($poByPriority);
        
        // ========== PO BY CLIENT & DETAILS (DRY Refactored) ==========
        $clientReportData = $this->getClientPOReportData($request, $totalNilaiPOForPercentage);
        $poByClient = $clientReportData['poByClient'];
        $poDetailsByClient = $clientReportData['poDetailsByClient'];
        
        // ========== PO TREND BY MONTH ==========
        $trendData = $this->getPoTrendByMonth();
        $poTrendByMonth = $trendData['data'];
        $monthLabels = $trendData['labels'];
        
        // ========== RECENT POs ==========
        $recentPOs = Order::with(['klien', 'creator'])
            ->whereIn('status', ['dikonfirmasi', 'diproses'])
            ->orderBy('tanggal_order', 'desc')
            ->limit(10)
            ->get();
        
        // ========== ORDER WINNERS ==========
        $orderWinners = $this->getOrderWinnersSummary($request, $totalNilaiPOForPercentage);
        
        // ========== OUTSTANDING CHART ==========
        $outstandingChartData = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->whereNotIn('order_details.status', ['selesai'])
            ->whereNull('order_details.deleted_at')
            ->select(
                'orders.id',
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
            'title', 'activeTab', 'periode', 'startDate', 'endDate',
            'totalOutstanding', 'totalQtyOutstanding', 'poBerjalan', 'avgNilaiPerPO',
            'poByStatus', 'poDetailsByStatus', 'poByPriority', 'poDetailsByPriority',
            'poByClient', 'poDetailsByClient', 'poTrendByMonth', 'monthLabels',
            'recentPOs', 'orderWinners', 'outstandingChartData', 'totalOutstandingChart'
        ));
    }
    
    public function export(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
    
    public function exportOutstandingPdf()
    {
        $flag = self::CLOSED_INTERNAL_FLAG;

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
                'orders.id as order_id', 'orders.po_number', 'orders.no_order', 'orders.alasan_pembatalan',
                'kliens.nama as klien_nama', 'kliens.cabang as klien_cabang',
                'bahan_baku_klien.nama as material_nama',
                'order_details.qty', 'order_details.harga_jual', 'order_details.total_harga',
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
        
        $pdf = Pdf::loadView('pages.laporan.pdf.outstanding', [
            'outstandingDetails' => $outstandingDetails,
            'totalQty' => $outstandingDetails->sum('qty'),
            'totalNilai' => $outstandingDetails->sum('total_harga'),
            'totalPO' => $outstandingDetails->pluck('po_number')->unique()->count(),
            'generatedAt' => now()->format('d/m/Y H:i')
        ])->setPaper('A4', 'landscape');
        
        return $pdf->download('Outstanding_PO_' . now()->format('Ymd_His') . '.pdf');
    }
    
    public function exportClientPdf(Request $request)
    {
        $data = $this->getClientPOReportData($request);
        
        $pdf = Pdf::loadView('pages.laporan.pdf.client', [
            'poByClient' => $data['poByClient'],
            'poDetailsByClient' => $data['poDetailsByClient'],
            'totalKlien' => $data['totalKlien'],
            'totalPO' => $data['totalPO'],
            'totalNilai' => $data['totalNilai'],
            'totalOutstanding' => $data['totalOutstanding'],
            'avgPerPO' => $data['avgPerPO'],
            'filterInfo' => $data['filterInfo'],
            'generatedAt' => now()->format('d/m/Y H:i')
        ])->setPaper('A4', 'landscape');
        
        return $pdf->download('PO_By_Client_' . now()->format('Ymd_His') . '.pdf');
    }
    
    public function exportClientExcel(Request $request)
    {
        $data = $this->getClientPOReportData($request);
        
        $totals = [
            'totalKlien' => $data['totalKlien'],
            'totalPO' => $data['totalPO'],
            'totalNilai' => $data['totalNilai'],
            'totalOutstanding' => $data['totalOutstanding'],
            'avgPerPO' => $data['avgPerPO'],
        ];
        
        return Excel::download(
            new ClientPOExport($data['poByClient'], $data['poDetailsByClient'], $totals, $data['filterInfo']), 
            'PO_By_Client_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
    
    public function orderWinnerDetails(Request $request): JsonResponse
    {
        $groupedData = $this->buildOrderWinnerGroupedData($request)['groupedData'];
        
        // Convert associative arrays to indexed arrays for JSON
        foreach ($groupedData as &$marketing) {
            $marketing['kliens'] = array_values($marketing['kliens']);
            foreach ($marketing['kliens'] as &$klien) {
                $klien['cabangs'] = array_values($klien['cabangs']);
            }
        }
        
        return response()->json(array_values($groupedData));
    }
    
    public function exportOrderWinnerPdf(Request $request)
    {
        $data = $this->buildOrderWinnerGroupedData($request);
        
        $pdf = Pdf::loadView('pages.laporan.pdf.order-winner', [
            'groupedData' => $data['groupedData'],
            'totalPO' => $data['totalPO'],
            'totalNilai' => $data['totalNilai'],
            'filterInfo' => $this->getFilterInfoText($request),
            'generatedAt' => now()->format('d/m/Y H:i')
        ])->setPaper('A4', 'portrait');
        
        return $pdf->download('Order_Winners_' . now()->format('Ymd_His') . '.pdf');
    }
    
    public function exportTrendPdf()
    {
        $trendData = $this->getPoTrendByMonth();
        
        $totalPO = array_sum(array_column($trendData['data'], 'total_po'));
        $totalNilai = array_sum(array_column($trendData['data'], 'total_nilai'));
        $avgPerPO = $totalPO > 0 ? $totalNilai / $totalPO : 0;
        
        $pdf = Pdf::loadView('pages.laporan.pdf.po-trend', [
            'poTrendByMonth' => $trendData['data'],
            'totalPO' => $totalPO,
            'totalNilai' => $totalNilai,
            'avgPerPO' => $avgPerPO,
            'generatedAt' => now()->format('d/m/Y H:i')
        ])->setPaper('A4', 'portrait');
        
        return $pdf->download('PO_Trend_12_Bulan_' . now()->format('Ymd_His') . '.pdf');
    }
    
    public function exportPriorityPdf()
    {
        $poByPriority = Order::select('priority', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_amount) as nilai'))
            ->whereIn('status', ['dikonfirmasi', 'diproses'])
            ->groupBy('priority')
            ->get();
        
        $poDetailsByPriority = $this->getPoDetailsByPriority($poByPriority);
        
        $totalPO = $poByPriority->sum('total');
        $totalNilai = $poByPriority->sum('nilai');
        
        $pdf = Pdf::loadView('pages.laporan.pdf.po-priority', [
            'poByPriority' => $poByPriority,
            'poDetailsByPriority' => $poDetailsByPriority,
            'totalPO' => $totalPO,
            'totalNilai' => $totalNilai,
            'avgPerPO' => $totalPO > 0 ? $totalNilai / $totalPO : 0,
            'generatedAt' => now()->format('d/m/Y H:i')
        ])->setPaper('A4', 'landscape');
        
        return $pdf->download('PO_Berdasarkan_Prioritas_' . now()->format('Ymd_His') . '.pdf');
    }
    
    public function exportStatusPdf()
    {
        $poByStatus = Order::select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_amount) as nilai'))
            ->groupBy('status')
            ->get();
        
        $poDetailsByStatus = [];
        // Eliminasi N+1 dengan Eager Loading sekaligus
        $allOrders = Order::with('klien')->whereIn('status', $poByStatus->pluck('status'))->orderBy('po_number')->get();
        
        foreach ($poByStatus as $statusData) {
            $poDetailsByStatus[$statusData->status] = $allOrders->where('status', $statusData->status)
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
                })->toArray();
        }
        
        $pdf = Pdf::loadView('pages.laporan.pdf.po-status', [
            'poByStatus' => $poByStatus,
            'poDetailsByStatus' => $poDetailsByStatus,
            'totalPO' => $poByStatus->sum('total'),
            'totalNilai' => $poByStatus->sum('nilai'),
            'generatedAt' => now()->format('d/m/Y H:i')
        ])->setPaper('A4', 'landscape');
        
        return $pdf->download('PO_Berdasarkan_Status_' . now()->format('Ymd_His') . '.pdf');
    }

    public function closePabrik(Request $request, Order $order): JsonResponse
    {
        return $this->handleStatusUpdate($order, 'selesai', null, 'Closed Pabrik');
    }

    public function closeInternal(Request $request, Order $order): JsonResponse
    {
        return $this->handleStatusUpdate($order, 'selesai', self::CLOSED_INTERNAL_FLAG, 'closed secara internal');
    }

    public function reopenOrder(Request $request, Order $order): JsonResponse
    {
        if ($order->alasan_pembatalan !== self::CLOSED_INTERNAL_FLAG) {
            return response()->json(['success' => false, 'message' => 'Order ini tidak sedang dalam status Closed Internal.'], 422);
        }

        try {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => 'diproses',
                    'selesai_at' => null,
                    'alasan_pembatalan' => null
                ]);
            });

            $poLabel = $order->po_number ?: $order->no_order;
            return response()->json(['success' => true, 'message' => "Order {$poLabel} berhasil dikembalikan ke status aktif."]);
        } catch (\Exception $e) {
            Log::error('Reopen Order Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }

    // =========================================================================
    // PRIVATE HELPER METHODS (INTERNAL SERVICES & REFACTORING LOGIC)
    // =========================================================================

    private function applyDateFilter($query, $periode, $startDate, $endDate, $tablePrefix = 'orders'): void
    {
        $column = $tablePrefix ? "{$tablePrefix}.tanggal_order" : 'tanggal_order';
        
        if ($periode === 'tahun_ini') {
            $query->whereYear($column, Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear($column, Carbon::now()->year)
                  ->whereMonth($column, Carbon::now()->month);
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $query->whereBetween($column, [$startDate, $endDate]);
        }
    }

    private function getFilterInfoText(Request $request): string
    {
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($periode === 'tahun_ini') return 'Tahun ' . Carbon::now()->year;
        if ($periode === 'bulan_ini') return Carbon::now()->isoFormat('MMMM YYYY');
        if ($periode === 'custom' && $startDate && $endDate) return Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');
        return 'Semua Data';
    }

    private function getPoDetailsByStatus(array $statuses): array
    {
        // Optimasi N+1
        $allOrders = Order::with('klien')
            ->whereIn('status', $statuses)
            ->orderBy('po_number')
            ->get();
            
        $poDetailsByStatus = [];
        foreach ($statuses as $status) {
            $poDetailsByStatus[$status] = $allOrders->where('status', $status)
                ->map(function($order) {
                    return [
                        'po_number' => $order->po_number ?: $order->no_order,
                        'klien_nama' => $order->klien->nama ?? '-',
                        'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d/m/Y') : '-'
                    ];
                })->values()->toArray();
        }
        return $poDetailsByStatus;
    }

    private function getPoDetailsByPriority($poByPriority): array
    {
        // Fetch semua data sekaligus untuk mencegah N+1 di dalam loop priority
        $priorities = $poByPriority->pluck('priority')->toArray();
        
        $allItems = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
            ->whereIn('orders.priority', $priorities)
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->whereNotIn('order_details.status', ['selesai'])
            ->whereNull('order_details.deleted_at')
            ->select(
                'orders.id as order_id', 'orders.priority',
                DB::raw("COALESCE(orders.po_number, orders.no_order) as po_number"),
                'kliens.nama as klien_nama', 'kliens.cabang', 'orders.po_end_date', 'orders.status',
                DB::raw("COALESCE(bahan_baku_klien.nama, order_details.nama_material_po, '-') as bahan_baku"),
                'order_details.qty as total_qty', 'order_details.harga_jual',
                DB::raw("COALESCE(NULLIF(order_details.total_harga, 0), order_details.qty * order_details.harga_jual) as total_amount")
            )
            ->orderBy('orders.po_number')->orderBy('kliens.nama')->get();

        $poDetailsByPriority = [];
        foreach ($poByPriority as $priorityData) {
            // Group detail item berdasar priority
            $poDetailsByPriority[$priorityData->priority] = $allItems->where('priority', $priorityData->priority)
                ->map(function($row) {
                    return [
                        'po_number'    => $row->po_number,
                        'klien_nama'   => $row->klien_nama,
                        'cabang'       => $row->cabang ?? '-',
                        'tanggal_order'=> $row->po_end_date ? Carbon::parse($row->po_end_date)->format('d/m/Y') : '-',
                        'bahan_baku'   => $row->bahan_baku,
                        'total_qty'    => (float) $row->total_qty,
                        'harga_jual'   => (float) $row->harga_jual,
                        'total_amount' => (float) $row->total_amount,
                        'status'       => $row->status,
                    ];
                })->values()->toArray();
            
            // Recalculate priority value
            $priorityData->nilai = (float) $allItems->where('priority', $priorityData->priority)->sum('total_amount');
        }
        
        return $poDetailsByPriority;
    }

    private function getClientPOReportData(Request $request, $totalNilaiPOForPercentage = 0): array
    {
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $poByClient = Order::select(
                'kliens.id as klien_id', 'kliens.nama as klien_nama', 'kliens.cabang',
                DB::raw('COUNT(orders.id) as total_po'),
                DB::raw('SUM(COALESCE(orders.original_total_amount, orders.total_amount)) as total_nilai'),
                DB::raw('SUM((SELECT SUM(COALESCE(od.original_qty, od.qty)) FROM order_details od WHERE od.order_id = orders.id AND od.deleted_at IS NULL)) as total_qty'),
                DB::raw('MAX(orders.tanggal_order) as last_order_date'),
                DB::raw("SUM(CASE WHEN orders.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as status_dikonfirmasi"),
                DB::raw("SUM(CASE WHEN orders.status = 'diproses' THEN 1 ELSE 0 END) as status_diproses"),
                DB::raw("SUM(CASE WHEN orders.status = 'selesai' THEN 1 ELSE 0 END) as status_selesai"),
                DB::raw("SUM(CASE WHEN orders.status IN ('dikonfirmasi', 'diproses') THEN COALESCE(orders.original_total_amount, orders.total_amount) ELSE 0 END) as outstanding_amount"),
                DB::raw("SUM(CASE WHEN orders.status IN ('dikonfirmasi', 'diproses') THEN (SELECT SUM(COALESCE(od.original_qty, od.qty)) FROM order_details od WHERE od.order_id = orders.id AND od.deleted_at IS NULL) ELSE 0 END) as outstanding_qty")
            )
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where(function($query) use ($periode, $startDate, $endDate) {
                $this->applyDateFilter($query, $periode, $startDate, $endDate, 'orders');
            })
            ->groupBy('kliens.id', 'kliens.nama', 'kliens.cabang')
            ->orderBy('total_nilai', 'desc')
            ->get();

        $totalNilai = $poByClient->sum('total_nilai');
        $referenceTotal = $totalNilaiPOForPercentage > 0 ? $totalNilaiPOForPercentage : $totalNilai;

        $poByClient->transform(function($item) use ($referenceTotal) {
            $item->percentage = $referenceTotal > 0 ? ($item->total_nilai / $referenceTotal) * 100 : 0;
            $item->avg_nilai_per_po = $item->total_po > 0 ? $item->total_nilai / $item->total_po : 0;
            return $item;
        });

        // Mengatasi N+1 pada detail orders & materials dengan satu eager load besar
        $clientIds = $poByClient->pluck('klien_id')->toArray();
        $allOrders = Order::with(['orderDetails.bahanBakuKlien'])
            ->whereIn('klien_id', $clientIds)
            ->whereIn('status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where(function($query) use ($periode, $startDate, $endDate) {
                $this->applyDateFilter($query, $periode, $startDate, $endDate);
            })
            ->orderBy('tanggal_order', 'desc')
            ->get();

        $allMaterials = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
            ->whereIn('orders.klien_id', $clientIds)
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where(function($query) use ($periode, $startDate, $endDate) {
                $this->applyDateFilter($query, $periode, $startDate, $endDate, 'orders');
            })
            ->select('orders.klien_id', 'bahan_baku_klien.id', 'bahan_baku_klien.nama', 
                DB::raw('SUM(order_details.qty) as total_qty'), DB::raw('SUM(order_details.total_harga) as total_nilai'))
            ->groupBy('orders.klien_id', 'bahan_baku_klien.id', 'bahan_baku_klien.nama')
            ->orderBy('total_nilai', 'desc')
            ->get();

        $poDetailsByClient = [];
        foreach ($poByClient as $client) {
            $clientOrders = $allOrders->where('klien_id', $client->klien_id)->map(function($order) {
                $materials = $order->orderDetails->pluck('bahanBakuKlien.nama')->filter()->unique()->values()->toArray();
                return [
                    'id' => $order->id, 'po_number' => $order->po_number ?: $order->no_order,
                    'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d/m/Y') : '-',
                    'status' => $order->status, 'priority' => $order->priority,
                    'total_amount' => $order->contract_amount, 'total_qty' => $order->original_qty_sum,
                    'materials' => implode(', ', $materials) ?: '-', 'materials_count' => count($materials),
                ];
            })->values()->toArray();

            $clientMaterials = $allMaterials->where('klien_id', $client->klien_id)->map(function($item) {
                return ['nama' => $item->nama, 'total_qty' => $item->total_qty, 'total_nilai' => $item->total_nilai];
            })->values()->toArray();
            
            $poDetailsByClient[$client->klien_id] = [
                'orders' => $clientOrders,
                'materials' => $clientMaterials
            ];
        }

        return [
            'poByClient' => $poByClient,
            'poDetailsByClient' => $poDetailsByClient,
            'totalKlien' => $poByClient->count(),
            'totalPO' => $poByClient->sum('total_po'),
            'totalNilai' => $totalNilai,
            'totalOutstanding' => $poByClient->sum('outstanding_amount'),
            'avgPerPO' => $poByClient->sum('total_po') > 0 ? $totalNilai / $poByClient->sum('total_po') : 0,
            'filterInfo' => $this->getFilterInfoText($request)
        ];
    }

    private function getPoTrendByMonth(): array
    {
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
        
        return ['data' => $poTrendByMonth, 'labels' => $monthLabels];
    }

    private function getOrderWinnersSummary(Request $request, $totalNilaiPOForPercentage)
    {
        return DB::table('order_winners')
            ->join('orders', 'order_winners.order_id', '=', 'orders.id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where(function($query) use ($request) {
                $this->applyDateFilter($query, $request->get('periode', 'all'), $request->get('start_date'), $request->get('end_date'), 'orders');
            })
            ->select('users.id as user_id', 'users.nama as marketing_nama',
                DB::raw('COUNT(DISTINCT orders.id) as total_po'),
                DB::raw('SUM(COALESCE(orders.original_total_amount, orders.total_amount)) as total_nilai'),
                DB::raw('AVG(COALESCE(orders.original_total_amount, orders.total_amount)) as avg_nilai')
            )
            ->groupBy('users.id', 'users.nama')->orderBy('total_nilai', 'desc')->limit(10)->get()
            ->map(function($item) use ($totalNilaiPOForPercentage) {
                $item->percentage = $totalNilaiPOForPercentage > 0 ? ($item->total_nilai / $totalNilaiPOForPercentage) * 100 : 0;
                return $item;
            });
    }

    private function buildOrderWinnerGroupedData(Request $request): array
    {
        $details = DB::table('order_winners')
            ->join('orders', 'order_winners.order_id', '=', 'orders.id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses', 'selesai'])
            ->where(function($query) use ($request) {
                $this->applyDateFilter($query, $request->get('periode', 'all'), $request->get('start_date'), $request->get('end_date'), 'orders');
            })
            ->select('users.nama as marketing_nama', 'kliens.nama as klien_nama', 'kliens.cabang as klien_cabang',
                'orders.po_number', 'orders.tanggal_order', 'orders.status as order_status',
                DB::raw('COALESCE(orders.original_total_amount, orders.total_amount) as total_nilai'),
                DB::raw('(SELECT SUM(COALESCE(od.original_qty, od.qty)) FROM order_details od WHERE od.order_id = orders.id AND od.deleted_at IS NULL) as total_qty')
            )
            ->orderBy('users.nama')->orderBy('kliens.nama')->orderBy('kliens.cabang')->orderBy('orders.tanggal_order', 'desc')
            ->get();
        
        $groupedData = [];
        $totalNilai = 0;
        $totalPO = 0;
        
        foreach ($details as $item) {
            $marketingKey = $item->marketing_nama;
            $klienKey = $item->klien_nama;
            $cabangKey = $item->klien_cabang ?: 'Tanpa Cabang';
            
            if (!isset($groupedData[$marketingKey])) {
                $groupedData[$marketingKey] = ['marketing_nama' => $item->marketing_nama, 'total_nilai' => 0, 'total_po' => 0, 'kliens' => []];
            }
            if (!isset($groupedData[$marketingKey]['kliens'][$klienKey])) {
                $groupedData[$marketingKey]['kliens'][$klienKey] = ['klien_nama' => $item->klien_nama, 'total_nilai' => 0, 'total_po' => 0, 'cabangs' => []];
            }
            if (!isset($groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey])) {
                $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey] = ['cabang_nama' => $cabangKey, 'total_nilai' => 0, 'total_po' => 0, 'orders' => []];
            }
            
            $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey]['orders'][] = [
                'po_number' => $item->po_number,
                'tanggal_order' => Carbon::parse($item->tanggal_order)->format('d/m/Y'),
                'order_status' => $item->order_status,
                'total_nilai' => $item->total_nilai,
                'total_qty' => $item->total_qty
            ];
            
            $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey]['total_nilai'] += $item->total_nilai;
            $groupedData[$marketingKey]['kliens'][$klienKey]['cabangs'][$cabangKey]['total_po']++;
            $groupedData[$marketingKey]['kliens'][$klienKey]['total_nilai'] += $item->total_nilai;
            $groupedData[$marketingKey]['kliens'][$klienKey]['total_po']++;
            $groupedData[$marketingKey]['total_nilai'] += $item->total_nilai;
            $groupedData[$marketingKey]['total_po']++;
            
            $totalNilai += $item->total_nilai;
            $totalPO++;
        }
        
        return ['groupedData' => $groupedData, 'totalNilai' => $totalNilai, 'totalPO' => $totalPO];
    }

    private function handleStatusUpdate(Order $order, string $status, ?string $alasan, string $actionName): JsonResponse
    {
        if (!in_array($order->status, ['dikonfirmasi', 'diproses'])) {
            return response()->json(['success' => false, 'message' => 'Order tidak dapat diproses karena statusnya bukan dikonfirmasi/diproses.'], 422);
        }

        try {
            DB::transaction(function () use ($order, $status, $alasan) {
                $order->update([
                    'status' => $status,
                    'selesai_at' => ($status === 'selesai') ? now() : null,
                    'alasan_pembatalan' => $alasan,
                ]);
            });

            $poLabel = $order->po_number ?: $order->no_order;
            return response()->json(['success' => true, 'message' => "Order {$poLabel} berhasil {$actionName}."]);
        } catch (\Exception $e) {
            Log::error("{$actionName} Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}