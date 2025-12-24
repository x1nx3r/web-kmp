{{-- Menunggu Fisik Tab Content --}}
<div class="space-y-6 fade-in-up">
    {{-- Search and Filter Section --}}
    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 mb-3 sm:mb-6">
        <div class="space-y-3 sm:space-y-6">
            {{-- Search Section --}}
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                {{-- Search Input --}}
                <div class="flex-1">
                    <label class="flex items-center text-xs sm:text-sm font-bold text-purple-700 mb-1 sm:mb-3">
                        <div class="w-4 h-4 sm:w-6 sm:h-6 bg-purple-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                            <i class="fas fa-search text-white text-xs"></i>
                        </div>
                        Pencarian Menunggu Fisik
                    </label>
                    <div class="relative flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" 
                                   id="searchInputFisik" 
                                   name="search_fisik"
                                   value="{{ request('search_fisik') }}"
                                   placeholder="Cari No. PO atau nama purchasing..." 
                                   class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-purple-200 focus:border-purple-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-fisik"
                                   onkeypress="handleSearchKeyPressFisik(event)">
                            <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                                <div class="w-3 h-3 sm:w-6 sm:h-6 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-search text-purple-500 text-xs sm:text-sm"></i>
                                </div>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="submitSearchFisik()"
                                class="px-4 sm:px-6 py-2 sm:py-3 bg-purple-500 hover:bg-purple-600 text-white rounded-lg sm:rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold text-sm whitespace-nowrap">
                            <i class="fas fa-search mr-0 sm:mr-2"></i>
                            <span class="hidden sm:inline">Cari</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filter Section - Horizontal Layout --}}
            <div class="rounded-lg sm:rounded-xl p-2 sm:p-3">
                <h3 class="flex items-center text-xs sm:text-sm font-bold text-purple-700 mb-2 sm:mb-3">
                    <div class="w-4 h-4 sm:w-5 sm:h-5 bg-purple-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                        <i class="fas fa-filter text-white text-xs"></i>
                    </div>
                    Filter & Urutan
                </h3>
                
                {{-- Horizontal Filter Layout --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-2 sm:gap-4">
                    {{-- Filter by Purchasing --}}
                    <div class="w-full sm:w-64 shrink-0">
                        <label class="block text-xs font-medium text-purple-600 mb-1">
                            <i class="fas fa-user mr-1 text-purple-500 text-xs"></i>
                            PIC Purchasing
                        </label>
                        <select id="filterPurchasingFisik" name="filter_purchasing_fisik" class="w-full py-2 px-3 border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 bg-white transition-all duration-200 text-sm" onchange="applyFiltersFisik()">
                            <option value="">Semua Purchasing</option>
                            @php
                                // Debug: check purchasing data
                                $purchasingOptions = collect();
                                foreach($menungguFisik->items() ?? [] as $item) {
                                    if($item->purchasing && $item->purchasing->nama) {
                                        $purchasingOptions->put($item->purchasing->id, $item->purchasing->nama);
                                    }
                                }
                                $purchasingOptions = $purchasingOptions->unique()->filter();
                            @endphp
                            @foreach($purchasingOptions as $id => $name)
                                <option value="{{ $id }}" {{ request('filter_purchasing_fisik') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort by Date --}}
                    <div class="w-full sm:w-48 shrink-0">
                        <label class="block text-xs font-medium text-purple-600 mb-1">
                            <i class="fas fa-sort mr-1 text-purple-500 text-xs"></i>
                            Urutkan
                        </label>
                        <select id="sortDateFisik" name="sort_date_fisik" class="w-full py-2 px-3 border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 bg-white transition-all duration-200 text-sm" onchange="applyFiltersFisik()">
                            <option value="">Default</option>
                            <option value="newest" {{ request('sort_date_fisik') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort_date_fisik') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>

                    {{-- Clear Filter Button --}}
                    <div class="w-full sm:w-auto sm:ml-auto shrink-0">
                        <button onclick="clearAllFiltersFisik()" class="w-full sm:w-auto px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 text-sm font-medium whitespace-nowrap">
                            <i class="fas fa-times mr-1"></i>
                            Hapus Filter
                        </button>
                    </div>
                </div>
            </div>

            {{-- Active Filters Display --}}
            <div id="activeFiltersFisik" class="flex flex-wrap gap-2" style="display: none;">
                <span class="text-xs sm:text-sm font-bold text-purple-700">Filter aktif:</span>
            </div>
        </div>
    </div>

    {{-- Simplified Header Section --}}
    <div class="flex items-center justify-between mb-4 bg-purple-50 border border-purple-200 p-3 rounded-lg">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                <i class="fas fa-box-open text-white text-sm"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Menunggu Fisik</h3>
        </div>
        <span class="text-sm text-purple-600 font-medium">{{ $menungguFisik->total() }} pengiriman</span>
    </div>

    {{-- Content --}}
    @php
        // Group pengiriman by order_id (purchase_order_id field)
        $groupedPengiriman = $menungguFisik->items() ? collect($menungguFisik->items())->groupBy('purchase_order_id') : collect();
        
        // Define user role access once for all items
        $currentUser = Auth::user();
        $canManagePengiriman = in_array($currentUser->role, ['direktur', 'manager_purchasing', 'staff_purchasing']);
        $canVerifyFisik = in_array($currentUser->role, ['direktur', 'manager_purchasing']);
    @endphp

    <div class="space-y-2">
        @forelse($groupedPengiriman as $poId => $pengirimanList)
            @php
                $po = $pengirimanList->first()->order;
                $purchasing = $pengirimanList->first()->purchasing;
            @endphp
            {{-- Simplified PO Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 border-l-4 border-l-purple-500 fisik-pengiriman-card po-card" 
                 data-no-po="{{ strtolower($po->po_number ?? '') }}" 
                 data-purchasing="{{ strtolower($purchasing->nama ?? '') }}" 
                 data-pengiriman="{{ $pengirimanList->count() }}">
                
                <div class="p-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-purple-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-alt text-white text-xs"></i>
                            </div>
                            <div>
                                @if($po && $po->po_number)
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $po->po_number }}</h3>
                                @endif
                                @if($po && $po->klien && $po->klien->nama)
                                    <p class="text-xs text-gray-500">{{ $po->klien->nama }}{{ $po->klien->cabang ? ' - ' . $po->klien->cabang : '' }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $pengirimanList->count() }} {{ $pengirimanList->count() == 1 ? 'pengiriman' : 'pengiriman' }}</p>
                                @php 
                                    // Calculate total with refraksi consideration
                                    $totalHarga = 0;
                                    foreach($pengirimanList as $item) {
                                        if($item->approvalPembayaran && $item->approvalPembayaran->amount_after_refraksi > 0) {
                                            $totalHarga += $item->approvalPembayaran->amount_after_refraksi;
                                        } else {
                                            $totalHarga += $item->total_harga_kirim;
                                        }
                                    }
                                @endphp
                                @if($totalHarga > 0)
                                    <p class="text-sm font-semibold text-purple-600">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Simplified Pengiriman List - Auto Show --}}
                <div class="border-t border-gray-200 pengiriman-list" id="pengiriman-list-po-{{ $poId }}">
                    <div class="p-3">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-truck text-purple-600 mr-2"></i>
                            Daftar Pengiriman Menunggu Fisik ({{ $pengirimanList->count() }})
                        </h4>
                        
                        <div class="divide-y divide-gray-200">
                            @foreach($pengirimanList as $pengiriman)
                                <div class="py-3 hover:bg-gray-50 transition-colors px-2 -mx-2 rounded" 
                                     data-pengiriman-no="{{ strtolower($pengiriman->no_pengiriman ?? '') }}"
                                     data-purchasing="{{ strtolower($pengiriman->purchasing->nama ?? '') }}"
                                     data-qty="{{ $pengiriman->total_qty_kirim ?? 0 }}"
                                     data-amount="{{ $pengiriman->total_harga_kirim ?? 0 }}"
                                     data-date="{{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('Y-m-d') : '' }}"
                                     data-status="{{ $pengiriman->status ?? '' }}">
                                    
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        {{-- Info Section --}}
                                        <div class="flex-1 min-w-0 space-y-2">
                                            {{-- Row 1: No Pengiriman & Status --}}
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-2 h-2 bg-purple-500 rounded-full shrink-0"></div>
                                                    <span class="text-sm font-semibold text-gray-900">{{ $pengiriman->no_pengiriman }}</span>
                                                </div>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-box-open mr-1"></i>
                                                    Menunggu Fisik
                                                </span>
                                            </div>
                                            
                                            {{-- Row 2: Detail Info --}}
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                                                @if($pengiriman->purchasing && $pengiriman->purchasing->nama)
                                                    <span class="flex items-center gap-1">
                                                        <i class="fas fa-user text-purple-600"></i>
                                                        <span class="truncate max-w-[150px]">{{ $pengiriman->purchasing->nama }}</span>
                                                    </span>
                                                @endif
                                                @if($pengiriman->tanggal_kirim)
                                                    <span class="flex items-center gap-1">
                                                        <i class="fas fa-calendar text-purple-600"></i>
                                                        {{ $pengiriman->tanggal_kirim->format('d M Y') }}
                                                    </span>
                                                @endif
                                                
                                                {{-- Qty - Cek apakah ada refraksi dari approval pembayaran --}}
                                                @php
                                                    $qtyToShow = $pengiriman->total_qty_kirim;
                                                    $hasRefraksiQty = false;
                                                    $refraksiQtyAmount = 0;
                                                    
                                                    if($pengiriman->approvalPembayaran && $pengiriman->approvalPembayaran->qty_after_refraksi > 0) {
                                                        $qtyToShow = $pengiriman->approvalPembayaran->qty_after_refraksi;
                                                        $hasRefraksiQty = true;
                                                        $refraksiQtyAmount = $pengiriman->approvalPembayaran->qty_before_refraksi - $pengiriman->approvalPembayaran->qty_after_refraksi;
                                                    }
                                                @endphp
                                                
                                                @if($qtyToShow && $qtyToShow > 0)
                                                    <span class="flex flex-col gap-0.5">
                                                        <span class="flex items-center gap-1">
                                                            <i class="fas fa-weight text-purple-600"></i>
                                                            <span class="font-medium">{{ number_format($qtyToShow, 0, ',', '.') }} kg</span>
                                                        </span>
                                                        @if($hasRefraksiQty && $refraksiQtyAmount > 0)
                                                            <span class="text-red-600 text-[10px] ml-4">
                                                                <i class="fas fa-arrow-down"></i>
                                                                Refraksi: {{ number_format($refraksiQtyAmount, 2, ',', '.') }} kg
                                                            </span>
                                                        @endif
                                                    </span>
                                                @endif
                                                
                                                {{-- Amount - Cek apakah ada refraksi dari approval pembayaran --}}
                                                @php
                                                    $amountToShow = $pengiriman->total_harga_kirim;
                                                    $hasRefraksiAmount = false;
                                                    
                                                    if($pengiriman->approvalPembayaran && $pengiriman->approvalPembayaran->amount_after_refraksi > 0) {
                                                        $amountToShow = $pengiriman->approvalPembayaran->amount_after_refraksi;
                                                        $hasRefraksiAmount = true;
                                                    }
                                                @endphp
                                                
                                                @if($amountToShow && $amountToShow > 0)
                                                    <span class="flex flex-col gap-0.5">
                                                        <span class="flex items-center gap-1 font-medium text-green-700">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                            <span>Rp {{ number_format($amountToShow, 0, ',', '.') }}</span>
                                                        </span>
                                                        @if($hasRefraksiAmount && $pengiriman->approvalPembayaran->refraksi_amount > 0)
                                                            <span class="text-red-600 text-[10px] ml-4">
                                                                <i class="fas fa-arrow-down"></i>
                                                                Refraksi: Rp {{ number_format($pengiriman->approvalPembayaran->refraksi_amount, 0, ',', '.') }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Action Button Section --}}
                                        <div class="flex flex-col sm:flex-row gap-2 sm:w-auto w-full">
                                            {{-- Download Bukti Pembayaran Button - Accessible for All Users --}}
                                            @if($pengiriman->approvalPembayaran && $pengiriman->approvalPembayaran->bukti_pembayaran)
                                                <a href="{{ asset('storage/' . $pengiriman->approvalPembayaran->bukti_pembayaran) }}" 
                                                   download
                                                   class="bg-green-500 hover:bg-green-600 active:bg-green-700 text-white px-3 py-2 rounded-lg text-xs font-medium flex items-center justify-center transition-all duration-200 w-full sm:w-auto whitespace-nowrap shadow-sm hover:shadow" 
                                                   title="Download Bukti Pembayaran">
                                                    <i class="fas fa-download mr-1.5"></i>
                                                    Bukti Bayar
                                                </a>
                                            @endif
                                            
                                            @if($canVerifyFisik)
                                                {{-- Button for Direktur & Manager: Verifikasi Fisik --}}
                                                <button onclick="openVerifikasiFisikModal({{ $pengiriman->id }}, '{{ $pengiriman->no_pengiriman }}')" 
                                                        class="bg-purple-500 hover:bg-purple-600 active:bg-purple-700 text-white px-3 py-2 rounded-lg text-xs font-medium flex items-center justify-center transition-all duration-200 w-full sm:w-auto whitespace-nowrap shadow-sm hover:shadow" 
                                                        title="Verifikasi Fisik">
                                                    <i class="fas fa-box-check mr-1.5"></i>
                                                    Verifikasi Fisik
                                                </button>
                                            @elseif($currentUser)
                                                {{-- Button for Staff Purchasing: Read Only --}}
                                                <button onclick="openVerifikasiFisikModal({{ $pengiriman->id }}, '{{ $pengiriman->no_pengiriman }}')" 
                                                        class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-medium flex items-center justify-center transition-all duration-200 w-full sm:w-auto whitespace-nowrap shadow-sm hover:shadow" 
                                                        title="Lihat Detail">
                                                    <i class="fas fa-eye mr-1.5"></i>
                                                    Lihat
                                                </button>
                                            @else
                                                {{-- Button for Other Roles: Disabled --}}
                                                <button disabled
                                                        class="bg-gray-400 text-gray-200 px-3 py-2 rounded-lg text-xs font-medium flex items-center justify-center cursor-not-allowed opacity-60 w-full sm:w-auto whitespace-nowrap" 
                                                        title="Akses Terbatas - Hanya Direktur dan Manager Purchasing yang dapat melakukan verifikasi fisik">
                                                    <i class="fas fa-lock mr-1.5"></i>
                                                    Terbatas
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>    
                </div>
            </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-box-open text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Pengiriman Menunggu Fisik</h3>
                    <p>Belum ada pengiriman dengan status menunggu fisik.</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if(isset($menungguFisik) && $menungguFisik->hasPages())
            <div class="bg-white rounded-lg shadow-sm border p-4 mt-6">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    {{-- Results Info --}}
                    <div class="mb-3 sm:mb-0">
                        <p class="text-sm text-gray-700">
                            Menampilkan
                            <span class="font-medium">{{ $menungguFisik->firstItem() }}</span>
                            sampai
                            <span class="font-medium">{{ $menungguFisik->lastItem() }}</span>
                            dari
                            <span class="font-medium">{{ $menungguFisik->total() }}</span>
                            Pengiriman Menunggu Fisik
                        </p>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="flex items-center space-x-2">
                        {{-- Previous Page --}}
                        @if ($menungguFisik->onFirstPage())
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </span>
                        @else
                            @php
                                $prevUrl = $menungguFisik->previousPageUrl();
                                $prevUrlParts = parse_url($prevUrl);
                                parse_str($prevUrlParts['query'] ?? '', $prevParams);
                                $prevParams['tab'] = 'menunggu-fisik';
                                // Preserve other filters
                                if (request('search_fisik')) $prevParams['search_fisik'] = request('search_fisik');
                                if (request('filter_purchasing_fisik')) $prevParams['filter_purchasing_fisik'] = request('filter_purchasing_fisik');
                                if (request('sort_date_fisik')) $prevParams['sort_date_fisik'] = request('sort_date_fisik');
                                $prevUrl = $prevUrlParts['path'] . '?' . http_build_query($prevParams);
                            @endphp
                            <a href="{{ $prevUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition-colors">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @if($menungguFisik->lastPage() > 1)
                            <div class="hidden sm:flex items-center space-x-1">
                                @foreach ($menungguFisik->getUrlRange(1, $menungguFisik->lastPage()) as $page => $url)
                                    @php
                                        $urlParts = parse_url($url);
                                        parse_str($urlParts['query'] ?? '', $urlParams);
                                        $urlParams['tab'] = 'menunggu-fisik';
                                        // Preserve other filters
                                        if (request('search_fisik')) $urlParams['search_fisik'] = request('search_fisik');
                                        if (request('filter_purchasing_fisik')) $urlParams['filter_purchasing_fisik'] = request('filter_purchasing_fisik');
                                        if (request('sort_date_fisik')) $urlParams['sort_date_fisik'] = request('sort_date_fisik');
                                        $pageUrl = $urlParts['path'] . '?' . http_build_query($urlParams);
                                    @endphp
                                    
                                    @if ($page == $menungguFisik->currentPage())
                                        <span class="px-3 py-2 text-sm font-medium text-purple-700 bg-purple-100 border border-purple-300 rounded-lg">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $pageUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition-colors">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Mobile Page Indicator --}}
                            <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                                {{ $menungguFisik->currentPage() }} / {{ $menungguFisik->lastPage() }}
                            </div>
                        @endif

                        {{-- Next Page --}}
                        @if ($menungguFisik->hasMorePages())
                            @php
                                $nextUrl = $menungguFisik->nextPageUrl();
                                $nextUrlParts = parse_url($nextUrl);
                                parse_str($nextUrlParts['query'] ?? '', $nextParams);
                                $nextParams['tab'] = 'menunggu-fisik';
                                // Preserve other filters
                                if (request('search_fisik')) $nextParams['search_fisik'] = request('search_fisik');
                                if (request('filter_purchasing_fisik')) $nextParams['filter_purchasing_fisik'] = request('filter_purchasing_fisik');
                                if (request('sort_date_fisik')) $nextParams['sort_date_fisik'] = request('sort_date_fisik');
                                $nextUrl = $nextUrlParts['path'] . '?' . http_build_query($nextParams);
                            @endphp
                            <a href="{{ $nextUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-purple-50 hover:text-purple-700 hover:border-purple-300 transition-colors">
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

