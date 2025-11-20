@extends('pages.laporan.base')

@section('report-content')

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    {{-- Total Omset Sampai Saat Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Total Omset Sampai Saat Ini</p>
                <h3 class="text-2xl font-bold text-purple-600">
                    @if($totalOmset >= 1000000000)
                        Rp {{ number_format($totalOmset / 1000000000, 2, ',', '.') }} Miliar
                    @else
                        Rp {{ number_format($totalOmset / 1000000, 2, ',', '.') }} Juta
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Omset Tahun Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-gift text-blue-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Omset Tahun Ini</p>
                <h3 class="text-2xl font-bold text-blue-600">
                    @if($omsetTahunIni >= 1000000000)
                        Rp {{ number_format($omsetTahunIni / 1000000000, 2, ',', '.') }} Miliar
                    @else
                        Rp {{ number_format($omsetTahunIni / 1000000, 2, ',', '.') }} Juta
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Omset Bulan Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-alt text-green-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Omset Bulan Ini</p>
                <h3 class="text-2xl font-bold text-green-600">
                    Rp {{ number_format($omsetBulanIni / 1000000, 2, ',', '.') }} Juta
                </h3>
            </div>
        </div>
    </div>
</div>

{{-- Pie Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- Card 1: Omset Marketing --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Omset Marketing</h3>
                <p class="text-sm text-gray-500">Berdasarkan PIC Marketing</p>
            </div>
            <div class="w-48">
                <select name="periode_marketing" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="toggleCustomDateMarketing(this.value)">
                    <option value="all" {{ $periode == 'all' ? 'selected' : '' }}>Semua Data</option>
                    <option value="tahun_ini" {{ $periode == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periode == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ $periode == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
                
                {{-- Custom Date Range for Marketing --}}
                <div id="customDateMarketing" class="mt-2 space-y-2" style="display: {{ $periode == 'custom' ? 'block' : 'none' }}">
                    <input type="date" 
                           name="start_date_marketing" 
                           value="{{ request('start_date_marketing') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Tanggal Mulai">
                    <input type="date" 
                           name="end_date_marketing" 
                           value="{{ request('end_date_marketing') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Tanggal Akhir">
                    <button type="button" 
                            onclick="submitMarketingCustom()"
                            class="w-full px-3 py-2 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        
        <div class="flex justify-center items-center" style="height: 400px;">
            @if($omsetMarketing->count() > 0)
                <canvas id="chartOmsetMarketing"></canvas>
            @else
                <div class="text-center text-gray-400">
                    <i class="fas fa-chart-pie text-4xl mb-2"></i>
                    <p>Tidak ada data omset marketing</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Card 2: Omset Procurement --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Omset Procurement</h3>
                <p class="text-sm text-gray-500">Berdasarkan PIC Purchasing</p>
            </div>
            <div class="w-48">
                <input type="hidden" name="periode_marketing" value="{{ $periode }}">
                @if($periode == 'custom')
                    <input type="hidden" name="start_date_marketing" value="{{ request('start_date_marketing') }}">
                    <input type="hidden" name="end_date_marketing" value="{{ request('end_date_marketing') }}">
                @endif
                
                <select name="periode_procurement" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        onchange="toggleCustomDateProcurement(this.value)">
                    <option value="all" {{ $periodeProcurement == 'all' ? 'selected' : '' }}>Semua Data</option>
                    <option value="tahun_ini" {{ $periodeProcurement == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periodeProcurement == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ $periodeProcurement == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
                
                {{-- Custom Date Range for Procurement --}}
                <div id="customDateProcurement" class="mt-2 space-y-2" style="display: {{ $periodeProcurement == 'custom' ? 'block' : 'none' }}">
                    <input type="date" 
                           name="start_date_procurement" 
                           value="{{ request('start_date_procurement') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="Tanggal Mulai">
                    <input type="date" 
                           name="end_date_procurement" 
                           value="{{ request('end_date_procurement') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="Tanggal Akhir">
                    <button type="button"
                            onclick="submitProcurementCustom()"
                            class="w-full px-3 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        
        <div class="flex justify-center items-center" style="height: 400px;">
            @if($omsetProcurement->count() > 0)
                <canvas id="chartOmsetProcurement"></canvas>
            @else
                <div class="text-center text-gray-400">
                    <i class="fas fa-chart-pie text-4xl mb-2"></i>
                    <p>Tidak ada data omset procurement</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Proyek Per Bulan Chart --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- Card 1: Proyek Per Bulan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                    Proyek Per Bulan
                </h3>
                <p class="text-sm text-gray-500">Distribusi bulanan per tahun</p>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" 
                        onclick="changeTahunProyek(-1)"
                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="px-4 py-2 bg-green-50 border border-green-200 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Tahun: </span>
                    <span id="currentYearProyek" class="text-lg font-bold text-green-600">{{ $selectedYear }}</span>
                </div>
                <button type="button" 
                        onclick="changeTahunProyek(1)"
                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        
        <div style="height: 350px;">
            <canvas id="chartProyekPerBulan"></canvas>
        </div>
    </div>

    {{-- Card 2: Nilai Order Per Bulan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                    Nilai Order Per Bulan
                </h3>
                <p class="text-sm text-gray-500">Total nilai order bulanan per tahun</p>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" 
                        onclick="changeTahunNilai(-1)"
                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Tahun: </span>
                    <span id="currentYearNilai" class="text-lg font-bold text-blue-600">{{ $selectedYearNilai }}</span>
                </div>
                <button type="button" 
                        onclick="changeTahunNilai(1)"
                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        
        <div style="height: 350px;">
            <canvas id="chartNilaiOrderPerBulan"></canvas>
        </div>
    </div>
</div>

{{-- Top Klien & Top Supplier Section --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- Card 1: Top Klien --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                    Top 10 Klien
                </h3>
            </div>
            <div class="w-48">
                <select name="periode_klien" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        onchange="toggleCustomDateKlien(this.value)">
                    <option value="all" {{ $periodeKlien == 'all' ? 'selected' : '' }}>Semua Data</option>
                    <option value="tahun_ini" {{ $periodeKlien == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periodeKlien == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ $periodeKlien == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
                
                {{-- Custom Date Range for Klien --}}
                <div id="customDateKlien" class="mt-2 space-y-2" style="display: {{ $periodeKlien == 'custom' ? 'block' : 'none' }}">
                    <input type="date" 
                           name="start_date_klien" 
                           value="{{ request('start_date_klien') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500"
                           placeholder="Tanggal Mulai">
                    <input type="date" 
                           name="end_date_klien" 
                           value="{{ request('end_date_klien') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500"
                           placeholder="Tanggal Akhir">
                    <button type="button" 
                            onclick="submitKlienCustom()"
                            class="w-full px-3 py-2 text-sm bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        
        <div id="topKlienContainer" class="overflow-y-auto" style="max-height: 400px;">
            @if($topKlien->count() > 0)
                <div class="space-y-3">
                    @foreach($topKlien as $index => $item)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3 flex-1">
                                <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-yellow-600">#{{ $index + 1 }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $item->klien ? $item->klien->nama : 'Unknown' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->klien && $item->klien->cabang ? $item->klien->cabang : '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-4">
                                <p class="text-sm font-bold text-yellow-600">
                                    Rp {{ number_format($item->total / 1000000, 2, ',', '.') }} Jt
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-400 py-8">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p>Tidak ada data klien</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Card 2: Top Supplier --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-award text-orange-500 mr-2"></i>
                    Top 10 Supplier
                </h3>
            </div>
            <div class="w-48">
                <select name="periode_supplier" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        onchange="toggleCustomDateSupplier(this.value)">
                    <option value="all" {{ $periodeSupplier == 'all' ? 'selected' : '' }}>Semua Data</option>
                    <option value="tahun_ini" {{ $periodeSupplier == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periodeSupplier == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ $periodeSupplier == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
                
                {{-- Custom Date Range for Supplier --}}
                <div id="customDateSupplier" class="mt-2 space-y-2" style="display: {{ $periodeSupplier == 'custom' ? 'block' : 'none' }}">
                    <input type="date" 
                           name="start_date_supplier" 
                           value="{{ request('start_date_supplier') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                           placeholder="Tanggal Mulai">
                    <input type="date" 
                           name="end_date_supplier" 
                           value="{{ request('end_date_supplier') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                           placeholder="Tanggal Akhir">
                    <button type="button"
                            onclick="submitSupplierCustom()"
                            class="w-full px-3 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        
        <div id="topSupplierContainer" class="overflow-y-auto" style="max-height: 400px;">
            @if($topSupplier->count() > 0)
                <div class="space-y-3">
                    @foreach($topSupplier as $index => $item)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3 flex-1">
                                <div class="flex-shrink-0 w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-orange-600">#{{ $index + 1 }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $item->nama ?? 'Unknown' }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $item->alamat ?? '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-4">
                                <p class="text-sm font-bold text-orange-600">
                                    Rp {{ number_format($item->total / 1000000, 2, ',', '.') }} Jt
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-400 py-8">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p>Tidak ada data supplier</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
// Chart instances
let chartOmsetMarketing = null;
let chartOmsetProcurement = null;
let chartProyekPerBulan = null;
let chartNilaiOrderPerBulan = null;

// Current year for proyek per bulan
let currentYearProyek = {{ $selectedYear }};
let currentYearNilai = {{ $selectedYearNilai }};
const availableYears = @json($availableYears);

// Chart colors
const chartColors = [
    '#3B82F6', // blue
    '#10B981', // green
    '#F59E0B', // amber
    '#EF4444', // red
    '#8B5CF6', // violet
    '#EC4899', // pink
    '#06B6D4', // cyan
    '#F97316', // orange
];

// Toggle custom date for marketing
function toggleCustomDateMarketing(value) {
    const customDiv = document.getElementById('customDateMarketing');
    if (value === 'custom') {
        customDiv.style.display = 'block';
    } else {
        customDiv.style.display = 'none';
        // Load data via AJAX without refresh
        loadMarketingChart(value, null, null);
    }
}

// Toggle custom date for procurement
function toggleCustomDateProcurement(value) {
    const customDiv = document.getElementById('customDateProcurement');
    if (value === 'custom') {
        customDiv.style.display = 'block';
    } else {
        customDiv.style.display = 'none';
        // Load data via AJAX without refresh
        loadProcurementChart(value, null, null);
    }
}

// Toggle custom date for klien
function toggleCustomDateKlien(value) {
    const customDiv = document.getElementById('customDateKlien');
    if (value === 'custom') {
        customDiv.style.display = 'block';
    } else {
        customDiv.style.display = 'none';
        // Load data via AJAX without refresh
        loadTopKlien(value, null, null);
    }
}

// Toggle custom date for supplier
function toggleCustomDateSupplier(value) {
    const customDiv = document.getElementById('customDateSupplier');
    if (value === 'custom') {
        customDiv.style.display = 'block';
    } else {
        customDiv.style.display = 'none';
        // Load data via AJAX without refresh
        loadTopSupplier(value, null, null);
    }
}

// Submit custom filter for marketing
function submitMarketingCustom() {
    const periode = document.querySelector('[name="periode_marketing"]').value;
    const startDate = document.querySelector('[name="start_date_marketing"]').value;
    const endDate = document.querySelector('[name="end_date_marketing"]').value;
    
    if (!startDate || !endDate) {
        alert('Mohon isi tanggal mulai dan tanggal akhir');
        return;
    }
    
    loadMarketingChart(periode, startDate, endDate);
}

// Submit custom filter for procurement
function submitProcurementCustom() {
    const periode = document.querySelector('[name="periode_procurement"]').value;
    const startDate = document.querySelector('[name="start_date_procurement"]').value;
    const endDate = document.querySelector('[name="end_date_procurement"]').value;
    
    if (!startDate || !endDate) {
        alert('Mohon isi tanggal mulai dan tanggal akhir');
        return;
    }
    
    loadProcurementChart(periode, startDate, endDate);
}

// Submit custom filter for klien
function submitKlienCustom() {
    const periode = document.querySelector('[name="periode_klien"]').value;
    const startDate = document.querySelector('[name="start_date_klien"]').value;
    const endDate = document.querySelector('[name="end_date_klien"]').value;
    
    if (!startDate || !endDate) {
        alert('Mohon isi tanggal mulai dan tanggal akhir');
        return;
    }
    
    loadTopKlien(periode, startDate, endDate);
}

// Submit custom filter for supplier
function submitSupplierCustom() {
    const periode = document.querySelector('[name="periode_supplier"]').value;
    const startDate = document.querySelector('[name="start_date_supplier"]').value;
    const endDate = document.querySelector('[name="end_date_supplier"]').value;
    
    if (!startDate || !endDate) {
        alert('Mohon isi tanggal mulai dan tanggal akhir');
        return;
    }
    
    loadTopSupplier(periode, startDate, endDate);
}

// Load Marketing Chart via AJAX
function loadMarketingChart(periode, startDate, endDate) {
    const params = new URLSearchParams({
        periode_marketing: periode,
        ajax: 'marketing'
    });
    
    if (startDate) params.append('start_date_marketing', startDate);
    if (endDate) params.append('end_date_marketing', endDate);
    
    fetch(`{{ route('laporan.omset') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateMarketingChart(data);
    })
    .catch(error => console.error('Error:', error));
}

// Load Procurement Chart via AJAX
function loadProcurementChart(periode, startDate, endDate) {
    const params = new URLSearchParams({
        periode_procurement: periode,
        ajax: 'procurement'
    });
    
    if (startDate) params.append('start_date_procurement', startDate);
    if (endDate) params.append('end_date_procurement', endDate);
    
    fetch(`{{ route('laporan.omset') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Procurement data received:', data);
        updateProcurementChart(data);
    })
    .catch(error => console.error('Error:', error));
}

// Load Top Klien via AJAX
function loadTopKlien(periode, startDate, endDate) {
    const params = new URLSearchParams({
        periode_klien: periode,
        ajax: 'top_klien'
    });
    
    if (startDate) params.append('start_date_klien', startDate);
    if (endDate) params.append('end_date_klien', endDate);
    
    fetch(`{{ route('laporan.omset') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateTopKlien(data);
    })
    .catch(error => console.error('Error:', error));
}

// Load Top Supplier via AJAX
function loadTopSupplier(periode, startDate, endDate) {
    const params = new URLSearchParams({
        periode_supplier: periode,
        ajax: 'top_supplier'
    });
    
    if (startDate) params.append('start_date_supplier', startDate);
    if (endDate) params.append('end_date_supplier', endDate);
    
    fetch(`{{ route('laporan.omset') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateTopSupplier(data);
    })
    .catch(error => console.error('Error:', error));
}

// Update Marketing Chart
function updateMarketingChart(data) {
    const canvas = document.getElementById('chartOmsetMarketing');
    const container = canvas.parentElement;
    
    if (data.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-400">
                <i class="fas fa-chart-pie text-4xl mb-2"></i>
                <p>Tidak ada data omset marketing</p>
            </div>
        `;
        return;
    }
    
    if (!canvas) {
        container.innerHTML = '<canvas id="chartOmsetMarketing"></canvas>';
    }
    
    const labels = data.map(item => item.nama);
    const values = data.map(item => item.total);
    
    if (chartOmsetMarketing) {
        chartOmsetMarketing.destroy();
    }
    
    const ctx = document.getElementById('chartOmsetMarketing').getContext('2d');
    chartOmsetMarketing = createPieChart(ctx, labels, values);
}

// Update Procurement Chart
function updateProcurementChart(data) {
    console.log('Updating procurement chart with data:', data);
    
    const canvas = document.getElementById('chartOmsetProcurement');
    const container = canvas.parentElement;
    
    if (data.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-400">
                <i class="fas fa-chart-pie text-4xl mb-2"></i>
                <p>Tidak ada data omset procurement</p>
            </div>
        `;
        return;
    }
    
    if (!canvas) {
        container.innerHTML = '<canvas id="chartOmsetProcurement"></canvas>';
    }
    
    const labels = data.map(item => item.nama);
    const values = data.map(item => parseFloat(item.total) || 0);
    
    console.log('Labels:', labels);
    console.log('Values:', values);
    
    if (chartOmsetProcurement) {
        chartOmsetProcurement.destroy();
    }
    
    const ctx = document.getElementById('chartOmsetProcurement').getContext('2d');
    chartOmsetProcurement = createPieChart(ctx, labels, values);
}

// Update Top Klien
function updateTopKlien(data) {
    const container = document.getElementById('topKlienContainer');
    
    if (data.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-400 py-8">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Tidak ada data klien</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="space-y-3">';
    data.forEach((item, index) => {
        const nominal = item.total / 1000000;
        html += `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center space-x-3 flex-1">
                    <div class="shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-bold text-yellow-600">#${index + 1}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            ${item.nama}
                        </p>
                        <p class="text-xs text-gray-500">
                            ${item.cabang || '-'}
                        </p>
                    </div>
                </div>
                <div class="text-right shrink-0 ml-4">
                    <p class="text-sm font-bold text-yellow-600">
                        Rp ${nominal.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} Jt
                    </p>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}

// Update Top Supplier
function updateTopSupplier(data) {
    const container = document.getElementById('topSupplierContainer');
    
    if (data.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-400 py-8">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Tidak ada data supplier</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="space-y-3">';
    data.forEach((item, index) => {
        const nominal = item.total / 1000000;
        html += `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center space-x-3 flex-1">
                    <div class="shrink-0 w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-bold text-orange-600">#${index + 1}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            ${item.nama}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            ${item.cabang || '-'}
                        </p>
                    </div>
                </div>
                <div class="text-right shrink-0 ml-4">
                    <p class="text-sm font-bold text-orange-600">
                        Rp ${nominal.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} Jt
                    </p>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}

// Create Pie Chart with percentage labels
function createPieChart(ctx, labels, values) {
    return new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: chartColors,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 25,
                        font: {
                            size: 14
                        },
                        boxWidth: 22,
                        boxHeight: 22
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': Rp ' + value.toLocaleString('id-ID') + ' (' + percentage + '%)';
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 15
                    },
                    formatter: (value, context) => {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(0);
                        return percentage + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// Change year for proyek per bulan
function changeTahunProyek(direction) {
    const currentIndex = availableYears.indexOf(currentYearProyek);
    let newIndex = currentIndex - direction; // -1 for next year (higher), +1 for prev year (lower)
    
    // Boundary check
    if (newIndex < 0 || newIndex >= availableYears.length) {
        return;
    }
    
    currentYearProyek = availableYears[newIndex];
    document.getElementById('currentYearProyek').textContent = currentYearProyek;
    
    loadProyekPerBulanChart(currentYearProyek);
}

// Change year for nilai order per bulan
function changeTahunNilai(direction) {
    const currentIndex = availableYears.indexOf(currentYearNilai);
    let newIndex = currentIndex - direction;
    
    // Boundary check
    if (newIndex < 0 || newIndex >= availableYears.length) {
        return;
    }
    
    currentYearNilai = availableYears[newIndex];
    document.getElementById('currentYearNilai').textContent = currentYearNilai;
    
    loadNilaiOrderPerBulanChart(currentYearNilai);
}

// Load Proyek Per Bulan Chart via AJAX
function loadProyekPerBulanChart(tahun) {
    fetch(`{{ route('laporan.omset') }}?ajax=proyek_per_bulan&tahun=${tahun}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        updateProyekPerBulanChart(result.data);
    })
    .catch(error => console.error('Error:', error));
}

// Load Nilai Order Per Bulan Chart via AJAX
function loadNilaiOrderPerBulanChart(tahun) {
    fetch(`{{ route('laporan.omset') }}?ajax=nilai_order_per_bulan&tahun=${tahun}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        updateNilaiOrderPerBulanChart(result.data);
    })
    .catch(error => console.error('Error:', error));
}

// Update Proyek Per Bulan Chart
function updateProyekPerBulanChart(data) {
    if (chartProyekPerBulan) {
        chartProyekPerBulan.data.datasets[0].data = data;
        chartProyekPerBulan.update();
    } else {
        const ctx = document.getElementById('chartProyekPerBulan').getContext('2d');
        chartProyekPerBulan = createBarChart(ctx, data);
    }
}

// Update Nilai Order Per Bulan Chart
function updateNilaiOrderPerBulanChart(data) {
    if (chartNilaiOrderPerBulan) {
        chartNilaiOrderPerBulan.data.datasets[0].data = data;
        chartNilaiOrderPerBulan.update();
    } else {
        const ctx = document.getElementById('chartNilaiOrderPerBulan').getContext('2d');
        chartNilaiOrderPerBulan = createLineChart(ctx, data);
    }
}

// Create Bar Chart for Proyek Per Bulan
function createBarChart(ctx, data) {
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Jumlah Proyek',
                data: data,
                backgroundColor: '#10B981',
                borderColor: '#10B981',
                borderWidth: 0,
                borderRadius: 4,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: '#1F2937',
                    padding: 10,
                    cornerRadius: 6,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Jumlah: ' + context.parsed.y + ' proyek';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5,
                        color: '#6B7280',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: '#E5E7EB',
                        drawBorder: false
                    },
                    border: {
                        display: false
                    }
                },
                x: {
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: true,
                        color: '#F3F4F6',
                        drawBorder: false
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });
}

// Create Line Chart for Nilai Order Per Bulan
function createLineChart(ctx, data) {
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Nilai Order',
                data: data,
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderColor: '#3B82F6',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: '#1F2937',
                    padding: 12,
                    cornerRadius: 6,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y;
                            if (value >= 1000000000) {
                                return 'Nilai: Rp ' + (value / 1000000000).toFixed(2) + ' Miliar';
                            } else if (value >= 1000000) {
                                return 'Nilai: Rp ' + (value / 1000000).toFixed(2) + ' Juta';
                            } else {
                                return 'Nilai: Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return (value / 1000000000).toFixed(1) + 'M';
                            } else if (value >= 1000000) {
                                return (value / 1000000).toFixed(0) + 'Jt';
                            }
                            return value;
                        }
                    },
                    grid: {
                        color: '#E5E7EB',
                        drawBorder: false
                    },
                    border: {
                        display: false
                    }
                },
                x: {
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: true,
                        color: '#F3F4F6',
                        drawBorder: false
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });
}

// Initialize charts on page load
@if($omsetMarketing->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const ctxMarketing = document.getElementById('chartOmsetMarketing').getContext('2d');
    const labelsMarketing = [
        @foreach($omsetMarketing as $item)
            '{{ $item->creator ? $item->creator->nama : "Unknown" }}',
        @endforeach
    ];
    const valuesMarketing = [
        @foreach($omsetMarketing as $item)
            {{ $item->total }},
        @endforeach
    ];
    chartOmsetMarketing = createPieChart(ctxMarketing, labelsMarketing, valuesMarketing);
});
@endif

@if($omsetProcurement->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const ctxProcurement = document.getElementById('chartOmsetProcurement').getContext('2d');
    const labelsProcurement = [
        @foreach($omsetProcurement as $item)
            '{{ $item["nama"] }}',
        @endforeach
    ];
    const valuesProcurement = [
        @foreach($omsetProcurement as $item)
            {{ floatval($item["total"]) }},
        @endforeach
    ];
    console.log('Initial Procurement Labels:', labelsProcurement);
    console.log('Initial Procurement Values:', valuesProcurement);
    chartOmsetProcurement = createPieChart(ctxProcurement, labelsProcurement, valuesProcurement);
});
@endif

// Initialize Proyek Per Bulan Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctxProyek = document.getElementById('chartProyekPerBulan').getContext('2d');
    const dataProyek = @json($proyekPerBulan);
    chartProyekPerBulan = createBarChart(ctxProyek, dataProyek);
});

// Initialize Nilai Order Per Bulan Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctxNilai = document.getElementById('chartNilaiOrderPerBulan').getContext('2d');
    const dataNilai = @json($nilaiOrderPerBulan);
    chartNilaiOrderPerBulan = createLineChart(ctxNilai, dataNilai);
});
</script>

@endsection
