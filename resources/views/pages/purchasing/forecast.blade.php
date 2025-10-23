@extends('layouts.app')
@section('title', 'Forecasting - Kamil Maju Persada')
@section('content')


<x-welcome-banner title="Forecasting" subtitle="Rencanakan Pengiriman Disini" icon="fas fa-chart-bar" />
{{-- Breadcrumb --}}
<div id="dynamicBreadcrumb">
    {{-- Default breadcrumb, akan diupdate via JavaScript --}}
    <x-breadcrumb :items="[
    ['title' => 'Purchasing', 'url' => '#'],
    'Forecasting'
    ]" />
</div>

{{-- Tabs Navigation --}}
<div class="mb-6">
    <div class="border-b-2">
        {{-- Desktop Navigation --}}
        <nav class="-mb-px hidden sm:flex justify-between px-16" aria-label="Tabs">
            <button onclick="switchTab('buat-forecasting')" 
                    id="tab-buat-forecasting" 
                    class="tab-button active border-transparent text-green-600 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-plus-circle mr-2"></i>
                Buat Forecasting
            </button>
            <button onclick="switchTab('pending')" 
                    id="tab-pending" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-clock mr-2"></i>
                Forecasting Pending
                @if(isset($pendingForecasts) && $pendingForecasts->total() > 0)
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $pendingForecasts->total() }}</span>
                @endif
            </button>
            <button onclick="switchTab('sukses')" 
                    id="tab-sukses" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-check-circle mr-2"></i>
               Forecasting Sukses
                @if(isset($suksesForecasts) && $suksesForecasts->total() > 0)
                    <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $suksesForecasts->total() }}</span>
                @endif
            </button>
            <button onclick="switchTab('gagal')" 
                    id="tab-gagal" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-times-circle mr-2"></i>
                Forecasting Gagal
                @if(isset($gagalForecasts) && $gagalForecasts->total() > 0)
                    <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $gagalForecasts->total() }}</span>
                @endif
            </button>
        </nav>

        {{-- Mobile Navigation --}}
        <nav class="-mb-px flex sm:hidden overflow-x-auto justify-between scrollbar-hide px-8" aria-label="Tabs">
            <button onclick="switchTab('buat-forecasting')" 
                    id="tab-buat-forecasting-mobile" 
                    class="tab-button active border-transparent text-green-600 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max">
                <div class="flex flex-col items-center space-y-1">
                    <i class="fas fa-plus-circle text-sm"></i>
                    <span>Buat</span>
                </div>
            </button>
            <button onclick="switchTab('pending')" 
                    id="tab-pending-mobile" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max relative">
                <div class="flex flex-col items-center space-y-1">
                    <div class="relative">
                        <i class="fas fa-clock text-sm"></i>
                        @if(isset($pendingForecasts) && $pendingForecasts->total() > 0)
                            <span class="absolute -top-2 -right-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center">{{ $pendingForecasts->total() > 99 ? '99+' : $pendingForecasts->total() }}</span>
                        @endif
                    </div>
                    <span>Pending</span>
                </div>
            </button>
            <button onclick="switchTab('sukses')" 
                    id="tab-sukses-mobile" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max relative">
                <div class="flex flex-col items-center space-y-1">
                    <div class="relative">
                        <i class="fas fa-check-circle text-sm"></i>
                        @if(isset($suksesForecasts) && $suksesForecasts->total() > 0)
                            <span class="absolute -top-2 -right-2 bg-green-100 text-green-800 text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center">{{ $suksesForecasts->total() > 99 ? '99+' : $suksesForecasts->total() }}</span>
                        @endif
                    </div>
                    <span>Sukses</span>
                </div>
            </button>
            <button onclick="switchTab('gagal')" 
                    id="tab-gagal-mobile" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max relative">
                <div class="flex flex-col items-center space-y-1">
                    <div class="relative">
                        <i class="fas fa-times-circle text-sm"></i>
                        @if(isset($gagalForecasts) && $gagalForecasts->total() > 0)
                            <span class="absolute -top-2 -right-2 bg-red-100 text-red-800 text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center">{{ $gagalForecasts->total() > 99 ? '99+' : $gagalForecasts->total() }}</span>
                        @endif
                    </div>
                    <span>Gagal</span>
                </div>
            </button>
        </nav>
    </div>
</div>

