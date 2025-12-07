@extends('layouts.app')
@section('title', 'Pengiriman - Kamil Maju Persada')
@section('content')
<x-welcome-banner title="Pengiriman" subtitle="Atur Pengiriman ke Pabrik" icon="fas fa-shipping-fast" />
{{-- Breadcrumb --}}
<div id="dynamicBreadcrumb">
    {{-- Default breadcrumb, akan diupdate via JavaScript --}}
    <x-breadcrumb :items="[
        ['title' => 'Pengiriman', 'url' => route('purchasing.pengiriman.index')]
    ]" />
</div>

{{-- Tabs Navigation --}}
<div class="mb-6">
    <div class="border-b-2">
        {{-- Desktop Navigation --}}
        <nav class="-mb-px hidden sm:flex justify-between px-16" aria-label="Tabs">
            <button onclick="switchTab('pengiriman-masuk')" 
                    id="tab-pengiriman-masuk" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-inbox mr-2"></i>
                Pengiriman Masuk
                <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="masuk-count">0</span>
            </button>
            <button onclick="switchTab('menunggu-fisik')" 
                    id="tab-menunggu-fisik" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-box-open mr-2"></i>
                Menunggu Fisik
                <span class="ml-2 bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="fisik-count">0</span>
            </button>
            <button onclick="switchTab('menunggu-verifikasi')" 
                    id="tab-menunggu-verifikasi" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-clock mr-2"></i>
                Menunggu Verifikasi
                <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="verifikasi-count">0</span>
            </button>
            
            <button onclick="switchTab('pengiriman-berhasil')" 
                    id="tab-pengiriman-berhasil" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-check-circle mr-2"></i>
                Pengiriman Berhasil
                <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="berhasil-count">0</span>
            </button>
            <button onclick="switchTab('pengiriman-gagal')" 
                    id="tab-pengiriman-gagal" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-times-circle mr-2"></i>
                Pengiriman Gagal
                <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="gagal-count">0</span>
            </button>
        </nav>

        {{-- Mobile Navigation --}}
        <nav class="-mb-px flex sm:hidden overflow-x-auto scrollbar-hide justify-between px-8" aria-label="Tabs">
            <button onclick="switchTab('pengiriman-masuk')" 
                    id="tab-pengiriman-masuk-mobile" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max">
                <div class="flex flex-col items-center space-y-1">
                    <div class="relative">
                        <i class="fas fa-inbox text-sm"></i>
                        <span class="absolute -top-2 -right-2 bg-blue-100 text-blue-800 text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center" id="masuk-count-mobile">0</span>
                    </div>
                    <span>Masuk</span>
                </div>
            </button>
            <button onclick="switchTab('menunggu-fisik')" 
                    id="tab-menunggu-fisik-mobile" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max relative">
                <div class="flex flex-col items-center space-y-1">
                    <div class="relative">
                        <i class="fas fa-box-open text-sm"></i>
                        <span class="absolute -top-2 -right-2 bg-purple-100 text-purple-800 text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center" id="fisik-count-mobile">0</span>
                    </div>
                    <span>Fisik</span>
                </div>
            </button>
            <button onclick="switchTab('menunggu-verifikasi')" 
                    id="tab-menunggu-verifikasi-mobile" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max relative">
                <div class="flex flex-col items-center space-y-1">
                    <div class="relative">
                        <i class="fas fa-clock text-sm"></i>
                        <span class="absolute -top-2 -right-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center" id="verifikasi-count-mobile">0</span>
                    </div>
                    <span>Verifikasi</span>
                </div>
            </button>
            
            <button onclick="switchTab('pengiriman-berhasil')" 
                    id="tab-pengiriman-berhasil-mobile" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max relative">
                <div class="flex flex-col items-center space-y-1">
                    <div class="relative">
                        <i class="fas fa-check-circle text-sm"></i>
                        <span class="absolute -top-2 -right-2 bg-green-100 text-green-800 text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center" id="berhasil-count-mobile">0</span>
                    </div>
                    <span>Berhasil</span>
                </div>
            </button>
            <button onclick="switchTab('pengiriman-gagal')" 
                    id="tab-pengiriman-gagal-mobile" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max relative">
                <div class="flex flex-col items-center space-y-1">
                    <div class="relative">
                        <i class="fas fa-times-circle text-sm"></i>
                        <span class="absolute -top-2 -right-2 bg-red-100 text-red-800 text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center" id="gagal-count-mobile">0</span>
                    </div>
                    <span>Gagal</span>
                </div>
            </button>
        </nav>
    </div>
