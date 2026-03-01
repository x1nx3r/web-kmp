{{-- Forecast Detail & Management Modal --}}
<div id="forecastDetailModal" class="fixed backdrop-blur-xs inset-0 overflow-y-auto h-full w-full z-[9999] hidden">
    <div class="relative min-h-screen flex items-start justify-center py-8 px-4">
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-5xl border border-gray-200">
            {{-- Modal Header --}}
            <div class="flex items-center  justify-between p-6 border-b border-gray-200 bg-yellow-300 rounded-t-xl">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gray-400 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-cog text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Kelola Forecast</h3>
                        <p class="text-sm text-gray-600" id="forecastModalSubtitle">Detail dan manajemen forecast</p>
                    </div>
                </div>
                <button type="button" onclick="closeForecastDetailModal()" 
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-all duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

        {{-- Modal Content --}}
        <div class="p-6 max-h-[80vh] overflow-y-auto">
            {{-- Loading State --}}
            <div id="forecastModalLoading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
                <p class="text-sm text-gray-500">Memuat data forecast...</p>
            </div>

            {{-- Main Content --}}
            <div id="forecastModalContent" class="hidden">
                <form id="forecastDetailForm" class="space-y-6">
                    @csrf
                    <input type="hidden" id="forecastId" name="forecast_id">

                    {{-- Forecast Information Section --}}
                    <div class="bg-white rounded-lg p-6 border border-gray-200">
                        <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
                            <div class="w-6 h-6 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-info-circle text-gray-600 text-sm"></i>
                            </div>
                            Informasi Forecast
                        </h4>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- No Forecast --}}
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-hashtag text-gray-500 mr-2"></i>
                                    No. Forecast
                                </label>
                                <input type="text" id="noForecast" name="no_forecast" readonly 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800 font-medium">
                            </div>

                            {{-- No PO --}}
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-file-alt text-gray-500 mr-2"></i>
                                    No. Purchase Order
                                </label>
                                <input type="text" id="noPO" name="po_number" readonly 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800 font-medium">
                            </div>

                            {{-- Klien --}}
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-building text-gray-500 mr-2"></i>
                                    Klien
                                </label>
                                <input type="text" id="klienName" name="klien_name" readonly 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800 font-medium">
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-flag text-gray-500 mr-2"></i>
                                    Status
                                </label>
                                <input type="text" id="forecastStatus" name="status" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800 font-medium">
                            </div>

                            {{-- PIC Purchasing --}}
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user-tie text-gray-500 mr-2"></i>
                                    PIC Procurement
                                </label>
                                <input type="text" id="picPurchasing" name="pic_purchasing" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800 font-medium">
                            </div>
                        </div>
                    </div>

                    {{-- Forecast Details Section --}}
                    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                        <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
                            <div class="w-6 h-6 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-chart-line text-gray-600 text-sm"></i>
                            </div>
                            Detail Forecast
                        </h4>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            {{-- Tanggal Forecast --}}
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar text-yellow-600 mr-2"></i>
                                    Tanggal Forecast
                                </label>
                                <input type="date" id="tanggalForecast" name="tanggal_forecast" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800 font-medium">
                            </div>

                            {{-- Hari Kirim --}}
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-truck text-yellow-600 mr-2"></i>
                                    Hari Kirim
                                </label>
                                <input type="text" id="hariKirim" name="hari_kirim_forecast" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800 font-medium">
                            </div>
                        </div>

                        {{-- Summary Info --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                            {{-- Total Quantity --}}
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-boxes text-yellow-600 mr-2"></i>
                                    Total Quantity
                                </label>
                                <input type="number" id="totalQty" name="total_qty_forecast" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800 font-medium">
                            </div>

                            {{-- Total Harga --}}
                            <div>
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-money-bill-wave text-yellow-600 mr-2"></i>
                                    Total Harga
                                </label>
                                <input type="text" id="totalHarga" name="total_harga_forecast" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800 font-medium">
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Section --}}
                    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                        <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
                            <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-sticky-note text-blue-600 text-sm"></i>
                            </div>
                            Catatan & Keterangan
                        </h4>

                        <div>
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-comment text-gray-500 mr-2"></i>
                                Catatan
                            </label>
                            <div id="catatanForecast" class="w-full px-3 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 text-sm min-h-[80px] leading-relaxed">
                                Tidak ada catatan
                            </div>
                        </div>
                    </div>

                    {{-- Forecast Details Section --}}
                    <div class="bg-white rounded-lg p-6 border border-gray-200" id="forecastDetailsSection">
                        <h4 class="flex items-center text-xl font-bold text-gray-800 mb-6">
                            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-list-alt text-white text-sm"></i>
                            </div>
                            Detail Bahan Baku
                        </h4>
                        
                        <div id="forecastDetailsContainer" class="bg-white rounded-lg border border-green-200 shadow-sm overflow-hidden">
                            {{-- Will be populated by JavaScript --}}
                            <p class="text-sm text-gray-500 p-6">Memuat detail bahan baku...</p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col gap-6 pt-8 border-t-2 border-gray-200">
                        {{-- Status Change Actions --}}
                        <div class="bg-white rounded-lg p-6 border border-gray-200">
                            <h5 class="flex items-center text-lg font-semibold text-gray-800 mb-4">
                                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-shipping-fast text-gray-600 text-sm"></i>
                                </div>
                                Ubah Status Forecast
                            </h5>
                            <p class="text-sm text-gray-600 mb-6">Pilih tindakan yang akan dilakukan pada forecast ini:</p>
                            
                            <div class="flex flex-col sm:flex-row gap-4" id="forecastActionButtons">
                                {{-- Buttons will be dynamically shown/hidden based on user role and PIC --}}
                                <button type="button" onclick="changeToPengiriman()" 
                                        id="btnPengiriman"
                                        class="flex-1 px-6 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center justify-center font-medium border border-green-600"
                                        style="display: none;">
                                    <i class="fas fa-truck mr-3"></i>
                                    <span>Ubah ke Pengiriman</span>
                                </button>
                                
                                <button type="button" onclick="changeToPengirimanBatal()" 
                                        id="btnBatalPengiriman"
                                        class="flex-1 px-6 py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center justify-center font-medium border border-red-600"
                                        style="display: none;">
                                    <i class="fas fa-times-circle mr-3"></i>
                                    <span>Pengiriman Batal</span>
                                </button>
                            </div>
                            
                            {{-- Access Denied Message --}}
                            <div id="accessDeniedMessage" class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded-lg border border-gray-200" style="display: none;">
                                <i class="fas fa-lock mr-2"></i>
                                Hanya direktur, Manager Procurement, dan PIC Procurement yang dapat mengubah status forecast ini.
                            </div>
                            
                            {{-- Additional Info --}}
                            <div class="mt-4 text-xs text-gray-600 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-info-circle text-gray-500 mt-1"></i>
                                    <div class="space-y-1">
                                        <p><strong class="text-gray-800">Pengiriman:</strong> Forecast akan diproses untuk pengiriman ke klien</p>
                                        <p><strong class="text-gray-800">Pengiriman Batal:</strong> Forecast dibatalkan dan tidak akan diproses</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Main Action Buttons --}}
                        <div class="flex gap-4 justify-end bg-gray-50 p-4 rounded-lg mt-6">
                            <button type="button" onclick="closeForecastDetailModal()" 
                                    class="px-8 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 font-medium">
                                <i class="fas fa-times mr-2"></i>Tutup
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Error State --}}
            <div id="forecastModalError" class="hidden text-center py-8">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Data</h3>
                <p class="text-sm text-gray-500 mb-4">Terjadi kesalahan saat memuat data forecast.</p>
                <button type="button" onclick="retryLoadForecast()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-redo mr-2"></i>Coba Lagi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Success/Error Toast Notifications --}}
