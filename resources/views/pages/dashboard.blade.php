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
                    $today = \Carbon\Carbon::now();
                    $dayOfMonth = $today->day;
                    $currentWeekOfMonth = 1;
                    
                    if ($dayOfMonth >= 1 && $dayOfMonth <= 7) {
                        $currentWeekOfMonth = 1;
                    } elseif ($dayOfMonth >= 8 && $dayOfMonth <= 14) {
                        $currentWeekOfMonth = 2;
                    } elseif ($dayOfMonth >= 15 && $dayOfMonth <= 21) {
                        $currentWeekOfMonth = 3;
                    } else {
                        $currentWeekOfMonth = 4;
                    }
                    
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
        {{-- Pengiriman Normal Minggu Ini --}}
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow cursor-pointer" 
             onclick="showPengirimanModal('normal')">
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
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow cursor-pointer"
             onclick="showPengirimanModal('bongkar')">
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
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow cursor-pointer"
             onclick="showPengirimanModal('gagal')">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-500 text-xl"></i>
                </div>
                <h3 class="text-sm text-gray-500">Pengiriman Ditolak</h3>
            </div>
            <p class="text-2xl font-bold text-red-600 mb-1">{{ count($pengirimanGagalList) }}</p>
            <p class="text-sm text-gray-500">Minggu Ini</p>
        </div>
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

{{-- Modal Pengiriman Detail --}}
<div id="pengirimanModal" class="fixed inset-0 bg-white/20 backdrop-blur-xs hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-lg bg-white">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between pb-3 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Detail Pengiriman</h3>
            <div class="flex items-center gap-2">
                <button onclick="downloadPDF()" 
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition flex items-center gap-2">
                    <i class="fas fa-download"></i>
                    <span>Download PDF</span>
                </button>
                <button onclick="closePengirimanModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        {{-- Modal Body --}}
        <div class="mt-4 max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. PO</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Klien</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PIC</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">QTY Kirim</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase" id="headerExtra">Persentase</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody id="modalTableBody" class="divide-y divide-gray-200">
                    <!-- Data will be inserted here -->
                </tbody>
            </table>
            <div id="emptyState" class="hidden text-center py-8 text-gray-400">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Tidak ada data</p>
            </div>
        </div>
        
        {{-- Modal Footer --}}
        <div class="flex justify-end pt-4 border-t mt-4">
            <button onclick="closePengirimanModal()" 
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- Chart.js Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

