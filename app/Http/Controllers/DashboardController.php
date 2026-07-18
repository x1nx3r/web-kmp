<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Pengiriman;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MarginExport;
use App\Services\DashboardService;
use App\Services\ChartService;

class DashboardController extends Controller
{
    /**
     * Status pengiriman yang dianggap "aktif" untuk perhitungan omset & margin.
     *
     * Diekstrak sebagai konstanta untuk menghilangkan duplikasi array literal
     * yang sebelumnya ditulis ulang identik di 4 lokasi berbeda pada file ini.
     * Isi array TIDAK berubah dari versi sebelumnya.
     */
    private const VALID_PENGIRIMAN_STATUSES = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];

    /**
     * Helper: tambahkan kondisi exclude pengiriman yang semua invoice-nya berstatus "digabung".
     * Pengiriman tanpa invoice sama sekali tetap dimasukkan (pakai fallback qty * harga_jual).
     *
     * Catatan: method ini juga terdapat di App\Services\DashboardService dengan implementasi
     * identik. Duplikasi ini TIDAK dihapus dalam refactoring ini karena menghapus salah satu
     * versi memerlukan perubahan simultan pada file Service yang tidak termasuk dalam cakupan
     * audit/refactor saat ini. Direkomendasikan sebagai tindak lanjut terpisah.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
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
     * Normalisasi nama bahan baku agar variasi penulisan/alias tergabung dalam 1 kategori.
     *
     * Dipertahankan apa adanya (tidak dihapus) meski tidak terpanggil di dalam file ini,
     * karena tanpa memeriksa seluruh codebase (View, method lain di luar cakupan audit)
     * tidak dapat dipastikan method ini benar-benar dead code.
     */
    private function normalizeBahanBakuName(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '-';
        }

        $key = mb_strtolower($name);
        $key = preg_replace('/\s+/', ' ', $key);

        $synonyms = [
            'tepung biskuit' => 'Tepung biskuit',
            'biscuit meal'   => 'Tepung biskuit',
            'biskuit meal'   => 'Tepung biskuit',
            'biskuit  meal'  => 'Tepung biskuit',
            'tepung roti'    => 'Tepung biskuit',
            'mie kuning'     => 'Mie kuning',
            'noodle broken'  => 'Mie kuning',
            'tepung mie'     => 'Mie kuning',
        ];

        if (isset($synonyms[$key])) {
            return $synonyms[$key];
        }

        return ucwords($name);
    }

    /**
     * Hitung range tanggal default minggu berjalan (pembagian bulan 1-7, 8-14, 15-21, 22-akhir).
     *
     * @return array{start: Carbon, end: Carbon}
     */
    private function getDefaultWeekRange(): array
    {
        $resolved = $this->resolveCurrentWeek();

        return [
            'start' => $resolved['start'],
            'end'   => $resolved['end'],
        ];
    }

    /**
     * Helper kalkulasi week-of-month dari tanggal hari ini.
     * Mengembalikan int 1-4 dan Carbon startOfMonth.
     *
     * @return array{week: int, startOfMonth: Carbon}
     */
    private function getCurrentWeekOfMonth(): array
    {
        $today        = Carbon::now();
        $dayOfMonth   = $today->day;
        $startOfMonth = Carbon::now()->startOfMonth();

        if ($dayOfMonth >= 1 && $dayOfMonth <= 7) {
            $week = 1;
        } elseif ($dayOfMonth >= 8 && $dayOfMonth <= 14) {
            $week = 2;
        } elseif ($dayOfMonth >= 15 && $dayOfMonth <= 21) {
            $week = 3;
        } else {
            $week = 4;
        }

        return ['week' => $week, 'startOfMonth' => $startOfMonth];
    }

    /**
     * Satu sumber kebenaran untuk rumus penentuan minggu berjalan (nomor minggu + rentang
     * tanggal). Rumus ini sebelumnya ditulis ulang secara identik di getDefaultWeekRange(),
     * downloadMarginMingguIniPdf(), dan downloadMarginMingguIniExcel(). Hasil perhitungan
     * TIDAK berubah dari versi sebelumnya.
     *
     * @return array{week: int, start: Carbon, end: Carbon}
     */
    private function resolveCurrentWeek(): array
    {
        ['week' => $currentWeekOfMonth, 'startOfMonth' => $startOfMonth] = $this->getCurrentWeekOfMonth();

        $startOfWeek = $currentWeekOfMonth === 1
            ? $startOfMonth->copy()
            : $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);

        $endOfWeek = $currentWeekOfMonth === 4
            ? $startOfMonth->copy()->endOfMonth()
            : $startOfWeek->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());

        return [
            'week'  => $currentWeekOfMonth,
            'start' => $startOfWeek,
            'end'   => $endOfWeek,
        ];
    }

    /**
     * Daftar relasi eager-load yang dibutuhkan oleh hitungMarginDariPengiriman().
     *
     * Sebelumnya, query margin "bulan ini" (di index() dan PDF) hanya memuat sebagian relasi
     * ini (tanpa purchasing, order.klien, order.winner.user) padahal hitungMarginDariPengiriman()
     * selalu mengakses relasi-relasi tersebut — artinya query itu memicu lazy-load N+1 secara
     * diam-diam. Menyatukan daftar relasi di satu tempat dan memakainya secara konsisten
     * menghilangkan N+1 tersebut TANPA mengubah data yang dihasilkan (nilai relasi yang diakses
     * tetap sama, hanya cara pengambilannya yang lebih efisien).
     *
     * @return array<int, string>
     */
    private function marginEagerLoadRelations(): array
    {
        return [
            'purchasing:id,nama',
            'order.klien:id,nama,cabang',
            'order.winner.user:id,nama',
            'pengirimanDetails.bahanBakuSupplier.supplier:id,nama',
            'pengirimanDetails.bahanBakuSupplier:id,nama,supplier_id',
            'pengirimanDetails.orderDetail.bahanBakuKlien:id,nama',
            'approvalPembayaran',
            'invoicePenagihan',
        ];
    }

    /**
     * Ambil pengiriman untuk perhitungan margin dalam suatu rentang tanggal.
     * Menggantikan blok query yang sebelumnya ditulis ulang identik di index()
     * dan kedua method download.
     */
    private function getPengirimanForMarginByRange(Carbon $start, Carbon $end): Collection
    {
        return Pengiriman::with($this->marginEagerLoadRelations())
            ->whereIn('status', self::VALID_PENGIRIMAN_STATUSES)
            ->whereBetween('tanggal_kirim', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('tanggal_kirim', 'asc')
            ->get();
    }

    /**
     * Ambil pengiriman untuk perhitungan gross margin bulan berjalan.
     * Menggantikan blok query "margin bulan ini" yang sebelumnya ditulis ulang
     * identik di index() dan downloadMarginMingguIniPdf().
     */
    private function getPengirimanForMarginByMonth(int $year, int $month): Collection
    {
        return Pengiriman::with($this->marginEagerLoadRelations())
            ->whereIn('status', self::VALID_PENGIRIMAN_STATUSES)
            ->whereYear('tanggal_kirim', $year)
            ->whereMonth('tanggal_kirim', $month)
            ->get();
    }

    /**
     * Ambil data invoice_penagihan (row lengkap) dan total gross sales per invoice sekaligus
     * dalam 1-2 query batch (menghindari N+1). Sama persis dengan
     * MarginController::loadInvoiceDataForPengirimanList() — dipertahankan sebagai duplikat di
     * sini karena Dashboard dan Margin report saat ini bukan berbagi trait/service yang sama
     * (lihat catatan duplikasi di applyValidInvoiceFilter() di atas).
     *
     * @return array{invoices: \Illuminate\Support\Collection, grossTotals: \Illuminate\Support\Collection}
     */
    private function loadInvoiceDataForPengirimanList($pengirimanList): array
    {
        $invoiceIds = collect($pengirimanList)
            ->pluck('invoice_penagihan_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($invoiceIds)) {
            return ['invoices' => collect(), 'grossTotals' => collect()];
        }

        $invoices = DB::table('invoice_penagihan')
            ->whereIn('id', $invoiceIds)
            ->get()
            ->keyBy('id');

        $grossTotals = DB::table('pengiriman as p2')
            ->join('pengiriman_details as pd2', 'pd2.pengiriman_id', '=', 'p2.id')
            ->join('order_details as od2', 'od2.id', '=', 'pd2.purchase_order_bahan_baku_id')
            ->whereIn('p2.invoice_penagihan_id', $invoiceIds)
            ->whereNull('p2.deleted_at')
            ->select('p2.invoice_penagihan_id', DB::raw('SUM(pd2.qty_kirim * od2.harga_jual) as gross_total'))
            ->groupBy('p2.invoice_penagihan_id')
            ->get()
            ->pluck('gross_total', 'invoice_penagihan_id');

        return ['invoices' => $invoices, 'grossTotals' => $grossTotals];
    }

    /**
     * Hitung margin dari collection pengiriman.
     *
     * FIX (invoice_penagihan_id belum ter-backfill): sama seperti fix di
     * MarginController::hitungHargaBeliJual() — resolusi invoice sekarang punya fallback ke
     * relasi lama invoicePenagihan (match by pengiriman_id) saat pengiriman.invoice_penagihan_id
     * masih null tapi invoice-nya sendiri ada dan bukan hasil merge.
     *
     * FIX (fallback PO-price cuma pakai 1 detail): fallback PO-price sekarang menjumlahkan
     * qty*harga_jual dari SEMUA pengirimanDetails (bukan cuma detail pertama), konsisten
     * dengan basis qty sisi beli.
     *
     * @param  \Illuminate\Support\Collection  $pengirimanList
     * @param  bool  $withMeta  Sertakan pengiriman_id, status, no_pengiriman, has_refraksi
     * @return array{rows: array, totalMargin: float, totalHargaBeli: float, totalHargaJual: float}
     */
    private function hitungMarginDariPengiriman(Collection $pengirimanList, bool $withMeta = false): array
    {
        $toFloat = fn($val) => floatval(str_replace(',', '.', (string)($val ?? 0)));

        $invoiceData = $this->loadInvoiceDataForPengirimanList($pengirimanList);

        $rows           = [];
        $totalMargin    = 0;
        $totalHargaBeli = 0;
        $totalHargaJual = 0;

        foreach ($pengirimanList as $p) {
            $invoiceRow = ($p->invoice_penagihan_id ?? null)
                ? ($invoiceData['invoices'][$p->invoice_penagihan_id] ?? null)
                : null;

            // FIX: fallback ke relasi lama kalau invoice_penagihan_id belum ter-backfill.
            if (!$invoiceRow && $p->invoicePenagihan && $p->invoicePenagihan->status !== 'digabung') {
                $invoiceRow = $p->invoicePenagihan;
            }

            $hasValidInvoice   = $invoiceRow && $invoiceRow->status !== 'digabung';
            $invoiceIdResolved = $invoiceRow->id ?? null;

            if (!$p->approvalPembayaran && !$hasValidInvoice) {
                continue;
            }

            // Ambil detail pertama untuk info bahan baku/supplier
            $detail = $p->pengirimanDetails->first();
            if (!$detail) continue;

            // Total qty dijumlah dari semua details
            $totalQtyKirim = $p->pengirimanDetails->sum('qty_kirim');

            // ===== HARGA JUAL =====
            $hargaJualPerKg     = 0;
            $totalHargaJualItem = 0;

            if ($hasValidInvoice) {
                $amountJual = $toFloat($invoiceRow->amount_after_refraksi) > 0
                    ? $toFloat($invoiceRow->amount_after_refraksi)
                    : $toFloat($invoiceRow->subtotal);

                $grossInvoiceTotal = $toFloat($invoiceData['grossTotals'][$invoiceIdResolved] ?? 0);
                $grossPengiriman   = $toFloat($p->pengirimanDetails->sum(
                    fn($d) => $toFloat($d->qty_kirim) * $toFloat(optional($d->orderDetail)->harga_jual)
                ));

                if ($grossInvoiceTotal > 0 && $amountJual > 0) {
                    $ratio              = $grossPengiriman / $grossInvoiceTotal;
                    $totalHargaJualItem = $ratio * $amountJual;
                } else {
                    $totalHargaJualItem = $amountJual;
                }

                $qtyJual = $toFloat($p->pengirimanDetails->sum('qty_kirim'));

                if ($qtyJual > 0 && $totalHargaJualItem > 0) {
                    $hargaJualPerKg = $totalHargaJualItem / $qtyJual;
                }

            } else {
                // FIX: sum SEMUA pengirimanDetails, bukan cuma $detail->orderDetail tunggal.
                $totalHargaJualItem = $toFloat($p->pengirimanDetails->sum(
                    fn($d) => $toFloat($d->qty_kirim) * $toFloat(optional($d->orderDetail)->harga_jual)
                ));

                if ($totalQtyKirim > 0 && $totalHargaJualItem > 0) {
                    $hargaJualPerKg = $totalHargaJualItem / $totalQtyKirim;
                }
            }

            // ===== HARGA BELI ===== (tidak berubah)
            $hargaBeliPerKg     = 0;
            $totalHargaBeliItem = 0;

            if ($p->approvalPembayaran) {
                $amountBeli = $toFloat($p->approvalPembayaran->subtotal) > 0
                    ? $toFloat($p->approvalPembayaran->subtotal)
                    : ($toFloat($p->approvalPembayaran->amount_after_refraksi) > 0
                        ? $toFloat($p->approvalPembayaran->amount_after_refraksi)
                        : $toFloat($p->total_harga_kirim));

                $qtyBeli = $toFloat($p->approvalPembayaran->qty_after_refraksi) > 0
                    ? $toFloat($p->approvalPembayaran->qty_after_refraksi)
                    : $toFloat($p->total_qty_kirim);

                if ($qtyBeli > 0 && $amountBeli > 0) {
                    $hargaBeliPerKg = $amountBeli / $qtyBeli;
                }

                $totalHargaBeliItem = $amountBeli;

            } else {
                $hargaBeliPerKg     = $toFloat($detail->harga_satuan);
                $totalHargaBeliItem = $p->pengirimanDetails->sum('total_harga');
            }

            $margin           = $totalHargaJualItem - $totalHargaBeliItem;
            $marginPercentage = $totalHargaJualItem > 0 ? ($margin / $totalHargaJualItem) * 100 : 0;

            $klien            = $p->order->klien ?? null;
            $namaKlien        = $klien ? $klien->nama . ($klien->cabang ? " - {$klien->cabang}" : '') : '-';
            $picMarketingUser = $p->order->winner->user ?? null;
            $namaPicMarketing = $picMarketingUser ? $picMarketingUser->nama : '-';
            $supplier         = $detail->bahanBakuSupplier->supplier ?? null;
            $bahanBaku        = $detail->orderDetail->bahanBakuKlien ?? null;
            $bahanBakuSupplier = $detail->bahanBakuSupplier ?? null;

            $row = [
                'tanggal_kirim'     => $p->tanggal_kirim,
                'pic_purchasing'    => $p->purchasing->nama ?? '-',
                'pic_marketing'     => $namaPicMarketing,
                'klien'             => $namaKlien,
                'supplier'          => $supplier->nama ?? '-',
                'bahan_baku'        => $bahanBaku->nama ?? $bahanBakuSupplier->nama ?? '-',
                'qty_kirim'         => $totalQtyKirim,
                'qty'               => $totalQtyKirim,
                'harga_beli_per_kg' => $hargaBeliPerKg,
                'harga_beli_total'  => $totalHargaBeliItem,
                'harga_jual_per_kg' => $hargaJualPerKg,
                'harga_jual_total'  => $totalHargaJualItem,
                'total_harga_beli'  => $totalHargaBeliItem,
                'total_harga_jual'  => $totalHargaJualItem,
                'margin'            => $margin,
                'margin_percentage' => $marginPercentage,
            ];

            if ($withMeta) {
                $row['pengiriman_id'] = $p->id;
                $row['status']        = $p->status;
                $row['no_pengiriman'] = $p->no_pengiriman ?? '-';
                $row['has_refraksi']  = $p->approvalPembayaran
                    && floatval($p->approvalPembayaran->refraksi_amount ?? 0) > 0;
            }

            $rows[] = $row;

            $totalMargin    += $margin;
            $totalHargaBeli += $totalHargaBeliItem;
            $totalHargaJual += $totalHargaJualItem;
        }

        return compact('rows', 'totalMargin', 'totalHargaBeli', 'totalHargaJual');
    }

    /**
     * Bungkus hitungMarginDariPengiriman() dan tambahkan perhitungan gross margin percentage.
     *
     * Rumus `$totalHargaJual > 0 ? ($totalMargin / $totalHargaJual) * 100 : 0` sebelumnya
     * ditulis ulang identik di 4 lokasi (index() 2×, downloadMarginMingguIniPdf() 2×).
     * Hasil perhitungan TIDAK berubah dari versi sebelumnya.
     *
     * @return array{rows: array, totalMargin: float, totalHargaBeli: float, totalHargaJual: float, grossMarginPercentage: float}
     */
    private function computeGrossMargin(Collection $pengirimanList, bool $withMeta = false): array
    {
        $hasil = $this->hitungMarginDariPengiriman($pengirimanList, $withMeta);

        $grossMarginPercentage = $hasil['totalHargaJual'] > 0
            ? ($hasil['totalMargin'] / $hasil['totalHargaJual']) * 100
            : 0;

        return $hasil + ['grossMarginPercentage' => $grossMarginPercentage];
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request): View
    {
        // ========== PARSE DATE RANGE FILTER (WEEKLY) ==========
        $useCustomRange = false;
        $defaultRange   = $this->getDefaultWeekRange();
        $weekStart      = $defaultRange['start'];
        $weekEnd        = $defaultRange['end'];

        $startDateParam = $request->get('start_date', '');
        $endDateParam   = $request->get('end_date', '');

        if (!empty($startDateParam) && !empty($endDateParam)) {
            try {
                $parsedStart = Carbon::createFromFormat('Y-m-d', $startDateParam)->startOfDay();
                $parsedEnd   = Carbon::createFromFormat('Y-m-d', $endDateParam)->endOfDay();

                if ($parsedStart->lte($parsedEnd)) {
                    $weekStart      = $parsedStart;
                    $weekEnd        = $parsedEnd;
                    $useCustomRange = true;
                }
            } catch (\Exception $e) {
                // Fallback ke default range (behavior tidak berubah), namun kini tercatat di log
                // agar kegagalan parsing filter tanggal dari user dapat ditelusuri.
                Log::warning('Dashboard: gagal parsing filter tanggal, fallback ke range default.', [
                    'start_date' => $startDateParam,
                    'end_date'   => $endDateParam,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        $rangeStartLabel = $weekStart->format('d M Y');
        $rangeEndLabel   = $weekEnd->format('d M Y');

        $metrics = DashboardService::getSummaryMetrics($weekStart, $weekEnd);

        [
            'targetMingguan'         => $targetMingguan,
            'targetBulanan'          => $targetBulanan,
            'targetTahunan'          => $targetTahunan,
            'targetMingguanAdjusted' => $targetMingguanAdjusted,
            'targetBulananAdjusted'  => $targetBulananAdjusted,
            'omsetMingguIni'         => $omsetMingguIni,
            'omsetBulanIni'          => $omsetBulanIni,
            'omsetTahunIni'          => $omsetTahunIni,
            'omsetSistemMingguIni'   => $omsetSistemMingguIni,
            'omsetManualMingguIni'   => $omsetManualMingguIni,
            'omsetSistemBulanIni'    => $omsetSistemBulanIni,
            'omsetManualBulanIni'    => $omsetManualBulanIni,
            'progressMinggu'         => $progressMinggu,
            'progressBulan'          => $progressBulan,
            'progressTahun'          => $progressTahun,
            'totalOutstanding'       => $totalOutstanding,
            'totalQtyOutstanding'    => $totalQtyOutstanding,
            'poBerjalan'             => $poBerjalan,
        ] = $metrics;

        // ========== PENGIRIMAN MINGGU INI ==========
        $deliveryData = DashboardService::getWeeklyDeliveries($weekStart, $weekEnd);

        [
            'pengirimanNormalList'               => $pengirimanNormalList,
            'pengirimanBongkarSebagianList'      => $pengirimanBongkarSebagianList,
            'pengirimanNormalMingguIni'          => $pengirimanNormalMingguIni,
            'pengirimanBongkarSebagianMingguIni' => $pengirimanBongkarSebagianMingguIni,
            'totalQtyPengirimanMingguIni'        => $totalQtyPengirimanMingguIni,
        ] = $deliveryData;

        // ========== PENGIRIMAN GAGAL ==========
        $pengirimanGagalList = Pengiriman::with(['order.klien', 'purchasing'])
            ->where('status', 'gagal')
            ->where(function ($query) use ($weekStart, $weekEnd) {
                $query->where(function ($q) use ($weekStart, $weekEnd) {
                    $q->whereNotNull('tanggal_kirim')
                      ->whereBetween('tanggal_kirim', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()]);
                })->orWhere(function ($q) use ($weekStart, $weekEnd) {
                    $q->whereNull('tanggal_kirim')
                      ->whereBetween('updated_at', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()]);
                });
            })
            ->get()
            ->map(fn($pengiriman) => [
                'id'              => $pengiriman->id,
                'po_number'       => $pengiriman->order->po_number ?? 'N/A',
                'tanggal_kirim'   => $pengiriman->tanggal_kirim,
                'tanggal_gagal'   => $pengiriman->updated_at,
                'klien'           => $pengiriman->order->klien->nama ?? 'N/A',
                'cabang'          => $pengiriman->order->klien->cabang ?? null,
                'total_qty_kirim' => $pengiriman->total_qty_kirim,
                'catatan'         => $pengiriman->catatan ?? '-',
                'status'          => $pengiriman->status,
                'purchasing'      => $pengiriman->purchasing->nama ?? 'N/A',
            ])
            ->toArray();

        // ========== ORDER BULAN INI ==========
        $orderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->count();

        $nilaiOrderBulanIni = DB::table('orders')
            ->leftJoin('order_details', function ($join) {
                $join->on('order_details.order_id', '=', 'orders.id')->whereNull('order_details.deleted_at');
            })
            ->whereNull('orders.deleted_at')
            ->whereYear('orders.tanggal_order', Carbon::now()->year)
            ->whereMonth('orders.tanggal_order', Carbon::now()->month)
            ->sum(DB::raw('COALESCE(order_details.original_qty, order_details.qty) * order_details.harga_jual'));

        // ========== MARGIN MINGGU INI ==========
        $pengirimanMarginMingguIni = $this->getPengirimanForMarginByRange($weekStart, $weekEnd);

        $hasilMingguIni          = $this->computeGrossMargin($pengirimanMarginMingguIni, withMeta: true);
        $topMarginMingguIni      = $hasilMingguIni['rows'];
        $totalMarginMingguIni    = $hasilMingguIni['totalMargin'];
        $totalHargaBeliMingguIni = $hasilMingguIni['totalHargaBeli'];
        $totalHargaJualMingguIni = $hasilMingguIni['totalHargaJual'];
        $grossMarginMingguIni    = $hasilMingguIni['grossMarginPercentage'];

        // ========== GROSS MARGIN BULAN INI ==========
        $pengirimanMarginBulanIni = $this->getPengirimanForMarginByMonth(Carbon::now()->year, Carbon::now()->month);

        $hasilBulanIni          = $this->computeGrossMargin($pengirimanMarginBulanIni);
        $totalMarginBulanIni    = $hasilBulanIni['totalMargin'];
        $totalHargaBeliBulanIni = $hasilBulanIni['totalHargaBeli'];
        $totalHargaJualBulanIni = $hasilBulanIni['totalHargaJual'];
        $grossMarginBulanIni    = $hasilBulanIni['grossMarginPercentage'];

        return view('pages.dashboard', compact(
            'targetMingguan', 'targetBulanan', 'targetTahunan',
            'targetMingguanAdjusted', 'targetBulananAdjusted',
            'omsetMingguIni', 'omsetBulanIni', 'omsetTahunIni',
            'omsetSistemMingguIni', 'omsetManualMingguIni',
            'omsetSistemBulanIni', 'omsetManualBulanIni',
            'progressMinggu', 'progressBulan', 'progressTahun',
            'totalOutstanding', 'totalQtyOutstanding', 'poBerjalan',
            'pengirimanNormalMingguIni', 'pengirimanBongkarSebagianMingguIni',
            'totalQtyPengirimanMingguIni',
            'pengirimanNormalList', 'pengirimanBongkarSebagianList', 'pengirimanGagalList',
            'orderBulanIni', 'nilaiOrderBulanIni',
            'topMarginMingguIni', 'grossMarginMingguIni', 'totalMarginMingguIni',
            'grossMarginBulanIni',
            'rangeStartLabel', 'rangeEndLabel', 'useCustomRange',
            'startDateParam', 'endDateParam'
        ));
    }

    // =========================================================================
    // CHART AJAX ENDPOINTS
    // =========================================================================

    public function getOmsetPerKlien(Request $request)
    {
        $tahun  = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');

        $result = ChartService::getOmsetPerKlienChart($tahun, $search);

        return response()->json($result);
    }

    public function getOmsetPerSupplier(Request $request)
    {
        $tahun  = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');

        $result = ChartService::getOmsetPerSupplierChart($tahun, $search);

        return response()->json($result);
    }

    public function getOmsetPerBahanBaku(Request $request)
    {
        $tahun  = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');

        $result = ChartService::getOmsetPerBahanBakuChart($tahun, $search);

        return response()->json($result);
    }

    // =========================================================================
    // DOWNLOAD MARGIN MINGGU INI — PDF
    // =========================================================================

    public function downloadMarginMingguIniPdf(): Response
    {
        ['week' => $currentWeekOfMonth, 'start' => $startOfWeek, 'end' => $endOfWeek] = $this->resolveCurrentWeek();

        // ---- Margin minggu ini ----
        $pengirimanMargin = $this->getPengirimanForMarginByRange($startOfWeek, $endOfWeek);

        $hasilPdf                = $this->computeGrossMargin($pengirimanMargin, withMeta: true);
        $marginDataMingguIni     = $hasilPdf['rows'];
        $totalMarginMingguIni    = $hasilPdf['totalMargin'];
        $totalHargaBeliMingguIni = $hasilPdf['totalHargaBeli'];
        $totalHargaJualMingguIni = $hasilPdf['totalHargaJual'];
        $grossMarginMingguIni    = $hasilPdf['grossMarginPercentage'];

        // ---- Gross margin bulan ini ----
        $pengirimanMarginBulanIni = $this->getPengirimanForMarginByMonth(Carbon::now()->year, Carbon::now()->month);

        $hasilBulanPdf          = $this->computeGrossMargin($pengirimanMarginBulanIni);
        $totalMarginBulanIni    = $hasilBulanPdf['totalMargin'];
        $totalHargaJualBulanIni = $hasilBulanPdf['totalHargaJual'];
        $grossMarginBulanIni    = $hasilBulanPdf['grossMarginPercentage'];

        $data = [
            'marginData'          => $marginDataMingguIni,
            'totalMargin'         => $totalMarginMingguIni,
            'totalHargaBeli'      => $totalHargaBeliMingguIni,
            'totalHargaJual'      => $totalHargaJualMingguIni,
            'grossMargin'         => $grossMarginMingguIni,
            'grossMarginBulanIni' => $grossMarginBulanIni,
            'totalMarginBulanIni' => $totalMarginBulanIni,
            'currentMonth'        => Carbon::now()->format('F Y'),
            'startDate'           => $startOfWeek->format('d/m/Y'),
            'endDate'             => $endOfWeek->format('d/m/Y'),
            'currentWeek'         => $currentWeekOfMonth,
            'generatedAt'         => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        try {
            $pdf = Pdf::loadView('pages.dashboard.pdf.margin-minggu-ini', $data);
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download('Margin_Minggu_' . $currentWeekOfMonth . '_' . Carbon::now()->format('M_Y') . '.pdf');
        } catch (\Throwable $e) {
            // Response tetap diteruskan ke exception handler Laravel seperti sebelumnya
            // (perilaku tidak berubah); hanya ditambahkan pencatatan konteks bisnis.
            Log::error('Dashboard: gagal membuat PDF margin minggu ini.', [
                'week'  => $currentWeekOfMonth,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    // =========================================================================
    // DOWNLOAD MARGIN MINGGU INI — EXCEL
    // =========================================================================

    public function downloadMarginMingguIniExcel(): Response
    {
        ['week' => $currentWeekOfMonth, 'start' => $startOfWeek, 'end' => $endOfWeek] = $this->resolveCurrentWeek();

        $pengirimanMargin = $this->getPengirimanForMarginByRange($startOfWeek, $endOfWeek);

        $hasilExcel              = $this->computeGrossMargin($pengirimanMargin, withMeta: true);
        $totalMarginMingguIni    = $hasilExcel['totalMargin'];
        $totalHargaBeliMingguIni = $hasilExcel['totalHargaBeli'];
        $totalHargaJualMingguIni = $hasilExcel['totalHargaJual'];
        $grossMarginMingguIni    = $hasilExcel['grossMarginPercentage'];

        // Format tanggal_kirim jadi string untuk Excel
        $marginDataMingguIni = array_map(function ($row) {
            $row['tanggal_kirim'] = ($row['tanggal_kirim'] instanceof Carbon
                ? $row['tanggal_kirim']
                : Carbon::parse($row['tanggal_kirim'])
            )->format('d/m/Y');
            return $row;
        }, $hasilExcel['rows']);

        $profitCount = count(array_filter($marginDataMingguIni, fn($item) => $item['margin'] >= 0));
        $lossCount   = count($marginDataMingguIni) - $profitCount;

        $totals = [
            'totalQty'              => array_sum(array_column($marginDataMingguIni, 'qty')),
            'totalHargaBeli'        => $totalHargaBeliMingguIni,
            'totalHargaJual'        => $totalHargaJualMingguIni,
            'totalMargin'           => $totalMarginMingguIni,
            'grossMarginPercentage' => $grossMarginMingguIni,
            'profitCount'           => $profitCount,
            'lossCount'             => $lossCount,
        ];

        $filters = [
            'start_date' => $startOfWeek->format('Y-m-d'),
            'end_date'   => $endOfWeek->format('Y-m-d'),
        ];

        try {
            return Excel::download(
                new MarginExport($marginDataMingguIni, $totals, $filters),
                'Margin_Minggu_' . $currentWeekOfMonth . '_' . Carbon::now()->format('M_Y') . '.xlsx'
            );
        } catch (\Throwable $e) {
            Log::error('Dashboard: gagal membuat Excel margin minggu ini.', [
                'week'  => $currentWeekOfMonth,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}