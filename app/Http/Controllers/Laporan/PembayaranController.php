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
    public function index(Request $request)
    {
        $title = 'Pembayaran';
        $activeTab = 'pembayaran';

        // Calculate Total Pembayaran (completed approvals)
        $totalPembayaran = ApprovalPembayaran::where('status', 'completed')
            ->sum('amount_after_refraksi') ?? 0;

        // Calculate Pembayaran Tahun Ini - use updated_at as fallback if superadmin_approved_at is null
        $pembayaranTahunIni = ApprovalPembayaran::where('status', 'completed')
            ->where(function($query) {
                $query->whereYear('superadmin_approved_at', Carbon::now()->year)
                    ->orWhere(function($q) {
                        $q->whereNull('superadmin_approved_at')
                          ->whereYear('updated_at', Carbon::now()->year);
                    });
            })
            ->sum('amount_after_refraksi') ?? 0;

        // Calculate Pembayaran Bulan Ini
        $pembayaranBulanIni = ApprovalPembayaran::where('status', 'completed')
            ->where(function($query) {
                $query->where(function($q) {
                    $q->whereYear('superadmin_approved_at', Carbon::now()->year)
                      ->whereMonth('superadmin_approved_at', Carbon::now()->month);
                })->orWhere(function($q) {
                    $q->whereNull('superadmin_approved_at')
                      ->whereYear('updated_at', Carbon::now()->year)
                      ->whereMonth('updated_at', Carbon::now()->month);
                });
            })
            ->sum('amount_after_refraksi') ?? 0;

        // Calculate Total Piutang Supplier (belum lunas)
        $totalPiutangSupplier = CatatanPiutang::where('status', '!=', 'lunas')
            ->sum('sisa_piutang') ?? 0;

        // Calculate Jumlah Transaksi Pembayaran
        $jumlahTransaksi = ApprovalPembayaran::where('status', 'completed')->count();

        // Get filter periode
        $periode = $request->get('periode', 'semua');
        $periodeSupplier = $request->get('periode_supplier', 'semua');
        $periodePiutang = $request->get('periode_piutang', 'semua');

        // Handle AJAX request for Pembayaran Per Supplier
        if ($request->ajax() && $request->get('ajax') === 'pembayaran_supplier') {
            $pembayaranQuery = ApprovalPembayaran::select('suppliers.nama as supplier_name', DB::raw('SUM(approval_pembayaran.amount_after_refraksi) as total'))
                ->join('pengiriman', 'approval_pembayaran.pengiriman_id', '=', 'pengiriman.id')
                ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->where('approval_pembayaran.status', 'completed')
                ->whereNull('pengiriman_details.deleted_at')
                ->groupBy('suppliers.id', 'suppliers.nama');

            // Apply filter
            if ($periode === 'tahun_ini') {
                $pembayaranQuery->whereYear('approval_pembayaran.superadmin_approved_at', Carbon::now()->year);
            } elseif ($periode === 'bulan_ini') {
                $pembayaranQuery->whereYear('approval_pembayaran.superadmin_approved_at', Carbon::now()->year)
                    ->whereMonth('approval_pembayaran.superadmin_approved_at', Carbon::now()->month);
            } elseif ($periode === 'custom' && $request->filled(['start_date', 'end_date'])) {
                $pembayaranQuery->whereBetween('approval_pembayaran.superadmin_approved_at', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            $data = $pembayaranQuery->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'nama' => $item->supplier_name ?? 'Unknown',
                        'total' => floatval($item->total ?? 0)
                    ];
                })->filter(function($item) {
                    return $item['total'] > 0;
                })->values();

            return response()->json($data);
        }

        // Handle AJAX request for Top Supplier
        if ($request->ajax() && $request->get('ajax') === 'top_supplier') {
            $topSupplierQuery = ApprovalPembayaran::select('suppliers.nama as supplier_name', 'suppliers.alamat as supplier_address', DB::raw('SUM(approval_pembayaran.amount_after_refraksi) as total'))
                ->join('pengiriman', 'approval_pembayaran.pengiriman_id', '=', 'pengiriman.id')
                ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->where('approval_pembayaran.status', 'completed')
                ->whereNull('pengiriman_details.deleted_at')
                ->groupBy('suppliers.id', 'suppliers.nama', 'suppliers.alamat');

            // Apply filter
            if ($periodeSupplier === 'tahun_ini') {
                $topSupplierQuery->whereYear('approval_pembayaran.superadmin_approved_at', Carbon::now()->year);
            } elseif ($periodeSupplier === 'bulan_ini') {
                $topSupplierQuery->whereYear('approval_pembayaran.superadmin_approved_at', Carbon::now()->year)
                    ->whereMonth('approval_pembayaran.superadmin_approved_at', Carbon::now()->month);
            } elseif ($periodeSupplier === 'custom' && $request->filled(['start_date_supplier', 'end_date_supplier'])) {
                $topSupplierQuery->whereBetween('approval_pembayaran.superadmin_approved_at', [
                    $request->start_date_supplier,
                    $request->end_date_supplier
                ]);
            }

            $data = $topSupplierQuery->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'nama' => $item->supplier_name ?? 'Unknown',
                        'alamat' => $item->supplier_address ?? null,
                        'total' => floatval($item->total ?? 0)
                    ];
                })->filter(function($item) {
                    return $item['total'] > 0;
                })->values();

            return response()->json($data);
        }

        // Handle AJAX request for Pembayaran Per Bulan
        if ($request->ajax() && $request->get('ajax') === 'pembayaran_per_bulan') {
            $tahun = $request->get('tahun', Carbon::now()->year);

            $pembayaranPerBulan = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $total = ApprovalPembayaran::where('status', 'completed')
                    ->where(function($query) use ($tahun, $bulan) {
                        $query->where(function($q) use ($tahun, $bulan) {
                            $q->whereYear('superadmin_approved_at', $tahun)
                              ->whereMonth('superadmin_approved_at', $bulan);
                        })->orWhere(function($q) use ($tahun, $bulan) {
                            $q->whereNull('superadmin_approved_at')
                              ->whereYear('updated_at', $tahun)
                              ->whereMonth('updated_at', $bulan);
                        });
                    })
                    ->sum('amount_after_refraksi');
                $pembayaranPerBulan[] = floatval($total ?? 0);
            }

            return response()->json([
                'data' => $pembayaranPerBulan,
                'tahun' => $tahun
            ]);
        }

        // Handle AJAX request for Jumlah Transaksi Per Bulan
        if ($request->ajax() && $request->get('ajax') === 'jumlah_transaksi_per_bulan') {
            $tahun = $request->get('tahun', Carbon::now()->year);

            $jumlahTransaksiPerBulan = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $count = ApprovalPembayaran::where('status', 'completed')
                    ->where(function($query) use ($tahun, $bulan) {
                        $query->where(function($q) use ($tahun, $bulan) {
                            $q->whereYear('superadmin_approved_at', $tahun)
                              ->whereMonth('superadmin_approved_at', $bulan);
                        })->orWhere(function($q) use ($tahun, $bulan) {
                            $q->whereNull('superadmin_approved_at')
                              ->whereYear('updated_at', $tahun)
                              ->whereMonth('updated_at', $bulan);
                        });
                    })
                    ->count();
                $jumlahTransaksiPerBulan[] = $count;
            }

            return response()->json([
                'data' => $jumlahTransaksiPerBulan,
                'tahun' => $tahun
            ]);
        }

        // Handle AJAX request for Top Piutang Supplier
        if ($request->ajax() && $request->get('ajax') === 'piutang_supplier') {
            $piutangQuery = CatatanPiutang::select('supplier_id', DB::raw('SUM(sisa_piutang) as total'))
                ->where('status', '!=', 'lunas')
                ->with('supplier:id,nama,alamat')
                ->groupBy('supplier_id');

            // Apply filter
            if ($periodePiutang === 'tahun_ini') {
                $piutangQuery->whereYear('created_at', Carbon::now()->year);
            } elseif ($periodePiutang === 'bulan_ini') {
                $piutangQuery->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month);
            } elseif ($periodePiutang === 'custom' && $request->filled(['start_date_piutang', 'end_date_piutang'])) {
                $piutangQuery->whereBetween('created_at', [
                    $request->start_date_piutang,
                    $request->end_date_piutang
                ]);
            }

            $data = $piutangQuery->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'nama' => $item->supplier ? $item->supplier->nama : 'Unknown',
                        'alamat' => $item->supplier ? $item->supplier->alamat : null,
                        'total' => floatval($item->total ?? 0)
                    ];
                })->filter(function($item) {
                    return $item['total'] > 0;
                })->values();

            return response()->json($data);
        }

        // Initial data for charts
        $pembayaranSupplier = ApprovalPembayaran::select('suppliers.nama as supplier_name', DB::raw('SUM(approval_pembayaran.amount_after_refraksi) as total'))
            ->join('pengiriman', 'approval_pembayaran.pengiriman_id', '=', 'pengiriman.id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->where('approval_pembayaran.status', 'completed')
            ->whereNull('pengiriman_details.deleted_at')
            ->groupBy('suppliers.id', 'suppliers.nama')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Top Supplier
        $topSupplier = ApprovalPembayaran::select('suppliers.nama as supplier_name', 'suppliers.alamat as supplier_address', DB::raw('SUM(approval_pembayaran.amount_after_refraksi) as total'))
            ->join('pengiriman', 'approval_pembayaran.pengiriman_id', '=', 'pengiriman.id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->where('approval_pembayaran.status', 'completed')
            ->whereNull('pengiriman_details.deleted_at')
            ->groupBy('suppliers.id', 'suppliers.nama', 'suppliers.alamat')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Top Piutang Supplier
        $topPiutangSupplier = CatatanPiutang::select('supplier_id', DB::raw('SUM(sisa_piutang) as total'))
            ->where('status', '!=', 'lunas')
            ->with('supplier:id,nama,alamat')
            ->groupBy('supplier_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Pembayaran per bulan (current year)
        $selectedYear = $request->get('tahun', Carbon::now()->year);
        $selectedYearTransaksi = $request->get('tahun_transaksi', Carbon::now()->year);

        $pembayaranPerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total = ApprovalPembayaran::where('status', 'completed')
                ->where(function($query) use ($selectedYear, $bulan) {
                    $query->where(function($q) use ($selectedYear, $bulan) {
                        $q->whereYear('superadmin_approved_at', $selectedYear)
                          ->whereMonth('superadmin_approved_at', $bulan);
                    })->orWhere(function($q) use ($selectedYear, $bulan) {
                        $q->whereNull('superadmin_approved_at')
                          ->whereYear('updated_at', $selectedYear)
                          ->whereMonth('updated_at', $bulan);
                    });
                })
                ->sum('amount_after_refraksi');
            $pembayaranPerBulan[] = floatval($total ?? 0);
        }

        // Jumlah Transaksi per bulan (current year)
        $jumlahTransaksiPerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $count = ApprovalPembayaran::where('status', 'completed')
                ->where(function($query) use ($selectedYearTransaksi, $bulan) {
                    $query->where(function($q) use ($selectedYearTransaksi, $bulan) {
                        $q->whereYear('superadmin_approved_at', $selectedYearTransaksi)
                          ->whereMonth('superadmin_approved_at', $bulan);
                    })->orWhere(function($q) use ($selectedYearTransaksi, $bulan) {
                        $q->whereNull('superadmin_approved_at')
                          ->whereYear('updated_at', $selectedYearTransaksi)
                          ->whereMonth('updated_at', $bulan);
                    });
                })
                ->count();
            $jumlahTransaksiPerBulan[] = $count;
        }

        // Get available years - use updated_at as fallback
        $availableYears = ApprovalPembayaran::where('status', 'completed')
            ->selectRaw('DISTINCT YEAR(COALESCE(superadmin_approved_at, updated_at)) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        }

        return view('pages.laporan.pembayaran', compact(
            'title',
            'activeTab',
            'totalPembayaran',
            'pembayaranTahunIni',
            'pembayaranBulanIni',
            'totalPiutangSupplier',
            'jumlahTransaksi',
            'pembayaranSupplier',
            'topSupplier',
            'topPiutangSupplier',
            'pembayaranPerBulan',
            'jumlahTransaksiPerBulan',
            'selectedYear',
            'selectedYearTransaksi',
            'availableYears',
            'periode',
            'periodeSupplier',
            'periodePiutang'
        ));
    }

    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
}
