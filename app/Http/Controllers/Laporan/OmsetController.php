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
        
        // Calculate Total Omset (all time)
        $totalOmset = Order::sum('total_amount') ?? 0;
        
        // Calculate Omset Tahun Ini (current year)
        $omsetTahunIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->sum('total_amount') ?? 0;
        
        // Calculate Omset Bulan Ini (current month)
        $omsetBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->sum('total_amount') ?? 0;
        
        // Get filter periode (default: all)
        $periode = $request->get('periode_marketing', 'all');
        $periodeProcurement = $request->get('periode_procurement', 'all');
        $periodeKlien = $request->get('periode_klien', 'all');
        $periodeSupplier = $request->get('periode_supplier', 'all');
        
        // Handle AJAX request for Top Klien
        if ($request->ajax() && $request->get('ajax') === 'top_klien') {
            $topKlienQuery = Order::select('klien_id', DB::raw('SUM(total_amount) as total'))
                ->with('klien:id,nama,cabang')
                ->groupBy('klien_id');
            
            // Apply filter for klien
            if ($periodeKlien === 'tahun_ini') {
                $topKlienQuery->whereYear('tanggal_order', Carbon::now()->year);
            } elseif ($periodeKlien === 'bulan_ini') {
                $topKlienQuery->whereYear('tanggal_order', Carbon::now()->year)
                    ->whereMonth('tanggal_order', Carbon::now()->month);
            } elseif ($periodeKlien === 'custom' && $request->filled(['start_date_klien', 'end_date_klien'])) {
                $topKlienQuery->whereBetween('tanggal_order', [
                    $request->start_date_klien,
                    $request->end_date_klien
                ]);
            }
            
            $topKlien = $topKlienQuery->orderBy('total', 'desc')
                ->limit(10)
                ->get();
            
            $data = $topKlien->map(function($item) {
                return [
                    'nama' => $item->klien ? $item->klien->nama : 'Unknown',
                    'cabang' => $item->klien ? $item->klien->cabang : null,
                    'total' => floatval($item->total ?? 0)
                ];
            })->filter(function($item) {
                return $item['total'] > 0;
            })->values();
            
            return response()->json($data);
        }
        
        // Handle AJAX request for Top Supplier
        if ($request->ajax() && $request->get('ajax') === 'top_supplier') {
            // Go through: invoice_penagihan -> pengiriman -> pengiriman_details -> bahan_baku_supplier -> supplier
            // Using amount_after_refraksi for consistency with Procurement pie chart
            $topSupplierQuery = DB::table('invoice_penagihan')
                ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->select('suppliers.id as supplier_id', 'suppliers.nama', 'suppliers.alamat', 
                    DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
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
                ->limit(10)
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
            $omsetMarketingQuery = Order::select('created_by', DB::raw('SUM(total_amount) as total'))
                ->with('creator:id,nama')
                ->groupBy('created_by');
            
            // Apply filter for marketing
            if ($periode === 'tahun_ini') {
                $omsetMarketingQuery->whereYear('tanggal_order', Carbon::now()->year);
            } elseif ($periode === 'bulan_ini') {
                $omsetMarketingQuery->whereYear('tanggal_order', Carbon::now()->year)
                    ->whereMonth('tanggal_order', Carbon::now()->month);
            } elseif ($periode === 'custom' && $request->filled(['start_date_marketing', 'end_date_marketing'])) {
                $omsetMarketingQuery->whereBetween('tanggal_order', [
                    $request->start_date_marketing,
                    $request->end_date_marketing
                ]);
            }
            
            $omsetMarketing = $omsetMarketingQuery->get();
            
            $data = $omsetMarketing->map(function($item) {
                return [
                    'nama' => $item->creator ? $item->creator->nama : 'Unknown',
                    'total' => $item->total
                ];
            });
            
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
        
        // Omset Marketing by PIC (from Order)
        $omsetMarketingQuery = Order::select('created_by', DB::raw('SUM(total_amount) as total'))
            ->with('creator:id,nama')
            ->groupBy('created_by');
        
        // Apply filter for marketing
        if ($periode === 'tahun_ini') {
            $omsetMarketingQuery->whereYear('tanggal_order', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $omsetMarketingQuery->whereYear('tanggal_order', Carbon::now()->year)
                ->whereMonth('tanggal_order', Carbon::now()->month);
        } elseif ($periode === 'custom' && $request->filled(['start_date_marketing', 'end_date_marketing'])) {
            $omsetMarketingQuery->whereBetween('tanggal_order', [
                $request->start_date_marketing,
                $request->end_date_marketing
            ]);
        }
        
        $omsetMarketing = $omsetMarketingQuery->get();
        
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
        
        // Get proyek per bulan data
        $proyekPerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $count = Order::whereYear('tanggal_order', $selectedYear)
                ->whereMonth('tanggal_order', $bulan)
                ->count();
            $proyekPerBulan[] = $count;
        }
        
        // Get nilai order per bulan data
        $nilaiOrderPerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total = Order::whereYear('tanggal_order', $selectedYearNilai)
                ->whereMonth('tanggal_order', $bulan)
                ->sum('total_amount');
            $nilaiOrderPerBulan[] = floatval($total ?? 0);
        }
        
        // Get Top Klien data
        $topKlienQuery = Order::select('klien_id', DB::raw('SUM(total_amount) as total'))
            ->with('klien:id,nama,cabang')
            ->groupBy('klien_id');
        
        // Apply filter for klien
        if ($periodeKlien === 'tahun_ini') {
            $topKlienQuery->whereYear('tanggal_order', Carbon::now()->year);
        } elseif ($periodeKlien === 'bulan_ini') {
            $topKlienQuery->whereYear('tanggal_order', Carbon::now()->year)
                ->whereMonth('tanggal_order', Carbon::now()->month);
        } elseif ($periodeKlien === 'custom' && $request->filled(['start_date_klien', 'end_date_klien'])) {
            $topKlienQuery->whereBetween('tanggal_order', [
                $request->start_date_klien,
                $request->end_date_klien
            ]);
        }
        
        $topKlien = $topKlienQuery->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        // Get Top Supplier data
        // Go through: invoice_penagihan -> pengiriman -> pengiriman_details -> bahan_baku_supplier -> supplier
        // Using amount_after_refraksi for consistency with Procurement pie chart
        $topSupplierQuery = DB::table('invoice_penagihan')
            ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->select('suppliers.id as supplier_id', 'suppliers.nama', 'suppliers.alamat', 
                DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
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
            ->limit(10)
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
