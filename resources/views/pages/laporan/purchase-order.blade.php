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
                        Rp {{ number_format($totalOutstanding / 1000000000, 1, ',', '.') }} Miliar
                    @elseif($totalOutstanding >= 1000000)
                        Rp {{ number_format($totalOutstanding / 1000000, 1, ',', '.') }} Juta
                    @else
                        Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
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
                        Rp {{ number_format($avgNilaiPerPO / 1000000000, 1, ',', '.') }} Miliar
                    @elseif($avgNilaiPerPO >= 1000000)
                        Rp {{ number_format($avgNilaiPerPO / 1000000, 1, ',', '.') }} Juta
                    @else
                        Rp {{ number_format($avgNilaiPerPO, 0, ',', '.') }}
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
                <p class="text-xs md:text-sm text-gray-500">Distribusi nilai PO per marketing</p>
            </div>
        </div>

        <div class="flex justify-center items-center" style="height: 300px; max-height: 400px;">
            @if($orderWinners->count() > 0)
                <canvas id="chartOrderWinners"></canvas>
            @else
                <div class="text-center text-gray-400">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p class="text-sm">Tidak ada data order winner</p>
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

// PO By Client Chart (Pie with percentage)
@if($poByClient->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartPOByClient').getContext('2d');
    const isMobile = window.innerWidth < 768;

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [@foreach($poByClient as $item) '{{ $item->klien_nama }}', @endforeach],
            datasets: [{
                data: [@foreach($poByClient as $item) {{ $item->total_nilai }}, @endforeach],
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
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const percentage = @json($poByClient->pluck('percentage')->toArray())[context.dataIndex];
                            let formattedValue = '';
                            if (value >= 1000000000) {
                                formattedValue = 'Rp ' + (value/1000000000).toFixed(1) + ' Miliar';
                            } else if (value >= 1000000) {
                                formattedValue = 'Rp ' + (value/1000000).toFixed(1) + ' Juta';
                            } else {
                                formattedValue = 'Rp ' + value.toLocaleString('id-ID');
                            }
                            return label + ': ' + formattedValue + ' (' + percentage.toFixed(1) + '%)';
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: isMobile ? 10 : 14 },
                    formatter: (value, context) => {
                        const percentage = @json($poByClient->pluck('percentage')->toArray())[context.dataIndex];
                        return percentage > 5 ? percentage.toFixed(1) + '%' : '';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
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
                                formattedValue = 'Rp ' + (value/1000000000).toFixed(1) + ' Miliar';
                            } else if (value >= 1000000) {
                                formattedValue = 'Rp ' + (value/1000000).toFixed(1) + ' Juta';
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
                                return 'Rp ' + (value / 1000000000).toFixed(0) + ' Miliar';
                            } else if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(0) + ' Juta';
                            } else {
                                return 'Rp ' + value.toLocaleString('id-ID');
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

// Outstanding Chart (Pie by PO Number)
@if($outstandingChartData->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartOutstanding').getContext('2d');
    const isMobile = window.innerWidth < 768;
    const klienData = @json($outstandingChartData->pluck('klien_nama')->toArray());
    const orderStatusData = @json($outstandingChartData->pluck('order_status')->toArray());
    const namaMaterialData = @json($outstandingChartData->pluck('nama_material')->toArray());

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [@foreach($outstandingChartData as $item) '{{ $item->display_name }}', @endforeach],
            datasets: [{
                data: [@foreach($outstandingChartData as $item) {{ $item->total_nilai }}, @endforeach],
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
                        padding: isMobile ? 8 : 15,
                        font: { size: isMobile ? 9 : 11 },
                        boxWidth: isMobile ? 10 : 12
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed || 0;
                            const percentage = (value / {{ $totalOutstandingChart }} * 100).toFixed(1);
                            let formattedValue = '';
                            if (value >= 1000000000) {
                                formattedValue = 'Rp ' + (value/1000000000).toFixed(1) + ' Miliar';
                            } else if (value >= 1000000) {
                                formattedValue = 'Rp ' + (value/1000000).toFixed(1) + ' Juta';
                            } else {
                                formattedValue = 'Rp ' + value.toLocaleString('id-ID');
                            }
                            const klien = klienData[context.dataIndex];
                            const orderStatus = orderStatusData[context.dataIndex];
                            const namaMaterial = namaMaterialData[context.dataIndex];

                            return [
                                'PO: ' + context.label,
                                'Klien: ' + klien,
                                'Status: ' + orderStatus.charAt(0).toUpperCase() + orderStatus.slice(1),
                                'Nilai: ' + formattedValue + ' (' + percentage + '%)',
                                'Material: ' + (namaMaterial || 'N/A')
                            ];
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: isMobile ? 9 : 12 },
                    formatter: (value, context) => {
                        const percentage = (value / {{ $totalOutstandingChart }} * 100);
                        // Only show label if percentage is > 5%
                        return percentage > 5 ? percentage.toFixed(1) + '%' : '';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
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

function openClientModal() {
    document.getElementById('clientModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeClientModal() {
    document.getElementById('clientModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const outstandingModal = document.getElementById('outstandingModal');
    const clientModal = document.getElementById('clientModal');

    if (event.target === outstandingModal) {
        closeOutstandingModal();
    }
    if (event.target === clientModal) {
        closeClientModal();
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
                            Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
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
                                {{ number_format($detail->harga_jual, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                {{ number_format($detail->total_harga, 0, ',', '.') }}
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
                            {{ number_format($totalOutstanding, 0, ',', '.') }}
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

{{-- PO By Client Modal --}}
<div id="clientModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-5xl shadow-lg rounded-xl bg-white">
        {{-- Header --}}
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Detail PO Berdasarkan Klien</h3>
                <p class="text-sm text-gray-500 mt-1">Akumulasi Purchase Order per Pabrik/Klien</p>
            </div>
            <button onclick="closeClientModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        {{-- Summary Info --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
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
                            Rp {{ number_format($poByClient->sum('total_nilai'), 0, ',', '.') }}
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

        {{-- Table --}}
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pabrik</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah PO</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga Semua PO</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $no = 1; @endphp
                    @forelse($poByClient as $client)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $no++ }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="font-medium">{{ $client->klien_nama }}</div>
                                @if($client->cabang)
                                    <div class="text-xs text-gray-500">{{ $client->cabang }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center font-medium">
                                {{ number_format($client->total_po, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                Rp {{ number_format($client->total_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>Tidak ada data klien</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 sticky bottom-0">
                    <tr class="font-bold">
                        <td colspan="2" class="px-4 py-3 text-sm text-gray-900 text-right">TOTAL:</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">
                            {{ number_format($poByClient->sum('total_po'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                            Rp {{ number_format($poByClient->sum('total_nilai'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex justify-end">
            <button onclick="closeClientModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

@endsection
