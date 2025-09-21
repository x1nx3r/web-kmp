<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        // Get purchasing users for PIC dropdown
        $purchasingUsers = \App\Models\User::where('role', 'purchasing')
            ->orWhere('role', 'admin')
            ->orderBy('nama')
            ->get();

        return view('pages.purchasing.supplier.tambah', compact('purchasingUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'pic_purchasing_id' => 'nullable|exists:users,id',
            'bahan_baku' => 'required|array|min:1',
            'bahan_baku.*.nama' => 'required|string|max:255',
            'bahan_baku.*.satuan' => 'required|string|max:50',
            'bahan_baku.*.harga_per_satuan' => 'required|numeric|min:0',
            'bahan_baku.*.stok' => 'required|numeric|min:0',
        ], [
            'nama.required' => 'Nama supplier harus diisi',
            'bahan_baku.required' => 'Minimal satu bahan baku harus ditambahkan',
            'bahan_baku.min' => 'Minimal satu bahan baku harus ditambahkan',
            'bahan_baku.*.nama.required' => 'Nama bahan baku harus diisi',
            'bahan_baku.*.satuan.required' => 'Satuan bahan baku harus dipilih',
            'bahan_baku.*.harga_per_satuan.required' => 'Harga per satuan harus diisi',
            'bahan_baku.*.stok.required' => 'Stok harus diisi',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique slug
            $baseSlug = Str::slug($request->nama);
            $slug = $baseSlug;
            $counter = 1;

            // Check if slug exists and make it unique
            while (Supplier::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Create supplier
            $supplier = Supplier::create([
                'nama' => $request->nama,
                'slug' => $slug,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'pic_purchasing_id' => $request->pic_purchasing_id,
            ]);

            // Create bahan baku for supplier
            foreach ($request->bahan_baku as $bahanBaku) {
                $supplier->bahanBakuSuppliers()->create([
                    'nama' => $bahanBaku['nama'],
                    'satuan' => $bahanBaku['satuan'],
                    'harga_per_satuan' => str_replace('.', '', $bahanBaku['harga_per_satuan']), // Remove thousand separators
                    'stok' => str_replace('.', '', $bahanBaku['stok']), // Remove thousand separators
                ]);
            }

            DB::commit();

            return redirect()->route('supplier.index')
                ->with('success', 'Supplier berhasil ditambahkan dengan ' . count($request->bahan_baku) . ' bahan baku');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Gagal menyimpan supplier: ' . $e->getMessage());
        }
    }

    public function show(Supplier $supplier)
    {
        // Akan diimplementasi nanti
    }

    public function edit(Supplier $supplier)
    {
        // Get purchasing users for PIC dropdown
        $purchasingUsers = \App\Models\User::where('role', 'purchasing')
            ->orWhere('role', 'admin')
            ->get();
            
        return view('pages.purchasing.supplier.edit', compact('supplier', 'purchasingUsers'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        // Debug logging
        Log::info('Supplier Update Request', [
            'supplier_id' => $supplier->id,
            'nama' => $request->nama,
            'bahan_baku_count' => $request->has('bahan_baku') ? count($request->bahan_baku) : 0,
            'bahan_baku_data' => $request->bahan_baku
        ]);

        // Validation
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'pic_purchasing_id' => 'nullable|exists:users,id',
            'bahan_baku' => 'nullable|array',
            'bahan_baku.*.nama' => 'required_with:bahan_baku|string|max:255',
            'bahan_baku.*.satuan' => 'required_with:bahan_baku|string|max:50',
            'bahan_baku.*.harga_per_satuan' => 'required_with:bahan_baku|numeric|min:0',
            'bahan_baku.*.stok' => 'required_with:bahan_baku|numeric|min:0',
        ], [
            'nama.required' => 'Nama supplier harus diisi',
            'bahan_baku.*.nama.required_with' => 'Nama bahan baku harus diisi',
            'bahan_baku.*.satuan.required_with' => 'Satuan bahan baku harus dipilih',
            'bahan_baku.*.harga_per_satuan.required_with' => 'Harga per satuan harus diisi',
            'bahan_baku.*.stok.required_with' => 'Stok harus diisi',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique slug if name changed
            $slug = $supplier->slug;
            if ($request->nama !== $supplier->nama) {
                $baseSlug = Str::slug($request->nama);
                $slug = $baseSlug;
                $counter = 1;

                // Check if slug exists and make it unique (excluding current supplier)
                while (Supplier::where('slug', $slug)->where('id', '!=', $supplier->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Update supplier
            $supplier->update([
                'nama' => $request->nama,
                'slug' => $slug,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'pic_purchasing_id' => $request->pic_purchasing_id,
            ]);

            // Handle bahan baku updates - use upsert approach to avoid duplicate key errors
            $existingBahanBaku = $supplier->bahanBakuSuppliers()->get()->keyBy('nama');
            $submittedNames = [];

            // Update or create bahan baku from form data
            if ($request->has('bahan_baku') && is_array($request->bahan_baku)) {
                foreach ($request->bahan_baku as $bahanBaku) {
                    if (!empty($bahanBaku['nama']) && !empty($bahanBaku['satuan'])) {
                        $nama = trim($bahanBaku['nama']);
                        $submittedNames[] = $nama;

                        // Clean up numeric values
                        $hargaPerSatuan = str_replace(['.', ','], '', $bahanBaku['harga_per_satuan'] ?? '0');
                        $stok = str_replace(['.', ','], '', $bahanBaku['stok'] ?? '0');

                        $bahanBakuData = [
                            'nama' => $nama,
                            'satuan' => $bahanBaku['satuan'],
                            'harga_per_satuan' => is_numeric($hargaPerSatuan) ? $hargaPerSatuan : 0,
                            'stok' => is_numeric($stok) ? $stok : 0,
                        ];

                        // Check if this bahan baku already exists for this supplier
                        if ($existingBahanBaku->has($nama)) {
                            // Update existing
                            $existingBahanBaku[$nama]->update($bahanBakuData);
                        } else {
                            // Create new
                            $supplier->bahanBakuSuppliers()->create($bahanBakuData);
                        }
                    }
                }
            }

            // Delete bahan baku that were removed from the form
            $supplier->bahanBakuSuppliers()
                ->whereNotIn('nama', $submittedNames)
                ->delete();

            DB::commit();

            $bahanBakuCount = $request->has('bahan_baku') ? count(array_filter($request->bahan_baku, function($item) {
                return !empty($item['nama']);
            })) : 0;

            return redirect()->route('supplier.index')
                ->with('success', 'Supplier berhasil diperbarui dengan ' . $bahanBakuCount . ' bahan baku');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate supplier: ' . $e->getMessage()]);
        }
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
