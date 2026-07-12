<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\ApprovalPembayaran;
use App\Models\CatatanPiutang;
use App\Models\Pengiriman;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    private const STATUS_COMPLETED = 'completed';
    private const STATUS_LUNAS = 'lunas';

    public function index(Request $request)
    {
        // 1. Tangani Request AJAX secara terpisah untuk menjaga Single Responsibility
        if ($request->ajax()) {
            return $this->handleAjaxRequests($request);
        }

        // 2. Persiapkan Data Halaman Utama
        $title = 'Pembayaran';
        $activeTab = 'pembayaran';
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $totalPembayaran = ApprovalPembayaran::where('status', self::STATUS_COMPLETED)->sum('amount_after_refraksi') ?? 0;

        $pembayaranTahunIni = ApprovalPembayaran::where('status', self::STATUS_COMPLETED)
            ->where(fn($q) => $this->applyApprovalDateFilter($q, $currentYear))
            ->sum('amount_after_refraksi') ?? 0;

        $pembayaranBulanIni = ApprovalPembayaran::where('status', self::STATUS_COMPLETED)
            ->where(fn($q) => $this->applyApprovalDateFilter($q, $currentYear, $currentMonth))
            ->sum('amount_after_refraksi') ?? 0;

        $totalPiutangSupplier = CatatanPiutang::where('status', '!=', self::STATUS_LUNAS)->sum('sisa_piutang') ?? 0;
        $jumlahTransaksi = ApprovalPembayaran::where('status', self::STATUS_COMPLETED)->count();

        // 3. Filter Periode Halaman
        $periode = $request->get('periode', 'semua');
        $periodeSupplier = $request->get('periode_supplier', 'semua');
        $periodePiutang = $request->get('periode_piutang', 'semua');
        $selectedYear = $request->get('tahun', $currentYear);
        $selectedYearTransaksi = $request->get('tahun_transaksi', $currentYear);

        // 4. Data Chart Awal (Dioptimalkan menggunakan helper method)
        $pembayaranSupplier = $this->getBaseSupplierQuery()
            ->groupBy('suppliers.id', 'suppliers.nama')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        $topSupplier = $this->getBaseSupplierQuery(true)
            ->groupBy('suppliers.id', 'suppliers.nama', 'suppliers.alamat')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        $topPiutangSupplier = CatatanPiutang::select('supplier_id', DB::raw('SUM(sisa_piutang) as total'))
            ->where('status', '!=', self::STATUS_LUNAS)
            ->with('supplier:id,nama,alamat')
            ->groupBy('supplier_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // 5. Kalkulasi Bulanan (N+1 Terpecahkan: 1 Kueri SQL vs 12 Kueri)
        $pembayaranPerBulan = $this->getMonthlyStats($selectedYear, 'sum');
        $jumlahTransaksiPerBulan = $this->getMonthlyStats($selectedYearTransaksi, 'count');

        // 6. Ketersediaan Tahun
        $availableYears = ApprovalPembayaran::where('status', self::STATUS_COMPLETED)
            ->selectRaw('DISTINCT YEAR(COALESCE(superadmin_approved_at, updated_at)) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [$currentYear];
        }

        return view('pages.laporan.pembayaran', compact(
            'title', 'activeTab', 'totalPembayaran', 'pembayaranTahunIni', 'pembayaranBulanIni',
            'totalPiutangSupplier', 'jumlahTransaksi', 'pembayaranSupplier', 'topSupplier',
            'topPiutangSupplier', 'pembayaranPerBulan', 'jumlahTransaksiPerBulan',
            'selectedYear', 'selectedYearTransaksi', 'availableYears',
            'periode', 'periodeSupplier', 'periodePiutang'
        ));
    }

    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }

    /**
     * ----------------------------------------------------------------------
     * PRIVATE METHODS & HELPERS (Abstraksi Clean Code)
     * ----------------------------------------------------------------------
     */

    private function handleAjaxRequests(Request $request)
    {
        $ajaxType = $request->get('ajax');

        return match ($ajaxType) {
            'pembayaran_supplier' => $this->ajaxPembayaranSupplier($request),
            'top_supplier' => $this->ajaxTopSupplier($request),
            'pembayaran_per_bulan' => $this->ajaxPembayaranPerBulan($request),
            'jumlah_transaksi_per_bulan' => $this->ajaxJumlahTransaksiPerBulan($request),
            'piutang_supplier' => $this->ajaxPiutangSupplier($request),
            default => response()->json(['error' => 'Invalid AJAX request'], 400),
        };
    }

    private function ajaxPembayaranSupplier(Request $request)
    {
        $query = $this->getBaseSupplierQuery()->groupBy('suppliers.id', 'suppliers.nama');
        $this->applyAjaxPeriodFilter($query, $request->get('periode'), $request);

        $data = $query->orderBy('total', 'desc')->limit(10)->get()
            ->map(fn($item) => ['nama' => $item->supplier_name ?? 'Unknown', 'total' => floatval($item->total ?? 0)])
            ->filter(fn($item) => $item['total'] > 0)->values();

        return response()->json($data);
    }

    private function ajaxTopSupplier(Request $request)
    {
        $query = $this->getBaseSupplierQuery(true)->groupBy('suppliers.id', 'suppliers.nama', 'suppliers.alamat');
        $this->applyAjaxPeriodFilter($query, $request->get('periode_supplier'), $request, 'start_date_supplier', 'end_date_supplier');

        $data = $query->orderBy('total', 'desc')->limit(10)->get()
            ->map(fn($item) => [
                'nama' => $item->supplier_name ?? 'Unknown',
                'alamat' => $item->supplier_address ?? null,
                'total' => floatval($item->total ?? 0)
            ])->filter(fn($item) => $item['total'] > 0)->values();

        return response()->json($data);
    }

    private function ajaxPembayaranPerBulan(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        return response()->json([
            'data' => $this->getMonthlyStats($tahun, 'sum'),
            'tahun' => $tahun
        ]);
    }

    private function ajaxJumlahTransaksiPerBulan(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        return response()->json([
            'data' => $this->getMonthlyStats($tahun, 'count'),
            'tahun' => $tahun
        ]);
    }

    private function ajaxPiutangSupplier(Request $request)
    {
        $periode = $request->get('periode_piutang', 'semua');
        $query = CatatanPiutang::select('supplier_id', DB::raw('SUM(sisa_piutang) as total'))
            ->where('status', '!=', self::STATUS_LUNAS)
            ->with('supplier:id,nama,alamat')
            ->groupBy('supplier_id');

        if ($periode === 'tahun_ini') {
            $query->whereYear('created_at', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month);
        } elseif ($periode === 'custom' && $request->filled(['start_date_piutang', 'end_date_piutang'])) {
            $request->validate(['start_date_piutang' => 'date', 'end_date_piutang' => 'date']);
            $query->whereBetween('created_at', [$request->start_date_piutang, $request->end_date_piutang]);
        }

        $data = $query->orderBy('total', 'desc')->limit(10)->get()
            ->map(fn($item) => [
                'nama' => $item->supplier->nama ?? 'Unknown',
                'alamat' => $item->supplier->alamat ?? null,
                'total' => floatval($item->total ?? 0)
            ])->filter(fn($item) => $item['total'] > 0)->values();

        return response()->json($data);
    }

    /**
     * Sentralisasi kueri JOIN untuk supplier demi mencegah duplikasi kode.
     */
    private function getBaseSupplierQuery(bool $withAddress = false)
    {
        $selects = ['suppliers.nama as supplier_name', DB::raw('SUM(approval_pembayaran.amount_after_refraksi) as total')];
        if ($withAddress) $selects[] = 'suppliers.alamat as supplier_address';

        return ApprovalPembayaran::select($selects)
            ->join('pengiriman', 'approval_pembayaran.pengiriman_id', '=', 'pengiriman.id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->where('approval_pembayaran.status', self::STATUS_COMPLETED)
            ->whereNull('pengiriman_details.deleted_at');
    }

    /**
     * Sentralisasi logika filter tanggal fallback (superadmin_approved_at vs updated_at).
     */
    private function applyApprovalDateFilter($query, $year, $month = null)
    {
        return $query->where(function($q) use ($year, $month) {
            $q->where(function($subq) use ($year, $month) {
                $subq->whereYear('superadmin_approved_at', $year);
                if ($month) $subq->whereMonth('superadmin_approved_at', $month);
            })->orWhere(function($subq) use ($year, $month) {
                $subq->whereNull('superadmin_approved_at')->whereYear('updated_at', $year);
                if ($month) $subq->whereMonth('updated_at', $month);
            });
        });
    }

    /**
     * Terapkan filter periode untuk kueri AJAX grafik.
     */
    private function applyAjaxPeriodFilter($query, $periode, Request $request, $startField = 'start_date', $endField = 'end_date')
    {
        if ($periode === 'tahun_ini') {
            $query->whereYear('approval_pembayaran.superadmin_approved_at', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('approval_pembayaran.superadmin_approved_at', Carbon::now()->year)
                  ->whereMonth('approval_pembayaran.superadmin_approved_at', Carbon::now()->month);
        } elseif ($periode === 'custom' && $request->filled([$startField, $endField])) {
            $request->validate([$startField => 'date', $endField => 'date']);
            $query->whereBetween('approval_pembayaran.superadmin_approved_at', [$request->$startField, $request->$endField]);
        }
    }

    /**
     * Menghitung statistik bulanan (1-12) dengan 1x SQL Query (Bebas N+1 Query).
     */
    private function getMonthlyStats($year, string $aggregateType)
    {
        $aggregateFn = $aggregateType === 'sum' ? 'SUM(amount_after_refraksi)' : 'COUNT(id)';
        
        $dbData = ApprovalPembayaran::where('status', self::STATUS_COMPLETED)
            ->where(fn($q) => $this->applyApprovalDateFilter($q, $year))
            ->selectRaw('COALESCE(MONTH(superadmin_approved_at), MONTH(updated_at)) as bulan, ' . $aggregateFn . ' as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $result = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $val = floatval($dbData[$bulan] ?? 0);
            $result[] = $aggregateType === 'count' ? (int) $val : $val;
        }

        return $result;
    }
}