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
    <div class="fixed inset-0 transition-opacity" style="background-color: rgba(17, 24, 39, 0.85);" onclick="closeChartFullscreen()"></div>
    
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
            <div class="flex items-center space-x-4">
                {{-- Extrapolation Toggle (only for supplier charts) --}}
                <div id="extrapolation-toggle-container" class="hidden flex items-center space-x-2 bg-white rounded-lg px-3 py-1.5 border border-gray-200">
                    <button id="extrapolation-toggle" onclick="toggleFullscreenExtrapolation()" 
                            class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors bg-green-500"
                            role="switch" aria-checked="true">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform translate-x-4"></span>
                    </button>
                    <span class="text-xs text-gray-500">Hanya tampilkan riwayat harga dengan histori tercatat dari supplier</span>
                </div>
                <button onclick="closeChartFullscreen()" 
                        class="p-2 rounded-lg hover:bg-gray-200 transition-colors text-gray-500 hover:text-gray-700"
                        title="Close fullscreen">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
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
    let inlineHistoryOnlyState = {};  // Tracks toggle per material (default true)

    console.log('📊 Order materials data:', orderMaterialsData);
    console.log('📅 Order date:', orderDateString, 'Index:', orderDateIndex);

    // Toggle inline chart extrapolation and refresh
    window.toggleInlineExtrapolation = function(materialIndex) {
        // Toggle state (default is true = history only)
        const currentState = inlineHistoryOnlyState[materialIndex] !== undefined ? inlineHistoryOnlyState[materialIndex] : true;
        inlineHistoryOnlyState[materialIndex] = !currentState;
        
        const newState = inlineHistoryOnlyState[materialIndex];
        
        // Update toggle appearance
        const toggle = document.getElementById(`inline-extrapolation-toggle-${materialIndex}`);
        const knob = toggle.querySelector('span');
        toggle.setAttribute('aria-checked', newState);

        if (newState) {
            toggle.classList.remove('bg-gray-300');
            toggle.classList.add('bg-green-500');
            knob.classList.remove('translate-x-0.5');
            knob.classList.add('translate-x-3');
        } else {
            toggle.classList.remove('bg-green-500');
            toggle.classList.add('bg-gray-300');
            knob.classList.remove('translate-x-3');
            knob.classList.add('translate-x-0.5');
        }
        
        // Recreate the supplier chart with new state
        const materialData = orderMaterialsData[materialIndex];
        if (!materialData || !orderMaterialCharts[materialIndex]) return;
        
        // Destroy existing supplier chart
        if (orderMaterialCharts[materialIndex].supplierChart) {
            orderMaterialCharts[materialIndex].supplierChart.destroy();
        }
        
        // Recreate with same axis config
        const supplierCanvas = document.getElementById(`order-supplier-chart-${materialIndex}`);
        const axisConfig = orderMaterialCharts[materialIndex].axisConfig || { yAxisMin: 0, yAxisMax: 100000 };
        
        orderMaterialCharts[materialIndex].supplierChart = createOrderSupplierChart(
            supplierCanvas, 
            materialData, 
            materialIndex, 
            axisConfig,
            newState
        );
        
        // Refresh legend
        createOrderExternalLegend(orderMaterialCharts[materialIndex].supplierChart, `order-supplier-legend-${materialIndex}`);
    };

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
                            <!-- Extrapolation Toggle -->
                            <div class="flex items-center space-x-1 bg-white rounded-md px-2 py-1 border border-gray-200">
                                <span class="text-xs text-gray-500">Hanya Tampilkan Harga Dengan Histori Tercatat</span>
                                <button id="inline-extrapolation-toggle-${index}" 
                                        onclick="toggleInlineExtrapolation(${index})" 
                                        class="relative inline-flex h-4 w-7 items-center rounded-full transition-colors bg-green-500"
                                        role="switch" aria-checked="true" title="Tampilkan riwayat harga dengan histori tercatat dari supplier">
                                    <span class="inline-block h-3 w-3 transform rounded-full bg-white shadow transition-transform translate-x-3"></span>
                                </button>
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
            const priceRange = maxPrice - minPrice;
            const padding = priceRange === 0 ? maxPrice * 0.05 : priceRange * 0.1;

            let calculatedMin = minPrice - padding;
            let calculatedMax = maxPrice + padding;

            // Dynamic rounding based on range size (variance)
            let roundingFactor = 100;
            if (priceRange > 50000) roundingFactor = 10000;
            else if (priceRange > 10000) roundingFactor = 2000; // Tighter rounding for medium variance
            else if (priceRange > 2000) roundingFactor = 500;
            else if (priceRange > 500) roundingFactor = 100;
            else roundingFactor = 50; // Very tight rounding for small variance
            
            // Handle flat line case
            if (priceRange === 0) {
                 roundingFactor = maxPrice > 100000 ? 5000 : 1000;
            }

            yAxisMin = Math.floor(calculatedMin / roundingFactor) * roundingFactor;
            yAxisMax = Math.ceil(calculatedMax / roundingFactor) * roundingFactor;
            yAxisMin = Math.max(0, yAxisMin);
        }

        console.log(`📊 Synchronized Y-axis for ${materialData.nama}:`, { yAxisMin, yAxisMax });

        const axisConfig = { yAxisMin, yAxisMax };

        // Create charts (default showHistoryOnly = true)
        const showHistoryOnly = inlineHistoryOnlyState[index] !== undefined ? inlineHistoryOnlyState[index] : true;
        const clientChart = createOrderClientChart(clientCanvas, materialData, index, axisConfig);
        const supplierChart = createOrderSupplierChart(supplierCanvas, materialData, index, axisConfig, showHistoryOnly);

        // Store chart instances and axis config for re-rendering
        orderMaterialCharts[index] = {
            clientChart: clientChart,
            supplierChart: supplierChart,
            axisConfig: axisConfig
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
    function createOrderSupplierChart(canvas, materialData, materialIndex, axisConfig, showHistoryOnly = true) {
        const { yAxisMin, yAxisMax } = axisConfig;

        let suppliers = materialData.supplier_options || [];
        if (suppliers.length === 0) return null;

        const colors = [
            { border: 'rgb(16, 185, 129)', bg: 'rgba(16, 185, 129, 0.1)' },
            { border: 'rgb(249, 115, 22)', bg: 'rgba(249, 115, 22, 0.1)' },
            { border: 'rgb(139, 92, 246)', bg: 'rgba(139, 92, 246, 0.1)' },
            { border: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' },
            { border: 'rgb(14, 165, 233)', bg: 'rgba(14, 165, 233, 0.1)' },
            { border: 'rgb(245, 158, 11)', bg: 'rgba(245, 158, 11, 0.1)' },
            { border: 'rgb(99, 102, 241)', bg: 'rgba(99, 102, 241, 0.1)' },
            { border: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' }
        ];

        const datasets = suppliers.map((supplier, index) => {
            const hasHistory = (supplier.price_history || []).some(p => {
                const pDate = new Date(p.tanggal).toISOString().split('T')[0];
                return pDate < todayString;
            });
            
            const isHidden = showHistoryOnly && !hasHistory;

            const color = supplier.is_selected 
                ? { border: 'rgb(34, 197, 94)', bg: 'rgba(34, 197, 94, 0.1)' }
                : colors[index % colors.length];

            let priceHistory = supplier.price_history || [];

            if (supplier.current_price) {
                priceHistory = priceHistory.filter(point =>
                    !(point.formatted_tanggal === todayFormatted)
                );

                priceHistory = [...priceHistory, {
                    tanggal: todayString,
                    harga: parseFloat(supplier.current_price),
                    formatted_tanggal: todayFormatted,
                    is_current_price: true
                }];
            }
            
            priceHistory.sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));

            const priceMap = new Map();
            priceHistory.forEach(point => {
                priceMap.set(point.formatted_tanggal, point);
            });

            let firstKnownPrice = null;
            let hasPreHistory = false;
            let extrapolationSourceDate = null;
            
            if (thirtyDayFrame && thirtyDayFrame.length > 0) {
                const startDateStr = thirtyDayFrame[0].dateString;
                const preHistory = priceHistory.filter(p => p.tanggal < startDateStr);
                if (preHistory.length > 0) {
                    const sourcePoint = preHistory[preHistory.length - 1];
                    firstKnownPrice = sourcePoint.harga;
                    hasPreHistory = true;
                    extrapolationSourceDate = sourcePoint.formatted_tanggal;
                }
            }
            
            if (firstKnownPrice === null) {
                for (const day of thirtyDayFrame) {
                    if (priceMap.has(day.formatted)) {
                        const sourcePoint = priceMap.get(day.formatted);
                        firstKnownPrice = sourcePoint.harga;
                        extrapolationSourceDate = sourcePoint.formatted_tanggal;
                        break;
                    }
                }
            }
            
            if (firstKnownPrice === null && supplier.current_price) {
                firstKnownPrice = parseFloat(supplier.current_price);
                extrapolationSourceDate = 'Current Price';
            }

            let lastKnownPrice = null;
            let lastKnownPoint = null;
            const chartData = thirtyDayFrame.map((day, dayIndex) => {
                if (priceMap.has(day.formatted)) {
                    lastKnownPoint = priceMap.get(day.formatted);
                    lastKnownPrice = lastKnownPoint.harga;
                    return lastKnownPoint;
                } else if (lastKnownPrice !== null) {
                    return { harga: lastKnownPrice, formatted_tanggal: day.formatted, interpolated: true };
                } else if (firstKnownPrice !== null) {
                    if (hasPreHistory || !showHistoryOnly) {
                        return { 
                            harga: firstKnownPrice, 
                            formatted_tanggal: day.formatted, 
                            interpolated: true,
                            is_extrapolation_start: dayIndex === 0,
                            extrapolation_source: extrapolationSourceDate
                        };
                    }
                }
                return { harga: null, formatted_tanggal: day.formatted };
            });

            return {
                label: supplier.supplier_name +
                       (supplier.pic_name ? ` (PIC: ${supplier.pic_name})` : '') +
                       (supplier.is_selected ? ' ✓ Selected' : ''),
                data: chartData.map(point => point.harga),
                hidden: isHidden,
                borderColor: color.border,
                backgroundColor: color.bg,
                borderWidth: supplier.is_selected ? 4 : 2,
                borderDash: supplier.is_selected ? [] : [5, 5],
                fill: false,
                tension: 0.4,
                pointRadius: chartData.map(point => {
                    if (point.is_current_price) return supplier.is_selected ? 8 : 6;
                    if (point.is_extrapolation_start) return 4;
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
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgb(249, 115, 22)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const point = context.dataset.chartData[context.dataIndex];
                                let suffix = '';
                                if (point?.is_current_price) suffix = ' (Current)';
                                if (point?.is_extrapolation_start) suffix = ` (from ${point.extrapolation_source})`;
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
    let fullscreenState = null;  // Tracks toggle state for extrapolation
    
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
        
        // Store current state for toggle
        fullscreenState = {
            chartType: chartType,
            materialData: materialData,
            showHistoryOnly: true // Default (History Only)
        };
        
        // Show/hide toggle based on chart type
        const toggleContainer = document.getElementById('extrapolation-toggle-container');
        if (chartType === 'supplier') {
            toggleContainer.classList.remove('hidden');
            toggleContainer.classList.add('flex');
            updateToggleAppearance(fullscreenState.showHistoryOnly);
        } else {
            toggleContainer.classList.add('hidden');
            toggleContainer.classList.remove('flex');
        }
        
        // Wait for modal to be visible, then create chart with ALL historical data
        setTimeout(() => {
            renderFullscreenChart();
        }, 50);
    };
    
    // Render fullscreen chart based on current state
    function renderFullscreenChart() {
        if (!fullscreenState) return;
        
        const canvas = document.getElementById('fullscreen-chart-canvas');
        const legendContainer = document.getElementById('fullscreen-chart-legend');
        
        // Destroy existing chart
        if (fullscreenChart) {
            fullscreenChart.destroy();
            fullscreenChart = null;
        }
        legendContainer.innerHTML = '';
        
        const { chartType, materialData } = fullscreenState;
        
        if (chartType === 'client') {
            fullscreenChart = createFullscreenClientChart(canvas, materialData);
        } else {
            fullscreenChart = createFullscreenSupplierChart(canvas, materialData, fullscreenState.showHistoryOnly);
        }
        
        // Create legend
        if (fullscreenChart) {
            createOrderExternalLegend(fullscreenChart, 'fullscreen-chart-legend');
        }
    }
    
    // Toggle extrapolation and refresh chart
    window.toggleFullscreenExtrapolation = function() {
        if (!fullscreenState) return;
        
        fullscreenState.showHistoryOnly = !fullscreenState.showHistoryOnly;
        updateToggleAppearance(fullscreenState.showHistoryOnly);
        renderFullscreenChart();
    };
    
    // Update toggle button appearance
    function updateToggleAppearance(isOn) {
        const toggle = document.getElementById('extrapolation-toggle');
        const knob = toggle.querySelector('span');
        
        if (isOn) {
            toggle.classList.remove('bg-gray-300');
            toggle.classList.add('bg-green-500');
            knob.classList.remove('translate-x-0.5');
            knob.classList.add('translate-x-4');
            toggle.setAttribute('aria-checked', 'true');
        } else {
            toggle.classList.remove('bg-green-500');
            toggle.classList.add('bg-gray-300');
            knob.classList.remove('translate-x-4');
            knob.classList.add('translate-x-0.5');
            toggle.setAttribute('aria-checked', 'false');
        }
    }
    
    // Create fullscreen CLIENT chart with ALL historical data
    function createFullscreenClientChart(canvas, materialData) {
        const priceHistory = materialData.client_price_history || [];
        if (priceHistory.length === 0) return null;
        
        // Sort by date
        const sortedHistory = [...priceHistory].sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
        
        // Extract labels and data
        const labels = sortedHistory.map(p => {
            const date = new Date(p.tanggal);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: '2-digit' });
        });
        const data = sortedHistory.map(p => p.harga);
        
        // Calculate y-axis
        const minPrice = Math.min(...data);
        const maxPrice = Math.max(...data);
        const priceRange = maxPrice - minPrice;
        const padding = priceRange === 0 ? maxPrice * 0.05 : priceRange * 0.1;
        
        let calculatedMin = minPrice - padding;
        let calculatedMax = maxPrice + padding;
        
        // Dynamic rounding based on range size
        let roundingFactor = 100;
        
        if (priceRange > 50000) roundingFactor = 10000;
        else if (priceRange > 10000) roundingFactor = 2000;
        else if (priceRange > 2000) roundingFactor = 500;
        else if (priceRange > 500) roundingFactor = 100;
        else roundingFactor = 50;
        
        if (priceRange === 0) {
             roundingFactor = maxPrice > 100000 ? 5000 : 1000;
             calculatedMin = minPrice - (maxPrice * 0.05);
             calculatedMax = maxPrice + (maxPrice * 0.05);
        }
        
        const yAxisMin = Math.max(0, Math.floor(calculatedMin / roundingFactor) * roundingFactor);
        const yAxisMax = Math.ceil(calculatedMax / roundingFactor) * roundingFactor;
        
        // Find order date index for marker
        const orderIdx = sortedHistory.findIndex(p => {
            const pDate = new Date(p.tanggal).toISOString().split('T')[0];
            return pDate === orderDateString;
        });
        
        return new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: materialData.nama,
                    data: data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(59, 130, 246)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        callbacks: {
                            label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y)
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { color: 'rgb(107, 114, 128)', maxTicksLimit: 12 }
                    },
                    y: {
                        min: yAxisMin,
                        max: yAxisMax,
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: {
                            color: 'rgb(107, 114, 128)',
                            callback: v => 'Rp ' + (v / 1000) + 'k'
                        }
                    }
                }
            }
        });
    }
    
    // Create fullscreen SUPPLIER chart with ALL historical data
    function createFullscreenSupplierChart(canvas, materialData, showHistoryOnly = true) {
        let suppliers = materialData.supplier_options || [];
        if (suppliers.length === 0) return null;
        
        // Note: We no longer filter out suppliers. We control visibility via the 'hidden' attribute.
        
        // Collect all unique dates from all suppliers
        const allDates = new Set();
        suppliers.forEach(supplier => {
            (supplier.price_history || []).forEach(p => {
                allDates.add(new Date(p.tanggal).toISOString().split('T')[0]);
            });
        });
        
        // Add today
        allDates.add(todayString);
        
        // Sort dates
        const sortedDates = [...allDates].sort();
        const labels = sortedDates.map(d => {
            const date = new Date(d);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: '2-digit' });
        });
        
        // Calculate y-axis from all prices
        const allPrices = [];
        suppliers.forEach(s => {
            (s.price_history || []).forEach(p => p.harga && allPrices.push(p.harga));
            if (s.current_price) allPrices.push(parseFloat(s.current_price));
        });
        
        const minPrice = Math.min(...allPrices);
        const maxPrice = Math.max(...allPrices);
        const priceRange = maxPrice - minPrice;
        const padding = priceRange === 0 ? maxPrice * 0.05 : priceRange * 0.1;
        
        let calculatedMin = minPrice - padding;
        let calculatedMax = maxPrice + padding;
        
        // Dynamic rounding based on range size
        let roundingFactor = 100;
        
        if (priceRange > 50000) roundingFactor = 10000;
        else if (priceRange > 10000) roundingFactor = 2000;
        else if (priceRange > 2000) roundingFactor = 500;
        else if (priceRange > 500) roundingFactor = 100;
        else roundingFactor = 50;
        
        if (priceRange === 0) {
             roundingFactor = maxPrice > 100000 ? 5000 : 1000;
             calculatedMin = minPrice - (maxPrice * 0.05);
             calculatedMax = maxPrice + (maxPrice * 0.05);
        }
        
        const yAxisMin = Math.max(0, Math.floor(calculatedMin / roundingFactor) * roundingFactor);
        const yAxisMax = Math.ceil(calculatedMax / roundingFactor) * roundingFactor;
        
        // Find order date index
        const orderIdx = sortedDates.indexOf(orderDateString);
        
        // Create datasets
        const colors = [
            { border: 'rgb(16, 185, 129)', bg: 'rgba(16, 185, 129, 0.1)' },
            { border: 'rgb(249, 115, 22)', bg: 'rgba(249, 115, 22, 0.1)' },
            { border: 'rgb(139, 92, 246)', bg: 'rgba(139, 92, 246, 0.1)' },
            { border: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' },
            { border: 'rgb(14, 165, 233)', bg: 'rgba(14, 165, 233, 0.1)' },
            { border: 'rgb(245, 158, 11)', bg: 'rgba(245, 158, 11, 0.1)' },
            { border: 'rgb(99, 102, 241)', bg: 'rgba(99, 102, 241, 0.1)' },
            { border: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' }
        ];
        
        const datasets = suppliers.map((supplier, idx) => {
            // Strictly check for history BEFORE today
            const hasHistory = (supplier.price_history || []).some(p => {
                const pDate = new Date(p.tanggal).toISOString().split('T')[0];
                return pDate < todayString;
            });
            
            // Hide if toggle is ON (History Only) and supplier has no history
            const isHidden = showHistoryOnly && !hasHistory;

            const color = supplier.is_selected 
                ? { border: 'rgb(34, 197, 94)', bg: 'rgba(34, 197, 94, 0.1)' }
                : colors[idx % colors.length];
            
            // Build price map
            const priceMap = new Map();
            (supplier.price_history || []).forEach(p => {
                priceMap.set(new Date(p.tanggal).toISOString().split('T')[0], p.harga);
            });
            
            // Add current price for today
            if (supplier.current_price) {
                priceMap.set(todayString, parseFloat(supplier.current_price));
            }
            
            // Find first known price for backward extrapolation (use current_price as fallback)
            let firstKnownPrice = supplier.current_price ? parseFloat(supplier.current_price) : null;
            let extrapolationSourceDate = supplier.current_price ? 'Current Price' : null;
            
            for (const date of sortedDates) {
                if (priceMap.has(date)) {
                    firstKnownPrice = priceMap.get(date);
                    const d = new Date(date);
                    extrapolationSourceDate = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: '2-digit' });
                    break;
                }
            }
            
            // Map to sorted dates with forward-fill AND optional backward extrapolation
            let lastPrice = null;
            const fullData = sortedDates.map((date, idx) => {
                if (priceMap.has(date)) {
                    lastPrice = priceMap.get(date);
                    return { y: lastPrice };
                } else if (lastPrice !== null) {
                    return { y: lastPrice }; // forward-fill
                } else if (!showHistoryOnly && firstKnownPrice !== null) {
                    return { 
                        y: firstKnownPrice,
                        is_extrapolation_start: idx === 0,
                        extrapolation_source: extrapolationSourceDate
                    };
                }
                return null;
            });
            
            return {
                label: supplier.supplier_name + (supplier.is_selected ? ' ✓' : ''),
                data: fullData.map(d => d?.y ?? null),
                chartData: fullData, // Store metadata for callbacks
                hidden: isHidden, // Control visibility based on toggle
                borderColor: color.border,
                backgroundColor: color.bg,
                borderWidth: supplier.is_selected ? 3 : 2,
                tension: 0.3,
                pointRadius: fullData.map(point => {
                    if (!point) return 0;
                    if (point.is_extrapolation_start) return 4;
                    return 3;
                }),
                pointHoverRadius: 5,
                spanGaps: true
            };
        });
        
        return new Chart(canvas, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        callbacks: {
                            label: ctx => {
                                const point = ctx.dataset.chartData[ctx.dataIndex];
                                let suffix = '';
                                if (point?.is_extrapolation_start) suffix = ` (from ${point.extrapolation_source})`;
                                return ctx.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y) + suffix;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { color: 'rgb(107, 114, 128)', maxTicksLimit: 12 }
                    },
                    y: {
                        min: yAxisMin,
                        max: yAxisMax,
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: {
                            color: 'rgb(107, 114, 128)',
                            callback: v => 'Rp ' + (v / 1000) + 'k'
                        }
                    }
                }
            }
        });
    }
    
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
