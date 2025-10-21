<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\PengirimanDetail;
use App\Models\PurchaseOrder;
use App\Models\Klien;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PengirimanController extends Controller
{
    
    public function index(Request $request): View
    {
        // Base query dengan eager loading
        $baseQuery = function($status) use ($request) {
            $query = Pengiriman::with([
                'purchaseOrder:id,no_po,klien_id', 
                'purchaseOrder.klien:id,nama,cabang', 
                'purchasing:id,nama', 
                'pengirimanDetails'
            ])
            ->whereNotNull('purchase_order_id')
            ->whereNotNull('purchasing_id')
            ->where('status', $status);

            // Apply search filter for pengiriman masuk
            if ($status === 'pending' && $request->filled('search_masuk')) {
                $search = $request->get('search_masuk');
                $query->where(function($q) use ($search) {
                    $q->whereHas('purchaseOrder', function($poQuery) use ($search) {
                        $poQuery->where('no_po', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('purchasing', function($purchasingQuery) use ($search) {
                        $purchasingQuery->where('nama', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('no_pengiriman', 'LIKE', "%{$search}%");
                });
            }

            // Apply search filter for pengiriman berhasil
            if ($status === 'berhasil' && $request->filled('search_berhasil')) {
                $search = $request->get('search_berhasil');
                $query->where(function($q) use ($search) {
                    $q->whereHas('purchaseOrder', function($poQuery) use ($search) {
                        $poQuery->where('no_po', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('purchasing', function($purchasingQuery) use ($search) {
                        $purchasingQuery->where('nama', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('no_pengiriman', 'LIKE', "%{$search}%");
                });
            }

            // Apply search filter for pengiriman gagal
            if ($status === 'gagal' && $request->filled('search_gagal')) {
                $search = $request->get('search_gagal');
                $query->where(function($q) use ($search) {
                    $q->whereHas('purchaseOrder', function($poQuery) use ($search) {
                        $poQuery->where('no_po', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('purchasing', function($purchasingQuery) use ($search) {
                        $purchasingQuery->where('nama', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('no_pengiriman', 'LIKE', "%{$search}%");
                });
            }

            // Apply search filter for menunggu verifikasi
            if ($status === 'menunggu_verifikasi' && $request->filled('search_verifikasi')) {
                $search = $request->get('search_verifikasi');
                $query->where(function($q) use ($search) {
                    $q->whereHas('purchaseOrder', function($poQuery) use ($search) {
                        $poQuery->where('no_po', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('purchasing', function($purchasingQuery) use ($search) {
                        $purchasingQuery->where('nama', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('no_pengiriman', 'LIKE', "%{$search}%");
                });
            }

            // Apply purchasing filter for pengiriman masuk
            if ($status === 'pending' && $request->filled('filter_purchasing')) {
                $query->where('purchasing_id', $request->get('filter_purchasing'));
            }

            // Apply purchasing filter for pengiriman berhasil
            if ($status === 'berhasil' && $request->filled('filter_purchasing_berhasil')) {
                $query->where('purchasing_id', $request->get('filter_purchasing_berhasil'));
            }

            // Apply purchasing filter for pengiriman gagal
            if ($status === 'gagal' && $request->filled('filter_purchasing_gagal')) {
                $query->where('purchasing_id', $request->get('filter_purchasing_gagal'));
            }

            // Apply purchasing filter for menunggu verifikasi
            if ($status === 'menunggu_verifikasi' && $request->filled('filter_purchasing_verifikasi')) {
                $query->where('purchasing_id', $request->get('filter_purchasing_verifikasi'));
            }

            // Apply date range filter for pengiriman berhasil
            if ($status === 'berhasil' && $request->filled('date_range_berhasil')) {
                $query->whereDate('tanggal_kirim', $request->get('date_range_berhasil'));
            }

            // Apply date range filter for pengiriman gagal
            if ($status === 'gagal' && $request->filled('date_range_gagal')) {
                $query->whereDate('tanggal_kirim', $request->get('date_range_gagal'));
            }

            // Apply date sorting for pengiriman masuk
            if ($status === 'pending' && $request->filled('sort_date_masuk')) {
                $sortOrder = $request->get('sort_date_masuk') === 'oldest' ? 'asc' : 'desc';
                $query->orderBy('created_at', $sortOrder);
            } 
            // Apply date sorting for pengiriman berhasil
            elseif ($status === 'berhasil' && $request->filled('sort_order_berhasil')) {
                $sortOrder = $request->get('sort_order_berhasil') === 'oldest' ? 'asc' : 'desc';
                $query->orderBy('created_at', $sortOrder);
            } 
            // Apply date sorting for pengiriman gagal
            elseif ($status === 'gagal' && $request->filled('sort_order_gagal')) {
                $sortOrder = $request->get('sort_order_gagal') === 'oldest' ? 'asc' : 'desc';
                $query->orderBy('created_at', $sortOrder);
            } 
            // Apply date sorting for menunggu verifikasi
            elseif ($status === 'menunggu_verifikasi' && $request->filled('sort_date_verifikasi')) {
                $sortOrder = $request->get('sort_date_verifikasi') === 'oldest' ? 'asc' : 'desc';
                $query->orderBy('created_at', $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            return $query;
        };

        // Get data for each status
        $pengirimanMasuk = $baseQuery('pending')->paginate(10, ['*'], 'masuk_page');
        $menungguVerifikasi = $baseQuery('menunggu_verifikasi')->paginate(10, ['*'], 'verifikasi_page');
        $pengirimanBerhasil = $baseQuery('berhasil')->paginate(10, ['*'], 'berhasil_page');
        $pengirimanGagal = $baseQuery('gagal')->paginate(10, ['*'], 'gagal_page');

        return view('pages.purchasing.pengiriman', compact(
            'pengirimanMasuk', 
            'menungguVerifikasi', 
            'pengirimanBerhasil', 
            'pengirimanGagal'
        ));
    }

    /**
     * Show the form for creating a new pengiriman.
     */
    public function create(): View
    {
        $klien = Klien::all();
        $purchaseOrders = PurchaseOrder::where('status', 'approved')->get();

        return view('pages.purchasing.pengiriman-create', compact('klien', 'purchaseOrders'));
    }

    /**
     * Store a newly created pengiriman in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'klien_id' => 'required|exists:klien,id',
            'tanggal_pengiriman' => 'required|date',
            'status' => 'required|in:pending,in_transit,delivered,cancelled',
            'keterangan' => 'nullable|string',
            'details' => 'required|array',
            'details.*.bahan_baku_id' => 'required|exists:bahan_baku_klien,id',
            'details.*.jumlah' => 'required|numeric|min:0',
            'details.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $pengiriman = Pengiriman::create([
            'purchase_order_id' => $validated['purchase_order_id'],
            'klien_id' => $validated['klien_id'],
            'tanggal_pengiriman' => $validated['tanggal_pengiriman'],
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'],
            'total_amount' => 0, // Will be calculated after adding details
        ]);

        $totalAmount = 0;

        foreach ($validated['details'] as $detail) {
            $subtotal = $detail['jumlah'] * $detail['harga_satuan'];
            $totalAmount += $subtotal;

            PengirimanDetail::create([
                'pengiriman_id' => $pengiriman->id,
                'bahan_baku_id' => $detail['bahan_baku_id'],
                'jumlah' => $detail['jumlah'],
                'harga_satuan' => $detail['harga_satuan'],
                'subtotal' => $subtotal,
            ]);
        }

        $pengiriman->update(['total_amount' => $totalAmount]);

        return redirect()->route('purchasing.pengiriman.index')
            ->with('success', 'Data pengiriman berhasil dibuat.');
    }

    /**
     * Display the specified pengiriman.
     */
    public function show(Pengiriman $pengiriman): View
    {
        $pengiriman->load(['klien', 'purchaseOrder', 'details.bahanBaku']);

        return view('pages.purchasing.pengiriman-show', compact('pengiriman'));
    }

    /**
     * Show the form for editing the specified pengiriman.
     */
    public function edit(Pengiriman $pengiriman): View
    {
        $pengiriman->load(['details']);
        $klien = Klien::all();
        $purchaseOrders = PurchaseOrder::where('status', 'approved')->get();

        return view('pages.purchasing.pengiriman-edit', compact('pengiriman', 'klien', 'purchaseOrders'));
    }

    /**
     * Update the specified pengiriman in storage.
     */
    public function update(Request $request, Pengiriman $pengiriman): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'klien_id' => 'required|exists:klien,id',
            'tanggal_pengiriman' => 'required|date',
            'status' => 'required|in:pending,in_transit,delivered,cancelled',
            'keterangan' => 'nullable|string',
            'details' => 'required|array',
            'details.*.bahan_baku_id' => 'required|exists:bahan_baku_klien,id',
            'details.*.jumlah' => 'required|numeric|min:0',
            'details.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $pengiriman->update([
            'purchase_order_id' => $validated['purchase_order_id'],
            'klien_id' => $validated['klien_id'],
            'tanggal_pengiriman' => $validated['tanggal_pengiriman'],
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'],
        ]);

        // Delete existing details
        $pengiriman->details()->delete();

        $totalAmount = 0;

        // Create new details
        foreach ($validated['details'] as $detail) {
            $subtotal = $detail['jumlah'] * $detail['harga_satuan'];
            $totalAmount += $subtotal;

            PengirimanDetail::create([
                'pengiriman_id' => $pengiriman->id,
                'bahan_baku_id' => $detail['bahan_baku_id'],
                'jumlah' => $detail['jumlah'],
                'harga_satuan' => $detail['harga_satuan'],
                'subtotal' => $subtotal,
            ]);
        }

        $pengiriman->update(['total_amount' => $totalAmount]);

        return redirect()->route('purchasing.pengiriman.index')
            ->with('success', 'Data pengiriman berhasil diperbarui.');
    }

    /**
     * Remove the specified pengiriman from storage.
     */
    public function destroy(Pengiriman $pengiriman): RedirectResponse
    {
        $pengiriman->details()->delete();
        $pengiriman->delete();

        return redirect()->route('purchasing.pengiriman.index')
            ->with('success', 'Data pengiriman berhasil dihapus.');
    }

    /**
     * Update status pengiriman
     */
    public function updateStatus(Request $request, Pengiriman $pengiriman)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,menunggu_verifikasi,berhasil,gagal',
            'catatan' => 'nullable|string'
        ]);

        $pengiriman->status = $validated['status'];
        if (isset($validated['catatan'])) {
            $pengiriman->catatan = $validated['catatan'];
        }
        $pengiriman->save();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pengiriman berhasil diperbarui',
                'data' => $pengiriman
            ]);
        }

        return redirect()->back()
            ->with('success', 'Status pengiriman berhasil diperbarui.');
    }


    
    /**
     * Get pengiriman detail via AJAX
     */
    public function getDetail(Request $request, $id)
    {
        try {
            $pengiriman = Pengiriman::with([
                'purchaseOrder', 
                'purchaseOrder.klien', 
                'purchasing', 
                'pengirimanDetails'
            ])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'pengiriman' => $pengiriman
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get modal aksi content for pengiriman
     */
    public function getAksiModal(Request $request, $id)
    {
        try {
            Log::info("Loading aksi modal for pengiriman ID: {$id}");
            
            // Load step by step to debug relationship issues
            $pengiriman = Pengiriman::with([
                'purchaseOrder', 
                'purchaseOrder.klien', 
                'purchasing', 
                'forecast',
                'pengirimanDetails.bahanBakuSupplier',
                'pengirimanDetails.bahanBakuSupplier.supplier'
            ])->findOrFail($id);

            Log::info("Pengiriman loaded with " . $pengiriman->pengirimanDetails->count() . " details");

            // Load picPurchasing separately to avoid chain issues
            foreach ($pengiriman->pengirimanDetails as $detail) {
                if ($detail->bahanBakuSupplier && $detail->bahanBakuSupplier->supplier) {
                    $detail->bahanBakuSupplier->supplier->load('picPurchasing');
                }
            }

            // Load riwayat harga
            foreach ($pengiriman->pengirimanDetails as $detail) {
                if ($detail->bahanBakuSupplier) {
                    $detail->bahanBakuSupplier->load(['riwayatHarga' => function($query) {
                        $query->latest('tanggal_perubahan')->limit(1);
                    }]);
                }
            }

            // Debug: Log pengiriman details
            Log::info("Pengiriman details data:", [
                'id' => $pengiriman->id,
                'no_pengiriman' => $pengiriman->no_pengiriman,
                'tanggal_kirim' => $pengiriman->tanggal_kirim,
                'hari_kirim' => $pengiriman->hari_kirim,
                'details_count' => $pengiriman->pengirimanDetails->count(),
                'first_detail' => $pengiriman->pengirimanDetails->first() ? [
                    'id' => $pengiriman->pengirimanDetails->first()->id,
                    'qty_kirim' => $pengiriman->pengirimanDetails->first()->qty_kirim,
                    'bahan_baku' => $pengiriman->pengirimanDetails->first()->bahanBakuSupplier->nama ?? 'N/A'
                ] : null
            ]);
            
            // Return HTML content for modal
            return view('pages.purchasing.pengiriman.pengiriman-masuk.detail', compact('pengiriman'));
            
        } catch (\Exception $e) {
            Log::error('Error in getAksiModal: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '<br><small>' . $e->getFile() . ':' . $e->getLine() . '</small></div>', 500);
        }
    }

    /**
     * Show submit modal for pengiriman confirmation
     */
    public function getSubmitModal(Request $request)
    {
        try {
            $pengiriman = Pengiriman::with([
                'purchaseOrder', 
                'purchaseOrder.klien', 
                'purchasing', 
                'forecast'
            ])->findOrFail($request->get('pengiriman_id', 1)); // Default to 1 for testing
            
            return view('pages.purchasing.pengiriman.pengiriman-masuk.submit', compact('pengiriman'));
            
        } catch (\Exception $e) {
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }

    /**
     * Store pengiriman data (Submit for verification)
     */
    public function submitPengiriman(Request $request)
    {
        try {
            // Validate request
            $validatedData = $request->validate([
                'pengiriman_id' => 'required|exists:pengiriman,id',
                'no_pengiriman' => 'required|string|max:255',
                'tanggal_kirim' => 'required|date',
                'hari_kirim' => 'required|string',
                'total_qty_kirim' => 'required|numeric|min:0',
                'total_harga_kirim' => 'required|numeric|min:0',
                'bukti_foto_bongkar.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'catatan' => 'nullable|string',
                'details' => 'required|array|min:1',
                'details.*.bahan_baku_supplier_id' => 'required|exists:bahan_baku_supplier,id',
                'details.*.qty_kirim' => 'required|numeric|min:0',
                'details.*.harga_satuan' => 'required|numeric|min:0',
                'details.*.total_harga' => 'required|numeric|min:0',
            ], [
                'pengiriman_id.required' => 'ID pengiriman diperlukan',
                'pengiriman_id.exists' => 'Pengiriman tidak ditemukan',
                'no_pengiriman.required' => 'Nomor pengiriman harus diisi',
                'tanggal_kirim.required' => 'Tanggal kirim harus diisi',
                'tanggal_kirim.date' => 'Format tanggal kirim tidak valid',
                'total_qty_kirim.required' => 'Total qty kirim harus diisi',
                'total_harga_kirim.required' => 'Total harga kirim harus diisi',
                'details.required' => 'Detail barang harus diisi',
                'details.min' => 'Minimal satu detail barang harus diisi',
                'details.*.bahan_baku_supplier_id.required' => 'Bahan baku harus dipilih',
                'details.*.qty_kirim.required' => 'Qty kirim harus diisi',
                'details.*.harga_satuan.required' => 'Harga satuan harus diisi',
            ]);

            // Begin transaction
            DB::beginTransaction();

            // Update pengiriman
            $pengiriman = Pengiriman::findOrFail($validatedData['pengiriman_id']);
            
            // Handle multiple file upload with old file deletion
            $buktiFileNames = $pengiriman->bukti_foto_bongkar; // Keep existing files if no new files
            
            if ($request->hasFile('bukti_foto_bongkar')) {
                // Delete all old photos if exists
                if ($pengiriman->bukti_foto_bongkar) {
                    $oldPhotos = is_array($pengiriman->bukti_foto_bongkar) 
                        ? $pengiriman->bukti_foto_bongkar 
                        : [$pengiriman->bukti_foto_bongkar];
                        
                    foreach ($oldPhotos as $oldPhoto) {
                        if (Storage::disk('public')->exists('pengiriman/bukti/' . $oldPhoto)) {
                            Storage::disk('public')->delete('pengiriman/bukti/' . $oldPhoto);
                        }
                    }
                }
                
                // Upload new files
                $buktiFileNames = [];
                $files = $request->file('bukti_foto_bongkar');
                
                // Handle single or multiple files
                if (!is_array($files)) {
                    $files = [$files];
                }
                
                foreach ($files as $index => $file) {
                    if ($file && $file->isValid()) {
                        $buktiFileName = 'pengiriman_' . $pengiriman->id . '_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                        $file->storeAs('pengiriman/bukti', $buktiFileName, 'public');
                        $buktiFileNames[] = $buktiFileName;
                    }
                }
            }

            // Update pengiriman data
            $pengiriman->update([
                'no_pengiriman' => $validatedData['no_pengiriman'],
                'tanggal_kirim' => $validatedData['tanggal_kirim'],
                'hari_kirim' => $validatedData['hari_kirim'],
                'total_qty_kirim' => $validatedData['total_qty_kirim'],
                'total_harga_kirim' => $validatedData['total_harga_kirim'],
                'bukti_foto_bongkar' => $buktiFileNames, // Store array of filenames
                'catatan' => $validatedData['catatan'],
                'status' => 'menunggu_verifikasi'
            ]);

            // Update existing details (don't delete and recreate)
            foreach ($validatedData['details'] as $index => $detail) {
                // Find existing detail by index (assuming index matches existing detail order)
                $existingDetail = $pengiriman->pengirimanDetails->get($index);
                
                if ($existingDetail) {
                    // Update existing detail
                    $existingDetail->update([
                        'qty_kirim' => $detail['qty_kirim'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'total_harga' => $detail['total_harga'],
                    ]);
                } else {
                    // If detail doesn't exist (shouldn't happen in this case), create new
                    $bahanBakuSupplier = \App\Models\BahanBakuSupplier::find($detail['bahan_baku_supplier_id']);
                    
                    $poDetail = null;
                    if ($bahanBakuSupplier) {
                        $poDetail = \App\Models\PurchaseOrderBahanBaku::where('purchase_order_id', $pengiriman->purchase_order_id)
                            ->whereHas('bahanBakuKlien', function($query) use ($bahanBakuSupplier) {
                                $query->where('nama', $bahanBakuSupplier->nama);
                            })
                            ->first();
                        
                        if (!$poDetail) {
                            $poDetail = \App\Models\PurchaseOrderBahanBaku::where('purchase_order_id', $pengiriman->purchase_order_id)
                                ->first();
                        }
                    }
                    
                    PengirimanDetail::create([
                        'pengiriman_id' => $pengiriman->id,
                        'purchase_order_bahan_baku_id' => $poDetail ? $poDetail->id : null,
                        'bahan_baku_supplier_id' => $detail['bahan_baku_supplier_id'],
                        'qty_kirim' => $detail['qty_kirim'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'total_harga' => $detail['total_harga'],
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil diajukan untuk verifikasi',
                'no_pengiriman' => $pengiriman->no_pengiriman,
                'pengiriman' => $pengiriman
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bahan baku harga for AJAX requests
     */
    public function getBahanBakuHarga($id)
    {
        try {
            $bahanBaku = \App\Models\BahanBakuSupplier::with(['riwayatHarga' => function($query) {
                $query->latest('tanggal_perubahan')->limit(1);
            }])->findOrFail($id);
            
            $latestHarga = $bahanBaku->riwayatHarga->first();
            $harga = $latestHarga ? $latestHarga->harga_baru : $bahanBaku->harga_per_satuan;
            
            return response()->json([
                'success' => true,
                'harga' => $harga,
                'nama_bahan_baku' => $bahanBaku->nama
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Show batal modal for pengiriman confirmation
     */
    public function getBatalModal(Request $request)
    {
        try {
            $pengiriman = Pengiriman::with([
                'purchaseOrder', 
                'purchaseOrder.klien', 
                'purchasing', 
                'forecast'
            ])->findOrFail($request->get('pengiriman_id'));
            
            return view('pages.purchasing.pengiriman.pengiriman-masuk.batal', compact('pengiriman'));
            
        } catch (\Exception $e) {
            return response('<div class="text-center py-8 text-red-500">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }

    /**
     * Cancel pengiriman with catatan only
     */
    public function batalPengiriman(Request $request)
    {
        try {
            // Validate request - only catatan is allowed to be updated
            $validatedData = $request->validate([
                'pengiriman_id' => 'required|exists:pengiriman,id',
                'catatan' => 'required|string|max:1000',
                'alasan_batal' => 'required|string|max:500'
            ], [
                'pengiriman_id.required' => 'ID pengiriman diperlukan',
                'pengiriman_id.exists' => 'Pengiriman tidak ditemukan',
                'catatan.required' => 'Catatan pembatalan harus diisi',
                'catatan.max' => 'Catatan tidak boleh lebih dari 1000 karakter',
                'alasan_batal.required' => 'Alasan pembatalan harus diisi',
                'alasan_batal.max' => 'Alasan pembatalan tidak boleh lebih dari 500 karakter'
            ]);

            // Begin transaction
            DB::beginTransaction();

            // Update only catatan and status to 'batal'
            $pengiriman = Pengiriman::findOrFail($validatedData['pengiriman_id']);
            
            // Combine existing catatan with cancellation reason
            $newCatatan = $validatedData['catatan'] . "\n\n[PEMBATALAN]\n" . $validatedData['alasan_batal'] . "\n[Dibatalkan pada: " . now()->format('d M Y H:i') . "]";
            
            $pengiriman->update([
                'catatan' => $newCatatan,
                'status' => 'gagal'
            ]);

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil dibatalkan',
                'no_pengiriman' => $pengiriman->no_pengiriman,
                'pengiriman' => $pengiriman
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detail for pengiriman berhasil
     */
    public function getDetailBerhasil($id)
    {
        try {
            $pengiriman = Pengiriman::with([
                'purchaseOrder',
                'purchaseOrder.klien',
                'purchasing',
                'pengirimanDetails.bahanBakuSupplier',
                'pengirimanDetails.bahanBakuSupplier.supplier'
            ])->where('status', 'berhasil')->findOrFail($id);

            // Format data for response
            $data = [
                'id' => $pengiriman->id,
                'no_pengiriman' => $pengiriman->no_pengiriman,
                'status' => ucfirst($pengiriman->status),
                'no_po' => $pengiriman->purchaseOrder->no_po ?? '-',
                'pic_purchasing' => $pengiriman->purchasing->nama ?? '-',
                'tanggal_kirim' => $pengiriman->tanggal_kirim ? Carbon::parse($pengiriman->tanggal_kirim)->format('d F Y') : '-',
                'hari_kirim' => $pengiriman->hari_kirim ?? '-',
                'total_qty' => number_format($pengiriman->total_qty_kirim ?? 0, 0, ',', '.') . ' kg',
                'total_harga' => 'Rp ' . number_format($pengiriman->total_harga_kirim ?? 0, 0, ',', '.'),
                'total_items' => $pengiriman->pengirimanDetails ? $pengiriman->pengirimanDetails->count() : 0,
                'catatan' => $pengiriman->catatan,
                'rating' => $pengiriman->rating,
                'ulasan' => $pengiriman->ulasan,
                'bukti_foto_bongkar' => $pengiriman->bukti_foto_bongkar_array ?? [],
                'bukti_foto_urls' => $pengiriman->bukti_foto_bongkar_url ?? [],
                'details' => $pengiriman->pengirimanDetails ? $pengiriman->pengirimanDetails->map(function($detail) {
                    return [
                        'bahan_baku' => $detail->bahanBakuSupplier->nama ?? '-',
                        'supplier' => $detail->bahanBakuSupplier->supplier->nama ?? '-',
                        'qty_kirim' => $detail->qty_kirim,
                        'harga_satuan' => $detail->harga_satuan,
                        'total_harga' => $detail->total_harga
                    ];
                }) : []
            ];

            return response()->json([
                'success' => true,
                'pengiriman' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detail for pengiriman gagal
     */
    public function getDetailGagal($id)
    {
        try {
            $pengiriman = Pengiriman::with([
                'purchaseOrder',
                'purchaseOrder.klien',
                'purchasing',
                'pengirimanDetails.bahanBakuSupplier',
                'pengirimanDetails.bahanBakuSupplier.supplier'
            ])->where('status', 'gagal')->findOrFail($id);

            // Format data for response
            $data = [
                'id' => $pengiriman->id,
                'no_pengiriman' => $pengiriman->no_pengiriman,
                'status' => ucfirst($pengiriman->status),
                'no_po' => $pengiriman->purchaseOrder->no_po ?? '-',
                'pic_purchasing' => $pengiriman->purchasing->nama ?? '-',
                'tanggal_kirim' => $pengiriman->tanggal_kirim ? Carbon::parse($pengiriman->tanggal_kirim)->format('d F Y') : '-',
                'hari_kirim' => $pengiriman->hari_kirim ?? '-',
                'total_qty' => number_format($pengiriman->total_qty_kirim ?? 0, 0, ',', '.') . ' kg',
                'total_harga' => 'Rp ' . number_format($pengiriman->total_harga_kirim ?? 0, 0, ',', '.'),
                'total_items' => $pengiriman->pengirimanDetails ? $pengiriman->pengirimanDetails->count() : 0,
                'catatan' => $pengiriman->catatan,
                'details' => $pengiriman->pengirimanDetails ? $pengiriman->pengirimanDetails->map(function($detail) {
                    return [
                        'bahan_baku' => $detail->bahanBakuSupplier->nama ?? '-',
                        'supplier' => $detail->bahanBakuSupplier->supplier->nama ?? '-',
                        'qty_kirim' => $detail->qty_kirim,
                        'harga_satuan' => $detail->harga_satuan,
                        'total_harga' => $detail->total_harga
                    ];
                }) : []
            ];

            return response()->json([
                'success' => true,
                'pengiriman' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detail for pengiriman verifikasi
     */
    public function getDetailVerifikasi($id)
    {
        try {
            // Load pengiriman dengan relasi basic terlebih dahulu
            $pengiriman = Pengiriman::where('status', 'menunggu_verifikasi')->findOrFail($id);
            
            // Load relasi satu per satu untuk menghindari timeout
            $pengiriman->load('purchasing');
            $pengiriman->load('forecast');  
            $pengiriman->load('purchaseOrder.klien');
            $pengiriman->load('pengirimanDetails');
            
            // Debug logging untuk troubleshoot
            Log::info('Debug Pengiriman Detail:', [
                'pengiriman_id' => $pengiriman->id,
                'purchase_order_id' => $pengiriman->purchase_order_id,
                'has_purchase_order' => $pengiriman->purchaseOrder ? 'YES' : 'NO',
                'qty_total' => $pengiriman->purchaseOrder?->qty_total,
                'total_amount' => $pengiriman->purchaseOrder?->total_amount,
            ]);

            return view('pages.purchasing.pengiriman.menunggu-verifikasi.detail', compact('pengiriman'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Pengiriman not found with ID: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak ditemukan atau bukan status menunggu verifikasi'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in getDetailVerifikasi for ID ' . $id . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifikasi pengiriman - ubah status menjadi berhasil dan kurangi qty PO
     */
    public function verifikasiPengiriman($id)
    {
        try {
            DB::beginTransaction();

            $pengiriman = Pengiriman::with([
                'purchaseOrder',
                'pengirimanDetails.purchaseOrderBahanBaku'
            ])->where('status', 'menunggu_verifikasi')->findOrFail($id);

            // Update status pengiriman menjadi berhasil
            $pengiriman->update([
                'status' => 'berhasil',
                'catatan' => 'berhasil'
            ]);

            // Kurangi qty di purchase_order_bahan_baku untuk setiap detail
            foreach ($pengiriman->pengirimanDetails as $detail) {
                $purchaseOrderBahanBaku = $detail->purchaseOrderBahanBaku;
                if ($purchaseOrderBahanBaku) {
                    $newQty = $purchaseOrderBahanBaku->jumlah - $detail->qty_kirim;
                    $purchaseOrderBahanBaku->update([
                        'jumlah' => max(0, $newQty) // Pastikan tidak negatif
                    ]);
                }
            }

            // Update qty_total di purchase_order
            if ($pengiriman->purchaseOrder) {
                $newQtyTotal = $pengiriman->purchaseOrder->qty_total - $pengiriman->total_qty_kirim;
                $pengiriman->purchaseOrder->update([
                    'qty_total' => max(0, $newQtyTotal) // Pastikan tidak negatif
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil diverifikasi dan catatan diperbarui menjadi "berhasil"'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revisi pengiriman - ubah status menjadi pending dan update catatan
     */
    public function revisiPengiriman(Request $request, $id)
    {
        try {
            $request->validate([
                'catatan' => 'required|string|max:1000',
                'hapus_foto' => 'nullable|boolean'
            ]);

            $pengiriman = Pengiriman::where('status', 'menunggu_verifikasi')->findOrFail($id);

            // Jika diminta untuk hapus foto, hapus dari storage
            if ($request->get('hapus_foto', false) && $pengiriman->bukti_foto_bongkar) {
                $oldPhotos = is_array($pengiriman->bukti_foto_bongkar) 
                    ? $pengiriman->bukti_foto_bongkar 
                    : [$pengiriman->bukti_foto_bongkar];
                    
                foreach ($oldPhotos as $oldPhoto) {
                    if (Storage::disk('public')->exists('pengiriman/bukti/' . $oldPhoto)) {
                        Storage::disk('public')->delete('pengiriman/bukti/' . $oldPhoto);
                    }
                }
                
                $pengiriman->update([
                    'status' => 'pending',
                    'catatan' => $request->catatan,
                    'bukti_foto_bongkar' => null
                ]);
            } else {
                $pengiriman->update([
                    'status' => 'pending',
                    'catatan' => $request->catatan
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil direvisi dan dikembalikan ke status pending'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal merevisi pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revisi modal for pengiriman verifikasi
     */
    public function getRevisiModal($id)
    {
        try {
            // First, get the pengiriman with basic info
            $pengiriman = Pengiriman::where('status', 'menunggu_verifikasi')->findOrFail($id);
            
            // Load relations step by step with error handling
            try {
                $pengiriman->load('purchasing');
            } catch (\Exception $e) {
                Log::error('Error loading purchasing relation in revisi modal: ' . $e->getMessage());
            }

            try {
                $pengiriman->load([
                    'purchaseOrder' => function($query) {
                        $query->with('klien');
                    }
                ]);
            } catch (\Exception $e) {
                Log::error('Error loading purchaseOrder relation in revisi modal: ' . $e->getMessage());
            }

            return view('pages.purchasing.pengiriman.menunggu-verifikasi.revisi', compact('pengiriman'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Pengiriman not found for revisi modal with ID: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak ditemukan atau bukan status menunggu verifikasi'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in getRevisiModal for ID ' . $id . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat modal revisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verifikasi modal for pengiriman verifikasi
     */
    public function getVerifikasiModal($id)
    {
        try {
            // First, get the pengiriman with basic info
            $pengiriman = Pengiriman::where('status', 'menunggu_verifikasi')->findOrFail($id);
            
            // Load relations step by step with error handling
            try {
                $pengiriman->load('purchasing');
            } catch (\Exception $e) {
                Log::error('Error loading purchasing relation in verifikasi modal: ' . $e->getMessage());
            }

            try {
                $pengiriman->load([
                    'purchaseOrder' => function($query) {
                        $query->with('klien');
                    }
                ]);
            } catch (\Exception $e) {
                Log::error('Error loading purchaseOrder relation in verifikasi modal: ' . $e->getMessage());
            }
            
            // Load pengiriman details with safer approach
            try {
                $pengiriman->load([
                    'pengirimanDetails' => function($query) {
                        $query->with([
                            'purchaseOrderBahanBaku',
                            'bahanBakuSupplier' => function($q) {
                                $q->with('supplier');
                            }
                        ]);
                    }
                ]);
            } catch (\Exception $e) {
                Log::error('Error loading pengirimanDetails relation in verifikasi modal: ' . $e->getMessage());
                // Load pengirimanDetails tanpa relasi bersarang sebagai fallback
                $pengiriman->load('pengirimanDetails');
            }

            return view('pages.purchasing.pengiriman.menunggu-verifikasi.verifikasi', compact('pengiriman'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Pengiriman not found for verifikasi modal with ID: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak ditemukan atau bukan status menunggu verifikasi'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in getVerifikasiModal for ID ' . $id . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat modal verifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan review pengiriman
     */
    public function storeReview(Request $request)
    {
        try {
            $request->validate([
                'pengiriman_id' => 'required|exists:pengiriman,id',
                'rating' => 'required|integer|min:1|max:5',
                'ulasan' => 'nullable|string|max:1000'
            ]);

            $pengiriman = Pengiriman::findOrFail($request->pengiriman_id);
            
            // Update pengiriman dengan review
            $pengiriman->update([
                'rating' => $request->rating,
                'ulasan' => $request->ulasan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review berhasil disimpan!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing review: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update review pengiriman yang sudah ada
     */
    public function updateReview(Request $request, $id)
    {
        try {
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'ulasan' => 'nullable|string|max:1000'
            ]);

            $pengiriman = Pengiriman::findOrFail($id);
            
            // Update review
            $pengiriman->update([
                'rating' => $request->rating,
                'ulasan' => $request->ulasan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review berhasil diperbarui!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating review: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui review: ' . $e->getMessage()
            ], 500);
        }
    }
}