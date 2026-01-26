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
    public function index(Request $request)
    {
        $title = 'Analisis Margin';
        $activeTab = 'margin';

        // Get filter parameters
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $picPurchasing = $request->get('pic_purchasing');
        $picMarketing = $request->get('pic_marketing');
        $klienId = $request->get('klien');
        $supplierId = $request->get('supplier');
        $bahanBakuId = $request->get('bahan_baku');

        // Get filter options
        $picPurchasingList = User::whereIn('id', function($query) {
                $query->select('purchasing_id')
                    ->from('pengiriman')
                    ->whereIn('status', ['menunggu_fisik','menunggu_verifikasi', 'berhasil'])
                    ->whereNull('deleted_at')
                    ->distinct();
            })
            ->select('id', 'nama')
            ->orderBy('nama')
            ->get();

        $picMarketingList = User::whereIn('id', function($query) {
                $query->select('user_id')
                    ->from('order_winners')
                    ->whereIn('order_id', function($subQuery) {
                        $subQuery->select('purchase_order_id')
                            ->from('pengiriman')
                            ->whereIn('status', ['menunggu_fisik','menunggu_verifikasi', 'berhasil'])
                            ->whereNull('deleted_at')
                            ->distinct();
                    });
            })
            ->select('id', 'nama')
            ->orderBy('nama')
            ->get();

        $klienList = Klien::whereIn('id', function($query) {
                $query->select('klien_id')
                    ->from('orders')
                    ->whereIn('id', function($subQuery) {
                        $subQuery->select('purchase_order_id')
                            ->from('pengiriman')
                            ->whereIn('status', ['menunggu_fisik','menunggu_verifikasi', 'berhasil'])
                            ->whereNull('deleted_at');
                    });
            })
            ->select('id', 'nama', 'cabang')
            ->orderBy('nama')
            ->get();

        $supplierList = Supplier::whereIn('id', function($query) {
                $query->select('supplier_id')
                    ->from('bahan_baku_supplier')
                    ->whereIn('id', function($subQuery) {
                        $subQuery->select('bahan_baku_supplier_id')
                            ->from('pengiriman_details')
                            ->whereIn('pengiriman_id', function($innerQuery) {
                                $innerQuery->select('id')
                                    ->from('pengiriman')
                                    ->whereIn('status', ['menunggu_fisik','menunggu_verifikasi', 'berhasil'])
                                    ->whereNull('deleted_at');
                            });
                    })
                    ->distinct();
            })
            ->select('id', 'nama')
            ->orderBy('nama')
            ->get();

        $bahanBakuList = BahanBakuKlien::whereIn('id', function($query) {
                $query->select('bahan_baku_klien_id')
                    ->from('order_details')
                    ->whereIn('id', function($subQuery) {
                        $subQuery->select('purchase_order_bahan_baku_id')
                            ->from('pengiriman_details')
                            ->whereIn('pengiriman_id', function($innerQuery) {
                                $innerQuery->select('id')
                                    ->from('pengiriman')
                                    ->whereIn('status', ['menunggu_fisik','menunggu_verifikasi', 'berhasil'])
                                    ->whereNull('deleted_at');
                            });
                    })
                    ->distinct();
            })
            ->select('id', 'nama')
            ->distinct()
            ->orderBy('nama')
            ->get()
            ->unique('nama') // Filter bahan baku dengan nama yang sama
            ->values(); // Reset array keys

        // Build query untuk pengiriman dengan relasi yang dibutuhkan
        $query = Pengiriman::with([
            'purchasing:id,nama',
            'order.klien:id,nama,cabang',
            'order.winner.user:id,nama',
            'pengirimanDetails.bahanBakuSupplier.supplier:id,nama',
            'pengirimanDetails.bahanBakuSupplier:id,nama,supplier_id',
            'pengirimanDetails.orderDetail.bahanBakuKlien:id,nama',
            'approvalPembayaran',
            'invoicePenagihan'
        ])
        ->whereIn('status', ['menunggu_fisik','menunggu_verifikasi', 'berhasil'])
        ->whereBetween('tanggal_kirim', [$startDate, $endDate]);

        // Apply filters
        if ($picPurchasing) {
            $query->where('purchasing_id', $picPurchasing);
        }

        if ($picMarketing) {
            $query->whereHas('order.winner', function($q) use ($picMarketing) {
                $q->where('user_id', $picMarketing);
            });
        }

        if ($klienId) {
            $query->whereHas('order', function($q) use ($klienId) {
                $q->where('klien_id', $klienId);
            });
        }

        if ($supplierId) {
            $query->whereHas('pengirimanDetails.bahanBakuSupplier', function($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
        }

        if ($bahanBakuId) {
            // Cari semua bahan baku dengan nama yang sama
            $bahanBakuNama = BahanBakuKlien::find($bahanBakuId)->nama ?? null;
            if ($bahanBakuNama) {
                $bahanBakuIds = BahanBakuKlien::where('nama', $bahanBakuNama)->pluck('id')->toArray();
                $query->whereHas('pengirimanDetails.orderDetail', function($q) use ($bahanBakuIds) {
                    $q->whereIn('bahan_baku_klien_id', $bahanBakuIds);
                });
            }
        }

        $pengirimanList = $query->orderBy('tanggal_kirim', 'asc')->get();

        // Process data untuk table
        $marginData = [];
        $totalQty = 0;
        $totalHargaBeli = 0;
        $totalHargaJual = 0;
        $totalMargin = 0;

        foreach ($pengirimanList as $p) {
            // Skip jika tidak ada approval pembayaran atau invoice penagihan
            if (!$p->approvalPembayaran && !$p->invoicePenagihan) {
                continue;
            }

            foreach ($p->pengirimanDetails as $detail) {
                // Hitung harga beli per kg (dari approval pembayaran)
                $hargaBeliPerKg = 0;
                $totalHargaBeliItem = 0;
                
                if ($p->approvalPembayaran) {
                    $qtyAfterRefraksi = $p->approvalPembayaran->qty_after_refraksi ?? $p->total_qty_kirim;
                    $amountAfterRefraksi = $p->approvalPembayaran->amount_after_refraksi ?? $p->total_harga_kirim;
                    
                    if ($qtyAfterRefraksi > 0) {
                        $hargaBeliPerKg = $amountAfterRefraksi / $qtyAfterRefraksi;
                    }
                    
                    $totalHargaBeliItem = $hargaBeliPerKg * $detail->qty_kirim;
                } else {
                    // Fallback ke harga dari detail
                    $hargaBeliPerKg = $detail->harga_satuan ?? 0;
                    $totalHargaBeliItem = $detail->total_harga ?? 0;
                }

                // Hitung harga jual per kg (dari invoice penagihan atau order detail)
                $hargaJualPerKg = 0;
                $totalHargaJualItem = 0;
                $sumberHargaJual = '-';
                
                if ($p->invoicePenagihan) {
                    $qtyJual = $p->invoicePenagihan->qty_after_refraksi ?? $p->invoicePenagihan->qty_before_refraksi ?? $p->total_qty_kirim;
                    $amountJual = $p->invoicePenagihan->amount_after_refraksi ?? $p->invoicePenagihan->subtotal ?? 0;
                    
                    if ($qtyJual > 0) {
                        $hargaJualPerKg = $amountJual / $qtyJual;
                    }
                    
                    $totalHargaJualItem = $hargaJualPerKg * $detail->qty_kirim;
                    $sumberHargaJual = 'Invoice Penagihan';
                } elseif ($detail->orderDetail && $detail->orderDetail->harga_jual > 0) {
                    $hargaJualPerKg = $detail->orderDetail->harga_jual;
                    $totalHargaJualItem = $detail->qty_kirim * $hargaJualPerKg;
                    $sumberHargaJual = 'Purchase Order';
                }

                // Hitung margin - Profit Margin: (margin / harga jual) * 100
                $margin = $totalHargaJualItem - $totalHargaBeliItem;
                $marginPercentage = $totalHargaJualItem > 0 ? ($margin / $totalHargaJualItem) * 100 : 0;

                // Get klien info
                $klien = $p->order->klien ?? null;
                $namaKlien = $klien ? $klien->nama . ($klien->cabang ? " ({$klien->cabang})" : '') : '-';

                // Get PIC Marketing info
                $picMarketingUser = $p->order->winner->user ?? null;
                $namaPicMarketing = $picMarketingUser ? $picMarketingUser->nama : '-';

                // Get supplier and bahan baku info
                $supplier = $detail->bahanBakuSupplier->supplier ?? null;
                $bahanBaku = $detail->orderDetail->bahanBakuKlien ?? null;
                $bahanBakuSupplier = $detail->bahanBakuSupplier ?? null;

                $marginData[] = [
                    'pengiriman_id' => $p->id,
                    'status' => $p->status,
                    'tanggal_kirim' => Carbon::parse($p->tanggal_kirim)->format('d/m/Y'),
                    'no_pengiriman' => $p->no_pengiriman ?? '-',
                    'no_po' => $p->order->po_number ?? '-',
                    'pic_purchasing' => $p->purchasing->nama ?? '-',
                    'pic_marketing' => $namaPicMarketing,
                    'klien' => $namaKlien,
                    'supplier' => $supplier->nama ?? '-',
                    'bahan_baku' => $bahanBaku->nama ?? $bahanBakuSupplier->nama ?? '-',
                    'qty' => $detail->qty_kirim,
                    'harga_beli_per_kg' => $hargaBeliPerKg,
                    'harga_beli_total' => $totalHargaBeliItem,
                    'harga_jual_per_kg' => $hargaJualPerKg,
                    'harga_jual_total' => $totalHargaJualItem,
                    'margin' => $margin,
                    'margin_percentage' => $marginPercentage,
                    'sumber_harga_jual' => $sumberHargaJual,
                    'has_refraksi' => $p->approvalPembayaran && $p->approvalPembayaran->refraksi_amount > 0,
                ];

                $totalQty += $detail->qty_kirim;
                $totalHargaBeli += $totalHargaBeliItem;
                $totalHargaJual += $totalHargaJualItem;
                $totalMargin += $margin;
            }
        }

        // Data sudah urut berdasarkan tanggal_kirim (asc) dari query

        // Calculate gross margin percentage - Profit Margin: (margin / harga jual) * 100
        $grossMarginPercentage = $totalHargaJual > 0 ? ($totalMargin / $totalHargaJual) * 100 : 0;

        // Calculate statistics
        $profitCount = count(array_filter($marginData, fn($item) => $item['margin'] >= 0));
        $lossCount = count($marginData) - $profitCount;
        $avgMarginPercentage = count($marginData) > 0 ? array_sum(array_column($marginData, 'margin_percentage')) / count($marginData) : 0;

        return view('pages.laporan.margin', compact(
            'title',
            'activeTab',
            'marginData',
            'totalQty',
            'totalHargaBeli',
            'totalHargaJual',
            'totalMargin',
            'grossMarginPercentage',
            'profitCount',
            'lossCount',
            'avgMarginPercentage',
            'startDate',
            'endDate',
            'picPurchasing',
            'picMarketing',
            'klienId',
            'supplierId',
            'bahanBakuId',
            'picPurchasingList',
            'picMarketingList',
            'klienList',
            'supplierList',
            'bahanBakuList'
        ));
    }

    public function export(Request $request)
    {
        // Get filter parameters (same as index method)
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $picPurchasing = $request->get('pic_purchasing');
        $picMarketing = $request->get('pic_marketing');
        $klienId = $request->get('klien');
        $supplierId = $request->get('supplier');
        $bahanBakuId = $request->get('bahan_baku');

        // Build query untuk pengiriman dengan relasi yang dibutuhkan
        $query = Pengiriman::with([
            'purchasing:id,nama',
            'order.klien:id,nama,cabang',
            'order.winner.user:id,nama',
            'pengirimanDetails.bahanBakuSupplier.supplier:id,nama',
            'pengirimanDetails.bahanBakuSupplier:id,nama,supplier_id',
            'pengirimanDetails.orderDetail.bahanBakuKlien:id,nama',
            'approvalPembayaran',
            'invoicePenagihan'
        ])
        ->whereIn('status', ['menunggu_fisik','menunggu_verifikasi', 'berhasil'])
        ->whereBetween('tanggal_kirim', [$startDate, $endDate]);

        // Apply filters
        if ($picPurchasing) {
            $query->where('purchasing_id', $picPurchasing);
            $picName = User::find($picPurchasing)->nama ?? '';
        }

        if ($picMarketing) {
            $query->whereHas('order.winner', function($q) use ($picMarketing) {
                $q->where('user_id', $picMarketing);
            });
            $picMarketingName = User::find($picMarketing)->nama ?? '';
        }

        if ($klienId) {
            $query->whereHas('order', function($q) use ($klienId) {
                $q->where('klien_id', $klienId);
            });
            $klienName = Klien::find($klienId)->nama ?? '';
        }

        if ($supplierId) {
            $query->whereHas('pengirimanDetails.bahanBakuSupplier', function($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
            $supplierName = Supplier::find($supplierId)->nama ?? '';
        }

        if ($bahanBakuId) {
            // Cari semua bahan baku dengan nama yang sama
            $bahanBakuNama = BahanBakuKlien::find($bahanBakuId)->nama ?? null;
            if ($bahanBakuNama) {
                $bahanBakuIds = BahanBakuKlien::where('nama', $bahanBakuNama)->pluck('id')->toArray();
                $query->whereHas('pengirimanDetails.orderDetail', function($q) use ($bahanBakuIds) {
                    $q->whereIn('bahan_baku_klien_id', $bahanBakuIds);
                });
                $bahanBakuName = $bahanBakuNama;
            }
        }

        $pengirimanList = $query->orderBy('tanggal_kirim', 'asc')->get();

        // Process data untuk PDF
        $marginData = [];
        $totalQty = 0;
        $totalHargaBeli = 0;
        $totalHargaJual = 0;
        $totalMargin = 0;

        foreach ($pengirimanList as $p) {
            if (!$p->approvalPembayaran && !$p->invoicePenagihan) {
                continue;
            }

            foreach ($p->pengirimanDetails as $detail) {
                $hargaBeliPerKg = 0;
                $totalHargaBeliItem = 0;
                
                if ($p->approvalPembayaran) {
                    $qtyAfterRefraksi = $p->approvalPembayaran->qty_after_refraksi ?? $p->total_qty_kirim;
                    $amountAfterRefraksi = $p->approvalPembayaran->amount_after_refraksi ?? $p->total_harga_kirim;
                    
                    if ($qtyAfterRefraksi > 0) {
                        $hargaBeliPerKg = $amountAfterRefraksi / $qtyAfterRefraksi;
                    }
                    
                    $totalHargaBeliItem = $hargaBeliPerKg * $detail->qty_kirim;
                } else {
                    $hargaBeliPerKg = $detail->harga_satuan ?? 0;
                    $totalHargaBeliItem = $detail->total_harga ?? 0;
                }

                $hargaJualPerKg = 0;
                $totalHargaJualItem = 0;
                
                if ($p->invoicePenagihan) {
                    $qtyJual = $p->invoicePenagihan->qty_after_refraksi ?? $p->invoicePenagihan->qty_before_refraksi ?? $p->total_qty_kirim;
                    $amountJual = $p->invoicePenagihan->amount_after_refraksi ?? $p->invoicePenagihan->subtotal ?? 0;
                    
                    if ($qtyJual > 0) {
                        $hargaJualPerKg = $amountJual / $qtyJual;
                    }
                    
                    $totalHargaJualItem = $hargaJualPerKg * $detail->qty_kirim;
                } elseif ($detail->orderDetail && $detail->orderDetail->harga_jual > 0) {
                    $hargaJualPerKg = $detail->orderDetail->harga_jual;
                    $totalHargaJualItem = $detail->qty_kirim * $hargaJualPerKg;
                }

                $margin = $totalHargaJualItem - $totalHargaBeliItem;
                $marginPercentage = $totalHargaJualItem > 0 ? ($margin / $totalHargaJualItem) * 100 : 0;

                $klien = $p->order->klien ?? null;
                $namaKlien = $klien ? $klien->nama . ($klien->cabang ? " ({$klien->cabang})" : '') : '-';

                // Get PIC Marketing info
                $picMarketingUser = $p->order->winner->user ?? null;
                $namaPicMarketing = $picMarketingUser ? $picMarketingUser->nama : '-';

                $supplier = $detail->bahanBakuSupplier->supplier ?? null;
                $bahanBaku = $detail->orderDetail->bahanBakuKlien ?? null;
                $bahanBakuSupplier = $detail->bahanBakuSupplier ?? null;

                $marginData[] = [
                    'tanggal_kirim' => Carbon::parse($p->tanggal_kirim)->format('d/m/Y'),
                    'no_pengiriman' => $p->no_pengiriman ?? '-',
                    'pic_purchasing' => $p->purchasing->nama ?? '-',
                    'pic_marketing' => $namaPicMarketing,
                    'klien' => $namaKlien,
                    'supplier' => $supplier->nama ?? '-',
                    'bahan_baku' => $bahanBaku->nama ?? $bahanBakuSupplier->nama ?? '-',
                    'qty' => $detail->qty_kirim,
                    'harga_beli_per_kg' => $hargaBeliPerKg,
                    'harga_beli_total' => $totalHargaBeliItem,
                    'harga_jual_per_kg' => $hargaJualPerKg,
                    'harga_jual_total' => $totalHargaJualItem,
                    'margin' => $margin,
                    'margin_percentage' => $marginPercentage,
                ];

                $totalQty += $detail->qty_kirim;
                $totalHargaBeli += $totalHargaBeliItem;
                $totalHargaJual += $totalHargaJualItem;
                $totalMargin += $margin;
            }
        }

        // Sort by margin percentage descending
        usort($marginData, function($a, $b) {
            return $b['margin_percentage'] <=> $a['margin_percentage'];
        });

        // Calculate gross margin percentage
        $grossMarginPercentage = $totalHargaJual > 0 ? ($totalMargin / $totalHargaJual) * 100 : 0;

        // Calculate profit/loss count
        $profitCount = count(array_filter($marginData, function($item) {
            return $item['margin'] >= 0;
        }));
        $lossCount = count($marginData) - $profitCount;

        // Build filter description
        $filterDesc = [];
        if ($picPurchasing && isset($picName)) {
            $filterDesc[] = 'PIC Procurement: ' . $picName;
        }
        if ($picMarketing && isset($picMarketingName)) {
            $filterDesc[] = 'PIC Marketing: ' . $picMarketingName;
        }
        if ($klienId && isset($klienName)) {
            $filterDesc[] = 'Klien: ' . $klienName;
        }
        if ($supplierId && isset($supplierName)) {
            $filterDesc[] = 'Supplier: ' . $supplierName;
        }
        if ($bahanBakuId && isset($bahanBakuName)) {
            $filterDesc[] = 'Bahan Baku: ' . $bahanBakuName;
        }

        // Data untuk PDF
        $data = [
            'marginData' => $marginData,
            'totalQty' => $totalQty,
            'totalHargaBeli' => $totalHargaBeli,
            'totalHargaJual' => $totalHargaJual,
            'totalMargin' => $totalMargin,
            'grossMarginPercentage' => $grossMarginPercentage,
            'profitCount' => $profitCount,
            'lossCount' => $lossCount,
            'startDate' => Carbon::parse($startDate)->format('d/m/Y'),
            'endDate' => Carbon::parse($endDate)->format('d/m/Y'),
            'filterDesc' => implode(' • ', $filterDesc),
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pages.laporan.pdf.margin', $data);
        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'Laporan_Margin_' . Carbon::parse($startDate)->format('d-m-Y') . '_sd_' . Carbon::parse($endDate)->format('d-m-Y') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        // Get filter parameters
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $picPurchasing = $request->get('pic_purchasing');
        $picMarketing = $request->get('pic_marketing');
        $klienId = $request->get('klien');
        $supplierId = $request->get('supplier');
        $bahanBakuId = $request->get('bahan_baku');

        // Build query untuk pengiriman dengan relasi yang dibutuhkan
        $query = Pengiriman::with([
            'purchasing:id,nama',
            'order.klien:id,nama,cabang',
            'order.winner.user:id,nama',
            'pengirimanDetails.bahanBakuSupplier.supplier:id,nama',
            'pengirimanDetails.bahanBakuSupplier:id,nama,supplier_id',
            'pengirimanDetails.orderDetail.bahanBakuKlien:id,nama',
            'approvalPembayaran',
            'invoicePenagihan'
        ])
        ->whereIn('status', ['menunggu_fisik','menunggu_verifikasi', 'berhasil'])
        ->whereBetween('tanggal_kirim', [$startDate, $endDate]);

        // Apply filters and get names
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if ($picPurchasing) {
            $query->where('purchasing_id', $picPurchasing);
            $filters['pic_purchasing_name'] = User::find($picPurchasing)->nama ?? '';
        }

        if ($picMarketing) {
            $query->whereHas('order.winner', function($q) use ($picMarketing) {
                $q->where('user_id', $picMarketing);
            });
            $filters['pic_marketing_name'] = User::find($picMarketing)->nama ?? '';
        }

        if ($klienId) {
            $query->whereHas('order', function($q) use ($klienId) {
                $q->where('klien_id', $klienId);
            });
            $klien = Klien::find($klienId);
            $filters['klien_name'] = $klien ? $klien->nama . ($klien->cabang ? " ({$klien->cabang})" : '') : '';
        }

        if ($supplierId) {
            $query->whereHas('pengirimanDetails.bahanBakuSupplier', function($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
            $filters['supplier_name'] = Supplier::find($supplierId)->nama ?? '';
        }

        if ($bahanBakuId) {
            // Cari semua bahan baku dengan nama yang sama
            $bahanBakuNama = BahanBakuKlien::find($bahanBakuId)->nama ?? null;
            if ($bahanBakuNama) {
                $bahanBakuIds = BahanBakuKlien::where('nama', $bahanBakuNama)->pluck('id')->toArray();
                $query->whereHas('pengirimanDetails.orderDetail', function($q) use ($bahanBakuIds) {
                    $q->whereIn('bahan_baku_klien_id', $bahanBakuIds);
                });
                $filters['bahan_baku_name'] = $bahanBakuNama;
            }
        }

        $pengirimanList = $query->orderBy('tanggal_kirim', 'asc')->get();

        // Process data untuk Excel
        $marginData = [];
        $totalQty = 0;
        $totalHargaBeli = 0;
        $totalHargaJual = 0;
        $totalMargin = 0;

        foreach ($pengirimanList as $p) {
            if (!$p->approvalPembayaran && !$p->invoicePenagihan) {
                continue;
            }

            foreach ($p->pengirimanDetails as $detail) {
                $hargaBeliPerKg = 0;
                $totalHargaBeliItem = 0;
                
                if ($p->approvalPembayaran) {
                    $qtyAfterRefraksi = $p->approvalPembayaran->qty_after_refraksi ?? $p->total_qty_kirim;
                    $amountAfterRefraksi = $p->approvalPembayaran->amount_after_refraksi ?? $p->total_harga_kirim;
                    
                    if ($qtyAfterRefraksi > 0) {
                        $hargaBeliPerKg = $amountAfterRefraksi / $qtyAfterRefraksi;
                    }
                    
                    $totalHargaBeliItem = $hargaBeliPerKg * $detail->qty_kirim;
                } else {
                    $hargaBeliPerKg = $detail->harga_satuan ?? 0;
                    $totalHargaBeliItem = $detail->total_harga ?? 0;
                }

                $hargaJualPerKg = 0;
                $totalHargaJualItem = 0;
                
                if ($p->invoicePenagihan) {
                    $qtyJual = $p->invoicePenagihan->qty_after_refraksi ?? $p->invoicePenagihan->qty_before_refraksi ?? $p->total_qty_kirim;
                    $amountJual = $p->invoicePenagihan->amount_after_refraksi ?? $p->invoicePenagihan->subtotal ?? 0;
                    
                    if ($qtyJual > 0) {
                        $hargaJualPerKg = $amountJual / $qtyJual;
                    }
                    
                    $totalHargaJualItem = $hargaJualPerKg * $detail->qty_kirim;
                } elseif ($detail->orderDetail && $detail->orderDetail->harga_jual > 0) {
                    $hargaJualPerKg = $detail->orderDetail->harga_jual;
                    $totalHargaJualItem = $detail->qty_kirim * $hargaJualPerKg;
                }

                $margin = $totalHargaJualItem - $totalHargaBeliItem;
                $marginPercentage = $totalHargaJualItem > 0 ? ($margin / $totalHargaJualItem) * 100 : 0;

                $klien = $p->order->klien ?? null;
                $namaKlien = $klien ? $klien->nama . ($klien->cabang ? " ({$klien->cabang})" : '') : '-';

                $picMarketingUser = $p->order->winner->user ?? null;
                $namaPicMarketing = $picMarketingUser ? $picMarketingUser->nama : '-';

                $supplier = $detail->bahanBakuSupplier->supplier ?? null;
                $bahanBaku = $detail->orderDetail->bahanBakuKlien ?? null;
                $bahanBakuSupplier = $detail->bahanBakuSupplier ?? null;

                $marginData[] = [
                    'tanggal_kirim' => Carbon::parse($p->tanggal_kirim)->format('d/m/Y'),
                    'no_pengiriman' => $p->no_pengiriman ?? '-',
                    'pic_purchasing' => $p->purchasing->nama ?? '-',
                    'pic_marketing' => $namaPicMarketing,
                    'klien' => $namaKlien,
                    'supplier' => $supplier->nama ?? '-',
                    'bahan_baku' => $bahanBaku->nama ?? $bahanBakuSupplier->nama ?? '-',
                    'qty' => $detail->qty_kirim,
                    'harga_beli_per_kg' => $hargaBeliPerKg,
                    'harga_beli_total' => $totalHargaBeliItem,
                    'harga_jual_per_kg' => $hargaJualPerKg,
                    'harga_jual_total' => $totalHargaJualItem,
                    'margin' => $margin,
                    'margin_percentage' => $marginPercentage,
                    'has_refraksi' => $p->approvalPembayaran && $p->approvalPembayaran->refraksi_amount > 0,
                ];

                $totalQty += $detail->qty_kirim;
                $totalHargaBeli += $totalHargaBeliItem;
                $totalHargaJual += $totalHargaJualItem;
                $totalMargin += $margin;
            }
        }

        // Data sudah urut berdasarkan tanggal_kirim (asc) dari query

        // Calculate gross margin percentage
        $grossMarginPercentage = $totalHargaJual > 0 ? ($totalMargin / $totalHargaJual) * 100 : 0;

        // Calculate profit/loss count
        $profitCount = count(array_filter($marginData, fn($item) => $item['margin'] >= 0));
        $lossCount = count($marginData) - $profitCount;

        // Prepare totals
        $totals = [
            'totalQty' => $totalQty,
            'totalHargaBeli' => $totalHargaBeli,
            'totalHargaJual' => $totalHargaJual,
            'totalMargin' => $totalMargin,
            'grossMarginPercentage' => $grossMarginPercentage,
            'profitCount' => $profitCount,
            'lossCount' => $lossCount,
        ];

        // Generate filename
        $filename = 'Laporan_Margin_' . Carbon::parse($startDate)->format('d-m-Y') . '_sd_' . Carbon::parse($endDate)->format('d-m-Y') . '.xlsx';

        // Export to Excel
        return Excel::download(new MarginExport($marginData, $totals, $filters), $filename);
    }
}
