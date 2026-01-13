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
                <p class="text-sm text-gray-600 mb-2 flex items-center">
                    Total Omset Sampai Saat Ini
                    <span class="ml-2 px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded" 
                          title="Termasuk Omset Sistem dan Omset Manual">
                        <i class="fas fa-layer-group"></i>
                    </span>
                </p>
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
                <p class="text-sm text-gray-600 mb-2 flex items-center">
                    Omset Tahun Ini ({{ date('Y') }})
                    <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded" 
                          title="Termasuk Omset Sistem dan Omset Manual">
                        <i class="fas fa-layer-group"></i>
                    </span>
                </p>
                <h3 class="text-2xl font-bold text-blue-600">
                    @if($omsetTahunIniSummary >= 1000000000)
                        Rp {{ number_format($omsetTahunIniSummary / 1000000000, 2, ',', '.') }} Miliar
                    @else
                        Rp {{ number_format($omsetTahunIniSummary / 1000000, 2, ',', '.') }} Juta
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
                <p class="text-sm text-gray-600 mb-2 flex items-center">
                    Omset Bulan Ini ({{ date('F Y') }})
                    <span class="ml-2 px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded" 
                          title="Termasuk Omset Sistem dan Omset Manual">
                        <i class="fas fa-layer-group"></i>
                    </span>
                </p>
                <h3 class="text-2xl font-bold text-green-600">
                    Rp {{ number_format($omsetBulanIniSummary / 1000000, 2, ',', '.') }} Juta
                </h3>
            </div>
        </div>
    </div>
</div>

{{-- Include Target Analysis Section --}}
@include('pages.laporan.partials.target_analysis')

{{-- Pie Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- Card 1: Omset Marketing --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Omset Marketing</h3>
                <p class="text-sm text-gray-500">Berdasarkan PIC Marketing</p>
            </div>
            <div class="flex items-center gap-2">
                @if($omsetMarketing->count() > 0)
                    <button onclick="openMarketingModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fas fa-list"></i>
                        <span class="hidden sm:inline">Detail</span>
                    </button>
                @endif
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
                <p class="text-sm text-gray-500">Berdasarkan PIC Procurement</p>
            </div>
            <div class="flex items-center gap-2">
                @if($omsetProcurement->count() > 0)
                    <button onclick="openProcurementModal()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fas fa-list"></i>
                        <span class="hidden sm:inline">Detail</span>
                    </button>
                @endif
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

{{-- Include Klien Chart Section --}}
@include('pages.laporan.partials.klien_chart')

{{-- Include Supplier Chart Section --}}
@include('pages.laporan.partials.supplier_chart')

{{-- Include Bahan Baku Chart Section --}}
@include('pages.laporan.partials.bahan_baku_chart')

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
// Chart instances
let chartOmsetMarketing = null;
let chartOmsetProcurement = null;
let chartOmsetPerKlien = null;

// Current year for omset per klien
let currentYearKlien = {{ date('Y') }};
const availableYearsKlien = @json(range(2020, date('Y')));

// Search filter state
let klienSearchTimeout = null;
let currentKlienSearch = '';

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

// Handle keyup event for klien search (debounced)
function handleKlienSearchKeyup(event) {
    const searchValue = event.target.value.trim();
    
    // Show/hide clear button
    const clearBtn = document.getElementById('clearSearchKlien');
    if (searchValue) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    
    // Debounce the search
    clearTimeout(klienSearchTimeout);
    klienSearchTimeout = setTimeout(() => {
        currentKlienSearch = searchValue;
        loadOmsetPerKlienChart(currentYearKlien, searchValue);
    }, 500); // Wait 500ms after user stops typing
}

// Clear klien search
function clearKlienSearch() {
    document.getElementById('searchKlien').value = '';
    document.getElementById('clearSearchKlien').classList.add('hidden');
    currentKlienSearch = '';
    loadOmsetPerKlienChart(currentYearKlien, '');
}

// Change year for omset per klien chart
function changeYearKlienChart(direction) {
    const currentIndex = availableYearsKlien.indexOf(currentYearKlien);
    let newIndex = currentIndex + direction;
    
    // Boundary check
    if (newIndex < 0 || newIndex >= availableYearsKlien.length) {
        return;
    }
    
    currentYearKlien = availableYearsKlien[newIndex];
    document.getElementById('currentYearKlien').textContent = currentYearKlien;
    
    loadOmsetPerKlienChart(currentYearKlien, currentKlienSearch);
}

// Load Omset per Klien Chart via AJAX
function loadOmsetPerKlienChart(tahun, search = '') {
    let url = `{{ route('laporan.omset') }}?ajax=omset_per_klien&tahun=${tahun}`;
    if (search) {
        url += `&search=${encodeURIComponent(search)}`;
    }
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        updateOmsetPerKlienChart(result);
    })
    .catch(error => console.error('Error:', error));
}

