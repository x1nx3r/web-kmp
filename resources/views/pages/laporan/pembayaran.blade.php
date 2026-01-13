@extends('pages.laporan.base')

@section('report-content')

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    {{-- Total Pembayaran --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Total Pembayaran</p>
                <h3 class="text-2xl font-bold text-blue-600">
                    @if($totalPembayaran >= 1000000000)
                        Rp {{ number_format($totalPembayaran / 1000000000, 2, ',', '.') }} Miliar
                    @else
                        Rp {{ number_format($totalPembayaran / 1000000, 2, ',', '.') }} Juta
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Pembayaran Tahun Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-check text-green-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Pembayaran Tahun Ini ({{ date('Y') }})</p>
                <h3 class="text-2xl font-bold text-green-600">
                    @if($pembayaranTahunIni >= 1000000000)
                        Rp {{ number_format($pembayaranTahunIni / 1000000000, 2, ',', '.') }} Miliar
                    @else
                        Rp {{ number_format($pembayaranTahunIni / 1000000, 2, ',', '.') }} Juta
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Pembayaran Bulan Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-day text-purple-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Pembayaran Bulan Ini ({{ date('F Y') }})</p>
                <h3 class="text-2xl font-bold text-purple-600">
                    Rp {{ number_format($pembayaranBulanIni / 1000000, 2, ',', '.') }} Juta
                </h3>
            </div>
        </div>
    </div>
</div>

{{-- Secondary Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    {{-- Total Piutang Supplier --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Total Piutang Supplier</p>
                <h3 class="text-2xl font-bold text-orange-600">
                    @if($totalPiutangSupplier >= 1000000000)
                        Rp {{ number_format($totalPiutangSupplier / 1000000000, 2, ',', '.') }} Miliar
                    @else
                        Rp {{ number_format($totalPiutangSupplier / 1000000, 2, ',', '.') }} Juta
                    @endif
                </h3>
            </div>
        </div>
    </div>

    {{-- Jumlah Transaksi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-receipt text-teal-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Total Transaksi Pembayaran</p>
                <h3 class="text-2xl font-bold text-teal-600">
                    {{ number_format($jumlahTransaksi) }} <span class="text-base font-normal text-gray-500">transaksi</span>
                </h3>
            </div>
        </div>
    </div>
</div>

{{-- Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Pembayaran Per Supplier (Pie Chart) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                Pembayaran Per Supplier
            </h3>
            <div class="flex items-center space-x-2">
                <select id="periodePembayaranSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="semua" {{ $periode == 'semua' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="tahun_ini" {{ $periode == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periode == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
        </div>
        <div id="customDatePembayaranSupplier" class="mb-4 hidden">
            <div class="grid grid-cols-2 gap-2">
                <input type="date" id="startDatePembayaranSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <input type="date" id="endDatePembayaranSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="relative h-80">
            <canvas id="pembayaranSupplierChart"></canvas>
        </div>
    </div>

    {{-- Top 10 Supplier (Table) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-trophy text-yellow-600 mr-2"></i>
                Top 10 Supplier
            </h3>
            <div class="flex items-center space-x-2">
                <select id="periodeTopSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="semua" {{ $periodeSupplier == 'semua' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="tahun_ini" {{ $periodeSupplier == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periodeSupplier == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
        </div>
        <div id="customDateTopSupplier" class="mb-4 hidden">
            <div class="grid grid-cols-2 gap-2">
                <input type="date" id="startDateTopSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                <input type="date" id="endDateTopSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
        </div>
        <div class="overflow-y-auto max-h-80">
            <table class="w-full text-sm" id="topSupplierTable">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Rank</th>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Supplier</th>
                        <th class="text-right px-4 py-2 font-semibold text-gray-700">Total Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topSupplier as $index => $item)
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
                            <div class="font-medium text-gray-800">{{ $item->supplier_name }}</div>
                            @if($item->supplier_address)
                                <div class="text-xs text-gray-500">{{ Str::limit($item->supplier_address, 40) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-blue-600">
                            Rp {{ number_format($item->total, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Belum ada data pembayaran</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Second Row Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Pembayaran Per Bulan (Bar Chart) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                Pembayaran Per Bulan
            </h3>
            <div class="flex items-center space-x-2">
                <select id="tahunPembayaran" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="relative h-80">
            <canvas id="pembayaranPerBulanChart"></canvas>
        </div>
    </div>

    {{-- Jumlah Transaksi Per Bulan (Line Chart) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-chart-line text-purple-600 mr-2"></i>
                Jumlah Transaksi Per Bulan
            </h3>
            <div class="flex items-center space-x-2">
                <select id="tahunTransaksi" class="text-sm border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $selectedYearTransaksi == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="relative h-80">
            <canvas id="jumlahTransaksiChart"></canvas>
        </div>
    </div>
</div>

{{-- Piutang Supplier Section --}}
<div class="grid grid-cols-1 gap-6 mb-6">
    {{-- Top Piutang Supplier --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-truck text-orange-600 mr-2"></i>
                Top 10 Piutang Supplier
            </h3>
            <div class="flex items-center space-x-2">
                <select id="periodePiutangSupplier" class="text-sm border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="semua" {{ $periodePiutang == 'semua' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="tahun_ini" {{ $periodePiutang == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periodePiutang == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="piutangSupplierTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Rank</th>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Supplier</th>
                        <th class="text-left px-4 py-2 font-semibold text-gray-700">Alamat</th>
                        <th class="text-right px-4 py-2 font-semibold text-gray-700">Total Piutang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPiutangSupplier as $index => $item)
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
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $item->supplier ? $item->supplier->nama : 'Unknown' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $item->supplier ? Str::limit($item->supplier->alamat, 50) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-orange-600">
                            Rp {{ number_format($item->total, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
                            <p>Tidak ada piutang supplier yang belum lunas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial data from PHP
    let pembayaranSupplierData = @json($pembayaranSupplier->map(function($item) {
        return [
            'nama' => $item->supplier_name ?? 'Unknown',
            'total' => floatval($item->total ?? 0)
        ];
    })->filter(function($item) {
        return $item['total'] > 0;
    })->values());

    let pembayaranPerBulanData = @json($pembayaranPerBulan);
    let jumlahTransaksiData = @json($jumlahTransaksiPerBulan);

    const bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    // Color palette
    const colors = [
        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'
    ];

    // Pembayaran Per Supplier Chart (Pie)
    const pembayaranSupplierCtx = document.getElementById('pembayaranSupplierChart').getContext('2d');
    const pembayaranSupplierChart = new Chart(pembayaranSupplierCtx, {
        type: 'pie',
        data: {
            labels: pembayaranSupplierData.map(item => item.nama),
            datasets: [{
                data: pembayaranSupplierData.map(item => item.total),
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 10,
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: Rp ${value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Pembayaran Per Bulan Chart (Bar)
    const pembayaranPerBulanCtx = document.getElementById('pembayaranPerBulanChart').getContext('2d');
    const pembayaranPerBulanChart = new Chart(pembayaranPerBulanCtx, {
        type: 'bar',
        data: {
            labels: bulanLabels,
            datasets: [{
                label: 'Pembayaran',
                data: pembayaranPerBulanData,
                backgroundColor: '#3b82f6',
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
                            return 'Rp ' + context.raw.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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
                                return 'Rp ' + (value / 1000000000).toFixed(1) + ' M';
                            }
                            return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                        }
                    }
                }
            }
        }
    });

    // Jumlah Transaksi Per Bulan Chart (Line)
    const jumlahTransaksiCtx = document.getElementById('jumlahTransaksiChart').getContext('2d');
    const jumlahTransaksiChart = new Chart(jumlahTransaksiCtx, {
        type: 'line',
        data: {
            labels: bulanLabels,
            datasets: [{
                label: 'Jumlah Transaksi',
                data: jumlahTransaksiData,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#8b5cf6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // Filter handlers
    function setupPeriodeFilter(selectId, customDateId, startId, endId, ajaxType, updateFunction) {
        const select = document.getElementById(selectId);
        const customDate = document.getElementById(customDateId);
        const startDate = document.getElementById(startId);
        const endDate = document.getElementById(endId);

        select.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDate.classList.remove('hidden');
            } else {
                customDate.classList.add('hidden');
                fetchData(ajaxType, { periode: this.value }, updateFunction);
            }
        });

        if (startDate && endDate) {
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
        fetch(`{{ route('laporan.pembayaran') }}?${urlParams}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => updateFunction(data))
        .catch(error => console.error('Error:', error));
    }

    // Setup filters
    setupPeriodeFilter('periodePembayaranSupplier', 'customDatePembayaranSupplier', 'startDatePembayaranSupplier', 'endDatePembayaranSupplier', 'pembayaran_supplier', function(data) {
        pembayaranSupplierChart.data.labels = data.map(item => item.nama);
        pembayaranSupplierChart.data.datasets[0].data = data.map(item => item.total);
        pembayaranSupplierChart.update();
    });

    setupPeriodeFilter('periodeTopSupplier', 'customDateTopSupplier', 'startDateTopSupplier', 'endDateTopSupplier', 'top_supplier', function(data) {
        updateTopSupplierTable(data);
    });

    // Simple filter for Piutang Supplier (no custom date)
    document.getElementById('periodePiutangSupplier').addEventListener('change', function() {
        fetchData('piutang_supplier', { periode: this.value }, function(data) {
            updatePiutangSupplierTable(data);
        });
    });

    // Year filter for Pembayaran Per Bulan
    document.getElementById('tahunPembayaran').addEventListener('change', function() {
        fetchData('pembayaran_per_bulan', { tahun: this.value }, function(response) {
            pembayaranPerBulanChart.data.datasets[0].data = response.data;
            pembayaranPerBulanChart.update();
        });
    });

    // Year filter for Jumlah Transaksi
    document.getElementById('tahunTransaksi').addEventListener('change', function() {
        fetchData('jumlah_transaksi_per_bulan', { tahun: this.value }, function(response) {
            jumlahTransaksiChart.data.datasets[0].data = response.data;
            jumlahTransaksiChart.update();
        });
    });

    // Update table functions
    function updateTopSupplierTable(data) {
        const tbody = document.querySelector('#topSupplierTable tbody');
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Belum ada data pembayaran</p>
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

    function updatePiutangSupplierTable(data) {
        const tbody = document.querySelector('#piutangSupplierTable tbody');
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
                        <p>Tidak ada piutang supplier yang belum lunas</p>
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
