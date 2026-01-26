@extends('pages.laporan.base')

@section('report-content')

{{-- Header Section --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Target Omset Procurement</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola dan pantau target omset per procurement</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Year Selector --}}
            <div class="flex items-center gap-2">
                <button onclick="changeYear(-1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                <span class="text-lg font-semibold text-gray-700 min-w-[80px] text-center" id="currentYear">{{ $selectedYear }}</span>
                <button onclick="changeYear(1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </button>
            </div>
            
            <a href="{{ route('laporan.omset') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>
</div>

{{-- Target Omset Info --}}
<div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-sm border border-blue-300 p-6 mb-6 text-white">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center">
            <p class="text-sm opacity-90">Target Tahunan</p>
            <p class="text-2xl font-bold mt-1">
                @if($targetOmset)
                    Rp {{ number_format($targetOmset->target_tahunan, 2, ',', '.') }}
                @else
                    <span class="text-base">Belum Ditetapkan</span>
                @endif
            </p>
        </div>
        <div class="text-center">
            <p class="text-sm opacity-90">Target Bulanan</p>
            <p class="text-2xl font-bold mt-1">
                @if($targetOmset)
                    Rp {{ number_format($targetOmset->target_bulanan, 2, ',', '.') }}
                @else
                    <span class="text-base">Belum Ditetapkan</span>
                @endif
            </p>
        </div>
        <div class="text-center">
            <p class="text-sm opacity-90">Target Mingguan</p>
            <p class="text-2xl font-bold mt-1">
                @if($targetOmset)
                    Rp {{ number_format($targetOmset->target_mingguan, 2, ',', '.') }}
                @else
                    <span class="text-base">Belum Ditetapkan</span>
                @endif
            </p>
        </div>
    </div>
    @if(!$targetOmset)
        <div class="mt-4 text-center">
            <p class="text-sm">Silakan tetapkan target omset tahun {{ $selectedYear }} terlebih dahulu di <a href="{{ route('laporan.omset') }}" class="underline font-semibold">halaman omset</a></p>
        </div>
    @endif
</div>