// Update Omset per Klien Chart
function updateOmsetPerKlienChart(data) {
    if (chartOmsetPerKlien) {
        chartOmsetPerKlien.data.labels = data.klien_names;
        chartOmsetPerKlien.data.datasets = data.datasets;
        chartOmsetPerKlien.update();
    } else {
        const ctx = document.getElementById('chartOmsetPerKlien').getContext('2d');
        chartOmsetPerKlien = createGroupedBarChart(ctx, data.klien_names, data.datasets);
    }
}

// Create Line Chart for Omset per Klien
function createGroupedBarChart(ctx, labels, datasets) {
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: {
                            size: 11
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            return label;
                        }
                    }
                },
                datalabels: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: {
                            size: 10
                        },
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(2) + 'Jt';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            },
            elements: {
                line: {
                    tension: 0.4,
                    borderWidth: 2
                },
                point: {
                    radius: 4,
                    hitRadius: 10,
                    hoverRadius: 6,
                    hoverBorderWidth: 2
                }
            }
        },
        plugins: [ChartDataLabels]
    });
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
                            const percentage = ((value / total) * 100).toFixed(2);
                            return label + ': Rp ' + value.toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' (' + percentage + '%)';
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

// Initialize Omset per Klien Chart
document.addEventListener('DOMContentLoaded', function() {
    loadOmsetPerKlienChart(currentYearKlien);
});

