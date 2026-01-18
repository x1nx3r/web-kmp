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

{{-- Fullscreen Chart Modal --}}
<div id="chart-fullscreen-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/80 transition-opacity" onclick="closeChartFullscreen()"></div>
    
    {{-- Modal Content --}}
    <div class="fixed inset-4 sm:inset-8 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center space-x-3">
                <div id="modal-chart-icon" class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div>
                    <h3 id="modal-chart-title" class="text-lg font-semibold text-gray-900">Chart Title</h3>
                    <p id="modal-chart-subtitle" class="text-sm text-gray-500">Material Name</p>
                </div>
            </div>
            <button onclick="closeChartFullscreen()" 
                    class="p-2 rounded-lg hover:bg-gray-200 transition-colors text-gray-500 hover:text-gray-700"
                    title="Close fullscreen">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        {{-- Chart Container --}}
        <div class="flex-1 p-6 overflow-hidden">
            <div class="h-full w-full relative bg-gray-50 rounded-lg p-4">
                <canvas id="fullscreen-chart-canvas" class="w-full h-full"></canvas>
            </div>
        </div>
        
        {{-- Legend Container --}}
        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50">
            <div id="fullscreen-chart-legend" class="flex flex-wrap items-center justify-center gap-4"></div>
        </div>
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

    // Custom plugin to draw order date vertical line
    const orderDateLinePlugin = {
        id: 'orderDateLine',
        afterDraw: (chart, args, options) => {
            if (options.orderDateIndex === undefined || options.orderDateIndex < 0) return;

            const { ctx, chartArea: { left, right, top, bottom }, scales: { x } } = chart;
            const xPos = x.getPixelForValue(options.orderDateIndex);

            if (xPos < left || xPos > right) return;

            // Draw dashed vertical line
            ctx.save();
            ctx.beginPath();
            ctx.setLineDash([5, 5]);
            ctx.strokeStyle = 'rgba(99, 102, 241, 0.7)';
            ctx.lineWidth = 2;
            ctx.moveTo(xPos, top);
            ctx.lineTo(xPos, bottom);
            ctx.stroke();

            // Draw label
            ctx.setLineDash([]);
            ctx.fillStyle = 'rgba(99, 102, 241, 0.9)';
            ctx.font = 'bold 10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Order', xPos, top - 5);

            ctx.restore();
        }
    };

    // Register the plugin globally
    Chart.register(orderDateLinePlugin);

    // Global date constants
    const today = new Date();
    const todayString = today.toISOString().split('T')[0];
    const todayFormatted = today.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });

    // Order date (when the order was created)
    const orderDateString = @json($order->tanggal_order->format('Y-m-d'));
    const orderDate = new Date(orderDateString);
    const orderDateFormatted = orderDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });

    // Generate 30-day date frame (from 30 days ago to today)
    function generate30DayFrame() {
        const dates = [];
        for (let i = 29; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            dates.push({
                dateString: date.toISOString().split('T')[0],
                formatted: date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
            });
        }
        return dates;
    }

    const thirtyDayFrame = generate30DayFrame();
    const thirtyDayLabels = thirtyDayFrame.map(d => d.formatted);

    // Find the index of order date in the 30-day frame
    const orderDateIndex = thirtyDayFrame.findIndex(d => d.dateString === orderDateString);

    // Order chart system state
    let activeOrderMaterialIndex = 0;
    let orderMaterialsData = @json($chartsData);
    let orderMaterialCharts = {};

    console.log('📊 Order materials data:', orderMaterialsData);
    console.log('📅 Order date:', orderDateString, 'Index:', orderDateIndex);

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
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2 text-xs">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <span class="text-gray-600">${materialData.nama}</span>
                            </div>
                            <button onclick="openChartFullscreen('client', ${index}, '${materialData.nama}')" 
                                    class="p-1.5 rounded-lg hover:bg-gray-200 transition-colors text-gray-500 hover:text-gray-700" 
                                    title="View fullscreen">
                                <i class="fas fa-expand text-sm"></i>
                            </button>
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
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2 text-xs">
                                <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                <span class="text-gray-600">${materialData.supplier_options ? materialData.supplier_options.length : 0} suppliers</span>
                            </div>
                            <button onclick="openChartFullscreen('supplier', ${index}, '${materialData.nama}')" 
                                    class="p-1.5 rounded-lg hover:bg-gray-200 transition-colors text-gray-500 hover:text-gray-700" 
                                    title="View fullscreen">
                                <i class="fas fa-expand text-sm"></i>
                            </button>
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

        // Create price map for 30-day frame lookup
        const priceMap = new Map();
        priceHistory.forEach(point => {
            priceMap.set(point.formatted_tanggal, point);
        });

        // Find the first known price for backward extrapolation
        // Use order_price as fallback for the entire line if no history
        let firstKnownPrice = materialData.order_price ? parseFloat(materialData.order_price) : null;
        for (const day of thirtyDayFrame) {
            if (priceMap.has(day.formatted)) {
                firstKnownPrice = priceMap.get(day.formatted).harga;
                break;
            }
        }

        // Map data to 30-day frame with forward-fill and backward extrapolation
        let lastKnownPrice = null;
        let lastKnownPoint = null;
        const chartData = thirtyDayFrame.map(day => {
            if (priceMap.has(day.formatted)) {
                lastKnownPoint = priceMap.get(day.formatted);
                lastKnownPrice = lastKnownPoint.harga;
                return lastKnownPoint;
            } else if (lastKnownPrice !== null) {
                // Forward fill
                return { harga: lastKnownPrice, formatted_tanggal: day.formatted, interpolated: true };
            } else if (firstKnownPrice !== null) {
                // Backward extrapolation - use first known price
                return { harga: firstKnownPrice, formatted_tanggal: day.formatted, interpolated: true };
            }
            return { harga: null, formatted_tanggal: day.formatted };
        });

        const data = {
            labels: thirtyDayLabels,
            datasets: [{
                label: `${materialData.nama} Client Price`,
                data: chartData.map(point => point.harga),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: false,
                tension: 0.4,
                pointRadius: chartData.map(point => {
                    if (point.is_order_price) return 8;
                    if (point.interpolated || point.harga === null) return 0;
                    return 4;
                }),
                pointBackgroundColor: chartData.map(point => {
                    if (point.is_order_price) return 'rgb(34, 197, 94)';
                    return 'rgb(59, 130, 246)';
                }),
                spanGaps: true,
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
                    orderDateLine: {
                        orderDateIndex: orderDateIndex
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
                                return label === todayFormatted ? label + ' (Today)' : label;
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
                    is_current_price: true  // This is the LIVE current price
                });
            }

            priceHistory.sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));

            // Create price map for 30-day frame lookup
            const priceMap = new Map();
            priceHistory.forEach(point => {
                priceMap.set(point.formatted_tanggal, point);
            });

            // Find the first known price for backward extrapolation
            // If we have current_price, use it as fallback for the entire line
            let firstKnownPrice = supplier.current_price ? parseFloat(supplier.current_price) : null;
            for (const day of thirtyDayFrame) {
                if (priceMap.has(day.formatted)) {
                    firstKnownPrice = priceMap.get(day.formatted).harga;
                    break;
                }
            }

            // Map data to 30-day frame with forward-fill and backward extrapolation
            let lastKnownPrice = null;
            let lastKnownPoint = null;
            const chartData = thirtyDayFrame.map(day => {
                if (priceMap.has(day.formatted)) {
                    lastKnownPoint = priceMap.get(day.formatted);
                    lastKnownPrice = lastKnownPoint.harga;
                    return lastKnownPoint;
                } else if (lastKnownPrice !== null) {
                    // Forward fill
                    return { harga: lastKnownPrice, formatted_tanggal: day.formatted, interpolated: true };
                } else if (firstKnownPrice !== null) {
                    // Backward extrapolation - use first known price (or current_price as fallback)
                    return { harga: firstKnownPrice, formatted_tanggal: day.formatted, interpolated: true };
                }
                return { harga: null, formatted_tanggal: day.formatted };
            });

            return {
                label: supplier.supplier_name +
                       (supplier.pic_name ? ` (PIC: ${supplier.pic_name})` : '') +
                       (supplier.is_selected ? ' ✓ Selected' : ''),
                data: chartData.map(point => point.harga),
                borderColor: color.border,
                backgroundColor: color.bg,
                borderWidth: supplier.is_selected ? 4 : 2,
                borderDash: supplier.is_selected ? [] : [5, 5],
                fill: false,
                tension: 0.4,
                pointRadius: chartData.map(point => {
                    if (point.is_current_price) return supplier.is_selected ? 8 : 6;
                    if (point.interpolated || point.harga === null) return 0;
                    return supplier.is_selected ? 6 : 4;
                }),
                pointBackgroundColor: chartData.map(point => {
                    if (point.is_current_price) return 'rgb(34, 197, 94)';
                    return color.border;
                }),
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                chartData: chartData,
                spanGaps: true
            };
        });

        const chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: thirtyDayLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    orderDateLine: {
                        orderDateIndex: orderDateIndex
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
                                const dataset = datasets[context.datasetIndex];
                                const point = dataset.chartData[context.dataIndex];

                                let suffix = '';
                                if (point?.is_current_price) suffix = ' (Current)';

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
                                return label === todayFormatted ? label + ' (Today)' : label;
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

    // ========================================
    // FULLSCREEN MODAL FUNCTIONS
    // ========================================
    
    let fullscreenChart = null;
    
    // Function to open chart in fullscreen modal
    window.openChartFullscreen = function(chartType, materialIndex, materialName) {
        console.log('🔲 Opening fullscreen:', chartType, materialIndex, materialName);
        
        const modal = document.getElementById('chart-fullscreen-modal');
        const titleEl = document.getElementById('modal-chart-title');
        const subtitleEl = document.getElementById('modal-chart-subtitle');
        const iconEl = document.getElementById('modal-chart-icon');
        const canvas = document.getElementById('fullscreen-chart-canvas');
        const legendContainer = document.getElementById('fullscreen-chart-legend');
        
        if (!modal || !canvas) return;
        
        // Destroy existing fullscreen chart
        if (fullscreenChart) {
            fullscreenChart.destroy();
            fullscreenChart = null;
        }
        
        // Clear legend
        legendContainer.innerHTML = '';
        
        // Set titles and icon based on chart type
        if (chartType === 'client') {
            titleEl.textContent = 'Client Price History';
            subtitleEl.textContent = materialName;
            iconEl.className = 'w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center';
        } else {
            titleEl.textContent = 'Supplier Options';
            subtitleEl.textContent = materialName;
            iconEl.className = 'w-10 h-10 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl flex items-center justify-center';
        }
        
        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Get material data
        const materialData = orderMaterialsData[materialIndex];
        if (!materialData) return;
        
        // Calculate y-axis config (same logic as createOrderMaterialCharts)
        const allPrices = [];
        if (chartType === 'client') {
            if (materialData.client_price_history) {
                materialData.client_price_history.forEach(p => p.harga && allPrices.push(p.harga));
            }
            if (materialData.order_price > 0) allPrices.push(parseFloat(materialData.order_price));
        } else {
            if (materialData.supplier_options) {
                materialData.supplier_options.forEach(s => {
                    if (s.price_history) s.price_history.forEach(p => p.harga && allPrices.push(p.harga));
                    if (s.current_price) allPrices.push(parseFloat(s.current_price));
                });
            }
        }
        
        let yAxisMin = 0, yAxisMax = 100000;
        if (allPrices.length > 0) {
            const minPrice = Math.min(...allPrices);
            const maxPrice = Math.max(...allPrices);
            const padding = (maxPrice - minPrice) * 0.1 || maxPrice * 0.1;
            yAxisMin = Math.max(0, Math.floor((minPrice - padding) / 100) * 100);
            yAxisMax = Math.ceil((maxPrice + padding) / 100) * 100;
        }
        
        // Wait for modal to be visible, then create chart
        setTimeout(() => {
            if (chartType === 'client') {
                fullscreenChart = createOrderClientChart(canvas, materialData, 'fullscreen', { yAxisMin, yAxisMax });
            } else {
                fullscreenChart = createOrderSupplierChart(canvas, materialData, 'fullscreen', { yAxisMin, yAxisMax });
            }
            
            // Create legend
            if (fullscreenChart) {
                createOrderExternalLegend(fullscreenChart, 'fullscreen-chart-legend');
            }
        }, 50);
    };
    
    // Function to close fullscreen modal
    window.closeChartFullscreen = function() {
        const modal = document.getElementById('chart-fullscreen-modal');
        
        // Destroy chart
        if (fullscreenChart) {
            fullscreenChart.destroy();
            fullscreenChart = null;
        }
        
        // Hide modal
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    };
    
    // ESC key handler
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('chart-fullscreen-modal');
            if (modal && !modal.classList.contains('hidden')) {
                closeChartFullscreen();
            }
        }
    });

    // Initialize - show first material if available
    if (orderMaterialsData.length > 0) {
        setTimeout(() => {
            switchToOrderMaterialTab(0);
        }, 100);
    }
});
</script>