<div id="forecastToast" class="fixed top-4 right-4 z-60 hidden">
    <div class="bg-white border-l-4 border-green-500 p-4 shadow-lg rounded-md max-w-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i id="toastIcon" class="fas fa-check-circle text-green-500"></i>
            </div>
            <div class="ml-3">
                <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
            </div>
            <div class="ml-auto pl-3">
                <button onclick="hideToast()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Current user data for authorization checks
const currentUser = {
    id: {{ Auth::id() }},
    role: '{{ Auth::user()->role }}'
};

// Check if user can perform actions on forecast (pengiriman/batal)
function checkForecastAuthorization(forecastData) {
    const btnPengiriman = document.getElementById('btnPengiriman');
    const btnBatalPengiriman = document.getElementById('btnBatalPengiriman');
    const accessDeniedMessage = document.getElementById('accessDeniedMessage');
    
    // Authorization logic:
    // Only direktur, manager_purchasing, or PIC Purchasing can change status to pengiriman or batal
    const canModify = currentUser.role === 'direktur' || 
                      currentUser.role === 'manager_purchasing' ||
                      (forecastData.pic_purchasing_id && forecastData.pic_purchasing_id == currentUser.id);
    
    // Store canModify globally so populateForecastDetails can use it
    window.currentForecastCanModify = canModify;

    if (canModify) {
        // Show action buttons
        if (btnPengiriman) btnPengiriman.style.display = 'flex';
        if (btnBatalPengiriman) btnBatalPengiriman.style.display = 'flex';
        if (accessDeniedMessage) accessDeniedMessage.style.display = 'none';
    } else {
        // Hide action buttons and show access denied message
        if (btnPengiriman) btnPengiriman.style.display = 'none';
        if (btnBatalPengiriman) btnBatalPengiriman.style.display = 'none';
        if (accessDeniedMessage) accessDeniedMessage.style.display = 'block';
    }
}

