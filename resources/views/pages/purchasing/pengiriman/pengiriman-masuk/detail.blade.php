{{-- Meta tags untuk CSRF --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Modal Header - Sticky --}}
<div class="sticky top-0 z-10 flex items-center justify-between p-6 border-b border-gray-200 bg-blue-600 rounded-t-xl">
    <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center shadow-sm">
            <i class="fas fa-truck text-blue-600 text-xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white">Pengiriman Masuk</h3>
            <p class="text-sm text-blue-100 opacity-90">Masukkan Data dengan Benar</p>
        </div>
    </div>
    <button type="button" onclick="closeAksiModal()" 
            class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
        <i class="fas fa-times text-xl"></i>
    </button>
</div>

{{-- Modal Content - Scrollable --}}
<div class="overflow-y-auto max-h-[calc(90vh-160px)] p-6 space-y-6">

    <form id="pengirimanForm" method="POST" action="{{ route('purchasing.pengiriman.submit') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="pengiriman_id" value="{{ $pengiriman->id }}">
        
        {{-- Card 1: Data PO & PIC Purchasing --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-file-alt text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Data PO & PIC Purchasing</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No. PO</label>
                    <input type="text" 
                           value="{{ $pengiriman->order->po_number ?? '-' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">PIC Purchasing</label>
                    <input type="text" 
                           value="{{ $pengiriman->purchasing->nama ?? '-' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kuantitas PO</label>
                    <input type="text" 
                           value="{{ $pengiriman->order->total_qty ?  number_format($pengiriman->order->total_qty, 0, ',', '.') . ' KG': '-' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total PO</label>
                    <input type="text" 
                           value="{{ $pengiriman->order->total_amount ? 'Rp ' . number_format($pengiriman->order->total_amount, 0, ',', '.') : '-' }}" 
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
                           value="{{ $pengiriman->forecast->no_forecast ?? '-' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Forecast</label>
                    <input type="text" 
                           value="{{ $pengiriman->forecast->tanggal_forecast ? $pengiriman->forecast->tanggal_forecast->format('d M Y') : '-' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hari Kirim Forecast</label>
                    <input type="text" 
                           value="{{ $pengiriman->forecast->hari_kirim_forecast ?? '-' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Qty Forecast</label>
                    <input type="text" 
                           value="{{ $pengiriman->forecast ? number_format($pengiriman->forecast->total_qty_forecast, 0, ',', '.') . ' kg' : '-' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                           readonly>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga Forecast</label>
                    <input type="text" 
                           value="{{ $pengiriman->forecast ? 'Rp ' . number_format($pengiriman->forecast->total_harga_forecast, 0, ',', '.') : '-' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                           readonly>
                </div>
            </div>
        </div>

        {{-- Card 3: Form Input Pengiriman & Detail --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-shipping-fast text-orange-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Form Input Pengiriman & Detail</h3>
            </div>
            
            {{-- Input Pengiriman --}}
            <div class="mb-6">
                <h4 class="text-md font-semibold text-gray-800 mb-4">Data Pengiriman</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Pengiriman</label>
                        <input type="text" 
                               name="no_pengiriman_display" 
                               id="no_pengiriman_display"
                               value="{{ $pengiriman->no_pengiriman ?? 'Auto Generate' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed"
                               readonly
                               placeholder="Akan di-generate otomatis">
                       
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kirim <span class="text-red-500">*</span></label>
                        <input type="date" 
                               name="tanggal_kirim" 
                               id="tanggal_kirim"
                               value="{{ old('tanggal_kirim', $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('Y-m-d') : '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               onchange="updateHariKirim()"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hari Kirim</label>
                        <input type="text" 
                               name="hari_kirim" 
                               id="hari_kirim"
                               value="{{ old('hari_kirim', $pengiriman->hari_kirim ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                               readonly
                               placeholder="Akan terisi otomatis">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Qty Kirim</label>
                        <input type="text" 
                               name="total_qty_kirim_display" 
                               id="total_qty_kirim_display"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                               readonly>
                        <input type="hidden" name="total_qty_kirim" id="total_qty_kirim">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga Kirim</label>
                        <input type="text" 
                               name="total_harga_kirim_display" 
                               id="total_harga_kirim_display"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                               readonly>
                        <input type="hidden" name="total_harga_kirim" id="total_harga_kirim">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Foto Bongkar</label>
                        <input type="file" 
                               name="bukti_foto_bongkar" 
                               id="bukti_foto_bongkar"
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @if($pengiriman->bukti_foto_bongkar)
                            <div class="mt-2 space-y-1">
                                <a href="{{ asset('storage/pengiriman/bukti/' . $pengiriman->bukti_foto_bongkar) }}" 
                                   target="_blank"
                                   class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                                    <i class="fas fa-eye mr-1"></i>
                                    Lihat foto saat ini
                                </a>
                                @if($pengiriman->bukti_foto_bongkar_uploaded_at)
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $pengiriman->bukti_foto_bongkar_uploaded_at->diffForHumans() }}
                                        <span class="text-gray-400">({{ $pengiriman->bukti_foto_bongkar_uploaded_at->format('d/m/Y H:i') }})</span>
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- Info: Foto tanda terima diupload di tab menunggu verifikasi --}}
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-800 mb-1">Informasi Upload Foto Tanda Terima</h4>
                            <p class="text-xs text-blue-700">
                                Upload foto tanda terima dilakukan di tab <strong>Menunggu Verifikasi</strong> setelah pengiriman disubmit. 
                                Anda dapat mengupload foto langsung pada kartu pengiriman di tab tersebut.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Pengiriman --}}
            <div>
                <div class="mb-4">
                    <h4 class="text-md font-semibold text-gray-800">Detail Barang Pengiriman</h4>
                    <p class="text-sm text-gray-600 mt-1">Data barang yang akan dikirim (hanya qty yang dapat diubah)</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-300 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Bahan Baku</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Supplier</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Qty Kirim (kg)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Harga Satuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody id="pengirimanDetailContainer" class="bg-white divide-y divide-gray-200">
                            @forelse($pengiriman->pengirimanDetails ?? [] as $index => $detail)
                                <tr class="detail-item" data-index="{{ $index }}">
                                    <td class="px-4 py-3 border-b">
                                        <input type="hidden" name="details[{{ $index }}][bahan_baku_supplier_id]" value="{{ $detail->bahan_baku_supplier_id }}">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $detail->bahanBakuSupplier->nama ?? 'Unknown Bahan Baku' }}
                                        </div>
                                       
                                    </td>
                                    <td class="px-4 py-3 border-b">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $detail->bahanBakuSupplier->supplier->nama ?? 'Unknown Supplier' }}
                                        </div>
                                      
                                    </td>
                                    <td class="px-4 py-3 border-b">                        <input type="number" 
                               name="details[{{ $index }}][qty_kirim]" 
                               value="{{ old('details.' . $index . '.qty_kirim', $detail->qty_kirim ?? 0) }}"
                               class="qty-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               step="0.01" min="0" 
                               onchange="calculateSubtotal({{ $index }})"
                               oninput="calculateSubtotal({{ $index }})"
                               required>
                                    </td>
                                    <td class="px-4 py-3 border-b">
                                        @php
                                            // Get harga terbaru dari riwayat harga atau harga_per_satuan
                                            $latestHarga = $detail->bahanBakuSupplier->riwayatHarga->first();
                                            $hargaSatuan = $latestHarga ? $latestHarga->harga_baru : ($detail->bahanBakuSupplier->harga_per_satuan ?? 0);
                                        @endphp
                                        <input type="number" 
                                               name="details[{{ $index }}][harga_satuan]" 
                                               value="{{ $hargaSatuan }}"
                                               class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm cursor-not-allowed" 
                                               readonly>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Rp {{ number_format($hargaSatuan, 0, ',', '.') }}/{{ $detail->bahanBakuSupplier->satuan ?? 'kg' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b">
                                        @php
                                            $totalHarga = ($detail->qty_kirim ?? 0) * $hargaSatuan;
                                        @endphp
                                        <input type="number" 
                                               name="details[{{ $index }}][total_harga]" 
                                               value="{{ $totalHarga }}"
                                               class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm cursor-not-allowed" 
                                               readonly>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Rp {{ number_format($totalHarga, 0, ',', '.') }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                        <p>Belum ada detail barang pengiriman</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Total Summary --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total Qty Kirim:</span>
                            <span class="text-lg font-bold text-blue-600" id="totalQtyKirim">0 kg</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total Harga Kirim:</span>
                            <span class="text-lg font-bold text-blue-600" id="totalHargaKirim">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Catatan Pengiriman --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-sticky-note text-purple-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Catatan Pengiriman</h3>
            </div>
            
            {{-- Catatan Sebelumnya --}}
            <div class="mb-6">
                <h4 class="text-md font-semibold text-gray-800 mb-3">Catatan Sebelumnya</h4>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    @if($pengiriman->catatan)
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $pengiriman->catatan }}</p>
                    @else
                        <p class="text-sm text-gray-500 italic">Tidak ada catatan sebelumnya untuk pengiriman ini.</p>
                    @endif
                </div>
            </div>
            
            {{-- Input Catatan Baru --}}
            <div>
                <h4 class="text-md font-semibold text-gray-800 mb-3">Tambah/Update Catatan</h4>
                <div class="space-y-3">
                    <textarea name="catatan" 
                              id="catatan_pengiriman"
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-none transition-colors"
                              placeholder="Tambahkan catatan baru atau update catatan pengiriman..."
                              maxlength="500"
                              data-original-catatan="{{ $pengiriman->catatan ?? '' }}">{{ old('catatan', $pengiriman->catatan ?? '') }}</textarea>
                    
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>
                            <i class="fas fa-info-circle mr-1"></i>
                            Catatan akan menggantikan catatan sebelumnya jika diisi
                        </span>
                        <span id="catatanCounter">0/500 karakter</span>
                    </div>
                    
                    {{-- Quick Actions --}}
                    <div class="flex space-x-2">
                        <button type="button" 
                                onclick="clearCatatan()" 
                                class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                            <i class="fas fa-eraser mr-1"></i>
                            Kosongkan
                        </button>
                        <button type="button" 
                                onclick="resetToOriginal()" 
                                class="px-3 py-1.5 text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors">
                            <i class="fas fa-undo mr-1"></i>
                            Reset ke Asli
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>
{{-- End Modal Content --}}

{{-- Footer - Sticky --}}
<div class="sticky bottom-0 bg-white z-10 flex justify-between items-center p-6 border-t border-gray-200 rounded-b-xl">
    <div class="flex space-x-3">
        <button type="button" onclick="closeAksiModal()" 
                class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors font-medium">
            <i class="fas fa-times mr-2"></i>
            Tutup
        </button>
        <button type="button" onclick="openBatalModal()" 
                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
            <i class="fas fa-ban mr-2"></i>
            Jadikan Pengiriman Batal
        </button>
    </div>
    <button type="button" onclick="submitPengiriman()" 
            class="px-8 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-semibold shadow-md hover:shadow-lg">
        <i class="fas fa-paper-plane mr-2"></i>
        Ajukan Verifikasi
    </button>
</div>

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- JavaScript khusus untuk modal aksi --}}
<script>
// Open batal modal
function openBatalModal() {
    const pengirimanId = document.querySelector('input[name="pengiriman_id"]').value;
    console.log('Loading batal modal for pengiriman ID:', pengirimanId);
    
    // Load batal modal content with pengiriman_id parameter
    fetch(`/procurement/pengiriman/batal-modal?pengiriman_id=${pengirimanId}`)
    .then(response => {
        console.log('Batal modal response status:', response.status);
        return response.text();
    })
    .then(html => {
        console.log('Batal modal HTML received, length:', html.length);
        
        // Create and show batal modal
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = html;
        document.body.appendChild(modalContainer);
        
        console.log('Batal modal container added to body');
        
        // Find and show the modal
        const batalModal = modalContainer.querySelector('#batalModal');
        if (batalModal) {
            console.log('Batal modal found, showing...');
            // Make sure modal is visible
            batalModal.style.display = 'flex';
        } else {
            console.error('Batal modal not found in HTML');
        }
    })
    .catch(error => {
        console.error('Error loading batal modal:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: 'Gagal memuat modal pembatalan: ' + error.message,
                icon: 'error',
                confirmButtonColor: '#EF4444'
            });
        } else {
            alert('Gagal memuat modal pembatalan: ' + error.message);
        }
    });
}

