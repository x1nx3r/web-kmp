<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <x-penawaran.header :editMode="$editMode" />

    <div class="p-6 space-y-6">
        {{-- Main Charts Section --}}
        <x-penawaran.charts />

        {{-- Secondary Content Layout --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Left Section - Client & Material Selection --}}
            <div class="xl:col-span-1 space-y-6">
                {{-- Client Selection --}}
                <x-penawaran.client-selector 
                    :kliens="$kliens"
                    :selectedKlien="$selectedKlien"
                    :selectedKlienCabang="$selectedKlienCabang"
                    :klienSearch="$klienSearch"
                    :selectedKota="$selectedKota"
                    :klienSort="$klienSort"
                    :availableCities="$availableCities"
                />

                {{-- Selected Materials --}}
                <x-penawaran.materials-list 
                    :selectedMaterials="$selectedMaterials"
                    :selectedKlien="$selectedKlien"
                    :selectedKlienCabang="$selectedKlienCabang"
                />
            </div>

            {{-- Right Section - Analysis Table --}}
            <div class="xl:col-span-2">
                {{-- Detailed Analysis Table --}}
                <x-penawaran.analysis-table 
                    :marginAnalysis="$marginAnalysis"
                    :selectedSuppliers="$selectedSuppliers"
                    :totalRevenue="$totalRevenue"
                    :totalCost="$totalCost"
                    :totalProfit="$totalProfit"
                    :overallMargin="$overallMargin"
                />

                {{-- Summary Review Section --}}
                <x-penawaran.summary 
                    :selectedMaterials="$selectedMaterials"
                    :selectedKlien="$selectedKlien"
                    :selectedKlienCabang="$selectedKlienCabang"
                    :totalRevenue="$totalRevenue"
                    :totalProfit="$totalProfit"
                    :overallMargin="$overallMargin"
                    :marginAnalysis="$marginAnalysis"
                    :selectedSuppliers="$selectedSuppliers"
                />

                {{-- Action Buttons --}}
                <x-penawaran.action-buttons 
                    :selectedMaterials="$selectedMaterials"
                    :editMode="$editMode"
                />
            </div>
        </div>
    </div>

    {{-- Add Material Modal --}}
    <x-penawaran.add-material-modal 
        :showAddMaterialModal="$showAddMaterialModal"
        :availableMaterials="$availableMaterials"
        :currentMaterial="$currentMaterial"
        :currentQuantity="$currentQuantity"
        :useCustomPrice="$useCustomPrice"
        :customPrice="$customPrice"
    />