// Global variable to store current forecast data
let currentForecastData = null;

// Open forecast detail modal — always fetches fresh data from API
function openForecastDetailModal(forecastData) {
    console.log('Opening forecast modal with data:', forecastData);

    const modal   = document.getElementById('forecastDetailModal');
    const loading = document.getElementById('forecastModalLoading');
    const content = document.getElementById('forecastModalContent');
    const error   = document.getElementById('forecastModalError');

    // Ensure modal is attached to body to avoid z-index issues
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    // Show modal in loading state
    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    error.classList.add('hidden');
    document.body.style.overflow = 'hidden';

    // Store basic data immediately (so id is available early)
    currentForecastData = forecastData;

    // Fetch fresh full data (including detail IDs) from the API
    fetchForecastDetail(forecastData.id);
}

// Fetch full forecast detail from API and populate modal
function fetchForecastDetail(forecastId) {
    const loading = document.getElementById('forecastModalLoading');
    const content = document.getElementById('forecastModalContent');
    const error   = document.getElementById('forecastModalError');

    loading.classList.remove('hidden');
    content.classList.add('hidden');
    error.classList.add('hidden');

    fetch(`/procurement/forecasting/${forecastId}/detail`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                         || document.querySelector('input[name="_token"]')?.value
                         || '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.forecast) {
            currentForecastData = data.forecast;
            populateForecastModal(data.forecast);
        } else {
            console.error('API error:', data.message);
            loading.classList.add('hidden');
            error.classList.remove('hidden');
        }
    })
    .catch(err => {
        console.error('fetchForecastDetail network error:', err);
        loading.classList.add('hidden');
        error.classList.remove('hidden');
    });
}

// Populate modal with forecast data
function populateForecastModal(data) {
    try {
        console.log('Populating modal with data:', data);
        
        // Populate form fields with real data
        document.getElementById('forecastId').value = data.id || '';
        document.getElementById('noForecast').value = data.no_forecast || '';
        document.getElementById('noPO').value = data.po_number || '';
        document.getElementById('klienName').value = data.klien || '';
        document.getElementById('forecastStatus').value = data.status || 'Pending';
        document.getElementById('picPurchasing').value = data.pic_purchasing || 'Tidak ada PIC';
        
        // Convert date to Y-m-d for the date input
        // API returns "d M Y" (e.g. "28 Feb 2026"), blade passes "d/m/Y" (e.g. "28/02/2026")
        let dateForInput = '';
        if (data.tanggal_forecast && data.tanggal_forecast !== 'N/A') {
            try {
                const parsed = new Date(data.tanggal_forecast);
                if (!isNaN(parsed)) {
                    // new Date() handles "28 Feb 2026" natively
                    const y = parsed.getFullYear();
                    const m = String(parsed.getMonth() + 1).padStart(2, '0');
                    const d = String(parsed.getDate()).padStart(2, '0');
                    dateForInput = `${y}-${m}-${d}`;
                } else {
                    // Fallback: try d/m/Y split
                    const parts = data.tanggal_forecast.split('/');
                    if (parts.length === 3) {
                        dateForInput = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                    }
                }
            } catch (e) {
                console.warn('Date conversion failed:', e);
            }
        }
        document.getElementById('tanggalForecast').value = dateForInput;
        
        document.getElementById('hariKirim').value = data.hari_kirim || '';
        document.getElementById('totalQty').value = data.total_qty || '0';
        document.getElementById('totalHarga').value = data.total_harga || 'Rp 0';
        document.getElementById('catatanForecast').textContent = data.catatan || 'Tidak ada catatan';
        
        // Update modal subtitle
        document.getElementById('forecastModalSubtitle').textContent = `No. Forecast: ${data.no_forecast || 'N/A'}`;
        
        // Check authorization first (sets window.currentForecastCanModify used by populateForecastDetails)
        checkForecastAuthorization(data);
        
        // Populate forecast details if available
        populateForecastDetails(data.details || []);
        
        // Show content and hide loading
        document.getElementById('forecastModalLoading').classList.add('hidden');
        document.getElementById('forecastModalContent').classList.remove('hidden');
        
    } catch (error) {
        console.error('Error populating modal:', error);
        showModalError();
    }
}

