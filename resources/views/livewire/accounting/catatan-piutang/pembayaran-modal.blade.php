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
                            <span class="font-medium text-blue-900">No. Piutang:</span>
                            <span class="ml-2 text-blue-700 font-semibold">{{ $selectedPiutang->no_piutang }}</span>
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
                                <input type="number" step="0.01" wire:model="jumlah_bayar" placeholder="0.00" max="{{ $selectedPiutang->sisa_piutang }}" class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                            <input type="file" wire:model="bukti_pembayaran" accept=".jpg,.jpeg,.png,.pdf" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Format: JPG, PNG, PDF (Max: 5MB)
                            </p>
                            @error('bukti_pembayaran') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex justify-end space-x-3 mt-6 pt-5 border-t border-gray-200">
                        <button type="button" wire:click="closePembayaranModal" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all shadow-md font-medium">
                            <i class="fas fa-check mr-2"></i>Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
