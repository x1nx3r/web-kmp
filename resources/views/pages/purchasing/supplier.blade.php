@extends('layouts.app')
@section('title', 'Supplier - Kamil Maju Persada')
@section('content')



{{-- Flash Messages - Hidden, using modal instead --}}
@if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 sm:mb-6 flex items-start hidden">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-400 text-xl"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-green-800">
                {{ session('success') }}
            </p>
        </div>
        <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
                <button type="button" onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 sm:mb-6 flex items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-red-800">
                {{ session('error') }}
            </p>
        </div>
        <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
                <button type="button" onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
    </div>
@endif

<x-welcome-banner title="Supplier" subtitle="Kelola data supplier perusahaan" icon="fas fa-industry" />
{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['title' => 'Purchasing', 'url' => '#'],
    'Supplier'
]" />
{{-- Search and Filter Section --}}
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 mb-3 sm:mb-6">
    <div class="space-y-3 sm:space-y-6">
        {{-- Search Section --}}
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
            {{-- Search Input --}}
            <div class="flex-1">
                <label class="flex items-center text-xs sm:text-sm font-bold text-green-700 mb-1 sm:mb-3">
                    <div class="w-4 h-4 sm:w-6 sm:h-6 bg-green-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                        <i class="fas fa-search text-white text-xs"></i>
                    </div>
                    Pencarian
                </label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           value="{{ request('search') }}" 
                           placeholder="Cari nama supplier, PIC purchasing, atau bahan baku..." 
                           class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm"
                           onkeyup="debounceSearch()">
                    <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                        <div class="w-3 h-3 sm:w-6 sm:h-6 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-search text-green-500 text-xs sm:text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="rounded-lg sm:rounded-xl p-2 sm:p-4">
            <h3 class="flex items-center text-xs sm:text-sm font-bold text-green-700 mb-2 sm:mb-4">
                <div class="w-4 h-4 sm:w-6 sm:h-6 bg-green-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                    <i class="fas fa-filter text-white text-xs"></i>
                </div>
                Filter & Urutan
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4">
                {{-- Bahan Baku Filter --}}
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                        <i class="fas fa-sort mr-1 sm:mr-2 text-green-500 text-xs"></i>
                        Urutkan Bahan Baku
                    </label>
                    <select name="sort_bahan_baku" id="sortBahanBaku" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFilters()">
                        <option value="">Default</option>
                        <option value="terbanyak" {{ request('sort_bahan_baku') == 'terbanyak' ? 'selected' : '' }}>Terbanyak</option>
                        <option value="tersedikit" {{ request('sort_bahan_baku') == 'tersedikit' ? 'selected' : '' }}>Tersedikit</option>
                    </select>
                </div>

                {{-- Stok Filter --}}
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                        <i class="fas fa-sort mr-1 sm:mr-2 text-green-500 text-xs"></i>
                        Urutkan Stok
                    </label>
                    <select name="sort_stok" id="sortStok" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFilters()">
                        <option value="">Default</option>
                        <option value="terbanyak" {{ request('sort_stok') == 'terbanyak' ? 'selected' : '' }}>Terbanyak</option>
                        <option value="tersedikit" {{ request('sort_stok') == 'tersedikit' ? 'selected' : '' }}>Tersedikit</option>
                    </select>
                </div>

                {{-- Bahan Baku Spesifik Filter --}}
                <div class="sm:col-span-1 lg:col-span-1">
                    <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                        <i class="fas fa-box mr-1 sm:mr-2 text-green-500 text-xs"></i>
                        Filter Bahan Baku
                    </label>
                    <select name="bahan_baku" id="bahanBakuFilter" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFilters()">
                        <option value="">Semua Bahan Baku</option>
                        @if(isset($bahanBakuList))
                            @foreach($bahanBakuList as $bahan)
                                <option value="{{ $bahan['value'] }}" {{ request('bahan_baku') == $bahan['value'] ? 'selected' : '' }}>{{ $bahan['label'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>

        {{-- Active Filters Display --}}
        <div id="activeFilters" class="flex flex-wrap gap-2" style="display: none;">
            <span class="text-xs sm:text-sm font-bold text-green-700">Filter aktif:</span>
        </div>
    </div>
</div>

{{-- Add Button --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
    <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-500 rounded-full flex items-center justify-center mr-2 sm:mr-3">
            <i class="fas fa-list text-white text-xs sm:text-sm"></i>
        </div>
        Daftar Supplier
    </h2>
    <a href="{{ route('supplier.create') }}" class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg sm:rounded-xl transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-semibold text-sm">
        <i class="fas fa-plus mr-2"></i>Tambah Supplier
    </a>
</div>

{{-- Supplier List --}}
<div class="space-y-4">
    @if($suppliers->count() > 0)
{{-- Supplier Cards List --}}
<div class="space-y-1 sm:space-y-4">
    @foreach($suppliers as $index => $supplier)
        {{-- Mobile: Simple List Item / Desktop: Full Card --}}
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-gray-300 border-l-4 border-l-green-500 hover:border-l-green-600">
            {{-- Mobile List View --}}
            <div class="block sm:hidden">
                <div class="p-3 border-b border-gray-100">
                    {{-- Mobile Header --}}
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-gray-900">{{ $supplier->nama }}</h3>
                            <p class="text-xs text-green-600 mt-1">
                                <i class="fas fa-building mr-1"></i>
                                Supplier #{{ $suppliers->firstItem() + $index }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-1">
                            <span class="text-xs font-bold text-green-600">{{ $supplier->bahanBakuSuppliers->count() }}</span>
                            <span class="text-xs text-green-600">Bahan</span>
                            <span class="mx-1 text-gray-300">|</span>
                            <span class="text-xs font-bold text-blue-600">{{ number_format($supplier->bahanBakuSuppliers->sum('stok'), 0, ',', '.') }}</span>
                            <span class="text-xs text-blue-600">Stok</span>
                            @php
                                $avgRating = $supplier->getAverageRating();
                            @endphp
                            @if($avgRating)
                                <span class="mx-1 text-gray-300">|</span>
                                <i class="fas fa-star text-yellow-500 text-xs"></i>
                                <span class="text-xs font-bold text-yellow-600">{{ $avgRating }}</span>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Mobile Contact Info --}}
                    <div class="space-y-1">
                        <div class="flex items-center text-xs text-gray-600">
                            <i class="fas fa-map-marker-alt w-4 text-green-500 mr-2"></i>
                            <span class="truncate">{{ $supplier->alamat ?? 'Tidak tersedia' }}</span>
                        </div>
                        <div class="flex items-center text-xs text-gray-600">
                            <i class="fas fa-phone w-4 text-green-500 mr-2"></i>
                            <span>{{ $supplier->no_hp ?? 'Tidak tersedia' }}</span>
                        </div>
                        <div class="flex items-center text-xs text-gray-600">
                            <i class="fas fa-user-tie w-4 text-green-500 mr-2"></i>
                            <span class="truncate">{{ $supplier->picPurchasing->nama ?? 'Belum ditentukan' }}</span>
                        </div>
                    </div>
                </div>
                
                {{-- Mobile Actions --}}
                <div class="p-3 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <button type="button" 
                                    onclick="toggleProductList({{ $supplier->id }})"
                                    class="flex items-center px-3 py-2 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded-lg transition-all duration-200">
                                <i class="fas fa-box mr-1"></i>
                                <span>Bahan Baku</span>
                                <i class="fas fa-chevron-down transform transition-transform ml-2 text-xs" id="chevron-{{ $supplier->id }}"></i>
                            </button>
                            
                            @php
                                $totalReviews = $supplier->getTotalReviews();
                            @endphp
                            @if($totalReviews > 0)
                                <a href="{{ route('supplier.reviews', $supplier->slug) }}" 
                                   class="flex items-center px-3 py-2 text-xs font-medium text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg transition-all duration-200">
                                    <i class="fas fa-star mr-1"></i>
                                    <span>{{ $totalReviews }} Review{{ $totalReviews > 1 ? 's' : '' }}</span>
                                </a>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500">{{ $supplier->updated_at->format('d/m/Y') }}</span>
                            <a href="{{ route('supplier.edit', $supplier->slug) }}" class="w-6 h-6 flex items-center justify-center text-yellow-600 bg-yellow-50 rounded hover:bg-yellow-100 transition-colors" title="Edit">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <button type="button" class="w-6 h-6 flex items-center justify-center text-red-600 bg-red-50 rounded" onclick="openDeleteModal('{{ $supplier->slug }}', '{{ $supplier->nama }}')">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Desktop Card View --}}
            <div class="hidden sm:block">
                <div class="p-5">
                    {{-- Header Section --}}
                    <div class="flex items-center justify-between mb-4">
                        {{-- Left: Title & Subtitle --}}
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $supplier->nama }}</h3>
                            <p class="text-xs text-green-600 font-medium mt-0.5">
                                <i class="fas fa-building mr-1"></i>
                                Supplier #{{ $suppliers->firstItem() + $index }}
                            </p>
                        </div>

                        {{-- Right: Stats Cards --}}
                        <div class="flex items-center gap-3">
                            {{-- Total Bahan Baku --}}
                            <div class="bg-green-50 rounded-lg px-3 py-2 min-w-[80px] text-center">
                                <p class="text-xl font-bold text-green-600">{{ $supplier->bahanBakuSuppliers->count() }}</p>
                                <p class="text-xs text-green-700">Total Bahan Baku</p>
                            </div>
                            
                            
                            {{-- Rating --}}
                            @php
                                $avgRating = $supplier->getAverageRating();
                                $totalReviews = $supplier->getTotalReviews();
                            @endphp
                            @if($avgRating)
                                <div class="bg-yellow-50 rounded-lg px-3 py-2 min-w-[80px] text-center">
                                    <div class="flex items-center justify-center gap-1 mb-1">
                                        <i class="fas fa-star text-yellow-500 text-sm"></i>
                                        <p class="text-xl font-bold text-yellow-600">{{ $avgRating }}</p>
                                    </div>
                                    <p class="text-xs text-yellow-700">{{ $totalReviews }} Review</p>
                                </div>
                            @else
                                <div class="bg-gray-50 rounded-lg px-3 py-2 min-w-[80px] text-center">
                                    <div class="flex items-center justify-center gap-1 mb-1">
                                        <i class="fas fa-star text-gray-400 text-sm"></i>
                                        <p class="text-xl font-bold text-gray-400">-</p>
                                    </div>
                                    <p class="text-xs text-gray-500">Belum ada review</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Contact Info - Simple Layout --}}
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        {{-- Alamat --}}
                        <div class="flex items-center gap-2 bg-green-50 rounded-lg px-3 py-2">
                            <i class="fas fa-map-marker-alt text-green-600 text-sm"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-green-600 font-semibold mb-0.5">ALAMAT</p>
                                <p class="text-sm text-gray-900 truncate">{{ $supplier->alamat ?? 'Tidak tersedia' }}</p>
                            </div>
                        </div>
                        
                        {{-- No HP --}}
                        <div class="flex items-center gap-2 bg-green-50 rounded-lg px-3 py-2">
                            <i class="fas fa-phone text-green-600 text-sm"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-green-600 font-semibold mb-0.5">NO HP</p>
                                <p class="text-sm text-gray-900">{{ $supplier->no_hp ?? 'Tidak tersedia' }}</p>
                            </div>
                        </div>

                        {{-- PIC Purchasing --}}
                        <div class="flex items-center gap-2 bg-green-50 rounded-lg px-3 py-2">
                            <i class="fas fa-user-tie text-green-600 text-sm"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-green-600 font-semibold mb-0.5">PIC PURCHASING</p>
                                <p class="text-sm text-gray-900 truncate">{{ $supplier->picPurchasing->nama ?? 'Belum ditentukan' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Action Section --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                        {{-- Left: Action Buttons --}}
                        <div class="flex items-center gap-2">
                            {{-- Lihat Bahan Baku Button --}}
                            <button type="button" 
                                    onclick="toggleProductList({{ $supplier->id }})"
                                    class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors">
                                <i class="fas fa-box text-xs"></i>
                                <span>Lihat Daftar Bahan Baku</span>
                                <i class="fas fa-chevron-down transform transition-transform text-xs" id="chevron-desktop-{{ $supplier->id }}"></i>
                            </button>

                            {{-- Review Button --}}
                            @if($totalReviews > 0)
                                <a href="{{ route('supplier.reviews', $supplier->slug) }}" 
                                   class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-lg transition-colors">
                                    <i class="fas fa-star text-xs"></i>
                                    <span>{{ $totalReviews }} Review{{ $totalReviews > 1 ? 's' : '' }}</span>
                                </a>
                            @endif
                        </div>

                        {{-- Right: Date & Actions --}}
                        <div class="flex items-center gap-3">
                            {{-- Last Updated --}}
                            <div class="flex items-center gap-1 text-xs text-gray-500">
                                <i class="far fa-clock text-green-500"></i>
                                <span>{{ $supplier->updated_at->format('d/m/Y') }}</span>
                            </div>

                            {{-- Edit & Delete --}}
                            <div class="flex items-center gap-2">
                                <a href="{{ route('supplier.edit', $supplier->slug) }}" 
                                   class="w-8 h-8 flex items-center justify-center text-yellow-600 hover:text-white bg-yellow-50 hover:bg-yellow-600 rounded-lg transition-colors" 
                                   title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                
                                <button type="button" 
                                        class="w-8 h-8 flex items-center justify-center text-red-600 hover:text-white bg-red-50 hover:bg-red-600 rounded-lg transition-colors" 
                                        onclick="openDeleteModal('{{ $supplier->slug }}', '{{ $supplier->nama }}')"
                                        title="Hapus">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                    {{-- Product List (Hidden by default) --}}
                    <div id="product-list-{{ $supplier->id }}" class="hidden border-t-2 border-gray-100 bg-gray-50">
                        {{-- Mobile Product List --}}
                        <div class="block sm:hidden p-3">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-box text-green-500 mr-2 text-xs"></i>
                                Bahan Baku
                            </h4>
                            <div class="space-y-2">
                                @forelse($supplier->bahanBakuSuppliers as $bahanBaku)
                                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-900">{{ $bahanBaku->nama }}</p>
                                                
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-bold text-green-600">Rp {{ number_format($bahanBaku->harga_per_satuan, 0, ',', '.') }}</p>
                                                <p class="text-xs text-gray-500">/ {{ $bahanBaku->satuan }}</p>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                onclick="redirectToRiwayatHarga('{{ $supplier->slug }}', '{{ $bahanBaku->slug }}')"
                                                class="w-full text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded text-xs font-medium transition-colors flex items-center justify-center">
                                            <i class="fas fa-chart-line mr-1.5"></i>
                                            Lihat Riwayat Harga
                                        </button>
                                    </div>
                                @empty
                                    <div class="bg-white rounded-lg p-4 text-center border border-gray-200">
                                        <i class="fas fa-inbox text-gray-300 text-2xl mb-2"></i>
                                        <p class="text-xs text-gray-500">Belum ada bahan baku</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Desktop Product List --}}
                        <div class="hidden sm:block p-5">
                            <h4 class="text-base font-semibold text-gray-700 mb-4 flex items-center">
                                <i class="fas fa-box text-green-500 mr-2"></i>
                                Daftar Bahan Baku
                            </h4>
                            <div class="bg-white rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y dixvide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Bahan Baku</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Harga</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($supplier->bahanBakuSuppliers as $bahanBaku)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center">
                                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                            <span class="text-xs font-bold text-green-600">{{ $loop->iteration }}</span>
                                                        </div>
                                                        <span class="text-sm font-medium text-gray-900">{{ $bahanBaku->nama }}</span>
                                                    </div>
                                                </td>
                                              
                                                <td class="px-4 py-3">
                                                    <div>
                                                        <p class="text-sm font-semibold text-green-600">Rp {{ number_format($bahanBaku->harga_per_satuan, 0, ',', '.') }}</p>
                                                        <p class="text-xs text-gray-500">per {{ $bahanBaku->satuan }}</p>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <button type="button" 
                                                            onclick="redirectToRiwayatHarga('{{ $supplier->slug }}', '{{ $bahanBaku->slug }}')"
                                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors">
                                                        <i class="fas fa-chart-line mr-1.5"></i>
                                                        Riwayat Harga
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center">
                                                    <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                                                    <p class="text-sm text-gray-500 font-medium">Belum ada bahan baku terdaftar</p>
                                                    <p class="text-xs text-gray-400 mt-1">Silakan tambahkan bahan baku untuk supplier ini</p>
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
            <div class="bg-white rounded-lg shadow-sm p-4 mt-4">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                    {{-- Pagination Info --}}
                    <div class="flex items-center text-sm text-gray-700">
                        <span class="mr-2">Menampilkan</span>
                        <span class="font-medium text-green-600">{{ $suppliers->firstItem() ?? 0 }}</span>
                        <span class="mx-1">sampai</span>
                        <span class="font-medium text-green-600">{{ $suppliers->lastItem() ?? 0 }}</span>
                        <span class="mx-1">dari</span>
                        <span class="font-medium text-green-600">{{ $suppliers->total() }}</span>
                        <span class="ml-1">supplier</span>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="flex items-center space-x-1">
                        {{-- Previous Page --}}
                        @if ($suppliers->onFirstPage())
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </span>
                        @else
                            <a href="{{ $suppliers->appends(request()->query())->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @if($suppliers->lastPage() > 1)
                            <div class="hidden sm:flex items-center space-x-1">
                                @foreach ($suppliers->appends(request()->query())->getUrlRange(1, $suppliers->lastPage()) as $page => $url)
                                    @if ($page == $suppliers->currentPage())
                                        <span class="px-3 py-2 text-sm font-medium text-white bg-green-600 border border-green-600 rounded-lg">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Mobile Page Indicator --}}
                            <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                                {{ $suppliers->currentPage() }} / {{ $suppliers->lastPage() }}
                            </div>
                        @endif

                        {{-- Next Page --}}
                        @if ($suppliers->hasMorePages())
                            <a href="{{ $suppliers->appends(request()->query())->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                                Selanjutnya
                                <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                Selanjutnya
                                <i class="fas fa-chevron-right ml-1"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data supplier</h3>
            <p class="text-gray-500">
                @if(request('search'))
                    Tidak ditemukan supplier dengan kata kunci "{{ request('search') }}"
                @else
                    Belum ada supplier yang terdaftar di sistem
                @endif
            </p>
        </div>
    @endif
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="fixed inset-0  bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden backdrop-blur-xs">
    <div class="relative top-20 mx-auto p-5 border w-11/12 sm:w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
                </div>
                <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            {{-- Modal Body --}}
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-3">
                    Apakah Anda yakin ingin menghapus supplier berikut?
                </p>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <div class="flex items-center">
                        <i class="fas fa-building text-red-500 mr-2"></i>
                        <span class="font-semibold text-red-800" id="supplierNameToDelete">-</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait supplier ini.
                </p>
            </div>
            
            {{-- Modal Footer --}}
            <div class="flex items-center justify-end space-x-3">
                <button type="button" 
                        onclick="closeDeleteModal()" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 rounded-lg transition-all duration-200 text-sm font-semibold">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </button>
                <button type="button" 
                        onclick="confirmDelete()" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg text-sm font-semibold">
                    <i class="fas fa-trash mr-2"></i>
                    Hapus Supplier
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let searchTimeout;

