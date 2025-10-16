<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\Supplier;
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kliens = Klien::orderBy('nama')->get();
        $materials = BahanBakuKlien::with('klien')->orderBy('nama')->get();
        $suppliers = Supplier::orderBy('nama')->get();
        
        return view('pages.marketing.orders.create', compact('kliens', 'materials', 'suppliers'));
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
            'created_by' => Auth::id(),
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['klien', 'creator', 'orderDetails.bahanBakuKlien', 'orderDetails.supplier'])
            ->findOrFail($id);
            
        return view('pages.marketing.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $order = Order::with(['orderDetails.bahanBakuKlien', 'orderDetails.supplier'])
            ->findOrFail($id);
            
        if ($order->status !== 'draft') {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Hanya order dengan status draft yang dapat diedit.');
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
