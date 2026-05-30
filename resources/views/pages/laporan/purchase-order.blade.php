@extends('pages.laporan.base')

@section('report-content')

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Total Outstanding</p>
        <h3 class="text-lg font-bold text-red-600">
            @if($totalOutstanding >= 1000000000)
                Rp {{ number_format($totalOutstanding / 1000000000, 2, ',', '.') }} M
            @elseif($totalOutstanding >= 1000000)
                Rp {{ number_format($totalOutstanding / 1000000, 2, ',', '.') }} Jt
            @else
                Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
            @endif
        </h3>
        <p class="text-xs text-gray-400 mt-0.5">Dikonfirmasi & Diproses</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Qty Outstanding</p>
        <h3 class="text-lg font-bold text-orange-500">{{ number_format($totalQtyOutstanding, 0, ',', '.') }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">Total Quantity</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">PO Berjalan</p>
        <h3 class="text-lg font-bold text-blue-600">{{ number_format($poBerjalan, 0, ',', '.') }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">Dikonfirmasi & Diproses</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Rata-rata per PO</p>
        <h3 class="text-lg font-bold text-purple-600">
            @if($avgNilaiPerPO >= 1000000000)
                Rp {{ number_format($avgNilaiPerPO / 1000000000, 2, ',', '.') }} M
            @elseif($avgNilaiPerPO >= 1000000)
                Rp {{ number_format($avgNilaiPerPO / 1000000, 2, ',', '.') }} Jt
            @else
                Rp {{ number_format($avgNilaiPerPO, 0, ',', '.') }}
            @endif
        </h3>
        <p class="text-xs text-gray-400 mt-0.5">Average Value</p>
    </div>
</div>

{{-- Nilai Outstanding & PO By Status --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-5">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Nilai Outstanding</h3>
                <p class="text-xs text-gray-400">Distribusi nilai outstanding per PO</p>
            </div>
            @if($outstandingChartData->count() > 0)
                <button onclick="openOutstandingModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium">
                    <i class="fas fa-list mr-1"></i>Detail
                </button>
            @endif
        </div>
        <div style="height: 260px;">
            @if($outstandingChartData->count() > 0)
                <canvas id="chartOutstanding"></canvas>
            @else
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <i class="fas fa-check-circle text-3xl mb-2"></i>
                    <p class="text-sm">Semua order sudah selesai</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">PO Berdasarkan Status</h3>
                <p class="text-xs text-gray-400">Distribusi status purchase order</p>
            </div>
            @if($poByStatus->count() > 0)
                <button onclick="openStatusModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium">
                    <i class="fas fa-list mr-1"></i>Detail
                </button>
            @endif
        </div>
        <div style="height: 260px;">
            @if($poByStatus->count() > 0)
                <canvas id="chartPOByStatus"></canvas>
            @else
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <i class="fas fa-chart-pie text-3xl mb-2"></i>
                    <p class="text-sm">Tidak ada data status</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white rounded-lg border border-gray-200 p-3 mb-3">
    <form method="GET" action="{{ route('laporan.po') }}">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Filter Periode</label>
                <select name="periode" id="periodeFilter" class="rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all" {{ $periode == 'all' ? 'selected' : '' }}>Semua Data</option>
                    <option value="tahun_ini" {{ $periode == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="bulan_ini" {{ $periode == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ $periode == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div id="startDateDiv" class="{{ $periode == 'custom' ? '' : 'hidden' }}">
                <label class="block text-xs font-medium text-gray-600 mb-1">Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div id="endDateDiv" class="{{ $periode == 'custom' ? '' : 'hidden' }}">
                <label class="block text-xs font-medium text-gray-600 mb-1">Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded">
                <i class="fas fa-filter mr-1"></i>Filter
            </button>
        </div>
    </form>
</div>

{{-- PO By Client & Order Winners --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-5">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">PO Berdasarkan Klien</h3>
                <p class="text-xs text-gray-400">Distribusi nilai PO per klien</p>
            </div>
            @if($poByClient->count() > 0)
                <button onclick="openClientModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium">
                    <i class="fas fa-list mr-1"></i>Detail
                </button>
            @endif
        </div>
        <div style="height: 260px;">
            @if($poByClient->count() > 0)
                <canvas id="chartPOByClient"></canvas>
            @else
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <i class="fas fa-chart-pie text-3xl mb-2"></i>
                    <p class="text-sm">Tidak ada data PO</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Top 10 Order Winners</h3>
                <p class="text-xs text-gray-400">Nilai PO per marketing ({{ $orderWinners->count() }} data)</p>
            </div>
            @if($orderWinners->count() > 0)
                <button onclick="openOrderWinnerModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium">
                    <i class="fas fa-list mr-1"></i>Detail
                </button>
            @endif
        </div>
        <div style="height: 260px;">
            @if($orderWinners->count() > 0)
                <canvas id="chartOrderWinners"></canvas>
            @else
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <i class="fas fa-inbox text-3xl mb-2"></i>
                    <p class="text-sm">Tidak ada data order winner</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Trend & Priority --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-5">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Trend PO 12 Bulan Terakhir</h3>
                <p class="text-xs text-gray-400">Total nilai PO per bulan</p>
            </div>
            <button onclick="openPOTrendModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium">
                <i class="fas fa-list mr-1"></i>Detail
            </button>
        </div>
        <div style="height: 220px;">
            <canvas id="chartPOTrend"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">PO Berdasarkan Prioritas</h3>
                <p class="text-xs text-gray-400">Distribusi prioritas purchase order</p>
            </div>
            <button onclick="openPOPriorityModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium">
                <i class="fas fa-list mr-1"></i>Detail
            </button>
        </div>
        <div style="height: 220px;">
            <canvas id="chartPOByPriority"></canvas>
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
document.getElementById('periodeFilter').addEventListener('change', function() {
    const show = this.value === 'custom';
    document.getElementById('startDateDiv').classList.toggle('hidden', !show);
    document.getElementById('endDateDiv').classList.toggle('hidden', !show);
});

const chartColors = [
    '#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6',
    '#EC4899','#06B6D4','#F97316','#84CC16','#6366F1'
];

function fmtRp(value) {
    if (value >= 1000000000) return 'Rp ' + (value/1000000000).toFixed(2) + ' M';
    if (value >= 1000000)    return 'Rp ' + (value/1000000).toFixed(2) + ' Jt';
    return 'Rp ' + value.toLocaleString('id-ID', {minimumFractionDigits: 2});
}

// PO By Client
@if($poByClient->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const percentages = @json($poByClient->pluck('percentage')->toArray());
    new Chart(document.getElementById('chartPOByClient').getContext('2d'), {
        type: 'bar',
        data: {
            labels: [@foreach($poByClient as $item)'{{ $item->klien_nama }}',@endforeach],
            datasets: [{
                data: [@foreach($poByClient as $item){{ $item->total_nilai }},@endforeach],
                backgroundColor: chartColors,
                borderRadius: 3
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: { callbacks: { label: ctx => fmtRp(ctx.parsed.y) + ' (' + percentages[ctx.dataIndex].toFixed(1) + '%)' } }
            },
            scales: {
                x: { ticks: { maxRotation: 45, font: { size: 10 } }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { callback: fmtRp, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }
            }
        }
    });
});
@endif

// PO By Status
@if($poByStatus->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('chartPOByStatus').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: [@foreach($poByStatus as $item)'{{ ucfirst($item->status) }}',@endforeach],
            datasets: [{ data: [@foreach($poByStatus as $item){{ $item->total }},@endforeach], backgroundColor: chartColors, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { padding: 12, font: { size: 11 }, boxWidth: 12 } },
                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed + ' PO' } },
                datalabels: { color: '#fff', font: { weight: 'bold', size: 12 }, formatter: v => v }
            }
        },
        plugins: [ChartDataLabels]
    });
});
@endif

// PO Trend
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('chartPOTrend').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($monthLabels),
            datasets: [{
                data: @json(array_column($poTrendByMonth, 'total_nilai')),
                borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,0.08)',
                borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => fmtRp(ctx.parsed.y) } } },
            scales: { y: { beginAtZero: true, ticks: { callback: fmtRp, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { ticks: { font: { size: 10 } }, grid: { display: false } } }
        }
    });
});

