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
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari No. PO, nama klien, atau no forecast..." 
                               class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-pending"
                               onkeyup="debounceSearchPending()">
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
                        <select id="sortAmountPending" name="sort_amount" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-yellow-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersPending()">
                            <option value="">Default</option>
                            <option value="highest" {{ request('sort_amount') == 'highest' ? 'selected' : '' }}>Tertinggi</option>
                            <option value="lowest" {{ request('sort_amount') == 'lowest' ? 'selected' : '' }}>Terendah</option>
                        </select>
                    </div>

                    {{-- Sort by Quantity --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-yellow-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-yellow-500 text-xs"></i>
                            Urutkan Qty
                        </label>
                        <select id="sortQtyPending" name="sort_qty" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-yellow-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersPending()">
                            <option value="">Default</option>
                            <option value="highest" {{ request('sort_qty') == 'highest' ? 'selected' : '' }}>Terbanyak</option>
                            <option value="lowest" {{ request('sort_qty') == 'lowest' ? 'selected' : '' }}>Tersedikit</option>
                        </select>
                    </div>

                    {{-- Sort by Date --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-yellow-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-yellow-500 text-xs"></i>
                            Urutkan Tanggal
                        </label>
                        <select id="sortDatePending" name="sort_date" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-yellow-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersPending()">
                            <option value="">Default</option>
                            <option value="newest" {{ request('sort_date') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort_date') == 'oldest' ? 'selected' : '' }}>Terlama</option>
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

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-center sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6 border border-yellow-200 py-2 bg-yellow-50 rounded-lg sm:rounded-xl">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center ">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-500 rounded-full flex items-center justify-center mr-2 sm:mr-3">
                <i class="fas fa-clock text-white text-xs sm:text-sm"></i>
            </div>
            Daftar Forecast Pending
        </h2>
        
        {{-- Summary Stats --}}
        <div class="flex items-center space-x-4 text-sm">
            @php
                $totalForecasts = collect($pendingForecasts ?? [])->count();
                $totalPOs = collect($pendingForecasts ?? [])->groupBy('purchase_order_id')->count();
                $totalAmount = collect($pendingForecasts ?? [])->sum('total_harga_forecast');
            @endphp
            <div class="text-center">
                <p class="text-xs text-gray-500">Total PO</p>
                <p class="text-lg font-bold text-blue-600">{{ $totalPOs }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500">Total Forecast</p>
                <p class="text-lg font-bold text-yellow-600">{{ $totalForecasts }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500">Total Perkiraan Harga</p>
                <p class="text-lg font-bold text-green-600">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- PO Cards with Forecasts --}}
    <div class="space-y-1 sm:space-y-4">
        @php
            // Group forecasts by purchase_order_id
            $groupedForecasts = collect($pendingForecasts ?? [])->groupBy('purchase_order_id');
        @endphp

        @forelse($groupedForecasts as $poId => $forecasts)
            @php
                $po = $forecasts->first()->purchaseOrder;
            @endphp
                     {{-- PO Card --}}
            <div class="bg-white rounded-lg sm:rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-gray-300 border-l-4 border-l-yellow-500 hover:border-l-yellow-600 pending-forecast-card" 
                 data-no-po="{{ strtolower($po->no_po ?? '') }}" 
                 data-klien="{{ strtolower((optional($po->klien)->nama ?? '') . (optional($po->klien)->cabang ? ' - ' . optional($po->klien)->cabang : '')) }}" 
                 data-forecasts="{{ $forecasts->count() }}">
                    
                    {{-- Mobile View --}}
                    <div class="block sm:hidden">
                        <div class="p-3 border-b border-gray-100">
                            {{-- Mobile Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="text-sm font-bold text-gray-900">{{ $po->no_po ?? 'N/A' }}</h3>
                                    <p class="text-xs text-yellow-600 mt-1">
                                        {{ (optional($po->klien)->nama ?? 'N/A') . (optional($po->klien)->cabang ? ' - ' . optional($po->klien)->cabang : '') }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        {{ $forecasts->count() }} Forecast
                                    </span>
                                    <button type="button" class="toggle-forecast-btn bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs transition-colors duration-200 flex items-center" onclick="toggleForecastList('po-{{ $poId }}')">
                                        <i class="fas fa-chevron-right forecast-icon" id="icon-po-{{ $poId }}"></i>
                                    </button>
                                </div>
                            </div>
                            
                           
                        </div>
                    </div>

                    {{-- Desktop View --}}
                    <div class="hidden sm:block">
                        <div class="p-4 sm:p-6">
                            {{-- Desktop Header --}}
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-file-invoice text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">{{ $po->no_po ?? 'N/A' }}</h3>
                                            <p class="text-sm text-gray-600 flex items-center">
                                                <i class="fas fa-building text-gray-400 mr-1"></i>
                                                {{ (optional($po->klien)->nama ?? 'N/A') . (optional($po->klien)->cabang ? ' - ' . optional($po->klien)->cabang : '') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500">Total Forecasts</p>
                                        <p class="text-lg font-bold text-yellow-600">{{ $forecasts->count() }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500">Total Perkiraan Harga</p>
                                        <p class="text-lg font-bold text-green-600">Rp {{ number_format($forecasts->sum('total_harga_forecast'), 0, ',', '.') }}</p>
                                    </div>
                                    <button type="button" class="toggle-forecast-btn bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center" onclick="toggleForecastList('po-{{ $poId }}')">
                                        <i class="fas fa-chevron-right mr-2 forecast-icon" id="icon-po-{{ $poId }}"></i>
                                        <span class="forecast-text" id="text-po-{{ $poId }}">Tampilkan</span>
                                    </button>
                                </div>
                            </div>

                            {{-- PO Info Grid --}}
                           
                        </div>
                    </div>

                    {{-- Forecasts List --}}
                    <div class="border-t border-gray-200 forecast-list" id="forecast-list-po-{{ $poId }}">
                        <div class="p-4 sm:p-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-chart-line text-yellow-600 mr-2"></i>
                                Daftar Forecast Pending
                            </h4>
                            
                            <div class="space-y-3">
                                @foreach($forecasts as $forecast)
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors duration-200" 
                                         data-forecast-no="{{ strtolower($forecast->no_forecast ?? '') }}"
                                         data-purchasing="{{ strtolower(optional($forecast->purchasing)->name ?? '') }}"
                                         data-qty="{{ $forecast->total_qty_forecast ?? 0 }}"
                                         data-amount="{{ $forecast->total_harga_forecast ?? 0 }}"
                                         data-hari-kirim="{{ $forecast->hari_kirim_forecast ?? 0 }}"
                                         data-date="{{ $forecast->tanggal_forecast ? $forecast->tanggal_forecast->format('Y-m-d') : '' }}"
                                         data-status="{{ $forecast->status ?? '' }}">
                                        {{-- Mobile Forecast Item --}}
                                        <div class="block sm:hidden">
                                            <div class="space-y-3">
                                                {{-- Mobile Header --}}
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <h5 class="text-sm font-bold text-gray-900">{{ $forecast->no_forecast }}</h5>
                                                        <p class="text-xs text-gray-600 mt-1 flex items-center">
                                                            <i class="fas fa-user text-gray-400 mr-1"></i>
                                                            {{ optional($forecast->purchasing)->nama ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                    @php
                                                        $statusClass = match($forecast->status ?? 'pending') {
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'sukses' => 'bg-green-100 text-green-800',
                                                            'gagal' => 'bg-red-100 text-red-800',
                                                            default => 'bg-gray-100 text-gray-800'
                                                        };
                                                    @endphp
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                                        {{ ucfirst($forecast->status ?? 'Pending') }}
                                                    </span>
                                                </div>
                                                
                                                {{-- Mobile Info Cards --}}
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div class="bg-blue-50 p-2 rounded">
                                                        <p class="text-xs text-blue-600 font-medium">Qty</p>
                                                        <p class="text-sm font-bold text-blue-700">{{ number_format($forecast->total_qty_forecast ?? 0, 2, ',', '.') }}</p>
                                                    </div>
                                                    <div class="bg-green-50 p-2 rounded">
                                                        <p class="text-xs text-green-600 font-medium">Total</p>
                                                        <p class="text-sm font-bold text-green-700">Rp {{ number_format($forecast->total_harga_forecast ?? 0, 0, ',', '.') }}</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div class="bg-orange-50 p-2 rounded">
                                                        <p class="text-xs text-orange-600 font-medium">Hari Kirim</p>
                                                        <p class="text-sm font-bold text-orange-700">Hari {{ $forecast->hari_kirim_forecast ?? 0 }}</p>
                                                    </div>
                                                    <div class="bg-gray-50 p-2 rounded">
                                                        <p class="text-xs text-gray-600 font-medium">Tanggal Kirim</p>
                                                        <p class="text-sm font-bold text-gray-700">{{ $forecast->tanggal_forecast ? $forecast->tanggal_forecast->format('d/m/Y') : 'N/A' }}</p>
                                                    </div>
                                                </div>
                                                
                                                {{-- Mobile Actions --}}
                                                <div class="flex space-x-2">
                                                    <button type="button" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium py-2 px-3 rounded-lg transition-colors duration-200">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        Detail
                                                    </button>
                                                    <button type="button" class="flex-1 bg-green-500 hover:bg-green-600 text-white text-xs font-medium py-2 px-3 rounded-lg transition-colors duration-200">
                                                        <i class="fas fa-truck mr-1"></i>
                                                        Kirim
                                                    </button>
                                                    <button type="button" class="flex-1 bg-red-500 hover:bg-red-600 text-white text-xs font-medium py-2 px-3 rounded-lg transition-colors duration-200">
                                                        <i class="fas fa-times mr-1"></i>
                                                        Batal
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            @if($forecast->catatan)
                                                <div class="mt-3 pt-3 border-t border-gray-200">
                                                    <p class="text-xs text-gray-600">
                                                        <i class="fas fa-sticky-note text-gray-400 mr-1"></i>
                                                        <strong>Catatan:</strong> {{ $forecast->catatan }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Desktop Forecast Item --}}
                                        <div class="hidden sm:block">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    {{-- Forecast Header --}}
                                                    <div class="flex items-center space-x-3 mb-4">
                                                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-chart-bar text-white text-sm"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="text-lg font-bold text-gray-900">{{ $forecast->no_forecast }}</h5>
                                                            <p class="text-sm text-gray-600 flex items-center">
                                                                <i class="fas fa-user text-gray-400 mr-2"></i>
                                                                {{ optional($forecast->purchasing)->nama ?? 'N/A' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Main Info Grid --}}
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                                                        <div class="bg-blue-50 p-3 rounded-lg">
                                                            <div class="flex items-center justify-between">
                                                                <div>
                                                                    <p class="text-xs text-blue-600 font-medium">Perkiraan Quantity</p>
                                                                    <p class="text-lg font-bold text-blue-700">{{ number_format($forecast->total_qty_forecast ?? 0, 2, ',', '.') }}</p>
                                                                </div>
                                                                <i class="fas fa-boxes text-blue-400 text-xl"></i>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="bg-green-50 p-3 rounded-lg">
                                                            <div class="flex items-center justify-between">
                                                                <div>
                                                                    <p class="text-xs text-green-600 font-medium">Perkiraan Harga</p>
                                                                    <p class="text-lg font-bold text-green-700">Rp {{ number_format($forecast->total_harga_forecast ?? 0, 0, ',', '.') }}</p>
                                                                </div>
                                                                <i class="fas fa-money-bill-wave text-green-400 text-xl"></i>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="bg-orange-50 p-3 rounded-lg">
                                                            <div class="flex items-center justify-between">
                                                                <div>
                                                                    <p class="text-xs text-orange-600 font-medium">Hari Kirim</p>
                                                                    <p class="text-lg font-bold text-orange-700">Hari {{ $forecast->hari_kirim_forecast ?? 0 }}</p>
                                                                </div>
                                                                <i class="fas fa-truck text-orange-400 text-xl"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Date Info --}}
                                                    <div class="bg-gray-50 p-3 rounded-lg">
                                                        <div class="flex items-center">
                                                            <i class="fas fa-calendar-alt text-gray-400 mr-3"></i>
                                                            <div>
                                                                <p class="text-xs text-gray-500 font-medium">Perkiraan Tanggal Kirim</p>
                                                                <p class="text-sm font-semibold text-gray-700">{{ $forecast->tanggal_forecast ? $forecast->tanggal_forecast->format('d/m/Y') : 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {{-- Action Buttons --}}
                                                <div class="flex flex-col space-y-2 ml-6">
                                                    <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center min-w-[140px] justify-center">
                                                        <i class="fas fa-eye mr-2"></i>
                                                        Detail
                                                    </button>
                                                    <button type="button" class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center min-w-[140px] justify-center">
                                                        <i class="fas fa-truck mr-2"></i>
                                                        Pengiriman
                                                    </button>
                                                    <button type="button" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center min-w-[140px] justify-center">
                                                        <i class="fas fa-times mr-2"></i>
                                                        Batalkan
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            @if($forecast->catatan)
                                                <div class="mt-3 pt-3 border-t border-gray-200">
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-sticky-note text-gray-400 mr-2"></i>
                                                        <strong>Catatan:</strong> {{ $forecast->catatan }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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
    </div>
</div>

<script>
// Debounced search function
let searchTimeoutPending;
function debounceSearchPending() {
    clearTimeout(searchTimeoutPending);
    searchTimeoutPending = setTimeout(() => {
        applyFiltersPending();
    }, 500);
}

// Apply filters function
function applyFiltersPending() {
    const searchValue = document.getElementById('searchInputPending').value.toLowerCase();
    const dateRange = document.getElementById('dateRangeFilter').value;
    const sortAmount = document.getElementById('sortAmountPending').value;
    const sortQty = document.getElementById('sortQtyPending').value;
    const sortDate = document.getElementById('sortDatePending').value;
    const sortHariKirim = document.getElementById('sortHariKirimPending').value;
    
    // Get all PO cards
    const poCards = document.querySelectorAll('.po-card');
    
    // Filter cards based on search
    poCards.forEach(card => {
        const noPo = card.getAttribute('data-no-po') || '';
        const klien = card.getAttribute('data-klien') || '';
        const forecastElements = card.querySelectorAll('[data-forecast-no]');
        
        let shouldShow = false;
        
        if (!searchValue) {
            shouldShow = true;
        } else {
            // Search in PO data
            if (noPo.includes(searchValue) || klien.includes(searchValue)) {
                shouldShow = true;
            }
            
            // Search in forecast data
            forecastElements.forEach(element => {
                const forecastNo = (element.getAttribute('data-forecast-no') || '').toLowerCase();
                const purchasing = (element.getAttribute('data-purchasing') || '').toLowerCase();
                if (forecastNo.includes(searchValue) || purchasing.includes(searchValue)) {
                    shouldShow = true;
                }
            });
        }
        
        if (shouldShow) {
            card.style.display = 'block';
            card.classList.add('filter-fade-in');
        } else {
            card.style.display = 'none';
            card.classList.remove('filter-fade-in');
        }
    });
    
    // Show active filters
    updateActiveFiltersPending();
    
    // Update result count
    updateResultCountPending();
    
    // For server-side filtering (if needed)
    // Uncomment the following lines to enable server-side filtering
    /*
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'pending';
    
    const params = new URLSearchParams();
    params.append('tab', currentTab);
    
    if (searchValue) params.append('search', searchValue);
    if (dateRange) params.append('date_range', dateRange);
    if (sortAmount) params.append('sort_amount', sortAmount);
    if (sortQty) params.append('sort_qty', sortQty);
    if (sortDate) params.append('sort_date', sortDate);
    
    window.location.href = window.location.pathname + '?' + params.toString();
    */
}

// Update result count
function updateResultCountPending() {
    const visibleCards = document.querySelectorAll('.po-card[style*="display: block"], .po-card:not([style*="display: none"])').length;
    const totalCards = document.querySelectorAll('.po-card').length;
    
    // Find or create result count element
    let resultCountElement = document.getElementById('result-count-pending');
    if (!resultCountElement) {
        resultCountElement = document.createElement('div');
        resultCountElement.id = 'result-count-pending';
        resultCountElement.className = 'text-sm text-gray-600 mb-4';
        
        const headerSection = document.querySelector('h2');
        if (headerSection && headerSection.parentNode) {
            headerSection.parentNode.appendChild(resultCountElement);
        }
    }
    
    // Result count display removed
    resultCountElement.style.display = 'none';
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
        const dateLabels = {
            'today': 'Hari Ini',
            'week': 'Minggu Ini', 
            'month': 'Bulan Ini'
        };
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Tanggal: ${dateLabels[dateRange]}</span>`;
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
        const hariKirimLabels = {
            'shortest': 'Tercepat',
            'longest': 'Terlama'
        };
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Kirim: ${hariKirimLabels[sortHariKirim]}</span>`;
        hasActiveFilters = true;
    }
    
    if (hasActiveFilters) {
        activeFiltersContainer.innerHTML = filtersHTML;
        activeFiltersContainer.style.display = 'flex';
    } else {
        activeFiltersContainer.style.display = 'none';
    }
}

// Clear all filters
function clearAllFiltersPending() {
    document.getElementById('searchInputPending').value = '';
    document.getElementById('dateRangeFilter').value = '';
    document.getElementById('sortAmountPending').value = '';
    document.getElementById('sortQtyPending').value = '';
    document.getElementById('sortDatePending').value = '';
    document.getElementById('sortHariKirimPending').value = '';
    
    // Preserve current tab
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'pending';
    
    window.location.href = window.location.pathname + '?tab=' + currentTab;
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
    updateActiveFiltersPending();
    updateResultCountPending();
    
    // Initialize all forecast lists as hidden on page load
    const forecastLists = document.querySelectorAll('.forecast-list');
    forecastLists.forEach(list => {
        list.style.display = 'none';
    });
    
    // Add clear filters button if there are active filters
    const activeFiltersContainer = document.getElementById('activeFiltersPending');
    const hasFilters = activeFiltersContainer && activeFiltersContainer.style.display !== 'none';
    
    if (hasFilters) {
        const clearButton = document.createElement('button');
        clearButton.innerHTML = '<i class="fas fa-times mr-1"></i>Hapus Semua Filter';
        clearButton.className = 'px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs hover:bg-red-200 transition-colors duration-200 active-filter-tag';
        clearButton.onclick = clearAllFiltersPending;
        activeFiltersContainer.appendChild(clearButton);
    }
    
    // Add event listeners for real-time filtering
    const searchInput = document.getElementById('searchInputPending');
    if (searchInput) {
        searchInput.addEventListener('input', debounceSearchPending);
    }
    
    // Add smooth scroll to results when filtering
    const filterElements = document.querySelectorAll('#dateRangeFilter, #sortAmountPending, #sortQtyPending, #sortDatePending, #sortHariKirimPending');
    filterElements.forEach(element => {
        element.addEventListener('change', () => {
            applyFiltersPending();
            // Smooth scroll to results
            const resultsSection = document.querySelector('.space-y-1.sm\\:space-y-4');
            if (resultsSection) {
                resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
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
