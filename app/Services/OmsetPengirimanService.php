<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;


class OmsetPengirimanService
{
    public const VALID_STATUSES = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];

    public static function perPengirimanQuery(?Carbon $start = null, ?Carbon $end = null): Builder
    {
        $query = DB::table('pengiriman as p')
            // li: fallback invoice lama per pengiriman (1 agregat, bukan subquery per baris).
            ->leftJoin(DB::raw(self::legacyInvoiceSub() . ' as li'), 'li.pengiriman_id', '=', 'p.id')
            // Invoice yang dipakai = p.invoice_penagihan_id, atau fallback li.invoice_id bila NULL.
            ->leftJoin('invoice_penagihan as ip', function ($join) {
                $join->whereRaw('ip.id = COALESCE(p.invoice_penagihan_id, li.invoice_id)');
            })
            ->leftJoin(DB::raw(self::invoiceGrossSub() . ' as ig'), function ($join) {
                $join->whereRaw('ig.invoice_penagihan_id = COALESCE(p.invoice_penagihan_id, li.invoice_id)');
            })
            ->leftJoin(DB::raw(self::invoiceItemAmountSub() . ' as iia'), function ($join) {
                $join->whereRaw('iia.invoice_penagihan_id = COALESCE(p.invoice_penagihan_id, li.invoice_id)')
                    ->whereRaw('(iia.item_pengiriman_id = p.id
                        OR (iia.item_pengiriman_id IS NULL AND iia.no_pengiriman = p.no_pengiriman))');
            })
            ->leftJoin(DB::raw(self::pengirimanDetailSub() . ' as pds'), 'pds.pengiriman_id', '=', 'p.id')
            ->whereIn('p.status', self::VALID_STATUSES)
            ->whereNull('p.deleted_at');

        if ($start && $end) {
            $query->whereBetween('p.tanggal_kirim', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
        }

        return $query
            ->select(
                'p.id as pengiriman_id',
                'p.forecast_id',
                'p.tanggal_kirim',
                'p.status',
                'p.catatan',
                'p.total_harga_kirim',
                'p.total_qty_kirim',
                DB::raw(self::omsetCaseSql() . ' as omset'),
                DB::raw('CASE
                    WHEN MAX(ig.pengiriman_count) > 1 THEN MAX(pds.qty_total)
                    ELSE COALESCE(MAX(ip.qty_after_refraksi), MAX(pds.qty_total))
                END as qty')
            )
            ->groupBy(
                'p.id', 'p.forecast_id', 'p.tanggal_kirim', 'p.status',
                'p.catatan', 'p.total_harga_kirim', 'p.total_qty_kirim'
            );
    }

    /** Total omset sistem dalam rentang tanggal (inklusif). */
    public static function totalOmset(Carbon $start, Carbon $end): float
    {
        return (float) DB::query()
            ->fromSub(self::perPengirimanQuery($start, $end), 't')
            ->sum('t.omset');
    }

    public static function totalOmsetBulan(int $year, int $month): float
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();

        return self::totalOmset($start, $start->copy()->endOfMonth());
    }

    public static function totalOmsetTahun(int $year): float
    {
        $start = Carbon::create($year, 1, 1)->startOfYear();

        return self::totalOmset($start, $start->copy()->endOfYear());
    }

    private static function legacyInvoiceSub(): string
    {
        return "(
            SELECT ipl.pengiriman_id,
                   MAX(ipl.id) AS invoice_id
            FROM invoice_penagihan ipl
            WHERE ipl.status != 'digabung'
            GROUP BY ipl.pengiriman_id
        )";
    }

    private static function invoiceGrossSub(): string
    {
        return '(
            SELECT p2.invoice_penagihan_id,
                   SUM(pd2.qty_kirim * od2.harga_jual) AS gross_invoice_total,
                   COUNT(DISTINCT p2.id)               AS pengiriman_count
            FROM pengiriman p2
            JOIN pengiriman_details pd2 ON pd2.pengiriman_id = p2.id
            JOIN order_details od2      ON od2.id = pd2.purchase_order_bahan_baku_id
            WHERE p2.invoice_penagihan_id IS NOT NULL
              AND p2.deleted_at IS NULL
            GROUP BY p2.invoice_penagihan_id
        )';
    }

    private static function invoiceItemAmountSub(): string
    {
        return "(
            SELECT t.invoice_penagihan_id,
                   t.item_pengiriman_id,
                   t.no_pengiriman,
                   MAX(t.item_amount) AS item_amount
            FROM (
                SELECT ip.id AS invoice_penagihan_id,
                       jt.item_pengiriman_id,
                       TRIM(SUBSTRING(jt.item_name, LENGTH('Pengiriman ') + 1)) AS no_pengiriman,
                       (jt.amount - COALESCE(jt.refraksi_amount, 0)) AS item_amount
                FROM invoice_penagihan ip
                JOIN JSON_TABLE(
                    COALESCE(ip.items, '[]'),
                    '\$[*]' COLUMNS (
                        item_name          VARCHAR(255)  PATH '\$.item_name',
                        item_pengiriman_id BIGINT        PATH '\$.pengiriman_id',
                        amount             DECIMAL(18,2) PATH '\$.amount',
                        refraksi_amount    DECIMAL(18,2) PATH '\$.refraksi_amount'
                    )
                ) AS jt
                WHERE jt.item_name LIKE 'Pengiriman %'
            ) t
            GROUP BY t.invoice_penagihan_id, t.item_pengiriman_id, t.no_pengiriman
        )";
    }

    private static function pengirimanDetailSub(): string
    {
        return '(
            SELECT pd.pengiriman_id,
                   SUM(pd.qty_kirim * od.harga_jual) AS gross_po,
                   SUM(pd.qty_kirim)                 AS qty_total
            FROM pengiriman_details pd
            LEFT JOIN order_details od ON od.id = pd.purchase_order_bahan_baku_id
            GROUP BY pd.pengiriman_id
        )';
    }

    private static function omsetCaseSql(): string
    {
        $invoiceAmount = 'COALESCE(NULLIF(MAX(ip.amount_after_refraksi), 0), NULLIF(MAX(ip.subtotal), 0))';

        return "CASE
            WHEN MAX(iia.item_amount) IS NOT NULL
                THEN MAX(iia.item_amount)
            WHEN {$invoiceAmount} IS NULL
                THEN COALESCE(MAX(pds.gross_po), 0)
            WHEN MAX(ig.gross_invoice_total) > 0
                THEN (COALESCE(MAX(pds.gross_po), 0) / MAX(ig.gross_invoice_total)) * {$invoiceAmount}
            ELSE {$invoiceAmount}
        END";
    }
}