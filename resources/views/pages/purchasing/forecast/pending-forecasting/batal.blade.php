{{-- Modal Batal Pengiriman --}}
<div id="batalPengirimanModal" class="fixed inset-0 overflow-y-auto h-full w-full z-[10000] hidden">
    <div class="relative min-h-screen flex items-center justify-center py-6 px-4">
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg border border-gray-200">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-red-500 rounded-t-xl">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-times-circle text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Batal Pengiriman</h3>
                        <p class="text-xs text-red-100" id="batalModalSubtitle">Pembatalan forecast pengiriman</p>
                    </div>
                </div>
                <button type="button" onclick="closeBatalPengirimanModal()" 
                        class="text-white hover:text-red-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Content --}}
            <div class="p-4 relative">
                {{-- Loading Overlay --}}
                <div id="batalFormLoading" class="absolute inset-0 bg-white bg-opacity-75 items-center justify-center z-10 hidden rounded-lg">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-500 mx-auto mb-2"></div>
                        <p class="text-sm text-gray-600">Memproses pembatalan...</p>
                    </div>
                </div>
                
                <form id="batalPengirimanForm" class="space-y-4">
                    @csrf
                    <input type="hidden" id="batalForecastId" name="forecast_id">

                    {{-- Warning Section --}}
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-red-800 mb-1">Peringatan Pembatalan</h4>
                                <p class="text-xs text-red-700 leading-relaxed">
                                    Anda akan membatalkan pengiriman untuk forecast ini. Data forecast akan dipindahkan 
                                    ke tabel pengiriman dengan status <strong>"Gagal"</strong> dan forecast akan 
                                    berubah status menjadi <strong>"Gagal"</strong>.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Forecast Info Summary --}}
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <h5 class="flex items-center text-xs font-semibold text-gray-800 mb-2">
                            <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                            Informasi Forecast
                        </h5>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600">No. Forecast:</span>
                                <span id="batalNoForecast" class="font-medium text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Klien:</span>
                                <span id="batalKlien" class="font-medium text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Qty:</span>
                                <span id="batalTotalQty" class="font-medium text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Harga:</span>
                                <span id="batalTotalHarga" class="font-medium text-gray-900">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Reason Input Section --}}
                    <div class="space-y-3">
                        <div>
                            <label for="alasanBatal" class="flex items-center text-xs font-medium text-gray-700 mb-2">
                                <i class="fas fa-comment-alt text-gray-500 mr-2"></i>
                                Alasan Pembatalan <span class="text-red-500 ml-1">*</span>
                            </label>
                            <textarea 
                                id="alasanBatal" 
                                name="alasan_batal" 
                                rows="3" 
                                placeholder="Jelaskan alasan mengapa pengiriman forecast ini dibatalkan..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 resize-none text-sm"
                                required></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Minimal 10 karakter.
                            </p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2 pt-3 border-t border-gray-200">
                        <button type="button" onclick="closeBatalPengirimanModal()" 
                                class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 font-medium text-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Kembali
                        </button>
                        <button type="submit" id="submitBatalBtn"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium text-sm">
                            <i class="fas fa-times-circle mr-1"></i>
                            Konfirmasi Pembatalan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Success/Error Toast for Batal Modal --}}
<div id="batalToast" class="fixed top-4 right-4 z-[10001] hidden">
    <div class="bg-white border-l-4 border-red-500 p-4 shadow-lg rounded-md max-w-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i id="batalToastIcon" class="fas fa-times-circle text-red-500"></i>
            </div>
            <div class="ml-3">
                <p id="batalToastMessage" class="text-sm font-medium text-gray-900"></p>
            </div>
            <div class="ml-auto pl-3">
                <button onclick="hideBatalToast()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variable to store current forecast data for batal modal
if (typeof currentBatalForecastData === 'undefined') {
    var currentBatalForecastData = null;
}

// Open batal pengiriman modal
function openBatalPengirimanModal(forecastData) {
    console.log('Opening batal pengiriman modal with data:', forecastData);
    
    const modal = document.getElementById('batalPengirimanModal');
    
    // Ensure modal is attached to body to avoid container issues
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
    
    // Store forecast data
    currentBatalForecastData = forecastData;
    
    // Populate modal with forecast data
    populateBatalModal(forecastData);
}

