<div class="relative">
    {{-- Global Loading Overlay --}}
    <div 
        wire:loading 
        wire:target="search,klienFilter,statusFilter,sortBy,clearSearch,clearFilters"
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
                                <span class="text-gray-900 text-sm font-medium">Spesifikasi Material</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Welcome Banner --}}
    <x-welcome-banner
        title="Spesifikasi Material"
        subtitle="Kelola spesifikasi bahan baku klien"
        icon="fas fa-clipboard-list"
    />

    {{-- Filter Section --}}
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter text-purple-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Filter & Pencarian</h3>
                </div>
                @if($search || $materialSearch || $klienFilter || $cabangFilter || $statusFilter || $sort !== 'nama' || $direction !== 'asc')
                    <div class="flex items-center space-x-2">
                        <span class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded-full font-medium">
                            {{ ($search ? 1 : 0) + ($materialSearch ? 1 : 0) + ($klienFilter ? 1 : 0) + ($cabangFilter ? 1 : 0) + ($statusFilter ? 1 : 0) + ($sort !== 'nama' || $direction !== 'asc' ? 1 : 0) }} filter aktif
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
            <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
                {{-- General Search Input --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-gray-400"></i>
                        Pencarian Umum
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <div wire:loading.remove wire:target="search">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <div wire:loading wire:target="search">
                                <i class="fas fa-spinner fa-spin text-purple-500"></i>
                            </div>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.500ms="search"
                            placeholder="Cari di semua field (nama, spesifikasi, klien)..."
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg text-sm
                                   focus:ring-2 focus:ring-purple-500 focus:border-purple-500 
                                   transition-all duration-200 bg-gray-50 focus:bg-white"
                        >
                        @if($search)
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button 
                                    wire:click="clearSearch"
                                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                    title="Clear search"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Material-specific Search --}}
                <div class="lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-cube mr-1 text-gray-400"></i>
                        Material
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.500ms="materialSearch"
                            placeholder="Cari material spesifik..."
                            list="material-suggestions"
                            class="block w-full pl-3 pr-8 py-3 border border-gray-300 rounded-lg text-sm
                                   focus:ring-2 focus:ring-purple-500 focus:border-purple-500 
                                   transition-all duration-200 bg-gray-50 focus:bg-white"
                        >
                        <datalist id="material-suggestions">
                            @foreach($materialNames as $materialName)
                                <option value="{{ $materialName }}">
                            @endforeach
                        </datalist>
                        @if($materialSearch)
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button 
                                    wire:click="clearMaterialSearch"
                                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                    title="Clear material search"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Klien Filter --}}
                <div class="lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-1 text-gray-400"></i>
                        Klien
                    </label>
                    <select
                        wire:model.live="klienFilter"
                        class="block w-full py-3 px-3 border border-gray-300 rounded-lg text-sm
                               focus:ring-2 focus:ring-purple-500 focus:border-purple-500 
                               transition-all duration-200 bg-gray-50 focus:bg-white"
                    >
                        <option value="">Semua Klien</option>
                        @foreach($kliens->groupBy('nama') as $namaKlien => $klienGroup)
                            <optgroup label="{{ $namaKlien }}">
                                @foreach($klienGroup as $klien)
                                    <option value="{{ $klien->id }}">{{ $klien->cabang }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                {{-- Plant/Location Filter --}}
                <div class="lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>
                        Lokasi
                    </label>
                    <select
                        wire:model.live="cabangFilter"
                        class="block w-full py-3 px-3 border border-gray-300 rounded-lg text-sm
                               focus:ring-2 focus:ring-purple-500 focus:border-purple-500 
                               transition-all duration-200 bg-gray-50 focus:bg-white"
                    >
                        <option value="">Semua Lokasi</option>
                        @if($klienFilter && $selectedKlienCabangs->isNotEmpty())
                            {{-- Show only cabangs for selected klien --}}
                            @foreach($selectedKlienCabangs as $cabang)
                                <option value="{{ $cabang }}">{{ $cabang }}</option>
                            @endforeach
                        @else
                            {{-- Show all cabangs --}}
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang }}">{{ $cabang }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-1 text-gray-400"></i>
                        Status
                    </label>
                    <select
                        wire:model.live="statusFilter"
                        class="block w-full py-3 px-3 border border-gray-300 rounded-lg text-sm
                               focus:ring-2 focus:ring-purple-500 focus:border-purple-500 
                               transition-all duration-200 bg-gray-50 focus:bg-white"
                    >
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="pending">Pending</option>
                        <option value="non_aktif">Non-aktif</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Filters Summary --}}
    @if($search || $materialSearch || $klienFilter || $cabangFilter || $statusFilter)
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-filter text-blue-600"></i>
                    <span class="text-sm font-medium text-blue-900">Filter Aktif:</span>
                </div>
                <button
                    wire:click="clearFilters"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                    Reset Semua
                </button>
            </div>
            <div class="mt-2 flex flex-wrap gap-2">
                @if($search)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-search mr-1"></i>
                        Pencarian: "{{ $search }}"
                        <button wire:click="clearSearch" class="ml-1 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endif
                @if($materialSearch)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-cube mr-1"></i>
                        Material: "{{ $materialSearch }}"
                        <button wire:click="clearMaterialSearch" class="ml-1 text-green-600 hover:text-green-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endif
                @if($klienFilter)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        <i class="fas fa-building mr-1"></i>
                        Klien: {{ $kliens->where('id', $klienFilter)->first()->nama ?? 'Unknown' }}
                        <button wire:click="$set('klienFilter', '')" class="ml-1 text-purple-600 hover:text-purple-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endif
                @if($cabangFilter)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        Lokasi: {{ $cabangFilter }}
                        <button wire:click="$set('cabangFilter', '')" class="ml-1 text-orange-600 hover:text-orange-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endif
                @if($statusFilter)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-flag mr-1"></i>
                        Status: {{ ucfirst($statusFilter) }}
                        <button wire:click="$set('statusFilter', '')" class="ml-1 text-yellow-600 hover:text-yellow-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endif
            </div>
        </div>
    @endif

    {{-- Status Count Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Material</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($statusCounts['all']) }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Aktif</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($statusCounts['aktif']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($statusCounts['pending']) }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Non-aktif</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($statusCounts['non_aktif']) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Materials Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-clipboard-list mr-2 text-purple-600"></i>
                    Daftar Spesifikasi Material
                </h3>
                <div class="text-sm text-gray-600">
                    {{ $materials->total() }} material ditemukan
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('material_type')" class="flex items-center space-x-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                <span>Material</span>
                                @if($sort === 'material_type')
                                    <i class="fas fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }} text-purple-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('klien')" class="flex items-center space-x-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                <span>Klien</span>
                                @if($sort === 'klien')
                                    <i class="fas fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }} text-purple-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('cabang')" class="flex items-center space-x-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                <span>Lokasi</span>
                                @if($sort === 'cabang')
                                    <i class="fas fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }} text-purple-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spesifikasi</th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('satuan')" class="flex items-center space-x-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                <span>Satuan</span>
                                @if($sort === 'satuan')
                                    <i class="fas fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }} text-purple-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('status')" class="flex items-center space-x-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                <span>Status</span>
                                @if($sort === 'status')
                                    <i class="fas fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }} text-purple-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('updated_at')" class="flex items-center space-x-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                <span>Update Terakhir</span>
                                @if($sort === 'updated_at')
                                    <i class="fas fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }} text-purple-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($materials as $material)
                        <tr wire:key="material-{{ $material->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-cube text-purple-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $material->nama }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $material->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $material->klien->nama }}</div>
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-building mr-1"></i>
                                    {{ $material->klien->cabang }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-map-marker-alt text-blue-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $material->klien->cabang }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($material->spesifikasi)
                                    <div class="text-sm text-gray-900 max-w-xs">
                                        <div class="line-clamp-2">{{ \Illuminate\Support\Str::limit($material->spesifikasi, 100) }}</div>
                                        @if(strlen($material->spesifikasi) > 100)
                                            <button 
                                                wire:click="editMaterial({{ $material->id }})"
                                                class="text-xs text-purple-600 hover:text-purple-800 mt-1"
                                            >
                                                Lihat lengkap...
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400 italic">Belum ada spesifikasi</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $material->satuan }}</td>
                            <td class="px-6 py-4">
                                @if($material->status === 'aktif')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Aktif
                                    </span>
                                @elseif($material->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Non-aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $material->updated_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button
                                        wire:click="editMaterial({{ $material->id }})"
                                        class="text-purple-600 hover:text-purple-800 transition-colors duration-200"
                                        title="Edit Spesifikasi"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $material->id }})"
                                        class="text-red-600 hover:text-red-800 transition-colors duration-200"
                                        title="Hapus"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Material</h3>
                                    <p class="text-gray-600">
                                        @if($search || $materialSearch || $klienFilter || $cabangFilter || $statusFilter)
                                            Tidak ditemukan material dengan kriteria pencarian yang Anda masukkan.
                                        @else
                                            Belum ada material yang terdaftar dalam sistem.
                                        @endif
                                    </p>
                                    @if($search || $materialSearch || $klienFilter || $cabangFilter || $statusFilter)
                                        <button
                                            wire:click="clearFilters"
                                            class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 text-sm font-medium"
                                        >
                                            <i class="fas fa-times mr-2"></i>
                                            Reset Filter
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($materials->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $materials->links() }}
            </div>
        @endif
    </div>

    {{-- Edit Modal --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/10 backdrop-blur-sm transition-opacity" wire:click="closeEditModal"></div>

            {{-- Modal Container --}}
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full transform transition-all" @click.stop>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-edit mr-2 text-purple-600"></i>
                                Edit Spesifikasi Material
                            </h3>
                            <button
                                wire:click="closeEditModal"
                                class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                            >
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                <form wire:submit.prevent="submitEditForm" class="p-6 space-y-4">
                    <div>
                        <label for="edit_nama" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Material <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="editForm.nama"
                            id="edit_nama"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('editForm.nama') border-red-500 @enderror"
                            placeholder="Nama material"
                        >
                        @error('editForm.nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit_satuan" class="block text-sm font-medium text-gray-700 mb-2">
                            Satuan <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="editForm.satuan"
                            id="edit_satuan"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('editForm.satuan') border-red-500 @enderror"
                            placeholder="kg, liter, ml, dll"
                        >
                        @error('editForm.satuan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit_spesifikasi" class="block text-sm font-medium text-gray-700 mb-2">
                            Spesifikasi
                        </label>
                        <textarea
                            wire:model="editForm.spesifikasi"
                            id="edit_spesifikasi"
                            rows="4"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Deskripsi detail spesifikasi material..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Contoh: Moisture <12%, Protein >8%, Fat >12%, dll.
                        </p>
                    </div>

                    <div>
                        <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model="editForm.status"
                            id="edit_status"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('editForm.status') border-red-500 @enderror"
                        >
                            <option value="aktif">Aktif</option>
                            <option value="pending">Pending</option>
                            <option value="non_aktif">Non-aktif</option>
                        </select>
                        @error('editForm.status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button
                            type="button"
                            wire:click="closeEditModal"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200 font-medium"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 font-medium"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="submitEditForm">
                                <i class="fas fa-save mr-2"></i>
                                Simpan
                            </span>
                            <span wire:loading wire:target="submitEditForm">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/10 backdrop-blur-sm transition-opacity" wire:click="cancelDelete"></div>

            {{-- Modal Container --}}
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full transform transition-all" @click.stop>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                            {{ $deleteModal['title'] }}
                        </h3>
                    </div>
                
                <div class="px-6 py-4">
                    <p class="text-gray-700">{{ $deleteModal['message'] }}</p>
                    <p class="text-sm text-red-600 mt-2">
                        <i class="fas fa-warning mr-1"></i>
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <button
                        wire:click="cancelDelete"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </button>
                    <button
                        wire:click="deleteMaterial"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors duration-200 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="deleteMaterial">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus
                        </span>
                        <span wire:loading wire:target="deleteMaterial">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Menghapus...
                        </span>
                    </button>
                </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if(session()->has('message'))
        <div class="fixed top-4 right-4 z-[70]">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="fixed top-4 right-4 z-[70]">
            <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif
</div>

<script>
    // Auto-hide flash messages
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessages = document.querySelectorAll('[class*="fixed top-4 right-4"]');
        flashMessages.forEach(function(message) {
            setTimeout(function() {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.remove();
                }, 300);
            }, 5000);
        });
    });
</script>