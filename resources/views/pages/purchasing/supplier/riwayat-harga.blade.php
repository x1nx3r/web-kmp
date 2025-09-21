@extends('layouts.app')
@section('title', 'Riwayat Harga Bahan Baku - Kamil Maju Persada')
@section('content')


<x-welcome-banner title="Riwayat Harga Bahan Baku" :subtitle="$bahanBakuData->nama . ' dari ' . $supplierData->nama" icon="fas fa-chart-line" />
{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['title' => 'Purchasing', 'url' => '#'],
    ['title' => 'Supplier', 'url' => route('supplier.index')],
    ['title' => $supplierData->nama, 'url' => route('supplier.edit', $supplierData->slug)],
    'Riwayat Harga: ' . $bahanBakuData->nama
]" />

{{-- Back Button --}}
<div class="mb-4 sm:mb-6">
    <a href="javascript:history.back()" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
        <i class="fas fa-arrow-left mr-2"></i>
        <span class="font-semibold">Kembali</span>
    </a>
</div>

{{-- Bahan Baku Info Card --}}
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 border border-gray-200 mb-4 sm:mb-6">
    <div class="flex items-center mb-3 sm:mb-4">
        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-indigo-500 rounded-full flex items-center justify-center mr-2 sm:mr-3">
            <i class="fas fa-cube text-white text-xs sm:text-sm"></i>
        </div>
        <h2 class="text-base sm:text-lg lg:text-xl font-bold text-indigo-800">Informasi Bahan Baku</h2>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 sm:p-4 border border-blue-200">
            <div class="flex items-center mb-1 sm:mb-2">
                <i class="fas fa-tag text-blue-500 mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                <span class="text-xs sm:text-sm font-semibold text-blue-700">Nama Bahan Baku</span>
            </div>
            <p class="text-sm sm:text-lg font-bold text-gray-800">{{ $bahanBakuData->nama }}</p>
        </div>
        
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-3 sm:p-4 border border-green-200">
            <div class="flex items-center mb-1 sm:mb-2">
                <i class="fas fa-weight-hanging text-green-500 mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                <span class="text-xs sm:text-sm font-semibold text-green-700">Satuan</span>
            </div>
            <p class="text-sm sm:text-lg font-bold text-gray-800">{{ $bahanBakuData->satuan }}</p>
        </div>
        
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-3 sm:p-4 border border-purple-200">
            <div class="flex items-center mb-1 sm:mb-2">
                <i class="fas fa-building text-purple-500 mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                <span class="text-xs sm:text-sm font-semibold text-purple-700">Supplier</span>
            </div>
            <p class="text-sm sm:text-lg font-bold text-gray-800">{{ $bahanBakuData->supplier_nama }}</p>
        </div>
    </div>
</div>