{{-- Tab Content --}}
<div class="tab-content-wrapper">
    <div class="tab-content">
        <div id="content-buat-forecasting" class="tab-pane active" style="display: block;">
            <div class="tab-content-inner">
                @include('pages.purchasing.forecast.buat-forecasting')
            </div>
        </div>
        <div id="content-pending" class="tab-pane hidden" style="display: none;">
            <div class="tab-content-inner">
                @include('pages.purchasing.forecast.pending-forecasting')
            </div>
        </div>
        <div id="content-sukses" class="tab-pane hidden" style="display: none;">
            <div class="tab-content-inner">
                @include('pages.purchasing.forecast.sukses-forecasting')
            </div>
        </div>
        <div id="content-gagal" class="tab-pane hidden" style="display: none;">
            <div class="tab-content-inner">
                @include('pages.purchasing.forecast.gagal-forecasting')
            </div>
        </div>
    </div>
</div>

{{-- JavaScript untuk Tab Switching --}}
<script>
// Function to get URL parameters
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Function to update breadcrumb based on active tab
function updateBreadcrumb(tabName) {
    const breadcrumbContainer = document.getElementById('dynamicBreadcrumb');
    if (!breadcrumbContainer) return;
    
    let tabTitle = 'Forecasting';
    
    switch(tabName) {
        case 'buat-forecasting':
            tabTitle = 'Forecasting - Buat Forecasting';
            break;
        case 'pending':
            tabTitle = 'Forecasting - Pending';
            break;
        case 'sukses':
            tabTitle = 'Forecasting - Sukses';
            break;
        case 'gagal':
            tabTitle = 'Forecasting - Gagal';
            break;
        default:
            tabTitle = 'Forecasting - Buat Forecasting';
    }
    
    // Safely update only the breadcrumb text without using innerHTML
    const breadcrumbSpan = breadcrumbContainer.querySelector('span.text-gray-500');
    if (breadcrumbSpan) {
        breadcrumbSpan.textContent = tabTitle;
    }
}

// Function to update URL with tab parameter
function updateUrl(tabName) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    
    // Get current tab to check if we're switching tabs
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'buat-forecasting';
    
    // Only clean pagination if we're actually switching tabs
    if (tabName !== currentTab) {
        // Remove pagination parameters from other tabs only
        if (tabName !== 'buat-forecasting') {
            url.searchParams.delete('page');
        }
        if (tabName !== 'pending') {
            url.searchParams.delete('page_pending');
        }
        if (tabName !== 'sukses') {
            url.searchParams.delete('page_sukses');
        }
        if (tabName !== 'gagal') {
            url.searchParams.delete('page_gagal');
        }
    }
    // If we're staying on the same tab, preserve all pagination parameters
    
    window.history.replaceState({}, '', url);
}

function switchTab(tabName) {
    console.log('Switching to tab:', tabName);
    
    // Check if we're already on the target tab to avoid unnecessary updates
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'buat-forecasting';
    
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
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.add('hidden');
        pane.classList.remove('active');
        pane.style.display = 'none';
        pane.style.opacity = '0';
        pane.style.visibility = 'hidden';
        console.log('Hiding pane:', pane.id);
    });
    
    // Small delay to ensure proper hiding
    setTimeout(() => {
        // Show active tab content
        const activeContent = document.getElementById('content-' + tabName);
        if (activeContent) {
            activeContent.classList.remove('hidden');
            activeContent.classList.add('active');
            activeContent.style.display = 'block';
            activeContent.style.opacity = '1';
            activeContent.style.visibility = 'visible';
            console.log('Showing pane:', activeContent.id);
            
            // Ensure the element stays within its parent
            const parent = activeContent.parentElement;
            if (parent && !parent.contains(activeContent)) {
                console.warn('Element moved outside parent, re-appending');
                parent.appendChild(activeContent);
            }
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
}

// Function to handle pagination without changing tabs
function handlePagination(pageUrl) {
    // Extract the current tab from URL to ensure we stay on it
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'buat-forecasting';
    
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
    const activeTab = urlParams.get('tab') || 'buat-forecasting';
    
    console.log('Initializing tab from URL:', activeTab);
    console.log('Current URL params:', Object.fromEntries(urlParams));
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.classList.remove('active', 'border-green-500', 'text-green-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Hide all tab content properly
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.add('hidden');
        pane.classList.remove('active');
        pane.style.display = 'none';
        pane.style.opacity = '0';
        pane.style.visibility = 'hidden';
    });
    
    // Show active tab content
    const activeContent = document.getElementById('content-' + activeTab);
    if (activeContent) {
        activeContent.classList.remove('hidden');
        activeContent.classList.add('active');
        activeContent.style.display = 'block';
        activeContent.style.opacity = '1';
        activeContent.style.visibility = 'visible';
        console.log('Showing pane:', activeContent.id);
    }
    
    // Add active class to correct tab (both desktop and mobile)
    const activeTabButtonDesktop = document.getElementById('tab-' + activeTab);
    const activeTabButtonMobile = document.getElementById('tab-' + activeTab + '-mobile');
    
    if (activeTabButtonDesktop) {
        activeTabButtonDesktop.classList.remove('border-transparent', 'text-gray-500');
        activeTabButtonDesktop.classList.add('active', 'border-green-500', 'text-green-600');
    }
    
    if (activeTabButtonMobile) {
        activeTabButtonMobile.classList.remove('border-transparent', 'text-gray-500');
        activeTabButtonMobile.classList.add('active', 'border-green-500', 'text-green-600');
    }
    
    // Update breadcrumb
    updateBreadcrumb(activeTab);
}

// Initialize tab on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeTabFromUrl();
});

