{{-- Pengiriman Berhasil Tab Content --}}
<div class="space-y-6">
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
                        Pencarian Pengiriman Berhasil
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="searchInputBerhasil" 
                               name="search_berhasil"
                               value="{{ request('search_berhasil') }}"
                               placeholder="Cari No. Pengiriman, No. PO, atau nama purchasing..." 
                               class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-berhasil"
                               onkeyup="debounceSearchBerhasil()"
                               onchange="submitSearchBerhasil()">
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
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
                    {{-- Date Range Filter --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                            <i class="fas fa-calendar mr-1 sm:mr-2 text-green-500 text-xs"></i>
                            Tanggal Pengiriman
                        </label>
                        <input type="date" 
                               id="dateRangeFilterBerhasil" 
                               name="date_range_berhasil" 
                               value="{{ request('date_range_berhasil') }}" 
                               class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" 
                               onchange="applyFiltersBerhasil()">
                    </div>

                    {{-- Filter by PIC Purchasing --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                            <i class="fas fa-user-tie mr-1 sm:mr-2 text-green-500 text-xs"></i>
                            PIC Purchasing
                        </label>
                        <select id="filterPurchasingBerhasil" 
                                name="filter_purchasing_berhasil" 
                                class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" 
                                onchange="applyFiltersBerhasil()">
                            <option value="">Semua PIC</option>
                            @php
                                $purchasingOptions = collect($pengirimanBerhasil->items() ?? [])->pluck('purchasing.nama', 'purchasing.id')->unique()->filter();
                            @endphp
                            @foreach($purchasingOptions as $id => $nama)
                                <option value="{{ $id }}" {{ request('filter_purchasing_berhasil') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-green-500 text-xs"></i>
                            Urutan
                        </label>
                        <select id="sortOrderBerhasil" 
                                name="sort_order_berhasil" 
                                class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" 
                                onchange="applyFiltersBerhasil()">
                            <option value="newest" {{ request('sort_order_berhasil') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort_order_berhasil') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>
                </div>
                
                {{-- Clear Filter Button (Below Grid) --}}
                <div class="flex justify-end mt-3">
                    <button onclick="clearAllFiltersBerhasil()" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 text-xs sm:text-sm font-semibold">
                        <i class="fas fa-times mr-1"></i>
                        Hapus Semua Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    Pengiriman Berhasil
                </h3>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Total: {{ $pengirimanBerhasil->total() ?? 0 }} pengiriman (Halaman {{ $pengirimanBerhasil->currentPage() ?? 1 }} dari {{ $pengirimanBerhasil->lastPage() ?? 1 }})
                </div>
            </div>
        </div>

        @forelse($pengirimanBerhasil ?? [] as $pengiriman)
        @empty
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-check-circle text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Pengiriman Berhasil</h3>
                <p>Belum ada pengiriman dengan status berhasil.</p>
            </div>
        @endforelse

        @if(isset($pengirimanBerhasil) && $pengirimanBerhasil->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">No Pengiriman</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">PO & PIC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Detail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pengirimanBerhasil as $pengiriman)
                            <tr class="hover:bg-green-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $pengiriman->no_pengiriman }}</div>
                                    <div class="text-sm text-gray-500">{{ $pengiriman->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $pengiriman->order->po_number ?? '-' }}</div>
                                    <div class="text-sm text-gray-500">{{ $pengiriman->purchasing->nama ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-sm font-medium text-blue-600">
                                            {{ number_format($pengiriman->total_qty_kirim ?? 0, 0, ',', '.') }} kg
                                        </div>
                                        <div class="text-sm font-medium text-green-600">
                                            Rp {{ number_format($pengiriman->total_harga_kirim ?? 0, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $pengiriman->pengirimanDetails->count() }} item
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $pengiriman->tanggal_kirim ? \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d M Y') : '-' }}</div>
                                    <div class="text-xs">{{ $pengiriman->hari_kirim ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ ucfirst($pengiriman->status) }}
                                    </span>
                                    @if($pengiriman->catatan)
                                        <div class="text-xs text-gray-600 mt-1">
                                            <i class="fas fa-sticky-note mr-1"></i>{{ $pengiriman->catatan }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex flex-col gap-2">
                                        <button onclick="openDetailModalBerhasil({{ $pengiriman->id }})" 
                                                class="inline-flex items-center px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-800 rounded-md transition-colors duration-150">
                                            <i class="fas fa-eye mr-1"></i>
                                            Detail
                                        </button>
                                        @if($pengiriman->approvalPembayaran && $pengiriman->approvalPembayaran->bukti_pembayaran)
                                            <a href="{{ asset('storage/' . $pengiriman->approvalPembayaran->bukti_pembayaran) }}" 
                                               download
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-md transition-colors duration-150 text-center">
                                                <i class="fas fa-download mr-1"></i>
                                                Bukti Bayar
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($pengirimanBerhasil->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            @if($pengirimanBerhasil->onFirstPage())
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                    Sebelumnya
                                </span>
                            @else
                                <a href="{{ $pengirimanBerhasil->previousPageUrl() }}&tab=pengiriman-berhasil" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    Sebelumnya
                                </a>
                            @endif

                            @if($pengirimanBerhasil->hasMorePages())
                                <a href="{{ $pengirimanBerhasil->nextPageUrl() }}&tab=pengiriman-berhasil" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
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
                                    <span class="font-medium">{{ $pengirimanBerhasil->firstItem() }}</span>
                                    sampai
                                    <span class="font-medium">{{ $pengirimanBerhasil->lastItem() }}</span>
                                    dari
                                    <span class="font-medium">{{ $pengirimanBerhasil->total() }}</span>
                                    hasil
                                </p>
                            </div>

                            <div>
                                <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                    @if($pengirimanBerhasil->onFirstPage())
                                        <span aria-disabled="true" aria-label="Previous">
                                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </span>
                                    @else
                                        @php
                                            $prevUrl = $pengirimanBerhasil->previousPageUrl();
                                            $prevUrlParts = parse_url($prevUrl);
                                            parse_str($prevUrlParts['query'] ?? '', $prevParams);
                                            $prevParams['tab'] = 'pengiriman-berhasil';
                                            // Preserve other filters
                                            if (request('search_berhasil')) $prevParams['search_berhasil'] = request('search_berhasil');
                                            if (request('date_range_berhasil')) $prevParams['date_range_berhasil'] = request('date_range_berhasil');
                                            if (request('filter_purchasing_berhasil')) $prevParams['filter_purchasing_berhasil'] = request('filter_purchasing_berhasil');
                                            if (request('sort_order_berhasil')) $prevParams['sort_order_berhasil'] = request('sort_order_berhasil');
                                            $prevUrl = $prevUrlParts['path'] . '?' . http_build_query($prevParams);
                                        @endphp
                                        <a href="{{ $prevUrl }}" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Previous">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    @endif

                                    @foreach($pengirimanBerhasil->getUrlRange(1, $pengirimanBerhasil->lastPage()) as $page => $url)
                                        @php
                                            $urlParts = parse_url($url);
                                            parse_str($urlParts['query'] ?? '', $urlParams);
                                            $urlParams['tab'] = 'pengiriman-berhasil';
                                            // Preserve other filters
                                            if (request('search_berhasil')) $urlParams['search_berhasil'] = request('search_berhasil');
                                            if (request('date_range_berhasil')) $urlParams['date_range_berhasil'] = request('date_range_berhasil');
                                            if (request('filter_purchasing_berhasil')) $urlParams['filter_purchasing_berhasil'] = request('filter_purchasing_berhasil');
                                            if (request('sort_order_berhasil')) $urlParams['sort_order_berhasil'] = request('sort_order_berhasil');
                                            $pageUrl = $urlParts['path'] . '?' . http_build_query($urlParams);
                                        @endphp
                                        
                                        @if($page == $pengirimanBerhasil->currentPage())
                                            <span aria-current="page">
                                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-green-600 border border-green-600 cursor-default leading-5">{{ $page }}</span>
                                            </span>
                                        @else
                                            <a href="{{ $pageUrl }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    @if($pengirimanBerhasil->hasMorePages())
                                        @php
                                            $nextUrl = $pengirimanBerhasil->nextPageUrl();
                                            $nextUrlParts = parse_url($nextUrl);
                                            parse_str($nextUrlParts['query'] ?? '', $nextParams);
                                            $nextParams['tab'] = 'pengiriman-berhasil';
                                            // Preserve other filters
                                            if (request('search_berhasil')) $nextParams['search_berhasil'] = request('search_berhasil');
                                            if (request('date_range_berhasil')) $nextParams['date_range_berhasil'] = request('date_range_berhasil');
                                            if (request('filter_purchasing_berhasil')) $nextParams['filter_purchasing_berhasil'] = request('filter_purchasing_berhasil');
                                            if (request('sort_order_berhasil')) $nextParams['sort_order_berhasil'] = request('sort_order_berhasil');
                                            $nextUrl = $nextUrlParts['path'] . '?' . http_build_query($nextParams);
                                        @endphp
                                        <a href="{{ $nextUrl }}" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Next">
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

{{-- Include Modal Detail Pengiriman Berhasil --}}
@include('pages.purchasing.pengiriman.pengiriman-berhasil.detail')

<script>
// Debounce timer for search
let searchTimeoutBerhasil = null;

// Function to handle search with debounce
function debounceSearchBerhasil() {
    clearTimeout(searchTimeoutBerhasil);
    searchTimeoutBerhasil = setTimeout(() => {
        submitSearchBerhasil();
    }, 300); // Wait 300ms after user stops typing
}

// Function to submit search form
function submitSearchBerhasil() {
    const searchInput = document.getElementById('searchInputBerhasil');
    const dateFilter = document.getElementById('dateRangeFilterBerhasil');
    const filterPurchasing = document.getElementById('filterPurchasingBerhasil');
    const sortOrder = document.getElementById('sortOrderBerhasil');
    
    // Build query parameters
    const params = new URLSearchParams();
    
    if (searchInput.value.trim()) {
        params.append('search_berhasil', searchInput.value.trim());
    }
    
    if (dateFilter.value) {
        params.append('date_range_berhasil', dateFilter.value);
    }
    
    if (filterPurchasing.value) {
        params.append('filter_purchasing_berhasil', filterPurchasing.value);
    }
    
    if (sortOrder.value) {
        params.append('sort_order_berhasil', sortOrder.value);
    }
    
    // Add tab parameter to stay on pengiriman-berhasil tab
    params.append('tab', 'pengiriman-berhasil');
    
    // Reset to page 1 when searching/filtering
    params.append('berhasil_page', '1');
    
    // Redirect with new parameters
    const url = '/procurement/pengiriman' + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}

// Function to apply filters
function applyFiltersBerhasil() {
    submitSearchBerhasil();
}

// Function to clear all filters
function clearAllFiltersBerhasil() {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Keep only the tab parameter
    const newParams = new URLSearchParams();
    newParams.set('tab', 'pengiriman-berhasil');
    
    window.location.href = '/procurement/pengiriman?' + newParams.toString();
}

// Initialize filters on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Set search value
    const searchValue = urlParams.get('search_berhasil');
    if (searchValue) {
        document.getElementById('searchInputBerhasil').value = searchValue;
    }
    
    // Set date range filter
    const dateRange = urlParams.get('date_range_berhasil');
    if (dateRange) {
        document.getElementById('dateRangeFilterBerhasil').value = dateRange;
    }
    
    // Set purchasing filter
    const filterPurchasing = urlParams.get('filter_purchasing_berhasil');
    if (filterPurchasing) {
        document.getElementById('filterPurchasingBerhasil').value = filterPurchasing;
    }
    
    // Set sort order filter
    const sortOrder = urlParams.get('sort_order_berhasil');
    if (sortOrder) {
        document.getElementById('sortOrderBerhasil').value = sortOrder;
    }
});


</script>
