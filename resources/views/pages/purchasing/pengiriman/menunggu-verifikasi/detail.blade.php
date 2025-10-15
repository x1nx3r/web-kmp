{{-- Modal Header - Sticky --}}
<div class="sticky top-0 z-10 flex items-center justify-between p-6 border-b border-gray-200 bg-yellow-600 rounded-t-xl">
    <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center shadow-sm">
            <i class="fas fa-check-circle text-yellow-600 text-xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white">Detail Verifikasi</h3>
            <p class="text-sm text-yellow-100 opacity-90">{{ $pengiriman->no_pengiriman ?? 'No Pengiriman' }}</p>
        </div>
    </div>
    <button type="button" onclick="closeAksiVerifikasiModal()" 
            class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
        <i class="fas fa-times text-xl"></i>
    </button>
</div>

{{-- Modal Content - Scrollable --}}
<div class="overflow-y-auto max-h-[calc(90vh-160px)] p-6 space-y-6">

    {{-- Card 1: Data PO & PIC Purchasing --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-file-alt text-yellow-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Data PO & PIC Purchasing</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. PO</label>
                <input type="text" 
                       value="{{ optional($pengiriman->purchaseOrder)->no_po ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PIC Purchasing</label>
                <input type="text" 
                       value="{{ optional($pengiriman->purchasing)->nama ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kuantitas PO</label>
                <input type="text" 
                       value="{{ optional($pengiriman->purchaseOrder)->qty_total ? number_format($pengiriman->purchaseOrder->qty_total, 0, ',', '.') . ' KG' : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total PO</label>
                <input type="text" 
                       value="{{ optional($pengiriman->purchaseOrder)->total_amount ? 'Rp ' . number_format($pengiriman->purchaseOrder->total_amount, 0, ',', '.') : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
        </div>
    </div>

    {{-- Card 2: Data Forecasting --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-chart-line text-green-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Data Forecasting</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->no_forecast ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->tanggal_forecast ? $pengiriman->forecast->tanggal_forecast->format('d M Y') : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hari Kirim Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->hari_kirim_forecast ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Qty Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->total_qty_forecast ? number_format($pengiriman->forecast->total_qty_forecast, 0, ',', '.') . ' kg' : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->total_harga_forecast ? 'Rp ' . number_format($pengiriman->forecast->total_harga_forecast, 0, ',', '.') : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
        </div>
    </div>

    {{-- Card 3: Data Klien --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-building text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Data Klien</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Klien</label>
                <input type="text" 
                       value="{{ optional(optional($pengiriman->purchaseOrder)->klien)->nama ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                <input type="text" 
                       value="{{ optional(optional($pengiriman->purchaseOrder)->klien)->cabang ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
        </div>
    </div>

    {{-- Card 4: Data Pengiriman --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-truck text-yellow-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Data Pengiriman</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. Pengiriman</label>
                <input type="text" 
                       value="{{ $pengiriman->no_pengiriman ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kirim</label>
                <input type="text" 
                       value="{{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d M Y') : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hari Kirim</label>
                <input type="text" 
                       value="{{ $pengiriman->hari_kirim ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-600 text-white">
                        <i class="fas fa-clock mr-1"></i>
                        Menunggu Verifikasi
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Qty Dikirim</label>
                <input type="text" 
                       value="{{ $pengiriman->total_qty_kirim ? number_format($pengiriman->total_qty_kirim, 0, ',', '.') . ' kg' : '0 kg' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga Pengiriman</label>
                <input type="text" 
                       value="{{ $pengiriman->total_harga_kirim ? 'Rp ' . number_format($pengiriman->total_harga_kirim, 0, ',', '.') : 'Rp 0' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
        </div>
    </div>

    {{-- Card 5: Detail Bahan Baku --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-boxes text-purple-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Detail Bahan Baku</h3>
        </div>
        
        @if($pengiriman->pengirimanDetails && $pengiriman->pengirimanDetails->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Dikirim</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pengiriman->pengirimanDetails as $detail)
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        @if(optional($detail->bahanBakuSupplier)->nama)
                                            {{ $detail->bahanBakuSupplier->nama }}
                                        @elseif(optional(optional($detail->purchaseOrderBahanBaku)->bahanBakuSupplier)->nama)
                                            {{ $detail->purchaseOrderBahanBaku->bahanBakuSupplier->nama }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if(optional(optional($detail->bahanBakuSupplier)->supplier)->nama)
                                            {{ $detail->bahanBakuSupplier->supplier->nama }}
                                        @elseif(optional(optional(optional($detail->purchaseOrderBahanBaku)->bahanBakuSupplier)->supplier)->nama)
                                            {{ $detail->purchaseOrderBahanBaku->bahanBakuSupplier->supplier->nama }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($detail->qty_kirim ?? 0, 0, ',', '.') }} kg
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($detail->total_harga ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-box-open text-gray-300 text-3xl mb-2"></i>
                <p>Tidak ada detail bahan baku</p>
            </div>
        @endif
    </div>

    {{-- Card 6: Bukti Foto Bongkar --}}
    @if($pengiriman->bukti_foto_bongkar)
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-camera text-indigo-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Bukti Foto Bongkar</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $photos = [];
                    if ($pengiriman->bukti_foto_bongkar) {
                        if (is_array($pengiriman->bukti_foto_bongkar)) {
                            $photos = $pengiriman->bukti_foto_bongkar;
                        } elseif (is_string($pengiriman->bukti_foto_bongkar)) {
                            try {
                                $decoded = json_decode($pengiriman->bukti_foto_bongkar, true);
                                $photos = is_array($decoded) ? $decoded : [];
                            } catch (\Exception $e) {
                                $photos = [];
                            }
                        }
                    }
                @endphp
                @if(is_array($photos) && count($photos) > 0)
                    @foreach($photos as $photo)
                        @if($photo)
                            <div class="relative group">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($photo) }}" 
                                     alt="Bukti Foto Bongkar" 
                                     class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
                                     onclick="openImageModal('{{ \Illuminate\Support\Facades\Storage::url($photo) }}')"
                                     onerror="this.src='/images/placeholder.png'; this.alt='Image not found';">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity rounded-lg flex items-center justify-center">
                                    <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity text-2xl"></i>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-image text-gray-300 text-3xl mb-2"></i>
                        <p>Tidak ada foto bukti bongkar</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Card 7: Catatan --}}
    @if($pengiriman->catatan)
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-sticky-note text-orange-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Catatan</h3>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700">{{ $pengiriman->catatan }}</p>
            </div>
        </div>
    @endif
</div>

{{-- Modal Footer - Sticky --}}
<div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-xl">
    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-4 space-y-2 space-y-reverse sm:space-y-0">
        <button type="button" onclick="closeAksiVerifikasiModal()" 
                class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 font-medium">
            <i class="fas fa-times mr-2"></i>
            Tutup
        </button>
        <button type="button" onclick="openRevisiModalFromDetail()" 
                class="w-full sm:w-auto px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200 font-medium">
            <i class="fas fa-edit mr-2"></i>
            Revisi
        </button>
        <button type="button" onclick="openVerifikasiModalFromDetail()" 
                class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 font-medium">
            <i class="fas fa-check-circle mr-2"></i>
            Verifikasi
        </button>
    </div>
</div>

{{-- Image Modal --}}
<div id="imageModal" class="fixed inset-0 z-50 hidden" style="background-color: rgba(0,0,0,0.9);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative max-w-4xl max-h-full">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <img id="modalImage" src="" alt="Preview" class="max-w-full max-h-screen object-contain">
        </div>
    </div>
</div>

<script>
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Note: Fungsi openRevisiModalFromDetail dan openVerifikasiModalFromDetail 
// sudah didefinisikan secara global di menunggu-verifikasi.blade.php
</script>