// Populate forecast details table
function populateForecastDetails(details) {
    const container = document.getElementById('forecastDetailsContainer');
    
    if (!details || details.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-500 p-6">Tidak ada detail bahan baku tersedia.</p>';
        return;
    }
    
    const canModify = window.currentForecastCanModify || false;
    const showDeleteBtn = canModify && details.length > 1;
    
    let tableHTML = `
        <div class="overflow-hidden">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No.</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Bahan Baku</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Harga Satuan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Total Harga</th>
                        ${showDeleteBtn ? '<th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>' : ''}
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    // Calculate totals
    let totalQty = 0;
    let totalAmount = 0;
    
    details.forEach((detail, index) => {
        // Extract numeric values for calculation
        const qty = parseInt(detail.qty?.toString().replace(/[.,]/g, '') || '0');
        const totalHarga = parseFloat(detail.total_harga?.toString().replace(/[Rp\s.,]/g, '') || '0');
        
        totalQty += qty;
        totalAmount += totalHarga;
        
        const deleteBtn = showDeleteBtn
            ? `<td class="px-6 py-4 text-center">
                <button type="button"
                    onclick="confirmDeleteForecastDetail(${currentForecastData.id}, ${detail.id}, '${(detail.bahan_baku || 'bahan baku ini').replace(/'/g, "\\'")}')"
                    class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-semibold transition-colors duration-200"
                    title="Hapus detail ini">
                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                </button>
              </td>`
            : '';

        tableHTML += `
            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-gray-100 transition-colors duration-200" id="forecast-detail-row-${detail.id}">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${index + 1}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-semibold text-gray-900">${detail.bahan_baku || 'N/A'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${detail.supplier || 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-semibold text-gray-900">${detail.qty || '0'}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${detail.harga_satuan || 'Rp 0'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-bold text-green-600">${detail.total_harga || 'Rp 0'}</span>
                </td>
                ${deleteBtn}
            </tr>
        `;
    });
    
    const colspanTotal = showDeleteBtn ? 3 : 3;

    // Add total row
    tableHTML += `
        <tr class="bg-gray-100 border-t-2 border-gray-300">
            <td class="px-6 py-4 text-sm font-bold text-gray-900" colspan="${colspanTotal}">
                <div class="flex items-center">
                    <i class="fas fa-calculator text-gray-600 mr-3"></i>
                    <span class="text-lg">TOTAL</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-bold text-gray-900 bg-gray-50 px-3 py-1 rounded">
                    ${totalQty.toLocaleString('id-ID')}
                </span>
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-500">-</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-bold text-gray-900 bg-gray-50 px-3 py-1 rounded">
                    Rp ${totalAmount.toLocaleString('id-ID')}
                </span>
            </td>
            ${showDeleteBtn ? '<td></td>' : ''}
        </tr>
    `;
    
    tableHTML += `
                </tbody>
            </table>
        </div>
        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <p class="text-sm text-gray-700 flex items-center">
                <i class="fas fa-info-circle text-gray-500 mr-2"></i>
                <span class="font-semibold">Total ${details.length} item</span> bahan baku dalam forecast ini
            </p>
        </div>
    `;
    
    container.innerHTML = tableHTML;
}

// Show confirmation dialog before deleting a forecast detail
function confirmDeleteForecastDetail(forecastId, detailId, bahanBakuName) {
    if (!confirm(`Apakah Anda yakin ingin menghapus detail bahan baku "${bahanBakuName}" dari forecast ini?\n\nTotal qty dan harga forecast akan diperbarui otomatis.`)) {
        return;
    }
    deleteForecastDetail(forecastId, detailId);
}

// Delete a single forecast detail via AJAX
function deleteForecastDetail(forecastId, detailId) {
    const row = document.getElementById('forecast-detail-row-' + detailId);
    if (row) {
        row.style.opacity = '0.5';
        row.style.pointerEvents = 'none';
    }

    fetch(`/procurement/forecasting/${forecastId}/detail/${detailId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                         || document.querySelector('input[name="_token"]')?.value
                         || '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Re-fetch fresh data from API so detail IDs and totals are always accurate
            fetchForecastDetail(forecastId);
        } else {
            showToast(data.message || 'Gagal menghapus detail.', 'error');
            if (row) {
                row.style.opacity = '';
                row.style.pointerEvents = '';
            }
        }
    })
    .catch(err => {
        console.error('deleteForecastDetail error:', err);
        showToast('Terjadi kesalahan jaringan. Silakan coba lagi.', 'error');
        if (row) {
            row.style.opacity = '';
            row.style.pointerEvents = '';
        }
    });
}

