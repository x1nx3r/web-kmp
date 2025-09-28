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
                    <div class="text-gray-500 text-sm font-medium bg-gray-100 px-3 py-2 rounded-lg">
                        <i class="far fa-clock mr-2"></i>
                        {{ now()->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        <div class="grid grid-cols-12 gap-6">
            {{-- Left Sidebar - Client & Material Selection --}}
            <div class="col-span-4 space-y-6">
                {{-- Client Selection --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="border-b border-gray-200 p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-users text-green-600 text-sm"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Pilih Klien</h3>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach($kliens as $namaKlien => $cabangList)
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
                            @endforeach
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

            {{-- Main Dashboard --}}
            <div class="col-span-8 space-y-6">
                {{-- Header Stats --}}
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Pendapatan</div>
                                <div class="text-xl font-bold text-green-600">
                                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-arrow-up text-green-600"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Biaya</div>
                                <div class="text-xl font-bold text-red-600">
                                    Rp {{ number_format($totalCost, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-arrow-down text-red-600"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Keuntungan</div>
                                <div class="text-xl font-bold {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($totalProfit, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="w-10 h-10 {{ $totalProfit >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-lg flex items-center justify-center">
                                <i class="fas fa-{{ $totalProfit >= 0 ? 'trophy' : 'exclamation-triangle' }} {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Margin Keseluruhan</div>
                                <div class="text-xl font-bold {{ $overallMargin >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                    {{ number_format($overallMargin, 1) }}%
                                </div>
                            </div>
                            <div class="w-10 h-10 {{ $overallMargin >= 0 ? 'bg-blue-100' : 'bg-red-100' }} rounded-lg flex items-center justify-center">
                                <i class="fas fa-percentage {{ $overallMargin >= 0 ? 'text-blue-600' : 'text-red-600' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Price Comparison Charts --}}
                <div class="grid grid-cols-2 gap-6">
                    {{-- Client Prices Chart --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="border-b border-gray-200 p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-chart-line text-blue-600 text-sm"></i>
                                </div>
                                <h3 class="font-semibold text-gray-900">Tren Harga Klien</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="h-64" id="klien-chart-container">
                                <canvas id="klienPriceChart" class="w-full h-full"></canvas>
                                <div id="klien-placeholder" class="flex items-center justify-center h-full text-center text-gray-500" style="display: none;">
                                    <div>
                                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-chart-line text-blue-500 text-lg"></i>
                                        </div>
                                        <p class="font-medium text-gray-700">Pilih material untuk melihat tren harga</p>
                                        <p class="text-sm text-gray-500 mt-1">Grafik akan menampilkan riwayat harga klien</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Supplier Prices Chart --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="border-b border-gray-200 p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-chart-line text-orange-600 text-sm"></i>
                                </div>
                                <h3 class="font-semibold text-gray-900">Tren Harga Supplier Terbaik</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="h-64" id="supplier-chart-container">
                                <canvas id="supplierPriceChart" class="w-full h-full"></canvas>
                                <div id="supplier-placeholder" class="flex items-center justify-center h-full text-center text-gray-500" style="display: none;">
                                    <div>
                                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-chart-line text-orange-500 text-lg"></i>
                                        </div>
                                        <p class="font-medium text-gray-700">Pilih material untuk melihat tren harga</p>
                                        <p class="text-sm text-gray-500 mt-1">Grafik akan menampilkan riwayat harga supplier</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detailed Analysis Table --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="border-b border-gray-200 p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-table text-gray-600 text-sm"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Analisis Margin Detail</h3>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Klien</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier Terbaik</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Supplier</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keuntungan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($marginAnalysis as $analysis)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-cube text-blue-600 text-sm"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900">{{ $analysis['nama'] }}</div>
                                                    <div class="text-sm text-gray-500">{{ $analysis['satuan'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ number_format($analysis['quantity']) }}</td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($analysis['klien_price'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 bg-orange-100 rounded flex items-center justify-center mr-2">
                                                    <i class="fas fa-industry text-orange-600 text-xs"></i>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">{{ $analysis['best_supplier'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($analysis['supplier_price'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-4">
                                            <span class="text-sm font-medium text-green-600 bg-green-50 px-2 py-1 rounded">
                                                Rp {{ number_format($analysis['revenue'], 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="text-sm font-medium text-red-600 bg-red-50 px-2 py-1 rounded">
                                                Rp {{ number_format($analysis['cost'], 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="text-sm font-medium {{ $analysis['profit'] >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }} px-2 py-1 rounded">
                                                Rp {{ number_format($analysis['profit'], 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $analysis['margin_percent'] >= 20 ? 'bg-green-100 text-green-800' : ($analysis['margin_percent'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ number_format($analysis['margin_percent'], 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
                                                    <i class="fas fa-table text-gray-400 text-2xl"></i>
                                                </div>
                                                <p class="font-medium">Belum ada data analisis</p>
                                                <p class="text-sm mt-1">Tambahkan material untuk melihat analisis detail margin</p>
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
                            <div class="flex space-x-3">
                                <button
                                    wire:click="exportPdf"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    wire:loading.attr="disabled"
                                    wire:target="exportPdf"
                                >
                                    <i class="fas fa-file-pdf mr-2"></i>
                                    <span wire:loading.remove wire:target="exportPdf">Export PDF</span>
                                    <span wire:loading wire:target="exportPdf">Mengekspor...</span>
                                </button>
                                <button
                                    wire:click="buatOrder"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    wire:loading.attr="disabled"
                                    wire:target="buatOrder"
                                >
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    <span wire:loading.remove wire:target="buatOrder">Buat Order</span>
                                    <span wire:loading wire:target="buatOrder">Memproses...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Material Modal --}}
    @if($showAddMaterialModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black bg-opacity-30" wire:click="closeAddMaterialModal"></div>

            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-lg shadow-lg max-w-lg w-full" @click.stop>
                    {{-- Modal Header --}}
                    <div class="border-b border-gray-200 p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-plus text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Tambah Material</h3>
                                <p class="text-gray-600 text-sm">Pilih material untuk analisis margin</p>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Material
                            </label>
                            <select
                                wire:model="currentMaterial"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-purple-500 focus:ring-1 focus:ring-purple-200 text-sm"
                            >
                                <option value="">Pilih material...</option>
                                @foreach($availableMaterials as $material)
                                    <option value="{{ $material->id }}">
                                        {{ $material->nama }} ({{ $material->satuan }}) - Rp {{ number_format($material->harga_approved, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah
                            </label>
                            <input
                                type="number"
                                wire:model="currentQuantity"
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-purple-500 focus:ring-1 focus:ring-purple-200 text-sm"
                                placeholder="Masukkan jumlah material"
                            >
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="border-t border-gray-200 px-4 py-3 flex justify-end space-x-3">
                        <button
                            wire:click="closeAddMaterialModal"
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            wire:click="addMaterial"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors"
                        >
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

    // Function to create or update charts
    function updateCharts(dynamicData = null) {
        // Use dynamic data from event if available, otherwise use server-side data
        const analysisData = dynamicData || @json($marginAnalysis);

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

        console.log('updateCharts() called at:', new Date().toISOString());
        console.log('Data source:', dynamicData ? 'from event' : 'from server');
        console.log('Analysis data:', analysisData);
        console.log('Using data:', dataToUse);

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

        // Client Price Chart
        if (klienCtx) {
            const klienData = dataToUse.map(item => ({
                label: item.nama,
                data: item.klien_price_history || [],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }));

            // Get all unique dates for x-axis
            const allDates = new Set();
            klienData.forEach(dataset => {
                dataset.data.forEach(point => {
                    allDates.add(point.formatted_tanggal);
                });
            });
            const labels = Array.from(allDates).sort();

            // Transform data for Chart.js format
            const datasets = klienData.map(dataset => ({
                ...dataset,
                data: labels.map(label => {
                    const point = dataset.data.find(p => p.formatted_tanggal === label);
                    return point ? point.harga : null;
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
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(156, 163, 175, 0.1)' },
                            ticks: { color: 'rgb(107, 114, 128)', font: { size: 10 } }
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

        // Supplier Price Chart
        if (supplierCtx) {
            const supplierData = dataToUse.map(item => ({
                label: item.nama,
                data: item.supplier_price_history || [],
                borderColor: 'rgb(249, 115, 22)',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(249, 115, 22)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }));

            // Get all unique dates for x-axis
            const allSupplierDates = new Set();
            supplierData.forEach(dataset => {
                dataset.data.forEach(point => {
                    allSupplierDates.add(point.formatted_tanggal);
                });
            });
            const supplierLabels = Array.from(allSupplierDates).sort();

            // Transform data for Chart.js format
            const supplierDatasets = supplierData.map(dataset => ({
                ...dataset,
                data: supplierLabels.map(label => {
                    const point = dataset.data.find(p => p.formatted_tanggal === label);
                    return point ? point.harga : null;
                })
            }));

            supplierChart = new Chart(supplierCtx, {
                type: 'line',
                data: {
                    labels: supplierLabels,
                    datasets: supplierDatasets
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
                            borderColor: 'rgb(249, 115, 22)',
                            borderWidth: 1,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(156, 163, 175, 0.1)' },
                            ticks: { color: 'rgb(107, 114, 128)', font: { size: 10 } }
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
        setTimeout(updateCharts, 100);
    });

    // Also listen for specific events when margin analysis is updated
    window.addEventListener('margin-analysis-updated', function(event) {
        console.log('margin-analysis-updated event received:', event.detail);
        const newData = event.detail.analysisData || event.detail[0]?.analysisData;
        setTimeout(() => updateCharts(newData), 100);
    });
});
</script>