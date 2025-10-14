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
                    <div class="relative">
                        <input type="text" 
                               id="searchInputMasuk" 
                               name="search_masuk"
                               value="{{ request('search_masuk') }}"
                               placeholder="Cari No. PO atau nama purchasing..." 
                               class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-blue-200 focus:border-blue-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-masuk"
                               onkeyup="debounceSearchMasuk()"
                               onchange="submitSearchMasuk()">
                        <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                            <div class="w-3 h-3 sm:w-6 sm:h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-blue-500 text-xs sm:text-sm"></i>
                            </div>
                        </div>
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
                    <div class="w-full sm:w-64 flex-shrink-0">
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
                    <div class="w-full sm:w-48 flex-shrink-0">
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
                    <div class="w-full sm:w-auto sm:ml-auto flex-shrink-0">
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
                // Group pengiriman by purchase_order_id
                $groupedPengiriman = collect($pengirimanMasuk->items() ?? [])->groupBy('purchase_order_id');
            @endphp
        </div>
    </div>



    {{-- Simplified PO Cards with Pengiriman --}}
    <div class="space-y-2">
        @forelse($groupedPengiriman as $poId => $pengirimanList)
            @php
                $po = $pengirimanList->first()->purchaseOrder;
                $purchasing = $pengirimanList->first()->purchasing;
                

            @endphp
            {{-- Simplified PO Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 border-l-4 border-l-blue-500 masuk-pengiriman-card po-card" 
                 data-no-po="{{ strtolower($po->no_po ?? '') }}" 
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
                                    <p class="text-sm font-semibold text-green-600">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                @endif
                            </div>
                            <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs flex items-center" onclick="togglePengirimanList('po-{{ $poId }}')">
                                <i class="fas fa-chevron-right pengiriman-icon" id="icon-po-{{ $poId }}"></i>
                                <span class="ml-1" id="text-po-{{ $poId }}">Detail</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Simplified Pengiriman List --}}
                <div class="border-t border-gray-200 pengiriman-list" id="pengiriman-list-po-{{ $poId }}">
                    <div class="p-3">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-truck text-blue-600 mr-2"></i>
                            Daftar Pengiriman ({{ $pengirimanList->count() }})
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
                                                <div class="w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-shipping-fast text-white text-xs"></i>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">{{ $pengiriman->no_pengiriman }}</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ ucfirst($pengiriman->status) }}
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
                                        
                                        <div class="flex space-x-1">
                                            <button onclick="showPengirimanDetail({{ $pengiriman->id }})" 
                                                    class="text-blue-600 hover:text-blue-800 p-1 rounded" title="Detail">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>
                                            <a href="{{ route('purchasing.pengiriman.edit', $pengiriman->id) }}" 
                                               class="text-green-600 hover:text-green-800 p-1 rounded" title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                            <button onclick="konfirmasiKirim({{ $pengiriman->id }})" 
                                                    class="text-amber-600 hover:text-amber-800 p-1 rounded" title="Konfirmasi">
                                                <i class="fas fa-paper-plane text-xs"></i>
                                            </button>
                                            <button onclick="batalPengiriman({{ $pengiriman->id }})" 
                                                    class="text-red-600 hover:text-red-800 p-1 rounded" title="Batal">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($pengiriman->catatan)
                                        <div class="mt-2 pt-2 border-t border-gray-200">
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
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Pengiriman Masuk</h3>
                    <p>Belum ada pengiriman dengan status pending.</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if(isset($pengirimanMasuk) && $pengirimanMasuk->hasPages())
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-3 sm:px-6 py-3 sm:py-4 border-t-2 border-blue-200 rounded-b-lg sm:rounded-b-xl">
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-2 sm:space-y-0">
                    <div class="text-xs sm:text-sm text-blue-700 font-semibold">
                        Menampilkan {{ $pengirimanMasuk->firstItem() ?? 0 }} - {{ $pengirimanMasuk->lastItem() ?? 0 }} dari {{ $pengirimanMasuk->total() ?? 0 }} pengiriman
                    </div>
                    <div class="pagination-custom">
                        {{ $pengirimanMasuk->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- JavaScript untuk Tab Pengiriman Masuk --}}
<script>
// Debounced search function for server-side filtering
let searchTimeoutMasuk;
function debounceSearchMasuk() {
    clearTimeout(searchTimeoutMasuk);
    searchTimeoutMasuk = setTimeout(() => {
        submitSearchMasuk();
    }, 1000); // Wait 1 second before submitting
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
    window.location.href = '/purchasing/pengiriman?' + currentParams.toString();
}

// Apply filters function for server-side filtering
function applyFiltersMasuk() {
    console.log('applyFiltersMasuk called');
    const currentParams = new URLSearchParams(window.location.search);
    
    // Get filter values
    const searchValue = document.getElementById('searchInputMasuk').value;
    const filterPurchasing = document.getElementById('filterPurchasing').value;
    const sortDate = document.getElementById('sortDateMasuk').value;
    
    console.log('Filter values:', {
        searchValue,
        filterPurchasing,
        sortDate
    });
    
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
    
    const newUrl = '/purchasing/pengiriman?' + currentParams.toString();
    console.log('Navigating to:', newUrl);
    
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
    
    window.location.href = '/purchasing/pengiriman?' + newParams.toString();
}

function showPengirimanDetail(id) {
    // Open detail modal
    fetch(`/purchasing/pengiriman/${id}/detail`)
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
    
    fetch(`/purchasing/pengiriman/${id}/status`, {
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
            window.location.href = '/purchasing/pengiriman?' + currentParams.toString();
        } else {
            alert('Gagal mengupdate status pengiriman');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate status');
    });
}

// Toggle pengiriman list visibility
function togglePengirimanList(poId) {
    const pengirimanList = document.getElementById('pengiriman-list-' + poId);
    const icon = document.getElementById('icon-' + poId);
    const text = document.getElementById('text-' + poId);
    
    if (pengirimanList.style.display === 'none') {
        // Show pengiriman list
        pengirimanList.style.display = 'block';
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
        if (text) text.textContent = 'Sembunyikan';
    } else {
        // Hide pengiriman list
        pengirimanList.style.display = 'none';
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
    const searchValue = urlParams.get('search_masuk');
    if (searchValue) {
        console.log('Setting search value:', searchValue);
        document.getElementById('searchInputMasuk').value = searchValue;
    }
    
    // Set purchasing filter
    const filterPurchasing = urlParams.get('filter_purchasing');
    if (filterPurchasing) {
        console.log('Setting filter purchasing:', filterPurchasing);
        document.getElementById('filterPurchasing').value = filterPurchasing;
    }
    
    // Set sort date filter
    const sortDate = urlParams.get('sort_date_masuk');
    if (sortDate) {
        console.log('Setting sort date:', sortDate);
        document.getElementById('sortDateMasuk').value = sortDate;
    }
    
    // Update active filters display
    updateActiveFiltersMasuk();
    
    // Initialize pengiriman list states
    document.querySelectorAll('.pengiriman-list').forEach(list => {
        list.style.display = 'none';
    });
    
    // Add event listeners for search input
    const searchInput = document.getElementById('searchInputMasuk');
    if (searchInput) {
        searchInput.addEventListener('input', debounceSearchMasuk);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitSearchMasuk();
            }
        });
    }
    
    console.log('Filter initialization complete');
});
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