// PO By Priority
@if($poByPriority->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const pColors = { 'tinggi': '#EF4444', 'sedang': '#F59E0B', 'rendah': '#6B7280' };
    new Chart(document.getElementById('chartPOByPriority').getContext('2d'), {
        type: 'bar',
        data: {
            labels: [@foreach($poByPriority as $item)'{{ ucfirst($item->priority) }}',@endforeach],
            datasets: [{
                data: [@foreach($poByPriority as $item){{ $item->total }},@endforeach],
                backgroundColor: [@foreach($poByPriority as $item)pColors['{{ $item->priority }}'],@endforeach],
                borderRadius: 3
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.parsed.y + ' PO' } } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
        }
    });
});
@endif

// Outstanding Chart
@if($outstandingChartData->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const klienData = @json($outstandingChartData->pluck('klien_nama')->toArray());
    const orderStatusData = @json($outstandingChartData->pluck('order_status')->toArray());
    const namaMaterialData = @json($outstandingChartData->pluck('nama_material')->toArray());
    new Chart(document.getElementById('chartOutstanding').getContext('2d'), {
        type: 'bar',
        data: {
            labels: [@foreach($outstandingChartData as $item)'{{ $item->display_name }}',@endforeach],
            datasets: [{
                data: [@foreach($outstandingChartData as $item){{ $item->total_nilai }},@endforeach],
                backgroundColor: chartColors, borderRadius: 3
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: {
                    callbacks: {
                        title: ctx => 'PO: ' + ctx[0].label,
                        label: ctx => {
                            const v = ctx.parsed.y;
                            const pct = (v / {{ $totalOutstandingChart }} * 100).toFixed(1);
                            return ['Klien: '+klienData[ctx.dataIndex], 'Status: '+orderStatusData[ctx.dataIndex], fmtRp(v)+' ('+pct+'%)', 'Material: '+(namaMaterialData[ctx.dataIndex]||'N/A')];
                        }
                    }
                }
            },
            scales: {
                x: { ticks: { maxRotation: 45, font: { size: 10 } }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { callback: fmtRp, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }
            }
        }
    });
});
@endif

// Order Winners
@if($orderWinners->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const percentages = @json($orderWinners->pluck('percentage')->toArray());
    new Chart(document.getElementById('chartOrderWinners').getContext('2d'), {
        type: 'pie',
        data: {
            labels: [@foreach($orderWinners as $w)'{{ $w->marketing_nama }}',@endforeach],
            datasets: [{ data: [@foreach($orderWinners as $w){{ $w->total_nilai }},@endforeach], backgroundColor: chartColors, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { padding: 10, font: { size: 10 }, boxWidth: 12 } },
                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + fmtRp(ctx.parsed) + ' (' + percentages[ctx.dataIndex].toFixed(1) + '%)' } },
                datalabels: { color: '#fff', font: { weight: 'bold', size: 11 }, formatter: (v, ctx) => { const p = percentages[ctx.dataIndex]; return p > 5 ? p.toFixed(1)+'%' : ''; } }
            }
        },
        plugins: [ChartDataLabels]
    });
});
@endif

