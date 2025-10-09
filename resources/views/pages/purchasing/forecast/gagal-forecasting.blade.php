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
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    Forecast Gagal
                </h3>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Total: {{ $gagalForecasts->total() }} forecast (Halaman {{ $gagalForecasts->currentPage() }} dari {{ $gagalForecasts->lastPage() }})
                </div>
            </div>
        </div>

        @forelse($gagalForecasts as $forecast)
        @empty
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-times-circle text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Forecast Gagal</h3>
                <p>Belum ada forecast dengan status gagal.</p>
            </div>
        @endforelse

        @if($gagalForecasts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">No Forecast</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">PO & Klien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Detail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($gagalForecasts as $forecast)
                            <tr class="hover:bg-red-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $forecast->no_forecast }}</div>
                                    @if($forecast->purchasing)
                                        <div class="text-sm text-gray-500">PIC: {{ $forecast->purchasing->nama }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium">{{ $forecast->purchaseOrder->no_po ?? 'N/A' }}</div>
                                        <div class="text-gray-500">{{ $forecast->purchaseOrder->klien->nama ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div>Qty: <span class="font-medium">{{ $forecast->total_qty_forecast_formatted ?? number_format($forecast->total_qty_forecast, 0, ',', '.') }}</span></div>
                                        <div>Total: <span class="font-medium">{{ $forecast->total_harga_forecast_formatted ?? 'Rp ' . number_format($forecast->total_harga_forecast, 0, ',', '.') }}</span></div>
                                        <div>Kirim: <span class="font-medium">{{ $forecast->hari_kirim_forecast }}</span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $forecast->tanggal_forecast_formatted ?? \Carbon\Carbon::parse($forecast->tanggal_forecast)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Gagal
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openDetailModalGagal({{ $forecast->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                        <i class="fas fa-eye mr-1"></i>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($gagalForecasts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            @if($gagalForecasts->onFirstPage())
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                    Sebelumnya
                                </span>
                            @else
                                <a href="{{ $gagalForecasts->previousPageUrl() }}&tab=gagal" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    Sebelumnya
                                </a>
                            @endif

                            @if($gagalForecasts->hasMorePages())
                                <a href="{{ $gagalForecasts->nextPageUrl() }}&tab=gagal" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    Selanjutnya
                                </a>
                            @else
                                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                    Selanjutnya
                                </span>
                            @endif
                        </div>

                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Menampilkan
                                    <span class="font-medium">{{ $gagalForecasts->firstItem() }}</span>
                                    sampai
                                    <span class="font-medium">{{ $gagalForecasts->lastItem() }}</span>
                                    dari
                                    <span class="font-medium">{{ $gagalForecasts->total() }}</span>
                                    hasil
                                </p>
                            </div>

                            <div>
                                <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                    @if($gagalForecasts->onFirstPage())
                                        <span aria-disabled="true" aria-label="Previous">
                                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </span>
                                    @else
                                        <a href="{{ $gagalForecasts->previousPageUrl() }}&tab=gagal" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Previous">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    @endif

                                    @foreach($gagalForecasts->getUrlRange(1, $gagalForecasts->lastPage()) as $page => $url)
                                        @if($page == $gagalForecasts->currentPage())
                                            <span aria-current="page">
                                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-red-600 border border-red-600 cursor-default leading-5">{{ $page }}</span>
                                            </span>
                                        @else
                                            <a href="{{ $url }}&tab=gagal" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    @if($gagalForecasts->hasMorePages())
                                        <a href="{{ $gagalForecasts->nextPageUrl() }}&tab=gagal" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Next">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    @else
                                        <span aria-disabled="true" aria-label="Next">
                                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Include Modal Detail --}}
@include('pages.purchasing.forecast.gagal-forecasting.detail')

<script>
// Debounce timer for search
let searchTimeoutGagal = null;

// Function to handle search with debounce
function debounceSearchGagal() {
    clearTimeout(searchTimeoutGagal);
    searchTimeoutGagal = setTimeout(() => {
        submitSearchGagal();
    }, 300); // Wait 300ms after user stops typing
}

// Function to submit search form
function submitSearchGagal() {
    const searchInput = document.getElementById('searchInputGagal');
    const dateFilter = document.getElementById('dateRangeFilterGagal');
    const sortOrder = document.getElementById('sortOrderGagal');
    
    // Build query parameters
    const params = new URLSearchParams();
    
    if (searchInput.value.trim()) {
        params.append('search_gagal', searchInput.value.trim());
    }
    
    if (dateFilter.value) {
        params.append('date_range_gagal', dateFilter.value);
    }
    
    if (sortOrder.value) {
        params.append('sort_order_gagal', sortOrder.value);
    }
    
    // Add tab parameter to stay on gagal tab
    params.append('tab', 'gagal');
    
    // Reset to page 1 when searching/filtering
    params.append('page_gagal', '1');
    
    // Redirect with new parameters
    const url = '/forecasting' + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}

// Function to apply filters
function applyFiltersGagal() {
    submitSearchGagal();
}
</script>
