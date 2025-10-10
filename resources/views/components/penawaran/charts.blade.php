{{-- Main Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Client Prices Chart --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-chart-line text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Tren Harga Klien</h3>
                        <p class="text-sm text-gray-500 mt-1">Historical client pricing trends</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-xs text-gray-600">Client Prices</span>
                </div>
            </div>
        </div>
        <div class="p-8 relative">
            <div id="klien-chart-container" class="h-80">
                <canvas id="klienPriceChart" class="w-full h-full"></canvas>
            </div>
            <div id="klien-placeholder" class="absolute inset-0 m-8 flex items-center justify-center text-center" style="display: none;">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-blue-500 text-2xl"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-base font-semibold text-gray-800">Pilih material untuk melihat tren harga</h4>
                        <p class="text-sm text-gray-500">Grafik akan menampilkan riwayat harga klien</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Supplier Prices Chart --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-chart-line text-orange-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Perbandingan Harga Supplier</h3>
                        <p class="text-sm text-gray-500 mt-1">Multiple supplier price comparison</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <div class="flex items-center text-xs">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-1"></div>
                        <span class="text-gray-600">Terbaik</span>
                    </div>
                    <div class="flex items-center text-xs">
                        <div class="w-3 h-3 bg-orange-500 rounded-full mr-1"></div>
                        <span class="text-gray-600">Alternatif</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-8 relative">
            <div id="supplier-chart-container" class="h-80">
                <canvas id="supplierPriceChart" class="w-full h-full"></canvas>
            </div>
            <div id="supplier-placeholder" class="absolute inset-0 m-8 flex items-center justify-center text-center" style="display: none;">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-orange-500 text-2xl"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-base font-semibold text-gray-800">Pilih material untuk melihat perbandingan supplier</h4>
                        <p class="text-sm text-gray-500">Grafik akan menampilkan harga dari multiple supplier</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
