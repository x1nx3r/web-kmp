<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Supplier;
use App\Models\BahanBakuSupplier;
use App\Models\RiwayatHargaBahanBaku;
use App\Models\Pengiriman;
use App\Models\PengirimanDetail;
use App\Models\Klien;
use App\Models\BahanBakuSupplierKlien;
use App\Services\ReferenceDataService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Supplier::with(['bahanBakuSuppliers' => function($q) {
            $q->orderBy('nama', 'asc');
        }, 'picPurchasing']);

        $this->applySearchFilters($query, $request);
        $this->applySorting($query, $request);

        $suppliers = $query->paginate(10);

        $bahanBakuList = BahanBakuSupplier::select('nama')
            ->distinct()
            ->orderBy('nama')
            ->pluck('nama')
            ->map(fn($nama) => [
                'value' => strtolower(str_replace(' ', '_', $nama)),
                'label' => $nama
            ]);

        $purchasingUsers = ReferenceDataService::getPurchasingUsers();

        return view('pages.purchasing.supplier', compact('suppliers', 'bahanBakuList', 'purchasingUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorizeAccess('create');

        $purchasingUsers = ReferenceDataService::getPurchasingUsers();
        return view('pages.purchasing.supplier.tambah', compact('purchasingUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess('create');
        $request->validate($this->getValidationRules('store'), $this->getValidationMessages());

        try {
            DB::beginTransaction();

            $supplier = Supplier::create([
                'nama' => $request->nama,
                'slug' => $this->generateSupplierSlug($request->nama),
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'pic_purchasing_id' => $request->pic_purchasing_id,
            ]);

            foreach ($request->bahan_baku as $bahanBaku) {
                $hargaPerSatuan = $this->parseNumeric($bahanBaku['harga_per_satuan']);
                $stok = $this->parseNumeric($bahanBaku['stok']);
                $slug = BahanBakuSupplier::generateUniqueSlug($bahanBaku['nama'], $supplier->id);
                
                $newBahanBaku = $supplier->bahanBakuSuppliers()->create([
                    'nama' => $bahanBaku['nama'],
                    'slug' => $slug,
                    'satuan' => $bahanBaku['satuan'],
                    'harga_per_satuan' => $hargaPerSatuan,
                    'stok' => $stok,
                ]);

                RiwayatHargaBahanBaku::catatPerubahanHarga(
                    $newBahanBaku->id,
                    null,
                    $hargaPerSatuan,
                    "Data awal bahan baku '{$bahanBaku['nama']}' untuk supplier '{$supplier->nama}'"
                );
            }

            DB::commit();

            return redirect()->route('supplier.index')
                ->with('success', 'Supplier berhasil ditambahkan dengan ' . count($request->bahan_baku) . ' bahan baku');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Gagal menyimpan supplier: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        // Akan diimplementasi nanti
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier): View
    {
        $this->authorizeAccess('edit', $supplier);

        $purchasingUsers = ReferenceDataService::getPurchasingUsers();
        $klienList = Klien::select('id', 'nama', 'cabang')->orderBy('nama')->orderBy('cabang')->get();
        
        $supplier->load(['bahanBakuSuppliers.hargaPerKlien.klien']);
        
        $priceData = [];
        foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
            $priceData[$bahanBaku->id] = [
                'global' => (float) $bahanBaku->harga_per_satuan,
                'klien' => []
            ];
            foreach ($bahanBaku->hargaPerKlien as $hargaKlien) {
                $priceData[$bahanBaku->id]['klien'][$hargaKlien->klien_id] = (float) $hargaKlien->harga_per_satuan;
            }
        }
            
        return view('pages.purchasing.supplier.edit', compact('supplier', 'purchasingUsers', 'klienList', 'priceData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        try {
            $this->authorizeAccess('edit', $supplier);
        } catch (\Exception $e) {
            return redirect()->route('supplier.index')->with('error', $e->getMessage());
        }

        Log::info('Supplier Update Request', [
            'supplier_id' => $supplier->id,
            'bahan_baku_count' => $request->has('bahan_baku') ? count($request->bahan_baku) : 0,
        ]);

        $request->validate($this->getValidationRules('update'), $this->getValidationMessages());

        try {
            DB::beginTransaction();

            $slug = ($request->nama !== $supplier->nama) 
                ? $this->generateSupplierSlug($request->nama, $supplier->id) 
                : $supplier->slug;

            $supplier->update([
                'nama' => $request->nama,
                'slug' => $slug,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'pic_purchasing_id' => $request->pic_purchasing_id,
            ]);

            $klienId = ($request->edit_harga_untuk !== 'global') ? (int) $request->edit_harga_untuk : null;
            $submittedIds = [];

            if ($request->has('bahan_baku') && is_array($request->bahan_baku)) {
                $submittedIds = $this->processBahanBakuUpdates($supplier, $request->bahan_baku, $klienId);
            }

            // Hapus bahan baku yang tidak ada di form
            $supplier->bahanBakuSuppliers()->whereNotIn('id', $submittedIds)->delete();

            DB::commit();

            $bahanBakuCount = $request->has('bahan_baku') ? count(array_filter($request->bahan_baku, fn($item) => !empty($item['nama']))) : 0;
            return redirect()->route('supplier.index')
                ->with('success', "Supplier berhasil diperbarui dengan {$bahanBakuCount} bahan baku");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate supplier: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        try {
            $this->authorizeAccess('delete', $supplier);
        } catch (\Exception $e) {
            return redirect()->route('supplier.index')->with('error', $e->getMessage());
        }

        try {
            DB::beginTransaction();

            Log::info('Supplier Delete Request', ['supplier_id' => $supplier->id]);

            $bahanBakuCount = $supplier->bahanBakuSuppliers()->count();
            $supplier->bahanBakuSuppliers()->delete();
            
            $supplierName = $supplier->nama;
            $supplier->delete();

            DB::commit();

            return redirect()->route('supplier.index')
                ->with('success', "Supplier '{$supplierName}' berhasil dihapus beserta {$bahanBakuCount} bahan baku terkait");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Supplier Delete Error', ['supplier_id' => $supplier->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus supplier: ' . $e->getMessage());
        }
    }

    /**
     * Show price history for specific material
     */
    public function riwayatHarga(Supplier $supplier, BahanBakuSupplier $bahanBaku): View
    {
        if ($bahanBaku->supplier_id !== $supplier->id) {
            abort(404, 'Bahan baku tidak ditemukan untuk supplier ini');
        }
        
        $riwayatHargaData = $bahanBaku->riwayatHarga()
            ->with('klien')
            ->orderBy('tanggal_perubahan', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($riwayatHargaData->isEmpty()) {
            RiwayatHargaBahanBaku::catatPerubahanHarga(
                $bahanBaku->id,
                null,
                (float) $bahanBaku->harga_per_satuan,
                "Data riwayat awal untuk bahan baku '{$bahanBaku->nama}'"
            );
            $riwayatHargaData = $bahanBaku->riwayatHarga()->with('klien')->orderBy('tanggal_perubahan', 'asc')->orderBy('id', 'asc')->get();
        }

        $riwayatHarga = $this->formatRiwayatData($riwayatHargaData);
        
        $allDates = [];
        $chartDataByKlien = $this->prepareChartDataByKlien($riwayatHarga, $allDates);

        return view('pages.purchasing.supplier.riwayat-harga', [
            'supplierData' => (object) ['id' => $supplier->id, 'nama' => $supplier->nama, 'slug' => $supplier->slug],
            'bahanBakuData' => (object) [
                'id' => $bahanBaku->id, 'slug' => $bahanBaku->slug, 'nama' => $bahanBaku->nama,
                'satuan' => $bahanBaku->satuan, 'supplier_nama' => $supplier->nama,
                'harga_saat_ini' => (float) $bahanBaku->harga_per_satuan, 'stok_saat_ini' => (float) $bahanBaku->stok
            ],
            'riwayatHarga' => $riwayatHarga,
            'chartDataByKlien' => $chartDataByKlien,
            'allDates' => $allDates
        ]);
    }

    /**
     * Show supplier reviews page
     */
    public function reviews(Request $request, Supplier $supplier): View
    {
        $query = Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', function($q) use ($supplier) {
            $q->where('supplier_id', $supplier->id);
        })
        ->whereIn('status', ['berhasil', 'gagal'])
        ->with(['order.klien', 'purchasing', 'pengirimanDetails.bahanBakuSupplier']);

        if ($request->filled('status') && in_array($request->status, ['berhasil', 'gagal'])) {
            $query->where('status', $request->status);
        }
        if ($request->filled('rating') && $request->rating >= 1 && $request->rating <= 5) {
            $query->where('rating', $request->rating);
        }
        if ($request->filled('klien')) {
            $query->whereHas('order.klien', fn($k) => $k->where('id', $request->klien));
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_pengiriman', 'LIKE', "%{$search}%")
                  ->orWhere('ulasan', 'LIKE', "%{$search}%")
                  ->orWhereHas('order', fn($o) => $o->where('no_order', 'LIKE', "%{$search}%"))
                  ->orWhereHas('order.klien', fn($k) => $k->where('nama', 'LIKE', "%{$search}%")->orWhere('cabang', 'LIKE', "%{$search}%"))
                  ->orWhereHas('purchasing', fn($p) => $p->where('nama', 'LIKE', "%{$search}%"));
            });
        }

        $pengiriman = $query->orderBy('created_at', 'desc')->paginate(10)->appends(request()->query());

        $klienList = Klien::select('id', 'nama', 'cabang')
            ->orderBy('nama')
            ->get()
            ->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama . ($k->cabang ? ' - ' . $k->cabang : '')]);

        $stats = [
            'average_rating' => $supplier->getAverageRating(),
            'total_reviews' => $supplier->getTotalReviews(),
            'berhasil_count' => $supplier->getPengirimanBerhasilCount(),
            'gagal_count' => $supplier->getPengirimanGagalCount(),
            'rating_distribution' => array_fill(1, 5, 0)
        ];

        // Optimized Rating Distribution (1 Query instead of 5)
        $ratingCounts = Pengiriman::whereHas('pengirimanDetails.bahanBakuSupplier', fn($q) => $q->where('supplier_id', $supplier->id))
            ->whereNotNull('rating')
            ->select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating');

        foreach ($ratingCounts as $rating => $count) {
            if ($rating >= 1 && $rating <= 5) {
                $stats['rating_distribution'][(int)$rating] = $count;
            }
        }

        return view('pages.purchasing.supplier.reviews', compact('supplier', 'pengiriman', 'stats', 'klienList'));
    }

    /**
     * Get PO/Orders data for specific price in riwayat harga
     */
    public function getPOByHarga(Request $request, Supplier $supplier, BahanBakuSupplier $bahanBaku): JsonResponse
    {
        if ($bahanBaku->supplier_id !== $supplier->id) {
            return response()->json(['error' => 'Bahan baku tidak ditemukan untuk supplier ini'], 404);
        }

        $harga = $request->input('harga');
        if (!$harga) {
            return response()->json(['error' => 'Harga tidak ditemukan'], 400);
        }

        $pengirimanDetails = PengirimanDetail::where('bahan_baku_supplier_id', $bahanBaku->id)
            ->where('harga_satuan', $harga)
            ->with(['pengiriman.order.klien'])
            ->get();

        $orders = [];
        $totalQty = 0;

        foreach ($pengirimanDetails as $detail) {
            $order = $detail->pengiriman->order;
            if (!$order) continue;

            $orderId = $order->id;
            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'order_id' => $order->id,
                    'no_order' => $order->no_order,
                    'po_number' => $order->po_number ?? '-',
                    'tanggal_order' => $order->tanggal_order ? Carbon::parse($order->tanggal_order)->format('d M Y') : '-',
                    'klien_nama' => $order->klien->nama ?? '-',
                    'klien_cabang' => $order->klien->cabang ?? '-',
                    'status_order' => $order->status,
                    'pengiriman' => []
                ];
            }

            $orders[$orderId]['pengiriman'][] = [
                'no_pengiriman' => $detail->pengiriman->no_pengiriman,
                'tanggal_kirim' => $detail->pengiriman->tanggal_kirim ? Carbon::parse($detail->pengiriman->tanggal_kirim)->format('d M Y') : '-',
                'qty_kirim' => (float) $detail->qty_kirim,
                'total_harga' => (float) $detail->total_harga,
                'status' => $detail->pengiriman->status,
            ];
            $totalQty += (float) $detail->qty_kirim;
        }

        return response()->json([
            'success' => true,
            'harga' => (float) $harga,
            'tanggal' => $request->input('tanggal'),
            'satuan' => $bahanBaku->satuan,
            'bahan_baku_nama' => $bahanBaku->nama,
            'supplier_nama' => $supplier->nama,
            'total_po' => count($orders),
            'total_qty' => $totalQty,
            'orders' => array_values($orders),
        ]);
    }

    // =========================================================================
    // PRIVATE HELPER METHODS (REFACTORING EXTRACTIONS)
    // =========================================================================

    private function applySearchFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhereHas('picPurchasing', fn($s) => $s->where('nama', 'like', "%{$search}%"))
                  ->orWhereHas('bahanBakuSuppliers', fn($s) => $s->where('nama', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('bahan_baku')) {
            $bahanBaku = str_replace('_', ' ', $request->bahan_baku);
            $query->whereHas('bahanBakuSuppliers', fn($s) => $s->where('nama', 'like', "%{$bahanBaku}%"));
        }
    }

    private function applySorting($query, Request $request): void
    {
        if ($request->filled('sort_bahan_baku')) {
            $query->withCount('bahanBakuSuppliers')->orderBy('bahan_baku_suppliers_count', $request->sort_bahan_baku == 'terbanyak' ? 'desc' : 'asc');
        } elseif ($request->filled('sort_stok')) {
            $query->withSum('bahanBakuSuppliers', 'stok')->orderBy('bahan_baku_suppliers_sum_stok', $request->sort_stok == 'terbanyak' ? 'desc' : 'asc');
        } else {
            $query->orderBy('nama', 'asc');
        }
    }

    private function authorizeAccess(string $action, ?Supplier $supplier = null): void
    {
        $user = Auth::user();
        $isGlobalAdmin = in_array($user->role, ['direktur', 'manager_purchasing']);
        
        if ($action === 'create' && !$isGlobalAdmin && $user->role !== 'staff_purchasing') {
            abort(403, 'Anda tidak memiliki akses untuk menambah supplier.');
        }
        
        if (in_array($action, ['edit', 'delete']) && $supplier) {
            $isPic = ($user->role === 'staff_purchasing' && $supplier->pic_purchasing_id === $user->id);
            if (!$isGlobalAdmin && !$isPic) {
                throw new \Exception("Anda tidak memiliki akses untuk melakukan aksi ini pada supplier.");
            }
        }
    }

    private function generateSupplierSlug(string $nama, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($nama);
        $slug = $baseSlug;
        $counter = 1;
        $query = Supplier::where('slug', $slug);
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            $query = Supplier::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }
        return $slug;
    }

    private function parseNumeric(?string $value): float
    {
        return (float) str_replace(['.', ','], '', $value ?? '0');
    }

    private function processBahanBakuUpdates(Supplier $supplier, array $bahanBakuData, ?int $klienId): array
    {
        $existingBahanBaku = $supplier->bahanBakuSuppliers()->get()->keyBy('id');
        $submittedIds = [];

        foreach ($bahanBakuData as $item) {
            if (empty($item['nama']) || empty($item['satuan'])) {
                continue;
            }

            $nama = trim($item['nama']);
            $harga = $this->parseNumeric($item['harga_per_satuan']);
            $stok = $this->parseNumeric($item['stok']);

            if (!empty($item['id']) && $existingBahanBaku->has($item['id'])) {
                $this->updateExistingBahanBaku($supplier, $existingBahanBaku[$item['id']], $nama, $item['satuan'], $stok, $harga, $klienId);
                $submittedIds[] = $item['id'];
            } else {
                $newId = $this->createNewBahanBaku($supplier, $nama, $item['satuan'], $stok, $harga, $klienId);
                $submittedIds[] = $newId;
            }
        }

        return $submittedIds;
    }

    private function updateExistingBahanBaku(Supplier $supplier, BahanBakuSupplier $bahanBaku, string $nama, string $satuan, float $stok, float $hargaBaru, ?int $klienId): void
    {
        $slug = ($bahanBaku->nama !== $nama) ? BahanBakuSupplier::generateUniqueSlug($nama, $supplier->id, $bahanBaku->id) : $bahanBaku->slug;
        
        $bahanBaku->update(['nama' => $nama, 'slug' => $slug, 'satuan' => $satuan, 'stok' => $stok]);

        if ($klienId === null) {
            $hargaLama = (float) $bahanBaku->harga_per_satuan;
            $bahanBaku->update(['harga_per_satuan' => $hargaBaru]);
            if ($hargaLama != $hargaBaru) {
                RiwayatHargaBahanBaku::catatPerubahanHarga($bahanBaku->id, $hargaLama, $hargaBaru, "Update harga GLOBAL bahan baku '{$nama}' melalui edit supplier");
            }
        } else {
            $hargaKlien = BahanBakuSupplierKlien::firstOrNew(['bahan_baku_supplier_id' => $bahanBaku->id, 'klien_id' => $klienId]);
            $hargaLama = $hargaKlien->exists ? (float) $hargaKlien->harga_per_satuan : (float) $bahanBaku->harga_per_satuan;
            
            $hargaKlien->harga_per_satuan = $hargaBaru;
            $hargaKlien->save();
            
            if ($hargaLama != $hargaBaru) {
                $klienInfo = Klien::find($klienId);
                $klienNama = $klienInfo ? $klienInfo->nama . ($klienInfo->cabang ? ' - ' . $klienInfo->cabang : '') : 'Klien';
                RiwayatHargaBahanBaku::catatPerubahanHarga($bahanBaku->id, $hargaLama, $hargaBaru, "Update harga untuk {$klienNama} - bahan baku '{$nama}' melalui edit supplier", null, null, $klienId);
            }
        }
    }

    private function createNewBahanBaku(Supplier $supplier, string $nama, string $satuan, float $stok, float $harga, ?int $klienId): int
    {
        $newBahanBaku = $supplier->bahanBakuSuppliers()->create([
            'nama' => $nama,
            'slug' => BahanBakuSupplier::generateUniqueSlug($nama, $supplier->id),
            'satuan' => $satuan,
            'harga_per_satuan' => $harga,
            'stok' => $stok,
        ]);

        if ($klienId === null) {
            RiwayatHargaBahanBaku::catatPerubahanHarga($newBahanBaku->id, null, $harga, "Bahan baku baru '{$nama}' ditambahkan (harga global)");
        } else {
            BahanBakuSupplierKlien::create(['bahan_baku_supplier_id' => $newBahanBaku->id, 'klien_id' => $klienId, 'harga_per_satuan' => $harga]);
            $klienInfo = Klien::find($klienId);
            $klienNama = $klienInfo ? $klienInfo->nama . ($klienInfo->cabang ? ' - ' . $klienInfo->cabang : '') : 'Klien';
            RiwayatHargaBahanBaku::catatPerubahanHarga($newBahanBaku->id, null, $harga, "Bahan baku baru '{$nama}' ditambahkan untuk {$klienNama}", null, null, $klienId);
        }

        return $newBahanBaku->id;
    }

    private function formatRiwayatData($riwayatHargaData): array
    {
        return $riwayatHargaData->map(fn($item) => [
            'id' => $item->id,
            'tanggal' => $item->tanggal_perubahan->format('Y-m-d'),
            'harga' => (float) $item->harga_baru,
            'harga_lama' => $item->harga_lama ? (float) $item->harga_lama : null,
            'selisih_harga' => (float) $item->selisih_harga,
            'persentase_perubahan' => (float) $item->persentase_perubahan,
            'tipe_perubahan' => $item->tipe_perubahan,
            'keterangan' => $item->keterangan,
            'klien_id' => $item->klien_id,
            'klien_nama' => $item->klien ? $item->klien->nama : 'Global',
            'klien_cabang' => $item->klien ? $item->klien->cabang : null,
            'formatted_tanggal' => $item->tanggal_perubahan->format('d M Y'),
            'formatted_hari' => $item->tanggal_perubahan->format('l'),
            'formatted_harga' => number_format((float) $item->harga_baru, 0, ',', '.'),
            'formatted_harga_lama' => $item->harga_lama ? number_format((float) $item->harga_lama, 0, ',', '.') : null,
            'formatted_selisih' => number_format(abs((float) $item->selisih_harga), 0, ',', '.'),
            'color_class' => $item->color_class,
            'badge_class' => $item->badge_class,
            'icon' => $item->icon,
        ])->toArray();
    }

    private function prepareChartDataByKlien(array $riwayatHarga, array &$allDates): array
    {
        $chartDataByKlien = [];
        
        foreach ($riwayatHarga as $item) {
            $klienKey = $item['klien_id'] ?? 'global';
            $klienLabel = $item['klien_nama'] . ($item['klien_cabang'] ? ' - ' . $item['klien_cabang'] : '');
            
            if (!isset($chartDataByKlien[$klienKey])) {
                $chartDataByKlien[$klienKey] = ['label' => $klienLabel, 'dataByDate' => []];
            }
            $chartDataByKlien[$klienKey]['dataByDate'][$item['tanggal']] = $item['harga'];
            if (!in_array($item['tanggal'], $allDates)) {
                $allDates[] = $item['tanggal'];
            }
        }
        
        sort($allDates);
        
        foreach ($chartDataByKlien as $klienKey => &$klienData) {
            $dates = array_keys($klienData['dataByDate']);
            sort($dates);
            $firstDate = $dates[0] ?? null;
            $lastDate = end($dates) ?: null;
            
            $alignedData = [];
            $lastKnownValue = null;
            $hasStarted = false;
            
            foreach ($allDates as $date) {
                if (isset($klienData['dataByDate'][$date])) {
                    $hasStarted = true;
                    $lastKnownValue = $klienData['dataByDate'][$date];
                }
                
                if ($hasStarted && $date <= $lastDate) {
                    $alignedData[] = ['tanggal' => $date, 'harga' => $lastKnownValue];
                } elseif (!$hasStarted && $date < $firstDate) {
                    $alignedData[] = ['tanggal' => $date, 'harga' => null];
                } elseif ($date > $lastDate) {
                    $alignedData[] = ['tanggal' => $date, 'harga' => $lastKnownValue];
                } else {
                    $alignedData[] = ['tanggal' => $date, 'harga' => null];
                }
            }
            $klienData['data'] = $alignedData;
            unset($klienData['dataByDate']);
        }
        return $chartDataByKlien;
    }

    private function getValidationRules(string $mode): array
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'pic_purchasing_id' => 'nullable|exists:users,id',
        ];

        if ($mode === 'store') {
            $rules['bahan_baku'] = 'required|array|min:1';
            $rules['bahan_baku.*.nama'] = 'required|string|max:255';
            $rules['bahan_baku.*.satuan'] = 'required|string|max:50';
            $rules['bahan_baku.*.harga_per_satuan'] = 'required|numeric|min:0';
            $rules['bahan_baku.*.stok'] = 'required|numeric|min:0';
        } else {
            $rules['edit_harga_untuk'] = 'required|string';
            $rules['bahan_baku'] = 'nullable|array';
            $rules['bahan_baku.*.nama'] = 'required_with:bahan_baku|string|max:255';
            $rules['bahan_baku.*.satuan'] = 'required_with:bahan_baku|string|max:50';
            $rules['bahan_baku.*.harga_per_satuan'] = 'required_with:bahan_baku|numeric|min:0';
            $rules['bahan_baku.*.stok'] = 'required_with:bahan_baku|numeric|min:0';
        }
        return $rules;
    }

    private function getValidationMessages(): array
    {
        return [
            'nama.required' => 'Nama supplier harus diisi',
            'bahan_baku.required' => 'Minimal satu bahan baku harus ditambahkan',
            'bahan_baku.min' => 'Minimal satu bahan baku harus ditambahkan',
            'edit_harga_untuk.required' => 'Pilihan harga harus dipilih',
            'bahan_baku.*.nama.required' => 'Nama bahan baku harus diisi',
            'bahan_baku.*.nama.required_with' => 'Nama bahan baku harus diisi',
            'bahan_baku.*.satuan.required' => 'Satuan bahan baku harus dipilih',
            'bahan_baku.*.satuan.required_with' => 'Satuan bahan baku harus dipilih',
            'bahan_baku.*.harga_per_satuan.required' => 'Harga per satuan harus diisi',
            'bahan_baku.*.harga_per_satuan.required_with' => 'Harga per satuan harus diisi',
            'bahan_baku.*.stok.required' => 'Stok harus diisi',
            'bahan_baku.*.stok.required_with' => 'Stok harus diisi',
        ];
    }
}