</div>



@push('styles')
<style>
.tab-button {
    @apply border-transparent text-gray-500 hover:text-green-600 hover:border-green-300;
}

.tab-button.active {
    @apply border-green-500 text-green-600;
}

.tab-content {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}

.tab-content:not(.hidden) {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tab-content-wrapper .space-y-6 > * + * {
    margin-top: 0;
}

.tab-content-wrapper .space-y-6 {
    margin: 0;
    padding: 0;
}
</style>
@endpush

@push('scripts')
<script>
// Tab switching functionality
function updateUrl(tabName) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    
    // Get current tab to check if we're switching tabs
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'pengiriman-masuk';
    
    // Only clean pagination if we're actually switching tabs
    if (tabName !== currentTab) {
        // Remove pagination parameters from other tabs only
        if (tabName !== 'pengiriman-masuk') {
            url.searchParams.delete('page');
        }
        if (tabName !== 'menunggu-verifikasi') {
            url.searchParams.delete('page_menunggu');
        }
        if (tabName !== 'menunggu-fisik') {
            url.searchParams.delete('page_fisik');
        }
        if (tabName !== 'pengiriman-berhasil') {
            url.searchParams.delete('page_berhasil');
        }
        if (tabName !== 'pengiriman-gagal') {
            url.searchParams.delete('page_gagal');
        }
    }
    // If we're staying on the same tab, preserve all pagination parameters
    
    window.history.pushState({ tab: tabName }, '', url);
}

function switchTab(tabName) {
    console.log('Switching to tab:', tabName);
    
    // Check if we're already on the target tab to avoid unnecessary updates
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'pengiriman-masuk';
    
    // If we're already on this tab, just return without doing anything
    if (tabName === currentTab) {
        console.log('Already on tab:', tabName);
        return;
    }
    
    // Remove active class from all tabs (both desktop and mobile)
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.classList.remove('active', 'border-green-500', 'text-green-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Hide all tab content properly
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.style.display = 'none';
        content.style.opacity = '0';
        content.style.visibility = 'hidden';
        console.log('Hiding content:', content.id);
    });
    
    // Small delay to ensure proper hiding
    setTimeout(() => {
        // Show active tab content
        const activeContent = document.getElementById(tabName);
        if (activeContent) {
            activeContent.classList.remove('hidden');
            activeContent.style.display = 'block';
            activeContent.style.opacity = '1';
            activeContent.style.visibility = 'visible';
            console.log('Showing content:', activeContent.id);
        }
    }, 50);
    
    // Add active class to clicked tab (both desktop and mobile versions)
    const activeTabDesktop = document.getElementById('tab-' + tabName);
    const activeTabMobile = document.getElementById('tab-' + tabName + '-mobile');
    
    if (activeTabDesktop) {
        activeTabDesktop.classList.remove('border-transparent', 'text-gray-500');
        activeTabDesktop.classList.add('active', 'border-green-500', 'text-green-600');
    }
    
    if (activeTabMobile) {
        activeTabMobile.classList.remove('border-transparent', 'text-gray-500');
        activeTabMobile.classList.add('active', 'border-green-500', 'text-green-600');
    }
    
    // Update URL with tab parameter only when switching tabs
    updateUrl(tabName);
    
    // Update breadcrumb
    updateBreadcrumb(tabName);
    
    // Load data for the active tab
    loadTabData(tabName);
}

