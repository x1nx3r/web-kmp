@props(['selectedOrderItems', 'selectedKlien', 'selectedKlienCabang'])

{{-- Selected Order Items --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-cubes text-purple-600 text-sm"></i>
                </div>
                <h3 class="font-semibold text-gray-900">Item Order</h3>
            </div>
            <button
                wire:click="openAddItemModal"
                class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                {{ (!$selectedKlien || !$selectedKlienCabang) ? 'disabled' : '' }}
            >
                <i class="fas fa-plus mr-1"></i>Tambah
            </button>
        </div>
    </div>
    <div class="p-4">
        <div class="space-y-3 max-h-80 overflow-y-auto">
            @forelse($selectedOrderItems as $index => $item)
                <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-purple-400">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-cube text-purple-600 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $item['material_name'] }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($item['qty'], 2) }} {{ $item['satuan'] }} • {{ $item['suppliers_count'] ?? 0 }} supplier tersedia
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    <span class="font-medium">Rp {{ number_format($item['total_harga'], 0, ',', '.') }}</span>
                                    <span class="text-gray-500 ml-2">
                                        (Margin: {{ number_format($item['margin_percentage'] ?? 0, 1) }}%)
                                    </span>
                                    @if(isset($item['best_supplier_price']))
                                        <span class="text-gray-500 ml-2">
                                            • Best: Rp {{ number_format($item['best_supplier_price'], 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button
                            wire:click="removeOrderItem('{{ $item['id'] }}')"
                            class="text-red-500 hover:text-red-700 p-1 rounded transition-colors"
                        >
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-cubes text-2xl mb-2"></i>
                    <p>Belum ada item order</p>
                    @if(!$selectedKlien || !$selectedKlienCabang)
                        <p class="text-sm mt-1">Pilih klien terlebih dahulu</p>
                    @else
                        <p class="text-sm mt-1">Klik tombol "Tambah" untuk menambahkan item</p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>