// Populate batal modal with forecast data
function populateBatalModal(data) {
    try {
        console.log('Populating batal modal with data:', data);
        
        // Set hidden forecast ID
        document.getElementById('batalForecastId').value = data.id || '';
        
        // Update modal subtitle
        document.getElementById('batalModalSubtitle').textContent = `Forecast: ${data.no_forecast || 'N/A'}`;
        
        // Populate forecast info summary with proper data handling
        document.getElementById('batalNoForecast').textContent = data.no_forecast || 'N/A';
        
        // Handle klien data - the data comes as formatted string from controller
        document.getElementById('batalKlien').textContent = data.klien || 'N/A';
        
        // Handle total qty - the data comes as formatted string from controller
        document.getElementById('batalTotalQty').textContent = data.total_qty || '0';
        
        // Handle total harga - the data comes as formatted string from controller
        document.getElementById('batalTotalHarga').textContent = data.total_harga || 'Rp 0';
        
        // Clear previous reason input
        document.getElementById('alasanBatal').value = '';
        
        console.log('Modal populated with:', {
            no_forecast: data.no_forecast,
            klien: data.klien,
            total_qty: data.total_qty,
            total_harga: data.total_harga
        });
        
    } catch (error) {
        console.error('Error populating batal modal:', error);
        showBatalToast('Gagal memuat data forecast', 'error');
    }
}

// Close batal pengiriman modal
function closeBatalPengirimanModal() {
    const modal = document.getElementById('batalPengirimanModal');
    modal.classList.add('hidden');
    
    // Restore body scroll
    document.body.style.overflow = '';
    
    // Reset form
    document.getElementById('batalPengirimanForm').reset();
    currentBatalForecastData = null;
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const batalForm = document.getElementById('batalPengirimanForm');
    
    if (batalForm) {
        batalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const alasanBatal = document.getElementById('alasanBatal').value.trim();
            
            // Validation
            if (alasanBatal.length < 10) {
                showBatalToast('Alasan pembatalan minimal 10 karakter', 'error');
                document.getElementById('alasanBatal').focus();
                return;
            }
            
            // Langsung submit tanpa konfirmasi modal
            submitBatalPengiriman(alasanBatal);
        });
    }
    
    // Move modal to body when DOM is ready
    const batalModal = document.getElementById('batalPengirimanModal');
    if (batalModal && batalModal.parentElement !== document.body) {
        document.body.appendChild(batalModal);
    }
    
    // Close modal when clicking outside
    if (batalModal) {
        batalModal.addEventListener('click', function(e) {
            if (e.target === batalModal) {
                closeBatalPengirimanModal();
            }
        });
    }
});

// Submit batal pengiriman
function submitBatalPengiriman(alasanBatal) {
    const submitBtn = document.getElementById('submitBatalBtn');
    const loadingOverlay = document.getElementById('batalFormLoading');
    
    // Check if required elements exist
    if (!submitBtn) {
        console.error('Submit button not found');
        showBatalToast('Terjadi kesalahan pada form. Silakan refresh halaman.', 'error');
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
    
    // Disable form elements
    const alasanInput = document.getElementById('alasanBatal');
    if (alasanInput) {
        alasanInput.disabled = true;
    }
    
    // Validate forecast data
    if (!currentBatalForecastData?.id) {
        resetBatalFormState(submitBtn, loadingOverlay, originalText);
        showBatalToast('Data forecast tidak valid. Silakan refresh halaman.', 'error');
        return;
    }
    
    console.log('Submitting batal pengiriman:', {
        forecast_id: currentBatalForecastData?.id,
        alasan_batal: alasanBatal,
        no_forecast: currentBatalForecastData?.no_forecast
    });
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        resetBatalFormState(submitBtn, loadingOverlay, originalText);
        showBatalToast('CSRF token tidak ditemukan. Silakan refresh halaman.', 'error');
        return;
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('alasan_batal', alasanBatal);
    formData.append('_token', csrfToken);
    
    // Show immediate feedback
    showBatalToast('Memproses pembatalan forecast...', 'warning');
    
    console.log('Starting fetch request to:', `/procurement/forecast/${currentBatalForecastData?.id}/batal`);
    console.log('Request payload:', {
        alasan_batal: alasanBatal,
        _token: csrfToken,
        forecast_id: currentBatalForecastData?.id
    });
    
    // Simple fetch request without complications
    console.log('Making fetch request now...');
    
    // Add a manual timeout using Promise.race
    const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Request timeout after 30 seconds')), 30000);
    });
    
    const fetchPromise = fetch(`/procurement/forecasting/${currentBatalForecastData?.id}/batal`, {
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
        resetBatalFormState(submitBtn, loadingOverlay, originalText);
        
        if (data.success) {
            // Show success message
            showBatalToast(`Forecast "${currentBatalForecastData?.no_forecast}" berhasil dibatalkan!`, 'success');
            
            // Close modal immediately
            setTimeout(() => {
                closeBatalPengirimanModal();
                
                // Close detail modal if open
                const detailModal = document.getElementById('forecastDetailModal');
                if (detailModal && !detailModal.classList.contains('hidden')) {
                    closeForecastDetailModal();
                }
                
                // Refresh page
                window.location.reload();
            }, 1500); // Reduced delay
        } else {
            // Show error message from server
            if (data.message) {
                showBatalToast(data.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error submitting batal pengiriman:', {
            error: error,
            errorName: error.name,
            errorMessage: error.message,
            errorStack: error.stack,
            forecastId: currentBatalForecastData?.id,
            url: `/procurement/forecasting/${currentBatalForecastData?.id}/batal`
        });
        
        // Reset UI elements
        resetBatalFormState(submitBtn, loadingOverlay, originalText);
        
        // Handle specific error types only
        if (error.message.includes('Request timeout')) {
            showBatalToast('Request timeout. Silakan coba lagi.', 'error');
        } else if (error.name === 'AbortError') {
            showBatalToast('Request dibatalkan atau timeout. Silakan coba lagi.', 'error');
        } else if (error.message.includes('HTTP 500')) {
            showBatalToast('Terjadi kesalahan server. Silakan coba lagi.', 'error');
        } else if (error.message.includes('HTTP 422')) {
            showBatalToast('Data tidak valid. Periksa form dan coba lagi.', 'error');
        } else if (error.message.includes('HTTP 404')) {
            showBatalToast('Forecast tidak ditemukan. Silakan refresh halaman.', 'error');
        } else if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
            showBatalToast('Koneksi bermasalah. Periksa internet Anda.', 'error');
        }
    });
}


