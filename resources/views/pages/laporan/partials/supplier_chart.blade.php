{{-- Omset per Supplier (Bar Chart) Section --}}
<div class="mb-6">
    {{-- Card: Omset per Supplier --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-bar text-orange-500 mr-2"></i>
                    Omset Supplier 
                    <span class="ml-2 px-2 py-1 text-xs bg-orange-100 text-orange-700 rounded-full" 
                          title="Omset Sistem (transaksi terverifikasi) per bulan">
                        <i class="fas fa-info-circle mr-1"></i>Per Bulan
                    </span>
                </h3>
                <p class="text-sm text-gray-500 mt-1">Distribusi omset bulanan per supplier</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Search Filter --}}
                <div class="relative">
                    <input type="text" 
                           id="searchSupplier" 
                           placeholder="Cari supplier..." 
                           class="w-64 pl-10 pr-10 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                           onkeyup="handleSupplierSearchKeyup(event)">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <button onclick="clearSupplierSearch()" 
                            id="clearSearchSupplier"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                {{-- Year Navigation --}}
                <div class="flex items-center gap-2">
                    <button onclick="changeYearSupplierChart(-1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="px-4 py-2 bg-orange-50 rounded-lg">
                        <span class="text-sm font-semibold text-orange-700">Tahun: </span>
                        <span id="currentYearSupplier" class="text-lg font-bold text-orange-600">{{ date('Y') }}</span>
                    </div>
                    <button onclick="changeYearSupplierChart(1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-right text-gray-600"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div style="height: 500px;">
            <canvas id="chartOmsetPerSupplier"></canvas>
        </div>
    </div>
</div>

<script>
// Chart instance for supplier
let chartOmsetPerSupplier = null;

// Current year for omset per supplier
let currentYearSupplier = {{ date('Y') }};
const availableYearsSupplier = @json(range(2020, date('Y')));

// Search filter state
let supplierSearchTimeout = null;
let currentSupplierSearch = '';

// Handle keyup event for supplier search (debounced)
function handleSupplierSearchKeyup(event) {
    const searchValue = event.target.value.trim();
    
    // Show/hide clear button
    const clearBtn = document.getElementById('clearSearchSupplier');
    if (searchValue) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    
    // Debounce the search
    clearTimeout(supplierSearchTimeout);
    supplierSearchTimeout = setTimeout(() => {
        currentSupplierSearch = searchValue;
        loadOmsetPerSupplierChart(currentYearSupplier, searchValue);
    }, 500); // Wait 500ms after user stops typing
}

// Clear supplier search
function clearSupplierSearch() {
    document.getElementById('searchSupplier').value = '';
    document.getElementById('clearSearchSupplier').classList.add('hidden');
    currentSupplierSearch = '';
    loadOmsetPerSupplierChart(currentYearSupplier, '');
}

// Change year for omset per supplier chart
function changeYearSupplierChart(direction) {
    const currentIndex = availableYearsSupplier.indexOf(currentYearSupplier);
    let newIndex = currentIndex + direction;
    
    // Boundary check
    if (newIndex < 0 || newIndex >= availableYearsSupplier.length) {
        return;
    }
    
    currentYearSupplier = availableYearsSupplier[newIndex];
    document.getElementById('currentYearSupplier').textContent = currentYearSupplier;
    
    loadOmsetPerSupplierChart(currentYearSupplier, currentSupplierSearch);
}

// Load Omset per Supplier Chart via AJAX
function loadOmsetPerSupplierChart(tahun, search = '') {
    let url = `{{ route('laporan.omset') }}?ajax=omset_per_supplier&tahun=${tahun}`;
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
        updateOmsetPerSupplierChart(result);
    })
    .catch(error => console.error('Error:', error));
}

// Update Omset per Supplier Chart
function updateOmsetPerSupplierChart(data) {
    if (chartOmsetPerSupplier) {
        chartOmsetPerSupplier.data.labels = data.supplier_names;
        chartOmsetPerSupplier.data.datasets = data.datasets;
        chartOmsetPerSupplier.update();
    } else {
        const ctx = document.getElementById('chartOmsetPerSupplier').getContext('2d');
        chartOmsetPerSupplier = createGroupedBarChartSupplier(ctx, data.supplier_names, data.datasets);
    }
}

// Create Grouped Bar Chart for Omset per Supplier
function createGroupedBarChartSupplier(ctx, labels, datasets) {
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

// Initialize Omset per Supplier Chart
document.addEventListener('DOMContentLoaded', function() {
    loadOmsetPerSupplierChart(currentYearSupplier);
});
</script>
