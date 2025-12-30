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
        
        // Omset Minggu Ini - Sistem
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $omsetSistemMingguIni = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
            ->whereBetween('pengiriman.tanggal_kirim', [$startOfWeek, $endOfWeek])
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
        
        // Hitung minggu ke berapa sekarang dalam bulan ini (1-4)
        $startOfMonth = Carbon::now()->startOfMonth();
        $currentWeekOfMonth = 1;
        $tempDate = $startOfMonth->copy();
        
        while ($tempDate->addDays(7)->lte(Carbon::now()->startOfWeek())) {
            $currentWeekOfMonth++;
        }
        $currentWeekOfMonth = min($currentWeekOfMonth, 4); // Max 4 minggu
        
        // Loop dari minggu 1 sampai minggu sebelum minggu ini
        for ($w = 1; $w < $currentWeekOfMonth; $w++) {
            // Hitung range tanggal untuk minggu ini
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
            
            // Hitung omset sistem untuk minggu ini
            $omsetSistemWeek = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
                ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
            
            // Omset manual untuk minggu ini (1/4 dari omset manual bulan ini)
            $omsetManualWeek = $omsetManualBulanIni / 4;
            
            // Total omset minggu
            $omsetTotalWeek = $omsetSistemWeek + $omsetManualWeek;
            
            // Target untuk minggu ini (dengan carry forward)
            $targetWeek = $targetMingguanBase + $sisaTargetMingguanSebelumnya;
            
            // Selisih
            $selisihWeek = $omsetTotalWeek - $targetWeek;
            
            // Update sisa target untuk minggu berikutnya
            if ($selisihWeek < 0) {
                $sisaTargetMingguanSebelumnya = $targetWeek - $omsetTotalWeek;
            } else {
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
        $pengirimanMingguIni = Pengiriman::with('forecast:id,total_qty_forecast')
            ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereBetween('tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
            ->get();
        
        // Count pengiriman normal (>70%) dan bongkar sebagian (<=70%)
        $pengirimanNormalMingguIni = 0;
        $pengirimanBongkarSebagianMingguIni = 0;
        
        foreach ($pengirimanMingguIni as $pengiriman) {
            if ($pengiriman->forecast && $pengiriman->forecast->total_qty_forecast > 0) {
                $percentage = ($pengiriman->total_qty_kirim / $pengiriman->forecast->total_qty_forecast) * 100;
                
                if ($percentage > 70) {
                    // Pengiriman Normal (>70%)
                    $pengirimanNormalMingguIni++;
                } elseif ($percentage > 0 && $percentage <= 70) {
                    // Bongkar Sebagian (>0% dan <=70%)
                    $pengirimanBongkarSebagianMingguIni++;
                }
            } else {
                // Jika tidak ada forecast, anggap sebagai pengiriman normal
                $pengirimanNormalMingguIni++;
            }
        }
        
        $totalQtyPengirimanMingguIni = Pengiriman::leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->whereBetween('pengiriman.tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->sum(DB::raw('COALESCE(invoice_penagihan.qty_after_refraksi, pengiriman.total_qty_kirim)'));
        
        // ========== PENGIRIMAN GAGAL MINGGU INI ==========
        $pengirimanGagalMingguIni = Pengiriman::whereBetween('tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
            ->where('status', 'gagal')
            ->count();
        
        // ========== ORDER BULAN INI ==========
        $orderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->count();
        
        $nilaiOrderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->sum('total_amount');
        
        // ========== TREND OMSET 4 MINGGU TERAKHIR ==========
        // Menggunakan logic pembagian bulan menjadi 4 minggu (seperti di OmsetController)
        $omsetTrend = [];
        
        // Get current month start
        $currentMonthStart = Carbon::now()->startOfMonth();
        
        // Hitung minggu saat ini dalam bulan (1-4)
        $currentWeekOfMonth = 1;
        $tempDate = $currentMonthStart->copy();
        while ($tempDate->addDays(7)->lte(Carbon::now()->startOfWeek())) {
            $currentWeekOfMonth++;
        }
        $currentWeekOfMonth = min($currentWeekOfMonth, 4);
        
        // Loop untuk 4 minggu terakhir (minggu 1-4 dalam bulan ini)
        for ($weekNum = 1; $weekNum <= 4; $weekNum++) {
            // Hitung range tanggal untuk minggu ini berdasarkan pembagian bulan
            if ($weekNum == 1) {
                $weekStart = $currentMonthStart->copy();
            } else {
                $weekStart = $currentMonthStart->copy()->addDays(($weekNum - 1) * 7);
            }
            
            if ($weekNum == 4) {
                $weekEnd = $currentMonthStart->copy()->endOfMonth();
            } else {
                $weekEnd = $weekStart->copy()->addDays(6)->min($currentMonthStart->copy()->endOfMonth());
            }
            
            // Hitung omset sistem untuk minggu ini
            $omsetSistem = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
                ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
            
            // Get omset manual for this month (dibagi 4)
            $omsetManualBulanIni = OmsetManual::where('tahun', Carbon::now()->year)
                ->where('bulan', Carbon::now()->month)
                ->value('omset_manual') ?? 0;
            $omsetManualWeek = $omsetManualBulanIni / 4;
            
            $omset = $omsetSistem + $omsetManualWeek;
            
            // Hitung target adjusted untuk minggu ini (dengan carry forward dari minggu sebelumnya)
            $sisaTargetMingguanSebelumnya = 0;
            
            // Loop dari minggu 1 sampai minggu sebelum minggu ini untuk hitung sisa target
            for ($w = 1; $w < $weekNum; $w++) {
                // Range minggu sebelumnya
                if ($w == 1) {
                    $prevWeekStart = $currentMonthStart->copy();
                } else {
                    $prevWeekStart = $currentMonthStart->copy()->addDays(($w - 1) * 7);
                }
                
                if ($w == 4) {
                    $prevWeekEnd = $currentMonthStart->copy()->endOfMonth();
                } else {
                    $prevWeekEnd = $prevWeekStart->copy()->addDays(6)->min($currentMonthStart->copy()->endOfMonth());
                }
                
                // Omset sistem minggu sebelumnya
                $prevOmsetSistem = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                    ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
                    ->whereBetween('pengiriman.tanggal_kirim', [$prevWeekStart->startOfDay(), $prevWeekEnd->endOfDay()])
                    ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
                
                $prevOmsetManual = $omsetManualBulanIni / 4;
                $prevOmsetTotal = $prevOmsetSistem + $prevOmsetManual;
                
                // Target minggu sebelumnya (base + sisa sebelumnya)
                $prevTarget = ($targetBulananAdjusted / 4) + $sisaTargetMingguanSebelumnya;
                
                // Hitung selisih
                $selisih = $prevOmsetTotal - $prevTarget;
                
                // Update sisa target
                if ($selisih < 0) {
                    $sisaTargetMingguanSebelumnya = $prevTarget - $prevOmsetTotal;
                } else {
                    $sisaTargetMingguanSebelumnya = 0;
                }
            }
            
            // Target adjusted untuk minggu ini
            $targetWeekAdjusted = ($targetBulananAdjusted / 4) + $sisaTargetMingguanSebelumnya;
            
            $omsetTrend[] = [
                'week' => 'Minggu ' . $weekNum,
                'label' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M'),
                'omset' => $omset,
                'target' => $targetWeekAdjusted,
                'is_current' => $weekNum == $currentWeekOfMonth
            ];
        }
        
        // ========== PO BY STATUS ==========
        $poByStatus = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();
        
        // ========== TOP 5 KLIEN (by order value this month) ==========
        $topKlien = Order::select(
                'kliens.nama as klien_nama',
                DB::raw('COUNT(orders.id) as total_po'),
                DB::raw('SUM(orders.total_amount) as total_nilai')
            )
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->whereYear('orders.tanggal_order', Carbon::now()->year)
            ->whereMonth('orders.tanggal_order', Carbon::now()->month)
            ->groupBy('kliens.id', 'kliens.nama')
            ->orderBy('total_nilai', 'desc')
            ->limit(5)
            ->get();
        
        return view('pages.dashboard', compact(
            'targetMingguan',
            'targetBulanan',
            'targetTahunan',
            'targetMingguanAdjusted',
            'targetBulananAdjusted',
            'omsetMingguIni',
            'omsetSistemMingguIni',
            'omsetManualMingguIni',
            'omsetBulanIni',
            'omsetSistemBulanIni',
            'omsetManualBulanIni',
            'omsetTahunIni',
            'progressMinggu',
            'progressBulan',
            'progressTahun',
            'totalOutstanding',
            'totalQtyOutstanding',
            'poBerjalan',
            'pengirimanNormalMingguIni',
            'pengirimanBongkarSebagianMingguIni',
            'totalQtyPengirimanMingguIni',
            'pengirimanGagalMingguIni',
            'orderBulanIni',
            'nilaiOrderBulanIni',
            'omsetTrend',
            'poByStatus',
            'topKlien'
        ));
    }
}
