<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Klien;
use App\Models\InvoicePenagihan;
use App\Models\CatatanPiutang;
use App\Models\CatatanPiutangPabrik;
use App\Models\ApprovalPenagihan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenagihanController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Penagihan';
        $activeTab = 'penagihan';

        // Calculate Total Penagihan (completed invoices)
        $totalPenagihan = InvoicePenagihan::whereHas('approvalPenagihan', function($query) {
            $query->where('status', 'completed');
        })->sum('total_amount') ?? 0;

        // Calculate Penagihan Tahun Ini
        $penagihanTahunIni = InvoicePenagihan::whereHas('approvalPenagihan', function($query) {
            $query->where('status', 'completed');
        })->whereYear('invoice_date', Carbon::now()->year)
            ->sum('total_amount') ?? 0;

        // Calculate Penagihan Bulan Ini
        $penagihanBulanIni = InvoicePenagihan::whereHas('approvalPenagihan', function($query) {
            $query->where('status', 'completed');
        })->whereYear('invoice_date', Carbon::now()->year)
            ->whereMonth('invoice_date', Carbon::now()->month)
            ->sum('total_amount') ?? 0;

        // Calculate Total Piutang Supplier (belum lunas) - from catatan_piutangs table
        $totalPiutangSupplier = CatatanPiutang::where('status', '!=', 'lunas')
            ->sum('sisa_piutang') ?? 0;

        // Calculate Total Piutang Pabrik (belum lunas) - from catatan_piutang_pabriks table
        $totalPiutangPabrik = CatatanPiutangPabrik::where('status', '!=', 'lunas')
            ->sum('sisa_piutang') ?? 0;

        // Get filter periode
        $periode = $request->get('periode', 'semua');
        $periodeKlien = $request->get('periode_klien', 'semua');
        $periodePiutangSupplier = $request->get('periode_piutang_supplier', 'semua');
        $periodePiutangPabrik = $request->get('periode_piutang_pabrik', 'semua');

        // Handle AJAX request for Penagihan Per Klien
        if ($request->ajax() && $request->get('ajax') === 'penagihan_klien') {
            $penagihanQuery = InvoicePenagihan::select('customer_name', DB::raw('SUM(total_amount) as total'))
                ->whereHas('approvalPenagihan', function($query) {
                    $query->where('status', 'completed');
                })
                ->groupBy('customer_name');

            // Apply filter
            if ($periode === 'tahun_ini') {
                $penagihanQuery->whereYear('invoice_date', Carbon::now()->year);
            } elseif ($periode === 'bulan_ini') {
                $penagihanQuery->whereYear('invoice_date', Carbon::now()->year)
                    ->whereMonth('invoice_date', Carbon::now()->month);
            } elseif ($periode === 'custom' && $request->filled(['start_date', 'end_date'])) {
                $penagihanQuery->whereBetween('invoice_date', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            $data = $penagihanQuery->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'nama' => $item->customer_name,
                        'total' => floatval($item->total ?? 0)
                    ];
                })->filter(function($item) {
                    return $item['total'] > 0;
                })->values();

            return response()->json($data);
        }

        // Handle AJAX request for Top Klien by Penagihan
        if ($request->ajax() && $request->get('ajax') === 'top_klien') {
            $topKlienQuery = InvoicePenagihan::select('customer_name', 'customer_address', DB::raw('SUM(total_amount) as total'))
                ->whereHas('approvalPenagihan', function($query) {
                    $query->where('status', 'completed');
                })
                ->groupBy('customer_name', 'customer_address');

            // Apply filter
            if ($periodeKlien === 'tahun_ini') {
                $topKlienQuery->whereYear('invoice_date', Carbon::now()->year);
            } elseif ($periodeKlien === 'bulan_ini') {
                $topKlienQuery->whereYear('invoice_date', Carbon::now()->year)
                    ->whereMonth('invoice_date', Carbon::now()->month);
            } elseif ($periodeKlien === 'custom' && $request->filled(['start_date_klien', 'end_date_klien'])) {
                $topKlienQuery->whereBetween('invoice_date', [
                    $request->start_date_klien,
                    $request->end_date_klien
                ]);
            }

            $data = $topKlienQuery->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'nama' => $item->customer_name,
                        'alamat' => $item->customer_address,
                        'total' => floatval($item->total ?? 0)
                    ];
                })->filter(function($item) {
                    return $item['total'] > 0;
                })->values();

            return response()->json($data);
        }

        // Handle AJAX request for Penagihan Per Bulan
        if ($request->ajax() && $request->get('ajax') === 'penagihan_per_bulan') {
            $tahun = $request->get('tahun', Carbon::now()->year);

            $penagihanPerBulan = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $total = InvoicePenagihan::whereHas('approvalPenagihan', function($query) {
                    $query->where('status', 'completed');
                })->whereYear('invoice_date', $tahun)
                    ->whereMonth('invoice_date', $bulan)
                    ->sum('total_amount');
                $penagihanPerBulan[] = floatval($total ?? 0);
            }

            return response()->json([
                'data' => $penagihanPerBulan,
                'tahun' => $tahun
            ]);
        }

        // Handle AJAX request for Jumlah Invoice Per Bulan
        if ($request->ajax() && $request->get('ajax') === 'jumlah_invoice_per_bulan') {
            $tahun = $request->get('tahun', Carbon::now()->year);

            $jumlahInvoicePerBulan = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $count = InvoicePenagihan::whereHas('approvalPenagihan', function($query) {
                    $query->where('status', 'completed');
                })->whereYear('invoice_date', $tahun)
                    ->whereMonth('invoice_date', $bulan)
                    ->count();
                $jumlahInvoicePerBulan[] = $count;
            }

            return response()->json([
                'data' => $jumlahInvoicePerBulan,
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
            if ($periodePiutangSupplier === 'tahun_ini') {
                $piutangQuery->whereYear('created_at', Carbon::now()->year);
            } elseif ($periodePiutangSupplier === 'bulan_ini') {
                $piutangQuery->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month);
            } elseif ($periodePiutangSupplier === 'custom' && $request->filled(['start_date_piutang_supplier', 'end_date_piutang_supplier'])) {
                $piutangQuery->whereBetween('created_at', [
                    $request->start_date_piutang_supplier,
                    $request->end_date_piutang_supplier
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

        // Handle AJAX request for Top Piutang Pabrik
        if ($request->ajax() && $request->get('ajax') === 'piutang_pabrik') {
            $piutangQuery = CatatanPiutangPabrik::select('klien_id', DB::raw('SUM(sisa_piutang) as total'))
                ->where('status', '!=', 'lunas')
                ->with('klien:id,nama,alamat')
                ->groupBy('klien_id');

            // Apply filter
            if ($periodePiutangPabrik === 'tahun_ini') {
                $piutangQuery->whereYear('created_at', Carbon::now()->year);
            } elseif ($periodePiutangPabrik === 'bulan_ini') {
                $piutangQuery->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month);
            } elseif ($periodePiutangPabrik === 'custom' && $request->filled(['start_date_piutang_pabrik', 'end_date_piutang_pabrik'])) {
                $piutangQuery->whereBetween('created_at', [
                    $request->start_date_piutang_pabrik,
                    $request->end_date_piutang_pabrik
                ]);
            }

            $data = $piutangQuery->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'nama' => $item->klien ? $item->klien->nama : 'Unknown',
                        'alamat' => $item->klien ? $item->klien->alamat : null,
                        'total' => floatval($item->total ?? 0)
                    ];
                })->filter(function($item) {
                    return $item['total'] > 0;
                })->values();

            return response()->json($data);
        }

        // Initial data for charts
        $penagihanKlien = InvoicePenagihan::select('customer_name', DB::raw('SUM(total_amount) as total'))
            ->whereHas('approvalPenagihan', function($query) {
                $query->where('status', 'completed');
            })
            ->groupBy('customer_name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Apply periode filter for initial load
        if ($periode === 'tahun_ini') {
            $penagihanKlien = InvoicePenagihan::select('customer_name', DB::raw('SUM(total_amount) as total'))
                ->whereHas('approvalPenagihan', function($query) {
                    $query->where('status', 'completed');
                })
                ->whereYear('invoice_date', Carbon::now()->year)
                ->groupBy('customer_name')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();
        } elseif ($periode === 'bulan_ini') {
            $penagihanKlien = InvoicePenagihan::select('customer_name', DB::raw('SUM(total_amount) as total'))
                ->whereHas('approvalPenagihan', function($query) {
                    $query->where('status', 'completed');
                })
                ->whereYear('invoice_date', Carbon::now()->year)
                ->whereMonth('invoice_date', Carbon::now()->month)
                ->groupBy('customer_name')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();
        }

        // Top Klien
        $topKlien = InvoicePenagihan::select('customer_name', 'customer_address', DB::raw('SUM(total_amount) as total'))
            ->whereHas('approvalPenagihan', function($query) {
                $query->where('status', 'completed');
            })
            ->groupBy('customer_name', 'customer_address')
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

        // Top Piutang Pabrik
        $topPiutangPabrik = CatatanPiutangPabrik::select('klien_id', DB::raw('SUM(sisa_piutang) as total'))
            ->where('status', '!=', 'lunas')
            ->with('klien:id,nama,alamat')
            ->groupBy('klien_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Penagihan per bulan (current year)
        $selectedYear = $request->get('tahun', Carbon::now()->year);
        $selectedYearInvoice = $request->get('tahun_invoice', Carbon::now()->year);

        $penagihanPerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total = InvoicePenagihan::whereHas('approvalPenagihan', function($query) {
                $query->where('status', 'completed');
            })->whereYear('invoice_date', $selectedYear)
                ->whereMonth('invoice_date', $bulan)
                ->sum('total_amount');
            $penagihanPerBulan[] = floatval($total ?? 0);
        }

        // Jumlah Invoice per bulan (current year)
        $jumlahInvoicePerBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $count = InvoicePenagihan::whereHas('approvalPenagihan', function($query) {
                $query->where('status', 'completed');
            })->whereYear('invoice_date', $selectedYearInvoice)
                ->whereMonth('invoice_date', $bulan)
                ->count();
            $jumlahInvoicePerBulan[] = $count;
        }

        // Get available years
        $availableYears = InvoicePenagihan::selectRaw('DISTINCT YEAR(invoice_date) as year')
            ->whereHas('approvalPenagihan', function($query) {
                $query->where('status', 'completed');
            })
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        }

        return view('pages.laporan.penagihan', compact(
            'title',
            'activeTab',
            'totalPenagihan',
            'penagihanTahunIni',
            'penagihanBulanIni',
            'totalPiutangSupplier',
            'totalPiutangPabrik',
            'penagihanKlien',
            'topKlien',
            'topPiutangSupplier',
            'topPiutangPabrik',
            'penagihanPerBulan',
            'jumlahInvoicePerBulan',
            'selectedYear',
            'selectedYearInvoice',
            'availableYears',
            'periode',
            'periodeKlien',
            'periodePiutangSupplier',
            'periodePiutangPabrik'
        ));
    }

    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
}
