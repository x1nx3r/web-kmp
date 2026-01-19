<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\InvoicePenagihan;
use App\Models\Pengiriman;
use App\Models\TargetOmset;
use App\Models\OmsetManual;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        // ========== OMSET MINGGUAN (Paling Penting) ==========
        $currentYear = Carbon::now()->year;
        $targetOmset = TargetOmset::getTargetForYear($currentYear);
        $targetMingguan = $targetOmset->target_mingguan ?? 0;
        $targetBulanan = $targetOmset->target_bulanan ?? 0;
        $targetTahunan = $targetOmset->target_tahunan ?? 0;
        
        // Tentukan minggu ke berapa sekarang dalam bulan ini (1-4, berdasarkan tanggal 1-7, 8-14, 15-21, 22-akhir)
        $today = Carbon::now();
        $dayOfMonth = $today->day;
        $currentWeekOfMonth = 1;
        
        if ($dayOfMonth >= 1 && $dayOfMonth <= 7) {
            $currentWeekOfMonth = 1;
        } elseif ($dayOfMonth >= 8 && $dayOfMonth <= 14) {
            $currentWeekOfMonth = 2;
        } elseif ($dayOfMonth >= 15 && $dayOfMonth <= 21) {
            $currentWeekOfMonth = 3;
        } else {
            $currentWeekOfMonth = 4;
        }
        
        // Hitung range tanggal untuk minggu ini (sesuai logik rekap bulanan)
        $startOfMonth = Carbon::now()->startOfMonth();
        
        if ($currentWeekOfMonth == 1) {
            $startOfWeek = $startOfMonth->copy();
        } else {
            $startOfWeek = $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);
        }
        
        if ($currentWeekOfMonth == 4) {
            // Minggu ke-4 sampai akhir bulan
            $endOfWeek = $startOfMonth->copy()->endOfMonth();
        } else {
            $endOfWeek = $startOfWeek->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
        }
        
        // Omset Minggu Ini - Sistem (menggunakan range yang benar dengan fallback logic)
        $omsetSistemMingguIni = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereBetween('pengiriman.tanggal_kirim', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
            ->whereNull('pengiriman.deleted_at')
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
        
        // Omset Manual Minggu Ini (dibagi 4 dari bulan ini)
        $omsetManualBulanIni = OmsetManual::where('tahun', Carbon::now()->year)
            ->where('bulan', Carbon::now()->month)
            ->value('omset_manual') ?? 0;
        $omsetManualMingguIni = $omsetManualBulanIni / 4;
        
        // Total Omset Minggu Ini
        $omsetMingguIni = $omsetSistemMingguIni + $omsetManualMingguIni;
        
        // Omset Bulan Ini - Sistem (dengan fallback logic)
        $omsetSistemBulanIni = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->whereMonth('pengiriman.tanggal_kirim', Carbon::now()->month)
            ->whereNull('pengiriman.deleted_at')
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
        
        // Total Omset Bulan Ini
        $omsetBulanIni = $omsetSistemBulanIni + $omsetManualBulanIni;
        
        // Omset Tahun Ini - Sistem (dengan fallback logic)
        $omsetSistemTahunIni = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', Carbon::now()->year)
            ->whereNull('pengiriman.deleted_at')
            ->select(
                'pengiriman.id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->groupBy('pengiriman.id')
            ->get()
            ->sum('omset_pengiriman');
        
        // Omset Manual Tahun Ini
        $omsetManualTahunIni = OmsetManual::where('tahun', Carbon::now()->year)
            ->sum('omset_manual') ?? 0;
        
        // Total Omset Tahun Ini
        $omsetTahunIni = $omsetSistemTahunIni + $omsetManualTahunIni;
        
        // Calculate Adjusted Target untuk bulan dan minggu saat ini (dengan carry forward)
        // Hitung total sisa target dari bulan-bulan sebelumnya
        $bulanSekarang = Carbon::now()->month;
        $sisaTargetSebelumnya = 0;
        
        for ($b = 1; $b < $bulanSekarang; $b++) {
            $omsetSistemBulanLalu = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereYear('pengiriman.tanggal_kirim', $currentYear)
                ->whereMonth('pengiriman.tanggal_kirim', $b)
                ->whereNull('pengiriman.deleted_at')
                ->select(
                    'pengiriman.id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman')
                )
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');
            
            $omsetManualBulanLalu = OmsetManual::where('tahun', $currentYear)
                ->where('bulan', $b)
                ->value('omset_manual') ?? 0;
            
            $omsetTotalBulanLalu = $omsetSistemBulanLalu + $omsetManualBulanLalu;
            $targetBulanLalu = $targetBulanan + $sisaTargetSebelumnya;
            $selisihBulanLalu = $omsetTotalBulanLalu - $targetBulanLalu;
            
            if ($selisihBulanLalu < 0) {
                $sisaTargetSebelumnya = $targetBulanLalu - $omsetTotalBulanLalu;
            } else {
                $sisaTargetSebelumnya = 0;
            }
        }
        
        // Target Adjusted untuk bulan ini
        $targetBulananAdjusted = $targetBulanan + $sisaTargetSebelumnya;
        
        // Target mingguan BASE (untuk bulan ini)
        $targetMingguanBase = $targetBulananAdjusted / 4;
        
        // Calculate target mingguan adjusted untuk minggu ini dengan carry forward dari minggu-minggu sebelumnya di bulan ini
        $sisaTargetMingguanSebelumnya = 0;
        
        // Loop dari minggu 1 sampai minggu sebelum minggu ini
        for ($w = 1; $w < $currentWeekOfMonth; $w++) {
            // Hitung range tanggal untuk minggu w (sesuai logika rekap bulanan)
            if ($w == 1) {
                $weekStart = $startOfMonth->copy();
            } else {
                $weekStart = $startOfMonth->copy()->addDays(($w - 1) * 7);
            }
            
            if ($w == 4) {
                $weekEnd = $startOfMonth->copy()->endOfMonth();
            } else {
                $weekEnd = $weekStart->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
            }
            
            // Hitung omset sistem untuk minggu w (dengan fallback logic)
            $omsetSistemWeek = DB::table('pengiriman')
                ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                ->whereBetween('pengiriman.tanggal_kirim', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                ->whereNull('pengiriman.deleted_at')
                ->select(
                    'pengiriman.id',
                    DB::raw('COALESCE(
                        MAX(invoice_penagihan.amount_after_refraksi),
                        SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                    ) as omset_pengiriman')
                )
                ->groupBy('pengiriman.id')
                ->get()
                ->sum('omset_pengiriman');
            
            // Omset manual untuk minggu ini (1/4 dari omset manual bulan ini)
            $omsetManualWeek = $omsetManualBulanIni / 4;
            
            // Total omset minggu
            $omsetTotalWeek = $omsetSistemWeek + $omsetManualWeek;
            
            // Target untuk minggu w (dengan carry forward)
            $targetWeek = $targetMingguanBase + $sisaTargetMingguanSebelumnya;
            
            // Selisih
            $selisihWeek = $omsetTotalWeek - $targetWeek;
            
            // Update sisa target untuk minggu berikutnya
            if ($selisihWeek < 0) {
                // Target tidak tercapai, akumulasi sisa
                $sisaTargetMingguanSebelumnya = $targetWeek - $omsetTotalWeek;
            } else {
                // Target tercapai, reset sisa ke 0
                $sisaTargetMingguanSebelumnya = 0;
            }
        }
        
        // Target Adjusted untuk minggu ini = base + sisa dari minggu-minggu sebelumnya
        $targetMingguanAdjusted = $targetMingguanBase + $sisaTargetMingguanSebelumnya;
        
        // Progress Percentages dengan target adjusted
        $progressMinggu = $targetMingguanAdjusted > 0 ? ($omsetMingguIni / $targetMingguanAdjusted) * 100 : 0;
        $progressBulan = $targetBulananAdjusted > 0 ? ($omsetBulanIni / $targetBulananAdjusted) * 100 : 0;
        $progressTahun = $targetTahunan > 0 ? ($omsetTahunIni / $targetTahunan) * 100 : 0;
        
        // ========== OUTSTANDING PO ==========
        // Total Outstanding (nilai dari order details dengan status dikonfirmasi & diproses)
        $totalOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.total_harga');
        
        // Total Qty Outstanding
        $totalQtyOutstanding = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
            ->sum('order_details.qty');
        
        // PO Berjalan
        $poBerjalan = Order::whereIn('status', ['dikonfirmasi', 'diproses'])->count();
        
        // ========== PENGIRIMAN MINGGU INI ==========
        // Menggunakan logic yang sama dengan omset (pembagian bulan menjadi 4 minggu)
        // Hitung range tanggal untuk minggu ini berdasarkan pembagian bulan
        if ($currentWeekOfMonth == 1) {
            $weekStartPengiriman = $startOfMonth->copy();
        } else {
            $weekStartPengiriman = $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);
        }
        
        if ($currentWeekOfMonth == 4) {
            $weekEndPengiriman = $startOfMonth->copy()->endOfMonth();
        } else {
            $weekEndPengiriman = $weekStartPengiriman->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
        }
        
        // Get all pengiriman with status menunggu_fisik, menunggu_verifikasi, dan berhasil
        $pengirimanMingguIni = Pengiriman::with(['forecast:id,total_qty_forecast', 'order.klien', 'purchasing'])
            ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereBetween('tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
            ->get();
        
        // Prepare arrays untuk menyimpan detail pengiriman berdasarkan kategori
        $pengirimanNormalList = [];
        $pengirimanBongkarSebagianList = [];
        
        // Count pengiriman normal (>70%) dan bongkar sebagian (<=70%)
        $pengirimanNormalMingguIni = 0;
        $pengirimanBongkarSebagianMingguIni = 0;
        
        foreach ($pengirimanMingguIni as $pengiriman) {
            if ($pengiriman->forecast && $pengiriman->forecast->total_qty_forecast > 0) {
                $percentage = ($pengiriman->total_qty_kirim / $pengiriman->forecast->total_qty_forecast) * 100;
                
                if ($percentage > 70) {
                    // Pengiriman Normal (>70%)
                    $pengirimanNormalMingguIni++;
                    $pengirimanNormalList[] = [
                        'id' => $pengiriman->id,
                        'po_number' => $pengiriman->order->po_number ?? 'N/A',
                        'tanggal_kirim' => $pengiriman->tanggal_kirim,
                        'klien' => $pengiriman->order->klien->nama ?? 'N/A',
                        'cabang' => $pengiriman->order->klien->cabang ?? null,
                        'total_qty_kirim' => $pengiriman->total_qty_kirim,
                        'total_qty_forecast' => $pengiriman->forecast->total_qty_forecast,
                        'percentage' => round($percentage, 2),
                        'status' => $pengiriman->status,
                        'purchasing' => $pengiriman->purchasing->nama ?? 'N/A',
                    ];
                } elseif ($percentage > 0 && $percentage <= 70) {
                    // Bongkar Sebagian (>0% dan <=70%)
                    $pengirimanBongkarSebagianMingguIni++;
                    $pengirimanBongkarSebagianList[] = [
                        'id' => $pengiriman->id,
                        'po_number' => $pengiriman->order->po_number ?? 'N/A',
                        'tanggal_kirim' => $pengiriman->tanggal_kirim,
                        'klien' => $pengiriman->order->klien->nama ?? 'N/A',
                        'cabang' => $pengiriman->order->klien->cabang ?? null,
                        'total_qty_kirim' => $pengiriman->total_qty_kirim,
                        'total_qty_forecast' => $pengiriman->forecast->total_qty_forecast,
                        'percentage' => round($percentage, 2),
                        'status' => $pengiriman->status,
                        'purchasing' => $pengiriman->purchasing->nama ?? 'N/A',
                    ];
                }
            } else {
                // Jika tidak ada forecast, anggap sebagai pengiriman normal
                $pengirimanNormalMingguIni++;
                $pengirimanNormalList[] = [
                    'id' => $pengiriman->id,
                    'po_number' => $pengiriman->order->po_number ?? 'N/A',
                    'tanggal_kirim' => $pengiriman->tanggal_kirim,
                    'klien' => $pengiriman->order->klien->nama ?? 'N/A',
                    'cabang' => $pengiriman->order->klien->cabang ?? null,
                    'total_qty_kirim' => $pengiriman->total_qty_kirim,
                    'total_qty_forecast' => 0,
                    'percentage' => 0,
                    'status' => $pengiriman->status,
                    'purchasing' => $pengiriman->purchasing->nama ?? 'N/A',
                ];
            }
        }
        
        $totalQtyPengirimanMingguIni = Pengiriman::leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->whereBetween('pengiriman.tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->sum(DB::raw('COALESCE(invoice_penagihan.qty_after_refraksi, pengiriman.total_qty_kirim)'));
        
        // ========== PENGIRIMAN GAGAL MINGGU INI ==========
        $pengirimanGagalList = Pengiriman::with(['order.klien', 'purchasing'])
            ->whereBetween('tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
            ->where('status', 'gagal')
            ->get()
            ->map(function($pengiriman) {
                return [
                    'id' => $pengiriman->id,
                    'po_number' => $pengiriman->order->po_number ?? 'N/A',
                    'tanggal_kirim' => $pengiriman->tanggal_kirim,
                    'klien' => $pengiriman->order->klien->nama ?? 'N/A',
                    'cabang' => $pengiriman->order->klien->cabang ?? null,
                    'total_qty_kirim' => $pengiriman->total_qty_kirim,
                    'catatan' => $pengiriman->catatan ?? '-',
                    'status' => $pengiriman->status,
                    'purchasing' => $pengiriman->purchasing->nama ?? 'N/A',
                ];
            })
            ->toArray();
        
        // ========== ORDER BULAN INI ==========
        $orderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->count();
        
        $nilaiOrderBulanIni = Order::whereYear('tanggal_order', Carbon::now()->year)
            ->whereMonth('tanggal_order', Carbon::now()->month)
            ->sum('total_amount');
        
        // ========== MARGIN MINGGU INI ==========
        // Query pengiriman untuk minggu ini dengan relasi yang dibutuhkan
        $pengirimanMarginMingguIni = Pengiriman::with([
            'purchasing:id,nama',
            'order.klien:id,nama,cabang',
            'order.winner.user:id,nama',
            'pengirimanDetails.bahanBakuSupplier.supplier:id,nama',
            'pengirimanDetails.bahanBakuSupplier:id,nama,supplier_id',
            'pengirimanDetails.orderDetail.bahanBakuKlien:id,nama',
            'approvalPembayaran',
            'invoicePenagihan'
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereBetween('tanggal_kirim', [$weekStartPengiriman->startOfDay(), $weekEndPengiriman->endOfDay()])
        ->get();
        
        // Process margin data untuk minggu ini
        $marginDataMingguIni = [];
        $totalMarginMingguIni = 0;
        $totalHargaBeliMingguIni = 0;
        $totalHargaJualMingguIni = 0;
        
        foreach ($pengirimanMarginMingguIni as $p) {
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
                    $hargaBeliPerKg = $detail->harga_satuan ?? 0;
                    $totalHargaBeliItem = $detail->total_harga ?? 0;
                }

                // Hitung harga jual per kg (dari invoice penagihan atau order detail)
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

                // Hitung margin - Profit Margin: (margin / harga jual) * 100
                $margin = $totalHargaJualItem - $totalHargaBeliItem;
                $marginPercentage = $totalHargaJualItem > 0 ? ($margin / $totalHargaJualItem) * 100 : 0;

                // Get klien info
                $klien = $p->order->klien ?? null;
                $namaKlien = $klien ? $klien->nama . ($klien->cabang ? " - {$klien->cabang}" : '') : '-';

                // Get PIC Marketing info
                $picMarketingUser = $p->order->winner->user ?? null;
                $namaPicMarketing = $picMarketingUser ? $picMarketingUser->nama : '-';

                // Get supplier and bahan baku info
                $supplier = $detail->bahanBakuSupplier->supplier ?? null;
                $bahanBaku = $detail->orderDetail->bahanBakuKlien ?? null;
                $bahanBakuSupplier = $detail->bahanBakuSupplier ?? null;

                $marginDataMingguIni[] = [
                    'pengiriman_id' => $p->id,
                    'status' => $p->status,
                    'pic_purchasing' => $p->purchasing->nama ?? '-',
                    'pic_marketing' => $namaPicMarketing,
                    'klien' => $namaKlien,
                    'supplier' => $supplier->nama ?? '-',
                    'bahan_baku' => $bahanBaku->nama ?? $bahanBakuSupplier->nama ?? '-',
                    'margin' => $margin,
                    'margin_percentage' => $marginPercentage,
                ];

                $totalMarginMingguIni += $margin;
                $totalHargaBeliMingguIni += $totalHargaBeliItem;
                $totalHargaJualMingguIni += $totalHargaJualItem;
            }
        }

        // Sort by margin percentage descending
        usort($marginDataMingguIni, function($a, $b) {
            return $b['margin_percentage'] <=> $a['margin_percentage'];
        });
        $topMarginMingguIni = $marginDataMingguIni; // Tampilkan semua data
        
        // Hitung gross margin percentage minggu ini - Profit Margin: (margin / harga jual) * 100
        $grossMarginMingguIni = $totalHargaJualMingguIni > 0 ? ($totalMarginMingguIni / $totalHargaJualMingguIni) * 100 : 0;

        // ========== GROSS MARGIN BULAN INI ==========
        // Query pengiriman untuk bulan ini
        $pengirimanMarginBulanIni = Pengiriman::with([
            'pengirimanDetails.bahanBakuSupplier',
            'pengirimanDetails.orderDetail',
            'approvalPembayaran',
            'invoicePenagihan'
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereYear('tanggal_kirim', Carbon::now()->year)
        ->whereMonth('tanggal_kirim', Carbon::now()->month)
        ->get();
        
        // Process margin data untuk bulan ini
        $totalMarginBulanIni = 0;
        $totalHargaBeliBulanIni = 0;
        $totalHargaJualBulanIni = 0;
        $countMarginBulanIni = 0;
        
        foreach ($pengirimanMarginBulanIni as $p) {
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

                $totalMarginBulanIni += $margin;
                $totalHargaBeliBulanIni += $totalHargaBeliItem;
                $totalHargaJualBulanIni += $totalHargaJualItem;
                $countMarginBulanIni++;
            }
        }

        // Hitung gross margin bulan ini - Profit Margin: (margin / harga jual) * 100
        $grossMarginBulanIni = $totalHargaJualBulanIni > 0 ? ($totalMarginBulanIni / $totalHargaJualBulanIni) * 100 : 0;
        
        return view('pages.dashboard', compact(
            'targetMingguan',
            'targetBulanan',
            'targetTahunan',
            'targetMingguanAdjusted',
            'targetBulananAdjusted',
            'omsetMingguIni',
            'omsetBulanIni',
            'omsetTahunIni',
            'omsetSistemMingguIni',
            'omsetManualMingguIni',
            'omsetSistemBulanIni',
            'omsetManualBulanIni',
            'progressMinggu',
            'progressBulan',
            'progressTahun',
            'totalOutstanding',
            'totalQtyOutstanding',
            'poBerjalan',
            'pengirimanNormalMingguIni',
            'pengirimanBongkarSebagianMingguIni',
            'totalQtyPengirimanMingguIni',
            'pengirimanNormalList',
            'pengirimanBongkarSebagianList',
            'pengirimanGagalList',
            'orderBulanIni',
            'nilaiOrderBulanIni',
            'topMarginMingguIni',
            'grossMarginMingguIni',
            'totalMarginMingguIni',
            'grossMarginBulanIni'
        ));
    }
    
    /**
     * Get Omset per Klien Chart Data (AJAX)
     */
    public function getOmsetPerKlien(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');
        
        // Get top 5 klien berdasarkan total omset tahun ini (with optional search filter & fallback logic)
        $topKlienQuery = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
            ->select('kliens.id as klien_id', 'kliens.nama', 'kliens.cabang', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman'))
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $tahun)
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('kliens.deleted_at')
            ->groupBy('pengiriman.id', 'kliens.id', 'kliens.nama', 'kliens.cabang');
        
        // Apply search filter if provided
        if (!empty($search)) {
            $topKlienQuery->where(function($q) use ($search) {
                $q->where('kliens.nama', 'like', '%' . $search . '%')
                  ->orWhere('kliens.cabang', 'like', '%' . $search . '%');
            });
        }
        
        $topKlienData = $topKlienQuery->get();
        
        // Group by klien and sum omset_pengiriman
        $topKlien = $topKlienData->groupBy('klien_id')->map(function($items) {
            $first = $items->first();
            return (object)[
                'klien_id' => $first->klien_id,
                'nama' => $first->nama,
                'cabang' => $first->cabang,
                'total' => $items->sum('omset_pengiriman')
            ];
        })->sortByDesc('total')->values();
        
        $klienNames = [];
        $datasets = [];
        
        // Warna untuk setiap bulan
        $monthColors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899',
            '#06B6D4', '#F97316', '#14B8A6', '#F43F5E', '#8B5CF6', '#6366F1'
        ];
        
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Prepare datasets per month
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthData = [];
            
            foreach ($topKlien as $klien) {
                // Get omset untuk klien ini di bulan ini (dengan fallback logic)
                $omsetBulan = DB::table('pengiriman')
                    ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                    ->leftJoin('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->join('orders', 'pengiriman.purchase_order_id', '=', 'orders.id')
                    ->where('orders.klien_id', $klien->klien_id)
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereYear('pengiriman.tanggal_kirim', $tahun)
                    ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                    ->whereNull('pengiriman.deleted_at')
                    ->select(
                        'pengiriman.id',
                        DB::raw('COALESCE(
                            MAX(invoice_penagihan.amount_after_refraksi),
                            SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                        ) as omset_pengiriman')
                    )
                    ->groupBy('pengiriman.id')
                    ->get()
                    ->sum('omset_pengiriman');
                
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
        
        // Get klien names with cabang
        foreach ($topKlien as $klien) {
            $namaLengkap = $klien->nama;
            if (!empty($klien->cabang)) {
                $namaLengkap .= ' - ' . $klien->cabang . '';
            }
            $klienNames[] = $namaLengkap;
        }
        
        return response()->json([
            'klien_names' => $klienNames,
            'datasets' => $datasets
        ]);
    }
    
    /**
     * Get Omset per Supplier Chart Data (AJAX)
     */
    public function getOmsetPerSupplier(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');
        
        // Get top 5 supplier berdasarkan total omset tahun ini (with optional search filter & fallback logic)
        $topSupplierQuery = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
            ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
            ->select('suppliers.id as supplier_id', 'suppliers.nama', 'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman'))
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $tahun)
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->groupBy('pengiriman.id', 'suppliers.id', 'suppliers.nama');
        
        // Apply search filter if provided
        if (!empty($search)) {
            $topSupplierQuery->where(function($q) use ($search) {
                $q->where('suppliers.nama', 'like', '%' . $search . '%')
                  ->orWhere('suppliers.alamat', 'like', '%' . $search . '%');
            });
        }
        
        $topSupplierData = $topSupplierQuery->get();
        
        // Group by supplier and sum omset_pengiriman
        $topSupplier = $topSupplierData->groupBy('supplier_id')->map(function($items) {
            $first = $items->first();
            return (object)[
                'supplier_id' => $first->supplier_id,
                'nama' => $first->nama,
                'total' => $items->sum('omset_pengiriman')
            ];
        })->sortByDesc('total')->values();
        
        $supplierNames = [];
        $datasets = [];
        
        // Warna untuk setiap bulan
        $monthColors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899',
            '#06B6D4', '#F97316', '#14B8A6', '#F43F5E', '#8B5CF6', '#6366F1'
        ];
        
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Prepare datasets per month
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthData = [];
            
            foreach ($topSupplier as $supplier) {
                // Get omset untuk supplier ini di bulan ini (dengan fallback logic)
                $omsetBulan = DB::table('pengiriman')
                    ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                    ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->leftJoin('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->join('bahan_baku_supplier', 'pengiriman_details.bahan_baku_supplier_id', '=', 'bahan_baku_supplier.id')
                    ->where('bahan_baku_supplier.supplier_id', $supplier->supplier_id)
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereYear('pengiriman.tanggal_kirim', $tahun)
                    ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                    ->whereNull('pengiriman.deleted_at')
                    ->select(
                        'pengiriman.id',
                        DB::raw('COALESCE(
                            MAX(invoice_penagihan.amount_after_refraksi),
                            SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                        ) as omset_pengiriman')
                    )
                    ->groupBy('pengiriman.id')
                    ->get()
                    ->sum('omset_pengiriman');
                
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
        
        // Get supplier names
        foreach ($topSupplier as $supplier) {
            $supplierNames[] = $supplier->nama;
        }
        
        return response()->json([
            'supplier_names' => $supplierNames,
            'datasets' => $datasets
        ]);
    }
    
    /**
     * Get Omset per Bahan Baku Chart Data (AJAX)
     */
    public function getOmsetPerBahanBaku(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        $search = $request->get('search', '');
        
        // Query total omset per bahan baku dengan fallback logic - GROUP BY nama (bukan id)
        $topBahanBakuRaw = DB::table('pengiriman')
            ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
            ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
            ->join('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
            ->join('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
            ->select(
                'bahan_baku_klien.id as bahan_baku_id',
                'bahan_baku_klien.nama',
                'pengiriman.id as pengiriman_id',
                DB::raw('COALESCE(
                    MAX(invoice_penagihan.amount_after_refraksi),
                    SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                ) as omset_pengiriman')
            )
            ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
            ->whereYear('pengiriman.tanggal_kirim', $tahun)
            ->whereNull('pengiriman.deleted_at')
            ->whereNull('bahan_baku_klien.deleted_at')
            ->groupBy('pengiriman.id', 'bahan_baku_klien.id', 'bahan_baku_klien.nama');
        
        // Apply search filter if provided
        if (!empty($search)) {
            $topBahanBakuRaw->where(function($q) use ($search) {
                $q->where('bahan_baku_klien.nama', 'like', '%' . $search . '%')
                  ->orWhere('bahan_baku_klien.spesifikasi', 'like', '%' . $search . '%');
            });
        }
        
        $topBahanBakuData = $topBahanBakuRaw->get();
        
        // Group by nama (bukan id) dan sum omset
        $topBahanBakuGrouped = $topBahanBakuData->groupBy('nama')->map(function($items) {
            $bahanBakuIds = $items->pluck('bahan_baku_id')->unique()->implode(',');
            return (object)[
                'nama' => $items->first()->nama,
                'bahan_baku_ids' => $bahanBakuIds,
                'total' => $items->sum('omset_pengiriman')
            ];
        })->filter(function($item) {
            return $item->total > 0;
        })->sortByDesc('total');
        
        $topBahanBaku = $topBahanBakuGrouped;
        
        $bahanBakuNames = [];
        $datasets = [];
        
        // Warna untuk setiap bulan
        $monthColors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899',
            '#06B6D4', '#F97316', '#14B8A6', '#F43F5E', '#8B5CF6', '#6366F1'
        ];
        
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Prepare datasets per month
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthData = [];
            
            foreach ($topBahanBaku as $bahanBaku) {
                // Get all IDs yang memiliki nama yang sama
                $bahanBakuIds = explode(',', $bahanBaku->bahan_baku_ids);
                
                // Get omset untuk semua bahan baku dengan nama yang sama di bulan ini (dengan fallback logic)
                $omsetBulan = DB::table('pengiriman')
                    ->leftJoin('invoice_penagihan', 'pengiriman.id', '=', 'invoice_penagihan.pengiriman_id')
                    ->join('pengiriman_details', 'pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
                    ->join('order_details', 'pengiriman_details.purchase_order_bahan_baku_id', '=', 'order_details.id')
                    ->whereIn('order_details.bahan_baku_klien_id', $bahanBakuIds)
                    ->whereIn('pengiriman.status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
                    ->whereYear('pengiriman.tanggal_kirim', $tahun)
                    ->whereMonth('pengiriman.tanggal_kirim', $bulan)
                    ->whereNull('pengiriman.deleted_at')
                    ->select(
                        'pengiriman.id',
                        DB::raw('COALESCE(
                            MAX(invoice_penagihan.amount_after_refraksi),
                            SUM(pengiriman_details.qty_kirim * order_details.harga_jual)
                        ) as omset_pengiriman')
                    )
                    ->groupBy('pengiriman.id')
                    ->get()
                    ->sum('omset_pengiriman');
                
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
        
        // Get bahan baku names
        foreach ($topBahanBaku as $bahanBaku) {
            $bahanBakuNames[] = $bahanBaku->nama;
        }
        
        return response()->json([
            'bahan_baku_names' => $bahanBakuNames,
            'datasets' => $datasets
        ]);
    }
    
    /**
     * Download PDF Margin Minggu Ini
     */
    public function downloadMarginMingguIniPdf()
    {
        // Hitung range tanggal untuk minggu ini (sama seperti di index)
        $today = Carbon::now();
        $dayOfMonth = $today->day;
        $currentWeekOfMonth = 1;
        
        if ($dayOfMonth >= 1 && $dayOfMonth <= 7) {
            $currentWeekOfMonth = 1;
        } elseif ($dayOfMonth >= 8 && $dayOfMonth <= 14) {
            $currentWeekOfMonth = 2;
        } elseif ($dayOfMonth >= 15 && $dayOfMonth <= 21) {
            $currentWeekOfMonth = 3;
        } else {
            $currentWeekOfMonth = 4;
        }
        
        // Hitung range tanggal untuk minggu ini
        $startOfMonth = Carbon::now()->startOfMonth();
        
        if ($currentWeekOfMonth == 1) {
            $startOfWeek = $startOfMonth->copy();
        } else {
            $startOfWeek = $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);
        }
        
        if ($currentWeekOfMonth == 4) {
            $endOfWeek = $startOfMonth->copy()->endOfMonth();
        } else {
            $endOfWeek = $startOfWeek->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
        }

        // Query pengiriman untuk margin minggu ini
        $pengirimanMargin = Pengiriman::with([
            'pengirimanDetails.bahanBakuSupplier.supplier',
            'pengirimanDetails.bahanBakuSupplier',
            'pengirimanDetails.orderDetail.bahanBakuKlien',
            'order.klien',
            'order.winner.user',
            'purchasing',
            'approvalPembayaran',
            'invoicePenagihan'
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereBetween('tanggal_kirim', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
        ->get();

        // Process margin data
        $marginDataMingguIni = [];
        $totalMarginMingguIni = 0;
        $totalHargaBeliMingguIni = 0;
        $totalHargaJualMingguIni = 0;

        foreach ($pengirimanMargin as $p) {
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

                $bahanBaku = $detail->orderDetail?->bahanBakuKlien;
                $bahanBakuSupplier = $detail->bahanBakuSupplier;

                // Get PIC Marketing info
                $picMarketingUser = $p->order->winner->user ?? null;
                $namaPicMarketing = $picMarketingUser ? $picMarketingUser->nama : '-';

                $marginDataMingguIni[] = [
                    'pengiriman_id' => $p->id,
                    'tanggal_kirim' => $p->tanggal_kirim,
                    'status' => $p->status,
                    'pic_purchasing' => $p->purchasing->nama ?? '-',
                    'pic_marketing' => $namaPicMarketing,
                    'klien' => $p->order->klien->nama ?? '-',
                    'supplier' => $bahanBakuSupplier->supplier->nama ?? '-',
                    'bahan_baku' => $bahanBaku->nama ?? $bahanBakuSupplier->nama ?? '-',
                    'qty_kirim' => $detail->qty_kirim,
                    'harga_beli_per_kg' => $hargaBeliPerKg,
                    'total_harga_beli' => $totalHargaBeliItem,
                    'harga_jual_per_kg' => $hargaJualPerKg,
                    'total_harga_jual' => $totalHargaJualItem,
                    'margin' => $margin,
                    'margin_percentage' => $marginPercentage,
                ];

                $totalMarginMingguIni += $margin;
                $totalHargaBeliMingguIni += $totalHargaBeliItem;
                $totalHargaJualMingguIni += $totalHargaJualItem;
            }
        }

        // Sort by margin percentage descending
        usort($marginDataMingguIni, function($a, $b) {
            return $b['margin_percentage'] <=> $a['margin_percentage'];
        });

        // Hitung gross margin percentage minggu ini
        $grossMarginMingguIni = $totalHargaJualMingguIni > 0 ? ($totalMarginMingguIni / $totalHargaJualMingguIni) * 100 : 0;

        // ========== GROSS MARGIN BULAN INI ==========
        // Query pengiriman untuk bulan ini
        $pengirimanMarginBulanIni = Pengiriman::with([
            'pengirimanDetails.bahanBakuSupplier',
            'pengirimanDetails.orderDetail',
            'approvalPembayaran',
            'invoicePenagihan'
        ])
        ->whereIn('status', ['menunggu_fisik', 'menunggu_verifikasi', 'berhasil'])
        ->whereYear('tanggal_kirim', Carbon::now()->year)
        ->whereMonth('tanggal_kirim', Carbon::now()->month)
        ->get();
        
        // Process margin data untuk bulan ini
        $totalMarginBulanIni = 0;
        $totalHargaBeliBulanIni = 0;
        $totalHargaJualBulanIni = 0;
        
        foreach ($pengirimanMarginBulanIni as $p) {
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

                $totalMarginBulanIni += $margin;
                $totalHargaBeliBulanIni += $totalHargaBeliItem;
                $totalHargaJualBulanIni += $totalHargaJualItem;
            }
        }

        // Hitung gross margin bulan ini - Profit Margin: (margin / harga jual) * 100
        $grossMarginBulanIni = $totalHargaJualBulanIni > 0 ? ($totalMarginBulanIni / $totalHargaJualBulanIni) * 100 : 0;

        // Data untuk PDF
        $data = [
            'marginData' => $marginDataMingguIni,
            'totalMargin' => $totalMarginMingguIni,
            'totalHargaBeli' => $totalHargaBeliMingguIni,
            'totalHargaJual' => $totalHargaJualMingguIni,
            'grossMargin' => $grossMarginMingguIni,
            'grossMarginBulanIni' => $grossMarginBulanIni,
            'totalMarginBulanIni' => $totalMarginBulanIni,
            'currentMonth' => Carbon::now()->format('F Y'),
            'startDate' => $startOfWeek->format('d/m/Y'),
            'endDate' => $endOfWeek->format('d/m/Y'),
            'currentWeek' => $currentWeekOfMonth,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pages.dashboard.pdf.margin-minggu-ini', $data);
        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'Margin_Minggu_' . $currentWeekOfMonth . '_' . Carbon::now()->format('M_Y') . '.pdf';
        
        return $pdf->download($filename);
    }
}