// Global refresh function that preserves all parameters
function refreshWithPreservedParams() {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Preserve current tab and page parameters
    const currentTab = currentParams.get('tab') || 'buat-forecasting';
    const currentPage = currentParams.get('page') || '1';
    const currentPagePending = currentParams.get('page_pending') || '1';
    const currentPageSukses = currentParams.get('page_sukses') || '1';
    const currentPageGagal = currentParams.get('page_gagal') || '1';
    
    // Build URL with preserved parameters
    const params = new URLSearchParams();
    params.append('tab', currentTab);
    
    // Preserve pagination for all tabs
    if (currentParams.get('page')) params.append('page', currentPage);
    if (currentParams.get('page_pending')) params.append('page_pending', currentPagePending);
    if (currentParams.get('page_sukses')) params.append('page_sukses', currentPageSukses);
    if (currentParams.get('page_gagal')) params.append('page_gagal', currentPageGagal);
    
    // Preserve search and filter parameters for buat-forecasting tab
    if (currentParams.get('search')) params.append('search', currentParams.get('search'));
    if (currentParams.get('status')) params.append('status', currentParams.get('status'));
    if (currentParams.get('sort_amount')) params.append('sort_amount', currentParams.get('sort_amount'));
    if (currentParams.get('sort_items')) params.append('sort_items', currentParams.get('sort_items'));
    
    // Preserve search and filter parameters for pending tab
    if (currentParams.get('search_pending')) params.append('search_pending', currentParams.get('search_pending'));
    if (currentParams.get('date_range')) params.append('date_range', currentParams.get('date_range'));
    if (currentParams.get('sort_amount_pending')) params.append('sort_amount_pending', currentParams.get('sort_amount_pending'));
    if (currentParams.get('sort_qty_pending')) params.append('sort_qty_pending', currentParams.get('sort_qty_pending'));
    if (currentParams.get('sort_date_pending')) params.append('sort_date_pending', currentParams.get('sort_date_pending'));
    if (currentParams.get('sort_hari_kirim')) params.append('sort_hari_kirim', currentParams.get('sort_hari_kirim'));
    
    // Preserve search and filter parameters for sukses tab
    if (currentParams.get('search_sukses')) params.append('search_sukses', currentParams.get('search_sukses'));
    if (currentParams.get('date_range_sukses')) params.append('date_range_sukses', currentParams.get('date_range_sukses'));
    if (currentParams.get('sort_order_sukses')) params.append('sort_order_sukses', currentParams.get('sort_order_sukses'));
    
    // Preserve search and filter parameters for gagal tab
    if (currentParams.get('search_gagal')) params.append('search_gagal', currentParams.get('search_gagal'));
    if (currentParams.get('date_range_gagal')) params.append('date_range_gagal', currentParams.get('date_range_gagal'));
    if (currentParams.get('sort_order_gagal')) params.append('sort_order_gagal', currentParams.get('sort_order_gagal'));
    
    // Reload with preserved parameters
    window.location.href = '/forecasting?' + params.toString();
}

// Global function to close success modal and refresh
window.closeSuccessModal = function() {
    // Close the modal first
    if (document.getElementById('successModal')) {
        document.getElementById('successModal').classList.add('hidden');
    }
    
    // Refresh with preserved parameters
    refreshWithPreservedParams();
};

