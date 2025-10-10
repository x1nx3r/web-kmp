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
    let klienChart = null;
    let supplierChart = null;

    // Chart state management for race condition prevention
    let lastUpdateTime = 0;
    let lastDataSource = null;

    // Function to create or update charts
    function updateCharts(dynamicData = null) {
        // Prevent rapid successive updates
        const now = Date.now();
        if (now - lastUpdateTime < 50) {
            console.log('Skipping rapid update');
            return;
        }
        lastUpdateTime = now;

        // Use dynamic data from event if available, otherwise use server-side data
        const analysisData = dynamicData || @json($marginAnalysis);
        
        // Prioritize event data over server data if we recently received event data
        const dataSource = dynamicData ? 'from event' : 'from server';
        
        // If we just processed event data and now getting empty server data, skip it
        if (lastDataSource === 'from event' && dataSource === 'from server' && (!analysisData || analysisData.length === 0)) {
            console.log('Skipping empty server data after event data');
            return;
        }
        
        lastDataSource = dataSource;

        console.log('updateCharts() called at:', new Date().toISOString());
        console.log('Data source:', dataSource);
        console.log('Analysis data:', analysisData);

        try {
            // Get placeholders
            const klienPlaceholder = document.getElementById('klien-placeholder');
            const supplierPlaceholder = document.getElementById('supplier-placeholder');
            const klienCtx = document.getElementById('klienPriceChart');
            const supplierCtx = document.getElementById('supplierPriceChart');

            // Check if DOM elements exist (might be removed during navigation)
            if (!klienCtx || !supplierCtx) {
                console.log('Chart canvas elements not found - likely page changed');
                return;
            }

            // Use real data
            const dataToUse = analysisData;

            // Debug: log detailed chart data
            if (dataToUse && dataToUse.length > 0) {
                console.log('Number of materials:', dataToUse.length);
                console.log('First material klien_price_history:', dataToUse[0].klien_price_history);
                console.log('First material supplier_price_history:', dataToUse[0].supplier_price_history);
            } else {
                console.log('No data available - showing placeholders');
            }

            if (!dataToUse || dataToUse.length === 0) {
                // Show placeholders, hide canvas
                if (klienPlaceholder && klienCtx) {
                    klienPlaceholder.style.display = 'flex';
                    klienCtx.style.display = 'none';
                }
                if (supplierPlaceholder && supplierCtx) {
                    supplierPlaceholder.style.display = 'flex';
                    supplierCtx.style.display = 'none';
                }

                // Destroy existing charts
                if (klienChart) {
                    klienChart.destroy();
                    klienChart = null;
                }
                if (supplierChart) {
                    supplierChart.destroy();
                    supplierChart = null;
                }
                return;
            }

            // Hide placeholders, show canvas
            if (klienPlaceholder && klienCtx) {
                klienPlaceholder.style.display = 'none';
                klienCtx.style.display = 'block';
            }
            if (supplierPlaceholder && supplierCtx) {
                supplierPlaceholder.style.display = 'none';
                supplierCtx.style.display = 'block';
            }

            // Destroy existing charts before recreating
            if (klienChart) {
                klienChart.destroy();
            }
            if (supplierChart) {
                supplierChart.destroy();
            }

        // Chart Enhancement: Prepare synchronized data with today's extrapolation
        const today = new Date();
        const todayString = today.toISOString().split('T')[0]; // YYYY-MM-DD format
        const todayFormatted = today.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        
        // Function to extrapolate to today's date
        function extrapolateToToday(priceHistory, materialName, isSupplier = false) {
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

        // ===== SYNCHRONIZED Y-AXIS CALCULATION =====
        // Collect ALL prices from both client and supplier data BEFORE creating charts
        const allPrices = [];
        
        // Collect klien prices
        dataToUse.forEach(item => {
            if (item.klien_price_history && item.klien_price_history.length > 0) {
                item.klien_price_history.forEach(point => {
                    if (point.harga) allPrices.push(point.harga);
                });
            }
            // Include custom prices if set
            if (item.is_custom_price && item.custom_price && item.custom_price > 0) {
                allPrices.push(parseFloat(item.custom_price));
            }
        });
        
        // Collect supplier prices
        dataToUse.forEach(item => {
            // Check supplier_options for multiple suppliers
            if (item.supplier_options && item.supplier_options.length > 0) {
                item.supplier_options.forEach(supplier => {
                    if (supplier.price_history && supplier.price_history.length > 0) {
                        supplier.price_history.forEach(point => {
                            if (point.harga) allPrices.push(point.harga);
                        });
                    }
                });
            }
            // Fallback to single supplier price history
            if (item.supplier_price_history && item.supplier_price_history.length > 0) {
                item.supplier_price_history.forEach(point => {
                    if (point.harga) allPrices.push(point.harga);
                });
            }
        });
        
        // Calculate synchronized y-axis range
        let yAxisMin = 0;
        let yAxisMax = 100000; // Default fallback
        
        if (allPrices.length > 0) {
            const minPrice = Math.min(...allPrices);
            const maxPrice = Math.max(...allPrices);
            
            // Add 15% padding for visual breathing room
            const padding = (maxPrice - minPrice) * 0.15;
            
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
        
        console.log('Final Y-axis range:', { yAxisMin, yAxisMax, totalPrices: allPrices.length });
        // ===== END SYNCHRONIZED Y-AXIS CALCULATION =====

        // Client Price Chart
        if (klienCtx) {
            // Define a color palette for multiple materials
            const clientColorPalette = [
                { border: 'rgb(59, 130, 246)', bg: 'rgba(59, 130, 246, 0.1)' },   // Blue
                { border: 'rgb(139, 92, 246)', bg: 'rgba(139, 92, 246, 0.1)' },   // Purple
                { border: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' },   // Pink
                { border: 'rgb(251, 146, 60)', bg: 'rgba(251, 146, 60, 0.1)' },   // Orange
                { border: 'rgb(14, 165, 233)', bg: 'rgba(14, 165, 233, 0.1)' },   // Sky
                { border: 'rgb(168, 85, 247)', bg: 'rgba(168, 85, 247, 0.1)' },   // Violet
                { border: 'rgb(234, 88, 12)', bg: 'rgba(234, 88, 12, 0.1)' },     // Red-Orange
                { border: 'rgb(20, 184, 166)', bg: 'rgba(20, 184, 166, 0.1)' },   // Teal
            ];

            const klienData = dataToUse.map((item, index) => {
                let priceHistory = extrapolateToToday(item.klien_price_history || [], item.nama, false);
                
                // Add custom price as today's data point if available
                if (item.is_custom_price && item.custom_price && item.custom_price > 0) {
                    // Remove any existing today point to avoid duplicates
                    priceHistory = priceHistory.filter(point => 
                        !(point.formatted_tanggal === todayFormatted || point.is_custom)
                    );
                    
                    // Add custom price as today's point
                    const customPoint = {
                        tanggal: todayString,
                        harga: parseFloat(item.custom_price),
                        formatted_tanggal: todayFormatted,
                        is_custom: true
                    };
                    
                    priceHistory.push(customPoint);
                }
                
                // Sort by date to maintain chronological order
                priceHistory.sort((a, b) => {
                    const dateA = new Date(a.tanggal);
                    const dateB = new Date(b.tanggal);
                    return dateA - dateB;
                });
                
                // Get color from palette (cycle through if more materials than colors)
                const colorIndex = index % clientColorPalette.length;
                const colors = clientColorPalette[colorIndex];
                
                return {
                    label: item.nama,
                    data: priceHistory,
                    borderColor: colors.border,
                    backgroundColor: colors.bg,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: (context) => {
                        // Make custom price points larger
                        return context.raw && context.raw.is_custom ? 6 : 4;
                    },
                    pointBackgroundColor: (context) => {
                        // Make custom price points green, otherwise use line color
                        return context.raw && context.raw.is_custom ? 'rgb(34, 197, 94)' : colors.border;
                    },
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                };
            });

            // Get all unique dates for x-axis
            const allDates = new Set();
            klienData.forEach(dataset => {
                dataset.data.forEach(point => {
                    allDates.add(point.formatted_tanggal);
                });
            });
            const labels = Array.from(allDates).sort((a, b) => {
                // Custom sort to ensure proper date order
                const dateA = a === todayFormatted ? today : new Date(a + ' 2025');
                const dateB = b === todayFormatted ? today : new Date(b + ' 2025');
                return dateA - dateB;
            });

            // Transform data for Chart.js format
            const datasets = klienData.map(dataset => ({
                ...dataset,
                data: labels.map(label => {
                    const point = dataset.data.find(p => p.formatted_tanggal === label);
                    return point ? point.harga : null;
                }),
                // Add special styling for different point types
                pointBackgroundColor: labels.map(label => {
                    const point = dataset.data.find(p => p.formatted_tanggal === label);
                    if (point?.is_custom) return 'rgb(16, 185, 129)'; // Green for custom
                    if (point?.extrapolated) return 'rgb(239, 68, 68)'; // Red for extrapolated
                    return dataset.borderColor; // Use line color for consistency
                }),
                pointBorderColor: labels.map(label => {
                    const point = dataset.data.find(p => p.formatted_tanggal === label);
                    if (point?.is_custom) return 'rgb(16, 185, 129)';
                    if (point?.extrapolated) return 'rgb(239, 68, 68)';
                    return '#fff';
                }),
                pointRadius: labels.map(label => {
                    const point = dataset.data.find(p => p.formatted_tanggal === label);
                    return point?.is_custom ? 8 : 4; // Larger for custom prices
                })
            }));

            klienChart = new Chart(klienCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 11 },
                                padding: 10,
                                boxWidth: 8,
                                boxHeight: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    const value = 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    const materialName = context.dataset.label;
                                    const labelDate = context.label;
                                    
                                    // Find the material data to check for custom price
                                    const materialData = dataToUse.find(item => item.nama === materialName);
                                    
                                    let suffix = '';
                                    if (labelDate === todayFormatted) {
                                        if (materialData && materialData.custom_price && materialData.custom_price > 0) {
                                            suffix = ' (Custom Price)';
                                        } else {
                                            suffix = ' (Today)';
                                        }
                                    }
                                    
                                    return materialName + ': ' + value + suffix;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(156, 163, 175, 0.1)' },
                            ticks: { 
                                color: 'rgb(107, 114, 128)', 
                                font: { size: 10 },
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
                                font: { size: 10 },
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }

        // Supplier Price Chart - Enhanced for Multiple Suppliers
        if (supplierCtx) {
            // Create datasets for multiple suppliers per material
            const supplierDatasets = [];
            const colorPalette = [
                { border: 'rgb(16, 185, 129)', bg: 'rgba(16, 185, 129, 0.1)' }, // Best supplier - green
                { border: 'rgb(249, 115, 22)', bg: 'rgba(249, 115, 22, 0.1)' }, // Alternative - orange
                { border: 'rgb(139, 92, 246)', bg: 'rgba(139, 92, 246, 0.1)' }, // Alternative - purple
                { border: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' }, // Alternative - pink
                { border: 'rgb(14, 165, 233)', bg: 'rgba(14, 165, 233, 0.1)' }, // Alternative - blue
            ];

            dataToUse.forEach((material, materialIndex) => {
                if (material.supplier_options && material.supplier_options.length > 0) {
                    material.supplier_options.forEach((supplier, supplierIndex) => {
                        if (supplier.price_history && supplier.price_history.length > 0) {
                            const colorIndex = supplierIndex % colorPalette.length;
                            const color = colorPalette[colorIndex];
                            const extendedHistory = extrapolateToToday(supplier.price_history, `${material.nama} - ${supplier.supplier_name}`, true);
                            const supplierLabel = supplier.pic_name 
                                ? `${material.nama} - ${supplier.supplier_name} (PIC: ${supplier.pic_name})${supplier.is_best ? ' [Terbaik]' : ''}`
                                : `${material.nama} - ${supplier.supplier_name}${supplier.is_best ? ' (Terbaik)' : ''}`;
                            
                            supplierDatasets.push({
                                label: supplierLabel,
                                data: extendedHistory,
                                borderColor: supplier.is_best ? colorPalette[0].border : color.border,
                                backgroundColor: supplier.is_best ? colorPalette[0].bg : color.bg,
                                borderWidth: supplier.is_best ? 3 : 2,
                                fill: false,
                                tension: 0.4,
                                pointRadius: supplier.is_best ? 5 : 4,
                                pointBackgroundColor: supplier.is_best ? colorPalette[0].border : color.border,
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                borderDash: supplier.is_best ? [] : [5, 5], // Dashed line for non-best suppliers
                            });
                        }
                    });
                }
                
                // Fallback to single supplier price history if no supplier_options
                if ((!material.supplier_options || material.supplier_options.length === 0) && material.supplier_price_history) {
                    const extendedHistory = extrapolateToToday(material.supplier_price_history, `${material.nama} - ${material.best_supplier}`, true);
                    const fallbackLabel = material.best_supplier_pic 
                        ? `${material.nama} - ${material.best_supplier} (PIC: ${material.best_supplier_pic})`
                        : `${material.nama} - ${material.best_supplier}`;
                    supplierDatasets.push({
                        label: fallbackLabel,
                        data: extendedHistory,
                        borderColor: colorPalette[0].border,
                        backgroundColor: colorPalette[0].bg,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: colorPalette[0].border,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    });
                }
            });

            // Get all unique dates for x-axis from all suppliers
            const allSupplierDates = new Set();
            supplierDatasets.forEach(dataset => {
                dataset.data.forEach(point => {
                    allSupplierDates.add(point.formatted_tanggal);
                });
            });
            const supplierLabels = Array.from(allSupplierDates).sort((a, b) => {
                // Custom sort to ensure proper date order
                const dateA = a === todayFormatted ? today : new Date(a + ' 2025');
                const dateB = b === todayFormatted ? today : new Date(b + ' 2025');
                return dateA - dateB;
            });

            // Transform data for Chart.js format with extrapolation styling
            const finalSupplierDatasets = supplierDatasets.map(dataset => ({
                ...dataset,
                data: supplierLabels.map(label => {
                    const point = dataset.data.find(p => p.formatted_tanggal === label);
                    return point ? point.harga : null;
                }),
                // Add special styling for extrapolated points
                pointBackgroundColor: supplierLabels.map(label => {
                    const point = dataset.data.find(p => p.formatted_tanggal === label);
                    if (point?.extrapolated) {
                        return 'rgb(239, 68, 68)'; // Red for extrapolated
                    }
                    return dataset.borderColor;
                }),
                pointBorderColor: supplierLabels.map(label => {
                    const point = dataset.data.find(p => p.formatted_tanggal === label);
                    return point?.extrapolated ? 'rgb(239, 68, 68)' : '#fff';
                })
            }));

            supplierChart = new Chart(supplierCtx, {
                type: 'line',
                data: {
                    labels: supplierLabels,
                    datasets: finalSupplierDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 10 },
                                padding: 10,
                                boxWidth: 8,
                                boxHeight: 8,
                                filter: function(item, chart) {
                                    // Show only first 6 suppliers in legend to avoid clutter
                                    return item.datasetIndex < 6;
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgb(249, 115, 22)',
                            borderWidth: 1,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    const label = context.dataset.label || '';
                                    const value = 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    const isToday = context.label === todayFormatted;
                                    return label + ': ' + value + (isToday ? ' (Today)' : '');
                                },
                                afterLabel: function(context) {
                                    // Show if this is the best supplier
                                    if (context.dataset.label.includes('(Terbaik)')) {
                                        return 'Supplier Terbaik';
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
                                font: { size: 10 },
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
                                font: { size: 10 },
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
        
        } catch (error) {
            console.error('Error creating charts:', error);
            // Don't throw - just log and continue
        }
    }

    // Initial chart creation
    updateCharts();

    // Track if we're in the middle of a save/submit action
    let isSaving = false;

    // Listen for save/submit actions to prevent chart updates during save
    window.addEventListener('livewire:request', function(event) {
        const method = event.detail?.params?.[0]?.method;
        if (method === 'saveDraft' || method === 'submitForVerification' || method === 'resetForm') {
            console.log('Save/submit action detected, pausing chart updates');
            isSaving = true;
        }
    });

    // Reset the saving flag when request completes
    window.addEventListener('livewire:finish', function(event) {
        const method = event.detail?.params?.[0]?.method;
        if (method === 'saveDraft' || method === 'submitForVerification' || method === 'resetForm') {
            console.log('Save/submit action completed');
            isSaving = false;
            
            // If it was a reset, clear the charts
            if (method === 'resetForm') {
                setTimeout(() => {
                    updateCharts();
                }, 100);
            }
        }
    });

    // Listen for Livewire updates and rerender charts
    document.addEventListener('livewire:morph', function() {
        // Don't update charts during save operations
        if (isSaving) {
            console.log('Skipping chart update during save operation');
            return;
        }

        setTimeout(() => {
            // Only update if we don't have recent event data
            if (lastDataSource !== 'from event' || Date.now() - lastUpdateTime > 2000) {
                updateCharts();
            }
        }, 50);
    });

    // Also listen for specific events when margin analysis is updated
    window.addEventListener('margin-analysis-updated', function(event) {
        console.log('margin-analysis-updated event received:', event.detail);
        const newData = event.detail.analysisData || event.detail[0]?.analysisData;
        setTimeout(() => updateCharts(newData), 100);
    });
    
    // Listen for chart data updates (for custom prices)
    window.addEventListener('chart-data-updated', function(event) {
        console.log('chart-data-updated event received');
        setTimeout(() => updateCharts(), 100);
    });
});
</script>
