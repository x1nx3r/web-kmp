<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Pengiriman;
use App\Models\TargetOmset;
use App\Models\OmsetManual;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MarginExport;

class DashboardController extends Controller
{
    /**
     * Normalisasi nama bahan baku agar variasi penulisan/alias tergabung dalam 1 kategori.
     */
    private function normalizeBahanBakuName(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '-';
        }

        $key = mb_strtolower($name);
        $key = preg_replace('/\s+/', ' ', $key);

        $synonyms = [
            'tepung biskuit' => 'Tepung biskuit',
            'biscuit meal'   => 'Tepung biskuit',
            'biskuit meal'   => 'Tepung biskuit',
            'biskuit  meal'  => 'Tepung biskuit',
            'tepung roti'    => 'Tepung biskuit',
            'mie kuning'     => 'Mie kuning',
            'noodle broken'  => 'Mie kuning',
            'tepung mie'     => 'Mie kuning',
        ];

        if (isset($synonyms[$key])) {
            return $synonyms[$key];
        }

        return ucwords($name);
    }

    /**
     * Hitung range tanggal default minggu berjalan (pembagian bulan 1-7, 8-14, 15-21, 22-akhir).
     */
    private function getDefaultWeekRange(): array
    {
        $today        = Carbon::now();
        $dayOfMonth   = $today->day;
        $startOfMonth = Carbon::now()->startOfMonth();

        if ($dayOfMonth >= 1 && $dayOfMonth <= 7) {
            $currentWeekOfMonth = 1;
        } elseif ($dayOfMonth >= 8 && $dayOfMonth <= 14) {
            $currentWeekOfMonth = 2;
        } elseif ($dayOfMonth >= 15 && $dayOfMonth <= 21) {
            $currentWeekOfMonth = 3;
        } else {
            $currentWeekOfMonth = 4;
        }

        if ($currentWeekOfMonth == 1) {
            $startOfWeek = $startOfMonth->copy();
        } else {
            $startOfWeek = $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);
        }

        if ($currentWeekOfMonth == 4) {
            $endOfWeek = $startOfMonth->copy()->endOfMonth();
        } else {
            $endOfWeek = $startOfWeek->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
        }

        return [
            'start' => $startOfWeek,
            'end'   => $endOfWeek,
        ];
    }

    /**
     * Helper kalkulasi week-of-month dari tanggal hari ini.
     * Mengembalikan int 1-4 dan Carbon startOfMonth.
     */
    private function getCurrentWeekOfMonth(): array
    {
        $today        = Carbon::now();
        $dayOfMonth   = $today->day;
        $startOfMonth = Carbon::now()->startOfMonth();

        if ($dayOfMonth >= 1 && $dayOfMonth <= 7) {
            $week = 1;
        } elseif ($dayOfMonth >= 8 && $dayOfMonth <= 14) {
            $week = 2;
        } elseif ($dayOfMonth >= 15 && $dayOfMonth <= 21) {
            $week = 3;
        } else {
            $week = 4;
        }

        return ['week' => $week, 'startOfMonth' => $startOfMonth];
    }

    /**
     * Hitung margin dari collection pengiriman.
     * Konsisten dengan MarginController::hitungHargaBeliJual():
     *  - Total jual/beli diambil LANGSUNG dari amount invoice/approval (bukan harga/kg × qty_kirim)
     *  - Prioritas jual : subtotal → amount_after_refraksi → harga_jual PO
     *  - Prioritas beli : subtotal → amount_after_refraksi → total_harga_kirim → harga_satuan detail
     *
     * @param  \Illuminate\Support\Collection  $pengirimanList
     * @param  bool  $withMeta  Sertakan pengiriman_id, status, no_pengiriman, has_refraksi
     * @return array{rows: array, totalMargin: float, totalHargaBeli: float, totalHargaJual: float}
     */
    private function hitungMarginDariPengiriman($pengirimanList, bool $withMeta = false): array
    {
        $toFloat = fn($val) => floatval(str_replace(',', '.', (string)($val ?? 0)));

        $rows           = [];
        $totalMargin    = 0;
        $totalHargaBeli = 0;
        $totalHargaJual = 0;

        foreach ($pengirimanList as $p) {
            if (!$p->approvalPembayaran && !$p->invoicePenagihan) {
                continue;
            }

            foreach ($p->pengirimanDetails as $detail) {

                // ===== HARGA JUAL =====
                $hargaJualPerKg    = 0;
                $totalHargaJualItem = 0;

                if ($p->invoicePenagihan) {
                    $amountJual = $toFloat($p->invoicePenagihan->subtotal) > 0
                        ? $toFloat($p->invoicePenagihan->subtotal)
                        : $toFloat($p->invoicePenagihan->amount_after_refraksi);

                    $qtyJual = $toFloat($p->invoicePenagihan->qty_after_refraksi) > 0
                        ? $toFloat($p->invoicePenagihan->qty_after_refraksi)
                        : $toFloat($p->invoicePenagihan->qty_before_refraksi ?? $p->total_qty_kirim);

                    if ($qtyJual > 0 && $amountJual > 0) {
                        $hargaJualPerKg = $amountJual / $qtyJual;
                    }

                    // Total langsung dari invoice agar refraksi tidak dibatalkan
                    $totalHargaJualItem = $amountJual;

                } elseif ($detail->orderDetail && $toFloat($detail->orderDetail->harga_jual) > 0) {
                    $hargaJualPerKg    = $toFloat($detail->orderDetail->harga_jual);
                    $totalHargaJualItem = $toFloat($detail->qty_kirim) * $hargaJualPerKg;
                }

                // ===== HARGA BELI =====
                $hargaBeliPerKg    = 0;
                $totalHargaBeliItem = 0;

                if ($p->approvalPembayaran) {
                    $amountBeli = $toFloat($p->approvalPembayaran->subtotal) > 0
                        ? $toFloat($p->approvalPembayaran->subtotal)
                        : ($toFloat($p->approvalPembayaran->amount_after_refraksi) > 0
                            ? $toFloat($p->approvalPembayaran->amount_after_refraksi)
                            : $toFloat($p->total_harga_kirim));

                    $qtyBeli = $toFloat($p->approvalPembayaran->qty_after_refraksi) > 0
                        ? $toFloat($p->approvalPembayaran->qty_after_refraksi)
                        : $toFloat($p->total_qty_kirim);

                    if ($qtyBeli > 0 && $amountBeli > 0) {
                        $hargaBeliPerKg = $amountBeli / $qtyBeli;
                    }

                    // Total langsung dari approval agar refraksi tidak dibatalkan
                    $totalHargaBeliItem = $amountBeli;

                } else {
                    $hargaBeliPerKg    = $toFloat($detail->harga_satuan);
                    $totalHargaBeliItem = $toFloat($detail->total_harga);
                }

                $margin           = $totalHargaJualItem - $totalHargaBeliItem;
                $marginPercentage = $totalHargaJualItem > 0 ? ($margin / $totalHargaJualItem) * 100 : 0;

                $klien             = $p->order->klien ?? null;
                $namaKlien         = $klien ? $klien->nama . ($klien->cabang ? " - {$klien->cabang}" : '') : '-';
                $picMarketingUser  = $p->order->winner->user ?? null;
                $namaPicMarketing  = $picMarketingUser ? $picMarketingUser->nama : '-';
                $supplier          = $detail->bahanBakuSupplier->supplier ?? null;
                $bahanBaku         = $detail->orderDetail->bahanBakuKlien ?? null;
                $bahanBakuSupplier = $detail->bahanBakuSupplier ?? null;

                $row = [
                    'tanggal_kirim'     => $p->tanggal_kirim,
                    'pic_purchasing'    => $p->purchasing->nama ?? '-',
                    'pic_marketing'     => $namaPicMarketing,
                    'klien'             => $namaKlien,
                    'supplier'          => $supplier->nama ?? '-',
                    'bahan_baku'        => $bahanBaku->nama ?? $bahanBakuSupplier->nama ?? '-',
                    'qty_kirim'         => $detail->qty_kirim,
                    'qty'               => $detail->qty_kirim,
                    'harga_beli_per_kg' => $hargaBeliPerKg,
                    'harga_beli_total'  => $totalHargaBeliItem,
                    'harga_jual_per_kg' => $hargaJualPerKg,
                    'harga_jual_total'  => $totalHargaJualItem,
                    'total_harga_beli'  => $totalHargaBeliItem,
                    'total_harga_jual'  => $totalHargaJualItem,
                    'margin'            => $margin,
                    'margin_percentage' => $marginPercentage,
                ];

                if ($withMeta) {
                    $row['pengiriman_id'] = $p->id;
                    $row['status']        = $p->status;
                    $row['no_pengiriman'] = $p->no_pengiriman ?? '-';
                    $row['has_refraksi']  = $p->approvalPembayaran
                        && floatval($p->approvalPembayaran->refraksi_amount ?? 0) > 0;
                }

                $rows[] = $row;

                $totalMargin    += $margin;
                $totalHargaBeli += $totalHargaBeliItem;
                $totalHargaJual += $totalHargaJualItem;
            }
        }

        return compact('rows', 'totalMargin', 'totalHargaBeli', 'totalHargaJual');
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        // ========== PARSE DATE RANGE FILTER (WEEKLY) ==========
        $useCustomRange = false;
        $defaultRange   = $this->getDefaultWeekRange();
        $weekStart      = $defaultRange['start'];
        $weekEnd        = $defaultRange['end'];

        $startDateParam = $request->get('start_date', '');
        $endDateParam   = $request->get('end_date', '');

        if (!empty($startDateParam) && !empty($endDateParam)) {
            try {
                $parsedStart = Carbon::createFromFormat('Y-m-d', $startDateParam)->startOfDay();
                $parsedEnd   = Carbon::createFromFormat('Y-m-d', $endDateParam)->endOfDay();

                if ($parsedStart->lte($parsedEnd)) {
                    $weekStart      = $parsedStart;
                    $weekEnd        = $parsedEnd;
                    $useCustomRange = true;
                }
            } catch (\Exception $e) {
                // fallback ke default
            }
        }

        $rangeStartLabel = $weekStart->format('d M Y');
        $rangeEndLabel   = $weekEnd->format('d M Y');

        // ========== TARGET ==========
        $currentYear = Carbon::now()->year;
        $targetOmset = TargetOmset::getTargetForYear($currentYear);

        $targetMingguan = $targetOmset->target_mingguan ?? 0;
        $targetBulanan  = $targetOmset->target_bulanan  ?? 0;
        $targetTahunan  = $targetOmset->target_tahunan  ?? 0;

        ['week' => $currentWeekOfMonth, 'startOfMonth' => $startOfMonth] = $this->getCurrentWeekOfMonth();

        // ========== OMSET MINGGU INI ==========
        $omsetSistemMingguIni = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
            ->whereNull('pengiriman.deleted_at')
            ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');

        $omsetManualBulanIni  = OmsetManual::where('tahun', Carbon::now()->year)->where('bulan', Carbon::now()->month)->value('omset_manual') ?? 0;
        $omsetManualMingguIni = $omsetManualBulanIni / 4;
        $omsetMingguIni       = $omsetSistemMingguIni + $omsetManualMingguIni;

        // ========== OMSET BULAN INI ==========
        $omsetSistemBulanIni = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month)
            ->whereNull('pengiriman.deleted_at')
            ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');

        $omsetBulanIni = $omsetSistemBulanIni + $omsetManualBulanIni;

        // ========== OMSET TAHUN INI ==========
        $omsetSistemTahunIni = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->whereNull('pengiriman.deleted_at')
            ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');

        $omsetManualTahunIni = OmsetManual::where('tahun', Carbon::now()->year)->sum('omset_manual') ?? 0;
        $omsetTahunIni       = $omsetSistemTahunIni + $omsetManualTahunIni;

        // ========== TARGET ADJUSTED (carry forward bulanan) ==========
        $bulanSekarang        = Carbon::now()->month;
        $sisaTargetSebelumnya = 0;

        for ($b = 1; $b < $bulanSekarang; $b++) {
            $omsetSistemBulanLalu = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $currentYear)
                ->whereMonth('pengiriman.tanggal_kirim', $b)
                ->whereNull('pengiriman.deleted_at')
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

            $omsetSistemWeek = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereBetween('pengiriman.tanggal_kirim', [$weekStartLoop->startOfDay(), $weekEndLoop->endOfDay()])
                ->whereNull('pengiriman.deleted_at')
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

        // ========== PENGIRIMAN MINGGU INI ==========
        $pengirimanMingguIni = Pengiriman::with(['forecast:id,total_qty_forecast', 'order.klien', 'purchasing'])
            ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereBetween('tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
            ->get();

        $pengirimanNormalList               = [];
        $pengirimanBongkarSebagianList      = [];
        $pengirimanNormalMingguIni          = 0;
        $pengirimanBongkarSebagianMingguIni = 0;

        foreach ($pengirimanMingguIni as $pengiriman) {
            if ($pengiriman->forecast && $pengiriman->forecast->total_qty_forecast > 0) {
                $percentage = ($pengiriman->total_qty_kirim / $pengiriman->forecast->total_qty_forecast) * 100;

                $item = [
                    'id'                 => $pengiriman->id,
                    'po_number'          => $pengiriman->order->po_number ?? 'N/A',
                    'tanggal_kirim'      => $pengiriman->tanggal_kirim,
                    'klien'              => $pengiriman->order->klien->nama ?? 'N/A',
                    'cabang'             => $pengiriman->order->klien->cabang ?? null,
                    'total_qty_kirim'    => $pengiriman->total_qty_kirim,
                    'total_qty_forecast' => $pengiriman->forecast->total_qty_forecast,
                    'percentage'         => round($percentage, 2),
                    'status'             => $pengiriman->status,
                    'purchasing'         => $pengiriman->purchasing->nama ?? 'N/A',
                ];

                if ($percentage > 70) {
                    $pengirimanNormalMingguIni++;
                    $pengirimanNormalList[] = $item;
                } elseif ($percentage > 0) {
                    $pengirimanBongkarSebagianMingguIni++;
                    $pengirimanBongkarSebagianList[] = $item;
                }
            } else {
                $pengirimanNormalMingguIni++;
                $pengirimanNormalList[] = [
                    'id'                 => $pengiriman->id,
                    'po_number'          => $pengiriman->order->po_number ?? 'N/A',
                    'tanggal_kirim'      => $pengiriman->tanggal_kirim,
                    'klien'              => $pengiriman->order->klien->nama ?? 'N/A',
                    'cabang'             => $pengiriman->order->klien->cabang ?? null,
                    'total_qty_kirim'    => $pengiriman->total_qty_kirim,
                    'total_qty_forecast' => 0,
                    'percentage'         => 0,
                    'status'             => $pengiriman->status,
                    'purchasing'         => $pengiriman->purchasing->nama ?? 'N/A',
                ];
            }
        }

        $totalQtyPengirimanMingguIni = Pengiriman::leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->sum(DB::raw('COALESCE(invoice_penagihan.qty_after_refraksi, pengiriman.total_qty_kirim)'));

        // ========== PENGIRIMAN GAGAL ==========
        $pengirimanGagalList = Pengiriman::with(['order.klien', 'purchasing'])
            ->where('status', 'gagal')
            ->where(function ($query) use ($weekStart, $weekEnd) {
                $query->where(function ($q) use ($weekStart, $weekEnd) {
                    $q->whereNotNull('tanggal_kirim')
                      ->whereBetween('tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()]);
                })->orWhere(function ($q) use ($weekStart, $weekEnd) {
                    $q->whereNull('tanggal_kirim')
                      ->whereBetween('updated_at', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()]);
                });
            })
            ->get()
            ->map(fn($pengiriman) => [
                'id'              => $pengiriman->id,
                'po_number'       => $pengiriman->order->po_number ?? 'N/A',
                'tanggal_kirim'   => $pengiriman->tanggal_kirim,
                'tanggal_gagal'   => $pengiriman->updated_at,
                'klien'           => $pengiriman->order->klien->nama ?? 'N/A',
                'cabang'          => $pengiriman->order->klien->cabang ?? null,
                'total_qty_kirim' => $pengiriman->total_qty_kirim,
                'catatan'         => $pengiriman->catatan ?? '-',
                'status'          => $pengiriman->status,
                'purchasing'      => $pengiriman->purchasing->nama ?? 'N/A',
            ])
            ->toArray();

        // ========== ORDER BULAN INI ==========
        $orderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->count();

        $nilaiOrderBulanIni = DB::table('orders')
            ->leftJoin('order_details', function ($join) {
                $join->on('order_details.order_id', '=', 'orders.id')->whereNull('order_details.deleted_at');
            })
            ->whereNull('orders.deleted_at')
            ->whereYear('orders.tanggal_order', Carbon::now()->year)
            ->whereMonth('orders.tanggal_order', Carbon::now()->month)
            ->sum(DB::raw('COALESCE(order_details.original_qty, order_details.qty) * order_details.harga_jual'));

        // ========== MARGIN MINGGU INI ==========
        $pengirimanMarginMingguIni = Pengiriman::with([
            'purchasing:id,nama',
            'order.klien:id,nama,cabang',
            'order.winner.user:id,nama',
            'pengirimanDetails.bahanBakuSupplier.supplier:id,nama',
            'pengirimanDetails.bahanBakuSupplier:id,nama,supplier_id',
            'pengirimanDetails.orderDetail.bahanBakuKlien:id,nama',
            'approvalPembayaran',
            'invoicePenagihan',
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereBetween('tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
        ->orderBy('tanggal_kirim', 'asc')
        ->get();

        $hasilMingguIni          = $this->hitungMarginDariPengiriman($pengirimanMarginMingguIni, withMeta: true);
        $topMarginMingguIni      = $hasilMingguIni['rows'];
        $totalMarginMingguIni    = $hasilMingguIni['totalMargin'];
        $totalHargaBeliMingguIni = $hasilMingguIni['totalHargaBeli'];
        $totalHargaJualMingguIni = $hasilMingguIni['totalHargaJual'];

        $grossMarginMingguIni = $totalHargaJualMingguIni > 0
            ? ($totalMarginMingguIni / $totalHargaJualMingguIni) * 100
            : 0;

        // ========== GROSS MARGIN BULAN INI ==========
        $pengirimanMarginBulanIni = Pengiriman::with([
            'pengirimanDetails.bahanBakuSupplier',
            'pengirimanDetails.orderDetail',
            'approvalPembayaran',
            'invoicePenagihan',
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereYear('tanggal_kirim', Carbon::now()->year)
        ->whereMonth('tanggal_kirim', Carbon::now()->month)
        ->get();

        $hasilBulanIni          = $this->hitungMarginDariPengiriman($pengirimanMarginBulanIni);
        $totalMarginBulanIni    = $hasilBulanIni['totalMargin'];
        $totalHargaBeliBulanIni = $hasilBulanIni['totalHargaBeli'];
        $totalHargaJualBulanIni = $hasilBulanIni['totalHargaJual'];

        $grossMarginBulanIni = $totalHargaJualBulanIni > 0
            ? ($totalMarginBulanIni / $totalHargaJualBulanIni) * 100
            : 0;

        return view('pages.dashboard', compact(
            'targetMingguan', 'targetBulanan', 'targetTahunan',
            'targetMingguanAdjusted', 'targetBulananAdjusted',
            'omsetMingguIni', 'omsetBulanIni', 'omsetTahunIni',
            'omsetSistemMingguIni', 'omsetManualMingguIni',
            'omsetSistemBulanIni', 'omsetManualBulanIni',
            'progressMinggu', 'progressBulan', 'progressTahun',
            'totalOutstanding', 'totalQtyOutstanding', 'poBerjalan',
            'pengirimanNormalMingguIni', 'pengirimanBongkarSebagianMingguIni',
            'totalQtyPengirimanMingguIni',
            'pengirimanNormalList', 'pengirimanBongkarSebagianList', 'pengirimanGagalList',
            'orderBulanIni', 'nilaiOrderBulanIni',
            'topMarginMingguIni', 'grossMarginMingguIni', 'totalMarginMingguIni',
            'grossMarginBulanIni',
            'rangeStartLabel', 'rangeEndLabel', 'useCustomRange',
            'startDateParam', 'endDateParam'
        ));
    }

    // =========================================================================
    // CHART AJAX ENDPOINTS (tidak ada perubahan logika)
    // =========================================================================

    public function getOmsetPerKlien(Request $request)
    {
        $tahun  = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');

        $topKlienQuery = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->select('kliens.id as klien_id', 'kliens.nama', 'kliens.cabang', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $tahun)
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('kliens.deleted_at')
            ->groupBy('pengiriman.id', 'kliens.id', 'kliens.nama', 'kliens.cabang');

        if (!empty($search)) {
            $topKlienQuery->where(fn($q) => $q->where('kliens.nama', 'like', "%{$search}%")->orWhere('kliens.cabang', 'like', "%{$search}%"));
        }

        $topKlien = $topKlienQuery->get()
            ->groupBy('klien_id')
            ->map(function ($items) {
                $first = $items->first();
                return (object) ['klien_id' => $first->klien_id, 'nama' => $first->nama, 'cabang' => $first->cabang, 'total' => $items->sum('omset_pengiriman')];
            })
            ->sortByDesc('total')
            ->values();

        $monthColors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#F97316','#14B8A6','#F43F5E','#8B5CF6','#6366F1'];
        $monthNames  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        $klienNames = [];
        $datasets   = [];

        foreach ($topKlien as $klien) {
            $cabang      = trim((string)($klien->cabang ?? ''));
            $klienNames[] = (string)$klien->nama . ($cabang !== '' ? ' - ' . $cabang : '');
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthData = [];
            foreach ($topKlien as $klien) {
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
                    ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                    ->groupBy('pengiriman.id')
                    ->get()->sum('omset_pengiriman');

                $monthData[] = floatval($omsetBulan);
            }

            $datasets[] = ['label' => $monthNames[$bulan - 1], 'data' => $monthData, 'backgroundColor' => $monthColors[$bulan - 1], 'borderColor' => $monthColors[$bulan - 1], 'borderWidth' => 1];
        }

        return response()->json(['klien_names' => $klienNames, 'datasets' => $datasets]);
    }

    public function getOmsetPerSupplier(Request $request)
    {
        $tahun  = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');

        $topSupplierQuery = DB::table('pengiriman')
            ->leftJoin('approval_pembayaran', 'pengiriman.id', '=', 'approval_pembayaran.pengiriman_id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->select('suppliers.id as supplier_id', 'suppliers.nama', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(NULLIF(MAX(approval_pembayaran.subtotal),0), NULLIF(MAX(approval_pembayaran.amount_after_refraksi),0), SUM(pengiriman_details.total_harga)) as omset_pengiriman'))
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $tahun)
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->groupBy('pengiriman.id', 'suppliers.id', 'suppliers.nama');

        if (!empty($search)) {
            $topSupplierQuery->where(fn($q) => $q->where('suppliers.nama', 'like', "%{$search}%")->orWhere('suppliers.alamat', 'like', "%{$search}%"));
        }

        $topSupplier = $topSupplierQuery->get()
            ->groupBy('supplier_id')
            ->map(function ($items) {
                $first = $items->first();
                return (object) ['supplier_id' => $first->supplier_id, 'nama' => $first->nama, 'total' => $items->sum('omset_pengiriman')];
            })
            ->sortByDesc('total')
            ->values();

        $monthColors   = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#F97316','#14B8A6','#F43F5E','#8B5CF6','#6366F1'];
        $monthNames    = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $supplierNames = $topSupplier->pluck('nama')->toArray();
        $datasets      = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthData = [];
            foreach ($topSupplier as $supplier) {
                $omsetBulan = DB::table('pengiriman')
                    ->leftJoin('approval_pembayaran', 'pengiriman.id', '=', 'approval_pembayaran.pengiriman_id')
                    ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                    ->where('bahan_baku_supplier.supplier_id', $supplier->supplier_id)
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereYear('pengiriman.tanggal_kirim', $tahun)
                    ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                    ->whereNull('pengiriman.deleted_at')
                    ->select('pengiriman.id', DB::raw('COALESCE(NULLIF(MAX(approval_pembayaran.subtotal),0), NULLIF(MAX(approval_pembayaran.amount_after_refraksi),0), SUM(pengiriman_details.total_harga)) as omset_pengiriman'))
                    ->groupBy('pengiriman.id')
                    ->get()->sum('omset_pengiriman');

                $monthData[] = floatval($omsetBulan);
            }

            $datasets[] = ['label' => $monthNames[$bulan - 1], 'data' => $monthData, 'backgroundColor' => $monthColors[$bulan - 1], 'borderColor' => $monthColors[$bulan - 1], 'borderWidth' => 1];
        }

        return response()->json(['supplier_names' => $supplierNames, 'datasets' => $datasets]);
    }

    public function getOmsetPerBahanBaku(Request $request)
    {
        $tahun  = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');

        $topBahanBakuRaw = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->join('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
            ->select('bahan_baku_klien.id as bahan_baku_id', 'bahan_baku_klien.nama', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $tahun)
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('bahan_baku_klien.deleted_at')
            ->groupBy('pengiriman.id', 'bahan_baku_klien.id', 'bahan_baku_klien.nama');

        if (!empty($search)) {
            $topBahanBakuRaw->where(fn($q) => $q->where('bahan_baku_klien.nama', 'like', "%{$search}%")->orWhere('bahan_baku_klien.spesifikasi', 'like', "%{$search}%"));
        }

        $topBahanBaku = $topBahanBakuRaw->get()
            ->groupBy(fn($row) => $this->normalizeBahanBakuName($row->nama))
            ->map(function ($items, $normalizedName) {
                return (object) ['nama' => $normalizedName, 'bahan_baku_ids' => $items->pluck('bahan_baku_id')->unique()->values()->all(), 'total' => $items->sum('omset_pengiriman')];
            })
            ->filter(fn($item) => $item->total > 0)
            ->sortByDesc('total')
            ->values();

        $monthColors    = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#F97316','#14B8A6','#F43F5E','#8B5CF6','#6366F1'];
        $monthNames     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $bahanBakuNames = $topBahanBaku->pluck('nama')->toArray();
        $datasets       = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthData = [];
            foreach ($topBahanBaku as $bahanBaku) {
                $omsetBulan = DB::table('pengiriman')
                    ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                    ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->join('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->whereIn('order_details.bahan_baku_klien_id', $bahanBaku->bahan_baku_ids)
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereYear('pengiriman.tanggal_kirim', $tahun)
                    ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                    ->whereNull('pengiriman.deleted_at')
                    ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                    ->groupBy('pengiriman.id')
                    ->get()->sum('omset_pengiriman');

                $monthData[] = floatval($omsetBulan);
            }

            $datasets[] = ['label' => $monthNames[$bulan - 1], 'data' => $monthData, 'backgroundColor' => $monthColors[$bulan - 1], 'borderColor' => $monthColors[$bulan - 1], 'borderWidth' => 1];
        }

        return response()->json(['bahan_baku_names' => $bahanBakuNames, 'datasets' => $datasets]);
    }

    // =========================================================================
    // DOWNLOAD MARGIN MINGGU INI — PDF
    // =========================================================================

    public function downloadMarginMingguIniPdf()
    {
        ['week' => $currentWeekOfMonth, 'startOfMonth' => $startOfMonth] = $this->getCurrentWeekOfMonth();

        $startOfWeek = $currentWeekOfMonth == 1
            ? $startOfMonth->copy()
            : $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);

        $endOfWeek = $currentWeekOfMonth == 4
            ? $startOfMonth->copy()->endOfMonth()
            : $startOfWeek->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());

        // ---- Margin minggu ini ----
        $pengirimanMargin = Pengiriman::with([
            'purchasing:id,nama',
            'order.klien:id,nama,cabang',
            'order.winner.user:id,nama',
            'pengirimanDetails.bahanBakuSupplier.supplier:id,nama',
            'pengirimanDetails.bahanBakuSupplier:id,nama,supplier_id',
            'pengirimanDetails.orderDetail.bahanBakuKlien:id,nama',
            'approvalPembayaran',
            'invoicePenagihan',
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereBetween('tanggal_kirim', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
        ->orderBy('tanggal_kirim', 'asc')
        ->get();

        $hasilPdf                = $this->hitungMarginDariPengiriman($pengirimanMargin, withMeta: true);
        $marginDataMingguIni     = $hasilPdf['rows'];
        $totalMarginMingguIni    = $hasilPdf['totalMargin'];
        $totalHargaBeliMingguIni = $hasilPdf['totalHargaBeli'];
        $totalHargaJualMingguIni = $hasilPdf['totalHargaJual'];

        $grossMarginMingguIni = $totalHargaJualMingguIni > 0
            ? ($totalMarginMingguIni / $totalHargaJualMingguIni) * 100
            : 0;

        // ---- Gross margin bulan ini ----
        $pengirimanMarginBulanIni = Pengiriman::with([
            'pengirimanDetails.bahanBakuSupplier',
            'pengirimanDetails.orderDetail',
            'approvalPembayaran',
            'invoicePenagihan',
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereYear('tanggal_kirim', Carbon::now()->year)
        ->whereMonth('tanggal_kirim', Carbon::now()->month)
        ->get();

        $hasilBulanPdf          = $this->hitungMarginDariPengiriman($pengirimanMarginBulanIni);
        $totalMarginBulanIni    = $hasilBulanPdf['totalMargin'];
        $totalHargaJualBulanIni = $hasilBulanPdf['totalHargaJual'];

        $grossMarginBulanIni = $totalHargaJualBulanIni > 0
            ? ($totalMarginBulanIni / $totalHargaJualBulanIni) * 100
            : 0;

        $data = [
            'marginData'          => $marginDataMingguIni,
            'totalMargin'         => $totalMarginMingguIni,
            'totalHargaBeli'      => $totalHargaBeliMingguIni,
            'totalHargaJual'      => $totalHargaJualMingguIni,
            'grossMargin'         => $grossMarginMingguIni,
            'grossMarginBulanIni' => $grossMarginBulanIni,
            'totalMarginBulanIni' => $totalMarginBulanIni,
            'currentMonth'        => Carbon::now()->format('F Y'),
            'startDate'           => $startOfWeek->format('d/m/Y'),
            'endDate'             => $endOfWeek->format('d/m/Y'),
            'currentWeek'         => $currentWeekOfMonth,
            'generatedAt'         => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pages.dashboard.pdf.margin-minggu-ini', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Margin_Minggu_' . $currentWeekOfMonth . '_' . Carbon::now()->format('M_Y') . '.pdf');
    }

    // =========================================================================
    // DOWNLOAD MARGIN MINGGU INI — EXCEL
    // =========================================================================

    public function downloadMarginMingguIniExcel()
    {
        ['week' => $currentWeekOfMonth, 'startOfMonth' => $startOfMonth] = $this->getCurrentWeekOfMonth();

        $startOfWeek = $currentWeekOfMonth == 1
            ? $startOfMonth->copy()
            : $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);

        $endOfWeek = $currentWeekOfMonth == 4
            ? $startOfMonth->copy()->endOfMonth()
            : $startOfWeek->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());

        $pengirimanMargin = Pengiriman::with([
            'purchasing:id,nama',
            'order.klien:id,nama,cabang',
            'order.winner.user:id,nama',
            'pengirimanDetails.bahanBakuSupplier.supplier:id,nama',
            'pengirimanDetails.bahanBakuSupplier:id,nama,supplier_id',
            'pengirimanDetails.orderDetail.bahanBakuKlien:id,nama',
            'approvalPembayaran',
            'invoicePenagihan',
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereBetween('tanggal_kirim', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
        ->orderBy('tanggal_kirim', 'asc')
        ->get();

        $hasilExcel              = $this->hitungMarginDariPengiriman($pengirimanMargin, withMeta: true);
        $totalMarginMingguIni    = $hasilExcel['totalMargin'];
        $totalHargaBeliMingguIni = $hasilExcel['totalHargaBeli'];
        $totalHargaJualMingguIni = $hasilExcel['totalHargaJual'];

        // Format tanggal_kirim jadi string untuk Excel
        $marginDataMingguIni = array_map(function ($row) {
            $row['tanggal_kirim'] = ($row['tanggal_kirim'] instanceof Carbon
                ? $row['tanggal_kirim']
                : Carbon::parse($row['tanggal_kirim'])
            )->format('d/m/Y');
            return $row;
        }, $hasilExcel['rows']);

        $grossMarginMingguIni = $totalHargaJualMingguIni > 0
            ? ($totalMarginMingguIni / $totalHargaJualMingguIni) * 100
            : 0;

        $profitCount = count(array_filter($marginDataMingguIni, fn($item) => $item['margin'] >= 0));
        $lossCount   = count($marginDataMingguIni) - $profitCount;

        $totals = [
            'totalQty'              => array_sum(array_column($marginDataMingguIni, 'qty')),
            'totalHargaBeli'        => $totalHargaBeliMingguIni,
            'totalHargaJual'        => $totalHargaJualMingguIni,
            'totalMargin'           => $totalMarginMingguIni,
            'grossMarginPercentage' => $grossMarginMingguIni,
            'profitCount'           => $profitCount,
            'lossCount'             => $lossCount,
        ];

        $filters = [
            'start_date' => $startOfWeek->format('Y-m-d'),
            'end_date'   => $endOfWeek->format('Y-m-d'),
        ];

        return Excel::download(
            new MarginExport($marginDataMingguIni, $totals, $filters),
            'Margin_Minggu_' . $currentWeekOfMonth . '_' . Carbon::now()->format('M_Y') . '.xlsx'
        );
    }
}