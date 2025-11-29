<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\Order;
use App\Models\InvoicePenagihan;
use App\Models\Pengiriman;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OmsetController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Omset';
        $activeTab = 'omset';
        
        // Calculate Total Omset (all time) - using amount_after_refraksi from invoice_penagihan
        $totalOmset = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Calculate Omset Tahun Ini (current year)
        $omsetTahunIni = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->whereYear('pengiriman.updated_at', Carbon::now()->year)
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Calculate Omset Bulan Ini (current month)
        $omsetBulanIni = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->whereYear('pengiriman.updated_at', Carbon::now()->year)
            ->whereMonth('pengiriman.updated_at', Carbon::now()->month)
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Get filter periode (default: all)
        $periode = $request->get('periode_marketing', 'all');
        $periodeProcurement = $request->get('periode_procurement', 'all');
        $periodeKlien = $request->get('periode_klien', 'all');
        $periodeSupplier = $request->get('periode_supplier', 'all');
        
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
                $topKlienQuery->whereYear('pengiriman.updated_at', Carbon::now()->year);
            } elseif ($periodeKlien === 'bulan_ini') {
                $topKlienQuery->whereYear('pengiriman.updated_at', Carbon::now()->year)
                    ->whereMonth('pengiriman.updated_at', Carbon::now()->month);
            } elseif ($periodeKlien === 'custom' && $request->filled(['start_date_klien', 'end_date_klien'])) {
                $topKlienQuery->whereBetween('pengiriman.updated_at', [
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
                $topSupplierQuery->whereYear('pengiriman.updated_at', Carbon::now()->year);
            } elseif ($periodeSupplier === 'bulan_ini') {
                $topSupplierQuery->whereYear('pengiriman.updated_at', Carbon::now()->year)
                    ->whereMonth('pengiriman.updated_at', Carbon::now()->month);
            } elseif ($periodeSupplier === 'custom' && $request->filled(['start_date_supplier', 'end_date_supplier'])) {
                $topSupplierQuery->whereBetween('pengiriman.updated_at', [
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
                // Count unique orders from successful pengiriman
                $count = DB::table('invoice_penagihan')
                    ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                    ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                    ->where('pengiriman.status', 'berhasil')
                    ->whereYear('pengiriman.updated_at', $tahun)
                    ->whereMonth('pengiriman.updated_at', $bulan)
                    ->distinct('orders.id')
                    ->count('orders.id');
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
                $total = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                    ->where('pengiriman.status', 'berhasil')
                    ->whereYear('pengiriman.updated_at', $tahun)
                    ->whereMonth('pengiriman.updated_at', $bulan)
                    ->sum('invoice_penagihan.amount_after_refraksi');
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
                $omsetMarketingQuery->whereYear('pengiriman.updated_at', Carbon::now()->year);
            } elseif ($periode === 'bulan_ini') {
                $omsetMarketingQuery->whereYear('pengiriman.updated_at', Carbon::now()->year)
                    ->whereMonth('pengiriman.updated_at', Carbon::now()->month);
            } elseif ($periode === 'custom' && $request->filled(['start_date_marketing', 'end_date_marketing'])) {
                $omsetMarketingQuery->whereBetween('pengiriman.updated_at', [
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
                $omsetProcurementQuery->whereYear('pengiriman.updated_at', Carbon::now()->year);
            } elseif ($periodeProcurement === 'bulan_ini') {
                $omsetProcurementQuery->whereYear('pengiriman.updated_at', Carbon::now()->year)
                    ->whereMonth('pengiriman.updated_at', Carbon::now()->month);
            } elseif ($periodeProcurement === 'custom' && $request->filled(['start_date_procurement', 'end_date_procurement'])) {
                $omsetProcurementQuery->whereBetween('pengiriman.updated_at', [
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
            $omsetMarketingQuery->whereYear('pengiriman.updated_at', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $omsetMarketingQuery->whereYear('pengiriman.updated_at', Carbon::now()->year)
                ->whereMonth('pengiriman.updated_at', Carbon::now()->month);
        } elseif ($periode === 'custom' && $request->filled(['start_date_marketing', 'end_date_marketing'])) {
            $omsetMarketingQuery->whereBetween('pengiriman.updated_at', [
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
            $omsetProcurementQuery->whereYear('pengiriman.updated_at', Carbon::now()->year);
        } elseif ($periodeProcurement === 'bulan_ini') {
            $omsetProcurementQuery->whereYear('pengiriman.updated_at', Carbon::now()->year)
                ->whereMonth('pengiriman.updated_at', Carbon::now()->month);
        } elseif ($periodeProcurement === 'custom' && $request->filled(['start_date_procurement', 'end_date_procurement'])) {
            $omsetProcurementQuery->whereBetween('pengiriman.updated_at', [
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
        
        // Get proyek per bulan data - using successful pengiriman
        $proyekPerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $count = DB::table('invoice_penagihan')
                ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->where('pengiriman.status', 'berhasil')
                ->whereYear('pengiriman.updated_at', $selectedYear)
                ->whereMonth('pengiriman.updated_at', $bulan)
                ->distinct('orders.id')
                ->count('orders.id');
            $proyekPerBulan[] = $count;
        }
        
        // Get nilai order per bulan data - using amount_after_refraksi
        $nilaiOrderPerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->where('pengiriman.status', 'berhasil')
                ->whereYear('pengiriman.updated_at', $selectedYearNilai)
                ->whereMonth('pengiriman.updated_at', $bulan)
                ->sum('invoice_penagihan.amount_after_refraksi');
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
            $topKlienQuery->whereYear('pengiriman.updated_at', Carbon::now()->year);
        } elseif ($periodeKlien === 'bulan_ini') {
            $topKlienQuery->whereYear('pengiriman.updated_at', Carbon::now()->year)
                ->whereMonth('pengiriman.updated_at', Carbon::now()->month);
        } elseif ($periodeKlien === 'custom' && $request->filled(['start_date_klien', 'end_date_klien'])) {
            $topKlienQuery->whereBetween('pengiriman.updated_at', [
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
            $topSupplierQuery->whereYear('pengiriman.updated_at', Carbon::now()->year);
        } elseif ($periodeSupplier === 'bulan_ini') {
            $topSupplierQuery->whereYear('pengiriman.updated_at', Carbon::now()->year)
                ->whereMonth('pengiriman.updated_at', Carbon::now()->month);
        } elseif ($periodeSupplier === 'custom' && $request->filled(['start_date_supplier', 'end_date_supplier'])) {
            $topSupplierQuery->whereBetween('pengiriman.updated_at', [
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
            'omsetTahunIni',
            'omsetBulanIni',
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
    
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
}