{{-- Modal Verifikasi Fisik --}}
<div id="verifikasiFisikModal" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 z-50 hidden" style="display: none; align-items: center; justify-content: center;">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-full overflow-y-auto border border-purple-600">
        <div class="p-2" id="verifikasiFisikModalContent">
        </div>
    </div>
</div>

{{-- SweetAlert2 Library --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- JavaScript untuk Tab Menunggu Fisik --}}
<script>
// Variables for current pengiriman
let currentPengirimanIdFisik = null;

// Handle Enter key press in search input
function handleSearchKeyPressFisik(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        submitSearchFisik();
    }
}

// Submit search to server
function submitSearchFisik() {
    const currentParams = new URLSearchParams(window.location.search);
    const searchValue = document.getElementById('searchInputFisik').value;
    
    currentParams.set('tab', 'menunggu-fisik');
    
    if (searchValue) {
        currentParams.set('search_fisik', searchValue);
    } else {
        currentParams.delete('search_fisik');
    }
    
    currentParams.delete('fisik_page');
    
    window.location.href = '/procurement/pengiriman?' + currentParams.toString();
}

// Apply filters function for server-side filtering
function applyFiltersFisik() {
    const currentParams = new URLSearchParams(window.location.search);
    
    const searchValue = document.getElementById('searchInputFisik').value;
    const filterPurchasing = document.getElementById('filterPurchasingFisik').value;
    const sortDate = document.getElementById('sortDateFisik').value;
    
    currentParams.set('tab', 'menunggu-fisik');
    
    if (searchValue) currentParams.set('search_fisik', searchValue);
    else currentParams.delete('search_fisik');
    
    if (filterPurchasing) currentParams.set('filter_purchasing_fisik', filterPurchasing);
    else currentParams.delete('filter_purchasing_fisik');
    
    if (sortDate) currentParams.set('sort_date_fisik', sortDate);
    else currentParams.delete('sort_date_fisik');
    
    currentParams.delete('fisik_page');
    
    window.location.href = '/procurement/pengiriman?' + currentParams.toString();
}

