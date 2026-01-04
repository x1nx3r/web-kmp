{{-- Omset per Klien (Bar Chart) Section - Dashboard --}}
<div class="mb-6">
    {{-- Card: Omset per Klien --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                    Omset Klien
                    <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full" 
                          title="Omset Sistem (transaksi terverifikasi) per bulan">
                        <i class="fas fa-info-circle mr-1"></i>Per Bulan
                    </span>
                </h3>
                <p class="text-sm text-gray-500 mt-1">Distribusi omset bulanan per klien</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Search Filter --}}
                <div class="relative">
                    <input type="text" 
                           id="searchKlienDashboard" 
                           placeholder="Cari klien..." 
                           class="w-64 pl-10 pr-10 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           onkeyup="handleKlienSearchKeyupDashboard(event)">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <button onclick="clearKlienSearchDashboard()" 
                            id="clearSearchKlienDashboard"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                {{-- Year Navigation --}}
                <div class="flex items-center gap-2">
                    <button onclick="changeYearKlienChartDashboard(-1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="px-4 py-2 bg-blue-50 rounded-lg">
                        <span class="text-sm font-semibold text-blue-700">Tahun: </span>
                        <span id="currentYearKlienDashboard" class="text-lg font-bold text-blue-600">{{ date('Y') }}</span>
                    </div>
                    <button onclick="changeYearKlienChartDashboard(1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-right text-gray-600"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div style="height: 500px;">
            <canvas id="chartOmsetPerKlienDashboard"></canvas>
        </div>
    </div>
</div>

<script>
// Chart instance for klien - Dashboard
let chartOmsetPerKlienDashboard = null;

// Current year for omset per klien - Dashboard
let currentYearKlienDashboard = {{ date('Y') }};
const availableYearsKlienDashboard = @json(range(2020, date('Y')));

// Search filter state - Dashboard
let klienSearchTimeoutDashboard = null;
let currentKlienSearchDashboard = '';

// Handle keyup event for klien search (debounced) - Dashboard
function handleKlienSearchKeyupDashboard(event) {
    const searchValue = event.target.value.trim();
    
    // Show/hide clear button
    const clearBtn = document.getElementById('clearSearchKlienDashboard');
    if (searchValue) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    
    // Debounce the search
    clearTimeout(klienSearchTimeoutDashboard);
    klienSearchTimeoutDashboard = setTimeout(() => {
        currentKlienSearchDashboard = searchValue;
        loadOmsetPerKlienChartDashboard(currentYearKlienDashboard, searchValue);
    }, 500); // Wait 500ms after user stops typing
}

// Clear klien search - Dashboard
function clearKlienSearchDashboard() {
    document.getElementById('searchKlienDashboard').value = '';
    document.getElementById('clearSearchKlienDashboard').classList.add('hidden');
    currentKlienSearchDashboard = '';
    loadOmsetPerKlienChartDashboard(currentYearKlienDashboard, '');
}

// Change year for omset per klien chart - Dashboard
function changeYearKlienChartDashboard(direction) {
    const currentIndex = availableYearsKlienDashboard.indexOf(currentYearKlienDashboard);
    let newIndex = currentIndex + direction;
    
    // Boundary check
    if (newIndex < 0 || newIndex >= availableYearsKlienDashboard.length) {
        return;
    }
    
    currentYearKlienDashboard = availableYearsKlienDashboard[newIndex];
    document.getElementById('currentYearKlienDashboard').textContent = currentYearKlienDashboard;
    
    loadOmsetPerKlienChartDashboard(currentYearKlienDashboard, currentKlienSearchDashboard);
}

// Load Omset per Klien Chart via AJAX - Dashboard
function loadOmsetPerKlienChartDashboard(tahun, search = '') {
    let url = `{{ route('dashboard.omsetPerKlien') }}?tahun=${tahun}`;
    if (search) {
        url += `&search=${encodeURIComponent(search)}`;
    }
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        updateOmsetPerKlienChartDashboard(result);
    })
    .catch(error => console.error('Error:', error));
}

// Update Omset per Klien Chart - Dashboard
function updateOmsetPerKlienChartDashboard(data) {
    if (chartOmsetPerKlienDashboard) {
        chartOmsetPerKlienDashboard.data.labels = data.klien_names;
        chartOmsetPerKlienDashboard.data.datasets = data.datasets;
        chartOmsetPerKlienDashboard.update();
    } else {
        const ctx = document.getElementById('chartOmsetPerKlienDashboard').getContext('2d');
        chartOmsetPerKlienDashboard = createGroupedBarChartDashboard(ctx, data.klien_names, data.datasets);
    }
}

// Create Grouped Bar Chart for Omset per Klien - Dashboard
function createGroupedBarChartDashboard(ctx, labels, datasets) {
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            return label;
                        }
                    }
                },
                datalabels: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        },
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(0) + 'Jt';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// Initialize Omset per Klien Chart - Dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadOmsetPerKlienChartDashboard(currentYearKlienDashboard);
});
</script>
