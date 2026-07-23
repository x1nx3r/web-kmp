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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MarginExport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class MarginController extends Controller
{
    private const STATUS_VALID = ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'];

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date',
            'pic_purchasing' => 'nullable|integer',
            'pic_marketing'  => 'nullable|integer',
            'klien'          => 'nullable|integer',
            'supplier'       => 'nullable|integer',
            'bahan_baku'     => 'nullable|integer',
        ]);
    }

    /**
     * Cari item invoice (dari kolom JSON `items` di invoice_penagihan) yang cocok
     * dengan pengiriman ini berdasarkan no_pengiriman.
     * Item JSON adalah SUMBER KEBENARAN per-pengiriman: sudah berisi `amount` final
     * (termasuk markup/adjustment manual per shipment) dan `details[].harga_jual`
     * (harga dasar per kg yang disepakati), sehingga TIDAK PERLU direkonstruksi lagi
     * lewat rasio/proporsi dari order_details.
     *
     * @param  \stdClass|null $invoiceRow  row invoice_penagihan (hasil DB::table()->get())
     * @param  string|null    $noPengiriman
     * @return array|null  item yang match, atau null kalau tidak ketemu
     */
    private function findInvoiceItemForPengiriman($invoiceRow, ?string $noPengiriman): ?array
    {
        if (!$invoiceRow || empty($invoiceRow->items) || !$noPengiriman) {
            return null;
        }

        $items = json_decode($invoiceRow->items, true);
        if (!is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            $itemName    = $item['item_name'] ?? '';
            $description = $item['description'] ?? '';

            if (str_contains($itemName, $noPengiriman) || str_contains($description, $noPengiriman)) {
                return $item;
            }
        }

        return null;
    }

    private function hitungHargaBeliJual($p, $detail, array $invoiceData = []): array
    {
        $toFloat = fn($val) => floatval(str_replace(',', '.', (string)($val ?? 0)));

        $hargaJualPerKg     = 0;
        $totalHargaJualItem = 0;
        $sumberHargaJual    = '-';

        $invoiceRow = ($p->invoice_penagihan_id ?? null)
            ? ($invoiceData['invoices'][$p->invoice_penagihan_id] ?? null)
            : null;

        // FIX: invoice_penagihan_id belum ter-backfill utk pengiriman ini, tapi invoice lama
        // yang match langsung lewat pengiriman_id (relasi invoicePenagihan, sudah di-eager-load
        // di buildQuery()) masih ada dan bukan hasil merge -> aman dipakai apa adanya.
        if (!$invoiceRow && $p->invoicePenagihan && $p->invoicePenagihan->status !== 'digabung') {
            $invoiceRow = $p->invoicePenagihan;
        }

        $hasValidInvoice = $invoiceRow && $invoiceRow->status !== 'digabung';

        if ($hasValidInvoice) {
            // FIX UTAMA: ambil angka LANGSUNG dari item JSON invoice yang match dengan
            // no_pengiriman ini, alih-alih merekonstruksi ulang lewat rasio proporsional
            // (qty x harga_jual order_detail) yang mengasumsikan semua pengiriman dalam
            // invoice gabungan punya markup/adjustment seragam -- padahal tidak.
            $invoiceItem = $this->findInvoiceItemForPengiriman($invoiceRow, $p->no_pengiriman ?? null);

            if ($invoiceItem) {
                // 'amount' = nilai final yang ditagih untuk pengiriman ini (sudah termasuk
                // markup/adjustment manual per shipment, mis. +Rp100/kg).
                $totalHargaJualItem = $toFloat($invoiceItem['amount'] ?? 0);

                // 'details[0].harga_jual' = harga dasar per kg yang disepakati untuk bahan
                // baku ini (angka asli, bukan hasil bagi/rekonstruksi).
                $hargaJualPerKg = $toFloat($invoiceItem['details'][0]['harga_jual'] ?? 0);

                // Fallback kalau harga_jual per kg tidak ada di detail tapi qty ada:
                // turunkan dari amount / qty milik item ini sendiri (bukan qty invoice).
                if ($hargaJualPerKg <= 0) {
                    $qtyItem = $toFloat($invoiceItem['details'][0]['qty'] ?? $p->pengirimanDetails->sum('qty_kirim'));
                    if ($qtyItem > 0 && $totalHargaJualItem > 0) {
                        $hargaJualPerKg = $totalHargaJualItem / $qtyItem;
                    }
                }

                $sumberHargaJual = 'Invoice Penagihan';

            } else {
                // Item spesifik tidak ketemu di JSON (mis. invoice lama sebelum format ini
                // dipakai, atau pengiriman tunggal tanpa breakdown items) -> fallback ke
                // amount_after_refraksi / subtotal invoice secara utuh (bukan proporsi).
                $amountAfter = $toFloat($invoiceRow->amount_after_refraksi);
                $totalHargaJualItem = $amountAfter > 0 ? $amountAfter : $toFloat($invoiceRow->subtotal);

                $qtyJual = $toFloat($p->pengirimanDetails->sum('qty_kirim'));
                if ($qtyJual > 0 && $totalHargaJualItem > 0) {
                    $hargaJualPerKg = $totalHargaJualItem / $qtyJual;
                }

                $sumberHargaJual = 'Invoice Penagihan (fallback)';
            }

        } else {
            // Belum ada invoice valid -> pakai harga_jual dari Purchase Order (order_detail).
            $totalHargaJualItem = $toFloat($p->pengirimanDetails->sum(
                fn($d) => $toFloat($d->qty_kirim) * $toFloat(optional($d->orderDetail)->harga_jual)
            ));
            $qtyJualPO = $toFloat($p->pengirimanDetails->sum('qty_kirim'));

            if ($qtyJualPO > 0 && $totalHargaJualItem > 0) {
                $hargaJualPerKg  = $totalHargaJualItem / $qtyJualPO;
                $sumberHargaJual = 'Purchase Order';
            }
        }

        // ===== HARGA BELI ===== (tidak berubah)
        $hargaBeliPerKg     = 0;
        $totalHargaBeliItem = 0;

        if ($p->approvalPembayaran) {
            $subtotal     = $toFloat($p->approvalPembayaran->subtotal);
            $amountAfter  = $toFloat($p->approvalPembayaran->amount_after_refraksi);

            if ($subtotal > 0) {
                $amountBeli = $subtotal;
            } else {
                $amountBeli = $amountAfter > 0 ? $amountAfter : $toFloat($p->total_harga_kirim);
            }

            $qtyAfter = $toFloat($p->approvalPembayaran->qty_after_refraksi);
            $qtyBeli  = $qtyAfter > 0 ? $qtyAfter : $toFloat($p->total_qty_kirim);

            if ($qtyBeli > 0 && $amountBeli > 0) {
                $hargaBeliPerKg = $amountBeli / $qtyBeli;
            }

            $totalHargaBeliItem = $amountBeli;

        } else {
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
        ->whereIn('status', self::STATUS_VALID)
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
     * Ambil data invoice_penagihan (row lengkap, termasuk kolom `items` JSON) untuk
     * seluruh pengiriman yang butuh dihitung, dalam 1 query batch (menghindari N+1).
     *
     * Catatan: sejak fix harga jual, kita TIDAK LAGI butuh query grossTotals (SUM
     * qty_kirim x harga_jual order_detail) karena totalnya sekarang diambil langsung
     * dari item JSON per pengiriman (lihat findInvoiceItemForPengiriman()), bukan
     * direkonstruksi lewat rasio.
     *
     * @return array{invoices: \Illuminate\Support\Collection}
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
            return ['invoices' => collect()];
        }

        $invoices = DB::table('invoice_penagihan')
            ->whereIn('id', $invoiceIds)
            ->get()
            ->keyBy('id');

        return ['invoices' => $invoices];
    }

    private function prosesMarginData($pengirimanList, bool $withMeta = false): array
    {
        $marginData     = [];
        $totalQty       = 0;
        $totalHargaBeli = 0;
        $totalHargaJual = 0;
        $totalMargin    = 0;

        $invoiceData = $this->loadInvoiceDataForPengirimanList($pengirimanList);

        foreach ($pengirimanList as $p) {
            $invoiceRow = ($p->invoice_penagihan_id ?? null)
                ? ($invoiceData['invoices'][$p->invoice_penagihan_id] ?? null)
                : null;

            if (!$invoiceRow && $p->invoicePenagihan && $p->invoicePenagihan->status !== 'digabung') {
                $invoiceRow = $p->invoicePenagihan;
            }

            $hasValidInvoice = $invoiceRow && $invoiceRow->status !== 'digabung';

            if (!$p->approvalPembayaran && !$hasValidInvoice) {
                continue;
            }

            $detail = $p->pengirimanDetails->first();
            if (!$detail) continue;

            $qtyTotal = $p->pengirimanDetails->sum('qty_kirim');
            $harga    = $this->hitungHargaBeliJual($p, $detail, $invoiceData);

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

    public function index(Request $request)
    {
        $this->validateFilters($request);

        $title     = 'Analisis Margin';
        $activeTab = 'margin';

        $startDate     = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate       = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $picPurchasing = $request->get('pic_purchasing');
        $picMarketing  = $request->get('pic_marketing');
        $klienId       = $request->get('klien');
        $supplierId    = $request->get('supplier');
        $bahanBakuId   = $request->get('bahan_baku');

        // ---- Menggunakan JOIN yang efisien, bukan Nested Subqueries ----
        $picPurchasingList = User::select('users.id', 'users.nama')
            ->join('pengiriman', 'users.id', '=', 'pengiriman.purchasing_id')
            ->whereIn('pengiriman.status', self::STATUS_VALID)
            ->whereNull('pengiriman.deleted_at')
            ->distinct()
            ->orderBy('users.nama')
            ->get();

        $picMarketingList = User::select('users.id', 'users.nama')
            ->join('order_winners', 'users.id', '=', 'order_winners.user_id')
            ->join('pengiriman', 'order_winners.order_id', '=', 'pengiriman.purchase_order_id')
            ->whereIn('pengiriman.status', self::STATUS_VALID)
            ->whereNull('pengiriman.deleted_at')
            ->distinct()
            ->orderBy('users.nama')
            ->get();

        $klienList = Klien::select('kliens.id', 'kliens.nama', 'kliens.cabang')
            ->join('orders', 'kliens.id', '=', 'orders.klien_id')
            ->join('pengiriman', 'orders.id', '=', 'pengiriman.purchase_order_id')
            ->whereIn('pengiriman.status', self::STATUS_VALID)
            ->whereNull('pengiriman.deleted_at')
            ->distinct()
            ->orderBy('kliens.nama')
            ->get();

        $supplierList = Supplier::select('suppliers.id', 'suppliers.nama')
            ->join('bahan_baku_supplier', 'suppliers.id', '=', 'bahan_baku_supplier.supplier_id')
            ->join('pengiriman_details', 'bahan_baku_supplier.id', '=', 'pengiriman_details.bahan_baku_supplier_id')
            ->join('pengiriman', 'pengiriman_details.pengiriman_id', '=', 'pengiriman.id')
            ->whereIn('pengiriman.status', self::STATUS_VALID)
            ->whereNull('pengiriman.deleted_at')
            ->distinct()
            ->orderBy('suppliers.nama')
            ->get();

        $bahanBakuList = BahanBakuKlien::select('bahan_baku_klien.id', 'bahan_baku_klien.nama')
            ->join('order_details', 'bahan_baku_klien.id', '=', 'order_details.bahan_baku_klien_id')
            ->join('pengiriman_details', 'order_details.id', '=', 'pengiriman_details.purchase_order_bahan_baku_id')
            ->join('pengiriman', 'pengiriman_details.pengiriman_id', '=', 'pengiriman.id')
            ->whereIn('pengiriman.status', self::STATUS_VALID)
            ->whereNull('pengiriman.deleted_at')
            ->distinct()
            ->orderBy('bahan_baku_klien.nama')
            ->get()
            ->unique('nama')
            ->values();

        // ---- Data ----
        $pengirimanList = $this->buildQuery($request)->get();
        $hasil = $this->prosesMarginData($pengirimanList, true);
        
        $marginData     = $hasil['marginData'];
        $totalQty       = $hasil['totalQty'];
        $totalHargaBeli = $hasil['totalHargaBeli'];
        $totalHargaJual = $hasil['totalHargaJual'];
        $totalMargin    = $hasil['totalMargin'];

        $summary = $this->hitungSummary($marginData, $totalHargaJual, $totalMargin);
        
        $grossMarginPercentage = $summary['grossMarginPercentage'];
        $profitCount           = $summary['profitCount'];
        $lossCount             = $summary['lossCount'];
        $avgMarginPercentage   = $summary['avgMarginPercentage'];

        return view('pages.laporan.margin', compact(
            'title', 'activeTab',
            'marginData', 'totalQty', 'totalHargaBeli', 'totalHargaJual', 'totalMargin',
            'grossMarginPercentage', 'profitCount', 'lossCount', 'avgMarginPercentage',
            'startDate', 'endDate',
            'picPurchasing', 'picMarketing', 'klienId', 'supplierId', 'bahanBakuId',
            'picPurchasingList', 'picMarketingList', 'klienList', 'supplierList', 'bahanBakuList'
        ));
    }

    public function export(Request $request)
    {
        try {
            $this->validateFilters($request);

            $startDate     = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate       = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $picPurchasing = $request->get('pic_purchasing');
            $picMarketing  = $request->get('pic_marketing');
            $klienId       = $request->get('klien');
            $supplierId    = $request->get('supplier');
            $bahanBakuId   = $request->get('bahan_baku');

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

            $pengirimanList = $this->buildQuery($request)->get();
            $hasil = $this->prosesMarginData($pengirimanList, false);

            $marginData     = $hasil['marginData'];
            $totalQty       = $hasil['totalQty'];
            $totalHargaBeli = $hasil['totalHargaBeli'];
            $totalHargaJual = $hasil['totalHargaJual'];
            $totalMargin    = $hasil['totalMargin'];

            usort($marginData, fn($a, $b) => $b['margin_percentage'] <=> $a['margin_percentage']);

            $summary = $this->hitungSummary($marginData, $totalHargaJual, $totalMargin);
            
            $grossMarginPercentage = $summary['grossMarginPercentage'];
            $profitCount           = $summary['profitCount'];
            $lossCount             = $summary['lossCount'];

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

            $filename = 'Laporan_Margin_' . Carbon::parse($startDate)->format('d-m-Y') . '_sd_' . Carbon::parse($endDate)->format('d-m-Y') . '.pdf';

            return $pdf->download($filename);
            
        } catch (Exception $e) {
            Log::error('Gagal memproses Export PDF Margin: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses laporan PDF.');
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $this->validateFilters($request);

            $startDate     = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate       = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $picPurchasing = $request->get('pic_purchasing');
            $picMarketing  = $request->get('pic_marketing');
            $klienId       = $request->get('klien');
            $supplierId    = $request->get('supplier');
            $bahanBakuId   = $request->get('bahan_baku');

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
                $filters['klien_name'] = $klienObj ? $klienObj->nama . ($klienObj->cabang ? " ({$klienObj->cabang})" : '') : '';
            }
            if ($supplierId) {
                $filters['supplier_name'] = Supplier::find($supplierId)->nama ?? '';
            }
            if ($bahanBakuId) {
                $filters['bahan_baku_name'] = BahanBakuKlien::find($bahanBakuId)->nama ?? '';
            }

            $pengirimanList = $this->buildQuery($request)->get();
            $hasil = $this->prosesMarginData($pengirimanList, true);

            $marginData     = $hasil['marginData'];
            $totalQty       = $hasil['totalQty'];
            $totalHargaBeli = $hasil['totalHargaBeli'];
            $totalHargaJual = $hasil['totalHargaJual'];
            $totalMargin    = $hasil['totalMargin'];

            $summary = $this->hitungSummary($marginData, $totalHargaJual, $totalMargin);
            
            $grossMarginPercentage = $summary['grossMarginPercentage'];
            $profitCount           = $summary['profitCount'];
            $lossCount             = $summary['lossCount'];

            $totals = [
                'totalQty'              => $totalQty,
                'totalHargaBeli'        => $totalHargaBeli,
                'totalHargaJual'        => $totalHargaJual,
                'totalMargin'           => $totalMargin,
                'grossMarginPercentage' => $grossMarginPercentage,
                'profitCount'           => $profitCount,
                'lossCount'             => $lossCount,
            ];

            $filename = 'Laporan_Margin_' . Carbon::parse($startDate)->format('d-m-Y') . '_sd_' . Carbon::parse($endDate)->format('d-m-Y') . '.xlsx';

            return Excel::download(new MarginExport($marginData, $totals, $filters), $filename);

        } catch (Exception $e) {
            Log::error('Gagal memproses Export Excel Margin: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengekspor ke Excel.');
        }
    }
}