// Clear all filters
function clearAllFiltersFisik() {
    const currentParams = new URLSearchParams(window.location.search);
    
    const newParams = new URLSearchParams();
    newParams.set('tab', 'menunggu-fisik');
    
    window.location.href = '/procurement/pengiriman?' + newParams.toString();
}

// Open verifikasi fisik modal
function openVerifikasiFisikModal(id, noPengiriman) {
    console.log('Opening verifikasi fisik modal for ID:', id);
    currentPengirimanIdFisik = id;
    
    fetch(`/procurement/pengiriman/${id}/detail-fisik`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('verifikasiFisikModalContent').innerHTML = html;
            document.getElementById('verifikasiFisikModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            console.log('Modal opened successfully');
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat detail pengiriman: ' + error.message
            });
        });
}

// Close verifikasi fisik modal
function closeVerifikasiFisikModal() {
    document.getElementById('verifikasiFisikModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentPengirimanIdFisik = null;
}

// Save catatan pengiriman fisik
function saveCatatanPengirimanFisik(pengirimanId) {
    const catatanTextarea = document.getElementById('catatanPengirimanFisik');
    const catatan = catatanTextarea.value;
    
    // Show loading
    const button = event.target;
    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    
    // Send AJAX request
    fetch(`/procurement/pengiriman/${pengirimanId}/update-catatan`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ catatan: catatan })
    })
    .then(response => {
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Response bukan JSON. Kemungkinan ada error di server.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Catatan berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert('Catatan berhasil disimpan');
            }
        } else {
            throw new Error(data.message || 'Gagal menyimpan catatan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: error.message || 'Gagal menyimpan catatan',
                confirmButtonColor: '#EF4444'
            });
        } else {
            alert('Gagal menyimpan catatan: ' + error.message);
        }
    })
    .finally(() => {
        // Restore button
        button.disabled = false;
        button.innerHTML = originalHtml;
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const fisikModal = document.getElementById('verifikasiFisikModal');
    if (event.target === fisikModal) {
        closeVerifikasiFisikModal();
    }
});

