<div class="relative">
    {{-- Global Loading Overlay --}}
    <div 
        wire:loading 
        wire:target="search,location,sort,sortBy,clearSearch,clearFilters"
        class="fixed inset-0 bg-black/10 backdrop-blur-sm z-40 flex items-center justify-center"
        style="display: none;"
    >
        <div class="bg-white rounded-lg shadow-lg p-6 flex items-center space-x-3">
            <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-gray-700 font-medium">Memuat data...</span>
        </div>
    </div>

    {{-- Navigation Breadcrumb --}}
    <div class="bg-white border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <div>
                                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-home"></i>
                                    <span class="sr-only">Home</span>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-300 mr-4"></i>
                                <span class="text-gray-900 text-sm font-medium">Daftar Klien</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Welcome Banner --}}
    <x-welcome-banner
        title="Daftar Klien"
        subtitle="Kelola data klien perusahaan"
        icon="fas fa-users"
    />

    {{-- Search and Filter Section --}}
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter text-blue-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Filter & Pencarian</h3>
                </div>
                @if($search || $location || $sort !== 'nama' || $direction !== 'asc')
                    <div class="flex items-center space-x-2">
                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full font-medium">
                            {{ ($search ? 1 : 0) + ($location ? 1 : 0) + ($sort !== 'nama' || $direction !== 'asc' ? 1 : 0) }} filter aktif
                        </span>
                        <button
                            wire:click="clearFilters"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors duration-200"
                        >
                            <i class="fas fa-times mr-1"></i>
                            Reset
                        </button>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Filter Controls --}}
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                {{-- Search Input --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-gray-400"></i>
                        Pencarian
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <div wire:loading.remove wire:target="search">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <div wire:loading wire:target="search">
                                <i class="fas fa-spinner fa-spin text-blue-500"></i>
                            </div>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.500ms="search"
                            placeholder="Cari nama perusahaan, cabang, atau nomor HP..."
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg text-sm
                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                   transition-all duration-200 bg-gray-50 focus:bg-white
                                   disabled:opacity-50 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                            wire:target="search"
                        >
                        @if($search)
                            <button
                                wire:click="clearSearch"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center group"
                                title="Hapus pencarian"
                            >
                                <i class="fas fa-times text-gray-400 group-hover:text-red-500 transition-colors duration-200"></i>
                            </button>
                        @endif
                    </div>
                    @if($search)
                        <div class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Mencari: "<span class="font-medium text-blue-600">{{ $search }}</span>"
                        </div>
                    @endif
                </div>

                {{-- Location Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>
                        Lokasi
                    </label>
                    <div class="relative">
                        <select
                            wire:model.live="location"
                            class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm
                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                   transition-colors duration-200 bg-gray-50 focus:bg-white
                                   appearance-none cursor-pointer"
                        >
                            <option value="">Semua Lokasi</option>
                            @foreach($availableLocations as $loc)
                                <option value="{{ $loc }}">{{ $loc }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                    @if($location)
                        <div class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-filter mr-1"></i>
                            Filter: <span class="font-medium text-blue-600">{{ $location }}</span>
                        </div>
                    @endif
                </div>

                {{-- Sort Controls --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort mr-1 text-gray-400"></i>
                        Urutkan
                    </label>
                    <div class="flex space-x-2">
                        <div class="flex-1 relative">
                            <select
                                wire:model.live="sort"
                                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-sm
                                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                       transition-colors duration-200 bg-gray-50 focus:bg-white
                                       appearance-none cursor-pointer pr-8"
                            >
                                <option value="nama">Nama</option>
                                <option value="cabang_count">Jumlah Cabang</option>
                                <option value="lokasi">Lokasi</option>
                                <option value="updated_at">Terakhir Update</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        
                        <button
                            wire:click="sortBy('{{ $sort }}')"
                            class="flex-shrink-0 px-3 py-3 border border-gray-300 rounded-lg 
                                   hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                   transition-all duration-200 group"
                            title="Toggle arah pengurutan ({{ $direction === 'asc' ? 'A→Z' : 'Z→A' }})"
                        >
                            <i class="fas fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }} 
                                      text-gray-500 group-hover:text-blue-600 transition-colors duration-200"></i>
                        </button>
                    </div>
                    
                    <div class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-{{ $direction === 'asc' ? 'sort-alpha-up' : 'sort-alpha-down' }} mr-1"></i>
                        {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}
                        @if($sort !== 'nama')
                            • {{ 
                                $sort === 'cabang_count' ? 'Jumlah Cabang' :
                                ($sort === 'lokasi' ? 'Lokasi' : 'Terakhir Update')
                            }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Header --}}
        {{-- Action Header with Stats --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-users text-blue-600 mr-3"></i>
                    Daftar Klien
                </h2>
                <p class="text-gray-600 mt-1">Kelola data klien dan cabang perusahaan</p>
            </div>
            
            <div class="flex items-center space-x-4">
                {{-- Stats Summary --}}
                <div class="flex items-center space-x-4 text-sm">
                    <div class="flex items-center space-x-1">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <span class="text-gray-600">Total:</span>
                        <span class="font-semibold text-gray-900">{{ $kliens->total() }}</span>
                        <span class="text-gray-500">klien</span>
                    </div>
                    @if($search || $location)
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-gray-600">Hasil:</span>
                            <span class="font-semibold text-green-600">{{ $kliens->count() }}</span>
                            <span class="text-gray-500">ditemukan</span>
                        </div>
                    @endif
                </div>
                
                {{-- Add Client Button --}}
                <button
                    wire:click="openCompanyModal"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 
                           text-white text-sm font-medium rounded-lg shadow-sm 
                           transition-colors duration-200 focus:outline-none focus:ring-2 
                           focus:ring-offset-2 focus:ring-blue-500"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Klien
                </button>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <div wire:loading.delay class="mb-4">
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Memuat data...
        </div>
    </div>

    {{-- Main Content Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100
                transition-all duration-300"
         wire:loading.class="opacity-60">
        @if($kliens->count() > 0)
            {{-- Table Header --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-table text-green-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Klien</h3>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if($search || $location)
                            <div class="text-sm bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-200">
                                <i class="fas fa-filter mr-1"></i>
                                Hasil filter: <span class="font-semibold">{{ $kliens->count() }}</span>
                            </div>
                        @endif
                        <div class="text-sm text-gray-600">
                            Total: <span class="font-semibold text-green-600">{{ $kliens->total() }}</span> klien
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Content --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Klien</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Cabang</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Update</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $grouped = $kliens->getCollection()->groupBy('nama');
                            $currentPage = $kliens->currentPage();
                            $perPage = $kliens->perPage();
                            $startingRowNumber = ($currentPage - 1) * $perPage + 1;
                            $rowNumber = $startingRowNumber;
                        @endphp

                        @foreach($grouped as $name => $group)
                            @php
                                $groupId = 'group-' . md5($name);
                                $branches = $group->pluck('cabang')->filter()->unique();
                                $mainLocation = $branches->first() ?? 'Tidak diketahui';
                                $latestUpdate = $group->max('updated_at');
                            @endphp

                            {{-- Main client row --}}
                            <tr
                                class="hover:bg-gray-50 border-b border-gray-200 cursor-pointer"
                                wire:click="toggleGroup('{{ $groupId }}')"
                            >
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $rowNumber }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $name }}</div>
                                        </div>
                                        <button
                                            type="button"
                                            class="flex items-center px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors duration-200"
                                        >
                                            <i class="fas mr-1 transform transition-transform duration-200 {{ in_array($groupId, $openGroups) ? 'fa-chevron-down rotate-0' : 'fa-chevron-right' }}"></i>
                                            <span>{{ in_array($groupId, $openGroups) ? 'Tutup' : 'Lihat Cabang' }}</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ $group->count() }} cabang
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="fas fa-location-dot text-gray-400 mr-2"></i>
                                        {{ $mainLocation }}
                                        @if($branches->count() > 1)
                                            <span class="text-xs text-gray-400 ml-1">(+{{ $branches->count() - 1 }} lokasi)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button
                                            type="button"
                                            wire:click.stop="editCompany('{{ $name }}')"
                                            class="text-amber-600 hover:text-amber-800 text-sm font-medium"
                                            title="Edit Perusahaan"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click.stop="deleteCompany('{{ $name }}')"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                                            title="Hapus Perusahaan"
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click.stop="toggleGroup('{{ $groupId }}')"
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                        >
                                            Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Expandable branches section --}}
                            @if(in_array($groupId, $openGroups))
                                <tr class="bg-gray-50">
                                    <td colspan="6" class="p-0">
                                        <div class="border-t border-gray-200">
                                            {{-- Branch header --}}
                                            <div class="px-6 py-3 bg-gray-100 border-b border-gray-200">
                                                <h4 class="text-sm font-medium text-gray-900">Cabang untuk: {{ $name }}</h4>
                                            </div>

                                            {{-- Branch table --}}
                                            <div class="overflow-x-auto">
                                                <table class="w-full">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Cabang</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Update</th>
                                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach($group as $klien)
                                                            @php $detailId = 'detail-' . $klien->id; @endphp

                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-4 py-3">
                                                                    <div class="flex items-center justify-between">
                                                                        <div>
                                                                            <div class="text-sm font-medium text-gray-900">{{ $klien->cabang }}</div>
                                                                        </div>
                                                                        <button
                                                                            wire:click="toggleBahanBaku('{{ $detailId }}')"
                                                                            class="text-xs text-blue-600 hover:text-blue-800"
                                                                        >
                                                                            <i class="fas {{ in_array($detailId, $openBahanBaku) ? 'fa-chevron-up' : 'fa-chevron-down' }} mr-1"></i>
                                                                            Material
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                                <td class="px-4 py-3 text-sm text-gray-500">
                                                                    {{ $klien->no_hp ?: '-' }}
                                                                </td>
                                                                <td class="px-4 py-3 text-sm text-gray-500">
                                                                    {{ $klien->updated_at->format('d/m/Y H:i') }}
                                                                </td>
                                                                <td class="px-4 py-3 text-right">
                                                                    <div class="flex items-center justify-end space-x-2">
                                                                        <a
                                                                            href="{{ route('klien.edit', $klien) }}"
                                                                            class="text-blue-600 hover:text-blue-800 text-sm"
                                                                            title="Edit Cabang & Kelola Material"
                                                                        >
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                        <button
                                                                            wire:click="deleteBranch({{ $klien->id }}, '{{ $klien->cabang }}')"
                                                                            class="text-red-600 hover:text-red-800 text-sm"
                                                                            title="Hapus Cabang"
                                                                        >
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>

                                                            {{-- Bahan Baku detail row --}}
                                                            @if(in_array($detailId, $openBahanBaku))
                                                                <tr>
                                                                    <td colspan="4" class="p-0">
                                                                        <div class="bg-gray-50 border-t border-gray-200">
                                                                            <div class="px-4 py-3 bg-gray-100 border-b border-gray-200">
                                                                                <h5 class="text-sm font-medium text-gray-900">Material untuk {{ $klien->cabang }}</h5>
                                                                            </div>

                                                                            @if($klien->bahanBakuKliens->count() > 0)
                                                                                <div class="overflow-x-auto">
                                                                                    <table class="w-full">
                                                                                        <thead class="bg-gray-50">
                                                                                            <tr>
                                                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                                                            @foreach($klien->bahanBakuKliens as $material)
                                                                                                <tr class="hover:bg-gray-50">
                                                                                                    <td class="px-3 py-2">
                                                                                                        <div>
                                                                                                            <div class="text-sm font-medium text-gray-900">{{ $material->nama }}</div>
                                                                                                            @if($material->spesifikasi)
                                                                                                                <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($material->spesifikasi, 50) }}</div>
                                                                                                            @endif
                                                                                                        </div>
                                                                                                    </td>
                                                                                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $material->satuan }}</td>
                                                                                                    <td class="px-3 py-2">
                                                                                                        @if($material->harga_approved)
                                                                                                            <div class="text-sm font-medium text-green-600">
                                                                                                                Rp {{ number_format($material->harga_approved, 0, ',', '.') }}
                                                                                                            </div>
                                                                                                        @else
                                                                                                            <span class="text-xs text-gray-400">Belum ada harga</span>
                                                                                                        @endif
                                                                                                    </td>
                                                                                                    <td class="px-3 py-2">
                                                                                                        @if($material->status === 'aktif')
                                                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                                                                <i class="fas fa-check-circle mr-1"></i>
                                                                                                                Aktif
                                                                                                            </span>
                                                                                                        @elseif($material->status === 'pending')
                                                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                                                                <i class="fas fa-clock mr-1"></i>
                                                                                                                Pending
                                                                                                            </span>
                                                                                                        @else
                                                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                                                                <i class="fas fa-times-circle mr-1"></i>
                                                                                                                Non-aktif
                                                                                                            </span>
                                                                                                        @endif
                                                                                                    </td>
                                                                                                    <td class="px-3 py-2 text-right">
                                                                                                        <div class="flex items-center justify-end space-x-2">
                                                                                                            @if($material->harga_approved)
                                                                                                                <a href="{{ route('klien.riwayat-harga', [$klien, $material]) }}"
                                                                                                                   class="text-blue-600 hover:text-blue-800 text-xs"
                                                                                                                   title="Lihat Riwayat Harga">
                                                                                                                    <i class="fas fa-chart-line"></i>
                                                                                                                </a>
                                                                                                            @endif
                                                                                                            <span class="text-xs text-gray-400 italic">Read-only</span>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            @endforeach
                                                                                        </tbody>
                                                                                    </table>
                                                                                </div>
                                                                                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                                                                                    <a href="{{ route('klien.edit', $klien) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                                                        <i class="fas fa-edit mr-1"></i>
                                                                                        Kelola Material
                                                                                    </a>
                                                                                </div>
                                                                            @else
                                                                                <div class="px-4 py-6 text-center">
                                                                                    <div class="text-gray-400 mb-2">
                                                                                        <i class="fas fa-box-open text-2xl"></i>
                                                                                    </div>
                                                                                    <p class="text-sm text-gray-500 mb-3">Belum ada material untuk cabang ini</p>
                                                                                    <a href="{{ route('klien.edit', $klien) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                                                        <i class="fas fa-edit mr-1"></i>
                                                                                        Kelola Material
                                                                                    </a>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            @php $rowNumber++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $kliens->links() }}
            </div>

        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-users text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    @if($search || $location)
                        Tidak ada hasil untuk pencarian "{{ $search ?: $location }}"
                    @else
                        Belum ada data klien
                    @endif
                </h3>
                <p class="text-gray-500 mb-6">
                    @if($search || $location)
                        Coba ubah kata kunci pencarian atau hapus filter yang diterapkan.
                    @else
                        Mulai dengan menambahkan perusahaan atau cabang klien pertama.
                    @endif
                </p>
                @if($search || $location)
                    <button
                        wire:click="clearFilters"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Hapus Filter
                    </button>
                @else
                    <div class="flex justify-center space-x-3">
                        <button
                            wire:click="openCompanyModal"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        >
                            <i class="fas fa-building mr-2"></i>
                            Tambah Perusahaan
                        </button>
                        <button
                            wire:click="openBranchModal"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Cabang
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Company Modal --}}
    @if($showCompanyModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.3);" wire:click="closeCompanyModal"></div>

            {{-- Modal content --}}
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- This element is to trick the browser into centering the modal contents. --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" @click.stop>
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                {{ $editingCompany ? 'Edit Perusahaan' : 'Tambah Perusahaan' }}
                            </h3>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Perusahaan</label>
                                <input
                                    type="text"
                                    wire:model="companyForm.nama"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('companyForm.nama') border-red-500 @enderror"
                                    placeholder="Masukkan nama perusahaan"
                                >
                                @error('companyForm.nama')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button
                            wire:click="submitCompanyForm"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            <span wire:loading.remove>{{ $editingCompany ? 'Update' : 'Tambah' }}</span>
                            <span wire:loading>
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Processing...
                            </span>
                        </button>
                        <button
                            wire:click="closeCompanyModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Branch Modal --}}
    @if($showBranchModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.3);" wire:click="closeBranchModal"></div>

            {{-- Modal content --}}
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- This element is to trick the browser into centering the modal contents. --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" @click.stop>
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                {{ $editingBranch ? 'Edit Cabang' : 'Tambah Cabang' }}
                            </h3>

                            @if(!$editingBranch)
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Perusahaan</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="branchForm.company_type" value="existing" class="mr-2">
                                            <span class="text-sm">Perusahaan yang sudah ada</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="branchForm.company_type" value="new" class="mr-2">
                                            <span class="text-sm">Perusahaan baru</span>
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $branchForm['company_type'] === 'existing' || $editingBranch ? 'Pilih Perusahaan' : 'Nama Perusahaan Baru' }}
                                </label>
                                @if($branchForm['company_type'] === 'existing' && !$editingBranch)
                                    <select
                                        wire:model="branchForm.company_nama"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('branchForm.company_nama') border-red-500 @enderror"
                                    >
                                        <option value="">Pilih perusahaan...</option>
                                        @foreach($uniqueCompanies as $company)
                                            <option value="{{ $company }}">{{ $company }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        type="text"
                                        wire:model="branchForm.company_nama"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('branchForm.company_nama') border-red-500 @enderror"
                                        placeholder="Masukkan nama perusahaan"
                                        @if($editingBranch) readonly @endif
                                    >
                                @endif
                                @error('branchForm.company_nama')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Cabang</label>
                                <input
                                    type="text"
                                    wire:model="branchForm.cabang"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('branchForm.cabang') border-red-500 @enderror"
                                    placeholder="Masukkan lokasi cabang"
                                >
                                @error('branchForm.cabang')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. HP (Opsional)</label>
                                <input
                                    type="text"
                                    wire:model="branchForm.no_hp"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Masukkan nomor HP"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button
                            wire:click="submitBranchForm"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            <span wire:loading.remove>{{ $editingBranch ? 'Update' : 'Tambah' }}</span>
                            <span wire:loading>
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Processing...
                            </span>
                        </button>
                        <button
                            wire:click="closeBranchModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirmation Modal --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.3);" wire:click="closeConfirmModal"></div>

            {{-- Modal content --}}
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- This element is to trick the browser into centering the modal contents. --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" @click.stop>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ $confirmModal['title'] }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ $confirmModal['message'] }}
                                </p>
                                @if($confirmModal['warning'])
                                    <p class="text-sm text-red-600 mt-2 font-medium">
                                        {{ $confirmModal['warning'] }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button
                            wire:click="confirmAction"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            <span wire:loading.remove>{{ $confirmModal['confirmText'] }}</span>
                            <span wire:loading>
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Processing...
                            </span>
                        </button>
                        <button
                            wire:click="closeConfirmModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="fixed bottom-4 right-4 z-50">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('message') }}
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 z-50">
            <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif
</div>