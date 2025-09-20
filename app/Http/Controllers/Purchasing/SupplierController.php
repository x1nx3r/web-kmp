<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with('bahanBakuSuppliers');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        $suppliers = $query->orderBy('nama', 'asc')->paginate(5);

        return view('pages.purchasing.supplier', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.purchasing.supplier.tambah');
    }

    // Method untuk store, update, delete bisa ditambahkan nanti
    public function store(Request $request)
    {
        // Akan diimplementasi nanti
    }

    public function show(Supplier $supplier)
    {
        // Akan diimplementasi nanti
    }

    public function edit(Supplier $supplier)
    {
        return view('pages.purchasing.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        // Akan diimplementasi nanti
    }

    public function destroy(Supplier $supplier)
    {
        // Akan diimplementasi nanti
    }

    public function riwayatHarga($supplier, $bahanBaku)
    {
        // Demo data untuk riwayat harga
        $supplierData = (object) [
            'id' => $supplier,
            'nama' => 'PT. Supplier Demo'
        ];

        $bahanBakuData = (object) [
            'id' => $bahanBaku,
            'nama' => $bahanBaku == 1 ? 'Tepung Terigu Premium' : 'Gula Pasir Halus',
            'satuan' => 'KG',
            'supplier_nama' => 'PT. Supplier Demo'
        ];

        // Demo data riwayat harga (berdasarkan tanggal spesifik)
        $riwayatHarga = [
            ['tanggal' => '2024-01-05', 'harga' => 10500],
            ['tanggal' => '2024-01-18', 'harga' => 10800],
            ['tanggal' => '2024-02-02', 'harga' => 11000],
            ['tanggal' => '2024-02-15', 'harga' => 10750],
            ['tanggal' => '2024-03-08', 'harga' => 11200],
            ['tanggal' => '2024-03-22', 'harga' => 11500],
            ['tanggal' => '2024-04-12', 'harga' => 12000],
            ['tanggal' => '2024-04-28', 'harga' => 11800],
            ['tanggal' => '2024-05-10', 'harga' => 12300],
            ['tanggal' => '2024-05-25', 'harga' => 12100],
            ['tanggal' => '2024-06-07', 'harga' => 12500],
            ['tanggal' => '2024-06-20', 'harga' => 12700],
            ['tanggal' => '2024-07-03', 'harga' => 12200],
            ['tanggal' => '2024-07-18', 'harga' => 12400],
            ['tanggal' => '2024-08-05', 'harga' => 12800],
            ['tanggal' => '2024-08-22', 'harga' => 13000],
            ['tanggal' => '2024-09-08', 'harga' => 12900],
            ['tanggal' => '2024-09-25', 'harga' => 13200],
            ['tanggal' => '2024-10-10', 'harga' => 13100],
            ['tanggal' => '2024-10-28', 'harga' => 13400],
            ['tanggal' => '2024-11-12', 'harga' => 13300],
            ['tanggal' => '2024-11-30', 'harga' => 13600],
            ['tanggal' => '2024-12-15', 'harga' => 13800],
            ['tanggal' => '2025-01-08', 'harga' => 14000]
        ];

        return view('pages.purchasing.supplier.riwayat-harga', compact('supplierData', 'bahanBakuData', 'riwayatHarga'));
    }
}
