@extends('pages.laporan.base')

@section('report-content')
<!-- Weekly and Yearly Statistics Cards - Baris 1 (4 cards) -->
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
    <!-- Pengiriman Minggu Ini -->
    <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform">
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
    
    <!-- Tonase Minggu Ini -->
    <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-green-500 text-white shadow-lg">
                <i class="fas fa-weight text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-green-700 truncate">Tonase Minggu Ini</p>
                <p class="text-xl sm:text-2xl font-bold text-green-900">{{ number_format($weeklyStats['total_tonase'], 2, ',', '.') }} Kg</p>
                <p class="text-xs text-green-600 truncate">{{ $weeklyStats['week_start'] }} - {{ $weeklyStats['week_end'] }}</p>
            </div>
        </div>
    </div>
    
    <!-- Pengiriman Tahun Ini -->
    <div class="bg-white rounded-xl shadow-lg border border-purple-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-purple-500 text-white shadow-lg">
                <i class="fas fa-calendar-alt text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-purple-700 truncate">Pengiriman Tahun Ini</p>
                <p class="text-xl sm:text-2xl font-bold text-purple-900">{{ number_format($yearlyStats['total_pengiriman']) }}</p>
                <p class="text-xs text-purple-600">Tahun {{ $yearlyStats['year'] }}</p>
            </div>
        </div>
    </div>
    
    <!-- Tonase Tahun Ini -->
    <div class="bg-white rounded-xl shadow-lg border border-orange-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-orange-500 text-white shadow-lg">
                <i class="fas fa-balance-scale text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-orange-700 truncate">Tonase Tahun Ini</p>
                <p class="text-xl sm:text-2xl font-bold text-orange-900">{{ number_format($yearlyStats['total_tonase'], 2, ',', '.') }} Kg</p>
                <p class="text-xs text-orange-600">Tahun {{ $yearlyStats['year'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Total Statistics Cards - Baris 2 (2 cards) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
    <!-- Pengiriman Total -->
    <div class="bg-white rounded-xl shadow-lg border border-indigo-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-indigo-500 text-white shadow-lg">
                <i class="fas fa-truck text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-indigo-700 truncate">Pengiriman Total</p>
                <p class="text-xl sm:text-2xl font-bold text-indigo-900">{{ number_format($totalStats['total_pengiriman']) }}</p>
                <p class="text-xs text-indigo-600">Keseluruhan</p>
            </div>
        </div>
    </div>
    
    <!-- Tonase Total -->
    <div class="bg-white rounded-xl shadow-lg border border-cyan-200 p-4 sm:p-6 hover:shadow-xl transition-all duration-300 transform">
        <div class="flex items-center">
            <div class="p-2 sm:p-3 rounded-xl bg-cyan-500 text-white shadow-lg">
                <i class="fas fa-balance-scale-right text-lg sm:text-2xl"></i>
            </div>
            <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-cyan-700 truncate">Tonase Total</p>
                <p class="text-xl sm:text-2xl font-bold text-cyan-900">{{ number_format($totalStats['total_tonase'], 2, ',', '.') }} Kg</p>
                <p class="text-xs text-cyan-600">Keseluruhan</p>
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

<!-- Pie Chart Section - Status Pengiriman -->
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 sm:p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-4 space-y-4 lg:space-y-0">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-chart-pie text-white text-lg"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Distribusi Status Pengiriman</h3>
                <p class="text-xs sm:text-sm text-gray-600">Normal (>70%), Bongkar Sebagian (≤70%), dan Ditolak</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
            <form method="GET" id="pieChartFilterForm" class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
                <!-- Preserve other parameters -->
                <input type="hidden" name="year" value="{{ request('year', now()->year) }}">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                @if($purchasing)<input type="hidden" name="purchasing" value="{{ $purchasing }}">@endif
                @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
                
                <div class="flex items-center space-x-2">
                    <label class="text-xs sm:text-sm font-medium text-gray-700 whitespace-nowrap">Filter:</label>
                    <select name="pie_filter" id="pieFilterSelect" class="text-xs sm:text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" onchange="togglePieDateRange()">
                        <option value="semua" {{ $pieChartFilter == 'semua' ? 'selected' : '' }}>Semua Data</option>
                        <option value="bulan_ini" {{ $pieChartFilter == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="tahun_ini" {{ $pieChartFilter == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="range" {{ $pieChartFilter == 'range' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                
                <div id="pieDateRangeContainer" class="flex items-center space-x-2" style="display: {{ $pieChartFilter == 'range' ? 'flex' : 'none' }}">
                    <input type="date" name="pie_start_date" value="{{ $pieChartStartDate ?? now()->startOfMonth()->format('Y-m-d') }}" 
                           class="text-xs sm:text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <span class="text-gray-500">-</span>
                    <input type="date" name="pie_end_date" value="{{ $pieChartEndDate ?? now()->endOfMonth()->format('Y-m-d') }}" 
                           class="text-xs sm:text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <button type="submit" class="px-4 py-1.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white text-xs sm:text-sm rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-sync-alt mr-1"></i> Update
                </button>
            </form>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pie Chart -->
        <div class="flex items-center justify-center">
            <div class="w-full max-w-md h-80">
                <canvas id="statusPieChart"></canvas>
            </div>
        </div>
        
        <!-- Legend & Summary -->
        <div class="flex flex-col justify-center space-y-4">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Ringkasan Data</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded-full" style="background-color: #10B981;"></div>
                            <span class="text-sm font-medium text-gray-700">Pengiriman Normal</span>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-600">{{ $pieChartData['normal'] }}</div>
                            <div class="text-xs text-gray-500">{{ $pieChartData['normal_percentage'] }}%</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded-full" style="background-color: #F59E0B;"></div>
                            <span class="text-sm font-medium text-gray-700">Bongkar Sebagian</span>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-orange-600">{{ $pieChartData['bongkar'] }}</div>
                            <div class="text-xs text-gray-500">{{ $pieChartData['bongkar_percentage'] }}%</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded-full" style="background-color: #EF4444;"></div>
                            <span class="text-sm font-medium text-gray-700">Pengiriman Ditolak</span>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-red-600">{{ $pieChartData['gagal'] }}</div>
                            <div class="text-xs text-gray-500">{{ $pieChartData['gagal_percentage'] }}%</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg border-2 border-purple-200">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-truck text-purple-600"></i>
                            <span class="text-sm font-bold text-purple-700">Total Pengiriman</span>
                        </div>
                        <div class="text-right">
                            <div class="text-xl font-bold text-purple-700">{{ $pieChartData['total'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
           
            
            <!-- Tombol Detail -->
            <div class="mt-4">
                <button type="button" onclick="showPieChartDetailModal()" class="w-full px-4 py-3 bg-blue-600  text-white text-sm rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-list-alt"></i>
                    <span>Lihat Detail Pengiriman</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pie Chart -->
<div id="pieChartDetailModal" class="fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-7xl max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-blue-600">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                            <i class="fas fa-list-alt text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 id="pieChartModalTitle" class="text-xl font-bold text-white">Detail Pengiriman</h3>
                            <p id="pieChartModalSubtitle" class="text-sm text-purple-100 mt-0.5"></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="exportPieChartPDF()" class="px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i>
                            <span class="hidden sm:inline">Export PDF</span>
                        </button>
                        <button onclick="closePieChartModal()" class="text-white hover:bg-white/20 rounded-lg p-2 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-green-700">Normal (>70%)</p>
                                <p id="modalNormalCount" class="text-2xl font-bold text-green-900">0</p>
                            </div>
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-orange-700">Bongkar Sebagian</p>
                                <p id="modalBongkarCount" class="text-2xl font-bold text-orange-900">0</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-red-700">Ditolak</p>
                                <p id="modalGagalCount" class="text-2xl font-bold text-red-900">0</p>
                            </div>
                            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-times-circle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-purple-700">Total</p>
                                <p id="modalTotalCount" class="text-2xl font-bold text-purple-900">0</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-truck text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Table Content -->
                <div id="pieChartModalContent" class="bg-white rounded-lg border border-gray-200">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
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
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7 gap-4">
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
                        <option value="berhasil" {{ $status == 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                        <option value="menunggu_fisik" {{ $status == 'menunggu_fisik' ? 'selected' : '' }}>Menunggu Fisik</option>
                        <option value="menunggu_verifikasi" {{ $status == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="gagal" {{ $status == 'gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">PIC Procurement</label>
                    <select name="purchasing" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua PIC</option>
                        @foreach($purchasingUsers as $user)
                            <option value="{{ $user->id }}" {{ $purchasing == $user->id ? 'selected' : '' }}>{{ $user->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pabrik</label>
                    <select name="pabrik" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Pabrik</option>
                        @foreach($pabrikList as $pabrikItem)
                            <option value="{{ $pabrikItem->id }}" {{ $pabrik == $pabrikItem->id ? 'selected' : '' }}>
                                {{ $pabrikItem->nama }}{{ $pabrikItem->cabang ? ' - ' . $pabrikItem->cabang : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                    <select name="supplier" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Supplier</option>
                        @foreach($supplierList as $supplierItem)
                            <option value="{{ $supplierItem->id }}" {{ $supplier == $supplierItem->id ? 'selected' : '' }}>{{ $supplierItem->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="No. Pengiriman atau No. PO..." 
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
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No. PO</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Pabrik</th>
                       
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Tanggal Kirim</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">PIC Procurement</th>
                        <th class="px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty Kirim</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden xl:table-cell">Supplier</th>
                        <th class="px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Harga Beli/Kg</th>
                        <th class="px-3 sm:px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pengirimanData as $index => $pengiriman)
                    <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 cursor-pointer" 
                        data-id="{{ $pengiriman->id }}"
                        data-status="{{ $pengiriman->status }}"
                        onclick="navigateToPengirimanDetail({{ $pengiriman->id }}, '{{ $pengiriman->status }}')">
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ ($pengirimanData->currentPage() - 1) * $pengirimanData->perPage() + $index + 1 }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-blue-600">
                                {{ $pengiriman->order->po_number ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-500 lg:hidden">
                                {{ $pengiriman->order->klien->nama ?? '-' }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden lg:table-cell">
                            {{ $pengiriman->order->klien->nama ?? '-' }}
                            @if($pengiriman->order->klien->cabang ?? false)
                                <span class="text-xs text-gray-500">({{ $pengiriman->order->klien->cabang }})</span>
                            @endif
                        </td>
                            
                            <div class="text-xs text-gray-500 sm:hidden">
                                @php
                                    // Display logic: prioritas tanggal_kirim, fallback ke updated_at untuk pengiriman gagal
                                    $displayTanggal = $pengiriman->tanggal_kirim 
                                        ? $pengiriman->tanggal_kirim->format('d/m/Y') 
                                        : ($pengiriman->status === 'gagal' && $pengiriman->updated_at 
                                            ? $pengiriman->updated_at->format('d/m/Y')
                                            : '-');
                                @endphp
                                {{ $displayTanggal }}
                            </div>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                @php
                                    // Display logic: prioritas tanggal_kirim, fallback ke updated_at untuk pengiriman gagal
                                    $displayTanggal = $pengiriman->tanggal_kirim 
                                        ? $pengiriman->tanggal_kirim->format('d/m/Y') 
                                        : ($pengiriman->status === 'gagal' && $pengiriman->updated_at 
                                            ? $pengiriman->updated_at->format('d/m/Y') 
                                            : '-');
                                @endphp
                                {{ $displayTanggal }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $pengiriman->purchasing->nama ?? '-' }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ number_format($pengiriman->total_qty_kirim ?? 0, 2, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden xl:table-cell">
                            @php
                                $suppliers = $pengiriman->pengirimanDetails->map(function($detail) {
                                    return $detail->bahanBakuSupplier->supplier->nama ?? '-';
                                })->unique()->values();
                            @endphp
                            @if($suppliers->count() > 1)
                                <div class="text-xs">{{ $suppliers->first() }}</div>
                                <div class="text-xs text-gray-500">+{{ $suppliers->count() - 1 }} lainnya</div>
                            @else
                                {{ $suppliers->first() ?? '-' }}
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 hidden md:table-cell">
                            @php
                                $totalHarga = 0;
                                $totalQty = 0;
                                foreach($pengiriman->pengirimanDetails as $detail) {
                                    $totalHarga += ($detail->harga_satuan ?? 0) * ($detail->qty_kirim ?? 0);
                                    $totalQty += $detail->qty_kirim ?? 0;
                                }
                                $avgHarga = $totalQty > 0 ? $totalHarga / $totalQty : 0;
                            @endphp
                            Rp {{ number_format($avgHarga, 0, ',', '.') }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-center">
                            @switch($pengiriman->status)
                                @case('pending')
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 shadow-sm">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></div>
                                        <span class="hidden sm:inline">Pending</span>
                                        <span class="sm:hidden">P</span>
                                    </span>
                                    @break
                                
                                @case('menunggu_fisik')
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 shadow-sm">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-1.5 animate-pulse"></div>
                                        <span class="hidden sm:inline">Menunggu Fisik</span>
                                        <span class="sm:hidden">MF</span>
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
// Helper function to determine tab based on status
function getTabByStatus(status) {
    switch(status) {
        case 'pending':
            return 'pengiriman-masuk';
        case 'berhasil':
            return 'pengiriman-berhasil';
        case 'menunggu_verifikasi':
            return 'menunggu-verifikasi';
        case 'menunggu_fisik':
            return 'menunggu-fisik';
        case 'gagal':
            return 'pengiriman-gagal';
        default:
            return 'pengiriman-masuk';
    }
}

// Function to navigate to pengiriman detail
function navigateToPengirimanDetail(id, status) {
    const tab = getTabByStatus(status);
    window.location.href = `{{ route('purchasing.pengiriman.index') }}?tab=${tab}&detail=${id}`;
}

// Chart data from backend
const chartData = @json($chartData);
const yearRange = @json($yearRange);
const pieChartData = @json($pieChartData);
const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#14B8A6'];

let combinedChart;
let statusPieChart;
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
                            const formattedValue = currentDataType === 'pengiriman' ? 
                                value.toLocaleString('id-ID') : 
                                value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            return `${label}: ${formattedValue} ${unit}`;
                        },
                        
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

// Initialize Pie Chart for Status Distribution
function initStatusPieChart() {
    const ctx = document.getElementById('statusPieChart').getContext('2d');
    
    if (pieChartData.total === 0) {
        ctx.fillStyle = '#6b7280';
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Tidak ada data untuk ditampilkan', ctx.canvas.width / 2, ctx.canvas.height / 2);
        return;
    }
    
    statusPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Pengiriman Normal', 'Bongkar Sebagian', 'Pengiriman Ditolak'],
            datasets: [{
                data: [pieChartData.normal, pieChartData.bongkar, pieChartData.gagal],
                backgroundColor: [
                    '#10B981', // Green for Normal
                    '#F59E0B', // Orange for Bongkar Sebagian
                    '#EF4444'  // Red for Gagal
                ],
                borderColor: [
                    '#ffffff',
                    '#ffffff',
                    '#ffffff'
                ],
                borderWidth: 3,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // We use custom legend
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = pieChartData.total;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    },
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    displayColors: true,
                    boxWidth: 15,
                    boxHeight: 15
                }
            },
         
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
        'Jumlah Pengiriman (Menunggu Fisik, Menunggu Verifikasi, Berhasil, Gagal)' : 
        'Tonase dalam Kg (Menunggu Fisik, Menunggu Verifikasi, Berhasil)';
    
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

// Toggle pie chart date range visibility
function togglePieDateRange() {
    const filterSelect = document.getElementById('pieFilterSelect');
    const dateRangeContainer = document.getElementById('pieDateRangeContainer');
    
    if (filterSelect.value === 'range') {
        dateRangeContainer.style.display = 'flex';
    } else {
        dateRangeContainer.style.display = 'none';
    }
}

// Show pie chart detail modal
function showPieChartDetailModal() {
    const filter = document.getElementById('pieFilterSelect').value;
    const startDate = document.querySelector('input[name="pie_start_date"]')?.value || '';
    const endDate = document.querySelector('input[name="pie_end_date"]')?.value || '';
    
    // Set modal title based on filter
    let filterTitle = '';
    let filterSubtitle = '';
    const now = new Date();
    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    switch(filter) {
        case 'semua':
            filterTitle = 'Semua Data Pengiriman';
            filterSubtitle = 'Menampilkan seluruh data pengiriman';
            break;
        case 'bulan_ini':
            filterTitle = `Pengiriman Bulan ${monthNames[now.getMonth()]} ${now.getFullYear()}`;
            filterSubtitle = 'Data pengiriman bulan ini';
            break;
        case 'tahun_ini':
            filterTitle = `Pengiriman Tahun ${now.getFullYear()}`;
            filterSubtitle = 'Data pengiriman tahun ini';
            break;
        case 'range':
            filterTitle = 'Pengiriman Custom Range';
            filterSubtitle = `Periode: ${formatDate(startDate)} s/d ${formatDate(endDate)}`;
            break;
    }
    
    document.getElementById('pieChartModalTitle').textContent = filterTitle;
    document.getElementById('pieChartModalSubtitle').textContent = filterSubtitle;
    document.getElementById('pieChartModalContent').innerHTML = '<div class="text-center py-12"><i class="fas fa-spinner fa-spin text-4xl text-purple-600"></i><p class="mt-4 text-gray-600 font-medium">Memuat data...</p></div>';
    
    // Show modal
    document.getElementById('pieChartDetailModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Fetch data
    let url = `{{ route('laporan.pengiriman.pieChartDetails') }}?filter=${filter}`;
    if (filter === 'range' && startDate && endDate) {
        url += `&start_date=${startDate}&end_date=${endDate}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                displayPieChartDetails(result.data);
            } else {
                throw new Error(result.error || 'Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('pieChartModalContent').innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-600"></i>
                    <p class="mt-4 text-gray-900 font-medium">Gagal memuat data</p>
                    <p class="text-sm text-gray-600 mt-2">${error.message}</p>
                </div>
            `;
        });
}

// Display pie chart details in modal
function displayPieChartDetails(data) {
    if (!data || data.length === 0) {
        document.getElementById('pieChartModalContent').innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-inbox text-4xl text-gray-400"></i>
                <p class="mt-4 text-gray-900 font-medium">Tidak ada data</p>
                <p class="text-sm text-gray-600 mt-2">Tidak ada data pengiriman untuk periode ini</p>
            </div>
        `;
        return;
    }
    
    // Count categories
    let normalCount = 0, bongkarCount = 0, gagalCount = 0;
    data.forEach(item => {
        if (item.kategori === 'normal') normalCount++;
        else if (item.kategori === 'bongkar') bongkarCount++;
        else if (item.kategori === 'gagal') gagalCount++;
    });
    
    // Update summary cards
    document.getElementById('modalNormalCount').textContent = normalCount;
    document.getElementById('modalBongkarCount').textContent = bongkarCount;
    document.getElementById('modalGagalCount').textContent = gagalCount;
    document.getElementById('modalTotalCount').textContent = data.length;
    
    // Build table
    let html = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No PO</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Supplier</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty Forecast (Kg)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty Pengiriman (Kg)</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    data.forEach((item, index) => {
        // Determine badge class and icon
        let badgeClass = '';
        let badgeIcon = '';
        let badgeText = item.status_label;
        
        if (item.kategori === 'normal') {
            badgeClass = 'bg-green-100 text-green-800 border-green-200';
            badgeIcon = '<i class="fas fa-check-circle mr-1.5"></i>';
        } else if (item.kategori === 'bongkar') {
            badgeClass = 'bg-orange-100 text-orange-800 border-orange-200';
            badgeIcon = '<i class="fas fa-exclamation-triangle mr-1.5"></i>';
        } else {
            badgeClass = 'bg-red-100 text-red-800 border-red-200';
            badgeIcon = '<i class="fas fa-times-circle mr-1.5"></i>';
        }
        
        // Format date
        const tanggal = item.tanggal_kirim ? new Date(item.tanggal_kirim).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }) : '-';
        
        html += `
            <tr class="hover:bg-blue-50 cursor-pointer transition-colors group" 
                onclick="navigateToPengirimanDetail(${item.id}, '${item.status_pengiriman}')">
                <td class="px-4 py-3 text-sm text-gray-900 font-medium">${index + 1}</td>
                <td class="px-4 py-3 text-sm text-indigo-600 font-semibold group-hover:text-indigo-800">
                    ${item.po_number || '-'}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">${tanggal}</td>
                <td class="px-4 py-3 text-sm text-gray-900">${item.supplier || '-'}</td>
                <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                    ${Number(item.qty_forecast || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </td>
                <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                    ${Number(item.qty_pengiriman || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </td>
                <td class="px-4 py-3 text-sm text-center">
                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full border ${badgeClass}">
                        ${badgeIcon}${badgeText}
                    </span>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between text-sm">
                <p class="text-gray-600">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <strong>Total:</strong> ${data.length} pengiriman
                </p>
               
            </div>
        </div>
    `;
    
    document.getElementById('pieChartModalContent').innerHTML = html;
}

// Close pie chart modal
function closePieChartModal() {
    document.getElementById('pieChartDetailModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Export pie chart details to PDF
function exportPieChartPDF() {
    const filter = document.getElementById('pieFilterSelect').value;
    const startDate = document.querySelector('input[name="pie_start_date"]')?.value || '';
    const endDate = document.querySelector('input[name="pie_end_date"]')?.value || '';
    
    // Build URL
    let url = `{{ route('laporan.pengiriman.pieChartPDF') }}?filter=${filter}`;
    if (filter === 'range' && startDate && endDate) {
        url += `&start_date=${startDate}&end_date=${endDate}`;
    }
    
    // Show loading notification
    showNotification('Sedang membuat PDF...', 'info');
    
    // Open in new window to download
    window.open(url, '_blank');
}

// Format date helper
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    initCombinedChart();
    initStatusPieChart();
    
    document.getElementById('chartTypeSelector').addEventListener('change', function() {
        updateChartDataType(this.value);
    });
});



</script>
@endsection