// Helper function to reset form state
function resetBatalFormState(submitBtn, loadingOverlay, originalText) {
    if (submitBtn) {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
    
    const alasanInput = document.getElementById('alasanBatal');
    if (alasanInput) {
        alasanInput.disabled = false;
    }
    
    if (loadingOverlay) {
        loadingOverlay.classList.add('hidden');
        loadingOverlay.classList.remove('flex');
    }
}

// Show toast notification for batal modal
function showBatalToast(message, type = 'error') {
    const toast = document.getElementById('batalToast');
    const icon = document.getElementById('batalToastIcon');
    const messageEl = document.getElementById('batalToastMessage');
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
        hideBatalToast();
    }, 5000);
}

// Hide batal toast notification
function hideBatalToast() {
    document.getElementById('batalToast').classList.add('hidden');
}

// Handle escape key for batal modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const batalModal = document.getElementById('batalPengirimanModal');
        if (batalModal && !batalModal.classList.contains('hidden')) {
            closeBatalPengirimanModal();
        }
    }
});
</script>

<style>
/* Modal styles for batal pengiriman - Ensure highest priority */
#batalPengirimanModal {
    backdrop-filter: blur(4px);
    position: fixed !important;
    z-index: 10000 !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
}

/* Ensure modal content is properly positioned */
#batalPengirimanModal > div {
    position: relative;
    z-index: 10001;
    width: 100%;
    height: 100%;
}

/* Modal content container */
#batalPengirimanModal .relative.bg-white {
    position: relative;
    z-index: 10002;
}

/* Form input focus styles */
#batalPengirimanModal input:focus,
#batalPengirimanModal select:focus,
#batalPengirimanModal textarea:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    border-color: #ef4444;
    outline: none;
}

/* Animation for batal modal */
#batalPengirimanModal > div > div {
    animation: batalModalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes batalModalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-32px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Batal toast animation */
#batalToast {
    animation: batalToastSlideIn 0.3s ease-out;
}

@keyframes batalToastSlideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Textarea character counter */
#alasanBatal:focus + .text-xs::after {
    content: " (" attr(data-count) "/500)";
    color: #6b7280;
}

/* Enhanced shadows for batal modal */
#batalPengirimanModal .shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(239, 68, 68, 0.25);
}

/* Responsive adjustments for batal modal */
@media (max-width: 640px) {
    #batalPengirimanModal > div {
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

/* Loading spinner for batal modal */
#submitBatalBtn .fa-spinner {
    animation: spin 1s linear infinite;
}
</style>
