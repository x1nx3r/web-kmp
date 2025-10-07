@extends('layouts.app')
@section('title', 'Forecasting - Kamil Maju Persada')
@section('content')


<x-welcome-banner title="Forecasting" subtitle="Rencanakan Pengiriman Disini" icon="fas fa-chart-bar" />
{{-- Breadcrumb --}}
<div id="dynamicBreadcrumb">
    {{-- Default breadcrumb, akan diupdate via JavaScript --}}
    <x-breadcrumb :items="[
        ['title' => 'Forecasting', 'url' => '#']
    ]" />
</div>

{{-- Tabs Navigation --}}
<div class="mb-6">
    <div class="border-b-2">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
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
                Pending
                <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">3</span>
            </button>
            <button onclick="switchTab('sukses')" 
                    id="tab-sukses" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-check-circle mr-2"></i>
                Sukses
                <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">12</span>
            </button>
            <button onclick="switchTab('gagal')" 
                    id="tab-gagal" 
                    class="tab-button border-transparent text-gray-500 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-times-circle mr-2"></i>
                Gagal
                <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">2</span>
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
    
    // Remove page parameters from other tabs, but preserve current tab's pagination
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'buat-forecasting';
    
    // Remove pagination parameters from other tabs
    if (tabName !== 'buat-forecasting') {
        url.searchParams.delete('page_buat_forecasting');
    }
    if (tabName !== 'pending') {
        url.searchParams.delete('page_pending');
    }
    
    // Only remove current tab's pagination if switching to a different tab
    if (tabName !== currentTab) {
        if (tabName === 'buat-forecasting') {
            url.searchParams.delete('page_buat_forecasting');
        } else if (tabName === 'pending') {
            url.searchParams.delete('page_pending');
        }
    }
    
    window.history.replaceState({}, '', url);
}

function switchTab(tabName) {
    console.log('Switching to tab:', tabName);
    
    // Check if we're already on the target tab to avoid unnecessary updates
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'buat-forecasting';
    
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
    
    // Add active class to clicked tab
    const activeTab = document.getElementById('tab-' + tabName);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('active', 'border-green-500', 'text-green-600');
    }
    
    // Update URL with tab parameter only if we're switching tabs
    if (tabName !== currentTab) {
        updateUrl(tabName);
    }
    
    // Update breadcrumb
    updateBreadcrumb(tabName);
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
    
    // Add active class to correct tab
    const activeTabButton = document.getElementById('tab-' + activeTab);
    if (activeTabButton) {
        activeTabButton.classList.remove('border-transparent', 'text-gray-500');
        activeTabButton.classList.add('active', 'border-green-500', 'text-green-600');
    }
    
    // Update breadcrumb
    updateBreadcrumb(activeTab);
}

// Initialize tab on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeTabFromUrl();
});
</script>

<style>
.tab-button.active {
    @apply border-green-800 text-green-600;
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