{{-- Omset Charts Section --}}
<div class="space-y-6">
    {{-- Margin Analysis Minggu Ini --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-chart-line text-green-500"></i>
                    Margin Minggu Ini
                </h2>
                <p class="text-sm text-gray-500 mt-1">Seluruh pengiriman dengan margin minggu ini ({{ count($topMarginMingguIni) }} pengiriman)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('dashboard.margin-minggu-ini.pdf') }}" 
                   class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    Download PDF
                </a>
                <a href="{{ route('laporan.margin') }}" 
                   class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm flex items-center gap-2">
                    <i class="fas fa-eye"></i>
                    Lihat Detail
                </a>
            </div>
        </div>

        {{-- Summary Cards Margin Minggu Ini --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            {{-- Total Margin Minggu Ini --}}
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Nilai Margin Minggu Ini</p>
                        <p class="text-2xl font-bold {{ $totalMarginMingguIni >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($totalMarginMingguIni, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Gross Margin % Minggu Ini --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Prosentase Margin Minggu Ini</p>
                        <p class="text-2xl font-bold {{ $grossMarginMingguIni >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ number_format($grossMarginMingguIni, 2) }}%
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-percentage text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        @if(count($topMarginMingguIni) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC Procurement</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pabrik</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($topMarginMingguIni as $index => $item)
                            <tr class="hover:bg-gray-100 transition-colors cursor-pointer" 
                                onclick="window.location.href='{{ route('purchasing.pengiriman.index') }}?tab={{ $item['status'] === 'berhasil' ? 'pengiriman-berhasil' : ($item['status'] === 'menunggu_verifikasi' ? 'menunggu-verifikasi' : 'menunggu-fisik') }}&detail={{ $item['pengiriman_id'] }}'">
                                <td class="px-4 py-3 text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-gray-900 font-medium">{{ $item['pic_purchasing'] }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $item['klien'] }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $item['supplier'] }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $item['bahan_baku'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold {{ $item['margin'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($item['margin'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $item['margin_percentage'] >= 20 ? 'bg-green-100 text-green-800' : 
                                           ($item['margin_percentage'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ number_format($item['margin_percentage'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Belum ada data margin untuk minggu ini</p>
            </div>
        @endif

        {{-- Gross Margin Bulan Ini --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Prosentase Gross Profit Bulan {{ \Carbon\Carbon::now()->format('F Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold {{ $grossMarginBulanIni >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($grossMarginBulanIni, 2) }}%
                        </p>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include Klien Chart Section --}}
    @include('pages.laporan.partials.klien_chart_dashboard')

    {{-- Include Supplier Chart Section --}}
    @include('pages.laporan.partials.supplier_chart_dashboard')

    {{-- Include Bahan Baku Chart Section --}}
    @include('pages.laporan.partials.bahan_baku_chart_dashboard')
</div>

{{-- Modal Script --}}
<script>
// Data pengiriman dari backend
const pengirimanData = {
    normal: @json($pengirimanNormalList),
    bongkar: @json($pengirimanBongkarSebagianList),
    gagal: @json($pengirimanGagalList)
};

// Variable untuk menyimpan tipe pengiriman yang sedang dibuka
let currentModalType = '';
let currentModalData = [];

// Helper function to get tab name based on modal type and status  
function getTabName() {
    if (currentModalType === 'gagal') {
        return 'gagal';
    }
    // For normal and bongkar, they can be berhasil, menunggu_verifikasi, or menunggu_fisik
    // We'll use berhasil as default since most will be completed
    return 'masuk'; // Go to masuk tab to see all pending shipments
}

// Helper function to get individual tab based on status
function getTabByStatus(status) {
    switch(status) {
        case 'berhasil':
            return 'pengiriman-berhasil';
        case 'menunggu_verifikasi':
            return 'menunggu-verifikasi';
        case 'menunggu_fisik':
            return 'menunggu-fisik';
        case 'gagal':
            return 'pengiriman-gagal';
        default:
            return 'pengiriman-masuk';
    }
}

function showPengirimanModal(type) {
    const modal = document.getElementById('pengirimanModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalTableBody = document.getElementById('modalTableBody');
    const emptyState = document.getElementById('emptyState');
    const headerExtra = document.getElementById('headerExtra');
    
    // Set title based on type
    let title = '';
    let data = [];
    
    switch(type) {
        case 'normal':
            title = 'Pengiriman Normal (>70%)';
            data = pengirimanData.normal;
            headerExtra.textContent = 'Persentase';
            break;
        case 'bongkar':
            title = 'Bongkar Sebagian (≤70%)';
            data = pengirimanData.bongkar;
            headerExtra.textContent = 'Persentase';
            break;
        case 'gagal':
            title = 'Pengiriman Ditolak';
            data = pengirimanData.gagal;
            headerExtra.textContent = 'Keterangan';
            break;
    }
    
    // Simpan data untuk download PDF
    currentModalType = type;
    currentModalData = data;
    
    modalTitle.textContent = title;
    
    // Clear previous data
    modalTableBody.innerHTML = '';
    
    // Check if data is empty
    if (data.length === 0) {
        emptyState.classList.remove('hidden');
        modalTableBody.classList.add('hidden');
    } else {
        emptyState.classList.add('hidden');
        modalTableBody.classList.remove('hidden');
        
        // Populate table
        data.forEach((item, index) => {
            const row = document.createElement('tr');
            row.className = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
            
            // Format tanggal
            const tanggal = new Date(item.tanggal_kirim).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
            
            // Format status
            let statusBadge = '';
            switch(item.status) {
                case 'berhasil':
                    statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Berhasil</span>';
                    break;
                case 'menunggu_verifikasi':
                    statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Menunggu Verifikasi</span>';
                    break;
                case 'menunggu_fisik':
                    statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Menunggu Fisik</span>';
                    break;
                case 'gagal':
                    statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Gagal</span>';
                    break;
                default:
                    statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">' + item.status + '</span>';
            }
            
            // Extra column content
            let extraContent = '';
            if (type === 'gagal') {
                extraContent = item.catatan || '-';
            } else {
                const percentage = item.percentage || 0;
                const percentageColor = percentage > 70 ? 'text-green-600' : 'text-yellow-600';
                extraContent = `<span class="font-semibold ${percentageColor}">${percentage}%</span><br><small class="text-gray-500">${item.total_qty_kirim} / ${item.total_qty_forecast}</small>`;
            }
            
            row.innerHTML = `
                <td class="px-4 py-3">
                    <a href="{{ route('purchasing.pengiriman.index') }}?tab=${getTabByStatus(item.status)}&detail=${item.id}" class="text-blue-600 hover:text-blue-800 hover:underline">
                        ${item.po_number || '-'}
                    </a>
                </td>
                <td class="px-4 py-3 text-gray-600">${tanggal}</td>
                <td class="px-4 py-3 text-gray-900">${item.klien}${item.cabang ? ' (' + item.cabang + ')' : ''}</td>
                <td class="px-4 py-3 text-gray-600">${item.purchasing}</td>
                <td class="px-4 py-3 text-right text-gray-900">${Number(item.total_qty_kirim).toLocaleString('id-ID')}</td>
                <td class="px-4 py-3 text-right">${extraContent}</td>
                <td class="px-4 py-3 text-center">${statusBadge}</td>
            `;
            
            // Add click event to entire row
            row.style.cursor = 'pointer';
            row.classList.add('hover:bg-gray-100', 'transition-colors');
            row.addEventListener('click', function(e) {
                // Don't navigate if clicking on the link itself
                if (e.target.tagName !== 'A' && !e.target.closest('a')) {
                    window.location.href = `{{ route('purchasing.pengiriman.index') }}?tab=${getTabByStatus(item.status)}&detail=${item.id}`;
                }
            });
            
            modalTableBody.appendChild(row);
        });
    }
    
    // Show modal
    modal.classList.remove('hidden');
}

function closePengirimanModal() {
    const modal = document.getElementById('pengirimanModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('pengirimanModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePengirimanModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePengirimanModal();
    }
});

// Function to download PDF
function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4'); // landscape orientation
    
    // Get current date for filename
    const now = new Date();
    const dateStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
    
    // Determine title and filename based on type
    let title = '';
    let filename = '';
    
    switch(currentModalType) {
        case 'normal':
            title = 'Laporan Pengiriman Normal (>70%)';
            filename = `Pengiriman_Normal_${dateStr}.pdf`;
            break;
        case 'bongkar':
            title = 'Laporan Bongkar Sebagian (≤70%)';
            filename = `Bongkar_Sebagian_${dateStr}.pdf`;
            break;
        case 'gagal':
            title = 'Laporan Pengiriman Gagal';
            filename = `Pengiriman_Gagal_${dateStr}.pdf`;
            break;
    }
    
    // Add title
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text(title, 148, 15, { align: 'center' });
    
    // Add date info
    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text(`Tanggal: ${dateStr}`, 148, 22, { align: 'center' });
    doc.text(`Total: ${currentModalData.length} pengiriman`, 148, 27, { align: 'center' });
    
    // Prepare table data
    const headers = currentModalType === 'gagal' 
        ? [['No. PO', 'Tanggal', 'Klien', 'PIC', 'QTY Kirim', 'Keterangan', 'Status']]
        : [['No. PO', 'Tanggal', 'Klien', 'PIC', 'QTY Kirim', 'Persentase', 'Status']];
    
    const tableData = currentModalData.map(item => {
        const tanggal = new Date(item.tanggal_kirim).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
        
        const klien = item.klien + (item.cabang ? ' (' + item.cabang + ')' : '');
        const qtyKirim = Number(item.total_qty_kirim).toLocaleString('id-ID');
        
        let status = '';
        switch(item.status) {
            case 'berhasil':
                status = 'Berhasil';
                break;
            case 'menunggu_verifikasi':
                status = 'Menunggu Verifikasi';
                break;
            case 'menunggu_fisik':
                status = 'Menunggu Fisik';
                break;
            case 'gagal':
                status = 'Gagal';
                break;
            default:
                status = item.status;
        }
        
        if (currentModalType === 'gagal') {
            return [
                item.po_number || '-',
                tanggal,
                klien,
                item.purchasing,
                qtyKirim,
                item.catatan || '-',
                status
            ];
        } else {
            const percentage = item.percentage || 0;
            const qtyInfo = qtyKirim + ' / ' + Number(item.total_qty_forecast).toLocaleString('id-ID');
            return [
                item.po_number || '-',
                tanggal,
                klien,
                item.purchasing,
                qtyKirim,
                percentage + '% (' + qtyInfo + ')',
                status
            ];
        }
    });
    
    // Add table
    doc.autoTable({
        startY: 32,
        head: headers,
        body: tableData,
        theme: 'grid',
        styles: {
            fontSize: 8,
            cellPadding: 2,
        },
        headStyles: {
            fillColor: [68, 114, 196],
            textColor: 255,
            fontStyle: 'bold',
            halign: 'center'
        },
        columnStyles: {
            0: { cellWidth: 30 }, // No. PO
            1: { cellWidth: 25 }, // Tanggal
            2: { cellWidth: 50 }, // Klien
            3: { cellWidth: 35 }, // PIC
            4: { cellWidth: 25, halign: 'right' }, // QTY Kirim
            5: { cellWidth: currentModalType === 'gagal' ? 60 : 45 }, // Persentase/Keterangan
            6: { cellWidth: 30, halign: 'center' } // Status
        },
        didDrawPage: function(data) {
            // Footer
            doc.setFontSize(8);
            doc.setTextColor(128);
            doc.text(
                'Dicetak pada: ' + new Date().toLocaleString('id-ID'),
                data.settings.margin.left,
                doc.internal.pageSize.height - 10
            );
            doc.text(
                'Halaman ' + doc.internal.getNumberOfPages(),
                doc.internal.pageSize.width - data.settings.margin.right - 20,
                doc.internal.pageSize.height - 10
            );
        }
    });
    
    // Save PDF
    doc.save(filename);
}
</script>

@endsection
