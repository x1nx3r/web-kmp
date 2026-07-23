{{-- Modal Pengiriman --}}
<div id="pengirimanModal" class="fixed inset-0 overflow-y-auto h-full w-full z-[10000] hidden">
    <div class="relative min-h-screen flex items-center justify-center py-6 px-4">
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl border border-gray-200">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-green-500 rounded-t-xl">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-shipping-fast text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Konfirmasi Pengiriman</h3>
                        <p class="text-xs text-green-100" id="pengirimanModalSubtitle">Proses pengiriman forecast</p>
                    </div>
                </div>
                <button type="button" onclick="closePengirimanModal()" 
                        class="text-white hover:text-green-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Content --}}
            <div class="p-4 relative max-h-[80vh] overflow-y-auto">
                {{-- Loading State --}}
                <div id="pengirimanModalLoading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-green-500 mb-3"></div>
                    <p class="text-sm text-gray-500">Memuat detail forecast...</p>
                </div>

                {{-- Error State --}}
                <div id="pengirimanModalError" class="hidden text-center py-8">
                    <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    </div>
                    <h3 class="text-base font-medium text-gray-900 mb-1">Gagal Memuat Data</h3>
                    <p class="text-sm text-gray-500 mb-4">Terjadi kesalahan saat memuat detail forecast.</p>
                    <button type="button" onclick="retryLoadPengirimanDetail()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-redo mr-2"></i>Coba Lagi
                    </button>
                </div>

                {{-- Loading Overlay (saat submit) --}}
                <div id="pengirimanFormLoading" class="absolute inset-0 bg-white bg-opacity-75 items-center justify-center z-10 hidden rounded-lg">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500 mx-auto mb-2"></div>
                        <p class="text-sm text-gray-600">Memproses pengiriman...</p>
                    </div>
                </div>
                
                {{-- Main Content --}}
                <form id="pengirimanForm" class="space-y-4 hidden">
                    @csrf
                    <input type="hidden" id="pengirimanForecastId" name="forecast_id">

                    {{-- Info Section --}}
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-info-circle text-green-600 text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-green-800 mb-1">Informasi Pengiriman</h4>
                                <p class="text-xs text-green-700 leading-relaxed">
                                    Anda akan memproses pengiriman untuk forecast ini. Data forecast akan dipindahkan 
                                    ke tabel pengiriman dengan status <strong>"Pending"</strong> dan forecast akan 
                                    berubah status menjadi <strong>"Sukses"</strong>.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Forecast Info Summary --}}
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <h5 class="flex items-center text-xs font-semibold text-gray-800 mb-2">
                            <i class="fas fa-chart-bar text-gray-600 mr-2"></i>
                            Informasi Forecast
                        </h5>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600">No. Forecast:</span>
                                <span id="pengirimanNoForecast" class="font-medium text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Klien:</span>
                                <span id="pengirimanKlien" class="font-medium text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Qty:</span>
                                <span id="pengirimanTotalQty" class="font-medium text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Harga:</span>
                                <span id="pengirimanTotalHarga" class="font-medium text-gray-900">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Bahan Baku + Plat Nomor Truk Section --}}
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="p-3 bg-gray-50 border-b border-gray-200">
                            <h5 class="flex items-center text-xs font-semibold text-gray-800">
                                <i class="fas fa-truck-loading text-gray-600 mr-2"></i>
                                Detail Bahan Baku & Plat Nomor Truk
                            </h5>
                            <p class="text-xs text-gray-500 mt-1">
                                Isi plat nomor truk untuk masing-masing bahan baku (opsional).
                            </p>
                        </div>
                        <div id="pengirimanDetailsContainer" class="overflow-x-auto">
                            {{-- Diisi oleh JavaScript --}}
                        </div>
                    </div>

                    {{-- Note Section --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-start space-x-2">
                            <div class="w-5 h-5 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-lightbulb text-blue-600 text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="text-xs font-semibold text-blue-800 mb-1">Catatan Penting</h5>
                                <ul class="text-xs text-blue-700 space-y-1">
                                    <li>• Data pengiriman akan dibuat dengan status <strong>pending</strong></li>
                                    <li>• Qty dan harga pengiriman akan kosong untuk diisi nanti</li>
                                    <li>• Forecast akan berubah status menjadi <strong>sukses</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2 pt-3 border-t border-gray-200">
                        <button type="button" onclick="closePengirimanModal()" 
                                class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 font-medium text-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Kembali
                        </button>
                        <button type="submit" id="submitPengirimanBtn"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium text-sm">
                            <i class="fas fa-shipping-fast mr-1"></i>
                            Konfirmasi Pengiriman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Success/Error Toast for Pengiriman Modal --}}
