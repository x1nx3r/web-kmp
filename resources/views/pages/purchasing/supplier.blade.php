@extends('layouts.app')
@section('title', 'Supplier - Kamil Maju Persada')
@section('content')

{{-- Welcome Banner --}}
<div class="bg-green-800 rounded-xl sm:rounded-2xl p-3 sm:p-6 lg:p-8 mb-4 sm:mb-6 lg:mb-8 text-white shadow-lg mt-2 sm:mt-4 lg:mt-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">Supplier</h1>
            <p class="text-white text-xs sm:text-base lg:text-lg">Kelola data supplier perusahaan</p>
        </div>
        <div class="hidden lg:block">
            <i class="fas fa-industry text-6xl text-white"></i>
        </div>
    </div>
</div>

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
                        <option value="bahan_baku_a" {{ request('bahan_baku') == 'bahan_baku_a' ? 'selected' : '' }}>Bahan Baku A</option>
                        <option value="bahan_baku_b" {{ request('bahan_baku') == 'bahan_baku_b' ? 'selected' : '' }}>Bahan Baku B</option>
                        <option value="bahan_baku_c" {{ request('bahan_baku') == 'bahan_baku_c' ? 'selected' : '' }}>Bahan Baku C</option>
                        <option value="bahan_baku_d" {{ request('bahan_baku') == 'bahan_baku_d' ? 'selected' : '' }}>Bahan Baku D</option>
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
    <button type="button" class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg sm:rounded-xl transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-semibold text-sm" disabled>
        <i class="fas fa-plus mr-2"></i>Tambah Supplier
    </button>
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
                            <span class="text-xs font-bold text-green-600">{{ $supplier->total_produk ?? 0 }}</span>
                            <span class="text-xs text-green-600">Bahan</span>
                            <span class="mx-1 text-gray-300">|</span>
                            <span class="text-xs font-bold text-blue-600">{{ $supplier->total_barang ?? 0 }}</span>
                            <span class="text-xs text-blue-600">Stok</span>
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
                            <span class="truncate">{{ $supplier->pic_purchasing ?? 'Belum ditentukan' }}</span>
                        </div>
                    </div>
                </div>
                
                {{-- Mobile Actions --}}
                <div class="p-3 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <button type="button" 
                                onclick="toggleProductList({{ $supplier->id }})"
                                class="flex items-center px-3 py-2 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded-lg transition-all duration-200">
                            <i class="fas fa-box mr-1"></i>
                            <span>Bahan Baku</span>
                            <i class="fas fa-chevron-down transform transition-transform ml-2 text-xs" id="chevron-{{ $supplier->id }}"></i>
                        </button>
                        
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500">{{ $supplier->updated_at->format('d/m/Y') }}</span>
                            <button type="button" class="w-6 h-6 flex items-center justify-center text-yellow-600 bg-yellow-50 rounded" disabled>
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <button type="button" class="w-6 h-6 flex items-center justify-center text-red-600 bg-red-50 rounded" disabled>
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Desktop Card View --}}
            <div class="hidden sm:block">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        {{-- Left Section: Supplier Info --}}
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $supplier->nama }}</h3>
                                    <div class="flex items-center text-sm text-green-600 font-medium">
                                        <i class="fas fa-building mr-2"></i>
                                        <span>Supplier #{{ $suppliers->firstItem() + $index }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Section: Stats --}}
                        <div class="flex items-center space-x-6">
                            <div class="text-center bg-green-50 rounded-lg px-4 py-3 border border-green-200">
                                <p class="text-2xl font-bold text-green-600">{{ $supplier->total_produk ?? 0 }}</p>
                                <p class="text-xs text-green-700 font-medium">Total Bahan Baku</p>
                            </div>
                            <div class="text-center bg-blue-50 rounded-lg px-4 py-3 border border-blue-200">
                                <p class="text-2xl font-bold text-blue-600">{{ $supplier->total_barang ?? 0 }}</p>
                                <p class="text-xs text-blue-700 font-medium">Total Stok</p>
                            </div>
                        </div>
                    </div>

                    {{-- Contact Information Grid --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            {{-- Alamat --}}
                            <div class="flex items-start bg-white rounded-lg p-3 border border-gray-200 hover:border-green-300 transition-colors">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-green-600 text-sm"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-green-600 uppercase tracking-wide font-bold mb-1">Alamat</p>
                                    <p class="text-sm text-gray-900 font-medium truncate">{{ $supplier->alamat ?? 'Tidak tersedia' }}</p>
                                </div>
                            </div>
                            
                            {{-- No HP --}}
                            <div class="flex items-start bg-white rounded-lg p-3 border border-gray-200 hover:border-green-300 transition-colors">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-phone text-green-600 text-sm"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-green-600 uppercase tracking-wide font-bold mb-1">No HP</p>
                                    <p class="text-sm text-gray-900 font-medium">{{ $supplier->no_hp ?? 'Tidak tersedia' }}</p>
                                </div>
                            </div>

                            {{-- PIC Purchasing --}}
                            <div class="flex items-start bg-white rounded-lg p-3 border border-gray-200 hover:border-green-300 transition-colors">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-user-tie text-green-600 text-sm"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-green-600 uppercase tracking-wide font-bold mb-1">PIC Purchasing</p>
                                    <p class="text-sm text-gray-900 font-medium truncate">{{ $supplier->pic_purchasing ?? 'Belum ditentukan' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Section --}}
                    <div class="flex items-center justify-between pt-4 border-t-2 border-green-100">
                        {{-- Product Dropdown Button --}}
                        <button type="button" 
                                onclick="toggleProductList({{ $supplier->id }})"
                                class="flex items-center px-5 py-3 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <i class="fas fa-box mr-2"></i>
                            Lihat Daftar Bahan Baku & Harga
                            <i class="fas fa-chevron-down transform transition-transform ml-3" id="chevron-desktop-{{ $supplier->id }}"></i>
                        </button>

                        {{-- Action Buttons --}}
                        <div class="flex items-center space-x-4">
                            {{-- Last Updated --}}
                            <div class="flex items-center text-xs text-gray-500 bg-gray-100 px-3 py-2 rounded-full">
                                <i class="far fa-clock mr-2 text-green-500"></i>
                                <span class="font-medium">{{ $supplier->updated_at->format('d/m/Y') }}</span>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center space-x-2">
                                <button type="button" class="w-10 h-10 flex items-center justify-center text-yellow-600 hover:text-white bg-yellow-50 hover:bg-yellow-600 rounded-lg transition-all duration-200 transform hover:scale-105" disabled>
                                    <i class="fas fa-edit text-sm" title="Edit"></i>
                                </button>
                                
                                <button type="button" class="w-10 h-10 flex items-center justify-center text-red-600 hover:text-white bg-red-50 hover:bg-red-600 rounded-lg transition-all duration-200 transform hover:scale-105" disabled>
                                    <i class="fas fa-trash text-sm" title="Hapus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                    {{-- Product List (Hidden by default) --}}
                    <div id="product-list-{{ $supplier->id }}" class="hidden border-t-2 border-green-100 bg-green-50">
                        {{-- Mobile Product List --}}
                        <div class="block sm:hidden p-3">
                            <h4 class="text-sm font-bold text-green-800 mb-2">Bahan Baku & Harga</h4>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                <div class="bg-white rounded p-2 border-l-2 border-green-400">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <i class="fas fa-cube text-green-600 text-xs mr-2"></i>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900">Bahan Baku A</p>
                                                <p class="text-xs text-gray-600">Stok: 150 KG</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-bold text-green-700">Rp 25.000</p>
                                            <p class="text-xs text-green-600">per KG</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white rounded p-2 border-l-2 border-blue-400">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <i class="fas fa-cube text-blue-600 text-xs mr-2"></i>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900">Bahan Baku B</p>
                                                <p class="text-xs text-gray-600">Stok: 200 KG</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-bold text-blue-700">Rp 15.000</p>
                                            <p class="text-xs text-blue-600">per KG</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white rounded p-2 border-l-2 border-purple-400">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <i class="fas fa-cube text-purple-600 text-xs mr-2"></i>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900">Bahan Baku C</p>
                                                <p class="text-xs text-gray-600">Stok: 75 KG</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-bold text-purple-700">Rp 30.000</p>
                                            <p class="text-xs text-purple-600">per KG</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Desktop Product List --}}
                        <div class="hidden sm:block p-6">
                            <h4 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-list-ul text-white text-sm"></i>
                                </div>
                                Daftar Bahan Baku & Harga
                            </h4>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                {{-- Sample products (replace with actual data) --}}
                                <div class="bg-white rounded-lg p-4 border-l-4 border-green-400 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1 flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                                <i class="fas fa-cube text-green-600 text-sm"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-gray-900">Bahan Baku A</p>
                                                <p class="text-xs text-gray-600 flex items-center">
                                                    <i class="fas fa-boxes mr-1 text-green-500"></i>
                                                    Stok: 150 KG
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right bg-green-50 rounded-lg px-3 py-2">
                                            <p class="text-sm font-bold text-green-700">Rp 25.000</p>
                                            <p class="text-xs text-green-600">per KG</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white rounded-lg p-4 border-l-4 border-blue-400 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1 flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                                <i class="fas fa-cube text-blue-600 text-sm"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-gray-900">Bahan Baku B</p>
                                                <p class="text-xs text-gray-600 flex items-center">
                                                    <i class="fas fa-boxes mr-1 text-blue-500"></i>
                                                    Stok: 200 KG
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right bg-blue-50 rounded-lg px-3 py-2">
                                            <p class="text-sm font-bold text-blue-700">Rp 15.000</p>
                                            <p class="text-xs text-blue-600">per KG</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white rounded-lg p-4 border-l-4 border-purple-400 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1 flex items-center">
                                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                                <i class="fas fa-cube text-purple-600 text-sm"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-gray-900">Bahan Baku C</p>
                                                <p class="text-xs text-gray-600 flex items-center">
                                                    <i class="fas fa-boxes mr-1 text-purple-500"></i>
                                                    Stok: 75 KG
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right bg-purple-50 rounded-lg px-3 py-2">
                                            <p class="text-sm font-bold text-purple-700">Rp 30.000</p>
                                            <p class="text-xs text-purple-600">per KG</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- Pagination --}}
        <div class="mt-6 sm:mt-8 bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 p-3 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                {{-- Pagination Info --}}
                <div class="text-xs sm:text-sm text-gray-700 flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-green-500 mr-2"></i>
                        <span class="font-medium">
                            Menampilkan {{ $suppliers->firstItem() ?? 0 }} - {{ $suppliers->lastItem() ?? 0 }} 
                            dari {{ $suppliers->total() }} supplier
                        </span>
                    </div>
                    
                {{-- Pagination Links --}}
                <div class="flex justify-center sm:justify-end">
                    {{ $suppliers->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
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
    const params = new URLSearchParams();
    
    if (search) params.append('search', search);
    if (sortBahanBaku) params.append('sort_bahan_baku', sortBahanBaku);
    if (sortStok) params.append('sort_stok', sortStok);
    if (bahanBaku) params.append('bahan_baku', bahanBaku);

    // Show active filters
    showActiveFilters(search, sortBahanBaku, sortStok, bahanBaku);

    // Redirect with new parameters
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
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
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
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
</script>
@endpush