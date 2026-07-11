<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\PengirimanDetail;
use App\Models\User;
use App\Exports\PengirimanExport;
use App\Services\ReferenceDataService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PengirimanController extends Controller
{
    // Konstanta untuk menghindari Hardcoded String & Magic Numbers
    private const STATUS_BERHASIL = 'berhasil';
    private const STATUS_GAGAL = 'gagal';
    private const STATUS_MENUNGGU_FISIK = 'menunggu_fisik';
    private const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    
    private const BONGKAR_THRESHOLD = 70;
    private const PAGINATION_LIMIT = 15;

    public function index(Request $request)
    {
        // Validasi input parameter untuk keamanan
        $request->validate([
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date',
            'pie_start_date' => 'nullable|date',
            'pie_end_date'   => 'nullable|date',
            'year'           => 'nullable|integer',
        ]);

        $title = 'Pengiriman';
        $activeTab = 'pengiriman';
        
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status');
        $purchasing = $request->get('purchasing');
        $search = $request->get('search');
        $pabrik = $request->get('pabrik');
        $supplier = $request->get('supplier');
        
        $weekDates = $this->calculateWeekDates();
        
        $weeklyStats = $this->getWeeklyStats($weekDates['start'], $weekDates['end']);
        $yearlyStats = $this->getYearlyStats(now()->year);
        $totalStats = $this->getTotalStats();
        
        $pieChartFilter = $request->get('pie_filter', 'bulan_ini');
        $pieChartDates = $this->resolvePieChartDates($request, $pieChartFilter);
        $pieChartData = $this->getPieChartData($pieChartFilter, $pieChartDates['start'], $pieChartDates['end']);
        
        $yearRange = $this->getYearRange();
        $selectedYear = $request->get('year', now()->year);
        $chartData = $this->getYearlyChartData($selectedYear);
        
        $pengirimanData = $this->getPaginatedPengiriman(
            $startDate, $endDate, $status, $purchasing, $search, $pabrik, $supplier
        );
        
        $purchasingUsers = ReferenceDataService::getPurchasingUsers();
        $pabrikList = ReferenceDataService::getKliens();
        $supplierList = ReferenceDataService::getSuppliers();
        
        return view('pages.laporan.pengiriman', compact(
            'title', 'activeTab', 'weeklyStats', 'yearlyStats', 'totalStats',
            'chartData', 'yearRange', 'pengirimanData', 'purchasingUsers',
            'pabrikList', 'supplierList', 'startDate', 'endDate', 'status',
            'purchasing', 'search', 'pabrik', 'supplier', 'pieChartFilter',
            'pieChartData'
        ) + [
            'pieChartStartDate' => $pieChartDates['start'],
            'pieChartEndDate' => $pieChartDates['end'],
        ]);
    }

    /**
     * Memusatkan query logic "smart date filter" agar tidak redundant
     */
    private function applyDateFilter($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->where(function($subq) use ($startDate, $endDate) {
                // Pengiriman normal (bukan gagal)
                $subq->where('pengiriman.status', '!=', self::STATUS_GAGAL)
                     ->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
            })->orWhere(function($subq) use ($startDate, $endDate) {
                // Pengiriman gagal yang PUNYA tanggal_kirim
                $subq->where('pengiriman.status', self::STATUS_GAGAL)
                     ->whereNotNull('pengiriman.tanggal_kirim')
                     ->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
            })->orWhere(function($subq) use ($startDate, $endDate) {
                // Pengiriman gagal yang TIDAK punya tanggal_kirim
                $subq->where('pengiriman.status', self::STATUS_GAGAL)
                     ->whereNull('pengiriman.tanggal_kirim')
                     ->whereBetween('pengiriman.updated_at', [$startDate, $endDate]);
            });
        });
    }

    private function getPaginatedPengiriman($startDate, $endDate, $status, $purchasing, $search, $pabrik, $supplier)
    {
        $query = Pengiriman::with(['purchasing', 'purchaseOrder', 'pengirimanDetails.bahanBakuSupplier', 'invoicePenagihan'])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->select('pengiriman.*', 
                'orders.po_number',
                DB::raw('CASE WHEN pengiriman.status = "'.self::STATUS_BERHASIL.'" AND invoice_penagihan.qty_after_refraksi IS NOT NULL THEN invoice_penagihan.qty_after_refraksi ELSE pengiriman.total_qty_kirim END as display_qty'),
                DB::raw('CASE WHEN pengiriman.status = "'.self::STATUS_BERHASIL.'" AND invoice_penagihan.amount_after_refraksi IS NOT NULL THEN invoice_penagihan.amount_after_refraksi ELSE pengiriman.total_harga_kirim END as display_harga')
            );
            
        $query = $this->applyDateFilter($query, $startDate, $endDate);
        
        if ($status) $query->where('pengiriman.status', $status);
        if ($purchasing) $query->where('pengiriman.purchasing_id', $purchasing);
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('pengiriman.no_pengiriman', 'like', "%{$search}%")
                  ->orWhere('orders.po_number', 'like', "%{$search}%");
            });
        }
        if ($pabrik) {
            $query->whereHas('order.klien', fn($q) => $q->where('id', $pabrik));
        }
        if ($supplier) {
            $query->whereHas('pengirimanDetails.bahanBakuSupplier.supplier', fn($q) => $q->where('id', $supplier));
        }
        
        return $query->orderBy('pengiriman.tanggal_kirim', 'desc')->paginate(self::PAGINATION_LIMIT);
    }
    
    private function calculateWeekDates(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $currentWeekOfMonth = 1;
        $tempDate = $startOfMonth->copy();
        
        while ($tempDate->addDays(7)->lte(Carbon::now()->startOfWeek())) {
            $currentWeekOfMonth++;
        }
        $currentWeekOfMonth = min($currentWeekOfMonth, 4);
        
        $weekStart = ($currentWeekOfMonth == 1) ? $startOfMonth->copy() : $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);
        $weekEnd = ($currentWeekOfMonth == 4) ? $startOfMonth->copy()->endOfMonth() : $weekStart->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
        
        return ['start' => $weekStart, 'end' => $weekEnd];
    }

    private function resolvePieChartDates(Request $request, string $filter): array
    {
        $start = null;
        $end = null;
        switch ($filter) {
            case 'bulan_ini':
                $start = now()->startOfMonth()->format('Y-m-d');
                $end = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'tahun_ini':
                $start = now()->startOfYear()->format('Y-m-d');
                $end = now()->endOfYear()->format('Y-m-d');
                break;
            case 'range':
                $start = $request->get('pie_start_date', now()->startOfMonth()->format('Y-m-d'));
                $end = $request->get('pie_end_date', now()->endOfMonth()->format('Y-m-d'));
                break;
        }
        return ['start' => $start, 'end' => $end];
    }
    
    private function getYearRange(): array
    {
        $range = Pengiriman::whereNotNull('tanggal_kirim')
            ->selectRaw('MIN(YEAR(tanggal_kirim)) as min_year, MAX(YEAR(tanggal_kirim)) as max_year')
            ->first();
            
        $minYear = $range->min_year ?? (now()->year - 2);
        $maxYear = max((int) ($range->max_year ?? now()->year), now()->year);
        
        return [
            'min_year' => (int) $minYear,
            'max_year' => (int) $maxYear
        ];
    }
    
    private function getPieChartData($filter, $startDate, $endDate): array
    {
        // Query Gagal
        $gagalQuery = Pengiriman::where('pengiriman.status', self::STATUS_GAGAL);
        if ($filter !== 'semua' && $startDate && $endDate) {
            $gagalQuery = $this->applyDateFilter($gagalQuery, $startDate, $endDate);
        }
        $gagalCount = $gagalQuery->count();

        // Query Normal & Bongkar (Optimized Database Aggregation)
        $nbQuery = Pengiriman::leftJoin('forecasts', 'pengiriman.forecast_id', '=', 'forecasts.id')
            ->whereIn('pengiriman.status', [self::STATUS_BERHASIL, self::STATUS_MENUNGGU_FISIK, self::STATUS_MENUNGGU_VERIFIKASI]);
            
        if ($filter !== 'semua' && $startDate && $endDate) {
            $nbQuery->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
        }
        
        $nbStats = $nbQuery->selectRaw("
            SUM(CASE 
                WHEN forecasts.id IS NULL OR forecasts.total_qty_forecast IS NULL OR forecasts.total_qty_forecast = 0 THEN 1
                WHEN (pengiriman.total_qty_kirim / forecasts.total_qty_forecast * 100) > ? THEN 1
                ELSE 0
            END) as normal_count,
            SUM(CASE 
                WHEN forecasts.id IS NOT NULL AND forecasts.total_qty_forecast > 0 
                     AND (pengiriman.total_qty_kirim / forecasts.total_qty_forecast * 100) <= ? THEN 1
                ELSE 0
            END) as bongkar_count
        ", [self::BONGKAR_THRESHOLD, self::BONGKAR_THRESHOLD])->first();

        $normalCount = (int) ($nbStats->normal_count ?? 0);
        $bongkarCount = (int) ($nbStats->bongkar_count ?? 0);
        $total = $normalCount + $bongkarCount + $gagalCount;
        
        return [
            'normal' => $normalCount,
            'bongkar' => $bongkarCount,
            'gagal' => $gagalCount,
            'total' => $total,
            'normal_percentage' => $total > 0 ? round(($normalCount / $total) * 100, 1) : 0,
            'bongkar_percentage' => $total > 0 ? round(($bongkarCount / $total) * 100, 1) : 0,
            'gagal_percentage' => $total > 0 ? round(($gagalCount / $total) * 100, 1) : 0,
        ];
    }
    
    private function getWeeklyStats($weekStart, $weekEnd): array
    {
        $baseQuery = Pengiriman::query();
        $countQuery = clone $baseQuery;
        
        $countQuery->where(function($q) use ($weekStart, $weekEnd) {
            $q->where(function($subq) use ($weekStart, $weekEnd) {
                $subq->whereIn('pengiriman.status', [self::STATUS_MENUNGGU_FISIK, self::STATUS_MENUNGGU_VERIFIKASI, self::STATUS_BERHASIL])
                     ->whereBetween('pengiriman.tanggal_kirim', [$weekStart, $weekEnd]);
            })->orWhere(function($subq) use ($weekStart, $weekEnd) {
                $subq->where('pengiriman.status', self::STATUS_GAGAL)
                     ->whereNotNull('pengiriman.tanggal_kirim')
                     ->whereBetween('pengiriman.tanggal_kirim', [$weekStart, $weekEnd]);
            })->orWhere(function($subq) use ($weekStart, $weekEnd) {
                $subq->where('pengiriman.status', self::STATUS_GAGAL)
                     ->whereNull('pengiriman.tanggal_kirim')
                     ->whereBetween('pengiriman.updated_at', [$weekStart, $weekEnd]);
            });
        });
        $countData = $countQuery->selectRaw('COUNT(DISTINCT pengiriman.id) as total_pengiriman')->first();
        
        $tonaseQuery = clone $baseQuery;
        $tonaseData = $tonaseQuery->whereIn('pengiriman.status', [self::STATUS_MENUNGGU_FISIK, self::STATUS_MENUNGGU_VERIFIKASI, self::STATUS_BERHASIL])
            ->whereBetween('pengiriman.tanggal_kirim', [$weekStart, $weekEnd])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN pengiriman.status = "'.self::STATUS_BERHASIL.'" AND invoice_penagihan.qty_after_refraksi IS NOT NULL THEN invoice_penagihan.qty_after_refraksi ELSE pengiriman.total_qty_kirim END), 0) as total_tonase')
            ->first();
            
        return [
            'total_pengiriman' => $countData->total_pengiriman ?? 0,
            'total_tonase' => $tonaseData->total_tonase ?? 0,
            'week_start' => $weekStart->format('d M'),
            'week_end' => $weekEnd->format('d M Y')
        ];
    }
    
    private function getYearlyStats($year): array
    {
        $countQuery = Pengiriman::query();
        $countQuery->where(function($q) use ($year) {
            $q->where(function($subq) use ($year) {
                $subq->whereIn('pengiriman.status', [self::STATUS_MENUNGGU_FISIK, self::STATUS_MENUNGGU_VERIFIKASI, self::STATUS_BERHASIL])
                     ->whereYear('pengiriman.tanggal_kirim', $year);
            })->orWhere(function($subq) use ($year) {
                $subq->where('pengiriman.status', self::STATUS_GAGAL)
                     ->whereNotNull('pengiriman.tanggal_kirim')
                     ->whereYear('pengiriman.tanggal_kirim', $year);
            })->orWhere(function($subq) use ($year) {
                $subq->where('pengiriman.status', self::STATUS_GAGAL)
                     ->whereNull('pengiriman.tanggal_kirim')
                     ->whereYear('pengiriman.updated_at', $year);
            });
        });
        $countData = $countQuery->selectRaw('COUNT(DISTINCT pengiriman.id) as total_pengiriman')->first();
        
        $tonaseData = Pengiriman::whereYear('pengiriman.tanggal_kirim', $year)
            ->whereIn('pengiriman.status', [self::STATUS_MENUNGGU_FISIK, self::STATUS_MENUNGGU_VERIFIKASI, self::STATUS_BERHASIL])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN pengiriman.status = "'.self::STATUS_BERHASIL.'" AND invoice_penagihan.qty_after_refraksi IS NOT NULL THEN invoice_penagihan.qty_after_refraksi ELSE pengiriman.total_qty_kirim END), 0) as total_tonase')
            ->first();
            
        return [
            'total_pengiriman' => $countData->total_pengiriman ?? 0,
            'total_tonase' => $tonaseData->total_tonase ?? 0,
            'year' => $year
        ];
    }
    
    private function getTotalStats(): array
    {
        $countData = Pengiriman::whereIn('pengiriman.status', [self::STATUS_MENUNGGU_FISIK, self::STATUS_MENUNGGU_VERIFIKASI, self::STATUS_BERHASIL, self::STATUS_GAGAL])
            ->selectRaw('COUNT(DISTINCT pengiriman.id) as total_pengiriman')
            ->first();
        
        $tonaseData = Pengiriman::whereIn('pengiriman.status', [self::STATUS_MENUNGGU_FISIK, self::STATUS_MENUNGGU_VERIFIKASI, self::STATUS_BERHASIL])
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN pengiriman.status = "'.self::STATUS_BERHASIL.'" AND invoice_penagihan.qty_after_refraksi IS NOT NULL THEN invoice_penagihan.qty_after_refraksi ELSE pengiriman.total_qty_kirim END), 0) as total_tonase')
            ->first();
            
        return [
            'total_pengiriman' => $countData->total_pengiriman ?? 0,
            'total_tonase' => $tonaseData->total_tonase ?? 0
        ];
    }
    
    private function getYearlyChartData($year): array
    {
        $monthlyData = Pengiriman::where(function($query) use ($year) {
                $query->where(function($q) use ($year) {
                    $q->whereIn('pengiriman.status', [self::STATUS_MENUNGGU_FISIK, self::STATUS_MENUNGGU_VERIFIKASI, self::STATUS_BERHASIL])
                      ->whereYear('pengiriman.tanggal_kirim', $year);
                })->orWhere(function($q) use ($year) {
                    $q->where('pengiriman.status', self::STATUS_GAGAL)
                      ->whereNotNull('pengiriman.tanggal_kirim')
                      ->whereYear('pengiriman.tanggal_kirim', $year);
                })->orWhere(function($q) use ($year) {
                    $q->where('pengiriman.status', self::STATUS_GAGAL)
                      ->whereNull('pengiriman.tanggal_kirim')
                      ->whereYear('pengiriman.updated_at', $year);
                });
            })
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->selectRaw("
                CASE 
                    WHEN pengiriman.status = '".self::STATUS_GAGAL."' AND pengiriman.tanggal_kirim IS NULL THEN MONTH(pengiriman.updated_at)
                    ELSE MONTH(pengiriman.tanggal_kirim)
                END as month,
                pengiriman.purchasing_id,
                COUNT(DISTINCT pengiriman.id) as total_pengiriman,
                COALESCE(SUM(CASE WHEN pengiriman.status = '".self::STATUS_BERHASIL."' AND invoice_penagihan.qty_after_refraksi IS NOT NULL THEN invoice_penagihan.qty_after_refraksi ELSE pengiriman.total_qty_kirim END), 0) as total_tonase
            ")
            ->groupBy(['month', 'pengiriman.purchasing_id'])
            ->with('purchasing')
            ->get();
            
        $purchasingUsers = ReferenceDataService::getPurchasingUsers();
        $chartData = [];
        
        foreach ($purchasingUsers as $user) {
            $userName = $user->nama ?? $user->name ?? 'Unknown User';
            $chartData[$userName] = [
                'pengiriman' => array_fill(0, 12, 0),
                'tonase' => array_fill(0, 12, 0)
            ];
        }
        
        foreach ($monthlyData as $data) {
            if ($data->purchasing) {
                $month = $data->month - 1; 
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
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date'
        ]);

        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
            $status = $request->get('status');
            $purchasing = $request->get('purchasing');
            $search = $request->get('search');
            $pabrik = $request->get('pabrik');
            $supplier = $request->get('supplier');

            $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing'])->get();
            
            $pabrikName = $pabrik ? (\App\Models\Klien::find($pabrik)->nama ?? null) : null;
            $supplierName = $supplier ? (\App\Models\Supplier::find($supplier)->nama ?? null) : null;

            $pengirimanCount = Pengiriman::whereBetween('tanggal_kirim', [$startDate, $endDate])->count();
            if ($pengirimanCount === 0) {
                return redirect()->back()->with('error', 'Tidak ada data pengiriman pada periode ' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)));
            }

            $filename = 'Laporan_Pengiriman_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(
                new PengirimanExport($startDate, $endDate, $status, $purchasing, $search, $purchasingUsers, $pabrik, $pabrikName, $supplier, $supplierName),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error saat export: ' . $e->getMessage());
        }
    }

    /**
     * Memusatkan query dasar untuk kebutuhan laporan detail pie chart
     */
    private function getBasePieChartDetailQuery($filter, $startDate, $endDate)
    {
        $query = Pengiriman::leftJoin('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->leftJoin('forecasts', 'pengiriman.forecast_id', '=', 'forecasts.id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->leftJoin('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->select(
                'pengiriman.id', 'pengiriman.status', 'pengiriman.total_qty_kirim', 
                'pengiriman.tanggal_kirim', 'pengiriman.updated_at', 'orders.po_number',
                DB::raw('GROUP_CONCAT(DISTINCT suppliers.nama SEPARATOR ", ") as supplier_nama'),
                'forecasts.total_qty_forecast'
            )
            ->groupBy(
                'pengiriman.id', 'pengiriman.status', 'pengiriman.total_qty_kirim', 
                'pengiriman.tanggal_kirim', 'pengiriman.updated_at', 'orders.po_number', 
                'forecasts.total_qty_forecast'
            );
            
        if ($filter !== 'semua' && $startDate && $endDate) {
            $query = $this->applyDateFilter($query, $startDate, $endDate);
        }

        return $query;
    }

    /**
     * Mengatur formatting data per baris untuk report
     */
    private function formatPieChartDetailRow($pengiriman, bool $forPdf = false): ?array
    {
        if (!in_array($pengiriman->status, [self::STATUS_GAGAL, self::STATUS_BERHASIL, self::STATUS_MENUNGGU_FISIK, self::STATUS_MENUNGGU_VERIFIKASI])) {
            return null; // Skip pending/other status
        }

        $kategori = '';
        $statusLabel = '';

        if ($pengiriman->status === self::STATUS_GAGAL) {
            $kategori = 'gagal';
            $statusLabel = 'Ditolak';
        } else {
            $totalQtyForecast = (float) $pengiriman->total_qty_forecast;
            $totalQtyKirim = (float) $pengiriman->total_qty_kirim;
            
            if ($totalQtyForecast > 0) {
                $percentage = ($totalQtyKirim / $totalQtyForecast) * 100;
                if ($percentage > self::BONGKAR_THRESHOLD) {
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
        }

        $displayDate = null;
        if ($pengiriman->status === self::STATUS_GAGAL) {
            if ($pengiriman->tanggal_kirim) {
                $displayDate = $forPdf ? $pengiriman->tanggal_kirim->format('d/m/Y') : $pengiriman->tanggal_kirim->format('Y-m-d');
            } else {
                $dateObj = $pengiriman->updated_at ? Carbon::parse($pengiriman->updated_at) : null;
                $displayDate = $dateObj ? ($forPdf ? $dateObj->format('d/m/Y') : $dateObj->format('Y-m-d')) : ($forPdf ? '-' : null);
            }
        } else {
            if ($pengiriman->tanggal_kirim) {
                $displayDate = $forPdf ? $pengiriman->tanggal_kirim->format('d/m/Y') : $pengiriman->tanggal_kirim->format('Y-m-d');
            } else {
                 $displayDate = $forPdf ? '-' : null;
            }
        }

        return [
            'id' => $pengiriman->id,
            'po_number' => $pengiriman->po_number ?? '-',
            'supplier' => $pengiriman->supplier_nama ?? '-',
            'tanggal_kirim' => $displayDate,
            'qty_forecast' => $pengiriman->total_qty_forecast ? (float) $pengiriman->total_qty_forecast : 0,
            'qty_pengiriman' => (float) $pengiriman->total_qty_kirim,
            'status_label' => $statusLabel,
            'kategori' => $kategori,
            'status_pengiriman' => $pengiriman->status
        ];
    }
    
    public function getPieChartDetails(Request $request)
    {
        try {
            $filter = $request->get('filter', 'bulan_ini');
            $dates = $this->resolvePieChartDates($request, $filter);
            
            $pengirimanData = $this->getBasePieChartDetailQuery($filter, $dates['start'], $dates['end'])->get();
            $details = [];
            
            foreach ($pengirimanData as $pengiriman) {
                $formatted = $this->formatPieChartDetailRow($pengiriman, false);
                if ($formatted) $details[] = $formatted;
            }
            
            return response()->json([
                'success' => true,
                'data' => $details
            ]);
            
        } catch (\Exception $e) {
            Log::error('Pie Chart Details Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    
    public function exportPieChartPDF(Request $request)
    {
        try {
            $filter = $request->get('filter', 'bulan_ini');
            $dates = $this->resolvePieChartDates($request, $filter);
            $startDate = $dates['start'];
            $endDate = $dates['end'];
            
            $pengirimanData = $this->getBasePieChartDetailQuery($filter, $startDate, $endDate)->get();
            
            $details = [];
            $summary = ['normal' => 0, 'bongkar' => 0, 'gagal' => 0, 'total' => 0];
            
            foreach ($pengirimanData as $pengiriman) {
                $formatted = $this->formatPieChartDetailRow($pengiriman, true);
                if ($formatted) {
                    $details[] = $formatted;
                    $summary[$formatted['kategori']]++;
                }
            }
            $summary['total'] = count($details);
            
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
            
            $pdf = Pdf::loadView('pages.laporan.pengiriman-pie-chart-pdf', [
                'details' => $details,
                'summary' => $summary,
                'reportTitle' => $reportTitle,
                'reportPeriod' => $reportPeriod,
                'generatedAt' => now()->translatedFormat('d F Y H:i')
            ])->setPaper('a4', 'landscape');
            
            return $pdf->download('Detail_Pengiriman_' . now()->format('Y-m-d_H-i-s') . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('Export Pie Chart PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }
}