// Global function to close detail modals and preserve state
window.closeDetailModal = function() {
    // Close any open detail modals
    const detailModal = document.getElementById('detailForecastModal');
    if (detailModal) {
        detailModal.classList.add('hidden');
    }
    
    // Check for modal in sukses tab
    const suksesDetailModal = document.getElementById('detailSuksesForecastModal');
    if (suksesDetailModal) {
        suksesDetailModal.classList.add('hidden');
    }
    
    // Check for modal in gagal tab
    const gagalDetailModal = document.getElementById('detailGagalForecastModal');
    if (gagalDetailModal) {
        gagalDetailModal.classList.add('hidden');
    }
    
    // No need to refresh page, just close modal
};

// Global function to close pengiriman modal and refresh with preserved params
window.closePengirimanModal = function() {
    // Close the modal first
    const pengirimanModal = document.getElementById('pengirimanModal');
    if (pengirimanModal) {
        pengirimanModal.classList.add('hidden');
    }
    
    // Refresh with preserved parameters
    refreshWithPreservedParams();
};

// Global function to close batal modal and refresh with preserved params
window.closeBatalModal = function() {
    // Close the modal first
    const batalModal = document.getElementById('batalModal');
    if (batalModal) {
        batalModal.classList.add('hidden');
    }
    
    // Refresh with preserved parameters
    refreshWithPreservedParams();
};

// Expose refresh function globally
window.refreshWithPreservedParams = refreshWithPreservedParams;

// Expose pagination handler globally
window.handlePagination = handlePagination;

// Global function to navigate to a page while preserving current tab
window.navigateToPage = function(url) {
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'buat-forecasting';
    
    // Parse the target URL
    const targetUrl = new URL(url, window.location.origin);
    const targetParams = new URLSearchParams(targetUrl.search);
    
    // Ensure tab is preserved
    targetParams.set('tab', currentTab);
    
    // Navigate to the URL with preserved tab
    window.location.href = targetUrl.pathname + '?' + targetParams.toString();
};

// Override any pagination links to use our navigation function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tab first
    initializeTabFromUrl();
    
    // Override pagination links after a short delay to ensure they're rendered
    setTimeout(function() {
        const paginationLinks = document.querySelectorAll('a[href*="page"]');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                if (href) {
                    window.navigateToPage(href);
                }
            });
        });
    }, 500);
});
</script>

<style>
.tab-button.active {
    @apply border-green-800 text-green-600;
}

/* Mobile tab navigation scrolling */
.scrollbar-hide {
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;  /* Safari and Chrome */
}

/* Mobile tab responsive styling */
@media (max-width: 640px) {
    .tab-button {
        min-width: 80px;
        text-align: center;
    }
    
    /* Mobile tab badge positioning */
    .tab-button .absolute {
        font-size: 10px;
        line-height: 1;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

/* Tab content wrapper */
.tab-content-wrapper {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.tab-content {
    position: relative;
    width: 100%;
}

.tab-content-inner {
    position: relative;
    width: 100%;
    box-sizing: border-box;
}

.tab-pane {
    position: relative;
    width: 100%;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease-in-out;
}

.tab-pane.active {
    display: block !important;
    opacity: 1;
    visibility: visible;
}

.tab-pane.hidden {
    display: none !important;
    opacity: 0;
    visibility: hidden;
}

/* Specific styling for each tab */
#content-buat-forecasting,
#content-pending,
#content-sukses,
#content-gagal {
    position: relative;
    width: 100%;
    box-sizing: border-box;
    min-height: 200px;
}

/* Reset any conflicting styles for tab content */
.tab-pane * {
    box-sizing: border-box;
}

/* Ensure proper spacing and alignment */
.tab-pane .space-y-6 > * + * {
    margin-top: 1.5rem;
}

/* Fix any layout issues */
.tab-pane .bg-white {
    background-color: white;
    position: relative;
    z-index: 1;
}

/* Ensure proper container behavior */
.tab-content-wrapper .space-y-6 {
    margin: 0;
    padding: 0;
}
</style>

{{-- Include Modal Buat Forecast --}}
@include('pages.purchasing.forecast.buat-forecasting.modal-buat-forecasting')

{{-- Include Universal Success Modal --}}
@include('components.success-modal')

{{-- Include Batal Pengiriman Modal --}}
@include('pages.purchasing.forecast.pending-forecasting.batal')

{{-- Include Pengiriman Modal --}}
@include('pages.purchasing.forecast.pending-forecasting.pengiriman')

{{-- Include Forecast Detail Modal (outside tab content to avoid z-index issues) --}}
@include('pages.purchasing.forecast.pending-forecasting.detail')

{{-- Include Sukses Detail Modal --}}
@include('pages.purchasing.forecast.sukses-forecasting.detail')

@endsection