// Function to handle pagination without changing tabs
function handlePagination(pageUrl) {
    // Extract the current tab from URL to ensure we stay on it
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'pengiriman-masuk';
    
    // Parse the pagination URL to extract parameters
    const url = new URL(pageUrl, window.location.origin);
    const pageParams = new URLSearchParams(url.search);
    
    // Ensure tab parameter is preserved
    pageParams.set('tab', currentTab);
    
    // Navigate to the pagination URL with preserved tab
    window.location.href = url.pathname + '?' + pageParams.toString();
}

// Function to initialize tab based on URL parameter
function initializeTabFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'pengiriman-masuk';
    
    console.log('Initializing tab from URL:', activeTab);
    console.log('Current URL params:', Object.fromEntries(urlParams));
    
    // Validate tab exists
    const validTabs = ['pengiriman-masuk', 'menunggu-verifikasi', 'menunggu-fisik', 'pengiriman-berhasil', 'pengiriman-gagal'];
    if (!validTabs.includes(activeTab)) {
        activeTab = 'pengiriman-masuk';
    }
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.classList.remove('active', 'border-green-500', 'text-green-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Hide all tab content properly
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.style.display = 'none';
        content.style.opacity = '0';
        content.style.visibility = 'hidden';
    });
    
    // Show active tab content
    const activeContent = document.getElementById(activeTab);
    if (activeContent) {
        activeContent.classList.remove('hidden');
        activeContent.style.display = 'block';
        activeContent.style.opacity = '1';
        activeContent.style.visibility = 'visible';
    }
    
    // Add active class to active tab (both desktop and mobile versions)
    const activeTabDesktop = document.getElementById('tab-' + activeTab);
    const activeTabMobile = document.getElementById('tab-' + activeTab + '-mobile');
    
    if (activeTabDesktop) {
        activeTabDesktop.classList.remove('border-transparent', 'text-gray-500');
        activeTabDesktop.classList.add('active', 'border-green-500', 'text-green-600');
    }
    
    if (activeTabMobile) {
        activeTabMobile.classList.remove('border-transparent', 'text-gray-500');
        activeTabMobile.classList.add('active', 'border-green-500', 'text-green-600');
    }
    
    // Update breadcrumb
    updateBreadcrumb(activeTab);
}

function updateBreadcrumb(tabName) {
    const breadcrumbContainer = document.getElementById('dynamicBreadcrumb');
    
    let tabTitle = 'Pengiriman';
    let tabUrl = '';
    
    switch(tabName) {
        case 'pengiriman-masuk':
            tabTitle = 'Pengiriman - Pengiriman Masuk';
            tabUrl = '?tab=pengiriman-masuk';
            break;
        case 'menunggu-verifikasi':
            tabTitle = 'Pengiriman - Menunggu Verifikasi';
            tabUrl = '?tab=menunggu-verifikasi';
            break;
        case 'menunggu-fisik':
            tabTitle = 'Pengiriman - Menunggu Fisik';
            tabUrl = '?tab=menunggu-fisik';
            break;
        case 'pengiriman-berhasil':
            tabTitle = 'Pengiriman - Pengiriman Berhasil';
            tabUrl = '?tab=pengiriman-berhasil';
            break;
        case 'pengiriman-gagal':
            tabTitle = 'Pengiriman - Pengiriman Gagal';
            tabUrl = '?tab=pengiriman-gagal';
            break;
        default:
            tabTitle = 'Pengiriman - Pengiriman Masuk';
            tabUrl = '?tab=pengiriman-masuk';
    }
    
    // Update page title
    document.title = tabTitle + ' - Kamil Maju Persada';
}