// Debounce function untuk search
function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        applyFilters();
    }, 500); // Wait 500ms after user stops typing
}

// Apply filters function
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const sortBahanBaku = document.getElementById('sortBahanBaku').value;
    const sortStok = document.getElementById('sortStok').value;
    const bahanBaku = document.getElementById('bahanBakuFilter').value;

    // Build URL with parameters
    const currentParams = new URLSearchParams(window.location.search);
    
    // Clear existing filter parameters but keep pagination and per_page if applicable
    currentParams.delete('search');
    currentParams.delete('sort_bahan_baku');
    currentParams.delete('sort_stok');
    currentParams.delete('bahan_baku');
    
    // Add new filter parameters
    if (search) currentParams.set('search', search);
    if (sortBahanBaku) currentParams.set('sort_bahan_baku', sortBahanBaku);
    if (sortStok) currentParams.set('sort_stok', sortStok);
    if (bahanBaku) currentParams.set('bahan_baku', bahanBaku);
    
    // Reset to first page only when applying new filters
    currentParams.delete('page');

    // Show active filters
    showActiveFilters(search, sortBahanBaku, sortStok, bahanBaku);

    // Redirect with new parameters
    const newUrl = window.location.pathname + (currentParams.toString() ? '?' + currentParams.toString() : '');
    window.location.href = newUrl;
}