<div id="pengirimanToast" class="fixed top-4 right-4 z-[10001] hidden">
    <div class="bg-white border-l-4 border-green-500 p-4 shadow-lg rounded-md max-w-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i id="pengirimanToastIcon" class="fas fa-check-circle text-green-500"></i>
            </div>
            <div class="ml-3">
                <p id="pengirimanToastMessage" class="text-sm font-medium text-gray-900"></p>
            </div>
            <div class="ml-auto pl-3">
                <button onclick="hidePengirimanToast()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variable to store current forecast data for pengiriman modal
if (typeof currentPengirimanForecastData === 'undefined') {
    var currentPengirimanForecastData = null;
}

// Open pengiriman modal — always fetches fresh data (mirip forecastDetailModal)
function openPengirimanModal(forecastData) {
    const modal = document.getElementById('pengirimanModal');

    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    currentPengirimanForecastData = forecastData;

    // Reset UI state
    document.getElementById('pengirimanModalLoading').classList.remove('hidden');
    document.getElementById('pengirimanModalError').classList.add('hidden');
    document.getElementById('pengirimanForm').classList.add('hidden');

    fetchPengirimanDetail(forecastData.id);
}

// Fetch fresh forecast detail (reuse endpoint yang sudah ada)
function fetchPengirimanDetail(forecastId) {
    const loading = document.getElementById('pengirimanModalLoading');
    const errorEl = document.getElementById('pengirimanModalError');
    const form = document.getElementById('pengirimanForm');

    loading.classList.remove('hidden');
    errorEl.classList.add('hidden');
    form.classList.add('hidden');

    fetch(`/procurement/forecasting/${forecastId}/detail`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.forecast) {
            currentPengirimanForecastData = data.forecast;
            populatePengirimanModal(data.forecast);
            loading.classList.add('hidden');
            form.classList.remove('hidden');
        } else {
            loading.classList.add('hidden');
            errorEl.classList.remove('hidden');
        }
    })
    .catch(() => {
        loading.classList.add('hidden');
        errorEl.classList.remove('hidden');
    });
}

function retryLoadPengirimanDetail() {
    if (currentPengirimanForecastData && currentPengirimanForecastData.id) {
        fetchPengirimanDetail(currentPengirimanForecastData.id);
    }
}

// Populate pengiriman modal with forecast data
function populatePengirimanModal(data) {
    try {
        document.getElementById('pengirimanForecastId').value = data.id || '';
        document.getElementById('pengirimanModalSubtitle').textContent = `Forecast: ${data.no_forecast || 'N/A'}`;
        document.getElementById('pengirimanNoForecast').textContent = data.no_forecast || 'N/A';
        document.getElementById('pengirimanKlien').textContent = data.klien || 'N/A';
        document.getElementById('pengirimanTotalQty').textContent = data.total_qty || '0';
        document.getElementById('pengirimanTotalHarga').textContent = data.total_harga || 'Rp 0';

        renderPengirimanDetailsTable(data.details || []);
    } catch (error) {
        showPengirimanToast('Gagal memuat data forecast', 'error');
    }
}

