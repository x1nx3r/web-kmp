{{-- Main Charts Section with Dynamic Material Tabs --}}
<div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-visible">
    {{-- Header Section --}}
    <div class="border-b border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-chart-area text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Material Price Analysis</h3>
                    <p class="text-sm text-gray-500 mt-1">Client pricing vs supplier comparison by material</p>
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
            </div>
        </div>
        
        {{-- Dynamic Material Tabs --}}
        <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg overflow-x-auto" id="material-tabs-container">
            {{-- Tabs will be populated dynamically by JavaScript --}}
            <div class="flex-shrink-0 px-4 py-2 text-sm text-gray-500 italic">
                No materials selected
            </div>
        </div>
    </div>

    {{-- Tab Content Container --}}
    <div class="p-8 relative" id="tab-content-container">
        {{-- Material tab contents will be populated here --}}
        
        {{-- Empty State (shown when no materials) --}}
        <div id="charts-empty-state" class="flex items-center justify-center h-96 text-center">
            <div class="flex flex-col items-center justify-center space-y-3">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-area text-blue-500 text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-900">Select Materials to View Charts</h4>
                <p class="text-sm text-gray-500 max-w-md">
                    Choose materials from the left panel to start analyzing price trends and supplier comparisons.
                    Each material will get its own tab with detailed charts.
                </p>
            </div>
        </div>
    </div>
</div>
