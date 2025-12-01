{{-- Meta CSRF Token --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Modal Konfirmasi Revisi Pengiriman --}}
<div id="revisiModal" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        
        {{-- Header Modal --}}
        <div class="bg-red-600 px-6 py-4 border-b border-red-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Revisi Pengiriman</h3>
                        <p class="text-sm text-red-100 opacity-90">Masukkan catatan revisi untuk pengiriman ini</p>
                    </div>
                </div>
                <button type="button" onclick="closeRevisiModal()" 
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
                    <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                    Informasi Pengiriman yang akan Direvisi
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
                        <span class="text-gray-600">Klien:</span>
                        <span class="font-medium">{{ $pengiriman->order->klien->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal Kirim:</span>
                        <span class="font-medium">{{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Qty:</span>
                        <span class="font-medium">{{ $pengiriman->total_qty_kirim ? number_format($pengiriman->total_qty_kirim, 0, ',', '.') . ' kg' : '0 kg' }}</span>
                    </div>
                </div>
            </div>

            {{-- Review Pengiriman --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
                    <i class="fas fa-star text-blue-600 mr-2"></i>
                    Review Pengiriman
                </h4>
                
                @if($pengiriman->rating || $pengiriman->ulasan)
                    <div class="space-y-3">
                        {{-- Rating --}}
                        @if($pengiriman->rating)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Rating:</span>
                                <div class="flex items-center space-x-2">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-lg {{ $i <= $pengiriman->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ $pengiriman->rating }}/5</span>
                                </div>
                            </div>
                        @endif
                        
                        {{-- Ulasan --}}
                        @if($pengiriman->ulasan)
                            <div>
                                <span class="text-sm text-gray-600">Ulasan:</span>
                                <div class="mt-1 p-3 bg-white border border-blue-300 rounded-lg">
                                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $pengiriman->ulasan }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex items-center justify-center text-gray-500 py-2">
                        <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                        <span class="text-sm">Pengiriman belum direview</span>
                    </div>
                @endif
            </div>

            {{-- Form Catatan Revisi --}}
            <form id="revisiForm">
                <input type="hidden" id="pengirimanId" value="{{ $pengiriman->id }}">
                
                <div class="mb-6">
                    <label for="catatanRevisi" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sticky-note mr-1 text-red-600"></i>
                        Catatan Revisi <span class="text-red-500">*</span>
                    </label>
                    <textarea id="catatanRevisi" 
                              name="catatan" 
                              rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"
                              placeholder="Masukkan catatan revisi untuk pengiriman ini..."
                              required></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Catatan ini akan digunakan sebagai alasan revisi dan akan terlihat oleh tim purchasing.
                    </p>
                </div>

                {{-- Peringatan --}}
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-600 mt-1 mr-3"></i>
                        <div>
                            <h5 class="text-sm font-semibold text-red-800 mb-1">Peringatan Revisi</h5>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• Pengiriman akan dikembalikan ke status pending</li>
                                <li>• Catatan revisi akan tersimpan dalam riwayat pengiriman</li>
                                <li>• Tim purchasing perlu melakukan perbaikan sesuai catatan</li>
                                <li>• Proses verifikasi harus dilakukan ulang setelah perbaikan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Footer dengan Tombol Aksi --}}
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
            <button type="button" onclick="closeRevisiModal()" 
                    class="w-full sm:w-auto px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200 font-medium text-sm">
                <i class="fas fa-times mr-2"></i>
                Tutup
            </button>
            @php
                $currentUser = Auth::user();
                $canRevise = in_array($currentUser->role, ['direktur', 'manager_purchasing']);
            @endphp
            
            @if($canRevise)
                <button type="button" onclick="submitRevisiPengiriman()" 
                        class="w-full sm:w-auto px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200 font-medium text-sm">
                    <i class="fas fa-edit mr-2"></i>
                    Revisi Pengiriman
                </button>
            @else
                <div class="w-full sm:w-auto px-6 py-2.5 bg-gray-400 text-gray-200 rounded-lg cursor-not-allowed text-sm font-medium text-center" title="Hanya Direktur dan Manager Purchasing yang dapat merevisi">
                    <i class="fas fa-lock mr-2"></i>
                    Akses Terbatas
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Function to close revisi modal
function closeRevisiModal() {
    if (window.parent && window.parent.closeRevisiModalFromDetail) {
        window.parent.closeRevisiModalFromDetail();
    } else {
        const modal = document.getElementById('revisiModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
}

// Note: submitRevisiPengiriman function is now defined globally in menunggu-verifikasi.blade.php

// Auto resize textarea
document.getElementById('catatanRevisi').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Focus on textarea when modal opens
setTimeout(() => {
    document.getElementById('catatanRevisi').focus();
}, 100);
</script>
