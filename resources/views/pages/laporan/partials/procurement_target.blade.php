{{-- Procurement Target Card --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-users-cog text-indigo-600"></i>
                Target Omset Procurement
            </h3>
            <p class="text-sm text-gray-500 mt-1">Monitoring target dan realisasi omset per procurement</p>
        </div>
        
        @if(auth()->user()->isDirektur())
        <button onclick="toggleSettingMode()" id="btnToggleSetting" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
            <i class="fas fa-cog"></i>
            <span>Atur Target</span>
        </button>
        @endif
    </div>

    {{-- Setting Mode (Only Direktur) --}}
    @if(auth()->user()->isDirektur())
    <div id="procurementSettingMode" class="hidden mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
        <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i class="fas fa-sliders-h text-indigo-600"></i>
            Pengaturan Persentase Target (Tahun <span id="settingYear">{{ $selectedYearTarget }}</span>)
        </h4>
        
        <div class="mb-3 text-sm text-gray-600 bg-white p-3 rounded border border-indigo-100">
            <div class="flex justify-between items-center">
                <span>Total Alokasi:</span>
                <div>
                    <span id="totalPersentaseProcurement" class="font-bold text-lg text-indigo-700">0%</span>
                    <span id="sisaPersentaseProcurement" class="text-gray-500 ml-2 text-xs"></span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 bg-white rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Procurement</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Persentase (%)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Target Tahunan</th>
                    </tr>
                </thead>
                <tbody id="procurementSettingTableBody" class="bg-white divide-y divide-gray-200">
                    {{-- Will be populated by JS --}}
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-end gap-2">
            <button onclick="cancelSettingMode()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                Batal
            </button>
            <button onclick="saveProcurementTargets()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i>
                Simpan Target
            </button>
        </div>
    </div>
    @endif

    {{-- Monitoring Section --}}
    <div id="procurementMonitoringMode">
        {{-- Period Filter --}}
        <div class="flex items-center gap-3 mb-4 flex-wrap">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Periode:</label>
                <select id="procurementPeriodType" onchange="loadProcurementProgress()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="yearly">Tahunan</option>
                    <option value="monthly" selected>Bulanan</option>
                    <option value="weekly">Mingguan</option>
                </select>
            </div>

            <div id="procurementMonthWrapper" class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Bulan:</label>
                <select id="procurementMonth" onchange="loadProcurementProgress()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div id="procurementWeekWrapper" class="flex items-center gap-2 hidden">
                <label class="text-sm font-medium text-gray-700">Minggu:</label>
                <select id="procurementWeek" onchange="loadProcurementProgress()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Pilih Minggu</option>
                    <option value="1">Minggu 1 (1-7)</option>
                    <option value="2">Minggu 2 (8-14)</option>
                    <option value="3">Minggu 3 (15-21)</option>
                    <option value="4">Minggu 4 (22-Akhir)</option>
                </select>
            </div>
        </div>

        {{-- Progress Content --}}
        <div id="procurementProgressContent" class="min-h-[300px]">
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
                <p>Memuat data...</p>
            </div>
        </div>
    </div>
</div>

{{-- Load Chart.js and DataLabels Plugin --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
// Procurement Target Variables
let procurementUsers = [];
let procurementTargets = {};
let procurementChartInstance = null;

// Load procurement users (for setting)
function loadProcurementUsers() {
    // Get from server
    fetch('{{ route("laporan.omset.getProcurementTargetData") }}?tahun={{ $selectedYearTarget }}&get_users=1')
        .then(response => response.json())
        .then(result => {
            if (result.users) {
                procurementUsers = result.users;
                procurementTargets = result.targets || {};
                renderSettingTable();
            }
        })
        .catch(error => console.error('Error loading users:', error));
}

// Toggle Setting Mode
function toggleSettingMode() {
    const settingMode = document.getElementById('procurementSettingMode');
    const monitoringMode = document.getElementById('procurementMonitoringMode');
    const btnToggle = document.getElementById('btnToggleSetting');
    
    if (settingMode.classList.contains('hidden')) {
        settingMode.classList.remove('hidden');
        monitoringMode.classList.add('hidden');
        btnToggle.innerHTML = '<i class="fas fa-chart-line"></i><span>Lihat Progress</span>';
        loadProcurementUsers();
    } else {
        settingMode.classList.add('hidden');
        monitoringMode.classList.remove('hidden');
        btnToggle.innerHTML = '<i class="fas fa-cog"></i><span>Atur Target</span>';
    }
}

// Cancel Setting Mode
function cancelSettingMode() {
    const settingMode = document.getElementById('procurementSettingMode');
    const monitoringMode = document.getElementById('procurementMonitoringMode');
    const btnToggle = document.getElementById('btnToggleSetting');
    
    settingMode.classList.add('hidden');
    monitoringMode.classList.remove('hidden');
    btnToggle.innerHTML = '<i class="fas fa-cog"></i><span>Atur Target</span>';
}

// Render Setting Table
function renderSettingTable() {
    const tbody = document.getElementById('procurementSettingTableBody');
    let html = '';
    
    procurementUsers.forEach(user => {
        const existingTarget = procurementTargets[user.id] || 0;
        const targetAmount = ({{ $targetTahunan }} * existingTarget / 100);
        
        html += `
            <tr>
                <td class="px-4 py-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900">${user.nama}</span>
                        <span class="px-2 py-0.5 text-xs rounded-full ${user.role === 'manager_purchasing' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'}">
                            ${user.role === 'manager_purchasing' ? 'Manager' : 'Staff'}
                        </span>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <input 
                        type="number" 
                        data-user-id="${user.id}"
                        value="${existingTarget}"
                        min="0" 
                        max="100" 
                        step="0.01"
                        class="w-20 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 text-center procurement-target-input"
                        onchange="updateProcurementTotal()"
                    />
                </td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-700" id="procTargetAmount_${user.id}">
                    Rp ${targetAmount.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0})}
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    updateProcurementTotal();
}

// Update Total Persentase
function updateProcurementTotal() {
    let total = 0;
    document.querySelectorAll('.procurement-target-input').forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
        
        // Update target amount
        const userId = input.dataset.userId;
        const targetAmount = ({{ $targetTahunan }} * value / 100);
        const elem = document.getElementById('procTargetAmount_' + userId);
        if (elem) {
            elem.textContent = 'Rp ' + targetAmount.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        }
    });
    
    document.getElementById('totalPersentaseProcurement').textContent = total.toFixed(2) + '%';
    
    const sisa = 100 - total;
    const sisaElement = document.getElementById('sisaPersentaseProcurement');
    
    if (sisa < 0) {
        sisaElement.textContent = 'Kelebihan: ' + Math.abs(sisa).toFixed(2) + '%';
        sisaElement.className = 'text-red-600 ml-2 text-xs font-semibold';
    } else if (sisa > 0) {
        sisaElement.textContent = 'Sisa: ' + sisa.toFixed(2) + '%';
        sisaElement.className = 'text-orange-600 ml-2 text-xs font-semibold';
    } else {
        sisaElement.textContent = '✓ Pas 100%';
        sisaElement.className = 'text-green-600 ml-2 text-xs font-semibold';
    }
}

// Save Procurement Targets
function saveProcurementTargets() {
    const targets = [];
    let total = 0;
    
    document.querySelectorAll('.procurement-target-input').forEach(input => {
        const userId = input.dataset.userId;
        const persentase = parseFloat(input.value) || 0;
        total += persentase;
        
        // Simpan semua target termasuk yang 0% (untuk reset/nonaktifkan procurement)
        targets.push({
            user_id: userId,
            persentase: persentase
        });
    });
    
    if (total > 100) {
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
            tahun: {{ $selectedYearTarget }},
            targets: targets
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Target berhasil disimpan!');
            cancelSettingMode();
            loadProcurementProgress();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan target');
    });
}

// Handle Period Type Change
document.getElementById('procurementPeriodType').addEventListener('change', function() {
    const period = this.value;
    const monthWrapper = document.getElementById('procurementMonthWrapper');
    const weekWrapper = document.getElementById('procurementWeekWrapper');
    
    if (period === 'yearly') {
        monthWrapper.classList.add('hidden');
        weekWrapper.classList.add('hidden');
    } else if (period === 'monthly') {
        monthWrapper.classList.remove('hidden');
        weekWrapper.classList.add('hidden');
    } else if (period === 'weekly') {
        monthWrapper.classList.remove('hidden');
        weekWrapper.classList.remove('hidden');
    }
});

// Load Procurement Progress
function loadProcurementProgress() {
    const period = document.getElementById('procurementPeriodType').value;
    const month = document.getElementById('procurementMonth').value;
    const week = document.getElementById('procurementWeek').value;
    
    // Validate
    if (period === 'weekly' && !week) {
        document.getElementById('procurementProgressContent').innerHTML = `
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-calendar-week text-4xl mb-2"></i>
                <p>Silakan pilih minggu terlebih dahulu</p>
            </div>
        `;
        return;
    }
    
    // Show loading
    document.getElementById('procurementProgressContent').innerHTML = `
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
            <p>Memuat data...</p>
        </div>
    `;
    
    let url = '{{ route("laporan.omset.getProcurementTargetData") }}?tahun={{ $selectedYearTarget }}';
    if (month && period !== 'yearly') url += '&bulan=' + month;
    if (week && period === 'weekly') url += '&minggu=' + week;
    
    fetch(url)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                renderProcurementProgress(result.data, period);
            } else {
                document.getElementById('procurementProgressContent').innerHTML = `
                    <div class="text-center py-12 text-orange-400">
                        <i class="fas fa-info-circle text-4xl mb-2"></i>
                        <p class="font-semibold">${result.message || 'Belum ada data'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('procurementProgressContent').innerHTML = `
                <div class="text-center py-12 text-red-400">
                    <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                    <p class="font-semibold">Gagal memuat data</p>
                </div>
            `;
        });
}

// Render Procurement Progress (Donut Chart + Cards)
function renderProcurementProgress(data, period) {
    // Filter out procurement dengan target 0%
    const activeData = data ? data.filter(item => parseFloat(item.persentase_target) > 0) : [];
    
    if (!activeData || activeData.length === 0) {
        document.getElementById('procurementProgressContent').innerHTML = `
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-user-slash text-4xl mb-2"></i>
                <p class="font-semibold mb-2">Belum Ada Target Procurement Aktif</p>
                <p class="text-sm">Tidak ada procurement dengan target > 0% untuk periode ini</p>
            </div>
        `;
        return;
    }
    
    const periodLabel = period === 'yearly' ? 'Tahunan' : (period === 'monthly' ? 'Bulanan' : 'Mingguan');
    
    let html = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Chart Section --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3 text-center">Progress ${periodLabel}</h4>
                <div style="max-width: 400px; margin: 0 auto;">
                    <canvas id="procurementProgressChart"></canvas>
                </div>
            </div>
            
            {{-- Cards Section --}}
            <div class="space-y-3">
                <h4 class="font-semibold text-gray-900 mb-3">Detail per Procurement</h4>
    `;
    
    data.forEach((item, index) => {
        // Skip procurement dengan target 0%
        if (parseFloat(item.persentase_target) <= 0) {
            return;
        }
        
        const progressPercent = item.progress;
        const statusColor = item.status === 'tercapai' ? 'green' : 'red';
        const progressBarColor = progressPercent >= 100 ? 'bg-green-500' : (progressPercent >= 70 ? 'bg-yellow-500' : 'bg-red-500');
        
        html += `
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <h5 class="font-semibold text-gray-900">${item.nama}</h5>
                        <p class="text-xs text-gray-500">${item.role === 'manager_purchasing' ? 'Manager' : 'Staff'} Procurement</p>
                    </div>
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">
                        ${item.persentase_target}%
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs">Target ${periodLabel}</p>
                        <p class="font-semibold text-gray-900">Rp ${parseFloat(item.target_amount).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Realisasi</p>
                        <p class="font-semibold text-${statusColor}-600">Rp ${parseFloat(item.actual_omset).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    </div>
                </div>
                
                <div class="space-y-1">
                    <div class="flex justify-between text-xs text-gray-600">
                        <span>Progress</span>
                        <span class="font-semibold">${progressPercent}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="${progressBarColor} h-2 rounded-full transition-all" style="width: ${Math.min(progressPercent, 100)}%"></div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `
            </div>
        </div>
    `;
    
    document.getElementById('procurementProgressContent').innerHTML = html;
    
    // Render Donut Chart
    renderProcurementChart(data);
}

// Render Donut Chart
function renderProcurementChart(data) {
    if (procurementChartInstance) {
        procurementChartInstance.destroy();
    }
    
    // Filter out procurement dengan target 0%
    const activeData = data.filter(item => parseFloat(item.persentase_target) > 0);
    
    // If no active procurement, don't render chart
    if (activeData.length === 0) {
        return;
    }
    
    const labels = activeData.map(item => item.nama);
    const actualData = activeData.map(item => parseFloat(item.actual_omset));
    const totalOmset = actualData.reduce((a, b) => a + b, 0);
    const percentages = actualData.map(value => ((value / totalOmset) * 100).toFixed(1));
    
    const colors = [
        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', 
        '#EC4899', '#06B6D4', '#F97316'
    ];
    
    const ctx = document.getElementById('procurementProgressChart').getContext('2d');
    procurementChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: percentages,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const index = context.dataIndex;
                            const value = actualData[index];
                            const percentage = percentages[index];
                            
                            // Format nilai lengkap dengan desimal
                            const formattedValue = value.toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            
                            return label + ': Rp ' + formattedValue + ' (' + percentage + '%)';
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 14
                    },
                    formatter: function(value, context) {
                        return value + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProcurementProgress();
});
</script>
