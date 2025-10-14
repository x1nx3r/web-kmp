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
                                    <div class="text-sm font-medium text-gray-900">{{ $pengiriman->purchaseOrder->no_po ?? '-' }}</div>
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openDetailModal({{ $pengiriman->id }})" 
                                            class="inline-flex items-center px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-800 rounded-md transition-colors duration-150">
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
                                    Menampilkan {{ $pengirimanBerhasil->firstItem() }} sampai {{ $pengirimanBerhasil->lastItem() }} dari {{ $pengirimanBerhasil->total() }} pengiriman
                                </p>
                            </div>

                            <div>
                                {{ $pengirimanBerhasil->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Modal Detail Pengiriman --}}
<div id="detailPengirimanModal" class="fixed inset-0 backdrop-blur-xs  bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        
        {{-- Header Modal --}}
        <div class="bg-green-600 px-6 py-4 border-b border-green-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Detail Pengiriman</h3>
                        <p class="text-sm text-green-100 opacity-90">Informasi lengkap pengiriman berhasil</p>
                    </div>
                </div>
                <button type="button" onclick="closeDetailModal()" 
                        class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6 max-h-[calc(90vh-120px)] overflow-y-auto">
            <div id="detailContent" class="space-y-6">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

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
    const url = '/purchasing/pengiriman' + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}

// Function to apply filters
function applyFiltersBerhasil() {
    submitSearchBerhasil();
}

// Function to open detail modal
function openDetailModal(pengirimanId) {
    console.log('Opening detail modal for pengiriman ID:', pengirimanId);
    
    // Show loading state
    const modal = document.getElementById('detailPengirimanModal');
    const content = document.getElementById('detailContent');
    
    content.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-green-600 text-2xl mr-3"></i>
            <span class="text-gray-600">Memuat detail pengiriman...</span>
        </div>
    `;
    
    // Show modal
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    
    // Fetch pengiriman detail
    fetch(`/purchasing/pengiriman/${pengirimanId}/detail-berhasil`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            populateDetailModal(data.pengiriman);
        } else {
            throw new Error(data.message || 'Gagal memuat detail pengiriman');
        }
    })
    .catch(error => {
        console.error('Error loading pengiriman detail:', error);
        content.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-3"></i>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Detail</h4>
                <p class="text-gray-600">Terjadi kesalahan saat memuat detail pengiriman.</p>
            </div>
        `;
    });
}

// Function to populate modal with pengiriman data
function populateDetailModal(pengiriman) {
    const content = document.getElementById('detailContent');
    
    let detailsTable = '';
    if (pengiriman.details && pengiriman.details.length > 0) {
        detailsTable = `
            <div>
                <h4 class="text-md font-semibold text-gray-900 mb-3">Detail Barang</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bahan Baku</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Kirim</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${pengiriman.details.map(detail => `
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">${detail.bahan_baku || '-'}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">${detail.supplier || '-'}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-medium">${detail.qty_kirim || '0'} kg</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">Rp ${Number(detail.harga_satuan || 0).toLocaleString('id-ID')}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-medium">Rp ${Number(detail.total_harga || 0).toLocaleString('id-ID')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }
    
    content.innerHTML = `
        <!-- Informasi Umum -->
        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Informasi Pengiriman</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-600">No Pengiriman</label>
                    <p class="text-sm text-gray-900 font-medium">${pengiriman.no_pengiriman}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Status</label>
                    <p class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            ${pengiriman.status}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">No PO</label>
                    <p class="text-sm text-gray-900">${pengiriman.no_po}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">PIC Purchasing</label>
                    <p class="text-sm text-gray-900">${pengiriman.pic_purchasing}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Tanggal Kirim</label>
                    <p class="text-sm text-gray-900">${pengiriman.tanggal_kirim}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Hari Kirim</label>
                    <p class="text-sm text-gray-900">${pengiriman.hari_kirim}</p>
                </div>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Ringkasan</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-600">Total Quantity</label>
                    <p class="text-lg font-bold text-blue-600">${pengiriman.total_qty}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Total Harga</label>
                    <p class="text-lg font-bold text-green-600">${pengiriman.total_harga}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Total Item</label>
                    <p class="text-lg font-bold text-purple-600">${pengiriman.total_items} item</p>
                </div>
            </div>
        </div>

        ${pengiriman.catatan ? `
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <h4 class="text-md font-semibold text-gray-900 mb-2">Catatan</h4>
                <p class="text-sm text-gray-700">${pengiriman.catatan}</p>
            </div>
        ` : ''}

        ${detailsTable}
    `;
}

// Function to close detail modal
function closeDetailModal() {
    const modal = document.getElementById('detailPengirimanModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
}

// Function to clear all filters
function clearAllFiltersBerhasil() {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Keep only the tab parameter
    const newParams = new URLSearchParams();
    newParams.set('tab', 'pengiriman-berhasil');
    
    window.location.href = '/purchasing/pengiriman?' + newParams.toString();
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

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('detailPengirimanModal');
    if (event.target === modal) {
        closeDetailModal();
    }
});
</script>
