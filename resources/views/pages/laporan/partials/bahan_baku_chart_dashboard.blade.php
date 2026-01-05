{{-- Omset per Bahan Baku (Bar Chart) Section - Dashboard --}}
<div class="mb-6">
    {{-- Card: Omset per Bahan Baku --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4 gap-4">
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center flex-wrap">
                    <i class="fas fa-chart-bar text-purple-500 mr-2"></i>
                    <span class="mr-2">Omset Bahan Baku Klien</span>
                    <span class="px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded-full" 
                          title="Omset Sistem (transaksi terverifikasi) per bulan">
                        <i class="fas fa-info-circle mr-1"></i>Per Bulan
                    </span>
                </h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Distribusi omset bulanan per bahan baku</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                {{-- Search Filter --}}
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           id="searchBahanBakuDashboard" 
                           placeholder="Cari bahan baku..." 
                           class="w-full pl-10 pr-10 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           onkeyup="handleBahanBakuSearchKeyupDashboard(event)">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <button onclick="clearBahanBakuSearchDashboard()" 
                            id="clearSearchBahanBakuDashboard"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                {{-- Year Navigation --}}
                <div class="flex items-center justify-center gap-2">
                    <button onclick="changeYearBahanBakuChartDashboard(-1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="px-3 sm:px-4 py-2 bg-purple-50 rounded-lg">
                        <span class="text-xs sm:text-sm font-semibold text-purple-700">Tahun: </span>
                        <span id="currentYearBahanBakuDashboard" class="text-base sm:text-lg font-bold text-purple-600">{{ date('Y') }}</span>
                    </div>
                    <button onclick="changeYearBahanBakuChartDashboard(1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-right text-gray-600"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="h-64 sm:h-80 md:h-96 lg:h-[500px]">
            <canvas id="chartOmsetPerBahanBakuDashboard"></canvas>
        </div>
    </div>
</div>

<script>
// Chart instance for bahan baku - Dashboard
let chartOmsetPerBahanBakuDashboard = null;

// Current year for omset per bahan baku - Dashboard
let currentYearBahanBakuDashboard = {{ date('Y') }};
const availableYearsBahanBakuDashboard = @json(range(2020, date('Y')));

// Search filter state - Dashboard
let bahanBakuSearchTimeoutDashboard = null;
let currentBahanBakuSearchDashboard = '';

// Handle keyup event for bahan baku search (debounced) - Dashboard
function handleBahanBakuSearchKeyupDashboard(event) {
    const searchValue = event.target.value.trim();
    
    // Show/hide clear button
    const clearBtn = document.getElementById('clearSearchBahanBakuDashboard');
    if (searchValue) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    
    // Debounce the search
    clearTimeout(bahanBakuSearchTimeoutDashboard);
    bahanBakuSearchTimeoutDashboard = setTimeout(() => {
        currentBahanBakuSearchDashboard = searchValue;
        loadOmsetPerBahanBakuChartDashboard(currentYearBahanBakuDashboard, searchValue);
    }, 500); // Wait 500ms after user stops typing
}

// Clear bahan baku search - Dashboard
function clearBahanBakuSearchDashboard() {
    document.getElementById('searchBahanBakuDashboard').value = '';
    document.getElementById('clearSearchBahanBakuDashboard').classList.add('hidden');
    currentBahanBakuSearchDashboard = '';
    loadOmsetPerBahanBakuChartDashboard(currentYearBahanBakuDashboard, '');
}

// Change year for omset per bahan baku chart - Dashboard
function changeYearBahanBakuChartDashboard(direction) {
    const currentIndex = availableYearsBahanBakuDashboard.indexOf(currentYearBahanBakuDashboard);
    let newIndex = currentIndex + direction;
    
    // Boundary check
    if (newIndex < 0 || newIndex >= availableYearsBahanBakuDashboard.length) {
        return;
    }
    
    currentYearBahanBakuDashboard = availableYearsBahanBakuDashboard[newIndex];
    document.getElementById('currentYearBahanBakuDashboard').textContent = currentYearBahanBakuDashboard;
    
    loadOmsetPerBahanBakuChartDashboard(currentYearBahanBakuDashboard, currentBahanBakuSearchDashboard);
}

// Load Omset per Bahan Baku Chart via AJAX - Dashboard
function loadOmsetPerBahanBakuChartDashboard(tahun, search = '') {
    let url = `{{ route('dashboard.omsetPerBahanBaku') }}?tahun=${tahun}`;
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
        updateOmsetPerBahanBakuChartDashboard(result);
    })
    .catch(error => console.error('Error:', error));
}

// Update Omset per Bahan Baku Chart - Dashboard
function updateOmsetPerBahanBakuChartDashboard(data) {
    if (chartOmsetPerBahanBakuDashboard) {
        chartOmsetPerBahanBakuDashboard.data.labels = data.bahan_baku_names;
        chartOmsetPerBahanBakuDashboard.data.datasets = data.datasets;
        chartOmsetPerBahanBakuDashboard.update();
    } else {
        const ctx = document.getElementById('chartOmsetPerBahanBakuDashboard').getContext('2d');
        chartOmsetPerBahanBakuDashboard = createGroupedBarChartBahanBakuDashboard(ctx, data.bahan_baku_names, data.datasets);
    }
}

// Create Line Chart for Omset per Bahan Baku - Dashboard
function createGroupedBarChartBahanBakuDashboard(ctx, labels, datasets) {
    return new Chart(ctx, {
        type: 'line',
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
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
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
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
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
            },
            elements: {
                line: {
                    tension: 0.4,
                    borderWidth: 2
                },
                point: {
                    radius: 4,
                    hitRadius: 10,
                    hoverRadius: 6,
                    hoverBorderWidth: 2
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// Initialize Omset per Bahan Baku Chart - Dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadOmsetPerBahanBakuChartDashboard(currentYearBahanBakuDashboard);
});
</script>
