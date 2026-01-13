@extends('pages.laporan.base')

@section('report-content')

{{-- Summary Cards - 5 columns like pembayaran --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    {{-- Total Penagihan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-file-invoice-dollar text-purple-600 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 truncate">Total Penagihan</p>
                <h3 class="text-lg font-bold text-purple-600">
                    @if($totalPenagihan >= 1000000000)
                        Rp {{ number_format($totalPenagihan / 1000000000, 2, ',', '.') }} M
                    @else
                        Rp {{ number_format($totalPenagihan / 1000000, 2, ',', '.') }} Jt
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Penagihan Tahun Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-check text-blue-600 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 truncate">Penagihan Tahun Ini</p>
                <h3 class="text-lg font-bold text-blue-600">
                    @if($penagihanTahunIni >= 1000000000)
                        Rp {{ number_format($penagihanTahunIni / 1000000000, 2, ',', '.') }} M
                    @else
                        Rp {{ number_format($penagihanTahunIni / 1000000, 2, ',', '.') }} Jt
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Penagihan Bulan Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-day text-green-600 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 truncate">Penagihan Bulan Ini</p>
                <h3 class="text-lg font-bold text-green-600">
                    @if($penagihanBulanIni >= 1000000000)
                        Rp {{ number_format($penagihanBulanIni / 1000000000, 2, ',', '.') }} M
                    @else
                        Rp {{ number_format($penagihanBulanIni / 1000000, 2, ',', '.') }} Jt
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Total Piutang Pabrik --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-industry text-orange-600 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 truncate">Piutang Pabrik</p>
                <h3 class="text-lg font-bold text-orange-600">
                    @if($totalPiutangPabrik >= 1000000000)
                        Rp {{ number_format($totalPiutangPabrik / 1000000000, 2, ',', '.') }} M
                    @else
                        Rp {{ number_format($totalPiutangPabrik / 1000000, 2, ',', '.') }} Jt
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Jumlah Invoice --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-receipt text-indigo-600 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 truncate">Jumlah Invoice</p>
                <h3 class="text-lg font-bold text-indigo-600">
                    {{ number_format(array_sum($jumlahInvoicePerBulan)) }} Invoice
                </h3>
            </div>
        </div>
    </div>
</div>

{{-- Charts Section - Row 1: 2 columns --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Penagihan Per Klien (Pie Chart) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-chart-pie text-purple-600 mr-2"></i>
                Penagihan Per Klien
            </h3>
            <div class="flex items-center space-x-2">
                <select id="periodePenagihanKlien" class="text-sm border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="semua" {{ $periode == 'semua' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="tahun_ini" {{ $periode == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periode == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
        </div>
        <div id="customDatePenagihanKlien" class="mb-4 hidden">
            <div class="grid grid-cols-2 gap-2">
                <input type="date" id="startDatePenagihanKlien" class="text-sm border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                <input type="date" id="endDatePenagihanKlien" class="text-sm border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
            </div>
        </div>
        <div class="relative h-72">
            <canvas id="penagihanKlienChart"></canvas>
        </div>
    </div>

    {{-- Top 10 Klien (Table) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-trophy text-yellow-600 mr-2"></i>
                Top 10 Klien
            </h3>
            <div class="flex items-center space-x-2">
                <select id="periodeTopKlien" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="semua" {{ $periodeKlien == 'semua' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="tahun_ini" {{ $periodeKlien == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periodeKlien == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
        </div>
        <div id="customDateTopKlien" class="mb-4 hidden">
            <div class="grid grid-cols-2 gap-2">
                <input type="date" id="startDateTopKlien" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <input type="date" id="endDateTopKlien" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="overflow-y-auto max-h-72">
            <table class="w-full text-sm" id="topKlienTable">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">#</th>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Klien</th>
                        <th class="text-right px-4 py-2 font-semibold text-gray-700">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topKlien as $index => $klien)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3">
                                @if($index < 3)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $index == 0 ? 'bg-yellow-100 text-yellow-600' : ($index == 1 ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-600') }} font-bold text-xs">
                                        {{ $index + 1 }}
                                    </span>
                                @else
                                    <span class="text-gray-600 font-medium">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $klien->customer_name }}</div>
                                @if($klien->customer_address)
                                    <div class="text-xs text-gray-500">{{ Str::limit($klien->customer_address, 40) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-600">
                                Rp {{ number_format($klien->total, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>Belum ada data penagihan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Charts Section - Row 2: 2 columns --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Nilai Penagihan Per Bulan (Bar Chart) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                Penagihan Per Bulan
            </h3>
            <select id="tahunPenagihanPerBulan" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="relative h-72">
            <canvas id="penagihanPerBulanChart"></canvas>
        </div>
    </div>

    {{-- Jumlah Invoice Per Bulan (Line Chart) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
                Jumlah Invoice Per Bulan
            </h3>
            <select id="tahunJumlahInvoice" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ $year == $selectedYearInvoice ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="relative h-72">
            <canvas id="jumlahInvoicePerBulanChart"></canvas>
        </div>
    </div>
</div>

{{-- Top 10 Piutang Pabrik --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-industry text-orange-600 mr-2"></i>
            Top 10 Piutang Pabrik
        </h3>
        <div class="flex items-center space-x-2">
            <select id="periodePiutangPabrik" class="text-sm border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                <option value="semua" {{ $periodePiutangPabrik == 'semua' ? 'selected' : '' }}>Semua Periode</option>
                <option value="tahun_ini" {{ $periodePiutangPabrik == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                <option value="bulan_ini" {{ $periodePiutangPabrik == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="piutangPabrikTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">#</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">Pabrik/Klien</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">Alamat</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-700">Total Piutang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topPiutangPabrik as $index => $piutang)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3">
                            @if($index < 3)
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $index == 0 ? 'bg-red-100 text-red-600' : ($index == 1 ? 'bg-orange-100 text-orange-600' : 'bg-yellow-100 text-yellow-600') }} font-bold text-xs">
                                    {{ $index + 1 }}
                                </span>
                            @else
                                <span class="text-gray-600 font-medium">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $piutang['nama'] ?? 'Unknown' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ isset($piutang['alamat']) ? Str::limit($piutang['alamat'], 50) : '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-orange-600">
                            Rp {{ number_format($piutang['total'] ?? 0, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
                            <p>Tidak ada piutang pabrik yang belum lunas</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart Colors
    const chartColors = [
        '#9333ea', '#3b82f6', '#22c55e', '#f97316', '#ef4444',
        '#a855f7', '#0ea5e9', '#84cc16', '#fb923c', '#f43f5e'
    ];

    // Initial data from PHP - transform data to correct format
    let penagihanKlienData = @json($penagihanKlien->map(function($item) {
        return [
            'nama' => $item->customer_name ?? 'Unknown',
            'total' => floatval($item->total ?? 0)
        ];
    })->filter(function($item) {
        return $item['total'] > 0;
    })->values());

    const penagihanPerBulanData = @json($penagihanPerBulan);
    const jumlahInvoicePerBulanData = @json($jumlahInvoicePerBulan);
    const bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    // Initialize Charts
    let penagihanKlienChart, penagihanPerBulanChart, jumlahInvoicePerBulanChart;

    // Penagihan Per Klien Chart (Pie)
    const ctxPenagihanKlien = document.getElementById('penagihanKlienChart');

    if (penagihanKlienData && penagihanKlienData.length > 0) {
        penagihanKlienChart = new Chart(ctxPenagihanKlien, {
            type: 'pie',
            data: {
                labels: penagihanKlienData.map(item => item.nama),
                datasets: [{
                    data: penagihanKlienData.map(item => item.total),
                    backgroundColor: chartColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 11 },
                            padding: 8,
                            boxWidth: 12,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        const shortLabel = label ? (label.substring(0, 20) + (label.length > 20 ? '...' : '')) : 'Unknown';
                                        return {
                                            text: `${shortLabel} (${percentage}%)`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {                        label: function(context) {
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `Rp ${value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (${percentage}%)`;
                        }
                    }
                    },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 11 },
                        formatter: (value, ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return percentage > 5 ? percentage + '%' : '';
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    } else {
        // Show empty message
        ctxPenagihanKlien.parentElement.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-gray-400">
                <i class="fas fa-chart-pie text-5xl mb-3"></i>
                <p class="text-sm">Belum ada data penagihan</p>
            </div>
        `;
    }

    // Penagihan Per Bulan Chart (Bar)
    const ctxPenagihanPerBulan = document.getElementById('penagihanPerBulanChart');
    penagihanPerBulanChart = new Chart(ctxPenagihanPerBulan, {
        type: 'bar',
        data: {
            labels: bulanLabels,
            datasets: [{
                label: 'Nilai Penagihan',
                data: penagihanPerBulanData,
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 1,
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
                            return `Rp ${context.parsed.y.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        }
                    }
                },
                datalabels: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return 'Rp ' + (value / 1000000000).toFixed(1) + ' M';
                            }
                            return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                        }
                    }
                }
            }
        }
    });

    // Jumlah Invoice Per Bulan Chart (Line)
    const ctxJumlahInvoice = document.getElementById('jumlahInvoicePerBulanChart');
    jumlahInvoicePerBulanChart = new Chart(ctxJumlahInvoice, {
        type: 'line',
        data: {
            labels: bulanLabels,
            datasets: [{
                label: 'Jumlah Invoice',
                data: jumlahInvoicePerBulanData,
                borderColor: 'rgba(99, 102, 241, 1)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgba(99, 102, 241, 1)'
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
                            return `${context.parsed.y} Invoice`;
                        }
                    }
                },
                datalabels: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Number.isInteger(value) ? value : '';
                        }
                    }
                }
            }
        }
    });

    // Filter Handler Functions
    function setupPeriodeFilter(selectId, customDivId, startDateId, endDateId, ajaxType, updateFunction) {
        const select = document.getElementById(selectId);
        const customDiv = document.getElementById(customDivId);
        const startDate = document.getElementById(startDateId);
        const endDate = document.getElementById(endDateId);

        select.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDiv.classList.remove('hidden');
            } else {
                customDiv.classList.add('hidden');
                fetchData(ajaxType, { periode: this.value }, updateFunction);
            }
        });

        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                if (startDate.value && endDate.value) {
                    fetchData(ajaxType, {
                        periode: 'custom',
                        start_date: startDate.value,
                        end_date: endDate.value
                    }, updateFunction);
                }
            });

            endDate.addEventListener('change', function() {
                if (startDate.value && endDate.value) {
                    fetchData(ajaxType, {
                        periode: 'custom',
                        start_date: startDate.value,
                        end_date: endDate.value
                    }, updateFunction);
                }
            });
        }
    }

    function fetchData(ajaxType, params, updateFunction) {
        const urlParams = new URLSearchParams({ ajax: ajaxType, ...params });
        fetch(`{{ route('laporan.penagihan') }}?${urlParams}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => updateFunction(data))
        .catch(error => console.error('Error:', error));
    }

    // Setup filters
    setupPeriodeFilter('periodePenagihanKlien', 'customDatePenagihanKlien', 'startDatePenagihanKlien', 'endDatePenagihanKlien', 'penagihan_klien', function(data) {
        if (penagihanKlienChart) {
            penagihanKlienChart.data.labels = data.map(item => item.nama);
            penagihanKlienChart.data.datasets[0].data = data.map(item => item.total);
            penagihanKlienChart.update();
        }
    });

    setupPeriodeFilter('periodeTopKlien', 'customDateTopKlien', 'startDateTopKlien', 'endDateTopKlien', 'top_klien', function(data) {
        updateTopKlienTable(data);
    });

    // Simple filter for Piutang Pabrik (no custom date)
    document.getElementById('periodePiutangPabrik').addEventListener('change', function() {
        fetchData('piutang_pabrik', { periode: this.value }, function(data) {
            updatePiutangPabrikTable(data);
        });
    });

    // Year filter for Penagihan Per Bulan
    document.getElementById('tahunPenagihanPerBulan').addEventListener('change', function() {
        fetchData('penagihan_per_bulan', { tahun: this.value }, function(response) {
            penagihanPerBulanChart.data.datasets[0].data = response.data;
            penagihanPerBulanChart.update();
        });
    });

    // Year filter for Jumlah Invoice
    document.getElementById('tahunJumlahInvoice').addEventListener('change', function() {
        fetchData('jumlah_invoice_per_bulan', { tahun: this.value }, function(response) {
            jumlahInvoicePerBulanChart.data.datasets[0].data = response.data;
            jumlahInvoicePerBulanChart.update();
        });
    });

    // Update table functions
    function updateTopKlienTable(data) {
        const tbody = document.querySelector('#topKlienTable tbody');
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Belum ada data penagihan</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map((item, index) => `
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="px-4 py-3">
                    ${index < 3 ? `
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full ${index == 0 ? 'bg-yellow-100 text-yellow-600' : (index == 1 ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-600')} font-bold text-xs">
                            ${index + 1}
                        </span>
                    ` : `<span class="text-gray-600 font-medium">${index + 1}</span>`}
                </td>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800">${item.nama}</div>
                    ${item.alamat ? `<div class="text-xs text-gray-500">${item.alamat.substring(0, 40)}</div>` : ''}
                </td>
                <td class="px-4 py-3 text-right font-semibold text-blue-600">
                    Rp ${item.total.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </td>
            </tr>
        `).join('');
    }

    function updatePiutangPabrikTable(data) {
        const tbody = document.querySelector('#piutangPabrikTable tbody');
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
                        <p>Tidak ada piutang pabrik yang belum lunas</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map((item, index) => `
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="px-4 py-3">
                    ${index < 3 ? `
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full ${index == 0 ? 'bg-red-100 text-red-600' : (index == 1 ? 'bg-orange-100 text-orange-600' : 'bg-yellow-100 text-yellow-600')} font-bold text-xs">
                            ${index + 1}
                        </span>
                    ` : `<span class="text-gray-600 font-medium">${index + 1}</span>`}
                </td>
                <td class="px-4 py-3 font-medium text-gray-800">${item.nama}</td>
                <td class="px-4 py-3 text-gray-600">${item.alamat ? item.alamat.substring(0, 50) : '-'}</td>
                <td class="px-4 py-3 text-right font-semibold text-orange-600">
                    Rp ${item.total.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </td>
            </tr>
        `).join('');
    }
});
</script>
@endpush
