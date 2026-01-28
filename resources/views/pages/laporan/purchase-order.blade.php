@extends('pages.laporan.base')

@section('report-content')

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    {{-- Total Outstanding --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Total Outstanding</p>
                <h3 class="text-2xl font-bold text-red-600">
                    @if($totalOutstanding >= 1000000000)
                        Rp {{ number_format($totalOutstanding / 1000000000, 2, ',', '.') }} Miliar
                    @elseif($totalOutstanding >= 1000000)
                        Rp {{ number_format($totalOutstanding / 1000000, 2, ',', '.') }} Juta
                    @else
                        Rp {{ number_format($totalOutstanding, 2, ',', '.') }}
                    @endif
                </h3>
                <p class="text-xs text-gray-500 mt-1">PO Dikonfirmasi & Diproses</p>
            </div>
        </div>
    </div>

    {{-- Total Qty Outstanding --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-boxes text-orange-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Qty Outstanding</p>
                <h3 class="text-2xl font-bold text-orange-600">
                    {{ number_format($totalQtyOutstanding, 0, ',', '.') }}
                </h3>
                <p class="text-xs text-gray-500 mt-1">Total Quantity</p>
            </div>
        </div>
    </div>

    {{-- PO Berjalan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-file-alt text-blue-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">PO Berjalan</p>
                <h3 class="text-2xl font-bold text-blue-600">
                    {{ number_format($poBerjalan, 0, ',', '.') }}
                </h3>
                <p class="text-xs text-gray-500 mt-1">Dikonfirmasi & Diproses</p>
            </div>
        </div>
    </div>

    {{-- Rata-rata Nilai per PO --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Rata-rata Nilai per PO</p>
                <h3 class="text-2xl font-bold text-purple-600">
                    @if($avgNilaiPerPO >= 1000000000)
                        Rp {{ number_format($avgNilaiPerPO / 1000000000, 2, ',', '.') }} Miliar
                    @elseif($avgNilaiPerPO >= 1000000)
                        Rp {{ number_format($avgNilaiPerPO / 1000000, 2, ',', '.') }} Juta
                    @else
                        Rp {{ number_format($avgNilaiPerPO, 2, ',', '.') }}
                    @endif
                </h3>
                <p class="text-xs text-gray-500 mt-1">Average Value</p>
            </div>
        </div>
    </div>
</div>

{{-- 2. Nilai Outstanding & PO Berdasarkan Status --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- Outstanding Status Pie Chart --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base md:text-lg font-semibold text-gray-900">Nilai Outstanding</h3>
                <p class="text-xs md:text-sm text-gray-500">Distribusi nilai outstanding per PO</p>
            </div>
            @if($outstandingChartData->count() > 0)
                <button onclick="openOutstandingModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-list"></i>
                    <span class="hidden sm:inline">Detail</span>
                </button>
            @endif
        </div>

        <div class="flex justify-center items-center" style="height: 300px; max-height: 400px;">
            @if($outstandingChartData->count() > 0)
                <canvas id="chartOutstanding"></canvas>
            @else
                <div class="text-center text-gray-400">
                    <i class="fas fa-check-circle text-4xl mb-2"></i>
                    <p class="text-sm">Semua order detail sudah selesai!</p>
                </div>
            @endif
        </div>
    </div>

    {{-- PO By Status (Doughnut Chart) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base md:text-lg font-semibold text-gray-900">PO Berdasarkan Status</h3>
                <p class="text-xs md:text-sm text-gray-500">Distribusi status purchase order</p>
            </div>
            @if($poByStatus->count() > 0)
                <button onclick="openStatusModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-list"></i>
                    <span class="hidden sm:inline">Detail</span>
                </button>
            @endif
        </div>

        <div class="flex justify-center items-center" style="height: 300px; max-height: 400px;">
            @if($poByStatus->count() > 0)
                <canvas id="chartPOByStatus"></canvas>
            @else
                <div class="text-center text-gray-400">
                    <i class="fas fa-chart-pie text-4xl mb-2"></i>
                    <p class="text-sm">Tidak ada data status</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- 3. PO Berdasarkan Klien & PO Winner --}}
{{-- Filter Section for Client & Winner Charts --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
    <form method="GET" action="{{ route('laporan.po') }}" class="space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Periode Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filter Periode (Klien & Winner)</label>
                <select name="periode" id="periodeFilter" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="all" {{ $periode == 'all' ? 'selected' : '' }}>Semua Data</option>
                    <option value="tahun_ini" {{ $periode == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periode == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ $periode == 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            {{-- Start Date --}}
            <div id="startDateDiv" class="{{ $periode == 'custom' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            {{-- End Date --}}
            <div id="endDateDiv" class="{{ $periode == 'custom' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            {{-- Button --}}
            <div class="flex items-end">
                <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- PO By Client (Pie Chart) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base md:text-lg font-semibold text-gray-900">PO Berdasarkan Klien</h3>
                <p class="text-xs md:text-sm text-gray-500">Distribusi nilai PO per klien</p>
            </div>
            @if($poByClient->count() > 0)
                <button onclick="openClientModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-list"></i>
                    <span class="hidden sm:inline">Detail</span>
                </button>
            @endif
        </div>

        <div class="flex justify-center items-center" style="height: 300px; max-height: 400px;">
            @if($poByClient->count() > 0)
                <canvas id="chartPOByClient"></canvas>
            @else
                <div class="text-center text-gray-400">
                    <i class="fas fa-chart-pie text-4xl mb-2"></i>
                    <p class="text-sm">Tidak ada data PO</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Order Winners Pie Chart --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base md:text-lg font-semibold text-gray-900">Top 10 Order Winners</h3>
                <p class="text-xs md:text-sm text-gray-500">Distribusi nilai PO per marketing ({{ $orderWinners->count() }} data)</p>
            </div>
            @if($orderWinners->count() > 0)
                <button onclick="openOrderWinnerModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-list"></i>
                    <span class="hidden sm:inline">Detail</span>
                </button>
            @else
                <span class="text-xs text-red-500">(Tidak ada data untuk ditampilkan)</span>
            @endif
        </div>

        <div class="flex justify-center items-center" style="height: 300px; max-height: 400px;">
            @if($orderWinners->count() > 0)
                <canvas id="chartOrderWinners"></canvas>
            @else
                <div class="text-center text-gray-400">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p class="text-sm">Tidak ada data order winner</p>
                    <p class="text-xs mt-2">Pastikan ada data di tabel order_winners dan orders dengan status dikonfirmasi/diproses/selesai</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- 4. Trend PO & PO Berdasarkan Prioritas --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- PO Trend by Month --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base md:text-lg font-semibold text-gray-900">Trend PO 12 Bulan Terakhir</h3>
                <p class="text-xs md:text-sm text-gray-500">Total nilai PO per bulan</p>
            </div>
            <button onclick="openPOTrendModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-list"></i>
                <span class="hidden sm:inline">Detail</span>
            </button>
        </div>

        <div style="height: 250px; max-height: 350px;">
            <canvas id="chartPOTrend"></canvas>
        </div>
    </div>

    {{-- PO By Priority --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base md:text-lg font-semibold text-gray-900">PO Berdasarkan Prioritas</h3>
                <p class="text-xs md:text-sm text-gray-500">Distribusi prioritas purchase order</p>
            </div>
            <button onclick="openPOPriorityModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-list"></i>
                <span class="hidden sm:inline">Detail</span>
            </button>
        </div>

        <div style="height: 250px; max-height: 350px;">
            <canvas id="chartPOByPriority"></canvas>
        </div>
    </div>
</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
// Toggle custom date inputs for filter
document.getElementById('periodeFilter').addEventListener('change', function() {
    const startDateDiv = document.getElementById('startDateDiv');
    const endDateDiv = document.getElementById('endDateDiv');

    if (this.value === 'custom') {
        startDateDiv.classList.remove('hidden');
        endDateDiv.classList.remove('hidden');
    } else {
        startDateDiv.classList.add('hidden');
        endDateDiv.classList.add('hidden');
    }
});

// Chart colors
const chartColors = [
    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
    '#EC4899', '#06B6D4', '#F97316', '#84CC16', '#6366F1'
];

// PO By Client Chart (Bar with percentage)
@if($poByClient->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartPOByClient').getContext('2d');
    const isMobile = window.innerWidth < 768;
    const percentages = @json($poByClient->pluck('percentage')->toArray());

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [@foreach($poByClient as $item) '{{ $item->klien_nama }}', @endforeach],
            datasets: [{
                label: 'Nilai PO',
                data: [@foreach($poByClient as $item) {{ $item->total_nilai }}, @endforeach],
                backgroundColor: chartColors,
                borderWidth: 1,
                borderColor: chartColors.map(c => c.replace('0.8', '1')),
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: isMobile ? 'y' : 'x',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            const value = context.parsed.y !== undefined ? context.parsed.y : context.parsed.x;
                            const percentage = percentages[context.dataIndex];
                            let formattedValue = '';
                            if (value >= 1000000000) {
                                formattedValue = 'Rp ' + (value/1000000000).toFixed(2) + ' Miliar';
                            } else if (value >= 1000000) {
                                formattedValue = 'Rp ' + (value/1000000).toFixed(2) + ' Juta';
                            } else {
                                formattedValue = 'Rp ' + value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                            return 'Nilai: ' + formattedValue + ' (' + percentage.toFixed(1) + '%)';
                        }
                    }
                },
                datalabels: {
                    display: false
                }
            },
            scales: {
                x: {
                    display: !isMobile,
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: { size: 10 }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return 'Rp ' + (value/1000000000).toFixed(2) + 'M';
                            } else if (value >= 1000000) {
                                return 'Rp ' + (value/1000000).toFixed(2) + 'Jt';
                            }
                            return 'Rp ' + value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        },
                        font: { size: 10 }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            }
        }
    });
});
@endif

// PO By Status Chart
@if($poByStatus->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartPOByStatus').getContext('2d');
    const isMobile = window.innerWidth < 768;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [@foreach($poByStatus as $item) '{{ ucfirst($item->status) }}', @endforeach],
            datasets: [{
                data: [@foreach($poByStatus as $item) {{ $item->total }}, @endforeach],
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
                    position: isMobile ? 'bottom' : 'right',
                    labels: {
                        padding: isMobile ? 10 : 20,
                        font: { size: isMobile ? 10 : 12 },
                        boxWidth: isMobile ? 10 : 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' PO';
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: isMobile ? 10 : 14 },
                    formatter: (value, context) => value
                }
            }
        },
        plugins: [ChartDataLabels]
    });
});
@endif

// PO Trend Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartPOTrend').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($monthLabels),
            datasets: [{
                label: 'Total Nilai PO (Juta)',
                data: @json(array_column($poTrendByMonth, 'total_nilai')),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y || 0;
                            let formattedValue = '';
                            if (value >= 1000000000) {
                                formattedValue = 'Rp ' + (value/1000000000).toFixed(2) + ' Miliar';
                            } else if (value >= 1000000) {
                                formattedValue = 'Rp ' + (value/1000000).toFixed(2) + ' Juta';
                            } else {
                                formattedValue = 'Rp ' + value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                            return formattedValue;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return 'Rp ' + (value / 1000000000).toFixed(2) + ' Miliar';
                            } else if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(2) + ' Juta';
                            } else {
                                return 'Rp ' + value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        }
    });
});

// PO By Priority Chart
@if($poByPriority->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartPOByPriority').getContext('2d');
    const priorityColors = {
        'tinggi': '#EF4444',
        'sedang': '#F59E0B',
        'rendah': '#6B7280'
    };
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [@foreach($poByPriority as $item) '{{ ucfirst($item->priority) }}', @endforeach],
            datasets: [{
                label: 'Jumlah PO',
                data: [@foreach($poByPriority as $item) {{ $item->total }}, @endforeach],
                backgroundColor: [@foreach($poByPriority as $item) priorityColors['{{ $item->priority }}'], @endforeach],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' PO';
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
@endif

// Outstanding Chart (Bar by PO Number)
@if($outstandingChartData->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartOutstanding').getContext('2d');
    const isMobile = window.innerWidth < 768;
    const klienData = @json($outstandingChartData->pluck('klien_nama')->toArray());
    const orderStatusData = @json($outstandingChartData->pluck('order_status')->toArray());
    const namaMaterialData = @json($outstandingChartData->pluck('nama_material')->toArray());

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [@foreach($outstandingChartData as $item) '{{ $item->display_name }}', @endforeach],
            datasets: [{
                label: 'Nilai Outstanding',
                data: [@foreach($outstandingChartData as $item) {{ $item->total_nilai }}, @endforeach],
                backgroundColor: chartColors,
                borderWidth: 1,
                borderColor: chartColors.map(c => c.replace('0.8', '1')),
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: isMobile ? 'y' : 'x',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return 'PO: ' + context[0].label;
                        },
                        label: function(context) {
                            const value = context.parsed.y !== undefined ? context.parsed.y : context.parsed.x;
                            const percentage = (value / {{ $totalOutstandingChart }} * 100).toFixed(1);
                            let formattedValue = '';
                            if (value >= 1000000000) {
                                formattedValue = 'Rp ' + (value/1000000000).toFixed(2) + ' Miliar';
                            } else if (value >= 1000000) {
                                formattedValue = 'Rp ' + (value/1000000).toFixed(2) + ' Juta';
                            } else {
                                formattedValue = 'Rp ' + value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                            const klien = klienData[context.dataIndex];
                            const orderStatus = orderStatusData[context.dataIndex];
                            const namaMaterial = namaMaterialData[context.dataIndex];

                            return [
                                'Klien: ' + klien,
                                'Status: ' + orderStatus.charAt(0).toUpperCase() + orderStatus.slice(1),
                                'Nilai: ' + formattedValue + ' (' + percentage + '%)',
                                'Material: ' + (namaMaterial || 'N/A')
                            ];
                        }
                    }
                },
                datalabels: {
                    display: false
                }
            },
            scales: {
                x: {
                    display: !isMobile,
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: { size: 10 }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return 'Rp ' + (value/1000000000).toFixed(2) + 'M';
                            } else if (value >= 1000000) {
                                return 'Rp ' + (value/1000000).toFixed(2) + 'Jt';
                            }
                            return 'Rp ' + value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        },
                        font: { size: 10 }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            }
        }
    });
});
@endif

