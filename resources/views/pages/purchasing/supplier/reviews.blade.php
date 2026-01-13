@extends('layouts.app')
@section('title', 'Reviews Supplier ' . $supplier->nama . ' - Kamil Maju Persada')
@section('content')

<x-welcome-banner 
    title="Reviews Supplier {{ $supplier->nama }}" 
    subtitle="Lihat semua review dan rating dari pengiriman supplier" 
    icon="fas fa-star" 
/>

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['title' => 'Supplier', 'url' => route('supplier.index')],
    ['title' => $supplier->nama, 'url' => route('supplier.show', $supplier->slug)],
    'Reviews'
]" />

{{-- Statistics Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    {{-- Average Rating --}}
    <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-yellow-100 text-sm font-medium">Rating Rata-rata</p>
                <div class="flex items-center mt-2">
                    <span class="text-3xl font-bold">{{ $stats['average_rating'] ? number_format($stats['average_rating'], 1) : '-' }}</span>
                    <i class="fas fa-star ml-2 text-yellow-200"></i>
                </div>
            </div>
            <div class="bg-yellow-300 bg-opacity-30 rounded-full p-3">
                <i class="fas fa-star text-2xl"></i>
            </div>
        </div>
    </div>

    {{-- Total Reviews --}}
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Total Reviews</p>
                <p class="text-3xl font-bold mt-2">{{ $stats['total_reviews'] }}</p>
            </div>
            <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                <i class="fas fa-comment-alt text-2xl"></i>
            </div>
        </div>
    </div>

    {{-- Pengiriman Berhasil --}}
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-medium">Pengiriman Berhasil</p>
                <p class="text-3xl font-bold mt-2">{{ $stats['berhasil_count'] }}</p>
            </div>
            <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
        </div>
    </div>

    {{-- Pengiriman Gagal --}}
    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-red-100 text-sm font-medium">Pengiriman Gagal</p>
                <p class="text-3xl font-bold mt-2">{{ $stats['gagal_count'] }}</p>
            </div>
            <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                <i class="fas fa-times-circle text-2xl"></i>
            </div>
        </div>
    </div>

    {{-- Rating Distribution --}}
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h4 class="text-sm font-medium text-gray-600 mb-3">Distribusi Rating</h4>
        <div class="space-y-2">
            @for($i = 5; $i >= 1; $i--)
                <div class="flex items-center text-xs">
                    <span class="w-6">{{ $i }}</span>
                    <i class="fas fa-star text-yellow-400 mx-1"></i>
                    <div class="flex-1 bg-gray-200 rounded-full h-2 mx-2">
                        @php
                            $percentage = $stats['total_reviews'] > 0 ? 
                                ($stats['rating_distribution'][$i] / $stats['total_reviews']) * 100 : 0;
                        @endphp
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                    <span class="w-6 text-right">{{ $stats['rating_distribution'][$i] }}</span>
                </div>
            @endfor
        </div>
    </div>
</div>

{{-- Search and Filter Section --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        {{-- Search --}}
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Cari Review</label>
            <input type="text" 
                   id="searchInput"
                   value="{{ request('search') }}" 
                   placeholder="Cari berdasarkan nomor pengiriman, ulasan, PO, atau klien..." 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 transition-all duration-200"
                   onkeyup="debounceSearch()">
        </div>

        {{-- Klien Filter --}}
        <div class="sm:w-56">
            <label class="block text-sm font-medium text-gray-700 mb-2">Filter Klien</label>
            <select id="klienFilter" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 transition-all duration-200"
                    onchange="applyFilters()">
                <option value="">Semua Klien</option>
                @foreach($klienList as $klien)
                    <option value="{{ $klien['id'] }}" {{ request('klien') == $klien['id'] ? 'selected' : '' }}>
                        {{ $klien['nama'] }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Status Filter --}}
        <div class="sm:w-48">
            <label class="block text-sm font-medium text-gray-700 mb-2">Filter Status</label>
            <select id="statusFilter" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 transition-all duration-200"
                    onchange="applyFilters()">
                <option value="">Semua Status</option>
                <option value="berhasil" {{ request('status') == 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                <option value="gagal" {{ request('status') == 'gagal' ? 'selected' : '' }}>Gagal</option>
            </select>
        </div>

        {{-- Rating Filter --}}
        <div class="sm:w-48">
            <label class="block text-sm font-medium text-gray-700 mb-2">Filter Rating</label>
            <select id="ratingFilter" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 transition-all duration-200"
                    onchange="applyFilters()">
                <option value="">Semua Rating</option>
                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Bintang</option>
                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Bintang</option>
                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Bintang</option>
                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Bintang</option>
                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Bintang</option>
            </select>
        </div>
    </div>
</div>

{{-- Reviews List --}}
<div class="space-y-6">
    @if($pengiriman->count() > 0)
        @foreach($pengiriman as $item)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                {{-- Header --}}
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            {{-- Status Badge --}}
                            @if($item->status == 'berhasil')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Berhasil
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Gagal
                                </span>
                            @endif

                            {{-- Pengiriman Info --}}
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $item->no_pengiriman }}</h3>
                                <p class="text-sm text-gray-600">
                                    Order: {{ $item->order->po_number ?? 'N/A' }} | 
                                    Klien: {{ $item->order->klien->nama ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        {{-- Date --}}
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ $item->created_at->format('d M Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $item->created_at->format('H:i') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Rating Section --}}
                        <div class="lg:col-span-1">
                            @if($item->rating)
                                <div class="text-center bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                    <div class="flex items-center justify-center mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-2xl {{ $i <= $item->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="text-xl font-bold text-yellow-700">{{ number_format($item->rating, 1) }} dari 5</p>
                                    <p class="text-sm text-yellow-600 mt-1">Rating Pengiriman</p>
                                </div>
                            @else
                                <div class="text-center bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-center mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-2xl text-gray-300"></i>
                                        @endfor
                                    </div>
                                    <p class="text-lg font-medium text-gray-500">Tidak ada rating</p>
                                </div>
                            @endif
                        </div>

                        {{-- Review Section --}}
                        <div class="lg:col-span-2">
                            @if($item->ulasan)
                                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                    <h4 class="text-sm font-semibold text-blue-800 mb-2 flex items-center">
                                        <i class="fas fa-comment mr-2"></i>
                                        Ulasan
                                    </h4>
                                    <p class="text-gray-700 leading-relaxed">{{ $item->ulasan }}</p>
                                </div>
                            @else
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-gray-500 italic">Tidak ada ulasan untuk pengiriman ini</p>
                                </div>
                            @endif

                            {{-- Additional Info --}}
                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                    <p class="text-xs font-medium text-green-700 mb-1">PIC Procurement</p>
                                    <p class="text-sm text-green-800">{{ $item->purchasing->nama ?? 'N/A' }}</p>
                                </div>
                                <div class="bg-purple-50 rounded-lg p-3 border border-purple-200">
                                    <p class="text-xs font-medium text-purple-700 mb-1">Bahan Baku</p>
                                    <p class="text-sm text-purple-800">
                                        {{ $item->pengirimanDetails->count() }} jenis bahan baku
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        @if($pengiriman->hasPages())
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                    {{-- Pagination Info --}}
                    <div class="flex items-center text-sm text-gray-700">
                        <span class="mr-2">Menampilkan</span>
                        <span class="font-medium text-yellow-600">{{ $pengiriman->firstItem() ?? 0 }}</span>
                        <span class="mx-1">sampai</span>
                        <span class="font-medium text-yellow-600">{{ $pengiriman->lastItem() ?? 0 }}</span>
                        <span class="mx-1">dari</span>
                        <span class="font-medium text-yellow-600">{{ $pengiriman->total() }}</span>
                        <span class="ml-1">review</span>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="flex items-center space-x-1">
                        {{-- Previous Page --}}
                        @if ($pengiriman->onFirstPage())
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </span>
                        @else
                            @php
                                $prevUrl = $pengiriman->previousPageUrl();
                                $prevUrlParts = parse_url($prevUrl);
                                parse_str($prevUrlParts['query'] ?? '', $prevParams);
                                // Preserve filters
                                if (request('search')) $prevParams['search'] = request('search');
                                if (request('klien')) $prevParams['klien'] = request('klien');
                                if (request('status')) $prevParams['status'] = request('status');
                                if (request('rating')) $prevParams['rating'] = request('rating');
                                $prevUrl = $prevUrlParts['path'] . '?' . http_build_query($prevParams);
                            @endphp
                            <a href="{{ $prevUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 transition-colors">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Sebelumnya
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @if($pengiriman->lastPage() > 1)
                            <div class="hidden sm:flex items-center space-x-1">
                                @foreach ($pengiriman->getUrlRange(1, $pengiriman->lastPage()) as $page => $url)
                                    @php
                                        $urlParts = parse_url($url);
                                        parse_str($urlParts['query'] ?? '', $urlParams);
                                        // Preserve filters
                                        if (request('search')) $urlParams['search'] = request('search');
                                        if (request('klien')) $urlParams['klien'] = request('klien');
                                        if (request('status')) $urlParams['status'] = request('status');
                                        if (request('rating')) $urlParams['rating'] = request('rating');
                                        $pageUrl = $urlParts['path'] . '?' . http_build_query($urlParams);
                                    @endphp
                                    @if ($page == $pengiriman->currentPage())
                                        <span class="px-3 py-2 text-sm font-medium text-white bg-yellow-500 border border-yellow-500 rounded-lg">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $pageUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 transition-colors">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Mobile Page Indicator --}}
                            <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                                {{ $pengiriman->currentPage() }} / {{ $pengiriman->lastPage() }}
                            </div>
                        @endif

                        {{-- Next Page --}}
                        @if ($pengiriman->hasMorePages())
                            @php
                                $nextUrl = $pengiriman->nextPageUrl();
                                $nextUrlParts = parse_url($nextUrl);
                                parse_str($nextUrlParts['query'] ?? '', $nextParams);
                                // Preserve filters
                                if (request('search')) $nextParams['search'] = request('search');
                                if (request('klien')) $nextParams['klien'] = request('klien');
                                if (request('status')) $nextParams['status'] = request('status');
                                if (request('rating')) $nextParams['rating'] = request('rating');
                                $nextUrl = $nextUrlParts['path'] . '?' . http_build_query($nextParams);
                            @endphp
                            <a href="{{ $nextUrl }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 transition-colors">
                                Selanjutnya
                                <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                Selanjutnya
                                <i class="fas fa-chevron-right ml-1"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <i class="fas fa-star text-6xl text-gray-300 mb-6"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Review</h3>
            <p class="text-gray-600 mb-6">
                @if(request()->hasAny(['search', 'status', 'rating', 'klien']))
                    Tidak ditemukan review dengan filter yang diterapkan.
                @else
                    Supplier ini belum memiliki review dari pengiriman yang telah dilakukan.
                @endif
            </p>
            <a href="{{ route('supplier.index') }}" class="inline-flex items-center px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Daftar Supplier
            </a>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
let searchTimeout;

// Debounce function for search
function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        applyFilters();
    }, 500);
}

// Apply filters function
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const klien = document.getElementById('klienFilter').value;
    const status = document.getElementById('statusFilter').value;
    const rating = document.getElementById('ratingFilter').value;

    // Build URL with parameters
    const params = new URLSearchParams();
    
    if (search) params.append('search', search);
    if (klien) params.append('klien', klien);
    if (status) params.append('status', status);
    if (rating) params.append('rating', rating);

    // Redirect with new parameters
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}
</script>
@endpush
