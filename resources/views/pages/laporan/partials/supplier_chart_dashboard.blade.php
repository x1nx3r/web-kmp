{{-- Omset per Supplier (Bar Chart) Section - Dashboard --}}
<div class="mb-6">
    {{-- Card: Omset per Supplier --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4 gap-4">
            <div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center flex-wrap">
                    <i class="fas fa-chart-bar text-orange-500 mr-2"></i>
                    <span class="mr-2">Omset Supplier</span>
                    <span class="px-2 py-1 text-xs bg-orange-100 text-orange-700 rounded-full" 
                          title="Omset Sistem (transaksi terverifikasi) per bulan">
                        <i class="fas fa-info-circle mr-1"></i>Per Bulan
                    </span>
                </h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Distribusi omset bulanan per supplier</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                {{-- Search Filter --}}
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           id="searchSupplierDashboard" 
                           placeholder="Cari supplier..." 
                           class="w-full pl-10 pr-10 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                           onkeyup="handleSupplierSearchKeyupDashboard(event)">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <button onclick="clearSupplierSearchDashboard()" 
                            id="clearSearchSupplierDashboard"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                {{-- Year Navigation --}}
                <div class="flex items-center justify-center gap-2">
                    <button onclick="changeYearSupplierChartDashboard(-1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="px-3 sm:px-4 py-2 bg-orange-50 rounded-lg">
                        <span class="text-xs sm:text-sm font-semibold text-orange-700">Tahun: </span>
                        <span id="currentYearSupplierDashboard" class="text-base sm:text-lg font-bold text-orange-600">{{ date('Y') }}</span>
                    </div>
                    <button onclick="changeYearSupplierChartDashboard(1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-right text-gray-600"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="h-64 sm:h-80 md:h-96 lg:h-[500px]">
            <canvas id="chartOmsetPerSupplierDashboard"></canvas>
        </div>
    </div>
</div>

<script>
// Chart instance for supplier - Dashboard
let chartOmsetPerSupplierDashboard = null;

// Current year for omset per supplier - Dashboard
let currentYearSupplierDashboard = {{ date('Y') }};
const availableYearsSupplierDashboard = @json(range(2020, date('Y')));

// Search filter state - Dashboard
let supplierSearchTimeoutDashboard = null;
let currentSupplierSearchDashboard = '';

// Handle keyup event for supplier search (debounced) - Dashboard
function handleSupplierSearchKeyupDashboard(event) {
    const searchValue = event.target.value.trim();
    
    // Show/hide clear button
    const clearBtn = document.getElementById('clearSearchSupplierDashboard');
    if (searchValue) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    
    // Debounce the search
    clearTimeout(supplierSearchTimeoutDashboard);
    supplierSearchTimeoutDashboard = setTimeout(() => {
        currentSupplierSearchDashboard = searchValue;
        loadOmsetPerSupplierChartDashboard(currentYearSupplierDashboard, searchValue);
    }, 500); // Wait 500ms after user stops typing
}

// Clear supplier search - Dashboard
function clearSupplierSearchDashboard() {
    document.getElementById('searchSupplierDashboard').value = '';
    document.getElementById('clearSearchSupplierDashboard').classList.add('hidden');
    currentSupplierSearchDashboard = '';
    loadOmsetPerSupplierChartDashboard(currentYearSupplierDashboard, '');
}

// Change year for omset per supplier chart - Dashboard
function changeYearSupplierChartDashboard(direction) {
    const currentIndex = availableYearsSupplierDashboard.indexOf(currentYearSupplierDashboard);
    let newIndex = currentIndex + direction;
    
    // Boundary check
    if (newIndex < 0 || newIndex >= availableYearsSupplierDashboard.length) {
        return;
    }
    
    currentYearSupplierDashboard = availableYearsSupplierDashboard[newIndex];
    document.getElementById('currentYearSupplierDashboard').textContent = currentYearSupplierDashboard;
    
    loadOmsetPerSupplierChartDashboard(currentYearSupplierDashboard, currentSupplierSearchDashboard);
}

// Load Omset per Supplier Chart via AJAX - Dashboard
function loadOmsetPerSupplierChartDashboard(tahun, search = '') {
    let url = `{{ route('dashboard.omsetPerSupplier') }}?tahun=${tahun}`;
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
        updateOmsetPerSupplierChartDashboard(result);
    })
    .catch(error => console.error('Error:', error));
}

// Update Omset per Supplier Chart - Dashboard
function updateOmsetPerSupplierChartDashboard(data) {
    if (chartOmsetPerSupplierDashboard) {
        chartOmsetPerSupplierDashboard.data.labels = data.supplier_names;
        chartOmsetPerSupplierDashboard.data.datasets = data.datasets;
        chartOmsetPerSupplierDashboard.update();
    } else {
        const ctx = document.getElementById('chartOmsetPerSupplierDashboard').getContext('2d');
        chartOmsetPerSupplierDashboard = createGroupedBarChartSupplierDashboard(ctx, data.supplier_names, data.datasets);
    }
}

// Create Grouped Bar Chart for Omset per Supplier - Dashboard
function createGroupedBarChartSupplierDashboard(ctx, labels, datasets) {
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

// Initialize Omset per Supplier Chart - Dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadOmsetPerSupplierChartDashboard(currentYearSupplierDashboard);
});
</script>