// Order Winners Chart (Pie)
@if($orderWinners->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartOrderWinners').getContext('2d');
    const isMobile = window.innerWidth < 768;

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [@foreach($orderWinners as $winner) '{{ $winner->marketing_nama }}', @endforeach],
            datasets: [{
                data: [@foreach($orderWinners as $winner) {{ $winner->total_nilai }}, @endforeach],
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
                    position: isMobile ? 'bottom' : 'right',
                    labels: {
                        padding: isMobile ? 10 : 20,
                        font: { size: isMobile ? 10 : 12 },
                        boxWidth: isMobile ? 10 : 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed || 0;
                            const percentage = @json($orderWinners->pluck('percentage')->toArray())[context.dataIndex];
                            let formattedValue;
                            if (value >= 1000000000) {
                                formattedValue = 'Rp ' + (value / 1000000000).toFixed(1) + ' Miliar';
                            } else if (value >= 1000000) {
                                formattedValue = 'Rp ' + (value / 1000000).toFixed(1) + ' Juta';
                            } else {
                                formattedValue = 'Rp ' + value.toLocaleString('id-ID');
                            }
                            return context.label + ': ' + formattedValue + ' (' + percentage.toFixed(1) + '%)';
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: isMobile ? 10 : 14 },
                    formatter: (value, context) => {
                        const percentage = @json($orderWinners->pluck('percentage')->toArray())[context.dataIndex];
                        return percentage > 5 ? percentage.toFixed(1) + '%' : '';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
});
@endif

// Modal Functions
function openOutstandingModal() {
    document.getElementById('outstandingModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeOutstandingModal() {
    document.getElementById('outstandingModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openStatusModal() {
    document.getElementById('statusModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openClientModal() {
    document.getElementById('clientModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeClientModal() {
    document.getElementById('clientModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openOrderWinnerModal() {
    const periode = '{{ $periode }}';
    const startDate = '{{ $startDate }}';
    const endDate = '{{ $endDate }}';
    
    fetch(`{{ route('laporan.po.orderWinnerDetails') }}?periode=${periode}&start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            displayOrderWinnerDetails(data);
            document.getElementById('orderWinnerModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => console.error('Error:', error));
}

function closeOrderWinnerModal() {
    document.getElementById('orderWinnerModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openPOTrendModal() {
    document.getElementById('poTrendModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Initialize chart in modal after it's visible
    setTimeout(() => {
        const ctx = document.getElementById('chartPOTrendModal').getContext('2d');
        
        // Destroy existing chart if any
        if (window.poTrendModalChart) {
            window.poTrendModalChart.destroy();
        }
        
        window.poTrendModalChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($monthLabels),
                datasets: [{
                    label: 'Total Nilai PO',
                    data: @json(array_column($poTrendByMonth, 'total_nilai')),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y || 0;
                                let formattedValue = '';
                                if (value >= 1000000000) {
                                    formattedValue = 'Rp ' + (value/1000000000).toFixed(2) + ' Miliar';
                                } else if (value >= 1000000) {
                                    formattedValue = 'Rp ' + (value/1000000).toFixed(2) + ' Juta';
                                } else {
                                    formattedValue = 'Rp ' + value.toLocaleString('id-ID');
                                }
                                return formattedValue;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000000) {
                                    return 'Rp ' + (value / 1000000000).toFixed(0) + ' M';
                                } else if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                                } else {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            }
        });
    }, 100);
}

function closePOTrendModal() {
    document.getElementById('poTrendModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Destroy chart when closing modal
    if (window.poTrendModalChart) {
        window.poTrendModalChart.destroy();
        window.poTrendModalChart = null;
    }
}

function openPOPriorityModal() {
    document.getElementById('poPriorityModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Initialize chart in modal after it's visible
    setTimeout(() => {
        const ctx = document.getElementById('chartPOPriorityModal').getContext('2d');
        
        // Destroy existing chart if any
        if (window.poPriorityModalChart) {
            window.poPriorityModalChart.destroy();
        }
        
        const priorityColors = {
            'tinggi': '#EF4444',
            'sedang': '#F59E0B',
            'rendah': '#6B7280'
        };
        
        const labels = [@foreach($poByPriority as $item) '{{ ucfirst($item->priority) }}', @endforeach];
        const data = [@foreach($poByPriority as $item) {{ $item->nilai }}, @endforeach];
        const colors = [@foreach($poByPriority as $item) priorityColors['{{ $item->priority }}'], @endforeach];
        
        window.poPriorityModalChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Nilai (Rp)',
                    data: data,
                    backgroundColor: colors,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y || 0;
                                let formattedValue = '';
                                if (value >= 1000000000) {
                                    formattedValue = 'Rp ' + (value/1000000000).toFixed(2) + ' Miliar';
                                } else if (value >= 1000000) {
                                    formattedValue = 'Rp ' + (value/1000000).toFixed(2) + ' Juta';
                                } else {
                                    formattedValue = 'Rp ' + value.toLocaleString('id-ID');
                                }
                                return formattedValue;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000000) {
                                    return 'Rp ' + (value / 1000000000).toFixed(0) + ' M';
                                } else if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                                } else {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            }
        });
    }, 100);
}

function closePOPriorityModal() {
    document.getElementById('poPriorityModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Destroy chart when closing modal
    if (window.poPriorityModalChart) {
        window.poPriorityModalChart.destroy();
        window.poPriorityModalChart = null;
    }
}

function displayOrderWinnerDetails(data) {
    let totalOverall = 0;
    let totalPOOverall = 0;
    
    let html = '';
    
    // Loop through Marketing
    data.forEach(marketing => {
        totalOverall += marketing.total_nilai;
        totalPOOverall += marketing.total_po;
        
        html += `
            <div class="mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 rounded-t-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-lg"><i class="fas fa-user-tie mr-2"></i>${marketing.marketing_nama}</h4>
                            <p class="text-sm opacity-90">${marketing.total_po} PO</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm opacity-90">Total Nilai</p>
                            <p class="font-bold text-xl">Rp ${(marketing.total_nilai / 1000000).toFixed(1)} Jt</p>
                        </div>
                    </div>
                </div>
                <div class="border border-t-0 border-gray-200 rounded-b-lg overflow-hidden">
        `;
        
        // Loop through Klien for this Marketing
        marketing.kliens.forEach((klien, klienIndex) => {
            html += `
                <div class="bg-gray-50 p-3 ${klienIndex > 0 ? 'border-t border-gray-300' : ''}">
                    <div class="flex justify-between items-center">
                        <div>
                            <h5 class="font-semibold text-gray-800"><i class="fas fa-building mr-2"></i>${klien.klien_nama}</h5>
                            <p class="text-xs text-gray-600">${klien.total_po} PO</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-600">Total</p>
                            <p class="font-semibold text-gray-800">Rp ${(klien.total_nilai / 1000000).toFixed(1)} Jt</p>
                        </div>
                    </div>
                </div>
            `;
            
            // Loop through Cabang for this Klien
            klien.cabangs.forEach((cabang, cabangIndex) => {
                html += `
                    <div class="bg-white px-6 py-3 ${cabangIndex > 0 ? 'border-t border-gray-200' : ''}">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <h6 class="font-medium text-gray-700"><i class="fas fa-map-marker-alt mr-2 text-orange-500"></i>${cabang.cabang_nama}</h6>
                                <p class="text-xs text-gray-500">${cabang.total_po} PO</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Subtotal</p>
                                <p class="font-medium text-gray-700">Rp ${(cabang.total_nilai / 1000000).toFixed(1)} Jt</p>
                            </div>
                        </div>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">No PO</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Tanggal</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-600">Status</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-600">Nilai PO</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                // Loop through Orders for this Cabang
                cabang.orders.forEach(order => {
                    const nilaiFormatted = (parseFloat(order.total_nilai) / 1000000).toFixed(1);
                    const statusBadge = order.order_status === 'selesai' ? 'bg-green-100 text-green-800' : 
                                       order.order_status === 'diproses' ? 'bg-blue-100 text-blue-800' : 
                                       'bg-yellow-100 text-yellow-800';
                    
                    html += `
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-3 py-2 text-gray-800">${order.po_number}</td>
                            <td class="px-3 py-2 text-gray-600">${order.tanggal_order}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${statusBadge}">
                                    ${order.order_status.charAt(0).toUpperCase() + order.order_status.slice(1)}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right font-medium text-gray-800">Rp ${nilaiFormatted} Jt</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            });
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    html += `
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4 rounded-lg mt-4">
            <div class="flex justify-between items-center">
                <div class="text-white">
                    <span class="font-bold text-lg">GRAND TOTAL</span>
                    <p class="text-sm opacity-90">${totalPOOverall} Total PO</p>
                </div>
                <span class="text-white font-bold text-2xl">Rp ${(totalOverall / 1000000).toFixed(1)} Jt</span>
            </div>
        </div>
    `;
    
    document.getElementById('orderWinnerDetailsContent').innerHTML = html;
}

function exportOrderWinnerPDF() {
    const periode = '{{ $periode }}';
    const startDate = '{{ $startDate }}';
    const endDate = '{{ $endDate }}';
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("laporan.po.orderWinnerPDF") }}';
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

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const outstandingModal = document.getElementById('outstandingModal');
    const statusModal = document.getElementById('statusModal');
    const clientModal = document.getElementById('clientModal');
    const orderWinnerModal = document.getElementById('orderWinnerModal');
    const poTrendModal = document.getElementById('poTrendModal');
    const poPriorityModal = document.getElementById('poPriorityModal');

    if (event.target === outstandingModal) {
        closeOutstandingModal();
    }
    if (event.target === statusModal) {
        closeStatusModal();
    }
    if (event.target === clientModal) {
        closeClientModal();
    }
    if (event.target === orderWinnerModal) {
        closeOrderWinnerModal();
    }
    if (event.target === poTrendModal) {
        closePOTrendModal();
    }
    if (event.target === poPriorityModal) {
        closePOPriorityModal();
    }
});
</script>

{{-- Outstanding Details Modal --}}
<div id="outstandingModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-xl bg-white">
        {{-- Header --}}
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Detail Outstanding Order</h3>
                <p class="text-sm text-gray-500 mt-1">Daftar semua item yang masih outstanding</p>
            </div>
            <button onclick="closeOutstandingModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        {{-- Summary Info --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-red-600 font-medium">Total Outstanding</p>
                        <p class="text-lg font-bold text-red-700">
                            Rp {{ number_format($totalOutstanding, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600 font-medium">Total PO</p>
                        <p class="text-lg font-bold text-blue-700">{{ $poBerjalan }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-boxes text-orange-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-orange-600 font-medium">Total Qty</p>
                        <p class="text-lg font-bold text-orange-700">{{ number_format($totalQtyOutstanding, 0, ',', '.') }} kg</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Export Button --}}
        <div class="mb-4 flex justify-end">
            <form action="{{ route('laporan.po.outstanding.pdf') }}" method="POST" target="_blank">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    Download PDF
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pabrik</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty (kg)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga (Rp/kg)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total (Rp)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $no = 1;
                        $outstandingDetails = \App\Models\OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
                            ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
                            ->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
                            ->whereNotIn('order_details.status', ['selesai'])
                            ->select(
                                'orders.po_number',
                                'orders.no_order',
                                'kliens.nama as klien_nama',
                                'kliens.cabang as klien_cabang',
                                'bahan_baku_klien.nama as material_nama',
                                'order_details.qty',
                                'order_details.harga_jual',
                                'order_details.total_harga',
                                'order_details.status as detail_status'
                            )
                            ->orderBy('orders.po_number')
                            ->orderBy('kliens.nama')
                            ->get();
                    @endphp

                    @forelse($outstandingDetails as $detail)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $no++ }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $detail->po_number ?: $detail->no_order }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $detail->klien_nama }}
                                @if($detail->klien_cabang)
                                    <span class="text-xs text-gray-500">({{ $detail->klien_cabang }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $detail->material_nama ?: '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($detail->qty, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($detail->harga_jual, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                {{ number_format($detail->total_harga, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>Tidak ada data outstanding</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 sticky bottom-0">
                    <tr class="font-bold">
                        <td colspan="4" class="px-4 py-3 text-sm text-gray-900 text-right">TOTAL:</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                            {{ number_format($totalQtyOutstanding, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">-</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                            {{ number_format($totalOutstanding, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex justify-end">
            <button onclick="closeOutstandingModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- PO By Status Modal --}}
<div id="statusModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-xl bg-white">
        {{-- Header --}}
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Detail PO Berdasarkan Status</h3>
                <p class="text-sm text-gray-500 mt-1">Distribusi status purchase order</p>
            </div>
            <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        {{-- Summary Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach($poByStatus as $status)
                @php
                    $statusConfig = match($status->status) {
                        'draft' => ['color' => 'gray', 'icon' => 'fa-file-alt', 'label' => 'Draft'],
                        'dikonfirmasi' => ['color' => 'yellow', 'icon' => 'fa-check-circle', 'label' => 'Dikonfirmasi'],
                        'diproses' => ['color' => 'blue', 'icon' => 'fa-cog', 'label' => 'Diproses'],
                        'selesai' => ['color' => 'green', 'icon' => 'fa-check-double', 'label' => 'Selesai'],
                        'dibatalkan' => ['color' => 'red', 'icon' => 'fa-times-circle', 'label' => 'Dibatalkan'],
                        default => ['color' => 'gray', 'icon' => 'fa-file', 'label' => ucfirst($status->status)]
                    };
                @endphp
                <div class="bg-{{ $statusConfig['color'] }}-50 rounded-lg p-4 border border-{{ $statusConfig['color'] }}-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-{{ $statusConfig['color'] }}-100 rounded-lg flex items-center justify-center">
                            <i class="fas {{ $statusConfig['icon'] }} text-{{ $statusConfig['color'] }}-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-{{ $statusConfig['color'] }}-600 font-medium">{{ $statusConfig['label'] }}</p>
                            <p class="text-lg font-bold text-{{ $statusConfig['color'] }}-700">{{ number_format($status->total, 0, ',', '.') }} PO</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Export Button --}}
        <div class="mb-4 flex justify-end">
            <form action="{{ route('laporan.po.status.pdf') }}" method="POST" target="_blank">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    Download PDF
                </button>
            </form>
        </div>

        {{-- Detail PO Per Status --}}
        <div class="space-y-6 max-h-[500px] overflow-y-auto">
            @foreach($poByStatus as $status)
                @php
                    $statusConfig = match($status->status) {
                        'draft' => ['color' => 'gray', 'icon' => 'fa-file-alt', 'label' => 'Draft'],
                        'dikonfirmasi' => ['color' => 'yellow', 'icon' => 'fa-check-circle', 'label' => 'Dikonfirmasi'],
                        'diproses' => ['color' => 'blue', 'icon' => 'fa-cog', 'label' => 'Diproses'],
                        'selesai' => ['color' => 'green', 'icon' => 'fa-check-double', 'label' => 'Selesai'],
                        'dibatalkan' => ['color' => 'red', 'icon' => 'fa-times-circle', 'label' => 'Dibatalkan'],
                        default => ['color' => 'gray', 'icon' => 'fa-file', 'label' => ucfirst($status->status)]
                    };
                    $poDetails = $poDetailsByStatus[$status->status] ?? [];
                    $totalPOStatus = $poByStatus->sum('total');
                    $percentage = $totalPOStatus > 0 ? ($status->total / $totalPOStatus) * 100 : 0;
                @endphp
                
                <div class="border border-{{ $statusConfig['color'] }}-200 rounded-lg overflow-hidden">
                    {{-- Status Header --}}
                    <div class="bg-{{ $statusConfig['color'] }}-50 px-4 py-3 border-b border-{{ $statusConfig['color'] }}-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-{{ $statusConfig['color'] }}-100 rounded-lg flex items-center justify-center">
                                    <i class="fas {{ $statusConfig['icon'] }} text-{{ $statusConfig['color'] }}-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-{{ $statusConfig['color'] }}-900">{{ $statusConfig['label'] }}</h4>
                                    <p class="text-xs text-{{ $statusConfig['color'] }}-600">
                                        {{ number_format($status->total, 0, ',', '.') }} PO 
                                        ({{ number_format($percentage, 1, ',', '.') }}%)
                                        • Total: Rp {{ number_format($status->nilai, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- PO List --}}
                    @if(count($poDetails) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No PO</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Klien</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($poDetails as $po)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-900 font-medium">{{ $po['po_number'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $po['klien_nama'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600">{{ $po['tanggal_order'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-4 py-6 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p class="text-sm">Tidak ada PO dengan status ini</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Analysis --}}
        <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-200">
            <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
                <i class="fas fa-chart-line"></i>
                Ringkasan
            </h4>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Total PO: {{ number_format($totalPOStatus, 0, ',', '.') }} purchase order</li>
                @foreach($poByStatus as $status)
                    <li>• {{ ucfirst($status->status) }}: {{ number_format($status->total, 0, ',', '.') }} PO ({{ number_format($totalPOStatus > 0 ? ($status->total / $totalPOStatus) * 100 : 0, 1, ',', '.') }}%)</li>
                @endforeach
            </ul>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex justify-end">
            <button onclick="closeStatusModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- PO By Client Modal --}}
<div id="clientModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-6 mx-auto p-5 border w-[95%] max-w-[1600px] shadow-lg rounded-xl bg-white mb-10">
        {{-- Header --}}
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Detail PO Berdasarkan Klien</h3>
                <p class="text-sm text-gray-500 mt-1">Akumulasi Purchase Order per Pabrik/Klien (Lengkap dengan Detail)</p>
            </div>
            <button onclick="closeClientModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        {{-- Summary Info --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600 font-medium">Total Klien</p>
                        <p class="text-lg font-bold text-blue-700">{{ $poByClient->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-green-600 font-medium">Total PO</p>
                        <p class="text-lg font-bold text-green-700">{{ number_format($poByClient->sum('total_po'), 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-purple-600 font-medium">Total Nilai</p>
                        <p class="text-lg font-bold text-purple-700">
                            Rp {{ number_format($poByClient->sum('total_nilai') / 1000000, 1, ',', '.') }} Jt
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-orange-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-orange-600 font-medium">Outstanding</p>
                        <p class="text-lg font-bold text-orange-700">
                            Rp {{ number_format($poByClient->sum('outstanding_amount') / 1000000, 1, ',', '.') }} Jt
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-teal-50 rounded-lg p-4 border border-teal-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-teal-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-teal-600 font-medium">Rata-rata/PO</p>
                        <p class="text-lg font-bold text-teal-700">
                            @php $avgAll = $poByClient->sum('total_po') > 0 ? $poByClient->sum('total_nilai') / $poByClient->sum('total_po') : 0; @endphp
                            Rp {{ number_format($avgAll / 1000000, 1, ',', '.') }} Jt
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Export Button --}}
        <div class="mb-4 flex justify-end">
            <form action="{{ route('laporan.po.client.pdf') }}" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="periode" value="{{ $periode }}">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    Download PDF
                </button>
            </form>
        </div>

        {{-- Client Cards with Expandable Details --}}
        <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
            @php $no = 1; @endphp
            @forelse($poByClient as $client)
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    {{-- Client Header --}}
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-xl font-bold">
                                    {{ $no++ }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg">{{ $client->klien_nama }}</h4>
                                    @if($client->cabang)
                                        <p class="text-sm text-blue-100">{{ $client->cabang }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-4 lg:gap-6">
                                <div class="text-center">
                                    <p class="text-xs text-blue-100">Total PO</p>
                                    <p class="font-bold text-lg">{{ $client->total_po }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-blue-100">Total Nilai</p>
                                    <p class="font-bold text-lg">Rp {{ number_format($client->total_nilai / 1000000, 1, ',', '.') }} Jt</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-blue-100">Outstanding</p>
                                    <p class="font-bold text-lg {{ $client->outstanding_amount > 0 ? 'text-yellow-300' : 'text-green-300' }}">
                                        Rp {{ number_format($client->outstanding_amount / 1000000, 1, ',', '.') }} Jt
                                    </p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-blue-100">Kontribusi</p>
                                    <p class="font-bold text-lg">{{ number_format($client->percentage, 1, ',', '.') }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            {{-- Status Breakdown --}}
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500">Status:</span>
                                @if($client->status_dikonfirmasi > 0)
                                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                        {{ $client->status_dikonfirmasi }} Dikonfirmasi
                                    </span>
                                @endif
                                @if($client->status_diproses > 0)
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        {{ $client->status_diproses }} Diproses
                                    </span>
                                @endif
                                @if($client->status_selesai > 0)
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                        {{ $client->status_selesai }} Selesai
                                    </span>
                                @endif
                            </div>
                            <div class="text-gray-300">|</div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                                <span>Last Order: {{ $client->last_order_date ? \Carbon\Carbon::parse($client->last_order_date)->format('d M Y') : '-' }}</span>
                            </div>
                            <div class="text-gray-300">|</div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i class="fas fa-chart-bar text-gray-400"></i>
                                <span>Avg/PO: Rp {{ number_format($client->avg_nilai_per_po / 1000000, 2, ',', '.') }} Jt</span>
                            </div>
                        </div>
                    </div>

                    {{-- Expandable Details --}}
                    <details class="group">
                        <summary class="px-4 py-3 bg-white cursor-pointer hover:bg-gray-50 transition-colors flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-600">
                                <i class="fas fa-chevron-right mr-2 group-open:rotate-90 transition-transform"></i>
                                Lihat Detail ({{ $client->total_po }} PO)
                            </span>
                            <span class="text-xs text-gray-500">Klik untuk expand</span>
                        </summary>
                        
                        <div class="border-t border-gray-200">
                            {{-- Materials Section --}}
                            @if(isset($poDetailsByClient[$client->klien_id]['materials']) && count($poDetailsByClient[$client->klien_id]['materials']) > 0)
                            <div class="p-4 bg-blue-50 border-b border-gray-200">
                                <h5 class="text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-box mr-2"></i>Material yang Dipesan
                                </h5>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($poDetailsByClient[$client->klien_id]['materials'] as $material)
                                        <span class="px-3 py-1 bg-white border border-blue-200 rounded-full text-xs">
                                            <span class="font-medium text-gray-800">{{ $material['nama'] }}</span>
                                            <span class="text-gray-500 ml-1">({{ number_format($material['total_qty'], 0, ',', '.') }} kg • Rp {{ number_format($material['total_nilai'] / 1000000, 1, ',', '.') }} Jt)</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- PO List --}}
                            @if(isset($poDetailsByClient[$client->klien_id]['orders']) && count($poDetailsByClient[$client->klien_id]['orders']) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. PO</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Prioritas</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Nilai</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($poDetailsByClient[$client->klien_id]['orders'] as $po)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-blue-600">
                                                {{ $po['po_number'] }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">
                                                {{ $po['tanggal_order'] }}
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 max-w-xs truncate" title="{{ $po['materials'] }}">
                                                {{ Str::limit($po['materials'], 40) }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-center">
                                                @php
                                                    $statusConfig = match($po['status']) {
                                                        'dikonfirmasi' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                                        'diproses' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                                        'selesai' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                                        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                                    };
                                                @endphp
                                                <span class="px-2 py-0.5 {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} rounded-full text-xs font-medium">
                                                    {{ ucfirst($po['status']) }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-center">
                                                @php
                                                    $priorityConfig = match($po['priority']) {
                                                        'tinggi' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                                        'sedang' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                                                        'rendah' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                                        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                                    };
                                                @endphp
                                                <span class="px-2 py-0.5 {{ $priorityConfig['bg'] }} {{ $priorityConfig['text'] }} rounded-full text-xs font-medium">
                                                    {{ ucfirst($po['priority'] ?? '-') }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format($po['total_qty'], 0, ',', '.') }} kg
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                                Rp {{ number_format($po['total_amount'], 0, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-center">
                                                <a href="{{ route('orders.show', $po['id']) }}" target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                    <i class="fas fa-external-link-alt"></i> Lihat
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="p-4 text-center text-gray-500">
                                <i class="fas fa-inbox text-2xl mb-2"></i>
                                <p class="text-sm">Tidak ada data PO untuk klien ini</p>
                            </div>
                            @endif
                        </div>
                    </details>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p>Tidak ada data klien</p>
                </div>
            @endforelse
        </div>

        {{-- Footer Summary --}}
        <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Total Klien:</span>
                    <span class="font-bold text-gray-900 ml-2">{{ $poByClient->count() }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Total PO:</span>
                    <span class="font-bold text-gray-900 ml-2">{{ number_format($poByClient->sum('total_po'), 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Total Nilai:</span>
                    <span class="font-bold text-gray-900 ml-2">Rp {{ number_format($poByClient->sum('total_nilai'), 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Total Outstanding:</span>
                    <span class="font-bold text-orange-600 ml-2">Rp {{ number_format($poByClient->sum('outstanding_amount'), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex justify-end">
            <button onclick="closeClientModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- Order Winner Details Modal --}}
<div id="orderWinnerModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Detail Order Winners</h3>
                <p class="text-sm text-gray-500 mt-1">Rincian PO yang dimenangkan per marketing</p>
            </div>
            <button onclick="closeOrderWinnerModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <div class="mb-4 flex justify-end">
            <button onclick="exportOrderWinnerPDF()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-file-pdf"></i>
                Download PDF
            </button>
        </div>

        <div id="orderWinnerDetailsContent" class="overflow-x-auto max-h-96 overflow-y-auto">
            <!-- Content will be loaded dynamically -->
        </div>

        <div class="mt-6 flex justify-end">
            <button onclick="closeOrderWinnerModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- PO Trend Details Modal --}}
<div id="poTrendModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Detail Trend PO 12 Bulan Terakhir</h3>
                <p class="text-sm text-gray-500 mt-1">Rincian jumlah dan nilai PO per bulan</p>
            </div>
            <button onclick="closePOTrendModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        {{-- Summary Info --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600 font-medium">Periode</p>
                        <p class="text-lg font-bold text-blue-700">12 Bulan</p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-green-600 font-medium">Total PO</p>
                        <p class="text-lg font-bold text-green-700">
                            {{ number_format(array_sum(array_column($poTrendByMonth, 'total_po')), 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-purple-600 font-medium">Total Nilai</p>
                        <p class="text-lg font-bold text-purple-700">
                            @php
                                $totalNilaiTrend = array_sum(array_column($poTrendByMonth, 'total_nilai'));
                            @endphp
                            Rp {{ number_format($totalNilaiTrend, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Export Button --}}
        <div class="mb-4 flex justify-end">
            <form action="{{ route('laporan.po.trend.pdf') }}" method="POST" target="_blank">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    Download PDF
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah PO</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nilai</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata per PO</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $no = 1; @endphp
                    @foreach($poTrendByMonth as $trend)
                        @php
                            $avgPerPO = $trend['total_po'] > 0 ? $trend['total_nilai'] / $trend['total_po'] : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $no++ }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $trend['month'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center font-semibold">
                                {{ number_format($trend['total_po'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                Rp {{ number_format($trend['total_nilai'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right">
                                Rp {{ number_format($avgPerPO, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 sticky bottom-0">
                    <tr class="font-bold">
                        <td colspan="2" class="px-4 py-3 text-sm text-gray-900 text-right">TOTAL:</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">
                            {{ number_format(array_sum(array_column($poTrendByMonth, 'total_po')), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                            Rp {{ number_format($totalNilaiTrend, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                            @php
                                $totalPOTrend = array_sum(array_column($poTrendByMonth, 'total_po'));
                                $avgOverall = $totalPOTrend > 0 ? $totalNilaiTrend / $totalPOTrend : 0;
                            @endphp
                            Rp {{ number_format($avgOverall, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Chart Preview --}}
        <div class="mt-6 bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Visualisasi Trend</h4>
            <div style="height: 200px;">
                <canvas id="chartPOTrendModal"></canvas>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex justify-end">
            <button onclick="closePOTrendModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- PO Priority Details Modal --}}
<div id="poPriorityModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Detail PO Berdasarkan Prioritas</h3>
                <p class="text-sm text-gray-500 mt-1">Distribusi PO berdasarkan tingkat prioritas</p>
            </div>
            <button onclick="closePOPriorityModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        {{-- Summary Info --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-red-600 font-medium">Prioritas Tinggi</p>
                        <p class="text-lg font-bold text-red-700">
                            {{ $poByPriority->where('priority', 'tinggi')->first()->total ?? 0 }} PO
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-minus-circle text-orange-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-orange-600 font-medium">Prioritas Sedang</p>
                        <p class="text-lg font-bold text-orange-700">
                            {{ $poByPriority->where('priority', 'sedang')->first()->total ?? 0 }} PO
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-info-circle text-gray-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 font-medium">Prioritas Rendah</p>
                        <p class="text-lg font-bold text-gray-700">
                            {{ $poByPriority->where('priority', 'rendah')->first()->total ?? 0 }} PO
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Export Button --}}
        <div class="mb-4 flex justify-end">
            <form action="{{ route('laporan.po.priority.pdf') }}" method="POST" target="_blank">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    Download PDF
                </button>
            </form>
        </div>

        {{-- Detail PO Per Priority --}}
        <div class="space-y-6 max-h-[500px] overflow-y-auto">
            @php 
                $totalNilaiPriority = $poByPriority->sum('nilai');
            @endphp
            @foreach($poByPriority as $priority)
                @php
                    $avgPerPO = $priority->total > 0 ? $priority->nilai / $priority->total : 0;
                    $percentage = $totalNilaiPriority > 0 ? ($priority->nilai / $totalNilaiPriority) * 100 : 0;
                    
                    // Set color based on priority
                    $headerColor = match($priority->priority) {
                        'tinggi' => 'from-red-600 to-red-700',
                        'sedang' => 'from-orange-500 to-orange-600',
                        'rendah' => 'from-gray-500 to-gray-600',
                        default => 'from-blue-600 to-blue-700'
                    };
                    
                    $badgeColor = match($priority->priority) {
                        'tinggi' => 'bg-red-100 text-red-800',
                        'sedang' => 'bg-orange-100 text-orange-800',
                        'rendah' => 'bg-gray-100 text-gray-800',
                        default => 'bg-blue-100 text-blue-800'
                    };
                @endphp
                
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    {{-- Priority Header --}}
                    <div class="bg-gradient-to-r {{ $headerColor }} text-white p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-bold text-lg flex items-center gap-2">
                                    <i class="fas fa-flag"></i>
                                    Prioritas {{ ucfirst($priority->priority) }}
                                </h4>
                                <p class="text-sm opacity-90">{{ number_format($priority->total, 0, ',', '.') }} PO</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm opacity-90">Total Nilai</p>
                                <p class="font-bold text-xl">Rp {{ number_format($priority->nilai / 1000000, 2, ',', '.') }} Jt</p>
                                <p class="text-xs opacity-75">{{ number_format($percentage, 1, ',', '.') }}% dari total</p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- PO Details Table --}}
                    @if(isset($poDetailsByPriority[$priority->priority]) && count($poDetailsByPriority[$priority->priority]) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. PO</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Klien</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cabang</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bahan Baku</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Nilai</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($poDetailsByPriority[$priority->priority] as $index => $po)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-blue-600">
                                        {{ $po['po_number'] }}
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-900">{{ $po['klien_nama'] }}</td>
                                    <td class="px-3 py-2 text-xs text-gray-600">{{ $po['cabang'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">{{ $po['tanggal_order'] }}</td>
                                    <td class="px-3 py-2 text-xs text-gray-700">{{ $po['bahan_baku'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 text-center">
                                        {{ number_format($po['total_qty'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs font-semibold text-gray-900 text-right">
                                        Rp {{ number_format($po['total_amount'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            {{ $po['status'] == 'dikonfirmasi' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($po['status']) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-4 text-center text-gray-500">
                        <i class="fas fa-inbox text-2xl mb-2"></i>
                        <p class="text-sm">Tidak ada PO untuk prioritas ini</p>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
        
        {{-- Summary Table --}}
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioritas</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah PO</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nilai</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata per PO</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($poByPriority as $priority)
                        @php
                            $avgPerPO = $priority->total > 0 ? $priority->nilai / $priority->total : 0;
                            $percentage = $totalNilaiPriority > 0 ? ($priority->nilai / $totalNilaiPriority) * 100 : 0;
                            
                            $badgeColor = match($priority->priority) {
                                'tinggi' => 'bg-red-100 text-red-800',
                                'sedang' => 'bg-orange-100 text-orange-800',
                                'rendah' => 'bg-gray-100 text-gray-800',
                                default => 'bg-blue-100 text-blue-800'
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                                    {{ ucfirst($priority->priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center font-semibold">
                                {{ number_format($priority->total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                Rp {{ number_format($priority->nilai, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right">
                                Rp {{ number_format($avgPerPO, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center">
                                {{ number_format($percentage, 1, ',', '.') }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">TOTAL:</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">
                            {{ number_format($poByPriority->sum('total'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                            Rp {{ number_format($totalNilaiPriority, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                            @php
                                $totalPO = $poByPriority->sum('total');
                                $avgOverall = $totalPO > 0 ? $totalNilaiPriority / $totalPO : 0;
                            @endphp
                            Rp {{ number_format($avgOverall, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">100.0%</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Chart Preview --}}
        <div class="mt-6 bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Visualisasi Distribusi</h4>
            <div style="height: 200px;">
                <canvas id="chartPOPriorityModal"></canvas>
            </div>
        </div>

        {{-- Analysis --}}
        <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-200">
            <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
                <i class="fas fa-chart-line"></i>
                Analisis
            </h4>
            <ul class="text-sm text-blue-800 space-y-1">
                @php
                    $tinggiData = $poByPriority->where('priority', 'tinggi')->first();
                    $sedangData = $poByPriority->where('priority', 'sedang')->first();
                    $rendahData = $poByPriority->where('priority', 'rendah')->first();
                @endphp
                @if($tinggiData)
                <li>• Prioritas Tinggi: {{ $tinggiData->total }} PO dengan total nilai Rp {{ number_format($tinggiData->nilai, 2, ',', '.') }}</li>
                @endif
                @if($sedangData)
                <li>• Prioritas Sedang: {{ $sedangData->total }} PO dengan total nilai Rp {{ number_format($sedangData->nilai, 2, ',', '.') }}</li>
                @endif
                @if($rendahData)
                <li>• Prioritas Rendah: {{ $rendahData->total }} PO dengan total nilai Rp {{ number_format($rendahData->nilai, 2, ',', '.') }}</li>
                @endif
            </ul>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex justify-end">
            <button onclick="closePOPriorityModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

@endsection
