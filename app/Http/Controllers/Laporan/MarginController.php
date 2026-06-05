<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\User;
use App\Models\Supplier;
use App\Models\BahanBakuKlien;
use App\Models\Klien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MarginExport;
use Maatwebsite\Excel\Facades\Excel;

class MarginController extends Controller
{
    /**
     * Hitung harga jual & beli per kg secara konsisten.
     *
     * Jual : invoice->subtotal (jika > 0) → invoice->amount_after_refraksi → fallback orderDetail->harga_jual
     * Beli : approvalPembayaran->subtotal (jika > 0) → amount_after_refraksi (jika > 0) → total_harga_kirim → fallback detail->harga_satuan
     * Qty  : qty_after_refraksi (jika > 0) → total_qty_kirim
     *
     * PENTING: totalHargaJualItem & totalHargaBeliItem diambil LANGSUNG dari amount invoice/approval
     * (bukan harga_per_kg × detail->qty_kirim) agar efek refraksi tidak dibatalkan.
     */
    private function hitungHargaBeliJual($p, $detail): array
    {
        $toFloat = fn($val) => floatval(str_replace(',', '.', (string)($val ?? 0)));

        // ===== HARGA JUAL =====
        $hargaJualPerKg     = 0;
        $totalHargaJualItem = 0;
        $sumberHargaJual    = '-';

        if ($p->invoicePenagihan) {
            // Prioritas amount: subtotal → amount_after_refraksi
            $amountJual = $toFloat($p->invoicePenagihan->subtotal) > 0
                ? $toFloat($p->invoicePenagihan->subtotal)
                : $toFloat($p->invoicePenagihan->amount_after_refraksi);

            // Prioritas qty: qty_after_refraksi → qty_before_refraksi → total_qty_kirim
            $qtyJual = $toFloat($p->invoicePenagihan->qty_after_refraksi) > 0
                ? $toFloat($p->invoicePenagihan->qty_after_refraksi)
                : $toFloat($p->invoicePenagihan->qty_before_refraksi ?? $p->total_qty_kirim);

            if ($qtyJual > 0 && $amountJual > 0) {
                $hargaJualPerKg = $amountJual / $qtyJual;
            }

            // Total diambil langsung dari invoice agar refraksi tidak dibatalkan
            $totalHargaJualItem = $amountJual;
            $sumberHargaJual    = 'Invoice Penagihan';

        } elseif ($detail->orderDetail && $toFloat($detail->orderDetail->harga_jual) > 0) {
            $hargaJualPerKg     = $toFloat($detail->orderDetail->harga_jual);
            $totalHargaJualItem = $toFloat($detail->qty_kirim) * $hargaJualPerKg;
            $sumberHargaJual    = 'Purchase Order';
        }

        // ===== HARGA BELI =====
        $hargaBeliPerKg     = 0;
        $totalHargaBeliItem = 0;

        if ($p->approvalPembayaran) {
            // Prioritas amount: subtotal → amount_after_refraksi → total_harga_kirim
            $amountBeli = $toFloat($p->approvalPembayaran->subtotal) > 0
                ? $toFloat($p->approvalPembayaran->subtotal)
                : ($toFloat($p->approvalPembayaran->amount_after_refraksi) > 0
                    ? $toFloat($p->approvalPembayaran->amount_after_refraksi)
                    : $toFloat($p->total_harga_kirim));

            // Prioritas qty: qty_after_refraksi → total_qty_kirim
            $qtyBeli = $toFloat($p->approvalPembayaran->qty_after_refraksi) > 0
                ? $toFloat($p->approvalPembayaran->qty_after_refraksi)
                : $toFloat($p->total_qty_kirim);

            if ($qtyBeli > 0 && $amountBeli > 0) {
                $hargaBeliPerKg = $amountBeli / $qtyBeli;
            }

            // Total diambil langsung dari approval agar refraksi tidak dibatalkan
            $totalHargaBeliItem = $amountBeli;

        } else {
            // Fallback: tidak ada approval, pakai data mentah dari detail pengiriman
            $hargaBeliPerKg     = $toFloat($detail->harga_satuan);
            $totalHargaBeliItem = $toFloat($detail->total_harga);
        }

        return [
            'harga_jual_per_kg' => $hargaJualPerKg,
            'harga_jual_total'  => $totalHargaJualItem,
            'harga_beli_per_kg' => $hargaBeliPerKg,
            'harga_beli_total'  => $totalHargaBeliItem,
            'sumber_harga_jual' => $sumberHargaJual,
        ];
    }

