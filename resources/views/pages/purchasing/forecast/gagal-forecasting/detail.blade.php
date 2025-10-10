{{-- Modal Detail Forecast Gagal (Clean Version) --}}
<div id="detailForecastModalGagal" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-file-alt text-red-600 mr-2"></i>
                Detail Forecast
            </h3>
            <button onclick="closeDetailModalGagal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="detailContentGagal" class="space-y-6">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
// Function to open detail modal (clean version)
function openDetailModalGagal(forecastId) {
    console.log('Opening detail modal for forecast ID:', forecastId);
    
    // Show loading state
    const modal = document.getElementById('detailForecastModalGagal');
    const content = document.getElementById('detailContentGagal');
    
    content.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-red-600 text-2xl mr-3"></i>
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
            populateDetailModalGagal(data.forecast);
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
function populateDetailModalGagal(forecast) {
    const content = document.getElementById('detailContentGagal');
    
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
        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Informasi Forecast</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-600">No Forecast</label>
                    <p class="text-sm text-gray-900 font-medium">${forecast.no_forecast}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Status</label>
                    <p class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>
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
                    <p class="text-lg font-bold text-red-600">${forecast.total_harga}</p>
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
function closeDetailModalGagal() {
    const modal = document.getElementById('detailForecastModalGagal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('detailForecastModalGagal');
    if (event.target === modal) {
        closeDetailModalGagal();
    }
});
</script>
