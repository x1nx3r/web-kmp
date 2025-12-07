{{-- Target Omset Analysis Section --}}
<div class="mb-6">
    {{-- Card: Target Setting Header --}}
    <div class="bg-green-500 rounded-xl shadow-lg p-6 mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="text-white flex-1">
                <h3 class="text-2xl font-bold mb-2 flex items-center">
                    Analisis Target Omset 
                    <div class="ml-4 flex items-center space-x-2 bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1">
                        <button onclick="changeTargetYearSlide('prev')" 
                                id="prevYearBtn"
                                class="text-white hover:text-indigo-200 transition-colors p-1 rounded hover:bg-white/20">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="currentYearDisplay" class="text-xl font-bold text-white px-3 min-w-[80px] text-center">
                            {{ $selectedYearTarget }}
                        </span>
                        <button onclick="changeTargetYearSlide('next')" 
                                id="nextYearBtn"
                                class="text-white hover:text-indigo-200 transition-colors p-1 rounded hover:bg-white/20">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </h3>
                <p class="text-indigo-100">Pantau progres pencapaian target omset perusahaan secara real-time</p>
            </div>
            @if(auth()->user()->role === 'direktur')
                <button type="button" 
                        onclick="openTargetModal()"
                        class="px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-cog mr-2"></i>Set Target Tahunan
                </button>
            @else
                <div class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-lg text-white text-sm">
                    <i class="fas fa-lock mr-2"></i>View Only
                </div>
            @endif
        </div>
    </div>

    {{-- Progress Cards Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        {{-- Weekly Progress --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                        Minggu Ini
                        <span class="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full">Week {{ date('W') }}</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1">Target: Rp {{ number_format($targetMingguan ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="w-16 h-16">
                    <canvas id="chartProgressMinggu"></canvas>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Pencapaian:</span>
                    <span class="font-bold text-green-600">Rp {{ number_format($omsetMingguIni ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Progress:</span>
                    <span class="font-bold {{ ($progressMinggu ?? 0) >= 100 ? 'text-green-600' : (($progressMinggu ?? 0) >= 75 ? 'text-blue-600' : 'text-orange-600') }}">
                        {{ number_format($progressMinggu ?? 0, 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ min(100, $progressMinggu ?? 0) }}%"></div>
                </div>
                <div class="flex justify-between text-sm pt-2 border-t border-gray-100">
                    <span class="text-gray-600">Sisa Target:</span>
                    <span class="font-bold text-orange-600">
                        Rp {{ number_format(max(0, ($targetMingguan ?? 0) - ($omsetMingguIni ?? 0)), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Monthly Progress --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                        Bulan Ini
                        <span class="ml-2 text-xs bg-purple-100 text-purple-600 px-2 py-1 rounded-full">{{ date('M') }}</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1">Target: Rp {{ number_format($targetBulanan ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="w-16 h-16">
                    <canvas id="chartProgressBulan"></canvas>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Pencapaian:</span>
                    <span class="font-bold text-green-600">Rp {{ number_format($omsetBulanIni ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Progress:</span>
                    <span class="font-bold {{ ($progressBulan ?? 0) >= 100 ? 'text-green-600' : (($progressBulan ?? 0) >= 75 ? 'text-blue-600' : 'text-orange-600') }}">
                        {{ number_format($progressBulan ?? 0, 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ min(100, $progressBulan ?? 0) }}%"></div>
                </div>
                <div class="flex justify-between text-sm pt-2 border-t border-gray-100">
                    <span class="text-gray-600">Sisa Target:</span>
                    <span class="font-bold text-orange-600">
                        Rp {{ number_format(max(0, ($targetBulanan ?? 0) - ($omsetBulanIni ?? 0)), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Yearly Progress --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                        Tahun Ini
                        <span id="yearBadge" class="ml-2 text-xs bg-indigo-100 text-indigo-600 px-2 py-1 rounded-full">{{ $selectedYearTarget }}</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1">Target: Rp <span id="targetTahunanDisplay">{{ number_format($targetTahunan ?? 0, 0, ',', '.') }}</span></p>
                </div>
                <div class="w-16 h-16">
                    <canvas id="chartProgressTahun"></canvas>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Pencapaian:</span>
                    <span class="font-bold text-green-600">Rp {{ number_format($omsetTahunIni ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Progress:</span>
                    <span class="font-bold {{ ($progressTahun ?? 0) >= 100 ? 'text-green-600' : (($progressTahun ?? 0) >= 75 ? 'text-blue-600' : 'text-orange-600') }}">
                        {{ number_format($progressTahun ?? 0, 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ min(100, $progressTahun ?? 0) }}%"></div>
                </div>
                <div class="flex justify-between text-sm pt-2 border-t border-gray-100">
                    <span class="text-gray-600">Sisa Target:</span>
                    <span class="font-bold text-orange-600">
                        Rp {{ number_format(max(0, ($targetTahunan ?? 0) - ($omsetTahunIni ?? 0)), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Breakdown Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-table text-indigo-600 mr-2"></i>
                Rekap Bulanan  <span id="rekapYear">{{ $selectedYearTarget }}</span>
            </h4>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500">Total Akumulasi:</span>
                <span id="totalAkumulasi" class="text-sm font-bold text-indigo-600">
                    Rp {{ number_format($omsetTahunIni ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Bulan</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Realisasi</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Selisih</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $rekapBulanan = $rekapBulanan ?? [];
                        $currentMonth = (int)date('n');
                    @endphp
                    @foreach($months as $index => $month)
                        @php
                            $bulanNum = $index + 1;
                            $data = $rekapBulanan[$bulanNum] ?? ['realisasi' => 0, 'progress' => 0, 'selisih' => 0, 'mingguan' => []];
                            $realisasi = $data['realisasi'];
                            $progress = $data['progress'];
                            $selisih = $data['selisih'];
                            $mingguanData = $data['mingguan'] ?? [];
                            $statusClass = $progress >= 100 ? 'text-green-600' : ($progress >= 75 ? 'text-blue-600' : ($progress > 0 ? 'text-orange-600' : 'text-gray-400'));
                            $isCurrent = $bulanNum == $currentMonth;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $isCurrent ? 'bg-indigo-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                @if(count($mingguanData) > 0)
                                    <button onclick="toggleWeeklyDetail({{ $bulanNum }})" 
                                            class="mr-2 text-indigo-600 hover:text-indigo-800">
                                        <i id="icon-{{ $bulanNum }}" class="fas fa-chevron-right transition-transform"></i>
                                    </button>
                                @endif
                                {{ $month }}
                                @if($isCurrent)
                                    <span class="ml-2 text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">Current</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                Rp {{ number_format($targetBulanan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                                Rp {{ number_format($realisasi, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <span class="font-bold {{ $statusClass }}">{{ number_format($progress, 1) }}%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <span class="font-semibold {{ $selisih >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $selisih >= 0 ? '+' : '' }}Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($progress >= 100)
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        <i class="fas fa-check-circle mr-1"></i>Target Tercapai
                                    </span>
                                @elseif($progress >= 75)
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                        <i class="fas fa-arrow-up mr-1"></i>On Track
                                    </span>
                                @elseif($progress > 0)
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-700">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Perlu Boost
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                        <i class="fas fa-minus-circle mr-1"></i>Belum Ada Data
                                    </span>
                                @endif
                            </td>
                        </tr>
                        {{-- Weekly Detail Row (Hidden by default) --}}
                        @if(count($mingguanData) > 0)
                            <tr id="weekly-{{ $bulanNum }}" class="hidden bg-gray-50">
                                <td colspan="6" class="px-6 py-4">
                                    <div class="ml-8 space-y-2">
                                        <p class="text-xs font-semibold text-gray-700 mb-3">📊 Detail Mingguan {{ $month }}:</p>
                                        @foreach($mingguanData as $minggu => $weekData)
                                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                                <div class="flex items-center space-x-3">
                                                    <span class="text-xs font-bold text-indigo-600 bg-indigo-100 px-2 py-1 rounded">
                                                        Minggu {{ $minggu }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $weekData['tanggal'] }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center space-x-4">
                                                    <span class="text-sm font-semibold text-gray-700">
                                                        Rp {{ number_format($weekData['omset'], 0, ',', '.') }}
                                                    </span>
                                                    <div class="w-32 bg-gray-200 rounded-full h-2">
                                                        <div class="bg-indigo-500 h-2 rounded-full" 
                                                             style="width: {{ min(100, $weekData['progress']) }}%"></div>
                                                    </div>
                                                    <span class="text-sm font-bold {{ $weekData['progress'] >= 100 ? 'text-green-600' : ($weekData['progress'] >= 75 ? 'text-blue-600' : 'text-orange-600') }}">
                                                        {{ number_format($weekData['progress'], 1) }}%
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot class="bg-gradient-to-r from-indigo-50 to-purple-50">
                    <tr>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">TOTAL</td>
                        <td class="px-6 py-4 text-sm font-bold text-right text-gray-900">
                            Rp {{ number_format($targetTahunan ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-right text-green-600">
                            Rp {{ number_format($omsetTahunIni ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-right text-indigo-600">
                            {{ number_format($progressTahun ?? 0, 1) }}%
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-right {{ (($omsetTahunIni ?? 0) - ($targetTahunan ?? 0)) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ (($omsetTahunIni ?? 0) - ($targetTahunan ?? 0)) >= 0 ? '+' : '' }}Rp {{ number_format(($omsetTahunIni ?? 0) - ($targetTahunan ?? 0), 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Modal Set Target --}}
<div id="targetModal" class="fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="bg-green-500 p-6 rounded-t-xl">
            <h3 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-bullseye mr-3"></i>
                Set Target Omset
            </h3>
        </div>
        <form id="targetForm" class="p-6">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Tahun
                </label>
                <input type="number" 
                       id="targetTahunInput" 
                       name="tahun"
                       value="{{ $selectedYearTarget }}"
                       min="2020"
                       max="2100"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Contoh: 2025">
                <p class="mt-2 text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Masukkan tahun untuk set atau edit target
                </p>
                @if(count($availableYearsTarget) > 0)
                    <div class="mt-2 p-2 bg-blue-50 rounded-lg">
                        <p class="text-xs text-blue-700">
                            <i class="fas fa-database mr-1"></i>
                            <strong>Tahun yang sudah ada data:</strong>
                            <span class="font-semibold">{{ implode(', ', $availableYearsTarget) }}</span>
                        </p>
                    </div>
                @endif
            </div>
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Target Tahunan
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-400">Rp</span>
                    <input type="text" 
                           id="targetTahunanInput" 
                           name="target_tahunan"
                           value="{{ number_format($targetTahunan ?? 0, 0, ',', '.') }}"
                           class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="0"
                           oninput="updateTargetPreview()">
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Target akan dibagi otomatis: 48 minggu (4 minggu per bulan) & 12 bulan
                </p>
            </div>
            <div class="mb-6 p-4 bg-indigo-50 rounded-lg">
                <p class="text-sm text-gray-700 mb-2">
                    <span class="font-semibold">Target Mingguan:</span> 
                    <span id="previewMingguan" class="text-indigo-600 font-bold">Rp 0</span>
                </p>
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">Target Bulanan:</span> 
                    <span id="previewBulanan" class="text-indigo-600 font-bold">Rp 0</span>
                </p>
            </div>
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeTargetModal()"
                        class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-600 transition-all shadow-lg">
                    <i class="fas fa-save mr-2"></i>Simpan Target
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Available years for target (from server)
const availableYearsTarget = @json($availableYearsTarget);
let currentYearTarget = {{ $selectedYearTarget }};

// Change target year with slide navigation
function changeTargetYearSlide(direction) {
    const currentIndex = availableYearsTarget.indexOf(currentYearTarget);
    let newIndex;
    
    // availableYearsTarget diurutkan DESC (2026, 2025, 2024, ...)
    // next = tahun lebih besar = index lebih kecil (ke kiri array)
    // prev = tahun lebih kecil = index lebih besar (ke kanan array)
    if (direction === 'next') {
        newIndex = currentIndex - 1; // Ke tahun yang lebih besar
    } else {
        newIndex = currentIndex + 1; // Ke tahun yang lebih kecil
    }
    
    // Boundary check
    if (newIndex < 0 || newIndex >= availableYearsTarget.length) {
        return; // Do nothing if out of bounds
    }
    
    const newYear = availableYearsTarget[newIndex];
    loadTargetAnalysisData(newYear);
}

// Load target analysis data via page reload with tahun_target parameter
function loadTargetAnalysisData(year) {
    // Update URL and reload page with new year parameter
    const url = new URL(window.location.href);
    url.searchParams.set('tahun_target', year);
    window.location.href = url.toString();
}

// Toggle weekly detail
function toggleWeeklyDetail(bulanNum) {
    const detailRow = document.getElementById('weekly-' + bulanNum);
    const icon = document.getElementById('icon-' + bulanNum);
    
    if (detailRow.classList.contains('hidden')) {
        detailRow.classList.remove('hidden');
        icon.classList.add('rotate-90');
    } else {
        detailRow.classList.add('hidden');
        icon.classList.remove('rotate-90');
    }
}

// Modal functions
function openTargetModal() {
    // Check if user is direktur
    @if(auth()->user()->role !== 'direktur')
        alert('⚠️ Akses Ditolak\n\nHanya Direktur yang dapat mengubah target omset.\nAnda hanya memiliki akses untuk melihat data.');
        return;
    @endif
    
    document.getElementById('targetModal').classList.remove('hidden');
    
    // Set tahun to current selected year
    document.getElementById('targetTahunInput').value = currentYearTarget;
    
    // Trigger change event to load target for that year
    const event = new Event('change');
    document.getElementById('targetTahunInput').dispatchEvent(event);
}

function closeTargetModal() {
    document.getElementById('targetModal').classList.add('hidden');
}

function updateTargetPreview() {
    const input = document.getElementById('targetTahunanInput').value;
    const cleanValue = input.replace(/\D/g, '');
    const targetTahunan = parseInt(cleanValue) || 0;
    
    // Target Bulanan = Target Tahunan / 12 bulan
    const targetBulanan = Math.round(targetTahunan / 12);
    
    // Target Mingguan = Target Tahunan / 48 minggu (12 bulan x 4 minggu per bulan)
    const targetMingguan = Math.round(targetTahunan / 48);
    
    document.getElementById('previewMingguan').textContent = 'Rp ' + targetMingguan.toLocaleString('id-ID');
    document.getElementById('previewBulanan').textContent = 'Rp ' + targetBulanan.toLocaleString('id-ID');
}

// Format input as currency
document.getElementById('targetTahunanInput')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
    }
    e.target.value = value;
});

// Handle tahun change in modal - load existing target if available
document.getElementById('targetTahunInput')?.addEventListener('change', function(e) {
    const tahun = e.target.value;
    
    // Validate year
    if (tahun < 2020 || tahun > 2100) {
        alert('Tahun harus antara 2020 - 2100');
        return;
    }
    
    // Fetch target for selected year
    fetch('{{ route("laporan.omset") }}?ajax=get_target&tahun=' + tahun, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.target_tahunan > 0) {
            // Format and set existing target
            const formatted = parseInt(data.target_tahunan).toLocaleString('id-ID');
            document.getElementById('targetTahunanInput').value = formatted;
        } else {
            // Clear input for new year
            document.getElementById('targetTahunanInput').value = '';
        }
        updateTargetPreview();
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// Handle form submit
document.getElementById('targetForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const targetInput = document.getElementById('targetTahunanInput').value;
    const cleanValue = targetInput.replace(/\D/g, '');
    const tahunValue = document.getElementById('targetTahunInput').value;
    
    // Validate tahun
    if (!tahunValue || tahunValue < 2020 || tahunValue > 2100) {
        alert('Mohon masukkan tahun yang valid (2020 - 2100)');
        return;
    }
    
    // Validate target
    if (!cleanValue || cleanValue == '0') {
        alert('Mohon masukkan target tahunan yang valid');
        return;
    }
    
    // Send AJAX request
    fetch('{{ route("laporan.omset.setTarget") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            target_tahunan: cleanValue,
            tahun: tahunValue
        })
    })
    .then(response => {
        if (response.status === 403) {
            return response.json().then(data => {
                throw new Error(data.message || 'Akses ditolak');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('✅ Target berhasil disimpan untuk tahun ' + tahunValue + '!');
            closeTargetModal();
            // Reload data for that year
            loadTargetAnalysisData(parseInt(tahunValue));
        } else {
            alert('❌ Gagal menyimpan target\n\n' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ ' + error.message);
    });
});

// Initialize progress doughnut charts
document.addEventListener('DOMContentLoaded', function() {
    // Weekly Progress Chart
    const ctxMinggu = document.getElementById('chartProgressMinggu')?.getContext('2d');
    if (ctxMinggu) {
        window.chartProgressMinggu = new Chart(ctxMinggu, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $progressMinggu ?? 0 }}, {{ 100 - ($progressMinggu ?? 0) }}],
                    backgroundColor: ['#3B82F6', '#E5E7EB'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }
    
    // Monthly Progress Chart
    const ctxBulan = document.getElementById('chartProgressBulan')?.getContext('2d');
    if (ctxBulan) {
        window.chartProgressBulan = new Chart(ctxBulan, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $progressBulan ?? 0 }}, {{ 100 - ($progressBulan ?? 0) }}],
                    backgroundColor: ['#8B5CF6', '#E5E7EB'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }
    
    // Yearly Progress Chart
    const ctxTahun = document.getElementById('chartProgressTahun')?.getContext('2d');
    if (ctxTahun) {
        window.chartProgressTahun = new Chart(ctxTahun, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $progressTahun ?? 0 }}, {{ 100 - ($progressTahun ?? 0) }}],
                    backgroundColor: ['#6366F1', '#E5E7EB'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }
});
</script>
