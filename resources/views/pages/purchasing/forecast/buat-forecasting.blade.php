{{-- Tab Buat Forecasting --}}
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
                        Pencarian Purchase Order
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="searchInput" 
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari No. PO atau nama klien..." 
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
                    {{-- Status Filter --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                            <i class="fas fa-flag mr-1 sm:mr-2 text-green-500 text-xs"></i>
                            Filter Status
                        </label>
                        <select id="statusFilter" name="status" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFilters()">
                            <option value="">Semua Status</option>
                            <option value="siap" {{ request('status') == 'siap' ? 'selected' : '' }}>Siap</option>
                            <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>Proses</option>
                        </select>
                    </div>

                    {{-- Sort by Amount --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-green-500 text-xs"></i>
                            Urutkan Total
                        </label>
                        <select id="sortAmount" name="sort_amount" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFilters()">
                            <option value="">Default</option>
                            <option value="highest" {{ request('sort_amount') == 'highest' ? 'selected' : '' }}>Tertinggi</option>
                            <option value="lowest" {{ request('sort_amount') == 'lowest' ? 'selected' : '' }}>Terendah</option>
                        </select>
                    </div>

                    {{-- Sort by Items --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-green-500 text-xs"></i>
                            Urutkan Items
                        </label>
                        <select id="sortItems" name="sort_items" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFilters()">
                            <option value="">Default</option>
                            <option value="most" {{ request('sort_items') == 'most' ? 'selected' : '' }}>Terbanyak</option>
                            <option value="least" {{ request('sort_items') == 'least' ? 'selected' : '' }}>Tersedikit</option>
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

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-500 rounded-full flex items-center justify-center mr-2 sm:mr-3">
                <i class="fas fa-list text-white text-xs sm:text-sm"></i>
            </div>
            Daftar Purchase Order
        </h2>
    </div>

    {{-- PO Cards List --}}
    <div class="space-y-1 sm:space-y-4">
        @if(isset($purchaseOrders) && $purchaseOrders->count() > 0)
            @foreach($purchaseOrders as $po)
                {{-- Mobile: Simple List Item / Desktop: Full Card --}}
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-gray-300 border-l-4 border-l-green-500 hover:border-l-green-600 po-card" 
                     data-no-po="{{ strtolower($po->no_po ?? '') }}" 
                     data-klien="{{ strtolower(($po->klien->nama ?? '') . ($po->klien->cabang ? ' - ' . $po->klien->cabang : '')) }}" 
                     data-status="{{ $po->status ?? '' }}">
                    
                    {{-- Mobile List View --}}
                    <div class="block sm:hidden">
                        <div class="p-3 border-b border-gray-100">
                            {{-- Mobile Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="text-sm font-bold text-gray-900">{{ $po->no_po ?? 'N/A' }}</h3>
                                    <p class="text-xs text-green-600 mt-1">{{ ($po->klien->nama ?? 'N/A') . ($po->klien->cabang ? ' - ' . $po->klien->cabang : '') }}</p>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($po->status ?? '') === 'siap' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($po->status ?? 'Unknown') }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Mobile Stats --}}
                            <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                                <div class="flex items-center space-x-3">
                                    <span class="font-bold text-blue-600">{{ $po->purchaseOrderBahanBakus->count() }}</span>
                                    <span class="text-blue-600">Items</span>
                                    <span class="mx-1 text-gray-300">|</span>
                                    <span class="font-bold text-green-600">{{ number_format($po->qty_total ?? 0, 2, ',', '.') }}</span>
                                    <span class="text-green-600">Qty</span>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-yellow-600">Rp {{ number_format($po->total_amount ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            
                            {{-- Mobile Date --}}
                            <div class="flex items-center text-xs text-gray-500">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <span>{{ $po->created_at ? $po->created_at->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                        </div>
                        
                        {{-- Mobile Actions --}}
                        <div class="p-3 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <button type="button" 
                                        onclick="togglePODetails({{ $po->id }})"
                                        class="flex items-center px-3 py-2 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded-lg transition-all duration-200">
                                    <i class="fas fa-list mr-1"></i>
                                    <span>Detail Bahan</span>
                                    <i class="fas fa-chevron-down transform transition-transform ml-2 text-xs" id="chevron-{{ $po->id }}"></i>
                                </button>
                                
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500">{{ $po->updated_at ? $po->updated_at->format('d/m/Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Desktop Card View --}}
                    <div class="hidden sm:block">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                {{-- Left Section: PO Info --}}
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-xl font-bold text-gray-900">{{ $po->no_po ?? 'N/A' }}</h3>
                                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ ($po->status ?? '') === 'siap' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($po->status ?? 'Unknown') }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <div class="flex items-center mb-1">
                                            <i class="fas fa-user w-4 text-green-500 mr-2"></i>
                                            <span>{{ ($po->klien->nama ?? 'N/A') . ($po->klien->cabang ? ' - ' . $po->klien->cabang : '') }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar-alt w-4 text-green-500 mr-2"></i>
                                            <span>{{ $po->created_at ? $po->created_at->format('d F Y') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Right Section: Stats --}}
                                <div class="flex items-center space-x-6">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600">{{ $po->purchaseOrderBahanBakus->count() }}</div>
                                        <div class="text-xs text-gray-500 uppercase tracking-wider">Items</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600">{{ number_format($po->qty_total ?? 0, 2, ',', '.') }}</div>
                                        <div class="text-xs text-gray-500 uppercase tracking-wider">Total Kuantitas</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-yellow-600">Rp {{ number_format($po->total_amount ?? 0, 0, ',', '.') }}</div>
                                        <div class="text-xs text-gray-500 uppercase tracking-wider">Total Harga</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Specification Information --}}
                            @if($po->catatan)
                                <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-gray-400 mr-2 mt-0.5"></i>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 mb-1">Catatan:</p>
                                            <p class="text-sm text-gray-600">{{ $po->catatan }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Bottom Section --}}
                            <div class="flex items-center justify-between pt-4 border-t-2 border-green-100">
                                {{-- Product Dropdown Button --}}
                                <button type="button" 
                                        onclick="togglePODetails({{ $po->id }})"
                                        class="flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg font-semibold text-sm">
                                    <i class="fas fa-list mr-2"></i>
                                    <span>Lihat Detail Bahan Baku ({{ $po->purchaseOrderBahanBakus->count() }})</span>
                                    <i class="fas fa-chevron-down transform transition-transform ml-2" id="chevron-desktop-{{ $po->id }}"></i>
                                </button>

                                {{-- Action Info --}}
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        Diperbarui {{ $po->updated_at ? $po->updated_at->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Bahan Baku List (Hidden by default) --}}
                    <div id="detail-list-{{ $po->id }}" class="hidden border-t-2 border-green-100 bg-green-50">
                        {{-- Mobile Detail List --}}
                        <div class="block sm:hidden p-3">
                            <h4 class="text-sm font-bold text-green-800 mb-2">Detail Bahan Baku</h4>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                @forelse($po->purchaseOrderBahanBakus as $detail)
                                    <div class="bg-white rounded-lg p-3 border border-green-200">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h6 class="text-xs font-bold text-gray-900">{{ $detail->bahanBakuKlien->nama ?? 'N/A' }}</h6>
                                                <p class="text-xs text-gray-600 mt-1">
                                                    {{ number_format($detail->jumlah ?? 0, 2, ',', '.') }} {{ $detail->bahanBakuKlien->satuan ?? '' }} • 
                                                    Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                                </p>
                                            </div>
                                            <button type="button" 
                                                    onclick="openForecastModal({{ $detail->id }}, '{{ $detail->bahanBakuKlien->nama ?? 'N/A' }}', {{ $detail->jumlah ?? 0 }}, {{ $po->id }}, '{{ $po->no_po ?? 'N/A' }}')"
                                                    class="ml-2 px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                                Forecast
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-gray-500">
                                        <p class="text-xs">Tidak ada detail bahan baku</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Desktop Detail List --}}
                        <div class="hidden sm:block p-6">
                            <h4 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                                <i class="fas fa-list-ul mr-2"></i>
                                Detail Bahan Baku & Harga
                            </h4>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @forelse($po->purchaseOrderBahanBakus as $detail)
                                    <div class="bg-white rounded-lg p-4 border border-green-200 hover:border-green-300 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-4">
                                                    <div class="flex-1">
                                                        <h6 class="font-semibold text-gray-900 mb-1">{{ $detail->bahanBakuKlien->nama ?? 'N/A' }}</h6>
                                                        @if($detail->bahanBakuKlien->spesifikasi ?? false)
                                                            <p class="text-xs text-gray-500 mb-2">{{ $detail->bahanBakuKlien->spesifikasi }}</p>
                                                        @endif
                                                        <div class="flex items-center space-x-4 text-sm">
                                                            <span class="flex items-center text-gray-600">
                                                                <i class="fas fa-weight text-blue-500 mr-1"></i>
                                                                <strong>{{ number_format($detail->jumlah ?? 0, 2, ',', '.') }}</strong> {{ $detail->bahanBakuKlien->satuan ?? '' }}
                                                            </span>
                                                            <span class="flex items-center text-gray-600">
                                                                <i class="fas fa-tag text-green-500 mr-1"></i>
                                                                <strong>Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</strong>/{{ $detail->bahanBakuKlien->satuan ?? 'unit' }}
                                                            </span>
                                                            <span class="flex items-center text-gray-600">
                                                                <i class="fas fa-calculator text-yellow-500 mr-1"></i>
                                                                <strong>Rp {{ number_format($detail->total_harga ?? 0, 0, ',', '.') }}</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button type="button" 
                                                        onclick="openForecastModal({{ $detail->id }}, '{{ $detail->bahanBakuKlien->nama ?? 'N/A' }}', {{ $detail->jumlah ?? 0 }}, {{ $po->id }}, '{{ $po->no_po ?? 'N/A' }}')"
                                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg font-semibold text-sm">
                                                    <i class="fas fa-chart-bar mr-2"></i>
                                                    Buat Forecast
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-box-open text-gray-300 text-3xl mb-3"></i>
                                        <p class="text-gray-500">Tidak ada detail bahan baku</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-12">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Purchase Order</h3>
                <p class="text-gray-500">
                    @if(request('search'))
                        Tidak ditemukan Purchase Order dengan kata kunci "{{ request('search') }}"
                    @else
                        Belum ada Purchase Order yang tersedia untuk dibuatkan forecast.
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if(isset($purchaseOrders) && $purchaseOrders->hasPages())
        <div class="bg-white rounded-lg shadow-sm border p-4 mt-6">
            <div class="flex flex-col sm:flex-row items-center justify-between">
                {{-- Results Info --}}
                <div class="mb-3 sm:mb-0">
                    <p class="text-sm text-gray-700">
                        Menampilkan
                        <span class="font-medium">{{ $purchaseOrders->firstItem() }}</span>
                        sampai
                        <span class="font-medium">{{ $purchaseOrders->lastItem() }}</span>
                        dari
                        <span class="font-medium">{{ $purchaseOrders->total() }}</span>
                        Purchase Order
                    </p>
                </div>

                {{-- Pagination Links --}}
                <div class="flex items-center space-x-2">
                    {{-- Previous Page --}}
                    @if ($purchaseOrders->onFirstPage())
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i>
                            Sebelumnya
                        </span>
                    @else
                        @php
                            $prevUrl = $purchaseOrders->previousPageUrl();
                            $prevUrlParts = parse_url($prevUrl);
                            parse_str($prevUrlParts['query'] ?? '', $prevParams);
                            $prevParams['tab'] = request('tab', 'buat-forecasting');
                            $prevUrl = $prevUrlParts['path'] . '?' . http_build_query($prevParams);
                        @endphp
                        <a href="{{ $prevUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i>
                            Sebelumnya
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @if($purchaseOrders->lastPage() > 1)
                        <div class="hidden sm:flex items-center space-x-1">
                            @foreach ($purchaseOrders->getUrlRange(1, $purchaseOrders->lastPage()) as $page => $url)
                                @if ($page == $purchaseOrders->currentPage())
                                    <span class="px-3 py-2 text-sm font-medium text-white bg-green-600 border border-green-600 rounded-lg">
                                        {{ $page }}
                                    </span>
                                @else
                                    @php
                                        $urlParts = parse_url($url);
                                        parse_str($urlParts['query'] ?? '', $urlParams);
                                        $urlParams['tab'] = request('tab', 'buat-forecasting');
                                        $pageUrl = $urlParts['path'] . '?' . http_build_query($urlParams);
                                    @endphp
                                    <a href="{{ $pageUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Mobile Page Indicator --}}
                        <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                            {{ $purchaseOrders->currentPage() }} / {{ $purchaseOrders->lastPage() }}
                        </div>
                    @endif

                    {{-- Next Page --}}
                    @if ($purchaseOrders->hasMorePages())
                        @php
                            $nextUrl = $purchaseOrders->nextPageUrl();
                            $nextUrlParts = parse_url($nextUrl);
                            parse_str($nextUrlParts['query'] ?? '', $nextParams);
                            $nextParams['tab'] = request('tab', 'buat-forecasting');
                            $nextUrl = $nextUrlParts['path'] . '?' . http_build_query($nextParams);
                        @endphp
                        <a href="{{ $nextUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
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
    </div>
</div>

{{-- Modal Buat Forecast --}}
<div id="forecastModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <form id="forecastForm">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 sm:mt-0 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                                Buat Forecast Bahan Baku
                            </h3>
                            
                            {{-- Info PO dan Bahan Baku --}}
                            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Purchase Order</label>
                                        <p class="text-lg font-semibold text-gray-900" id="modalPONumber"></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Bahan Baku</label>
                                        <p class="text-lg font-semibold text-gray-900" id="modalBahanBaku"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Fields --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="tanggal_forecast" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Forecast <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" id="tanggal_forecast" name="tanggal_forecast" required
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label for="hari_kirim_forecast" class="block text-sm font-medium text-gray-700 mb-2">
                                        Hari Kirim (hari) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="hari_kirim_forecast" name="hari_kirim_forecast" required min="1"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>
                            </div>

                            {{-- Pilih Supplier --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Pilih Supplier <span class="text-red-500">*</span>
                                </label>
                                <div id="supplierOptions" class="space-y-3">
                                    {{-- Supplier options akan diisi via JavaScript --}}
                                </div>
                            </div>

                            {{-- Quantity dan Harga --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="qty_forecast" class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantity Forecast <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" id="qty_forecast" name="qty_forecast" required min="0.01" step="0.01"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-16 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <span class="text-gray-500 text-sm" id="satuanBahanBaku"></span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Jumlah di PO: <span id="jumlahPO" class="font-medium"></span>
                                    </p>
                                </div>
                                <div>
                                    <label for="harga_satuan_forecast" class="block text-sm font-medium text-gray-700 mb-2">
                                        Harga Satuan <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">Rp</span>
                                        <input type="number" id="harga_satuan_forecast" name="harga_satuan_forecast" required min="0.01" step="0.01"
                                               class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                               oninput="calculateTotal()">
                                    </div>
                                </div>
                            </div>

                            {{-- Total Harga --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga</label>
                                <div class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-2">
                                    <span class="text-lg font-semibold text-gray-900" id="totalHarga">Rp 0</span>
                                </div>
                            </div>

                            {{-- Catatan --}}
                            <div class="mb-6">
                                <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan
                                </label>
                                <textarea id="catatan" name="catatan" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                          placeholder="Tambahkan catatan untuk forecast ini..."></textarea>
                            </div>

                            {{-- Hidden Fields --}}
                            <input type="hidden" id="purchase_order_id" name="purchase_order_id">
                            <input type="hidden" id="purchase_order_bahan_baku_id" name="purchase_order_bahan_baku_id">
                            <input type="hidden" id="bahan_baku_supplier_id" name="bahan_baku_supplier_id">
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="submitBtn"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-save mr-2"></i>
                        Buat Forecast
                    </button>
                    <button type="button" onclick="closeForecastModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
let currentPurchaseOrderBahanBakuId = null;
let currentPurchaseOrderId = null;
let searchTimeout;

// Fungsi untuk format rupiah Indonesia (titik untuk ribuan)
function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Format angka dengan decimal Indonesia (koma untuk decimal, titik untuk ribuan)
function formatNumber(num, decimals = 2) {
    const parts = num.toFixed(decimals).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join(',');
}

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
    const status = document.getElementById('statusFilter').value;
    const sortAmount = document.getElementById('sortAmount').value;
    const sortItems = document.getElementById('sortItems').value;

    // Build URL with parameters
    const params = new URLSearchParams();
    
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (sortAmount) params.append('sort_amount', sortAmount);
    if (sortItems) params.append('sort_items', sortItems);
    
    // Always preserve the current tab
    const currentTab = new URLSearchParams(window.location.search).get('tab') || 'buat-forecasting';
    params.append('tab', currentTab);

    // Show active filters
    showActiveFilters(search, status, sortAmount, sortItems);

    // Redirect with new parameters
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

// Show active filters
function showActiveFilters(search, status, sortAmount, sortItems) {
    const activeFiltersDiv = document.getElementById('activeFilters');
    const hasFilters = search || status || sortAmount || sortItems;

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

    if (status) {
        filtersHtml += `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-flag mr-1"></i>
            ${status.charAt(0).toUpperCase() + status.slice(1)}
        </span>`;
    }

    if (sortAmount) {
        const sortText = sortAmount === 'highest' ? 'Total Tertinggi' : 'Total Terendah';
        filtersHtml += `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
            <i class="fas fa-sort mr-1"></i>
            ${sortText}
        </span>`;
    }

    if (sortItems) {
        const sortText = sortItems === 'most' ? 'Items Terbanyak' : 'Items Tersedikit';
        filtersHtml += `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
            <i class="fas fa-sort mr-1"></i>
            ${sortText}
        </span>`;
    }

    activeFiltersDiv.innerHTML = filtersHtml;
    activeFiltersDiv.style.display = 'flex';
}

// Toggle PO Details
function togglePODetails(poId) {
    const detailsDiv = document.getElementById('detail-list-' + poId);
    const chevronMobile = document.getElementById('chevron-' + poId);
    const chevronDesktop = document.getElementById('chevron-desktop-' + poId);
    
    if (detailsDiv.classList.contains('hidden')) {
        // Show details
        detailsDiv.classList.remove('hidden');
        detailsDiv.classList.add('animate-fadeIn');
        if (chevronMobile) chevronMobile.classList.add('rotate-180');
        if (chevronDesktop) chevronDesktop.classList.add('rotate-180');
    } else {
        // Hide details
        detailsDiv.classList.add('hidden');
        detailsDiv.classList.remove('animate-fadeIn');
        if (chevronMobile) chevronMobile.classList.remove('rotate-180');
        if (chevronDesktop) chevronDesktop.classList.remove('rotate-180');
    }
}

// Initialize active filters on page load
document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const sortAmount = document.getElementById('sortAmount').value;
    const sortItems = document.getElementById('sortItems').value;

    showActiveFilters(search, status, sortAmount, sortItems);
});

// Open forecast modal
async function openForecastModal(purchaseOrderBahanBakuId, bahanBakuNama, jumlah, purchaseOrderId, noPO) {
    currentPurchaseOrderBahanBakuId = purchaseOrderBahanBakuId;
    currentPurchaseOrderId = purchaseOrderId;
    
    // Set info
    document.getElementById('modalPONumber').textContent = noPO;
    document.getElementById('modalBahanBaku').textContent = bahanBakuNama;
    document.getElementById('jumlahPO').textContent = jumlah;
    document.getElementById('purchase_order_id').value = purchaseOrderId;
    document.getElementById('purchase_order_bahan_baku_id').value = purchaseOrderBahanBakuId;
    
    // Reset form
    document.getElementById('forecastForm').reset();
    document.getElementById('purchase_order_id').value = purchaseOrderId;
    document.getElementById('purchase_order_bahan_baku_id').value = purchaseOrderBahanBakuId;
    
    // Set default tanggal forecast ke hari ini
    document.getElementById('tanggal_forecast').value = new Date().toISOString().split('T')[0];
    
    try {
        // Load supplier options
        const response = await fetch(`/forecasting/bahan-baku-suppliers/${purchaseOrderBahanBakuId}`);
        const data = await response.json();
        
        if (data.error) {
            alert('Gagal memuat data supplier: ' + data.error);
            return;
        }
        
        // Populate supplier options
        const supplierOptions = document.getElementById('supplierOptions');
        supplierOptions.innerHTML = '';
        
        if (data.bahan_baku_suppliers.length === 0) {
            supplierOptions.innerHTML = `
                <div class="text-center py-4 text-red-500">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Tidak ada supplier yang menyediakan bahan baku ini
                </div>
            `;
        } else {
            data.bahan_baku_suppliers.forEach(supplier => {
                const latestPrice = supplier.riwayat_harga.length > 0 ? supplier.riwayat_harga[0].harga : 0;
                
                supplierOptions.innerHTML += `
                    <div class="border border-gray-300 rounded-lg p-4 cursor-pointer hover:border-green-500 supplier-option" 
                         data-supplier-id="${supplier.id}" data-price="${latestPrice}">
                        <div class="flex items-center">
                            <input type="radio" id="supplier_${supplier.id}" name="supplier_option" value="${supplier.id}"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                            <label for="supplier_${supplier.id}" class="ml-3 flex-1 cursor-pointer">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900">${supplier.supplier.nama}</p>
                                        <p class="text-sm text-gray-600">${supplier.nama}</p>
                                        <p class="text-xs text-gray-500">Satuan: ${supplier.satuan}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-green-600">Rp ${parseInt(latestPrice).toLocaleString('id-ID')}</p>
                                        <p class="text-xs text-gray-500">Harga terakhir</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                `;
            });
            
            // Add click handlers for supplier options
            document.querySelectorAll('.supplier-option').forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    
                    // Update form
                    document.getElementById('bahan_baku_supplier_id').value = this.dataset.supplierId;
                    document.getElementById('harga_satuan_forecast').value = this.dataset.price;
                    
                    // Update UI
                    document.querySelectorAll('.supplier-option').forEach(opt => {
                        opt.classList.remove('border-green-500', 'bg-green-50');
                    });
                    this.classList.add('border-green-500', 'bg-green-50');
                    
                    calculateTotal();
                });
            });
        }
        
        // Set satuan
        if (data.purchase_order_bahan_baku && data.purchase_order_bahan_baku.bahan_baku_klien) {
            document.getElementById('satuanBahanBaku').textContent = data.purchase_order_bahan_baku.bahan_baku_klien.satuan || '';
        }
        
    } catch (error) {
        console.error('Error loading supplier data:', error);
        alert('Gagal memuat data supplier');
        return;
    }
    
    // Show modal
    document.getElementById('forecastModal').classList.remove('hidden');
}

// Close forecast modal
function closeForecastModal() {
    document.getElementById('forecastModal').classList.add('hidden');
    document.getElementById('forecastForm').reset();
    currentPurchaseOrderBahanBakuId = null;
    currentPurchaseOrderId = null;
}

// Calculate total
function calculateTotal() {
    const qty = parseFloat(document.getElementById('qty_forecast').value) || 0;
    const harga = parseFloat(document.getElementById('harga_satuan_forecast').value) || 0;
    const total = qty * harga;
    
    document.getElementById('totalHarga').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
}

// Submit forecast form
document.getElementById('forecastForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Check if supplier is selected
    const selectedSupplier = document.querySelector('input[name="supplier_option"]:checked');
    if (!selectedSupplier) {
        alert('Silakan pilih supplier terlebih dahulu');
        return;
    }
    
    // Set supplier ID
    document.getElementById('bahan_baku_supplier_id').value = selectedSupplier.value;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    
    try {
        const formData = new FormData(this);
        
        // Prepare data for API
        const data = {
            purchase_order_id: formData.get('purchase_order_id'),
            tanggal_forecast: formData.get('tanggal_forecast'),
            hari_kirim_forecast: formData.get('hari_kirim_forecast'),
            catatan: formData.get('catatan'),
            details: [{
                purchase_order_bahan_baku_id: formData.get('purchase_order_bahan_baku_id'),
                bahan_baku_supplier_id: formData.get('bahan_baku_supplier_id'),
                qty_forecast: formData.get('qty_forecast'),
                harga_satuan_forecast: formData.get('harga_satuan_forecast'),
                catatan_detail: null
            }]
        };
        
        const response = await fetch('/forecasting/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Forecast berhasil dibuat!');
            closeForecastModal();
            location.reload(); // Refresh page to show updated data
        } else {
            alert('Gagal membuat forecast: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error creating forecast:', error);
        alert('Terjadi kesalahan saat membuat forecast');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Close modal when clicking outside
document.getElementById('forecastModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeForecastModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeForecastModal();
    }
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

{{-- Modal Buat Forecast --}}
<div id="forecastModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <form id="forecastForm">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 sm:mt-0 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                                Buat Forecast Bahan Baku
                            </h3>
                            
                            {{-- Info PO dan Bahan Baku --}}
                            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Purchase Order</label>
                                        <p class="text-lg font-semibold text-gray-900" id="modalPONumber"></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Bahan Baku</label>
                                        <p class="text-lg font-semibold text-gray-900" id="modalBahanBaku"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Fields --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="tanggal_forecast" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Forecast <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" id="tanggal_forecast" name="tanggal_forecast" required
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label for="hari_kirim_forecast" class="block text-sm font-medium text-gray-700 mb-2">
                                        Hari Kirim (hari) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="hari_kirim_forecast" name="hari_kirim_forecast" required min="1"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>
                            </div>

                            {{-- Pilih Supplier --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Pilih Supplier <span class="text-red-500">*</span>
                                </label>
                                <div id="supplierOptions" class="space-y-3">
                                    {{-- Supplier options akan diisi via JavaScript --}}
                                </div>
                            </div>

                            {{-- Quantity dan Harga --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="qty_forecast" class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantity Forecast <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" id="qty_forecast" name="qty_forecast" required min="0.01" step="0.01"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-16 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <span class="text-gray-500 text-sm" id="satuanBahanBaku"></span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Jumlah di PO: <span id="jumlahPO" class="font-medium"></span>
                                    </p>
                                </div>
                                <div>
                                    <label for="harga_satuan_forecast" class="block text-sm font-medium text-gray-700 mb-2">
                                        Harga Satuan <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">Rp</span>
                                        <input type="number" id="harga_satuan_forecast" name="harga_satuan_forecast" required min="0.01" step="0.01"
                                               class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                               oninput="calculateTotal()">
                                    </div>
                                </div>
                            </div>

                            {{-- Total Harga --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga</label>
                                <div class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-2">
                                    <span class="text-lg font-semibold text-gray-900" id="totalHarga">Rp 0</span>
                                </div>
                            </div>

                            {{-- Catatan --}}
                            <div class="mb-6">
                                <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan
                                </label>
                                <textarea id="catatan" name="catatan" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                          placeholder="Tambahkan catatan untuk forecast ini..."></textarea>
                            </div>

                            {{-- Hidden Fields --}}
                            <input type="hidden" id="purchase_order_id" name="purchase_order_id">
                            <input type="hidden" id="purchase_order_bahan_baku_id" name="purchase_order_bahan_baku_id">
                            <input type="hidden" id="bahan_baku_supplier_id" name="bahan_baku_supplier_id">
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="submitBtn"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-save mr-2"></i>
                        Buat Forecast
                    </button>
                    <button type="button" onclick="closeForecastModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript Functions --}}
<script>
// Forecast modal functions
let currentPurchaseOrderBahanBakuId = null;
let currentPurchaseOrderId = null;

// Open forecast modal
async function openForecastModal(purchaseOrderBahanBakuId, bahanBakuNama, jumlah, purchaseOrderId, noPO) {
    currentPurchaseOrderBahanBakuId = purchaseOrderBahanBakuId;
    currentPurchaseOrderId = purchaseOrderId;
    
    // Set info
    document.getElementById('modalPONumber').textContent = noPO;
    document.getElementById('modalBahanBaku').textContent = bahanBakuNama;
    document.getElementById('jumlahPO').textContent = jumlah;
    document.getElementById('purchase_order_id').value = purchaseOrderId;
    document.getElementById('purchase_order_bahan_baku_id').value = purchaseOrderBahanBakuId;
    
    // Reset form
    document.getElementById('forecastForm').reset();
    document.getElementById('purchase_order_id').value = purchaseOrderId;
    document.getElementById('purchase_order_bahan_baku_id').value = purchaseOrderBahanBakuId;
    
    // Set default tanggal forecast ke hari ini
    document.getElementById('tanggal_forecast').value = new Date().toISOString().split('T')[0];
    
    try {
        // Load supplier options
        const response = await fetch(`/forecasting/bahan-baku-suppliers/${purchaseOrderBahanBakuId}`);
        const data = await response.json();
        
        if (data.error) {
            alert('Gagal memuat data supplier: ' + data.error);
            return;
        }
        
        // Populate supplier options
        const supplierOptions = document.getElementById('supplierOptions');
        supplierOptions.innerHTML = '';
        
        if (data.bahan_baku_suppliers.length === 0) {
            supplierOptions.innerHTML = `
                <div class="text-center py-4 text-red-500">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Tidak ada supplier yang menyediakan bahan baku ini
                </div>
            `;
        } else {
            data.bahan_baku_suppliers.forEach(supplier => {
                const latestPrice = supplier.riwayat_harga.length > 0 ? supplier.riwayat_harga[0].harga : 0;
                
                supplierOptions.innerHTML += `
                    <div class="border border-gray-300 rounded-lg p-4 cursor-pointer hover:border-green-500 supplier-option" 
                         data-supplier-id="${supplier.id}" data-price="${latestPrice}">
                        <div class="flex items-center">
                            <input type="radio" id="supplier_${supplier.id}" name="supplier_option" value="${supplier.id}"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                            <label for="supplier_${supplier.id}" class="ml-3 flex-1 cursor-pointer">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900">${supplier.supplier.nama}</p>
                                        <p class="text-sm text-gray-600">${supplier.nama}</p>
                                        <p class="text-xs text-gray-500">Satuan: ${supplier.satuan}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-green-600">Rp ${parseInt(latestPrice).toLocaleString('id-ID')}</p>
                                        <p class="text-xs text-gray-500">Harga terakhir</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                `;
            });
            
            // Add click handlers for supplier options
            document.querySelectorAll('.supplier-option').forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    
                    // Update form
                    document.getElementById('bahan_baku_supplier_id').value = this.dataset.supplierId;
                    document.getElementById('harga_satuan_forecast').value = this.dataset.price;
                    
                    // Update UI
                    document.querySelectorAll('.supplier-option').forEach(opt => {
                        opt.classList.remove('border-green-500', 'bg-green-50');
                    });
                    this.classList.add('border-green-500', 'bg-green-50');
                    
                    calculateTotal();
                });
            });
        }
        
        // Set satuan
        if (data.purchase_order_bahan_baku && data.purchase_order_bahan_baku.bahan_baku_klien) {
            document.getElementById('satuanBahanBaku').textContent = data.purchase_order_bahan_baku.bahan_baku_klien.satuan || '';
        }
        
    } catch (error) {
        console.error('Error loading supplier data:', error);
        alert('Gagal memuat data supplier');
        return;
    }
    
    // Show modal
    document.getElementById('forecastModal').classList.remove('hidden');
}

// Close forecast modal
function closeForecastModal() {
    document.getElementById('forecastModal').classList.add('hidden');
    document.getElementById('forecastForm').reset();
    currentPurchaseOrderBahanBakuId = null;
    currentPurchaseOrderId = null;
}

// Calculate total
function calculateTotal() {
    const qty = parseFloat(document.getElementById('qty_forecast').value) || 0;
    const harga = parseFloat(document.getElementById('harga_satuan_forecast').value) || 0;
    const total = qty * harga;
    
    document.getElementById('totalHarga').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
}

// Submit forecast form
document.getElementById('forecastForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Check if supplier is selected
    const selectedSupplier = document.querySelector('input[name="supplier_option"]:checked');
    if (!selectedSupplier) {
        alert('Silakan pilih supplier terlebih dahulu');
        return;
    }
    
    // Set supplier ID
    document.getElementById('bahan_baku_supplier_id').value = selectedSupplier.value;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    
    try {
        const formData = new FormData(this);
        
        // Prepare data for API
        const data = {
            purchase_order_id: formData.get('purchase_order_id'),
            tanggal_forecast: formData.get('tanggal_forecast'),
            hari_kirim_forecast: formData.get('hari_kirim_forecast'),
            catatan: formData.get('catatan'),
            details: [{
                purchase_order_bahan_baku_id: formData.get('purchase_order_bahan_baku_id'),
                bahan_baku_supplier_id: formData.get('bahan_baku_supplier_id'),
                qty_forecast: formData.get('qty_forecast'),
                harga_satuan_forecast: formData.get('harga_satuan_forecast'),
                catatan_detail: null
            }]
        };
        
        const response = await fetch('/forecasting/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Forecast berhasil dibuat!');
            closeForecastModal();
            location.reload(); // Refresh page to show updated data
        } else {
            alert('Gagal membuat forecast: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error creating forecast:', error);
        alert('Terjadi kesalahan saat membuat forecast');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Close modal when clicking outside
document.getElementById('forecastModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeForecastModal();
    }
});
</script>
