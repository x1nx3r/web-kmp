<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\EvaluasiProcurementExport;
use App\Http\Controllers\Controller;
use App\Models\Forecast;
use App\Models\Klien;
use App\Models\Supplier;
use App\Services\ReferenceDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class EvaluasiProcurementController extends Controller
{

    private const STATUS_REALISASI = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];


    private const KEYWORD_TAMBAHAN = 'tambahan';

    public function index(Request $request)
    {
        $title     = 'Evaluasi Procurement';
        $activeTab = 'evaluasiProcurement';

        $filters = $this->getValidatedFilters($request);

        $forecastData = $this->buildQuery(
            $filters['start_date'], $filters['end_date'], $filters['status'], 
            $filters['purchasing'], $filters['search'], $filters['pabrik'], $filters['supplier']
        )
            ->orderBy('display_tanggal', 'asc')
            ->orderBy('forecasts.id', 'asc')
            ->get();

        $omsetForecasting = $forecastData->sum('computed_total_forecast');

        $omsetRealisasi = $forecastData->sum(fn($f) => $this->hitungRealisasi($f));

        $omsetTambahan = $forecastData
            ->filter(fn($f) => $this->isTambahan($f->catatan)
                && in_array($f->pengiriman_status, self::STATUS_REALISASI))
            ->sum(fn($f) => $this->hitungRealisasi($f));

        $purchasingUsers = ReferenceDataService::getPurchasingUsers();
        $pabrikList      = ReferenceDataService::getKliens();
        $supplierList    = ReferenceDataService::getSuppliers();

        extract($filters);

        return view('pages.laporan.evaluasi-procurement', compact(
            'title', 'activeTab', 'forecastData', 'purchasingUsers',
            'pabrikList', 'supplierList', 'startDate', 'endDate',
            'status', 'purchasing', 'search', 'pabrik', 'supplier',
            'omsetForecasting', 'omsetRealisasi', 'omsetTambahan'
        ));
    }

    public function export(Request $request)
    {
        try {
            $filters = $this->getValidatedFilters($request);
            extract($filters);

            $purchasingUsers = ReferenceDataService::getPurchasingUsers();
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
        } catch (Exception $e) {
            Log::error('Export Evaluasi Procurement Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
        }
    }

    private function getValidatedFilters(Request $request): array
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'status'     => 'nullable|string',
            'purchasing' => 'nullable|integer',
            'search'     => 'nullable|string',
            'pabrik'     => 'nullable|integer',
            'supplier'   => 'nullable|integer',
        ]);

        return [
            'startDate'  => $request->get('start_date', now()->startOfMonth()->format('Y-m-d')),
            'endDate'    => $request->get('end_date', now()->endOfMonth()->format('Y-m-d')),
            'status'     => $request->get('status'),
            'purchasing' => $request->get('purchasing'),
            'search'     => $request->get('search'),
            'pabrik'     => $request->get('pabrik'),
            'supplier'   => $request->get('supplier'),

            'start_date' => $request->get('start_date', now()->startOfMonth()->format('Y-m-d')),
            'end_date'   => $request->get('end_date', now()->endOfMonth()->format('Y-m-d')),
        ];
    }

    /**
     * Build query utama dengan subquery.
     */
    public function buildQuery($startDate, $endDate, $status, $purchasing, $search, $pabrik, $supplier)
    {
        // Subquery 1: total forecast per forecast_id
        $forecastTotalsSub = DB::table('forecast_details as fd')
            ->join('order_details as od', 'fd.purchase_order_bahan_baku_id', '=', 'od.id')
            ->select(
                'fd.forecast_id',
                DB::raw('SUM(fd.qty_forecast * od.harga_jual) as total_forecast_computed'),
                DB::raw('SUM(fd.qty_forecast)               as total_qty_forecast')
            )
            ->groupBy('fd.forecast_id');

        // FIX BUG RASIO (sama seperti MarginController / OmsetController::omsetExpr()):
        // subquery invoiceGrossSub (rata-rata proporsional per invoice gabungan) TIDAK LAGI
        // dipakai untuk MENGHITUNG realisasi_amount — hanya dipertahankan sbg dokumentasi
        // historis di komentar. Realisasi sekarang diambil LANGSUNG dari JSON `items` per
        // pengiriman (lihat $pengirimanOmsetSub di bawah), bukan dari rasio
        // (qty*harga_jual pengiriman ini) / (gross seluruh pengiriman dalam invoice yang sama).

        // FIX (konsistensi dgn Margin & Omset): join invoice_penagihan sebelumnya HANYA lewat
        // p.invoice_penagihan_id (equi-join biasa). Untuk pengiriman yang kolom ini belum
        // ter-backfill (NULL), join gagal total, sehingga realisasi_amount selalu jatuh ke
        // fallback harga PO mentah (SUM qty*harga_jual PO) walau invoice asli (dengan
        // amount_after_refraksi yang sudah dipotong refraksi) sebenarnya ada & valid lewat
        // invoice_penagihan.pengiriman_id. Sekarang join memakai OR condition: match via
        // p.invoice_penagihan_id kalau ada, ATAU via ip.pengiriman_id kalau
        // p.invoice_penagihan_id NULL dan invoice lama itu bukan hasil merge (status != 'digabung').
        $pengirimanOmsetSub = DB::table('pengiriman as p')
            ->leftJoin('invoice_penagihan as ip', function ($join) {
                $join->on('p.invoice_penagihan_id', '=', 'ip.id')
                     ->orOn(function ($q) {
                         $q->whereNull('p.invoice_penagihan_id')
                           ->whereColumn('p.id', '=', 'ip.pengiriman_id')
                           ->where('ip.status', '!=', 'digabung');
                     });
            })
            ->leftJoin('pengiriman_details as pd', 'p.id', '=', 'pd.pengiriman_id')
            ->leftJoin('order_details as od', 'pd.purchase_order_bahan_baku_id', '=', 'od.id')
            ->whereNull('p.deleted_at')
            ->select(
                'p.id as pengiriman_id',
                'p.forecast_id',
                'p.tanggal_kirim as p_tanggal_kirim',
                'p.status as p_status',
                'p.catatan as p_catatan',
                'p.total_harga_kirim as p_total_harga_kirim',
                'p.total_qty_kirim as p_total_qty_kirim',
                // FIX BUG RASIO (identik dgn MarginController::hitungHargaBeliJual() /
                // findInvoiceItemForPengiriman() dan OmsetController::omsetExpr()): rumus
                // lama merekonstruksi realisasi_amount lewat rasio proporsional
                //   (qty*harga_jual pengiriman ini) / (gross seluruh pengiriman dalam invoice)
                //   x amount_after_refraksi/subtotal invoice
                // yang HANYA benar kalau markup/adjustment manual per pengiriman dalam satu
                // invoice gabungan seragam — kenyataannya tidak, sehingga hasilnya "diratakan"
                // dan menyimpang dari angka riil yang diinput saat invoice dibuat.
                //
                // FIX: `ip.items` (JSON) menyimpan breakdown final PER PENGIRIMAN
                // (item_name/description berisi no_pengiriman, `amount` = nilai final yang
                // ditagih, sudah termasuk markup manual per shipment). Di sini kita cari
                // elemen array yang match dengan `p.no_pengiriman` lewat JSON_TABLE, lalu
                // ambil `amount`-nya LANGSUNG — tanpa rasio/proporsi apa pun. Ini otomatis
                // benar untuk invoice tunggal maupun gabungan (merge). Dilakukan murni di SQL
                // dalam expression aggregate yang sama, sehingga TIDAK menambah query per
                // baris pengiriman (tidak ada N+1) — jumlah query tetap identik.
                //
                // Urutan fallback:
                //   1. Tidak ada invoice sama sekali -> SUM(qty_kirim * harga_jual) dari PO.
                //   2. Ada invoice & ketemu item JSON yang match no_pengiriman -> pakai
                //      `amount` item itu apa adanya.
                //   3. Ada invoice tapi item JSON tidak ketemu/tidak valid (invoice lama
                //      sebelum format `items` per-pengiriman dipakai) -> fallback ke
                //      amount_after_refraksi/subtotal invoice secara UTUH.
                //
                // CATATAN KOMPATIBILITAS: JSON_TABLE butuh MySQL 8.0.19+ / MariaDB 10.6+.
                DB::raw("
                    CASE
                        WHEN COALESCE(NULLIF(MAX(ip.amount_after_refraksi), 0), NULLIF(MAX(ip.subtotal), 0)) IS NULL
                            THEN SUM(pd.qty_kirim * od.harga_jual)
                        ELSE COALESCE(
                            (
                                SELECT jt.amount
                                FROM JSON_TABLE(
                                    MAX(ip.items),
                                    '$[*]' COLUMNS (
                                        item_name   VARCHAR(255) PATH '$.item_name',
                                        description TEXT         PATH '$.description',
                                        amount      DECIMAL(18,2) PATH '$.amount'
                                    )
                                ) AS jt
                                WHERE jt.item_name LIKE CONCAT('%', p.no_pengiriman, '%')
                                   OR jt.description LIKE CONCAT('%', p.no_pengiriman, '%')
                                LIMIT 1
                            ),
                            COALESCE(NULLIF(MAX(ip.amount_after_refraksi), 0), NULLIF(MAX(ip.subtotal), 0))
                        )
                    END as realisasi_amount
                "),
                DB::raw('COALESCE(MAX(ip.qty_after_refraksi), SUM(pd.qty_kirim)) as realisasi_qty')
            )
            ->groupBy(
                'p.id', 'p.forecast_id', 'p.tanggal_kirim', 'p.status',
                'p.catatan', 'p.total_harga_kirim', 'p.total_qty_kirim'
            );

        // Query utama
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
            DB::raw("COALESCE(po.p_tanggal_kirim, forecasts.tanggal_forecast) as display_tanggal"),
            'po.pengiriman_id',
            'po.p_tanggal_kirim as pengiriman_tanggal_kirim',
            'po.p_status        as pengiriman_status',
            'po.p_catatan       as pengiriman_catatan',
            'po.p_total_harga_kirim as pengiriman_total_harga_kirim',
            'po.p_total_qty_kirim   as pengiriman_total_qty_kirim',
            'po.realisasi_amount',
            'po.realisasi_qty',
            DB::raw('COALESCE(ft.total_forecast_computed, 0) as computed_total_forecast'),
            DB::raw('COALESCE(ft.total_qty_forecast,      0) as computed_qty_forecast'),
        )
        ->whereRaw("COALESCE(po.p_tanggal_kirim, forecasts.tanggal_forecast) between ? and ?", [$startDate, $endDate]);

        if ($status) {
            $query->whereNotNull('po.pengiriman_id')
                  ->where('po.p_status', $status);
        }
        if ($purchasing) {
            $query->where('forecasts.purchasing_id', $purchasing);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('orders.po_number', 'like', "%{$search}%")
                  ->orWhere('kliens.nama',    'like', "%{$search}%");
            });
        }
        if ($pabrik) {
            $query->where('kliens.id', $pabrik);
        }
        if ($supplier) {
            $query->whereHas('forecastDetails.bahanBakuSupplier', function ($q) use ($supplier) {
                $q->where('supplier_id', $supplier);
            });
        }

        return $query;
    }

    /**
     * Hitung nilai realisasi satu forecast.
     */
    private function hitungRealisasi($forecast): float
    {
        if (!in_array($forecast->pengiriman_status ?? null, self::STATUS_REALISASI)) {
            return 0.0;
        }

        return (float) ($forecast->realisasi_amount ?? 0);
    }
    private function isTambahan($catatan): bool
    {
        return str_contains(strtolower(trim((string) $catatan)), self::KEYWORD_TAMBAHAN);
    }
}