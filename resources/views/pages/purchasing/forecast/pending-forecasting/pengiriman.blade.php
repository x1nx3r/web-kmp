{{-- Modal Pengiriman --}}
<div id="pengirimanModal" class="fixed inset-0 overflow-y-auto h-full w-full z-[10000] hidden">
    <div class="relative min-h-screen flex items-center justify-center py-6 px-4">
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg border border-gray-200">
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
            <div class="p-4 relative">
                {{-- Loading Overlay --}}
                <div id="pengirimanFormLoading" class="absolute inset-0 bg-white bg-opacity-75 items-center justify-center z-10 hidden rounded-lg">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500 mx-auto mb-2"></div>
                        <p class="text-sm text-gray-600">Memproses pengiriman...</p>
                    </div>
                </div>
                
                <form id="pengirimanForm" class="space-y-4">
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
                                    <li>• Detail pengiriman akan kosong untuk diisi nanti</li>
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

// Open pengiriman modal
function openPengirimanModal(forecastData) {
    console.log('Opening pengiriman modal with data:', forecastData);
    
    const modal = document.getElementById('pengirimanModal');
    
    // Ensure modal is attached to body to avoid container issues
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
    
    // Store forecast data
    currentPengirimanForecastData = forecastData;
    
    // Populate modal with forecast data
    populatePengirimanModal(forecastData);
}

// Populate pengiriman modal with forecast data
function populatePengirimanModal(data) {
    try {
        console.log('Populating pengiriman modal with data:', data);
        
        // Set hidden forecast ID
        document.getElementById('pengirimanForecastId').value = data.id || '';
        
        // Update modal subtitle
        document.getElementById('pengirimanModalSubtitle').textContent = `Forecast: ${data.no_forecast || 'N/A'}`;
        
        // Populate forecast info summary with proper data handling
        document.getElementById('pengirimanNoForecast').textContent = data.no_forecast || 'N/A';
        
        // Handle klien data - the data comes as formatted string from controller
        document.getElementById('pengirimanKlien').textContent = data.klien || 'N/A';
        
        // Handle total qty - the data comes as formatted string from controller
        document.getElementById('pengirimanTotalQty').textContent = data.total_qty || '0';
        
        // Handle total harga - the data comes as formatted string from controller
        document.getElementById('pengirimanTotalHarga').textContent = data.total_harga || 'Rp 0';
        
        console.log('Pengiriman modal populated with:', {
            no_forecast: data.no_forecast,
            klien: data.klien,
            total_qty: data.total_qty,
            total_harga: data.total_harga
        });
        
    } catch (error) {
        console.error('Error populating pengiriman modal:', error);
        showPengirimanToast('Gagal memuat data forecast', 'error');
    }
}