// Show active filters
function showActiveFilters(search, sortBahanBaku, sortStok, bahanBaku) {
    const activeFiltersDiv = document.getElementById('activeFilters');
    const hasFilters = search || sortBahanBaku || sortStok || bahanBaku;

    if (!hasFilters) {
        activeFiltersDiv.style.display = 'none';
        return;
    }

    let filtersHtml = '<span class="text-sm font-medium text-gray-700">Filter aktif:</span>';

    if (search) {
        filtersHtml += `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <i class="fas fa-search mr-1"></i>
            "${search}"
        </span>`;
    }

    if (sortBahanBaku) {
        const sortText = sortBahanBaku === 'terbanyak' ? 'Bahan Baku Terbanyak' : 'Bahan Baku Tersedikit';
        filtersHtml += `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
            <i class="fas fa-sort mr-1"></i>
            ${sortText}
        </span>`;
    }

    if (sortStok) {
        const sortText = sortStok === 'terbanyak' ? 'Stok Terbanyak' : 'Stok Tersedikit';
        filtersHtml += `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
            <i class="fas fa-sort mr-1"></i>
            ${sortText}
        </span>`;
    }

    if (bahanBaku) {
        const bahanBakuText = bahanBaku.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        filtersHtml += `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
            <i class="fas fa-box mr-1"></i>
            ${bahanBakuText}
        </span>`;
    }

    activeFiltersDiv.innerHTML = filtersHtml;
    activeFiltersDiv.style.display = 'flex';
}

