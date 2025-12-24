{{-- Pengiriman Masuk Tab Content --}}
<div class="space-y-6 fade-in-up">
    {{-- Search and Filter Section --}}
    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 mb-3 sm:mb-6">
        <div class="space-y-3 sm:space-y-6">
            {{-- Search Section --}}
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                {{-- Search Input --}}
                <div class="flex-1">
                    <label class="flex items-center text-xs sm:text-sm font-bold text-blue-700 mb-1 sm:mb-3">
                        <div class="w-4 h-4 sm:w-6 sm:h-6 bg-blue-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                            <i class="fas fa-search text-white text-xs"></i>
                        </div>
                        Pencarian Pengiriman Masuk
                    </label>
                    <div class="relative flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" 
                                   id="searchInputMasuk" 
                                   name="search_masuk"
                                   value="{{ request('search_masuk') }}"
                                   placeholder="Cari No. PO atau nama purchasing..." 
                                   class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-blue-200 focus:border-blue-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-masuk"
                                   onkeypress="handleSearchKeyPressMasuk(event)">
                            <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                                <div class="w-3 h-3 sm:w-6 sm:h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-search text-blue-500 text-xs sm:text-sm"></i>
                                </div>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="submitSearchMasuk()"
                                class="px-4 sm:px-6 py-2 sm:py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg sm:rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold text-sm whitespace-nowrap">
                            <i class="fas fa-search mr-0 sm:mr-2"></i>
                            <span class="hidden sm:inline">Cari</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filter Section - Horizontal Layout --}}
            <div class="rounded-lg sm:rounded-xl p-2 sm:p-3">
                <h3 class="flex items-center text-xs sm:text-sm font-bold text-blue-700 mb-2 sm:mb-3">
                    <div class="w-4 h-4 sm:w-5 sm:h-5 bg-blue-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                        <i class="fas fa-filter text-white text-xs"></i>
                    </div>
                    Filter & Urutan
                </h3>
                
                {{-- Horizontal Filter Layout --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-2 sm:gap-4">
                    {{-- Filter by Purchasing --}}
                    <div class="w-full sm:w-64 shrink-0">
                        <label class="block text-xs font-medium text-blue-600 mb-1">
                            <i class="fas fa-user mr-1 text-blue-500 text-xs"></i>
                            PIC Purchasing
                        </label>
                        <select id="filterPurchasing" name="filter_purchasing" class="w-full py-2 px-3 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-200 focus:border-blue-500 bg-white transition-all duration-200 text-sm" onchange="applyFiltersMasuk()">
                            <option value="">Semua Purchasing</option>
                            @php
                                // Debug: check purchasing data
                                $purchasingOptions = collect();
                                foreach($pengirimanMasuk->items() ?? [] as $item) {
                                    if($item->purchasing && $item->purchasing->nama) {
                                        $purchasingOptions->put($item->purchasing->id, $item->purchasing->nama);
                                    }
                                }
                                $purchasingOptions = $purchasingOptions->unique()->filter();
                            @endphp
                            @foreach($purchasingOptions as $id => $name)
                                <option value="{{ $id }}" {{ request('filter_purchasing') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort by Date --}}
                    <div class="w-full sm:w-48 shrink-0">
                        <label class="block text-xs font-medium text-blue-600 mb-1">
                            <i class="fas fa-sort mr-1 text-blue-500 text-xs"></i>
                            Urutkan
                        </label>
                        <select id="sortDateMasuk" name="sort_date_masuk" class="w-full py-2 px-3 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-200 focus:border-blue-500 bg-white transition-all duration-200 text-sm" onchange="applyFiltersMasuk()">
                            <option value="">Default</option>
                            <option value="newest" {{ request('sort_date_masuk') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort_date_masuk') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>

                    {{-- Clear Filter Button --}}
                    <div class="w-full sm:w-auto sm:ml-auto shrink-0">
                        <button onclick="clearAllFiltersMasuk()" class="w-full sm:w-auto px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 text-sm font-medium whitespace-nowrap">
                            <i class="fas fa-times mr-1"></i>
                            Hapus Filter
                        </button>
                    </div>
                </div>
            </div>

            {{-- Active Filters Display --}}
            <div id="activeFiltersMasuk" class="flex flex-wrap gap-2" style="display: none;">
                <span class="text-xs sm:text-sm font-bold text-blue-700">Filter aktif:</span>
            </div>
        </div>
    </div>

    {{-- Simplified Header Section --}}
    <div class="flex items-center justify-between mb-4 bg-blue-50 border border-blue-200 p-3 rounded-lg">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center mr-2">
                <i class="fas fa-inbox text-white text-xs"></i>
            </div>
            Pengiriman Masuk
        </h2>
        
        {{-- Compact Summary Stats --}}
        <div class="flex items-center space-x-4 text-sm">
            @php
                // Group pengiriman by order_id (purchase_order_id field)
                $groupedPengiriman = collect($pengirimanMasuk->items() ?? [])->groupBy('purchase_order_id');
                
                // Define user role access once for all items
                $currentUser = Auth::user();
                $canManagePengiriman = in_array($currentUser->role, ['direktur', 'manager_purchasing', 'staff_purchasing']);
            @endphp
        </div>
    </div>



    {{-- Simplified PO Cards with Pengiriman --}}
    <div class="space-y-2">
        @forelse($groupedPengiriman as $poId => $pengirimanList)
            @php
                $po = $pengirimanList->first()->order;
                $purchasing = $pengirimanList->first()->purchasing;
            @endphp
            {{-- Simplified PO Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 border-l-4 border-l-blue-500 masuk-pengiriman-card po-card" 
                 data-no-po="{{ strtolower($po->po_number ?? '') }}" 
                 data-purchasing="{{ strtolower($purchasing->nama ?? '') }}" 
                 data-pengiriman="{{ $pengirimanList->count() }}">
                
                <div class="p-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
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
                                @php $totalHarga = $pengirimanList->sum('total_harga_kirim'); @endphp
                                @if($totalHarga > 0)
                                    <p class="text-sm font-semibold text-green-600">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Simplified Pengiriman List - Auto Show --}}
                <div class="border-t border-gray-200 pengiriman-list" id="pengiriman-list-po-{{ $poId }}">
                    <div class="p-3">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-truck text-blue-600 mr-2"></i>
                            Daftar Pengiriman ({{ $pengirimanList->count() }})
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
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full shrink-0"></div>
                                                    <span class="text-sm font-semibold text-gray-900">{{ $pengiriman->no_pengiriman }}</span>
                                                </div>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'menunggu verifikasi' => 'bg-blue-100 text-blue-800',
                                                        'berhasil' => 'bg-green-100 text-green-800',
                                                        'revisi' => 'bg-red-100 text-red-800',
                                                    ];
                                                    $statusColor = $statusColors[strtolower($pengiriman->status)] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColor }}">
                                                    @if(strtolower($pengiriman->status) == 'pending')
                                                        <i class="fas fa-clock mr-1"></i>
                                                    @elseif(strtolower($pengiriman->status) == 'menunggu verifikasi')
                                                        <i class="fas fa-hourglass-half mr-1"></i>
                                                    @elseif(strtolower($pengiriman->status) == 'berhasil')
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                    @elseif(strtolower($pengiriman->status) == 'revisi')
                                                        <i class="fas fa-undo mr-1"></i>
                                                    @endif
                                                    {{ ucfirst($pengiriman->status) }}
                                                </span>
                                            </div>
                                            
                                            {{-- Row 2: Detail Info --}}
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                                                @if($pengiriman->purchasing && $pengiriman->purchasing->nama)
                                                    <span class="flex items-center gap-1">
                                                        <i class="fas fa-user text-blue-600"></i>
                                                        <span class="truncate max-w-[150px]">{{ $pengiriman->purchasing->nama }}</span>
                                                    </span>
                                                @endif
                                                @if($pengiriman->tanggal_kirim)
                                                    <span class="flex items-center gap-1">
                                                        <i class="fas fa-calendar text-blue-600"></i>
                                                        {{ $pengiriman->tanggal_kirim->format('d M Y') }}
                                                    </span>
                                                @endif
                                                @if($pengiriman->total_qty_kirim && $pengiriman->total_qty_kirim > 0)
                                                    <span class="flex items-center gap-1">
                                                        <i class="fas fa-weight text-blue-600"></i>
                                                        {{ number_format($pengiriman->total_qty_kirim, 0, ',', '.') }} kg
                                                    </span>
                                                @endif
                                                @if($pengiriman->total_harga_kirim && $pengiriman->total_harga_kirim > 0)
                                                    <span class="flex items-center gap-1 font-medium text-green-700">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                        Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                           
                                        </div>
                                        
                                        {{-- Action Button Section --}}
                                        <div class="flex flex-col sm:flex-row gap-2 sm:w-auto w-full">
                                            @php
                                                // Check if user is PIC for this pengiriman (for Staff Purchasing)
                                                $isPIC = $pengiriman->purchasing_id === $currentUser->id;
                                                $canEdit = $canManagePengiriman && ($currentUser->role !== 'staff_purchasing' || $isPIC);
                                            @endphp
                                            
                                            @if($canEdit)
                                                {{-- Button for authorized users (Direktur, Manager, or PIC Staff) --}}
                                                <button onclick="openAksiModal({{ $pengiriman->id }}, '{{ $pengiriman->no_pengiriman }}', '{{ $pengiriman->status }}')" 
                                                        class="bg-green-500 hover:bg-green-600 active:bg-green-700 text-white px-3 py-2 rounded-lg text-xs font-medium flex items-center justify-center transition-all duration-200 w-full sm:w-auto whitespace-nowrap shadow-sm hover:shadow" 
                                                        title="Aksi Pengiriman">
                                                    <i class="fas fa-cog mr-1.5"></i>
                                                    Aksi
                                                </button>
                                            @elseif($canManagePengiriman && !$isPIC)
                                                {{-- Button for Staff Purchasing who are NOT PIC: View Only --}}
                                                <button onclick="openAksiModal({{ $pengiriman->id }}, '{{ $pengiriman->no_pengiriman }}', '{{ $pengiriman->status }}')" 
                                                        class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-medium flex items-center justify-center transition-all duration-200 w-full sm:w-auto whitespace-nowrap shadow-sm hover:shadow" 
                                                        title="Lihat Detail">
                                                    <i class="fas fa-eye mr-1.5"></i>
                                                    Lihat
                                                </button>
                                            @else
                                                {{-- Button for other roles: Disabled --}}
                                                <button disabled
                                                        class="bg-gray-400 text-gray-200 px-3 py-2 rounded-lg text-xs font-medium flex items-center justify-center cursor-not-allowed opacity-60 w-full sm:w-auto whitespace-nowrap" 
                                                        title="Akses Terbatas">
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
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Pengiriman Masuk</h3>
                    <p>Belum ada pengiriman dengan status pending.</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if(isset($pengirimanMasuk) && $pengirimanMasuk->hasPages())
            <div class="bg-white rounded-lg shadow-sm border p-4 mt-6">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    {{-- Results Info --}}
                    <div class="mb-3 sm:mb-0">
                        <p class="text-sm text-gray-700">
                            Menampilkan
                            <span class="font-medium">{{ $pengirimanMasuk->firstItem() }}</span>
                            sampai
                            <span class="font-medium">{{ $pengirimanMasuk->lastItem() }}</span>
                            dari
                            <span class="font-medium">{{ $pengirimanMasuk->total() }}</span>
                            Pengiriman Masuk
                        </p>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="flex items-center space-x-2">
                        {{-- Previous Page --}}
                        @if ($pengirimanMasuk->onFirstPage())
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </span>
                        @else
                            @php
                                $prevUrl = $pengirimanMasuk->previousPageUrl();
                                $prevUrlParts = parse_url($prevUrl);
                                parse_str($prevUrlParts['query'] ?? '', $prevParams);
                                $prevParams['tab'] = 'pengiriman-masuk';
                                // Preserve other filters
                                if (request('search_masuk')) $prevParams['search_masuk'] = request('search_masuk');
                                if (request('filter_purchasing')) $prevParams['filter_purchasing'] = request('filter_purchasing');
                                if (request('sort_date_masuk')) $prevParams['sort_date_masuk'] = request('sort_date_masuk');
                                $prevUrl = $prevUrlParts['path'] . '?' . http_build_query($prevParams);
                            @endphp
                            <a href="{{ $prevUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 transition-colors">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @if($pengirimanMasuk->lastPage() > 1)
                            <div class="hidden sm:flex items-center space-x-1">
                                @foreach ($pengirimanMasuk->getUrlRange(1, $pengirimanMasuk->lastPage()) as $page => $url)
                                    @php
                                        $urlParts = parse_url($url);
                                        parse_str($urlParts['query'] ?? '', $urlParams);
                                        $urlParams['tab'] = 'pengiriman-masuk';
                                        // Preserve other filters
                                        if (request('search_masuk')) $urlParams['search_masuk'] = request('search_masuk');
                                        if (request('filter_purchasing')) $urlParams['filter_purchasing'] = request('filter_purchasing');
                                        if (request('sort_date_masuk')) $urlParams['sort_date_masuk'] = request('sort_date_masuk');
                                        $pageUrl = $urlParts['path'] . '?' . http_build_query($urlParams);
                                    @endphp
                                    
                                    @if ($page == $pengirimanMasuk->currentPage())
                                        <span class="px-3 py-2 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-300 rounded-lg">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $pageUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 transition-colors">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Mobile Page Indicator --}}
                            <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                                {{ $pengirimanMasuk->currentPage() }} / {{ $pengirimanMasuk->lastPage() }}
                            </div>
                        @endif

                        {{-- Next Page --}}
                        @if ($pengirimanMasuk->hasMorePages())
                            @php
                                $nextUrl = $pengirimanMasuk->nextPageUrl();
                                $nextUrlParts = parse_url($nextUrl);
                                parse_str($nextUrlParts['query'] ?? '', $nextParams);
                                $nextParams['tab'] = 'pengiriman-masuk';
                                // Preserve other filters
                                if (request('search_masuk')) $nextParams['search_masuk'] = request('search_masuk');
                                if (request('filter_purchasing')) $nextParams['filter_purchasing'] = request('filter_purchasing');
                                if (request('sort_date_masuk')) $nextParams['sort_date_masuk'] = request('sort_date_masuk');
                                $nextUrl = $nextUrlParts['path'] . '?' . http_build_query($nextParams);
                            @endphp
                            <a href="{{ $nextUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 transition-colors">
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

{{-- Modal Aksi Pengiriman --}}
<div id="aksiModal" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 z-50 hidden" style="display: none; align-items: center; justify-content: center;">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-full overflow-y-auto border border-blue-600">
        <div class="p-2" id="aksiModalContent">
        </div>
    </div>
</div>

{{-- SweetAlert2 Library --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- JavaScript untuk Tab Pengiriman Masuk --}}
<script>
// ===== GLOBAL FUNCTIONS FOR CATATAN (Used in detail modal) =====
function updateCatatanCounter() {
    const textarea = document.getElementById('catatan_pengiriman');
    const counter = document.getElementById('catatanCounter');
    if (textarea && counter) {
        const currentLength = textarea.value.length;
        const maxLength = textarea.getAttribute('maxlength') || 500;
        counter.textContent = `${currentLength}/${maxLength} karakter`;
        
        // Change color based on usage
        if (currentLength > maxLength * 0.8) {
            counter.classList.add('text-red-500');
            counter.classList.remove('text-gray-500');
        } else {
            counter.classList.add('text-gray-500');
            counter.classList.remove('text-red-500');
        }
    }
}

function clearCatatan() {
    const textarea = document.getElementById('catatan_pengiriman');
    if (textarea) {
        textarea.value = '';
        updateCatatanCounter();
        textarea.focus();
    }
}

function resetToOriginal() {
    const textarea = document.getElementById('catatan_pengiriman');
    if (textarea) {
        const originalCatatan = textarea.getAttribute('data-original-catatan') || '';
        textarea.value = originalCatatan;
        updateCatatanCounter();
    }
}
// ===== END GLOBAL FUNCTIONS FOR CATATAN =====

// Handle Enter key press in search input
function handleSearchKeyPressMasuk(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        submitSearchMasuk();
    }
}

