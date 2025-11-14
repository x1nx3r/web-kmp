<div>
    @if($showEditModal)
    <div class="fixed inset-0 bg-green-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Catatan Piutang
                </h3>
                <button wire:click="closeEditModal" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit.prevent="update" class="p-6">
                <div class="space-y-5">
                    <!-- No Piutang (Read Only) -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Piutang</label>
                        <input type="text" value="{{ $no_piutang }}" readonly class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                    </div>

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
                            <input type="number" step="0.01" wire:model="jumlah_piutang" class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                        @error('jumlah_piutang') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea wire:model="keterangan" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
                        @error('keterangan') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Bukti Transaksi Saat Ini -->
                    @if($existing_bukti)
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-paperclip mr-1 text-blue-600"></i>
                            Bukti Transaksi Saat Ini
                        </label>
                        <a href="{{ Storage::url($existing_bukti) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-white border border-blue-300 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors text-sm font-medium">
                            <i class="fas fa-file-alt mr-2"></i>Lihat Bukti
                        </a>
                    </div>
                    @endif

                    <!-- Upload Bukti Transaksi Baru -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Bukti Baru (Opsional)</label>
                        <input type="file" wire:model="bukti_transaksi" accept=".jpg,.jpeg,.png,.pdf" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Format: JPG, PNG, PDF (Max: 5MB)
                        </p>
                        @error('bukti_transaksi') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end space-x-3 mt-6 pt-5 border-t border-gray-200">
                    <button type="button" wire:click="closeEditModal" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all shadow-md font-medium">
                        <i class="fas fa-save mr-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