{{-- Price Statistics Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 mb-6">
    {{-- Current Price --}}
    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-gray-600">Harga Saat Ini</p>
                <p class="text-lg sm:text-2xl font-bold text-blue-600">Rp {{ number_format($bahanBakuData->harga_saat_ini, 0, ',', '.') }}</p>
            </div>
            <div class="p-2 sm:p-3 bg-blue-100 rounded-full">
                <i class="fas fa-money-bill-wave text-blue-500 text-sm sm:text-base"></i>
            </div>
        </div>
    </div>
    
    {{-- Highest Price --}}
    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-gray-600">Harga Tertinggi</p>
                <p class="text-lg sm:text-2xl font-bold text-red-600">
                    @if(!empty($riwayatHarga))
                        @php
                            $hargaList = array_column($riwayatHarga, 'harga');
                            // Sort to make sure we get the right max/min values
                            sort($hargaList);
                        @endphp
                        @if(!empty($hargaList))
                            Rp {{ number_format(max($hargaList), 0, ',', '.') }}
                        @else
                            Rp {{ number_format($bahanBakuData->harga_saat_ini, 0, ',', '.') }}
                        @endif
                    @else
                        Rp {{ number_format($bahanBakuData->harga_saat_ini, 0, ',', '.') }}
                    @endif
                </p>
            </div>
            <div class="p-2 sm:p-3 bg-red-100 rounded-full">
                <i class="fas fa-arrow-up text-red-500 text-sm sm:text-base"></i>
            </div>
        </div>
    </div>
    
    {{-- Lowest Price --}}
    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-gray-600">Harga Terendah</p>
                <p class="text-lg sm:text-2xl font-bold text-green-600">
                    @if(!empty($riwayatHarga))
                        @php
                            $hargaList = array_column($riwayatHarga, 'harga');
                            // Sort to make sure we get the right max/min values
                            sort($hargaList);
                        @endphp
                        @if(!empty($hargaList))
                            Rp {{ number_format(min($hargaList), 0, ',', '.') }}
                        @else
                            Rp {{ number_format($bahanBakuData->harga_saat_ini, 0, ',', '.') }}
                        @endif
                    @else
                        Rp {{ number_format($bahanBakuData->harga_saat_ini, 0, ',', '.') }}
                    @endif
                </p>
            </div>
            <div class="p-2 sm:p-3 bg-green-100 rounded-full">
                <i class="fas fa-arrow-down text-green-500 text-sm sm:text-base"></i>
            </div>
        </div>
    </div>
    
    {{-- Price Trend --}}
    @php
        if (!empty($riwayatHarga)) {
            // Sort riwayat harga by date to ensure correct order
            usort($riwayatHarga, function($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });
            
            $firstPrice = $riwayatHarga[0]['harga'];
            $lastPrice = end($riwayatHarga)['harga'];
            $trend = $lastPrice > $firstPrice ? 'naik' : ($lastPrice < $firstPrice ? 'turun' : 'stabil');
            $trendPercentage = $firstPrice > 0 ? round((($lastPrice - $firstPrice) / $firstPrice) * 100, 2) : 0;
            $daysDiff = \Carbon\Carbon::parse($riwayatHarga[0]['tanggal'])->diffInDays(\Carbon\Carbon::parse(end($riwayatHarga)['tanggal']));
        } else {
            $trend = 'stabil';
            $trendPercentage = 0;
            $daysDiff = 0;
        }
    @endphp
    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 {{ $trend == 'naik' ? 'border-green-500' : ($trend == 'turun' ? 'border-red-500' : 'border-gray-500') }}">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-gray-600">Trend ({{ $daysDiff }} hari)</p>
                <p class="text-base sm:text-lg font-bold {{ $trend == 'naik' ? 'text-green-600' : ($trend == 'turun' ? 'text-red-600' : 'text-gray-600') }}">
                    {{ $trend == 'naik' ? '+' : ($trend == 'turun' ? '' : '') }}{{ $trendPercentage }}%
                </p>
                <p class="text-xs text-gray-500 capitalize">{{ $trend }}</p>
            </div>
            <div class="p-2 sm:p-3 {{ $trend == 'naik' ? 'bg-green-100' : ($trend == 'turun' ? 'bg-red-100' : 'bg-gray-100') }} rounded-full">
                <i class="fas fa-chart-line {{ $trend == 'naik' ? 'text-green-500' : ($trend == 'turun' ? 'text-red-500' : 'text-gray-500') }} text-sm sm:text-base"></i>
            </div>
        </div>
    </div>
</div>

{{-- Price Chart --}}
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 border border-gray-200 mb-4 sm:mb-6">
    <div class="flex items-center mb-3 sm:mb-6">
        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-500 rounded-full flex items-center justify-center mr-2 sm:mr-3">
            <i class="fas fa-chart-line text-white text-xs sm:text-sm"></i>
        </div>
        <h2 class="text-base sm:text-lg lg:text-xl font-bold text-purple-800">Grafik Perubahan Harga Harian</h2>
    </div>
    
    <div class="relative h-48 sm:h-64 lg:h-80">
        <canvas id="priceChart" class="w-full h-full"></canvas>
    </div>
</div>

{{-- Price History Table --}}
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 border border-gray-200">
    <div class="flex items-center mb-3 sm:mb-6">
        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-emerald-500 rounded-full flex items-center justify-center mr-2 sm:mr-3">
            <i class="fas fa-history text-white text-xs sm:text-sm"></i>
        </div>
        <h2 class="text-base sm:text-lg lg:text-xl font-bold text-emerald-800">Riwayat Perubahan Harga per Tanggal</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perubahan</th>
                    <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($riwayatHarga as $index => $item)
                    @php
                        $prevPrice = $index > 0 ? $riwayatHarga[$index - 1]['harga'] : null;
                        $change = $prevPrice ? $item['harga'] - $prevPrice : 0;
                        $changePercent = $prevPrice ? round(($change / $prevPrice) * 100, 2) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-2 sm:px-4 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-4 whitespace-nowrap">
                            <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $item['formatted_tanggal'] }}</div>
                            <div class="text-xs text-gray-500 hidden sm:block">{{ $item['formatted_hari'] }}</div>
                        </td>
                        <td class="px-2 sm:px-4 py-2 sm:py-4 whitespace-nowrap">
                            <div class="text-xs sm:text-sm font-semibold text-gray-900">Rp {{ $item['formatted_harga'] }}</div>
                        </td>
                        <td class="px-2 sm:px-4 py-2 sm:py-4 whitespace-nowrap">
                            @if($item['tipe_perubahan'] === 'naik')
                                <div class="text-xs sm:text-sm text-green-600 font-medium">+Rp {{ $item['formatted_selisih'] }}</div>
                                <div class="text-xs text-green-500">+{{ number_format($item['persentase_perubahan'], 2) }}%</div>
                            @elseif($item['tipe_perubahan'] === 'turun')
                                <div class="text-xs sm:text-sm text-red-600 font-medium">-Rp {{ $item['formatted_selisih'] }}</div>
                                <div class="text-xs text-red-500">{{ number_format($item['persentase_perubahan'], 2) }}%</div>
                            @else
                                <div class="text-xs sm:text-sm text-gray-500">{{ $item['tipe_perubahan'] === 'awal' ? 'Data Pertama' : 'Tidak Ada Perubahan' }}</div>
                            @endif
                        </td>
                        <td class="px-2 sm:px-4 py-2 sm:py-4 whitespace-nowrap">
                            <span class="inline-flex px-1 sm:px-2 py-1 text-xs font-semibold rounded-full {{ $item['badge_class'] }}">
                                <i class="{{ $item['icon'] }} mr-1"></i>
                                <span class="hidden sm:inline">{{ ucfirst($item['tipe_perubahan']) }}</span>
                                <span class="sm:hidden">{{ substr($item['tipe_perubahan'], 0, 1) }}</span>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-2 sm:px-4 py-6 sm:py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-chart-line text-2xl sm:text-4xl text-gray-300 mb-2 sm:mb-4"></i>
                                <p class="text-sm sm:text-lg font-medium">Belum ada data riwayat harga</p>
                                <p class="text-xs sm:text-sm">Data riwayat harga akan muncul setelah ada perubahan harga</p>
                            </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Data untuk chart
    let priceData = @json($riwayatHarga);
    
    // Check if data is empty
    if (!priceData || priceData.length === 0) {
        // Show message instead of chart
        document.getElementById('priceChart').style.display = 'none';
        const chartContainer = document.getElementById('priceChart').parentElement;
        chartContainer.innerHTML = '<div class="flex items-center justify-center h-64"><p class="text-gray-500 text-lg">Belum ada data riwayat harga</p></div>';
        return;
    }
    
    // Sort data by date chronologically (oldest to newest)
    priceData.sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
    
    const labels = priceData.map(item => {
        const date = new Date(item.tanggal);
        return date.toLocaleDateString('id-ID', { 
            day: 'numeric', 
            month: 'short'
        });
    });
    const prices = priceData.map(item => item.harga);
    
    // Konfigurasi Chart.js
    const ctx = document.getElementById('priceChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0.01)');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Harga per {{ $bahanBakuData->satuan }}',
                data: prices,
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(99, 102, 241)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: 'rgb(79, 70, 229)',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: 'rgb(75, 85, 99)',
                        font: {
                            size: 12,
                            weight: '600'
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        title: function(tooltipItems) {
                            const dataIndex = tooltipItems[0].dataIndex;
                            const fullDate = new Date(priceData[dataIndex].tanggal);
                            return fullDate.toLocaleDateString('id-ID', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',  
                                day: 'numeric'
                            });
                        },
                        label: function(context) {
                            return 'Harga: Rp ' + context.parsed.y.toLocaleString('id-ID');
                        },
                        afterLabel: function(context) {
                            // Show price change from previous date
                            const dataIndex = context.dataIndex;
                            if (dataIndex > 0) {
                                const currentPrice = priceData[dataIndex].harga;
                                const prevPrice = priceData[dataIndex - 1].harga;
                                const change = currentPrice - prevPrice;
                                const changePercent = ((change / prevPrice) * 100).toFixed(1);
                                
                                if (change > 0) {
                                    return `Naik: +Rp ${change.toLocaleString('id-ID')} (+${changePercent}%)`;
                                } else if (change < 0) {
                                    return `Turun: Rp ${change.toLocaleString('id-ID')} (${changePercent}%)`;
                                } else {
                                    return 'Tidak ada perubahan';
                                }
                            }
                            return 'Data pertama';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(156, 163, 175, 0.1)',
                        drawBorder: false,
                    },
                    ticks: {
                        color: 'rgb(107, 114, 128)',
                        font: {
                            size: 10
                        },
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(156, 163, 175, 0.1)',
                        drawBorder: false,
                    },
                    ticks: {
                        color: 'rgb(107, 114, 128)',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    },
                    beginAtZero: false
                }
            },
            elements: {
                point: {
                    hoverBorderWidth: 3
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
});
</script>
@endpush