// Close forecast detail modal
function closeForecastDetailModal() {
    const modal = document.getElementById('forecastDetailModal');
    modal.classList.add('hidden');
    
    // Restore body scroll
    document.body.style.overflow = '';
    
    // Reset form
    document.getElementById('forecastDetailForm').reset();
}

// Show modal error state
function showModalError() {
    document.getElementById('forecastModalLoading').classList.add('hidden');
    document.getElementById('forecastModalContent').classList.add('hidden');
    document.getElementById('forecastModalError').classList.remove('hidden');
}

// Retry loading forecast
function retryLoadForecast() {
    if (currentForecastData && currentForecastData.id) {
        fetchForecastDetail(currentForecastData.id);
    }
}

// Toggle reason section based on status change
function toggleReasonSection(status) {
    // This function is no longer needed since we removed editable status
}

// Change forecast status to Pengiriman
function changeToPengiriman() {
    if (!currentForecastData) {
        showToast('Data forecast tidak ditemukan', 'error');
        return;
    }
    
    // Prepare data for pengiriman modal - data already comes in correct format from controller
    const pengirimanData = {
        id: currentForecastData.id,
        no_forecast: currentForecastData.no_forecast,
        klien: currentForecastData.klien,
        total_qty: currentForecastData.total_qty,
        total_harga: currentForecastData.total_harga
    };
    
    console.log('Opening pengiriman modal with forecast data:', pengirimanData);
    
    // Open the pengiriman modal
    if (typeof openPengirimanModal === 'function') {
        openPengirimanModal(pengirimanData);
    } else {
        console.error('openPengirimanModal function not found');
        
        // Fallback to simple confirmation if modal is not available
        if (confirm('Apakah Anda yakin akan mengubah forecast ini menjadi "Pengiriman"?\n\nForecast akan diproses untuk pengiriman ke klien.')) {
            // Show loading state
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            btn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Reset button
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                // Show success message
                showToast('Forecast berhasil diubah menjadi "Pengiriman"!', 'success');
                
                // Close modal after delay
                setTimeout(() => {
                    closeForecastDetailModal();
                    // In real implementation, refresh the page or update the list
                    // window.location.reload();
                }, 1500);
            }, 2000);
        }
    }
}

// Change forecast status to Pengiriman Batal
function changeToPengirimanBatal() {
    if (!currentForecastData) {
        showToast('Data forecast tidak ditemukan', 'error');
        return;
    }
    
    // Prepare data for batal modal - data already comes in correct format from controller
    const batalData = {
        id: currentForecastData.id,
        no_forecast: currentForecastData.no_forecast,
        klien: currentForecastData.klien,
        total_qty: currentForecastData.total_qty,
        total_harga: currentForecastData.total_harga
    };
    
    console.log('Opening batal modal with forecast data:', batalData);
    
    // Open the batal pengiriman modal
    if (typeof openBatalPengirimanModal === 'function') {
        openBatalPengirimanModal(batalData);
    } else {
        console.error('openBatalPengirimanModal function not found');
        
        // Fallback to simple confirmation if modal is not available
        if (confirm('Apakah Anda yakin akan membatalkan pengiriman forecast ini?\n\nForecast akan dibatalkan dan tidak akan diproses.')) {
            // Show loading state
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            btn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Reset button
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                // Show success message
                showToast('Pengiriman forecast berhasil dibatalkan!', 'warning');
                
                // Close modal after delay
                setTimeout(() => {
                    closeForecastDetailModal();
                    // In real implementation, refresh the page or update the list
                    // window.location.reload();
                }, 1500);
            }, 2000);
        }
    }
}

