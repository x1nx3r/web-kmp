<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\TargetOmset;
use App\Models\OmsetManual;
use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\Pengiriman;

class DashboardService
{
    public static function getSummaryMetrics(Carbon $weekStart, Carbon $weekEnd)
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $cacheKey = 'dashboard:summary:' . $weekStart->format('Ymd') . ':' . $weekEnd->format('Ymd') . ':' . $currentYear . ':' . $currentMonth;

        return Cache::tags(['dashboard'])->remember($cacheKey, 600, function () use ($weekStart, $weekEnd, $currentYear, $currentMonth) {
            $targetOmset = TargetOmset::getTargetForYear($currentYear);

            $targetMingguan = $targetOmset->target_mingguan ?? 0;
            $targetBulanan  = $targetOmset->target_bulanan  ?? 0;
            $targetTahunan  = $targetOmset->target_tahunan  ?? 0;

            // Helper variables
            $today = Carbon::now();
            $dayOfMonth = $today->day;
            $startOfMonth = Carbon::now()->startOfMonth();

            // Calculate current week of month
            if ($dayOfMonth >= 1 && $dayOfMonth <= 7) {
                $currentWeekOfMonth = 1;
            } elseif ($dayOfMonth >= 8 && $dayOfMonth <= 14) {
                $currentWeekOfMonth = 2;
            } elseif ($dayOfMonth >= 15 && $dayOfMonth <= 21) {
                $currentWeekOfMonth = 3;
            } else {
                $currentWeekOfMonth = 4;
            }

            // ========== OMSET MINGGU INI ==========
            $omsetSistemMingguIniQuery = DB::table('pengiriman')
                ->leftJoin(DB::raw('(
                    SELECT pengiriman_id, MAX(subtotal) as subtotal
                    FROM invoice_penagihan
                    WHERE status != "digabung"
                    GROUP BY pengiriman_id
                ) as invoice_penagihan'), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
                ->whereNull('pengiriman.deleted_at');

            self::applyValidInvoiceFilter($omsetSistemMingguIniQuery);

            $omsetSistemMingguIni = $omsetSistemMingguIniQuery
                ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');

            $omsetManualBulanIni  = OmsetManual::where('tahun', $currentYear)->where('bulan', $currentMonth)->value('omset_manual') ?? 0;
            $omsetManualMingguIni = $omsetManualBulanIni / 4;
            $omsetMingguIni       = $omsetSistemMingguIni + $omsetManualMingguIni;

            // ========== OMSET BULAN INI ==========
            $omsetSistemBulanIniQuery = DB::table('pengiriman')
                ->leftJoin(DB::raw('(
                    SELECT pengiriman_id, MAX(subtotal) as subtotal
                    FROM invoice_penagihan
                    WHERE status != "digabung"
                    GROUP BY pengiriman_id
                ) as invoice_penagihan'), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $currentYear)
                ->whereMonth('pengiriman.tanggal_kirim', $currentMonth)
                ->whereNull('pengiriman.deleted_at');

            self::applyValidInvoiceFilter($omsetSistemBulanIniQuery);

            $omsetSistemBulanIni = $omsetSistemBulanIniQuery
                ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');

            $omsetBulanIni = $omsetSistemBulanIni + $omsetManualBulanIni;

            // ========== OMSET TAHUN INI ==========
            $omsetSistemTahunIniQuery = DB::table('pengiriman')
                ->leftJoin(DB::raw('(
                    SELECT pengiriman_id, MAX(subtotal) as subtotal
                    FROM invoice_penagihan
                    WHERE status != "digabung"
                    GROUP BY pengiriman_id
                ) as invoice_penagihan'), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $currentYear)
                ->whereNull('pengiriman.deleted_at');

            self::applyValidInvoiceFilter($omsetSistemTahunIniQuery);

            $omsetSistemTahunIni = $omsetSistemTahunIniQuery
                ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');

            $omsetManualTahunIni = OmsetManual::where('tahun', $currentYear)->sum('omset_manual') ?? 0;
            $omsetTahunIni       = $omsetSistemTahunIni + $omsetManualTahunIni;

            // ========== TARGET ADJUSTED (carry forward bulanan) ==========
            $sisaTargetSebelumnya = 0;

            for ($b = 1; $b < $currentMonth; $b++) {
                $omsetSistemBulanLaluQuery = DB::table('pengiriman')
                    ->leftJoin(DB::raw('(
                        SELECT pengiriman_id, MAX(subtotal) as subtotal
                        FROM invoice_penagihan
                        WHERE status != "digabung"
                        GROUP BY pengiriman_id
                    ) as invoice_penagihan'), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                    ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereYear('pengiriman.tanggal_kirim', $currentYear)
                    ->whereMonth('pengiriman.tanggal_kirim', $b)
                    ->whereNull('pengiriman.deleted_at');

                self::applyValidInvoiceFilter($omsetSistemBulanLaluQuery);

                $omsetSistemBulanLalu = $omsetSistemBulanLaluQuery
                    ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                    ->groupBy('pengiriman.id')
                    ->get()
                    ->sum('omset_pengiriman');

                $omsetManualBulanLalu = OmsetManual::where('tahun', $currentYear)->where('bulan', $b)->value('omset_manual') ?? 0;
                $omsetTotalBulanLalu  = $omsetSistemBulanLalu + $omsetManualBulanLalu;
                $targetBulanLalu      = $targetBulanan + $sisaTargetSebelumnya;
                $selisihBulanLalu     = $omsetTotalBulanLalu - $targetBulanLalu;
                $sisaTargetSebelumnya = $selisihBulanLalu < 0 ? abs($selisihBulanLalu) : 0;
            }

            $targetBulananAdjusted = $targetBulanan + $sisaTargetSebelumnya;
            $targetMingguanBase    = $targetBulananAdjusted / 4;

            // Target mingguan adjusted (carry forward mingguan)
            $sisaTargetMingguanSebelumnya = 0;

            for ($w = 1; $w < $currentWeekOfMonth; $w++) {
                $weekStartLoop = $w == 1 ? $startOfMonth->copy() : $startOfMonth->copy()->addDays(($w - 1) * 7);
                $weekEndLoop   = $w == 4 ? $startOfMonth->copy()->endOfMonth() : $weekStartLoop->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());

                $omsetSistemWeekQuery = DB::table('pengiriman')
                    ->leftJoin(DB::raw('(
                        SELECT pengiriman_id, MAX(subtotal) as subtotal
                        FROM invoice_penagihan
                        WHERE status != "digabung"
                        GROUP BY pengiriman_id
                    ) as invoice_penagihan'), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                    ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereBetween('pengiriman.tanggal_kirim', [$weekStartLoop->startOfDay(), $weekEndLoop->endOfDay()])
                    ->whereNull('pengiriman.deleted_at');

                self::applyValidInvoiceFilter($omsetSistemWeekQuery);

                $omsetSistemWeek = $omsetSistemWeekQuery
                    ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                    ->groupBy('pengiriman.id')
                    ->get()
                    ->sum('omset_pengiriman');

                $omsetManualWeek  = $omsetManualBulanIni / 4;
                $omsetTotalWeek   = $omsetSistemWeek + $omsetManualWeek;
                $targetWeek       = $targetMingguanBase + $sisaTargetMingguanSebelumnya;
                $selisihWeek      = $omsetTotalWeek - $targetWeek;
                $sisaTargetMingguanSebelumnya = $selisihWeek < 0 ? abs($selisihWeek) : 0;
            }

            $targetMingguanAdjusted = $targetMingguanBase + $sisaTargetMingguanSebelumnya;

            $progressMinggu = $targetMingguanAdjusted > 0 ? ($omsetMingguIni / $targetMingguanAdjusted) * 100 : 0;
            $progressBulan  = $targetBulananAdjusted > 0  ? ($omsetBulanIni  / $targetBulananAdjusted)  * 100 : 0;
            $progressTahun  = $targetTahunan > 0           ? ($omsetTahunIni  / $targetTahunan)           * 100 : 0;

            // ========== OUTSTANDING PO ==========
            $totalOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
                ->sum('order_details.total_harga');

            $totalQtyOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
                ->sum('order_details.qty');

            $poBerjalan = Order::whereIn('status', ['dikonfirmasi', 'diproses'])->count();

            return [
                'targetMingguan' => $targetMingguan,
                'targetBulanan' => $targetBulanan,
                'targetTahunan' => $targetTahunan,
                'targetMingguanAdjusted' => $targetMingguanAdjusted,
                'targetBulananAdjusted' => $targetBulananAdjusted,
                'omsetMingguIni' => $omsetMingguIni,
                'omsetBulanIni' => $omsetBulanIni,
                'omsetTahunIni' => $omsetTahunIni,
                'omsetSistemMingguIni' => $omsetSistemMingguIni,
                'omsetManualMingguIni' => $omsetManualMingguIni,
                'omsetSistemBulanIni' => $omsetSistemBulanIni,
                'omsetManualBulanIni' => $omsetManualBulanIni,
                'progressMinggu' => $progressMinggu,
                'progressBulan' => $progressBulan,
                'progressTahun' => $progressTahun,
                'totalOutstanding' => $totalOutstanding,
                'totalQtyOutstanding' => $totalQtyOutstanding,
                'poBerjalan' => $poBerjalan,
            ];
        });
    }

    private static function applyValidInvoiceFilter($query)
    {
        return $query->where(function ($q) {
            $q->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('invoice_penagihan as ip_all')
                    ->whereColumn('ip_all.pengiriman_id', 'pengiriman.id');
            })
            ->orWhereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('invoice_penagihan as ip_valid')
                    ->whereColumn('ip_valid.pengiriman_id', 'pengiriman.id')
                    ->where('ip_valid.status', '!=', 'digabung');
            });
        });
    }
}
