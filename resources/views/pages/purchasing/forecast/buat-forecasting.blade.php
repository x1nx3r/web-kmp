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
                    <div class="relative flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" 
                                   id="searchInput" 
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Cari No. PO atau nama klien..." 
                                   class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm"
                                   onkeypress="handleSearchKeyPress(event)">
                            <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                                <div class="w-3 h-3 sm:w-6 sm:h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-search text-green-500 text-xs sm:text-sm"></i>
                                </div>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="applyFilters()"
                                class="px-4 sm:px-6 py-2 sm:py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg sm:rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold text-sm whitespace-nowrap">
                            <i class="fas fa-search mr-0 sm:mr-2"></i>
                            <span class="hidden sm:inline">Cari</span>
                        </button>
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
                            <option value="dikonfirmasi" {{ request('status') == 'dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
                            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
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
        @if(isset($orders) && $orders->count() > 0)
            @foreach($orders as $po)
                {{-- Mobile: Simple List Item / Desktop: Full Card --}}
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-gray-300 border-l-4 border-l-green-500 hover:border-l-green-600 po-card" 
                     data-no-po="{{ strtolower($po->po_number ?? '') }}" 
                     data-klien="{{ strtolower(($po->klien->nama ?? '') . ($po->klien->cabang ? ' - ' . $po->klien->cabang : '')) }}" 
                     data-status="{{ $po->status ?? '' }}">
                    
                    {{-- Mobile List View --}}
                    <div class="block sm:hidden">
                        <div class="p-3 border-b border-gray-100">
                            {{-- Mobile Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="text-sm font-bold text-gray-900">{{ $po->po_number ?? 'N/A' }}</h3>
                                    <p class="text-xs text-green-600 mt-1">{{ ($po->klien->nama ?? 'N/A') . ($po->klien->cabang ? ' - ' . $po->klien->cabang : '') }}</p>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($po->status ?? '') === 'dikonfirmasi' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($po->status ?? 'Unknown') }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Mobile Stats --}}
                            <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                                <div class="flex items-center space-x-3">
                                    <span class="font-bold text-blue-600">{{ $po->orderDetails->count() }}</span>
                                    <span class="text-blue-600">Items</span>
                                    <span class="mx-1 text-gray-300">|</span>
                                    <span class="font-bold text-green-600">{{ number_format($po->qty_total ?? 0, 0, ',', '.') }}</span>
                                    <span class="text-green-600">Qty</span>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-yellow-600">Rp {{ number_format($po->total_amount ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            
                            {{-- Mobile Date --}}
                            <div class="flex items-center text-xs text-gray-500">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <span>
                                    @if($po->po_start_date && $po->po_end_date)
                                        {{ \Carbon\Carbon::parse($po->po_start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($po->po_end_date)->format('d/m/Y') }}
                                    @elseif($po->po_start_date)
                                        {{ \Carbon\Carbon::parse($po->po_start_date)->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </span>
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
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $po->updated_at ? $po->updated_at->format('d/m/Y') : 'N/A' }}
                                    </span>
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
                                        <h3 class="text-xl font-bold text-gray-900">{{ $po->po_number ?? 'N/A' }}</h3>
                                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ ($po->status ?? '') === 'dikonfirmasi' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($po->status ?? 'Unknown') }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <div class="flex items-center mb-1">
                                            <i class="fas fa-user w-4 text-green-500 mr-2"></i>
                                            <span>{{ ($po->klien->nama ?? 'N/A') . ($po->klien->cabang ? ' - ' . $po->klien->cabang : '') }}</span>
                                        </div>                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt w-4 text-green-500 mr-2"></i>
                            <span>
                                @if($po->po_start_date && $po->po_end_date)
                                    {{ \Carbon\Carbon::parse($po->po_start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($po->po_end_date)->format('d M Y') }}
                                @elseif($po->po_start_date)
                                    {{ \Carbon\Carbon::parse($po->po_start_date)->format('d M Y') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                                    </div>
                                </div>

                                {{-- Right Section: Stats --}}
                                <div class="flex items-center space-x-6">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600">{{ $po->orderDetails->count() }}</div>
                                        <div class="text-xs text-gray-500 uppercase tracking-wider">Items</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600">{{ number_format($po->total_qty ?? 0, 0, ',', '.') }}</div>
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
                                    <span>Lihat Detail Bahan Baku ({{ $po->orderDetails->count() }})</span>
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
                    <div id="detail-list-{{ $po->id }}" class="hidden border-t border-gray-200 bg-gray-50">
                        {{-- Mobile Detail List --}}
                        <div class="block sm:hidden p-3">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-list mr-2 text-green-600"></i>
                                Detail Bahan Baku & Harga
                            </h4>
                            <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                                @forelse($po->orderDetails as $detail)
                                    <div class="bg-white rounded-lg p-3 border border-gray-200 hover:border-green-300 transition-all shadow-sm">
                                        {{-- Nama Bahan --}}
                                        <h6 class="text-sm font-bold text-gray-900 mb-2 leading-tight">
                                            {{ $detail->bahanBakuKlien->nama ?? 'N/A' }}
                                        </h6>
                                        
                                        {{-- Info Grid --}}
                                        <div class="grid grid-cols-2 gap-2 mb-2.5">
                                            <div class="bg-blue-50 rounded px-2 py-1.5">
                                                <p class="text-xs text-blue-600 font-medium mb-0.5">Kuantitas</p>
                                                <p class="text-sm font-bold text-blue-800">{{ number_format($detail->qty ?? 0, 0, ',', '.') }} {{ $detail->satuan ?? 'kg' }}</p>
                                            </div>
                                            <div class="bg-green-50 rounded px-2 py-1.5">
                                                <p class="text-xs text-green-600 font-medium mb-0.5">Harga/{{ $detail->satuan ?? 'kg' }}</p>
                                                <p class="text-sm font-bold text-green-800">Rp {{ number_format($detail->harga_jual ?? 0, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                        
                                        {{-- Total & Button --}}
                                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                            <div>
                                                <p class="text-xs text-gray-500">Total Harga</p>
                                                <p class="text-sm font-bold text-yellow-700">Rp {{ number_format($detail->total_harga ?? 0, 0, ',', '.') }}</p>
                                            </div>
                                            @if(in_array(Auth::user()->role, ['direktur', 'manager_purchasing', 'staff_purchasing']))
                                            <button type="button" 
                                                    onclick="openForecastModal({{ $detail->id }}, '{{ $detail->bahanBakuKlien->nama ?? 'N/A' }}', {{ $detail->qty ?? 0 }}, {{ $po->id }}, '{{ $po->po_number ?? 'N/A' }}')"
                                                    class="flex-shrink-0 px-3 py-2 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                                                <i class="fas fa-chart-bar mr-1"></i>
                                                <span>Buat Forecast</span>
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-box-open text-gray-300 text-3xl mb-2"></i>
                                        <p class="text-sm">Tidak ada detail bahan baku</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Desktop Detail List --}}
                        <div class="hidden sm:block p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-list mr-2 text-gray-600"></i>
                                Detail Bahan Baku & Harga
                            </h4>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @forelse($po->orderDetails as $detail)
                                    <div class="bg-white rounded p-3 border border-gray-200 hover:border-green-300 transition-colors">
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="flex-1 min-w-0">
                                                <h6 class="text-sm font-semibold text-gray-900">{{ $detail->bahanBakuKlien->nama ?? 'N/A' }}</h6>
                                               
                                                <div class="flex items-center gap-4 text-sm text-gray-600 mt-2">
                                                    <span>
                                                        <strong>{{ number_format($detail->qty ?? 0) }}</strong> {{ $detail->satuan ?? 'kg' }}
                                                    </span>
                                                    <span>
                                                        <strong>Rp {{ number_format($detail->harga_jual ?? 0, 0, ',', '.') }}</strong>/{{ $detail->satuan ?? 'kg' }}
                                                    </span>
                                                    <span>
                                                        <strong>Rp {{ number_format($detail->total_harga ?? 0, 0, ',', '.') }}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                            @if(in_array(Auth::user()->role, ['direktur', 'manager_purchasing', 'staff_purchasing']))
                                            <button type="button" 
                                                    onclick="openForecastModal({{ $detail->id }}, '{{ $detail->bahanBakuKlien->nama ?? 'N/A' }}', {{ $detail->qty ?? 0 }}, {{ $po->id }}, '{{ $po->po_number ?? 'N/A' }}')"
                                                    class="flex-shrink-0 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium text-sm">
                                                    <i class="fas fa-chart-bar mr-2"></i>
                                                    Buat Forecast
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-box-open text-gray-300 text-3xl mb-3"></i>
                                        <p class="text-sm">Tidak ada detail bahan baku</p>
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
    @if(isset($orders) && $orders->hasPages())
        <div class="bg-white rounded-lg shadow-sm border p-4 mt-6">
            <div class="flex flex-col sm:flex-row items-center justify-between">
                {{-- Results Info --}}
                <div class="mb-3 sm:mb-0">
                    <p class="text-sm text-gray-700">
                        Menampilkan
                        <span class="font-medium">{{ $orders->firstItem() }}</span>
                        sampai
                        <span class="font-medium">{{ $orders->lastItem() }}</span>
                        dari
                        <span class="font-medium">{{ $orders->total() }}</span>
                        Purchase Order
                    </p>
                </div>

                {{-- Pagination Links --}}
                <div class="flex items-center space-x-2">
                    {{-- Previous Page --}}
                    @if ($orders->onFirstPage())
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i>
                            Sebelumnya
                        </span>
                    @else
                        @php
                            $prevUrl = $orders->previousPageUrl();
                            $prevUrlParts = parse_url($prevUrl);
                            parse_str($prevUrlParts['query'] ?? '', $prevParams);
                            $prevParams['tab'] = 'buat-forecasting';
                            // Preserve other filters
                            if (request('search')) $prevParams['search'] = request('search');
                            if (request('status')) $prevParams['status'] = request('status');
                            if (request('sort_amount')) $prevParams['sort_amount'] = request('sort_amount');
                            if (request('sort_items')) $prevParams['sort_items'] = request('sort_items');
                            $prevUrl = $prevUrlParts['path'] . '?' . http_build_query($prevParams);
                        @endphp
                        <a href="{{ $prevUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i>
                            Sebelumnya
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @if($orders->lastPage() > 1)
                        <div class="hidden sm:flex space-x-1">
                            @foreach ($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                                @if ($page == $orders->currentPage())
                                    <span class="px-3 py-2 text-sm font-medium text-white bg-green-600 border border-green-600 rounded-lg">
                                        {{ $page }}
                                    </span>
                                @else
                                    @php
                                        $pageUrlParts = parse_url($url);
                                        parse_str($pageUrlParts['query'] ?? '', $pageUrlParams);
                                        $pageUrlParams['tab'] = 'buat-forecasting';
                                        // Preserve other filters
                                        if (request('search')) $pageUrlParams['search'] = request('search');
                                        if (request('status')) $pageUrlParams['status'] = request('status');
                                        if (request('sort_amount')) $pageUrlParams['sort_amount'] = request('sort_amount');
                                        if (request('sort_items')) $pageUrlParams['sort_items'] = request('sort_items');
                                        $pageUrl = $pageUrlParts['path'] . '?' . http_build_query($pageUrlParams);
                                    @endphp
                                    <a href="{{ $pageUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Mobile Page Indicator --}}
                        <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                            {{ $orders->currentPage() }} / {{ $orders->lastPage() }}
                        </div>
                    @endif

                    {{-- Next Page --}}
                    @if ($orders->hasMorePages())
                        @php
                            $nextUrl = $orders->nextPageUrl();
                            $nextUrlParts = parse_url($nextUrl);
                            parse_str($nextUrlParts['query'] ?? '', $nextParams);
                            $nextParams['tab'] = 'buat-forecasting';
                            // Preserve other filters
                            if (request('search')) $nextParams['search'] = request('search');
                            if (request('status')) $nextParams['status'] = request('status');
                            if (request('sort_amount')) $nextParams['sort_amount'] = request('sort_amount');
                            if (request('sort_items')) $nextParams['sort_items'] = request('sort_items');
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

{{-- Modal Timeout --}}
<div id="timeoutModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="timeout-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>
        
        {{-- Center the modal --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="timeout-modal-title">
                            Request Timeout
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Server membutuhkan waktu lebih lama dari biasanya untuk memproses forecast Anda. 
                                Namun, forecast mungkin sudah berhasil dibuat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeTimeoutModal()" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                    <i class="fas fa-refresh mr-2"></i>
                    Refresh Halaman
                </button>
                <button type="button" onclick="closeTimeoutModalOnly()" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
// Handle Enter key press in search input
function handleSearchKeyPress(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        applyFilters();
    }
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
    
    // Always preserve the current tab and ensure it's buat-forecasting
    params.append('tab', 'buat-forecasting');

    // Show active filters
    showActiveFilters(search, status, sortAmount, sortItems);

    // Redirect with new parameters (without page to start from page 1)
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

// Global functions for modals (accessible from included files)
window.showTimeoutModal = function() {
    document.getElementById('timeoutModal').classList.remove('hidden');
};

window.closeTimeoutModal = function() {
    document.getElementById('timeoutModal').classList.add('hidden');
    
    // Preserve current page parameters when reloading
    const currentParams = new URLSearchParams(window.location.search);
    const currentPage = currentParams.get('page') || '1';
    const currentTab = currentParams.get('tab') || 'buat-forecasting';
    
    // Build URL with preserved parameters
    const params = new URLSearchParams();
    params.append('tab', currentTab);
    params.append('page', currentPage);
    
    // Preserve other filters
    if (currentParams.get('search')) params.append('search', currentParams.get('search'));
    if (currentParams.get('status')) params.append('status', currentParams.get('status'));
    if (currentParams.get('sort_amount')) params.append('sort_amount', currentParams.get('sort_amount'));
    if (currentParams.get('sort_items')) params.append('sort_items', currentParams.get('sort_items'));
    
    // Reload with preserved parameters
    window.location.href = window.location.pathname + '?' + params.toString();
};

window.closeTimeoutModalOnly = function() {
    document.getElementById('timeoutModal').classList.add('hidden');
};

// Override the universal success modal's close function to preserve page parameters
window.originalCloseSuccessModal = window.closeSuccessModal;
window.closeSuccessModal = function() {
    const modal = document.getElementById('successModal');
    if (!modal) return;
    
    modal.classList.add('hidden');
    modal.classList.remove('success-modal-auto-close');
    document.body.classList.remove('overflow-hidden');
    
    // Preserve current page parameters when reloading
    const currentParams = new URLSearchParams(window.location.search);
    const currentPage = currentParams.get('page') || '1';
    const currentTab = currentParams.get('tab') || 'buat-forecasting';
    
    // Build URL with preserved parameters
    const params = new URLSearchParams();
    params.append('tab', currentTab);
    params.append('page', currentPage);
    
    // Preserve other filters
    if (currentParams.get('search')) params.append('search', currentParams.get('search'));
    if (currentParams.get('status')) params.append('status', currentParams.get('status'));
    if (currentParams.get('sort_amount')) params.append('sort_amount', currentParams.get('sort_amount'));
    if (currentParams.get('sort_items')) params.append('sort_items', currentParams.get('sort_items'));
    
    // Reload with preserved parameters
    setTimeout(() => {
        window.location.href = window.location.pathname + '?' + params.toString();
    }, 300);
};
</script>
