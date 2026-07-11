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
    /**
     * Konstanta status pengiriman yang masuk kategori realisasi.
     */
    private const STATUS_REALISASI = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];
    private const CATATAN_TAMBAHAN = 'Tambahan';

    public function index(Request $request)
    {
        $title     = 'Evaluasi Procurement';
        $activeTab = 'evaluasiProcurement';

        // Mengambil filter yang sudah tervalidasi
        $filters = $this->getValidatedFilters($request);

        $forecastData = $this->buildQuery(
            $filters['start_date'], $filters['end_date'], $filters['status'], 
            $filters['purchasing'], $filters['search'], $filters['pabrik'], $filters['supplier']
        )
            ->orderBy('display_tanggal', 'asc')
            ->orderBy('forecasts.id', 'asc')
            ->get();

        // Hitung total forecast: SUM(qty_forecast * harga_jual)
        $omsetForecasting = $forecastData->sum('computed_total_forecast');

        // Omset realisasi: COALESCE(invoice_amount, fallback_sum_detail)
        $omsetRealisasi = $forecastData->sum(fn($f) => $this->hitungRealisasi($f));

        // Omset tambahan: catatan == 'Tambahan' && status realisasi
        $omsetTambahan = $forecastData
            ->filter(fn($f) => trim((string) $f->catatan) === self::CATATAN_TAMBAHAN
                && in_array($f->pengiriman_status, self::STATUS_REALISASI))
            ->sum(fn($f) => $this->hitungRealisasi($f));

        $purchasingUsers = ReferenceDataService::getPurchasingUsers();
        $pabrikList      = ReferenceDataService::getKliens();
        $supplierList    = ReferenceDataService::getSuppliers();

        // Extract filters array to individual variables to maintain View contract
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

    /**
     * Memusatkan pengambilan dan validasi input filter.
     * Mengamankan aplikasi dari injeksi parameter tipe data yang tidak sesuai.
     */
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
            
            // Duplicate keys for DB compatibility and view compatibility if needed
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

        // Subquery 2: omset realisasi per pengiriman.id
        $pengirimanOmsetSub = DB::table('pengiriman as p')
            ->leftJoin('invoice_penagihan as ip', 'p.id', '=', 'ip.pengiriman_id')
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
                DB::raw('COALESCE(MAX(ip.amount_after_refraksi), SUM(pd.qty_kirim * od.harga_jual)) as realisasi_amount'),
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
}