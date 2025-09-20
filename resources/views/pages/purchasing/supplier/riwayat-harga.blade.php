@extends('layouts.app')
@section('title', 'Riwayat Harga Bahan Baku - Kamil Maju Persada')
@section('content')

{{-- Welcome Banner --}}
<div class="bg-gradient-to-r from-indigo-800 to-purple-800 rounded-xl sm:rounded-2xl p-3 sm:p-6 lg:p-8 mb-4 sm:mb-6 lg:mb-8 text-white shadow-lg mt-2 sm:mt-4 lg:mt-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">Riwayat Harga Harian</h1>
            <p class="text-white text-xs sm:text-base lg:text-lg">Perubahan harga per tanggal: {{ $bahanBakuData->nama }} dari {{ $supplierData->nama }}</p>
        </div>
        <div class="hidden lg:block">
            <i class="fas fa-chart-line text-6xl text-white opacity-20"></i>
        </div>
    </div>
</div>

{{-- Back Button --}}
<div class="mb-4 sm:mb-6">
    <a href="javascript:history.back()" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
        <i class="fas fa-arrow-left mr-2"></i>
        <span class="font-semibold">Kembali</span>
    </a>
</div>

{{-- Bahan Baku Info Card --}}
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-6 border border-gray-200 mb-6">
    <div class="flex items-center mb-4">
        <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-cube text-white text-sm"></i>
        </div>
        <h2 class="text-lg sm:text-xl font-bold text-indigo-800">Informasi Bahan Baku</h2>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
            <div class="flex items-center mb-2">
                <i class="fas fa-tag text-blue-500 mr-2"></i>
                <span class="text-sm font-semibold text-blue-700">Nama Bahan Baku</span>
            </div>
            <p class="text-lg font-bold text-gray-800">{{ $bahanBakuData->nama }}</p>
        </div>
        
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
            <div class="flex items-center mb-2">
                <i class="fas fa-weight-hanging text-green-500 mr-2"></i>
                <span class="text-sm font-semibold text-green-700">Satuan</span>
            </div>
            <p class="text-lg font-bold text-gray-800">{{ $bahanBakuData->satuan }}</p>
        </div>
        
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
            <div class="flex items-center mb-2">
                <i class="fas fa-building text-purple-500 mr-2"></i>
                <span class="text-sm font-semibold text-purple-700">Supplier</span>
            </div>
            <p class="text-lg font-bold text-gray-800">{{ $bahanBakuData->supplier_nama }}</p>
        </div>
    </div>
</div>

{{-- Price Statistics Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Current Price --}}
    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Harga Saat Ini</p>
                <p class="text-2xl font-bold text-blue-600">Rp {{ number_format(end($riwayatHarga)['harga'], 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full">
                <i class="fas fa-money-bill-wave text-blue-500"></i>
            </div>
        </div>
    </div>
    
    {{-- Highest Price --}}
    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Harga Tertinggi</p>
                <p class="text-2xl font-bold text-red-600">Rp {{ number_format(max(array_column($riwayatHarga, 'harga')), 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-red-100 rounded-full">
                <i class="fas fa-arrow-up text-red-500"></i>
            </div>
        </div>
    </div>
    
    {{-- Lowest Price --}}
    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Harga Terendah</p>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format(min(array_column($riwayatHarga, 'harga')), 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
                <i class="fas fa-arrow-down text-green-500"></i>
            </div>
        </div>
    </div>
    
    {{-- Price Trend --}}
    @php
        $firstPrice = $riwayatHarga[0]['harga'];
        $lastPrice = end($riwayatHarga)['harga'];
        $trend = $lastPrice > $firstPrice ? 'naik' : ($lastPrice < $firstPrice ? 'turun' : 'stabil');
        $trendPercentage = $firstPrice > 0 ? round((($lastPrice - $firstPrice) / $firstPrice) * 100, 2) : 0;
        $daysDiff = \Carbon\Carbon::parse($riwayatHarga[0]['tanggal'])->diffInDays(\Carbon\Carbon::parse(end($riwayatHarga)['tanggal']));
    @endphp
    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 {{ $trend == 'naik' ? 'border-orange-500' : ($trend == 'turun' ? 'border-blue-500' : 'border-gray-500') }}">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Trend ({{ $daysDiff }} hari)</p>
                <p class="text-lg font-bold {{ $trend == 'naik' ? 'text-orange-600' : ($trend == 'turun' ? 'text-blue-600' : 'text-gray-600') }}">
                    {{ $trend == 'naik' ? '+' : ($trend == 'turun' ? '' : '') }}{{ $trendPercentage }}%
                </p>
                <p class="text-xs text-gray-500 capitalize">{{ $trend }}</p>
            </div>
            <div class="p-3 {{ $trend == 'naik' ? 'bg-orange-100' : ($trend == 'turun' ? 'bg-blue-100' : 'bg-gray-100') }} rounded-full">
                <i class="fas fa-chart-line {{ $trend == 'naik' ? 'text-orange-500' : ($trend == 'turun' ? 'text-blue-500' : 'text-gray-500') }}"></i>
            </div>
        </div>
    </div>
</div>

{{-- Price Chart --}}
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-6 border border-gray-200 mb-6">
    <div class="flex items-center mb-6">
        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-chart-line text-white text-sm"></i>
        </div>
        <h2 class="text-lg sm:text-xl font-bold text-purple-800">Grafik Perubahan Harga Harian</h2>
    </div>
    
    <div class="relative h-64 sm:h-80">
        <canvas id="priceChart" class="w-full h-full"></canvas>
    </div>
</div>

{{-- Price History Table --}}
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-6 border border-gray-200">
    <div class="flex items-center mb-6">
        <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-history text-white text-sm"></i>
        </div>
        <h2 class="text-lg sm:text-xl font-bold text-emerald-800">Riwayat Perubahan Harga per Tanggal</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Update</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perubahan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($riwayatHarga as $index => $item)
                    @php
                        $prevPrice = $index > 0 ? $riwayatHarga[$index - 1]['harga'] : null;
                        $change = $prevPrice ? $item['harga'] - $prevPrice : 0;
                        $changePercent = $prevPrice ? round(($change / $prevPrice) * 100, 2) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($item['tanggal'])->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($item['tanggal'])->format('l') }}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($item['harga'], 0, ',', '.') }}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($change > 0)
                                <div class="text-sm text-red-600 font-medium">+Rp {{ number_format($change, 0, ',', '.') }}</div>
                                <div class="text-xs text-red-500">+{{ $changePercent }}%</div>
                            @elseif($change < 0)
                                <div class="text-sm text-green-600 font-medium">-Rp {{ number_format(abs($change), 0, ',', '.') }}</div>
                                <div class="text-xs text-green-500">{{ $changePercent }}%</div>
                            @else
                                <div class="text-sm text-gray-500">Data Pertama</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($change > 0)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    Naik
                                </span>
                            @elseif($change < 0)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-arrow-down mr-1"></i>
                                    Turun
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="fas fa-minus mr-1"></i>
                                    Awal
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
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
    const priceData = @json($riwayatHarga);
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
