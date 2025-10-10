@props(['selectedMaterials'])

{{-- Action Buttons --}}
@if(count($selectedMaterials) > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-cogs text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Aksi Penawaran</h3>
                    <p class="text-sm text-gray-600">Simpan sebagai draft atau kirim untuk verifikasi</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <button
                    wire:click="resetForm"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors"
                    wire:loading.attr="disabled"
                >
                    <i class="fas fa-undo mr-2"></i>
                    Reset
                </button>
                <button
                    wire:click="saveDraft"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                    wire:target="saveDraft"
                >
                    <i class="fas fa-save mr-2"></i>
                    <span wire:loading.remove wire:target="saveDraft">Simpan Draft</span>
                    <span wire:loading wire:target="saveDraft">Menyimpan...</span>
                </button>
                <button
                    wire:click="submitForVerification"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                    wire:target="submitForVerification"
                >
                    <i class="fas fa-paper-plane mr-2"></i>
                    <span wire:loading.remove wire:target="submitForVerification">Kirim untuk Verifikasi</span>
                    <span wire:loading wire:target="submitForVerification">Mengirim...</span>
                </button>
            </div>
        </div>
    </div>
@endif
