<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use App\Models\Klien;
use App\Models\Order;
use App\Models\Pengiriman;
use App\Models\TargetOmset;
use Illuminate\Http\Request;
use App\Models\BahanBakuKlien;
use App\Models\InvoicePenagihan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OmsetController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Omset';
        $activeTab = 'omset';
        
        // Get selected year for target analysis (default: current year)
        $selectedYearTarget = $request->get('tahun_target', Carbon::now()->year);
        
        // Get all available years from target_omset table (tahun yang sudah ada target)
        $availableYearsTarget = TargetOmset::orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
        
        // Jika belum ada data target sama sekali, tampilkan tahun ini sebagai default
        if (empty($availableYearsTarget)) {
            $availableYearsTarget = [Carbon::now()->year];
        }
        
        // Jika tahun yang dipilih belum ada targetnya, set ke tahun terbaru yang ada target
        if (!in_array($selectedYearTarget, $availableYearsTarget)) {
            $selectedYearTarget = $availableYearsTarget[0] ?? Carbon::now()->year;
        }
        
        // Calculate Total Omset (all time) - using amount_after_refraksi from invoice_penagihan
        $totalOmset = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // ========== SUMMARY CARDS (ALWAYS CURRENT/NOW) ==========
        // Calculate Omset Tahun Ini (NOW - untuk summary card atas)
        $omsetTahunIniSummary = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Calculate Omset Bulan Ini (NOW - untuk summary card atas)
        $omsetBulanIniSummary = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month)
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // ========== TARGET ANALYSIS (SELECTED YEAR) ==========
        // Calculate Omset untuk tahun yang dipilih - untuk Target Analysis Card
        $omsetTahunIni = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Calculate Omset Bulan Ini untuk selected year - untuk Target Analysis Card
        $omsetBulanIni = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
            ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month)
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Get Target Omset for selected year
        $targetOmset = TargetOmset::getTargetForYear($selectedYearTarget);
        
        $targetTahunan = $targetOmset->target_tahunan ?? 0;
        $targetBulanan = $targetOmset->target_bulanan ?? 0;
        $targetMingguan = $targetOmset->target_mingguan ?? 0;
        
        // Calculate Omset Minggu Ini (current week) - GUNAKAN tanggal_kirim dari pengiriman
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $omsetMingguIni = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->whereBetween('pengiriman.tanggal_kirim', [$startOfWeek, $endOfWeek])
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Calculate Progress Percentages
        $progressMinggu = $targetMingguan > 0 ? ($omsetMingguIni / $targetMingguan) * 100 : 0;
        $progressBulan = $targetBulanan > 0 ? ($omsetBulanIni / $targetBulanan) * 100 : 0;
        $progressTahun = $targetTahunan > 0 ? ($omsetTahunIni / $targetTahunan) * 100 : 0;
        
        // Calculate Monthly Breakdown dengan detail mingguan
        $rekapBulanan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            // Omset total bulan - GUNAKAN tanggal_kirim dari pengiriman
            $omsetBulan = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->where('pengiriman.status', 'berhasil')
                ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
                ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
            
            $progressBulanIni = $targetBulanan > 0 ? ($omsetBulan / $targetBulanan) * 100 : 0;
            $selisih = $omsetBulan - $targetBulanan;
            
            // Calculate weekly breakdown for this month - GUNAKAN KALENDER SEBENARNYA
            $mingguanDetail = [];
            $startDate = Carbon::create($selectedYearTarget, $bulan, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth();
            $totalDaysInMonth = $startDate->daysInMonth;
            
            // Bagi bulan berdasarkan jumlah hari sebenarnya, bukan asumsi 4 minggu
            // Setiap minggu adalah 7 hari, jadi bisa ada 4-5 minggu per bulan
            $minggu = 1;
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $endDate) {
                $weekStart = $currentDate->copy();
                $weekEnd = $currentDate->copy()->addDays(6)->min($endDate);
                
                $omsetMinggu = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                    ->where('pengiriman.status', 'berhasil')
                    ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                    ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
                
                $progressMingguIni = $targetMingguan > 0 ? ($omsetMinggu / $targetMingguan) * 100 : 0;
                
                $mingguanDetail[$minggu] = [
                    'omset' => $omsetMinggu,
                    'progress' => $progressMingguIni,
                    'tanggal' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M')
                ];
                
                $minggu++;
                $currentDate->addDays(7);
            }
            
            $rekapBulanan[$bulan] = [
                'realisasi' => $omsetBulan,
                'progress' => $progressBulanIni,
                'selisih' => $selisih,
                'mingguan' => $mingguanDetail
            ];
        }
        
        // Get filter periode (default: all)
        $periode = $request->get('periode_marketing', 'all');
        $periodeProcurement = $request->get('periode_procurement', 'all');
        $periodeKlien = $request->get('periode_klien', 'all');
        $periodeSupplier = $request->get('periode_supplier', 'all');
        
        // Handle AJAX request for Target Analysis (load without refresh)
        if ($request->ajax() && $request->get('ajax') === 'target_analysis') {
            return response()->json([
                'selectedYearTarget' => $selectedYearTarget,
                'targetTahunan' => $targetTahunan,
                'targetBulanan' => $targetBulanan,
                'targetMingguan' => $targetMingguan,
                'omsetTahunIni' => $omsetTahunIni,
                'omsetBulanIni' => $omsetBulanIni,
                'omsetMingguIni' => $omsetMingguIni,
                'progressMinggu' => $progressMinggu,
                'progressBulan' => $progressBulan,
                'progressTahun' => $progressTahun,
                'rekapBulanan' => $rekapBulanan,
            ]);
        }
        
        // Handle AJAX request for Get Target by Year
        if ($request->ajax() && $request->get('ajax') === 'get_target') {
            $tahun = $request->get('tahun', Carbon::now()->year);
            $targetOmsetData = TargetOmset::getTargetForYear($tahun);
            
            return response()->json([
                'target_tahunan' => $targetOmsetData->target_tahunan ?? 0,
                'target_bulanan' => $targetOmsetData->target_bulanan ?? 0,
                'target_mingguan' => $targetOmsetData->target_mingguan ?? 0,
            ]);
        }
        
        // Handle AJAX request for Top Klien
        if ($request->ajax() && $request->get('ajax') === 'top_klien') {
            // Using amount_after_refraksi from invoice_penagihan
            $topKlienQuery = DB::table('invoice_penagihan')
                ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
                ->select('kliens.id as klien_id', 'kliens.nama', 'kliens.cabang',
                    DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
                ->where('pengiriman.status', 'berhasil')
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('kliens.deleted_at')
                ->groupBy('kliens.id', 'kliens.nama', 'kliens.cabang');
            
            // Apply filter for klien
            if ($periodeKlien === 'tahun_ini') {
                $topKlienQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
            } elseif ($periodeKlien === 'bulan_ini') {
                $topKlienQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                    ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
            } elseif ($periodeKlien === 'custom' && $request->filled(['start_date_klien', 'end_date_klien'])) {
                $topKlienQuery->whereBetween('pengiriman.tanggal_kirim', [
                    $request->start_date_klien,
                    $request->end_date_klien
                ]);
            }
            
            $topKlien = $topKlienQuery->orderBy('total', 'desc')
                ->get();
            
            $data = $topKlien->map(function($item) {
                return [
                    'nama' => $item->nama ?? 'Unknown',
                    'cabang' => $item->cabang,
                    'total' => floatval($item->total ?? 0)
                ];
            })->filter(function($item) {
                return $item['total'] > 0;
            })->values();
            
            return response()->json($data);
        }
        
        // Handle AJAX request for Top Supplier
        if ($request->ajax() && $request->get('ajax') === 'top_supplier') {
            // Using amount_after_refraksi from approval_pembayaran (actual payment to supplier)
            $topSupplierQuery = DB::table('approval_pembayaran')
                ->join('pengiriman', 'approval_pembayaran.pengiriman_id', '=', 'pengiriman.id')
                ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->select('suppliers.id as supplier_id', 'suppliers.nama', 'suppliers.alamat', 
                    DB::raw('SUM(approval_pembayaran.amount_after_refraksi) as total'))
                ->where('pengiriman.status', 'berhasil')
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('pengiriman_details.deleted_at')
                ->whereNull('bahan_baku_supplier.deleted_at')
                ->whereNull('suppliers.deleted_at')
                ->groupBy('suppliers.id', 'suppliers.nama', 'suppliers.alamat');
            
            // Apply filter for supplier
            if ($periodeSupplier === 'tahun_ini') {
                $topSupplierQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
            } elseif ($periodeSupplier === 'bulan_ini') {
                $topSupplierQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                    ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
            } elseif ($periodeSupplier === 'custom' && $request->filled(['start_date_supplier', 'end_date_supplier'])) {
                $topSupplierQuery->whereBetween('pengiriman.tanggal_kirim', [
                    $request->start_date_supplier,
                    $request->end_date_supplier
                ]);
            }
            
            $topSupplier = $topSupplierQuery->orderBy('total', 'desc')
                ->get();
            
            $data = $topSupplier->map(function($item) {
                return [
                    'nama' => $item->nama,
                    'cabang' => $item->alamat, // Using alamat as cabang
                    'total' => floatval($item->total ?? 0)
                ];
            })->filter(function($item) {
                return $item['total'] > 0;
            })->values();
            
            return response()->json($data);
        }
        
        // Handle AJAX request for Proyek Per Bulan
        if ($request->ajax() && $request->get('ajax') === 'proyek_per_bulan') {
            $tahun = $request->get('tahun', Carbon::now()->year);
            
            $proyekPerBulan = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                // Count unique orders based on tanggal_order
                $count = Order::whereYear('tanggal_order', $tahun)
                    ->whereMonth('tanggal_order', $bulan)
                    ->count();
                $proyekPerBulan[] = $count;
            }
            
            return response()->json([
                'data' => $proyekPerBulan,
                'tahun' => $tahun
            ]);
        }
        
        // Handle AJAX request for Nilai Order Per Bulan
        if ($request->ajax() && $request->get('ajax') === 'nilai_order_per_bulan') {
            $tahun = $request->get('tahun', Carbon::now()->year);
            
            $nilaiOrderPerBulan = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                // Sum nilai order based on tanggal_order (use total_amount from orders)
                $total = Order::whereYear('tanggal_order', $tahun)
                    ->whereMonth('tanggal_order', $bulan)
                    ->sum('total_amount');
                $nilaiOrderPerBulan[] = floatval($total ?? 0);
            }
            
            return response()->json([
                'data' => $nilaiOrderPerBulan,
                'tahun' => $tahun
            ]);
        }
        
        // Handle AJAX request for Marketing
        if ($request->ajax() && $request->get('ajax') === 'marketing') {
            // Query based on order_winner table with amount_after_refraksi
            $omsetMarketingQuery = DB::table('invoice_penagihan')
                ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
                ->join('users', 'order_winners.user_id', '=', 'users.id')
                ->select('order_winners.user_id', 'users.nama', 
                    DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
                ->where('pengiriman.status', 'berhasil')
                ->groupBy('order_winners.user_id', 'users.nama');
            
            // Apply filter for marketing
            if ($periode === 'tahun_ini') {
                $omsetMarketingQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
            } elseif ($periode === 'bulan_ini') {
                $omsetMarketingQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                    ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
            } elseif ($periode === 'custom' && $request->filled(['start_date_marketing', 'end_date_marketing'])) {
                $omsetMarketingQuery->whereBetween('pengiriman.tanggal_kirim', [
                    $request->start_date_marketing,
                    $request->end_date_marketing
                ]);
            }
            
            $omsetMarketing = $omsetMarketingQuery->get();
            
            $data = $omsetMarketing->map(function($item) {
                return [
                    'nama' => $item->nama ?? 'Unknown',
                    'total' => floatval($item->total ?? 0)
                ];
            })->filter(function($item) {
                return $item['total'] > 0;
            })->values();
            
            return response()->json($data);
        }
        
        // Handle AJAX request for Procurement
        if ($request->ajax() && $request->get('ajax') === 'procurement') {
            $omsetProcurementQuery = InvoicePenagihan::select('pengiriman.purchasing_id', DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
                ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->where('pengiriman.status', 'berhasil')
                ->groupBy('pengiriman.purchasing_id');
            
            // Apply filter for procurement
            if ($periodeProcurement === 'tahun_ini') {
                $omsetProcurementQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
            } elseif ($periodeProcurement === 'bulan_ini') {
                $omsetProcurementQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                    ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
            } elseif ($periodeProcurement === 'custom' && $request->filled(['start_date_procurement', 'end_date_procurement'])) {
                $omsetProcurementQuery->whereBetween('pengiriman.tanggal_kirim', [
                    $request->start_date_procurement,
                    $request->end_date_procurement
                ]);
            }
            
            $omsetProcurementData = $omsetProcurementQuery->get();
            
            // Get purchasing names
            $data = $omsetProcurementData->map(function($item) {
                $purchasing = \App\Models\User::find($item->purchasing_id);
                return [
                    'nama' => $purchasing ? $purchasing->nama : 'Unknown',
                    'total' => floatval($item->total ?? 0)
                ];
            })->filter(function($item) {
                return $item['total'] > 0;
            })->values();
            
            return response()->json($data);
        }
        
        // Omset Marketing by PIC (from Order Winners) - using amount_after_refraksi
        $omsetMarketingQuery = DB::table('invoice_penagihan')
            ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->select('order_winners.user_id', 'users.nama', 
                DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
            ->where('pengiriman.status', 'berhasil')
            ->groupBy('order_winners.user_id', 'users.nama');
        
        // Apply filter for marketing
        if ($periode === 'tahun_ini') {
            $omsetMarketingQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $omsetMarketingQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periode === 'custom' && $request->filled(['start_date_marketing', 'end_date_marketing'])) {
            $omsetMarketingQuery->whereBetween('pengiriman.tanggal_kirim', [
                $request->start_date_marketing,
                $request->end_date_marketing
            ]);
        }
        
        $omsetMarketing = $omsetMarketingQuery->get();
        
        // Transform data to collection for blade
        $omsetMarketing = $omsetMarketing->map(function($item) {
            return (object)[
                'user_id' => $item->user_id,
                'creator' => (object)['nama' => $item->nama],
                'total' => floatval($item->total ?? 0)
            ];
        })->filter(function($item) {
            return $item->total > 0;
        })->values();
        
        // Omset Procurement by PIC (from InvoicePenagihan with pengiriman status 'berhasil')
        $omsetProcurementQuery = InvoicePenagihan::select('pengiriman.purchasing_id', DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
            ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->groupBy('pengiriman.purchasing_id');
        
        // Apply filter for procurement
        if ($periodeProcurement === 'tahun_ini') {
            $omsetProcurementQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periodeProcurement === 'bulan_ini') {
            $omsetProcurementQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periodeProcurement === 'custom' && $request->filled(['start_date_procurement', 'end_date_procurement'])) {
            $omsetProcurementQuery->whereBetween('pengiriman.tanggal_kirim', [
                $request->start_date_procurement,
                $request->end_date_procurement
            ]);
        }
        
        $omsetProcurementData = $omsetProcurementQuery->get();
        
        // Get purchasing names
        $omsetProcurement = $omsetProcurementData->map(function($item) {
            $purchasing = \App\Models\User::find($item->purchasing_id);
            return [
                'purchasing_id' => $item->purchasing_id,
                'nama' => $purchasing ? $purchasing->nama : 'Unknown',
                'total' => floatval($item->total ?? 0)
            ];
        })->filter(function($item) {
            return $item['total'] > 0;
        })->values();
        
        // Get available years from orders
        $availableYears = Order::selectRaw('YEAR(tanggal_order) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        // Default to current year if no data
        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        }
        
        // Get selected year for proyek per bulan (default: current year)
        $selectedYear = $request->get('tahun_proyek', Carbon::now()->year);
        $selectedYearNilai = $request->get('tahun_nilai', Carbon::now()->year);
        
        // Get proyek per bulan data - based on tanggal_order
        $proyekPerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $count = Order::whereYear('tanggal_order', $selectedYear)
                ->whereMonth('tanggal_order', $bulan)
                ->count();
            $proyekPerBulan[] = $count;
        }
        
        // Get nilai order per bulan data - based on tanggal_order
        $nilaiOrderPerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total = Order::whereYear('tanggal_order', $selectedYearNilai)
                ->whereMonth('tanggal_order', $bulan)
                ->sum('total_amount');
            $nilaiOrderPerBulan[] = floatval($total ?? 0);
        }
        
        // Get Top Klien data - using amount_after_refraksi
        $topKlienQuery = DB::table('invoice_penagihan')
            ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->select('kliens.id as klien_id', 'kliens.nama', 'kliens.cabang',
                DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
            ->where('pengiriman.status', 'berhasil')
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('kliens.deleted_at')
            ->groupBy('kliens.id', 'kliens.nama', 'kliens.cabang');
        
        // Apply filter for klien
        if ($periodeKlien === 'tahun_ini') {
            $topKlienQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periodeKlien === 'bulan_ini') {
            $topKlienQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periodeKlien === 'custom' && $request->filled(['start_date_klien', 'end_date_klien'])) {
            $topKlienQuery->whereBetween('pengiriman.tanggal_kirim', [
                $request->start_date_klien,
                $request->end_date_klien
            ]);
        }
        
        $topKlien = $topKlienQuery->orderBy('total', 'desc')
            ->get();
        
        // Get Top Supplier data
        // Using amount_after_refraksi from approval_pembayaran (actual payment to supplier)
        $topSupplierQuery = DB::table('approval_pembayaran')
            ->join('pengiriman', 'approval_pembayaran.pengiriman_id', '=', 'pengiriman.id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->select('suppliers.id as supplier_id', 'suppliers.nama', 'suppliers.alamat', 
                DB::raw('SUM(approval_pembayaran.amount_after_refraksi) as total'))
            ->where('pengiriman.status', 'berhasil')
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('pengiriman_details.deleted_at')
            ->whereNull('bahan_baku_supplier.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->groupBy('suppliers.id', 'suppliers.nama', 'suppliers.alamat');
        
        // Apply filter for supplier
        if ($periodeSupplier === 'tahun_ini') {
            $topSupplierQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periodeSupplier === 'bulan_ini') {
            $topSupplierQuery->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periodeSupplier === 'custom' && $request->filled(['start_date_supplier', 'end_date_supplier'])) {
            $topSupplierQuery->whereBetween('pengiriman.tanggal_kirim', [
                $request->start_date_supplier,
                $request->end_date_supplier
            ]);
        }
        
        $topSupplier = $topSupplierQuery->orderBy('total', 'desc')
            ->get();
        
        return view('pages.laporan.omset', compact(
            'title', 
            'activeTab',
            'totalOmset',
            'omsetTahunIniSummary',    // For summary cards (NOW)
            'omsetBulanIniSummary',    // For summary cards (NOW)
            'omsetTahunIni',           // For target analysis (selected year)
            'omsetBulanIni',           // For target analysis (selected year)
            'omsetMingguIni',
            'targetTahunan',
            'targetBulanan',
            'targetMingguan',
            'progressMinggu',
            'progressBulan',
            'progressTahun',
            'rekapBulanan',
            'selectedYearTarget',
            'availableYearsTarget',
            'omsetMarketing',
            'omsetProcurement',
            'periode',
            'periodeProcurement',
            'periodeKlien',
            'periodeSupplier',
            'availableYears',
            'selectedYear',
            'selectedYearNilai',
            'proyekPerBulan',
            'nilaiOrderPerBulan',
            'topKlien',
            'topSupplier'
        ));
    }
    
    public function setTarget(Request $request)
    {
        try {
            // Check if user is direktur
            if (!Auth::check() || Auth::user()->role !== 'direktur') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Direktur yang dapat mengubah target omset.'
                ], 403);
            }
            
            $request->validate([
                'target_tahunan' => 'required|numeric|min:0',
                'tahun' => 'required|integer|min:2020|max:2100'
            ]);
            
            $targetTahunan = $request->target_tahunan;
            $tahun = $request->tahun;
            $createdBy = Auth::user()->nama ?? 'System';
            
            TargetOmset::setTarget($tahun, $targetTahunan, $createdBy);
            
            return response()->json([
                'success' => true,
                'message' => 'Target omset berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getTargetByYear(Request $request)
    {
        try {
            $tahun = $request->get('tahun', Carbon::now()->year);
            $target = TargetOmset::getTargetForYear($tahun);
            
            return response()->json([
                'success' => true,
                'data' => $target
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getAvailableYears()
    {
        try {
            // Get tahun-tahun yang sudah ada target
            $yearsWithTarget = TargetOmset::orderBy('tahun', 'desc')
                ->pluck('tahun')
                ->toArray();
            
            return response()->json([
                'success' => true,
                'years_with_target' => $yearsWithTarget,
                'current_year' => Carbon::now()->year
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
}
