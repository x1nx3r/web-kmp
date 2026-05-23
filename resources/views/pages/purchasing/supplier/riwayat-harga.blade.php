@extends('layouts.app')
@section('title', 'Riwayat Harga Bahan Baku - Kamil Maju Persada')
@section('content')

<x-welcome-banner title="Riwayat Harga Bahan Baku" :subtitle="$bahanBakuData->nama . ' dari ' . $supplierData->nama" icon="fas fa-chart-line" />

<x-breadcrumb :items="[
    ['title' => 'Purchasing', 'url' => '#'],
    ['title' => 'Supplier', 'url' => route('supplier.index')],
    ['title' => $supplierData->nama, 'url' => route('supplier.edit', $supplierData->slug)],
    'Riwayat Harga: ' . $bahanBakuData->nama
]" />

{{-- Back Button --}}
<div class="mb-5">
    <a href="javascript:history.back()"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
        <i class="fas fa-arrow-left text-xs"></i>Kembali
    </a>
</div>

{{-- Bahan Baku Info --}}
<div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Informasi Bahan Baku</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-gray-50 rounded-lg px-4 py-3">
            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Nama Bahan Baku</p>
            <p class="text-sm font-bold text-gray-800">{{ $bahanBakuData->nama }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg px-4 py-3">
            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Satuan</p>
            <p class="text-sm font-bold text-gray-800">{{ $bahanBakuData->satuan }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg px-4 py-3">
            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Supplier</p>
            <p class="text-sm font-bold text-gray-800">{{ $bahanBakuData->supplier_nama }}</p>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
@php
    if (!empty($riwayatHarga)) {
        usort($riwayatHarga, function($a, $b) {
            $d = strtotime($a['tanggal']) - strtotime($b['tanggal']);
            return $d !== 0 ? $d : $a['id'] - $b['id'];
        });
        $hargaList = array_column($riwayatHarga, 'harga');
        $firstPrice = $riwayatHarga[0]['harga'];
        $lastPrice = end($riwayatHarga)['harga'];
        $trend = $lastPrice > $firstPrice ? 'naik' : ($lastPrice < $firstPrice ? 'turun' : 'stabil');
        $trendPercentage = $firstPrice > 0 ? round((($lastPrice - $firstPrice) / $firstPrice) * 100, 2) : 0;
        $daysDiff = \Carbon\Carbon::parse($riwayatHarga[0]['tanggal'])->diffInDays(\Carbon\Carbon::parse(end($riwayatHarga)['tanggal']));
    } else {
        $hargaList = [];
        $trend = 'stabil';
        $trendPercentage = 0;
        $daysDiff = 0;
    }
    $trendColor = $trend == 'naik' ? 'green' : ($trend == 'turun' ? 'red' : 'gray');
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white border border-gray-200 border-l-4 border-l-blue-500 rounded-xl px-4 py-3">
        <p class="text-xs text-gray-500 mb-1">Harga Saat Ini</p>
        <p class="text-lg font-bold text-blue-600">Rp {{ number_format($bahanBakuData->harga_saat_ini, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white border border-gray-200 border-l-4 border-l-red-500 rounded-xl px-4 py-3">
        <p class="text-xs text-gray-500 mb-1">Harga Tertinggi</p>
        <p class="text-lg font-bold text-red-600">
            Rp {{ !empty($hargaList) ? number_format(max($hargaList), 0, ',', '.') : number_format($bahanBakuData->harga_saat_ini, 0, ',', '.') }}
        </p>
    </div>
    <div class="bg-white border border-gray-200 border-l-4 border-l-green-500 rounded-xl px-4 py-3">
        <p class="text-xs text-gray-500 mb-1">Harga Terendah</p>
        <p class="text-lg font-bold text-green-600">
            Rp {{ !empty($hargaList) ? number_format(min($hargaList), 0, ',', '.') : number_format($bahanBakuData->harga_saat_ini, 0, ',', '.') }}
        </p>
    </div>
    <div class="bg-white border border-gray-200 border-l-4 border-l-{{ $trendColor }}-500 rounded-xl px-4 py-3">
        <p class="text-xs text-gray-500 mb-1">Trend ({{ $daysDiff }} hari)</p>
        <p class="text-lg font-bold text-{{ $trendColor }}-600">
            {{ $trend == 'naik' ? '+' : '' }}{{ $trendPercentage }}%
        </p>
        <p class="text-xs text-{{ $trendColor }}-500 capitalize">{{ $trend }}</p>
    </div>
</div>

{{-- Price Chart --}}
<div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <p class="text-sm font-bold text-gray-700 flex items-center gap-2">
            <i class="fas fa-chart-line text-green-500"></i>Grafik Perubahan Harga
        </p>
        <div id="klienFilter" class="flex flex-wrap gap-2"></div>
    </div>
    <div class="relative h-64 sm:h-80 lg:h-96">
        <canvas id="priceChart" class="w-full h-full"></canvas>
    </div>
</div>

{{-- Price History Table --}}
<div class="bg-white border border-gray-200 rounded-xl p-5">
    <p class="text-sm font-bold text-gray-700 flex items-center gap-2 mb-4">
        <i class="fas fa-history text-green-500"></i>Riwayat Perubahan Harga per Tanggal
    </p>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Klien/Pabrik</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Perubahan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($riwayatHarga as $index => $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $item['formatted_tanggal'] }}</p>
                            <p class="text-xs text-gray-400 hidden sm:block">{{ $item['formatted_hari'] }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $item['klien_nama'] }}</p>
                            @if($item['klien_cabang'])
                                <p class="text-xs text-gray-400">{{ $item['klien_cabang'] }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                            Rp {{ $item['formatted_harga'] }}
                        </td>
                        <td class="px-4 py-3">
                            @if($item['tipe_perubahan'] === 'naik')
                                <p class="text-sm text-green-600 font-medium">+Rp {{ $item['formatted_selisih'] }}</p>
                                <p class="text-xs text-green-500">+{{ number_format($item['persentase_perubahan'], 2) }}%</p>
                            @elseif($item['tipe_perubahan'] === 'turun')
                                <p class="text-sm text-red-600 font-medium">-Rp {{ $item['formatted_selisih'] }}</p>
                                <p class="text-xs text-red-500">{{ number_format($item['persentase_perubahan'], 2) }}%</p>
                            @else
                                <p class="text-sm text-gray-400">{{ $item['tipe_perubahan'] === 'awal' ? 'Data Pertama' : 'Tidak Ada Perubahan' }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full {{ $item['badge_class'] }}">
                                <i class="{{ $item['icon'] }}"></i>
                                <span class="hidden sm:inline">{{ ucfirst($item['tipe_perubahan']) }}</span>
                                <span class="sm:hidden">{{ substr($item['tipe_perubahan'], 0, 1) }}</span>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <i class="fas fa-chart-line text-4xl block mb-3"></i>
                            <p class="text-sm font-medium text-gray-500">Belum ada data riwayat harga</p>
                            <p class="text-xs mt-1">Data akan muncul setelah ada perubahan harga</p>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    let chartDataByKlien = @json($chartDataByKlien ?? []);
    let allDates = @json($allDates ?? []);

    if (!chartDataByKlien || Object.keys(chartDataByKlien).length === 0) {
        document.getElementById('priceChart').parentElement.innerHTML =
            '<div class="flex items-center justify-center h-64 text-gray-400"><p class="text-sm">Belum ada data riwayat harga</p></div>';
        return;
    }

    const colorPalette = [
        { border: 'rgb(34, 197, 94)',   bg: 'rgba(34, 197, 94, 0.1)' },
        { border: 'rgb(99, 102, 241)',  bg: 'rgba(99, 102, 241, 0.1)' },
        { border: 'rgb(249, 115, 22)', bg: 'rgba(249, 115, 22, 0.1)' },
        { border: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' },
        { border: 'rgb(168, 85, 247)', bg: 'rgba(168, 85, 247, 0.1)' },
        { border: 'rgb(14, 165, 233)', bg: 'rgba(14, 165, 233, 0.1)' },
        { border: 'rgb(234, 179, 8)',  bg: 'rgba(234, 179, 8, 0.1)' },
        { border: 'rgb(239, 68, 68)',  bg: 'rgba(239, 68, 68, 0.1)' },
        { border: 'rgb(6, 182, 212)',  bg: 'rgba(6, 182, 212, 0.1)' },
        { border: 'rgb(132, 204, 22)', bg: 'rgba(132, 204, 22, 0.1)' },
    ];

    let visibleKliens = new Set(Object.keys(chartDataByKlien));
    const filterContainer = document.getElementById('klienFilter');

    Object.keys(chartDataByKlien).forEach((klienKey, i) => {
        const klienData = chartDataByKlien[klienKey];
        const color = colorPalette[i % colorPalette.length];

        const btn = document.createElement('button');
        btn.className = 'px-3 py-1.5 text-xs font-semibold rounded-full border-2 transition-all duration-200';
        btn.style.borderColor = color.border;
        btn.style.backgroundColor = color.bg;
        btn.style.color = color.border;
        btn.dataset.klienKey = klienKey;
        btn.innerHTML = `<i class="fas fa-check-circle mr-1"></i>${klienData.label}`;

        btn.addEventListener('click', function () {
            if (visibleKliens.has(klienKey)) {
                visibleKliens.delete(klienKey);
                btn.style.backgroundColor = 'white';
                btn.style.opacity = '0.5';
                btn.innerHTML = `<i class="fas fa-circle mr-1"></i>${klienData.label}`;
            } else {
                visibleKliens.add(klienKey);
                btn.style.backgroundColor = color.bg;
                btn.style.opacity = '1';
                btn.innerHTML = `<i class="fas fa-check-circle mr-1"></i>${klienData.label}`;
            }
            updateChart();
        });

        filterContainer.appendChild(btn);
    });

    function getVisibleDates() {
        const dates = new Set();
        Object.keys(chartDataByKlien).forEach(key => {
            if (!visibleKliens.has(key)) return;
            chartDataByKlien[key].data.forEach(d => { if (d.harga !== null) dates.add(d.tanggal); });
        });
        return Array.from(dates).sort();
    }

    function formatLabels(dates) {
        return dates.map(d => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
    }

    function prepareDatasets(visibleDates) {
        const datasets = [];
        Object.keys(chartDataByKlien).forEach((key, i) => {
            if (!visibleKliens.has(key)) return;
            const klienData = chartDataByKlien[key];
            const color = colorPalette[i % colorPalette.length];
            const actualData = klienData.data.filter(d => d.harga !== null);
            const labelMap = {};
            actualData.forEach(d => { labelMap[d.tanggal] = d.harga; });

            datasets.push({
                label: klienData.label,
                data: visibleDates.map(d => labelMap[d] ?? null),
                borderColor: color.border,
                backgroundColor: color.bg,
                borderWidth: 2.5,
                fill: false,
                tension: 0.3,
                pointBackgroundColor: color.border,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            });
        });
        return datasets;
    }

    const ctx = document.getElementById('priceChart').getContext('2d');
    let chart;

    function createChart() {
        const visibleDates = getVisibleDates();
        chart = new Chart(ctx, {
            type: 'line',
            data: { labels: formatLabels(visibleDates), datasets: prepareDatasets(visibleDates) },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17,24,39,0.95)',
                        titleColor: '#fff',
                        bodyColor: '#d1fae5',
                        borderColor: 'rgb(34,197,94)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12,
                        callbacks: {
                            title: items => new Date(visibleDates[items[0].dataIndex]).toLocaleDateString('id-ID', {
                                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                            }),
                            label: ctx => {
                                const label = ctx.dataset.label ? ctx.dataset.label + ': ' : '';
                                return label + (ctx.parsed.y !== null ? 'Rp ' + ctx.parsed.y.toLocaleString('id-ID') : 'Tidak ada data');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(156,163,175,0.1)', drawBorder: false },
                        ticks: { color: 'rgb(107,114,128)', font: { size: 10 }, maxRotation: 45, autoSkip: true, maxTicksLimit: 15 }
                    },
                    y: {
                        grid: { color: 'rgba(156,163,175,0.1)', drawBorder: false },
                        ticks: {
                            color: 'rgb(107,114,128)', font: { size: 11 },
                            callback: v => 'Rp ' + v.toLocaleString('id-ID')
                        },
                        beginAtZero: false
                    }
                },
                elements: { line: { spanGaps: false } },
                animation: { duration: 600, easing: 'easeInOutQuart' }
            }
        });
    }

    function updateChart() {
        if (chart) chart.destroy();
        createChart();
    }

    createChart();
});

// PO Modal
function showPOModal(harga, tanggal) {
    const modal = document.getElementById('poModal');
    const loading = document.getElementById('poModalLoading');
    const content = document.getElementById('poModalContent');
    const empty = document.getElementById('poModalEmpty');

    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    empty.classList.add('hidden');

    const url = '{{ route("supplier.riwayat-harga.po-by-harga", ["supplier" => $supplierData->slug, "bahanBaku" => $bahanBakuData->slug]) }}'
        + '?harga=' + harga + '&tanggal=' + encodeURIComponent(tanggal);

    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            loading.classList.add('hidden');
            if (data.success && data.orders.length > 0) {
                document.getElementById('modalSubtitle').textContent =
                    `${data.bahan_baku_nama} - ${data.supplier_nama} | ${tanggal}`;
                document.getElementById('totalPO').textContent = data.total_po;
                document.getElementById('totalQty').textContent = data.total_qty.toLocaleString('id-ID') + ' ' + data.satuan;
                document.getElementById('satuanLabel').textContent = data.satuan;
                document.getElementById('hargaLabel').textContent = 'Rp ' + data.harga.toLocaleString('id-ID');

                const poList = document.getElementById('poList');
                poList.innerHTML = '';
                data.orders.forEach((order, index) => {
                    const card = document.createElement('div');
                    card.className = 'border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow';

                    let pengirimanHTML = order.pengiriman.map(p => {
                        const b = getStatusBadge(p.status);
                        return `<div class="flex items-center justify-between py-2 border-t border-gray-100 text-sm">
                            <span class="flex-1 font-medium text-gray-700">${p.no_pengiriman}</span>
                            <span class="flex-1 text-center text-gray-600">${p.tanggal_kirim}</span>
                            <span class="flex-1 text-right font-semibold text-green-600">${p.qty_kirim.toLocaleString('id-ID')} ${data.satuan}</span>
                            <span class="flex-1 text-right"><span class="${b.class}">${b.text}</span></span>
                        </div>`;
                    }).join('');

                    const ob = getOrderStatusBadge(order.status_order);
                    card.innerHTML = `
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-sm">${index + 1}</div>
                                <div>
                                    <h5 class="font-bold text-gray-900">${order.po_number}</h5>
                                    <p class="text-xs text-gray-500">${order.tanggal_order}</p>
                                </div>
                            </div>
                            <span class="${ob.class}">${ob.text}</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-3 py-2 mb-3 text-sm flex items-center gap-2">
                            <i class="fas fa-building text-gray-400"></i>
                            <span class="font-medium text-gray-700">${order.klien_nama}</span>
                            ${order.klien_cabang ? `<span class="text-gray-400">- ${order.klien_cabang}</span>` : ''}
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-gray-400 uppercase mb-1">
                                <span>Pengiriman</span><span>Tanggal</span><span>Qty</span><span>Status</span>
                            </div>
                            ${pengirimanHTML}
                        </div>`;
                    poList.appendChild(card);
                });
                content.classList.remove('hidden');
            } else {
                empty.classList.remove('hidden');
            }
        })
        .catch(err => {
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
            document.getElementById('poModalEmpty').innerHTML = `
                <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-3 block"></i>
                <p class="text-red-500 text-sm font-medium">Terjadi kesalahan saat memuat data</p>
                <p class="text-xs text-gray-400 mt-1">${err.message}</p>`;
        });
}

function closePOModal() {
    document.getElementById('poModal').classList.add('hidden');
}

function getStatusBadge(status) {
    const map = {
        berhasil: 'bg-green-100 text-green-700',
        gagal:    'bg-red-100 text-red-700',
        pending:  'bg-yellow-100 text-yellow-700',
        diproses: 'bg-blue-100 text-blue-700',
    };
    const cls = map[status] || 'bg-gray-100 text-gray-700';
    return { class: `px-2 py-0.5 text-xs font-semibold rounded-full ${cls}`, text: status.charAt(0).toUpperCase() + status.slice(1) };
}

function getOrderStatusBadge(status) {
    const map = {
        selesai:    'bg-green-100 text-green-700',
        diproses:   'bg-blue-100 text-blue-700',
        pending:    'bg-yellow-100 text-yellow-700',
        dibatalkan: 'bg-red-100 text-red-700',
    };
    const cls = map[status] || 'bg-gray-100 text-gray-700';
    return { class: `px-2.5 py-1 text-xs font-semibold rounded-full ${cls}`, text: status.charAt(0).toUpperCase() + status.slice(1) };
}

document.addEventListener('click', e => { if (e.target === document.getElementById('poModal')) closePOModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closePOModal(); });
</script>
@endpush