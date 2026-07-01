<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use App\Models\Klien;
use App\Models\Order;
use App\Models\Pengiriman;
use App\Models\TargetOmset;
use App\Models\OmsetManual;
use App\Models\TargetOmsetProcurement;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\BahanBakuKlien;
use App\Models\InvoicePenagihan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ChartService;
use Illuminate\Support\Facades\Auth;

class OmsetController extends Controller
{
    /**
     * Helper: exclude pengiriman yang semua invoice-nya berstatus "digabung".
     * Pengiriman tanpa invoice tetap dimasukkan (pakai fallback qty * harga_jual).
     */
    private function applyValidInvoiceFilter($query)
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

    /**
     * Subquery invoice dengan amount_after_refraksi — dipakai berulang di seluruh controller.
     * Menghasilkan string SQL siap pakai di DB::raw().
     */
    private function invoiceSubquery(): string
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
     * Ekspresi COALESCE untuk omset: prioritas amount_after_refraksi → subtotal → qty×harga_jual.
     */
    private function omsetExpr(): \Illuminate\Database\Query\Expression
    {
        return DB::raw('COALESCE(
            NULLIF(MAX(invoice_penagihan.amount_after_refraksi), 0),
            NULLIF(MAX(invoice_penagihan.subtotal), 0),
            SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
        ) as omset_pengiriman');
    }

    /**
     * Helper: build base omset query dengan join standar.
     */
    private function calculateOmsetSistem($query)
    {
        $q = DB::table('pengiriman')
            ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereNull('pengiriman.deleted_at')
            ->mergeBindings($query)
            ->select('pengiriman.id', $this->omsetExpr())
            ->groupBy('pengiriman.id');

        $this->applyValidInvoiceFilter($q);

        return $q->get()->sum('omset_pengiriman');
    }

    public function index(Request $request)
    {
        $title     = 'Omset';
        $activeTab = 'omset';

        $selectedYearTarget = $request->get('tahun_target', Carbon::now()->year);

        $availableYearsTarget = TargetOmset::orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        if (empty($availableYearsTarget)) {
            $availableYearsTarget = [Carbon::now()->year];
        }
        if (!in_array($selectedYearTarget, $availableYearsTarget)) {
            $selectedYearTarget = $availableYearsTarget[0] ?? Carbon::now()->year;
        }

        // ===== Helper closure: base query dengan join standar =====
        $baseOmsetQuery = function () {
            $q = DB::table('pengiriman')
                ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereNull('pengiriman.deleted_at');
            $this->applyValidInvoiceFilter($q);
            return $q;
        };

        // ===== Helper closure: eksekusi query dan sum omset =====
        $sumOmset = function ($q) {
            return $q->select('pengiriman.id', $this->omsetExpr())
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');
        };

        // ========== TOTAL OMSET (all time) ==========
        $totalOmsetSistem = $sumOmset($baseOmsetQuery());
        $totalOmsetManual = OmsetManual::sum('omset_manual') ?? 0;
        $totalOmset       = $totalOmsetSistem + $totalOmsetManual;

        // ========== SUMMARY CARDS (SELECTED YEAR TARGET) ==========
        $omsetTahunIniSistemSummary = $sumOmset(
            $baseOmsetQuery()->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
        );
        $omsetTahunIniManualSummary = OmsetManual::where('tahun', $selectedYearTarget)->sum('omset_manual') ?? 0;
        $omsetTahunIniSummary       = $omsetTahunIniSistemSummary + $omsetTahunIniManualSummary;

        $omsetBulanIniSistemSummary = $sumOmset(
            $baseOmsetQuery()
                ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month)
        );
        $omsetBulanIniManualSummary = OmsetManual::where('tahun', Carbon::now()->year)
            ->where('bulan', Carbon::now()->month)
            ->value('omset_manual') ?? 0;
        $omsetBulanIniSummary = $omsetBulanIniSistemSummary + $omsetBulanIniManualSummary;

        // ========== TARGET ANALYSIS (SELECTED YEAR) ==========
        $omsetSistemTahunIni = $sumOmset(
            $baseOmsetQuery()->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
        );
        $omsetManualTahunIni = OmsetManual::where('tahun', $selectedYearTarget)->sum('omset_manual') ?? 0;
        $omsetTahunIni       = $omsetSistemTahunIni + $omsetManualTahunIni;

