@extends('layouts.app')
@section('title', 'Daftar Klien - Kamil Maju Persada')
@section('content')

{{-- Welcome Banner --}}
<x-welcome-banner 
    title="Daftar Klien" 
    subtitle="Kelola data klien perusahaan" 
    icon="fas fa-users" 
/>

<div x-data="klienListData()">
    {{-- Search and Filter Section --}}
    <x-klien.search-filter-section :availableLocations="$availableLocations" />

    {{-- Action Header --}}
    <x-klien.action-header 
        title="Daftar Klien"
        :createRoute="route('klien.create')"
        createLabel="Tambah Klien"
    />

    {{-- Main Content Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @if($kliens->count() > 0)
            {{-- Table Header --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Data Klien</h3>
                    <div class="text-sm text-gray-600">
                        Total: <span class="font-semibold text-green-600">{{ $kliens->total() }}</span> nama klien
                    </div>
                </div>
            </div>

            {{-- Table Content --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Klien</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Cabang</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Update</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $grouped = $kliens->getCollection()->groupBy('nama');
                            $currentPage = $kliens->currentPage();
                            $perPage = $kliens->perPage();
                            $startingRowNumber = ($currentPage - 1) * $perPage + 1;
                            $rowNumber = $startingRowNumber;
                        @endphp

                        @foreach($grouped as $name => $group)
                            @php $groupId = 'group-' . md5($name); @endphp
                            
                            <x-klien.table-row 
                                :name="$name" 
                                :group="$group" 
                                :groupId="$groupId" 
                                :rowNumber="$rowNumber"
                            />
                            
                            @php $rowNumber++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Section --}}
            <x-klien.pagination :paginator="$kliens" />

        @else
            {{-- Empty State --}}
            <x-klien.empty-state 
                :hasSearch="!!(request('search') || request('location'))"
                :searchTerm="request('search') ?: request('location')"
                :clearUrl="route('klien.index')"
            />
        @endif
    </div>

    {{-- CRUD Modals --}}
    <x-klien.branch-modal />
    <x-klien.confirm-modal />
</div>

@push('scripts')
@vite('resources/js/klien-list-manager.js')
<script>
// Initialize page data for Alpine.js
window.klienPageData = {
    search: '{{ request("search") }}',
    location: '{{ request("location") }}',
    sort: '{{ request("sort", "nama") }}',
    direction: '{{ request("direction", "asc") }}'
};

// Make client data available globally for unique company names
window.klienData = @json($kliens->getCollection()->map(function($k) { 
    return ['nama' => $k->nama, 'cabang' => $k->cabang]; 
}));
</script>
@endpush

@endsection
