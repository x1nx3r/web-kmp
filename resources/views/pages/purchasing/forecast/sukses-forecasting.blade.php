Tab Sukses Forecasting
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
                        Pencarian Forecast Sukses
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="searchInputSukses" 
                               name="search_sukses"
                               value="{{ request('search_sukses') }}"
                               placeholder="Cari No. PO, nama klien, atau no forecast..." 
                               class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-sukses"
                               onkeyup="debounceSearchSukses()"
                               onchange="submitSearchSukses()">
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
                    {{-- Date Range Filter --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                            <i class="fas fa-calendar mr-1 sm:mr-2 text-green-500 text-xs"></i>
                            Tanggal Forecast
                        </label>
                        <input type="date" id="dateRangeFilterSukses" name="date_range_sukses" value="{{ request('date_range_sukses') }}" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersSukses()">
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                            <i class="fas fa-sort mr-1 sm:mr-2 text-green-500 text-xs"></i>
                            Urutan
                        </label>
                        <select id="sortOrderSukses" name="sort_order_sukses" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFiltersSukses()">
                            <option value="newest" {{ request('sort_order_sukses') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort_order_sukses') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>

                    {{-- Clear Filter Button --}}
                    <div class="flex items-end">
                        <button type="button" onclick="clearFiltersSukses()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 sm:py-3 px-2 sm:px-4 rounded-lg transition-all duration-200 text-xs sm:text-sm font-medium">
                            <i class="fas fa-eraser mr-1 sm:mr-2"></i>
                            Bersihkan Filter
                        </button>
                    </div>
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
                    Forecast Sukses
                </h3>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Total: {{ count($suksesForecasts ?? []) }} forecast
                </div>
            </div>
        </div>

        @forelse($suksesForecasts ?? [] as $forecast)
        @empty
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-check-circle text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Forecast Sukses</h3>
                <p>Belum ada forecast dengan status sukses.</p>
            </div>
        @endforelse

        @if(count($suksesForecasts ?? []) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">No Forecast</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">PO & Klien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Detail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($suksesForecasts as $forecast)
                            <tr class="hover:bg-green-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $forecast->no_forecast }}</div>
                                    @if($forecast->purchasing)
                                        <div class="text-sm text-gray-500">PIC: {{ $forecast->purchasing->nama }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium">{{ $forecast->purchaseOrder->no_po ?? 'N/A' }}</div>
                                        <div class="text-gray-500">{{ $forecast->purchaseOrder->klien->nama ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div>Qty: <span class="font-medium">{{ $forecast->total_qty_forecast_formatted ?? number_format($forecast->total_qty_forecast, 0, ',', '.') }}</span></div>
                                        <div>Total: <span class="font-medium">{{ $forecast->total_harga_forecast_formatted ?? 'Rp ' . number_format($forecast->total_harga_forecast, 0, ',', '.') }}</span></div>
                                        <div>Kirim: <span class="font-medium">{{ $forecast->hari_kirim_forecast }} hari</span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $forecast->tanggal_forecast_formatted ?? \Carbon\Carbon::parse($forecast->tanggal_forecast)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Sukses
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openDetailModal({{ $forecast->id }})" 
                                            class="text-green-600 hover:text-green-900 transition-colors duration-150">
                                        <i class="fas fa-eye mr-1"></i>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Modal Detail Forecast (Clean Version) --}}
<div id="detailForecastModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-file-alt text-green-600 mr-2"></i>
                Detail Forecast
            </h3>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="detailContent" class="space-y-6">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
// Function to open detail modal (clean version)
function openDetailModal(forecastId) {
    console.log('Opening detail modal for forecast ID:', forecastId);
    
    // Show loading state
    const modal = document.getElementById('detailForecastModal');
    const content = document.getElementById('detailContent');
    
    content.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-green-600 text-2xl mr-3"></i>
            <span class="text-gray-600">Memuat detail forecast...</span>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Fetch forecast detail
    fetch(`/purchasing/forecast/${forecastId}/detail`, {
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
            populateDetailModal(data.forecast);
        } else {
            throw new Error(data.message || 'Gagal memuat detail forecast');
        }
    })
    .catch(error => {
        console.error('Error loading forecast detail:', error);
        content.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-3"></i>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Detail</h4>
                <p class="text-gray-600">Terjadi kesalahan saat memuat detail forecast.</p>
            </div>
        `;
    });
}

// Function to populate modal with forecast data
function populateDetailModal(forecast) {
    const content = document.getElementById('detailContent');
    
    let detailsTable = '';
    if (forecast.details && forecast.details.length > 0) {
        detailsTable = `
            <div>
                <h4 class="text-md font-semibold text-gray-900 mb-3">Detail Bahan Baku</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bahan Baku</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${forecast.details.map(detail => `
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">${detail.bahan_baku}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">${detail.supplier}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">${detail.qty}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">${detail.harga_satuan}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">${detail.total_harga}</td>
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
            <h4 class="text-md font-semibold text-gray-900 mb-3">Informasi Forecast</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-600">No Forecast</label>
                    <p class="text-sm text-gray-900 font-medium">${forecast.no_forecast}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Status</label>
                    <p class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            ${forecast.status}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">No PO</label>
                    <p class="text-sm text-gray-900">${forecast.no_po}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Klien</label>
                    <p class="text-sm text-gray-900">${forecast.klien}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">PIC Purchasing</label>
                    <p class="text-sm text-gray-900">${forecast.pic_purchasing}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Tanggal Forecast</label>
                    <p class="text-sm text-gray-900">${forecast.tanggal_forecast}</p>
                </div>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Ringkasan</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-600">Total Quantity</label>
                    <p class="text-lg font-bold text-blue-600">${forecast.total_qty}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Total Harga</label>
                    <p class="text-lg font-bold text-green-600">${forecast.total_harga}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Hari Kirim</label>
                    <p class="text-lg font-bold text-purple-600">${forecast.hari_kirim}</p>
                </div>
            </div>
        </div>

        ${forecast.catatan ? `
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <h4 class="text-md font-semibold text-gray-900 mb-2">Catatan</h4>
                <p class="text-sm text-gray-700">${forecast.catatan}</p>
            </div>
        ` : ''}

        ${detailsTable}
    `;
}

// Function to close detail modal
function closeDetailModal() {
    const modal = document.getElementById('detailForecastModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('detailForecastModal');
    if (event.target === modal) {
        closeDetailModal();
    }
});
</script>