// Render tabel bahan baku dengan input plat nomor truk per baris
function renderPengirimanDetailsTable(details) {
    const container = document.getElementById('pengirimanDetailsContainer');

    if (!details || details.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-500 p-4">Tidak ada detail bahan baku.</p>';
        return;
    }

    let rows = details.map((detail, index) => `
        <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
            <td class="px-3 py-2 text-xs text-gray-700 whitespace-nowrap">${index + 1}</td>
            <td class="px-3 py-2 text-xs font-medium text-gray-900">${detail.bahan_baku || 'N/A'}</td>
            <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap">${detail.qty || '0'}</td>
            <td class="px-3 py-2">
                <input type="text"
                       name="plat_nomor[${detail.id}]"
                       placeholder="Opsional"
                       class="w-full px-2 py-1.5 border border-gray-300 rounded-md text-xs focus:ring-2 focus:ring-green-200 focus:border-green-500">
            </td>
        </tr>
    `).join('');

    container.innerHTML = `
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-700 uppercase">No.</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-700 uppercase">Bahan Baku</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-700 uppercase">Qty</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-700 uppercase">Plat Nomor Truk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                ${rows}
            </tbody>
        </table>
    `;
}

// Close pengiriman modal
function closePengirimanModal() {
    const modal = document.getElementById('pengirimanModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    document.getElementById('pengirimanForm').reset();
    document.getElementById('pengirimanDetailsContainer').innerHTML = '';
    currentPengirimanForecastData = null;
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const pengirimanForm = document.getElementById('pengirimanForm');

    if (pengirimanForm) {
        pengirimanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitPengiriman();
        });
    }

    const pengirimanModal = document.getElementById('pengirimanModal');
    if (pengirimanModal && pengirimanModal.parentElement !== document.body) {
        document.body.appendChild(pengirimanModal);
    }

    if (pengirimanModal) {
        pengirimanModal.addEventListener('click', function(e) {
            if (e.target === pengirimanModal) {
                closePengirimanModal();
            }
        });
    }
});

// Submit pengiriman
function submitPengiriman() {
    const submitBtn = document.getElementById('submitPengirimanBtn');
    const loadingOverlay = document.getElementById('pengirimanFormLoading');

    if (!submitBtn) {
        showPengirimanToast('Terjadi kesalahan pada form. Silakan refresh halaman.', 'error');
        return;
    }

    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
    submitBtn.disabled = true;

    if (loadingOverlay) {
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');
    }

    if (!currentPengirimanForecastData?.id) {
        resetPengirimanFormState(submitBtn, loadingOverlay, originalText);
        showPengirimanToast('Data forecast tidak valid. Silakan refresh halaman.', 'error');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        resetPengirimanFormState(submitBtn, loadingOverlay, originalText);
        showPengirimanToast('CSRF token tidak ditemukan. Silakan refresh halaman.', 'error');
        return;
    }

    // Prepare form data — termasuk plat_nomor[detail_id] dari semua input
    const formData = new FormData(document.getElementById('pengirimanForm'));
    formData.set('_token', csrfToken);

    showPengirimanToast('Memproses pengiriman forecast...', 'warning');

    const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Request timeout after 30 seconds')), 30000);
    });

    const fetchPromise = fetch(`/procurement/forecasting/${currentPengirimanForecastData?.id}/kirim`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    });

    Promise.race([fetchPromise, timeoutPromise])
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        resetPengirimanFormState(submitBtn, loadingOverlay, originalText);

        if (data.success) {
            showPengirimanToast(`Forecast "${currentPengirimanForecastData?.no_forecast}" berhasil dikirim!`, 'success');

            setTimeout(() => {
                closePengirimanModal();

                const detailModal = document.getElementById('forecastDetailModal');
                if (detailModal && !detailModal.classList.contains('hidden')) {
                    closeForecastDetailModal();
                }

                window.location.reload();
            }, 1500);
        } else {
            if (data.message) {
                showPengirimanToast(data.message, 'error');
            }
        }
    })
    .catch(error => {
        resetPengirimanFormState(submitBtn, loadingOverlay, originalText);

        if (error.message.includes('Request timeout')) {
            showPengirimanToast('Request timeout. Silakan coba lagi.', 'error');
        } else if (error.name === 'AbortError') {
            showPengirimanToast('Request dibatalkan atau timeout. Silakan coba lagi.', 'error');
        } else if (error.message.includes('HTTP 500')) {
            showPengirimanToast('Terjadi kesalahan server. Silakan coba lagi.', 'error');
        } else if (error.message.includes('HTTP 422')) {
            showPengirimanToast('Data tidak valid. Periksa form dan coba lagi.', 'error');
        } else if (error.message.includes('HTTP 404')) {
            showPengirimanToast('Forecast tidak ditemukan. Silakan refresh halaman.', 'error');
        } else if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
            showPengirimanToast('Koneksi bermasalah. Periksa internet Anda.', 'error');
        }
    });
}