</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Material Tab Chart System Loading...');
    console.log('🎯 Chart.js available:', typeof Chart !== 'undefined');
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js is not loaded!');
        return;
    }
    console.log('✅ Chart.js version:', Chart.version || 'unknown');
    
    // Material tab system state
    let activeMaterialIndex = 0;
    let materialsData = [];
    let materialCharts = {}; // Store chart instances per material
    
    // Chart state management
    let lastUpdateTime = 0;
    
    // Function to create material tabs
    function createMaterialTabs(materials) {
        console.log('📊 Creating material tabs for:', materials.length, 'materials');
        const tabsContainer = document.getElementById('material-tabs-container');
        const emptyState = document.getElementById('charts-empty-state');
        const contentContainer = document.getElementById('tab-content-container');
        
        if (!tabsContainer) return;
        
        tabsContainer.innerHTML = '';
        
        if (materials.length === 0) {
            // Show empty state
            tabsContainer.innerHTML = '<div class="flex-shrink-0 px-4 py-2 text-sm text-gray-500 italic">No materials selected</div>';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }
        
        // Hide empty state
        if (emptyState) emptyState.classList.add('hidden');
        
        // Create tabs for each material
        materials.forEach((material, index) => {
            const tab = document.createElement('div');
            tab.className = `material-tab flex-shrink-0 px-4 py-2 text-sm font-medium rounded-md transition-all cursor-pointer ${
                index === activeMaterialIndex ? 
                'bg-white text-blue-600 shadow-sm border border-blue-200' : 
                'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
            }`;
            
            // Tab content with material name and margin
            const marginPercent = material.margin_percent ? material.margin_percent.toFixed(1) : '0.0';
            const marginColor = material.margin_percent > 20 ? 'text-green-600' : 
                               material.margin_percent > 10 ? 'text-yellow-600' : 'text-red-600';
            
            tab.innerHTML = `
                <div class="flex items-center space-x-2">
                    <span>${material.nama}</span>
                    <span class="text-xs ${marginColor} font-semibold">${marginPercent}%</span>
                </div>
            `;
            
            tab.onclick = () => switchToMaterialTab(index);
            tabsContainer.appendChild(tab);
        });
    }
    
    // Function to switch between material tabs
    function switchToMaterialTab(index) {
        console.log('🔄 Switching to material tab:', index);
        activeMaterialIndex = index;
        
        // Update tab appearance
        document.querySelectorAll('.material-tab').forEach((tab, i) => {
            if (i === index) {
                tab.className = 'material-tab flex-shrink-0 px-4 py-2 text-sm font-medium rounded-md transition-all cursor-pointer bg-white text-blue-600 shadow-sm border border-blue-200';
            } else {
                tab.className = 'material-tab flex-shrink-0 px-4 py-2 text-sm font-medium rounded-md transition-all cursor-pointer text-gray-600 hover:text-gray-900 hover:bg-gray-50';
            }
        });
        
        // Show the content for this material
        showMaterialContent(index);
    }
    
    // Function to show content for specific material
    function showMaterialContent(index) {
        const contentContainer = document.getElementById('tab-content-container');
        const emptyState = document.getElementById('charts-empty-state');
        
        if (!contentContainer || !materialsData[index]) return;
        
        // Hide empty state
        if (emptyState) emptyState.classList.add('hidden');
        
        // Create unique content area for this material
        const materialId = `material-${index}`;
        
        // Hide all other material contents
        contentContainer.querySelectorAll('.material-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Check if content already exists
        let materialContent = document.getElementById(materialId);
        if (!materialContent) {
            // Create new material content
            materialContent = createMaterialContent(materialsData[index], index);
            contentContainer.appendChild(materialContent);
        } else {
            // Show existing content
            materialContent.classList.remove('hidden');
        }
        
        // Create or update charts for this material
        createMaterialCharts(materialsData[index], index);
    }
    
    // Function to create material content HTML
    function createMaterialContent(materialData, index) {
        const materialContent = document.createElement('div');
        materialContent.id = `material-${index}`;
        materialContent.className = 'material-content';
        
        materialContent.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Client Chart -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-md font-semibold text-gray-900">Client Prices</h4>
                        <div class="flex items-center space-x-2 text-xs">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span class="text-gray-600">${materialData.nama}</span>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <canvas id="client-chart-${index}" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Supplier Chart -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-md font-semibold text-gray-900">Supplier Options</h4>
                        <div class="flex items-center space-x-2 text-xs">
                            <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                            <span class="text-gray-600">${materialData.supplier_options ? materialData.supplier_options.length : 0} suppliers</span>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <canvas id="supplier-chart-${index}" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        `;
        
        return materialContent;
    }
    
    // Function to create charts for specific material
    function createMaterialCharts(materialData, index) {
        console.log('📈 Creating charts for material:', materialData.nama);
        
        const clientCanvas = document.getElementById(`client-chart-${index}`);
        const supplierCanvas = document.getElementById(`supplier-chart-${index}`);
        
        if (!clientCanvas || !supplierCanvas) {
            console.error('Canvas elements not found for material:', index);
            return;
        }
        
        // Destroy existing charts if they exist
        if (materialCharts[index]) {
            if (materialCharts[index].clientChart) {
                materialCharts[index].clientChart.destroy();
            }
            if (materialCharts[index].supplierChart) {
                materialCharts[index].supplierChart.destroy();
            }
        }
        
        // Create client chart
        const clientChart = createClientChart(clientCanvas, materialData);
        
        // Create supplier chart
        const supplierChart = createSupplierChart(supplierCanvas, materialData);
        
        // Store chart instances
        materialCharts[index] = {
            clientChart: clientChart,
            supplierChart: supplierChart
        };
    }
    
    // Function to create client price chart
    function createClientChart(canvas, materialData) {
        const priceHistory = materialData.klien_price_history || [];
        
        const data = {
            labels: priceHistory.map(point => point.formatted_tanggal),
            datasets: [{
                label: `${materialData.nama} Client Price`,
                data: priceHistory.map(point => point.harga),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: false,
                tension: 0.4
            }]
        };
        
        return new Chart(canvas, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { color: 'rgb(107, 114, 128)', maxTicksLimit: 6 }
                    },
                    y: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { 
                            color: 'rgb(107, 114, 128)',
                            callback: function(value) {
                                return 'Rp ' + (value / 1000) + 'k';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Function to create supplier price chart
    function createSupplierChart(canvas, materialData) {
        const suppliers = materialData.supplier_options || [];
        
        const datasets = suppliers.map((supplier, index) => {
            const colors = [
                { border: 'rgb(16, 185, 129)', bg: 'rgba(16, 185, 129, 0.1)' }, // Green
                { border: 'rgb(249, 115, 22)', bg: 'rgba(249, 115, 22, 0.1)' }, // Orange  
                { border: 'rgb(139, 92, 246)', bg: 'rgba(139, 92, 246, 0.1)' }  // Purple
            ];
            const color = colors[index % colors.length];
            
            return {
                label: supplier.supplier_name + (supplier.is_best ? ' (Best)' : ''),
                data: (supplier.price_history || []).map(point => point.harga),
                borderColor: color.border,
                backgroundColor: color.bg,
                borderWidth: supplier.is_best ? 3 : 2,
                borderDash: supplier.is_best ? [] : [5, 5],
                fill: false,
                tension: 0.4
            };
        });
        
        // Get all unique dates from all suppliers
        const allDates = new Set();
        suppliers.forEach(supplier => {
            (supplier.price_history || []).forEach(point => {
                allDates.add(point.formatted_tanggal);
            });
        });
        
        const sortedDates = Array.from(allDates).sort((a, b) => {
            const dateA = new Date(a + ' 2025');
            const dateB = new Date(b + ' 2025');
            return dateA - dateB;
        });
        
        return new Chart(canvas, {
            type: 'line',
            data: {
                labels: sortedDates,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 10 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgb(249, 115, 22)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + 
                                       new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { color: 'rgb(107, 114, 128)', maxTicksLimit: 6 }
                    },
                    y: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { 
                            color: 'rgb(107, 114, 128)',
                            callback: function(value) {
                                return 'Rp ' + (value / 1000) + 'k';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Main function to update charts with new data
    function updateCharts(dynamicData = null) {
        console.log('🚀 updateCharts() called with material tab system');
        
        // Prevent rapid successive updates
        const now = Date.now();
        if (now - lastUpdateTime < 50) {
            console.log('Skipping rapid update');
            return;
        }
        lastUpdateTime = now;

        // Use dynamic data from event if available, otherwise use server-side data
        const analysisData = dynamicData || @json($marginAnalysis);
        
        console.log('📊 Analysis data received:', analysisData);
        console.log('📊 Data length:', analysisData ? analysisData.length : 0);
        
        if (!analysisData || analysisData.length === 0) {
            console.log('📊 No data available - showing empty state');
            materialsData = [];
            createMaterialTabs([]);
            return;
        }
        
        // Store materials data
        materialsData = analysisData;
        
        // Create tabs and show first material
        createMaterialTabs(analysisData);
        
        // Show first material by default
        if (analysisData.length > 0) {
            switchToMaterialTab(0);
        }
        
        console.log('✅ Material tab system updated successfully');
    }

    // Initialize on page load
    setTimeout(() => {
        updateCharts();
    }, 100);

    // Listen for margin analysis updates
    window.addEventListener('margin-analysis-updated', function(event) {
        console.log('🔥 margin-analysis-updated event received');
        const newData = event.detail.analysisData || event.detail[0]?.analysisData;
        setTimeout(() => {
            updateCharts(newData);
        }, 100);
    });
    
    // Listen for chart data updates (for custom prices)
    window.addEventListener('chart-data-updated', function(event) {
        console.log('chart-data-updated event received');
        setTimeout(() => updateCharts(), 100);
    });
});
</script>
