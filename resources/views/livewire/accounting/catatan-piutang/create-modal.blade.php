<div>
    @if($showCreateModal)
    <div class="fixed inset-0 bg-green-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Tambah Catatan Piutang
                </h3>
                <button wire:click="closeCreateModal" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit.prevent="create" class="p-6">
                <div class="space-y-5">
                    <!-- Supplier -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="supplier_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">Pilih Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Tanggal Piutang -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Piutang <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="tanggal_piutang" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('tanggal_piutang') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tanggal Jatuh Tempo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="tanggal_jatuh_tempo" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('tanggal_jatuh_tempo') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Jumlah Piutang -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Piutang <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                            <input
                                type="text"
                                id="jumlah_piutang_display"
                                placeholder="0"
                                class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                oninput="formatCurrencyInput(this, 'jumlah_piutang_hidden')"
                                onblur="syncToLivewire('jumlah_piutang_hidden')"
                            >
                        </div>
                        <input type="hidden" wire:model.defer="jumlah_piutang" id="jumlah_piutang_hidden">
                        @error('jumlah_piutang') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea wire:model="keterangan" rows="3" placeholder="Masukkan keterangan piutang..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
                        @error('keterangan') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end space-x-3 mt-6 pt-5 border-t border-gray-200">
                    <button type="button" wire:click="closeCreateModal" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all shadow-md font-medium">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

<script>
function formatCurrencyInput(displayInput, hiddenInputId) {
    // Get the raw value and remove all non-digit characters
    let value = displayInput.value.replace(/[^0-9]/g, '');

    // Update hidden input with raw numeric value
    let hiddenInput = document.getElementById(hiddenInputId);
    if (hiddenInput) {
        hiddenInput.value = value;
        // Dispatch input event to trigger Livewire update
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Format for display with thousand separators
    if (value) {
        displayInput.value = parseInt(value).toLocaleString('id-ID');
    } else {
        displayInput.value = '';
    }
}

function syncToLivewire(hiddenInputId) {
    let hiddenInput = document.getElementById(hiddenInputId);
    if (hiddenInput) {
        // Trigger Livewire update on blur
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
}
</script>
