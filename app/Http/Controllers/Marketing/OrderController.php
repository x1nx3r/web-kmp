<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\BahanBakuSupplier;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuthFallbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.marketing.orders.index');
    }

    /**
     * Return top suppliers for a given client material (limit 5), ordered by supplier price asc.
     * Used by the order create page to auto-populate supplier rows.
     */
    public function getSuppliersForMaterial($materialId)
    {
        $material = BahanBakuKlien::findOrFail($materialId);

        $suppliers = BahanBakuSupplier::with(['supplier.picPurchasing'])
            ->where('nama', 'like', '%' . $material->nama . '%')
            ->whereNotNull('harga_per_satuan')
            ->orderBy('harga_per_satuan', 'asc')
            ->limit(5)
            ->get();

        // Mirror Penawaran's supplier option shape but include supplier_table_id for convenience
        $result = $suppliers->map(function ($s) {
            return [
                // canonical keys:
                // - bahan_baku_supplier_id => id of the supplier-material (BahanBakuSupplier)
                // - supplier_id => id from suppliers table (for selects and validation)
                'supplier_name' => $s->supplier ? $s->supplier->nama : null,
                'pic_name' => $s->supplier && $s->supplier->picPurchasing ? $s->supplier->picPurchasing->nama : null,
                'bahan_baku_supplier_id' => $s->id,
                'supplier_id' => $s->supplier ? $s->supplier->id : null,
                'price' => (float) $s->harga_per_satuan,
                'satuan' => $s->satuan,
                'stok' => (float) $s->stok,
            ];
        });

        return response()->json(['data' => $result]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.marketing.orders.create-livewire');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'klien_id' => 'required|exists:kliens,id',
            'tanggal_order' => 'required|date',
            'priority' => 'required|in:rendah,normal,tinggi,mendesak',
            'catatan' => 'nullable|string',
            'order_details' => 'required|array|min:1',
            'order_details.*.bahan_baku_klien_id' => 'required|exists:bahan_baku_klien,id',
            'order_details.*.supplier_id' => 'required|exists:suppliers,id',
            'order_details.*.qty' => 'required|numeric|min:0.01',
            'order_details.*.satuan' => 'required|string|max:20',
            'order_details.*.harga_supplier' => 'required|numeric|min:0',
            'order_details.*.harga_jual' => 'required|numeric|min:0',
            'order_details.*.spesifikasi_khusus' => 'nullable|string',
            'order_details.*.catatan' => 'nullable|string',
        ]);

        $order = Order::create([
            'klien_id' => $request->klien_id,
            'created_by' => AuthFallbackService::id(),
            'tanggal_order' => $request->tanggal_order,
            'priority' => $request->priority,
            'catatan' => $request->catatan,
        ]);

        foreach ($request->order_details as $detail) {
            $order->orderDetails()->create($detail);
        }

        // Calculate totals
        $order->calculateTotals();

        return redirect()->route('orders.index')->with('success', 'Order berhasil dibuat.');
    }

    /**
     * Return an authenticated user id or a safe fallback user id for dev/test flows.
     * This is temporary until a full auth system is implemented.
     *
     * @return int|null
     */
    private function getFallbackUserId()
    {
        $user = auth()->user();
        if ($user) {
            return $user->id;
        }

        // Prefer a dedicated system user if present
        $fallback = User::where('email', 'system@local')->first();
        if ($fallback) {
            return $fallback->id;
        }

        // Otherwise return the first existing user in the DB
        $first = User::first();
        if ($first) {
            return $first->id;
        }

        // As a last resort, create a temporary system user in non-production
        try {
            if (!app()->environment('production')) {
                $created = User::create([
                    'name' => 'System (dev)',
                    'email' => 'system@local',
                    'password' => bcrypt(bin2hex(random_bytes(8))),
                ]);

                return $created->id;
            }
        } catch (\Exception $e) {
            // ignore and fall through to null
        }

        return null;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with([
            'klien', 
            'creator', 
            'orderDetails.bahanBakuKlien', 
            'orderDetails.orderSuppliers.supplier',
            'orderDetails.orderSuppliers.bahanBakuSupplier'
        ])->findOrFail($id);
            
        return view('pages.marketing.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $order = Order::with([
            'orderDetails.bahanBakuKlien', 
            'orderDetails.orderSuppliers.supplier',
            'orderDetails.orderSuppliers.bahanBakuSupplier'
        ])->findOrFail($id);
            
        if ($order->status !== 'draft') {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Hanya order dengan status draft yang dapat diedit.');
        }

        // Check if this order uses the new multi-supplier system
        $hasMultiSupplierData = $order->orderDetails()
            ->whereHas('orderSuppliers')
            ->exists();
            
        if ($hasMultiSupplierData) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Order ini menggunakan sistem multi-supplier baru dan tidak dapat diedit dengan interface lama. Silakan gunakan interface order baru.');
        }
        
        $kliens = Klien::orderBy('nama')->get();
        $materials = BahanBakuKlien::with('klien')->orderBy('nama')->get();
        $suppliers = Supplier::orderBy('nama')->get();
        
        return view('pages.marketing.orders.edit', compact('order', 'kliens', 'materials', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status !== 'draft') {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Hanya order dengan status draft yang dapat diupdate.');
        }

        $request->validate([
            'klien_id' => 'required|exists:kliens,id',
            'tanggal_order' => 'required|date',
            'priority' => 'required|in:rendah,normal,tinggi,mendesak',
            'catatan' => 'nullable|string',
            'order_details' => 'required|array|min:1',
            'order_details.*.bahan_baku_klien_id' => 'required|exists:bahan_baku_klien,id',
            'order_details.*.supplier_id' => 'required|exists:suppliers,id',
            'order_details.*.qty' => 'required|numeric|min:0.01',
            'order_details.*.satuan' => 'required|string|max:20',
            'order_details.*.harga_supplier' => 'required|numeric|min:0',
            'order_details.*.harga_jual' => 'required|numeric|min:0',
            'order_details.*.spesifikasi_khusus' => 'nullable|string',
            'order_details.*.catatan' => 'nullable|string',
        ]);

        $order->update([
            'klien_id' => $request->klien_id,
            'tanggal_order' => $request->tanggal_order,
            'priority' => $request->priority,
            'catatan' => $request->catatan,
        ]);

        // Delete existing details and create new ones
        $order->orderDetails()->delete();
        
        foreach ($request->order_details as $detail) {
            $order->orderDetails()->create($detail);
        }

        // Recalculate totals
        $order->calculateTotals();

        return redirect()->route('orders.show', $order->id)->with('success', 'Order berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status !== 'draft') {
            return redirect()->route('orders.index')
                ->with('error', 'Hanya order dengan status draft yang dapat dihapus.');
        }

        $order->delete();

        return redirect()->route('orders.index')->with('success', 'Order berhasil dihapus.');
    }

    /**
     * Confirm an order (change status from draft to dikonfirmasi)
     */
    public function confirm(string $id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status !== 'draft') {
            return redirect()->back()->with('error', 'Order sudah tidak dalam status draft.');
        }

        $order->confirm();

        return redirect()->back()->with('success', 'Order berhasil dikonfirmasi.');
    }

    /**
     * Start processing an order (change status to diproses)
     */
    public function startProcessing(string $id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status !== 'dikonfirmasi') {
            return redirect()->back()->with('error', 'Order harus dalam status dikonfirmasi untuk dapat diproses.');
        }

        $order->startProcessing();

        return redirect()->back()->with('success', 'Order mulai diproses.');
    }

    /**
     * Complete an order (change status to selesai)
     */
    public function complete(string $id)
    {
        $order = Order::findOrFail($id);
        
        if (!in_array($order->status, ['diproses', 'sebagian_dikirim'])) {
            return redirect()->back()->with('error', 'Order harus dalam status diproses atau sebagian dikirim untuk dapat diselesaikan.');
        }

        $order->complete();

        return redirect()->back()->with('success', 'Order berhasil diselesaikan.');
    }

    /**
     * Cancel an order
     */
    public function cancel(string $id)
    {
        $order = Order::findOrFail($id);
        
        if (in_array($order->status, ['selesai', 'dibatalkan'])) {
            return redirect()->back()->with('error', 'Order sudah selesai atau sudah dibatalkan.');
        }

        $order->cancel();

        return redirect()->back()->with('success', 'Order berhasil dibatalkan.');
    }
}
