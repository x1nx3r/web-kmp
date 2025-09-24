{{-- Search and Filter Component --}}
@props(['availableLocations' => collect()])

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="space-y-6">
        {{-- Search Section --}}
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label class="flex items-center text-sm font-bold text-green-700 mb-3">
                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-2">
                        <i class="fas fa-search text-white text-xs"></i>
                    </div>
                    Pencarian
                </label>
                <div class="relative">
                    <input 
                        type="text"
                        x-model="search"
                        x-on:input="debounceSearch()"
                        placeholder="Cari nama klien, lokasi cabang, atau kontak..."
                        class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm"
                    >
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-search text-green-500 text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="rounded-xl p-4 bg-gray-50">
            <h3 class="flex items-center text-sm font-bold text-green-700 mb-4">
                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-2">
                    <i class="fas fa-filter text-white text-xs"></i>
                </div>
                Filter & Urutan
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-2">Filter Lokasi</label>
                    <select 
                        x-model="location"
                        x-on:change="applyFilters()"
                        class="w-full py-3 px-4 border-2 border-green-200 rounded-lg focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-sm"
                    >
                        <option value="">Semua Lokasi</option>
                        @foreach($availableLocations as $location)
                            <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                {{ $location }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-2">Urutan</label>
                    <div class="flex gap-2">
                        <select 
                            x-model="sort"
                            x-on:change="applyFilters()"
                            class="flex-1 py-3 px-4 border-2 border-green-200 rounded-lg focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-sm"
                        >
                            <option value="nama">Nama Klien</option>
                            <option value="cabang_count">Jumlah Cabang</option>
                            <option value="lokasi">Lokasi Utama</option>
                            <option value="updated_at">Terbaru</option>
                        </select>
                        <button 
                            x-on:click="toggleDirection()"
                            class="px-4 py-3 border-2 border-green-200 rounded-lg bg-white hover:bg-green-50 transition-colors duration-200 text-sm font-medium"
                            x-text="direction === 'desc' ? '↓ Desc' : '↑ Asc'"
                        ></button>
                    </div>
                </div>
            </div>
            
            {{-- Active Filters --}}
            <div x-show="search || location" class="flex flex-wrap gap-2 mt-4">
                <span class="text-sm font-medium text-gray-700">Filter aktif:</span>
                <template x-if="search">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-search mr-1"></i>
                        "<span x-text="search"></span>"
                    </span>
                </template>
                <template x-if="location">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <span x-text="location"></span>
                    </span>
                </template>
            </div>
        </div>
    </div>
</div>