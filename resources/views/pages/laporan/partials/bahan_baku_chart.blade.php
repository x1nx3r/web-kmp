{{-- Omset per Bahan Baku (Line Chart) Section --}}
<div class="mb-6">
    {{-- Card: Omset per Bahan Baku --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4 gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-line text-purple-500 mr-2"></i>
                    Omset Bahan Baku Klien
                    <span class="ml-2 px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded-full" 
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
                           id="searchBahanBaku" 
                           placeholder="Cari bahan baku..." 
                           class="w-full pl-10 pr-10 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           onkeyup="handleBahanBakuSearchKeyup(event)">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <button onclick="clearBahanBakuSearch()" 
                            id="clearSearchBahanBaku"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                {{-- Year Navigation --}}
                <div class="flex items-center justify-center gap-2">
                    <button onclick="changeYearBahanBakuChart(-1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="px-3 sm:px-4 py-2 bg-purple-50 rounded-lg">
                        <span class="text-xs sm:text-sm font-semibold text-purple-700">Tahun: </span>
                        <span id="currentYearBahanBaku" class="text-base sm:text-lg font-bold text-purple-600">{{ date('Y') }}</span>
                    </div>
                    <button onclick="changeYearBahanBakuChart(1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-right text-gray-600"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="h-64 sm:h-80 md:h-96 lg:h-[500px]">
            <canvas id="chartOmsetPerBahanBaku"></canvas>
        </div>
    </div>
</div>

<script>
// Chart instance for bahan baku
let chartOmsetPerBahanBaku = null;

// Current year for omset per bahan baku
let currentYearBahanBaku = {{ date('Y') }};
const availableYearsBahanBaku = @json(range(2020, date('Y')));

// Search filter state
let bahanBakuSearchTimeout = null;
let currentBahanBakuSearch = '';

// Handle keyup event for bahan baku search (debounced)
function handleBahanBakuSearchKeyup(event) {
    const searchValue = event.target.value.trim();
    
    // Show/hide clear button
    const clearBtn = document.getElementById('clearSearchBahanBaku');
    if (searchValue) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    
    // Debounce the search
    clearTimeout(bahanBakuSearchTimeout);
    bahanBakuSearchTimeout = setTimeout(() => {
        currentBahanBakuSearch = searchValue;
        loadOmsetPerBahanBakuChart(currentYearBahanBaku, searchValue);
    }, 500); // Wait 500ms after user stops typing
}

// Clear bahan baku search
function clearBahanBakuSearch() {
    document.getElementById('searchBahanBaku').value = '';
    document.getElementById('clearSearchBahanBaku').classList.add('hidden');
    currentBahanBakuSearch = '';
    loadOmsetPerBahanBakuChart(currentYearBahanBaku, '');
}

// Change year for omset per bahan baku chart
function changeYearBahanBakuChart(direction) {
    const currentIndex = availableYearsBahanBaku.indexOf(currentYearBahanBaku);
    let newIndex = currentIndex + direction;
    
    // Boundary check
    if (newIndex < 0 || newIndex >= availableYearsBahanBaku.length) {
        return;
    }
    
    currentYearBahanBaku = availableYearsBahanBaku[newIndex];
    document.getElementById('currentYearBahanBaku').textContent = currentYearBahanBaku;
    
    loadOmsetPerBahanBakuChart(currentYearBahanBaku, currentBahanBakuSearch);
}

// Load Omset per Bahan Baku Chart via AJAX
function loadOmsetPerBahanBakuChart(tahun, search = '') {
    let url = `{{ route('laporan.omset') }}?ajax=omset_per_bahan_baku&tahun=${tahun}`;
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
        updateOmsetPerBahanBakuChart(result);
    })
    .catch(error => console.error('Error:', error));
}

// Update Omset per Bahan Baku Chart
function updateOmsetPerBahanBakuChart(data) {
    if (chartOmsetPerBahanBaku) {
        chartOmsetPerBahanBaku.data.labels = data.bahan_baku_names;
        chartOmsetPerBahanBaku.data.datasets = data.datasets;
        chartOmsetPerBahanBaku.update();
    } else {
        const ctx = document.getElementById('chartOmsetPerBahanBaku').getContext('2d');
        chartOmsetPerBahanBaku = createGroupedBarChartBahanBaku(ctx, data.bahan_baku_names, data.datasets);
    }
}

// Create Line Chart for Omset per Bahan Baku
function createGroupedBarChartBahanBaku(ctx, labels, datasets) {
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

// Initialize Omset per Bahan Baku Chart
document.addEventListener('DOMContentLoaded', function() {
    loadOmsetPerBahanBakuChart(currentYearBahanBaku);
});
</script>
