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
    /**
     * Status pengiriman yang dianggap "aktif".
     * Satu sumber dengan OmsetPengirimanService supaya konsisten.
     */
    private const VALID_PENGIRIMAN_STATUSES = OmsetPengirimanService::VALID_STATUSES;

    /**
     * Status order yang dihitung sebagai "outstanding PO".
     */
    private const OUTSTANDING_ORDER_STATUSES = ['dikonfirmasi', 'diproses'];

    public static function getSummaryMetrics(Carbon $weekStart, Carbon $weekEnd)
    {
        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $cacheKey = 'dashboard:summary:' . $weekStart->format('Ymd') . ':' . $weekEnd->format('Ymd') . ':' . $currentYear . ':' . $currentMonth;

        return Cache::tags(['dashboard'])->remember($cacheKey, 600, function () use ($weekStart, $weekEnd, $currentYear, $currentMonth) {
            $targetOmset = TargetOmset::getTargetForYear($currentYear);

            $targetMingguan = $targetOmset->target_mingguan ?? 0;
            $targetBulanan  = $targetOmset->target_bulanan  ?? 0;
            $targetTahunan  = $targetOmset->target_tahunan  ?? 0;

            // ========== OMSET MINGGU INI ==========
            // Semua omset sistem dihitung oleh OmsetPengirimanService (sama dengan Evaluasi Procurement).
            $omsetSistemMingguIni = OmsetPengirimanService::totalOmset($weekStart, $weekEnd);

            $omsetManualBulanIni  = OmsetManual::where('tahun', $currentYear)->where('bulan', $currentMonth)->value('omset_manual') ?? 0;
            $omsetManualMingguIni = $omsetManualBulanIni / 4;
            $omsetMingguIni       = $omsetSistemMingguIni + $omsetManualMingguIni;

            // ========== OMSET BULAN INI ==========
            $omsetSistemBulanIni = OmsetPengirimanService::totalOmsetBulan($currentYear, $currentMonth);
            $omsetBulanIni       = $omsetSistemBulanIni + $omsetManualBulanIni;

            // ========== OMSET TAHUN INI ==========
            $omsetSistemTahunIni = OmsetPengirimanService::totalOmsetTahun($currentYear);
            $omsetManualTahunIni = OmsetManual::where('tahun', $currentYear)->sum('omset_manual') ?? 0;
            $omsetTahunIni       = $omsetSistemTahunIni + $omsetManualTahunIni;

            // ========== TARGET (FLAT, TANPA CARRY-FORWARD) ==========
            $targetBulananAdjusted  = $targetBulanan;
            $targetMingguanAdjusted = $targetBulanan / 4;

            $progressMinggu = $targetMingguanAdjusted > 0 ? ($omsetMingguIni / $targetMingguanAdjusted) * 100 : 0;
            $progressBulan  = $targetBulananAdjusted  > 0 ? ($omsetBulanIni  / $targetBulananAdjusted)  * 100 : 0;
            $progressTahun  = $targetTahunan          > 0 ? ($omsetTahunIni  / $targetTahunan)          * 100 : 0;

            // ========== OUTSTANDING PO ==========
            $totalOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                ->whereIn('orders.status', self::OUTSTANDING_ORDER_STATUSES)
                ->sum('order_details.total_harga');

            $totalQtyOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                ->whereIn('orders.status', self::OUTSTANDING_ORDER_STATUSES)
                ->sum('order_details.qty');

            $poBerjalan = Order::whereIn('status', self::OUTSTANDING_ORDER_STATUSES)->count();

            return [
                'targetMingguan'         => $targetMingguan,
                'targetBulanan'          => $targetBulanan,
                'targetTahunan'          => $targetTahunan,
                'targetMingguanAdjusted' => $targetMingguanAdjusted,
                'targetBulananAdjusted'  => $targetBulananAdjusted,
                'omsetMingguIni'         => $omsetMingguIni,
                'omsetBulanIni'          => $omsetBulanIni,
                'omsetTahunIni'          => $omsetTahunIni,
                'omsetSistemMingguIni'   => $omsetSistemMingguIni,
                'omsetManualMingguIni'   => $omsetManualMingguIni,
                'omsetSistemBulanIni'    => $omsetSistemBulanIni,
                'omsetManualBulanIni'    => $omsetManualBulanIni,
                'progressMinggu'         => $progressMinggu,
                'progressBulan'          => $progressBulan,
                'progressTahun'          => $progressTahun,
                'totalOutstanding'       => $totalOutstanding,
                'totalQtyOutstanding'    => $totalQtyOutstanding,
                'poBerjalan'             => $poBerjalan,
            ];
        });
    }

    public static function getWeeklyDeliveries(Carbon $weekStart, Carbon $weekEnd): array
    {
        $cacheKey = 'dashboard:deliveries:' . $weekStart->format('Ymd') . ':' . $weekEnd->format('Ymd');

        return Cache::tags(['dashboard'])->remember($cacheKey, 300, function () use ($weekStart, $weekEnd) {
            $pengirimanMingguIni = Pengiriman::with(['forecast:id,total_qty_forecast', 'order.klien', 'purchasing'])
                ->whereIn('status', self::VALID_PENGIRIMAN_STATUSES)
                ->whereBetween('tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
                ->get();

            $pengirimanNormalList               = [];
            $pengirimanBongkarSebagianList      = [];
            $pengirimanNormalMingguIni          = 0;
            $pengirimanBongkarSebagianMingguIni = 0;

            foreach ($pengirimanMingguIni as $pengiriman) {
                if ($pengiriman->forecast && $pengiriman->forecast->total_qty_forecast > 0) {
                    $percentage = ($pengiriman->total_qty_kirim / $pengiriman->forecast->total_qty_forecast) * 100;

                    $item = self::buildDeliveryItem($pengiriman, $pengiriman->forecast->total_qty_forecast, round($percentage, 2));

                    if ($percentage > 70) {
                        $pengirimanNormalMingguIni++;
                        $pengirimanNormalList[] = $item;
                    } elseif ($percentage > 0) {
                        $pengirimanBongkarSebagianMingguIni++;
                        $pengirimanBongkarSebagianList[] = $item;
                    }
                } else {
                    $pengirimanNormalMingguIni++;
                    $pengirimanNormalList[] = self::buildDeliveryItem($pengiriman, 0, 0);
                }
            }

            $totalQtyPengirimanMingguIni = Pengiriman::leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
                ->whereIn('pengiriman.status', self::VALID_PENGIRIMAN_STATUSES)
                ->sum(DB::raw('COALESCE(invoice_penagihan.qty_after_refraksi, pengiriman.total_qty_kirim)'));

            return compact(
                'pengirimanNormalList', 'pengirimanBongkarSebagianList',
                'pengirimanNormalMingguIni', 'pengirimanBongkarSebagianMingguIni',
                'totalQtyPengirimanMingguIni'
            );
        });
    }

    /**
     * Bentuk 1 baris data pengiriman untuk daftar mingguan.
     */
    private static function buildDeliveryItem(Pengiriman $pengiriman, int|float $totalQtyForecast, int|float $percentage): array
    {
        return [
            'id'                 => $pengiriman->id,
            'po_number'          => $pengiriman->order->po_number ?? 'N/A',
            'tanggal_kirim'      => $pengiriman->tanggal_kirim,
            'klien'              => $pengiriman->order->klien->nama ?? 'N/A',
            'cabang'             => $pengiriman->order->klien->cabang ?? null,
            'total_qty_kirim'    => $pengiriman->total_qty_kirim,
            'total_qty_forecast' => $totalQtyForecast,
            'percentage'         => $percentage,
            'status'             => $pengiriman->status,
            'purchasing'         => $pengiriman->purchasing->nama ?? 'N/A',
        ];
    }
}