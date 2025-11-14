@props(['selectedMaterials', 'selectedKlien', 'selectedKlienCabang'])

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
