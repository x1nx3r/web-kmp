<div>
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-green-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-red-600 to-rose-600 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Konfirmasi Hapus
                </h3>
                <button wire:click="closeDeleteModal" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6">
                <div class="flex items-center justify-center mb-5">
                    <div class="rounded-full bg-red-100 p-4">
                        <i class="fas fa-trash-alt text-red-600 text-4xl"></i>
                    </div>
                </div>

                <p class="text-center text-gray-700 text-lg mb-5 font-medium">Apakah Anda yakin ingin menghapus catatan piutang ini?</p>

                @if($deletePiutang)
                <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">No. Piutang:</span>
                        <span class="text-gray-900">{{ $deletePiutang->no_piutang }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Supplier:</span>
                        <span class="text-gray-900">{{ $deletePiutang->supplier->nama }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Jumlah:</span>
                        <span class="text-gray-900 font-semibold">Rp {{ number_format($deletePiutang->jumlah_piutang, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Status:</span>
                        <span>
                            @if($deletePiutang->status == 'belum_lunas')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Belum Lunas</span>
                            @elseif($deletePiutang->status == 'cicilan')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Cicilan</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Lunas</span>
                            @endif
                        </span>
                    </div>
                </div>
                @endif

                <p class="text-center text-sm text-red-600 mt-5 font-semibold bg-red-50 py-3 px-4 rounded-lg border border-red-200">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    Data yang dihapus tidak dapat dikembalikan!
                </p>
            </div>

            {{-- Modal Footer --}}
            <div class="flex justify-end space-x-3 px-6 pb-6 pt-4 border-t border-gray-200">
                <button type="button" wire:click="closeDeleteModal" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button type="button" wire:click="delete" class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-lg hover:from-red-700 hover:to-rose-700 transition-all shadow-md font-medium">
                    <i class="fas fa-trash mr-2"></i>Hapus
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
