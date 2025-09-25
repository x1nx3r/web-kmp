{{-- Search and Filter Component --}}
@props(['availableLocations' => collect()])

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    {{-- Consolidated Search and Filter Section --}}
    <div class="flex items-center gap-4">
        {{-- Search Input --}}
        <div class="flex-1 min-w-0">
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

        {{-- Location Filter --}}
        <div class="w-48">
            <select 
                x-model="location"
                x-on:change="applyFilters()"
                class="w-full text-center pr-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm"
            >
                <option value="">Semua Lokasi</option>
                @foreach($availableLocations as $location)
                    <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                        {{ $location }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Sort Options --}}
        <div class="w-48">
            <select 
                x-model="sort"
                x-on:change="applyFilters()"
                class="w-full text-center pr-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm"
            >
                <option value="nama">Nama Klien</option>
                <option value="updated_at">Terbaru</option>
            </select>
        </div>

        {{-- Sort Direction --}}
        <div class="w-20">
            <button 
                x-on:click="toggleDirection()"
                class="w-full px-4 py-3 border-2 border-green-200 rounded-lg bg-white hover:bg-green-50 transition-colors duration-200 text-sm font-medium"
                x-text="direction === 'desc' ? '↓' : '↑'"
                :title="direction === 'desc' ? 'Descending' : 'Ascending'"
            ></button>
        </div>
    </div>
    
    {{-- Active Filters Row (only show if filters are active) --}}
    <div x-show="search || location" x-transition class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
        <span class="text-sm font-medium text-gray-700">Filter aktif:</span>
        <template x-if="search">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fas fa-search mr-1"></i>
                "<span x-text="search"></span>"
                <button @click="search = ''; applyFilters()" class="ml-1 hover:text-green-600">×</button>
            </span>
        </template>
        <template x-if="location">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                <i class="fas fa-map-marker-alt mr-1"></i>
                <span x-text="location"></span>
                <button @click="location = ''; applyFilters()" class="ml-1 hover:text-blue-600">×</button>
            </span>
        </template>
        <button 
            @click="search = ''; location = ''; applyFilters()" 
            class="text-xs text-gray-500 hover:text-red-500 font-medium"
        >
            Clear All
        </button>
    </div>
</div>