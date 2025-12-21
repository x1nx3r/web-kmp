<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Forecast;
use App\Models\ForecastDetail;
use App\Models\BahanBakuSupplier;
use App\Models\RiwayatHargaBahanBaku;
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
        // Ambil Order yang statusnya dikonfirmasi atau diproses
        $orders = Order::with([
            'klien',
            'orderDetails.bahanBakuKlien'
        ])
        ->whereIn('status', ['dikonfirmasi', 'diproses'])
        ->when(request('search'), function($query) {
            $query->where(function($q) {
                $q->where('po_number', 'like', '%' . request('search') . '%')
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
            $query->withCount('orderDetails');
            if (request('sort_items') == 'most') {
                $query->orderBy('order_details_count', 'desc');
            } elseif (request('sort_items') == 'least') {
                $query->orderBy('order_details_count', 'asc');
            }
        }, function($query) {
            $query->orderBy('created_at', 'desc');
        })
        ->paginate(10, ['*'], 'page_buat_forecasting')
        ->withQueryString();

        // Ambil forecast berdasarkan status dengan eager loading dan pagination
        $pendingForecasts = Forecast::with(['order.klien', 'purchasing'])
            ->pending()
            ->when(request('search_pending'), function($query) {
                $searchTerm = request('search_pending');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('no_forecast', 'like', "%{$searchTerm}%")
                      ->orWhereHas('order', function($subQ) use ($searchTerm) {
                          $subQ->where('po_number', 'like', "%{$searchTerm}%")
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

        $suksesForecasts = Forecast::with(['order.klien', 'purchasing'])
            ->sukses()
            ->when(request('search_sukses'), function($query) {
                $searchTerm = request('search_sukses');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('no_forecast', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('order', function($poQuery) use ($searchTerm) {
                          $poQuery->where('po_number', 'like', '%' . $searchTerm . '%')
                                  ->orWhereHas('klien', function($klienQuery) use ($searchTerm) {
                                      $klienQuery->where('nama', 'like', '%' . $searchTerm . '%');
                                  });
                      });
                });
            })
            ->when(request('date_range_sukses'), function($query) {
                $query->whereDate('tanggal_forecast', request('date_range_sukses'));
            })
            ->when(request('sort_order_sukses'), function($query) {
                if (request('sort_order_sukses') == 'oldest') {
                    $query->oldest('created_at');
                } else {
                    $query->latest('created_at');
                }
            }, function($query) {
                $query->latest('created_at');
            })
            ->paginate(10, ['*'], 'page_sukses')
            ->withQueryString();

        $gagalForecasts = Forecast::with(['order.klien', 'purchasing'])
            ->gagal()
            ->when(request('search_gagal'), function($query) {
                $searchTerm = request('search_gagal');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('no_forecast', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('order', function($poQuery) use ($searchTerm) {
                          $poQuery->where('po_number', 'like', '%' . $searchTerm . '%')
                                  ->orWhereHas('klien', function($klienQuery) use ($searchTerm) {
                                      $klienQuery->where('nama', 'like', '%' . $searchTerm . '%');
                                  });
                      });
                });
            })
            ->when(request('date_range_gagal'), function($query) {
                $query->whereDate('tanggal_forecast', request('date_range_gagal'));
            })
            ->when(request('sort_order_gagal'), function($query) {
                if (request('sort_order_gagal') == 'oldest') {
                    $query->oldest('created_at');
                } else {
                    $query->latest('created_at');
                }
            }, function($query) {
                $query->latest('created_at');
            })
            ->paginate(10, ['*'], 'page_gagal')
            ->withQueryString();

        return view('pages.purchasing.forecast', compact(
            'orders',
            'pendingForecasts',
            'suksesForecasts',
            'gagalForecasts'
        ));
    }

    public function getBahanBakuSuppliers($orderDetailId)
    {
        Log::info("getBahanBakuSuppliers called with OrderDetail ID: {$orderDetailId}");
        
        try {
            // Debug: Check if tables exist and have data
            $supplierCount = DB::table('suppliers')->count();
            $bahanBakuSupplierCount = DB::table('bahan_baku_supplier')->count();
            Log::info("Table counts - suppliers: {$supplierCount}, bahan_baku_supplier: {$bahanBakuSupplierCount}");
            
            $orderDetail = OrderDetail::with('bahanBakuKlien')->find($orderDetailId);
            
            if (!$orderDetail) {
                Log::error("OrderDetail not found with ID: {$orderDetailId}");
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }
            
            Log::info("Found OrderDetail: " . json_encode($orderDetail));

            // Ambil nama bahan baku dari order detail untuk filtering
            $orderBahanBakuNama = null;
            if ($orderDetail->bahanBakuKlien) {
                $orderBahanBakuNama = $orderDetail->bahanBakuKlien->nama;
                Log::info("Order bahan baku nama: {$orderBahanBakuNama}");
            }

            // Ambil bahan baku supplier yang tersedia dengan filtering berdasarkan nama bahan baku
            // Optimized query dengan proper join dan select, termasuk pic_purchasing
            try {
                $query = BahanBakuSupplier::select([
                        'bahan_baku_supplier.*',
                        'suppliers.nama as supplier_nama',
                        'suppliers.pic_purchasing_id',
                        'users.nama as pic_purchasing_nama'
                    ])
                    ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                    ->leftJoin('users', 'suppliers.pic_purchasing_id', '=', 'users.id')
                    ->where('bahan_baku_supplier.stok', '>', 0); // Hanya ambil yang ada stoknya
                    
                // Filter berdasarkan kemiripan nama bahan baku (case-insensitive, partial match)
                if ($orderBahanBakuNama) {
                    // Remove common words and clean the name for better matching
                    $cleanOrderName = strtolower(trim($orderBahanBakuNama));
                    $keywords = explode(' ', $cleanOrderName);
                    
                    $query->where(function($subQuery) use ($cleanOrderName, $keywords) {
                        // Exact match (case-insensitive)
                        $subQuery->whereRaw('LOWER(bahan_baku_supplier.nama) = ?', [$cleanOrderName])
                                // Partial match with full name
                                ->orWhereRaw('LOWER(bahan_baku_supplier.nama) LIKE ?', ['%' . $cleanOrderName . '%'])
                                ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(bahan_baku_supplier.nama), "%")', [$cleanOrderName]);
                        
                        // Match individual keywords for better coverage
                        foreach ($keywords as $keyword) {
                            $keyword = trim($keyword);
                            if (strlen($keyword) >= 3) { // Only consider keywords with 3+ characters
                                $subQuery->orWhereRaw('LOWER(bahan_baku_supplier.nama) LIKE ?', ['%' . $keyword . '%']);
                            }
                        }
                    });
                    
                    Log::info("Applied bahan baku name filter for: {$orderBahanBakuNama}");
                }
                
                $bahanBakuSuppliers = $query->orderBy('bahan_baku_supplier.nama', 'asc')->get();
                    
                Log::info('Main query executed successfully, found: ' . $bahanBakuSuppliers->count() . ' records');
            } catch (\Exception $queryError) {
                Log::error('Error in main query: ' . $queryError->getMessage());
                Log::error('Query error details: ' . $queryError->getTraceAsString());
                
                // Fallback to simpler query with manual supplier loading
                $fallbackQuery = BahanBakuSupplier::with('supplier.picPurchasing')
                    ->where('stok', '>', 0);
                
                // Apply the same bahan baku name filtering in fallback
                if ($orderBahanBakuNama) {
                    $cleanOrderName = strtolower(trim($orderBahanBakuNama));
                    $keywords = explode(' ', $cleanOrderName);
                    
                    $fallbackQuery->where(function($subQuery) use ($cleanOrderName, $keywords) {
                        // Exact match (case-insensitive)
                        $subQuery->whereRaw('LOWER(nama) = ?', [$cleanOrderName])
                                // Partial match with full name
                                ->orWhereRaw('LOWER(nama) LIKE ?', ['%' . $cleanOrderName . '%'])
                                ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(nama), "%")', [$cleanOrderName]);
                        
                        // Match individual keywords for better coverage
                        foreach ($keywords as $keyword) {
                            $keyword = trim($keyword);
                            if (strlen($keyword) >= 3) {
                                $subQuery->orWhereRaw('LOWER(nama) LIKE ?', ['%' . $keyword . '%']);
                            }
                        }
                    });
                }
                
                $bahanBakuSuppliers = $fallbackQuery->orderBy('nama', 'asc')->get();
                
                // Transform the data to include supplier_nama field
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
                    $allSuppliersQuery = BahanBakuSupplier::select([
                            'bahan_baku_supplier.*',
                            'suppliers.nama as supplier_nama',
                            'suppliers.pic_purchasing_id',
                            'users.nama as pic_purchasing_nama'
                        ])
                        ->join('suppliers', 'bahan_baku_supplier.supplier_id', '=', 'suppliers.id')
                        ->leftJoin('users', 'suppliers.pic_purchasing_id', '=', 'users.id');
                    
                    // Apply the same bahan baku name filtering in all suppliers query
                    if ($orderBahanBakuNama) {
                        $cleanOrderName = strtolower(trim($orderBahanBakuNama));
                        $keywords = explode(' ', $cleanOrderName);
                        
                        $allSuppliersQuery->where(function($subQuery) use ($cleanOrderName, $keywords) {
                            // Exact match (case-insensitive)
                            $subQuery->whereRaw('LOWER(bahan_baku_supplier.nama) = ?', [$cleanOrderName])
                                    // Partial match with full name
                                    ->orWhereRaw('LOWER(bahan_baku_supplier.nama) LIKE ?', ['%' . $cleanOrderName . '%'])
                                    ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(bahan_baku_supplier.nama), "%")', [$cleanOrderName]);
                            
                            // Match individual keywords for better coverage
                            foreach ($keywords as $keyword) {
                                $keyword = trim($keyword);
                                if (strlen($keyword) >= 3) {
                                    $subQuery->orWhereRaw('LOWER(bahan_baku_supplier.nama) LIKE ?', ['%' . $keyword . '%']);
                                }
                            }
                        });
                    }
                    
                    $allSuppliers = $allSuppliersQuery->orderBy('suppliers.nama', 'asc')
                        ->orderBy('bahan_baku_supplier.nama', 'asc')
                        ->get();
                        
                    Log::info('Fallback JOIN query executed successfully, found: ' . $allSuppliers->count() . ' records');
                } catch (\Exception $fallbackError) {
                    Log::error('Error in fallback query: ' . $fallbackError->getMessage());
                    Log::error('Fallback error details: ' . $fallbackError->getTraceAsString());
                    
                    // Use simple query as last resort with manual supplier loading
                    $finalFallbackQuery = BahanBakuSupplier::with('supplier.picPurchasing');
                    
                    // Apply the same bahan baku name filtering in final fallback
                    if ($orderBahanBakuNama) {
                        $cleanOrderName = strtolower(trim($orderBahanBakuNama));
                        $keywords = explode(' ', $cleanOrderName);
                        
                        $finalFallbackQuery->where(function($subQuery) use ($cleanOrderName, $keywords) {
                            // Exact match (case-insensitive)
                            $subQuery->whereRaw('LOWER(nama) = ?', [$cleanOrderName])
                                    // Partial match with full name
                                    ->orWhereRaw('LOWER(nama) LIKE ?', ['%' . $cleanOrderName . '%'])
                                    ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(nama), "%")', [$cleanOrderName]);
                            
                            // Match individual keywords for better coverage
                            foreach ($keywords as $keyword) {
                                $keyword = trim($keyword);
                                if (strlen($keyword) >= 3) {
                                    $subQuery->orWhereRaw('LOWER(nama) LIKE ?', ['%' . $keyword . '%']);
                                }
                            }
                        });
                    }
                    
                    $allSuppliers = $finalFallbackQuery->orderBy('nama', 'asc')->get();
                        
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
                'order_detail' => $orderDetail,
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
        // Authorization: Only direktur, manager_purchasing, and staff_purchasing can create forecasting
        $user = Auth::user();
        $allowedRoles = ['direktur', 'manager_purchasing', 'staff_purchasing'];
        
        if (!in_array($user->role, $allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk membuat forecasting. Hanya direktur dan tim purchasing yang dapat membuat forecasting.'
            ], 403);
        }

        // Log untuk debugging (dimatikan untuk performa)
        // Log::info('Request received:', $request->all());
        
        // Improved validation with better error messages
        try {
            $request->validate([
                'purchase_order_id' => 'required|exists:orders,id',
                'tanggal_forecast' => 'required|date',
                'hari_kirim_forecast' => 'required|string|max:50',
                'catatan' => 'nullable|string|max:1000',
                'details' => 'required|array|min:1',
                'details.*.purchase_order_bahan_baku_id' => 'required|exists:order_details,id',
                'details.*.bahan_baku_supplier_id' => 'required|exists:bahan_baku_supplier,id',
                'details.*.qty_forecast' => 'required|numeric|min:0.01|max:9999999.99',
                'details.*.harga_satuan_forecast' => 'required|numeric|min:0.01|max:999999999.99',
                'details.*.catatan_detail' => 'nullable|string|max:500',
                // Validation for price update
                'update_harga_supplier' => 'sometimes|array',
                'update_harga_supplier.bahan_baku_supplier_id' => 'required_with:update_harga_supplier|exists:bahan_baku_supplier,id',
                'update_harga_supplier.harga_lama' => 'required_with:update_harga_supplier|numeric|min:0',
                'update_harga_supplier.harga_baru' => 'required_with:update_harga_supplier|numeric|min:0.01|max:999999999.99',
                'update_harga_supplier.update_harga_supplier' => 'required_with:update_harga_supplier|boolean'
            ], [
                'purchase_order_id.required' => 'Order harus dipilih',
                'purchase_order_id.exists' => 'Order tidak valid',
                'tanggal_forecast.required' => 'Tanggal forecast harus diisi',
                'tanggal_forecast.date' => 'Format tanggal tidak valid',
                'hari_kirim_forecast.required' => 'Hari kirim forecast harus diisi',
                'details.required' => 'Detail forecast harus diisi',
                'details.min' => 'Minimal harus ada 1 item dalam forecast',
                'details.*.qty_forecast.required' => 'Qty forecast harus diisi',
                'details.*.qty_forecast.min' => 'Qty forecast minimal 0.01',
                'details.*.harga_satuan_forecast.required' => 'Harga satuan forecast harus diisi',
                'details.*.harga_satuan_forecast.min' => 'Harga satuan forecast minimal 0.01',
                'update_harga_supplier.harga_baru.min' => 'Harga baru minimal 0.01',
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
            
            // Log::info('Creating forecast for Order ID:', ['purchase_order_id' => $request->purchase_order_id]);

            // Generate nomor forecast otomatis dengan pengecekan duplikat
            $year = date('Y');
            $month = date('m');
            
            // Cari nomor forecast terakhir untuk bulan ini (termasuk yang soft deleted)
            $latestForecast = Forecast::withTrashed()
                ->where('no_forecast', 'like', 'FC-' . $year . $month . '-%')
                ->orderBy('no_forecast', 'desc')
                ->first();
            
            if ($latestForecast) {
                // Extract number dari no_forecast terakhir
                $lastNumber = (int) substr($latestForecast->no_forecast, -4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            
            $noForecast = 'FC-' . $year . $month . '-' . sprintf('%04d', $nextNumber);

            // Hitung total dulu sebelum create forecast
            $totalQty = 0;
            $totalHarga = 0;
            
            foreach ($request->details as $detail) {
                $totalQty += (float) $detail['qty_forecast'];
                $totalHarga += (float) $detail['qty_forecast'] * (float) $detail['harga_satuan_forecast'];
            }

            $forecastDetails = [];

            // Ambil semua Order Details dan Supplier yang dibutuhkan sekaligus untuk efisiensi
            $orderDetailIds = collect($request->details)->pluck('purchase_order_bahan_baku_id')->unique();
            $supplierIds = collect($request->details)->pluck('bahan_baku_supplier_id')->unique();
            
            $orderDetails = OrderDetail::whereIn('id', $orderDetailIds)->get()->keyBy('id');
            $suppliers = BahanBakuSupplier::with('supplier')->whereIn('id', $supplierIds)->get()->keyBy('id');

            // Handle price update for supplier if requested
            if ($request->has('update_harga_supplier') && $request->update_harga_supplier['update_harga_supplier'] === true) {
                $updatePriceData = $request->update_harga_supplier;
                
                Log::info('Processing price update for supplier', [
                    'bahan_baku_supplier_id' => $updatePriceData['bahan_baku_supplier_id'],
                    'harga_lama' => $updatePriceData['harga_lama'],
                    'harga_baru' => $updatePriceData['harga_baru']
                ]);
                
                // Validate that supplier exists
                $supplierToUpdate = BahanBakuSupplier::find($updatePriceData['bahan_baku_supplier_id']);
                if (!$supplierToUpdate) {
                    throw new \Exception("Bahan Baku Supplier tidak ditemukan untuk update harga");
                }
                
                // Update harga_per_satuan pada BahanBakuSupplier
                $supplierToUpdate->update([
                    'harga_per_satuan' => $updatePriceData['harga_baru']
                ]);
                
                // Catat perubahan harga ke RiwayatHargaBahanBaku
                RiwayatHargaBahanBaku::catatPerubahanHarga(
                    $updatePriceData['bahan_baku_supplier_id'],
                    $updatePriceData['harga_lama'],
                    $updatePriceData['harga_baru'],
                    'Perubahan harga melalui forecast', // Default keterangan
                    Auth::id(),
                    now()
                );
                
                // Update supplier data dalam collection untuk forecast
                $suppliers[$updatePriceData['bahan_baku_supplier_id']]->harga_per_satuan = $updatePriceData['harga_baru'];
                
                Log::info('Price update completed successfully');
            }

            // Get timestamp sekali saja
            $timestamp = now();

            // LOGIKA GROUPING FORECAST: 
            // Cek apakah sudah ada forecast dengan PO, tanggal kirim, hari kirim, dan supplier yang SAMA
            $existingForecast = null;
            $supplierIdsInRequest = collect($request->details)->pluck('bahan_baku_supplier_id')->unique();
            
            // Ambil supplier_id yang sebenarnya dari bahan_baku_supplier dengan null safety
            $actualSupplierIds = $suppliers->whereIn('id', $supplierIdsInRequest)
                ->filter(function($item) {
                    return $item->supplier_id !== null; // Only include items with valid supplier_id
                })
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
                
            Log::info("Searching for existing forecast with criteria - Order: {$request->purchase_order_id}, Date: {$request->tanggal_forecast}, Hari Kirim: {$request->hari_kirim_forecast}");
            Log::info("Found " . $potentialForecasts->count() . " potential forecasts");
                
            foreach ($potentialForecasts as $potentialForecast) {
                // Ambil bahan_baku_supplier_id dari forecast detail yang sudah ada
                $existingBahanBakuSupplierIds = $potentialForecast->forecastDetails->pluck('bahan_baku_supplier_id')->unique();
                
                // Load supplier data untuk forecast detail yang sudah ada (terpisah dari $suppliers yang baru)
                $existingSuppliersData = BahanBakuSupplier::with('supplier')->whereIn('id', $existingBahanBakuSupplierIds)->get()->keyBy('id');
                
                // Ambil supplier_id yang sebenarnya dari forecast detail yang sudah ada dengan null safety
                $existingActualSupplierIds = $existingSuppliersData
                    ->filter(function($item) {
                        return $item->supplier_id !== null; // Only include items with valid supplier_id
                    })
                    ->pluck('supplier_id')
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
                
                // Null safety check untuk accessing supplier relation
                $purchasingId = null;
                if ($firstSupplier && $firstSupplier->supplier && $firstSupplier->supplier->pic_purchasing_id) {
                    $purchasingId = $firstSupplier->supplier->pic_purchasing_id;
                } else {
                    $purchasingId = Auth::id();
                }

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
                // Ambil data Order Detail dari collection
                $orderDetail = $orderDetails->get($detail['purchase_order_bahan_baku_id']);
                
                if (!$orderDetail) {
                    throw new \Exception("Order Detail dengan ID {$detail['purchase_order_bahan_baku_id']} tidak ditemukan");
                }

                // Validasi supplier dari collection
                $bahanBakuSupplier = $suppliers->get($detail['bahan_baku_supplier_id']);
                if (!$bahanBakuSupplier) {
                    throw new \Exception("Bahan Baku Supplier dengan ID {$detail['bahan_baku_supplier_id']} tidak ditemukan");
                }
                
                // Hitung total harga Order dan Supplier
                $qtyForecast = (float) $detail['qty_forecast'];
                $hargaSatuanForecast = (float) $detail['harga_satuan_forecast'];
                $hargaSatuanOrder = (float) $orderDetail->harga_jual; // Menggunakan harga_jual dari order_details
                
                $totalHargaOrder = $qtyForecast * $hargaSatuanOrder;
                $totalHargaSupplier = $qtyForecast * $hargaSatuanForecast;
                
                // Prepare data untuk batch insert
                $forecastDetails[] = [
                    'forecast_id' => $forecast->id,
                    'purchase_order_bahan_baku_id' => $detail['purchase_order_bahan_baku_id'],
                    'bahan_baku_supplier_id' => $detail['bahan_baku_supplier_id'],
                    'qty_forecast' => $qtyForecast,
                    'harga_satuan_forecast' => $hargaSatuanForecast,
                    'total_harga_forecast' => $totalHargaSupplier,
                    'harga_satuan_po' => $hargaSatuanOrder,
                    'total_harga_po' => $totalHargaOrder,
                    'catatan_detail' => $detail['catatan_detail'] ?? null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];

                // Total sudah dihitung sebelumnya
                
                // Log comparison untuk debugging (dimatikan untuk performa)
                // if ($index === 0) {
                //     $selisih = $totalHargaSupplier - $totalHargaOrder;
                //     $persentase = $totalHargaOrder > 0 ? (($selisih / $totalHargaOrder) * 100) : 0;
                //     Log::info("Forecast detail #{$index} - Order: Rp" . number_format($hargaSatuanOrder, 0, ',', '.') .
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
     * Lakukan pengiriman forecast (ubah status menjadi 'sukses' dan buat data pengiriman dengan status 'pending')
     */
    public function kirimForecast($id)
    {
        Log::info("kirimForecast called with ID: {$id}");
        
        // Authorization: Only direktur, manager_purchasing, and PIC Purchasing can process forecast to pengiriman
        $user = Auth::user();
        
        // First, get the forecast to check PIC
        $forecastCheck = Forecast::select('id', 'purchasing_id')->find($id);
        
        if (!$forecastCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Forecast tidak ditemukan'
            ], 404);
        }
        
        // Check authorization
        $canProcess = $user->role === 'direktur' || 
                      $user->role === 'manager_purchasing' ||
                      ($user->id == $forecastCheck->purchasing_id);
        
        if (!$canProcess) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk memproses forecast ini menjadi pengiriman. Hanya direktur, manager purchasing, dan PIC Purchasing yang dapat memproses forecast.'
            ], 403);
        }
        
        try {
            // Set shorter timeout for this operation
            DB::statement('SET SESSION innodb_lock_wait_timeout = 10');
            
            Log::info("Starting database transaction for forecast delivery");
            DB::beginTransaction();

            // Load forecast with details
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
                    'message' => 'Hanya forecast dengan status pending yang dapat dikirim'
                ], 400);
            }

            // Generate no_pengiriman
            $timestamp = now();
            $noPengiriman = NULL; // Set to NULL as requested            
            Log::info("Creating pengiriman with no_pengiriman: NULL");
            
            // 1. Create Pengiriman record with empty delivery fields (using raw insert for speed)
            $pengirimanId = DB::table('pengiriman')->insertGetId([
                'purchase_order_id' => $forecast->purchase_order_id,
                'purchasing_id' => $forecast->purchasing_id,
                'forecast_id' => $forecast->id, // Add forecast_id relation
                'no_pengiriman' => $noPengiriman,
                'tanggal_kirim' => NULL,
                'hari_kirim' => NULL,
                'total_qty_kirim' => 0, // Empty as requested
                'total_harga_kirim' => 0, // Empty as requested
                'status' => 'pending',
                'catatan' => "PENGIRIMAN: Forecast {$forecast->no_forecast} | " . $timestamp->format('d/m/Y H:i'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]);
            
            Log::info("Pengiriman created with ID: {$pengirimanId}");

            // 2. Create pengiriman details with empty delivery fields
            if ($forecast->forecastDetails->isNotEmpty()) {
                Log::info("Creating pengiriman details, count: " . $forecast->forecastDetails->count());
                $pengirimanDetails = [];
                $currentTime = $timestamp->format('Y-m-d H:i:s');
                
                foreach ($forecast->forecastDetails as $detail) {
                    $pengirimanDetails[] = [
                        'pengiriman_id' => $pengirimanId,
                        'purchase_order_bahan_baku_id' => $detail->purchase_order_bahan_baku_id,
                        'bahan_baku_supplier_id' => $detail->bahan_baku_supplier_id,
                        'qty_kirim' => 0, // Empty as requested
                        'harga_satuan' => 0, // Empty as requested
                        'total_harga' => 0, // Empty as requested
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

            // 3. Update forecast status to 'sukses' using raw query
            Log::info("Updating forecast status to 'sukses'");
            DB::table('forecasts')
                ->where('id', $forecast->id)
                ->update([
                    'status' => 'sukses',
                    'updated_at' => $timestamp
                ]);
            Log::info("Forecast status updated successfully");

            Log::info("Committing transaction");
            DB::commit();
            Log::info("Transaction committed successfully");

            // Simplified logging
            Log::info("Forecast {$forecast->no_forecast} berhasil dikirim, dibuat pengiriman ID: {$pengirimanId}");

            return response()->json([
                'success' => true,
                'message' => "Forecast {$forecast->no_forecast} berhasil dikirim",
                'data' => [
                    'forecast_id' => $forecast->id,
                    'pengiriman_id' => $pengirimanId,
                    'no_forecast' => $forecast->no_forecast,
                    'no_pengiriman' => null // Set to null as requested
                ]
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            Log::error('Database query error in kirim forecast: ' . $e->getMessage());
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
            Log::error('Error kirim forecast - Exception: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ' line: ' . $e->getLine());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim forecast. Silakan coba lagi.',
                'debug_info' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get forecast detail for modal display
     */
    public function getForecastDetail($id)
    {
        try {
            Log::info("getForecastDetail called with ID: {$id}");
            
            // Load forecast with all necessary relations
            $forecast = Forecast::with([
                'order.klien',
                'purchasing',
                'forecastDetails.purchaseOrderBahanBaku.bahanBakuKlien',
                'forecastDetails.bahanBakuSupplier.supplier'
            ])->find($id);
            
            if (!$forecast) {
                Log::error("Forecast not found with ID: {$id}");
                return response()->json([
                    'success' => false,
                    'message' => 'Forecast tidak ditemukan'
                ], 404);
            }

            Log::info("Forecast found: {$forecast->no_forecast}");
            
            // Log relationship data for debugging
            Log::info("Order: " . ($forecast->order ? $forecast->order->po_number : 'null'));
            Log::info("Klien: " . ($forecast->order && $forecast->order->klien ? $forecast->order->klien->nama : 'null'));
            Log::info("Purchasing: " . ($forecast->purchasing ? $forecast->purchasing->nama : 'null'));
            Log::info("Forecast Details Count: " . $forecast->forecastDetails->count());

            // Format forecast data for frontend
            $forecastData = [
                'id' => $forecast->id,
                'no_forecast' => $forecast->no_forecast ?: 'N/A',
                'klien' => $forecast->order && $forecast->order->klien ? $forecast->order->klien->nama : 'N/A',
                'po_number' => $forecast->order ? $forecast->order->po_number : 'N/A',
                'pic_purchasing' => $forecast->purchasing ? $forecast->purchasing->nama : 'N/A',
                'tanggal_forecast' => $forecast->tanggal_forecast ? \Carbon\Carbon::parse($forecast->tanggal_forecast)->format('d M Y') : 'N/A',
                'hari_kirim' => $forecast->hari_kirim_forecast ?: 'N/A',
                'total_qty' => $forecast->total_qty_forecast ? number_format((float)$forecast->total_qty_forecast, 0, ',', '.') : '0',
                'total_harga' => $forecast->total_harga_forecast ? 'Rp ' . number_format((float)$forecast->total_harga_forecast, 0, ',', '.') : 'Rp 0',
                'status' => $forecast->status ?: 'N/A',
                'catatan' => $forecast->catatan ?: null,
                'created_at' => $forecast->created_at ? $forecast->created_at->format('d M Y, H:i') : '',
                'updated_at' => $forecast->updated_at ? $forecast->updated_at->format('d M Y, H:i') : '',
                'details' => []
            ];

            // Format forecast details
            foreach ($forecast->forecastDetails as $detail) {
                Log::info("Processing detail ID: {$detail->id}");
                Log::info("Detail Bahan Baku: " . ($detail->purchaseOrderBahanBaku && $detail->purchaseOrderBahanBaku->bahanBakuKlien ? $detail->purchaseOrderBahanBaku->bahanBakuKlien->nama : 'null'));
                Log::info("Detail Supplier: " . ($detail->bahanBakuSupplier && $detail->bahanBakuSupplier->supplier ? $detail->bahanBakuSupplier->supplier->nama : 'null'));
                
                $forecastData['details'][] = [
                    'id' => $detail->id,
                    'bahan_baku' => $detail->purchaseOrderBahanBaku && $detail->purchaseOrderBahanBaku->bahanBakuKlien ? $detail->purchaseOrderBahanBaku->bahanBakuKlien->nama : 'N/A',
                    'supplier' => $detail->bahanBakuSupplier && $detail->bahanBakuSupplier->supplier ? $detail->bahanBakuSupplier->supplier->nama : 'N/A',
                    'qty' => $detail->qty_forecast ? number_format((float)$detail->qty_forecast, 0, ',', '.') : '0',
                    'harga_satuan' => $detail->harga_satuan_forecast ? 'Rp ' . number_format((float)$detail->harga_satuan_forecast, 0, ',', '.') : 'Rp 0',
                    'total_harga' => ($detail->qty_forecast && $detail->harga_satuan_forecast) ? 'Rp ' . number_format((float)($detail->qty_forecast * $detail->harga_satuan_forecast), 0, ',', '.') : 'Rp 0',
                    'catatan_detail' => $detail->catatan_detail ?: null
                ];
            }

            Log::info("Forecast detail prepared successfully for: {$forecast->no_forecast}");

            return response()->json([
                'success' => true,
                'message' => 'Detail forecast berhasil dimuat',
                'forecast' => $forecastData
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting forecast detail - Exception: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ' line: ' . $e->getLine());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail forecast. Silakan coba lagi.',
                'debug_info' => config('app.debug') ? $e->getMessage() : null
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
        
        // Authorization: Only direktur, manager_purchasing, and PIC Purchasing can cancel forecast
        $user = Auth::user();
        
        // First, get the forecast to check PIC
        $forecastCheck = Forecast::select('id', 'purchasing_id')->find($id);
        
        if (!$forecastCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Forecast tidak ditemukan'
            ], 404);
        }
        
        // Check authorization
        $canCancel = $user->role === 'direktur' || 
                     $user->role === 'manager_purchasing' ||
                     ($user->id == $forecastCheck->purchasing_id);
        
        if (!$canCancel) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk membatalkan forecast ini. Hanya direktur, manager purchasing, dan PIC Purchasing yang dapat membatalkan forecast.'
            ], 403);
        }
        
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
            
            // 1. Create Pengiriman record with NULL values for qty and harga (pembatalan)
            // Format catatan: [Alasan] | Dibatalkan pada: [Tanggal Waktu]
            $catatanPembatalan = $request->alasan_batal . ' | Dibatalkan pada: ' . $timestamp->format('d M Y H:i');
            
            $pengirimanId = DB::table('pengiriman')->insertGetId([
                'purchase_order_id' => $forecast->purchase_order_id,
                'purchasing_id' => $forecast->purchasing_id,
                'forecast_id' => $forecast->id,
                'no_pengiriman' => $noPengiriman,
                'tanggal_kirim' => $forecast->tanggal_forecast,
                'hari_kirim' => $forecast->hari_kirim_forecast,
                'total_qty_kirim' => null, // Set NULL karena pembatalan
                'total_harga_kirim' => null, // Set NULL karena pembatalan
                'status' => 'gagal',
                'catatan' => $catatanPembatalan,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]);
            
            Log::info("Pengiriman created with ID: {$pengirimanId} (status gagal dengan qty dan harga NULL)");

            // 2. Batch insert pengiriman details dengan NULL values
            if ($forecast->forecastDetails->isNotEmpty()) {
                Log::info("Creating pengiriman details with NULL values, count: " . $forecast->forecastDetails->count());
                $pengirimanDetails = [];
                $currentTime = $timestamp->format('Y-m-d H:i:s');
                
                foreach ($forecast->forecastDetails as $detail) {
                    $pengirimanDetails[] = [
                        'pengiriman_id' => $pengirimanId,
                        'purchase_order_bahan_baku_id' => $detail->purchase_order_bahan_baku_id,
                        'bahan_baku_supplier_id' => $detail->bahan_baku_supplier_id,
                        'qty_kirim' => null, // Set NULL karena pembatalan
                        'harga_satuan' => null, // Set NULL karena pembatalan
                        'total_harga' => null, // Set NULL karena pembatalan
                        'catatan_detail' => $detail->catatan_detail ? "PEMBATALAN - Qty Forecast: {$detail->qty_forecast}, Harga Forecast: Rp " . number_format($detail->harga_satuan_forecast, 0, ',', '.') . " | {$detail->catatan_detail}" : "PEMBATALAN - Qty Forecast: {$detail->qty_forecast}, Harga Forecast: Rp " . number_format($detail->harga_satuan_forecast, 0, ',', '.'),
                        'created_at' => $currentTime,
                        'updated_at' => $currentTime
                    ];
                }
                
                // Batch insert for better performance
                Log::info("Inserting " . count($pengirimanDetails) . " pengiriman details");
                DB::table('pengiriman_details')->insert($pengirimanDetails);
                Log::info("Pengiriman details inserted successfully with NULL values");
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

    /**
     * Export pending forecasts to Excel
     */
    public function exportPending(Request $request)
    {
        try {
            $dateRange = $request->input('date_range');
            $purchasing = $request->input('filter_purchasing_pending');
            $search = $request->input('search_pending');
            $hariKirim = $request->input('sort_hari_kirim');

            $fileName = 'forecast_pending_' . now()->format('Y-m-d_His') . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ForecastPendingExport($dateRange, $purchasing, $search, $hariKirim),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Error exporting pending forecasts: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data forecast pending.');
        }
    }

    /**
     * Delete forecast (soft delete)
     */
    public function deleteForecast(Request $request, $id)
    {
        Log::info("deleteForecast called with ID: {$id}");
        Log::info("Request data: " . json_encode($request->all()));
        
        // Authorization: Only direktur, manager_purchasing, and PIC Purchasing can delete forecast
        $user = Auth::user();
        
        // First, get the forecast to check PIC
        $forecast = Forecast::find($id);
        
        if (!$forecast) {
            return response()->json([
                'success' => false,
                'message' => 'Forecast tidak ditemukan'
            ], 404);
        }
        
        // Check authorization
        $canDelete = $user->role === 'direktur' || 
                     $user->role === 'manager_purchasing' ||
                     ($user->id == $forecast->purchasing_id);
        
        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus forecast ini. Hanya direktur, manager purchasing, dan PIC Purchasing yang dapat menghapus forecast.'
            ], 403);
        }
        
        // Check if forecast status is still pending
        if ($forecast->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya forecast dengan status pending yang dapat dihapus'
            ], 400);
        }
        
        // Validation
        try {
            $request->validate([
                'alasan_hapus' => 'required|string|min:10|max:500'
            ], [
                'alasan_hapus.required' => 'Alasan penghapusan harus diisi',
                'alasan_hapus.min' => 'Alasan penghapusan minimal 10 karakter',
                'alasan_hapus.max' => 'Alasan penghapusan maksimal 500 karakter'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed in deleteForecast:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Data yang diinputkan tidak valid',
                'errors' => $e->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            Log::info("Deleting forecast ID: {$id}");
            
            $noForecast = $forecast->no_forecast;
            
            // Log alasan penghapusan untuk audit trail
            Log::info("Forecast {$noForecast} dihapus oleh {$user->nama} dengan alasan: {$request->alasan_hapus}");
            
            // Soft delete forecast details first
            $forecast->forecastDetails()->delete();
            
            // Soft delete forecast
            $forecast->delete();
            
            Log::info("Forecast {$noForecast} berhasil dihapus (soft delete)");
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Forecast berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting forecast - Exception: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ' line: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus forecast: ' . $e->getMessage(),
                'debug_info' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}