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

    // Global date constants for chart system
    const today = new Date();
    const todayString = today.toISOString().split('T')[0]; // YYYY-MM-DD format
    const todayFormatted = today.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }); // "24 Okt" format

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

    // Material tab system state
    let activeMaterialIndex = 0;
    let materialsData = [];
    let materialCharts = {}; // Store chart instances per material

    // Chart state management
    let lastUpdateTime = 0;

    // Function to interpolate missing data points for smoother lines with backward extrapolation
    function interpolateData(priceHistory, allDates) {
        if (!priceHistory || priceHistory.length === 0) return [];

        const result = [];
        const historyMap = new Map();

        // Create a map of existing data points
        priceHistory.forEach(point => {
            historyMap.set(point.formatted_tanggal, point);
        });

        // Sort all dates chronologically
        const sortedDates = [...allDates].sort((a, b) => {
            if (a === todayFormatted) return 1; // Today always last
            if (b === todayFormatted) return -1;
            return new Date(a + ', 2025') - new Date(b + ', 2025');
        });

        // Find first and last actual data points for extrapolation bounds
        let firstActualPrice = null;
        let lastActualPrice = null;

        // Get first actual price
        for (const date of sortedDates) {
            if (historyMap.has(date)) {
                firstActualPrice = historyMap.get(date).harga;
                break;
            }
        }

        // Get last actual price
        for (let i = sortedDates.length - 1; i >= 0; i--) {
            const date = sortedDates[i];
            if (historyMap.has(date) && !historyMap.get(date).extrapolated) {
                lastActualPrice = historyMap.get(date).harga;
                break;
            }
        }

        sortedDates.forEach((date, index) => {
            if (historyMap.has(date)) {
                // We have actual data for this date
                const actualPoint = historyMap.get(date);
                result.push(actualPoint);
            } else {
                // Need to interpolate/extrapolate this date
                let interpolatedPrice = null;

                // Find surrounding actual data points
                let prevPrice = null;
                let nextPrice = null;

                // Look backwards for previous actual price
                for (let i = index - 1; i >= 0; i--) {
                    if (historyMap.has(sortedDates[i]) && !historyMap.get(sortedDates[i]).extrapolated) {
                        prevPrice = historyMap.get(sortedDates[i]).harga;
                        break;
                    }
                }

                // Look forwards for next actual price
                for (let i = index + 1; i < sortedDates.length; i++) {
                    if (historyMap.has(sortedDates[i]) && !historyMap.get(sortedDates[i]).extrapolated) {
                        nextPrice = historyMap.get(sortedDates[i]).harga;
                        break;
                    }
                }

                // Determine interpolation strategy
                if (prevPrice !== null && nextPrice !== null) {
                    // Interpolate between two known points
                    interpolatedPrice = prevPrice; // Use previous price for simplicity
                } else if (prevPrice !== null) {
                    // Forward extrapolation (use last known price)
                    interpolatedPrice = prevPrice;
                } else if (nextPrice !== null) {
                    // Backward extrapolation (use first known price)
                    interpolatedPrice = nextPrice;
                } else if (firstActualPrice !== null) {
                    // Fallback to first actual price if available
                    interpolatedPrice = firstActualPrice;
                }

                if (interpolatedPrice !== null) {
                    result.push({
                        tanggal: date,
                        formatted_tanggal: date,
                        harga: interpolatedPrice,
                        interpolated: true
                    });
                }
            }
        });

        return result;
    }

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

        // Clear any existing content in the container
        if (contentContainer) {
            contentContainer.querySelectorAll('.material-content').forEach(content => {
                content.remove();
            });
        }

        // Create tabs for each material
        materials.forEach((material, index) => {
            const tab = document.createElement('div');
            // Always set first tab as active when creating tabs
            const isActive = index === 0;
            tab.className = `material-tab flex-shrink-0 px-4 py-2 text-sm font-medium rounded-md transition-all cursor-pointer ${
                isActive ?
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

        console.log('📊 Material tabs created, first tab marked as active');
    }

    // Function to switch between material tabs
    function switchToMaterialTab(index) {
        console.log('🔄 Switching to material tab:', index, 'of', materialsData.length);

        // Validate index
        if (!materialsData || index < 0 || index >= materialsData.length) {
            console.error('Invalid material index:', index);
            return;
        }

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

        console.log('✅ Switched to material tab:', materialsData[index]?.nama);
    }

    // Function to show content for specific material
    function showMaterialContent(index) {
        console.log('📋 Showing material content for index:', index);
        const contentContainer = document.getElementById('tab-content-container');
        const emptyState = document.getElementById('charts-empty-state');

        if (!contentContainer) {
            console.error('Content container not found');
            return;
        }

        if (!materialsData[index]) {
            console.error('Material data not found for index:', index);
            return;
        }

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
            console.log('📋 Creating new material content for:', materialsData[index].nama);
            // Create new material content
            materialContent = createMaterialContent(materialsData[index], index);
            contentContainer.appendChild(materialContent);
        } else {
            console.log('📋 Showing existing material content for:', materialsData[index].nama);
            // Show existing content
            materialContent.classList.remove('hidden');
        }

        // Create or update charts for this material
        setTimeout(() => {
            createMaterialCharts(materialsData[index], index);
        }, 10); // Small delay to ensure DOM is ready
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
                    <div class="h-80 w-full relative">
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
                    <div class="h-80 w-full relative">
                        <canvas id="supplier-chart-${index}" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>

            <!-- Separate scrollable legends below charts to avoid squishing -->
            <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-transparent">
                    <h5 class="text-xs text-gray-500 mb-2">Client Legend</h5>
                    <div class="overflow-x-auto">
                        <div id="client-legend-${index}" class="inline-flex items-center space-x-2 px-2 py-1 whitespace-nowrap"></div>
                    </div>
                </div>
                <div class="bg-transparent">
                    <h5 class="text-xs text-gray-500 mb-2">Supplier Legend</h5>
                    <div class="overflow-x-auto">
                        <div id="supplier-legend-${index}" class="inline-flex items-center space-x-2 px-2 py-1 whitespace-nowrap"></div>
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

        // ===== SYNCHRONIZED AXIS CALCULATION =====
        // Collect ALL prices from both client and supplier data for this material
        const allPrices = [];

        // Collect client prices
        if (materialData.klien_price_history && materialData.klien_price_history.length > 0) {
            materialData.klien_price_history.forEach(point => {
                if (point.harga) allPrices.push(point.harga);
            });
        }

        // Include custom prices if set
        if (materialData.is_custom_price && materialData.custom_price && materialData.custom_price > 0) {
            allPrices.push(parseFloat(materialData.custom_price));
        }

        // Collect supplier prices
        if (materialData.supplier_options && materialData.supplier_options.length > 0) {
            materialData.supplier_options.forEach(supplier => {
                if (supplier.price_history && supplier.price_history.length > 0) {
                    supplier.price_history.forEach(point => {
                        if (point.harga) allPrices.push(point.harga);
                    });
                }
            });
        }

        // Calculate synchronized y-axis range
        let yAxisMin = 0;
        let yAxisMax = 100000; // Default fallback

        if (allPrices.length > 0) {
            const minPrice = Math.min(...allPrices);
            const maxPrice = Math.max(...allPrices);

            // Add 10% padding for visual breathing room
            const padding = (maxPrice - minPrice) * 0.1;

            // Calculate range with padding
            let calculatedMin = minPrice - padding;
            let calculatedMax = maxPrice + padding;

            // Round to nice numbers based on price magnitude
            const roundingFactor = maxPrice < 100000 ? 1000 : 10000;

            yAxisMin = Math.floor(calculatedMin / roundingFactor) * roundingFactor;
            yAxisMax = Math.ceil(calculatedMax / roundingFactor) * roundingFactor;

            // Ensure yMin is not negative
            yAxisMin = Math.max(0, yAxisMin);
        }

        console.log(`📊 Synchronized Y-axis for ${materialData.nama}:`, { yAxisMin, yAxisMax, totalPrices: allPrices.length });

        // Function to extrapolate data to today
        function extrapolateToToday(priceHistory, datasetName) {
            if (!priceHistory || priceHistory.length === 0) return [];

            // Sort by date to ensure proper order
            const sortedHistory = [...priceHistory].sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
            const lastEntry = sortedHistory[sortedHistory.length - 1];
            const lastDate = new Date(lastEntry.tanggal);

            // If last entry is not today, extrapolate
            if (lastDate.toISOString().split('T')[0] !== todayString) {
                const extrapolatedEntry = {
                    tanggal: todayString,
                    harga: lastEntry.harga, // Keep same price for today
                    formatted_tanggal: todayFormatted,
                    extrapolated: true
                };
                sortedHistory.push(extrapolatedEntry);
            }

            return sortedHistory;
        }

        // ===== END SYNCHRONIZED AXIS CALCULATION =====

        // Create client chart with synchronized axes
        const clientChart = createClientChart(clientCanvas, materialData, index, { yAxisMin, yAxisMax, todayFormatted, extrapolateToToday });

        // Create supplier chart with synchronized axes
        const supplierChart = createSupplierChart(supplierCanvas, materialData, index, { yAxisMin, yAxisMax, todayFormatted, extrapolateToToday });

        // Store chart instances
        materialCharts[index] = {
            clientChart: clientChart,
            supplierChart: supplierChart
        };
    }

    // Function to create client price chart
    function createClientChart(canvas, materialData, materialIndex, axisConfig) {
        const { yAxisMin, yAxisMax, todayFormatted, extrapolateToToday } = axisConfig;

        // Get and extrapolate client price history
        let priceHistory = extrapolateToToday(materialData.klien_price_history || [], materialData.nama);

        // Add custom price as today's data point if available
        if (materialData.is_custom_price && materialData.custom_price && materialData.custom_price > 0) {
            // Remove any existing today point to avoid duplicates
            priceHistory = priceHistory.filter(point =>
                !(point.formatted_tanggal === todayFormatted || point.is_custom)
            );

            // Add custom price as today's point
            const customPoint = {
                tanggal: new Date().toISOString().split('T')[0],
                harga: parseFloat(materialData.custom_price),
                formatted_tanggal: todayFormatted,
                is_custom: true
            };

            priceHistory.push(customPoint);
        }

        // Sort by date to maintain chronological order with today at the end
        priceHistory.sort((a, b) => {
            const dateA = new Date(a.tanggal);
            const dateB = new Date(b.tanggal);
            return dateA - dateB;
        });

        // Create price map for 30-day frame lookup
        const priceMap = new Map();
        priceHistory.forEach(point => {
            priceMap.set(point.formatted_tanggal, point);
        });

        // Find the first known price for backward extrapolation
        // Use custom_price or klien_price as fallback for the entire line if no history
        let firstKnownPrice = materialData.custom_price ? parseFloat(materialData.custom_price) :
                              (materialData.klien_price ? parseFloat(materialData.klien_price) : null);
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
                    if (point.is_custom) return 8;
                    if (point.interpolated || point.harga === null) return 0;
                    if (point.extrapolated) return 6;
                    return 4;
                }),
                pointBackgroundColor: chartData.map(point => {
                    if (point.is_custom) return 'rgb(34, 197, 94)'; // Green for custom
                    if (point.extrapolated) return 'rgb(239, 68, 68)'; // Red for extrapolated
                    return 'rgb(59, 130, 246)'; // Blue for historical
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
                    legend: {
                        display: false // Hide built-in legend
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
                                if (point?.is_custom) suffix = ' (Custom Price)';
                                else if (point?.extrapolated) suffix = ' (Today - Extrapolated)';
                                else if (context.label === todayFormatted) suffix = ' (Today)';

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
                            autoSkip: true,
                            maxRotation: 0,
                            minRotation: 0,
                            align: 'center',
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

        // Create external legend for client chart
        createExternalLegend(chart, `client-legend-${materialIndex}`);

        return chart;
    }

    // Function to create supplier price chart
    function createSupplierChart(canvas, materialData, materialIndex, axisConfig) {
        const { yAxisMin, yAxisMax, todayFormatted, extrapolateToToday } = axisConfig;
        const suppliers = materialData.supplier_options || [];

        const datasets = suppliers.map((supplier, index) => {
            const colors = [
                { border: 'rgb(16, 185, 129)', bg: 'rgba(16, 185, 129, 0.1)' }, // Green
                { border: 'rgb(249, 115, 22)', bg: 'rgba(249, 115, 22, 0.1)' }, // Orange
                { border: 'rgb(139, 92, 246)', bg: 'rgba(139, 92, 246, 0.1)' }, // Purple
                { border: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' }, // Pink
                { border: 'rgb(14, 165, 233)', bg: 'rgba(14, 165, 233, 0.1)' }  // Sky Blue
            ];
            const color = supplier.is_best ? colors[0] : colors[index % colors.length];

            // Get and sort price history
            let priceHistory = supplier.price_history || [];
            priceHistory.sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));

            // Create price map for 30-day frame lookup
            const priceMap = new Map();
            priceHistory.forEach(point => {
                priceMap.set(point.formatted_tanggal, point);
            });

            // Find the first known price for backward extrapolation
            // Use supplier price as fallback for the entire line if no history
            let firstKnownPrice = supplier.price ? parseFloat(supplier.price) : null;
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

            return {
                label: supplier.supplier_name +
                       (supplier.pic_name ? ` (PIC: ${supplier.pic_name})` : '') +
                       (supplier.is_best ? ' (Best)' : ''),
                data: chartData.map(point => point.harga),
                borderColor: color.border,
                backgroundColor: color.bg,
                borderWidth: supplier.is_best ? 3 : 2,
                borderDash: supplier.is_best ? [] : [5, 5],
                fill: false,
                tension: 0.4,
                pointRadius: chartData.map(point => {
                    if (point.interpolated || point.harga === null) return 0;
                    if (supplier.is_best) return 5;
                    return 4;
                }),
                pointBackgroundColor: chartData.map(point => {
                    return color.border;
                }),
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                spanGaps: true,
                chartData: chartData
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
                    legend: {
                        display: false // Hide built-in legend
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
                                if (point?.interpolated) suffix = ' (Extrapolated)';

                                return context.dataset.label + ': Rp ' +
                                       new Intl.NumberFormat('id-ID').format(context.parsed.y) + suffix;
                            },
                            afterLabel: function(context) {
                                if (context.dataset.label.includes('(Best)')) {
                                    return 'Best Supplier';
                                }
                                return '';
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

        // Create external legend for supplier chart
        createExternalLegend(chart, `supplier-legend-${materialIndex}`);

        return chart;
    }

    // Function to create external clickable legend
    function createExternalLegend(chart, legendContainerId) {
        const legendContainer = document.getElementById(legendContainerId);
        if (!legendContainer) return;

        // Clear existing legend
        legendContainer.innerHTML = '';

        chart.data.datasets.forEach((dataset, datasetIndex) => {
            const legendItem = document.createElement('div');
            legendItem.className = 'flex items-center space-x-2 px-3 py-2 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-all';

            // Determine if dataset is visible
            const isVisible = chart.isDatasetVisible(datasetIndex);
            if (!isVisible) {
                legendItem.classList.add('opacity-50');
            }

            // Create color indicator
            const colorBox = document.createElement('div');
            colorBox.className = 'w-3 h-3 rounded-sm flex-shrink-0';
            colorBox.style.backgroundColor = dataset.borderColor;

            // Create label
            const label = document.createElement('span');
            label.className = 'text-xs font-medium text-gray-700 truncate';
            label.textContent = dataset.label;

            legendItem.appendChild(colorBox);
            legendItem.appendChild(label);

            // Add click handler for show/hide
            legendItem.addEventListener('click', () => {
                const meta = chart.getDatasetMeta(datasetIndex);
                meta.hidden = meta.hidden === null ? !chart.data.datasets[datasetIndex].hidden : null;

                // Update legend item appearance
                if (meta.hidden) {
                    legendItem.classList.add('opacity-50');
                } else {
                    legendItem.classList.remove('opacity-50');
                }

                chart.update('none'); // Update without animation for better performance
            });

            legendContainer.appendChild(legendItem);
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
            activeMaterialIndex = 0; // Reset active index
            createMaterialTabs([]);
            return;
        }

        // Store materials data
        materialsData = analysisData;

        // Reset to first material for new data
        activeMaterialIndex = 0;

        // Create tabs
        createMaterialTabs(analysisData);

        // Show first material by default with a small delay to ensure DOM is ready
        if (analysisData.length > 0) {
            setTimeout(() => {
                switchToMaterialTab(0);
            }, 10);
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