// Submit search to server
function submitSearchMasuk() {
    const currentParams = new URLSearchParams(window.location.search);
    const searchValue = document.getElementById('searchInputMasuk').value;
    
    // Preserve current tab
    currentParams.set('tab', 'pengiriman-masuk');
    
    // Update search parameter
    if (searchValue) {
        currentParams.set('search_masuk', searchValue);
    } else {
        currentParams.delete('search_masuk');
    }
    
    // Reset to first page when searching
    currentParams.delete('masuk_page');
    
    // Navigate to new URL
    window.location.href = '/procurement/pengiriman?' + currentParams.toString();
}

// Apply filters function for server-side filtering
function applyFiltersMasuk() {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Get filter values
    const searchValue = document.getElementById('searchInputMasuk').value;
    const filterPurchasing = document.getElementById('filterPurchasing').value;
    const sortDate = document.getElementById('sortDateMasuk').value;
    
    // Preserve current tab
    currentParams.set('tab', 'pengiriman-masuk');
    
    // Update parameters
    if (searchValue) currentParams.set('search_masuk', searchValue);
    else currentParams.delete('search_masuk');
    
    if (filterPurchasing) currentParams.set('filter_purchasing', filterPurchasing);
    else currentParams.delete('filter_purchasing');
    
    if (sortDate) currentParams.set('sort_date_masuk', sortDate);
    else currentParams.delete('sort_date_masuk');
    
    // Reset to first page when filtering
    currentParams.delete('masuk_page');
    
    const newUrl = '/procurement/pengiriman?' + currentParams.toString();
    
    // Navigate to new URL
    window.location.href = newUrl;
}

