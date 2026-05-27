@extends('layouts.app')
@section('title', 'Supplier - Kamil Maju Persada')
@section('content')

{{-- Flash Messages --}}
@if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-5 text-sm">
        <i class="fas fa-exclamation-circle text-red-400"></i>
        <span>{{ session('error') }}</span>
        <button type="button" onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

<x-welcome-banner title="Supplier" subtitle="Kelola data supplier perusahaan" icon="fas fa-industry" />

<x-breadcrumb :items="[
    ['title' => 'Purchasing', 'url' => '#'],
    'Supplier'
]" />

{{-- Search & Filter --}}
<div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
    {{-- Search --}}
    <div class="flex gap-2 mb-4">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text"
                   id="searchInput"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Cari nama supplier, PIC purchasing, atau bahan baku..."
                   onkeypress="handleSearchKeyPress(event)"
                   class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-500 transition">
        </div>
        <button type="button" onclick="applyFilters()"
                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
            <i class="fas fa-search mr-1.5"></i>Cari
        </button>
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Urutkan Bahan Baku</label>
            <select id="sortBahanBaku" name="sort_bahan_baku" onchange="applyFilters()"
                    class="w-full py-2.5 px-3 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-500 bg-white transition">
                <option value="">Default</option>
                <option value="terbanyak" {{ request('sort_bahan_baku') == 'terbanyak' ? 'selected' : '' }}>Terbanyak</option>
                <option value="tersedikit" {{ request('sort_bahan_baku') == 'tersedikit' ? 'selected' : '' }}>Tersedikit</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Urutkan Stok</label>
            <select id="sortStok" name="sort_stok" onchange="applyFilters()"
                    class="w-full py-2.5 px-3 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-500 bg-white transition">
                <option value="">Default</option>
                <option value="terbanyak" {{ request('sort_stok') == 'terbanyak' ? 'selected' : '' }}>Terbanyak</option>
                <option value="tersedikit" {{ request('sort_stok') == 'tersedikit' ? 'selected' : '' }}>Tersedikit</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Filter Bahan Baku</label>
            <select id="bahanBakuFilter" name="bahan_baku" onchange="applyFilters()"
                    class="w-full py-2.5 px-3 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-500 bg-white transition">
                <option value="">Semua Bahan Baku</option>
                @if(isset($bahanBakuList))
                    @foreach($bahanBakuList as $bahan)
                        <option value="{{ $bahan['value'] }}" {{ request('bahan_baku') == $bahan['value'] ? 'selected' : '' }}>{{ $bahan['label'] }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>

    {{-- Active Filters --}}
    <div id="activeFilters" class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100" style="display: none !important;"></div>
</div>

{{-- Header & Add Button --}}
<div class="flex items-center justify-between mb-4">
    <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
        <i class="fas fa-list text-green-600"></i>
        Daftar Supplier
    </h2>
    @if(in_array(auth()->user()->role, ['direktur', 'manager_purchasing', 'staff_purchasing']))
        <a href="{{ route('supplier.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
            <i class="fas fa-plus"></i>Tambah Supplier
        </a>
    @endif
</div>

{{-- Supplier List --}}
@if($suppliers->count() > 0)
    <div class="space-y-3">
        @foreach($suppliers as $index => $supplier)
            @php
                $avgRating = $supplier->getAverageRating();
                $totalReviews = $supplier->getTotalReviews();
                $user = auth()->user();
                $canEdit = false;
                $canDelete = false;
                if (in_array($user->role, ['direktur', 'manager_purchasing'])) {
                    $canEdit = true; $canDelete = true;
                } elseif ($user->role === 'staff_purchasing' && $supplier->pic_purchasing_id === $user->id) {
                    $canEdit = true; $canDelete = true;
                }
            @endphp

            <div class="bg-white border border-gray-200 border-l-4 border-l-green-500 rounded-xl overflow-hidden hover:shadow-md transition-shadow duration-200">

                {{-- ── MOBILE ── --}}
                <div class="block sm:hidden">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">{{ $supplier->nama }}</h3>
                                <p class="text-xs text-green-600 mt-0.5">Supplier #{{ $suppliers->firstItem() + $index }}</p>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <span class="font-bold text-green-600">{{ $supplier->bahanBakuSuppliers->count() }} <span class="font-normal text-gray-500">Bahan</span></span>
                                @if($avgRating)
                                    <span class="flex items-center gap-1 text-yellow-500 font-bold">
                                        <i class="fas fa-star text-xs"></i>{{ $avgRating }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-1.5 text-xs text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-green-500 w-4 text-center"></i>
                                <span class="truncate">{{ $supplier->alamat ?? 'Tidak tersedia' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-phone text-green-500 w-4 text-center"></i>
                                <span>{{ $supplier->no_hp ?? 'Tidak tersedia' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user-tie text-green-500 w-4 text-center"></i>
                                <span class="truncate">{{ $supplier->picPurchasing->nama ?? 'Belum ditentukan' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleProductList({{ $supplier->id }})"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition">
                                <i class="fas fa-box text-xs"></i>Bahan Baku
                                <i class="fas fa-chevron-down text-xs transition-transform" id="chevron-{{ $supplier->id }}"></i>
                            </button>
                            @if($totalReviews > 0)
                                <a href="{{ route('supplier.reviews', $supplier->slug) }}"
                                   class="flex items-center gap-1 px-3 py-1.5 text-yellow-700 bg-yellow-50 border border-yellow-200 text-xs font-medium rounded-lg">
                                    <i class="fas fa-star text-xs"></i>{{ $totalReviews }} Review
                                </a>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">{{ $supplier->updated_at->format('d/m/Y') }}</span>
                            @if($canEdit)
                                <a href="{{ route('supplier.edit', $supplier->slug) }}"
                                   class="w-7 h-7 flex items-center justify-center text-amber-600 hover:bg-amber-50 rounded-lg transition">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                            @endif
                            @if($canDelete)
                                <button type="button" onclick="openDeleteModal('{{ $supplier->slug }}', '{{ $supplier->nama }}')"
                                        class="w-7 h-7 flex items-center justify-center text-red-500 hover:bg-red-50 rounded-lg transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── DESKTOP ── --}}
                <div class="hidden sm:block p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">{{ $supplier->nama }}</h3>
                            <p class="text-xs text-green-600 mt-0.5">Supplier #{{ $suppliers->firstItem() + $index }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-center bg-green-50 rounded-lg px-4 py-2">
                                <p class="text-xl font-bold text-green-700">{{ $supplier->bahanBakuSuppliers->count() }}</p>
                                <p class="text-xs text-green-600">Bahan Baku</p>
                            </div>
                            @if($avgRating)
                                <div class="text-center bg-yellow-50 rounded-lg px-4 py-2">
                                    <p class="text-xl font-bold text-yellow-600 flex items-center justify-center gap-1">
                                        <i class="fas fa-star text-sm"></i>{{ $avgRating }}
                                    </p>
                                    <p class="text-xs text-yellow-600">{{ $totalReviews }} Review</p>
                                </div>
                            @else
                                <div class="text-center bg-gray-50 rounded-lg px-4 py-2">
                                    <p class="text-xl font-bold text-gray-300"><i class="fas fa-star text-sm"></i> –</p>
                                    <p class="text-xs text-gray-400">Belum ada review</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2.5">
                            <i class="fas fa-map-marker-alt text-green-500 text-sm"></i>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">Alamat</p>
                                <p class="text-sm text-gray-800 truncate">{{ $supplier->alamat ?? 'Tidak tersedia' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2.5">
                            <i class="fas fa-phone text-green-500 text-sm"></i>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">No HP</p>
                                <p class="text-sm text-gray-800">{{ $supplier->no_hp ?? 'Tidak tersedia' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2.5">
                            <i class="fas fa-user-tie text-green-500 text-sm"></i>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">PIC Procurement</p>
                                <p class="text-sm text-gray-800 truncate">{{ $supplier->picPurchasing->nama ?? 'Belum ditentukan' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleProductList({{ $supplier->id }})"
                                    class="flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                <i class="fas fa-box text-xs"></i>
                                Lihat Daftar Bahan Baku
                                <i class="fas fa-chevron-down text-xs transition-transform" id="chevron-desktop-{{ $supplier->id }}"></i>
                            </button>
                            @if($totalReviews > 0)
                                <a href="{{ route('supplier.reviews', $supplier->slug) }}"
                                   class="flex items-center gap-2 px-3 py-2 text-yellow-700 bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 text-sm font-medium rounded-lg transition">
                                    <i class="fas fa-star text-xs"></i>{{ $totalReviews }} Review{{ $totalReviews > 1 ? 's' : '' }}
                                </a>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                <i class="far fa-clock"></i>{{ $supplier->updated_at->format('d/m/Y') }}
                            </span>
                            @if($canEdit)
                                <a href="{{ route('supplier.edit', $supplier->slug) }}"
                                   class="w-8 h-8 flex items-center justify-center text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                   title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                            @endif
                            @if($canDelete)
                                <button type="button"
                                        onclick="openDeleteModal('{{ $supplier->slug }}', '{{ $supplier->nama }}')"
                                        class="w-8 h-8 flex items-center justify-center text-red-500 hover:bg-red-50 rounded-lg transition"
                                        title="Hapus">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Bahan Baku Collapsible --}}
                <div id="product-list-{{ $supplier->id }}" class="hidden border-t border-gray-100 bg-gray-50">
                    {{-- Mobile --}}
                    <div class="block sm:hidden p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Bahan Baku</p>
                        <div class="space-y-2">
                            @forelse($supplier->bahanBakuSuppliers as $bahanBaku)
                                <div class="bg-white rounded-lg p-3 border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-sm font-semibold text-gray-900">{{ $bahanBaku->nama }}</p>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-green-600">Rp {{ number_format($bahanBaku->harga_per_satuan, 0, ',', '.') }}</p>
                                            <p class="text-xs text-gray-400">/ {{ $bahanBaku->satuan }}</p>
                                        </div>
                                    </div>
                                    <button type="button"
                                            onclick="redirectToRiwayatHarga('{{ $supplier->slug }}', '{{ $bahanBaku->slug }}')"
                                            class="w-full text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center justify-center gap-1.5">
                                        <i class="fas fa-chart-line"></i>Lihat Riwayat Harga
                                    </button>
                                </div>
                            @empty
                                <div class="text-center py-6 text-gray-400">
                                    <i class="fas fa-inbox text-2xl mb-1 block"></i>
                                    <p class="text-xs">Belum ada bahan baku</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Desktop --}}
                    <div class="hidden sm:block p-5">
                        <p class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-box text-green-500"></i>Daftar Bahan Baku
                        </p>
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Bahan Baku</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($supplier->bahanBakuSuppliers as $bahanBaku)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="w-7 h-7 bg-green-100 text-green-700 rounded-full flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
                                                    <span class="text-sm text-gray-900">{{ $bahanBaku->nama }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <p class="text-sm font-semibold text-green-600">Rp {{ number_format($bahanBaku->harga_per_satuan, 0, ',', '.') }}</p>
                                                <p class="text-xs text-gray-400">per {{ $bahanBaku->satuan }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button"
                                                        onclick="redirectToRiwayatHarga('{{ $supplier->slug }}', '{{ $bahanBaku->slug }}')"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                                    <i class="fas fa-chart-line"></i>Riwayat Harga
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-8 text-center text-gray-400">
                                                <i class="fas fa-inbox text-3xl block mb-2"></i>
                                                <p class="text-sm">Belum ada bahan baku terdaftar</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($suppliers->hasPages())
        <div class="bg-white border border-gray-200 rounded-xl p-4 mt-4">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="text-sm text-gray-600">
                    Menampilkan <span class="font-semibold text-green-600">{{ $suppliers->firstItem() ?? 0 }}</span>–<span class="font-semibold text-green-600">{{ $suppliers->lastItem() ?? 0 }}</span>
                    dari <span class="font-semibold text-green-600">{{ $suppliers->total() }}</span> supplier
                </p>
                <div class="flex items-center gap-1">
                    @if ($suppliers->onFirstPage())
                        <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
                        </span>
                    @else
                        <a href="{{ $suppliers->appends(request()->query())->previousPageUrl() }}"
                           class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition">
                            <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
                        </a>
                    @endif

                    @if($suppliers->lastPage() > 1)
                        <div class="hidden sm:flex items-center gap-1">
                            @foreach ($suppliers->appends(request()->query())->getUrlRange(1, $suppliers->lastPage()) as $page => $url)
                                @if ($page == $suppliers->currentPage())
                                    <span class="px-3 py-1.5 text-sm font-semibold text-white bg-green-600 border border-green-600 rounded-lg">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition">{{ $page }}</a>
                                @endif
                            @endforeach
                        </div>
                        <span class="sm:hidden px-3 py-1.5 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-lg">
                            {{ $suppliers->currentPage() }} / {{ $suppliers->lastPage() }}
                        </span>
                    @endif

                    @if ($suppliers->hasMorePages())
                        <a href="{{ $suppliers->appends(request()->query())->nextPageUrl() }}"
                           class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition">
                            Selanjutnya<i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    @else
                        <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                            Selanjutnya<i class="fas fa-chevron-right ml-1"></i>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif

@else
    <div class="text-center py-16 text-gray-400">
        <i class="fas fa-inbox text-5xl block mb-3"></i>
        <h3 class="text-base font-semibold text-gray-600 mb-1">Tidak ada data supplier</h3>
        <p class="text-sm">
            @if(request('search'))
                Tidak ditemukan supplier dengan kata kunci "{{ request('search') }}"
            @else
                Belum ada supplier yang terdaftar di sistem
            @endif
        </p>
    </div>
@endif

{{-- Delete Modal --}}
<div id="deleteModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center">
    <div class="relative mx-auto p-6 w-11/12 sm:w-96 bg-white rounded-xl shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">Konfirmasi Hapus</h3>
            </div>
            <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <p class="text-sm text-gray-600 mb-3">Apakah Anda yakin ingin menghapus supplier berikut?</p>
        <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2.5 mb-3">
            <i class="fas fa-building text-red-400"></i>
            <span class="font-semibold text-red-700 text-sm" id="supplierNameToDelete">-</span>
        </div>
        <p class="text-xs text-gray-400 mb-5">
            <i class="fas fa-info-circle mr-1"></i>
            Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait supplier ini.
        </p>

        <div class="flex items-center justify-end gap-2">
            <button type="button" onclick="closeDeleteModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                <i class="fas fa-times mr-1.5"></i>Batal
            </button>
            <button type="button" onclick="confirmDelete()"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                <i class="fas fa-trash mr-1.5"></i>Hapus Supplier
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function handleSearchKeyPress(event) {
    if (event.key === 'Enter') { event.preventDefault(); applyFilters(); }
}

function applyFilters() {
    const params = new URLSearchParams(window.location.search);
    ['search','sort_bahan_baku','sort_stok','bahan_baku','page'].forEach(k => params.delete(k));

    const search = document.getElementById('searchInput').value;
    const sortBahanBaku = document.getElementById('sortBahanBaku').value;
    const sortStok = document.getElementById('sortStok').value;
    const bahanBaku = document.getElementById('bahanBakuFilter').value;

    if (search) params.set('search', search);
    if (sortBahanBaku) params.set('sort_bahan_baku', sortBahanBaku);
    if (sortStok) params.set('sort_stok', sortStok);
    if (bahanBaku) params.set('bahan_baku', bahanBaku);

    showActiveFilters(search, sortBahanBaku, sortStok, bahanBaku);
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
}

function showActiveFilters(search, sortBahanBaku, sortStok, bahanBaku) {
    const el = document.getElementById('activeFilters');
    const tags = [];

    if (search) tags.push(`<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-search"></i>"${search}"</span>`);
    if (sortBahanBaku) tags.push(`<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-sort"></i>Bahan ${sortBahanBaku === 'terbanyak' ? 'Terbanyak' : 'Tersedikit'}</span>`);
    if (sortStok) tags.push(`<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"><i class="fas fa-sort"></i>Stok ${sortStok === 'terbanyak' ? 'Terbanyak' : 'Tersedikit'}</span>`);
    if (bahanBaku) tags.push(`<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-box"></i>${bahanBaku.replace('_',' ')}</span>`);

    if (tags.length) {
        el.innerHTML = `<span class="text-xs font-semibold text-gray-500">Filter aktif:</span>` + tags.join('');
        el.style.display = 'flex';
    } else {
        el.style.display = 'none';
    }
}

function toggleProductList(supplierId) {
    const list = document.getElementById(`product-list-${supplierId}`);
    const cm = document.getElementById(`chevron-${supplierId}`);
    const cd = document.getElementById(`chevron-desktop-${supplierId}`);
    const hidden = list.classList.toggle('hidden');
    [cm, cd].forEach(c => c && c.classList.toggle('rotate-180', !hidden));
}

function redirectToRiwayatHarga(supplierSlug, bahanBakuSlug) {
    window.location.href = `/procurement/supplier/${supplierSlug}/bahan-baku/${bahanBakuSlug}/riwayat-harga`;
}

let supplierSlugToDelete = null;

function openDeleteModal(slug, name) {
    supplierSlugToDelete = slug;
    document.getElementById('supplierNameToDelete').textContent = name;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    supplierSlugToDelete = null;
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function confirmDelete() {
    if (!supplierSlugToDelete) { alert('Error: Data tidak lengkap.'); closeDeleteModal(); return; }
    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i>Menghapus...';
    btn.disabled = true;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/procurement/supplier/${supplierSlugToDelete}`;

    const csrf = document.querySelector('meta[name="csrf-token"]');
    if (csrf) {
        const t = document.createElement('input');
        t.type = 'hidden'; t.name = '_token'; t.value = csrf.getAttribute('content');
        form.appendChild(t);
    }
    const m = document.createElement('input');
    m.type = 'hidden'; m.name = '_method'; m.value = 'DELETE';
    form.appendChild(m);

    document.body.appendChild(form);
    form.submit();
}

document.addEventListener('click', e => { if (e.target === document.getElementById('deleteModal')) closeDeleteModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });

document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('searchInput').value;
    const sortBahanBaku = document.getElementById('sortBahanBaku').value;
    const sortStok = document.getElementById('sortStok').value;
    const bahanBaku = document.getElementById('bahanBakuFilter').value;
    showActiveFilters(search, sortBahanBaku, sortStok, bahanBaku);
});

@if(session('success'))
document.addEventListener('DOMContentLoaded', function () {
    let message = @json(session('success'));
    let actionType = 'default';
    if (message.includes('ditambahkan') || message.includes('dibuat')) actionType = 'create';
    else if (message.includes('diperbarui') || message.includes('diubah')) actionType = 'edit';
    else if (message.includes('dihapus')) actionType = 'delete';
    showSuccessModal(actionType, message, 'Operasi pada data supplier berhasil dilakukan.', '', true);
});
@endif

@if(session('error'))
document.addEventListener('DOMContentLoaded', function () {
    alert(@json(session('error')));
});
@endif
</script>

<style>
.rotate-180 { transform: rotate(180deg); }
.fas.fa-chevron-down { transition: transform 0.2s ease; }
</style>
@endpush

@include('components.success-modal')