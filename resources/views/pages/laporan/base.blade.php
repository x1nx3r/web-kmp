@extends('layouts.app')

@section('title')
    Laporan {{ $title }} - Kamil Maju Persada
@endsection
@section('content')
<x-welcome-banner title="Laporan {{ $title }}" subtitle="Informasi laporan {{ strtolower($title) }}" icon="fas fa-chart-bar" />

<!-- Navigation Tabs -->
<div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-6">
    <div class="px-4 sm:px-6 py-4">
        <!-- Mobile Dropdown -->
        <div class="sm:hidden">
            <label for="tabs" class="sr-only">Pilih Tab</label>
            <select id="tabs" name="tabs"
                    class="block w-full rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500"
                    onchange="window.location.href = this.value">
                <option value="{{ route('laporan.po') }}" {{ $activeTab === 'po' ? 'selected' : '' }}>
                    Laporan Purchase Order
                </option>
                <option value="{{ route('laporan.omset') }}" {{ $activeTab === 'omset' ? 'selected' : '' }}>
                    Laporan Omset
                </option>
                <option value="{{ route('laporan.pengiriman') }}" {{ $activeTab === 'pengiriman' ? 'selected' : '' }}>
                    Laporan Pengiriman
                </option>
                <option value="{{ route('laporan.pembayaran') }}" {{ $activeTab === 'pembayaran' ? 'selected' : '' }}>
                    Laporan Pembayaran
                </option>
                <option value="{{ route('laporan.penagihan') }}" {{ $activeTab === 'penagihan' ? 'selected' : '' }}>
                    Laporan Penagihan
                </option>
                <option value="{{ route('laporan.margin') }}" {{ $activeTab === 'margin' ? 'selected' : '' }}>
                    Analisis Margin
                </option>
            </select>
        </div>

        <!-- Desktop Tabs -->
        <nav class="hidden sm:flex sm:space-x-8" aria-label="Tabs">
            <a href="{{ route('laporan.po') }}"
               class="{{ $activeTab === 'po' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Laporan Purchase Order
            </a>
            <a href="{{ route('laporan.omset') }}"
               class="{{ $activeTab === 'omset' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Laporan Omset
            </a>
            <a href="{{ route('laporan.pengiriman') }}"
               class="{{ $activeTab === 'pengiriman' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Laporan Pengiriman
            </a>
            <a href="{{ route('laporan.pembayaran') }}"
               class="{{ $activeTab === 'pembayaran' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Laporan Pembayaran
            </a>
            <a href="{{ route('laporan.penagihan') }}"
               class="{{ $activeTab === 'penagihan' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Laporan Penagihan
            </a>
            <a href="{{ route('laporan.margin') }}"
               class="{{ $activeTab === 'margin' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Analisis Margin
            </a>
        </nav>
    </div>
</div>

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['title' => 'Laporan ' . $title, 'url' => '']
]" />
@yield('report-content')

<script>
function exportData() {
    const form = new FormData();
    form.append('start_date', document.querySelector('input[name="start_date"]').value);
    form.append('end_date', document.querySelector('input[name="end_date"]').value);

    fetch('{{ route("laporan." . $activeTab . ".export") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: form
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat export data');
    });
}
</script>

@endsection