// Close pengiriman modal
function closePengirimanModal() {
    const modal = document.getElementById('pengirimanModal');
    modal.classList.add('hidden');
    
    // Restore body scroll
    document.body.style.overflow = '';
    
    // Reset form
    document.getElementById('pengirimanForm').reset();
    currentPengirimanForecastData = null;
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const pengirimanForm = document.getElementById('pengirimanForm');
    
    if (pengirimanForm) {
        pengirimanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Direct submit without additional validation since no input fields required
            submitPengiriman();
        });
    }
    
    // Move modal to body when DOM is ready
    const pengirimanModal = document.getElementById('pengirimanModal');
    if (pengirimanModal && pengirimanModal.parentElement !== document.body) {
        document.body.appendChild(pengirimanModal);
    }
    
    // Close modal when clicking outside
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
    
    // Check if required elements exist
    if (!submitBtn) {
        console.error('Submit button not found');
        showPengirimanToast('Terjadi kesalahan pada form. Silakan refresh halaman.', 'error');
        return;
    }
    
    const originalText = submitBtn.innerHTML;
    
    // Show loading state immediately
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
    submitBtn.disabled = true;
    
    // Show loading overlay if it exists
    if (loadingOverlay) {
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');
    }
    
    // Validate forecast data
    if (!currentPengirimanForecastData?.id) {
        resetPengirimanFormState(submitBtn, loadingOverlay, originalText);
        showPengirimanToast('Data forecast tidak valid. Silakan refresh halaman.', 'error');
        return;
    }
    
    console.log('Submitting pengiriman:', {
        forecast_id: currentPengirimanForecastData?.id,
        no_forecast: currentPengirimanForecastData?.no_forecast
    });
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        resetPengirimanFormState(submitBtn, loadingOverlay, originalText);
        showPengirimanToast('CSRF token tidak ditemukan. Silakan refresh halaman.', 'error');
        return;
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('_token', csrfToken);
    
    // Show immediate feedback
    showPengirimanToast('Memproses pengiriman forecast...', 'warning');
    
    console.log('Starting fetch request to:', `/purchasing/forecast/${currentPengirimanForecastData?.id}/kirim`);
    console.log('Request payload:', {
        _token: csrfToken,
        forecast_id: currentPengirimanForecastData?.id
    });
    
    // Simple fetch request
    console.log('Making fetch request now...');
    
    // Add a manual timeout using Promise.race
    const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Request timeout after 30 seconds')), 30000);
    });
    
    const fetchPromise = fetch(`/purchasing/forecast/${currentPengirimanForecastData?.id}/kirim`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    });
    
    // Race between fetch and timeout
    Promise.race([fetchPromise, timeoutPromise])
    .then(response => {
        console.log('Response received:', response);
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        console.log('About to parse JSON response...');
        return response.json();
    })
    .then(data => {
        console.log('JSON parsed successfully, response data:', data);
        console.log('Success response received:', data);
        
        // Reset UI elements
        resetPengirimanFormState(submitBtn, loadingOverlay, originalText);
        
        if (data.success) {
            // Show success message
            showPengirimanToast(`Forecast "${currentPengirimanForecastData?.no_forecast}" berhasil dikirim!`, 'success');
            
            // Close modal immediately
            setTimeout(() => {
                closePengirimanModal();
                
                // Close detail modal if open
                const detailModal = document.getElementById('forecastDetailModal');
                if (detailModal && !detailModal.classList.contains('hidden')) {
                    closeForecastDetailModal();
                }
                
                // Refresh page
                window.location.reload();
            }, 1500); // Reduced delay
        } else {
            // Show error message
            showPengirimanToast(data.message || 'Gagal mengirim forecast. Silakan coba lagi.', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting pengiriman:', {
            error: error,
            errorName: error.name,
            errorMessage: error.message,
            errorStack: error.stack,
            forecastId: currentPengirimanForecastData?.id,
            url: `/purchasing/forecast/${currentPengirimanForecastData?.id}/kirim`
        });
        
        // Reset UI elements
        resetPengirimanFormState(submitBtn, loadingOverlay, originalText);
        
        // Handle different error types
        let errorMessage = 'Gagal mengirim forecast. Silakan coba lagi.';
        
        if (error.message.includes('Request timeout')) {
            errorMessage = 'Request timeout. Silakan coba lagi.';
            console.warn('Request timed out after 30 seconds');
        } else if (error.name === 'AbortError') {
            errorMessage = 'Request dibatalkan atau timeout. Silakan coba lagi.';
            console.warn('Request was aborted - possible causes: timeout, network interruption, or manual cancellation');
        } else if (error.message.includes('HTTP 500')) {
            errorMessage = 'Terjadi kesalahan server. Silakan coba lagi.';
        } else if (error.message.includes('HTTP 422')) {
            errorMessage = 'Data tidak valid. Periksa form dan coba lagi.';
        } else if (error.message.includes('HTTP 404')) {
            errorMessage = 'Forecast tidak ditemukan. Silakan refresh halaman.';
        } else if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
            errorMessage = 'Koneksi bermasalah. Periksa internet Anda.';
        }
        
        showPengirimanToast(errorMessage, 'error');
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
    
    // Set message
    messageEl.textContent = message;
    
    // Set icon and color based on type
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
    
    // Show toast
    toast.classList.remove('hidden');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hidePengirimanToast();
    }, 5000);
}

// Hide pengiriman toast notification
function hidePengirimanToast() {
    document.getElementById('pengirimanToast').classList.add('hidden');
}

// Handle escape key for pengiriman modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const pengirimanModal = document.getElementById('pengirimanModal');
        const batalModal = document.getElementById('batalPengirimanModal');
        
        // Close pengiriman modal if it's open and no other modal is open
        if (pengirimanModal && !pengirimanModal.classList.contains('hidden')) {
            // Only close if batal modal is not also open
            if (!batalModal || batalModal.classList.contains('hidden')) {
                closePengirimanModal();
            }
        }
    }
});
</script>

<style>
/* Modal styles for pengiriman - Ensure highest priority */
#pengirimanModal {
    backdrop-filter: blur(4px);
    position: fixed !important;
    z-index: 10000 !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
}

/* Ensure modal content is properly positioned */
#pengirimanModal > div {
    position: relative;
    z-index: 10001;
    width: 100%;
    height: 100%;
}

/* Modal content container */
#pengirimanModal .relative.bg-white {
    position: relative;
    z-index: 10002;
}

/* Animation for pengiriman modal */
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

/* Pengiriman toast animation */
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

/* Enhanced shadows for pengiriman modal */
#pengirimanModal .shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(34, 197, 94, 0.25);
}

/* Responsive adjustments for pengiriman modal */
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

/* Loading spinner for pengiriman modal */
#submitPengirimanBtn .fa-spinner {
    animation: spin 1s linear infinite;
}
</style>