// Change per page function
function changePerPage() {
    const perPage = document.getElementById('perPageSelect').value;
    const url = new URL(window.location);
    
    // Preserve existing parameters
    const currentParams = new URLSearchParams(window.location.search);
    
    // Set per_page parameter
    currentParams.set('per_page', perPage);
    currentParams.delete('page'); // Reset to first page when changing per_page
    
    // Build new URL with preserved parameters
    window.location.href = window.location.pathname + '?' + currentParams.toString();
}

// Toggle product list function
function toggleProductList(supplierId) {
    const productList = document.getElementById(`product-list-${supplierId}`);
    const chevronMobile = document.getElementById(`chevron-${supplierId}`);
    const chevronDesktop = document.getElementById(`chevron-desktop-${supplierId}`);
    
    if (productList.classList.contains('hidden')) {
        // Show the product list
        productList.classList.remove('hidden');
        productList.classList.add('animate-fadeIn');
        if (chevronMobile) chevronMobile.classList.add('rotate-180');
        if (chevronDesktop) chevronDesktop.classList.add('rotate-180');
    } else {
        // Hide the product list
        productList.classList.add('hidden');
        productList.classList.remove('animate-fadeIn');
        if (chevronMobile) chevronMobile.classList.remove('rotate-180');
        if (chevronDesktop) chevronDesktop.classList.remove('rotate-180');
    }
}