// Marketing Modal Functions
function openMarketingModal() {
    const periode = document.querySelector('[name="periode_marketing"]').value;
    const startDate = document.querySelector('[name="start_date_marketing"]')?.value || '';
    const endDate = document.querySelector('[name="end_date_marketing"]')?.value || '';
    
    fetch(`{{ route('laporan.omset.marketingDetails') }}?periode=${periode}&start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            displayMarketingDetails(data);
            document.getElementById('marketingModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => console.error('Error:', error));
}

function closeMarketingModal() {
    document.getElementById('marketingModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function displayMarketingDetails(data) {
    const groupedData = {};
    let totalOverall = 0;
    
    data.forEach(item => {
        if (!groupedData[item.marketing_nama]) {
            groupedData[item.marketing_nama] = [];
        }
        groupedData[item.marketing_nama].push(item);
        totalOverall += parseFloat(item.total_nilai);
    });
    
    let html = '';
    Object.keys(groupedData).forEach(marketing => {
        const items = groupedData[marketing];
        const subtotal = items.reduce((sum, item) => sum + parseFloat(item.total_nilai), 0);
        
        html += `
            <div class="mb-6">
                <div class="bg-blue-600 text-white px-4 py-2 rounded-t-lg flex justify-between items-center">
                    <strong>Marketing: ${marketing}</strong>
                    <span>Total: Rp ${subtotal.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No PO</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pabrik</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${items.map((item, idx) => `
                            <tr>
                                <td class="px-4 py-2 text-sm">${idx + 1}</td>
                                <td class="px-4 py-2 text-sm">${item.po_number || '-'}</td>
                                <td class="px-4 py-2 text-sm">${item.klien_nama}</td>
                                <td class="px-4 py-2 text-sm text-right">Rp ${parseFloat(item.total_nilai).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                        `).join('')}
                        <tr class="bg-blue-50 font-bold">
                            <td colspan="4" class="px-4 py-2 text-sm text-right">Subtotal:</td>
                            <td class="px-4 py-2 text-sm text-right">Rp ${subtotal.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    });
    
    html += `
        <div class="bg-indigo-100 p-4 rounded-lg mt-4">
            <div class="flex justify-between items-center">
                <strong class="text-lg">TOTAL KESELURUHAN:</strong>
                <strong class="text-lg text-indigo-700">Rp ${totalOverall.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
            </div>
        </div>
    `;
    
    document.getElementById('marketingDetailsContent').innerHTML = html;
}

// Procurement Modal Functions
function openProcurementModal() {
    const periode = document.querySelector('[name="periode_procurement"]').value;
    const startDate = document.querySelector('[name="start_date_procurement"]')?.value || '';
    const endDate = document.querySelector('[name="end_date_procurement"]')?.value || '';
    
    fetch(`{{ route('laporan.omset.procurementDetails') }}?periode=${periode}&start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            displayProcurementDetails(data);
            document.getElementById('procurementModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => console.error('Error:', error));
}

function closeProcurementModal() {
    document.getElementById('procurementModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function displayProcurementDetails(data) {
    const groupedData = {};
    let totalOverall = 0;
    
    data.forEach(item => {
        if (!groupedData[item.purchasing_nama]) {
            groupedData[item.purchasing_nama] = [];
        }
        groupedData[item.purchasing_nama].push(item);
        totalOverall += parseFloat(item.total_nilai);
    });
    
    let html = '';
    Object.keys(groupedData).forEach(procurement => {
        const items = groupedData[procurement];
        const subtotal = items.reduce((sum, item) => sum + parseFloat(item.total_nilai), 0);
        
        html += `
            <div class="mb-6">
                <div class="bg-green-600 text-white px-4 py-2 rounded-t-lg flex justify-between items-center">
                    <strong>Procurement: ${procurement}</strong>
                    <span>Total: Rp ${subtotal.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No PO</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pabrik</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${items.map((item, idx) => `
                            <tr>
                                <td class="px-4 py-2 text-sm">${idx + 1}</td>
                                <td class="px-4 py-2 text-sm">${item.po_number || '-'}</td>
                                <td class="px-4 py-2 text-sm">${item.klien_nama}</td>
                                <td class="px-4 py-2 text-sm text-right">Rp ${parseFloat(item.total_nilai).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                        `).join('')}
                        <tr class="bg-green-50 font-bold">
                            <td colspan="4" class="px-4 py-2 text-sm text-right">Subtotal:</td>
                            <td class="px-4 py-2 text-sm text-right">Rp ${subtotal.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    });
    
    html += `
        <div class="bg-green-100 p-4 rounded-lg mt-4">
            <div class="flex justify-between items-center">
                <strong class="text-lg">TOTAL KESELURUHAN:</strong>
                <strong class="text-lg text-green-700">Rp ${totalOverall.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
            </div>
        </div>
    `;
    
    document.getElementById('procurementDetailsContent').innerHTML = html;
}

// Export PDF Functions
function exportMarketingPDF() {
    const periode = document.querySelector('[name="periode_marketing"]').value;
    const startDate = document.querySelector('[name="start_date_marketing"]')?.value || '';
    const endDate = document.querySelector('[name="end_date_marketing"]')?.value || '';
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("laporan.omset.marketingPDF") }}';
    form.target = '_blank';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.innerHTML = `
        <input type="hidden" name="_token" value="${csrfToken}">
        <input type="hidden" name="periode" value="${periode}">
        <input type="hidden" name="start_date" value="${startDate}">
        <input type="hidden" name="end_date" value="${endDate}">
    `;
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportProcurementPDF() {
    const periode = document.querySelector('[name="periode_procurement"]').value;
    const startDate = document.querySelector('[name="start_date_procurement"]')?.value || '';
    const endDate = document.querySelector('[name="end_date_procurement"]')?.value || '';
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("laporan.omset.procurementPDF") }}';
    form.target = '_blank';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.innerHTML = `
        <input type="hidden" name="_token" value="${csrfToken}">
        <input type="hidden" name="periode" value="${periode}">
        <input type="hidden" name="start_date" value="${startDate}">
        <input type="hidden" name="end_date" value="${endDate}">
    `;
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>

{{-- Marketing Details Modal --}}
<div id="marketingModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Detail Omset Marketing</h3>
                <p class="text-sm text-gray-500 mt-1">Rincian PO per Marketing</p>
            </div>
            <button onclick="closeMarketingModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <div class="mb-4 flex justify-end">
            <button onclick="exportMarketingPDF()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-file-pdf"></i>
                Download PDF
            </button>
        </div>

        <div id="marketingDetailsContent" class="overflow-x-auto max-h-96 overflow-y-auto">
            <!-- Content will be loaded dynamically -->
        </div>

        <div class="mt-6 flex justify-end">
            <button onclick="closeMarketingModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- Procurement Details Modal --}}
<div id="procurementModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Detail Omset Procurement</h3>
                <p class="text-sm text-gray-500 mt-1">Rincian PO per Procurement</p>
            </div>
            <button onclick="closeProcurementModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <div class="mb-4 flex justify-end">
            <button onclick="exportProcurementPDF()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-file-pdf"></i>
                Download PDF
            </button>
        </div>

        <div id="procurementDetailsContent" class="overflow-x-auto max-h-96 overflow-y-auto">
            <!-- Content will be loaded dynamically -->
        </div>

        <div class="mt-6 flex justify-end">
            <button onclick="closeProcurementModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

@endsection
