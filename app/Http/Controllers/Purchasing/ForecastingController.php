<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderBahanBaku;
use App\Models\Forecast;
use App\Models\ForecastDetail;
use App\Models\BahanBakuSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ForecastingController extends Controller
{
    public function index()
    {
        // Ambil PO yang statusnya siap atau proses dan belum selesai
        $purchaseOrders = PurchaseOrder::with([
            'klien',
            'purchaseOrderBahanBakus.bahanBakuKlien'
        ])
        ->whereIn('status', ['siap', 'proses'])
        ->when(request('search'), function($query) {
            $query->where(function($q) {
                $q->where('no_po', 'like', '%' . request('search') . '%')
                  ->orWhereHas('klien', function($klienQuery) {
                      $klienQuery->where('nama', 'like', '%' . request('search') . '%');
                  });
            });
        })
        ->when(request('status'), function($query) {
            $query->where('status', request('status'));
        })
        ->when(request('sort_amount'), function($query) {
            if (request('sort_amount') == 'highest') {
                $query->orderBy('total_amount', 'desc');
            } elseif (request('sort_amount') == 'lowest') {
                $query->orderBy('total_amount', 'asc');
            }
        })
        ->when(request('sort_items'), function($query) {
            // Untuk sort items, kita perlu menggunakan withCount
            $query->withCount('purchaseOrderBahanBakus');
            if (request('sort_items') == 'most') {
                $query->orderBy('purchase_order_bahan_bakus_count', 'desc');
            } elseif (request('sort_items') == 'least') {
                $query->orderBy('purchase_order_bahan_bakus_count', 'asc');
            }
        }, function($query) {
            // Default sorting jika tidak ada sort items
            $query->orderBy('created_at', 'desc');
        })
        ->paginate(5)
        ->withQueryString(); // Preserve query parameters in pagination links

        // Ambil forecast berdasarkan status
        $pendingForecasts = Forecast::with(['purchaseOrder.klien', 'purchasing'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();

        $suksesForecasts = Forecast::with(['purchaseOrder.klien', 'purchasing'])
            ->sukses()
            ->orderBy('created_at', 'desc')
            ->get();

        $gagalForecasts = Forecast::with(['purchaseOrder.klien', 'purchasing'])
            ->gagal()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.purchasing.forecast', compact(
            'purchaseOrders',
            'pendingForecasts',
            'suksesForecasts',
            'gagalForecasts'
        ));
    }

    public function getBahanBakuSuppliers($purchaseOrderBahanBakuId)
    {
        $purchaseOrderBahanBaku = PurchaseOrderBahanBaku::with('bahanBakuKlien')->find($purchaseOrderBahanBakuId);
        
        if (!$purchaseOrderBahanBaku) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Ambil supplier yang menyediakan bahan baku yang sama dengan yang ada di PO
        $bahanBakuSuppliers = BahanBakuSupplier::with(['supplier', 'riwayatHarga' => function($query) {
            $query->orderBy('tanggal', 'desc')->limit(1);
        }])
        ->where('nama', $purchaseOrderBahanBaku->bahanBakuKlien->nama)
        ->get();

        return response()->json([
            'purchase_order_bahan_baku' => $purchaseOrderBahanBaku,
            'bahan_baku_suppliers' => $bahanBakuSuppliers
        ]);
    }

    public function createForecast(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'tanggal_forecast' => 'required|date',
            'hari_kirim_forecast' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.purchase_order_bahan_baku_id' => 'required|exists:purchase_order_bahan_baku,id',
            'details.*.bahan_baku_supplier_id' => 'required|exists:bahan_baku_suppliers,id',
            'details.*.qty_forecast' => 'required|numeric|min:0.01',
            'details.*.harga_satuan_forecast' => 'required|numeric|min:0.01',
            'details.*.catatan_detail' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Buat forecast
            $forecast = Forecast::create([
                'purchase_order_id' => $request->purchase_order_id,
                'purchasing_id' => 1, // Sementara hardcode, nanti bisa diganti dengan auth()->id()
                'tanggal_forecast' => $request->tanggal_forecast,
                'hari_kirim_forecast' => $request->hari_kirim_forecast,
                'status' => 'pending',
                'catatan' => $request->catatan
            ]);

            $totalQty = 0;
            $totalHarga = 0;

            // Buat detail forecast
            foreach ($request->details as $detail) {
                $totalHargaDetail = $detail['qty_forecast'] * $detail['harga_satuan_forecast'];
                
                ForecastDetail::create([
                    'forecast_id' => $forecast->id,
                    'purchase_order_bahan_baku_id' => $detail['purchase_order_bahan_baku_id'],
                    'bahan_baku_supplier_id' => $detail['bahan_baku_supplier_id'],
                    'qty_forecast' => $detail['qty_forecast'],
                    'harga_satuan_forecast' => $detail['harga_satuan_forecast'],
                    'total_harga_forecast' => $totalHargaDetail,
                    'catatan_detail' => $detail['catatan_detail'] ?? null
                ]);

                $totalQty += $detail['qty_forecast'];
                $totalHarga += $totalHargaDetail;
            }

            // Update total di forecast
            $forecast->update([
                'total_qty_forecast' => $totalQty,
                'total_harga_forecast' => $totalHarga
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Forecast berhasil dibuat',
                'forecast' => $forecast
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat forecast: ' . $e->getMessage()
            ], 500);
        }
    }
}