// Initialize active filters on page load
document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('searchInput').value;
    const sortBahanBaku = document.getElementById('sortBahanBaku').value;
    const sortStok = document.getElementById('sortStok').value;
    const bahanBaku = document.getElementById('bahanBakuFilter').value;

    showActiveFilters(search, sortBahanBaku, sortStok, bahanBaku);
});

// Redirect to price history page
function redirectToRiwayatHarga(supplierSlug, bahanBakuSlug) {
    const url = `/procurement/supplier/${supplierSlug}/bahan-baku/${bahanBakuSlug}/riwayat-harga`;
    window.location.href = url;
}

// Delete modal functions
let supplierSlugToDelete = null;

function openDeleteModal(supplierSlug, supplierName) {
    supplierSlugToDelete = supplierSlug;
    document.getElementById('supplierNameToDelete').textContent = supplierName;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeDeleteModal() {
    supplierSlugToDelete = null;
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Restore scrolling
}

function confirmDelete() {
    if (supplierSlugToDelete) {
        // Show loading state
        const deleteButton = event.target;
        const originalText = deleteButton.innerHTML;
        deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menghapus...';
        deleteButton.disabled = true;
        
        // Create and submit delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/procurement/supplier/${supplierSlugToDelete}`;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        }
        
        // Add DELETE method
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    } else {
        alert('Error: Tidak dapat menghapus supplier. Data tidak lengkap.');
        closeDeleteModal();
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});

// Add custom CSS for animations
const style = document.createElement('style');
style.textContent = `
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .transform {
        transition: transform 0.2s ease-in-out;
    }
    
    .rotate-180 {
        transform: rotate(180deg);
    }

    #activeFilters {
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        padding-top: 0.5rem;
        border-top: 1px solid #e5e7eb;
    }
`;
document.head.appendChild(style);

// Show success modal if there's a success flash message
@if(session('success'))
document.addEventListener('DOMContentLoaded', function() {
    let message = @json(session('success'));
    
    // Determine action type based on message content
    let actionType = 'default';
    if (message.includes('ditambahkan') || message.includes('dibuat')) {
        actionType = 'create';
    } else if (message.includes('diperbarui') || message.includes('diubah')) {
        actionType = 'edit';
    } else if (message.includes('dihapus')) {
        actionType = 'delete';
    }
    
    showSuccessModal(actionType, message, 'Operasi pada data supplier berhasil dilakukan.', '', true);
});
@endif

// Show error modal if there's an error flash message
@if(session('error'))
document.addEventListener('DOMContentLoaded', function() {
    alert(@json(session('error')));
});
@endif
</script>
@endpush

{{-- Include Modal Sukses Universal --}}
@include('components.success-modal')