// Update hari kirim berdasarkan tanggal (mirip seperti di modal forecasting)
function updateHariKirim() {
    const deliveryDate = document.getElementById('tanggal_kirim').value;
    if (deliveryDate) {
        const targetDate = new Date(deliveryDate);
        
        // Array nama hari dalam bahasa Indonesia
        const hariIndonesia = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const namaHari = hariIndonesia[targetDate.getDay()];
        
        document.getElementById('hari_kirim').value = namaHari;
    } else {
        document.getElementById('hari_kirim').value = '';
    }
}

// Hitung subtotal
function calculateSubtotal(index) {
    const qtyInput = document.querySelector(`input[name="details[${index}][qty_kirim]"]`);
    const hargaInput = document.querySelector(`input[name="details[${index}][harga_satuan]"]`);
    const totalInput = document.querySelector(`input[name="details[${index}][total_harga]"]`);
    
    if (qtyInput && hargaInput && totalInput) {
        const qty = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const total = qty * harga;
        
        // Update total input
        totalInput.value = total.toFixed(2);
        
        // Update display di bawahnya
        const totalDisplay = totalInput.parentElement.querySelector('.text-xs.text-gray-500');
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }
        
        // Update totals keseluruhan
        updateTotals();
    }
}

// Update total keseluruhan
function updateTotals() {
    let totalQty = 0;
    let totalHarga = 0;
    
    document.querySelectorAll('.detail-item').forEach(item => {
        const qtyInput = item.querySelector('input[name*="[qty_kirim]"]');
        const totalInput = item.querySelector('input[name*="[total_harga]"]');
        
        if (qtyInput && totalInput) {
            const qty = parseFloat(qtyInput.value) || 0;
            const harga = parseFloat(totalInput.value) || 0;
            
            totalQty += qty;
            totalHarga += harga;
        }
    });
    
    // Update tampilan summary
    const totalQtyEl = document.getElementById('totalQtyKirim');
    const totalHargaEl = document.getElementById('totalHargaKirim');
    
    if (totalQtyEl) {
        totalQtyEl.textContent = new Intl.NumberFormat('id-ID', { 
            minimumFractionDigits: 0, 
            maximumFractionDigits: 2 
        }).format(totalQty) + ' kg';
    }
    
    if (totalHargaEl) {
        totalHargaEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga);
    }
    
    // Update hidden dan display inputs
    const totalQtyHidden = document.getElementById('total_qty_kirim');
    const totalHargaHidden = document.getElementById('total_harga_kirim');
    const totalQtyDisplay = document.getElementById('total_qty_kirim_display');
    const totalHargaDisplay = document.getElementById('total_harga_kirim_display');
    
    if (totalQtyHidden) totalQtyHidden.value = totalQty;
    if (totalHargaHidden) totalHargaHidden.value = totalHarga;
    if (totalQtyDisplay) totalQtyDisplay.value = new Intl.NumberFormat('id-ID').format(totalQty) + ' kg';
    if (totalHargaDisplay) totalHargaDisplay.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga);
}