// Update active filters display
function updateActiveFiltersMasuk() {
    const activeFiltersContainer = document.getElementById('activeFiltersMasuk');
    const searchValue = document.getElementById('searchInputMasuk').value;
    const filterPurchasing = document.getElementById('filterPurchasing').value;
    const sortDate = document.getElementById('sortDateMasuk').value;
    
    let hasActiveFilters = false;
    let filtersHTML = '<span class="text-xs sm:text-sm font-bold text-blue-700">Filter aktif:</span>';
    
    if (searchValue) {
        filtersHTML += `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs active-filter-tag">Pencarian: ${searchValue}</span>`;
        hasActiveFilters = true;
    }
    
    if (filterPurchasing) {
        const purchasingSelect = document.getElementById('filterPurchasing');
        const purchasingName = purchasingSelect.options[purchasingSelect.selectedIndex].text;
        filtersHTML += `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs active-filter-tag">Purchasing: ${purchasingName}</span>`;
        hasActiveFilters = true;
    }
    
    if (sortDate) {
        const dateLabels = {
            'newest': 'Terbaru',
            'oldest': 'Terlama'
        };
        filtersHTML += `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs active-filter-tag">Urutkan: ${dateLabels[sortDate]}</span>`;
        hasActiveFilters = true;
    }
    
    if (hasActiveFilters) {
        filtersHTML += `<button onclick="clearAllFiltersMasuk()" class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs hover:bg-red-200 transition-colors ml-2">
            <i class="fas fa-times mr-1"></i>Hapus Semua
        </button>`;
        activeFiltersContainer.innerHTML = filtersHTML;
        activeFiltersContainer.style.display = 'flex';
    } else {
        activeFiltersContainer.style.display = 'none';
    }
}