// Update active filters on page load
document.addEventListener('DOMContentLoaded', function() {
    updateActiveFiltersFisik();
});

function updateActiveFiltersFisik() {
    const activeFiltersContainer = document.getElementById('activeFiltersFisik');
    const searchValue = document.getElementById('searchInputFisik').value;
    const filterPurchasing = document.getElementById('filterPurchasingFisik').value;
    const sortDate = document.getElementById('sortDateFisik').value;
    
    let hasActiveFilters = false;
    let filtersHTML = '<span class="text-xs sm:text-sm font-bold text-purple-700">Filter aktif:</span>';
    
    if (searchValue) {
        filtersHTML += `<span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs active-filter-tag">Pencarian: ${searchValue}</span>`;
        hasActiveFilters = true;
    }
    
    if (filterPurchasing) {
        const purchasingSelect = document.getElementById('filterPurchasingFisik');
        const purchasingName = purchasingSelect.options[purchasingSelect.selectedIndex].text;
        filtersHTML += `<span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs active-filter-tag">Purchasing: ${purchasingName}</span>`;
        hasActiveFilters = true;
    }
    
    if (sortDate) {
        const dateLabels = {
            'newest': 'Terbaru',
            'oldest': 'Terlama'
        };
        filtersHTML += `<span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs active-filter-tag">Urutkan: ${dateLabels[sortDate]}</span>`;
        hasActiveFilters = true;
    }
    
    if (hasActiveFilters) {
        filtersHTML += `<button onclick="clearAllFiltersFisik()" class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs hover:bg-red-200 transition-colors ml-2">
            <i class="fas fa-times mr-1"></i>Hapus Semua
        </button>`;
        activeFiltersContainer.innerHTML = filtersHTML;
        activeFiltersContainer.style.display = 'flex';
    } else {
        activeFiltersContainer.style.display = 'none';
    }
}