    /**
     * Bangun query pengiriman dengan eager load & filter yang seragam.
     */
    private function buildQuery(Request $request)
    {
        $startDate     = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate       = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $picPurchasing = $request->get('pic_purchasing');
        $picMarketing  = $request->get('pic_marketing');
        $klienId       = $request->get('klien');
        $supplierId    = $request->get('supplier');
        $bahanBakuId   = $request->get('bahan_baku');

        $query = Pengiriman::with([
            'purchasing:id,nama',
            'order.klien:id,nama,cabang',
            'order.winner.user:id,nama',
            'pengirimanDetails.bahanBakuSupplier.supplier:id,nama',
            'pengirimanDetails.bahanBakuSupplier:id,nama,supplier_id',
            'pengirimanDetails.orderDetail.bahanBakuKlien:id,nama',
            'approvalPembayaran',
            'invoicePenagihan',
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereBetween('tanggal_kirim', [$startDate, $endDate]);

        if ($picPurchasing) {
            $query->where('purchasing_id', $picPurchasing);
        }
        if ($picMarketing) {
            $query->whereHas('order.winner', fn($q) => $q->where('user_id', $picMarketing));
        }
        if ($klienId) {
            $query->whereHas('order', fn($q) => $q->where('klien_id', $klienId));
        }
        if ($supplierId) {
            $query->whereHas('pengirimanDetails.bahanBakuSupplier', fn($q) => $q->where('supplier_id', $supplierId));
        }
        if ($bahanBakuId) {
            $bahanBakuNama = BahanBakuKlien::find($bahanBakuId)->nama ?? null;
            if ($bahanBakuNama) {
                $bahanBakuIds = BahanBakuKlien::where('nama', $bahanBakuNama)->pluck('id')->toArray();
                $query->whereHas('pengirimanDetails.orderDetail', fn($q) => $q->whereIn('bahan_baku_klien_id', $bahanBakuIds));
            }
        }

        return $query->orderBy('tanggal_kirim', 'asc');
    }

    /**
     * Proses collection pengiriman menjadi array marginData + totals.
     * Dipakai di semua method agar konsisten.
     *
     * @param  \Illuminate\Support\Collection  $pengirimanList
     * @param  bool  $withMeta  Sertakan field tambahan (pengiriman_id, status, has_refraksi, sumber_harga_jual)
     * @return array{marginData: array, totalQty: float, totalHargaBeli: float, totalHargaJual: float, totalMargin: float}
     */
    private function prosesMarginData($pengirimanList, bool $withMeta = false): array
    {
        $marginData     = [];
        $totalQty       = 0;
        $totalHargaBeli = 0;
        $totalHargaJual = 0;
        $totalMargin    = 0;

        foreach ($pengirimanList as $p) {
            if (!$p->approvalPembayaran && !$p->invoicePenagihan) {
                continue;
            }

            // Ambil detail pertama untuk info bahan baku/supplier
            $detail = $p->pengirimanDetails->first();
            if (!$detail) continue;

            // Total qty dijumlah dari semua details
            $qtyTotal = $p->pengirimanDetails->sum('qty_kirim');

            $harga = $this->hitungHargaBeliJual($p, $detail);

            // Fallback harga beli total: jika tidak ada approval, sum dari semua details
            if (!$p->approvalPembayaran) {
                $harga['harga_beli_total'] = $p->pengirimanDetails->sum('total_harga');
                $harga['harga_beli_per_kg'] = $qtyTotal > 0
                    ? $harga['harga_beli_total'] / $qtyTotal
                    : 0;
            }

            $margin           = $harga['harga_jual_total'] - $harga['harga_beli_total'];
            $marginPercentage = $harga['harga_jual_total'] > 0
                ? ($margin / $harga['harga_jual_total']) * 100
                : 0;

            $klien            = $p->order->klien ?? null;
            $namaKlien        = $klien ? $klien->nama . ($klien->cabang ? " ({$klien->cabang})" : '') : '-';
            $namaPicMarketing = $p->order->winner->user->nama ?? '-';
            $supplier         = $detail->bahanBakuSupplier->supplier ?? null;
            $bahanBaku        = $detail->orderDetail->bahanBakuKlien ?? null;
            $bahanBakuSupplier = $detail->bahanBakuSupplier ?? null;

            $row = [
                'tanggal_kirim'     => Carbon::parse($p->tanggal_kirim)->format('d/m/Y'),
                'no_pengiriman'     => $p->no_pengiriman ?? '-',
                'no_po'             => $p->order->po_number ?? '-',
                'pic_purchasing'    => $p->purchasing->nama ?? '-',
                'pic_marketing'     => $namaPicMarketing,
                'klien'             => $namaKlien,
                'supplier'          => $supplier->nama ?? '-',
                'bahan_baku'        => $bahanBaku->nama ?? $bahanBakuSupplier->nama ?? '-',
                'qty'               => $qtyTotal,
                'harga_beli_per_kg' => $harga['harga_beli_per_kg'],
                'harga_beli_total'  => $harga['harga_beli_total'],
                'harga_jual_per_kg' => $harga['harga_jual_per_kg'],
                'harga_jual_total'  => $harga['harga_jual_total'],
                'margin'            => $margin,
                'margin_percentage' => $marginPercentage,
            ];

            if ($withMeta) {
                $row['pengiriman_id']     = $p->id;
                $row['status']            = $p->status;
                $row['sumber_harga_jual'] = $harga['sumber_harga_jual'];
                $row['has_refraksi']      = $p->approvalPembayaran
                    && floatval($p->approvalPembayaran->refraksi_amount ?? 0) > 0;
            }

            $marginData[] = $row;

            $totalQty       += $qtyTotal;
            $totalHargaBeli += $harga['harga_beli_total'];
            $totalHargaJual += $harga['harga_jual_total'];
            $totalMargin    += $margin;
        }

        return compact('marginData', 'totalQty', 'totalHargaBeli', 'totalHargaJual', 'totalMargin');
    }

    /**
     * Hitung ringkasan (summary stats) dari hasil prosesMarginData.
     */
    private function hitungSummary(array $marginData, float $totalHargaJual, float $totalMargin): array
    {
        $grossMarginPercentage = $totalHargaJual > 0 ? ($totalMargin / $totalHargaJual) * 100 : 0;
        $profitCount           = count(array_filter($marginData, fn($item) => $item['margin'] >= 0));
        $lossCount             = count($marginData) - $profitCount;
        $avgMarginPercentage   = count($marginData) > 0
            ? array_sum(array_column($marginData, 'margin_percentage')) / count($marginData)
            : 0;

        return compact('grossMarginPercentage', 'profitCount', 'lossCount', 'avgMarginPercentage');
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        $title     = 'Analisis Margin';
        $activeTab = 'margin';

        $startDate     = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate       = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $picPurchasing = $request->get('pic_purchasing');
        $picMarketing  = $request->get('pic_marketing');
        $klienId       = $request->get('klien');
        $supplierId    = $request->get('supplier');
        $bahanBakuId   = $request->get('bahan_baku');

        // ---- Dropdown filter options ----
        $picPurchasingList = User::whereIn('id', function ($query) {
            $query->select('purchasing_id')
                ->from('pengiriman')
                ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereNull('deleted_at')
                ->distinct();
        })->select('id', 'nama')->orderBy('nama')->get();

        $picMarketingList = User::whereIn('id', function ($query) {
            $query->select('user_id')
                ->from('order_winners')
                ->whereIn('order_id', function ($subQuery) {
                    $subQuery->select('purchase_order_id')
                        ->from('pengiriman')
                        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                        ->whereNull('deleted_at')
                        ->distinct();
                });
        })->select('id', 'nama')->orderBy('nama')->get();

        $klienList = Klien::whereIn('id', function ($query) {
            $query->select('klien_id')
                ->from('orders')
                ->whereIn('id', function ($subQuery) {
                    $subQuery->select('purchase_order_id')
                        ->from('pengiriman')
                        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                        ->whereNull('deleted_at');
                });
        })->select('id', 'nama', 'cabang')->orderBy('nama')->get();

        $supplierList = Supplier::whereIn('id', function ($query) {
            $query->select('supplier_id')
                ->from('bahan_baku_supplier')
                ->whereIn('id', function ($subQuery) {
                    $subQuery->select('bahan_baku_supplier_id')
                        ->from('pengiriman_details')
                        ->whereIn('pengiriman_id', function ($innerQuery) {
                            $innerQuery->select('id')
                                ->from('pengiriman')
                                ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                                ->whereNull('deleted_at');
                        });
                })->distinct();
        })->select('id', 'nama')->orderBy('nama')->get();

        $bahanBakuList = BahanBakuKlien::whereIn('id', function ($query) {
            $query->select('bahan_baku_klien_id')
                ->from('order_details')
                ->whereIn('id', function ($subQuery) {
                    $subQuery->select('purchase_order_bahan_baku_id')
                        ->from('pengiriman_details')
                        ->whereIn('pengiriman_id', function ($innerQuery) {
                            $innerQuery->select('id')
                                ->from('pengiriman')
                                ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                                ->whereNull('deleted_at');
                        });
                })->distinct();
        })->select('id', 'nama')->distinct()->orderBy('nama')->get()->unique('nama')->values();

        // ---- Data ----
        $pengirimanList = $this->buildQuery($request)->get();

        $hasil = $this->prosesMarginData($pengirimanList, withMeta: true);

        extract($hasil); // $marginData, $totalQty, $totalHargaBeli, $totalHargaJual, $totalMargin

        $summary = $this->hitungSummary($marginData, $totalHargaJual, $totalMargin);

        extract($summary); // $grossMarginPercentage, $profitCount, $lossCount, $avgMarginPercentage

        return view('pages.laporan.margin', compact(
            'title', 'activeTab',
            'marginData', 'totalQty', 'totalHargaBeli', 'totalHargaJual', 'totalMargin',
            'grossMarginPercentage', 'profitCount', 'lossCount', 'avgMarginPercentage',
            'startDate', 'endDate',
            'picPurchasing', 'picMarketing', 'klienId', 'supplierId', 'bahanBakuId',
            'picPurchasingList', 'picMarketingList', 'klienList', 'supplierList', 'bahanBakuList'
        ));
    }

    // =========================================================================
    // EXPORT PDF
    // =========================================================================

    public function export(Request $request)
    {
        $startDate     = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate       = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $picPurchasing = $request->get('pic_purchasing');
        $picMarketing  = $request->get('pic_marketing');
        $klienId       = $request->get('klien');
        $supplierId    = $request->get('supplier');
        $bahanBakuId   = $request->get('bahan_baku');

        // ---- Label filter untuk header PDF ----
        $picName          = $picPurchasing ? (User::find($picPurchasing)->nama ?? '')     : '';
        $picMarketingName = $picMarketing  ? (User::find($picMarketing)->nama ?? '')      : '';
        $supplierName     = $supplierId    ? (Supplier::find($supplierId)->nama ?? '')    : '';

        $klienName = '';
        if ($klienId) {
            $klienObj  = Klien::find($klienId);
            $klienName = $klienObj ? $klienObj->nama . ($klienObj->cabang ? " ({$klienObj->cabang})" : '') : '';
        }

        $bahanBakuName = '';
        if ($bahanBakuId) {
            $bahanBakuName = BahanBakuKlien::find($bahanBakuId)->nama ?? '';
        }

        // ---- Data ----
        $pengirimanList = $this->buildQuery($request)->get();

        $hasil = $this->prosesMarginData($pengirimanList, withMeta: false);

        extract($hasil);

        // PDF diurutkan margin % descending
        usort($marginData, fn($a, $b) => $b['margin_percentage'] <=> $a['margin_percentage']);

        $summary = $this->hitungSummary($marginData, $totalHargaJual, $totalMargin);

        extract($summary);

        $filterDesc = array_filter([
            $picPurchasing ? 'PIC Procurement: ' . $picName          : null,
            $picMarketing  ? 'PIC Marketing: '   . $picMarketingName : null,
            $klienId       ? 'Klien: '            . $klienName        : null,
            $supplierId    ? 'Supplier: '         . $supplierName     : null,
            $bahanBakuId   ? 'Bahan Baku: '       . $bahanBakuName    : null,
        ]);

        $data = [
            'marginData'            => $marginData,
            'totalQty'              => $totalQty,
            'totalHargaBeli'        => $totalHargaBeli,
            'totalHargaJual'        => $totalHargaJual,
            'totalMargin'           => $totalMargin,
            'grossMarginPercentage' => $grossMarginPercentage,
            'profitCount'           => $profitCount,
            'lossCount'             => $lossCount,
            'startDate'             => Carbon::parse($startDate)->format('d/m/Y'),
            'endDate'               => Carbon::parse($endDate)->format('d/m/Y'),
            'filterDesc'            => implode(' • ', $filterDesc),
            'generatedAt'           => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pages.laporan.pdf.margin', $data);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Laporan_Margin_'
            . Carbon::parse($startDate)->format('d-m-Y')
            . '_sd_'
            . Carbon::parse($endDate)->format('d-m-Y')
            . '.pdf';

        return $pdf->download($filename);
    }

    // =========================================================================
    // EXPORT EXCEL
    // =========================================================================

    public function exportExcel(Request $request)
    {
        $startDate     = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate       = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $picPurchasing = $request->get('pic_purchasing');
        $picMarketing  = $request->get('pic_marketing');
        $klienId       = $request->get('klien');
        $supplierId    = $request->get('supplier');
        $bahanBakuId   = $request->get('bahan_baku');

        // ---- Filters metadata untuk header sheet Excel ----
        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ];

        if ($picPurchasing) {
            $filters['pic_purchasing_name'] = User::find($picPurchasing)->nama ?? '';
        }
        if ($picMarketing) {
            $filters['pic_marketing_name'] = User::find($picMarketing)->nama ?? '';
        }
        if ($klienId) {
            $klienObj = Klien::find($klienId);
            $filters['klien_name'] = $klienObj
                ? $klienObj->nama . ($klienObj->cabang ? " ({$klienObj->cabang})" : '')
                : '';
        }
        if ($supplierId) {
            $filters['supplier_name'] = Supplier::find($supplierId)->nama ?? '';
        }
        if ($bahanBakuId) {
            $filters['bahan_baku_name'] = BahanBakuKlien::find($bahanBakuId)->nama ?? '';
        }

        // ---- Data ----
        $pengirimanList = $this->buildQuery($request)->get();

        // withMeta: true supaya has_refraksi tersedia di Excel (kolom penanda)
        $hasil = $this->prosesMarginData($pengirimanList, withMeta: true);

        extract($hasil);

        $summary = $this->hitungSummary($marginData, $totalHargaJual, $totalMargin);

        extract($summary);

        $totals = [
            'totalQty'              => $totalQty,
            'totalHargaBeli'        => $totalHargaBeli,
            'totalHargaJual'        => $totalHargaJual,
            'totalMargin'           => $totalMargin,
            'grossMarginPercentage' => $grossMarginPercentage,
            'profitCount'           => $profitCount,
            'lossCount'             => $lossCount,
        ];

        $filename = 'Laporan_Margin_'
            . Carbon::parse($startDate)->format('d-m-Y')
            . '_sd_'
            . Carbon::parse($endDate)->format('d-m-Y')
            . '.xlsx';

        return Excel::download(new MarginExport($marginData, $totals, $filters), $filename);
    }
}