// Clear all filters
function clearAllFiltersMasuk() {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Keep only the tab parameter
    const newParams = new URLSearchParams();
    newParams.set('tab', 'pengiriman-masuk');
    
    window.location.href = '/procurement/pengiriman?' + newParams.toString();
}

function showPengirimanDetail(id) {
    // Open detail modal
    fetch(`/procurement/pengiriman/${id}/detail`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show detail modal with data
                showDetailModal(data.pengiriman);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat detail pengiriman');
        });
}

function konfirmasiKirim(id) {
    if (confirm('Apakah Anda yakin ingin mengonfirmasi pengiriman ini? Status akan berubah menjadi "Menunggu Verifikasi".')) {
        updatePengirimanStatus(id, 'menunggu_verifikasi');
    }
}

function batalPengiriman(id) {
    const alasan = prompt('Masukkan alasan pembatalan:');
    if (alasan && alasan.trim()) {
        updatePengirimanStatus(id, 'gagal', alasan);
    } else if (alasan !== null) {
        alert('Alasan pembatalan harus diisi');
    }
}

function updatePengirimanStatus(id, status, catatan = null) {
    const payload = { status };
    if (catatan) {
        payload.catatan = catatan;
    }
    
    fetch(`/procurement/pengiriman/${id}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh current tab
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.set('tab', 'pengiriman-masuk');
            window.location.href = '/procurement/pengiriman?' + currentParams.toString();
        } else {
            alert('Gagal mengupdate status pengiriman');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate status');
    });
}

// Global variables untuk menyimpan state
window.currentPengirimanId = null;
window.currentNoKirim = null;
window.currentStatus = null;
window.globalSubmissionData = null;

// Open Aksi Modal
function openAksiModal(pengirimanId, noPengiriman, status) {
    // Simpan informasi pengiriman ke global variables
    window.currentPengirimanId = pengirimanId;
    window.currentNoKirim = noPengiriman;
    window.currentStatus = status;
    
    const modal = document.getElementById('aksiModal');
    const modalContent = document.getElementById('aksiModalContent');
    
    // Show loading
    modalContent.innerHTML = `
        <div class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
            <span class="ml-2 text-gray-600">Loading...</span>
        </div>
    `;
    
    // Show modal
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    
    // Load content from detail.blade.php
    fetch(`/procurement/pengiriman/${pengirimanId}/aksi-modal`)
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
            
            // Initialize modal setelah content loaded
            if (typeof initializePengirimanModal === 'function') {
                initializePengirimanModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = `
                <div class="text-center py-8">
                    <div class="text-red-500 mb-4">
                        <i class="fas fa-exclamation-triangle text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Data</h3>
                    <p class="text-gray-600 mb-4">Terjadi kesalahan saat memuat detail pengiriman</p>
                    <button onclick="closeAksiModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        Tutup
                    </button>
                </div>
            `;
        });
}

// Close Aksi Modal
function closeAksiModal() {
    const modal = document.getElementById('aksiModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('aksiModal');
    if (event.target === modal) {
        closeAksiModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAksiModal();
    }
});

// =================== GLOBAL FUNCTIONS FOR MODAL ===================
// These functions need to be in global scope because they are called from modal HTML loaded via AJAX

// Open batal modal (global function)
function openBatalModal() {
    const pengirimanId = window.currentPengirimanId;
    
    if (!pengirimanId) {
        console.error('Pengiriman ID not found');
        alert('ID pengiriman tidak ditemukan');
        return;
    }
    
    // Load batal modal content with pengiriman_id parameter
    fetch(`/procurement/pengiriman/batal-modal?pengiriman_id=${pengirimanId}`)
    .then(response => response.text())
    .then(html => {
        // Create and show batal modal
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = html;
        document.body.appendChild(modalContainer);
        
        // Find and show the modal
        const batalModal = modalContainer.querySelector('#batalModal');
        if (batalModal) {
            // Make sure modal is visible
            batalModal.style.display = 'flex';
        }
    })
    .catch(error => {
        console.error('Error loading batal modal:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: 'Gagal memuat modal pembatalan: ' + error.message,
                icon: 'error',
                confirmButtonColor: '#EF4444'
            });
        } else {
            alert('Gagal memuat modal pembatalan: ' + error.message);
        }
    });
}

// Update hari kirim berdasarkan tanggal (global function)
function updateHariKirim() {
    const deliveryDate = document.getElementById('tanggal_kirim');
    const hariKirimField = document.getElementById('hari_kirim');
    
    if (!deliveryDate || !hariKirimField) {
        return;
    }
    
    if (deliveryDate.value) {
        const targetDate = new Date(deliveryDate.value);
        
        // Array nama hari dalam bahasa Indonesia
        const hariIndonesia = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const namaHari = hariIndonesia[targetDate.getDay()];
        
        hariKirimField.value = namaHari;
    } else {
        hariKirimField.value = '';
    }
}

// ===================================================================
// PENGIRIMAN MODAL FUNCTIONS - Global functions for detail modal
// ===================================================================

// Hitung subtotal untuk setiap row
function calculateSubtotal(index) {
    const qtyInput = document.querySelector(`input[name="details[${index}][qty_kirim]"]`);
    const hargaInput = document.querySelector(`input[name="details[${index}][harga_satuan]"]`);
    const totalInput = document.querySelector(`input[name="details[${index}][total_harga]"]`);
    const rowElement = document.querySelector(`.detail-item[data-index="${index}"]`);
    
    if (qtyInput && hargaInput && totalInput && rowElement) {
        const qty = parseFloat(qtyInput.value) || 0;
        const hargaBeli = parseFloat(hargaInput.value) || 0;
        const hargaJual = parseFloat(rowElement.getAttribute('data-harga-jual')) || 0;
        
        // Calculate total harga beli
        const totalHargaBeli = qty * hargaBeli;
        
        // Update total input
        totalInput.value = totalHargaBeli.toFixed(2);
        
        // Update display di bawahnya
        const totalDisplay = totalInput.parentElement.querySelector('.text-xs.text-gray-500');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHargaBeli);
        }
        
        // Update totals keseluruhan
        updateTotals();
    }
}

// Update total keseluruhan (with margin calculation)
function updateTotals() {
    let totalQty = 0;
    let totalHargaBeli = 0;
    let totalHargaJual = 0;
    
    const detailItems = document.querySelectorAll('.detail-item');
    
    detailItems.forEach((item) => {
        const qtyInput = item.querySelector('input[name*="[qty_kirim]"]');
        const hargaBeliInput = item.querySelector('input[name*="[harga_satuan]"]');
        const hargaJualAttr = item.getAttribute('data-harga-jual');
        
        const qty = parseFloat(qtyInput?.value) || 0;
        const hargaBeli = parseFloat(hargaBeliInput?.value) || 0;
        const hargaJual = parseFloat(hargaJualAttr) || 0;
        
        totalQty += qty;
        totalHargaBeli += (qty * hargaBeli);
        totalHargaJual += (qty * hargaJual);
    });
    
    // Calculate total margin
    const totalMargin = totalHargaJual - totalHargaBeli;
    const marginPercentage = totalHargaBeli > 0 ? ((totalMargin / totalHargaBeli) * 100) : 0;
    
    // Update tampilan summary
    const totalQtyEl = document.getElementById('totalQtyKirim');
    const totalHargaBeliEl = document.getElementById('totalHargaBeli');
    const totalHargaJualEl = document.getElementById('totalHargaJual');
    const totalMarginEl = document.getElementById('totalMargin');
    const marginPercentageEl = document.getElementById('marginPercentage');
    
    if (totalQtyEl) {
        totalQtyEl.textContent = new Intl.NumberFormat('id-ID', { 
            minimumFractionDigits: 0, 
            maximumFractionDigits: 2 
        }).format(totalQty) + ' kg';
    }
    
    if (totalHargaBeliEl) {
        totalHargaBeliEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHargaBeli);
    }
    
    if (totalHargaJualEl) {
        totalHargaJualEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHargaJual);
    }
    
    if (totalMarginEl) {
        totalMarginEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalMargin);
        // Change color based on margin
        if (totalMargin >= 0) {
            totalMarginEl.classList.remove('text-red-600');
            totalMarginEl.classList.add('text-green-600');
        } else {
            totalMarginEl.classList.remove('text-green-600');
            totalMarginEl.classList.add('text-red-600');
        }
    }
    
    if (marginPercentageEl) {
        marginPercentageEl.textContent = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(marginPercentage) + '%';
        // Change color based on margin
        if (totalMargin >= 0) {
            marginPercentageEl.classList.remove('text-red-500');
            marginPercentageEl.classList.add('text-green-500');
        } else {
            marginPercentageEl.classList.remove('text-green-500');
            marginPercentageEl.classList.add('text-red-500');
        }
    }
    
    // Update hidden dan display inputs (for backward compatibility)
    const totalQtyHidden = document.getElementById('total_qty_kirim');
    const totalHargaHidden = document.getElementById('total_harga_kirim');
    const totalQtyDisplay = document.getElementById('total_qty_kirim_display');
    const totalHargaDisplay = document.getElementById('total_harga_kirim_display');
    
    if (totalQtyHidden) totalQtyHidden.value = totalQty;
    if (totalHargaHidden) totalHargaHidden.value = totalHargaBeli;
    if (totalQtyDisplay) totalQtyDisplay.value = new Intl.NumberFormat('id-ID').format(totalQty) + ' kg';
    if (totalHargaDisplay) totalHargaDisplay.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHargaBeli);
}

// Initialize pengiriman modal (called after AJAX load)
window.initializePengirimanModal = function() {
    // Calculate initial subtotals
    document.querySelectorAll('.detail-item').forEach((item) => {
        const index = item.getAttribute('data-index');
        if (index !== null && index !== undefined) {
            calculateSubtotal(parseInt(index));
        }
    });
    
    // Initial hari kirim update
    updateHariKirim();
    
    // Update overall totals
    updateTotals();
    
    // Initialize catatan counter
    const catatanTextarea = document.getElementById('catatan_pengiriman');
    if (catatanTextarea) {
        // Set up event listener untuk real-time counter update
        catatanTextarea.addEventListener('input', updateCatatanCounter);
        catatanTextarea.addEventListener('keyup', updateCatatanCounter);
        
        // Initialize counter
        if (typeof updateCatatanCounter === 'function') {
            updateCatatanCounter();
        }
        
        // Auto-resize textarea based on content
        catatanTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
}

// ===================================================================
// END OF PENGIRIMAN MODAL FUNCTIONS
// ===================================================================

// Show submit confirmation modal (global function)
function showSubmitModal(formData) {
    const pengirimanId = formData.get('pengiriman_id');
    
    // Store formData immediately
    window.globalSubmissionData = formData;
    
    // Load submit modal content
    fetch(`/procurement/pengiriman/submit-modal?pengiriman_id=${pengirimanId}`)
        .then(response => response.text())
        .then(html => {
            // Close any existing Swal loading
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            // Create and show submit modal
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = html;
            document.body.appendChild(modalContainer);
            
            // Find and populate the modal immediately
            const submitModal = modalContainer.querySelector('#submitModal');
            if (submitModal) {
                // Populate data directly
                populateModalSummary(formData, submitModal);
                
                // Make sure modal is visible
                submitModal.style.display = 'flex';
            }
            
            // Close current modal after successful modal creation
            closeAksiModal();
        })
        .catch(error => {
            console.error('Error loading submit modal:', error);
            // Close loading and show error
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal memuat modal konfirmasi: ' + error.message,
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
            } else {
                alert('Gagal memuat modal konfirmasi');
            }
        });
}

// Populate modal summary (global function)  
function populateModalSummary(formData, modalElement) {
    // Basic info
    const setPengiriman = modalElement.querySelector('#summary-no-pengiriman');
    if (setPengiriman) {
        setPengiriman.textContent = formData.get('no_pengiriman') || '-';
    }
    
    const tanggalKirim = formData.get('tanggal_kirim');
    if (tanggalKirim) {
        const date = new Date(tanggalKirim + 'T00:00:00');
        const setTanggal = modalElement.querySelector('#summary-tanggal-kirim');
        if (setTanggal) {
            setTanggal.textContent = date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long', 
                year: 'numeric'
            });
        }
    }
    
    const setHari = modalElement.querySelector('#summary-hari-kirim');
    if (setHari) {
        setHari.textContent = formData.get('hari_kirim') || '-';
    }
    
    const setCatatan = modalElement.querySelector('#summary-catatan');
    if (setCatatan) {
        setCatatan.textContent = formData.get('catatan') || 'Tidak ada catatan';
    }
    
    // Detail barang
    const detailContainer = modalElement.querySelector('#summary-detail-barang');
    if (detailContainer) {
        detailContainer.innerHTML = '';
        
        let totalItems = 0;
        let totalQty = 0;
        let totalHarga = 0;
        
        // Get detail from current form in aksi modal
        const detailRows = document.querySelectorAll('.detail-item');
        
        detailRows.forEach((row, index) => {
            const qtyInput = row.querySelector('input[name*="[qty_kirim]"]');
            const hargaInput = row.querySelector('input[name*="[harga_satuan]"]');
            const totalInput = row.querySelector('input[name*="[total_harga]"]');
            const bahanBakuEl = row.querySelector('.text-sm.font-medium.text-gray-900');
            
            const qty = parseFloat(qtyInput?.value) || 0;
            const harga = parseFloat(hargaInput?.value) || 0;
            const total = parseFloat(totalInput?.value) || 0;
            const bahanBaku = bahanBakuEl?.textContent.trim() || 'Unknown';
            
            if (qty > 0) {
                totalItems++;
                totalQty += qty;
                totalHarga += total;
                
                const rowHtml = `
                    <tr class="border-b">
                        <td class="px-3 py-2 text-sm">${bahanBaku}</td>
                        <td class="px-3 py-2 text-sm">${qty.toLocaleString('id-ID')} kg</td>
                        <td class="px-3 py-2 text-sm">Rp ${harga.toLocaleString('id-ID')}</td>
                        <td class="px-3 py-2 text-sm font-semibold">Rp ${total.toLocaleString('id-ID')}</td>
                    </tr>
                `;
                detailContainer.insertAdjacentHTML('beforeend', rowHtml);
            }
        });
        
        // Update totals
        const setTotalItem = modalElement.querySelector('#summary-total-item');
        if (setTotalItem) setTotalItem.textContent = totalItems + ' item';
        
        const setTotalQty = modalElement.querySelector('#summary-total-qty');
        if (setTotalQty) setTotalQty.textContent = totalQty.toLocaleString('id-ID') + ' kg';
        
        const setTotalHarga = modalElement.querySelector('#summary-total-harga');
        if (setTotalHarga) setTotalHarga.textContent = 'Rp ' + totalHarga.toLocaleString('id-ID');
    }
    
    // Store formData for submission
    window.globalSubmissionData = formData;
}

// Close submit modal
function closeSubmitModal() {
    const modal = document.getElementById('submitModal');
    if (modal) {
        modal.remove();
    }
    
    // Reopen aksi modal dengan data yang sudah diisi
    if (typeof openAksiModal === 'function') {
        // Get pengiriman ID from global variable or modal data
        const pengirimanId = window.currentPengirimanId || 1;
        const noKirim = window.currentNoKirim || '';
        const status = window.currentStatus || 'pending';
        openAksiModal(pengirimanId, noKirim, status);
    } else {
        // Fallback untuk reload page jika parent function tidak ada
        location.reload();
    }
}

// Confirm submit
function confirmSubmit() {
    const dataToSubmit = window.globalSubmissionData;
    
    if (!dataToSubmit) {
        alert('Data tidak tersedia. Silakan coba lagi.');
        return;
    }
    
    // Add timestamp to catatan
    const currentCatatan = dataToSubmit.get('catatan') || '';
    const timestamp = new Date().toLocaleString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
    
    const updatedCatatan = currentCatatan 
        ? `${currentCatatan}\n\n[Diajukan pada: ${timestamp}]`
        : `[Diajukan pada: ${timestamp}]`;
    
    dataToSubmit.set('catatan', updatedCatatan);
    
    // Submit to backend
    fetch('/procurement/pengiriman/submit', {
        method: 'POST',
        body: dataToSubmit,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal first
            const submitModal = document.getElementById('submitModal');
            if (submitModal) submitModal.remove();
            
            // Show success notification (similar to forecast)
            const successNotification = document.createElement('div');
            successNotification.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2';
            successNotification.style.animation = 'slideInRight 0.5s ease-out, fadeOut 0.5s ease-in 4.5s forwards';
            successNotification.innerHTML = `
                <i class="fas fa-check-circle text-lg"></i>
                <span class="font-medium">Pengiriman ${data.no_pengiriman || 'berhasil'} diajukan untuk verifikasi!</span>
                <button onclick="this.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            `;
            document.body.appendChild(successNotification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (successNotification.parentElement) {
                    successNotification.remove();
                }
            }, 5000);
            
            // Reload page after short delay
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            // Error
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Gagal mengajukan pengiriman',
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
            } else {
                alert(data.message || 'Gagal mengajukan pengiriman');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan pada sistem',
                icon: 'error',
                confirmButtonColor: '#EF4444'
            });
        } else {
            alert('Terjadi kesalahan pada sistem');
        }
    });
}

