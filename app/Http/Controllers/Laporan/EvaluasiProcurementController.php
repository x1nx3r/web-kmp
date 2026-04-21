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

        $omsetForecasting = $forecastData->sum('total_harga_forecast');
        $statusRealisasi  = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];

        $omsetRealisasi = $forecastData->sum(function ($f) use ($statusRealisasi) {
            return $this->hitungRealisasi($f, $statusRealisasi);
        });

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

    public function buildQuery($startDate, $endDate, $status, $purchasing, $search, $pabrik, $supplier)
    {
        $displayTanggalExpr = 'COALESCE(pengiriman.tanggal_kirim, forecasts.tanggal_forecast)';

        $query = Forecast::with([
            'purchasing',
            'pengiriman.invoicePenagihan',
            'forecastDetails.bahanBakuSupplier.supplier',
            'forecastDetails.orderDetail',
            'purchaseOrder.klien',
        ])
        ->leftJoin('pengiriman', 'pengiriman.forecast_id', '=', 'forecasts.id')
        ->leftJoin('invoice_penagihan', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
        ->leftJoin('orders', 'forecasts.purchase_order_id', '=', 'orders.id')
        ->leftJoin('kliens', 'kliens.id', '=', 'orders.klien_id') // ← JOIN klien langsung
        ->leftJoin('users as pic_user', 'forecasts.purchasing_id', '=', 'pic_user.id')
        ->select(
            'forecasts.*',
            DB::raw("({$displayTanggalExpr}) as display_tanggal"),
            'pengiriman.id as pengiriman_id',
            'pengiriman.status as pengiriman_status',
            'pengiriman.catatan as pengiriman_catatan',
            'pengiriman.total_harga_kirim as pengiriman_total_harga_kirim',
            'pengiriman.total_qty_kirim as pengiriman_total_qty_kirim',
            'invoice_penagihan.amount_after_refraksi as invoice_amount',
            'invoice_penagihan.qty_after_refraksi as invoice_qty'
        )
        ->whereRaw("({$displayTanggalExpr}) between ? and ?", [$startDate, $endDate]);

        // Filter status: pastikan pengiriman ada kalau status dipilih
        if ($status) {
            $query->whereNotNull('pengiriman.id')
                  ->where('pengiriman.status', $status);
        }

        // Filter PIC purchasing
        if ($purchasing) {
            $query->where('forecasts.purchasing_id', $purchasing);
        }

        // Filter search: pakai join yang sudah ada, bukan whereHas
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('orders.po_number', 'like', "%{$search}%")
                  ->orWhere('kliens.nama', 'like', "%{$search}%");
            });
        }

        // Filter pabrik: pakai join yang sudah ada, bukan whereHas
        if ($pabrik) {
            $query->where('kliens.id', $pabrik);
        }

        // Filter supplier: tetap pakai whereHas karena relasinya 3 level dalam
        if ($supplier) {
            $query->whereHas('forecastDetails.bahanBakuSupplier', function ($q) use ($supplier) {
                $q->where('supplier_id', $supplier);
            });
        }

        return $query;
    }

    private function hitungRealisasi($forecast, array $statusRealisasi): float
    {
        $pengirimanStatus = $forecast->pengiriman_status ?? null;
        if (! in_array($pengirimanStatus, $statusRealisasi)) {
            return 0.0;
        }
        if ($pengirimanStatus === 'berhasil' && ! is_null($forecast->invoice_amount)) {
            return (float) $forecast->invoice_amount;
        }
        return (float) ($forecast->pengiriman_total_harga_kirim ?? 0);
    }
}