@props(['showAddMaterialModal', 'availableMaterials', 'currentMaterial', 'currentQuantity', 'useCustomPrice', 'customPrice'])

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
