{{-- Meta CSRF Token --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Modal Konfirmasi Verifikasi Pengiriman --}}
<div id="verifikasiModal" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        
        {{-- Header Modal --}}
        <div class="bg-green-600 px-6 py-4 border-b border-green-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Verifikasi Pengiriman</h3>
                        <p class="text-sm text-green-100 opacity-90">Konfirmasi verifikasi pengiriman yang diterima</p>
                    </div>
                </div>
                <button type="button" onclick="closeVerifikasiModal()" 
                        class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6 max-h-[calc(90vh-180px)] overflow-y-auto">
            
            {{-- Informasi Pengiriman --}}
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold text-green-900 mb-3 flex items-center">
                    <i class="fas fa-info-circle text-green-600 mr-2"></i>
                    Informasi Pengiriman yang akan Diverifikasi
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">No. Pengiriman:</span>
                        <span class="font-medium">{{ $pengiriman->no_pengiriman ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">No. PO:</span>
                        <span class="font-medium">{{ optional($pengiriman->order)->po_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">PIC Purchasing:</span>
                        <span class="font-medium">{{ optional($pengiriman->purchasing)->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Klien:</span>
                        <span class="font-medium">{{ optional(optional($pengiriman->order)->klien)->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal Kirim:</span>
                        <span class="font-medium">{{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Qty:</span>
                        <span class="font-medium">{{ $pengiriman->total_qty_kirim ? number_format($pengiriman->total_qty_kirim, 0, ',', '.') . ' kg' : '0 kg' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Harga:</span>
                        <span class="font-medium">{{ $pengiriman->total_harga_kirim ? 'Rp ' . number_format($pengiriman->total_harga_kirim, 0, ',', '.') : 'Rp 0' }}</span>
                    </div>
                </div>
            </div>

            {{-- Detail Bahan Baku --}}
            @if($pengiriman->pengirimanDetails && $pengiriman->pengirimanDetails->count() > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
                        <i class="fas fa-boxes text-blue-600 mr-2"></i>
                        Detail Bahan Baku ({{ $pengiriman->pengirimanDetails->count() }} item)
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-blue-100">
                                    <th class="px-3 py-2 text-left font-medium text-blue-800">Bahan Baku</th>
                                    <th class="px-3 py-2 text-right font-medium text-blue-800">Qty</th>
                                    <th class="px-3 py-2 text-right font-medium text-blue-800">Harga Satuan</th>
                                    <th class="px-3 py-2 text-right font-medium text-blue-800">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengiriman->pengirimanDetails as $detail)
                                    <tr class="border-b border-blue-100">
                                        <td class="px-3 py-2">
                                            <div class="font-medium text-gray-900">
                                                @if(optional($detail->bahanBakuSupplier)->nama)
                                                    {{ $detail->bahanBakuSupplier->nama }}
                                                @elseif(optional(optional($detail->orderDetail)->bahanBakuSupplier)->nama)
                                                    {{ $detail->orderDetail->bahanBakuSupplier->nama }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                @if(optional(optional($detail->bahanBakuSupplier)->supplier)->nama)
                                                    {{ $detail->bahanBakuSupplier->supplier->nama }}
                                                @elseif(optional(optional(optional($detail->orderDetail)->bahanBakuSupplier)->supplier)->nama)
                                                    {{ $detail->orderDetail->bahanBakuSupplier->supplier->nama }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium">
                                            {{ number_format($detail->qty_kirim ?? 0, 0, ',', '.') }} kg
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium">
                                            Rp {{ number_format($detail->total_harga ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Dampak Verifikasi --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-yellow-600 mt-1 mr-3"></i>
                    <div>
                        <h5 class="text-sm font-semibold text-yellow-800 mb-1">Dampak Verifikasi</h5>
                        <ul class="text-sm text-yellow-700 space-y-1">
                            <li>• Status pengiriman akan berubah menjadi <strong>"Berhasil"</strong></li>
                            <li>• Kuantitas di Order Detail akan dikurangi sesuai qty pengiriman</li>
                            <li>• Total Qty Order akan diupdate: <strong>{{ number_format((optional($pengiriman->order)->total_qty ?? 0) - ($pengiriman->total_qty_kirim ?? 0), 0, ',', '.') }} kg</strong></li>
                            @if(optional($pengiriman->order)->status === 'dikonfirmasi')
                                <li>• Status Order akan berubah dari <strong>"Dikonfirmasi"</strong> menjadi <strong>"Diproses"</strong></li>
                            @endif
                            <li>• Data akan masuk ke riwayat pengiriman berhasil</li>
                            <li>• Proses ini tidak dapat dibatalkan</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Konfirmasi Checkbox --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <label class="flex items-start cursor-pointer">
                    <input type="checkbox" id="konfirmasiVerifikasi" class="mt-1 mr-3 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <div class="text-sm">
                        <div class="font-medium text-gray-900">Konfirmasi Verifikasi</div>
                        <div class="text-gray-600">
                            Saya telah memeriksa data pengiriman di atas dan mengkonfirmasi bahwa semua informasi sudah benar dan sesuai dengan barang yang diterima.
                        </div>
                    </div>
                </label>
            </div>

            <input type="hidden" id="pengirimanId" value="{{ $pengiriman->id }}">
        </div>

        {{-- Footer dengan Tombol Aksi --}}
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
            <button type="button" onclick="closeVerifikasiModal()" 
                    class="w-full sm:w-auto px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200 font-medium text-sm">
                <i class="fas fa-times mr-2"></i>
                Batal
            </button>
            <button type="button" onclick="submitVerifikasiPengiriman()" 
                    class="w-full sm:w-auto px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 font-medium text-sm">
                <i class="fas fa-check-circle mr-2"></i>
                Verifikasi Pengiriman
            </button>
        </div>
    </div>
</div>

<script>
// Function to close verifikasi modal
function closeVerifikasiModal() {
    if (window.parent && window.parent.closeVerifikasiModalFromDetail) {
        window.parent.closeVerifikasiModalFromDetail();
    } else {
        const modal = document.getElementById('verifikasiModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
}

// Note: submitVerifikasiPengiriman function is now defined globally in menunggu-verifikasi.blade.php

// Auto focus on confirmation checkbox when modal opens
setTimeout(() => {
    document.getElementById('konfirmasiVerifikasi').focus();
}, 100);
</script>
