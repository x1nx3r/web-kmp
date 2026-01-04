<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\InvoicePenagihan;
use App\Models\Pengiriman;
use App\Models\TargetOmset;
use App\Models\OmsetManual;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ========== OMSET MINGGUAN (Paling Penting) ==========
        $currentYear = Carbon::now()->year;
        $targetOmset = TargetOmset::getTargetForYear($currentYear);
        $targetMingguan = $targetOmset->target_mingguan ?? 0;
        $targetBulanan = $targetOmset->target_bulanan ?? 0;
        $targetTahunan = $targetOmset->target_tahunan ?? 0;
        
        // Tentukan minggu ke berapa sekarang dalam bulan ini (1-4, berdasarkan tanggal 1-7, 8-14, 15-21, 22-akhir)
        $today = Carbon::now();
        $dayOfMonth = $today->day;
        $currentWeekOfMonth = 1;
        
        if ($dayOfMonth >= 1 && $dayOfMonth <= 7) {
            $currentWeekOfMonth = 1;
        } elseif ($dayOfMonth >= 8 && $dayOfMonth <= 14) {
            $currentWeekOfMonth = 2;
        } elseif ($dayOfMonth >= 15 && $dayOfMonth <= 21) {
            $currentWeekOfMonth = 3;
        } else {
            $currentWeekOfMonth = 4;
        }
        
        // Hitung range tanggal untuk minggu ini (sesuai logik rekap bulanan)
        $startOfMonth = Carbon::now()->startOfMonth();
        
        if ($currentWeekOfMonth == 1) {
            $startOfWeek = $startOfMonth->copy();
        } else {
            $startOfWeek = $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);
        }
        
        if ($currentWeekOfMonth == 4) {
            // Minggu ke-4 sampai akhir bulan
            $endOfWeek = $startOfMonth->copy()->endOfMonth();
        } else {
            $endOfWeek = $startOfWeek->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
        }
        
        // Omset Minggu Ini - Sistem (menggunakan range yang benar)
        $omsetSistemMingguIni = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
            ->whereBetween('pengiriman.tanggal_kirim', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Omset Manual Minggu Ini (dibagi 4 dari bulan ini)
        $omsetManualBulanIni = OmsetManual::where('tahun', Carbon::now()->year)
            ->where('bulan', Carbon::now()->month)
            ->value('omset_manual') ?? 0;
        $omsetManualMingguIni = $omsetManualBulanIni / 4;
        
        // Total Omset Minggu Ini
        $omsetMingguIni = $omsetSistemMingguIni + $omsetManualMingguIni;
        
        // Omset Bulan Ini - Sistem
        $omsetSistemBulanIni = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month)
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Total Omset Bulan Ini
        $omsetBulanIni = $omsetSistemBulanIni + $omsetManualBulanIni;
        
        // Omset Tahun Ini - Sistem
        $omsetSistemTahunIni = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        // Omset Manual Tahun Ini
        $omsetManualTahunIni = OmsetManual::where('tahun', Carbon::now()->year)
            ->sum('omset_manual') ?? 0;
        
        // Total Omset Tahun Ini
        $omsetTahunIni = $omsetSistemTahunIni + $omsetManualTahunIni;
        
        // Calculate Adjusted Target untuk bulan dan minggu saat ini (dengan carry forward)
        // Hitung total sisa target dari bulan-bulan sebelumnya
        $bulanSekarang = Carbon::now()->month;
        $sisaTargetSebelumnya = 0;
        
        for ($b = 1; $b < $bulanSekarang; $b++) {
            $omsetSistemBulanLalu = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $currentYear)
                ->whereMonth('pengiriman.tanggal_kirim', $b)
                ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
            
            $omsetManualBulanLalu = OmsetManual::where('tahun', $currentYear)
                ->where('bulan', $b)
                ->value('omset_manual') ?? 0;
            
            $omsetTotalBulanLalu = $omsetSistemBulanLalu + $omsetManualBulanLalu;
            $targetBulanLalu = $targetBulanan + $sisaTargetSebelumnya;
            $selisihBulanLalu = $omsetTotalBulanLalu - $targetBulanLalu;
            
            if ($selisihBulanLalu < 0) {
                $sisaTargetSebelumnya = $targetBulanLalu - $omsetTotalBulanLalu;
            } else {
                $sisaTargetSebelumnya = 0;
            }
        }
        
        // Target Adjusted untuk bulan ini
        $targetBulananAdjusted = $targetBulanan + $sisaTargetSebelumnya;
        
        // Target mingguan BASE (untuk bulan ini)
        $targetMingguanBase = $targetBulananAdjusted / 4;
        
        // Calculate target mingguan adjusted untuk minggu ini dengan carry forward dari minggu-minggu sebelumnya di bulan ini
        $sisaTargetMingguanSebelumnya = 0;
        
        // Loop dari minggu 1 sampai minggu sebelum minggu ini
        for ($w = 1; $w < $currentWeekOfMonth; $w++) {
            // Hitung range tanggal untuk minggu w (sesuai logika rekap bulanan)
            if ($w == 1) {
                $weekStart = $startOfMonth->copy();
            } else {
                $weekStart = $startOfMonth->copy()->addDays(($w - 1) * 7);
            }
            
            if ($w == 4) {
                $weekEnd = $startOfMonth->copy()->endOfMonth();
            } else {
                $weekEnd = $weekStart->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
            }
            
            // Hitung omset sistem untuk minggu w
            $omsetSistemWeek = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
                ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
            
            // Omset manual untuk minggu ini (1/4 dari omset manual bulan ini)
            $omsetManualWeek = $omsetManualBulanIni / 4;
            
            // Total omset minggu
            $omsetTotalWeek = $omsetSistemWeek + $omsetManualWeek;
            
            // Target untuk minggu w (dengan carry forward)
            $targetWeek = $targetMingguanBase + $sisaTargetMingguanSebelumnya;
            
            // Selisih
            $selisihWeek = $omsetTotalWeek - $targetWeek;
            
            // Update sisa target untuk minggu berikutnya
            if ($selisihWeek < 0) {
                // Target tidak tercapai, akumulasi sisa
                $sisaTargetMingguanSebelumnya = $targetWeek - $omsetTotalWeek;
            } else {
                // Target tercapai, reset sisa ke 0
                $sisaTargetMingguanSebelumnya = 0;
            }
        }
        
        // Target Adjusted untuk minggu ini = base + sisa dari minggu-minggu sebelumnya
        $targetMingguanAdjusted = $targetMingguanBase + $sisaTargetMingguanSebelumnya;
        
        // Progress Percentages dengan target adjusted
        $progressMinggu = $targetMingguanAdjusted > 0 ? ($omsetMingguIni / $targetMingguanAdjusted) * 100 : 0;
        $progressBulan = $targetBulananAdjusted > 0 ? ($omsetBulanIni / $targetBulananAdjusted) * 100 : 0;
        $progressTahun = $targetTahunan > 0 ? ($omsetTahunIni / $targetTahunan) * 100 : 0;
        
        // ========== OUTSTANDING PO ==========
        // Total Outstanding (nilai dari order details dengan status dikonfirmasi & diproses)
        $totalOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.total_harga');
        
        // Total Qty Outstanding
        $totalQtyOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.qty');
        
        // PO Berjalan
        $poBerjalan = Order::whereIn('status', ['dikonfirmasi', 'diproses'])->count();
        
        // ========== PENGIRIMAN MINGGU INI ==========
        // Menggunakan logic yang sama dengan omset (pembagian bulan menjadi 4 minggu)
        // Hitung range tanggal untuk minggu ini berdasarkan pembagian bulan
        if ($currentWeekOfMonth == 1) {
            $weekStartPengiriman = $startOfMonth->copy();
        } else {
            $weekStartPengiriman = $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);
        }
        
        if ($currentWeekOfMonth == 4) {
            $weekEndPengiriman = $startOfMonth->copy()->endOfMonth();
        } else {
            $weekEndPengiriman = $weekStartPengiriman->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
        }
        
        // Get all pengiriman with status menunggu_fisik, menunggu_verifikasi, dan berhasil
        $pengirimanMingguIni = Pengiriman::with(['forecast:id,total_qty_forecast', 'order.klien', 'purchasing'])
            ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereBetween('tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
            ->get();
        
        // Prepare arrays untuk menyimpan detail pengiriman berdasarkan kategori
        $pengirimanNormalList = [];
        $pengirimanBongkarSebagianList = [];
        
        // Count pengiriman normal (>70%) dan bongkar sebagian (<=70%)
        $pengirimanNormalMingguIni = 0;
        $pengirimanBongkarSebagianMingguIni = 0;
        
        foreach ($pengirimanMingguIni as $pengiriman) {
            if ($pengiriman->forecast && $pengiriman->forecast->total_qty_forecast > 0) {
                $percentage = ($pengiriman->total_qty_kirim / $pengiriman->forecast->total_qty_forecast) * 100;
                
                if ($percentage > 70) {
                    // Pengiriman Normal (>70%)
                    $pengirimanNormalMingguIni++;
                    $pengirimanNormalList[] = [
                        'id' => $pengiriman->id,
                        'po_number' => $pengiriman->order->po_number ?? 'N/A',
                        'tanggal_kirim' => $pengiriman->tanggal_kirim,
                        'klien' => $pengiriman->order->klien->nama ?? 'N/A',
                        'cabang' => $pengiriman->order->klien->cabang ?? null,
                        'total_qty_kirim' => $pengiriman->total_qty_kirim,
                        'total_qty_forecast' => $pengiriman->forecast->total_qty_forecast,
                        'percentage' => round($percentage, 2),
                        'status' => $pengiriman->status,
                        'purchasing' => $pengiriman->purchasing->nama ?? 'N/A',
                    ];
                } elseif ($percentage > 0 && $percentage <= 70) {
                    // Bongkar Sebagian (>0% dan <=70%)
                    $pengirimanBongkarSebagianMingguIni++;
                    $pengirimanBongkarSebagianList[] = [
                        'id' => $pengiriman->id,
                        'po_number' => $pengiriman->order->po_number ?? 'N/A',
                        'tanggal_kirim' => $pengiriman->tanggal_kirim,
                        'klien' => $pengiriman->order->klien->nama ?? 'N/A',
                        'cabang' => $pengiriman->order->klien->cabang ?? null,
                        'total_qty_kirim' => $pengiriman->total_qty_kirim,
                        'total_qty_forecast' => $pengiriman->forecast->total_qty_forecast,
                        'percentage' => round($percentage, 2),
                        'status' => $pengiriman->status,
                        'purchasing' => $pengiriman->purchasing->nama ?? 'N/A',
                    ];
                }
            } else {
                // Jika tidak ada forecast, anggap sebagai pengiriman normal
                $pengirimanNormalMingguIni++;
                $pengirimanNormalList[] = [
                    'id' => $pengiriman->id,
                    'po_number' => $pengiriman->order->po_number ?? 'N/A',
                    'tanggal_kirim' => $pengiriman->tanggal_kirim,
                    'klien' => $pengiriman->order->klien->nama ?? 'N/A',
                    'cabang' => $pengiriman->order->klien->cabang ?? null,
                    'total_qty_kirim' => $pengiriman->total_qty_kirim,
                    'total_qty_forecast' => 0,
                    'percentage' => 0,
                    'status' => $pengiriman->status,
                    'purchasing' => $pengiriman->purchasing->nama ?? 'N/A',
                ];
            }
        }
        
        $totalQtyPengirimanMingguIni = Pengiriman::leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->whereBetween('pengiriman.tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->sum(DB::raw('COALESCE(invoice_penagihan.qty_after_refraksi, pengiriman.total_qty_kirim)'));
        
        // ========== PENGIRIMAN GAGAL MINGGU INI ==========
        $pengirimanGagalList = Pengiriman::with(['order.klien', 'purchasing'])
            ->whereBetween('tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
            ->where('status', 'gagal')
            ->get()
            ->map(function($pengiriman) {
                return [
                    'id' => $pengiriman->id,
                    'po_number' => $pengiriman->order->po_number ?? 'N/A',
                    'tanggal_kirim' => $pengiriman->tanggal_kirim,
                    'klien' => $pengiriman->order->klien->nama ?? 'N/A',
                    'cabang' => $pengiriman->order->klien->cabang ?? null,
                    'total_qty_kirim' => $pengiriman->total_qty_kirim,
                    'catatan' => $pengiriman->catatan ?? '-',
                    'status' => $pengiriman->status,
                    'purchasing' => $pengiriman->purchasing->nama ?? 'N/A',
                ];
            })
            ->toArray();
        
        // ========== ORDER BULAN INI ==========
        $orderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->count();
        
        $nilaiOrderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->sum('total_amount');
        
        return view('pages.dashboard', compact(
            'targetMingguan',
            'targetBulanan',
            'targetTahunan',
            'targetMingguanAdjusted',
            'targetBulananAdjusted',
            'omsetMingguIni',
            'omsetBulanIni',
            'omsetTahunIni',
            'omsetSistemMingguIni',
            'omsetManualMingguIni',
            'omsetSistemBulanIni',
            'omsetManualBulanIni',
            'progressMinggu',
            'progressBulan',
            'progressTahun',
            'totalOutstanding',
            'totalQtyOutstanding',
            'poBerjalan',
            'pengirimanNormalMingguIni',
            'pengirimanBongkarSebagianMingguIni',
            'totalQtyPengirimanMingguIni',
            'pengirimanNormalList',
            'pengirimanBongkarSebagianList',
            'pengirimanGagalList',
            'orderBulanIni',
            'nilaiOrderBulanIni'
        ));
    }
    
    /**
     * Get Omset per Klien Chart Data (AJAX)
     */
    public function getOmsetPerKlien(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');
        
        // Get top 5 klien berdasarkan total omset tahun ini (with optional search filter)
        $topKlienQuery = DB::table('invoice_penagihan')
            ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->select('kliens.id as klien_id', 'kliens.nama',
                DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
            ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $tahun)
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('kliens.deleted_at');
        
        // Apply search filter if provided
        if (!empty($search)) {
            $topKlienQuery->where(function($q) use ($search) {
                $q->where('kliens.nama', 'like', '%' . $search . '%')
                  ->orWhere('kliens.cabang', 'like', '%' . $search . '%');
            });
        }
        
        $topKlien = $topKlienQuery
            ->groupBy('kliens.id', 'kliens.nama')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        
        $klienNames = [];
        $datasets = [];
        
        // Warna untuk setiap bulan
        $monthColors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899',
            '#06B6D4', '#F97316', '#14B8A6', '#F43F5E', '#8B5CF6', '#6366F1'
        ];
        
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Prepare datasets per month
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthData = [];
            
            foreach ($topKlien as $klien) {
                // Get omset untuk klien ini di bulan ini
                $omsetBulan = DB::table('invoice_penagihan')
                    ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                    ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                    ->where('orders.klien_id', $klien->klien_id)
                    ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
                    ->whereYear('pengiriman.tanggal_kirim', $tahun)
                    ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                    ->whereNull('pengiriman.deleted_at')
                    ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
                
                $monthData[] = floatval($omsetBulan);
            }
            
            $datasets[] = [
                'label' => $monthNames[$bulan - 1],
                'data' => $monthData,
                'backgroundColor' => $monthColors[$bulan - 1],
                'borderColor' => $monthColors[$bulan - 1],
                'borderWidth' => 1
            ];
        }
        
        // Get klien names
        foreach ($topKlien as $klien) {
            $klienNames[] = $klien->nama;
        }
        
        return response()->json([
            'klien_names' => $klienNames,
            'datasets' => $datasets
        ]);
    }
    
    /**
     * Get Omset per Supplier Chart Data (AJAX)
     */
    public function getOmsetPerSupplier(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');
        
        // Get top 5 supplier berdasarkan total omset tahun ini (with optional search filter)
        $topSupplierQuery = DB::table('invoice_penagihan')
            ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->select('suppliers.id as supplier_id', 'suppliers.nama',
                DB::raw('SUM(invoice_penagihan.amount_after_refraksi) as total'))
            ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $tahun)
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('suppliers.deleted_at');
        
        // Apply search filter if provided
        if (!empty($search)) {
            $topSupplierQuery->where(function($q) use ($search) {
                $q->where('suppliers.nama', 'like', '%' . $search . '%')
                  ->orWhere('suppliers.alamat', 'like', '%' . $search . '%');
            });
        }
        
        $topSupplier = $topSupplierQuery
            ->groupBy('suppliers.id', 'suppliers.nama')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        
        $supplierNames = [];
        $datasets = [];
        
        // Warna untuk setiap bulan
        $monthColors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899',
            '#06B6D4', '#F97316', '#14B8A6', '#F43F5E', '#8B5CF6', '#6366F1'
        ];
        
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Prepare datasets per month
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthData = [];
            
            foreach ($topSupplier as $supplier) {
                // Get omset untuk supplier ini di bulan ini
                $omsetBulan = DB::table('invoice_penagihan')
                    ->join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                    ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                    ->where('bahan_baku_supplier.supplier_id', $supplier->supplier_id)
                    ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
                    ->whereYear('pengiriman.tanggal_kirim', $tahun)
                    ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                    ->whereNull('pengiriman.deleted_at')
                    ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
                
                $monthData[] = floatval($omsetBulan);
            }
            
            $datasets[] = [
                'label' => $monthNames[$bulan - 1],
                'data' => $monthData,
                'backgroundColor' => $monthColors[$bulan - 1],
                'borderColor' => $monthColors[$bulan - 1],
                'borderWidth' => 1
            ];
        }
        
        // Get supplier names
        foreach ($topSupplier as $supplier) {
            $supplierNames[] = $supplier->nama;
        }
        
        return response()->json([
            'supplier_names' => $supplierNames,
            'datasets' => $datasets
        ]);
    }
    
    /**
     * Get Omset per Bahan Baku Chart Data (AJAX)
     */
    public function getOmsetPerBahanBaku(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');
        
        // Query total omset per bahan baku - menggunakan subquery untuk menghindari double counting
        $topBahanBakuQuery = DB::table('bahan_baku_klien')
            ->select(
                'bahan_baku_klien.id as bahan_baku_id',
                'bahan_baku_klien.nama',
                DB::raw('COALESCE(SUM(DISTINCT invoice_data.amount_after_refraksi), 0) as total')
            )
            ->leftJoin(
                DB::raw('(
                    SELECT DISTINCT 
                        invoice_penagihan.id as invoice_id,
                        invoice_penagihan.amount_after_refraksi,
                        order_details.bahan_baku_klien_id
                    FROM invoice_penagihan
                    JOIN pengiriman ON invoice_penagihan.pengiriman_id = pengiriman.id
                    JOIN pengiriman_details ON pengiriman.id = pengiriman_details.pengiriman_id
                    JOIN order_details ON pengiriman_details.purchase_order_bahan_baku_id = order_details.id
                    WHERE pengiriman.status IN ("menunggu_verifikasi", "berhasil")
                        AND YEAR(pengiriman.tanggal_kirim) = ' . $tahun . '
                        AND pengiriman.deleted_at IS NULL
                ) as invoice_data'),
                'bahan_baku_klien.id',
                '=',
                'invoice_data.bahan_baku_klien_id'
            )
            ->whereNull('bahan_baku_klien.deleted_at');
        
        // Apply search filter if provided
        if (!empty($search)) {
            $topBahanBakuQuery->where(function($q) use ($search) {
                $q->where('bahan_baku_klien.nama', 'like', '%' . $search . '%')
                  ->orWhere('bahan_baku_klien.spesifikasi', 'like', '%' . $search . '%');
            });
        }
        
        $topBahanBaku = $topBahanBakuQuery
            ->groupBy('bahan_baku_klien.id', 'bahan_baku_klien.nama')
            ->having('total', '>', 0)
            ->orderBy('total', 'desc')
            ->get();
        
        $bahanBakuNames = [];
        $datasets = [];
        
        // Warna untuk setiap bulan
        $monthColors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899',
            '#06B6D4', '#F97316', '#14B8A6', '#F43F5E', '#8B5CF6', '#6366F1'
        ];
        
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Prepare datasets per month
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthData = [];
            
            foreach ($topBahanBaku as $bahanBaku) {
                // Get omset untuk bahan baku ini di bulan ini - menggunakan DISTINCT untuk menghindari double counting
                $omsetBulan = DB::table(DB::raw('(
                    SELECT DISTINCT 
                        invoice_penagihan.id as invoice_id,
                        invoice_penagihan.amount_after_refraksi
                    FROM invoice_penagihan
                    JOIN pengiriman ON invoice_penagihan.pengiriman_id = pengiriman.id
                    JOIN pengiriman_details ON pengiriman.id = pengiriman_details.pengiriman_id
                    JOIN order_details ON pengiriman_details.purchase_order_bahan_baku_id = order_details.id
                    WHERE order_details.bahan_baku_klien_id = ' . $bahanBaku->bahan_baku_id . '
                        AND pengiriman.status IN ("menunggu_verifikasi", "berhasil")
                        AND YEAR(pengiriman.tanggal_kirim) = ' . $tahun . '
                        AND MONTH(pengiriman.tanggal_kirim) = ' . $bulan . '
                        AND pengiriman.deleted_at IS NULL
                ) as distinct_invoices'))
                ->sum('amount_after_refraksi') ?? 0;
                
                $monthData[] = floatval($omsetBulan);
            }
            
            $datasets[] = [
                'label' => $monthNames[$bulan - 1],
                'data' => $monthData,
                'backgroundColor' => $monthColors[$bulan - 1],
                'borderColor' => $monthColors[$bulan - 1],
                'borderWidth' => 1
            ];
        }
        
        // Get bahan baku names
        foreach ($topBahanBaku as $bahanBaku) {
            $bahanBakuNames[] = $bahanBaku->nama;
        }
        
        return response()->json([
            'bahan_baku_names' => $bahanBakuNames,
            'datasets' => $datasets
        ]);
    }
}