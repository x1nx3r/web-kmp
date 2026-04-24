<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\EvaluasiProcurementExport;
use App\Http\Controllers\Controller;
use App\Models\Forecast;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Klien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EvaluasiProcurementController extends Controller
{
    public function index(Request $request)
    {
        $title     = 'Evaluasi Procurement';
        $activeTab = 'evaluasiProcurement';

        $startDate  = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate    = $request->get('end_date',   now()->endOfMonth()->format('Y-m-d'));
        $status     = $request->get('status');
        $purchasing = $request->get('purchasing');
        $search     = $request->get('search');
        $pabrik     = $request->get('pabrik');
        $supplier   = $request->get('supplier');

        $forecastData = $this->buildQuery($startDate, $endDate, $status, $purchasing, $search, $pabrik, $supplier)
            ->orderBy('display_tanggal', 'asc')
            ->orderBy('forecasts.id', 'asc')
            ->get();

        // ---------------------------------------------------------------
        // Hitung total forecast: SUM(qty_forecast * harga_jual)
        // ---------------------------------------------------------------
        $omsetForecasting = $forecastData->sum('computed_total_forecast');

        $statusRealisasi = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];

        // ---------------------------------------------------------------
        // Omset realisasi: ikut logika Laporan Omset
        //   COALESCE(invoice_amount, fallback_sum_detail)
        // ---------------------------------------------------------------
        $omsetRealisasi = $forecastData->sum(function ($f) use ($statusRealisasi) {
            return $this->hitungRealisasi($f, $statusRealisasi);
        });

        // ---------------------------------------------------------------
        // Omset tambahan: catatan == 'Tambahan' && status realisasi
        // ---------------------------------------------------------------
        $omsetTambahan = $forecastData
            ->filter(fn($f) => trim((string) $f->catatan) === 'Tambahan'
                && in_array($f->pengiriman_status, $statusRealisasi))
            ->sum(function ($f) use ($statusRealisasi) {
                return $this->hitungRealisasi($f, $statusRealisasi);
            });

        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing', 'direktur'])->get();
        $pabrikList      = Klien::orderBy('nama', 'asc')->get();
        $supplierList    = Supplier::orderBy('nama', 'asc')->get();

        return view('pages.laporan.evaluasi-procurement', compact(
            'title', 'activeTab', 'forecastData', 'purchasingUsers',
            'pabrikList', 'supplierList', 'startDate', 'endDate',
            'status', 'purchasing', 'search', 'pabrik', 'supplier',
            'omsetForecasting', 'omsetRealisasi', 'omsetTambahan'
        ));
    }

    public function export(Request $request)
    {
        $startDate  = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate    = $request->get('end_date',   now()->endOfMonth()->format('Y-m-d'));
        $status     = $request->get('status');
        $purchasing = $request->get('purchasing');
        $search     = $request->get('search');
        $pabrik     = $request->get('pabrik');
        $supplier   = $request->get('supplier');

        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing', 'direktur'])->get();
        $pabrikName      = $pabrik   ? Klien::find($pabrik)?->nama    : null;
        $supplierName    = $supplier ? Supplier::find($supplier)?->nama : null;

        $fileName = 'evaluasi_procurement_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new EvaluasiProcurementExport(
                $startDate, $endDate, $status, $purchasing, $search,
                $purchasingUsers, $pabrik, $pabrikName, $supplier, $supplierName
            ),
            $fileName
        );
    }

    /**
     * Build query utama.
     *
     * Perubahan vs versi lama:
     * 1. Tambah subquery `forecast_totals`  → computed_total_forecast (qty_forecast * harga_jual)
     * 2. Tambah subquery `pengiriman_omset` → computed_realisasi & computed_qty_kirim
     *    menggunakan COALESCE(invoice, SUM detail) per pengiriman.id  ← sama persis dengan Laporan Omset.
     * 3. Kolom lama pengiriman.total_harga_kirim tidak lagi dipakai untuk kalkulasi omset.
     */
    public function buildQuery($startDate, $endDate, $status, $purchasing, $search, $pabrik, $supplier)
    {
        // Karena tabel pengiriman masuk ke dalam subquery "po",
        // referensi tanggal_kirim harus melalui alias kolom subquery tersebut.
        $displayTanggalExpr = 'COALESCE(po.p_tanggal_kirim, forecasts.tanggal_forecast)';

        // ------------------------------------------------------------------
        // Subquery 1: total forecast per forecast_id
        //   SUM(fd.qty_forecast * od.harga_jual)
        // ------------------------------------------------------------------
        $forecastTotalsSub = DB::table('forecast_details as fd')
            ->join('order_details as od', 'fd.purchase_order_bahan_baku_id', '=', 'od.id')
            ->select(
                'fd.forecast_id',
                DB::raw('SUM(fd.qty_forecast * od.harga_jual) as total_forecast_computed'),
                DB::raw('SUM(fd.qty_forecast)               as total_qty_forecast')
            )
            ->groupBy('fd.forecast_id');

        // ------------------------------------------------------------------
        // Subquery 2: omset realisasi per pengiriman.id
        //   COALESCE(MAX(invoice.amount_after_refraksi),
        //            SUM(pd.qty_kirim * od.harga_jual))
        //   — identik dengan calculateOmsetSistem() di OmsetController
        //
        //   PENTING: sertakan tanggal_kirim (p_tanggal_kirim) agar bisa
        //   dipakai oleh COALESCE display_tanggal di query utama.
        // ------------------------------------------------------------------
        $pengirimanOmsetSub = DB::table('pengiriman as p')
            ->leftJoin('invoice_penagihan as ip', 'p.id', '=', 'ip.pengiriman_id')
            ->leftJoin('pengiriman_details as pd', 'p.id', '=', 'pd.pengiriman_id')
            ->leftJoin('order_details as od', 'pd.purchase_order_bahan_baku_id', '=', 'od.id')
            ->whereNull('p.deleted_at')
            ->select(
                'p.id as pengiriman_id',
                'p.forecast_id',
                'p.tanggal_kirim as p_tanggal_kirim',   // ← dibutuhkan untuk display_tanggal
                'p.status as p_status',
                'p.catatan as p_catatan',
                'p.total_harga_kirim as p_total_harga_kirim',
                'p.total_qty_kirim as p_total_qty_kirim',
                DB::raw('COALESCE(
                    MAX(ip.amount_after_refraksi),
                    SUM(pd.qty_kirim * od.harga_jual)
                ) as realisasi_amount'),
                DB::raw('COALESCE(
                    MAX(ip.qty_after_refraksi),
                    SUM(pd.qty_kirim)
                ) as realisasi_qty')
            )
            ->groupBy(
                'p.id', 'p.forecast_id', 'p.tanggal_kirim', 'p.status',
                'p.catatan', 'p.total_harga_kirim', 'p.total_qty_kirim'
            );

        // ------------------------------------------------------------------
        // Query utama
        // ------------------------------------------------------------------
        $query = Forecast::with([
            'purchasing',
            'forecastDetails.bahanBakuSupplier.supplier',
            'forecastDetails.orderDetail',
            'purchaseOrder.klien',
        ])
        ->leftJoinSub($pengirimanOmsetSub, 'po', function ($join) {
            $join->on('po.forecast_id', '=', 'forecasts.id');
        })
        ->leftJoinSub($forecastTotalsSub, 'ft', function ($join) {
            $join->on('ft.forecast_id', '=', 'forecasts.id');
        })
        ->leftJoin('orders', 'forecasts.purchase_order_id', '=', 'orders.id')
        ->leftJoin('kliens', 'kliens.id', '=', 'orders.klien_id')
        ->select(
            'forecasts.*',
            // display_tanggal: pakai po.p_tanggal_kirim (bukan pengiriman.tanggal_kirim)
            DB::raw("COALESCE(po.p_tanggal_kirim, forecasts.tanggal_forecast) as display_tanggal"),
            // --- kolom pengiriman ---
            'po.pengiriman_id',
            'po.p_tanggal_kirim as pengiriman_tanggal_kirim',
            'po.p_status        as pengiriman_status',
            'po.p_catatan       as pengiriman_catatan',
            'po.p_total_harga_kirim as pengiriman_total_harga_kirim',
            'po.p_total_qty_kirim   as pengiriman_total_qty_kirim',
            // --- computed omset realisasi (logika Laporan Omset) ---
            'po.realisasi_amount',
            'po.realisasi_qty',
            // --- computed total forecast (qty_forecast * harga_jual) ---
            DB::raw('COALESCE(ft.total_forecast_computed, 0) as computed_total_forecast'),
            DB::raw('COALESCE(ft.total_qty_forecast,      0) as computed_qty_forecast'),
        )
        // Filter tanggal: gunakan ekspresi yang sama (po.p_tanggal_kirim, bukan pengiriman.tanggal_kirim)
        ->whereRaw("COALESCE(po.p_tanggal_kirim, forecasts.tanggal_forecast) between ? and ?", [$startDate, $endDate]);

        // Filter status
        if ($status) {
            $query->whereNotNull('po.pengiriman_id')
                  ->where('po.p_status', $status);
        }

        // Filter PIC purchasing
        if ($purchasing) {
            $query->where('forecasts.purchasing_id', $purchasing);
        }

        // Filter search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('orders.po_number', 'like', "%{$search}%")
                  ->orWhere('kliens.nama',    'like', "%{$search}%");
            });
        }

        // Filter pabrik
        if ($pabrik) {
            $query->where('kliens.id', $pabrik);
        }

        // Filter supplier (3-level relation, tetap whereHas)
        if ($supplier) {
            $query->whereHas('forecastDetails.bahanBakuSupplier', function ($q) use ($supplier) {
                $q->where('supplier_id', $supplier);
            });
        }

        return $query;
    }

    /**
     * Hitung nilai realisasi satu forecast — selaras dengan Laporan Omset.
     *
     * Logika:
     *  - Jika status pengiriman BUKAN kategori realisasi → 0
     *  - Jika masuk kategori realisasi:
     *      pakai `realisasi_amount` (sudah berisi COALESCE invoice / sum detail dari subquery)
     */
    private function hitungRealisasi($forecast, array $statusRealisasi): float
    {
        $pengirimanStatus = $forecast->pengiriman_status ?? null;

        if (! in_array($pengirimanStatus, $statusRealisasi)) {
            return 0.0;
        }

        return (float) ($forecast->realisasi_amount ?? 0);
    }

    /**
     * Hitung qty kirim untuk satu forecast.
     *
     * Pakai realisasi_qty dari subquery (COALESCE invoice_qty / sum detail qty).
     */
    private function hitungQtyKirim($forecast, array $statusRealisasi): float|string
    {
        $pengirimanStatus = $forecast->pengiriman_status ?? null;

        if (! in_array($pengirimanStatus, $statusRealisasi)) {
            return '-';
        }

        return (float) ($forecast->realisasi_qty ?? 0);
    }
}