        $omsetSistemBulanIni = $sumOmset(
            $baseOmsetQuery()
                ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
                ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month)
        );
        $omsetManualBulanIni = OmsetManual::where('tahun', $selectedYearTarget)
            ->where('bulan', Carbon::now()->month)
            ->value('omset_manual') ?? 0;
        $omsetBulanIni = $omsetSistemBulanIni + $omsetManualBulanIni;

        // Get Target Omset for selected year
        $targetOmset    = TargetOmset::getTargetForYear($selectedYearTarget);
        $targetTahunan  = $targetOmset->target_tahunan  ?? 0;
        $targetBulanan  = $targetOmset->target_bulanan  ?? 0;
        $targetMingguan = $targetOmset->target_mingguan ?? 0;

        // ========== OMSET MINGGU INI ==========
        $today      = Carbon::now();
        $dayOfMonth = $today->day;

        if ($dayOfMonth >= 1 && $dayOfMonth <= 7) {
            $currentWeekOfMonth = 1;
        } elseif ($dayOfMonth >= 8 && $dayOfMonth <= 14) {
            $currentWeekOfMonth = 2;
        } elseif ($dayOfMonth >= 15 && $dayOfMonth <= 21) {
            $currentWeekOfMonth = 3;
        } else {
            $currentWeekOfMonth = 4;
        }

        $startOfMonth = Carbon::now()->startOfMonth();

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

        $omsetSistemMingguIni = $sumOmset(
            $baseOmsetQuery()->whereBetween('pengiriman.tanggal_kirim', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
        );
        $omsetManualMingguIni = $omsetManualBulanIni / 4;
        $omsetMingguIni       = $omsetSistemMingguIni + $omsetManualMingguIni;

        // ========== TARGET ADJUSTED (carry forward bulanan) ==========
        $bulanSekarang        = Carbon::now()->month;
        $sisaTargetSebelumnya = 0;

        if ($selectedYearTarget == Carbon::now()->year) {
            for ($b = 1; $b < $bulanSekarang; $b++) {
                $omsetSistemBulanLalu = $sumOmset(
                    $baseOmsetQuery()
                        ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
                        ->whereMonth('pengiriman.tanggal_kirim', $b)
                );
                $omsetManualBulanLalu = OmsetManual::where('tahun', $selectedYearTarget)
                    ->where('bulan', $b)
                    ->value('omset_manual') ?? 0;
                $omsetTotalBulanLalu  = $omsetSistemBulanLalu + $omsetManualBulanLalu;
                $targetBulanLalu      = $targetBulanan + $sisaTargetSebelumnya;
                $selisihBulanLalu     = $omsetTotalBulanLalu - $targetBulanLalu;
                $sisaTargetSebelumnya = $selisihBulanLalu < 0 ? $targetBulanLalu - $omsetTotalBulanLalu : 0;
            }
        }

        $targetBulananAdjusted = $targetBulanan + $sisaTargetSebelumnya;
        $targetMingguanBase    = $targetBulananAdjusted / 4;

        // Target mingguan adjusted (carry forward mingguan)
        $sisaTargetMingguanSebelumnya = 0;

        if ($selectedYearTarget == Carbon::now()->year) {
            for ($w = 1; $w < $currentWeekOfMonth; $w++) {
                $weekStart = $w == 1 ? $startOfMonth->copy() : $startOfMonth->copy()->addDays(($w - 1) * 7);
                $weekEnd   = $w == 4 ? $startOfMonth->copy()->endOfMonth() : $weekStart->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());

                $omsetSistemWeek = $sumOmset(
                    $baseOmsetQuery()->whereBetween('pengiriman.tanggal_kirim', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                );
                $omsetManualWeek              = $omsetManualBulanIni / 4;
                $omsetTotalWeek               = $omsetSistemWeek + $omsetManualWeek;
                $targetWeek                   = $targetMingguanBase + $sisaTargetMingguanSebelumnya;
                $selisihWeek                  = $omsetTotalWeek - $targetWeek;
                $sisaTargetMingguanSebelumnya = $selisihWeek < 0 ? $targetWeek - $omsetTotalWeek : 0;
            }
        }

        $targetMingguanAdjusted = $targetMingguanBase + $sisaTargetMingguanSebelumnya;

        $progressMinggu = $targetMingguanAdjusted > 0 ? ($omsetMingguIni / $targetMingguanAdjusted) * 100 : 0;
        $progressBulan  = $targetBulananAdjusted  > 0 ? ($omsetBulanIni  / $targetBulananAdjusted)  * 100 : 0;
        $progressTahun  = $targetTahunan          > 0 ? ($omsetTahunIni  / $targetTahunan)          * 100 : 0;

        // ========== REKAP BULANAN ==========
        $rekapBulanan        = [];
        $sisaTargetAkumulasi = 0;

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $omsetSistem = $sumOmset(
                $baseOmsetQuery()
                    ->whereYear('pengiriman.tanggal_kirim', $selectedYearTarget)
                    ->whereMonth('pengiriman.tanggal_kirim', $bulan)
            );

            $omsetManualData = OmsetManual::where('tahun', $selectedYearTarget)->where('bulan', $bulan)->first();
            $omsetManual     = $omsetManualData ? (float)$omsetManualData->omset_manual : 0;
            $omsetBulan      = $omsetSistem + $omsetManual;

            $targetBulananFlat         = $targetBulanan;
            $progressBulanIni          = $targetBulananFlat > 0 ? ($omsetBulan / $targetBulananFlat) * 100 : 0;
            $targetBulananAdjustedRekap = $targetBulanan + $sisaTargetAkumulasi;
            $selisihBulanIni           = $omsetBulan - $targetBulananAdjustedRekap;

            if ($selisihBulanIni < 0) {
                $sisaTargetAkumulasi = $targetBulananAdjustedRekap - $omsetBulan;
            } else {
                $sisaTargetAkumulasi = 0;
            }

            $targetMingguanBaseFlat = $targetBulananFlat / 4;
            $omsetManualPerMinggu   = $omsetManual / 4;
            $mingguanDetail         = [];
            $startDate              = Carbon::create($selectedYearTarget, $bulan, 1)->startOfDay();
            $endDate                = $startDate->copy()->endOfMonth();
            $sisaTargetMingguanAkumulasi = 0;

            for ($minggu = 1; $minggu <= 4; $minggu++) {
                if ($minggu == 1) {
                    $weekStart = $startDate->copy();
                } else {
                    $weekStart = $startDate->copy()->addDays(($minggu - 1) * 7);
                }

                if ($minggu == 4) {
                    $weekEnd = $endDate->copy();
                } else {
                    $weekEnd = $weekStart->copy()->addDays(6)->min($endDate);
                }

                if ($weekStart > $endDate) break;

                $omsetSistemMinggu = $sumOmset(
                    $baseOmsetQuery()->whereBetween('pengiriman.tanggal_kirim', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                );
                $omsetMinggu           = $omsetSistemMinggu + $omsetManualPerMinggu;
                $targetMingguIniFlat   = $targetMingguanBaseFlat;
                $progressMingguIni     = $targetMingguIniFlat > 0 ? ($omsetMinggu / $targetMingguIniFlat) * 100 : 0;
                $targetMingguIniAdjusted = $targetMingguanBaseFlat + $sisaTargetMingguanAkumulasi;
                $selisihMingguIni      = $omsetMinggu - $targetMingguIniAdjusted;

                if ($selisihMingguIni < 0) {
                    $sisaTargetMingguanAkumulasi = $targetMingguIniAdjusted - $omsetMinggu;
                } else {
                    $sisaTargetMingguanAkumulasi = 0;
                }

                $mingguanDetail[$minggu] = [
                    'omset'   => $omsetMinggu,
                    'progress' => $progressMingguIni,
                    'target'  => $targetMingguIniFlat,
                    'tanggal' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M'),
                ];
            }

            $rekapBulanan[$bulan] = [
                'realisasi'       => $omsetBulan,
                'omset_bulan_ini' => $omsetBulan,
                'omset_sistem'    => $omsetSistem,
                'omset_manual'    => $omsetManual,
                'target'          => $targetBulananFlat,
                'progress'        => $progressBulanIni,
                'selisih'         => $selisihBulanIni,
                'mingguan'        => $mingguanDetail,
            ];
        }

        // ========== FILTER PERIODE ==========
        $periode          = $request->get('periode_marketing', 'all');
        $periodeProcurement = $request->get('periode_procurement', 'all');
        $periodeKlien     = $request->get('periode_klien', 'all');
        $periodeSupplier  = $request->get('periode_supplier', 'all');

        // ===== AJAX: target_analysis =====
        if ($request->ajax() && $request->get('ajax') === 'target_analysis') {
            return response()->json([
                'selectedYearTarget' => $selectedYearTarget,
                'targetTahunan'      => $targetTahunan,
                'targetBulanan'      => $targetBulanan,
                'targetMingguan'     => $targetMingguan,
                'omsetTahunIni'      => $omsetTahunIni,
                'omsetBulanIni'      => $omsetBulanIni,
                'omsetMingguIni'     => $omsetMingguIni,
                'progressMinggu'     => $progressMinggu,
                'progressBulan'      => $progressBulan,
                'progressTahun'      => $progressTahun,
                'rekapBulanan'       => $rekapBulanan,
            ]);
        }

        // ===== AJAX: get_target =====
        if ($request->ajax() && $request->get('ajax') === 'get_target') {
            $tahun           = $request->get('tahun', Carbon::now()->year);
            $targetOmsetData = TargetOmset::getTargetForYear($tahun);
            return response()->json([
                'target_tahunan'  => $targetOmsetData->target_tahunan  ?? 0,
                'target_bulanan'  => $targetOmsetData->target_bulanan  ?? 0,
                'target_mingguan' => $targetOmsetData->target_mingguan ?? 0,
            ]);
        }

        // ===== AJAX: get_omset_sistem =====
        if ($request->ajax() && $request->get('ajax') === 'get_omset_sistem') {
            $tahun = $request->get('tahun', Carbon::now()->year);
            $bulan = $request->get('bulan', Carbon::now()->month);

            $omsetSistem = $sumOmset(
                $baseOmsetQuery()
                    ->whereYear('pengiriman.tanggal_kirim', $tahun)
                    ->whereMonth('pengiriman.tanggal_kirim', $bulan)
            );

            return response()->json(['omset_sistem' => $omsetSistem]);
        }

        // ===== AJAX: omset_per_klien =====
        if ($request->ajax() && $request->get('ajax') === 'omset_per_klien') {
            return response()->json(
                ChartService::getOmsetPerKlienChart(
                    $request->get('tahun', Carbon::now()->year),
                    $request->get('search', '')
                )
            );
        }

        // ===== AJAX: omset_per_supplier =====
        if ($request->ajax() && $request->get('ajax') === 'omset_per_supplier') {
            return response()->json(
                ChartService::getOmsetPerSupplierChart(
                    $request->get('tahun', Carbon::now()->year),
                    $request->get('search', '')
                )
            );
        }

        // ===== AJAX: omset_per_bahan_baku =====
        if ($request->ajax() && $request->get('ajax') === 'omset_per_bahan_baku') {
            return response()->json(
                ChartService::getOmsetPerBahanBakuChart(
                    $request->get('tahun', Carbon::now()->year),
                    $request->get('search', '')
                )
            );
        }

        // ===== AJAX: top_klien =====
        if ($request->ajax() && $request->get('ajax') === 'top_klien') {
            $topKlienQuery = DB::table('pengiriman')
                ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
                ->select(
                    'kliens.id as klien_id', 'kliens.nama', 'kliens.cabang', 'pengiriman.id as pengiriman_id',
                    $this->omsetExpr()
                )
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('kliens.deleted_at');

            $this->applyValidInvoiceFilter($topKlienQuery);
            $this->applyPeriodeFilter($topKlienQuery, $periodeKlien, $request, 'klien');

            $topKlienQuery->groupBy('pengiriman.id', 'kliens.id', 'kliens.nama', 'kliens.cabang');
            $topKlienData = $topKlienQuery->get();

            $topKlien = $topKlienData->groupBy('klien_id')->map(function ($items) {
                $first = $items->first();
                return (object)['nama' => $first->nama, 'cabang' => $first->cabang, 'total' => $items->sum('omset_pengiriman')];
            })->sortByDesc('total')->values();

            $data = $topKlien->map(function ($item) {
                return ['nama' => $item->nama ?? 'Unknown', 'cabang' => $item->cabang, 'total' => floatval($item->total ?? 0)];
            })->filter(fn($item) => $item['total'] > 0)->values();

            return response()->json($data);
        }

        // ===== AJAX: top_supplier =====
        if ($request->ajax() && $request->get('ajax') === 'top_supplier') {
            $topSupplierQuery = DB::table('pengiriman')
                ->leftJoin('approval_pembayaran', 'pengiriman.id', '=', 'approval_pembayaran.pengiriman_id')
                ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->select(
                    'suppliers.id as supplier_id', 'suppliers.nama', 'suppliers.alamat', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(
                        NULLIF(MAX(approval_pembayaran.subtotal), 0),
                        NULLIF(MAX(approval_pembayaran.amount_after_refraksi), 0),
                        SUM(pengiriman_details.total_harga)
                    ) as omset_pengiriman')
                )
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('suppliers.deleted_at');

            $this->applyPeriodeFilter($topSupplierQuery, $periodeSupplier, $request, 'supplier');
            $topSupplierQuery->groupBy('pengiriman.id', 'suppliers.id', 'suppliers.nama', 'suppliers.alamat');

            $topSupplierData = $topSupplierQuery->get();
            $topSupplier     = $topSupplierData->groupBy('supplier_id')->map(function ($items) {
                $first = $items->first();
                return (object)['nama' => $first->nama, 'alamat' => $first->alamat, 'total' => $items->sum('omset_pengiriman')];
            })->sortByDesc('total')->values();

            $data = $topSupplier->map(function ($item) {
                return ['nama' => $item->nama, 'cabang' => $item->alamat, 'total' => floatval($item->total ?? 0)];
            })->filter(fn($item) => $item['total'] > 0)->values();

            return response()->json($data);
        }

        // ===== AJAX: marketing =====
        if ($request->ajax() && $request->get('ajax') === 'marketing') {
            $omsetMarketingQuery = DB::table('pengiriman')
                ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
                ->join('users', 'order_winners.user_id', '=', 'users.id')
                ->select('order_winners.user_id', 'users.nama', 'pengiriman.id as pengiriman_id', $this->omsetExpr())
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil']);

            $this->applyValidInvoiceFilter($omsetMarketingQuery);
            $this->applyPeriodeFilter($omsetMarketingQuery, $periode, $request, 'marketing');

            $omsetMarketingQuery->groupBy('pengiriman.id', 'order_winners.user_id', 'users.nama');
            $omsetMarketingData = $omsetMarketingQuery->get();

            $omsetMarketing = $omsetMarketingData->groupBy('user_id')->map(function ($items) {
                $first = $items->first();
                return (object)['nama' => $first->nama, 'total' => $items->sum('omset_pengiriman')];
            })->values();

            $data = $omsetMarketing->map(fn($item) => [
                'nama'  => $item->nama ?? 'Unknown',
                'total' => floatval($item->total ?? 0),
            ])->filter(fn($item) => $item['total'] > 0)->values();

            return response()->json($data);
        }

        // ===== AJAX: procurement =====
        if ($request->ajax() && $request->get('ajax') === 'procurement') {
            $omsetProcurementQuery = DB::table('pengiriman')
                ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->select('pengiriman.purchasing_id', 'pengiriman.id as pengiriman_id', $this->omsetExpr())
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil']);

            $this->applyValidInvoiceFilter($omsetProcurementQuery);
            $this->applyPeriodeFilter($omsetProcurementQuery, $periodeProcurement, $request, 'procurement');

            $omsetProcurementQuery->groupBy('pengiriman.id', 'pengiriman.purchasing_id');
            $omsetProcurementDataRaw = $omsetProcurementQuery->get();

            $omsetProcurementData = $omsetProcurementDataRaw->groupBy('purchasing_id')->map(function ($items) {
                return (object)['purchasing_id' => $items->first()->purchasing_id, 'total' => $items->sum('omset_pengiriman')];
            })->values();

            $data = $omsetProcurementData->map(function ($item) {
                $purchasing = \App\Models\User::find($item->purchasing_id);
                return ['nama' => $purchasing ? $purchasing->nama : 'Unknown', 'total' => floatval($item->total ?? 0)];
            })->filter(fn($item) => $item['total'] > 0)->values();

            return response()->json($data);
        }

        // ===== NON-AJAX: Omset Marketing =====
        $omsetMarketingQuery = DB::table('pengiriman')
            ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->select('order_winners.user_id', 'users.nama', 'pengiriman.id as pengiriman_id', $this->omsetExpr())
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil']);

        $this->applyValidInvoiceFilter($omsetMarketingQuery);
        $this->applyPeriodeFilter($omsetMarketingQuery, $periode, $request, 'marketing');

        $omsetMarketingQuery->groupBy('pengiriman.id', 'order_winners.user_id', 'users.nama');
        $omsetMarketingDataRaw = $omsetMarketingQuery->get();

        $omsetMarketingGrouped = $omsetMarketingDataRaw->groupBy('user_id')->map(function ($items) {
            $first = $items->first();
            return (object)['user_id' => $first->user_id, 'nama' => $first->nama, 'total' => $items->sum('omset_pengiriman')];
        })->values();

        $omsetMarketing = $omsetMarketingGrouped->map(function ($item) {
            return (object)[
                'user_id' => $item->user_id,
                'creator' => (object)['nama' => $item->nama],
                'total'   => floatval($item->total ?? 0),
            ];
        })->filter(fn($item) => $item->total > 0)->values();

        // ===== NON-AJAX: Omset Procurement =====
        $omsetProcurementQuery = DB::table('pengiriman')
            ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select('pengiriman.purchasing_id', 'pengiriman.id as pengiriman_id', $this->omsetExpr())
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil']);

        $this->applyValidInvoiceFilter($omsetProcurementQuery);
        $this->applyPeriodeFilter($omsetProcurementQuery, $periodeProcurement, $request, 'procurement');

        $omsetProcurementQuery->groupBy('pengiriman.id', 'pengiriman.purchasing_id');
        $omsetProcurementDataRaw = $omsetProcurementQuery->get();

        $omsetProcurementDataGrouped = $omsetProcurementDataRaw->groupBy('purchasing_id')->map(function ($items) {
            return (object)['purchasing_id' => $items->first()->purchasing_id, 'total' => $items->sum('omset_pengiriman')];
        })->values();

        $omsetProcurement = $omsetProcurementDataGrouped->map(function ($item) {
            $purchasing = \App\Models\User::find($item->purchasing_id);
            return ['purchasing_id' => $item->purchasing_id, 'nama' => $purchasing ? $purchasing->nama : 'Unknown', 'total' => floatval($item->total ?? 0)];
        })->filter(fn($item) => $item['total'] > 0)->values();

        // ===== NON-AJAX: Top Klien =====
        $topKlienQuery = DB::table('pengiriman')
            ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->select(
                'kliens.id as klien_id', 'kliens.nama', 'kliens.cabang', 'pengiriman.id as pengiriman_id',
                $this->omsetExpr()
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('kliens.deleted_at');

        $this->applyValidInvoiceFilter($topKlienQuery);
        $this->applyPeriodeFilter($topKlienQuery, $periodeKlien, $request, 'klien');

        $topKlienQuery->groupBy('pengiriman.id', 'kliens.id', 'kliens.nama', 'kliens.cabang');
        $topKlienData = $topKlienQuery->get();

        $topKlien = $topKlienData->groupBy('klien_id')->map(function ($items) {
            $first = $items->first();
            return (object)[
                'klien_id' => $first->klien_id,
                'nama'     => $first->nama,
                'cabang'   => $first->cabang,
                'total'    => $items->sum('omset_pengiriman'),
            ];
        })->sortByDesc('total')->values();

        // ===== NON-AJAX: Top Supplier =====
        $topSupplierQuery = DB::table('pengiriman')
            ->leftJoin('approval_pembayaran', 'pengiriman.id', '=', 'approval_pembayaran.pengiriman_id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.id as supplier_id', 'suppliers.nama', 'suppliers.alamat', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(
                    NULLIF(MAX(approval_pembayaran.subtotal), 0),
                    NULLIF(MAX(approval_pembayaran.amount_after_refraksi), 0),
                    SUM(pengiriman_details.total_harga)
                ) as omset_pengiriman')
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('suppliers.deleted_at');

        $this->applyPeriodeFilter($topSupplierQuery, $periodeSupplier, $request, 'supplier');
        $topSupplierQuery->groupBy('pengiriman.id', 'suppliers.id', 'suppliers.nama', 'suppliers.alamat');
        $topSupplierData = $topSupplierQuery->get();

        $topSupplier = $topSupplierData->groupBy('supplier_id')->map(function ($items) {
            $first = $items->first();
            return (object)[
                'supplier_id' => $first->supplier_id,
                'nama'        => $first->nama,
                'alamat'      => $first->alamat,
                'total'       => $items->sum('omset_pengiriman'),
            ];
        })->sortByDesc('total')->values();

        return view('pages.laporan.omset', compact(
            'title', 'activeTab',
            'totalOmset',
            'omsetTahunIniSummary', 'omsetBulanIniSummary',
            'omsetTahunIni', 'omsetBulanIni', 'omsetMingguIni',
            'targetTahunan', 'targetBulanan', 'targetMingguan',
            'targetBulananAdjusted', 'targetMingguanAdjusted',
            'progressMinggu', 'progressBulan', 'progressTahun',
            'rekapBulanan',
            'selectedYearTarget', 'availableYearsTarget',
            'omsetMarketing', 'omsetProcurement',
            'periode', 'periodeProcurement', 'periodeKlien', 'periodeSupplier',
            'topKlien', 'topSupplier'
        ));
    }

    // =========================================================================
    // HELPER: Apply periode filter ke query (DRY)
    // =========================================================================

    /**
     * Terapkan filter periode ke query pengiriman.
     * $type: 'marketing' | 'procurement' | 'klien' | 'supplier'
     */
    private function applyPeriodeFilter($query, string $periode, Request $request, string $type): void
    {
        $startKey = "start_date_{$type}";
        $endKey   = "end_date_{$type}";

        if ($periode === 'tahun_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                  ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periode === 'custom' && $request->filled([$startKey, $endKey])) {
            $query->whereBetween('pengiriman.tanggal_kirim', [$request->get($startKey), $request->get($endKey)]);
        }
    }

    // =========================================================================
    // SET TARGET
    // =========================================================================

    public function setTarget(Request $request)
    {
        try {
            if (!Auth::check() || Auth::user()->role !== 'direktur') {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya Direktur yang dapat mengubah target omset.'], 403);
            }

            $request->validate([
                'target_tahunan' => 'required|numeric|min:0',
                'tahun'          => 'required|integer|min:2020|max:2100',
            ]);

            TargetOmset::setTarget($request->tahun, $request->target_tahunan, Auth::user()->nama ?? 'System');

            return response()->json(['success' => true, 'message' => 'Target omset berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getTargetByYear(Request $request)
    {
        try {
            $tahun  = $request->get('tahun', Carbon::now()->year);
            $target = TargetOmset::getTargetForYear($tahun);
            return response()->json(['success' => true, 'data' => $target]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAvailableYears()
    {
        try {
            $yearsWithTarget = TargetOmset::orderBy('tahun', 'desc')->pluck('tahun')->toArray();
            return response()->json(['success' => true, 'years_with_target' => $yearsWithTarget, 'current_year' => Carbon::now()->year]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // SAVE OMSET MANUAL
    // =========================================================================

    public function saveOmsetManual(Request $request)
    {
        try {
            if (Auth::user()->role !== 'direktur') {
                return response()->json(['success' => false, 'message' => 'Hanya Direktur yang dapat menginput omset manual'], 403);
            }

            $request->validate([
                'tahun'        => 'required|integer|min:2020|max:2100',
                'bulan'        => 'required|integer|min:1|max:12',
                'omset_manual' => 'required|numeric|min:0',
            ]);

            $omsetManual = OmsetManual::updateOrCreate(
                ['tahun' => $request->tahun, 'bulan' => $request->bulan],
                ['omset_manual' => $request->omset_manual, 'updated_by' => Auth::id()]
            );

            if ($omsetManual->wasRecentlyCreated) {
                $omsetManual->created_by = Auth::id();
                $omsetManual->save();
            }

            return response()->json(['success' => true, 'message' => 'Omset manual berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // MARKETING DETAILS & EXPORT
    // =========================================================================

    public function getMarketingDetails(Request $request)
    {
        $periode   = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $query = DB::table('pengiriman')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select(
                'users.nama as marketing_nama',
                'orders.no_order', 'orders.po_number',
                'kliens.nama as klien_nama',
                'pengiriman.id as pengiriman_id',
                $this->omsetExpr()
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'users.nama', 'orders.no_order', 'orders.po_number', 'kliens.nama');

        $this->applyValidInvoiceFilter($query);

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
        $periode   = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $query = DB::table('pengiriman')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('order_winners', 'orders.id', '=', 'order_winners.order_id')
            ->join('users', 'order_winners.user_id', '=', 'users.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select(
                'users.nama as marketing_nama',
                'orders.no_order', 'orders.po_number',
                'kliens.nama as klien_nama',
                'pengiriman.id as pengiriman_id',
                $this->omsetExpr()
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'users.nama', 'orders.no_order', 'orders.po_number', 'kliens.nama');

        $this->applyValidInvoiceFilter($query);

        if ($periode === 'tahun_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                  ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $query->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
        }

        $details     = $query->orderBy('users.nama')->orderBy('total_nilai', 'desc')->get();
        $groupedData = $details->groupBy('marketing_nama');

        $pdf = \PDF::loadView('pages.laporan.pdf.omset-marketing', [
            'groupedData'  => $groupedData,
            'periode'      => $periode,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'totalOverall' => $details->sum('total_nilai'),
        ]);

        return $pdf->download('Omset_Marketing_' . date('Y-m-d') . '.pdf');
    }

    // =========================================================================
    // PROCUREMENT DETAILS & EXPORT
    // =========================================================================

    public function getProcurementDetails(Request $request)
    {
        $periode   = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $query = DB::table('pengiriman')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->join('users', 'pengiriman.purchasing_id', '=', 'users.id')
            ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select(
                'users.nama as purchasing_nama',
                'orders.no_order', 'orders.po_number',
                'kliens.nama as klien_nama',
                'pengiriman.id as pengiriman_id',
                $this->omsetExpr()
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'users.nama', 'orders.no_order', 'orders.po_number', 'kliens.nama');

        $this->applyValidInvoiceFilter($query);

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
        $periode   = $request->get('periode', 'all');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $query = DB::table('pengiriman')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->join('users', 'pengiriman.purchasing_id', '=', 'users.id')
            ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->select(
                'users.nama as purchasing_nama',
                'orders.no_order', 'orders.po_number',
                'kliens.nama as klien_nama',
                'pengiriman.id as pengiriman_id',
                $this->omsetExpr()
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->groupBy('pengiriman.id', 'users.nama', 'orders.no_order', 'orders.po_number', 'kliens.nama');

        $this->applyValidInvoiceFilter($query);

        if ($periode === 'tahun_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
                  ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month);
        } elseif ($periode === 'custom' && $startDate && $endDate) {
            $query->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
        }

        $details     = $query->orderBy('users.nama')->orderBy('total_nilai', 'desc')->get();
        $groupedData = $details->groupBy('purchasing_nama');

        $pdf = \PDF::loadView('pages.laporan.pdf.omset-procurement', [
            'groupedData'  => $groupedData,
            'periode'      => $periode,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'totalOverall' => $details->sum('total_nilai'),
        ]);

        return $pdf->download('Omset_Procurement_' . date('Y-m-d') . '.pdf');
    }

    public function export(Request $request)
    {
        return response()->json(['message' => 'Export functionality will be implemented']);
    }

    // =========================================================================
    // PROCUREMENT TARGET
    // =========================================================================

    public function setProcurementTarget(Request $request)
    {
        try {
            if (!Auth::user()->isDirektur()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }

            $validated = $request->validate([
                'tahun'                => 'required|integer',
                'targets'              => 'required|array',
                'targets.*.user_id'    => 'required|exists:users,id',
                'targets.*.persentase' => 'required|numeric|min:0|max:100',
            ]);

            $totalPersentase = collect($validated['targets'])->sum('persentase');
            if ($totalPersentase > 100) {
                return response()->json(['success' => false, 'message' => 'Total persentase tidak boleh lebih dari 100%'], 422);
            }

            foreach ($validated['targets'] as $target) {
                TargetOmsetProcurement::setTarget($target['user_id'], $validated['tahun'], $target['persentase'], Auth::id());
            }

            return response()->json(['success' => true, 'message' => 'Target procurement berhasil disimpan', 'total_persentase' => $totalPersentase]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan target: ' . $e->getMessage()], 500);
        }
    }

    public function getProcurementTargetData(Request $request)
    {
        try {
            $tahun  = $request->get('tahun', Carbon::now()->year);
            $bulan  = $request->get('bulan');
            $minggu = $request->get('minggu');

            if ($request->get('get_users')) {
                $users   = User::whereIn('role', ['manager_purchasing', 'staff_purchasing'])
                    ->where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'role']);
                $targets = TargetOmsetProcurement::where('tahun', $tahun)->pluck('persentase_target', 'user_id')->toArray();
                return response()->json(['success' => true, 'users' => $users, 'targets' => $targets]);
            }

            $targetOmset = TargetOmset::getTargetForYear($tahun);
            if (!$targetOmset) {
                return response()->json(['success' => false, 'message' => 'Target omset untuk tahun ' . $tahun . ' belum ditetapkan'], 404);
            }

            $procurementTargets = TargetOmsetProcurement::with('user')->where('tahun', $tahun)->get();
            if ($procurementTargets->isEmpty()) {
                return response()->json([
                    'success'      => true,
                    'data'         => [],
                    'target_omset' => [
                        'tahunan'  => $targetOmset->target_tahunan,
                        'bulanan'  => $targetOmset->target_bulanan,
                        'mingguan' => $targetOmset->target_mingguan,
                    ],
                    'message' => 'Belum ada target procurement yang ditetapkan untuk tahun ' . $tahun,
                ]);
            }

            $data = [];
            foreach ($procurementTargets as $target) {
                $userId = $target->user_id;

                if ($minggu && $bulan) {
                    $targetAmount = $target->calculateTargetAmount($targetOmset, 'weekly');
                    $actualOmset  = $this->calculateProcurementOmset($userId, $tahun, $bulan, $minggu);
                } elseif ($bulan) {
                    $targetAmount = $target->calculateTargetAmount($targetOmset, 'monthly');
                    $actualOmset  = $this->calculateProcurementOmset($userId, $tahun, $bulan);
                } else {
                    $targetAmount = $target->calculateTargetAmount($targetOmset, 'yearly');
                    $actualOmset  = $this->calculateProcurementOmset($userId, $tahun);
                }

                $progress = $targetAmount > 0 ? ($actualOmset / $targetAmount) * 100 : 0;
                $selisih  = $actualOmset - $targetAmount;

                $data[] = [
                    'user_id'           => $userId,
                    'nama'              => $target->user->nama,
                    'role'              => $target->user->role,
                    'persentase_target' => $target->persentase_target,
                    'target_amount'     => $targetAmount,
                    'actual_omset'      => $actualOmset,
                    'progress'          => round($progress, 2),
                    'selisih'           => $selisih,
                    'status'            => $selisih >= 0 ? 'tercapai' : 'belum_tercapai',
                ];
            }

            return response()->json([
                'success'      => true,
                'data'         => $data,
                'target_omset' => [
                    'tahunan'  => $targetOmset->target_tahunan,
                    'bulanan'  => $targetOmset->target_bulanan,
                    'mingguan' => $targetOmset->target_mingguan,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data: ' . $e->getMessage()], 500);
        }
    }

    private function calculateProcurementOmset($userId, $tahun, $bulan = null, $minggu = null)
    {
        $query = DB::table('pengiriman')
            ->leftJoin(DB::raw($this->invoiceSubquery()), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->where('pengiriman.purchasing_id', $userId)
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereNull('pengiriman.deleted_at')
            ->whereYear('pengiriman.tanggal_kirim', $tahun);

        $this->applyValidInvoiceFilter($query);

        if ($bulan) {
            $query->whereMonth('pengiriman.tanggal_kirim', $bulan);
        }

        if ($minggu && $bulan) {
            $startOfMonth = Carbon::create($tahun, $bulan, 1)->startOfDay();
            $startOfWeek  = $minggu == 1 ? $startOfMonth->copy() : $startOfMonth->copy()->addDays(($minggu - 1) * 7);
            $endOfWeek    = $minggu == 4
                ? $startOfMonth->copy()->endOfMonth()
                : $startOfWeek->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
            $query->whereBetween('pengiriman.tanggal_kirim', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()]);
        }

        return $query
            ->select('pengiriman.id', $this->omsetExpr())
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
    }

    private function normalizeBahanBakuName(?string $name): string
    {
        $name = trim((string)$name);
        if ($name === '') return '';

        $lower = mb_strtolower($name, 'UTF-8');

        $aliases = [
            'Tepung biskuit' => ['tepung biskuit', 'biscuit meal', 'biskuit meal', 'biscuit mill', 'tepung roti'],
            'Mie kuning'     => ['mie kuning', 'mi kuning', 'noodle broken', 'tepung mie'],
        ];

        foreach ($aliases as $canonical => $keywords) {
            foreach ($keywords as $kw) {
                if ($lower === $kw || str_contains($lower, $kw)) {
                    return $canonical;
                }
            }
        }

        return $name;
    }
}