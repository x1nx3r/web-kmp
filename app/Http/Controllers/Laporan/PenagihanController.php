<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Klien;
use App\Models\InvoicePenagihan;
use App\Models\CatatanPiutangPabrik;
use App\Models\ApprovalPenagihan;
use App\Models\PembayaranPiutangPabrik;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenagihanController extends Controller
{
    private const STATUS_COMPLETED = 'completed';

    public function index(Request $request)
    {
        // 1. Tangani Request AJAX (Delegasi Single Responsibility)
        if ($request->ajax()) {
            return $this->handleAjaxRequests($request);
        }

        // 2. Persiapkan Data Awal
        $title = 'Penagihan';
        $activeTab = 'penagihan';
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $totalPenagihan = $this->getCompletedInvoiceQuery()->sum('total_amount') ?? 0;
        
        $penagihanTahunIni = $this->getCompletedInvoiceQuery()
            ->whereYear('invoice_date', $currentYear)
            ->sum('total_amount') ?? 0;

        $penagihanBulanIni = $this->getCompletedInvoiceQuery()
            ->whereYear('invoice_date', $currentYear)
            ->whereMonth('invoice_date', $currentMonth)
            ->sum('total_amount') ?? 0;

        // Hitung Piutang menggunakan withSum untuk menghemat memori (hindari load seluruh relasi)
        $totalPiutangPabrik = $this->getCompletedInvoiceQuery()
            ->withSum('pembayaranPabrik', 'jumlah_bayar')
            ->get()
            ->sum(function($invoice) {
                $totalPaid = $invoice->pembayaran_pabrik_sum_jumlah_bayar ?? 0;
                $remaining = $invoice->total_amount - $totalPaid;
                return $remaining > 0 ? $remaining : 0;
            });

        // 3. Ambil parameter periode
        $periode = $request->get('periode', 'semua');
        $periodeKlien = $request->get('periode_klien', 'semua');
        $periodePiutangPabrik = $request->get('periode_piutang_pabrik', 'semua');
        $selectedYear = $request->get('tahun', $currentYear);
        $selectedYearInvoice = $request->get('tahun_invoice', $currentYear);

        // 4. Inisialisasi Grafik Klien
        $penagihanKlienQuery = $this->getCompletedInvoiceQuery()
            ->select('customer_name', DB::raw('SUM(total_amount) as total'))
            ->groupBy('customer_name');
            
        // Perilaku asli: Initial load hanya memfilter tahun_ini atau bulan_ini, abaikan custom
        if ($periode === 'tahun_ini') {
            $penagihanKlienQuery->whereYear('invoice_date', $currentYear);
        } elseif ($periode === 'bulan_ini') {
            $penagihanKlienQuery->whereYear('invoice_date', $currentYear)
                                ->whereMonth('invoice_date', $currentMonth);
        }
        $penagihanKlien = $penagihanKlienQuery->orderBy('total', 'desc')->limit(10)->get();

        // 5. Inisialisasi Top Klien
        $topKlien = $this->getCompletedInvoiceQuery()
            ->select('customer_name', 'customer_address', DB::raw('SUM(total_amount) as total'))
            ->groupBy('customer_name', 'customer_address')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // 6. Inisialisasi Top Piutang Pabrik
        $invoicesForPiutang = $this->getCompletedInvoiceQuery()
            ->withSum('pembayaranPabrik', 'jumlah_bayar')
            ->with('pengiriman.klien')
            ->get();
        
        $topPiutangPabrikData = $this->calculatePiutangByKlien($invoicesForPiutang);
        $topPiutangPabrik = collect($topPiutangPabrikData);

        // 7. Kalkulasi Bulanan (N+1 Resolved)
        $penagihanPerBulan = $this->getMonthlyStats($selectedYear, 'sum');
        $jumlahInvoicePerBulan = $this->getMonthlyStats($selectedYearInvoice, 'count');

        // 8. Ketersediaan Tahun
        $availableYears = $this->getCompletedInvoiceQuery()
            ->selectRaw('DISTINCT YEAR(invoice_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [$currentYear];
        }

        return view('pages.laporan.penagihan', compact(
            'title', 'activeTab', 'totalPenagihan', 'penagihanTahunIni', 'penagihanBulanIni',
            'totalPiutangPabrik', 'penagihanKlien', 'topKlien', 'topPiutangPabrik',
            'penagihanPerBulan', 'jumlahInvoicePerBulan', 'selectedYear',
            'selectedYearInvoice', 'availableYears', 'periode', 'periodeKlien',
            'periodePiutangPabrik'
        ));
    }

    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }

    /**
     * ----------------------------------------------------------------------
     * PRIVATE METHODS & HELPERS
     * ----------------------------------------------------------------------
     */

    private function handleAjaxRequests(Request $request)
    {
        return match ($request->get('ajax')) {
            'penagihan_klien' => $this->ajaxPenagihanKlien($request),
            'top_klien' => $this->ajaxTopKlien($request),
            'penagihan_per_bulan' => $this->ajaxPenagihanPerBulan($request),
            'jumlah_invoice_per_bulan' => $this->ajaxJumlahInvoicePerBulan($request),
            'piutang_pabrik' => $this->ajaxPiutangPabrik($request),
            default => response()->json(['error' => 'Invalid AJAX request'], 400),
        };
    }

    private function ajaxPenagihanKlien(Request $request)
    {
        $query = $this->getCompletedInvoiceQuery()
            ->select('customer_name', DB::raw('SUM(total_amount) as total'))
            ->groupBy('customer_name');

        $this->applyPeriodFilter($query, $request->get('periode'), $request);

        $data = $query->orderBy('total', 'desc')->limit(10)->get()
            ->map(fn($item) => [
                'nama' => $item->customer_name,
                'total' => floatval($item->total ?? 0)
            ])->filter(fn($item) => $item['total'] > 0)->values();

        return response()->json($data);
    }

    private function ajaxTopKlien(Request $request)
    {
        $query = $this->getCompletedInvoiceQuery()
            ->select('customer_name', 'customer_address', DB::raw('SUM(total_amount) as total'))
            ->groupBy('customer_name', 'customer_address');

        $this->applyPeriodFilter($query, $request->get('periode_klien'), $request, 'klien');

        $data = $query->orderBy('total', 'desc')->limit(10)->get()
            ->map(fn($item) => [
                'nama' => $item->customer_name,
                'alamat' => $item->customer_address,
                'total' => floatval($item->total ?? 0)
            ])->filter(fn($item) => $item['total'] > 0)->values();

        return response()->json($data);
    }

    private function ajaxPenagihanPerBulan(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        return response()->json([
            'data' => $this->getMonthlyStats($tahun, 'sum'),
            'tahun' => $tahun
        ]);
    }

    private function ajaxJumlahInvoicePerBulan(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        return response()->json([
            'data' => $this->getMonthlyStats($tahun, 'count'),
            'tahun' => $tahun
        ]);
    }

    private function ajaxPiutangPabrik(Request $request)
    {
        $query = $this->getCompletedInvoiceQuery()
            ->withSum('pembayaranPabrik', 'jumlah_bayar')
            ->with('pengiriman.klien');

        $periode = $request->get('periode', 'semua');
        if ($periode === 'tahun_ini') {
            $query->whereYear('invoice_date', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('invoice_date', Carbon::now()->year)->whereMonth('invoice_date', Carbon::now()->month);
        }

        $data = $this->calculatePiutangByKlien($query->get());
        return response()->json($data);
    }

    /**
     * Sentralisasi kueri dasar untuk invoice yang berstatus completed.
     */
    private function getCompletedInvoiceQuery()
    {
        return InvoicePenagihan::whereHas('approvalPenagihan', function($query) {
            $query->where('status', self::STATUS_COMPLETED);
        });
    }

    /**
     * Sentralisasi logika pengelompokan Piutang Klien (Digunakan AJAX & View).
     */
    private function calculatePiutangByKlien($invoices)
    {
        $piutangByKlien = [];
        foreach ($invoices as $invoice) {
            $totalPaid = $invoice->pembayaran_pabrik_sum_jumlah_bayar ?? 0;
            $remaining = $invoice->total_amount - $totalPaid;

            if ($remaining > 0) {
                $klien = $invoice->pengiriman->klien ?? null;
                $klienId = $klien ? $klien->id : 0;
                $klienNama = $klien ? $klien->nama : $invoice->customer_name;
                $klienAlamat = $klien ? $klien->alamat : $invoice->customer_address;

                if (!isset($piutangByKlien[$klienId])) {
                    $piutangByKlien[$klienId] = [
                        'id' => $klienId,
                        'nama' => $klienNama,
                        'alamat' => $klienAlamat,
                        'total' => 0
                    ];
                }
                $piutangByKlien[$klienId]['total'] += $remaining;
            }
        }

        usort($piutangByKlien, fn($a, $b) => $b['total'] <=> $a['total']);
        return array_slice(array_values($piutangByKlien), 0, 10);
    }

    /**
     * Terapkan filter periode secara terpusat dan aman (Dengan Validasi).
     */
    private function applyPeriodFilter($query, $periode, Request $request, $prefix = '')
    {
        $startField = $prefix ? "start_date_{$prefix}" : 'start_date';
        $endField = $prefix ? "end_date_{$prefix}" : 'end_date';

        if ($periode === 'tahun_ini') {
            $query->whereYear('invoice_date', Carbon::now()->year);
        } elseif ($periode === 'bulan_ini') {
            $query->whereYear('invoice_date', Carbon::now()->year)->whereMonth('invoice_date', Carbon::now()->month);
        } elseif ($periode === 'custom' && $request->filled([$startField, $endField])) {
            $request->validate([$startField => 'date', $endField => 'date']);
            $query->whereBetween('invoice_date', [$request->$startField, $request->$endField]);
        }
    }

    /**
     * Menghitung statistik bulanan dalam 1 kali eksekusi SQL (Menyelesaikan N+1).
     */
    private function getMonthlyStats($year, string $type)
    {
        $aggregateFn = $type === 'sum' ? 'SUM(total_amount)' : 'COUNT(id)';
        
        $stats = $this->getCompletedInvoiceQuery()
            ->selectRaw("MONTH(invoice_date) as bulan, {$aggregateFn} as total")
            ->whereYear('invoice_date', $year)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $result = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $val = $stats[$bulan] ?? 0;
            $result[] = $type === 'sum' ? floatval($val) : intval($val);
        }

        return $result;
    }
}