// Note: Functions updateCatatanCounter(), clearCatatan(), and resetToOriginal() 
// are defined globally in pengiriman-masuk.blade.php parent file

// Submit pengiriman - menggunakan fungsi global dari pengiriman-masuk blade
function submitPengiriman() {
    const form = document.getElementById('pengirimanForm');
    
    // Validasi form
    if (!form.checkValidity()) {
        // Trigger HTML5 validation
        form.reportValidity();
        return;
    }
    
    // Validasi tanggal kirim
    const tanggalKirim = document.getElementById('tanggal_kirim').value;
    if (!tanggalKirim) {
        Swal.fire({
            title: 'Validation Error!',
            text: 'Tanggal kirim harus diisi',
            icon: 'warning',
            confirmButtonColor: '#EF4444'
        });
        return;
    }
    
    // Validasi minimal 1 detail dengan qty > 0
    const qtyInputs = document.querySelectorAll('.qty-input');
    let hasValidQty = false;
    
    qtyInputs.forEach(input => {
        if (parseFloat(input.value) > 0) {
            hasValidQty = true;
        }
    });
    
    if (!hasValidQty) {
        Swal.fire({
            title: 'Validation Error!',
            text: 'Minimal harus ada 1 item dengan quantity > 0',
            icon: 'warning',
            confirmButtonColor: '#EF4444'
        });
        return;
    }
    
    // Konfirmasi submit
    Swal.fire({
        title: 'Konfirmasi Pengajuan',
        html: 'Apakah Anda yakin ingin mengajukan verifikasi pengiriman ini?<br><small class="text-gray-600">Nomor pengiriman akan di-generate otomatis</small>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#EF4444',
        confirmButtonText: 'Ya, Ajukan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit form menggunakan fungsi global
            if (typeof submitForm === 'function') {
                submitForm('pengirimanForm', 'Mengajukan pengiriman...');
            } else {
                // Fallback jika fungsi global tidak ada
                form.submit();
            }
        }
    });
}

// Initialize pengiriman modal (mirip seperti di modal forecasting)
document.addEventListener('DOMContentLoaded', function() {
    // Calculate initial subtotals
    document.querySelectorAll('.detail-item').forEach((item) => {
        const index = item.getAttribute('data-index');
        if (index !== null && index !== undefined) {
            calculateSubtotal(index);
        }
    });
    
    // Initial hari kirim update
    updateHariKirim();
    
    // Update overall totals
    updateTotals();
    
    // Initialize catatan counter
    const catatanTextarea = document.getElementById('catatan_pengiriman');
    if (catatanTextarea) {
        // Set up event listener untuk real-time counter update
        catatanTextarea.addEventListener('input', updateCatatanCounter);
        catatanTextarea.addEventListener('keyup', updateCatatanCounter);
        
        // Initialize counter
        updateCatatanCounter();
        
        // Auto-resize textarea based on content
        catatanTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
});
</script>
