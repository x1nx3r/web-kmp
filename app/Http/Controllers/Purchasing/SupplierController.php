<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with(['bahanBakuSuppliers' => function($q) {
            $q->orderBy('nama', 'asc');
        }, 'picPurchasing']);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhereHas('picPurchasing', function($subQuery) use ($search) {
                      $subQuery->where('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('bahanBakuSuppliers', function($subQuery) use ($search) {
                      $subQuery->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by specific bahan baku
        if ($request->has('bahan_baku') && $request->bahan_baku != '') {
            $bahanBaku = str_replace('_', ' ', $request->bahan_baku);
            $query->whereHas('bahanBakuSuppliers', function($subQuery) use ($bahanBaku) {
                $subQuery->where('nama', 'like', "%{$bahanBaku}%");
            });
        }

        // Sort by bahan baku count
        if ($request->has('sort_bahan_baku') && $request->sort_bahan_baku != '') {
            $sortDirection = $request->sort_bahan_baku == 'terbanyak' ? 'desc' : 'asc';
            $query->withCount('bahanBakuSuppliers')->orderBy('bahan_baku_suppliers_count', $sortDirection);
        }

        // Sort by total stock
        if ($request->has('sort_stok') && $request->sort_stok != '') {
            $sortDirection = $request->sort_stok == 'terbanyak' ? 'desc' : 'asc';
            $query->withSum('bahanBakuSuppliers', 'stok')->orderBy('bahan_baku_suppliers_sum_stok', $sortDirection);
        }

        // Default ordering if no specific sort applied
        if (!$request->has('sort_bahan_baku') && !$request->has('sort_stok')) {
            $query->orderBy('nama', 'asc');
        }

        $suppliers = $query->paginate(5);

        // Get unique bahan baku names for filter dropdown
        $bahanBakuList = \App\Models\BahanBakuSupplier::select('nama')
            ->distinct()
            ->orderBy('nama')
            ->pluck('nama')
            ->map(function($nama) {
                return [
                    'value' => strtolower(str_replace(' ', '_', $nama)),
                    'label' => $nama
                ];
            });

        // Get purchasing users for PIC dropdown (jika diperlukan di form)
        $purchasingUsers = \App\Models\User::where('role', 'purchasing')
            ->orWhere('role', 'admin')
            ->orderBy('nama')
            ->get();

        return view('pages.purchasing.supplier', compact('suppliers', 'bahanBakuList', 'purchasingUsers'));
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
