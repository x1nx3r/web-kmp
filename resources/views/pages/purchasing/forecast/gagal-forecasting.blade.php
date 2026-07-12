{{-- Tab Gagal Forecasting --}}
<div class="space-y-6">
    {{-- Search and Filter Section --}}
    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 mb-3 sm:mb-6">
        <div class="space-y-3 sm:space-y-6">
            {{-- Search Section --}}
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                {{-- Search Input --}}
                <div class="flex-1">
                    <label class="flex items-center text-xs sm:text-sm font-bold text-red-700 mb-1 sm:mb-3">
                        <div class="w-4 h-4 sm:w-6 sm:h-6 bg-red-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                            <i class="fas fa-search text-white text-xs"></i>
                        </div>
                        Pencarian Forecast Gagal
                    </label>
                    <div class="relative flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" 
                                   id="searchInputGagal" 
                                   name="search_gagal"
                                   value="{{ request('search_gagal') }}"
                                   placeholder="Cari No. PO, nama klien/pabrik, PIC, atau bahan baku..." 
                                   class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-red-200 focus:border-red-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm search-input-gagal"
                                   onkeypress="handleSearchKeyPressGagal(event)">
                            <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                                <div class="w-3 h-3 sm:w-6 sm:h-6 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-search text-red-500 text-xs sm:text-sm"></i>
                                </div>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="submitSearchGagal()"
                                class="px-4 sm:px-6 py-2 sm:py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg sm:rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold text-sm whitespace-nowrap">
                            <i class="fas fa-search mr-0 sm:mr-2"></i>
                            <span class="hidden sm:inline">Cari</span>
                        </button>
                    </div>
                </div>
            </div>
            {{-- Filter Section --}}
            <div class="rounded-lg sm:rounded-xl p-2 sm:p-4">
                <h3 class="flex items-center text-xs sm:text-sm font-bold text-red-700 mb-2 sm:mb-4">
                    <div class="w-4 h-4 sm:w-6 sm:h-6 bg-red-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                        <i class="fas fa-filter text-white text-xs"></i>
                    </div>
                    Filter
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-red-700 mb-1 sm:mb-2">
                            <i class="fas fa-calendar mr-1 sm:mr-2 text-red-500 text-xs"></i>
                            Tanggal Mulai
                        </label>
                        <input type="date" id="tanggalMulaiFilterGagal" name="tanggal_mulai_gagal" value="{{ request('tanggal_mulai_gagal') }}" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-red-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-red-200 focus:border-red-500 bg-white transition-all duration-200 text-xs sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-red-700 mb-1 sm:mb-2">
                            <i class="fas fa-calendar mr-1 sm:mr-2 text-red-500 text-xs"></i>
                            Tanggal Berakhir
                        </label>
                        <input type="date" id="tanggalAkhirFilterGagal" name="tanggal_akhir_gagal" value="{{ request('tanggal_akhir_gagal') }}" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-red-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-red-200 focus:border-red-500 bg-white transition-all duration-200 text-xs sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-red-700 mb-1 sm:mb-2">
                            <i class="fas fa-user-tie mr-1 sm:mr-2 text-red-500 text-xs"></i>
                            PIC Procurement
                        </label>
                        <select id="filterPurchasingGagal" name="filter_purchasing_gagal" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-red-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-red-200 focus:border-red-500 bg-white transition-all duration-200 text-xs sm:text-sm">
                            <option value="">Semua PIC</option>
                            @foreach($gagalPurchasingOptions as $id => $nama)
                                <option value="{{ $id }}" {{ request('filter_purchasing_gagal') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-3">
                    <button onclick="applyFiltersGagal()" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 text-xs sm:text-sm font-semibold">
                        <i class="fas fa-filter mr-1"></i>
                        Terapkan Filter
                    </button>
                    <button onclick="clearAllFiltersGagal()" class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-lg transition-all duration-200 text-xs sm:text-sm font-semibold">
                        <i class="fas fa-times mr-1"></i>
                        Hapus Semua Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    Forecast Gagal
                </h3>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Total: {{ $gagalForecasts->total() }} forecast (Halaman {{ $gagalForecasts->currentPage() }} dari {{ $gagalForecasts->lastPage() }})
                </div>
                <a href="{{ route('forecast.export-gagal', request()->all()) }}" 
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export Excel
                </a>
            </div>
        </div>

        @forelse($gagalForecasts as $forecast)
        @empty
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-times-circle text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Forecast Gagal</h3>
                <p>Belum ada forecast dengan status gagal.</p>
            </div>
        @endforelse

        @if($gagalForecasts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">No Forecast</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">PO & Klien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Detail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($gagalForecasts as $forecast)
                            <tr class="hover:bg-red-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $forecast->no_forecast }}</div>
                                    @if($forecast->purchasing)
                                        <div class="text-sm text-gray-500">PIC: {{ $forecast->purchasing->nama }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium">{{ optional($forecast->order)->po_number ?? 'N/A' }}</div>
                                        <div class="text-gray-500">{{ optional(optional($forecast->order)->klien)->nama ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div>Qty: <span class="font-medium">{{ $forecast->total_qty_forecast_formatted ?? number_format($forecast->total_qty_forecast, 0, ',', '.') }}</span></div>
                                        <div>Total: <span class="font-medium">{{ $forecast->total_harga_forecast_formatted ?? 'Rp ' . number_format($forecast->total_harga_forecast, 0, ',', '.') }}</span></div>
                                        <div>Kirim: <span class="font-medium">{{ $forecast->hari_kirim_forecast }}</span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $forecast->tanggal_forecast_formatted ?? \Carbon\Carbon::parse($forecast->tanggal_forecast)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Gagal
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openDetailModalGagal({{ $forecast->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                        <i class="fas fa-eye mr-1"></i>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($gagalForecasts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            @if($gagalForecasts->onFirstPage())
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                    Sebelumnya
                                </span>
                            @else
                                <a href="{{ $gagalForecasts->previousPageUrl() }}&tab=gagal" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    Sebelumnya
                                </a>
                            @endif

                            @if($gagalForecasts->hasMorePages())
                                <a href="{{ $gagalForecasts->nextPageUrl() }}&tab=gagal" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    Selanjutnya
                                </a>
                            @else
                                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                    Selanjutnya
                                </span>
                            @endif
                        </div>

                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Menampilkan
                                    <span class="font-medium">{{ $gagalForecasts->firstItem() }}</span>
                                    sampai
                                    <span class="font-medium">{{ $gagalForecasts->lastItem() }}</span>
                                    dari
                                    <span class="font-medium">{{ $gagalForecasts->total() }}</span>
                                    hasil
                                </p>
                            </div>

                            <div>
                                <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                    @if($gagalForecasts->onFirstPage())
                                        <span aria-disabled="true" aria-label="Previous">
                                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </span>
                                    @else
                                        <a href="{{ $gagalForecasts->previousPageUrl() }}&tab=gagal" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Previous">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    @endif

                                    @php
                                        $currentPage = $gagalForecasts->currentPage();
                                        $lastPage = $gagalForecasts->lastPage();
                                        $onEachSide = 2;
                                        $start = max($currentPage - $onEachSide, 1);
                                        $end = min($currentPage + $onEachSide, $lastPage);
                                    @endphp

                                    {{-- Halaman pertama + elipsis --}}
                                    @if($start > 1)
                                        <a href="{{ $gagalForecasts->url(1) }}&tab=gagal" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">1</a>
                                        @if($start > 2)
                                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5">...</span>
                                        @endif
                                    @endif

                                    {{-- Halaman di sekitar halaman aktif --}}
                                    @for($page = $start; $page <= $end; $page++)
                                        @if($page == $currentPage)
                                            <span aria-current="page">
                                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-red-600 border border-red-600 cursor-default leading-5">{{ $page }}</span>
                                            </span>
                                        @else
                                            <a href="{{ $gagalForecasts->url($page) }}&tab=gagal" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                                        @endif
                                    @endfor

                                    {{-- Elipsis + halaman terakhir --}}
                                    @if($end < $lastPage)
                                        @if($end < $lastPage - 1)
                                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5">...</span>
                                        @endif
                                        <a href="{{ $gagalForecasts->url($lastPage) }}&tab=gagal" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">{{ $lastPage }}</a>
                                    @endif

                                    @if($gagalForecasts->hasMorePages())
                                        <a href="{{ $gagalForecasts->nextPageUrl() }}&tab=gagal" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Next">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    @else
                                        <span aria-disabled="true" aria-label="Next">
                                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Include Modal Detail --}}
@include('pages.purchasing.forecast.gagal-forecasting.detail')

<script>
// Handle Enter key press in search input
function handleSearchKeyPressGagal(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        submitSearchGagal();
    }
}

function submitSearchGagal() {
    const currentParams = new URLSearchParams(window.location.search);
    const searchValue = document.getElementById('searchInputGagal').value;

    currentParams.set('tab', 'gagal');

    if (searchValue.trim()) currentParams.set('search_gagal', searchValue.trim());
    else currentParams.delete('search_gagal');

    currentParams.delete('page_gagal');

    window.location.href = '/procurement/forecasting?' + currentParams.toString();
}

function applyFiltersGagal() {
    const currentParams = new URLSearchParams(window.location.search);

    const tanggalMulai = document.getElementById('tanggalMulaiFilterGagal').value;
    const tanggalAkhir = document.getElementById('tanggalAkhirFilterGagal').value;
    const filterPurchasing = document.getElementById('filterPurchasingGagal').value;

    currentParams.set('tab', 'gagal');

    if (tanggalMulai) currentParams.set('tanggal_mulai_gagal', tanggalMulai);
    else currentParams.delete('tanggal_mulai_gagal');

    if (tanggalAkhir) currentParams.set('tanggal_akhir_gagal', tanggalAkhir);
    else currentParams.delete('tanggal_akhir_gagal');

    if (filterPurchasing) currentParams.set('filter_purchasing_gagal', filterPurchasing);
    else currentParams.delete('filter_purchasing_gagal');

    currentParams.delete('page_gagal');

    window.location.href = '/procurement/forecasting?' + currentParams.toString();
}

function clearAllFiltersGagal() {
    const newParams = new URLSearchParams();
    newParams.set('tab', 'gagal');
    window.location.href = '/procurement/forecasting?' + newParams.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);

    const searchValue = urlParams.get('search_gagal');
    if (searchValue) document.getElementById('searchInputGagal').value = searchValue;

    const tanggalMulai = urlParams.get('tanggal_mulai_gagal');
    if (tanggalMulai) document.getElementById('tanggalMulaiFilterGagal').value = tanggalMulai;

    const tanggalAkhir = urlParams.get('tanggal_akhir_gagal');
    if (tanggalAkhir) document.getElementById('tanggalAkhirFilterGagal').value = tanggalAkhir;

    const filterPurchasing = urlParams.get('filter_purchasing_gagal');
    if (filterPurchasing) document.getElementById('filterPurchasingGagal').value = filterPurchasing;
});
</script>
