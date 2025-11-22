@extends('pages.laporan.base')

@section('report-content')

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    {{-- Total Penagihan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-file-invoice-dollar text-purple-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Total Penagihan</p>
                <h3 class="text-2xl font-bold text-purple-600">
                    @if($totalPenagihan >= 1000000000)
                        Rp {{ number_format($totalPenagihan / 1000000000, 2, ',', '.') }} Miliar
                    @else
                        Rp {{ number_format($totalPenagihan / 1000000, 2, ',', '.') }} Juta
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Penagihan Tahun Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Penagihan Tahun Ini</p>
                <h3 class="text-2xl font-bold text-blue-600">
                    @if($penagihanTahunIni >= 1000000000)
                        Rp {{ number_format($penagihanTahunIni / 1000000000, 2, ',', '.') }} Miliar
                    @else
                        Rp {{ number_format($penagihanTahunIni / 1000000, 2, ',', '.') }} Juta
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Penagihan Bulan Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-day text-green-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Penagihan Bulan Ini</p>
                <h3 class="text-2xl font-bold text-green-600">
                    Rp {{ number_format($penagihanBulanIni / 1000000, 2, ',', '.') }} Juta
                </h3>
            </div>
        </div>
    </div>

    {{-- Total Piutang Supplier --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-truck text-orange-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Piutang Supplier</p>
                <h3 class="text-2xl font-bold text-orange-600">
                    Rp {{ number_format($totalPiutangSupplier / 1000000, 2, ',', '.') }} Juta
                </h3>
            </div>
        </div>
    </div>

    {{-- Total Piutang Pabrik --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-industry text-red-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Piutang Pabrik</p>
                <h3 class="text-2xl font-bold text-red-600">
                    Rp {{ number_format($totalPiutangPabrik / 1000000, 2, ',', '.') }} Juta
                </h3>
            </div>
        </div>
    </div>
</div>

{{-- Charts Section --}}
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
        <div class="relative h-80">
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
        <div class="overflow-y-auto max-h-80">
            <table class="w-full text-sm" id="topKlienTable">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Rank</th>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Klien</th>
                        <th class="text-right px-4 py-2 font-semibold text-gray-700">Total Penagihan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topKlien as $index => $klien)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $index < 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600' }} font-semibold text-xs">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $klien->customer_name }}</p>
                                    @if($klien->customer_address)
                                        <p class="text-xs text-gray-500">{{ Str::limit($klien->customer_address, 30) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-600">
                                Rp {{ number_format($klien->total / 1000000, 2, ',', '.') }} Jt
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Nilai Penagihan Per Bulan (Line Chart) --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-chart-line text-green-600 mr-2"></i>
            Nilai Penagihan Per Bulan
        </h3>
        <select id="tahunPenagihanPerBulan" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            @foreach($availableYears as $year)
                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>
    </div>
    <div class="relative h-80">
        <canvas id="penagihanPerBulanChart"></canvas>
    </div>
</div>

{{-- Jumlah Invoice Per Bulan (Bar Chart) --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-chart-bar text-indigo-600 mr-2"></i>
            Jumlah Invoice Per Bulan
        </h3>
        <select id="tahunJumlahInvoice" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            @foreach($availableYears as $year)
                <option value="{{ $year }}" {{ $year == $selectedYearInvoice ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>
    </div>
    <div class="relative h-80">
        <canvas id="jumlahInvoicePerBulanChart"></canvas>
    </div>
</div>

{{-- Piutang Section --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Top 10 Piutang Supplier --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-truck text-orange-600 mr-2"></i>
                Top 10 Piutang Supplier
            </h3>
            <div class="flex items-center space-x-2">
                <select id="periodePiutangSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="semua" {{ $periodePiutangSupplier == 'semua' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="tahun_ini" {{ $periodePiutangSupplier == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periodePiutangSupplier == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
        </div>
        <div id="customDatePiutangSupplier" class="mb-4 hidden">
            <div class="grid grid-cols-2 gap-2">
                <input type="date" id="startDatePiutangSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                <input type="date" id="endDatePiutangSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>
        <div class="overflow-y-auto max-h-80">
            <table class="w-full text-sm" id="piutangSupplierTable">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Rank</th>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Supplier</th>
                        <th class="text-right px-4 py-2 font-semibold text-gray-700">Total Piutang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPiutangSupplier as $index => $piutang)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $index < 3 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600' }} font-semibold text-xs">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $piutang->supplier ? $piutang->supplier->nama : 'Unknown' }}</p>
                                    @if($piutang->supplier && $piutang->supplier->alamat)
                                        <p class="text-xs text-gray-500">{{ Str::limit($piutang->supplier->alamat, 30) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-orange-600">
                                Rp {{ number_format($piutang->total / 1000000, 2, ',', '.') }} Jt
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top 10 Piutang Pabrik --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-industry text-red-600 mr-2"></i>
                Top 10 Piutang Pabrik
            </h3>
            <div class="flex items-center space-x-2">
                <select id="periodePiutangPabrik" class="text-sm border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                    <option value="semua" {{ $periodePiutangPabrik == 'semua' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="tahun_ini" {{ $periodePiutangPabrik == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periodePiutangPabrik == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
        </div>
        <div id="customDatePiutangPabrik" class="mb-4 hidden">
            <div class="grid grid-cols-2 gap-2">
                <input type="date" id="startDatePiutangPabrik" class="text-sm border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                <input type="date" id="endDatePiutangPabrik" class="text-sm border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
            </div>
        </div>
        <div class="overflow-y-auto max-h-80">
            <table class="w-full text-sm" id="piutangPabrikTable">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Rank</th>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Pabrik/Klien</th>
                        <th class="text-right px-4 py-2 font-semibold text-gray-700">Total Piutang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPiutangPabrik as $index => $piutang)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $index < 3 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }} font-semibold text-xs">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $piutang->klien ? $piutang->klien->nama : 'Unknown' }}</p>
                                    @if($piutang->klien && $piutang->klien->alamat)
                                        <p class="text-xs text-gray-500">{{ Str::limit($piutang->klien->alamat, 30) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">
                                Rp {{ number_format($piutang->total / 1000000, 2, ',', '.') }} Jt
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Charts
    let penagihanKlienChart, penagihanPerBulanChart, jumlahInvoicePerBulanChart;

    // Chart Colors
    const chartColors = [
        'rgba(147, 51, 234, 0.8)', // purple
        'rgba(59, 130, 246, 0.8)', // blue
        'rgba(34, 197, 94, 0.8)', // green
        'rgba(249, 115, 22, 0.8)', // orange
        'rgba(239, 68, 68, 0.8)', // red
        'rgba(168, 85, 247, 0.8)', // violet
        'rgba(14, 165, 233, 0.8)', // sky
        'rgba(132, 204, 22, 0.8)', // lime
        'rgba(251, 146, 60, 0.8)', // amber
        'rgba(244, 63, 94, 0.8)', // rose
    ];

    // Penagihan Per Klien Chart (Pie)
    const penagihanKlienData = @json($penagihanKlien);
    const ctxPenagihanKlien = document.getElementById('penagihanKlienChart');
    penagihanKlienChart = new Chart(ctxPenagihanKlien, {
        type: 'pie',
        data: {
            labels: penagihanKlienData.map(item => item.customer_name),
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
                        padding: 10,
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label} (${percentage}%)`,
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
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `Rp ${(value / 1000000).toFixed(2)} Jt (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 12 },
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return percentage > 5 ? percentage + '%' : '';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // Penagihan Per Bulan Chart (Line)
    const penagihanPerBulanData = @json($penagihanPerBulan);
    const ctxPenagihanPerBulan = document.getElementById('penagihanPerBulanChart');
    penagihanPerBulanChart = new Chart(ctxPenagihanPerBulan, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Nilai Penagihan',
                data: penagihanPerBulanData,
                borderColor: 'rgba(34, 197, 94, 1)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgba(34, 197, 94, 1)'
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
                            return `Rp ${(context.parsed.y / 1000000).toFixed(2)} Juta`;
                        }
                    }
                },
                datalabels: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                        }
                    }
                }
            }
        }
    });

    // Jumlah Invoice Per Bulan Chart (Bar)
    const jumlahInvoicePerBulanData = @json($jumlahInvoicePerBulan);
    const ctxJumlahInvoice = document.getElementById('jumlahInvoicePerBulanChart');
    jumlahInvoicePerBulanChart = new Chart(ctxJumlahInvoice, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Jumlah Invoice',
                data: jumlahInvoicePerBulanData,
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1
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
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    color: '#4B5563',
                    font: { weight: 'bold', size: 11 },
                    formatter: (value) => value > 0 ? value : ''
                }
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
        },
        plugins: [ChartDataLabels]
    });

    // Event Listeners for Dynamic Filters

    // Penagihan Per Klien Filter
    document.getElementById('periodePenagihanKlien').addEventListener('change', function() {
        const customDiv = document.getElementById('customDatePenagihanKlien');
        if (this.value === 'custom') {
            customDiv.classList.remove('hidden');
        } else {
            customDiv.classList.add('hidden');
            updatePenagihanKlien(this.value);
        }
    });

    document.getElementById('startDatePenagihanKlien').addEventListener('change', updatePenagihanKlienCustom);
    document.getElementById('endDatePenagihanKlien').addEventListener('change', updatePenagihanKlienCustom);

    function updatePenagihanKlienCustom() {
        const startDate = document.getElementById('startDatePenagihanKlien').value;
        const endDate = document.getElementById('endDatePenagihanKlien').value;
        if (startDate && endDate) {
            updatePenagihanKlien('custom', startDate, endDate);
        }
    }

    function updatePenagihanKlien(periode, startDate = null, endDate = null) {
        const params = new URLSearchParams({ ajax: 'penagihan_klien', periode });
        if (startDate && endDate) {
            params.append('start_date', startDate);
            params.append('end_date', endDate);
        }

        fetch(`?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                penagihanKlienChart.data.labels = data.map(item => item.nama);
                penagihanKlienChart.data.datasets[0].data = data.map(item => item.total);
                penagihanKlienChart.update();
            });
    }

    // Top Klien Filter
    document.getElementById('periodeTopKlien').addEventListener('change', function() {
        const customDiv = document.getElementById('customDateTopKlien');
        if (this.value === 'custom') {
            customDiv.classList.remove('hidden');
        } else {
            customDiv.classList.add('hidden');
            updateTopKlien(this.value);
        }
    });

    document.getElementById('startDateTopKlien').addEventListener('change', updateTopKlienCustom);
    document.getElementById('endDateTopKlien').addEventListener('change', updateTopKlienCustom);

    function updateTopKlienCustom() {
        const startDate = document.getElementById('startDateTopKlien').value;
        const endDate = document.getElementById('endDateTopKlien').value;
        if (startDate && endDate) {
            updateTopKlien('custom', startDate, endDate);
        }
    }

    function updateTopKlien(periode, startDate = null, endDate = null) {
        const params = new URLSearchParams({ ajax: 'top_klien', periode });
        if (startDate && endDate) {
            params.append('start_date', startDate);
            params.append('end_date', endDate);
        }

        fetch(`?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#topKlienTable tbody');
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>';
                } else {
                    tbody.innerHTML = data.map((item, index) => `
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full ${index < 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600'} font-semibold text-xs">
                                    ${index + 1}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">${item.nama}</p>
                                    ${item.alamat ? `<p class="text-xs text-gray-500">${item.alamat.substring(0, 30)}${item.alamat.length > 30 ? '...' : ''}</p>` : ''}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-600">
                                Rp ${(item.total / 1000000).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')} Jt
                            </td>
                        </tr>
                    `).join('');
                }
            });
    }

    // Tahun Penagihan Per Bulan
    document.getElementById('tahunPenagihanPerBulan').addEventListener('change', function() {
        fetch(`?ajax=penagihan_per_bulan&tahun=${this.value}`)
            .then(response => response.json())
            .then(result => {
                penagihanPerBulanChart.data.datasets[0].data = result.data;
                penagihanPerBulanChart.update();
            });
    });

    // Tahun Jumlah Invoice
    document.getElementById('tahunJumlahInvoice').addEventListener('change', function() {
        fetch(`?ajax=jumlah_invoice_per_bulan&tahun=${this.value}`)
            .then(response => response.json())
            .then(result => {
                jumlahInvoicePerBulanChart.data.datasets[0].data = result.data;
                jumlahInvoicePerBulanChart.update();
            });
    });

    // Piutang Supplier Filter
    document.getElementById('periodePiutangSupplier').addEventListener('change', function() {
        const customDiv = document.getElementById('customDatePiutangSupplier');
        if (this.value === 'custom') {
            customDiv.classList.remove('hidden');
        } else {
            customDiv.classList.add('hidden');
            updatePiutangSupplier(this.value);
        }
    });

    document.getElementById('startDatePiutangSupplier').addEventListener('change', updatePiutangSupplierCustom);
    document.getElementById('endDatePiutangSupplier').addEventListener('change', updatePiutangSupplierCustom);

    function updatePiutangSupplierCustom() {
        const startDate = document.getElementById('startDatePiutangSupplier').value;
        const endDate = document.getElementById('endDatePiutangSupplier').value;
        if (startDate && endDate) {
            updatePiutangSupplier('custom', startDate, endDate);
        }
    }

    function updatePiutangSupplier(periode, startDate = null, endDate = null) {
        const params = new URLSearchParams({ ajax: 'piutang_supplier', periode });
        if (startDate && endDate) {
            params.append('start_date_piutang_supplier', startDate);
            params.append('end_date_piutang_supplier', endDate);
        }

        fetch(`?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#piutangSupplierTable tbody');
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>';
                } else {
                    tbody.innerHTML = data.map((item, index) => `
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full ${index < 3 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600'} font-semibold text-xs">
                                    ${index + 1}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">${item.nama}</p>
                                    ${item.alamat ? `<p class="text-xs text-gray-500">${item.alamat.substring(0, 30)}${item.alamat.length > 30 ? '...' : ''}</p>` : ''}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-orange-600">
                                Rp ${(item.total / 1000000).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')} Jt
                            </td>
                        </tr>
                    `).join('');
                }
            });
    }

    // Piutang Pabrik Filter
    document.getElementById('periodePiutangPabrik').addEventListener('change', function() {
        const customDiv = document.getElementById('customDatePiutangPabrik');
        if (this.value === 'custom') {
            customDiv.classList.remove('hidden');
        } else {
            customDiv.classList.add('hidden');
            updatePiutangPabrik(this.value);
        }
    });

    document.getElementById('startDatePiutangPabrik').addEventListener('change', updatePiutangPabrikCustom);
    document.getElementById('endDatePiutangPabrik').addEventListener('change', updatePiutangPabrikCustom);

    function updatePiutangPabrikCustom() {
        const startDate = document.getElementById('startDatePiutangPabrik').value;
        const endDate = document.getElementById('endDatePiutangPabrik').value;
        if (startDate && endDate) {
            updatePiutangPabrik('custom', startDate, endDate);
        }
    }

    function updatePiutangPabrik(periode, startDate = null, endDate = null) {
        const params = new URLSearchParams({ ajax: 'piutang_pabrik', periode });
        if (startDate && endDate) {
            params.append('start_date_piutang_pabrik', startDate);
            params.append('end_date_piutang_pabrik', endDate);
        }

        fetch(`?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#piutangPabrikTable tbody');
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>';
                } else {
                    tbody.innerHTML = data.map((item, index) => `
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full ${index < 3 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'} font-semibold text-xs">
                                    ${index + 1}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">${item.nama}</p>
                                    ${item.alamat ? `<p class="text-xs text-gray-500">${item.alamat.substring(0, 30)}${item.alamat.length > 30 ? '...' : ''}</p>` : ''}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">
                                Rp ${(item.total / 1000000).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')} Jt
                            </td>
                        </tr>
                    `).join('');
                }
            });
    }
});
</script>
@endpush
