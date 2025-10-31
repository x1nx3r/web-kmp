@props(['order', 'chartsData'])

{{-- Order Price Analysis with Material Tabs --}}
<div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-visible">
    {{-- Header Section --}}
    <div class="border-b border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-chart-line text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Order Price Analysis</h3>
                    <p class="text-sm text-gray-500 mt-1">Historical pricing for materials in order #{{ $order->no_order }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-xs text-gray-600">Client</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                    <span class="text-xs text-gray-600">Supplier</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-xs text-gray-600">Selected</span>
                </div>
            </div>
        </div>
        
        {{-- Dynamic Material Tabs --}}
        <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg overflow-x-auto" id="order-material-tabs-container">
            @if(count($chartsData) > 0)
                @foreach($chartsData as $index => $materialData)
                    <div class="order-material-tab flex-shrink-0 px-4 py-2 text-sm font-medium rounded-md transition-all cursor-pointer {{ $index === 0 ? 'bg-white text-green-600 shadow-sm border border-green-200' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
                         onclick="switchToOrderMaterialTab({{ $index }})">
                        <div class="flex items-center space-x-2">
                            <span>{{ $materialData['nama'] }}</span>
                            @if(isset($materialData['margin_percent']))
                                <span class="text-xs {{ $materialData['margin_percent'] > 20 ? 'text-green-600' : ($materialData['margin_percent'] > 10 ? 'text-yellow-600' : 'text-red-600') }} font-semibold">
                                    {{ number_format($materialData['margin_percent'], 1) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="flex-shrink-0 px-4 py-2 text-sm text-gray-500 italic">
                    No materials in this order
                </div>
            @endif
        </div>
    </div>

    {{-- Tab Content Container --}}
    <div class="p-8 relative" id="order-tab-content-container">
        @if(count($chartsData) === 0)
            {{-- Empty State --}}
            <div class="flex items-center justify-center h-96 text-center">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-500 text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900">No Materials Found</h4>
                    <p class="text-sm text-gray-500 max-w-md">
                        This order doesn't contain any materials with pricing data to analyze.
                    </p>
                </div>
            </div>
        @endif
        {{-- Material content will be dynamically populated --}}
    </div>
</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Order Material Chart System Loading...');
    console.log('🎯 Chart.js available:', typeof Chart !== 'undefined');
    
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js is not loaded!');
        return;
    }
    
    // Global date constants
    const today = new Date();
    const todayString = today.toISOString().split('T')[0];
    const todayFormatted = today.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
    
    // Order chart system state
    let activeOrderMaterialIndex = 0;
    let orderMaterialsData = @json($chartsData);
    let orderMaterialCharts = {};
    
    console.log('📊 Order materials data:', orderMaterialsData);
    
    // Function to interpolate missing data points
    function interpolateOrderData(priceHistory, allDates) {
        if (!priceHistory || priceHistory.length === 0) return [];
        
        const result = [];
        const historyMap = new Map();
        
        priceHistory.forEach(point => {
            historyMap.set(point.formatted_tanggal, point);
        });
        
        const sortedDates = [...allDates].sort((a, b) => {
            if (a === todayFormatted) return 1;
            if (b === todayFormatted) return -1;
            return new Date(a + ', 2025') - new Date(b + ', 2025');
        });
        
        let lastActualPrice = null;
        
        sortedDates.forEach((date, index) => {
            if (historyMap.has(date)) {
                const actualPoint = historyMap.get(date);
                result.push(actualPoint);
                if (!actualPoint.extrapolated) {
                    lastActualPrice = actualPoint.harga;
                }
            } else {
                if (lastActualPrice !== null) {
                    result.push({
                        tanggal: date,
                        formatted_tanggal: date,
                        harga: lastActualPrice,
                        interpolated: true
                    });
                }
            }
        });
        
        return result;
    }
    
    // Function to switch between material tabs
    function switchToOrderMaterialTab(index) {
        console.log('🔄 Switching to order material tab:', index);
        
        if (!orderMaterialsData || index < 0 || index >= orderMaterialsData.length) {
            console.error('Invalid material index:', index);
            return;
        }
        
        activeOrderMaterialIndex = index;
        
        // Update tab appearance
        document.querySelectorAll('.order-material-tab').forEach((tab, i) => {
            if (i === index) {
                tab.className = 'order-material-tab flex-shrink-0 px-4 py-2 text-sm font-medium rounded-md transition-all cursor-pointer bg-white text-green-600 shadow-sm border border-green-200';
            } else {
                tab.className = 'order-material-tab flex-shrink-0 px-4 py-2 text-sm font-medium rounded-md transition-all cursor-pointer text-gray-600 hover:text-gray-900 hover:bg-gray-50';
            }
        });
        
        showOrderMaterialContent(index);
    }
    
    // Make function globally available
    window.switchToOrderMaterialTab = switchToOrderMaterialTab;
    
    // Function to show content for specific material
    function showOrderMaterialContent(index) {
        console.log('📋 Showing order material content for index:', index);
        const contentContainer = document.getElementById('order-tab-content-container');
        
        if (!contentContainer || !orderMaterialsData[index]) {
            console.error('Content container or material data not found');
            return;
        }
        
        const materialId = `order-material-${index}`;
        
        // Hide all other material contents
        contentContainer.querySelectorAll('.order-material-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Check if content already exists
        let materialContent = document.getElementById(materialId);
        if (!materialContent) {
            console.log('📋 Creating new order material content for:', orderMaterialsData[index].nama);
            materialContent = createOrderMaterialContent(orderMaterialsData[index], index);
            contentContainer.appendChild(materialContent);
        } else {
            console.log('📋 Showing existing order material content for:', orderMaterialsData[index].nama);
            materialContent.classList.remove('hidden');
        }
        
        // Create charts
        setTimeout(() => {
            createOrderMaterialCharts(orderMaterialsData[index], index);
        }, 10);
    }
    
    // Function to create material content HTML
    function createOrderMaterialContent(materialData, index) {
        const materialContent = document.createElement('div');
        materialContent.id = `order-material-${index}`;
        materialContent.className = 'order-material-content';
        
        materialContent.innerHTML = `
            <div class="mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex flex-col sm:flex-row gap-4 text-sm">
                        <div class="flex-1 min-w-0">
                            <span class="text-gray-600">Order Quantity:</span>
                            <span class="font-semibold ml-2">${materialData.order_quantity} ${materialData.satuan}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-gray-600">Order Price:</span>
                            <span class="font-semibold ml-2">Rp ${new Intl.NumberFormat('id-ID').format(materialData.order_price)}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-gray-600">Selected Suppliers:</span>
                            <span class="font-semibold ml-2">${materialData.supplier_options.filter(s => s.is_selected).length}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col xl:flex-row gap-6">
                <!-- Client Chart -->
                <div class="flex-1 min-w-0 bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-md font-semibold text-gray-900">Client Price History</h4>
                        <div class="flex items-center space-x-2 text-xs">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span class="text-gray-600">${materialData.nama}</span>
                        </div>
                    </div>
                    <div class="h-80 w-full relative">
                        <canvas id="order-client-chart-${index}" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Supplier Chart -->
                <div class="flex-1 min-w-0 bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-md font-semibold text-gray-900">Supplier Options</h4>
                        <div class="flex items-center space-x-2 text-xs">
                            <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                            <span class="text-gray-600">${materialData.supplier_options ? materialData.supplier_options.length : 0} suppliers</span>
                        </div>
                    </div>
                    <div class="h-80 w-full relative">
                        <canvas id="order-supplier-chart-${index}" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>

            <!-- Legends -->
            <div class="mt-4 flex flex-col xl:flex-row gap-4">
                <div class="flex-1 min-w-0 bg-transparent">
                    <h5 class="text-xs text-gray-500 mb-2">Client Legend</h5>
                    <div class="overflow-x-auto">
                        <div id="order-client-legend-${index}" class="inline-flex items-center space-x-2 px-2 py-1 whitespace-nowrap"></div>
                    </div>
                </div>
                <div class="flex-1 min-w-0 bg-transparent">
                    <h5 class="text-xs text-gray-500 mb-2">Supplier Legend</h5>
                    <div class="overflow-x-auto">
                        <div id="order-supplier-legend-${index}" class="inline-flex items-center space-x-2 px-2 py-1 whitespace-nowrap"></div>
                    </div>
                </div>
            </div>
        `;
        
        return materialContent;
    }
    
    // Function to create charts for specific material
    function createOrderMaterialCharts(materialData, index) {
        console.log('📈 Creating order charts for material:', materialData.nama);
        
        const clientCanvas = document.getElementById(`order-client-chart-${index}`);
        const supplierCanvas = document.getElementById(`order-supplier-chart-${index}`);
        
        if (!clientCanvas || !supplierCanvas) {
            console.error('Canvas elements not found for material:', index);
            return;
        }
        
        // Destroy existing charts if they exist
        if (orderMaterialCharts[index]) {
            if (orderMaterialCharts[index].clientChart) {
                orderMaterialCharts[index].clientChart.destroy();
            }
            if (orderMaterialCharts[index].supplierChart) {
                orderMaterialCharts[index].supplierChart.destroy();
            }
        }
        
        // Calculate synchronized axes
        const allPrices = [];
        
        // Collect client prices
        if (materialData.client_price_history && materialData.client_price_history.length > 0) {
            materialData.client_price_history.forEach(point => {
                if (point.harga) allPrices.push(point.harga);
            });
        }
        
        // Include order price
        if (materialData.order_price && materialData.order_price > 0) {
            allPrices.push(parseFloat(materialData.order_price));
        }
        
        // Collect supplier prices
        if (materialData.supplier_options && materialData.supplier_options.length > 0) {
            materialData.supplier_options.forEach(supplier => {
                if (supplier.price_history && supplier.price_history.length > 0) {
                    supplier.price_history.forEach(point => {
                        if (point.harga) allPrices.push(point.harga);
                    });
                }
                if (supplier.current_price) {
                    allPrices.push(parseFloat(supplier.current_price));
                }
            });
        }
        
        // Calculate synchronized y-axis range
        let yAxisMin = 0;
        let yAxisMax = 100000;
        
        if (allPrices.length > 0) {
            const minPrice = Math.min(...allPrices);
            const maxPrice = Math.max(...allPrices);
            const padding = (maxPrice - minPrice) * 0.1;
            
            let calculatedMin = minPrice - padding;
            let calculatedMax = maxPrice + padding;
            
            const roundingFactor = maxPrice < 100000 ? 1000 : 10000;
            
            yAxisMin = Math.floor(calculatedMin / roundingFactor) * roundingFactor;
            yAxisMax = Math.ceil(calculatedMax / roundingFactor) * roundingFactor;
            yAxisMin = Math.max(0, yAxisMin);
        }
        
        console.log(`📊 Synchronized Y-axis for ${materialData.nama}:`, { yAxisMin, yAxisMax });
        
        // Create charts
        const clientChart = createOrderClientChart(clientCanvas, materialData, index, { yAxisMin, yAxisMax });
        const supplierChart = createOrderSupplierChart(supplierCanvas, materialData, index, { yAxisMin, yAxisMax });
        
        // Store chart instances
        orderMaterialCharts[index] = {
            clientChart: clientChart,
            supplierChart: supplierChart
        };
    }
    
    // Function to create client price chart
    function createOrderClientChart(canvas, materialData, materialIndex, axisConfig) {
        const { yAxisMin, yAxisMax } = axisConfig;
        
        let priceHistory = materialData.client_price_history || [];
        
        // Add order price as today's data point
        if (materialData.order_price && materialData.order_price > 0) {
            priceHistory = priceHistory.filter(point => 
                !(point.formatted_tanggal === todayFormatted)
            );
            
            const orderPoint = {
                tanggal: todayString,
                harga: parseFloat(materialData.order_price),
                formatted_tanggal: todayFormatted,
                is_order_price: true
            };
            
            priceHistory.push(orderPoint);
        }
        
        priceHistory.sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
        
        const data = {
            labels: priceHistory.map(point => point.formatted_tanggal),
            datasets: [{
                label: `${materialData.nama} Client Price`,
                data: priceHistory.map(point => point.harga),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: false,
                tension: 0.4,
                pointRadius: priceHistory.map(point => point.is_order_price ? 8 : 4),
                pointBackgroundColor: priceHistory.map(point => {
                    if (point.is_order_price) return 'rgb(34, 197, 94)';
                    return 'rgb(59, 130, 246)';
                }),
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        };
        
        const chart = new Chart(canvas, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const point = priceHistory[context.dataIndex];
                                let suffix = '';
                                if (point?.is_order_price) suffix = ' (Order Price)';
                                
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y) + suffix;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { 
                            color: 'rgb(107, 114, 128)', 
                            maxTicksLimit: 8,
                            callback: function(value, index) {
                                const label = this.getLabelForValue(value);
                                return label === todayFormatted ? label + ' (Order)' : label;
                            }
                        }
                    },
                    y: {
                        min: yAxisMin,
                        max: yAxisMax,
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
        
        createOrderExternalLegend(chart, `order-client-legend-${materialIndex}`);
        return chart;
    }
    
    // Function to create supplier price chart
    function createOrderSupplierChart(canvas, materialData, materialIndex, axisConfig) {
        const { yAxisMin, yAxisMax } = axisConfig;
        const suppliers = materialData.supplier_options || [];
        
        const datasets = suppliers.map((supplier, index) => {
            const colors = [
                { border: 'rgb(16, 185, 129)', bg: 'rgba(16, 185, 129, 0.1)' },
                { border: 'rgb(249, 115, 22)', bg: 'rgba(249, 115, 22, 0.1)' },
                { border: 'rgb(139, 92, 246)', bg: 'rgba(139, 92, 246, 0.1)' },
                { border: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' },
                { border: 'rgb(14, 165, 233)', bg: 'rgba(14, 165, 233, 0.1)' }
            ];
            
            const color = supplier.is_selected ? 
                { border: 'rgb(34, 197, 94)', bg: 'rgba(34, 197, 94, 0.1)' } : // Green for selected
                colors[index % colors.length];
            
            let priceHistory = supplier.price_history || [];
            
            // Add current price as today's point
            if (supplier.current_price) {
                priceHistory = priceHistory.filter(point => 
                    !(point.formatted_tanggal === todayFormatted)
                );
                
                priceHistory.push({
                    tanggal: todayString,
                    harga: parseFloat(supplier.current_price),
                    formatted_tanggal: todayFormatted,
                    is_current_price: true
                });
            }
            
            priceHistory.sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
            
            return {
                label: supplier.supplier_name + 
                       (supplier.pic_name ? ` (PIC: ${supplier.pic_name})` : '') +
                       (supplier.is_selected ? ' ✓ Selected' : ''),
                data: priceHistory.map(point => point.harga),
                borderColor: color.border,
                backgroundColor: color.bg,
                borderWidth: supplier.is_selected ? 4 : 2,
                borderDash: supplier.is_selected ? [] : [5, 5],
                fill: false,
                tension: 0.4,
                pointRadius: priceHistory.map(point => {
                    if (supplier.is_selected) return point.is_current_price ? 8 : 6;
                    return point.is_current_price ? 6 : 4;
                }),
                pointBackgroundColor: priceHistory.map(point => {
                    if (point.is_current_price) return 'rgb(34, 197, 94)';
                    return color.border;
                }),
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                priceHistory: priceHistory
            };
        });
        
        // Get labels from all datasets
        const allDates = new Set();
        datasets.forEach(dataset => {
            dataset.priceHistory.forEach(point => {
                allDates.add(point.formatted_tanggal);
            });
        });
        
        allDates.add(todayFormatted);
        const sortedDates = Array.from(allDates).sort((a, b) => {
            if (a === todayFormatted && b !== todayFormatted) return 1;
            if (b === todayFormatted && a !== todayFormatted) return -1;
            if (a === todayFormatted && b === todayFormatted) return 0;
            
            const dateA = new Date(a + ', 2025');
            const dateB = new Date(b + ', 2025');
            return dateA - dateB;
        });
        
        const chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: sortedDates,
                datasets: datasets.map(dataset => ({
                    ...dataset,
                    data: sortedDates.map(date => {
                        const point = dataset.priceHistory.find(p => p.formatted_tanggal === date);
                        return point ? point.harga : null;
                    }),
                    spanGaps: true
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgb(249, 115, 22)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const dataset = datasets[context.datasetIndex];
                                const date = sortedDates[context.dataIndex];
                                const point = dataset.priceHistory.find(p => p.formatted_tanggal === date);
                                
                                let suffix = '';
                                if (point?.is_current_price) suffix = ' (Order Price)';
                                
                                return context.dataset.label + ': Rp ' + 
                                       new Intl.NumberFormat('id-ID').format(context.parsed.y) + suffix;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { 
                            color: 'rgb(107, 114, 128)', 
                            maxTicksLimit: 8,
                            callback: function(value, index) {
                                const label = this.getLabelForValue(value);
                                return label === todayFormatted ? label + ' (Order)' : label;
                            }
                        }
                    },
                    y: {
                        min: yAxisMin,
                        max: yAxisMax,
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
        
        createOrderExternalLegend(chart, `order-supplier-legend-${materialIndex}`);
        return chart;
    }
    
    // Function to create external legend
    function createOrderExternalLegend(chart, legendContainerId) {
        const legendContainer = document.getElementById(legendContainerId);
        if (!legendContainer) return;
        
        legendContainer.innerHTML = '';
        
        chart.data.datasets.forEach((dataset, datasetIndex) => {
            const legendItem = document.createElement('div');
            legendItem.className = 'flex items-center space-x-2 px-3 py-2 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-all';
            
            const isVisible = chart.isDatasetVisible(datasetIndex);
            if (!isVisible) {
                legendItem.classList.add('opacity-50');
            }
            
            const colorBox = document.createElement('div');
            colorBox.className = 'w-3 h-3 rounded-sm flex-shrink-0';
            colorBox.style.backgroundColor = dataset.borderColor;
            
            const label = document.createElement('span');
            label.className = 'text-xs font-medium text-gray-700 truncate';
            label.textContent = dataset.label;
            
            legendItem.appendChild(colorBox);
            legendItem.appendChild(label);
            
            legendItem.addEventListener('click', () => {
                const meta = chart.getDatasetMeta(datasetIndex);
                meta.hidden = meta.hidden === null ? !chart.data.datasets[datasetIndex].hidden : null;
                
                if (meta.hidden) {
                    legendItem.classList.add('opacity-50');
                } else {
                    legendItem.classList.remove('opacity-50');
                }
                
                chart.update('none');
            });
            
            legendContainer.appendChild(legendItem);
        });
    }
    
    // Initialize - show first material if available
    if (orderMaterialsData.length > 0) {
        setTimeout(() => {
            switchToOrderMaterialTab(0);
        }, 100);
    }
});
</script>