@extends('layouts.app')

@section('title')
    Laporan {{ $title }} - Kamil Maju Persada
@endsection
@section('content')
<x-welcome-banner title="Laporan {{ $title }}" subtitle="Informasi laporan {{ strtolower($title) }}" icon="fas fa-chart-bar" />

<!-- Navigation Tabs -->
<div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-6">
    <div class="px-6 py-4">
        <nav class="flex space-x-8" aria-label="Tabs">
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
            <a href="{{ route('laporan.penagihan') }}"
               class="{{ $activeTab === 'penagihan' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Laporan Penagihan
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