@if($targetOmset)
{{-- Target Setting Card --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-gray-900">Pengaturan Persentase Target</h3>
        <div class="text-sm">
            <span class="text-gray-600">Total Alokasi:</span>
            <span id="totalPersentase" class="font-bold text-lg ml-2">0%</span>
            <span id="sisaPersentase" class="text-gray-500 ml-2"></span>
        </div>
    </div>

    <form id="formSetTarget" onsubmit="saveTargets(event)">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Procurement</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase Target (%)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Target Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($procurementUsers as $index => $user)
                        @php
                            $existingTarget = $targetProcurements->firstWhere('user_id', $user->id);
                            $persentase = $existingTarget ? $existingTarget->persentase_target : 0;
                            $targetAmount = $persentase > 0 ? ($targetOmset->target_tahunan * $persentase / 100) : 0;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $user->nama }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($user->role === 'manager_purchasing')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">Manager</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Staff</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input 
                                    type="number" 
                                    name="targets[{{ $user->id }}]" 
                                    data-user-id="{{ $user->id }}"
                                    value="{{ $persentase }}"
                                    min="0" 
                                    max="100" 
                                    step="0.01"
                                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center target-input"
                                    onchange="updateTotalPersentase()"
                                >
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900" id="targetAmount_{{ $user->id }}">
                                Rp {{ number_format($targetAmount, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-users text-4xl mb-2"></i>
                                <p>Tidak ada user procurement aktif</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($procurementUsers->count() > 0)
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="resetForm()" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                Reset
            </button>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i>
                Simpan Target
            </button>
        </div>
        @endif
    </form>
</div>

{{-- Progress Tracking Section --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-gray-900">Progress Realisasi Target</h3>
        <div class="flex items-center gap-3">
            {{-- Period Selector --}}
            <select id="periodSelector" onchange="loadProgressData()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="yearly">Tahunan</option>
                <option value="monthly">Bulanan</option>
                <option value="weekly">Mingguan</option>
            </select>
            
            {{-- Month Selector (hidden by default) --}}
            <select id="monthSelector" onchange="loadProgressData()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 hidden">
                <option value="">Pilih Bulan</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            
            {{-- Week Selector (hidden by default) --}}
            <select id="weekSelector" onchange="loadProgressData()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 hidden">
                <option value="">Pilih Minggu</option>
                <option value="1">Minggu 1 (Tgl 1-7)</option>
                <option value="2">Minggu 2 (Tgl 8-14)</option>
                <option value="3">Minggu 3 (Tgl 15-21)</option>
                <option value="4">Minggu 4 (Tgl 22-Akhir)</option>
            </select>
        </div>
    </div>

    <div id="progressContent">
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-chart-bar text-4xl mb-2"></i>
            <p>Pilih periode untuk melihat progress</p>
        </div>
    </div>

    {{-- Chart Container --}}
    <div class="mt-6">
        <canvas id="chartProcurementProgress" height="100"></canvas>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentYear = {{ $selectedYear }};
const availableYears = @json($availableYears);
const targetTahunan = {{ $targetOmset ? $targetOmset->target_tahunan : 0 }};
const targetBulanan = {{ $targetOmset ? $targetOmset->target_bulanan : 0 }};
const targetMingguan = {{ $targetOmset ? $targetOmset->target_mingguan : 0 }};
let chartProgress = null;

// Update total persentase
function updateTotalPersentase() {
    let total = 0;
    document.querySelectorAll('.target-input').forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
        
        // Update target amount
        const userId = input.dataset.userId;
        const targetAmount = (targetTahunan * value) / 100;
        document.getElementById('targetAmount_' + userId).textContent = 
            'Rp ' + targetAmount.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    });
    
    document.getElementById('totalPersentase').textContent = total.toFixed(2) + '%';
    
    const sisa = 100 - total;
    const sisaElement = document.getElementById('sisaPersentase');
    
    if (sisa < 0) {
        sisaElement.textContent = '(Kelebihan: ' + Math.abs(sisa).toFixed(2) + '%)';
        sisaElement.className = 'text-red-600 ml-2 font-semibold';
    } else if (sisa > 0) {
        sisaElement.textContent = '(Sisa: ' + sisa.toFixed(2) + '%)';
        sisaElement.className = 'text-orange-600 ml-2 font-semibold';
    } else {
        sisaElement.textContent = '(Pas 100%)';
        sisaElement.className = 'text-green-600 ml-2 font-semibold';
    }
}

// Change year
function changeYear(direction) {
    const currentIndex = availableYears.indexOf(currentYear);
    let newIndex = currentIndex + direction;
    
    if (newIndex < 0 || newIndex >= availableYears.length) {
        return;
    }
    
    currentYear = availableYears[newIndex];
    document.getElementById('currentYear').textContent = currentYear;
    
    // Reload page with new year
    window.location.href = '{{ route("laporan.omset.procurementTarget") }}?tahun=' + currentYear;
}

// Save targets
function saveTargets(event) {
    event.preventDefault();
    
    const targets = [];
    document.querySelectorAll('.target-input').forEach(input => {
        const userId = input.dataset.userId;
        const persentase = parseFloat(input.value) || 0;
        
        if (persentase > 0) {
            targets.push({
                user_id: userId,
                persentase: persentase
            });
        }
    });
    
    if (targets.length === 0) {
        alert('Mohon isi minimal satu target');
        return;
    }
    
    // Check total persentase
    const totalPersentase = targets.reduce((sum, t) => sum + t.persentase, 0);
    if (totalPersentase > 100) {
        alert('Total persentase tidak boleh lebih dari 100%');
        return;
    }
    
    // Send to server
    fetch('{{ route("laporan.omset.setProcurementTarget") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            tahun: currentYear,
            targets: targets
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Target berhasil disimpan!');
            loadProgressData(); // Refresh progress data
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan target');
    });
}

// Reset form
function resetForm() {
    if (confirm('Reset semua persentase target ke nilai awal?')) {
        location.reload();
    }
}

// Handle period selector change
document.getElementById('periodSelector').addEventListener('change', function() {
    const period = this.value;
    const monthSelector = document.getElementById('monthSelector');
    const weekSelector = document.getElementById('weekSelector');
    
    if (period === 'yearly') {
        monthSelector.classList.add('hidden');
        weekSelector.classList.add('hidden');
    } else if (period === 'monthly') {
        monthSelector.classList.remove('hidden');
        weekSelector.classList.add('hidden');
    } else if (period === 'weekly') {
        monthSelector.classList.remove('hidden');
        weekSelector.classList.remove('hidden');
    }
});

// Load progress data
function loadProgressData() {
    const period = document.getElementById('periodSelector').value;
    const month = document.getElementById('monthSelector').value;
    const week = document.getElementById('weekSelector').value;
    
    // Validate inputs
    if (period === 'monthly' && !month) {
        document.getElementById('progressContent').innerHTML = `
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-calendar text-4xl mb-2"></i>
                <p>Silakan pilih bulan terlebih dahulu</p>
            </div>
        `;
        return;
    }
    if (period === 'weekly' && (!month || !week)) {
        document.getElementById('progressContent').innerHTML = `
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-calendar-week text-4xl mb-2"></i>
                <p>Silakan pilih bulan dan minggu terlebih dahulu</p>
            </div>
        `;
        return;
    }
    
    // Show loading
    document.getElementById('progressContent').innerHTML = `
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
            <p>Memuat data...</p>
        </div>
    `;
    
    let url = '{{ route("laporan.omset.getProcurementTargetData") }}?tahun=' + currentYear;
    if (month) url += '&bulan=' + month;
    if (week) url += '&minggu=' + week;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error: ' + response.status);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                displayProgressData(result.data, period);
                updateChart(result.data);
            } else {
                document.getElementById('progressContent').innerHTML = `
                    <div class="text-center py-12 text-red-400">
                        <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
                        <p class="font-semibold">${result.message || 'Terjadi kesalahan'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('progressContent').innerHTML = `
                <div class="text-center py-12 text-red-400">
                    <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                    <p class="font-semibold">Terjadi kesalahan saat mengambil data</p>
                    <p class="text-sm mt-2">${error.message}</p>
                </div>
            `;
        });
}

// Display progress data
function displayProgressData(data, period) {
    const periodLabel = period === 'yearly' ? 'Tahunan' : (period === 'monthly' ? 'Bulanan' : 'Mingguan');
    
    // Check if data is empty
    if (!data || data.length === 0) {
        document.getElementById('progressContent').innerHTML = `
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-info-circle text-4xl mb-2"></i>
                <p class="text-lg font-semibold mb-2">Belum Ada Target Procurement</p>
                <p class="text-sm">Silakan atur persentase target untuk setiap procurement terlebih dahulu</p>
            </div>
        `;
        
        // Clear chart
        if (chartProgress) {
            chartProgress.destroy();
            chartProgress = null;
        }
        return;
    }
    
    let html = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Target %</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Target ${periodLabel}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Realisasi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Progress</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Selisih</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    data.forEach((item, index) => {
        const progressColor = item.progress >= 100 ? 'bg-green-500' : (item.progress >= 70 ? 'bg-yellow-500' : 'bg-red-500');
        const statusBadge = item.status === 'tercapai' 
            ? '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Tercapai</span>'
            : '<span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Belum Tercapai</span>';
        
        html += `
            <tr>
                <td class="px-4 py-3 text-sm">${index + 1}</td>
                <td class="px-4 py-3 text-sm font-medium">${item.nama}</td>
                <td class="px-4 py-3 text-sm text-center font-semibold">${item.persentase_target}%</td>
                <td class="px-4 py-3 text-sm text-right">Rp ${parseFloat(item.target_amount).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
                <td class="px-4 py-3 text-sm text-right font-semibold">Rp ${parseFloat(item.actual_omset).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-gray-200 rounded-full h-4">
                            <div class="${progressColor} h-4 rounded-full transition-all duration-300" style="width: ${Math.min(item.progress, 100)}%"></div>
                        </div>
                        <span class="text-sm font-semibold min-w-[50px]">${item.progress}%</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-right font-semibold ${item.selisih >= 0 ? 'text-green-600' : 'text-red-600'}">
                    ${item.selisih >= 0 ? '+' : ''}Rp ${parseFloat(item.selisih).toLocaleString('id-ID', {minimumFractionDigits: 2})}
                </td>
                <td class="px-4 py-3 text-center">${statusBadge}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('progressContent').innerHTML = html;
}

// Update chart
function updateChart(data) {
    // Check if data is empty
    if (!data || data.length === 0) {
        if (chartProgress) {
            chartProgress.destroy();
            chartProgress = null;
        }
        return;
    }
    
    const labels = data.map(item => item.nama);
    const targetData = data.map(item => item.target_amount);
    const actualData = data.map(item => item.actual_omset);
    
    if (chartProgress) {
        chartProgress.destroy();
    }
    
    const ctx = document.getElementById('chartProcurementProgress').getContext('2d');
    chartProgress = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Target',
                    data: targetData,
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                },
                {
                    label: 'Realisasi',
                    data: actualData,
                    backgroundColor: 'rgba(16, 185, 129, 0.6)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID', {minimumFractionDigits: 2});
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(0) + 'Jt';
                        }
                    }
                }
            }
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateTotalPersentase();
    loadProgressData();
});
</script>

@endsection