// ===== Modal Functions =====
function openOutstandingModal() { document.getElementById('outstandingModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeOutstandingModal() { document.getElementById('outstandingModal').classList.add('hidden'); document.body.style.overflow = ''; }
function openStatusModal() { document.getElementById('statusModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeStatusModal() { document.getElementById('statusModal').classList.add('hidden'); document.body.style.overflow = ''; }
function openClientModal() { document.getElementById('clientModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeClientModal() { document.getElementById('clientModal').classList.add('hidden'); document.body.style.overflow = ''; }
function closePOTrendModal() { document.getElementById('poTrendModal').classList.add('hidden'); document.body.style.overflow = ''; if (window.poTrendModalChart) { window.poTrendModalChart.destroy(); window.poTrendModalChart = null; } }
function closePOPriorityModal() { document.getElementById('poPriorityModal').classList.add('hidden'); document.body.style.overflow = ''; if (window.poPriorityModalChart) { window.poPriorityModalChart.destroy(); window.poPriorityModalChart = null; } }
function closeOrderWinnerModal() { document.getElementById('orderWinnerModal').classList.add('hidden'); document.body.style.overflow = ''; }

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

function showOutstandingToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-[9999] px-5 py-3 rounded shadow text-sm font-medium ${type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

function closePabrik(orderId, poNumber) {
    if (!confirm(`Yakin Closed Pabrik "${poNumber}"?\n\nStatus akan menjadi Selesai.`)) return;
    fetch(`/laporan/purchase-order/close-pabrik/${orderId}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
        .then(r => r.json()).then(data => { showOutstandingToast(data.message, data.success ? 'success' : 'error'); if (data.success) setTimeout(() => window.location.reload(), 1500); })
        .catch(() => showOutstandingToast('Terjadi kesalahan.', 'error'));
}

function closeInternal(orderId, poNumber) {
    if (!confirm(`Yakin Closed Internal "${poNumber}"?`)) return;
    fetch(`/laporan/purchase-order/close-internal/${orderId}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
        .then(r => r.json()).then(data => { showOutstandingToast(data.message, data.success ? 'success' : 'error'); if (data.success) setTimeout(() => window.location.reload(), 1500); })
        .catch(() => showOutstandingToast('Terjadi kesalahan.', 'error'));
}

function reopenOrder(orderId, poNumber) {
    if (!confirm(`Kembalikan "${poNumber}" ke Diproses?`)) return;
    fetch(`/laporan/purchase-order/reopen/${orderId}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
        .then(r => r.json()).then(data => { showOutstandingToast(data.message, data.success ? 'success' : 'error'); if (data.success) setTimeout(() => window.location.reload(), 1500); })
        .catch(() => showOutstandingToast('Terjadi kesalahan.', 'error'));
}

function openOrderWinnerModal() {
    const periode = '{{ $periode }}', startDate = '{{ $startDate }}', endDate = '{{ $endDate }}';
    fetch(`{{ route('laporan.po.orderWinnerDetails') }}?periode=${periode}&start_date=${startDate}&end_date=${endDate}`)
        .then(r => r.json()).then(data => { displayOrderWinnerDetails(data); document.getElementById('orderWinnerModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; })
        .catch(e => console.error(e));
}

function openPOTrendModal() {
    document.getElementById('poTrendModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        if (window.poTrendModalChart) window.poTrendModalChart.destroy();
        window.poTrendModalChart = new Chart(document.getElementById('chartPOTrendModal').getContext('2d'), {
            type: 'line',
            data: { labels: @json($monthLabels), datasets: [{ data: @json(array_column($poTrendByMonth, 'total_nilai')), borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,0.08)', borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => fmtRp(ctx.parsed.y) } } }, scales: { y: { beginAtZero: true, ticks: { callback: fmtRp } }, x: { grid: { display: false } } } }
        });
    }, 80);
}

function openPOPriorityModal() {
    document.getElementById('poPriorityModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        if (window.poPriorityModalChart) window.poPriorityModalChart.destroy();
        const pColors = { 'tinggi': '#EF4444', 'sedang': '#F59E0B', 'rendah': '#6B7280' };
        window.poPriorityModalChart = new Chart(document.getElementById('chartPOPriorityModal').getContext('2d'), {
            type: 'bar',
            data: {
                labels: [@foreach($poByPriority as $item)'{{ ucfirst($item->priority) }}',@endforeach],
                datasets: [{ data: [@foreach($poByPriority as $item){{ $item->nilai }},@endforeach], backgroundColor: [@foreach($poByPriority as $item)pColors['{{ $item->priority }}'],@endforeach], borderRadius: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => fmtRp(ctx.parsed.y) } } }, scales: { y: { beginAtZero: true, ticks: { callback: fmtRp } }, x: { grid: { display: false } } } }
        });
    }, 80);
}

function displayOrderWinnerDetails(data) {
    let totalOverall = 0, totalPOOverall = 0, html = '';
    data.forEach(m => {
        totalOverall += m.total_nilai; totalPOOverall += m.total_po;
        html += `<div class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white p-3 flex justify-between items-center">
                <div><p class="font-semibold">${m.marketing_nama}</p><p class="text-xs opacity-80">${m.total_po} PO</p></div>
                <p class="font-bold">Rp ${(m.total_nilai/1000000).toFixed(1)} Jt</p>
            </div>`;
        m.kliens.forEach(k => {
            html += `<div class="bg-gray-50 px-4 py-2 border-t border-gray-200 flex justify-between items-center">
                <div><p class="text-sm font-medium text-gray-800">${k.klien_nama}</p><p class="text-xs text-gray-500">${k.total_po} PO</p></div>
                <p class="text-sm font-semibold">Rp ${(k.total_nilai/1000000).toFixed(1)} Jt</p></div>`;
            k.cabangs.forEach(c => {
                html += `<div class="px-6 py-2 border-t border-gray-100">
                    <div class="flex justify-between items-center mb-2">
                        <p class="text-xs font-medium text-gray-700"><i class="fas fa-map-marker-alt text-orange-400 mr-1"></i>${c.cabang_nama} <span class="text-gray-400">(${c.total_po} PO)</span></p>
                        <p class="text-xs font-semibold">Rp ${(c.total_nilai/1000000).toFixed(1)} Jt</p>
                    </div>
                    <table class="min-w-full text-xs"><thead class="bg-gray-100"><tr><th class="px-2 py-1 text-left">No PO</th><th class="px-2 py-1 text-left">Tanggal</th><th class="px-2 py-1 text-center">Status</th><th class="px-2 py-1 text-right">Nilai</th></tr></thead><tbody>`;
                c.orders.forEach(o => {
                    const badge = o.order_status === 'selesai' ? 'bg-green-100 text-green-800' : o.order_status === 'diproses' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800';
                    html += `<tr class="border-t border-gray-100"><td class="px-2 py-1">${o.po_number}</td><td class="px-2 py-1 text-gray-500">${o.tanggal_order}</td><td class="px-2 py-1 text-center"><span class="px-1.5 py-0.5 rounded text-xs ${badge}">${o.order_status}</span></td><td class="px-2 py-1 text-right font-medium">Rp ${(parseFloat(o.total_nilai)/1000000).toFixed(1)} Jt</td></tr>`;
                });
                html += `</tbody></table></div>`;
            });
        });
        html += `</div>`;
    });
    html += `<div class="bg-blue-600 text-white p-3 rounded flex justify-between items-center mt-2"><div><p class="font-bold">GRAND TOTAL</p><p class="text-xs opacity-80">${totalPOOverall} PO</p></div><p class="font-bold text-lg">Rp ${(totalOverall/1000000).toFixed(1)} Jt</p></div>`;
    document.getElementById('orderWinnerDetailsContent').innerHTML = html;
}

function exportOrderWinnerPDF() {
    const periode = '{{ $periode }}', startDate = '{{ $startDate }}', endDate = '{{ $endDate }}';
    const form = document.createElement('form');
    form.method = 'POST'; form.action = '{{ route("laporan.po.orderWinnerPDF") }}'; form.target = '_blank';
    form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}"><input type="hidden" name="periode" value="${periode}"><input type="hidden" name="start_date" value="${startDate}"><input type="hidden" name="end_date" value="${endDate}">`;
    document.body.appendChild(form); form.submit(); document.body.removeChild(form);
}

document.addEventListener('click', function(e) {
    ['outstandingModal','statusModal','clientModal','orderWinnerModal','poTrendModal','poPriorityModal'].forEach(id => {
        const el = document.getElementById(id);
        if (e.target === el) el.querySelector('[onclick^="close"]')?.click();
    });
});
</script>

{{-- ===== MODALS ===== --}}
{{-- Modal base style: removed backdrop-blur, lighter overlay --}}

{{-- Outstanding Modal --}}
<div id="outstandingModal" x-data="{}" class="hidden fixed inset-0 bg-black/40 overflow-y-auto z-50">
    <div class="relative top-10 mx-auto p-4 w-11/12 max-w-6xl bg-white rounded-lg shadow-lg mb-10">
        <div class="flex justify-between items-center pb-3 mb-3 border-b border-gray-200">
            <div>
                <h3 class="text-base font-bold text-gray-900">Detail Outstanding Order</h3>
                <p class="text-xs text-gray-500">Daftar semua item yang masih outstanding</p>
            </div>
            <button onclick="closeOutstandingModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-red-50 rounded p-3 border border-red-100">
                <p class="text-xs text-red-600 font-medium">Total Outstanding</p>
                <p class="text-base font-bold text-red-700">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</p>
            </div>
            <div class="bg-blue-50 rounded p-3 border border-blue-100">
                <p class="text-xs text-blue-600 font-medium">Total PO</p>
                <p class="text-base font-bold text-blue-700">{{ $poBerjalan }}</p>
            </div>
            <div class="bg-orange-50 rounded p-3 border border-orange-100">
                <p class="text-xs text-orange-600 font-medium">Total Qty</p>
                <p class="text-base font-bold text-orange-700">{{ number_format($totalQtyOutstanding, 0, ',', '.') }} kg</p>
            </div>
        </div>

        <div class="mb-3 flex justify-end">
            <form action="{{ route('laporan.po.outstanding.pdf') }}" method="POST" target="_blank">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-sm font-medium flex items-center gap-1">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </form>
        </div>

        <div class="overflow-x-auto max-h-96 overflow-y-auto border border-gray-200 rounded">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">PO</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pabrik</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty (kg)</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @php
                        $no = 1;
                        $CLOSED_FLAG = '[CLOSED_INTERNAL]';
                        $outstandingDetails = \App\Models\OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                            ->join('kliens', 'orders.klien_id', '=', 'kliens.id')
                            ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
                            ->where(function($q) use ($CLOSED_FLAG) {
                                $q->whereIn('orders.status', ['dikonfirmasi', 'diproses'])
                                  ->orWhere(function($q2) use ($CLOSED_FLAG) {
                                      $q2->where('orders.status', 'selesai')->where('orders.alasan_pembatalan', $CLOSED_FLAG);
                                  });
                            })
                            ->whereNotIn('order_details.status', ['selesai'])
                            ->whereNull('order_details.deleted_at')
                            ->select('orders.id as order_id','orders.po_number','orders.no_order','orders.alasan_pembatalan','kliens.nama as klien_nama','kliens.cabang as klien_cabang','bahan_baku_klien.nama as material_nama','order_details.qty','order_details.harga_jual','order_details.total_harga','order_details.status as detail_status')
                            ->orderByRaw("CASE WHEN orders.alasan_pembatalan = '[CLOSED_INTERNAL]' THEN 1 ELSE 0 END")
                            ->orderBy('orders.po_number')->orderBy('kliens.nama')
                            ->get()
                            ->map(function($d) use ($CLOSED_FLAG) { $d->is_closed_internal = ($d->alasan_pembatalan === $CLOSED_FLAG); return $d; });
                    @endphp
                    @forelse($outstandingDetails as $detail)
                        <tr class="{{ $detail->is_closed_internal ? 'bg-red-50' : 'hover:bg-gray-50' }}">
                            <td class="px-3 py-2 text-gray-600">{{ $no++ }}</td>
                            <td class="px-3 py-2 font-medium text-gray-900">
                                {{ $detail->po_number ?: $detail->no_order }}
                                @if($detail->is_closed_internal)
                                    <span class="ml-1 px-1 py-0.5 rounded text-xs bg-red-100 text-red-800">Internal</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-800">{{ $detail->klien_nama }}@if($detail->klien_cabang) <span class="text-xs text-gray-400">({{ $detail->klien_cabang }})</span>@endif</td>
                            <td class="px-3 py-2 text-gray-800">{{ $detail->material_nama ?: '-' }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($detail->harga_jual, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right font-medium">{{ number_format($detail->total_harga, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 text-center" x-data="{ open: false }">
                                <div class="relative inline-block">
                                    <button @click="open = !open" @click.outside="open = false" class="px-2 py-1 text-xs border border-gray-300 rounded bg-white text-gray-600 hover:bg-gray-50">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div x-show="open" x-transition class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded shadow z-50">
                                        @if($detail->is_closed_internal)
                                            <button onclick="reopenOrder({{ $detail->order_id }}, '{{ $detail->po_number ?: $detail->no_order }}')" class="w-full text-left px-3 py-2 text-xs text-green-700 hover:bg-green-50 flex items-center gap-2"><i class="fas fa-undo"></i> Kembalikan ke Diproses</button>
                                        @else
                                            <button onclick="closePabrik({{ $detail->order_id }}, '{{ $detail->po_number ?: $detail->no_order }}')" class="w-full text-left px-3 py-2 text-xs text-red-700 hover:bg-red-50 flex items-center gap-2"><i class="fas fa-times-circle"></i> Closed Pabrik</button>
                                            <button onclick="closeInternal({{ $detail->order_id }}, '{{ $detail->po_number ?: $detail->no_order }}')" class="w-full text-left px-3 py-2 text-xs text-yellow-700 hover:bg-yellow-50 flex items-center gap-2"><i class="fas fa-lock"></i> Closed Internal</button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm"><i class="fas fa-inbox text-2xl mb-2 block"></i>Tidak ada data outstanding</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 sticky bottom-0">
                    <tr class="font-semibold text-sm">
                        <td colspan="4" class="px-3 py-2 text-right text-gray-700">TOTAL:</td>
                        <td class="px-3 py-2 text-right">{{ number_format($totalQtyOutstanding, 2, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right">-</td>
                        <td class="px-3 py-2 text-right">{{ number_format($totalOutstanding, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-4 flex justify-end">
            <button onclick="closeOutstandingModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium">Tutup</button>
        </div>
    </div>
</div>

{{-- Status Modal --}}
<div id="statusModal" class="hidden fixed inset-0 bg-black/40 overflow-y-auto z-50">
    <div class="relative top-10 mx-auto p-4 w-11/12 max-w-4xl bg-white rounded-lg shadow-lg mb-10">
        <div class="flex justify-between items-center pb-3 mb-3 border-b border-gray-200">
            <div>
                <h3 class="text-base font-bold text-gray-900">Detail PO Berdasarkan Status</h3>
                <p class="text-xs text-gray-500">Distribusi status purchase order</p>
            </div>
            <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            @foreach($poByStatus as $status)
                @php $sc = match($status->status) { 'draft'=>['gray','fa-file-alt','Draft'], 'dikonfirmasi'=>['yellow','fa-check-circle','Dikonfirmasi'], 'diproses'=>['blue','fa-cog','Diproses'], 'selesai'=>['green','fa-check-double','Selesai'], 'dibatalkan'=>['red','fa-times-circle','Dibatalkan'], default=>['gray','fa-file',ucfirst($status->status)] }; @endphp
                <div class="bg-{{ $sc[0] }}-50 border border-{{ $sc[0] }}-200 rounded px-3 py-2 flex items-center gap-2">
                    <i class="fas {{ $sc[1] }} text-{{ $sc[0] }}-600 text-sm"></i>
                    <div><p class="text-xs text-{{ $sc[0] }}-700 font-medium">{{ $sc[2] }}</p><p class="text-sm font-bold text-{{ $sc[0] }}-800">{{ number_format($status->total, 0, ',', '.') }} PO</p></div>
                </div>
            @endforeach
        </div>

        <div class="mb-3 flex justify-end">
            <form action="{{ route('laporan.po.status.pdf') }}" method="POST" target="_blank">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-sm font-medium flex items-center gap-1"><i class="fas fa-file-pdf"></i> Download PDF</button>
            </form>
        </div>

        <div class="space-y-3 max-h-[500px] overflow-y-auto">
            @foreach($poByStatus as $status)
                @php $sc = match($status->status) { 'draft'=>['gray','fa-file-alt','Draft'], 'dikonfirmasi'=>['yellow','fa-check-circle','Dikonfirmasi'], 'diproses'=>['blue','fa-cog','Diproses'], 'selesai'=>['green','fa-check-double','Selesai'], 'dibatalkan'=>['red','fa-times-circle','Dibatalkan'], default=>['gray','fa-file',ucfirst($status->status)] }; $poDetails = $poDetailsByStatus[$status->status] ?? []; $totalPOStatus = $poByStatus->sum('total'); $pct = $totalPOStatus > 0 ? ($status->total / $totalPOStatus) * 100 : 0; @endphp
                <div class="border border-{{ $sc[0] }}-200 rounded overflow-hidden">
                    <div class="bg-{{ $sc[0] }}-50 px-3 py-2 border-b border-{{ $sc[0] }}-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas {{ $sc[1] }} text-{{ $sc[0] }}-600"></i>
                            <div>
                                <p class="text-sm font-semibold text-{{ $sc[0] }}-900">{{ $sc[2] }}</p>
                                <p class="text-xs text-{{ $sc[0] }}-600">{{ number_format($status->total, 0, ',', '.') }} PO ({{ number_format($pct, 1) }}%) • Rp {{ number_format($status->nilai, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                    @if(count($poDetails) > 0)
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50"><tr><th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500">No PO</th><th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500">Klien</th><th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500">Tanggal</th></tr></thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($poDetails as $po)<tr class="hover:bg-gray-50"><td class="px-3 py-1.5 font-medium">{{ $po['po_number'] }}</td><td class="px-3 py-1.5 text-gray-700">{{ $po['klien_nama'] }}</td><td class="px-3 py-1.5 text-gray-500">{{ $po['tanggal_order'] }}</td></tr>@endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="px-4 py-4 text-center text-gray-400 text-sm">Tidak ada PO</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex justify-end">
            <button onclick="closeStatusModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium">Tutup</button>
        </div>
    </div>
</div>

{{-- Client Modal --}}
<div id="clientModal" class="hidden fixed inset-0 bg-black/40 overflow-y-auto z-50">
    <div class="relative top-6 mx-auto p-4 w-[95%] max-w-[1400px] bg-white rounded-lg shadow-lg mb-10">
        <div class="flex justify-between items-center pb-3 mb-3 border-b border-gray-200">
            <div>
                <h3 class="text-base font-bold text-gray-900">Detail PO Berdasarkan Klien</h3>
                <p class="text-xs text-gray-500">Akumulasi Purchase Order per Klien</p>
            </div>
            <button onclick="closeClientModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <div class="flex flex-wrap gap-3 mb-4 text-sm">
            <div class="bg-blue-50 border border-blue-100 rounded px-3 py-2"><p class="text-xs text-blue-600">Total Klien</p><p class="font-bold text-blue-700">{{ $poByClient->count() }}</p></div>
            <div class="bg-green-50 border border-green-100 rounded px-3 py-2"><p class="text-xs text-green-600">Total PO</p><p class="font-bold text-green-700">{{ number_format($poByClient->sum('total_po'), 0, ',', '.') }}</p></div>
            <div class="bg-purple-50 border border-purple-100 rounded px-3 py-2"><p class="text-xs text-purple-600">Total Nilai</p><p class="font-bold text-purple-700">Rp {{ number_format($poByClient->sum('total_nilai') / 1000000, 1, ',', '.') }} Jt</p></div>
            <div class="bg-orange-50 border border-orange-100 rounded px-3 py-2"><p class="text-xs text-orange-600">Outstanding</p><p class="font-bold text-orange-700">Rp {{ number_format($poByClient->sum('outstanding_amount') / 1000000, 1, ',', '.') }} Jt</p></div>
        </div>

        <div class="mb-3 flex justify-end gap-2">
            <form action="{{ route('laporan.po.client.excel') }}" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="periode" value="{{ $periode }}">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-sm font-medium flex items-center gap-1"><i class="fas fa-file-excel"></i> Excel</button>
            </form>
            <form action="{{ route('laporan.po.client.pdf') }}" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="periode" value="{{ $periode }}">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-sm font-medium flex items-center gap-1"><i class="fas fa-file-pdf"></i> PDF</button>
            </form>
        </div>

        <div class="space-y-3 max-h-[60vh] overflow-y-auto">
            @php $no = 1; @endphp
            @forelse($poByClient as $client)
                <div class="border border-gray-200 rounded overflow-hidden">
                    <div class="bg-blue-600 text-white px-4 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center text-sm font-bold">{{ $no++ }}</div>
                            <div>
                                <p class="font-semibold">{{ $client->klien_nama }}</p>
                                @if($client->cabang)<p class="text-xs text-blue-100">{{ $client->cabang }}</p>@endif
                            </div>
                        </div>
                        <div class="flex gap-4 text-sm">
                            <div class="text-center"><p class="text-xs text-blue-100">PO</p><p class="font-bold">{{ $client->total_po }}</p></div>
                            <div class="text-center"><p class="text-xs text-blue-100">Nilai</p><p class="font-bold">Rp {{ number_format($client->total_nilai/1000000, 1, ',', '.') }} Jt</p></div>
                            <div class="text-center"><p class="text-xs text-blue-100">Outstanding</p><p class="font-bold {{ $client->outstanding_amount > 0 ? 'text-yellow-300' : 'text-green-300' }}">Rp {{ number_format($client->outstanding_amount/1000000, 1, ',', '.') }} Jt</p></div>
                            <div class="text-center"><p class="text-xs text-blue-100">Kontribusi</p><p class="font-bold">{{ number_format($client->percentage, 1) }}%</p></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex flex-wrap gap-3 text-xs text-gray-600">
                        @if($client->status_dikonfirmasi > 0)<span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full">{{ $client->status_dikonfirmasi }} Dikonfirmasi</span>@endif
                        @if($client->status_diproses > 0)<span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full">{{ $client->status_diproses }} Diproses</span>@endif
                        @if($client->status_selesai > 0)<span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full">{{ $client->status_selesai }} Selesai</span>@endif
                        <span class="text-gray-400">Last Order: {{ $client->last_order_date ? \Carbon\Carbon::parse($client->last_order_date)->format('d M Y') : '-' }}</span>
                        <span class="text-gray-400">Avg/PO: Rp {{ number_format($client->avg_nilai_per_po/1000000, 2) }} Jt</span>
                    </div>
                    <details class="group">
                        <summary class="px-4 py-2 bg-white cursor-pointer hover:bg-gray-50 text-xs font-medium text-blue-600 flex items-center gap-1">
                            <i class="fas fa-chevron-right group-open:rotate-90 transition-transform"></i>
                            Lihat Detail ({{ $client->total_po }} PO)
                        </summary>
                        <div class="border-t border-gray-100">
                            @if(isset($poDetailsByClient[$client->klien_id]['materials']) && count($poDetailsByClient[$client->klien_id]['materials']) > 0)
                            <div class="p-3 bg-blue-50 border-b border-gray-100">
                                <p class="text-xs font-medium text-gray-600 mb-1"><i class="fas fa-box mr-1"></i>Material</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($poDetailsByClient[$client->klien_id]['materials'] as $mat)
                                        <span class="px-2 py-0.5 bg-white border border-blue-200 rounded text-xs">{{ $mat['nama'] }} <span class="text-gray-400">({{ number_format($mat['total_qty'], 0, ',', '.') }} kg)</span></span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @if(isset($poDetailsByClient[$client->klien_id]['orders']) && count($poDetailsByClient[$client->klien_id]['orders']) > 0)
                            <table class="min-w-full text-xs divide-y divide-gray-100">
                                <thead class="bg-gray-50"><tr>
                                    <th class="px-3 py-1.5 text-left font-medium text-gray-500">No. PO</th>
                                    <th class="px-3 py-1.5 text-left font-medium text-gray-500">Tanggal</th>
                                    <th class="px-3 py-1.5 text-left font-medium text-gray-500">Material</th>
                                    <th class="px-3 py-1.5 text-center font-medium text-gray-500">Status</th>
                                    <th class="px-3 py-1.5 text-center font-medium text-gray-500">Prioritas</th>
                                    <th class="px-3 py-1.5 text-right font-medium text-gray-500">Qty</th>
                                    <th class="px-3 py-1.5 text-right font-medium text-gray-500">Nilai</th>
                                    <th class="px-3 py-1.5 text-center font-medium text-gray-500">Aksi</th>
                                </tr></thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($poDetailsByClient[$client->klien_id]['orders'] as $po)
                                    @php
                                        $sBg = match($po['status']) { 'dikonfirmasi'=>'bg-yellow-100 text-yellow-800', 'diproses'=>'bg-blue-100 text-blue-800', 'selesai'=>'bg-green-100 text-green-800', default=>'bg-gray-100 text-gray-800' };
                                        $pBg = match($po['priority']) { 'tinggi'=>'bg-red-100 text-red-800', 'sedang'=>'bg-orange-100 text-orange-800', 'rendah'=>'bg-gray-100 text-gray-800', default=>'bg-gray-100 text-gray-800' };
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-1.5 font-medium text-blue-600">{{ $po['po_number'] }}</td>
                                        <td class="px-3 py-1.5 text-gray-500">{{ $po['tanggal_order'] }}</td>
                                        <td class="px-3 py-1.5 text-gray-700 max-w-xs truncate" title="{{ $po['materials'] }}">{{ Str::limit($po['materials'], 40) }}</td>
                                        <td class="px-3 py-1.5 text-center"><span class="px-1.5 py-0.5 rounded {{ $sBg }}">{{ ucfirst($po['status']) }}</span></td>
                                        <td class="px-3 py-1.5 text-center"><span class="px-1.5 py-0.5 rounded {{ $pBg }}">{{ ucfirst($po['priority'] ?? '-') }}</span></td>
                                        <td class="px-3 py-1.5 text-right">{{ number_format($po['total_qty'], 0, ',', '.') }} kg</td>
                                        <td class="px-3 py-1.5 text-right font-semibold">Rp {{ number_format($po['total_amount'], 0, ',', '.') }}</td>
                                        <td class="px-3 py-1.5 text-center"><a href="{{ route('orders.show', $po['id']) }}" target="_blank" class="text-blue-600 hover:text-blue-800"><i class="fas fa-external-link-alt"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                    </details>
                </div>
            @empty
                <div class="text-center py-8 text-gray-400 text-sm"><i class="fas fa-inbox text-3xl mb-2 block"></i>Tidak ada data klien</div>
            @endforelse
        </div>

        <div class="mt-4 bg-gray-50 rounded p-3 border border-gray-200 flex flex-wrap gap-4 text-sm">
            <span class="text-gray-500">Total Klien: <strong class="text-gray-800">{{ $poByClient->count() }}</strong></span>
            <span class="text-gray-500">Total PO: <strong class="text-gray-800">{{ number_format($poByClient->sum('total_po'), 0, ',', '.') }}</strong></span>
            <span class="text-gray-500">Total Nilai: <strong class="text-gray-800">Rp {{ number_format($poByClient->sum('total_nilai'), 0, ',', '.') }}</strong></span>
            <span class="text-gray-500">Outstanding: <strong class="text-orange-600">Rp {{ number_format($poByClient->sum('outstanding_amount'), 0, ',', '.') }}</strong></span>
        </div>

        <div class="mt-3 flex justify-end">
            <button onclick="closeClientModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium">Tutup</button>
        </div>
    </div>
</div>

{{-- Order Winner Modal --}}
<div id="orderWinnerModal" class="hidden fixed inset-0 bg-black/40 overflow-y-auto z-50">
    <div class="relative top-10 mx-auto p-4 w-11/12 max-w-5xl bg-white rounded-lg shadow-lg mb-10">
        <div class="flex justify-between items-center pb-3 mb-3 border-b border-gray-200">
            <div>
                <h3 class="text-base font-bold text-gray-900">Detail Order Winners</h3>
                <p class="text-xs text-gray-500">Rincian PO per marketing</p>
            </div>
            <button onclick="closeOrderWinnerModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div class="mb-3 flex justify-end">
            <button onclick="exportOrderWinnerPDF()" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-sm font-medium flex items-center gap-1"><i class="fas fa-file-pdf"></i> Download PDF</button>
        </div>
        <div id="orderWinnerDetailsContent" class="overflow-x-auto max-h-[500px] overflow-y-auto"></div>
        <div class="mt-3 flex justify-end">
            <button onclick="closeOrderWinnerModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium">Tutup</button>
        </div>
    </div>
</div>

{{-- PO Trend Modal --}}
<div id="poTrendModal" class="hidden fixed inset-0 bg-black/40 overflow-y-auto z-50">
    <div class="relative top-10 mx-auto p-4 w-11/12 max-w-3xl bg-white rounded-lg shadow-lg mb-10">
        <div class="flex justify-between items-center pb-3 mb-3 border-b border-gray-200">
            <div>
                <h3 class="text-base font-bold text-gray-900">Trend PO 12 Bulan Terakhir</h3>
                <p class="text-xs text-gray-500">Rincian jumlah dan nilai PO per bulan</p>
            </div>
            <button onclick="closePOTrendModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <div class="flex flex-wrap gap-3 mb-4 text-sm">
            <div class="bg-blue-50 border border-blue-100 rounded px-3 py-2"><p class="text-xs text-blue-600">Periode</p><p class="font-bold text-blue-700">12 Bulan</p></div>
            <div class="bg-green-50 border border-green-100 rounded px-3 py-2"><p class="text-xs text-green-600">Total PO</p><p class="font-bold text-green-700">{{ number_format(array_sum(array_column($poTrendByMonth, 'total_po')), 0, ',', '.') }}</p></div>
            <div class="bg-purple-50 border border-purple-100 rounded px-3 py-2"><p class="text-xs text-purple-600">Total Nilai</p><p class="font-bold text-purple-700">@php $totalNilaiTrend = array_sum(array_column($poTrendByMonth, 'total_nilai')); @endphp Rp {{ number_format($totalNilaiTrend, 0, ',', '.') }}</p></div>
        </div>

        <div class="mb-3 flex justify-end">
            <form action="{{ route('laporan.po.trend.pdf') }}" method="POST" target="_blank">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-sm font-medium flex items-center gap-1"><i class="fas fa-file-pdf"></i> Download PDF</button>
            </form>
        </div>

        <div class="overflow-x-auto max-h-72 overflow-y-auto border border-gray-200 rounded mb-4">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0"><tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">No</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Bulan</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">Jumlah PO</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Total Nilai</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Rata-rata/PO</th>
                </tr></thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @php $no = 1; @endphp
                    @foreach($poTrendByMonth as $trend)
                    @php $avgPerPO = $trend['total_po'] > 0 ? $trend['total_nilai'] / $trend['total_po'] : 0; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-500">{{ $no++ }}</td>
                        <td class="px-3 py-2 font-medium">{{ $trend['month'] }}</td>
                        <td class="px-3 py-2 text-center font-semibold">{{ number_format($trend['total_po'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right font-bold">Rp {{ number_format($trend['total_nilai'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right text-gray-600">Rp {{ number_format($avgPerPO, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 sticky bottom-0">
                    <tr class="font-semibold text-sm">
                        <td colspan="2" class="px-3 py-2 text-right text-gray-700">TOTAL:</td>
                        <td class="px-3 py-2 text-center">{{ number_format(array_sum(array_column($poTrendByMonth, 'total_po')), 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right">Rp {{ number_format($totalNilaiTrend, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right">@php $totalPOTrend = array_sum(array_column($poTrendByMonth, 'total_po')); @endphp Rp {{ number_format($totalPOTrend > 0 ? $totalNilaiTrend / $totalPOTrend : 0, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="height: 180px;"><canvas id="chartPOTrendModal"></canvas></div>

        <div class="mt-3 flex justify-end">
            <button onclick="closePOTrendModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium">Tutup</button>
        </div>
    </div>
</div>

{{-- PO Priority Modal --}}
<div id="poPriorityModal" class="hidden fixed inset-0 bg-black/40 overflow-y-auto z-50">
    <div class="relative top-10 mx-auto p-4 w-11/12 max-w-4xl bg-white rounded-lg shadow-lg mb-10">
        <div class="flex justify-between items-center pb-3 mb-3 border-b border-gray-200">
            <div>
                <h3 class="text-base font-bold text-gray-900">Detail PO Berdasarkan Prioritas</h3>
                <p class="text-xs text-gray-500">Distribusi PO berdasarkan tingkat prioritas</p>
            </div>
            <button onclick="closePOPriorityModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <div class="flex flex-wrap gap-3 mb-4">
            <div class="bg-red-50 border border-red-100 rounded px-3 py-2"><p class="text-xs text-red-600">Tinggi</p><p class="font-bold text-red-700">{{ $poByPriority->where('priority', 'tinggi')->first()->total ?? 0 }} PO</p></div>
            <div class="bg-orange-50 border border-orange-100 rounded px-3 py-2"><p class="text-xs text-orange-600">Sedang</p><p class="font-bold text-orange-700">{{ $poByPriority->where('priority', 'sedang')->first()->total ?? 0 }} PO</p></div>
            <div class="bg-gray-50 border border-gray-200 rounded px-3 py-2"><p class="text-xs text-gray-600">Rendah</p><p class="font-bold text-gray-700">{{ $poByPriority->where('priority', 'rendah')->first()->total ?? 0 }} PO</p></div>
        </div>

        <div class="mb-3 flex justify-end">
            <form action="{{ route('laporan.po.priority.pdf') }}" method="POST" target="_blank">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-sm font-medium flex items-center gap-1"><i class="fas fa-file-pdf"></i> Download PDF</button>
            </form>
        </div>

        <div class="space-y-3 max-h-[400px] overflow-y-auto mb-4">
            @php $totalNilaiPriority = $poByPriority->sum('nilai'); @endphp
            @foreach($poByPriority as $priority)
                @php
                    $pct = $totalNilaiPriority > 0 ? ($priority->nilai / $totalNilaiPriority) * 100 : 0;
                    $hdrColor = match($priority->priority) { 'tinggi'=>'bg-red-600', 'sedang'=>'bg-orange-500', 'rendah'=>'bg-gray-500', default=>'bg-blue-600' };
                @endphp
                <div class="border border-gray-200 rounded overflow-hidden">
                    <div class="{{ $hdrColor }} text-white px-4 py-2 flex justify-between items-center">
                        <div><p class="font-semibold text-sm">Prioritas {{ ucfirst($priority->priority) }}</p><p class="text-xs opacity-80">{{ number_format($priority->total, 0) }} PO</p></div>
                        <div class="text-right"><p class="font-bold">Rp {{ number_format($priority->nilai, 0, ',', '.') }}</p><p class="text-xs opacity-75">{{ number_format($pct, 1) }}%</p></div>
                    </div>
                    @if(isset($poDetailsByPriority[$priority->priority]) && count($poDetailsByPriority[$priority->priority]) > 0)
                    <table class="min-w-full text-xs divide-y divide-gray-100">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-2 py-1.5 text-left font-medium text-gray-500">No</th>
                            <th class="px-2 py-1.5 text-left font-medium text-gray-500">No. PO</th>
                            <th class="px-2 py-1.5 text-left font-medium text-gray-500">Klien</th>
                            <th class="px-2 py-1.5 text-left font-medium text-gray-500">Cabang</th>
                            <th class="px-2 py-1.5 text-left font-medium text-gray-500">Tanggal</th>
                            <th class="px-2 py-1.5 text-left font-medium text-gray-500">Bahan Baku</th>
                            <th class="px-2 py-1.5 text-right font-medium text-gray-500">Qty</th>
                            <th class="px-2 py-1.5 text-right font-medium text-gray-500">Harga</th>
                            <th class="px-2 py-1.5 text-right font-medium text-gray-500">Total</th>
                            <th class="px-2 py-1.5 text-center font-medium text-gray-500">Status</th>
                        </tr></thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @php $pTotalQty = 0; $pTotalAmt = 0; @endphp
                            @foreach($poDetailsByPriority[$priority->priority] as $i => $po)
                            @php $pTotalQty += $po['total_qty']; $pTotalAmt += $po['total_amount']; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-1.5 text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-2 py-1.5 font-medium text-blue-600">{{ $po['po_number'] }}</td>
                                <td class="px-2 py-1.5">{{ $po['klien_nama'] }}</td>
                                <td class="px-2 py-1.5 text-gray-500">{{ $po['cabang'] }}</td>
                                <td class="px-2 py-1.5 text-gray-500">{{ $po['tanggal_order'] }}</td>
                                <td class="px-2 py-1.5">{{ $po['bahan_baku'] }}</td>
                                <td class="px-2 py-1.5 text-right">{{ number_format($po['total_qty'], 2, ',', '.') }}</td>
                                <td class="px-2 py-1.5 text-right">{{ number_format($po['harga_jual'], 2, ',', '.') }}</td>
                                <td class="px-2 py-1.5 text-right font-semibold">Rp {{ number_format($po['total_amount'], 0, ',', '.') }}</td>
                                <td class="px-2 py-1.5 text-center"><span class="px-1.5 py-0.5 rounded {{ $po['status'] == 'dikonfirmasi' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($po['status']) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50"><tr class="font-semibold">
                            <td colspan="6" class="px-2 py-1.5 text-right text-gray-700">TOTAL:</td>
                            <td class="px-2 py-1.5 text-right">{{ number_format($pTotalQty, 2, ',', '.') }}</td>
                            <td class="px-2 py-1.5 text-right">-</td>
                            <td class="px-2 py-1.5 text-right">Rp {{ number_format($pTotalAmt, 0, ',', '.') }}</td>
                            <td></td>
                        </tr></tfoot>
                    </table>
                    @else
                        <p class="px-4 py-3 text-center text-gray-400 text-sm">Tidak ada PO</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Summary Table --}}
        <div class="overflow-x-auto border border-gray-200 rounded mb-4">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Prioritas</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">Jumlah PO</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Total Nilai</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Rata-rata/PO</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">%</th>
                </tr></thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($poByPriority as $priority)
                    @php $pct = $totalNilaiPriority > 0 ? ($priority->nilai / $totalNilaiPriority) * 100 : 0; $bBg = match($priority->priority) { 'tinggi'=>'bg-red-100 text-red-800', 'sedang'=>'bg-orange-100 text-orange-800', 'rendah'=>'bg-gray-100 text-gray-800', default=>'bg-blue-100 text-blue-800' }; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs font-semibold {{ $bBg }}">{{ ucfirst($priority->priority) }}</span></td>
                        <td class="px-3 py-2 text-center font-semibold">{{ number_format($priority->total, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right font-bold">Rp {{ number_format($priority->nilai, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right text-gray-600">Rp {{ number_format($priority->total > 0 ? $priority->nilai / $priority->total : 0, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-center text-gray-600">{{ number_format($pct, 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50"><tr class="font-semibold">
                    <td class="px-3 py-2 text-right text-gray-700">TOTAL:</td>
                    <td class="px-3 py-2 text-center">{{ number_format($poByPriority->sum('total'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-right">Rp {{ number_format($totalNilaiPriority, 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-right">@php $totalPOPr = $poByPriority->sum('total'); @endphp Rp {{ number_format($totalPOPr > 0 ? $totalNilaiPriority / $totalPOPr : 0, 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-center">100%</td>
                </tr></tfoot>
            </table>
        </div>

        <div style="height: 180px;"><canvas id="chartPOPriorityModal"></canvas></div>

        <div class="mt-3 flex justify-end">
            <button onclick="closePOPriorityModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium">Tutup</button>
        </div>
    </div>
</div>

@endsection