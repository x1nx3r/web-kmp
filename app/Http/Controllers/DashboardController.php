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
        
        // Progress Percentages
        $progressMinggu = $targetMingguan > 0 ? ($omsetMingguIni / $targetMingguan) * 100 : 0;
        $progressBulan = $targetBulanan > 0 ? ($omsetBulanIni / $targetBulanan) * 100 : 0;
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
        $pengirimanMingguIni = Pengiriman::whereBetween('tanggal_kirim', [$startOfWeek, $endOfWeek])
            ->where('status', 'berhasil')
            ->count();
        
        $totalQtyPengirimanMingguIni = Pengiriman::leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->whereBetween('pengiriman.tanggal_kirim', [$startOfWeek, $endOfWeek])
            ->where('pengiriman.status', 'berhasil')
            ->sum(DB::raw('COALESCE(invoice_penagihan.qty_after_refraksi, pengiriman.total_qty_kirim)'));
        
        // ========== ORDER BULAN INI ==========
        $orderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->count();
        
        $nilaiOrderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->sum('total_amount');
        
        // ========== TREND OMSET 4 MINGGU TERAKHIR ==========
        $omsetTrend = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            
            $omsetSistem = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->whereIn('pengiriman.status', ['menunggu_verifikasi', 'berhasil'])
                ->whereBetween('pengiriman.tanggal_kirim', [$weekStart, $weekEnd])
                ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
            
            // Get omset manual for the month of this week
            $omsetManualWeek = OmsetManual::where('tahun', $weekStart->year)
                ->where('bulan', $weekStart->month)
                ->value('omset_manual') ?? 0;
            $omsetManualWeek = $omsetManualWeek / 4; // Divide by 4 weeks
            
            $omset = $omsetSistem + $omsetManualWeek;
            
            $omsetTrend[] = [
                'week' => 'Minggu ' . ($i == 0 ? 'Ini' : $i),
                'label' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M'),
                'omset' => $omset,
                'target' => $targetMingguan
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
            'pengirimanMingguIni',
            'totalQtyPengirimanMingguIni',
            'orderBulanIni',
            'nilaiOrderBulanIni',
            'omsetTrend',
            'poByStatus',
            'topKlien'
        ));
    }
}
