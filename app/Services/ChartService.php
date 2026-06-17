<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ChartService
{
    public static function getOmsetPerKlienChart(int $tahun, string $search)
    {
        $cacheKey = 'chart:omset_klien:' . $tahun . ':' . md5($search);

        return Cache::tags(['charts'])->remember($cacheKey, 600, function () use ($tahun, $search) {
            $topKlienQuery = DB::table('pengiriman')
                ->leftJoin(DB::raw('(
                    SELECT pengiriman_id, MAX(subtotal) as subtotal
                    FROM invoice_penagihan
                    WHERE status != "digabung"
                    GROUP BY pengiriman_id
                ) as invoice_penagihan'), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
                ->select('kliens.id as klien_id', 'kliens.nama', 'kliens.cabang', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $tahun)
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('kliens.deleted_at')
                ->groupBy('pengiriman.id', 'kliens.id', 'kliens.nama', 'kliens.cabang');

            self::applyValidInvoiceFilter($topKlienQuery);

            if (!empty($search)) {
                $topKlienQuery->where(fn($q) => $q->where('kliens.nama', 'like', "%{$search}%")->orWhere('kliens.cabang', 'like', "%{$search}%"));
            }

            $topKlien = $topKlienQuery->get()
                ->groupBy('klien_id')
                ->map(function ($items) {
                    $first = $items->first();
                    return (object) ['klien_id' => $first->klien_id, 'nama' => $first->nama, 'cabang' => $first->cabang, 'total' => $items->sum('omset_pengiriman')];
                })
                ->sortByDesc('total')
                ->values();

            $monthColors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#F97316','#14B8A6','#F43F5E','#8B5CF6','#6366F1'];
            $monthNames  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

            $klienNames = [];
            $datasets   = [];

            foreach ($topKlien as $klien) {
                $cabang      = trim((string)($klien->cabang ?? ''));
                $klienNames[] = (string)$klien->nama . ($cabang !== '' ? ' - ' . $cabang : '');
            }

            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $monthData = [];
                foreach ($topKlien as $klien) {
                    $omsetBulanQuery = DB::table('pengiriman')
                        ->leftJoin(DB::raw('(
                            SELECT pengiriman_id, MAX(subtotal) as subtotal
                            FROM invoice_penagihan
                            WHERE status != "digabung"
                            GROUP BY pengiriman_id
                        ) as invoice_penagihan'), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                        ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                        ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                        ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                        ->where('orders.klien_id', $klien->klien_id)
                        ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                        ->whereYear('pengiriman.tanggal_kirim', $tahun)
                        ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                        ->whereNull('pengiriman.deleted_at');

                    self::applyValidInvoiceFilter($omsetBulanQuery);

                    $omsetBulan = $omsetBulanQuery
                        ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                        ->groupBy('pengiriman.id')
                        ->get()->sum('omset_pengiriman');

                    $monthData[] = floatval($omsetBulan);
                }

                $datasets[] = [
                    'label' => $monthNames[$bulan - 1],
                    'data' => $monthData,
                    'backgroundColor' => $monthColors[$bulan - 1],
                    'borderColor' => $monthColors[$bulan - 1],
                    'borderWidth' => 1
                ];
            }

            return ['klien_names' => $klienNames, 'datasets' => $datasets];
        });
    }

    public static function getOmsetPerSupplierChart(int $tahun, string $search)
    {
        $cacheKey = 'chart:omset_supplier:' . $tahun . ':' . md5($search);

        return Cache::tags(['charts'])->remember($cacheKey, 600, function () use ($tahun, $search) {
            $topSupplierQuery = DB::table('pengiriman')
                ->leftJoin('approval_pembayaran', 'pengiriman.id', '=', 'approval_pembayaran.pengiriman_id')
                ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->select('suppliers.id as supplier_id', 'suppliers.nama', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(NULLIF(MAX(approval_pembayaran.subtotal),0), NULLIF(MAX(approval_pembayaran.amount_after_refraksi),0), SUM(pengiriman_details.total_harga)) as omset_pengiriman'))
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $tahun)
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('suppliers.deleted_at')
                ->groupBy('pengiriman.id', 'suppliers.id', 'suppliers.nama');

            if (!empty($search)) {
                $topSupplierQuery->where(fn($q) => $q->where('suppliers.nama', 'like', "%{$search}%")->orWhere('suppliers.alamat', 'like', "%{$search}%"));
            }

            $topSupplier = $topSupplierQuery->get()
                ->groupBy('supplier_id')
                ->map(function ($items) {
                    $first = $items->first();
                    return (object) ['supplier_id' => $first->supplier_id, 'nama' => $first->nama, 'total' => $items->sum('omset_pengiriman')];
                })
                ->sortByDesc('total')
                ->values();

            $monthColors   = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#F97316','#14B8A6','#F43F5E','#8B5CF6','#6366F1'];
            $monthNames    = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            $supplierNames = $topSupplier->pluck('nama')->toArray();
            $datasets      = [];

            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $monthData = [];
                foreach ($topSupplier as $supplier) {
                    $omsetBulan = DB::table('pengiriman')
                        ->leftJoin('approval_pembayaran', 'pengiriman.id', '=', 'approval_pembayaran.pengiriman_id')
                        ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                        ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                        ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                        ->where('bahan_baku_supplier.supplier_id', $supplier->supplier_id)
                        ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                        ->whereYear('pengiriman.tanggal_kirim', $tahun)
                        ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                        ->whereNull('pengiriman.deleted_at')
                        ->select('pengiriman.id', DB::raw('COALESCE(NULLIF(MAX(approval_pembayaran.subtotal),0), NULLIF(MAX(approval_pembayaran.amount_after_refraksi),0), SUM(pengiriman_details.total_harga)) as omset_pengiriman'))
                        ->groupBy('pengiriman.id')
                        ->get()->sum('omset_pengiriman');

                    $monthData[] = floatval($omsetBulan);
                }

                $datasets[] = [
                    'label' => $monthNames[$bulan - 1],
                    'data' => $monthData,
                    'backgroundColor' => $monthColors[$bulan - 1],
                    'borderColor' => $monthColors[$bulan - 1],
                    'borderWidth' => 1
                ];
            }

            return ['supplier_names' => $supplierNames, 'datasets' => $datasets];
        });
    }

    public static function getOmsetPerBahanBakuChart(int $tahun, string $search)
    {
        $cacheKey = 'chart:omset_bahan_baku:' . $tahun . ':' . md5($search);

        return Cache::tags(['charts'])->remember($cacheKey, 600, function () use ($tahun, $search) {
            $topBahanBakuRaw = DB::table('pengiriman')
                ->leftJoin(DB::raw('(
                    SELECT pengiriman_id, MAX(subtotal) as subtotal
                    FROM invoice_penagihan
                    WHERE status != "digabung"
                    GROUP BY pengiriman_id
                ) as invoice_penagihan'), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->join('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->join('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
                ->select('bahan_baku_klien.id as bahan_baku_id', 'bahan_baku_klien.nama', 'pengiriman.id as pengiriman_id',
                    DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $tahun)
                ->whereNull('pengiriman.deleted_at')
                ->whereNull('bahan_baku_klien.deleted_at')
                ->groupBy('pengiriman.id', 'bahan_baku_klien.id', 'bahan_baku_klien.nama');

            self::applyValidInvoiceFilter($topBahanBakuRaw);

            if (!empty($search)) {
                $topBahanBakuRaw->where(fn($q) => $q->where('bahan_baku_klien.nama', 'like', "%{$search}%")->orWhere('bahan_baku_klien.spesifikasi', 'like', "%{$search}%"));
            }

            $topBahanBaku = $topBahanBakuRaw->get()
                ->groupBy(fn($row) => self::normalizeBahanBakuName($row->nama))
                ->map(function ($items, $normalizedName) {
                    return (object) ['nama' => $normalizedName, 'bahan_baku_ids' => $items->pluck('bahan_baku_id')->unique()->values()->all(), 'total' => $items->sum('omset_pengiriman')];
                })
                ->filter(fn($item) => $item->total > 0)
                ->sortByDesc('total')
                ->values();

            $monthColors    = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#F97316','#14B8A6','#F43F5E','#8B5CF6','#6366F1'];
            $monthNames     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            $bahanBakuNames = $topBahanBaku->pluck('nama')->toArray();
            $datasets       = [];

            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $monthData = [];
                foreach ($topBahanBaku as $bahanBaku) {
                    $omsetBulanQuery = DB::table('pengiriman')
                        ->leftJoin(DB::raw('(
                            SELECT pengiriman_id, MAX(subtotal) as subtotal
                            FROM invoice_penagihan
                            WHERE status != "digabung"
                            GROUP BY pengiriman_id
                        ) as invoice_penagihan'), 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                        ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                        ->join('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                        ->whereIn('order_details.bahan_baku_klien_id', $bahanBaku->bahan_baku_ids)
                        ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                        ->whereYear('pengiriman.tanggal_kirim', $tahun)
                        ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                        ->whereNull('pengiriman.deleted_at');

                    self::applyValidInvoiceFilter($omsetBulanQuery);

                    $omsetBulan = $omsetBulanQuery
                        ->select('pengiriman.id', DB::raw('COALESCE(MAX(invoice_penagihan.subtotal), SUM(pengiriman_details.qty_kirim * order_details.harga_jual)) as omset_pengiriman'))
                        ->groupBy('pengiriman.id')
                        ->get()->sum('omset_pengiriman');

                    $monthData[] = floatval($omsetBulan);
                }

                $datasets[] = [
                    'label' => $monthNames[$bulan - 1],
                    'data' => $monthData,
                    'backgroundColor' => $monthColors[$bulan - 1],
                    'borderColor' => $monthColors[$bulan - 1],
                    'borderWidth' => 1
                ];
            }

            return ['bahan_baku_names' => $bahanBakuNames, 'datasets' => $datasets];
        });
    }

    private static function applyValidInvoiceFilter($query)
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

    private static function normalizeBahanBakuName(?string $name): string
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
}