// Helper function to reset form state
function resetPengirimanFormState(submitBtn, loadingOverlay, originalText) {
    if (submitBtn) {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }

    if (loadingOverlay) {
        loadingOverlay.classList.add('hidden');
        loadingOverlay.classList.remove('flex');
    }
}

// Show toast notification for pengiriman modal
function showPengirimanToast(message, type = 'success') {
    const toast = document.getElementById('pengirimanToast');
    const icon = document.getElementById('pengirimanToastIcon');
    const messageEl = document.getElementById('pengirimanToastMessage');
    const toastContainer = toast.querySelector('div');

    messageEl.textContent = message;

    switch (type) {
        case 'success':
            icon.className = 'fas fa-check-circle text-green-500';
            toastContainer.className = 'bg-white border-l-4 border-green-500 p-4 shadow-lg rounded-md max-w-sm';
            break;
        case 'error':
            icon.className = 'fas fa-times-circle text-red-500';
            toastContainer.className = 'bg-white border-l-4 border-red-500 p-4 shadow-lg rounded-md max-w-sm';
            break;
        case 'warning':
            icon.className = 'fas fa-exclamation-triangle text-yellow-500';
            toastContainer.className = 'bg-white border-l-4 border-yellow-500 p-4 shadow-lg rounded-md max-w-sm';
            break;
    }

    toast.classList.remove('hidden');

    setTimeout(() => {
        hidePengirimanToast();
    }, 5000);
}

function hidePengirimanToast() {
    document.getElementById('pengirimanToast').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const pengirimanModal = document.getElementById('pengirimanModal');
        const batalModal = document.getElementById('batalPengirimanModal');

        if (pengirimanModal && !pengirimanModal.classList.contains('hidden')) {
            if (!batalModal || batalModal.classList.contains('hidden')) {
                closePengirimanModal();
            }
        }
    }
});
</script>

<style>
#pengirimanModal {
    backdrop-filter: blur(4px);
    position: fixed !important;
    z-index: 10000 !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
}

#pengirimanModal > div {
    position: relative;
    z-index: 10001;
    width: 100%;
    height: 100%;
}

#pengirimanModal .relative.bg-white {
    position: relative;
    z-index: 10002;
}

#pengirimanModal > div > div {
    animation: pengirimanModalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes pengirimanModalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-32px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

#pengirimanToast {
    animation: pengirimanToastSlideIn 0.3s ease-out;
}

@keyframes pengirimanToastSlideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

#pengirimanModal .shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(34, 197, 94, 0.25);
}

@media (max-width: 640px) {
    #pengirimanModal > div {
        width: 90%;
        margin: 0.5rem;
        padding: 0;
    }

    .grid.grid-cols-2 {
        grid-template-columns: 1fr;
    }

    .flex.gap-2 {
        flex-direction: column;
    }
}

#submitPengirimanBtn .fa-spinner {
    animation: spin 1s linear infinite;
}
</style>