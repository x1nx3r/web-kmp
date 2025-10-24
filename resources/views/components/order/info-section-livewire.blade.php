@props(['tanggalOrder', 'priority', 'catatan'])

{{-- Order Info --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 p-4">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-info-circle text-purple-600 text-sm"></i>
            </div>
            <h3 class="font-semibold text-gray-900">Informasi Order</h3>
        </div>
    </div>

    <div class="p-4 space-y-4">
        {{-- Tanggal Order --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Tanggal Order <span class="text-red-500">*</span>
            </label>
            <input type="date" wire:model="tanggalOrder" 
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" 
                   required>
        </div>

        {{-- Priority --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Prioritas <span class="text-red-500">*</span>
            </label>
            <select wire:model="priority" 
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" 
                    required>
                <option value="rendah">🔽 Rendah</option>
                <option value="normal">➖ Normal</option>
                <option value="tinggi">🔼 Tinggi</option>
                <option value="mendesak">🔥 Mendesak</option>
            </select>
        </div>

        {{-- Catatan --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Catatan
            </label>
            <textarea wire:model="catatan" rows="3" 
                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" 
                      placeholder="Catatan tambahan untuk order ini..."></textarea>
        </div>

        {{-- Priority Info --}}
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-gray-400 mt-0.5 mr-2"></i>
                <div class="text-sm text-gray-600">
                    <p class="font-medium mb-1">Panduan Prioritas:</p>
                    <ul class="space-y-1 text-xs">
                        <li><span class="font-medium">Rendah:</span> Pemesanan rutin, tidak urgent</li>
                        <li><span class="font-medium">Normal:</span> Pemesanan standar</li>
                        <li><span class="font-medium">Tinggi:</span> Dibutuhkan segera</li>
                        <li><span class="font-medium">Mendesak:</span> Prioritas tertinggi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>