// Global refresh function that preserves all parameters
function refreshWithPreservedParams() {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Preserve current tab and page parameters
    const currentTab = currentParams.get('tab') || 'pengiriman-masuk';
    const currentPage = currentParams.get('page') || '1';
    const currentPageMenunggu = currentParams.get('page_menunggu') || '1';
    const currentPageFisik = currentParams.get('page_fisik') || '1';
    const currentPageBerhasil = currentParams.get('page_berhasil') || '1';
    const currentPageGagal = currentParams.get('page_gagal') || '1';
    
    // Build URL with preserved parameters
    const params = new URLSearchParams();
    params.append('tab', currentTab);
    
    // Preserve pagination for all tabs
    if (currentParams.get('page')) params.append('page', currentPage);
    if (currentParams.get('page_menunggu')) params.append('page_menunggu', currentPageMenunggu);
    if (currentParams.get('page_fisik')) params.append('page_fisik', currentPageFisik);
    if (currentParams.get('page_berhasil')) params.append('page_berhasil', currentPageBerhasil);
    if (currentParams.get('page_gagal')) params.append('page_gagal', currentPageGagal);
    
    // Preserve search and filter parameters for pengiriman-masuk tab
    if (currentParams.get('search_masuk')) params.append('search_masuk', currentParams.get('search_masuk'));
    if (currentParams.get('filter_purchasing')) params.append('filter_purchasing', currentParams.get('filter_purchasing'));
    if (currentParams.get('sort_date_masuk')) params.append('sort_date_masuk', currentParams.get('sort_date_masuk'));
    
    // Preserve search and filter parameters for menunggu-verifikasi tab
    if (currentParams.get('search_verifikasi')) params.append('search_verifikasi', currentParams.get('search_verifikasi'));
    if (currentParams.get('filter_purchasing_verifikasi')) params.append('filter_purchasing_verifikasi', currentParams.get('filter_purchasing_verifikasi'));
    if (currentParams.get('sort_date_verifikasi')) params.append('sort_date_verifikasi', currentParams.get('sort_date_verifikasi'));
    
    // Preserve search and filter parameters for menunggu-fisik tab
    if (currentParams.get('search_fisik')) params.append('search_fisik', currentParams.get('search_fisik'));
    if (currentParams.get('filter_purchasing_fisik')) params.append('filter_purchasing_fisik', currentParams.get('filter_purchasing_fisik'));
    if (currentParams.get('sort_date_fisik')) params.append('sort_date_fisik', currentParams.get('sort_date_fisik'));
    
    // Preserve search and filter parameters for pengiriman-berhasil tab
    if (currentParams.get('search_berhasil')) params.append('search_berhasil', currentParams.get('search_berhasil'));
    if (currentParams.get('date_range_berhasil')) params.append('date_range_berhasil', currentParams.get('date_range_berhasil'));
    if (currentParams.get('filter_purchasing_berhasil')) params.append('filter_purchasing_berhasil', currentParams.get('filter_purchasing_berhasil'));
    if (currentParams.get('sort_order_berhasil')) params.append('sort_order_berhasil', currentParams.get('sort_order_berhasil'));
    
    // Preserve search and filter parameters for pengiriman-gagal tab
    if (currentParams.get('search_gagal')) params.append('search_gagal', currentParams.get('search_gagal'));
    if (currentParams.get('date_range_gagal')) params.append('date_range_gagal', currentParams.get('date_range_gagal'));
    if (currentParams.get('filter_purchasing_gagal')) params.append('filter_purchasing_gagal', currentParams.get('filter_purchasing_gagal'));
    if (currentParams.get('sort_order_gagal')) params.append('sort_order_gagal', currentParams.get('sort_order_gagal'));
    
    // Navigate with all preserved parameters
    const newUrl = window.location.pathname + '?' + params.toString();
    window.location.href = newUrl;
}

// Load data for specific tab
function loadTabData(tabName) {
    // This would be connected to your backend API
    console.log('Loading data for tab:', tabName);
    
    // Example: Update tab counts
    updateTabCounts();
}

