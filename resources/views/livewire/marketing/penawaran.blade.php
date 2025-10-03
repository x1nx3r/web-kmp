<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Analisis Penawaran</h1>
                        <p class="text-gray-600 text-sm">Dashboard analisis margin & profitabilitas</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('riwayat-penawaran') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors flex items-center">
                        <i class="fas fa-history mr-2"></i>
                        Riwayat Penawaran
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        {{-- Main Charts Section (Emphasized) --}}
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
                <div class="p-6">
                    <div class="h-80" id="klien-chart-container">
                        <canvas id="klienPriceChart" class="w-full h-full"></canvas>
                        <div id="klien-placeholder" class="flex items-center justify-center h-full text-center text-gray-500" style="display: none;">
                            <div>
                                <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-chart-line text-blue-500 text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-medium text-gray-700 mb-2">Pilih material untuk melihat tren harga</h4>
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
                <div class="p-6">
                    <div class="h-80" id="supplier-chart-container">
                        <canvas id="supplierPriceChart" class="w-full h-full"></canvas>
                        <div id="supplier-placeholder" class="flex items-center justify-center h-full text-center text-gray-500" style="display: none;">
                            <div>
                                <div class="w-16 h-16 bg-orange-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-chart-line text-orange-500 text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-medium text-gray-700 mb-2">Pilih material untuk melihat perbandingan supplier</h4>
                                <p class="text-sm text-gray-500">Grafik akan menampilkan harga dari multiple supplier</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary Content Layout --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Left Section - Client & Material Selection --}}
            <div class="xl:col-span-1 space-y-6">
                {{-- Client Selection --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="border-b border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-users text-green-600 text-sm"></i>
                                </div>
                                <h3 class="font-semibold text-gray-900">Pilih Klien</h3>
                            </div>
                            <div class="text-sm text-gray-500 flex items-center">
                                @if($klienSearch || $selectedKota)
                                    <i class="fas fa-filter text-green-500 mr-1"></i>
                                    <span class="font-medium">{{ $kliens->flatten()->count() }}</span> dari total klien
                                    @if($selectedKota)
                                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                                            {{ $selectedKota }}
                                        </span>
                                    @endif
                                @else
                                    <span class="font-medium">{{ $kliens->flatten()->count() }}</span> klien
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Search & Filter Controls --}}
                    <div class="border-b border-gray-200 p-4 bg-gray-50">
                        <div class="space-y-3">
                            {{-- Search Input --}}
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="klienSearch" 
                                    class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm bg-white"
                                    placeholder="Cari nama perusahaan, kota/lokasi, atau no HP..."
                                >
                                @if($klienSearch)
                                <button 
                                    wire:click="clearKlienSearch"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <i class="fas fa-times text-gray-400 hover:text-gray-600 text-sm"></i>
                                </button>
                                @endif
                            </div>

                            {{-- Filter and Sort Row --}}
                            <div class="grid grid-cols-2 gap-3">
                                {{-- City Filter --}}
                                <div class="space-y-1">
                                    <label class="text-xs font-medium text-gray-700 flex items-center">
                                        <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                        Filter Kota:
                                    </label>
                                    <div class="relative">
                                        <select 
                                            wire:model.live="selectedKota" 
                                            class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm bg-white pr-8"
                                        >
                                            <option value="">Semua Kota</option>
                                            @foreach($availableCities as $city)
                                                <option value="{{ $city }}">{{ $city }}</option>
                                            @endforeach
                                        </select>
                                        @if($selectedKota)
                                        <button 
                                            wire:click="clearKotaFilter"
                                            class="absolute inset-y-0 right-8 flex items-center"
                                        >
                                            <i class="fas fa-times text-gray-400 hover:text-gray-600 text-xs"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Sort Dropdown --}}
                                <div class="space-y-1">
                                    <label class="text-xs font-medium text-gray-700 flex items-center">
                                        <i class="fas fa-sort text-gray-400 mr-1"></i>
                                        Urutkan:
                                    </label>
                                    <select 
                                        wire:model.live="klienSort" 
                                        class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm bg-white"
                                    >
                                        <option value="nama_asc">Nama (A-Z)</option>
                                        <option value="nama_desc">Nama (Z-A)</option>
                                        <option value="cabang_asc">Kota (A-Z)</option>
                                        <option value="cabang_desc">Kota (Z-A)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        {{-- Loading State --}}
                        <div wire:loading.delay wire:target="klienSearch,klienSort,selectedKota" class="flex items-center justify-center py-8">
                            <div class="flex items-center text-gray-500">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-green-500 mr-3"></div>
                                <span class="text-sm">Mencari klien...</span>
                            </div>
                        </div>

                        {{-- Client List --}}
                        <div wire:loading.remove wire:target="klienSearch,klienSort,selectedKota" class="space-y-3 max-h-80 overflow-y-auto">
                            @forelse($kliens as $namaKlien => $cabangList)
                                @if($cabangList->count() == 1)
                                    {{-- Single location client --}}
                                    @php $klien = $cabangList->first(); @endphp
                                    <button
                                        wire:click="selectKlien('{{ $klien->unique_key }}')"
                                        class="w-full text-left p-3 rounded-lg border transition-all duration-200 {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'border-blue-300 bg-blue-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}"
                                    >
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'bg-blue-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-building {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'text-blue-600' : 'text-gray-600' }} text-sm"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900">{{ $klien->nama }}</div>
                                                <div class="text-sm text-gray-500 flex items-center mt-1">
                                                    <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                                    {{ $klien->cabang }}
                                                    <span class="mx-2 text-gray-300">•</span>
                                                    <span class="text-gray-500">{{ $klien->bahanBakuKliens->count() }} material</span>
                                                </div>
                                            </div>
                                            @if($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang)
                                                <div class="ml-3">
                                                    <i class="fas fa-check-circle text-blue-500"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </button>
                                @else
                                    {{-- Multi-location client --}}
                                    <div class="border border-gray-200 rounded-lg">
                                        <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 bg-gray-200 rounded flex items-center justify-center mr-3">
                                                    <i class="fas fa-building text-gray-600 text-xs"></i>
                                                </div>
                                                <div class="font-medium text-gray-800">{{ $namaKlien }}</div>
                                                <div class="ml-auto text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">
                                                    {{ $cabangList->count() }} lokasi
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-2 space-y-1">
                                            @foreach($cabangList as $klien)
                                                <button
                                                    wire:click="selectKlien('{{ $klien->unique_key }}')"
                                                    class="w-full text-left p-2 rounded transition-all duration-200 {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50' }}"
                                                >
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'bg-blue-100' : 'bg-gray-100' }} rounded flex items-center justify-center mr-3">
                                                            <i class="fas fa-map-marker-alt {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'text-blue-600' : 'text-gray-600' }} text-xs"></i>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="font-medium text-gray-900 text-sm">{{ $klien->cabang }}</div>
                                                            <div class="text-xs text-gray-500">{{ $klien->bahanBakuKliens->count() }} material</div>
                                                        </div>
                                                        @if($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang)
                                                            <div class="ml-2">
                                                                <i class="fas fa-check-circle text-blue-500 text-sm"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-search text-gray-400 text-xl"></i>
                                    </div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-2">Tidak ada klien ditemukan</h4>
                                    <p class="text-gray-500 text-sm">
                                        @if($klienSearch || $selectedKota)
                                            @if($klienSearch && $selectedKota)
                                                Tidak ditemukan klien dengan kata kunci "<strong>{{ $klienSearch }}</strong>" di kota <strong>{{ $selectedKota }}</strong>.
                                            @elseif($klienSearch)
                                                Tidak ditemukan klien dengan kata kunci "<strong>{{ $klienSearch }}</strong>".
                                            @else
                                                Tidak ditemukan klien di kota <strong>{{ $selectedKota }}</strong>.
                                            @endif
                                            <br>
                                            <button wire:click="clearKlienSearch" class="text-green-600 hover:text-green-700 underline mr-2">
                                                Hapus pencarian
                                            </button>
                                            @if($selectedKota)
                                            <button wire:click="clearKotaFilter" class="text-green-600 hover:text-green-700 underline">
                                                Hapus filter kota
                                            </button>
                                            @endif
                                        @else
                                            Belum ada data klien tersedia.
                                        @endif
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Selected Materials --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="border-b border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-cubes text-purple-600 text-sm"></i>
                                </div>
                                <h3 class="font-semibold text-gray-900">Material Terpilih</h3>
                            </div>
                            <button
                                wire:click="openAddMaterialModal"
                                class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ (!$selectedKlien || !$selectedKlienCabang) ? 'disabled' : '' }}
                            >
                                <i class="fas fa-plus mr-1"></i>Tambah
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @forelse($selectedMaterials as $index => $material)
                                <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-purple-400">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1">
                                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-cube text-purple-600 text-sm"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900">{{ $material['nama'] }}</div>
                                                <div class="text-sm text-gray-500">{{ $material['satuan'] }}</div>
                                            </div>
                                        </div>
                                        <button
                                            wire:click="removeMaterial({{ $index }})"
                                            class="text-red-500 hover:text-red-700 p-1 rounded transition-colors"
                                        >
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Jumlah</label>
                                        <input
                                            type="number"
                                            wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                            value="{{ $material['quantity'] }}"
                                            min="1"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:border-purple-500 focus:ring-1 focus:ring-purple-200"
                                            placeholder="Masukkan jumlah"
                                        >
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-box-open text-gray-400 text-lg"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">Belum ada material dipilih</p>
                                    <p class="text-sm text-gray-400 mt-1">Pilih klien dan tambahkan material</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Section - Analysis Table --}}
            <div class="xl:col-span-2">
                {{-- Detailed Analysis Table --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-table text-gray-600 text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Analisis Margin Detail</h3>
                                    <p class="text-sm text-gray-500 mt-1">Detailed margin analysis per material</p>
                                </div>
                            </div>
                            {{-- Summary Stats --}}
                            <div class="flex space-x-4 text-sm">
                                <div class="text-center">
                                    <div class="font-semibold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">Revenue</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-semibold text-red-600">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">Cost</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-semibold {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">Profit</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-semibold {{ $overallMargin >= 0 ? 'text-blue-600' : 'text-red-600' }}">{{ number_format($overallMargin, 1) }}%</div>
                                    <div class="text-xs text-gray-500">Margin</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material & Supplier</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Klien</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Supplier</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keuntungan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($marginAnalysis as $analysis)
                                    @forelse($analysis['supplier_options'] ?? [] as $supplierIndex => $supplier)
                                        <tr class="hover:bg-gray-50 transition-colors {{ $supplier['is_best'] ? 'bg-green-50' : '' }}">
                                            <td class="px-4 py-4">
                                                <div class="flex items-center">
                                                    {{-- Material Icon --}}
                                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                        <i class="fas fa-cube text-blue-600"></i>
                                                    </div>
                                                    <div class="flex-1">
                                                        {{-- Material Name (only show on first supplier) --}}
                                                        @if($supplierIndex === 0)
                                                            <div class="font-semibold text-gray-900">{{ $analysis['nama'] }}</div>
                                                            <div class="text-sm text-gray-500">{{ $analysis['satuan'] }}</div>
                                                        @else
                                                            <div class="text-sm text-gray-400 italic">{{ $analysis['nama'] }}</div>
                                                        @endif
                                                        
                                                        {{-- Supplier Info --}}
                                                        <div class="flex items-center mt-2 p-2 {{ $supplier['is_best'] ? 'bg-green-50 border border-green-300' : 'bg-gray-50 border border-gray-200' }} rounded-lg">
                                                            <div class="w-6 h-6 {{ $supplier['is_best'] ? 'bg-green-500' : 'bg-gray-400' }} rounded flex items-center justify-center mr-2">
                                                                <i class="fas fa-building text-white text-xs"></i>
                                                            </div>
                                                            <div class="flex-1">
                                                                <div class="text-sm font-medium {{ $supplier['is_best'] ? 'text-green-800' : 'text-gray-900' }}">
                                                                    {{ $supplier['supplier_name'] }}
                                                                    @if($supplier['is_best'])
                                                                        <span class="text-xs bg-green-600 text-white px-2 py-0.5 rounded ml-2">Best</span>
                                                                    @endif
                                                                </div>
                                                                <div class="text-xs {{ $supplier['is_best'] ? 'text-green-600' : 'text-gray-500' }}">
                                                                    @if($supplier['pic_name'])
                                                                        PIC: {{ $supplier['pic_name'] }} • Option {{ $supplierIndex + 1 }}
                                                                    @else
                                                                        Option {{ $supplierIndex + 1 }}
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            {{-- Only show quantity on first supplier row --}}
                                            <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                                @if($supplierIndex === 0)
                                                    {{ number_format($analysis['quantity']) }}
                                                @else
                                                    <span class="text-gray-400">{{ number_format($analysis['quantity']) }}</span>
                                                @endif
                                            </td>
                                            
                                            {{-- Only show client price on first supplier row --}}
                                            <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                                @if($supplierIndex === 0)
                                                    Rp {{ number_format($analysis['klien_price'], 0, ',', '.') }}
                                                    @if($analysis['is_custom_price'] ?? false)
                                                        <span class="ml-2 text-xs bg-purple-600 text-white px-2 py-0.5 rounded">Custom</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400">Rp {{ number_format($analysis['klien_price'], 0, ',', '.') }}</span>
                                                @endif
                                            </td>
                                            
                                            <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                                Rp {{ number_format($supplier['price'], 0, ',', '.') }}
                                            </td>
                                            
                                            {{-- Only show revenue on first supplier row --}}
                                            <td class="px-4 py-4">
                                                @if($supplierIndex === 0)
                                                    <span class="text-sm font-medium text-green-700 bg-green-50 px-2 py-1 rounded">
                                                        Rp {{ number_format($analysis['revenue'], 0, ',', '.') }}
                                                    </span>
                                                @else
                                                    <span class="text-sm font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded">
                                                        Rp {{ number_format($analysis['revenue'], 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </td>
                                            
                                            <td class="px-4 py-4">
                                                <span class="text-sm font-medium text-red-700 bg-red-50 px-2 py-1 rounded">
                                                    Rp {{ number_format($supplier['cost'], 0, ',', '.') }}
                                                </span>
                                            </td>
                                            
                                            <td class="px-4 py-4">
                                                <span class="text-sm font-medium {{ $supplier['profit'] >= 0 ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50' }} px-2 py-1 rounded">
                                                    Rp {{ number_format($supplier['profit'], 0, ',', '.') }}
                                                </span>
                                            </td>
                                            
                                            <td class="px-4 py-4">
                                                <span class="text-sm font-medium px-2 py-1 rounded {{ $supplier['margin_percent'] >= 20 ? 'bg-green-100 text-green-800' : ($supplier['margin_percent'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ number_format($supplier['margin_percent'], 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                                                <div class="flex items-center justify-center">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    No suppliers found for {{ $analysis['nama'] }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    
                                    {{-- Add spacing between different materials --}}
                                    @if(!$loop->last)
                                        <tr class="bg-gray-100">
                                            <td colspan="8" class="px-4 py-1"></td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mb-4">
                                                    <i class="fas fa-table text-gray-400 text-2xl"></i>
                                                </div>
                                                <h4 class="text-lg font-medium text-gray-700 mb-2">Belum ada data analisis</h4>
                                                <p class="text-sm text-gray-500">Tambahkan material untuk melihat analisis detail margin</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if(count($selectedMaterials) > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-cogs text-indigo-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Aksi</h3>
                                    <p class="text-sm text-gray-600">Lakukan tindakan berdasarkan analisis</p>
                                </div>
                            </div>
                            <button
                                wire:click="buatOrder"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled"
                                wire:target="buatOrder"
                            >
                                <i class="fas fa-shopping-cart mr-2"></i>
                                <span wire:loading.remove wire:target="buatOrder">Buat Penawaran</span>
                                <span wire:loading wire:target="buatOrder">Memproses...</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Material Modal --}}
    @if($showAddMaterialModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.3); backdrop-filter: blur(4px);" wire:click="closeAddMaterialModal"></div>

            {{-- Modal Container --}}
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full transform transition-all" @click.stop>
                    {{-- Modal Header --}}
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-plus text-purple-600 text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">Tambah Material</h3>
                                    <p class="text-gray-600 text-sm mt-1">Pilih material untuk analisis margin profitabilitas</p>
                                </div>
                            </div>
                            <button 
                                wire:click="closeAddMaterialModal"
                                class="text-gray-400 hover:text-gray-600 transition-colors p-2"
                            >
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-6">
                        {{-- Material Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                <i class="fas fa-cube mr-1 text-gray-400"></i>
                                Material
                            </label>
                            <div class="relative">
                                <select
                                    wire:model.live="currentMaterial"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm
                                           focus:border-purple-500 focus:ring-2 focus:ring-purple-200 
                                           transition-all duration-200 bg-gray-50 focus:bg-white
                                           appearance-none cursor-pointer"
                                >
                                    <option value="">Pilih material...</option>
                                    @foreach($availableMaterials as $material)
                                        <option value="{{ $material->id }}">
                                            {{ $material->nama }} ({{ $material->satuan }}) - Rp {{ number_format($material->harga_approved, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </div>
                            </div>
                            @if($currentMaterial)
                                @php
                                    $selectedMaterial = $availableMaterials->find($currentMaterial);
                                @endphp
                                @if($selectedMaterial)
                                    <div class="mt-2 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                        <div class="flex items-center text-sm">
                                            <div class="w-6 h-6 bg-purple-100 rounded-lg flex items-center justify-center mr-2">
                                                <i class="fas fa-info text-purple-600 text-xs"></i>
                                            </div>
                                            <div>
                                                <span class="font-medium text-purple-900">{{ $selectedMaterial->nama }}</span>
                                                <span class="text-purple-700 mx-2">•</span>
                                                <span class="text-purple-700">Satuan: {{ $selectedMaterial->satuan }}</span>
                                                <span class="text-purple-700 mx-2">•</span>
                                                <span class="text-purple-700">Harga: Rp {{ number_format($selectedMaterial->harga_approved, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Quantity Input --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                <i class="fas fa-calculator mr-1 text-gray-400"></i>
                                Jumlah
                                @if($currentMaterial)
                                    @php
                                        $selectedMaterial = $availableMaterials->find($currentMaterial);
                                    @endphp
                                    @if($selectedMaterial)
                                        <span class="text-purple-600 font-normal">(dalam {{ $selectedMaterial->satuan }})</span>
                                    @endif
                                @endif
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    wire:model="currentQuantity"
                                    min="1"
                                    step="1"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm
                                           focus:border-purple-500 focus:ring-2 focus:ring-purple-200 
                                           transition-all duration-200 bg-gray-50 focus:bg-white
                                           @if($currentMaterial) pr-20 @endif"
                                    placeholder="Masukkan jumlah material"
                                >
                                @if($currentMaterial)
                                    @php
                                        $selectedMaterial = $availableMaterials->find($currentMaterial);
                                    @endphp
                                    @if($selectedMaterial)
                                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm font-medium">{{ $selectedMaterial->satuan }}</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Custom Price Option --}}
                        @if($currentMaterial)
                            @php
                                $selectedMaterial = $availableMaterials->find($currentMaterial);
                            @endphp
                            @if($selectedMaterial)
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-sm font-medium text-gray-700">
                                            <i class="fas fa-tags mr-1 text-gray-400"></i>
                                            Harga Klien
                                        </label>
                                        <div class="flex items-center">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model.live="useCustomPrice" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                                <span class="ml-3 text-sm text-gray-600">Custom Price</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    @if($useCustomPrice ?? false)
                                        <div class="space-y-3">
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    wire:model="customPrice"
                                                    min="0"
                                                    step="100"
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm
                                                           focus:border-purple-500 focus:ring-2 focus:ring-purple-200 
                                                           transition-all duration-200 bg-white pl-12"
                                                    placeholder="Masukkan harga custom"
                                                >
                                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 text-sm">Rp</span>
                                                </div>
                                            </div>
                                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                                <div class="flex items-start">
                                                    <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center mr-2 mt-0.5">
                                                        <i class="fas fa-info text-blue-600 text-xs"></i>
                                                    </div>
                                                    <div class="text-sm">
                                                        <div class="font-medium text-blue-900">Price Comparison</div>
                                                        <div class="text-blue-700 mt-1">
                                                            Default: <span class="font-medium">Rp {{ number_format($selectedMaterial->harga_approved, 0, ',', '.') }}</span>
                                                            @if(($customPrice ?? 0) > 0)
                                                                <br>Custom: <span class="font-medium">Rp {{ number_format($customPrice, 0, ',', '.') }}</span>
                                                                @php
                                                                    $difference = $customPrice - $selectedMaterial->harga_approved;
                                                                    $percentage = $selectedMaterial->harga_approved > 0 ? ($difference / $selectedMaterial->harga_approved) * 100 : 0;
                                                                @endphp
                                                                <br>Difference: 
                                                                <span class="font-medium {{ $difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                    {{ $difference >= 0 ? '+' : '' }}Rp {{ number_format($difference, 0, ',', '.') }} 
                                                                    ({{ $difference >= 0 ? '+' : '' }}{{ number_format($percentage, 1) }}%)
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-tag mr-2 text-gray-400"></i>
                                                Using default price: <span class="font-medium text-gray-900 ml-1">Rp {{ number_format($selectedMaterial->harga_approved, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif

                        {{-- Cost Estimation --}}
                        @if($currentMaterial && $currentQuantity > 0)
                            @php
                                $selectedMaterial = $availableMaterials->find($currentMaterial);
                                $finalPrice = ($useCustomPrice ?? false) && ($customPrice ?? 0) > 0 ? $customPrice : $selectedMaterial->harga_approved;
                            @endphp
                            @if($selectedMaterial)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-calculator text-green-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-green-900">Total Estimation</div>
                                            <div class="text-sm text-green-700 mt-1">
                                                {{ number_format($currentQuantity) }} {{ $selectedMaterial->satuan }} × Rp {{ number_format($finalPrice, 0, ',', '.') }} = 
                                                <span class="font-semibold text-green-800">Rp {{ number_format($finalPrice * $currentQuantity, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="border-t border-gray-200 px-6 py-4 flex justify-end space-x-3 bg-gray-50 rounded-b-xl">
                        <button
                            wire:click="closeAddMaterialModal"
                            class="px-5 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg 
                                   hover:bg-gray-50 font-medium transition-colors duration-200 
                                   focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </button>
                        <button
                            wire:click="addMaterial"
                            class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium 
                                   rounded-lg transition-colors duration-200 shadow-sm
                                   focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2
                                   disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$currentMaterial || !$currentQuantity ? 'disabled' : '' }}
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Material
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="fixed bottom-4 right-4 z-50">
            <div class="bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <div class="w-6 h-6 bg-white bg-opacity-20 rounded flex items-center justify-center mr-3">
                        <i class="fas fa-check text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 z-50">
            <div class="bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <div class="w-6 h-6 bg-white bg-opacity-20 rounded flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

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

        // Dummy data for testing
        const dummyAnalysisData = [
            {
                nama: 'Semen Portland',
                klien_price_history: [
                    { tanggal: '2025-01-15', harga: 95000, formatted_tanggal: '15 Jan' },
                    { tanggal: '2025-01-20', harga: 97000, formatted_tanggal: '20 Jan' },
                    { tanggal: '2025-01-25', harga: 94000, formatted_tanggal: '25 Jan' },
                    { tanggal: '2025-01-28', harga: 96000, formatted_tanggal: '28 Jan' }
                ],
                supplier_price_history: [
                    { tanggal: '2025-01-15', harga: 85000, formatted_tanggal: '15 Jan' },
                    { tanggal: '2025-01-20', harga: 87000, formatted_tanggal: '20 Jan' },
                    { tanggal: '2025-01-25', harga: 84000, formatted_tanggal: '25 Jan' },
                    { tanggal: '2025-01-28', harga: 86000, formatted_tanggal: '28 Jan' }
                ]
            },
            {
                nama: 'Pasir Halus',
                klien_price_history: [
                    { tanggal: '2025-01-15', harga: 45000, formatted_tanggal: '15 Jan' },
                    { tanggal: '2025-01-20', harga: 47000, formatted_tanggal: '20 Jan' },
                    { tanggal: '2025-01-25', harga: 46000, formatted_tanggal: '25 Jan' },
                    { tanggal: '2025-01-28', harga: 48000, formatted_tanggal: '28 Jan' }
                ],
                supplier_price_history: [
                    { tanggal: '2025-01-15', harga: 38000, formatted_tanggal: '15 Jan' },
                    { tanggal: '2025-01-20', harga: 39000, formatted_tanggal: '20 Jan' },
                    { tanggal: '2025-01-25', harga: 37000, formatted_tanggal: '25 Jan' },
                    { tanggal: '2025-01-28', harga: 40000, formatted_tanggal: '28 Jan' }
                ]
            }
        ];

        // Use dummy data for testing - comment this line to use real data
        const testData = dummyAnalysisData;

        // Get placeholders
        const klienPlaceholder = document.getElementById('klien-placeholder');
        const supplierPlaceholder = document.getElementById('supplier-placeholder');
        const klienCtx = document.getElementById('klienPriceChart');
        const supplierCtx = document.getElementById('supplierPriceChart');

        // Use real data instead of test data now that we fixed the backend
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

        // Client Price Chart
        if (klienCtx) {
            const klienData = dataToUse.map(item => {
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
                
                return {
                    label: item.nama,
                    data: priceHistory,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: (context) => {
                        // Make custom price points larger
                        return context.raw && context.raw.is_custom ? 6 : 4;
                    },
                    pointBackgroundColor: (context) => {
                        // Make custom price points green
                        return context.raw && context.raw.is_custom ? 'rgb(34, 197, 94)' : 'rgb(59, 130, 246)';
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
                    return 'rgb(59, 130, 246)'; // Blue for historical
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
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 11 }
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
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 10 },
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
    }

    // Initial chart creation
    updateCharts();

    // Listen for Livewire updates and rerender charts
    document.addEventListener('livewire:morph', function() {
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