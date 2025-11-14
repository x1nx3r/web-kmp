@extends('pages.laporan.base')

@section('report-content')
<!-- Weekly Statistics Cards -->
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform ">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-blue-500 text-white shadow-lg">
                <i class="fas fa-calendar-week text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-blue-700 truncate">Pengiriman Minggu Ini</p>
                <p class="text-xl sm:text-2xl font-bold text-blue-900">{{ number_format($weeklyStats['total_pengiriman']) }}</p>
                <p class="text-xs text-blue-600 truncate">{{ $weeklyStats['week_start'] }} - {{ $weeklyStats['week_end'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-green-500 text-white shadow-lg">
                <i class="fas fa-weight text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-green-700 truncate">Tonase Minggu Ini</p>
                <p class="text-xl sm:text-2xl font-bold text-green-900">{{ number_format($weeklyStats['total_tonase']) . ' Kg' }}</p>
                <p class="text-xs text-green-600">Reset Selasa 00:00</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg border border-purple-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform ">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-purple-500 text-white shadow-lg">
                <i class="fas fa-truck text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-purple-700 truncate">Total Pengiriman</p>
                <p class="text-xl sm:text-2xl font-bold text-purple-900">{{ number_format($totalStats['total_pengiriman']) }}</p>
                <p class="text-xs text-purple-600">Keseluruhan</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg border border-orange-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform ">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-orange-500 text-white shadow-lg">
                <i class="fas fa-balance-scale text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-orange-700 truncate">Total Tonase</p>
                <p class="text-xl sm:text-2xl font-bold text-orange-900">{{ number_format($totalStats['total_tonase']) . ' Kg' }}</p>
                <p class="text-xs text-orange-600">Keseluruhan</p>
            </div>
        </div>
    </div>
</div>

<!-- Additional Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-lg border border-emerald-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-emerald-500 text-white shadow-lg">
                <i class="fas fa-money-bill-wave text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-emerald-700 truncate">Harga Beli Minggu Ini</p>
                <p class="text-xl sm:text-2xl font-bold text-emerald-900">Rp {{ number_format($weeklyStats['total_harga'], 0, ',', '.') }}</p>
                <p class="text-xs text-emerald-600 truncate">{{ $weeklyStats['week_start'] }} - {{ $weeklyStats['week_end'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg border border-teal-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-teal-500 text-white shadow-lg">
                <i class="fas fa-chart-line text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-teal-700 truncate">Harga Beli Tahun {{ $yearlyHargaStats['year'] }}</p>
                <p class="text-xl sm:text-2xl font-bold text-teal-900">Rp {{ number_format($yearlyHargaStats['total_harga_tahun'], 0, ',', '.') }}</p>
                <p class="text-xs text-teal-600">Total keseluruhan tahun</p>
            </div>
        </div>
    </div>
</div>

<!-- Combined Chart Section -->
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 sm:p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-4 space-y-4 lg:space-y-0">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Pengiriman & Tonase per PIC ({{ $chartData['year'] }})</h3>
                <p class="text-xs sm:text-sm text-gray-600">Klik legenda untuk memilih PIC tertentu (klik lagi untuk tampilkan semua)</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
            <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm font-medium text-gray-700 whitespace-nowrap">Jenis Data:</label>
                <select id="chartTypeSelector" class="text-xs sm:text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="pengiriman">Jumlah Pengiriman</option>
                    <option value="tonase">Tonase (Kg)</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm font-medium text-gray-700 whitespace-nowrap">Tahun:</label>
                <div class="flex items-center">
                    <button onclick="changeChartYear(-1)" class="w-8 h-8 flex items-center justify-center text-sm border border-gray-300 rounded-l-lg hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    <input type="text" id="chartYearInput" value="{{ $chartData['year'] }}" 
                           class="w-16 text-xs sm:text-sm text-center border-t border-b border-gray-300 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           readonly
                           title="Range: {{ $yearRange['min_year'] ?? 2020 }} - {{ $yearRange['max_year'] ?? date('Y') }}">
                    <button onclick="changeChartYear(1)" class="w-8 h-8 flex items-center justify-center text-sm border border-gray-300 rounded-r-lg hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="h-64 sm:h-80 lg:h-96">
        <canvas id="combinedChart"></canvas>
    </div>
</div>

<!-- Enhanced Filter Section -->
<div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-6">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-filter text-white text-sm"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Filter & Pencarian</h3>
                <p class="text-xs text-gray-500 mt-1">Filter tabel data pengiriman (semua status)</p>
            </div>
        </div>
    </div>
    <div class="p-4 sm:p-6">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-3  md:grid-cols-3 xl:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="menunggu_verifikasi" {{ $status == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="berhasil" {{ $status == 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                        <option value="gagal" {{ $status == 'gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">PIC Purchasing</label>
                    <select name="purchasing" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua PIC</option>
                        @foreach($purchasingUsers as $user)
                            <option value="{{ $user->id }}" {{ $purchasing == $user->id ? 'selected' : '' }}>{{ $user->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="No. pengiriman atau PIC..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4">
                <button type="submit" 
                        class="flex items-center justify-center bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-4 sm:px-6 py-2.5 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    <i class="fas fa-filter mr-2"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('laporan.pengiriman') }}" 
                   class="flex items-center justify-center bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white px-4 sm:px-6 py-2.5 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    <i class="fas fa-refresh mr-2"></i>
                    <span>Reset</span>
                </a>
                <a href="{{ route('laporan.pengiriman.export', request()->query()) }}" 
                   class="flex items-center justify-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 sm:px-6 py-2.5 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    <i class="fas fa-download mr-2"></i>
                    <span>Export</span>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-lg border border-gray-100">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-table text-white text-sm"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Data Pengiriman</h3>
                <p class="text-xs text-gray-500 mt-1">Menampilkan semua status sesuai filter yang dipilih</p>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <div class="inline-block min-w-full align-middle">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No. Pengiriman</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Tanggal Kirim</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">PIC Purchasing</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">PO Terkait</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty Kirim</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Total Harga</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pengirimanData as $index => $pengiriman)
                    <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ ($pengirimanData->currentPage() - 1) * $pengirimanData->perPage() + $index + 1 }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $pengiriman->no_pengiriman ?: 'PG-' . str_pad($pengiriman->id, 4, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="text-xs text-gray-500 sm:hidden">
                                {{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                {{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $pengiriman->purchasing->nama ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500 lg:hidden">
                                        PO-{{ str_pad($pengiriman->purchase_order_id ?? 0, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                            <div class="flex items-center">
                                PO-{{ str_pad($pengiriman->purchase_order_id ?? 0, 4, '0', STR_PAD_LEFT) }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ number_format($pengiriman->total_qty_kirim ?? 0) }} Kg
                            </div>
                            <div class="text-xs text-gray-500 md:hidden">
                                Rp {{ number_format($pengiriman->total_harga_kirim ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold hidden md:table-cell">
                            <div class="flex items-center">
                                Rp {{ number_format($pengiriman->total_harga_kirim ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                            @switch($pengiriman->status)
                                @case('pending')
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 shadow-sm">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></div>
                                        <span class="hidden sm:inline">Pending</span>
                                        <span class="sm:hidden">P</span>
                                    </span>
                                    @break
                                
                                @case('menunggu_verifikasi')
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-orange-100 to-orange-200 text-orange-800 shadow-sm">
                                        <div class="w-2 h-2 bg-orange-500 rounded-full mr-1.5 animate-pulse"></div>
                                        <span class="hidden sm:inline">Menunggu</span>
                                        <span class="sm:hidden">M</span>
                                    </span>
                                    @break
                                
                                @case('berhasil')
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow-sm">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></div>
                                        <span class="hidden sm:inline">Berhasil</span>
                                        <span class="sm:hidden">B</span>
                                    </span>
                                    @break
                                
                                @case('gagal')
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-red-100 to-red-200 text-red-800 shadow-sm">
                                        <div class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></div>
                                        <span class="hidden sm:inline">Gagal</span>
                                        <span class="sm:hidden">G</span>
                                    </span>
                                    @break
                                
                                @default
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm">
                                        <div class="w-2 h-2 bg-gray-500 rounded-full mr-1.5"></div>
                                        <span class="hidden sm:inline">Tidak Diketahui</span>
                                        <span class="sm:hidden">?</span>
                                    </span>
                            @endswitch
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada data pengiriman</h3>
                                <p class="text-xs text-gray-500">Tidak ada data pengiriman untuk periode ini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($pengirimanData->hasPages())
    <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                @if ($pengirimanData->onFirstPage())
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                        <i class="fas fa-chevron-left mr-2"></i> Sebelumnya
                    </span>
                @else
                    <a href="{{ $pengirimanData->appends(request()->query())->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                        <i class="fas fa-chevron-left mr-2"></i> Sebelumnya
                    </a>
                @endif

                @if ($pengirimanData->hasMorePages())
                    <a href="{{ $pengirimanData->appends(request()->query())->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                        Selanjutnya <i class="fas fa-chevron-right ml-2"></i>
                    </a>
                @else
                    <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                        Selanjutnya <i class="fas fa-chevron-right ml-2"></i>
                    </span>
                @endif
            </div>

            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Menampilkan
                        <span class="font-medium">{{ $pengirimanData->firstItem() ?: 0 }}</span>
                        sampai
                        <span class="font-medium">{{ $pengirimanData->lastItem() ?: 0 }}</span>
                        dari
                        <span class="font-medium">{{ $pengirimanData->total() }}</span>
                        hasil
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        {{-- Previous Page Link --}}
                        @if ($pengirimanData->onFirstPage())
                            <span aria-disabled="true" aria-label="Previous">
                                <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            </span>
                        @else
                            <a href="{{ $pengirimanData->appends(request()->query())->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Previous">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($pengirimanData->appends(request()->query())->getUrlRange(1, $pengirimanData->lastPage()) as $page => $url)
                            @if ($page == $pengirimanData->currentPage())
                                <span aria-current="page">
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-blue-600 cursor-default leading-5 shadow-sm">{{ $page }}</span>
                                </span>
                            @else
                                <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($pengirimanData->hasMorePages())
                            <a href="{{ $pengirimanData->appends(request()->query())->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Next">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span aria-disabled="true" aria-label="Next">
                                <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            </span>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart data from backend
const chartData = @json($chartData);
const yearRange = @json($yearRange);
const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#14B8A6'];

let combinedChart;
let currentDataType = 'pengiriman';
let selectedDatasetIndex = null;
const minYear = yearRange.min_year;
const maxYear = yearRange.max_year;

console.log('Year range:', yearRange);

// Initialize Combined Chart
function initCombinedChart() {
    const ctx = document.getElementById('combinedChart').getContext('2d');
    
    if (!chartData || !chartData.data || Object.keys(chartData.data).length === 0) {
        console.error('No chart data available');
        ctx.fillStyle = '#6b7280';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Tidak ada data untuk ditampilkan', ctx.canvas.width / 2, ctx.canvas.height / 2);
        return;
    }
    
    const datasets = Object.keys(chartData.data).map((pic, index) => ({
        label: pic,
        data: chartData.data[pic][currentDataType] || [],
        borderColor: colors[index % colors.length],
        backgroundColor: colors[index % colors.length] + '20',
        tension: 0.4,
        fill: false,
        borderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: colors[index % colors.length],
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        hidden: false
    }));

    combinedChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.months,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: currentDataType === 'pengiriman' ? 'Jumlah Pengiriman (Status: Berhasil)' : 'Tonase dalam Kg (Status: Berhasil)'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        generateLabels: function(chart) {
                            const original = Chart.defaults.plugins.legend.labels.generateLabels;
                            const labels = original.call(this, chart);
                            
                            labels.forEach((label, index) => {
                                if (selectedDatasetIndex === null) {
                                    label.fillStyle = colors[index % colors.length];
                                    label.strokeStyle = colors[index % colors.length];
                                    label.fontColor = '#000';
                                } else if (selectedDatasetIndex === index) {
                                    label.fillStyle = colors[index % colors.length];
                                    label.strokeStyle = colors[index % colors.length];
                                    label.fontColor = '#000';
                                } else {
                                    label.fillStyle = 'rgba(128, 128, 128, 0.3)';
                                    label.strokeStyle = 'rgba(128, 128, 128, 0.3)';
                                    label.fontColor = '#999';
                                }
                            });
                            
                            return labels;
                        }
                    },
                    onClick: function(evt, legendItem, legend) {
                        const index = legendItem.datasetIndex;
                        const ci = legend.chart;
                        
                        if (selectedDatasetIndex === index) {
                            selectedDatasetIndex = null;
                            ci.data.datasets.forEach((dataset, i) => {
                                ci.show(i);
                            });
                        } else {
                            selectedDatasetIndex = index;
                            ci.data.datasets.forEach((dataset, i) => {
                                if (i === index) {
                                    ci.show(i);
                                } else {
                                    ci.hide(i);
                                }
                            });
                        }
                        
                        ci.update();
                    }
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return 'Bulan: ' + context[0].label;
                        },
                        label: function(context) {
                            const label = context.dataset.label;
                            const value = context.parsed.y;
                            const unit = currentDataType === 'pengiriman' ? 'pengiriman' : 'Kg';
                            return `${label}: ${value.toLocaleString('id-ID')} ${unit}`;
                        },
                        footer: function(context) {
                            return 'Status: Berhasil';
                        }
                    },
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    footerColor: '#10B981',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true
                }
            },
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
            }
        }
    });
}

// Update chart data type
function updateChartDataType(dataType) {
    currentDataType = dataType;
    
    combinedChart.data.datasets.forEach((dataset, index) => {
        const pic = Object.keys(chartData.data)[index];
        dataset.data = chartData.data[pic][dataType];
    });
    
    combinedChart.options.scales.y.title.text = dataType === 'pengiriman' ? 
        'Jumlah Pengiriman (Status: Berhasil)' : 
        'Tonase dalam Kg (Status: Berhasil)';
    
    combinedChart.update('active');
}

// Change year with +/- buttons
function changeChartYear(direction) {
    const input = document.getElementById('chartYearInput');
    const currentYear = parseInt(input.value);
    const newYear = currentYear + direction;
    
    if (newYear >= minYear && newYear <= maxYear) {
        const url = new URL(window.location);
        url.searchParams.set('year', newYear);
        window.location = url;
    } else {
        const limitText = newYear < minYear ? `minimum (${minYear})` : `maksimum (${maxYear})`;
        showNotification(`Tahun ${limitText} tercapai`, 'warning');
    }
}

// Simple notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;

    const bgColor = type === 'success' ? 'bg-green-500' : 
                   type === 'error' ? 'bg-red-500' : 
                   type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
    const iconName = type === 'success' ? 'check' : 
                    type === 'error' ? 'exclamation-triangle' : 
                    type === 'warning' ? 'exclamation-circle' : 'info-circle';
    
    notification.classList.add(bgColor, 'text-white');

    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${iconName} mr-2"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    initCombinedChart();
    
    document.getElementById('chartTypeSelector').addEventListener('change', function() {
        updateChartDataType(this.value);
    });
});



</script>
@endsection