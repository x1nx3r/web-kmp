@extends('pages.laporan.base')

@section('report-content')

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total Transaksi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-list-alt text-blue-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-1">Total Transaksi</p>
                <h3 class="text-2xl font-bold text-blue-600">{{ number_format(count($marginData)) }}</h3>
                <p class="text-xs text-gray-500 mt-1">
                    <span class="text-green-600 font-medium">{{ $profitCount }} Profit</span> • 
                    <span class="text-red-600 font-medium">{{ $lossCount }} Loss</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Total Harga Beli --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-shopping-cart text-red-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-1">Total Harga Beli</p>
                <h3 class="text-2xl font-bold text-red-600">
                    @if($totalHargaBeli >= 1000000000)
                        Rp {{ number_format($totalHargaBeli / 1000000000, 2, ',', '.') }} M
                    @else
                        Rp {{ number_format($totalHargaBeli / 1000000, 2, ',', '.') }} Jt
                    @endif
                </h3>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($totalQty, 0, ',', '.') }} kg</p>
            </div>
        </div>
    </div>

    {{-- Total Harga Jual --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-tags text-green-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-1">Total Harga Jual</p>
                <h3 class="text-2xl font-bold text-green-600">
                    @if($totalHargaJual >= 1000000000)
                        Rp {{ number_format($totalHargaJual / 1000000000, 2, ',', '.') }} M
                    @else
                        Rp {{ number_format($totalHargaJual / 1000000, 2, ',', '.') }} Jt
                    @endif
                </h3>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($totalQty, 0, ',', '.') }} kg</p>
            </div>
        </div>
    </div>

    {{-- Gross Margin --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 {{ $totalMargin >= 0 ? 'bg-purple-100' : 'bg-orange-100' }} rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-chart-line {{ $totalMargin >= 0 ? 'text-purple-600' : 'text-orange-600' }} text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-1">Gross Margin</p>
                <h3 class="text-2xl font-bold {{ $totalMargin >= 0 ? 'text-purple-600' : 'text-orange-600' }}">
                    {{ $totalMargin >= 0 ? '+' : '' }}{{ number_format($grossMarginPercentage, 2, ',', '.') }}%
                </h3>
                <p class="text-xs {{ $totalMargin >= 0 ? 'text-purple-600' : 'text-orange-600' }} mt-1 font-medium">
                    {{ $totalMargin >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalMargin), 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Filter Section --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-filter text-indigo-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Filter Data</h3>
                <p class="text-xs text-gray-500">Pilih kriteria untuk memfilter data margin</p>
            </div>
        </div>
        <button type="button" onclick="resetFilters()" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
            <i class="fas fa-redo-alt mr-1"></i>
            Reset Filter
        </button>
    </div>

    <form method="GET" action="{{ route('laporan.margin') }}" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Tanggal Mulai --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="far fa-calendar mr-1"></i>
                    Tanggal Mulai
                </label>
                <input type="date" 
                       name="start_date" 
                       value="{{ $startDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>

            {{-- Tanggal Akhir --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="far fa-calendar mr-1"></i>
                    Tanggal Akhir
                </label>
                <input type="date" 
                       name="end_date" 
                       value="{{ $endDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>

            {{-- PIC Purchasing --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-1"></i>
                    PIC Procurement
                </label>
                <select name="pic_purchasing" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">Semua PIC</option>
                    @foreach($picPurchasingList as $pic)
                        <option value="{{ $pic->id }}" {{ $picPurchasing == $pic->id ? 'selected' : '' }}>
                            {{ $pic->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Klien --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-building mr-1"></i>
                    Klien (Pabrik)
                </label>
                <select name="klien" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">Semua Klien</option>
                    @foreach($klienList as $klien)
                        <option value="{{ $klien->id }}" {{ $klienId == $klien->id ? 'selected' : '' }}>
                            {{ $klien->nama }}{{ $klien->cabang ? " ({$klien->cabang})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Supplier --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-truck mr-1"></i>
                    Supplier
                </label>
                <select name="supplier" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">Semua Supplier</option>
                    @foreach($supplierList as $supplier)
                        <option value="{{ $supplier->id }}" {{ $supplierId == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Bahan Baku --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-box mr-1"></i>
                    Bahan Baku
                </label>
                <select name="bahan_baku" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">Semua Bahan Baku</option>
                    @foreach($bahanBakuList as $bahanBaku)
                        <option value="{{ $bahanBaku->id }}" {{ $bahanBakuId == $bahanBaku->id ? 'selected' : '' }}>
                            {{ $bahanBaku->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-4">
            <button type="button" 
                    onclick="downloadPDF()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium text-sm">
                <i class="fas fa-file-pdf mr-2"></i>
                Download PDF
            </button>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors font-medium text-sm">
                <i class="fas fa-search mr-2"></i>
                Terapkan Filter
            </button>
           
        </div>
    </form>
</div>

{{-- Data Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-table text-purple-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Detail Analisis Margin</h3>
                    <p class="text-xs text-gray-500">{{ count($marginData) }} transaksi ditemukan</p>
                </div>
            </div>
            
            {{-- Search --}}
            <div class="relative">
                <input type="text" 
                       id="searchTable" 
                       placeholder="Cari data..." 
                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm w-64"
                       onkeyup="searchTable()">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="marginTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Pengiriman</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC Procurement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klien</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty (kg)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli/kg</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Beli</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual/kg</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jual</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Margin (Rp)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Margin (%)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($marginData as $index => $item)
                    <tr class="hover:bg-gray-100 transition-colors cursor-pointer" 
                        onclick="window.location.href='{{ route('purchasing.pengiriman.index') }}?tab={{ isset($item['status']) && $item['status'] === 'berhasil' ? 'pengiriman-berhasil' : (isset($item['status']) && $item['status'] === 'menunggu_verifikasi' ? 'menunggu-verifikasi' : 'menunggu-fisik') }}&detail={{ $item['pengiriman_id'] ?? '' }}'">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item['tanggal_kirim'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $item['no_pengiriman'] }}
                            @if($item['has_refraksi'])
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800" title="Ada Refraksi">
                                    <i class="fas fa-percentage text-xs"></i>
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['pic_purchasing'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['klien'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['supplier'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['bahan_baku'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-medium">
                            {{ number_format($item['qty'], 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                            Rp {{ number_format($item['harga_beli_per_kg'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-red-700">
                            Rp {{ number_format($item['harga_beli_total'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                            @if($item['harga_jual_per_kg'] > 0)
                                Rp {{ number_format($item['harga_jual_per_kg'], 0, ',', '.') }}
                            @else
                                <span class="text-gray-400 text-xs">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-700">
                            @if($item['harga_jual_total'] > 0)
                                Rp {{ number_format($item['harga_jual_total'], 0, ',', '.') }}
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $item['margin'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ $item['margin'] >= 0 ? '+' : '' }}Rp {{ number_format($item['margin'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $item['margin'] >= 0 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">
                                {{ $item['margin'] >= 0 ? '+' : '' }}{{ number_format($item['margin_percentage'], 2, ',', '.') }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <p class="text-sm font-medium">Tidak ada data margin</p>
                                <p class="text-xs mt-1">Silakan ubah filter untuk melihat data</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($marginData) > 0)
                <tfoot class="bg-gray-100 font-semibold">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-right text-sm text-gray-900">TOTAL:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                            {{ number_format($totalQty, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-700">
                            Rp {{ number_format($totalHargaBeli, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-700">
                            Rp {{ number_format($totalHargaJual, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $totalMargin >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ $totalMargin >= 0 ? '+' : '' }}Rp {{ number_format($totalMargin, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $totalMargin >= 0 ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $totalMargin >= 0 ? '+' : '' }}{{ number_format($grossMarginPercentage, 2, ',', '.') }}%
                            </span>
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

<script>
// Search table function
function searchTable() {
    const input = document.getElementById('searchTable');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('marginTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length - 1; i++) { // Skip header and footer
        let found = false;
        const td = tr[i].getElementsByTagName('td');
        
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                const txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        tr[i].style.display = found ? '' : 'none';
    }
}

// Reset filters
function resetFilters() {
    window.location.href = '{{ route("laporan.margin") }}';
}

// Download PDF with current filters
function downloadPDF() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Build query string from form data
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Redirect to PDF download route with filters
    window.location.href = '{{ route("laporan.margin.pdf") }}?' + params.toString();
}

</script>

@endsection
