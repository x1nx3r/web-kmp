<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\EvaluasiProcurementExport;
use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
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
        $title = 'Evaluasi Procurement';
        $activeTab = 'evaluasiProcurement';

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status');
        $purchasing = $request->get('purchasing');
        $search = $request->get('search');
        $pabrik = $request->get('pabrik');
        $supplier = $request->get('supplier');

        $query = $this->baseQuery($startDate, $endDate, $status, $purchasing, $search, $pabrik, $supplier);

        $pengirimanData = $query
            ->orderByRaw('COALESCE(forecast_min.tanggal_forecast_min, pengiriman.tanggal_kirim) ASC')
            ->orderBy('pengiriman.id', 'asc')
            ->paginate(15)
            ->withQueryString();

        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing', 'direktur'])->get();
        $pabrikList = Klien::orderBy('nama', 'asc')->get();
        $supplierList = Supplier::orderBy('nama', 'asc')->get();

        return view('pages.laporan.evaluasi-procurement', compact(
            'title',
            'activeTab',
            'pengirimanData',
            'purchasingUsers',
            'pabrikList',
            'supplierList',
            'startDate',
            'endDate',
            'status',
            'purchasing',
            'search',
            'pabrik',
            'supplier'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status');
        $purchasing = $request->get('purchasing');
        $search = $request->get('search');
        $pabrik = $request->get('pabrik');
        $supplier = $request->get('supplier');

        $purchasingUsers = User::whereIn('role', ['manager_purchasing', 'staff_purchasing', 'direktur'])->get();

        $pabrikName = null;
        if ($pabrik) {
            $pabrikName = Klien::find($pabrik)?->nama;
        }

        $supplierName = null;
        if ($supplier) {
            $supplierName = Supplier::find($supplier)?->nama;
        }

        $fileName = 'evaluasi_procurement_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new EvaluasiProcurementExport(
                $startDate,
                $endDate,
                $status,
                $purchasing,
                $search,
                $purchasingUsers,
                $pabrik,
                $pabrikName,
                $supplier,
                $supplierName
            ),
            $fileName
        );
    }

    private function baseQuery($startDate, $endDate, $status, $purchasing, $search, $pabrik, $supplier)
    {
        $forecastMinSub = DB::table('forecasts')
            ->join('pengiriman', 'pengiriman.forecast_id', '=', 'forecasts.id')
            ->selectRaw('pengiriman.id as pengiriman_id, MIN(forecasts.tanggal_forecast) as tanggal_forecast_min, MIN(forecasts.hari_kirim_forecast) as hari_kirim_forecast_min')
            ->groupBy('pengiriman.id');

        $query = Pengiriman::with([
            'purchasing',
            'order.klien',
            'order.orderDetails.bahanBakuKlien',
            'forecast',
            'pengirimanDetails.bahanBakuSupplier.supplier.picPurchasing',
            'pengirimanDetails.orderDetail',
            'invoicePenagihan'
        ])
            ->leftJoinSub($forecastMinSub, 'forecast_min', function ($join) {
                $join->on('pengiriman.id', '=', 'forecast_min.pengiriman_id');
            })
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->select(
                'pengiriman.*',
                'orders.po_number',
                'forecast_min.tanggal_forecast_min as tanggal_forecast_min',
                'forecast_min.hari_kirim_forecast_min as hari_kirim_forecast_min',
                DB::raw('CASE 
                    WHEN pengiriman.status = "berhasil" AND invoice_penagihan.qty_after_refraksi IS NOT NULL 
                    THEN invoice_penagihan.qty_after_refraksi 
                    ELSE pengiriman.total_qty_kirim 
                END as display_qty'),
                DB::raw('CASE 
                    WHEN pengiriman.status = "berhasil" AND invoice_penagihan.amount_after_refraksi IS NOT NULL 
                    THEN invoice_penagihan.amount_after_refraksi 
                    ELSE pengiriman.total_harga_kirim 
                END as display_harga')
            )
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    // Pengiriman normal (bukan gagal) - pakai tanggal_kirim
                    $q->where('pengiriman.status', '!=', 'gagal')
                      ->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    // Pengiriman gagal yang PUNYA tanggal_kirim - pakai tanggal_kirim
                    $q->where('pengiriman.status', 'gagal')
                      ->whereNotNull('pengiriman.tanggal_kirim')
                      ->whereBetween('pengiriman.tanggal_kirim', [$startDate, $endDate]);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    // Pengiriman gagal yang TIDAK punya tanggal_kirim - pakai updated_at
                    $q->where('pengiriman.status', 'gagal')
                      ->whereNull('pengiriman.tanggal_kirim')
                      ->whereBetween('pengiriman.updated_at', [$startDate, $endDate]);
                });
            })
            // Exclude pengiriman yang tidak punya details (data kotor)
            ->whereHas('pengirimanDetails');

        if ($status) {
            $query->where('pengiriman.status', $status);
        }

        if ($purchasing) {
            $query->where('pengiriman.purchasing_id', $purchasing);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pengiriman.no_pengiriman', 'like', "%{$search}%")
                    ->orWhere('orders.po_number', 'like', "%{$search}%");
            });
        }

        if ($pabrik) {
            $query->whereHas('order.klien', function ($q) use ($pabrik) {
                $q->where('id', $pabrik);
            });
        }

        if ($supplier) {
            $query->whereHas('pengirimanDetails.bahanBakuSupplier.supplier', function ($q) use ($supplier) {
                $q->where('id', $supplier);
            });
        }

        return $query;
    }
}