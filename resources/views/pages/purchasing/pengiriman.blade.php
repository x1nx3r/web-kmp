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
                    class="tab-button active border-transparent text-green-600 hover:text-green-600 hover:border-green-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-inbox mr-2"></i>
                Pengiriman Masuk
                <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="masuk-count">0</span>
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
                    class="tab-button active border-transparent text-green-600 hover:text-green-600 hover:border-green-300 flex-shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors min-w-max">
                <div class="flex flex-col items-center space-y-1">
                    <div class="relative">
                        <i class="fas fa-inbox text-sm"></i>
                        <span class="absolute -top-2 -right-2 bg-blue-100 text-blue-800 text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center" id="masuk-count-mobile">0</span>
                    </div>
                    <span>Masuk</span>
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
function switchTab(tabName) {
    // Remove active class from all desktop tabs
    document.querySelectorAll('.tab-button:not([id$="-mobile"])').forEach(button => {
        button.classList.remove('active');
        button.classList.add('border-transparent', 'text-gray-500');
        button.classList.remove('border-green-500', 'text-green-600');
    });
    
    // Remove active class from all mobile tabs
    document.querySelectorAll('.tab-button[id$="-mobile"]').forEach(button => {
        button.classList.remove('active');
        button.classList.add('border-transparent', 'text-gray-500');
        button.classList.remove('border-green-500', 'text-green-600');
    });
    
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Add active class to clicked tab (both desktop and mobile versions)
    const desktopTab = document.getElementById('tab-' + tabName);
    const mobileTab = document.getElementById('tab-' + tabName + '-mobile');
    
    if (desktopTab) {
        desktopTab.classList.add('active', 'border-green-500', 'text-green-600');
        desktopTab.classList.remove('border-transparent', 'text-gray-500');
    }
    
    if (mobileTab) {
        mobileTab.classList.add('active', 'border-green-500', 'text-green-600');
        mobileTab.classList.remove('border-transparent', 'text-gray-500');
    }
    
    // Show selected tab content
    document.getElementById(tabName).classList.remove('hidden');
    
    // Load data for the active tab
    loadTabData(tabName);
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
    document.getElementById('berhasil-count').textContent = '{{ $pengirimanBerhasil->total() ?? 0 }}';
    document.getElementById('gagal-count').textContent = '{{ $pengirimanGagal->total() ?? 0 }}';
    
    // Update mobile counts
    document.getElementById('masuk-count-mobile').textContent = '{{ $pengirimanMasuk->total() ?? 0 }}';
    document.getElementById('verifikasi-count-mobile').textContent = '{{ $menungguVerifikasi->total() ?? 0 }}';
    document.getElementById('berhasil-count-mobile').textContent = '{{ $pengirimanBerhasil->total() ?? 0 }}';
    document.getElementById('gagal-count-mobile').textContent = '{{ $pengirimanGagal->total() ?? 0 }}';
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadTabData('pengiriman-masuk');
});

// Action buttons functionality
function verifikasiPengiriman(id) {
    if (confirm('Apakah Anda yakin ingin memverifikasi pengiriman ini?')) {
        // Call API to verify pengiriman
        fetch(`/purchasing/pengiriman/${id}/status`, {
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
        fetch(`/purchasing/pengiriman/${id}/status`, {
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
        fetch(`/purchasing/pengiriman/${id}/status`, {
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
    <div id="pengiriman-masuk" class="tab-content bg-white rounded-lg shadow-lg border border-gray-200 p-6">
        @include('pages.purchasing.pengiriman.pengiriman-masuk')
    </div>

    <!-- Menunggu Verifikasi Tab -->
    <div id="menunggu-verifikasi" class="tab-content bg-white rounded-lg shadow-lg border border-gray-200 p-6 hidden">
        @include('pages.purchasing.pengiriman.menunggu-verifikasi')
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