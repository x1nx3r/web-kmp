{{-- Meta CSRF Token --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Modal Konfirmasi Pembatalan Pengiriman --}}
<div id="batalModal" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        
        {{-- Header Modal --}}
        <div class="bg-red-600 px-6 py-4 border-b border-red-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Konfirmasi Pembatalan Pengiriman</h3>
                        <p class="text-sm text-red-100 opacity-90">Masukkan alasan pembatalan pengiriman</p>
                    </div>
                </div>
                <button type="button" onclick="closeBatalModal()" 
                        class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6 max-h-[calc(90vh-180px)] overflow-y-auto">
            
            {{-- Informasi Pengiriman --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold text-yellow-900 mb-3 flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    Informasi Pengiriman yang akan Dibatalkan
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">No. Pengiriman:</span>
                        <span class="font-medium">{{ $pengiriman->no_pengiriman ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">No. PO:</span>
                        <span class="font-medium">{{ $pengiriman->order->po_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">PIC Purchasing:</span>
                        <span class="font-medium">{{ $pengiriman->purchasing->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-medium">{{ ucfirst($pengiriman->status) }}</span>
                    </div>
                </div>
            </div>

            {{-- Form Pembatalan --}}
            <form id="batalForm" method="POST" action="{{ route('purchasing.pengiriman.batal') }}">
                @csrf
                <input type="hidden" name="pengiriman_id" value="{{ $pengiriman->id }}">
                
                <div class="space-y-4">
                    {{-- Catatan Saat Ini --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sticky-note mr-1"></i>
                            Catatan Pengiriman Saat Ini
                        </label>
                        <div class="bg-gray-50 border border-gray-300 rounded-lg p-3 min-h-[80px]">
                            @if($pengiriman->catatan)
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $pengiriman->catatan }}</p>
                            @else
                                <p class="text-sm text-gray-500 italic">Tidak ada catatan sebelumnya</p>
                            @endif
                        </div>
                    </div>

                    {{-- Hidden input untuk catatan yang sudah ada --}}
                    <input type="hidden" name="catatan" value="{{ $pengiriman->catatan ?? '' }}">

                    {{-- Alasan Pembatalan --}}
                    <div>
                        <label for="alasan_batal" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-ban mr-1"></i>
                            Alasan Pembatalan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alasan_batal" 
                                  id="alasan_batal" 
                                  rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-vertical"
                                  placeholder="Jelaskan alasan mengapa pengiriman ini dibatalkan..."
                                  maxlength="500"
                                  required>{{ old('alasan_batal') }}</textarea>
                        <div class="flex justify-between mt-1">
                            <p class="text-xs text-gray-500">Alasan pembatalan akan ditambahkan ke catatan yang sudah ada</p>
                            <p class="text-xs text-gray-500" id="alasan-counter">0/500</p>
                        </div>
                    </div>
                </div>

                {{-- Warning --}}
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                        <div>
                            <h5 class="font-semibold text-red-800 mb-2">Peringatan:</h5>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• Status pengiriman akan diubah menjadi "Gagal"</li>
                                <li>• Data pengiriman lainnya tidak akan berubah</li>
                                <li>• Tindakan ini tidak dapat dibatalkan</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </form>

        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center rounded-b-xl">
            <button type="button" onclick="closeBatalModal()" 
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors font-medium">
                <i class="fas fa-times mr-2"></i>
                Batal
            </button>
            <button type="button" onclick="submitBatalPengiriman()" 
                    class="px-8 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-semibold shadow-md hover:shadow-lg">
                <i class="fas fa-ban mr-2"></i>
                Batalkan Pengiriman
            </button>
        </div>
    </div>
</div>


{{-- JavaScript untuk modal batal --}}
<script>
// Character counter untuk alasan pembatalan
document.addEventListener('DOMContentLoaded', function() {
    // Alasan counter
    const alasanTextarea = document.getElementById('alasan_batal');
    const alasanCounter = document.getElementById('alasan-counter');
    
    function updateAlasanCounter() {
        const length = alasanTextarea.value.length;
        alasanCounter.textContent = `${length}/500`;
        if (length > 450) {
            alasanCounter.classList.add('text-red-500');
        } else {
            alasanCounter.classList.remove('text-red-500');
        }
    }
    
    if (alasanTextarea && alasanCounter) {
        alasanTextarea.addEventListener('input', updateAlasanCounter);
        updateAlasanCounter(); // Initial count
    }
});

// Note: submitBatalPengiriman and closeBatalModal functions are defined globally in pengiriman-masuk.blade.php
</script>
#submitBatalBtn .fa-spinner {
    animation: spin 1s linear infinite;
}
</style>
