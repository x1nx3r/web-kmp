{{-- Tab Pending Forecasting --}}
<div class="space-y-6">
    {{-- Search and Filter Section --}}
    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 mb-3 sm:mb-6">
        <div class="space-y-3 sm:space-y-6">
            {{-- Search Section --}}
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                {{-- Search Input --}}
                <div class="flex-1">
                    <label class="flex items-center text-xs sm:text-sm font-bold text-yellow-700 mb-1 sm:mb-3">
                        <div class="w-4 h-4 sm:w-6 sm:h-6 bg-yellow-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                            <i class="fas fa-search text-white text-xs"></i>
                        </div>
                        Pencarian Forecast Pending
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="searchInputPending" 
                               name="search_pending"
                               value="{{ request('search_pending') }}"
                               placeholder="Cari No. PO, nama klien, atau no forecast..." 
                               class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-pending"
                               onkeyup="debounceSearchPending()"
                               onchange="submitSearchPending()">
                        <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                            <div class="w-3 h-3 sm:w-6 sm:h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-yellow-500 text-xs sm:text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Section --}}
            <div class="rounded-lg sm:rounded-xl p-2 sm:p-4">
                <h3 class="flex items-center text-xs sm:text-sm font-bold text-yellow-700 mb-2 sm:mb-4">
                    <div class="w-4 h-4 sm:w-6 sm:h-6 bg-yellow-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                        <i class="fas fa-filter text-white text-xs"></i>
                    </div>
                    Filter & Urutan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 sm:gap-4 filter-grid">
                    {{-- Date Range Filter --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-yellow-700 mb-1 sm:mb-2">
                            <i class="fas fa-calendar mr-1 sm:mr-2 text-yellow-500 text-xs"></i>
                            Perkiraan Tanggal Kirim
                        </label>
                        <input type="date" id="dateRangeFilter" name="date_range" value="{{ request('date_range') }}" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-yellow-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersPending()">
                    </div>

                    {{-- Sort by Amount --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-yellow-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-yellow-500 text-xs"></i>
                            Urutkan Total
                        </label>
                        <select id="sortAmountPending" name="sort_amount_pending" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-yellow-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersPending()">
                            <option value="">Default</option>
                            <option value="highest" {{ request('sort_amount_pending') == 'highest' ? 'selected' : '' }}>Tertinggi</option>
                            <option value="lowest" {{ request('sort_amount_pending') == 'lowest' ? 'selected' : '' }}>Terendah</option>
                        </select>
                    </div>

                    {{-- Sort by Quantity --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-yellow-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-yellow-500 text-xs"></i>
                            Urutkan Qty
                        </label>
                        <select id="sortQtyPending" name="sort_qty_pending" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-yellow-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersPending()">
                            <option value="">Default</option>
                            <option value="highest" {{ request('sort_qty_pending') == 'highest' ? 'selected' : '' }}>Terbanyak</option>
                            <option value="lowest" {{ request('sort_qty_pending') == 'lowest' ? 'selected' : '' }}>Tersedikit</option>
                        </select>
                    </div>

                    {{-- Sort by Date --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-yellow-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-yellow-500 text-xs"></i>
                            Urutkan Tanggal
                        </label>
                        <select id="sortDatePending" name="sort_date_pending" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-yellow-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersPending()">
                            <option value="">Default</option>
                            <option value="newest" {{ request('sort_date_pending') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort_date_pending') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>

                    {{-- Sort by Hari Kirim --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-yellow-700 mb-1 sm:mb-2">
                            <i class="fas fa-truck mr-1 sm:mr-2 text-yellow-500 text-xs"></i>
                            Hari Kirim
                        </label>
                        <select id="sortHariKirimPending" name="sort_hari_kirim" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-yellow-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersPending()">
                            <option value="">Semua Hari</option>
                            <option value="senin" {{ request('sort_hari_kirim') == 'senin' ? 'selected' : '' }}>Senin</option>
                            <option value="selasa" {{ request('sort_hari_kirim') == 'selasa' ? 'selected' : '' }}>Selasa</option>
                            <option value="rabu" {{ request('sort_hari_kirim') == 'rabu' ? 'selected' : '' }}>Rabu</option>
                            <option value="kamis" {{ request('sort_hari_kirim') == 'kamis' ? 'selected' : '' }}>Kamis</option>
                            <option value="jumat" {{ request('sort_hari_kirim') == 'jumat' ? 'selected' : '' }}>Jumat</option>
                            <option value="sabtu" {{ request('sort_hari_kirim') == 'sabtu' ? 'selected' : '' }}>Sabtu</option>
                            <option value="minggu" {{ request('sort_hari_kirim') == 'minggu' ? 'selected' : '' }}>Minggu</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Active Filters Display --}}
            <div id="activeFiltersPending" class="flex flex-wrap gap-2" style="display: none;">
                <span class="text-xs sm:text-sm font-bold text-yellow-700">Filter aktif:</span>
            </div>
        </div>
    </div>

    {{-- Simplified Header Section --}}
    <div class="flex items-center justify-between mb-4 bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
            <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center mr-2">
                <i class="fas fa-clock text-white text-xs"></i>
            </div>
            Forecast Pending
        </h2>
        
        {{-- Compact Summary Stats --}}
        <div class="flex items-center space-x-4 text-sm">
            @php
                $totalForecasts = $pendingForecasts->total();
                $totalPOs = collect($pendingForecasts->items())->groupBy('purchase_order_id')->count();
                $totalAmount = collect($pendingForecasts->items())->sum('total_harga_forecast');
            @endphp
            <div class="text-center">
                <p class="text-xs text-gray-500">PO</p>
                <p class="text-sm font-bold text-blue-600">{{ $totalPOs }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500">Forecast</p>
                <p class="text-sm font-bold text-yellow-600">{{ $totalForecasts }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500">Total</p>
                <p class="text-sm font-bold text-green-600">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Simplified PO Cards with Forecasts --}}
    <div class="space-y-2">
        @php
            // Group forecasts by purchase_order_id
            $groupedForecasts = collect($pendingForecasts->items())->groupBy('purchase_order_id');
        @endphp

        @forelse($groupedForecasts as $poId => $forecasts)
            @php
                $po = $forecasts->first()->purchaseOrder;
            @endphp
            {{-- Simplified PO Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 border-l-4 border-l-yellow-500 pending-forecast-card po-card" 
                 data-no-po="{{ strtolower($po->no_po ?? '') }}" 
                 data-klien="{{ strtolower((optional($po->klien)->nama ?? '') . (optional($po->klien)->cabang ? ' - ' . optional($po->klien)->cabang : '')) }}" 
                 data-forecasts="{{ $forecasts->count() }}">
                
                <div class="p-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-alt text-white text-xs"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ $po->no_po ?? 'N/A' }}</h3>
                                <p class="text-xs text-gray-500">{{ ($po->klien->nama ?? 'N/A') . ($po->klien->cabang ? ' - ' . $po->klien->cabang : '') }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $forecasts->count() }} forecast</p>
                                <p class="text-sm font-semibold text-green-600">Rp {{ number_format($forecasts->sum('total_harga_forecast'), 0, ',', '.') }}</p>
                            </div>
                            <button type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs flex items-center" onclick="toggleForecastList('po-{{ $poId }}')">
                                <i class="fas fa-chevron-right forecast-icon" id="icon-po-{{ $poId }}"></i>
                                <span class="ml-1" id="text-po-{{ $poId }}">Detail</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Simplified Forecasts List --}}
                <div class="border-t border-gray-200 forecast-list" id="forecast-list-po-{{ $poId }}">
                    <div class="p-3">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-chart-line text-yellow-600 mr-2"></i>
                            Daftar Forecast ({{ $forecasts->count() }})
                        </h4>
                        
                        <div class="space-y-2">
                            @foreach($forecasts as $forecast)
                                <div class="bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition-colors" 
                                     data-forecast-no="{{ strtolower($forecast->no_forecast ?? '') }}"
                                     data-purchasing="{{ strtolower(optional($forecast->purchasing)->name ?? '') }}"
                                     data-qty="{{ $forecast->total_qty_forecast ?? 0 }}"
                                     data-amount="{{ $forecast->total_harga_forecast ?? 0 }}"
                                     data-hari-kirim="{{ $forecast->hari_kirim_forecast ?? 0 }}"
                                     data-date="{{ $forecast->tanggal_forecast ? $forecast->tanggal_forecast->format('Y-m-d') : '' }}"
                                     data-status="{{ $forecast->status ?? '' }}">
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-semibold text-gray-900">{{ $forecast->no_forecast }}</span>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ ucfirst($forecast->status ?? 'Pending') }}
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500">
                                                <span><i class="fas fa-boxes mr-1"></i>{{ number_format($forecast->total_qty_forecast ?? 0, 0, ',', '.') }}</span>
                                                <span><i class="fas fa-money-bill-wave mr-1"></i>Rp {{ number_format($forecast->total_harga_forecast ?? 0, 0, ',', '.') }}</span>
                                                <span><i class="fas fa-calendar mr-1"></i>{{ $forecast->tanggal_forecast ? $forecast->tanggal_forecast->format('d/m/Y') : 'N/A' }}</span>
                                                <span><i class="fas fa-truck mr-1"></i>{{ $forecast->hari_kirim_forecast ?? 'N/A' }}</span>
                                            </div>
                                            <div class="flex items-center mt-1 text-xs text-blue-600">
                                                <span><i class="fas fa-user-tie mr-1"></i>PIC: {{ optional($forecast->purchasing)->nama ?? 'Belum ditentukan' }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex space-x-1">
                                            <button type="button" 
                                                    onclick="openForecastDetailModal({{ json_encode([
                                                        'id' => $forecast->id,
                                                        'no_forecast' => $forecast->no_forecast,
                                                        'no_po' => $po->no_po ?? 'N/A',
                                                        'klien' => (optional($po->klien)->nama ?? 'N/A') . (optional($po->klien)->cabang ? ' - ' . optional($po->klien)->cabang : ''),
                                                        'pic_purchasing' => optional($forecast->purchasing)->nama ?? 'Tidak ada PIC',
                                                        'tanggal_forecast' => $forecast->tanggal_forecast ? $forecast->tanggal_forecast->format('d/m/Y') : 'N/A',
                                                        'status' => ucfirst($forecast->status ?? 'Pending'),
                                                        'total_qty' => number_format($forecast->total_qty_forecast ?? 0, 0, ',', '.'),
                                                        'total_harga' => 'Rp ' . number_format($forecast->total_harga_forecast ?? 0, 0, ',', '.'),
                                                        'hari_kirim' => $forecast->hari_kirim_forecast ?? 'N/A',
                                                        'catatan' => $forecast->catatan ?? '',
                                                        'details' => $forecast->forecastDetails->map(function($detail) {
                                                            return [
                                                                'bahan_baku' => optional($detail->bahanBakuSupplier)->nama ?? 'N/A',
                                                                'supplier' => optional($detail->bahanBakuSupplier->supplier)->nama ?? 'N/A',
                                                                'qty' => number_format($detail->qty_forecast ?? 0, 0, ',', '.'),
                                                                'harga_satuan' => 'Rp ' . number_format($detail->harga_satuan_forecast ?? 0, 0, ',', '.'),
                                                                'total_harga' => 'Rp ' . number_format($detail->total_harga_forecast ?? 0, 0, ',', '.')
                                                            ];
                                                        })
                                                    ]) }})"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded flex items-center transition-colors">
                                                <i class="fas fa-cog mr-1"></i>Kelola
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($forecast->catatan)
                                        <div class="mt-2 pt-2 border-t border-gray-200">
                                            <p class="text-xs text-gray-600">
                                                <i class="fas fa-sticky-note text-gray-400 mr-1"></i>
                                                {{ $forecast->catatan }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-clock text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Forecast Pending</h3>
                    <p>Belum ada forecast dengan status pending.</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if($pendingForecasts->hasPages())
            <div class="bg-white rounded-lg shadow-sm border p-4 mt-6">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    {{-- Results Info --}}
                    <div class="mb-3 sm:mb-0">
                        <p class="text-sm text-gray-700">
                            Menampilkan
                            <span class="font-medium">{{ $pendingForecasts->firstItem() }}</span>
                            sampai
                            <span class="font-medium">{{ $pendingForecasts->lastItem() }}</span>
                            dari
                            <span class="font-medium">{{ $pendingForecasts->total() }}</span>
                            Forecast Pending
                        </p>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="flex items-center space-x-2">
                        {{-- Previous Page --}}
                        @if ($pendingForecasts->onFirstPage())
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </span>
                        @else
                            @php
                                $prevUrl = $pendingForecasts->previousPageUrl();
                                $prevUrlParts = parse_url($prevUrl);
                                parse_str($prevUrlParts['query'] ?? '', $prevParams);
                                $prevParams['tab'] = 'pending';
                                // Preserve other filters
                                if (request('search_pending')) $prevParams['search_pending'] = request('search_pending');
                                if (request('date_range')) $prevParams['date_range'] = request('date_range');
                                if (request('sort_amount_pending')) $prevParams['sort_amount_pending'] = request('sort_amount_pending');
                                if (request('sort_qty_pending')) $prevParams['sort_qty_pending'] = request('sort_qty_pending');
                                if (request('sort_date_pending')) $prevParams['sort_date_pending'] = request('sort_date_pending');
                                if (request('sort_hari_kirim')) $prevParams['sort_hari_kirim'] = request('sort_hari_kirim');
                                $prevUrl = $prevUrlParts['path'] . '?' . http_build_query($prevParams);
                            @endphp
                            <a href="{{ $prevUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 transition-colors">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @if($pendingForecasts->lastPage() > 1)
                            <div class="hidden sm:flex items-center space-x-1">
                                @foreach ($pendingForecasts->getUrlRange(1, $pendingForecasts->lastPage()) as $page => $url)
                                    @if ($page == $pendingForecasts->currentPage())
                                        <span class="px-3 py-2 text-sm font-medium text-white bg-yellow-600 border border-yellow-600 rounded-lg">
                                            {{ $page }}
                                        </span>
                                    @else
                                        @php
                                            $pageUrlParts = parse_url($url);
                                            parse_str($pageUrlParts['query'] ?? '', $pageUrlParams);
                                            $pageUrlParams['tab'] = 'pending';
                                            // Preserve other filters
                                            if (request('search_pending')) $pageUrlParams['search_pending'] = request('search_pending');
                                            if (request('date_range')) $pageUrlParams['date_range'] = request('date_range');
                                            if (request('sort_amount_pending')) $pageUrlParams['sort_amount_pending'] = request('sort_amount_pending');
                                            if (request('sort_qty_pending')) $pageUrlParams['sort_qty_pending'] = request('sort_qty_pending');
                                            if (request('sort_date_pending')) $pageUrlParams['sort_date_pending'] = request('sort_date_pending');
                                            if (request('sort_hari_kirim')) $pageUrlParams['sort_hari_kirim'] = request('sort_hari_kirim');
                                            $pageUrl = $pageUrlParts['path'] . '?' . http_build_query($pageUrlParams);
                                        @endphp
                                        <a href="{{ $pageUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 transition-colors">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Mobile Page Indicator --}}
                            <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                                {{ $pendingForecasts->currentPage() }} / {{ $pendingForecasts->lastPage() }}
                            </div>
                        @endif

                        {{-- Next Page --}}
                        @if ($pendingForecasts->hasMorePages())
                            @php
                                $nextUrl = $pendingForecasts->nextPageUrl();
                                $nextUrlParts = parse_url($nextUrl);
                                parse_str($nextUrlParts['query'] ?? '', $nextParams);
                                $nextParams['tab'] = 'pending';
                                // Preserve other filters
                                if (request('search_pending')) $nextParams['search_pending'] = request('search_pending');
                                if (request('date_range')) $nextParams['date_range'] = request('date_range');
                                if (request('sort_amount_pending')) $nextParams['sort_amount_pending'] = request('sort_amount_pending');
                                if (request('sort_qty_pending')) $nextParams['sort_qty_pending'] = request('sort_qty_pending');
                                if (request('sort_date_pending')) $nextParams['sort_date_pending'] = request('sort_date_pending');
                                if (request('sort_hari_kirim')) $nextParams['sort_hari_kirim'] = request('sort_hari_kirim');
                                $nextUrl = $nextUrlParts['path'] . '?' . http_build_query($nextParams);
                            @endphp
                            <a href="{{ $nextUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 transition-colors">
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
    </div>

<script>
// Debounced search function for server-side filtering
let searchTimeoutPending;
function debounceSearchPending() {
    clearTimeout(searchTimeoutPending);
    searchTimeoutPending = setTimeout(() => {
        submitSearchPending();
    }, 1000); // Wait 1 second before submitting
}

// Submit search to server
function submitSearchPending() {
    const currentParams = new URLSearchParams(window.location.search);
    const searchValue = document.getElementById('searchInputPending').value;
    
    // Preserve current tab
    currentParams.set('tab', 'pending');
    
    // Update search parameter
    if (searchValue) {
        currentParams.set('search_pending', searchValue);
    } else {
        currentParams.delete('search_pending');
    }
    
    // Reset to first page when searching
    currentParams.delete('page_pending');
    
    // Navigate to new URL
    window.location.href = window.location.pathname + '?' + currentParams.toString();
}

// Apply filters function for server-side filtering
function applyFiltersPending() {
    console.log('applyFiltersPending called');
    const currentParams = new URLSearchParams(window.location.search);
    
    // Get filter values
    const searchValue = document.getElementById('searchInputPending').value;
    const dateRange = document.getElementById('dateRangeFilter').value;
    const sortAmount = document.getElementById('sortAmountPending').value;
    const sortQty = document.getElementById('sortQtyPending').value;
    const sortDate = document.getElementById('sortDatePending').value;
    const sortHariKirim = document.getElementById('sortHariKirimPending').value;
    
    console.log('Filter values:', {
        searchValue,
        dateRange,
        sortAmount,
        sortQty,
        sortDate,
        sortHariKirim
    });
    
    // Preserve current tab
    currentParams.set('tab', 'pending');
    
    // Update parameters
    if (searchValue) currentParams.set('search_pending', searchValue);
    else currentParams.delete('search_pending');
    
    if (dateRange) currentParams.set('date_range', dateRange);
    else currentParams.delete('date_range');
    
    if (sortAmount) currentParams.set('sort_amount_pending', sortAmount);
    else currentParams.delete('sort_amount_pending');
    
    if (sortQty) currentParams.set('sort_qty_pending', sortQty);
    else currentParams.delete('sort_qty_pending');
    
    if (sortDate) currentParams.set('sort_date_pending', sortDate);
    else currentParams.delete('sort_date_pending');
    
    if (sortHariKirim) currentParams.set('sort_hari_kirim', sortHariKirim);
    else currentParams.delete('sort_hari_kirim');
    
    // Reset to first page when filtering
    currentParams.delete('page_pending');
    
    const newUrl = window.location.pathname + '?' + currentParams.toString();
    console.log('Navigating to:', newUrl);
    
    // Navigate to new URL
    window.location.href = newUrl;
}

// Update active filters display
function updateActiveFiltersPending() {
    const activeFiltersContainer = document.getElementById('activeFiltersPending');
    const searchValue = document.getElementById('searchInputPending').value;
    const dateRange = document.getElementById('dateRangeFilter').value;
    const sortAmount = document.getElementById('sortAmountPending').value;
    const sortQty = document.getElementById('sortQtyPending').value;
    const sortDate = document.getElementById('sortDatePending').value;
    const sortHariKirim = document.getElementById('sortHariKirimPending').value;
    
    let hasActiveFilters = false;
    let filtersHTML = '<span class="text-xs sm:text-sm font-bold text-yellow-700">Filter aktif:</span>';
    
    if (searchValue) {
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pencarian: ${searchValue}</span>`;
        hasActiveFilters = true;
    }
    
    if (dateRange) {
        const formattedDate = new Date(dateRange).toLocaleDateString('id-ID');
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Tanggal: ${formattedDate}</span>`;
        hasActiveFilters = true;
    }
    
    if (sortAmount) {
        const sortLabels = {
            'highest': 'Total Tertinggi',
            'lowest': 'Total Terendah'
        };
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">${sortLabels[sortAmount]}</span>`;
        hasActiveFilters = true;
    }
    
    if (sortQty) {
        const qtyLabels = {
            'highest': 'Qty Terbanyak',
            'lowest': 'Qty Tersedikit'
        };
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">${qtyLabels[sortQty]}</span>`;
        hasActiveFilters = true;
    }
    
    if (sortDate) {
        const dateLabels = {
            'newest': 'Terbaru',
            'oldest': 'Terlama'
        };
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Urutkan: ${dateLabels[sortDate]}</span>`;
        hasActiveFilters = true;
    }
    
    if (sortHariKirim) {
        const hariLabels = {
            'senin': 'Senin',
            'selasa': 'Selasa',
            'rabu': 'Rabu',
            'kamis': 'Kamis',
            'jumat': 'Jumat',
            'sabtu': 'Sabtu',
            'minggu': 'Minggu'
        };
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Hari: ${hariLabels[sortHariKirim]}</span>`;
        hasActiveFilters = true;
    }
    
    if (hasActiveFilters) {
        filtersHTML += `<button onclick="clearAllFiltersPending()" class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs hover:bg-red-200 transition-colors ml-2">
            <i class="fas fa-times mr-1"></i>Hapus Semua
        </button>`;
        activeFiltersContainer.innerHTML = filtersHTML;
        activeFiltersContainer.style.display = 'flex';
    } else {
        activeFiltersContainer.style.display = 'none';
    }
}

// Clear all filters
function clearAllFiltersPending() {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Keep only the tab parameter
    const newParams = new URLSearchParams();
    newParams.set('tab', 'pending');
    
    window.location.href = window.location.pathname + '?' + newParams.toString();
}

// Toggle forecast list visibility
function toggleForecastList(poId) {
    const forecastList = document.getElementById('forecast-list-' + poId);
    const icon = document.getElementById('icon-' + poId);
    const text = document.getElementById('text-' + poId);
    
    if (forecastList.style.display === 'none') {
        // Show forecast list
        forecastList.style.display = 'block';
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
        if (text) text.textContent = 'Sembunyikan';
    } else {
        // Hide forecast list
        forecastList.style.display = 'none';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
        if (text) text.textContent = 'Tampilkan';
    }
}

// Initialize filters on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - initializing filters');
    
    // Set filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Set search value
    const searchValue = urlParams.get('search_pending');
    if (searchValue) {
        console.log('Setting search value:', searchValue);
        document.getElementById('searchInputPending').value = searchValue;
    }
    
    // Set date range filter
    const dateRange = urlParams.get('date_range');
    if (dateRange) {
        console.log('Setting date range:', dateRange);
        document.getElementById('dateRangeFilter').value = dateRange;
    }
    
    // Set sort filters
    const sortAmount = urlParams.get('sort_amount_pending');
    if (sortAmount) {
        console.log('Setting sort amount:', sortAmount);
        document.getElementById('sortAmountPending').value = sortAmount;
    }
    
    const sortQty = urlParams.get('sort_qty_pending');
    if (sortQty) {
        console.log('Setting sort qty:', sortQty);
        document.getElementById('sortQtyPending').value = sortQty;
    }
    
    const sortDate = urlParams.get('sort_date_pending');
    if (sortDate) {
        console.log('Setting sort date:', sortDate);
        document.getElementById('sortDatePending').value = sortDate;
    }
    
    const sortHariKirim = urlParams.get('sort_hari_kirim');
    if (sortHariKirim) {
        console.log('Setting sort hari kirim:', sortHariKirim);
        document.getElementById('sortHariKirimPending').value = sortHariKirim;
    }
    
    // Update active filters display
    updateActiveFiltersPending();
    
    // Initialize forecast list states
    document.querySelectorAll('.forecast-list').forEach(list => {
        list.style.display = 'none';
    });
    
    // Add event listeners for search input
    const searchInput = document.getElementById('searchInputPending');
    if (searchInput) {
        searchInput.addEventListener('input', debounceSearchPending);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitSearchPending();
            }
        });
    }
    
    console.log('Filter initialization complete');
});
</script>

<style>
/* Custom styles for pending forecasting page */
.pending-forecast-card {
    transition: box-shadow 0.3s ease;
}

.pending-forecast-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.toggle-forecast-btn {
    transition: background-color 0.2s ease;
}

.forecast-icon {
    transition: transform 0.2s ease-in-out;
}

.filter-button-active {
    background-color: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
}

.search-input-pending:focus {
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .pending-forecast-card {
        margin-bottom: 0.5rem;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
}

/* Animation for filter changes */
.filter-fade-in {
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

/* Active filter tags styling */
.active-filter-tag {
    animation: slideIn 0.2s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
