<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderBahanBaku;
use App\Models\Forecast;
use App\Models\ForecastDetail;
use App\Models\BahanBakuSupplier;
use App\Models\Pengiriman;
use App\Models\PengirimanDetail;
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
        ->paginate(5, ['*'], 'page_buat_forecasting')
        ->withQueryString();

        // Ambil forecast berdasarkan status dengan eager loading dan pagination
        $pendingForecasts = Forecast::with(['purchaseOrder.klien', 'purchasing'])
            ->pending()
            ->when(request('search_pending'), function($query) {
                $searchTerm = request('search_pending');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('no_forecast', 'like', "%{$searchTerm}%")
                      ->orWhereHas('purchaseOrder', function($subQ) use ($searchTerm) {
                          $subQ->where('no_po', 'like', "%{$searchTerm}%")
                               ->orWhereHas('klien', function($klienQ) use ($searchTerm) {
                                   $klienQ->where('nama', 'like', "%{$searchTerm}%");
                               });
                      })
                      ->orWhereHas('purchasing', function($userQ) use ($searchTerm) {
                          $userQ->where('nama', 'like', "%{$searchTerm}%");
                      });
                });
            })
            ->when(request('date_range'), function($query) {
                $query->whereDate('tanggal_forecast', request('date_range'));
            })
            ->when(request('sort_hari_kirim'), function($query) {
                $query->whereRaw('LOWER(hari_kirim_forecast) LIKE ?', ['%' . strtolower(request('sort_hari_kirim')) . '%']);
            })
            ->when(request('sort_amount_pending'), function($query) {
                if (request('sort_amount_pending') == 'highest') {
                    $query->orderBy('total_harga_forecast', 'desc');
                } elseif (request('sort_amount_pending') == 'lowest') {
                    $query->orderBy('total_harga_forecast', 'asc');
                }
            })
            ->when(request('sort_qty_pending'), function($query) {
                if (request('sort_qty_pending') == 'highest') {
                    $query->orderBy('total_qty_forecast', 'desc');
                } elseif (request('sort_qty_pending') == 'lowest') {
                    $query->orderBy('total_qty_forecast', 'asc');
                }
            })
            ->when(request('sort_date_pending'), function($query) {
                if (request('sort_date_pending') == 'newest') {
                    $query->orderBy('created_at', 'desc');
                } elseif (request('sort_date_pending') == 'oldest') {
                    $query->orderBy('created_at', 'asc');
                }
            }, function($query) {
                $query->latest('created_at');
            })
            ->paginate(10, ['*'], 'page_pending')
            ->withQueryString();

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
            // Optimized query dengan proper join dan select, termasuk pic_purchasing
            try {
                $bahanBakuSuppliers = BahanBakuSupplier::select([
                        'bahan_baku_supplier.*',
                        'suppliers.nama as supplier_nama',
                        'suppliers.pic_purchasing_id',
                        'users.nama as pic_purchasing_nama'
                    ])
                    ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                    ->leftJoin('users', 'suppliers.pic_purchasing_id', '=', 'users.id')
                    ->where('bahan_baku_supplier.stok', '>', 0) // Hanya ambil yang ada stoknya
                    ->orderBy('bahan_baku_supplier.nama', 'asc')
                    ->get();
                    
                Log::info('Main query executed successfully, found: ' . $bahanBakuSuppliers->count() . ' records');
            } catch (\Exception $queryError) {
                Log::error('Error in main query: ' . $queryError->getMessage());
                Log::error('Query error details: ' . $queryError->getTraceAsString());
                
                // Fallback to simpler query with manual supplier loading
                $bahanBakuSuppliers = BahanBakuSupplier::with('supplier.picPurchasing')
                    ->where('stok', '>', 0)
                    ->orderBy('nama', 'asc')
                    ->get();                    // Transform the data to include supplier_nama field
                    $bahanBakuSuppliers = $bahanBakuSuppliers->map(function($item) {
                        $item->supplier_nama = $item->supplier ? $item->supplier->nama : 'Supplier tidak diketahui';
                        $item->pic_purchasing_id = $item->supplier ? $item->supplier->pic_purchasing_id : null;
                        $item->pic_purchasing_nama = $item->supplier && $item->supplier->picPurchasing ? $item->supplier->picPurchasing->nama : null;
                        return $item;
                    });
                
                Log::info('Fallback query executed, found: ' . $bahanBakuSuppliers->count() . ' records');
            }

            Log::info('Found ' . $bahanBakuSuppliers->count() . ' bahan baku suppliers with stock');
            
            // Debug: Check sample data to see what's happening with pic_purchasing
            if ($bahanBakuSuppliers->count() > 0) {
                $firstSupplier = $bahanBakuSuppliers->first();
                Log::info('Sample supplier data:', [
                    'id' => $firstSupplier->id ?? 'missing',
                    'nama' => $firstSupplier->nama ?? 'missing',
                    'supplier_nama' => $firstSupplier->supplier_nama ?? 'missing',
                    'pic_purchasing_id' => $firstSupplier->pic_purchasing_id ?? 'missing',
                    'pic_purchasing_nama' => $firstSupplier->pic_purchasing_nama ?? 'missing',
                    'stok' => $firstSupplier->stok ?? 'missing',
                    'harga_per_satuan' => $firstSupplier->harga_per_satuan ?? 'missing'
                ]);
                
                // Additional debug: Check if users table has user with that ID
                if ($firstSupplier->pic_purchasing_id) {
                    $user = \App\Models\User::find($firstSupplier->pic_purchasing_id);
                    Log::info('User lookup for ID ' . $firstSupplier->pic_purchasing_id . ':', [
                        'found' => $user ? 'YES' : 'NO',
                        'name' => $user ? $user->name : 'N/A'
                    ]);
                }
            }
            
            // Jika tidak ada supplier dengan stok, ambil semua untuk debugging
            if ($bahanBakuSuppliers->count() === 0) {
                try {
                    $allSuppliers = BahanBakuSupplier::select([
                            'bahan_baku_supplier.*',
                            'suppliers.nama as supplier_nama',
                            'suppliers.pic_purchasing_id',
                            'users.nama as pic_purchasing_nama'
                        ])
                        ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                        ->leftJoin('users', 'suppliers.pic_purchasing_id', '=', 'users.id')
                        ->orderBy('suppliers.nama', 'asc')
                        ->orderBy('bahan_baku_supplier.nama', 'asc')
                        ->get();
                        
                    Log::info('Fallback JOIN query executed successfully, found: ' . $allSuppliers->count() . ' records');
                } catch (\Exception $fallbackError) {
                    Log::error('Error in fallback query: ' . $fallbackError->getMessage());
                    Log::error('Fallback error details: ' . $fallbackError->getTraceAsString());
                    
                    // Use simple query as last resort with manual supplier loading
                    $allSuppliers = BahanBakuSupplier::with('supplier.picPurchasing')
                        ->orderBy('nama', 'asc')
                        ->get();
                        
                    // Transform the data to include supplier_nama field
                    $allSuppliers = $allSuppliers->map(function($item) {
                        $item->supplier_nama = $item->supplier ? $item->supplier->nama : 'Supplier tidak diketahui';
                        $item->pic_purchasing_id = $item->supplier ? $item->supplier->pic_purchasing_id : null;
                        $item->pic_purchasing_nama = $item->supplier && $item->supplier->picPurchasing ? $item->supplier->picPurchasing->nama : null;
                        return $item;
                    });
                    
                    Log::info('Final fallback query executed, found: ' . $allSuppliers->count() . ' records');
                }
                    
                Log::info('Total suppliers in database: ' . $allSuppliers->count());
                
                // Debug: Log sample fallback data
                if ($allSuppliers->count() > 0) {
                    $firstFallback = $allSuppliers->first();
                    Log::info('Sample fallback supplier data:', [
                        'id' => $firstFallback->id ?? 'missing',
                        'nama' => $firstFallback->nama ?? 'missing',
                        'supplier_nama' => $firstFallback->supplier_nama ?? 'missing',
                        'pic_purchasing_id' => $firstFallback->pic_purchasing_id ?? 'missing',
                        'pic_purchasing_nama' => $firstFallback->pic_purchasing_nama ?? 'missing',
                        'stok' => $firstFallback->stok ?? 'missing',
                        'harga_per_satuan' => $firstFallback->harga_per_satuan ?? 'missing'
                    ]);
                }
                
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
            Log::error('Error trace: ' . $e->getTraceAsString());
            Log::error('Error file: ' . $e->getFile() . ' line: ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data supplier: ' . $e->getMessage()
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
            set_time_limit(60); // Increase timeout untuk debug
            
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

            $forecastDetails = [];

            // Ambil semua PO Bahan Baku dan Supplier yang dibutuhkan sekaligus untuk efisiensi
            $poBahanBakuIds = collect($request->details)->pluck('purchase_order_bahan_baku_id')->unique();
            $supplierIds = collect($request->details)->pluck('bahan_baku_supplier_id')->unique();
            
            $poBahanBakus = PurchaseOrderBahanBaku::whereIn('id', $poBahanBakuIds)->get()->keyBy('id');
            $suppliers = BahanBakuSupplier::with('supplier')->whereIn('id', $supplierIds)->get()->keyBy('id');

            // Get timestamp sekali saja
            $timestamp = now();

            // LOGIKA GROUPING FORECAST: 
            // Cek apakah sudah ada forecast dengan PO, tanggal kirim, hari kirim, dan supplier yang SAMA
            $existingForecast = null;
            $supplierIdsInRequest = collect($request->details)->pluck('bahan_baku_supplier_id')->unique();
            
            // Ambil supplier_id yang sebenarnya dari bahan_baku_supplier
            $actualSupplierIds = $suppliers->whereIn('id', $supplierIdsInRequest)
                ->pluck('supplier_id')
                ->unique()
                ->sort()
                ->values();
            
            Log::info("New bahan_baku_supplier IDs: [" . $supplierIdsInRequest->implode(', ') . "]");
            Log::info("Actual supplier IDs from those bahan_baku_supplier: [" . $actualSupplierIds->implode(', ') . "]");
            
            // Cari forecast yang sudah ada dengan kriteria yang sama:
            // 1. PO yang sama
            // 2. Tanggal forecast yang sama 
            // 3. Hari kirim yang sama
            // 4. Status masih pending
            // 5. Memiliki supplier yang PERSIS SAMA dengan yang akan ditambahkan
            $potentialForecasts = Forecast::where('purchase_order_id', $request->purchase_order_id)
                ->where('tanggal_forecast', $request->tanggal_forecast)
                ->where('hari_kirim_forecast', $request->hari_kirim_forecast)
                ->where('status', 'pending') // Hanya forecast yang masih pending
                ->with(['forecastDetails']) // Remove bahanBakuSupplier eager loading
                ->get();
                
            Log::info("Searching for existing forecast with criteria - PO: {$request->purchase_order_id}, Date: {$request->tanggal_forecast}, Hari Kirim: {$request->hari_kirim_forecast}");
            Log::info("Found " . $potentialForecasts->count() . " potential forecasts");
                
            foreach ($potentialForecasts as $potentialForecast) {
                // Ambil bahan_baku_supplier_id dari forecast detail yang sudah ada
                $existingBahanBakuSupplierIds = $potentialForecast->forecastDetails->pluck('bahan_baku_supplier_id')->unique();
                
                // Load supplier data untuk forecast detail yang sudah ada (terpisah dari $suppliers yang baru)
                $existingSuppliersData = BahanBakuSupplier::whereIn('id', $existingBahanBakuSupplierIds)->get()->keyBy('id');
                
                // Ambil supplier_id yang sebenarnya dari forecast detail yang sudah ada
                $existingActualSupplierIds = $existingSuppliersData->pluck('supplier_id')
                    ->unique()
                    ->sort()
                    ->values();
                
                Log::info("Checking forecast ID {$potentialForecast->id}:");
                Log::info("  - existing bahan_baku_supplier IDs: [" . $existingBahanBakuSupplierIds->implode(', ') . "]");
                Log::info("  - existing actual supplier IDs: [" . $existingActualSupplierIds->implode(', ') . "]");
                Log::info("  - new actual supplier IDs: [" . $actualSupplierIds->implode(', ') . "]");
                
                // Cek apakah semua supplier persis sama (exact match berdasarkan supplier_id)
                $supplierExactMatch = $existingActualSupplierIds->count() === $actualSupplierIds->count() && 
                                     $existingActualSupplierIds->diff($actualSupplierIds)->isEmpty();
                
                Log::info("  - supplier exact match: " . ($supplierExactMatch ? 'yes' : 'no'));
                
                if ($supplierExactMatch) {
                    $existingForecast = $potentialForecast;
                    Log::info("Found existing forecast for grouping: ID {$existingForecast->id}, No: {$existingForecast->no_forecast}");
                    break;
                }
            }
            
            if (!$existingForecast) {
                Log::info("No existing forecast found with same PO + date + day + suppliers, will create new one");
            }

            if ($existingForecast) {
                // Gunakan forecast yang sudah ada
                $forecast = $existingForecast;
                
                Log::info("Starting to update existing forecast...");
                
                // Update total qty dan harga dengan menambahkan yang baru
                $newTotalQty = $forecast->total_qty_forecast + $totalQty;
                $newTotalHarga = $forecast->total_harga_forecast + $totalHarga;
                
                // Update catatan jika ada catatan baru
                $newCatatan = $forecast->catatan;
                if (!empty($request->catatan)) {
                    $newCatatan = empty($forecast->catatan) 
                        ? $request->catatan 
                        : $forecast->catatan . "\n\n" . $request->catatan;
                }
                
                Log::info("About to update forecast with raw query...");
                // Use raw update untuk avoid potential lock issues
                DB::table('forecasts')
                    ->where('id', $forecast->id)
                    ->update([
                        'total_qty_forecast' => $newTotalQty,
                        'total_harga_forecast' => $newTotalHarga,
                        'catatan' => $newCatatan,
                        'updated_at' => $timestamp
                    ]);
                
                // Update object untuk response
                $forecast->total_qty_forecast = $newTotalQty;
                $forecast->total_harga_forecast = $newTotalHarga;
                $forecast->catatan = $newCatatan;
                
                Log::info("Forecast updated successfully with raw query");
                
                Log::info("Updated existing forecast with new totals - Qty: {$forecast->total_qty_forecast}, Harga: {$forecast->total_harga_forecast}");
            } else {
                // Tentukan purchasing_id berdasarkan supplier dari detail pertama
                $firstDetail = $request->details[0];
                $firstSupplier = $suppliers->get($firstDetail['bahan_baku_supplier_id']);
                $purchasingId = $firstSupplier && $firstSupplier->supplier ? $firstSupplier->supplier->pic_purchasing_id : Auth::id();

                // Buat forecast baru
                $forecast = Forecast::create([
                    'purchase_order_id' => $request->purchase_order_id,
                    'purchasing_id' => $purchasingId,
                    'no_forecast' => $noForecast,
                    'tanggal_forecast' => $request->tanggal_forecast,
                    'hari_kirim_forecast' => $request->hari_kirim_forecast,
                    'status' => 'pending',
                    'catatan' => $request->catatan,
                    'total_qty_forecast' => $totalQty,
                    'total_harga_forecast' => $totalHarga
                ]);
                
                Log::info("Created new forecast with ID: {$forecast->id} and number: {$forecast->no_forecast}");
            }
            
            // Log::info("Created forecast with ID: {$forecast->id} and number: {$forecast->no_forecast}");

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
            Log::info("About to insert " . count($forecastDetails) . " forecast details...");
            if (!empty($forecastDetails)) {
                DB::table('forecast_details')->insert($forecastDetails);
            }
            Log::info("Forecast details inserted successfully");

            
            // Log::info("Forecast created successfully with " . count($forecastDetails) . " details");
            
            // Log informasi grouping
            if ($existingForecast) {
                // Avoid additional query - just log the count we know
                Log::info("Forecast details added to existing forecast. New details added: " . count($forecastDetails));
            } else {
                Log::info("New forecast created with " . count($forecastDetails) . " details");
            }

            Log::info("About to commit transaction...");
            DB::commit();
            Log::info("Transaction committed successfully");

            return response()->json([
                'success' => true,
                'message' => $existingForecast 
                    ? "Forecast detail berhasil ditambahkan ke forecast yang sudah ada (No: {$forecast->no_forecast})"
                    : 'Forecast berhasil dibuat',
                'forecast' => [
                    'id' => $forecast->id,
                    'no_forecast' => $forecast->no_forecast,
                    'total_qty_forecast' => $forecast->total_qty_forecast,
                    'total_harga_forecast' => $forecast->total_harga_forecast,
                    'is_grouped' => $existingForecast ? true : false
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

    /**
     * Lakukan pengiriman forecast (ubah status menjadi 'sukses')
     */
    public function kirimForecast($id)
    {
        try {
            $forecast = Forecast::find($id);
            
            if (!$forecast) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forecast tidak ditemukan'
                ], 404);
            }

            if ($forecast->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya forecast dengan status pending yang dapat dikirim'
                ], 400);
            }

            $forecast->update([
                'status' => 'sukses',
                'updated_by' => Auth::id(),
                'updated_at' => now()
            ]);

            Log::info("Forecast {$forecast->no_forecast} berhasil dikirim oleh user " . Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Forecast berhasil dikirim'
            ]);

        } catch (\Exception $e) {
            Log::error('Error mengirim forecast: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim forecast: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan forecast (ubah status menjadi 'batal' dan buat data pengiriman dengan status 'gagal')
     */
    public function batalkanForecast(Request $request, $id)
    {
        Log::info("batalkanForecast called with ID: {$id}");
        Log::info("Request data: " . json_encode($request->all()));
        
        // Quick database connectivity test
        try {
            $tableExists = DB::select("SHOW TABLES LIKE 'forecasts'");
            Log::info("Forecasts table exists: " . (count($tableExists) > 0 ? 'yes' : 'no'));
            
            $pengirimanTableExists = DB::select("SHOW TABLES LIKE 'pengiriman'");
            Log::info("Pengiriman table exists: " . (count($pengirimanTableExists) > 0 ? 'yes' : 'no'));
        } catch (\Exception $dbTest) {
            Log::error("Database test failed: " . $dbTest->getMessage());
        }
        
        // Validasi input alasan pembatalan
        try {
            $request->validate([
                'alasan_batal' => 'required|string|min:10|max:500'
            ], [
                'alasan_batal.required' => 'Alasan pembatalan harus diisi',
                'alasan_batal.min' => 'Alasan pembatalan minimal 10 karakter',
                'alasan_batal.max' => 'Alasan pembatalan maksimal 500 karakter'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed in batalkanForecast:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Set shorter timeout for this operation
            DB::statement('SET SESSION innodb_lock_wait_timeout = 10');
            
            Log::info("Starting database transaction for forecast cancellation");
            DB::beginTransaction();

            // Optimized: Load forecast with minimal eager loading and lock for update
            Log::info("Loading forecast with ID: {$id}");
            $forecast = Forecast::with(['forecastDetails:id,forecast_id,purchase_order_bahan_baku_id,bahan_baku_supplier_id,qty_forecast,harga_satuan_forecast,total_harga_forecast,catatan_detail'])
                ->select('id', 'purchase_order_id', 'purchasing_id', 'no_forecast', 'tanggal_forecast', 'hari_kirim_forecast', 'total_qty_forecast', 'total_harga_forecast', 'status')
                ->lockForUpdate()
                ->find($id);
            
            if (!$forecast) {
                Log::error("Forecast not found with ID: {$id}");
                return response()->json([
                    'success' => false,
                    'message' => 'Forecast tidak ditemukan'
                ], 404);
            }

            Log::info("Forecast found: {$forecast->no_forecast}, Status: {$forecast->status}");

            if ($forecast->status !== 'pending') {
                Log::error("Forecast status is not pending: {$forecast->status}");
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya forecast dengan status pending yang dapat dibatalkan'
                ], 400);
            }

            // Optimized: Generate simplified no_pengiriman first
            $timestamp = now();
            $noPengiriman = 'BATAL-' . $forecast->id . '-' . $timestamp->format('ymdHis');
            
            Log::info("Creating pengiriman with no: {$noPengiriman}");
            
            // 1. Create Pengiriman record with optimized data (using raw insert for speed)
            $pengirimanId = DB::table('pengiriman')->insertGetId([
                'purchase_order_id' => $forecast->purchase_order_id,
                'purchasing_id' => $forecast->purchasing_id,
                'no_pengiriman' => $noPengiriman,
                'tanggal_kirim' => $forecast->tanggal_forecast,
                'hari_kirim' => $forecast->hari_kirim_forecast,
                'total_qty_kirim' => $forecast->total_qty_forecast,
                'total_harga_kirim' => $forecast->total_harga_forecast,
                'status' => 'gagal',
                'catatan' => "PEMBATALAN: {$request->alasan_batal} | Forecast: {$forecast->no_forecast} | " . $timestamp->format('d/m/Y H:i'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]);
            
            Log::info("Pengiriman created with ID: {$pengirimanId}");

            // 2. Optimized: Batch insert pengiriman details
            if ($forecast->forecastDetails->isNotEmpty()) {
                Log::info("Creating pengiriman details, count: " . $forecast->forecastDetails->count());
                $pengirimanDetails = [];
                $currentTime = $timestamp->format('Y-m-d H:i:s');
                
                foreach ($forecast->forecastDetails as $detail) {
                    $pengirimanDetails[] = [
                        'pengiriman_id' => $pengirimanId,
                        'purchase_order_bahan_baku_id' => $detail->purchase_order_bahan_baku_id,
                        'bahan_baku_supplier_id' => $detail->bahan_baku_supplier_id,
                        'qty_kirim' => $detail->qty_forecast,
                        'harga_satuan' => $detail->harga_satuan_forecast,
                        'total_harga' => $detail->total_harga_forecast,
                        'catatan_detail' => $detail->catatan_detail,
                        'created_at' => $currentTime,
                        'updated_at' => $currentTime
                    ];
                }
                
                // Batch insert for better performance
                Log::info("Inserting " . count($pengirimanDetails) . " pengiriman details");
                DB::table('pengiriman_details')->insert($pengirimanDetails);
                Log::info("Pengiriman details inserted successfully");
            } else {
                Log::info("No forecast details to copy");
            }

            // 3. Update forecast status using raw query to avoid potential lock issues
            Log::info("Updating forecast status to 'gagal'");
            DB::table('forecasts')
                ->where('id', $forecast->id)
                ->update([
                    'status' => 'gagal',
                    'updated_at' => $timestamp
                ]);
            Log::info("Forecast status updated successfully");

            Log::info("Committing transaction");
            DB::commit();
            Log::info("Transaction committed successfully");

            // Simplified logging
            Log::info("Forecast {$forecast->no_forecast} dibatalkan, dipindah ke pengiriman ID: {$pengirimanId}");

            return response()->json([
                'success' => true,
                'message' => "Forecast {$forecast->no_forecast} berhasil dibatalkan dan data dipindahkan ke pengiriman",
                'data' => [
                    'forecast_id' => $forecast->id,
                    'pengiriman_id' => $pengirimanId,
                    'no_forecast' => $forecast->no_forecast,
                    'no_pengiriman' => $noPengiriman
                ]
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            Log::error('Database query error in batalkan forecast: ' . $e->getMessage());
            Log::error('Error code: ' . $e->getCode());
            
            // Handle specific database errors
            if ($e->getCode() == 1205 || str_contains($e->getMessage(), 'Lock wait timeout')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sistem sedang sibuk. Silakan coba lagi dalam beberapa saat.',
                ], 500);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan database. Silakan coba lagi.',
                'debug_info' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error batalkan forecast - Exception: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ' line: ' . $e->getLine());
            Log::error('Error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan forecast. Silakan coba lagi.',
                'debug_info' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}