// Format currency helper
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('forecastToast');
    const icon = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');
    const toastContainer = toast.querySelector('div');
    
    // Set message
    messageEl.textContent = message;
    
    // Set icon and color based on type
    switch (type) {
        case 'success':
            icon.className = 'fas fa-check-circle text-green-500';
            toastContainer.className = 'bg-white border-l-4 border-green-500 p-4 shadow-lg rounded-md max-w-sm';
            break;
        case 'warning':
            icon.className = 'fas fa-exclamation-triangle text-yellow-500';
            toastContainer.className = 'bg-white border-l-4 border-yellow-500 p-4 shadow-lg rounded-md max-w-sm';
            break;
        case 'error':
            icon.className = 'fas fa-times-circle text-red-500';
            toastContainer.className = 'bg-white border-l-4 border-red-500 p-4 shadow-lg rounded-md max-w-sm';
            break;
        case 'info':
            icon.className = 'fas fa-info-circle text-gray-500';
            toastContainer.className = 'bg-white border-l-4 border-blue-500 p-4 shadow-lg rounded-md max-w-sm';
            break;
    }
    
    // Show toast
    toast.classList.remove('hidden');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hideToast();
    }, 5000);
}

// Hide toast notification
function hideToast() {
    document.getElementById('forecastToast').classList.add('hidden');
}

// Handle DOM ready events
document.addEventListener('DOMContentLoaded', function() {
    // Move modal to body to prevent z-index issues with tab containers
    const modal = document.getElementById('forecastDetailModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    
    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeForecastDetailModal();
            }
        });
    }
});

// Handle escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('forecastDetailModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeForecastDetailModal();
        }
    }
});
</script>

<style>
/* Modal styles - Ensure highest priority */
#forecastDetailModal {
    backdrop-filter: blur(4px);
    position: fixed !important;
    z-index: 9999 !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
}

/* Ensure modal content is properly positioned */
#forecastDetailModal > div {
    position: relative;
    z-index: 10000;
    width: 100%;
    height: 100%;
}

/* Modal content container */
#forecastDetailModal .relative.bg-white {
    position: relative;
    z-index: 10001;
}

/* Form input focus styles */
#forecastDetailModal input:focus,
#forecastDetailModal select:focus,
#forecastDetailModal textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: #3b82f6;
    outline: none;
}

/* Animation for modal */
#forecastDetailModal > div > div {
    animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-32px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Toast animation */
#forecastToast {
    animation: toastSlideIn 0.3s ease-out;
}

@keyframes toastSlideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Button hover effects */
.transition-all {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Enhanced shadows */
.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Custom scrollbar */
.max-h-\[80vh\]::-webkit-scrollbar {
    width: 8px;
}

.max-h-\[80vh\]::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.max-h-\[80vh\]::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.max-h-\[80vh\]::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    #forecastDetailModal > div {
        width: 95%;
        margin: 1rem;
        padding: 0;
    }
    
    .grid.grid-cols-1.lg\\:grid-cols-2 {
        grid-template-columns: 1fr;
    }
    
    .flex.flex-col.sm\\:flex-row {
        flex-direction: column;
    }
}

/* Loading spinner */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Gradient text */
.gradient-text {
    background: linear-gradient(45deg, #3b82f6, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Enhanced table styles */
table tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
}

/* Button animation */
button:hover {
    transform: translateY(-1px);
}

button:active {
    transform: translateY(0);
}
</style>

{{-- Include Batal Pengiriman Modal --}}
@include('pages.purchasing.forecast.pending-forecasting.batal')

{{-- Include Pengiriman Modal --}}
@include('pages.purchasing.forecast.pending-forecasting.pengiriman')
