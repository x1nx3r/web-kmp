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
    // Remove page parameter when switching tabs to start from page 1
    url.searchParams.delete('page');
    window.history.replaceState({}, '', url);
}

function switchTab(tabName) {
    console.log('Switching to tab:', tabName);
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.classList.remove('active', 'border-green-500', 'text-green-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Hide all tab content properly
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.add('hidden');
        pane.classList.remove('active');
        pane.style.display = 'none'; // Force hide
        console.log('Hiding pane:', pane.id);
    });
    
    // Show active tab content
    const activeContent = document.getElementById('content-' + tabName);
    if (activeContent) {
        activeContent.classList.remove('hidden');
        activeContent.classList.add('active');
        activeContent.style.display = 'block'; // Force show
        console.log('Showing pane:', activeContent.id);
        
        // Ensure the element stays within its parent
        const parent = activeContent.parentElement;
        if (parent && !parent.contains(activeContent)) {
            console.warn('Element moved outside parent, re-appending');
            parent.appendChild(activeContent);
        }
    }
    
    // Add active class to clicked tab
    const activeTab = document.getElementById('tab-' + tabName);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('active', 'border-green-500', 'text-green-600');
    }
    
    // Update URL with tab parameter
    updateUrl(tabName);
    
    // Update breadcrumb
    updateBreadcrumb(tabName);
}

// Function to initialize tab based on URL parameter
function initializeTabFromUrl() {
    const activeTab = getUrlParameter('tab') || 'buat-forecasting';
    switchTab(activeTab);
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

/* Tab content wrapper with strict containment */
.tab-content-wrapper {
    position: relative;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    contain: layout style paint;
}

.tab-content {
    position: relative;
    width: 100%;
    max-width: 100%;
    contain: layout style;
}

.tab-content-inner {
    position: relative;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.tab-pane {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    contain: layout;
}

.tab-pane.active {
    display: block !important;
}

.tab-pane.hidden {
    display: none !important;
}

/* Specific containment for each tab */
#content-buat-forecasting,
#content-pending,
#content-sukses,
#content-gagal {
    position: relative;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    isolation: isolate;
}

/* Force containment for all nested elements */
.tab-pane * {
    max-width: 100%;
    box-sizing: border-box;
}

/* Additional safety for large elements */
.tab-pane .space-y-6,
.tab-pane .bg-white {
    position: relative;
    max-width: 100%;
}
</style>

@endsection