// Submit verifikasi fisik (global function)
function submitVerifikasiFisik() {
    let pengirimanId = currentPengirimanIdFisik;
    const pengirimanIdInput = document.getElementById('pengirimanIdFisik');
    if (pengirimanIdInput) {
        pengirimanId = pengirimanIdInput.value;
    }

    if (!pengirimanId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'ID pengiriman tidak ditemukan'
        });
        return;
    }

    const konfirmasiCheckbox = document.getElementById('konfirmasiVerifikasiFisik');
    
    // Validasi konfirmasi checkbox
    if (konfirmasiCheckbox && !konfirmasiCheckbox.checked) {
        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Diperlukan',
            text: 'Silakan centang kotak konfirmasi terlebih dahulu sebelum melanjutkan verifikasi fisik.',
            confirmButtonColor: '#9333ea'
        });
        konfirmasiCheckbox.focus();
        return;
    }
    
    // Konfirmasi final sebelum submit
    Swal.fire({
        title: 'Konfirmasi Verifikasi Fisik',
        html: `
            <div class="text-left">
                <p class="mb-3">Apakah Anda yakin ingin memverifikasi fisik pengiriman ini?</p>
                <div class="bg-purple-50 border border-purple-200 rounded p-3">
                    <p class="text-sm text-purple-800">
                        <strong>Peringatan:</strong> Setelah diverifikasi fisik, pengiriman akan diproses ke tahap berikutnya.
                    </p>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#9333ea',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Verifikasi Fisik',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses Verifikasi Fisik...',
                text: 'Silakan tunggu sebentar.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit to server
            fetch(`/procurement/pengiriman/${pengirimanId}/verifikasi-fisik`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response bukan JSON. Kemungkinan ada error di server.');
                }
                return response.json();
            })
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    closeVerifikasiFisikModal();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Pengiriman berhasil diverifikasi fisik',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Verifikasi Fisik Gagal',
                        text: data.message || 'Terjadi kesalahan saat memverifikasi fisik pengiriman.',
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                    confirmButtonColor: '#dc2626'
                });
            });
        }
    });
}
</script>
