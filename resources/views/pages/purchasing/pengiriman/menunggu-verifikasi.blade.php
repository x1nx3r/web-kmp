{{-- Menunggu Verifikasi Tab Content --}}
<div class="space-y-6 fade-in-up">
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
                        Pencarian Menunggu Verifikasi
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="searchInputVerifikasi" 
                               name="search_verifikasi"
                               value="{{ request('search_verifikasi') }}"
                               placeholder="Cari No. PO atau nama purchasing..." 
                               class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-verifikasi"
                               onkeyup="debounceSearchVerifikasi()"
                               onchange="submitSearchVerifikasi()">
                        <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                            <div class="w-3 h-3 sm:w-6 sm:h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-yellow-500 text-xs sm:text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Section - Horizontal Layout --}}
            <div class="rounded-lg sm:rounded-xl p-2 sm:p-3">
                <h3 class="flex items-center text-xs sm:text-sm font-bold text-yellow-700 mb-2 sm:mb-3">
                    <div class="w-4 h-4 sm:w-5 sm:h-5 bg-yellow-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                        <i class="fas fa-filter text-white text-xs"></i>
                    </div>
                    Filter & Urutan
                </h3>
                
                {{-- Horizontal Filter Layout --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-2 sm:gap-4">
                    {{-- Filter by Purchasing --}}
                    <div class="w-full sm:w-64 flex-shrink-0">
                        <label class="block text-xs font-medium text-yellow-600 mb-1">
                            <i class="fas fa-user mr-1 text-yellow-500 text-xs"></i>
                            PIC Purchasing
                        </label>
                        <select id="filterPurchasingVerifikasi" name="filter_purchasing_verifikasi" class="w-full py-2 px-3 border border-yellow-200 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-sm" onchange="applyFiltersVerifikasi()">
                            <option value="">Semua Purchasing</option>
                            @php
                                // Debug: check purchasing data
                                $purchasingOptions = collect();
                                foreach($menungguVerifikasi->items() ?? [] as $item) {
                                    if($item->purchasing && $item->purchasing->nama) {
                                        $purchasingOptions->put($item->purchasing->id, $item->purchasing->nama);
                                    }
                                }
                                $purchasingOptions = $purchasingOptions->unique()->filter();
                            @endphp
                            @foreach($purchasingOptions as $id => $name)
                                <option value="{{ $id }}" {{ request('filter_purchasing_verifikasi') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort by Date --}}
                    <div class="w-full sm:w-48 flex-shrink-0">
                        <label class="block text-xs font-medium text-yellow-600 mb-1">
                            <i class="fas fa-sort mr-1 text-yellow-500 text-xs"></i>
                            Urutkan
                        </label>
                        <select id="sortDateVerifikasi" name="sort_date_verifikasi" class="w-full py-2 px-3 border border-yellow-200 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 bg-white transition-all duration-200 text-sm" onchange="applyFiltersVerifikasi()">
                            <option value="">Default</option>
                            <option value="newest" {{ request('sort_date_verifikasi') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort_date_verifikasi') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>

                    {{-- Clear Filter Button --}}
                    <div class="w-full sm:w-auto sm:ml-auto flex-shrink-0">
                        <button onclick="clearAllFiltersVerifikasi()" class="w-full sm:w-auto px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 text-sm font-medium whitespace-nowrap">
                            <i class="fas fa-times mr-1"></i>
                            Hapus Filter
                        </button>
                    </div>
                </div>
            </div>

            {{-- Active Filters Display --}}
            <div id="activeFiltersVerifikasi" class="flex flex-wrap gap-2" style="display: none;">
                <span class="text-xs sm:text-sm font-bold text-yellow-700">Filter aktif:</span>
            </div>
        </div>
    </div>

    {{-- Simplified Header Section --}}
    <div class="flex items-center justify-between mb-4 bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-white text-sm"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Menunggu Verifikasi</h3>
        </div>
        <span class="text-sm text-yellow-600 font-medium">{{ $menungguVerifikasi->total() }} pengiriman</span>
    </div>

    {{-- Content --}}
    @php
        // Group pengiriman by purchase_order_id
        $groupedPengiriman = $menungguVerifikasi->items() ? collect($menungguVerifikasi->items())->groupBy('purchase_order_id') : collect();
    @endphp

    <div class="space-y-2">
        @forelse($groupedPengiriman as $poId => $pengirimanList)
            @php
                $po = $pengirimanList->first()->purchaseOrder;
                $purchasing = $pengirimanList->first()->purchasing;
            @endphp
            {{-- Simplified PO Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 border-l-4 border-l-yellow-500 verifikasi-pengiriman-card po-card" 
                 data-no-po="{{ strtolower($po->no_po ?? '') }}" 
                 data-purchasing="{{ strtolower($purchasing->nama ?? '') }}" 
                 data-pengiriman="{{ $pengirimanList->count() }}">
                
                <div class="p-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-alt text-white text-xs"></i>
                            </div>
                            <div>
                                @if($po && $po->no_po)
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $po->no_po }}</h3>
                                @endif
                                @if($po && $po->klien && $po->klien->nama)
                                    <p class="text-xs text-gray-500">{{ $po->klien->nama }}{{ $po->klien->cabang ? ' - ' . $po->klien->cabang : '' }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $pengirimanList->count() }} pengiriman</p>
                                @php $totalHarga = $pengirimanList->sum('total_harga_kirim'); @endphp
                                @if($totalHarga > 0)
                                    <p class="text-sm font-semibold text-yellow-600">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                @endif
                            </div>
                            <button type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs flex items-center" onclick="togglePengirimanListVerifikasi('po-{{ $poId }}')">
                                <i class="fas fa-chevron-right pengiriman-icon" id="icon-po-{{ $poId }}"></i>
                                <span class="ml-1" id="text-po-{{ $poId }}">Tampilkan</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Simplified Pengiriman List --}}
                <div class="border-t border-gray-200 pengiriman-list" id="pengiriman-list-po-{{ $poId }}" style="display: none;">
                    <div class="p-3">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-truck text-yellow-600 mr-2"></i>
                            Daftar Pengiriman Menunggu Verifikasi ({{ $pengirimanList->count() }})
                        </h4>
                        
                        <div class="space-y-2">
                            @foreach($pengirimanList as $pengiriman)
                                <div class="bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition-colors" 
                                     data-pengiriman-no="{{ strtolower($pengiriman->no_pengiriman ?? '') }}"
                                     data-purchasing="{{ strtolower($purchasing->nama ?? '') }}"
                                     data-qty="{{ $pengiriman->total_qty_kirim ?? 0 }}"
                                     data-amount="{{ $pengiriman->total_harga_kirim ?? 0 }}"
                                     data-date="{{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('Y-m-d') : '' }}"
                                     data-status="{{ $pengiriman->status ?? '' }}">
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-4 h-4 bg-yellow-500 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-clock text-white text-xs"></i>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">{{ $pengiriman->no_pengiriman }}</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Menunggu Verifikasi
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500">
                                                @if($purchasing && $purchasing->nama)
                                                    <span><i class="fas fa-user mr-1"></i>{{ $purchasing->nama }}</span>
                                                @endif
                                                @if($pengiriman->tanggal_kirim)
                                                    <span><i class="fas fa-calendar mr-1"></i>{{ $pengiriman->tanggal_kirim->format('d M Y') }}</span>
                                                @endif
                                                @if($pengiriman->total_qty_kirim && $pengiriman->total_qty_kirim > 0)
                                                    <span><i class="fas fa-weight mr-1"></i>{{ number_format($pengiriman->total_qty_kirim, 0, ',', '.') }} kg</span>
                                                @endif
                                                @if($pengiriman->total_harga_kirim && $pengiriman->total_harga_kirim > 0)
                                                    <span><i class="fas fa-money-bill mr-1"></i>Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="openAksiVerifikasiModal({{ $pengiriman->id }}, '{{ $pengiriman->no_pengiriman }}', '{{ $pengiriman->status }}')" 
                                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs flex items-center transition-all duration-200" 
                                                    title="Aksi Verifikasi">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Aksi Verifikasi
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($pengiriman->catatan)
                                        <div class="mt-2 pt-2 border-t border-yellow-200">
                                            <p class="text-xs text-gray-600"><i class="fas fa-sticky-note mr-1"></i>{{ $pengiriman->catatan }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>    
                </div>
            </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-clock text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Pengiriman Menunggu Verifikasi</h3>
                    <p>Belum ada pengiriman dengan status menunggu verifikasi.</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if(isset($menungguVerifikasi) && $menungguVerifikasi->hasPages())
            <div class="bg-white rounded-lg shadow-sm border p-4 mt-6">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    {{-- Results Info --}}
                    <div class="mb-3 sm:mb-0">
                        <p class="text-sm text-gray-700">
                            Menampilkan
                            <span class="font-medium">{{ $menungguVerifikasi->firstItem() }}</span>
                            sampai
                            <span class="font-medium">{{ $menungguVerifikasi->lastItem() }}</span>
                            dari
                            <span class="font-medium">{{ $menungguVerifikasi->total() }}</span>
                            Pengiriman Menunggu Verifikasi
                        </p>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="flex items-center space-x-2">
                        {{-- Previous Page --}}
                        @if ($menungguVerifikasi->onFirstPage())
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </span>
                        @else
                            @php
                                $prevUrl = $menungguVerifikasi->previousPageUrl();
                                $prevUrlParts = parse_url($prevUrl);
                                parse_str($prevUrlParts['query'] ?? '', $prevParams);
                                $prevParams['tab'] = 'menunggu-verifikasi';
                                // Preserve other filters
                                if (request('search_verifikasi')) $prevParams['search_verifikasi'] = request('search_verifikasi');
                                if (request('filter_purchasing_verifikasi')) $prevParams['filter_purchasing_verifikasi'] = request('filter_purchasing_verifikasi');
                                if (request('sort_date_verifikasi')) $prevParams['sort_date_verifikasi'] = request('sort_date_verifikasi');
                                $prevUrl = $prevUrlParts['path'] . '?' . http_build_query($prevParams);
                            @endphp
                            <a href="{{ $prevUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 transition-colors">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @if($menungguVerifikasi->lastPage() > 1)
                            <div class="hidden sm:flex items-center space-x-1">
                                @foreach ($menungguVerifikasi->getUrlRange(1, $menungguVerifikasi->lastPage()) as $page => $url)
                                    @php
                                        $urlParts = parse_url($url);
                                        parse_str($urlParts['query'] ?? '', $urlParams);
                                        $urlParams['tab'] = 'menunggu-verifikasi';
                                        // Preserve other filters
                                        if (request('search_verifikasi')) $urlParams['search_verifikasi'] = request('search_verifikasi');
                                        if (request('filter_purchasing_verifikasi')) $urlParams['filter_purchasing_verifikasi'] = request('filter_purchasing_verifikasi');
                                        if (request('sort_date_verifikasi')) $urlParams['sort_date_verifikasi'] = request('sort_date_verifikasi');
                                        $pageUrl = $urlParts['path'] . '?' . http_build_query($urlParams);
                                    @endphp
                                    
                                    @if ($page == $menungguVerifikasi->currentPage())
                                        <span class="px-3 py-2 text-sm font-medium text-yellow-700 bg-yellow-100 border border-yellow-300 rounded-lg">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $pageUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 transition-colors">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Mobile Page Indicator --}}
                            <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                                {{ $menungguVerifikasi->currentPage() }} / {{ $menungguVerifikasi->lastPage() }}
                            </div>
                        @endif

                        {{-- Next Page --}}
                        @if ($menungguVerifikasi->hasMorePages())
                            @php
                                $nextUrl = $menungguVerifikasi->nextPageUrl();
                                $nextUrlParts = parse_url($nextUrl);
                                parse_str($nextUrlParts['query'] ?? '', $nextParams);
                                $nextParams['tab'] = 'menunggu-verifikasi';
                                // Preserve other filters
                                if (request('search_verifikasi')) $nextParams['search_verifikasi'] = request('search_verifikasi');
                                if (request('filter_purchasing_verifikasi')) $nextParams['filter_purchasing_verifikasi'] = request('filter_purchasing_verifikasi');
                                if (request('sort_date_verifikasi')) $nextParams['sort_date_verifikasi'] = request('sort_date_verifikasi');
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

{{-- Modal Aksi Verifikasi --}}
<div id="aksiVerifikasiModal" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 z-50 hidden" style="display: none; align-items: center; justify-content: center;">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-full overflow-y-auto border border-yellow-600">
        <div class="p-2" id="aksiVerifikasiModalContent">
        </div>
    </div>
</div>



{{-- SweetAlert2 Library --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- CSS untuk styling gambar --}}
<style>
/* Image hover effects */
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}

.group:hover .group-hover\:bg-opacity-20 {
    --tw-bg-opacity: 0.2;
}

/* Smooth transitions */
.transition-opacity {
    transition-property: opacity;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}
</style>

{{-- JavaScript untuk Tab Menunggu Verifikasi --}}
<script>
// Variables for current pengiriman
let currentPengirimanId = null;

// Debounced search function for server-side filtering
let searchTimeoutVerifikasi;
function debounceSearchVerifikasi() {
    clearTimeout(searchTimeoutVerifikasi);
    searchTimeoutVerifikasi = setTimeout(() => {
        submitSearchVerifikasi();
    }, 1000);
}

// Submit search to server
function submitSearchVerifikasi() {
    const currentParams = new URLSearchParams(window.location.search);
    const searchValue = document.getElementById('searchInputVerifikasi').value;
    
    currentParams.set('tab', 'menunggu-verifikasi');
    
    if (searchValue) {
        currentParams.set('search_verifikasi', searchValue);
    } else {
        currentParams.delete('search_verifikasi');
    }
    
    currentParams.delete('verifikasi_page');
    
    window.location.href = '/purchasing/pengiriman?' + currentParams.toString();
}

// Apply filters function for server-side filtering
function applyFiltersVerifikasi() {
    const currentParams = new URLSearchParams(window.location.search);
    
    const searchValue = document.getElementById('searchInputVerifikasi').value;
    const filterPurchasing = document.getElementById('filterPurchasingVerifikasi').value;
    const sortDate = document.getElementById('sortDateVerifikasi').value;
    
    currentParams.set('tab', 'menunggu-verifikasi');
    
    if (searchValue) currentParams.set('search_verifikasi', searchValue);
    else currentParams.delete('search_verifikasi');
    
    if (filterPurchasing) currentParams.set('filter_purchasing_verifikasi', filterPurchasing);
    else currentParams.delete('filter_purchasing_verifikasi');
    
    if (sortDate) currentParams.set('sort_date_verifikasi', sortDate);
    else currentParams.delete('sort_date_verifikasi');
    
    currentParams.delete('verifikasi_page');
    
    window.location.href = '/purchasing/pengiriman?' + currentParams.toString();
}

// Clear all filters
function clearAllFiltersVerifikasi() {
    const currentParams = new URLSearchParams(window.location.search);
    
    const newParams = new URLSearchParams();
    newParams.set('tab', 'menunggu-verifikasi');
    
    window.location.href = '/purchasing/pengiriman?' + newParams.toString();
}

// Open aksi verifikasi modal
function openAksiVerifikasiModal(id, noPengiriman, status) {
    console.log('Opening aksi verifikasi modal for ID:', id);
    currentPengirimanId = id;
    console.log('currentPengirimanId set to:', currentPengirimanId);
    
    fetch(`/purchasing/pengiriman/${id}/detail-verifikasi`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('aksiVerifikasiModalContent').innerHTML = html;
            document.getElementById('aksiVerifikasiModal').style.display = 'flex';
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

// Close aksi verifikasi modal
function closeAksiVerifikasiModal() {
    document.getElementById('aksiVerifikasiModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentPengirimanId = null;
}



// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const verifikasiModal = document.getElementById('aksiVerifikasiModal');
    if (event.target === verifikasiModal) {
        closeAksiVerifikasiModal();
    }
});

// Toggle pengiriman list function
function togglePengirimanListVerifikasi(poId) {
    const list = document.getElementById(`pengiriman-list-${poId}`);
    const icon = document.getElementById(`icon-${poId}`);
    const text = document.getElementById(`text-${poId}`);
    
    if (list.style.display === 'none') {
        list.style.display = 'block';
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
        text.textContent = 'Sembunyikan';
    } else {
        list.style.display = 'none';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
        text.textContent = 'Tampilkan';
    }
}

// Update active filters on page load
document.addEventListener('DOMContentLoaded', function() {
    updateActiveFiltersVerifikasi();
});

function updateActiveFiltersVerifikasi() {
    // Implementation for showing active filters
    const activeFiltersContainer = document.getElementById('activeFiltersVerifikasi');
    const searchValue = document.getElementById('searchInputVerifikasi').value;
    const filterPurchasing = document.getElementById('filterPurchasingVerifikasi').value;
    const sortDate = document.getElementById('sortDateVerifikasi').value;
    
    let hasActiveFilters = false;
    let filtersHTML = '<span class="text-xs sm:text-sm font-bold text-yellow-700">Filter aktif:</span>';
    
    if (searchValue) {
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs active-filter-tag">Pencarian: ${searchValue}</span>`;
        hasActiveFilters = true;
    }
    
    if (filterPurchasing) {
        const purchasingSelect = document.getElementById('filterPurchasingVerifikasi');
        const purchasingName = purchasingSelect.options[purchasingSelect.selectedIndex].text;
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs active-filter-tag">Purchasing: ${purchasingName}</span>`;
        hasActiveFilters = true;
    }
    
    if (sortDate) {
        const dateLabels = {
            'newest': 'Terbaru',
            'oldest': 'Terlama'
        };
        filtersHTML += `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs active-filter-tag">Urutkan: ${dateLabels[sortDate]}</span>`;
        hasActiveFilters = true;
    }
    
    if (hasActiveFilters) {
        filtersHTML += `<button onclick="clearAllFiltersVerifikasi()" class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs hover:bg-red-200 transition-colors ml-2">
            <i class="fas fa-times mr-1"></i>Hapus Semua
        </button>`;
        activeFiltersContainer.innerHTML = filtersHTML;
        activeFiltersContainer.style.display = 'flex';
    } else {
        activeFiltersContainer.style.display = 'none';
    }
}

// Fungsi untuk membuka modal revisi dari detail
function openRevisiModalFromDetail() {
    if (!currentPengirimanId) {
        console.error('currentPengirimanId is not set');
        Swal.fire('Error', 'ID pengiriman tidak ditemukan', 'error');
        return;
    }
    
    fetch(`/purchasing/pengiriman/${currentPengirimanId}/modal/revisi`)
    .then(response => response.text())
    .then(html => {
        // Create modal container if not exists
        let modalContainer = document.getElementById('revisi-modal-container');
        if (!modalContainer) {
            modalContainer = document.createElement('div');
            modalContainer.id = 'revisi-modal-container';
            document.body.appendChild(modalContainer);
        }
        
        // Insert modal HTML
        modalContainer.innerHTML = html;
        
        // Show modal
        const modal = document.getElementById('revisiModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
    })
    .catch(error => {
        console.error('Error loading revisi modal:', error);
        Swal.fire('Error', 'Gagal memuat modal revisi', 'error');
    });
}

// Fungsi untuk membuka modal verifikasi dari detail
function openVerifikasiModalFromDetail() {
    if (!currentPengirimanId) {
        console.error('currentPengirimanId is not set');
        Swal.fire('Error', 'ID pengiriman tidak ditemukan', 'error');
        return;
    }
    
    fetch(`/purchasing/pengiriman/${currentPengirimanId}/modal/verifikasi`)
    .then(response => response.text())
    .then(html => {
        // Create modal container if not exists
        let modalContainer = document.getElementById('verifikasi-modal-container');
        if (!modalContainer) {
            modalContainer = document.createElement('div');
            modalContainer.id = 'verifikasi-modal-container';
            document.body.appendChild(modalContainer);
        }
        
        // Insert modal HTML
        modalContainer.innerHTML = html;
        
        // Show modal
        const modal = document.getElementById('verifikasiModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
    })
    .catch(error => {
        console.error('Error loading verifikasi modal:', error);
        Swal.fire('Error', 'Gagal memuat modal verifikasi', 'error');
    });
}

// Global functions untuk modal yang bisa diakses dari modal terpisah
function closeRevisiModalFromDetail() {
    const modalContainer = document.getElementById('revisi-modal-container');
    if (modalContainer) {
        modalContainer.innerHTML = '';
    }
}

function closeVerifikasiModalFromDetail() {
    const modalContainer = document.getElementById('verifikasi-modal-container');
    if (modalContainer) {
        modalContainer.innerHTML = '';
    }
}

// Alias functions for compatibility
function closeRevisiModal() {
    closeRevisiModalFromDetail();
}

function closeVerifikasiModal() {
    closeVerifikasiModalFromDetail();
}

// Simple download function untuk gambar
function downloadImage(imageSrc, imageName = 'bukti_foto_bongkar.jpg') {
    try {
        const link = document.createElement('a');
        link.href = imageSrc;
        link.download = imageName;
        link.target = '_blank';
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
    } catch (error) {
        // Fallback: open in new tab
        window.open(imageSrc, '_blank');
    }
}

// Submit revisi pengiriman (global function)
function submitRevisiPengiriman() {
    // Try to get pengiriman ID from modal or use currentPengirimanId
    let pengirimanId = currentPengirimanId;
    const pengirimanIdInput = document.getElementById('pengirimanId');
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

    // Try different possible input IDs for catatan
    let catatanTextarea = document.getElementById('catatan_revisi') || 
                         document.getElementById('catatanRevisi');
    const catatan = catatanTextarea ? catatanTextarea.value.trim() : '';
    
    // Validasi
    if (!catatan) {
        Swal.fire({
            icon: 'warning',
            title: 'Catatan Revisi Diperlukan',
            text: 'Silakan masukkan catatan revisi terlebih dahulu.',
            confirmButtonColor: '#f59e0b'
        });
        if (catatanTextarea) catatanTextarea.focus();
        return;
    }
    
    if (catatan.length < 10) {
        Swal.fire({
            icon: 'warning',
            title: 'Catatan Terlalu Pendek',
            text: 'Catatan revisi harus minimal 10 karakter.',
            confirmButtonColor: '#f59e0b'
        });
        if (catatanTextarea) catatanTextarea.focus();
        return;
    }
    
    // Konfirmasi
    Swal.fire({
        title: 'Konfirmasi Revisi',
        text: 'Yakin ingin mengirim revisi untuk pengiriman ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Kirim Revisi',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses Revisi...',
                text: 'Silakan tunggu sebentar.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit via AJAX
            fetch(`/purchasing/pengiriman/${pengirimanId}/revisi`, {
                method: 'POST',
                body: JSON.stringify({
                    catatan: catatan
                }),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    // Call success callback
                    onRevisiSuccess(data.message || 'Pengiriman berhasil direvisi');
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat memproses revisi');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message || 'Terjadi kesalahan saat memproses revisi.',
                    confirmButtonColor: '#ef4444'
                });
            });
        }
    });
}

// Submit verifikasi pengiriman (global function)
function submitVerifikasiPengiriman() {
    // Try to get pengiriman ID from modal or use currentPengirimanId
    let pengirimanId = currentPengirimanId;
    const pengirimanIdInput = document.getElementById('pengirimanId');
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

    const konfirmasiCheckbox = document.getElementById('konfirmasiVerifikasi');
    
    // Validasi konfirmasi checkbox
    if (konfirmasiCheckbox && !konfirmasiCheckbox.checked) {
        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Diperlukan',
            text: 'Silakan centang kotak konfirmasi terlebih dahulu sebelum melanjutkan verifikasi.',
            confirmButtonColor: '#16a34a'
        });
        konfirmasiCheckbox.focus();
        return;
    }
    
    // Konfirmasi final sebelum submit
    Swal.fire({
        title: 'Konfirmasi Verifikasi',
        html: `
            <div class="text-left">
                <p class="mb-3">Apakah Anda yakin ingin memverifikasi pengiriman ini?</p>
                <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                    <p class="text-sm text-yellow-800">
                        <strong>Peringatan:</strong> Setelah diverifikasi, pengiriman akan berstatus "Berhasil" dan tidak dapat diubah lagi.
                    </p>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Verifikasi',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses Verifikasi...',
                text: 'Silakan tunggu sebentar.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit to server
            fetch(`/purchasing/pengiriman/${pengirimanId}/verifikasi`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    // Call success callback
                    onVerifikasiSuccess(data.message || 'Pengiriman berhasil diverifikasi');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Verifikasi Gagal',
                        text: data.message || 'Terjadi kesalahan saat memverifikasi pengiriman.',
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

// Callback setelah revisi berhasil
function onRevisiSuccess(message) {
    closeRevisiModal();
    closeAksiVerifikasiModal();
    
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message || 'Pengiriman berhasil direvisi',
        showConfirmButton: false,
        timer: 2000
    }).then(() => {
        window.location.reload();
    });
}

// Callback setelah verifikasi berhasil
function onVerifikasiSuccess(message) {
    closeVerifikasiModal();
    closeAksiVerifikasiModal();
    
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message || 'Pengiriman berhasil diverifikasi',
        showConfirmButton: false,
        timer: 2000
    }).then(() => {
        window.location.reload();
    });
}

// Modal functions are handled by detail.blade.php with separate modal files
</script>
