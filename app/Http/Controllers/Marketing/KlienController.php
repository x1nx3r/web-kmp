<?php

namespace App\Http\Controllers\Marketing;

use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\RiwayatHargaKlien;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class KlienController extends Controller
{
    public function index(Request $request)
    {
        $query = Klien::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Filter by location
        if ($request->has('location') && $request->location != '') {
            $query->where('cabang', 'like', '%' . $request->location . '%');
        }

        // Sorting: only allow specific columns and directions to prevent SQL injection
        $allowedSorts = ['nama', 'cabang_count', 'lokasi', 'updated_at'];
        $sort = $request->get('sort', 'nama');
        $direction = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'nama';
        }

        // Get unique client names first to implement proper grouped pagination
        $page = $request->get('page', 1);
        $perPage = 10; // 10 unique client names per page
        
        // Get unique names with pagination
        $uniqueNamesQuery = Klien::query()
            ->select('nama')
            ->distinct();

        // Apply search and location filter to unique names query
        if ($request->has('search') && $request->search != '') {
            $uniqueNamesQuery->search($request->search);
        }
        if ($request->has('location') && $request->location != '') {
            $uniqueNamesQuery->where('cabang', 'like', '%' . $request->location . '%');
        }

        // Apply sorting based on sort type
        if ($sort === 'cabang_count') {
            $uniqueNames = $uniqueNamesQuery
                ->selectRaw('nama, COUNT(*) as branch_count')
                ->groupBy('nama')
                ->orderBy('branch_count', $direction)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('nama')
                ->toArray();
        } elseif ($sort === 'lokasi') {
            $uniqueNames = $uniqueNamesQuery
                ->selectRaw('nama, MIN(cabang) as first_location')
                ->groupBy('nama')
                ->orderBy('first_location', $direction)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('nama')
                ->toArray();
        } else {
            $uniqueNames = $uniqueNamesQuery
                ->orderBy($sort === 'updated_at' ? 'updated_at' : 'nama', $direction)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('nama')
                ->toArray();
        }

        // Get total count for pagination
        $totalUniqueNames = Klien::query()
            ->when($request->has('search') && $request->search != '', function($q) use ($request) {
                $q->search($request->search);
            })
            ->when($request->has('location') && $request->location != '', function($q) use ($request) {
                $q->where('cabang', 'like', '%' . $request->location . '%');
            })
            ->distinct('nama')
            ->count();

        // Now get all records for these specific names with eager loading
        $kliens = collect();
        if (!empty($uniqueNames)) {
            $kliens = Klien::query()
                ->with(['purchaseOrders.purchaseOrderBahanBakus.bahanBakuKlien'])
                ->whereIn('nama', $uniqueNames)
                ->orderBy($sort, $direction)
                ->get();
        }

        // Create a custom paginator for the grouped results
        $currentPageResults = $kliens;
        
        // Create pagination links manually
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageResults,
            $totalUniqueNames,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => 'page'
            ]
        );
        
                $paginator->appends($request->only(['search', 'location', 'sort', 'direction']));

        // Get available locations for filter dropdown
        $availableLocations = Klien::query()
            ->whereNotNull('cabang')
            ->where('cabang', '!=', '')
            ->distinct()
            ->orderBy('cabang')
            ->pluck('cabang')
            ->unique()
            ->values();

        return view('pages.marketing.daftar-klien', [
            'kliens' => $paginator,
            'availableLocations' => $availableLocations,
            'search' => $request->get('search', ''),
            'location' => $request->get('location', ''),
            'sort' => $sort,
            'direction' => $direction
        ]);
    }
    /**
     * Show the form for creating a new Klien.
     */
    public function create()
    {
        $klien = new Klien();
        return view('pages.marketing.klien.create', compact('klien'));
    }

    /**
     * Store a newly created Klien in storage.
     * Smart logic: Creates company placeholder if new company, or adds branch to existing company
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nama' => 'required|string|max:255',
                'cabang' => 'required|string|max:255',
                'no_hp' => 'nullable|string|max:30',
            ]);

            // Check if this exact branch already exists
            $exists = Klien::where('nama', $data['nama'])
                          ->where('cabang', $data['cabang'])
                          ->exists();

            if ($exists) {
                $message = 'Cabang ini sudah terdaftar untuk perusahaan tersebut';
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return redirect()->back()->withErrors(['cabang' => $message])->withInput();
            }

            // Check if company already exists (has any branches)
            $companyExists = Klien::where('nama', $data['nama'])->exists();

            if (!$companyExists) {
                // New company: Create placeholder first, then the actual branch
                Klien::create([
                    'nama' => $data['nama'],
                    'cabang' => 'Kantor Pusat',
                    'no_hp' => null,
                ]);
                $message = 'Perusahaan dan cabang baru berhasil ditambahkan';
            } else {
                $message = 'Cabang berhasil ditambahkan';
            }

            // Create the actual branch
            Klien::create($data);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('klien.index')->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal menambahkan cabang'], 500);
            }

            return redirect()->route('klien.index')->with('error', 'Gagal menambahkan klien.');
        }
    }

    /**
     * Display the specified Klien.
     */
    public function show(Klien $klien)
    {
        return view('pages.marketing.klien.show', compact('klien'));
    }

    /**
     * Show the form for editing the specified Klien.
     */
    public function edit(Klien $klien)
    {
        // Load materials for this client with their price history
        $klien->load([
            'bahanBakuKliens' => function($query) {
                $query->with(['riwayatHarga' => function($q) {
                    $q->latest('tanggal_perubahan')->take(5);
                }]);
            }
        ]);

        // Get all unique company names for the dropdown
        $uniqueCompanies = Klien::distinct('nama')->orderBy('nama')->pluck('nama');

        return view('pages.marketing.klien.edit', compact('klien', 'uniqueCompanies'));
    }

    /**
     * Update the specified Klien in storage.
     */
    public function update(Request $request, Klien $klien)
    {
        try {
            $data = $request->validate([
                'nama' => 'required|string|max:255',
                'cabang' => 'required|string|max:255',
                'no_hp' => 'nullable|string|max:30',
            ]);

            // Check if this combination already exists (excluding current record)
            $exists = Klien::where('nama', $data['nama'])
                          ->where('cabang', $data['cabang'])
                          ->where('id', '!=', $klien->id)
                          ->exists();

            if ($exists) {
                $message = 'Cabang ini sudah terdaftar untuk perusahaan tersebut';
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return redirect()->back()->withErrors(['cabang' => $message])->withInput();
            }

            $klien->update($data);

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Cabang berhasil diperbarui']);
            }

            return redirect()->route('klien.index')->with('success', 'Klien berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal memperbarui cabang'], 500);
            }

            return redirect()->route('klien.index')->with('error', 'Gagal memperbarui klien.');
        }
    }

    /**
     * Remove the specified Klien (soft delete).
     */
    public function destroy(Klien $klien)
    {
        try {
            $companyName = $klien->nama;
            
            // Delete the branch
            $klien->delete();
            
            // Check if this was the only real branch (excluding placeholder)
            $remainingRealBranches = Klien::where('nama', $companyName)
                                         ->where('cabang', '!=', 'Kantor Pusat')
                                         ->count();
            
            if ($remainingRealBranches === 0) {
                // Also delete the placeholder if no real branches remain
                Klien::where('nama', $companyName)
                     ->where('cabang', 'Kantor Pusat')
                     ->whereNull('no_hp')
                     ->delete();
                $message = 'Cabang dan perusahaan berhasil dihapus';
            } else {
                $message = 'Cabang berhasil dihapus';
            }

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('klien.index')->with('success', $message);
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus cabang'], 500);
            }

            return redirect()->route('klien.index')->with('error', 'Gagal menghapus klien.');
        }
    }

    /**
     * Update company name (update all branches with same nama).
     */
    public function updateCompany(Request $request)
    {
        try {
            $data = $request->validate([
                'old_nama' => 'required|string',
                'nama' => 'required|string|max:255|unique:kliens,nama,' . $request->old_nama . ',nama',
            ]);

            // Update all branches with the old company name
            $updated = Klien::where('nama', $data['old_nama'])->update([
                'nama' => $data['nama'],
                'updated_at' => now()
            ]);

            if ($updated === 0) {
                throw new \Exception('Perusahaan tidak ditemukan');
            }

            return response()->json(['success' => true, 'message' => 'Perusahaan berhasil diperbarui']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete company (soft delete all branches with same nama).
     */
    public function destroyCompany(Request $request)
    {
        try {
            $data = $request->validate([
                'nama' => 'required|string',
            ]);

            // Soft delete all branches with the company name
            $deleted = Klien::where('nama', $data['nama'])->delete();

            if ($deleted === 0) {
                throw new \Exception('Perusahaan tidak ditemukan');
            }

            return response()->json(['success' => true, 'message' => 'Perusahaan berhasil dihapus']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new material for a client
     */
    public function storeMaterial(Request $request)
    {
        try {
            $validated = $request->validate([
                'klien_id' => 'required|exists:kliens,id',
                'nama' => 'required|string|max:255',
                'satuan' => 'required|string|max:50',
                'spesifikasi' => 'nullable|string',
                'harga_approved' => 'nullable|numeric|min:0',
                'status' => 'required|in:aktif,non_aktif,pending'
            ]);

            $material = new BahanBakuKlien($validated);
            
            if ($validated['harga_approved']) {
                $material->approved_at = now();
                $material->approved_by_marketing = Auth::id();
            }
            
            $material->save();

            // Create initial price history if approved price is set
            if ($validated['harga_approved']) {
                RiwayatHargaKlien::createPriceHistory(
                    $material->id,
                    $validated['harga_approved'],
                    Auth::id(),
                    'Harga initial approval'
                );
            }

            return response()->json([
                'success' => true, 
                'message' => 'Material berhasil ditambahkan',
                'data' => $material->load(['approvedByMarketing', 'riwayatHarga'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing material
     */
    public function updateMaterial(Request $request, BahanBakuKlien $material)
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'satuan' => 'required|string|max:50',
                'spesifikasi' => 'nullable|string',
                'harga_approved' => 'nullable|numeric|min:0',
                'status' => 'required|in:aktif,non_aktif,pending'
            ]);

            $oldPrice = $material->harga_approved;
            $newPrice = $validated['harga_approved'];

            // Update material
            $material->fill($validated);

            // Handle price approval changes
            if ($newPrice && $newPrice != $oldPrice) {
                $material->approved_at = now();
                $material->approved_by_marketing = Auth::id();

                // Create price history record using helper
                RiwayatHargaKlien::createPriceHistory(
                    $material->id,
                    $newPrice,
                    Auth::id(),
                    'Perubahan harga approved'
                );
            }

            $material->save();

            return response()->json([
                'success' => true, 
                'message' => 'Material berhasil diupdate',
                'data' => $material->load(['approvedByMarketing', 'riwayatHarga'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a material
     */
    public function destroyMaterial(BahanBakuKlien $material)
    {
        try {
            $materialName = $material->nama;
            $material->delete();

            return response()->json([
                'success' => true, 
                'message' => "Material '{$materialName}' berhasil dihapus"
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get price history for a material
     */
    public function getMaterialPriceHistory(BahanBakuKlien $material)
    {
        try {
            $history = $material->riwayatHarga()
                ->with('updatedByMarketing:id,nama')
                ->orderBy('tanggal_perubahan', 'desc')
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'harga_lama' => $record->harga_lama,
                        'harga_baru' => $record->harga_approved_baru, // Fix: use correct field name
                        'formatted_harga_lama' => $record->formatted_harga_lama,
                        'formatted_harga_baru' => $record->formatted_harga_baru,
                        'tanggal_perubahan' => $record->tanggal_perubahan->format('d/m/Y H:i'),
                        'keterangan' => $record->keterangan,
                        'diubah_oleh' => $record->updatedByMarketing->nama ?? 'System'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'material' => $material->only(['id', 'nama', 'satuan', 'harga_approved']),
                    'history' => $history
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a single material for editing
     */
    public function getMaterial(BahanBakuKlien $material)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $material->only(['id', 'nama', 'satuan', 'spesifikasi', 'harga_approved', 'status'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show price history page for a client's material (blade with chart + table)
     */
    public function riwayatHarga(Klien $klien, BahanBakuKlien $material)
    {
        // Ensure the material belongs to the client when klien_id is present
        if ($material->klien_id && $material->klien_id !== $klien->id) {
            abort(404);
        }

        // Load history and format for blade
        $history = $material->riwayatHarga()->orderBy('tanggal_perubahan', 'asc')->get();

        $riwayatHarga = $history->map(function ($item) {
            return [
                'id' => $item->id,
                'harga' => $item->harga_approved_baru ?? $item->harga_baru ?? 0,
                'formatted_harga' => $item->formatted_harga_baru ?? number_format($item->harga_approved_baru ?? $item->harga_baru ?? 0, 0, ',', '.'),
                'tanggal' => $item->tanggal_perubahan->toDateString(),
                'formatted_tanggal' => $item->tanggal_perubahan->format('d M Y'),
                'formatted_hari' => $item->tanggal_perubahan->format('l'),
                'tipe_perubahan' => $item->tipe_perubahan ?? 'awal',
                'formatted_selisih' => $item->formatted_selisih_harga ?? number_format($item->selisih_harga ?? 0, 0, ',', '.'),
                'persentase_perubahan' => $item->persentase_perubahan ?? 0,
                'badge_class' => $item->badge_class ?? 'bg-gray-100 text-gray-600',
                'icon' => $item->icon ?? 'fas fa-minus'
            ];
        })->toArray();

        // Basic stats for header cards
        $prices = array_column($riwayatHarga, 'harga');
        $stats = [
            'max' => !empty($prices) ? max($prices) : ($material->harga_approved ?? 0),
            'min' => !empty($prices) ? min($prices) : ($material->harga_approved ?? 0),
            'days' => count($riwayatHarga) > 1 ? (\Carbon\Carbon::parse($riwayatHarga[0]['tanggal'])->diffInDays(\Carbon\Carbon::parse(end($riwayatHarga)['tanggal'])) ) : 0,
        ];

        // trend calculations
        if (!empty($prices)) {
            $first = $prices[0];
            $last = end($prices);
            if ($first > 0) {
                $trendPercent = round((($last - $first) / $first) * 100, 2);
            } else {
                $trendPercent = 0;
            }
            $stats['trend_percent'] = abs($trendPercent);
            $stats['trend'] = $last > $first ? 'naik' : ($last < $first ? 'turun' : 'stabil');
            $stats['trend_prefix'] = $last > $first ? '+' : ($last < $first ? '' : '');
            $stats['trend_class'] = $last > $first ? 'border-green-500' : ($last < $first ? 'border-red-500' : 'border-gray-500');
            $stats['trend_text_class'] = $last > $first ? 'text-green-600' : ($last < $first ? 'text-red-600' : 'text-gray-600');
            $stats['trend_icon_class'] = $last > $first ? 'text-green-500' : ($last < $first ? 'text-red-500' : 'text-gray-500');
            $stats['trend_bg'] = $last > $first ? 'bg-green-100' : ($last < $first ? 'bg-red-100' : 'bg-gray-100');
        } else {
            $stats['trend_percent'] = 0;
            $stats['trend'] = 'stabil';
            $stats['trend_prefix'] = '';
            $stats['trend_class'] = 'border-gray-500';
            $stats['trend_text_class'] = 'text-gray-600';
            $stats['trend_icon_class'] = 'text-gray-500';
            $stats['trend_bg'] = 'bg-gray-100';
        }

        return view('pages.marketing.klien.riwayat-harga', [
            'klienData' => $klien,
            'bahanBakuData' => $material,
            'riwayatHarga' => $riwayatHarga,
            'stats' => $stats
        ]);
    }
}