<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use App\Models\TargetOmset;
use App\Models\OmsetManual;
use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\Pengiriman;

class DashboardService
{
    /**
     * Status pengiriman yang dianggap "aktif" untuk perhitungan omset.
     * Sebelumnya ditulis ulang sebagai array literal identik di 6 lokasi pada file ini.
     */
    private const VALID_PENGIRIMAN_STATUSES = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];

    /**
     * Status order yang dihitung sebagai "outstanding PO".
     * Sebelumnya ditulis ulang sebagai array literal identik di 2 lokasi pada file ini.
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

            $omsetExpr = self::omsetExpression();

            // ========== OMSET MINGGU INI ==========
            $omsetSistemMingguIniQuery = self::baseOmsetQuery()
                ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()]);

            self::applyValidInvoiceFilter($omsetSistemMingguIniQuery);

            $omsetSistemMingguIni = $omsetSistemMingguIniQuery
                ->select('pengiriman.id', $omsetExpr)
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');

            $omsetManualBulanIni  = OmsetManual::where('tahun', $currentYear)->where('bulan', $currentMonth)->value('omset_manual') ?? 0;
            $omsetManualMingguIni = $omsetManualBulanIni / 4;
            $omsetMingguIni       = $omsetSistemMingguIni + $omsetManualMingguIni;

            // ========== OMSET BULAN INI ==========
            $omsetSistemBulanIniQuery = self::baseOmsetQuery()
                ->whereYear('pengiriman.tanggal_kirim', $currentYear)
                ->whereMonth('pengiriman.tanggal_kirim', $currentMonth);

            self::applyValidInvoiceFilter($omsetSistemBulanIniQuery);

            $omsetSistemBulanIni = $omsetSistemBulanIniQuery
                ->select('pengiriman.id', $omsetExpr)
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');

            $omsetBulanIni = $omsetSistemBulanIni + $omsetManualBulanIni;

            // ========== OMSET TAHUN INI ==========
            $omsetSistemTahunIniQuery = self::baseOmsetQuery()
                ->whereYear('pengiriman.tanggal_kirim', $currentYear);

            self::applyValidInvoiceFilter($omsetSistemTahunIniQuery);

            $omsetSistemTahunIni = $omsetSistemTahunIniQuery
                ->select('pengiriman.id', $omsetExpr)
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');

            $omsetManualTahunIni = OmsetManual::where('tahun', $currentYear)->sum('omset_manual') ?? 0;
            $omsetTahunIni       = $omsetSistemTahunIni + $omsetManualTahunIni;

            // ========== TARGET (FLAT, TANPA CARRY-FORWARD) ==========
            // Sebelumnya target bulanan/mingguan disesuaikan (di-carry-forward) berdasarkan
            // kekurangan target bulan-bulan sebelumnya dalam tahun berjalan. Sekarang target
            // dipakai flat langsung dari target_bulanan tanpa penyesuaian apa pun.
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
     *
     * Sebelumnya array ini ditulis ulang identik (10 baris) di dua cabang kondisi
     * pada getWeeklyDeliveries(). Struktur dan isi key TIDAK berubah.
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

    /**
     * Query dasar untuk perhitungan omset pengiriman: join subquery invoice,
     * pengiriman_details, order_details, filter status aktif & belum dihapus.
     *
     * Sebelumnya blok join ini ditulis ulang identik di 5 lokasi pada file ini
     * (mingguan, bulanan, tahunan, loop bulanan, loop mingguan).
     */
    private static function baseOmsetQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('pengiriman')
            ->leftJoin(DB::raw(self::invoiceSubqueryRaw()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', self::VALID_PENGIRIMAN_STATUSES)
            ->whereNull('pengiriman.deleted_at');
    }

    /**
     * Subquery invoice yang dipakai berulang — sudah sertakan amount_after_refraksi.
     * Isi SQL TIDAK diubah dari versi sebelumnya, hanya dipindah ke method agar
     * bisa dipakai bersama oleh baseOmsetQuery().
     */
    private static function invoiceSubqueryRaw(): string
    {
        return '(
            SELECT pengiriman_id,
                   MAX(subtotal) as subtotal,
                   MAX(amount_after_refraksi) as amount_after_refraksi
            FROM invoice_penagihan
            WHERE status != "digabung"
            GROUP BY pengiriman_id
        ) as invoice_penagihan';
    }

    /**
     * COALESCE omset: prioritas amount_after_refraksi → subtotal → fallback qty×harga_jual.
     * Isi SQL TIDAK diubah dari versi sebelumnya.
     */
    private static function omsetExpression()
    {
        return DB::raw('COALESCE(
            NULLIF(MAX(invoice_penagihan.amount_after_refraksi), 0),
            NULLIF(MAX(invoice_penagihan.subtotal), 0),
            SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
        ) as omset_pengiriman');
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