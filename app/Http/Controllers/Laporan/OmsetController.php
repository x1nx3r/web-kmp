<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use App\Models\Klien;
use App\Models\Order;
use App\Models\Pengiriman;
use App\Models\TargetOmset;
use App\Models\OmsetManual;
use Illuminate\Http\Request;
use App\Models\BahanBakuKlien;
use App\Models\InvoicePenagihan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OmsetController extends Controller
{
    /**
     * Helper method to calculate omset with fallback to qty * harga_jual
     * Groups by pengiriman to avoid duplicates
     */
    private function calculateOmsetSistem($query)
    {
        return DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereNull('pengiriman.deleted_at')
            ->mergeBindings($query)
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
    }
    
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
        
        // Calculate Total Omset (all time) - Sistem + Manual
        $totalOmsetSistem = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereNull('pengiriman.deleted_at')
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
        
        $totalOmsetManual = OmsetManual::sum('omset_manual') ?? 0;
        
        $totalOmset = $totalOmsetSistem + $totalOmsetManual;
        
        // ========== SUMMARY CARDS (ALWAYS CURRENT/NOW) ==========
        // Calculate Omset Tahun Ini (NOW - untuk summary card atas) - Sistem + Manual
        $omsetTahunIniSistemSummary = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->whereNull('pengiriman.deleted_at')
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
        
        $omsetTahunIniManualSummary = OmsetManual::where('tahun', Carbon::now()->year)
            ->sum('omset_manual') ?? 0;
        
        $omsetTahunIniSummary = $omsetTahunIniSistemSummary + $omsetTahunIniManualSummary;
        
        // Calculate Omset Bulan Ini (NOW - untuk summary card atas) - Sistem + Manual
        $omsetBulanIniSistemSummary = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month)
            ->whereNull('pengiriman.deleted_at')
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
        
        $omsetBulanIniManualSummary = OmsetManual::where('tahun', Carbon::now()->year)
            ->where('bulan', Carbon::now()->month)
            ->value('omset_manual') ?? 0;
        
        $omsetBulanIniSummary = $omsetBulanIniSistemSummary + $omsetBulanIniManualSummary;
        
        // ========== TARGET ANALYSIS (SELECTED YEAR) ==========
        // Calculate Omset Sistem untuk tahun yang dipilih
        $omsetSistemTahunIni = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
            ->whereNull('pengiriman.deleted_at')
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
        
        // Calculate Omset Manual untuk tahun yang dipilih
        $omsetManualTahunIni = OmsetManual::where('tahun', $selectedYearTarget)
            ->sum('omset_manual') ?? 0;
        
        // Total Omset Tahunan = Sistem + Manual
        $omsetTahunIni = $omsetSistemTahunIni + $omsetManualTahunIni;
        
        // Calculate Omset Sistem Bulan Ini untuk selected year
        $omsetSistemBulanIni = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
            ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month)
            ->whereNull('pengiriman.deleted_at')
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
        
        // Calculate Omset Manual Bulan Ini
        $omsetManualBulanIni = OmsetManual::where('tahun', $selectedYearTarget)
            ->where('bulan', Carbon::now()->month)
            ->value('omset_manual') ?? 0;
        
        // Total Omset Bulan Ini = Sistem + Manual
        $omsetBulanIni = $omsetSistemBulanIni + $omsetManualBulanIni;
        
        // Get Target Omset for selected year
        $targetOmset = TargetOmset::getTargetForYear($selectedYearTarget);
        
        $targetTahunan = $targetOmset->target_tahunan ?? 0;
        $targetBulanan = $targetOmset->target_bulanan ?? 0;
        $targetMingguan = $targetOmset->target_mingguan ?? 0;
        
        // Calculate Omset Minggu Ini (current week) - MENGIKUTI LOGIKA REKAP BULANAN
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
        
        // Hitung omset sistem minggu ini
        $omsetSistemMingguIni = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereBetween('pengiriman.tanggal_kirim', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
            ->whereNull('pengiriman.deleted_at')
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
        
        // Omset manual untuk minggu ini (dibagi 4 dari omset manual bulan ini)
        $omsetManualMingguIni = $omsetManualBulanIni / 4;
        
        // Total Omset Minggu Ini = Sistem + Manual
        $omsetMingguIni = $omsetSistemMingguIni + $omsetManualMingguIni;
        
        // Calculate Adjusted Target untuk bulan dan minggu saat ini (dengan carry forward)
        // Hitung total sisa target dari bulan-bulan sebelumnya di tahun yang dipilih
        $bulanSekarang = Carbon::now()->month;
        $sisaTargetSebelumnya = 0;
        
        // Hanya hitung sisa jika tahun yang dipilih adalah tahun sekarang
        if ($selectedYearTarget == Carbon::now()->year) {
            for ($b = 1; $b < $bulanSekarang; $b++) {
                $omsetSistemBulanLalu = DB::table('pengiriman')
                    ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                    ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
                    ->whereMonth('pengiriman.tanggal_kirim', $b)
                    ->whereNull('pengiriman.deleted_at')
                    ->select(
                        'pengiriman.id',
                        DB::raw('COALESCE(
                            MAX(invoice_penagihan.amount_after_refraksi),
                            SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                        ) as omset_pengiriman')
                    )
                    ->groupBy('pengiriman.id')
                    ->get()
                    ->sum('omset_pengiriman');
                
                $omsetManualBulanLalu = OmsetManual::where('tahun', $selectedYearTarget)
                    ->where('bulan', $b)
                    ->value('omset_manual') ?? 0;
                
                $omsetTotalBulanLalu = $omsetSistemBulanLalu + $omsetManualBulanLalu;
                
                // Target bulan lalu juga adjusted (dengan carry forward sebelumnya)
                $targetBulanLalu = $targetBulanan + $sisaTargetSebelumnya;
                $selisihBulanLalu = $omsetTotalBulanLalu - $targetBulanLalu;
                
                if ($selisihBulanLalu < 0) {
                    // Target tidak tercapai, akumulasi sisa target
                    $sisaTargetSebelumnya = $targetBulanLalu - $omsetTotalBulanLalu;
                } else {
                    // Target tercapai, reset sisa ke 0
                    $sisaTargetSebelumnya = 0;
                }
            }
        }
        
        // Target Adjusted untuk bulan ini
        $targetBulananAdjusted = $targetBulanan + $sisaTargetSebelumnya;
        
        // Target mingguan BASE (untuk bulan ini)
        $targetMingguanBase = $targetBulananAdjusted / 4;
        
        // Calculate target mingguan adjusted untuk minggu ini dengan carry forward dari minggu-minggu sebelumnya di bulan ini
        $sisaTargetMingguanSebelumnya = 0;
        
        if ($selectedYearTarget == Carbon::now()->year) {
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
                $omsetSistemWeek = DB::table('pengiriman')
                    ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                    ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                    ->whereNull('pengiriman.deleted_at')
                    ->select(
                        'pengiriman.id',
                        DB::raw('COALESCE(
                            MAX(invoice_penagihan.amount_after_refraksi),
                            SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                        ) as omset_pengiriman')
                    )
                    ->groupBy('pengiriman.id')
                    ->get()
                    ->sum('omset_pengiriman');
                
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
        }
        
        // Target Adjusted untuk minggu ini = base + sisa dari minggu-minggu sebelumnya
        $targetMingguanAdjusted = $targetMingguanBase + $sisaTargetMingguanSebelumnya;
        
        // Calculate Progress Percentages dengan target adjusted
        $progressMinggu = $targetMingguanAdjusted > 0 ? ($omsetMingguIni / $targetMingguanAdjusted) * 100 : 0;
        $progressBulan = $targetBulananAdjusted > 0 ? ($omsetBulanIni / $targetBulananAdjusted) * 100 : 0;
        $progressTahun = $targetTahunan > 0 ? ($omsetTahunIni / $targetTahunan) * 100 : 0;
        
  
        $rekapBulanan = [];
        $sisaTargetAkumulasi = 0; // Akumulasi sisa target yang belum tercapai
        
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            // Omset sistem (dari transaksi real)
            $omsetSistem = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
                ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                ->whereNull('pengiriman.deleted_at')
                ->select(
                    'pengiriman.id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman')
                )
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');
            
            // Omset manual (input manual untuk data historis)
            $omsetManualData = OmsetManual::where('tahun', $selectedYearTarget)
                ->where('bulan', $bulan)
                ->first();
            $omsetManual = $omsetManualData ? (float)$omsetManualData->omset_manual : 0;
            
            // Total omset bulan ini = omset sistem + omset manual
            $omsetBulan = $omsetSistem + $omsetManual;
            
            // Target FLAT untuk kolom "Target" dan "Progress" (Target Tahunan / 12)
            $targetBulananFlat = $targetBulanan;
            
            // Progress berdasarkan target FLAT (bukan adjusted)
            $progressBulanIni = $targetBulananFlat > 0 ? ($omsetBulan / $targetBulananFlat) * 100 : 0;
            
            // Target ADJUSTED untuk kolom "Selisih" (dengan carry forward)
            // Januari: 100M + 0 = 100M
            // Februari: 100M + 50M (sisa Jan yang belum tercapai) = 150M
            // Maret: 100M + 150M (total sisa Jan+Feb) = 250M
            $targetBulananAdjusted = $targetBulanan + $sisaTargetAkumulasi;
            
            // Selisih bulan ini = Realisasi bulan ini - Target bulan ini (ADJUSTED dengan carry forward)
            $selisihBulanIni = $omsetBulan - $targetBulananAdjusted;
            
            // Update akumulasi SISA TARGET untuk bulan berikutnya (untuk selisih)
            // Jika target tidak tercapai penuh, sisanya bertambah
            if ($selisihBulanIni < 0) {
                // Target tidak tercapai, sisa = target adjusted yang belum tercapai
                $sisaTargetAkumulasi = $targetBulananAdjusted - $omsetBulan;
            } else {
                // Target tercapai, tidak ada sisa (reset ke 0)
                $sisaTargetAkumulasi = 0;
            }
            
            // Target mingguan BASE FLAT (untuk progress mingguan) = target bulanan FLAT / 4
            $targetMingguanBaseFlat = $targetBulananFlat / 4;
            
            // Calculate weekly breakdown for this month
            // Untuk omset manual, dibagi rata 4 minggu
            $omsetManualPerMinggu = $omsetManual / 4;
            $mingguanDetail = [];
            $startDate = Carbon::create($selectedYearTarget, $bulan, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth();
            
            // Sisa target akumulasi untuk minggu-minggu dalam bulan ini (untuk selisih mingguan)
            $sisaTargetMingguanAkumulasi = 0;
            
            // Bagi bulan menjadi tepat 4 minggu (7 hari per minggu = 28 hari)
            // Sisa hari di akhir bulan (29, 30, 31) masuk ke minggu ke-4
            for ($minggu = 1; $minggu <= 4; $minggu++) {
                if ($minggu == 1) {
                    $weekStart = $startDate->copy();
                } else {
                    $weekStart = $startDate->copy()->addDays(($minggu - 1) * 7);
                }
                
                if ($minggu == 4) {
                    // Minggu ke-4 ambil sampai akhir bulan (bisa lebih dari 7 hari)
                    $weekEnd = $endDate->copy();
                } else {
                    $weekEnd = $weekStart->copy()->addDays(6)->min($endDate);
                }
                
                // Skip jika weekStart sudah melewati end of month
                if ($weekStart > $endDate) {
                    break;
                }
                
                // Omset sistem mingguan
                $omsetSistemMinggu = DB::table('pengiriman')
                    ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                    ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                    ->whereNull('pengiriman.deleted_at')
                    ->select(
                        'pengiriman.id',
                        DB::raw('COALESCE(
                            MAX(invoice_penagihan.amount_after_refraksi),
                            SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                        ) as omset_pengiriman')
                    )
                    ->groupBy('pengiriman.id')
                    ->get()
                    ->sum('omset_pengiriman');
                
                // Total omset minggu = omset sistem + (omset manual / 4)
                $omsetMinggu = $omsetSistemMinggu + $omsetManualPerMinggu;
                
                // Target FLAT untuk progress (Target Bulanan Flat / 4)
                $targetMingguIniFlat = $targetMingguanBaseFlat;
                
                // Progress berdasarkan target FLAT (bukan adjusted)
                $progressMingguIni = $targetMingguIniFlat > 0 ? ($omsetMinggu / $targetMingguIniFlat) * 100 : 0;
                
                // Target ADJUSTED untuk selisih (dengan carry forward)
                // Minggu 1: 25M + 0 = 25M
                // Minggu 2: 25M + sisa minggu 1 = 25M + 20M = 45M (jika minggu 1 hanya capai 5M)
                // Minggu 3: 25M + total sisa minggu 1+2 = 25M + 65M = 90M (jika minggu 2 hanya capai 0M)
                $targetMingguIniAdjusted = $targetMingguanBaseFlat + $sisaTargetMingguanAkumulasi;
                
                // Selisih minggu ini (menggunakan target ADJUSTED)
                $selisihMingguIni = $omsetMinggu - $targetMingguIniAdjusted;
                
                // Update akumulasi sisa target mingguan untuk minggu berikutnya
                if ($selisihMingguIni < 0) {
                    // Target tidak tercapai, sisa bertambah
                    $sisaTargetMingguanAkumulasi = $targetMingguIniAdjusted - $omsetMinggu;
                } else {
                    // Target tercapai, reset sisa ke 0
                    $sisaTargetMingguanAkumulasi = 0;
                }
                
                $mingguanDetail[$minggu] = [
                    'omset' => $omsetMinggu,
                    'progress' => $progressMingguIni, // Progress dari target FLAT
                    'target' => $targetMingguIniFlat, // Target FLAT untuk ditampilkan
                    'tanggal' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M')
                ];
            }
            
            $rekapBulanan[$bulan] = [
                'realisasi' => $omsetBulan, // Realisasi bulan ini saja
                'omset_bulan_ini' => $omsetBulan, // Omset bulan ini saja
                'omset_sistem' => $omsetSistem,
                'omset_manual' => $omsetManual,
                'target' => $targetBulananFlat, // Target FLAT (untuk kolom Target dan Progress)
                'progress' => $progressBulanIni, // Progress dari target FLAT
                'selisih' => $selisihBulanIni, // Selisih dengan target ADJUSTED (carry forward)
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
        
        // Handle AJAX request for Get Omset Sistem
        if ($request->ajax() && $request->get('ajax') === 'get_omset_sistem') {
            $tahun = $request->get('tahun', Carbon::now()->year);
            $bulan = $request->get('bulan', Carbon::now()->month);
            
            // Get omset sistem untuk bulan tertentu
            $omsetSistem = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $tahun)
                ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                ->whereNull('pengiriman.deleted_at')
                ->select(
                    'pengiriman.id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman')
                )
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');
            
            return response()->json([
                'omset_sistem' => $omsetSistem
            ]);
        }
        
        // Handle AJAX request for Omset per Klien (Bar Chart)
        if ($request->ajax() && $request->get('ajax') === 'omset_per_klien') {
            $tahun = $request->get('tahun', Carbon::now()->year);
            $search = $request->get('search', '');
            
            $topKlienQuery = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
                ->select('kliens.id as klien_id', 'kliens.nama', 'kliens.cabang', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman'))
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $tahun)
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('kliens.deleted_at')
                ->groupBy('pengiriman.id', 'kliens.id', 'kliens.nama', 'kliens.cabang');
            
            // Apply search filter if provided
            if (!empty($search)) {
                $topKlienQuery->where(function($q) use ($search) {
                    $q->where('kliens.nama', 'like', '%' . $search . '%')
                      ->orWhere('kliens.cabang', 'like', '%' . $search . '%');
                });
            }
            
            $topKlienData = $topKlienQuery->get();
            
            // Group by klien and sum omset_pengiriman
            $topKlien = $topKlienData->groupBy('klien_id')->map(function($items) {
                $first = $items->first();
                return (object)[
                    'klien_id' => $first->klien_id,
                    'nama' => $first->nama,
                    'cabang' => $first->cabang,
                    'total' => $items->sum('omset_pengiriman')
                ];
            })->sortByDesc('total')->take(5)->values();
            
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
                    $omsetBulan = DB::table('pengiriman')
                        ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                        ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                        ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                        ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                        ->where('orders.klien_id', $klien->klien_id)
                        ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                        ->whereYear('pengiriman.tanggal_kirim', $tahun)
                        ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                        ->whereNull('pengiriman.deleted_at')
                        ->select(
                            'pengiriman.id',
                            DB::raw('COALESCE(
                                MAX(invoice_penagihan.amount_after_refraksi),
                                SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                            ) as omset_pengiriman')
                        )
                        ->groupBy('pengiriman.id')
                        ->get()
                        ->sum('omset_pengiriman');
                    
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
            
            // Get klien names with cabang
            foreach ($topKlien as $klien) {
                $namaLengkap = $klien->nama;
                if (!empty($klien->cabang)) {
                    $namaLengkap .= ' - ' . $klien->cabang . '';
                }
                $klienNames[] = $namaLengkap;
            }
            
            return response()->json([
                'klien_names' => $klienNames,
                'datasets' => $datasets
            ]);
        }
        
        // Handle AJAX request for Omset per Supplier (Bar Chart)
        if ($request->ajax() && $request->get('ajax') === 'omset_per_supplier') {
            $tahun = $request->get('tahun', Carbon::now()->year);
            $search = $request->get('search', '');
            
            // Get top 5 supplier berdasarkan total omset tahun ini (with optional search filter)
            $topSupplierQuery = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->select('suppliers.id as supplier_id', 'suppliers.nama', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman'))
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $tahun)
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('suppliers.deleted_at')
                ->groupBy('pengiriman.id', 'suppliers.id', 'suppliers.nama');
            
            // Apply search filter if provided
            if (!empty($search)) {
                $topSupplierQuery->where(function($q) use ($search) {
                    $q->where('suppliers.nama', 'like', '%' . $search . '%')
                      ->orWhere('suppliers.alamat', 'like', '%' . $search . '%');
                });
            }
            
            $topSupplierData = $topSupplierQuery->get();
            
            // Group by supplier and sum omset_pengiriman
            $topSupplier = $topSupplierData->groupBy('supplier_id')->map(function($items) {
                $first = $items->first();
                return (object)[
                    'supplier_id' => $first->supplier_id,
                    'nama' => $first->nama,
                    'total' => $items->sum('omset_pengiriman')
                ];
            })->sortByDesc('total')->take(5)->values();
            
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
                    $omsetBulan = DB::table('pengiriman')
                        ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                        ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                        ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                        ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                        ->where('bahan_baku_supplier.supplier_id', $supplier->supplier_id)
                        ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                        ->whereYear('pengiriman.tanggal_kirim', $tahun)
                        ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                        ->whereNull('pengiriman.deleted_at')
                        ->select(
                            'pengiriman.id',
                            DB::raw('COALESCE(
                                MAX(invoice_penagihan.amount_after_refraksi),
                                SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                            ) as omset_pengiriman')
                        )
                        ->groupBy('pengiriman.id')
                        ->get()
                        ->sum('omset_pengiriman');
                    
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
        
        // Handle AJAX request for Omset per Bahan Baku (Bar Chart)
        if ($request->ajax() && $request->get('ajax') === 'omset_per_bahan_baku') {
            $tahun = $request->get('tahun', Carbon::now()->year);
            $search = $request->get('search', '');
            
            // Query total omset per bahan baku - GROUP BY nama (bukan id) untuk menggabungkan yang namanya sama
            $topBahanBakuQuery = DB::table('bahan_baku_klien')
                ->select(
                    'bahan_baku_klien.nama',
                    DB::raw('GROUP_CONCAT(DISTINCT bahan_baku_klien.id) as bahan_baku_ids'),
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
                        WHERE pengiriman.status IN ("menunggu_fisik", "menunggu_verifikasi", "berhasil")
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
                ->groupBy('bahan_baku_klien.nama')  // Group by nama saja
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
                    // Get all IDs yang memiliki nama yang sama
                    $bahanBakuIds = explode(',', $bahanBaku->bahan_baku_ids);
                    
                    // Get omset untuk semua bahan baku dengan nama yang sama di bulan ini
                    $omsetBulan = DB::table('pengiriman')
                        ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                        ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                        ->join('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                        ->whereIn('order_details.bahan_baku_klien_id', $bahanBakuIds)
                        ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                        ->whereYear('pengiriman.tanggal_kirim', $tahun)
                        ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                        ->whereNull('pengiriman.deleted_at')
                        ->select(
                            'pengiriman.id',
                            DB::raw('COALESCE(
                                MAX(invoice_penagihan.amount_after_refraksi),
                                SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                            ) as omset_pengiriman')
                        )
                        ->groupBy('pengiriman.id')
                        ->get()
                        ->sum('omset_pengiriman');
                    
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
        
        // Handle AJAX request for Top Klien
        if ($request->ajax() && $request->get('ajax') === 'top_klien') {
            // Using amount_after_refraksi from invoice_penagihan
            $topKlienQuery = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
                ->select('kliens.id as klien_id', 'kliens.nama', 'kliens.cabang', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman'))
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('kliens.deleted_at')
                ->groupBy('pengiriman.id', 'kliens.id', 'kliens.nama', 'kliens.cabang');
            
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
            
            $topKlienData = $topKlienQuery->get();
            
            // Group by klien and sum omset_pengiriman
            $topKlien = $topKlienData->groupBy('klien_id')->map(function($items) {
                $first = $items->first();
                return (object)[
                    'nama' => $first->nama,
                    'cabang' => $first->cabang,
                    'total' => $items->sum('omset_pengiriman')
                ];
            })->sortByDesc('total')->values();
            
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
            // Using HARGA JUAL from invoice_penagihan (same as other omset calculations)
            $topSupplierQuery = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->select('suppliers.id as supplier_id', 'suppliers.nama', 'suppliers.alamat', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman'))
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('suppliers.deleted_at')
                ->groupBy('pengiriman.id', 'suppliers.id', 'suppliers.nama', 'suppliers.alamat');
            
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
            
            $topSupplierData = $topSupplierQuery->get();
            
            // Group by supplier and sum omset_pengiriman
            $topSupplier = $topSupplierData->groupBy('supplier_id')->map(function($items) {
                $first = $items->first();
                return (object)[
                    'nama' => $first->nama,
                    'alamat' => $first->alamat,
                    'total' => $items->sum('omset_pengiriman')
                ];
            })->sortByDesc('total')->values();
            
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
        
        // Handle AJAX request for Marketing
        if ($request->ajax() && $request->get('ajax') === 'marketing') {
            // Query based on order_winner table with amount_after_refraksi
            $omsetMarketingQuery = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
                ->join('users', 'order_winners.user_id', '=', 'users.id')
                ->select('order_winners.user_id', 'users.nama', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman'))
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->groupBy('pengiriman.id', 'order_winners.user_id', 'users.nama');
            
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
            
            $omsetMarketingData = $omsetMarketingQuery->get();
            
            // Group by user and sum omset_pengiriman
            $omsetMarketing = $omsetMarketingData->groupBy('user_id')->map(function($items) {
                $first = $items->first();
                return (object)[
                    'nama' => $first->nama,
                    'total' => $items->sum('omset_pengiriman')
                ];
            })->values();
            
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
            $omsetProcurementQuery = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->select('pengiriman.purchasing_id', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman'))
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->groupBy('pengiriman.id', 'pengiriman.purchasing_id');
            
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
            
            $omsetProcurementDataRaw = $omsetProcurementQuery->get();
            
            // Group by purchasing_id and sum omset_pengiriman
            $omsetProcurementData = $omsetProcurementDataRaw->groupBy('purchasing_id')->map(function($items) {
                return (object)[
                    'purchasing_id' => $items->first()->purchasing_id,
                    'total' => $items->sum('omset_pengiriman')
                ];
            })->values();
            
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
        $omsetMarketingQuery = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->select('order_winners.user_id', 'users.nama', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman'))
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'order_winners.user_id', 'users.nama');
        
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
        
        $omsetMarketingDataRaw = $omsetMarketingQuery->get();
        
        // Group by user and sum omset_pengiriman
        $omsetMarketingGrouped = $omsetMarketingDataRaw->groupBy('user_id')->map(function($items) {
            $first = $items->first();
            return (object)[
                'user_id' => $first->user_id,
                'nama' => $first->nama,
                'total' => $items->sum('omset_pengiriman')
            ];
        })->values();
        
        // Transform data to collection for blade
        $omsetMarketing = $omsetMarketingGrouped->map(function($item) {
            return (object)[
                'user_id' => $item->user_id,
                'creator' => (object)['nama' => $item->nama],
                'total' => floatval($item->total ?? 0)
            ];
        })->filter(function($item) {
            return $item->total > 0;
        })->values();
        
        // Omset Procurement by PIC (from InvoicePenagihan with pengiriman status 'berhasil')
        $omsetProcurementQuery = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select('pengiriman.purchasing_id', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman'))
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'pengiriman.purchasing_id');
        
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
        
        $omsetProcurementDataRaw = $omsetProcurementQuery->get();
        
        // Group by purchasing_id and sum omset_pengiriman
        $omsetProcurementDataGrouped = $omsetProcurementDataRaw->groupBy('purchasing_id')->map(function($items) {
            return (object)[
                'purchasing_id' => $items->first()->purchasing_id,
                'total' => $items->sum('omset_pengiriman')
            ];
        })->values();
        
        // Get purchasing names
        $omsetProcurement = $omsetProcurementDataGrouped->map(function($item) {
            $purchasing = \App\Models\User::find($item->purchasing_id);
            return [
                'purchasing_id' => $item->purchasing_id,
                'nama' => $purchasing ? $purchasing->nama : 'Unknown',
                'total' => floatval($item->total ?? 0)
            ];
        })->filter(function($item) {
            return $item['total'] > 0;
        })->values();
        
        // Get Top Klien data - using amount_after_refraksi
        $topKlienQuery = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->select('kliens.id as klien_id', 'kliens.nama', 'kliens.cabang', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman'))
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('kliens.deleted_at')
            ->groupBy('pengiriman.id', 'kliens.id', 'kliens.nama', 'kliens.cabang');
        
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
        
        $topKlienData = $topKlienQuery->get();
        
        // Group by klien and sum omset_pengiriman
        $topKlien = $topKlienData->groupBy('klien_id')->map(function($items) {
            $first = $items->first();
            return (object)[
                'klien_id' => $first->klien_id,
                'nama' => $first->nama,
                'cabang' => $first->cabang,
                'total' => $items->sum('omset_pengiriman')
            ];
        })->sortByDesc('total')->values();
        
        // Get Top Supplier data (NON-AJAX)
        // Using HARGA JUAL from invoice_penagihan (same as other omset calculations)
        $topSupplierQuery = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->select('suppliers.id as supplier_id', 'suppliers.nama', 'suppliers.alamat', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman'))
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->groupBy('pengiriman.id', 'suppliers.id', 'suppliers.nama', 'suppliers.alamat');
        
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
        
        $topSupplierData = $topSupplierQuery->get();
        
        // Group by supplier and sum omset_pengiriman
        $topSupplier = $topSupplierData->groupBy('supplier_id')->map(function($items) {
            $first = $items->first();
            return (object)[
                'supplier_id' => $first->supplier_id,
                'nama' => $first->nama,
                'alamat' => $first->alamat,
                'total' => $items->sum('omset_pengiriman')
            ];
        })->sortByDesc('total')->values();
        
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
            'targetBulananAdjusted',   // Target bulan ini dengan carry forward
            'targetMingguanAdjusted',  // Target minggu ini dengan carry forward
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
    
    public function saveOmsetManual(Request $request)
    {
        try {
            // Check if user is direktur
            if (Auth::user()->role !== 'direktur') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Direktur yang dapat menginput omset manual'
                ], 403);
            }
            
            $request->validate([
                'tahun' => 'required|integer|min:2020|max:2100',
                'bulan' => 'required|integer|min:1|max:12',
                'omset_manual' => 'required|numeric|min:0'
            ]);
            
            $omsetManual = OmsetManual::updateOrCreate(
                [
                    'tahun' => $request->tahun,
                    'bulan' => $request->bulan
                ],
                [
                    'omset_manual' => $request->omset_manual,
                    'updated_by' => Auth::id()
                ]
            );
            
            // Set created_by only on creation
            if ($omsetManual->wasRecentlyCreated) {
                $omsetManual->created_by = Auth::id();
                $omsetManual->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Omset manual berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getMarketingDetails(Request $request)
    {
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Get omset details per marketing with PO details using fallback logic
        $query = DB::table('pengiriman')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select(
                'users.nama as marketing_nama',
                'orders.no_order',
                'orders.po_number',
                'kliens.nama as klien_nama',
                'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(MAX(invoice_penagihan.amount_after_refraksi), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as total_nilai')
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'users.nama', 'orders.no_order', 'orders.po_number', 'kliens.nama');
        
        // Apply filter
        if ($periode === 'tahun_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $query->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
        }
        
        $details = $query->orderBy('users.nama')->orderBy('total_nilai', 'desc')->get();
        
        return response()->json($details);
    }
    
    public function exportMarketingPDF(Request $request)
    {
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Get omset details per marketing using fallback logic
        $query = DB::table('pengiriman')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select(
                'users.nama as marketing_nama',
                'orders.no_order',
                'orders.po_number',
                'kliens.nama as klien_nama',
                'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(MAX(invoice_penagihan.amount_after_refraksi), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as total_nilai')
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'users.nama', 'orders.no_order', 'orders.po_number', 'kliens.nama');
        
        // Apply filter
        if ($periode === 'tahun_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $query->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
        }
        
        $details = $query->orderBy('users.nama')->orderBy('total_nilai', 'desc')->get();
        
        // Group by marketing
        $groupedData = $details->groupBy('marketing_nama');
        
        $pdf = \PDF::loadView('pages.laporan.pdf.omset-marketing', [
            'groupedData' => $groupedData,
            'periode' => $periode,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalOverall' => $details->sum('total_nilai')
        ]);
        
        return $pdf->download('Omset_Marketing_'.date('Y-m-d').'.pdf');
    }
    
    public function getProcurementDetails(Request $request)
    {
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Get omset details per procurement with PO details using fallback logic
        $query = DB::table('pengiriman')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->join('users', 'pengiriman.purchasing_id', '=', 'users.id')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select(
                'users.nama as purchasing_nama',
                'orders.no_order',
                'orders.po_number',
                'kliens.nama as klien_nama',
                'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(MAX(invoice_penagihan.amount_after_refraksi), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as total_nilai')
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'users.nama', 'orders.no_order', 'orders.po_number', 'kliens.nama');
        
        // Apply filter
        if ($periode === 'tahun_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $query->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
        }
        
        $details = $query->orderBy('users.nama')->orderBy('total_nilai', 'desc')->get();
        
        return response()->json($details);
    }
    
    public function exportProcurementPDF(Request $request)
    {
        $periode = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Get omset details per procurement using fallback logic
        $query = DB::table('pengiriman')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->join('users', 'pengiriman.purchasing_id', '=', 'users.id')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select(
                'users.nama as purchasing_nama',
                'orders.no_order',
                'orders.po_number',
                'kliens.nama as klien_nama',
                'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(MAX(invoice_penagihan.amount_after_refraksi), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as total_nilai')
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'users.nama', 'orders.no_order', 'orders.po_number', 'kliens.nama');
        
        // Apply filter
        if ($periode === 'tahun_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $query->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
        }
        
        $details = $query->orderBy('users.nama')->orderBy('total_nilai', 'desc')->get();
        
        // Group by procurement
        $groupedData = $details->groupBy('purchasing_nama');
        
        $pdf = \PDF::loadView('pages.laporan.pdf.omset-procurement', [
            'groupedData' => $groupedData,
            'periode' => $periode,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalOverall' => $details->sum('total_nilai')
        ]);
        
        return $pdf->download('Omset_Procurement_'.date('Y-m-d').'.pdf');
    }
    
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
}
