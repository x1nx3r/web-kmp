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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
            $query->withCount('purchaseOrderBahanBakus');
            if (request('sort_items') == 'most') {
                $query->orderBy('purchase_order_bahan_bakus_count', 'desc');
            } elseif (request('sort_items') == 'least') {
                $query->orderBy('purchase_order_bahan_bakus_count', 'asc');
            }
        }, function($query) {
            $query->orderBy('created_at', 'desc');
        })
        ->paginate(5)
        ->withQueryString();

        // Ambil forecast berdasarkan status dengan eager loading
        $pendingForecasts = Forecast::with(['purchaseOrder.klien', 'purchasing'])
            ->pending()
            ->latest('created_at')
            ->get();

        $suksesForecasts = Forecast::with(['purchaseOrder.klien', 'purchasing'])
            ->sukses()
            ->latest('created_at')
            ->get();

        $gagalForecasts = Forecast::with(['purchaseOrder.klien', 'purchasing'])
            ->gagal()
            ->latest('created_at')
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
        Log::info("getBahanBakuSuppliers called with ID: {$purchaseOrderBahanBakuId}");
        
        try {
            // Debug: Check if tables exist and have data
            $supplierCount = DB::table('suppliers')->count();
            $bahanBakuSupplierCount = DB::table('bahan_baku_supplier')->count();
            Log::info("Table counts - suppliers: {$supplierCount}, bahan_baku_supplier: {$bahanBakuSupplierCount}");
            
            $purchaseOrderBahanBaku = PurchaseOrderBahanBaku::with('bahanBakuKlien')->find($purchaseOrderBahanBakuId);
            
            if (!$purchaseOrderBahanBaku) {
                Log::error("PurchaseOrderBahanBaku not found with ID: {$purchaseOrderBahanBakuId}");
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }
            
            Log::info("Found PurchaseOrderBahanBaku: " . json_encode($purchaseOrderBahanBaku));

            // Ambil semua bahan baku supplier yang tersedia, diurutkan berdasarkan nama supplier
            // Optimized query dengan proper join dan select
            $bahanBakuSuppliers = BahanBakuSupplier::select([
                    'bahan_baku_supplier.*',
                    'suppliers.nama as supplier_nama'
                ])
                ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                ->where('bahan_baku_supplier.stok', '>', 0) // Hanya ambil yang ada stoknya
                ->orderBy('bahan_baku_supplier.nama', 'asc')
                ->get();

            Log::info('Found ' . $bahanBakuSuppliers->count() . ' bahan baku suppliers with stock');
            
            // Jika tidak ada supplier dengan stok, ambil semua untuk debugging
            if ($bahanBakuSuppliers->count() === 0) {
                $allSuppliers = BahanBakuSupplier::select([
                        'bahan_baku_supplier.*',
                        'suppliers.nama as supplier_nama'
                    ])
                    ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                    ->orderBy('suppliers.nama', 'asc')
                    ->orderBy('bahan_baku_supplier.nama', 'asc')
                    ->get();
                    
                Log::info('Total suppliers in database: ' . $allSuppliers->count());
                // Log hanya 5 supplier pertama untuk debugging
                foreach ($allSuppliers->take(5) as $supplier) {
                    Log::info("Sample Supplier: {$supplier->nama} - {$supplier->supplier_nama} (Stock: {$supplier->stok})");
                }
                
                // Return all suppliers for debugging, even without stock
                $bahanBakuSuppliers = $allSuppliers;
            } else {
                // Log hanya 3 supplier pertama untuk debugging
                foreach ($bahanBakuSuppliers->take(3) as $supplier) {
                    Log::info("Sample Supplier: {$supplier->nama} - {$supplier->supplier_nama} (Stock: {$supplier->stok})");
                }
            }

            return response()->json([
                'purchase_order_bahan_baku' => $purchaseOrderBahanBaku,
                'bahan_baku_suppliers' => $bahanBakuSuppliers
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting bahan baku suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data supplier'
            ], 500);
        }
    }

    public function createForecast(Request $request)
    {
        // Log untuk debugging (dimatikan untuk performa)
        // Log::info('Request received:', $request->all());
        
        // Improved validation with better error messages
        try {
            $request->validate([
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'tanggal_forecast' => 'required|date',
                'hari_kirim_forecast' => 'required|string|max:50',
                'catatan' => 'nullable|string|max:1000',
                'details' => 'required|array|min:1',
                'details.*.purchase_order_bahan_baku_id' => 'required|exists:purchase_order_bahan_baku,id',
                'details.*.bahan_baku_supplier_id' => 'required|exists:bahan_baku_supplier,id',
                'details.*.qty_forecast' => 'required|numeric|min:0.01|max:9999999.99',
                'details.*.harga_satuan_forecast' => 'required|numeric|min:0.01|max:999999999.99',
                'details.*.catatan_detail' => 'nullable|string|max:500'
            ], [
                'purchase_order_id.required' => 'Purchase Order harus dipilih',
                'purchase_order_id.exists' => 'Purchase Order tidak valid',
                'tanggal_forecast.required' => 'Tanggal forecast harus diisi',
                'tanggal_forecast.date' => 'Format tanggal tidak valid',
                'hari_kirim_forecast.required' => 'Hari kirim forecast harus diisi',
                'details.required' => 'Detail forecast harus diisi',
                'details.min' => 'Minimal harus ada 1 item dalam forecast',
                'details.*.qty_forecast.required' => 'Qty forecast harus diisi',
                'details.*.qty_forecast.min' => 'Qty forecast minimal 0.01',
                'details.*.harga_satuan_forecast.required' => 'Harga satuan forecast harus diisi',
                'details.*.harga_satuan_forecast.min' => 'Harga satuan forecast minimal 0.01',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Data yang diinputkan tidak valid',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Set execution time limit
            set_time_limit(30); // Kurangi timeout lebih jauh
            
            DB::beginTransaction();
            
            // Log::info('Creating forecast for PO ID:', ['purchase_order_id' => $request->purchase_order_id]);

            // Generate nomor forecast otomatis (simplified untuk debugging)
            $year = date('Y');
            $month = date('m');
            
            // Simple counter using max ID untuk sementara
            $maxId = Forecast::max('id') ?? 0;
            $nextNumber = $maxId + 1;
            
            $noForecast = 'FC-' . $year . $month . '-' . sprintf('%04d', $nextNumber);

            // Hitung total dulu sebelum create forecast
            $totalQty = 0;
            $totalHarga = 0;
            
            foreach ($request->details as $detail) {
                $totalQty += (float) $detail['qty_forecast'];
                $totalHarga += (float) $detail['qty_forecast'] * (float) $detail['harga_satuan_forecast'];
            }

            // Buat forecast dengan total yang sudah dihitung
            $forecast = Forecast::create([
                'purchase_order_id' => $request->purchase_order_id,
                'purchasing_id' => Auth::id() ?? 1, // Gunakan user yang login atau default ke 1
                'no_forecast' => $noForecast,
                'tanggal_forecast' => $request->tanggal_forecast,
                'hari_kirim_forecast' => $request->hari_kirim_forecast,
                'status' => 'pending',
                'catatan' => $request->catatan,
                'total_qty_forecast' => $totalQty,
                'total_harga_forecast' => $totalHarga
            ]);
            
            // Log::info("Created forecast with ID: {$forecast->id} and number: {$forecast->no_forecast}");

            $forecastDetails = [];

            // Ambil semua PO Bahan Baku dan Supplier yang dibutuhkan sekaligus untuk efisiensi
            $poBahanBakuIds = collect($request->details)->pluck('purchase_order_bahan_baku_id')->unique();
            $supplierIds = collect($request->details)->pluck('bahan_baku_supplier_id')->unique();
            
            $poBahanBakus = PurchaseOrderBahanBaku::whereIn('id', $poBahanBakuIds)->get()->keyBy('id');
            $suppliers = BahanBakuSupplier::whereIn('id', $supplierIds)->get()->keyBy('id');

            // Get timestamp sekali saja
            $timestamp = now();

            // Validasi dan prepare data details
            foreach ($request->details as $index => $detail) {
                // Ambil data PO Bahan Baku dari collection
                $poBahanBaku = $poBahanBakus->get($detail['purchase_order_bahan_baku_id']);
                
                if (!$poBahanBaku) {
                    throw new \Exception("Purchase Order Bahan Baku dengan ID {$detail['purchase_order_bahan_baku_id']} tidak ditemukan");
                }

                // Validasi supplier dari collection
                $bahanBakuSupplier = $suppliers->get($detail['bahan_baku_supplier_id']);
                if (!$bahanBakuSupplier) {
                    throw new \Exception("Bahan Baku Supplier dengan ID {$detail['bahan_baku_supplier_id']} tidak ditemukan");
                }
                
                // Hitung total harga PO dan Supplier
                $qtyForecast = (float) $detail['qty_forecast'];
                $hargaSatuanForecast = (float) $detail['harga_satuan_forecast'];
                $hargaSatuanPO = (float) $poBahanBaku->harga_satuan;
                
                $totalHargaPO = $qtyForecast * $hargaSatuanPO;
                $totalHargaSupplier = $qtyForecast * $hargaSatuanForecast;
                
                // Prepare data untuk batch insert
                $forecastDetails[] = [
                    'forecast_id' => $forecast->id,
                    'purchase_order_bahan_baku_id' => $detail['purchase_order_bahan_baku_id'],
                    'bahan_baku_supplier_id' => $detail['bahan_baku_supplier_id'],
                    'qty_forecast' => $qtyForecast,
                    'harga_satuan_forecast' => $hargaSatuanForecast,
                    'total_harga_forecast' => $totalHargaSupplier,
                    'harga_satuan_po' => $hargaSatuanPO,
                    'total_harga_po' => $totalHargaPO,
                    'catatan_detail' => $detail['catatan_detail'] ?? null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];

                // Total sudah dihitung sebelumnya
                
                // Log comparison untuk debugging (dimatikan untuk performa)
                // if ($index === 0) {
                //     $selisih = $totalHargaSupplier - $totalHargaPO;
                //     $persentase = $totalHargaPO > 0 ? (($selisih / $totalHargaPO) * 100) : 0;
                //     Log::info("Forecast detail #{$index} - PO: Rp" . number_format($hargaSatuanPO, 0, ',', '.') .
                //               ", Supplier: Rp" . number_format($hargaSatuanForecast, 0, ',', '.') .
                //               ", Selisih: Rp" . number_format($selisih, 0, ',', '.') . " (" . number_format($persentase, 2) . "%)");
                // }
            }

            // Batch insert untuk performa yang lebih baik
            if (!empty($forecastDetails)) {
                DB::table('forecast_details')->insert($forecastDetails);
            }

            // Log::info("Forecast created successfully with " . count($forecastDetails) . " details");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Forecast berhasil dibuat',
                'forecast' => [
                    'id' => $forecast->id,
                    'no_forecast' => $forecast->no_forecast,
                    'total_qty_forecast' => $forecast->total_qty_forecast,
                    'total_harga_forecast' => $forecast->total_harga_forecast
                ]
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            Log::error('Database error creating forecast: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan database. Silakan coba lagi.'
            ], 500);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating forecast: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat forecast: ' . $e->getMessage()
            ], 500);
        }
    }
}