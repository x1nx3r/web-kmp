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

class PengirimanController extends Controller
{
    /**
     * Display a listing of the pengiriman.
     */
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
                    });
                });
            }

            // Apply purchasing filter for pengiriman masuk
            if ($status === 'pending' && $request->filled('filter_purchasing')) {
                $query->where('purchasing_id', $request->get('filter_purchasing'));
            }

            // Apply date sorting for pengiriman masuk
            if ($status === 'pending' && $request->filled('sort_date_masuk')) {
                $sortOrder = $request->get('sort_date_masuk') === 'oldest' ? 'asc' : 'desc';
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
}