// Submit pembatalan pengiriman (global function)
function submitBatalPengiriman() {
    const form = document.getElementById('batalForm');
    if (!form) {
        console.error('Form batalForm not found');
        alert('Form tidak ditemukan');
        return;
    }
    
    const formData = new FormData(form);
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Show confirmation
    Swal.fire({
        title: 'Konfirmasi Pembatalan',
        text: 'Apakah Anda yakin ingin membatalkan pengiriman ini? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Membatalkan pengiriman...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form via AJAX
            fetch('/procurement/pengiriman/batal', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    // Success notification with animation (reuse existing function)
                    showBatalSuccessNotification('Pengiriman berhasil dibatalkan!', data.no_pengiriman);
                    
                    // Close modal and reload page
                    closeBatalModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Error notification
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Terjadi kesalahan saat membatalkan pengiriman',
                        icon: 'error',
                        confirmButtonColor: '#EF4444'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.close();
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan: ' + error.message,
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
            });
        }
    });
}

// Close batal modal (global function)
function closeBatalModal() {
    const modal = document.getElementById('batalModal');
    if (modal) {
        modal.remove();
    }
}

// Success notification for batal (global function)
function showBatalSuccessNotification(message, noPengiriman) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg z-[9999] transform translate-x-full transition-transform duration-300 ease-in-out';
    notification.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas fa-check-circle text-xl"></i>
            <div>
                <div class="font-semibold">${message}</div>
                ${noPengiriman ? `<div class="text-sm opacity-90">No. Pengiriman: ${noPengiriman}</div>` : ''}
            </div>
        </div>
    `;
    
    // Add to DOM
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto dismiss after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Submit pengiriman (global function)
function submitPengiriman() {
    const form = document.getElementById('pengirimanForm');
    if (!form) {
        alert('Form tidak ditemukan');
        return;
    }
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Check if detail exists
    const detailItems = document.querySelectorAll('.detail-item');
    if (detailItems.length === 0) {
        alert('Tidak ada detail barang untuk dikirim');
        return;
    }
    
    // Validate all qty inputs are filled and > 0
    let hasValidQty = false;
    for (let item of detailItems) {
        const qtyInput = item.querySelector('input[name*="[qty_kirim]"]');
        if (qtyInput && parseFloat(qtyInput.value) > 0) {
            hasValidQty = true;
            break;
        }
    }
    
    if (!hasValidQty) {
        alert('Minimal satu barang harus memiliki qty kirim > 0');
        return;
    }
    
    // Create FormData
    const formData = new FormData(form);
    
    // Add totals to form data
    const totalQty = Array.from(detailItems).reduce((sum, item) => {
        const qtyInput = item.querySelector('input[name*="[qty_kirim]"]');
        return sum + (parseFloat(qtyInput.value) || 0);
    }, 0);
    
    const totalHarga = Array.from(detailItems).reduce((sum, item) => {
        const totalInput = item.querySelector('input[name*="[total_harga]"]');
        return sum + (parseFloat(totalInput.value) || 0);
    }, 0);
    
    formData.append('total_qty_kirim', totalQty);
    formData.append('total_harga_kirim', totalHarga);
    
    // Show loading
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Mengajukan Verifikasi...',
            text: 'Sedang memproses pengiriman Anda',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    // Submit pengiriman
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'Pengiriman berhasil diajukan untuk verifikasi',
                    icon: 'success',
                    confirmButtonColor: '#10B981'
                }).then(() => {
                    // Close modal and reload
                    closeAksiModal();
                    location.reload();
                });
            } else {
                alert(data.message || 'Pengiriman berhasil diajukan untuk verifikasi');
                closeAksiModal();
                location.reload();
            }
        } else {
            console.error('Failed to submit pengiriman:', data.message);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Gagal mengajukan verifikasi',
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
            } else {
                alert(data.message || 'Gagal mengajukan verifikasi');
            }
        }
    })
    .catch(error => {
        console.error('Error submitting pengiriman:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan saat mengajukan verifikasi',
                icon: 'error',
                confirmButtonColor: '#EF4444'
            });
        } else {
            alert('Terjadi kesalahan saat mengajukan verifikasi');
        }
    });
}
</script>

<style>
/* Custom styles for pengiriman masuk page - Blue theme version of pending-forecasting */
.masuk-pengiriman-card {
    transition: box-shadow 0.3s ease;
}

.masuk-pengiriman-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.toggle-pengiriman-btn {
    transition: background-color 0.2s ease;
}

.pengiriman-icon {
    transition: transform 0.2s ease-in-out;
}

/* Search input styles */
.search-input-masuk {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.search-input-masuk:focus {
    background: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), 0 4px 12px rgba(59, 130, 246, 0.15);
    transform: translateY(-1px);
}

/* Filter section styles */
.filter-grid > div {
    transition: all 0.3s ease;
}

.filter-grid > div:hover {
    transform: translateY(-1px);
}

.filter-grid select,
.filter-grid input[type="date"] {
    transition: all 0.3s ease;
}

.filter-grid select:focus,
.filter-grid input[type="date"]:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Button styles */
button.bg-blue-600:hover,
button.bg-blue-500:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

button.bg-red-500:hover {
    background-color: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Active filter tags */
.active-filter-tag {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border: 1px solid rgba(59, 130, 246, 0.2);
    transition: all 0.3s ease;
}

.active-filter-tag:hover {
    background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
    transform: scale(1.05);
}

/* Card styles similar to pending-forecasting */
.po-card {
    transition: box-shadow 0.3s ease;
}

.po-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Modal styles */
#aksiModal {
    backdrop-filter: blur(4px);
}

#aksiModal .bg-white {
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Summary stats */
.summary-stat {
    transition: all 0.3s ease;
    background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, rgba(248,250,252,0.9) 100%);
    border: 1px solid rgba(59, 130, 246, 0.1);
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

.summary-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,1) 100%);
}

/* Loading animation */
@keyframes pulseBlue {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.7;
        transform: scale(1.05);
    }
}

.loading-pulse {
    animation: pulseBlue 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Status badge styles */
.status-badge {
    transition: all 0.3s ease;
    border-radius: 20px;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.status-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Action button styles */
.action-btn {
    transition: all 0.3s ease;
    border-radius: 8px;
    font-weight: 500;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-btn-blue:hover {
    background-color: #1d4ed8;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.action-btn-green:hover {
    background-color: #059669;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.action-btn-red:hover {
    background-color: #dc2626;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.action-btn-yellow:hover {
    background-color: #d97706;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

/* Empty state styles */
.empty-state {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px dashed rgba(59, 130, 246, 0.3);
    border-radius: 16px;
    transition: all 0.3s ease;
}

.empty-state:hover {
    border-color: rgba(59, 130, 246, 0.5);
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .masuk-pengiriman-card {
        margin-bottom: 0.5rem;
        padding: 1rem;
    }
    
    .filter-grid {
        gap: 0.5rem;
    }
    
    .summary-stat {
        padding: 0.5rem;
        margin: 0.25rem;
    }
    
    table {
        font-size: 0.875rem;
    }
    
    .action-btn {
        padding: 0.5rem;
        font-size: 0.75rem;
    }
}

/* Animation for when content loads */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

/* Pagination styles */
.pagination-custom nav {
    display: flex;
    justify-content: center;
    align-items: center;
    space-x: 0.25rem;
}

.pagination-custom nav span,
.pagination-custom nav a {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    text-decoration: none;
    background-color: white;
    border: 1px solid #d1d5db;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.pagination-custom nav a:hover {
    background-color: #eff6ff;
    border-color: #3b82f6;
    color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
}

.pagination-custom nav span[aria-current="page"] {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-color: #1d4ed8;
    color: white;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.pagination-custom nav span[aria-disabled="true"] {
    background-color: #f9fafb;
    border-color: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

@media (max-width: 640px) {
    .pagination-custom nav span,
    .pagination-custom nav a {
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
        margin: 0 0.0625rem;
    }
}

/* Horizontal Filter Layout Enhancements */
@media (min-width: 640px) {
    .filter-horizontal {
        align-items: flex-end;
    }
    
    .filter-horizontal > div:last-child {
        margin-left: auto;
    }
}

/* Blue theme override for any remaining yellow elements */
.bg-yellow-50 { background-color: #eff6ff !important; }
.bg-yellow-100 { background-color: #dbeafe !important; }
.text-yellow-600 { color: #2563eb !important; }
.text-yellow-700 { color: #1d4ed8 !important; }
.border-yellow-200 { border-color: #bfdbfe !important; }
.ring-yellow-200 { --tw-ring-color: #bfdbfe !important; }

/* Mobile responsive filter grid */
@media (max-width: 640px) {
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

/* Success notification animations */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
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
