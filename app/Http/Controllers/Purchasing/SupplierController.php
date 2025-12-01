<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Supplier;
use App\Models\BahanBakuSupplier;
use App\Models\RiwayatHargaBahanBaku;
use App\Models\Pengiriman; // Import Pengiriman model
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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

        $suppliers = $query->paginate(10);

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
        $purchasingUsers = \App\Models\User::whereIn('role', ['direktur','manager_purchasing', 'staff_purchasing'])
            ->orderBy('nama')
            ->get();

        return view('pages.purchasing.supplier', compact('suppliers', 'bahanBakuList', 'purchasingUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only direktur, manager_purchasing, and staff_purchasing can create
        if (!in_array(Auth::user()->role, ['direktur', 'manager_purchasing', 'staff_purchasing'])) {
            abort(403, 'Anda tidak memiliki akses untuk menambah supplier.');
        }

        // Get purchasing users for PIC dropdown
        $purchasingUsers = \App\Models\User::whereIn('role', ['direktur','manager_purchasing', 'staff_purchasing'])
            ->orderBy('nama')
            ->get();

        return view('pages.purchasing.supplier.tambah', compact('purchasingUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only direktur, manager_purchasing, and staff_purchasing can create
        if (!in_array(Auth::user()->role, ['direktur', 'manager_purchasing', 'staff_purchasing'])) {
            abort(403, 'Anda tidak memiliki akses untuk menambah supplier.');
        }

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
                $hargaPerSatuan = str_replace('.', '', $bahanBaku['harga_per_satuan']); // Remove thousand separators
                $stok = str_replace('.', '', $bahanBaku['stok']); // Remove thousand separators
                
                // Generate unique slug
                $slug = \App\Models\BahanBakuSupplier::generateUniqueSlug($bahanBaku['nama'], $supplier->id);
                
                $newBahanBaku = $supplier->bahanBakuSuppliers()->create([
                    'nama' => $bahanBaku['nama'],
                    'slug' => $slug,
                    'satuan' => $bahanBaku['satuan'],
                    'harga_per_satuan' => $hargaPerSatuan,
                    'stok' => $stok,
                ]);

                // Catat riwayat harga untuk bahan baku baru
                RiwayatHargaBahanBaku::catatPerubahanHarga(
                    $newBahanBaku->id,
                    null, // harga lama = null karena bahan baku baru
                    $hargaPerSatuan,
                    "Data awal bahan baku '{$bahanBaku['nama']}' untuk supplier '{$supplier->nama}'"
                );
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
        // Authorization check
        $user = Auth::user();
        $canEdit = false;

        if (in_array($user->role, ['direktur', 'manager_purchasing'])) {
            // Direktur and manager_purchasing can edit all suppliers
            $canEdit = true;
        } elseif ($user->role === 'staff_purchasing') {
            // Staff_purchasing can only edit suppliers where they are PIC
            $canEdit = $supplier->pic_purchasing_id === $user->id;
        }

        if (!$canEdit) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit supplier ini.');
        }

        // Get purchasing users for PIC dropdown
        $purchasingUsers = \App\Models\User::whereIn('role', ['direktur','manager_purchasing', 'staff_purchasing'])
            ->get();
            
        return view('pages.purchasing.supplier.edit', compact('supplier', 'purchasingUsers'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        // Authorization check
        $user = Auth::user();
        $canEdit = false;

        if (in_array($user->role, ['direktur', 'manager_purchasing'])) {
            // Direktur and manager_purchasing can edit all suppliers
            $canEdit = true;
        } elseif ($user->role === 'staff_purchasing') {
            // Staff_purchasing can only edit suppliers where they are PIC
            $canEdit = $supplier->pic_purchasing_id === $user->id;
        }

        if (!$canEdit) {
            return redirect()->route('supplier.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit supplier ini.');
        }

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
                            'slug' => \App\Models\BahanBakuSupplier::generateUniqueSlug($nama, $supplier->id, $existingBahanBaku->has($nama) ? $existingBahanBaku[$nama]->id : null),
                            'satuan' => $bahanBaku['satuan'],
                            'harga_per_satuan' => is_numeric($hargaPerSatuan) ? $hargaPerSatuan : 0,
                            'stok' => is_numeric($stok) ? $stok : 0,
                        ];

                        // Check if this bahan baku already exists for this supplier
                        if ($existingBahanBaku->has($nama)) {
                            // Update existing bahan baku
                            $bahanBakuToUpdate = $existingBahanBaku[$nama];
                            $hargaLama = (float) $bahanBakuToUpdate->harga_per_satuan;
                            $hargaBaru = (float) $hargaPerSatuan;
                            
                            $bahanBakuToUpdate->update($bahanBakuData);
                            
                            // Catat riwayat harga jika ada perubahan harga
                            if ($hargaLama != $hargaBaru) {
                                RiwayatHargaBahanBaku::catatPerubahanHarga(
                                    $bahanBakuToUpdate->id,
                                    $hargaLama,
                                    $hargaBaru,
                                    "Update harga bahan baku '{$nama}' melalui edit supplier"
                                );
                            }
                        } else {
                            // Create new bahan baku
                            $newBahanBaku = $supplier->bahanBakuSuppliers()->create($bahanBakuData);
                            
                            // Catat riwayat harga untuk bahan baku baru
                            RiwayatHargaBahanBaku::catatPerubahanHarga(
                                $newBahanBaku->id,
                                null, // harga lama = null karena bahan baku baru
                                (float) $hargaPerSatuan,
                                "Bahan baku baru '{$nama}' ditambahkan ke supplier '{$supplier->nama}'"
                            );
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
        // Authorization check
        $user = Auth::user();
        $canDelete = false;

        if (in_array($user->role, ['direktur', 'manager_purchasing'])) {
            // Direktur and manager_purchasing can delete all suppliers
            $canDelete = true;
        } elseif ($user->role === 'staff_purchasing') {
            // Staff_purchasing can only delete suppliers where they are PIC
            $canDelete = $supplier->pic_purchasing_id === $user->id;
        }

        if (!$canDelete) {
            return redirect()->route('supplier.index')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus supplier ini.');
        }

        try {
            DB::beginTransaction();

            // Log the deletion attempt
            Log::info('Supplier Delete Request', [
                'supplier_id' => $supplier->id,
                'supplier_nama' => $supplier->nama,
                'bahan_baku_count' => $supplier->bahanBakuSuppliers->count()
            ]);

            // Delete related bahan baku first (due to foreign key constraints)
            $bahanBakuCount = $supplier->bahanBakuSuppliers->count();
            $supplier->bahanBakuSuppliers()->delete();

            // Delete the supplier
            $supplierName = $supplier->nama;
            $supplier->delete();

            DB::commit();

            return redirect()->route('supplier.index')
                ->with('success', "Supplier '{$supplierName}' berhasil dihapus beserta {$bahanBakuCount} bahan baku terkait");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Supplier Delete Error', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghapus supplier: ' . $e->getMessage());
        }
    }

    public function riwayatHarga(Supplier $supplier, BahanBakuSupplier $bahanBaku)
    {
        // Debug logging
        Log::info('RiwayatHarga Request', [
            'supplier_id' => $supplier->id,
            'supplier_slug' => $supplier->slug,
            'bahan_baku_id' => $bahanBaku->id,
            'bahan_baku_slug' => $bahanBaku->slug
        ]);
        
        // Pastikan bahan baku ini milik supplier yang benar
        if ($bahanBaku->supplier_id !== $supplier->id) {
            abort(404, 'Bahan baku tidak ditemukan untuk supplier ini');
        }
        
        // Get riwayat harga dari database
        $riwayatHarga = $bahanBaku->riwayatHarga()
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();

        // Jika tidak ada riwayat, buat entry pertama dari harga saat ini
        if ($riwayatHarga->isEmpty()) {
            RiwayatHargaBahanBaku::catatPerubahanHarga(
                $bahanBaku->id,
                null,
                (float) $bahanBaku->harga_per_satuan,
                "Data riwayat awal untuk bahan baku '{$bahanBaku->nama}'"
            );
            
            // Refresh riwayat harga setelah dibuat
            $riwayatHarga = $bahanBaku->riwayatHarga()
                ->orderBy('tanggal_perubahan', 'asc')
                ->get();
        }

        // Format data supplier
        $supplierData = (object) [
            'id' => $supplier->id,
            'nama' => $supplier->nama,
            'slug' => $supplier->slug
        ];

        // Format data bahan baku
        $bahanBakuData = (object) [
            'id' => $bahanBaku->id,
            'nama' => $bahanBaku->nama,
            'satuan' => $bahanBaku->satuan,
            'supplier_nama' => $supplier->nama,
            'harga_saat_ini' => (float) $bahanBaku->harga_per_satuan,
            'stok_saat_ini' => (float) $bahanBaku->stok
        ];

        // Format data riwayat harga untuk view
        $riwayatHarga = $riwayatHarga->map(function ($item) {
            return [
                'id' => $item->id,
                'tanggal' => $item->tanggal_perubahan->format('Y-m-d'),
                'harga' => (float) $item->harga_baru,
                'harga_lama' => $item->harga_lama ? (float) $item->harga_lama : null,
                'selisih_harga' => (float) $item->selisih_harga,
                'persentase_perubahan' => (float) $item->persentase_perubahan,
                'tipe_perubahan' => $item->tipe_perubahan,
                'keterangan' => $item->keterangan,
                'formatted_tanggal' => $item->tanggal_perubahan->format('d M Y'),
                'formatted_hari' => $item->tanggal_perubahan->format('l'),
                'formatted_harga' => number_format((float) $item->harga_baru, 0, ',', '.'),
                'formatted_harga_lama' => $item->harga_lama ? number_format((float) $item->harga_lama, 0, ',', '.') : null,
                'formatted_selisih' => number_format(abs((float) $item->selisih_harga), 0, ',', '.'),
                'color_class' => $item->color_class,
                'badge_class' => $item->badge_class,
                'icon' => $item->icon,
            ];
        })->toArray();

        return view('pages.purchasing.supplier.riwayat-harga', compact('supplierData', 'bahanBakuData', 'riwayatHarga'));
    }

    /**
     * Show supplier reviews page
     */
    public function reviews(Request $request, Supplier $supplier)
    {
        // Get all pengiriman for this supplier
        $query = Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', function($q) use ($supplier) {
            $q->where('supplier_id', $supplier->id);
        })
        ->whereIn('status', ['berhasil', 'gagal'])
        ->with(['order.klien', 'purchasing', 'pengirimanDetails.bahanBakuSupplier']);

        // Filter by status if requested
        if ($request->filled('status') && in_array($request->status, ['berhasil', 'gagal'])) {
            $query->where('status', $request->status);
        }

        // Filter by rating if requested
        if ($request->filled('rating') && $request->rating >= 1 && $request->rating <= 5) {
            $query->where('rating', $request->rating);
        }

        // Filter by klien if requested
        if ($request->filled('klien')) {
            $query->whereHas('order.klien', function($klienQuery) use ($request) {
                $klienQuery->where('id', $request->klien);
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_pengiriman', 'LIKE', "%{$search}%")
                  ->orWhere('ulasan', 'LIKE', "%{$search}%")
                  ->orWhereHas('order', function($orderQuery) use ($search) {
                      $orderQuery->where('no_order', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('order.klien', function($klienQuery) use ($search) {
                      $klienQuery->where('nama', 'LIKE', "%{$search}%")
                                 ->orWhere('cabang', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('purchasing', function($purchasingQuery) use ($search) {
                      $purchasingQuery->where('nama', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Sort by latest
        $pengiriman = $query->orderBy('created_at', 'desc')->paginate(10)->appends(request()->query());

        // Get all klien for filter dropdown
        $klienList = \App\Models\Klien::select('id', 'nama', 'cabang')
            ->orderBy('nama')
            ->get()
            ->map(function($klien) {
                return [
                    'id' => $klien->id,
                    'nama' => $klien->nama . ($klien->cabang ? ' - ' . $klien->cabang : '')
                ];
            });

        // Get statistics
        $stats = [
            'average_rating' => $supplier->getAverageRating(),
            'total_reviews' => $supplier->getTotalReviews(),
            'berhasil_count' => $supplier->getPengirimanBerhasilCount(),
            'gagal_count' => $supplier->getPengirimanGagalCount(),
            'rating_distribution' => []
        ];

        // Get rating distribution
        for ($i = 1; $i <= 5; $i++) {
            $count = Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', function($q) use ($supplier) {
                $q->where('supplier_id', $supplier->id);
            })
            ->where('rating', $i)
            ->count();
            
            $stats['rating_distribution'][$i] = $count;
        }

        return view('pages.purchasing.supplier.reviews', compact('supplier', 'pengiriman', 'stats', 'klienList'));
    }
}
