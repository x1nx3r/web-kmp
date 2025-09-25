<?php

namespace App\Http\Controllers\Marketing;

use App\Models\Klien;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        return view('pages.marketing.klien.edit', compact('klien'));
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
}