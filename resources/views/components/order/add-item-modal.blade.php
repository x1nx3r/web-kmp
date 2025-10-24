@props(['availableMaterials', 'suppliers', 'currentMaterial', 'currentSupplier', 'currentQuantity', 'currentSatuan', 'currentHargaSupplier', 'currentHargaJual', 'currentSpesifikasi', 'currentCatatan'])

{{-- Add Item Modal --}}
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Tambah Item Order</h3>
            <button wire:click="closeAddItemModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        {{-- Modal Content --}}
        <div class="space-y-4">
            {{-- Material Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Material <span class="text-red-500">*</span>
                </label>
                <select wire:model.live="currentMaterial" wire:change="selectMaterial($event.target.value)"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Pilih Material</option>
                    @foreach($availableMaterials as $material)
                        <option value="{{ $material['id'] }}">{{ $material['nama'] }} ({{ $material['satuan'] }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Supplier Selection --}}
            @if($currentMaterial)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Supplier <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="currentSupplier" wire:change="selectSupplier($event.target.value)"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Pilih Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier['supplier_id'] }}">
                                {{ $supplier['supplier_name'] }} - Rp {{ number_format($supplier['harga_per_unit'], 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Quantity and Unit --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="currentQuantity" step="0.01" min="0.01"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Satuan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="currentSatuan"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="kg, ton, box, dll">
                </div>
            </div>

            {{-- Pricing --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Harga Supplier <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="currentHargaSupplier" step="0.01" min="0"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Harga Jual <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="currentHargaJual" step="0.01" min="0"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="0">
                </div>
            </div>

            {{-- Margin Preview --}}
            @if($currentHargaSupplier > 0 && $currentHargaJual > 0 && $currentQuantity > 0)
                @php
                    $totalHpp = $currentQuantity * $currentHargaSupplier;
                    $totalHarga = $currentQuantity * $currentHargaJual;
                    $marginPerUnit = $currentHargaJual - $currentHargaSupplier;
                    $totalMargin = $currentQuantity * $marginPerUnit;
                    $marginPercentage = $currentHargaJual > 0 ? ($marginPerUnit / $currentHargaJual) * 100 : 0;
                @endphp
                <div class="bg-gray-50 rounded-lg p-3">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Preview Perhitungan:</h4>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Total HPP:</span>
                            <div class="font-medium">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">Total Harga:</span>
                            <div class="font-medium">Rp {{ number_format($totalHarga, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">Margin:</span>
                            <div class="font-medium text-green-600">
                                Rp {{ number_format($totalMargin, 0, ',', '.') }}
                                ({{ number_format($marginPercentage, 1) }}%)
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Additional Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

        {{-- Modal Footer --}}
        <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
            <button wire:click="closeAddItemModal" 
                    class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                Batal
            </button>
            <button wire:click="addOrderItem"
                    class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ (!$currentMaterial || !$currentSupplier || !$currentQuantity || !$currentSatuan || !$currentHargaSupplier || !$currentHargaJual) ? 'disabled' : '' }}>
                <i class="fas fa-plus mr-2"></i>
                Tambah Item
            </button>
        </div>
    </div>
</div>