@extends('layouts.app')
@section('title', 'Dashboard - Kamil Maju Persada')
@section('content')

<div class="container mx-auto py-6 space-y-6">
    {{-- Header --}}
    <x-welcome-banner title="Dashboard" subtitle="Lihat Ringkasan Mingguan" icon="fas fa-tachometer-alt" />


    {{-- OMSET MINGGUAN - PRIORITAS UTAMA --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Omset Minggu Ini</h2>
                @php
                    // Calculate week number within the month (1-4) - same as laporan
                    $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
                    $currentWeekOfMonth = 1;
                    $tempDate = $startOfMonth->copy();
                    
                    while ($tempDate->addDays(7)->lte(\Carbon\Carbon::now()->startOfWeek())) {
                        $currentWeekOfMonth++;
                    }
                    $currentWeekOfMonth = min($currentWeekOfMonth, 4);
                    
                    // Calculate date range for this week based on month divisions
                    if ($currentWeekOfMonth == 1) {
                        $weekStart = $startOfMonth->copy();
                    } else {
                        $weekStart = $startOfMonth->copy()->addDays(($currentWeekOfMonth - 1) * 7);
                    }
                    
                    if ($currentWeekOfMonth == 4) {
                        $weekEnd = $startOfMonth->copy()->endOfMonth();
                    } else {
                        $weekEnd = $weekStart->copy()->addDays(6)->min($startOfMonth->copy()->endOfMonth());
                    }
                @endphp
                <p class="text-sm text-gray-500 mt-1">
                    {{ $weekStart->format('d M') }} - {{ $weekEnd->format('d M Y') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Realisasi --}}
            <div class="bg-gray-50 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Realisasi</p>
                <h3 class="text-3xl font-bold text-gray-900 mb-2">
                    @if($omsetMingguIni >= 1000000000)
                        Rp {{ number_format($omsetMingguIni / 1000000000, 1, ',', '.') }}M
                    @elseif($omsetMingguIni >= 1000000)
                        Rp {{ number_format($omsetMingguIni / 1000000, 1, ',', '.') }}Jt
                    @else
                        Rp {{ number_format($omsetMingguIni, 0, ',', '.') }}
                    @endif
                </h3>
                
                {{-- Breakdown Sistem & Manual --}}
                <div class="mt-3 pt-3 border-t border-gray-200 space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600 flex items-center">(Sistem) 
                        </span>
                        <span class="font-semibold text-gray-700">
                           Rp {{ number_format($omsetSistemMingguIni / 1000000, 1, ',', '.') }}Jt
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600 flex items-center">(Manual)
                        </span>
                        <span class="font-semibold text-gray-700">
                            Rp {{ number_format($omsetManualMingguIni / 1000000, 1, ',', '.') }}Jt
                        </span>
                    </div>
                </div>
                
                <div class="mt-3">
                    @if($progressMinggu >= 100)
                        <span class="inline-flex items-center gap-1 text-sm text-green-600">
                            <i class="fas fa-check-circle"></i> Target Tercapai
                        </span>
                    @elseif($progressMinggu >= 75)
                        <span class="inline-flex items-center gap-1 text-sm text-yellow-600">
                            <i class="fas fa-clock"></i> Hampir Tercapai
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle"></i> Perlu Ditingkatkan
                        </span>
                    @endif
                </div>
            </div>

            {{-- Target --}}
            <div class="bg-gray-50 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Target Mingguan</p>
                <h3 class="text-3xl font-bold text-gray-900 mb-3">
                    @if($targetMingguanAdjusted >= 1000000000)
                        Rp {{ number_format($targetMingguanAdjusted / 1000000000, 1, ',', '.') }}M
                    @elseif($targetMingguanAdjusted >= 1000000)
                        Rp {{ number_format($targetMingguanAdjusted / 1000000, 1, ',', '.') }}Jt
                    @else
                        Rp {{ number_format($targetMingguanAdjusted, 0, ',', '.') }}
                    @endif
                </h3>
                <p class="text-sm text-gray-500">Per minggu</p>
            </div>

            {{-- Progress --}}
            <div class="bg-gray-50 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Progress</p>
                <h3 class="text-3xl font-bold text-gray-900 mb-3">{{ number_format($progressMinggu, 1) }}%</h3>
                <div class="w-full bg-gray-300 rounded-full h-3 mb-3">
                    <div class="{{ $progressMinggu >= 100 ? 'bg-green-500' : 'bg-blue-500' }} h-full rounded-full" style="width: {{ min($progressMinggu, 100) }}%"></div>
                </div>
                <p class="text-sm text-gray-500">
                    @if($targetMingguanAdjusted > $omsetMingguIni)
                        Kurang Rp {{ number_format(($targetMingguanAdjusted - $omsetMingguIni) / 1000000, 1, ',', '.') }}Jt
                    @else
                        Lebih Rp {{ number_format(($omsetMingguIni - $targetMingguanAdjusted) / 1000000, 1, ',', '.') }}Jt
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Ringkasan Utama --}}
    <div class="grid grid-cols-3 md:grid-cols-3 gap-4">
        {{-- Outstanding PO --}}
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                </div>
                <h3 class="text-sm text-gray-500">Outstanding PO</h3>
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-1">
                @if($totalOutstanding >= 1000000000)
                    Rp {{ number_format($totalOutstanding / 1000000000, 1, ',', '.') }}M
                @elseif($totalOutstanding >= 1000000)
                    Rp {{ number_format($totalOutstanding / 1000000, 1, ',', '.') }}Jt
                @else
                    Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
                @endif
            </p>
            <p class="text-sm text-gray-500">{{ number_format($poBerjalan) }} PO Berjalan</p>
        </div>

        {{-- Omset Bulan Ini --}}
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-green-500 text-xl"></i>
                </div>
                <h3 class="text-sm text-gray-500">Omset Bulan Ini</h3>
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-1">
                @if($omsetBulanIni >= 1000000000)
                    Rp {{ number_format($omsetBulanIni / 1000000000, 1, ',', '.') }}M
                @elseif($omsetBulanIni >= 1000000)
                    Rp {{ number_format($omsetBulanIni / 1000000, 1, ',', '.') }}Jt
                @else
                    Rp {{ number_format($omsetBulanIni, 0, ',', '.') }}
                @endif
            </p>
            
            {{-- Breakdown Sistem & Manual --}}
            <div class="flex items-center gap-2 mt-2 mb-1 text-xs text-gray-600">
                <span class="inline-flex items-center gap-1" title="Omset dari sistem">
                    (Sistem) {{ number_format($omsetSistemBulanIni / 1000000, 1, ',', '.') }}Jt
                </span>
                <span class="text-gray-300">|</span>
                <span class="inline-flex items-center gap-1" title="Omset manual">
                    (Manual) {{ number_format($omsetManualBulanIni / 1000000, 1, ',', '.') }}Jt
                </span>
            </div>
            
            <p class="text-sm text-gray-500">{{ number_format($progressBulan, 1) }}% dari target</p>
        </div>
            {{-- Order Bulan Ini --}}
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-purple-500 text-xl"></i>
                </div>
                <h3 class="text-sm text-gray-500">Order Bulan Ini</h3>
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($orderBulanIni) }}</p>
            <p class="text-sm text-gray-500">
                @if($nilaiOrderBulanIni >= 1000000000)
                    Rp {{ number_format($nilaiOrderBulanIni / 1000000000, 1, ',', '.') }}M
                @elseif($nilaiOrderBulanIni >= 1000000)
                    Rp {{ number_format($nilaiOrderBulanIni / 1000000, 1, ',', '.') }}Jt
                @else
                    Rp {{ number_format($nilaiOrderBulanIni, 0, ',', '.') }}
                @endif
            </p>
    </div>
    </div>

    {{-- Informasi Pengiriman Minggu Ini --}}
    <div class="grid grid-cols-3 md:grid-cols-3 gap-4">
        {{-- Pengiriman Berhasil (Selain Bongkar Sebagian) Minggu Ini --}}
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
                <h3 class="text-sm text-gray-500">Pengiriman Normal</h3>
            </div>
            <p class="text-2xl font-bold text-green-600 mb-1">{{ number_format($pengirimanNormalMingguIni) }}</p>
            <p class="text-sm text-gray-500">Minggu Ini (>70%)</p>
        </div>

        {{-- Bongkar Sebagian (<=70%) Minggu Ini --}}
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box-open text-yellow-500 text-xl"></i>
                </div>
                <h3 class="text-sm text-gray-500">Bongkar Sebagian</h3>
            </div>
            <p class="text-2xl font-bold text-yellow-600 mb-1">{{ number_format($pengirimanBongkarSebagianMingguIni) }}</p>
            <p class="text-sm text-gray-500">Minggu Ini (≤70%)</p>
        </div>

        {{-- Pengiriman Gagal Minggu Ini --}}
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-500 text-xl"></i>
                </div>
                <h3 class="text-sm text-gray-500">Pengiriman Gagal</h3>
            </div>
            <p class="text-2xl font-bold text-red-600 mb-1">{{ number_format($pengirimanGagalMingguIni) }}</p>
            <p class="text-sm text-gray-500">Minggu Ini</p>
        </div>
    </div>



    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Trend Omset 4 Minggu --}}
        <div class="bg-white rounded-lg shadow-md p-5">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900">Trend Omset 4 Minggu</h3>
                <p class="text-sm text-gray-500">vs target mingguan</p>
            </div>
            <div style="height: 250px;">
                <canvas id="chartOmsetTrend"></canvas>
            </div>
        </div>

        {{-- PO by Status --}}
        <div class="bg-white rounded-lg shadow-md p-5">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900">Status Purchase Order</h3>
                <p class="text-sm text-gray-500">Distribusi PO saat ini</p>
            </div>
            <div class="flex justify-center items-center" style="height: 250px;">
                @if($poByStatus->count() > 0)
                    <canvas id="chartPOStatus"></canvas>
                @else
                    <div class="text-center text-gray-400">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p class="text-sm">Tidak ada data PO</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Top 5 Klien Bulan Ini --}}
    <div class="bg-white rounded-lg shadow-md p-5">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-gray-900">Top 5 Klien Bulan Ini</h3>
            <p class="text-sm text-gray-500">Berdasarkan nilai order</p>
        </div>
        
        @if($topKlien->count() > 0)
            <div class="overflow-x-auto -mx-5">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="text-left py-3 px-5 text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="text-left py-3 px-5 text-xs font-medium text-gray-500 uppercase">Klien</th>
                            <th class="text-center py-3 px-5 text-xs font-medium text-gray-500 uppercase">PO</th>
                            <th class="text-right py-3 px-5 text-xs font-medium text-gray-500 uppercase">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topKlien as $index => $klien)
                        <tr class="border-t border-gray-100">
                            <td class="py-4 px-5">
                                @if($index == 0)
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-yellow-50 text-yellow-500 font-bold text-lg">
                                        <i class="fas fa-trophy"></i>
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-50 text-gray-600 font-semibold">
                                        {{ $index + 1 }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-5 font-medium text-gray-900">{{ $klien->klien_nama }}</td>
                            <td class="py-4 px-5 text-center text-gray-600">{{ number_format($klien->total_po) }}</td>
                            <td class="py-4 px-5 text-right font-semibold text-gray-900">
                                @if($klien->total_nilai >= 1000000000)
                                    Rp {{ number_format($klien->total_nilai / 1000000000, 2, ',', '.') }} Miliar
                                @elseif($klien->total_nilai >= 1000000)
                                    Rp {{ number_format($klien->total_nilai / 1000000, 2, ',', '.') }} Juta
                                @else
                                    Rp {{ number_format($klien->total_nilai, 0, ',', '.') }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p class="text-sm">Belum ada data order bulan ini</p>
            </div>
        @endif
    </div>

    {{-- Target Progress Bars --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Progress Minggu --}}
        <div class="bg-white rounded-lg shadow-md p-5">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm text-gray-600">Target Minggu Ini</h4>
                <span class="text-sm font-bold {{ $progressMinggu >= 100 ? 'text-green-600' : 'text-gray-900' }}">
                    {{ number_format($progressMinggu, 1) }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-full rounded-full {{ $progressMinggu >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" 
                     style="width: {{ min($progressMinggu, 100) }}%"></div>
            </div>
        </div>

        {{-- Progress Bulan --}}
        <div class="bg-white rounded-lg shadow-md p-5">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm text-gray-600">Target Bulan Ini</h4>
                <span class="text-sm font-bold {{ $progressBulan >= 100 ? 'text-green-600' : 'text-gray-900' }}">
                    {{ number_format($progressBulan, 1) }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-full rounded-full {{ $progressBulan >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" 
                     style="width: {{ min($progressBulan, 100) }}%"></div>
            </div>
        </div>

        {{-- Progress Tahun --}}
        <div class="bg-white rounded-lg shadow-md p-5">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm text-gray-600">Target Tahun Ini</h4>
                <span class="text-sm font-bold {{ $progressTahun >= 100 ? 'text-green-600' : 'text-gray-900' }}">
                    {{ number_format($progressTahun, 1) }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-full rounded-full {{ $progressTahun >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" 
                     style="width: {{ min($progressTahun, 100) }}%"></div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Trend Omset Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctxTrend = document.getElementById('chartOmsetTrend').getContext('2d');
    new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: @json(array_column($omsetTrend, 'label')),
            datasets: [{
                label: 'Omset',
                data: @json(array_column($omsetTrend, 'omset')),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 2,
                borderRadius: 6
            }, {
                label: 'Target',
                data: @json(array_column($omsetTrend, 'target')),
                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 2,
                borderDash: [5, 5],
                type: 'line',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                },
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
                            return context.dataset.label + ': ' + formattedValue;
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
                                return 'Rp ' + (value / 1000000000).toFixed(1) + 'M';
                            } else if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                            } else {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        }
    });

    // PO Status Chart
    @if($poByStatus->count() > 0)
    const ctxStatus = document.getElementById('chartPOStatus').getContext('2d');
    const isMobile = window.innerWidth < 768;
    
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: [@foreach($poByStatus as $item) '{{ ucfirst($item->status) }}', @endforeach],
            datasets: [{
                data: [@foreach($poByStatus as $item) {{ $item->total }}, @endforeach],
                backgroundColor: [
                    'rgb(59, 130, 246)',   // blue
                    'rgb(16, 185, 129)',   // green
                    'rgb(245, 158, 11)',   // yellow
                    'rgb(239, 68, 68)',    // red
                    'rgb(139, 92, 246)',   // purple
                ],
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
                        padding: isMobile ? 10 : 15,
                        font: { size: isMobile ? 10 : 12 },
                        boxWidth: isMobile ? 12 : 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' PO';
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>

@endsection
