<div>
    @if($showPembayaranModal && $selectedPiutang)
    <div class="fixed inset-0 bg-green-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Tambah Pembayaran
                </h3>
                <button wire:click="closePembayaranModal" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6">
                <!-- Info Piutang -->
                <div class="mb-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200 shadow-sm">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-white rounded-lg px-3 py-2">
                            <span class="font-medium text-blue-900">ID Piutang:</span>
                            <span class="ml-2 text-blue-700 font-semibold">#{{ $selectedPiutang->id }}</span>
                        </div>
                        <div class="bg-white rounded-lg px-3 py-2">
                            <span class="font-medium text-blue-900">Supplier:</span>
                            <span class="ml-2 text-blue-700 font-semibold">{{ $selectedPiutang->supplier->nama }}</span>
                        </div>
                        <div class="bg-white rounded-lg px-3 py-2">
                            <span class="font-medium text-blue-900">Total Piutang:</span>
                            <span class="ml-2 text-blue-700 font-bold">Rp {{ number_format($selectedPiutang->jumlah_piutang, 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-white rounded-lg px-3 py-2">
                            <span class="font-medium text-orange-900">Sisa Piutang:</span>
                            <span class="ml-2 text-orange-700 font-bold">Rp {{ number_format($selectedPiutang->sisa_piutang, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <form wire:submit.prevent="addPembayaran">
                    <div class="space-y-5">
                        <!-- Tanggal Bayar -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="tanggal_bayar" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('tanggal_bayar') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Jumlah Bayar -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                                <input
                                    type="text"
                                    id="jumlah_bayar_display"
                                    value="{{ $jumlah_bayar ? number_format($jumlah_bayar, 0, ',', '.') : '' }}"
                                    placeholder="0"
                                    class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    oninput="formatCurrencyJumlahBayar(this)"
                                >
                                <input type="hidden" wire:model.defer="jumlah_bayar" id="jumlah_bayar_hidden">
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Maksimal: Rp {{ number_format($selectedPiutang->sisa_piutang, 0, ',', '.') }}
                            </p>
                            @error('jumlah_bayar') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Metode Pembayaran -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Metode Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="metode_pembayaran" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Pilih Metode</option>
                                <option value="tunai">💵 Tunai</option>
                                <option value="transfer">🏦 Transfer Bank</option>
                                <option value="cek">📄 Cek</option>
                                <option value="giro">📋 Giro</option>
                            </select>
                            @error('metode_pembayaran') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea wire:model="catatan_pembayaran" rows="3" placeholder="Masukkan catatan pembayaran..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
                            @error('catatan_pembayaran') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Bukti Pembayaran -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Pembayaran</label>
                            <input type="file" wire:model="bukti_pembayaran" accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Format: JPG, PNG, PDF (Max: 5MB)
                            </p>

                            {{-- Upload Progress Indicator --}}
                            <div wire:loading wire:target="bukti_pembayaran" class="mt-3">
                                <div class="flex items-center text-blue-600">
                                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Mengunggah file...</span>
                                </div>
                            </div>

                            @error('bukti_pembayaran') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex justify-end space-x-3 mt-6 pt-5 border-t border-gray-200">
                        <button type="button" wire:click="closePembayaranModal" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>Batal
                        </button>
                        <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="bukti_pembayaran"
                            class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all shadow-md font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="bukti_pembayaran">
                                <i class="fas fa-check mr-2"></i>Simpan Pembayaran
                            </span>
                            <span wire:loading wire:target="bukti_pembayaran" class="flex items-center">
                                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Mengunggah...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@script
<script>
    // Format currency for jumlah bayar
    window.formatCurrencyJumlahBayar = function(displayInput) {
        let value = displayInput.value.replace(/[^0-9]/g, '');

        let hiddenInput = document.getElementById('jumlah_bayar_hidden');
        if (hiddenInput) {
            hiddenInput.value = value;
            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        }

        if (value) {
            displayInput.value = parseInt(value).toLocaleString('id-ID');
        } else {
            displayInput.value = '';
        }
    }

    // Initialize on modal open
    document.addEventListener('livewire:navigated', function() {
        initCurrencyInput();
    });

    function initCurrencyInput() {
        const displayInput = document.getElementById('jumlah_bayar_display');
        const hiddenInput = document.getElementById('jumlah_bayar_hidden');

        if (displayInput && hiddenInput && hiddenInput.value) {
            displayInput.value = parseInt(hiddenInput.value).toLocaleString('id-ID');
        }
    }

    // Re-initialize when modal opens
    $wire.on('pembayaranModalOpened', () => {
        setTimeout(initCurrencyInput, 100);
    });
</script>
@endscript
