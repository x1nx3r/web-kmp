{{-- Tab Gagal Forecasting --}}
<div class="space-y-6">
    {{-- Search and Filter Section --}}
    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 mb-3 sm:mb-6">
        <div class="space-y-3 sm:space-y-6">
            {{-- Search Section --}}
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                {{-- Search Input --}}
                <div class="flex-1">
                    <label class="flex items-center text-xs sm:text-sm font-bold text-red-700 mb-1 sm:mb-3">
                        <div class="w-4 h-4 sm:w-6 sm:h-6 bg-red-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                            <i class="fas fa-search text-white text-xs"></i>
                        </div>
                        Pencarian Forecast Gagal
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="searchInputGagal" 
                               name="search_gagal"
                               value="{{ request('search_gagal') }}"
                               placeholder="Cari No. PO, nama klien, atau no forecast..." 
                               class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-red-200 focus:border-red-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-gagal"
                               onkeyup="debounceSearchGagal()"
                               onchange="submitSearchGagal()">
                        <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                            <div class="w-3 h-3 sm:w-6 sm:h-6 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-red-500 text-xs sm:text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Section --}}
            <div class="rounded-lg sm:rounded-xl p-2 sm:p-4">
                <h3 class="flex items-center text-xs sm:text-sm font-bold text-red-700 mb-2 sm:mb-4">
                    <div class="w-4 h-4 sm:w-6 sm:h-6 bg-red-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                        <i class="fas fa-filter text-white text-xs"></i>
                    </div>
                    Filter & Urutan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4">
                    {{-- Date Range Filter --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-red-700 mb-1 sm:mb-2">
                            <i class="fas fa-calendar mr-1 sm:mr-2 text-red-500 text-xs"></i>
                            Tanggal Forecast
                        </label>
                        <input type="date" id="dateRangeFilterGagal" name="date_range_gagal" value="{{ request('date_range_gagal') }}" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-red-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-red-200 focus:border-red-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersGagal()">
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-red-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-red-500 text-xs"></i>
                            Urutan
                        </label>
                        <select id="sortOrderGagal" name="sort_order_gagal" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-red-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-red-200 focus:border-red-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersGagal()">
                            <option value="newest" {{ request('sort_order_gagal') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort_order_gagal') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>

                    {{-- Clear Filter Button --}}
                    <div class="flex items-end">
                        <button type="button" onclick="clearFiltersGagal()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 sm:py-3 px-2 sm:px-4 rounded-lg transition-all duration-200 text-xs sm:text-sm font-medium">
                            <i class="fas fa-eraser mr-1 sm:mr-2"></i>
                            Bersihkan Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-6">
            <i class="fas fa-times-circle text-red-600 mr-2"></i>
            Forecast Gagal
        </h3>

        @forelse($gagalForecasts ?? [] as $forecast)
            <div class="border border-gray-200 rounded-lg p-6 mb-4 hover:shadow-md transition-shadow duration-200">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900">{{ $forecast->no_forecast }}</h4>
                        <p class="text-gray-600 flex items-center mt-1">
                            <i class="fas fa-file-alt text-gray-400 mr-2"></i>
                            PO: {{ $forecast->purchaseOrder->no_po ?? 'N/A' }}
                        </p>
                        <p class="text-gray-600 flex items-center mt-1">
                            <i class="fas fa-user text-gray-400 mr-2"></i>
                            {{ $forecast->purchaseOrder->klien->nama ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                            {{ $forecast->status_label }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ $forecast->tanggal_forecast_formatted }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center">
                        <i class="fas fa-boxes text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600">
                            Total Qty: <span class="font-medium">{{ $forecast->total_qty_forecast_formatted }}</span>
                        </span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-money-bill-wave text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600">
                            Total: <span class="font-medium">{{ $forecast->total_harga_forecast_formatted }}</span>
                        </span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-truck text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600">
                            Kirim: <span class="font-medium">{{ $forecast->hari_kirim_forecast }} hari</span>
                        </span>
                    </div>
                </div>

                @if($forecast->catatan)
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-sticky-note text-gray-400 mr-2"></i>
                            <strong>Catatan:</strong> {{ $forecast->catatan }}
                        </p>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-times-circle text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Forecast Gagal</h3>
                <p>Belum ada forecast dengan status gagal.</p>
            </div>
        @endforelse
    </div>
</div>
