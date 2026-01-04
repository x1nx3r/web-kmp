{{-- Omset per Klien (Bar Chart) Section --}}
<div class="mb-6">
    {{-- Card: Omset per Klien --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                    Omset Klien
                    <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full" 
                          title="Omset Sistem (transaksi terverifikasi) per bulan">
                        <i class="fas fa-info-circle mr-1"></i>Per Bulan
                    </span>
                </h3>
                <p class="text-sm text-gray-500 mt-1">Distribusi omset bulanan per klien</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Search Filter --}}
                <div class="relative">
                    <input type="text" 
                           id="searchKlien" 
                           placeholder="Cari klien..." 
                           class="w-64 pl-10 pr-10 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           onkeyup="handleKlienSearchKeyup(event)">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <button onclick="clearKlienSearch()" 
                            id="clearSearchKlien"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                {{-- Year Navigation --}}
                <div class="flex items-center gap-2">
                    <button onclick="changeYearKlienChart(-1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="px-4 py-2 bg-blue-50 rounded-lg">
                        <span class="text-sm font-semibold text-blue-700">Tahun: </span>
                        <span id="currentYearKlien" class="text-lg font-bold text-blue-600">{{ date('Y') }}</span>
                    </div>
                    <button onclick="changeYearKlienChart(1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-right text-gray-600"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div style="height: 500px;">
            <canvas id="chartOmsetPerKlien"></canvas>
        </div>
    </div>
</div>
