<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Forecast;
use App\Models\ForecastDetail;
use App\Models\BahanBakuSupplier;
use App\Models\RiwayatHargaBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ForecastingController extends Controller
{
    public function index()
    {
        $orders = Order::with(['klien', 'orderDetails.bahanBakuKlien'])
            ->whereIn('status', ['dikonfirmasi', 'diproses'])
            ->when(request('search'), function($query) {
                $query->where(function($q) {
                    $q->where('po_number', 'like', '%' . request('search') . '%')
                      ->orWhereHas('klien', function($klienQuery) {
                          $klienQuery->where('nama', 'like', '%' . request('search') . '%');
                      });
                });
            })
            ->when(request('status'), fn($query) => $query->where('status', request('status')))
            ->when(request('sort_amount'), fn($query) => $query->orderBy('total_amount', request('sort_amount') === 'highest' ? 'desc' : 'asc'))
            ->when(request('sort_items'), function($query) {
                $query->withCount('orderDetails');
                $query->orderBy('order_details_count', request('sort_items') === 'most' ? 'desc' : 'asc');
            }, fn($query) => $query->orderBy('created_at', 'desc'))
            ->paginate(20, ['*'], 'page_buat_forecasting')
            ->withQueryString();

        $pendingForecasts = $this->getForecastsByStatus('pending', 'page_pending');
        $suksesForecasts = $this->getForecastsByStatus('sukses', 'page_sukses');
        $gagalForecasts = $this->getForecastsByStatus('gagal', 'page_gagal');

        // Optimasi: Gunakan select distinct agar database tidak mengirim seluruh data ke PHP
        $pendingPurchasingOptions = \App\Models\User::whereIn('id', Forecast::pending()->select('purchasing_id')->distinct()->pluck('purchasing_id'))->orderBy('nama')->pluck('nama', 'id');
        $suksesPurchasingOptions = \App\Models\User::whereIn('id', Forecast::sukses()->select('purchasing_id')->distinct()->pluck('purchasing_id'))->orderBy('nama')->pluck('nama', 'id');
        $gagalPurchasingOptions = \App\Models\User::whereIn('id', Forecast::gagal()->select('purchasing_id')->distinct()->pluck('purchasing_id'))->orderBy('nama')->pluck('nama', 'id');

        return view('pages.purchasing.forecast', compact(
            'orders', 'pendingForecasts', 'suksesForecasts', 'gagalForecasts',
            'pendingPurchasingOptions', 'suksesPurchasingOptions', 'gagalPurchasingOptions'
        ));
    }

    public function getBahanBakuSuppliers($orderDetailId)
    {
        try {
            $orderDetail = OrderDetail::with(['bahanBakuKlien', 'order.klien'])->find($orderDetailId);
            if (!$orderDetail) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            $klienId = $orderDetail->order->klien_id ?? null;
            $orderBahanBakuNama = $orderDetail->bahanBakuKlien->nama ?? null;

            // Single structured query menggunakan Eloquent
            $query = BahanBakuSupplier::with([
                'supplier.picPurchasing', 
                'hargaPerKlien' => fn($q) => $klienId ? $q->where('klien_id', $klienId) : null
            ])->where('stok', '>', 0);

            // Terapkan filter pencarian nama jika ada
            if ($orderBahanBakuNama) {
                $this->applyBahanBakuNameFilter($query, $orderBahanBakuNama);
            }

            $bahanBakuSuppliers = $query->orderBy('nama', 'asc')->get();

            // Transformasi data agar sesuai dengan API contract asli
            $bahanBakuSuppliers = $bahanBakuSuppliers->map(function($item) {
                $item->supplier_nama = $item->supplier->nama ?? 'Supplier tidak diketahui';
                $item->pic_purchasing_id = $item->supplier->pic_purchasing_id ?? null;
                $item->pic_purchasing_nama = $item->supplier->picPurchasing->nama ?? null;
                
                $hargaKlien = $item->hargaPerKlien->first();
                if ($hargaKlien) {
                    $item->harga_per_satuan = $hargaKlien->harga_per_satuan;
                    $item->is_harga_khusus = true;
                } else {
                    $item->is_harga_khusus = false;
                }
                
                return $item;
            });

            // Jika kosong, fetch all untuk fallback (tanpa stok) - Sesuai behavior asli
            if ($bahanBakuSuppliers->isEmpty()) {
                Log::debug("No stock found, fetching all for fallback");
                $fallbackQuery = BahanBakuSupplier::with(['supplier.picPurchasing']);
                if ($orderBahanBakuNama) {
                    $this->applyBahanBakuNameFilter($fallbackQuery, $orderBahanBakuNama);
                }
                
                $bahanBakuSuppliers = $fallbackQuery->orderBy('nama', 'asc')->get()->map(function($item) {
                    $item->supplier_nama = $item->supplier->nama ?? 'Supplier tidak diketahui';
                    $item->pic_purchasing_id = $item->supplier->pic_purchasing_id ?? null;
                    $item->pic_purchasing_nama = $item->supplier->picPurchasing->nama ?? null;
                    return $item;
                });
            }

            return response()->json([
                'order_detail' => $orderDetail,
                'bahan_baku_suppliers' => $bahanBakuSuppliers
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting bahan baku suppliers: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data supplier: ' . $e->getMessage()], 500);
        }
    }

    public function createForecast(Request $request)
    {
        if (!$this->authorizeAction()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk membuat forecasting.'], 403);
        }

        try {
            $this->validateCreateRequest($request);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Data yang diinputkan tidak valid', 'errors' => $e->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $totalQty = collect($request->details)->sum('qty_forecast');
            $totalHarga = collect($request->details)->sum(fn($detail) => (float) $detail['qty_forecast'] * (float) $detail['harga_satuan_forecast']);

            $supplierIds = collect($request->details)->pluck('bahan_baku_supplier_id')->unique();
            $suppliers = BahanBakuSupplier::with('supplier')->whereIn('id', $supplierIds)->get()->keyBy('id');
            $orderDetails = OrderDetail::whereIn('id', collect($request->details)->pluck('purchase_order_bahan_baku_id'))->get()->keyBy('id');

            $purchaseOrder = Order::find($request->purchase_order_id);
            $klienId = $purchaseOrder->klien_id ?? null;

            // 1. Process Update Harga Supplier if requested
            if ($request->has('update_harga_supplier') && $request->update_harga_supplier['update_harga_supplier'] === true) {
                $this->processSupplierPriceUpdate($request->update_harga_supplier, $klienId, $suppliers);
            }

            // 2. Auto Sync Harga Klien
            if ($klienId) {
                $this->syncHargaKlien($klienId, $request->details);
            }

            // 3. Grouping Logic
            $actualSupplierIds = $suppliers->whereNotNull('supplier_id')->pluck('supplier_id')->unique()->sort()->values();
            $existingForecast = $this->findGroupableForecast($request->purchase_order_id, $request->tanggal_forecast, $request->hari_kirim_forecast, $actualSupplierIds);

            $timestamp = now();
            
            if ($existingForecast) {
                $forecast = $existingForecast;
                $newTotalQty = $forecast->total_qty_forecast + $totalQty;
                $newTotalHarga = $forecast->total_harga_forecast + $totalHarga;
                $newCatatan = empty($forecast->catatan) ? $request->catatan : (empty($request->catatan) ? $forecast->catatan : $forecast->catatan . "\n\n" . $request->catatan);
                
                DB::table('forecasts')->where('id', $forecast->id)->update([
                    'total_qty_forecast' => $newTotalQty,
                    'total_harga_forecast' => $newTotalHarga,
                    'catatan' => $newCatatan,
                    'updated_at' => $timestamp
                ]);
                $forecast->total_qty_forecast = $newTotalQty;
                $forecast->total_harga_forecast = $newTotalHarga;
            } else {
                $firstSupplier = $suppliers->get($request->details[0]['bahan_baku_supplier_id']);
                $purchasingId = $firstSupplier->supplier->pic_purchasing_id ?? Auth::id();
                
                $forecast = Forecast::create([
                    'purchase_order_id' => $request->purchase_order_id,
                    'purchasing_id' => $purchasingId,
                    'no_forecast' => $this->generateNoForecast(),
                    'tanggal_forecast' => $request->tanggal_forecast,
                    'hari_kirim_forecast' => $request->hari_kirim_forecast,
                    'status' => 'pending',
                    'catatan' => $request->catatan,
                    'total_qty_forecast' => $totalQty,
                    'total_harga_forecast' => $totalHarga
                ]);
            }

            // 4. Insert Details
            $forecastDetails = [];
            foreach ($request->details as $detail) {
                $orderDetail = $orderDetails->get($detail['purchase_order_bahan_baku_id']);
                if (!$orderDetail || !$suppliers->has($detail['bahan_baku_supplier_id'])) {
                    throw new \Exception("Data Referensi (Order Detail / Supplier) tidak valid.");
                }

                $qty = (float) $detail['qty_forecast'];
                $hargaSatuan = (float) $detail['harga_satuan_forecast'];
                $hargaPO = (float) $orderDetail->harga_jual;

                $forecastDetails[] = [
                    'forecast_id' => $forecast->id,
                    'purchase_order_bahan_baku_id' => $detail['purchase_order_bahan_baku_id'],
                    'bahan_baku_supplier_id' => $detail['bahan_baku_supplier_id'],
                    'qty_forecast' => $qty,
                    'harga_satuan_forecast' => $hargaSatuan,
                    'total_harga_forecast' => $qty * $hargaSatuan,
                    'harga_satuan_po' => $hargaPO,
                    'total_harga_po' => $qty * $hargaPO,
                    'catatan_detail' => $detail['catatan_detail'] ?? null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }

            if (!empty($forecastDetails)) {
                DB::table('forecast_details')->insert($forecastDetails);
            }

            DB::commit();
            Log::info("Forecast Transaction Committed: {$forecast->no_forecast}");

            return response()->json([
                'success' => true,
                'message' => $existingForecast ? "Detail berhasil ditambahkan ke forecast (No: {$forecast->no_forecast})" : 'Forecast berhasil dibuat',
                'forecast' => [
                    'id' => $forecast->id,
                    'no_forecast' => $forecast->no_forecast,
                    'total_qty_forecast' => $forecast->total_qty_forecast,
                    'total_harga_forecast' => $forecast->total_harga_forecast,
                    'is_grouped' => (bool)$existingForecast
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating forecast: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'], 500);
        }
    }

    public function kirimForecast($id)
    {
        $forecast = Forecast::select('id', 'purchasing_id')->find($id);
        if (!$forecast) return response()->json(['success' => false, 'message' => 'Forecast tidak ditemukan'], 404);
        
        if (!$this->authorizeAction($forecast)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        
        try {
            DB::beginTransaction();
            $forecast = Forecast::with(['forecastDetails'])
                ->lockForUpdate()
                ->find($id);
                
            if ($forecast->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Hanya forecast pending yang dapat dikirim'], 400);
            }

            $timestamp = now();
            
            // Raw insert to Pengiriman based on original behavior mapping
            $pengirimanId = DB::table('pengiriman')->insertGetId([
                'purchase_order_id' => $forecast->purchase_order_id,
                'purchasing_id' => $forecast->purchasing_id,
                'forecast_id' => $forecast->id,
                'no_pengiriman' => null,
                'tanggal_kirim' => null,
                'hari_kirim' => null,
                'total_qty_kirim' => 0,
                'total_harga_kirim' => 0,
                'status' => 'pending',
                'catatan' => "PENGIRIMAN: Forecast {$forecast->no_forecast} | " . $timestamp->format('d/m/Y H:i'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]);
            
            $pengirimanDetails = $forecast->forecastDetails->map(fn($detail) => [
                'pengiriman_id' => $pengirimanId,
                'purchase_order_bahan_baku_id' => $detail->purchase_order_bahan_baku_id,
                'bahan_baku_supplier_id' => $detail->bahan_baku_supplier_id,
                'qty_kirim' => 0,
                'harga_satuan' => 0,
                'total_harga' => 0,
                'catatan_detail' => $detail->catatan_detail,
                'created_at' => $timestamp->format('Y-m-d H:i:s'),
                'updated_at' => $timestamp->format('Y-m-d H:i:s')
            ])->toArray();

            if (!empty($pengirimanDetails)) {
                DB::table('pengiriman_details')->insert($pengirimanDetails);
            }

            DB::table('forecasts')->where('id', $forecast->id)->update(['status' => 'sukses', 'updated_at' => $timestamp]);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Forecast {$forecast->no_forecast} berhasil dikirim",
                'data' => ['forecast_id' => $forecast->id, 'pengiriman_id' => $pengirimanId, 'no_forecast' => $forecast->no_forecast, 'no_pengiriman' => null]
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            if ($e->getCode() == 1205 || str_contains($e->getMessage(), 'Lock wait timeout')) {
                return response()->json(['success' => false, 'message' => 'Sistem sedang sibuk (Timeout). Silakan coba lagi.'], 500);
            }
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan database.'], 500);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Gagal mengirim forecast.'], 500);
        }
    }

    public function getForecastDetail($id)
    {
        try {
            $forecast = Forecast::with([
                'order.klien', 'purchasing',
                'forecastDetails.purchaseOrderBahanBaku.bahanBakuKlien',
                'forecastDetails.bahanBakuSupplier.supplier'
            ])->find($id);
            
            if (!$forecast) return response()->json(['success' => false, 'message' => 'Forecast tidak ditemukan'], 404);

            $forecastData = [
                'id' => $forecast->id,
                'no_forecast' => $forecast->no_forecast ?: 'N/A',
                'klien' => $forecast->order->klien->nama ?? 'N/A',
                'po_number' => $forecast->order->po_number ?? 'N/A',
                'pic_purchasing' => $forecast->purchasing->nama ?? 'N/A',
                'pic_purchasing_id' => $forecast->purchasing_id,
                'tanggal_forecast' => $forecast->tanggal_forecast ? \Carbon\Carbon::parse($forecast->tanggal_forecast)->format('d M Y') : 'N/A',
                'hari_kirim' => $forecast->hari_kirim_forecast ?: 'N/A',
                'total_qty' => number_format((float)($forecast->total_qty_forecast ?? 0), 0, ',', '.'),
                'total_harga' => 'Rp ' . number_format((float)($forecast->total_harga_forecast ?? 0), 0, ',', '.'),
                'status' => $forecast->status ?: 'N/A',
                'catatan' => $forecast->catatan,
                'created_at' => $forecast->created_at?->format('d M Y, H:i') ?: '',
                'updated_at' => $forecast->updated_at?->format('d M Y, H:i') ?: '',
                'details' => $forecast->forecastDetails->map(fn($detail) => [
                    'id' => $detail->id,
                    'bahan_baku' => $detail->purchaseOrderBahanBaku->bahanBakuKlien->nama ?? 'N/A',
                    'supplier' => $detail->bahanBakuSupplier->supplier->nama ?? 'N/A',
                    'qty' => number_format((float)($detail->qty_forecast ?? 0), 0, ',', '.'),
                    'harga_satuan' => 'Rp ' . number_format((float)($detail->harga_satuan_forecast ?? 0), 0, ',', '.'),
                    'total_harga' => 'Rp ' . number_format((float)(($detail->qty_forecast ?? 0) * ($detail->harga_satuan_forecast ?? 0)), 0, ',', '.'),
                    'catatan_detail' => $detail->catatan_detail
                ])
            ];

            return response()->json(['success' => true, 'message' => 'Detail forecast berhasil dimuat', 'forecast' => $forecastData]);

        } catch (\Exception $e) {
            Log::error('Error getForecastDetail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail forecast.'], 500);
        }
    }

    public function batalkanForecast(Request $request, $id)
    {
        $forecast = Forecast::select('id', 'purchasing_id')->find($id);
        if (!$forecast) return response()->json(['success' => false, 'message' => 'Forecast tidak ditemukan'], 404);
        
        if (!$this->authorizeAction($forecast)) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        
        try {
            $request->validate(['alasan_batal' => 'required|string|min:10|max:500']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid', 'errors' => $e->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $forecast = Forecast::with(['forecastDetails'])->lockForUpdate()->find($id);
            
            if ($forecast->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Hanya forecast pending yang dapat dibatalkan'], 400);
            }

            $timestamp = now();
            $noPengiriman = 'BATAL-' . $forecast->id . '-' . $timestamp->format('ymdHis');
            $catatan = $request->alasan_batal . ' | Dibatalkan pada: ' . $timestamp->format('d M Y H:i');
            
            $pengirimanId = DB::table('pengiriman')->insertGetId([
                'purchase_order_id' => $forecast->purchase_order_id,
                'purchasing_id' => $forecast->purchasing_id,
                'forecast_id' => $forecast->id,
                'no_pengiriman' => $noPengiriman,
                'tanggal_kirim' => $forecast->tanggal_forecast,
                'hari_kirim' => $forecast->hari_kirim_forecast,
                'total_qty_kirim' => null,
                'total_harga_kirim' => null,
                'status' => 'gagal',
                'catatan' => $catatan,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]);
            
            $pengirimanDetails = $forecast->forecastDetails->map(fn($detail) => [
                'pengiriman_id' => $pengirimanId,
                'purchase_order_bahan_baku_id' => $detail->purchase_order_bahan_baku_id,
                'bahan_baku_supplier_id' => $detail->bahan_baku_supplier_id,
                'qty_kirim' => null,
                'harga_satuan' => null,
                'total_harga' => null,
                'catatan_detail' => "PEMBATALAN - Qty Forecast: {$detail->qty_forecast}, Harga Forecast: Rp " . number_format($detail->harga_satuan_forecast, 0, ',', '.') . ($detail->catatan_detail ? " | {$detail->catatan_detail}" : ""),
                'created_at' => $timestamp->format('Y-m-d H:i:s'),
                'updated_at' => $timestamp->format('Y-m-d H:i:s')
            ])->toArray();

            if (!empty($pengirimanDetails)) DB::table('pengiriman_details')->insert($pengirimanDetails);
            
            DB::table('forecasts')->where('id', $forecast->id)->update(['status' => 'gagal', 'updated_at' => $timestamp]);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Forecast {$forecast->no_forecast} berhasil dibatalkan",
                'data' => ['forecast_id' => $forecast->id, 'pengiriman_id' => $pengirimanId, 'no_forecast' => $forecast->no_forecast, 'no_pengiriman' => $noPengiriman]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Gagal membatalkan forecast.'], 500);
        }
    }

    public function exportPending(Request $request)
    {
        try {
            $fileName = 'forecast_pending_' . now()->format('Y-m-d_His') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ForecastPendingExport(
                    $request->input('tanggal_mulai_pending'),
                    $request->input('tanggal_akhir_pending'),
                    $request->input('filter_purchasing_pending'),
                    $request->input('search_pending')
                ),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Error exporting pending forecasts: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data forecast pending.');
        }
    }

    public function deleteForecast(Request $request, $id)
    {
        $forecast = Forecast::find($id);
        if (!$forecast) return response()->json(['success' => false, 'message' => 'Forecast tidak ditemukan'], 404);
        
        if (!$this->authorizeAction($forecast)) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        
        if ($forecast->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Hanya forecast pending yang dapat dihapus'], 400);
        }
        
        try {
            $request->validate(['alasan_hapus' => 'required|string|min:10|max:500']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid', 'errors' => $e->errors()], 422);
        }
        
        try {
            DB::beginTransaction();
            $forecast->forecastDetails()->delete();
            $forecast->delete();
            DB::commit();
            
            Log::info("Forecast {$forecast->no_forecast} dihapus oleh " . Auth::user()->nama . " alasan: {$request->alasan_hapus}");
            return response()->json(['success' => true, 'message' => 'Forecast berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Kesalahan saat menghapus forecast.'], 500);
        }
    }

    public function deleteForecastDetail(Request $request, $forecastId, $detailId)
    {
        $forecast = Forecast::select('id', 'purchasing_id', 'status', 'no_forecast', 'total_qty_forecast', 'total_harga_forecast')->find($forecastId);
        if (!$forecast) return response()->json(['success' => false, 'message' => 'Forecast tidak ditemukan'], 404);
        
        if (!$this->authorizeAction($forecast)) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        if ($forecast->status !== 'pending') return response()->json(['success' => false, 'message' => 'Hanya status pending.'], 400);

        $totalDetails = ForecastDetail::where('forecast_id', $forecastId)->count();
        if ($totalDetails <= 1) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus detail terakhir.'], 400);
        }

        $detail = ForecastDetail::where('id', $detailId)->where('forecast_id', $forecastId)->first();
        if (!$detail) return response()->json(['success' => false, 'message' => 'Detail forecast tidak ditemukan'], 404);

        try {
            DB::beginTransaction();

            $newTotalQty = max(0, (float) $forecast->total_qty_forecast - (float) $detail->qty_forecast);
            $newTotalHarga = max(0, (float) $forecast->total_harga_forecast - (float) $detail->total_harga_forecast);

            $detail->delete();
            DB::table('forecasts')->where('id', $forecastId)->update([
                'total_qty_forecast' => $newTotalQty,
                'total_harga_forecast' => $newTotalHarga,
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Detail berhasil dihapus',
                'new_total_qty' => number_format($newTotalQty, 0, ',', '.'),
                'new_total_harga' => 'Rp ' . number_format($newTotalHarga, 0, ',', '.'),
                'remaining_count' => $totalDetails - 1,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }

    /* ======================================================================
     * PRIVATE METHODS (Ekstraksi FormRequest, Policy, & Service Class logic)
     * ====================================================================== */

    private function getForecastsByStatus(string $status, string $pageName)
    {
        return Forecast::with(['order.klien', 'purchasing', 'forecastDetails.bahanBakuSupplier.supplier', 'forecastDetails.purchaseOrderBahanBaku.bahanBakuKlien'])
            ->where('status', $status)
            ->when(request("search_{$status}"), function($query) use ($status) {
                $term = request("search_{$status}");
                $query->where(function($q) use ($term) {
                    $q->whereHas('order', function($subQ) use ($term) {
                            $subQ->where('po_number', 'like', "%{$term}%")
                                ->orWhereHas('klien', fn($k) => $k->where('nama', 'like', "%{$term}%"));
                        })
                        ->orWhereHas('purchasing', fn($u) => $u->where('nama', 'like', "%{$term}%"))
                        ->orWhereHas('forecastDetails.purchaseOrderBahanBaku.bahanBakuKlien', function($bbQ) use ($term) {
                            $bbQ->where('nama', 'like', "%{$term}%");
                        });
                });
            })
            ->when(request("tanggal_mulai_{$status}") && request("tanggal_akhir_{$status}"), function($query) use ($status) {
                $query->whereBetween('tanggal_forecast', [request("tanggal_mulai_{$status}"), request("tanggal_akhir_{$status}")]);
            })
            ->when(request("tanggal_mulai_{$status}") && !request("tanggal_akhir_{$status}"), function($query) use ($status) {
                $query->whereDate('tanggal_forecast', '>=', request("tanggal_mulai_{$status}"));
            })
            ->when(!request("tanggal_mulai_{$status}") && request("tanggal_akhir_{$status}"), function($query) use ($status) {
                $query->whereDate('tanggal_forecast', '<=', request("tanggal_akhir_{$status}"));
            })
            ->when(request("filter_purchasing_{$status}"), fn($query) => $query->where('purchasing_id', request("filter_purchasing_{$status}")))
            ->latest('created_at')
            ->paginate($status === 'pending' ? 50 : 10, ['*'], $pageName)
            ->withQueryString();
    }

    private function authorizeAction($forecast = null): bool
    {
        $user = Auth::user();
        if (in_array($user->role, ['direktur', 'manager_purchasing'])) return true;
        if ($forecast && $user->id == $forecast->purchasing_id) return true;
        return $user->role === 'staff_purchasing' && !$forecast; // Staff bisa create, tidak bisa eksekusi milik orang lain
    }

    private function applyBahanBakuNameFilter($query, string $orderBahanBakuNama): void
    {
        $cleanOrderName = strtolower(trim($orderBahanBakuNama));
        $keywords = array_filter(explode(' ', $cleanOrderName), fn($k) => strlen($k) >= 3);
        
        $query->where(function($subQuery) use ($cleanOrderName, $keywords) {
            $subQuery->whereRaw('LOWER(nama) = ?', [$cleanOrderName])
                     ->orWhereRaw('LOWER(nama) LIKE ?', ['%' . $cleanOrderName . '%']);
            foreach ($keywords as $keyword) {
                $subQuery->orWhereRaw('LOWER(nama) LIKE ?', ['%' . trim($keyword) . '%']);
            }
        });
    }

    private function validateCreateRequest(Request $request): void
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:orders,id',
            'tanggal_forecast' => 'required|date',
            'hari_kirim_forecast' => 'required|string|max:50',
            'details' => 'required|array|min:1',
            'details.*.qty_forecast' => 'required|numeric|min:0.01',
            'details.*.harga_satuan_forecast' => 'required|numeric|min:0.01',
            'update_harga_supplier' => 'sometimes|array',
            'update_harga_supplier.harga_baru' => 'required_with:update_harga_supplier|numeric|min:0.01',
        ]);
    }

    private function generateNoForecast(): string
    {
        $prefix = 'FC-' . date('Ym') . '-';
        $latest = Forecast::withTrashed()->where('no_forecast', 'like', $prefix . '%')->orderByDesc('no_forecast')->first();
        $nextNumber = $latest ? ((int) substr($latest->no_forecast, -4)) + 1 : 1;
        return $prefix . sprintf('%04d', $nextNumber);
    }

    private function processSupplierPriceUpdate(array $updateData, ?int $klienId, &$suppliers): void
    {
        $supplierId = $updateData['bahan_baku_supplier_id'];
        $supplierToUpdate = BahanBakuSupplier::find($supplierId);
        
        if (!$supplierToUpdate) return;

        if ($klienId) {
            \App\Models\BahanBakuSupplierKlien::updateOrCreate(
                ['bahan_baku_supplier_id' => $supplierId, 'klien_id' => $klienId],
                ['harga_per_satuan' => $updateData['harga_baru']]
            );
            $msg = 'Perubahan harga khusus untuk klien melalui forecast';
        } else {
            $supplierToUpdate->update(['harga_per_satuan' => $updateData['harga_baru']]);
            $msg = 'Perubahan harga global melalui forecast';
        }

        RiwayatHargaBahanBaku::catatPerubahanHarga(
            $supplierId, $updateData['harga_lama'], $updateData['harga_baru'], $msg, Auth::id(), now(), $klienId
        );

        if (isset($suppliers[$supplierId])) {
            $suppliers[$supplierId]->harga_per_satuan = $updateData['harga_baru'];
        }
    }

    private function syncHargaKlien(int $klienId, array $details): void
    {
        foreach ($details as $detail) {
            $bbSupplierId = $detail['bahan_baku_supplier_id'];
            $hargaForecast = $detail['harga_satuan_forecast'];
            
            $hargaKlien = \App\Models\BahanBakuSupplierKlien::where('bahan_baku_supplier_id', $bbSupplierId)
                            ->where('klien_id', $klienId)->first();
            
            if ($hargaKlien && $hargaKlien->harga_per_satuan != $hargaForecast) {
                $hargaLama = $hargaKlien->harga_per_satuan;
                $hargaKlien->update(['harga_per_satuan' => $hargaForecast]);
                RiwayatHargaBahanBaku::catatPerubahanHarga($bbSupplierId, $hargaLama, $hargaForecast, 'Update harga khusus klien', Auth::id(), now(), $klienId);
            } elseif (!$hargaKlien) {
                \App\Models\BahanBakuSupplierKlien::create([
                    'bahan_baku_supplier_id' => $bbSupplierId, 'klien_id' => $klienId, 'harga_per_satuan' => $hargaForecast
                ]);
                RiwayatHargaBahanBaku::catatPerubahanHarga($bbSupplierId, null, $hargaForecast, 'Harga awal khusus untuk klien', Auth::id(), now(), $klienId);
            }
        }
    }

    private function findGroupableForecast($poId, $tglForecast, $hariKirim, $actualSupplierIds)
    {
        $potentialForecasts = Forecast::where('purchase_order_id', $poId)
            ->where('tanggal_forecast', $tglForecast)
            ->where('hari_kirim_forecast', $hariKirim)
            ->where('status', 'pending')
            ->with(['forecastDetails'])
            ->get();
            
        foreach ($potentialForecasts as $pot) {
            $existingSuppliersData = BahanBakuSupplier::with('supplier')
                ->whereIn('id', $pot->forecastDetails->pluck('bahan_baku_supplier_id'))
                ->get();
                
            $existingActualIds = $existingSuppliersData->whereNotNull('supplier_id')->pluck('supplier_id')->unique()->sort()->values();
            
            if ($existingActualIds->count() === $actualSupplierIds->count() && $existingActualIds->diff($actualSupplierIds)->isEmpty()) {
                return $pot;
            }
        }
        return null;
    }
    public function exportGagal(Request $request)
    {
        try {
            $fileName = 'forecast_gagal_' . now()->format('Y-m-d_His') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ForecastGagalExport(
                    $request->input('tanggal_mulai_gagal'),
                    $request->input('tanggal_akhir_gagal'),
                    $request->input('filter_purchasing_gagal'),
                    $request->input('search_gagal')
                ),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Error exporting gagal forecasts: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data forecast gagal.');
        }
    }
    public function exportSukses(Request $request)
    {
        try {
            $fileName = 'forecast_sukses_' . now()->format('Y-m-d_His') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ForecastSuksesExport(
                    $request->input('tanggal_mulai_sukses'),
                    $request->input('tanggal_akhir_sukses'),
                    $request->input('filter_purchasing_sukses'),
                    $request->input('search_sukses')
                ),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Error exporting sukses forecasts: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data forecast sukses.');
        }
    }
}