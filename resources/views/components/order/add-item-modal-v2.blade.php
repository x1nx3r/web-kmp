@props(['availableMaterials', 'currentMaterial', 'currentQuantity', 'currentSatuan', 'currentHargaJual', 'currentSpesifikasi', 'currentCatatan', 'autoSuppliers', 'bestMargin', 'recommendedPrice'])

{{-- Add Item Modal V2 - Multi-Supplier Auto-Population --}}
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50" wire:click.self="closeAddItemModal">
    <div class="relative top-10 mx-auto p-5 border border-gray-200 w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 shadow-2xl rounded-lg bg-white max-h-[90vh] overflow-y-auto">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Tambah Item Order - Multi-Supplier</h3>
            <button wire:click="closeAddItemModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        {{-- Modal Content --}}
        <div class="space-y-6">
            {{-- Step 1: Material Selection --}}
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="text-md font-semibold text-blue-900 mb-3">
                    <i class="fas fa-box-open mr-2"></i>
                    Step 1: Pilih Material
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Material <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="currentMaterial" wire:change="selectMaterial($event.target.value)"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Material</option>
                            @foreach($availableMaterials as $material)
                                <option value="{{ $material['id'] }}">{{ $material['nama'] }} ({{ $material['satuan'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="currentQuantity" step="0.01" min="0.01"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0">
                    </div>
                </div>
            </div>

            {{-- Step 2: Auto-Populated Suppliers (Only show if material is selected) --}}
            @if($currentMaterial && !empty($autoSuppliers))
                <div class="bg-green-50 rounded-lg p-4">
                    <h4 class="text-md font-semibold text-green-900 mb-3">
                        <i class="fas fa-magic mr-2"></i>
                        Step 2: Supplier Otomatis Ditemukan ({{ count($autoSuppliers) }} supplier)
                    </h4>
                    
                    {{-- Supplier Summary Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                        @foreach($autoSuppliers as $index => $supplier)
                            <div class="border rounded-lg p-3 {{ $supplier['is_recommended'] ? 'border-green-500 bg-green-100' : 'border-gray-200 bg-white' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-medium text-sm {{ $supplier['is_recommended'] ? 'text-green-900' : 'text-gray-900' }}">
                                        {{ $supplier['supplier_name'] }}
                                        @if($supplier['is_recommended'])
                                            <span class="ml-1 px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full">Best</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-600">Rank #{{ $index + 1 }}</div>
                                </div>
                                <div class="text-xs text-gray-600 mb-1">{{ $supplier['supplier_location'] }}</div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-sm font-semibold">Rp {{ number_format($supplier['harga_supplier'], 0, ',', '.') }}</div>
                                        <div class="text-xs text-gray-500">per {{ $supplier['satuan'] }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs {{ $supplier['margin_percentage'] >= 20 ? 'text-green-600' : ($supplier['margin_percentage'] >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ number_format($supplier['margin_percentage'], 1) }}% margin
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Stock: {{ number_format($supplier['stok'], 0) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Best Supplier Highlight --}}
                    @if($bestMargin > 0)
                        <div class="bg-white border border-green-300 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-green-800">
                                        <i class="fas fa-star mr-1"></i>
                                        Recommended Price: Rp {{ number_format($recommendedPrice, 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-600">Best margin dengan supplier termurah</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-green-600">{{ number_format($bestMargin, 1) }}%</div>
                                    <div class="text-xs text-gray-500">Best Margin</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Step 3: Pricing & Details --}}
            @if($currentMaterial)
                <div class="bg-purple-50 rounded-lg p-4">
                    <h4 class="text-md font-semibold text-purple-900 mb-3">
                        <i class="fas fa-dollar-sign mr-2"></i>
                        Step 3: Set Harga Jual & Detail
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Harga Jual <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model.live="currentHargaJual" step="0.01" min="0"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="0">
                            @if($recommendedPrice > 0)
                                <div class="text-xs text-gray-500 mt-1">
                                    Rekomendasi: Rp {{ number_format($recommendedPrice, 0, ',', '.') }}
                                    <button type="button" wire:click="$set('currentHargaJual', {{ $recommendedPrice }})" 
                                            class="ml-2 text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-magic text-xs"></i> Gunakan
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Satuan</label>
                            <input type="text" wire:model="currentSatuan" readonly
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                   placeholder="Satuan akan terisi otomatis">
                        </div>
                    </div>

                    {{-- Live Margin Calculation --}}
                    @if($currentHargaJual > 0 && $currentQuantity > 0 && !empty($autoSuppliers))
                        @php
                            $bestSupplier = collect($autoSuppliers)->sortBy('harga_supplier')->first();
                            $totalHarga = $currentQuantity * $currentHargaJual;
                            $totalHpp = $currentQuantity * $bestSupplier['harga_supplier'];
                            $totalMargin = $totalHarga - $totalHpp;
                            $marginPercentage = $totalHarga > 0 ? ($totalMargin / $totalHarga) * 100 : 0;
                        @endphp
                        <div class="mt-4 bg-white border border-purple-300 rounded-lg p-3">
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Preview Perhitungan (vs Best Supplier):</h5>
                            <div class="grid grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Total HPP:</span>
                                    <div class="font-medium">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Total Harga:</span>
                                    <div class="font-medium">Rp {{ number_format($totalHarga, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Total Margin:</span>
                                    <div class="font-medium text-green-600">Rp {{ number_format($totalMargin, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Margin %:</span>
                                    <div class="font-medium {{ $marginPercentage >= 20 ? 'text-green-600' : ($marginPercentage >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format($marginPercentage, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Additional Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Spesifikasi Khusus</label>
                            <textarea wire:model="currentSpesifikasi" rows="2"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                      placeholder="Spesifikasi khusus untuk item ini"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea wire:model="currentCatatan" rows="2"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                      placeholder="Catatan untuk item ini"></textarea>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Info Message if no material selected --}}
            @if(!$currentMaterial)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-yellow-600 mr-3"></i>
                        <div>
                            <h5 class="text-sm font-medium text-yellow-800">Pilih Material Terlebih Dahulu</h5>
                            <p class="text-sm text-yellow-700">Setelah memilih material, sistem akan otomatis menampilkan semua supplier yang tersedia dengan harga dan margin terbaik.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- No suppliers found message --}}
            @if($currentMaterial && empty($autoSuppliers))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                        <div>
                            <h5 class="text-sm font-medium text-red-800">Supplier Tidak Ditemukan</h5>
                            <p class="text-sm text-red-700">Tidak ada supplier yang memiliki material ini. Silakan pilih material lain atau hubungi admin untuk menambah supplier.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
            <button wire:click="closeAddItemModal" 
                    class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                Batal
            </button>
            <button wire:click="addOrderItem"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ (!$currentMaterial || !$currentQuantity || !$currentSatuan || !$currentHargaJual || empty($autoSuppliers)) ? 'disabled' : '' }}>
                <i class="fas fa-plus mr-2"></i>
                Tambah Item ({{ count($autoSuppliers ?? []) }} supplier)
            </button>
        </div>
    </div>
</div>