// Update tab counts (populated from backend data)
function updateTabCounts() {
    // Update counts from actual data
    document.getElementById('masuk-count').textContent = '{{ $pengirimanMasuk->total() ?? 0 }}';
    document.getElementById('verifikasi-count').textContent = '{{ $menungguVerifikasi->total() ?? 0 }}';
    document.getElementById('fisik-count').textContent = '{{ $menungguFisik->total() ?? 0 }}';
    document.getElementById('berhasil-count').textContent = '{{ $pengirimanBerhasil->total() ?? 0 }}';
    document.getElementById('gagal-count').textContent = '{{ $pengirimanGagal->total() ?? 0 }}';
    
    // Update mobile counts
    document.getElementById('masuk-count-mobile').textContent = '{{ $pengirimanMasuk->total() ?? 0 }}';
    document.getElementById('verifikasi-count-mobile').textContent = '{{ $menungguVerifikasi->total() ?? 0 }}';
    document.getElementById('fisik-count-mobile').textContent = '{{ $menungguFisik->total() ?? 0 }}';
    document.getElementById('berhasil-count-mobile').textContent = '{{ $pengirimanBerhasil->total() ?? 0 }}';
    document.getElementById('gagal-count-mobile').textContent = '{{ $pengirimanGagal->total() ?? 0 }}';
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tab from URL parameter
    initializeTabFromUrl();
    
    // Update tab counts
    updateTabCounts();
});

// Handle browser back/forward buttons
window.addEventListener('popstate', function(event) {
    console.log('Popstate event triggered:', event.state);
    initializeTabFromUrl();
});

// Action buttons functionality
function verifikasiPengiriman(id) {
    if (confirm('Apakah Anda yakin ingin memverifikasi pengiriman ini?')) {
        // Call API to verify pengiriman
        fetch(`/procurement/pengiriman/${id}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: 'delivered'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function tolakPengiriman(id) {
    const alasan = prompt('Masukkan alasan penolakan:');
    if (alasan) {
        // Call API to reject pengiriman
        fetch(`/procurement/pengiriman/${id}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: 'cancelled',
                alasan: alasan
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function kirimUlang(id) {
    if (confirm('Apakah Anda yakin ingin mengirim ulang pengiriman ini?')) {
        // Call API to resend pengiriman
        fetch(`/procurement/pengiriman/${id}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: 'pending'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}
</script>
@endpush

{{-- Tab Content --}}
<div class="tab-content-wrapper space-y-6">
    
    <!-- Pengiriman Masuk Tab -->
    <div id="pengiriman-masuk" class="tab-content bg-white rounded-lg shadow-lg border border-gray-200 p-6 hidden">
        @include('pages.purchasing.pengiriman.pengiriman-masuk')
    </div>

    <!-- Menunggu Verifikasi Tab -->
    <div id="menunggu-verifikasi" class="tab-content bg-white rounded-lg shadow-lg border border-gray-200 p-6 hidden">
        @include('pages.purchasing.pengiriman.menunggu-verifikasi')
    </div>

    <!-- Menunggu Fisik Tab -->
    <div id="menunggu-fisik" class="tab-content bg-white rounded-lg shadow-lg border border-gray-200 p-6 hidden">
        @include('pages.purchasing.pengiriman.menunggu-fisik')
    </div>

    <!-- Pengiriman Berhasil Tab -->
    <div id="pengiriman-berhasil" class="tab-content bg-white rounded-lg shadow-lg border border-gray-200 p-6 hidden">
        @include('pages.purchasing.pengiriman.pengiriman-berhasil')
    </div>

    <!-- Pengiriman Gagal Tab -->
    <div id="pengiriman-gagal" class="tab-content bg-white rounded-lg shadow-lg border border-gray-200 p-6 hidden">
        @include('pages.purchasing.pengiriman.pengiriman-gagal')
    </div>
    
</div>
@endsection