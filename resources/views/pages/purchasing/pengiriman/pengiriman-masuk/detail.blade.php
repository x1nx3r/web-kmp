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
                           value="{{ $pengiriman->purchaseOrder->no_po ?? '-' }}" 
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
                           value="{{ $pengiriman->purchaseOrder->qty_total ?  number_format($pengiriman->purchaseOrder->qty_total, 0, ',', '.') . ' KG': '-' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                           readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total PO</label>
                    <input type="text" 
                           value="{{ $pengiriman->purchaseOrder->total_amount ? 'Rp ' . number_format($pengiriman->purchaseOrder->total_amount, 0, ',', '.') : '-' }}" 
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Pengiriman <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="no_pengiriman" 
                               id="no_pengiriman"
                               value="{{ old('no_pengiriman', $pengiriman->no_pengiriman ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Masukkan nomor pengiriman" 
                               required>
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
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
        <div class="bg-amber-200 border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-sticky-note text-purple-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Catatan Pengiriman</h3>
            </div>
            
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                @if($pengiriman->catatan)
                    <p class="text-sm text-gray-700 whitespace-pre-wrap ">{{ $pengiriman->catatan }}</p>
                @else
                    <p class="text-sm text-gray-500 italic">Tidak ada catatan untuk pengiriman ini.</p>
                @endif
            </div>
            {{-- Hidden input for catatan --}}
            <input type="hidden" name="catatan" value="{{ $pengiriman->catatan ?? '' }}">
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
    fetch(`/purchasing/pengiriman/batal-modal?pengiriman_id=${pengirimanId}`)
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

// Submit pengiriman
function submitPengiriman() {
    const form = document.getElementById('pengirimanForm');
    const formData = new FormData(form);
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Check if detail exists (should already exist from database)
    const detailItems = document.querySelectorAll('.detail-item');
    if (detailItems.length === 0) {
        alert('Tidak ada detail barang untuk dikirim');
        return;
    }
    
    // Validate all qty inputs are filled and > 0
    let hasValidQty = false;
    for (let item of detailItems) {
        const qtyInput = item.querySelector('input[name*="[qty_kirim]"]');
        if (qtyInput && parseFloat(qtyInput.value) > 0) {
            hasValidQty = true;
            break;
        }
    }
    
    if (!hasValidQty) {
        alert('Minimal satu barang harus memiliki qty kirim > 0');
        return;
    }
    
    // Add totals to form data
    const totalQty = document.querySelectorAll('.detail-item').reduce((sum, item) => {
        const qtyInput = item.querySelector('input[name*="[qty_kirim]"]');
        return sum + (parseFloat(qtyInput.value) || 0);
    }, 0);
    
    const totalHarga = document.querySelectorAll('.detail-item').reduce((sum, item) => {
        const totalInput = item.querySelector('input[name*="[total_harga]"]');
        return sum + (parseFloat(totalInput.value) || 0);
    }, 0);
    
    formData.append('total_qty_kirim', totalQty);
    formData.append('total_harga_kirim', totalHarga);
    
    // Show confirmation modal (submit.blade.php)
    showSubmitModal(formData);
}

// Show submit confirmation modal
function showSubmitModal(formData) {
    const pengirimanId = formData.get('pengiriman_id');
    console.log('Loading submit modal for pengiriman ID:', pengirimanId);
    
    // Load submit modal content with pengiriman_id parameter
    fetch(`/purchasing/pengiriman/submit-modal?pengiriman_id=${pengirimanId}`)
    .then(response => {
        console.log('Submit modal response status:', response.status);
        return response.text();
    })
    .then(html => {
        console.log('Submit modal HTML received, length:', html.length);
        
        // Create and show submit modal
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = html;
        document.body.appendChild(modalContainer);
        
        console.log('Modal container added to body');
        
        // Find and populate the modal immediately
        const submitModal = modalContainer.querySelector('#submitModal');
        if (submitModal) {
            console.log('Submit modal found, populating data...');
            // Populate data directly
            populateModalSummary(formData, submitModal);
            
            // Make sure modal is visible
            submitModal.style.display = 'flex';
        } else {
            console.error('Submit modal not found in HTML');
        }
        
        // Close current modal after successful modal creation
        setTimeout(() => {
            closeAksiModal();
        }, 200);
    })
    .catch(error => {
        console.error('Error loading submit modal:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: 'Gagal memuat modal konfirmasi: ' + error.message,
                icon: 'error',
                confirmButtonColor: '#EF4444'
            });
        } else {
            alert('Gagal memuat modal konfirmasi: ' + error.message);
        }
    });
}

// Populate modal summary (local version)
function populateModalSummary(formData, modalElement) {
    console.log('Populating modal summary with formData:', formData);
    
    // Tunggu sebentar untuk memastikan modal sudah ter-render
    setTimeout(() => {
        // Basic info
        const setPengirimam = modalElement.querySelector('#summary-no-pengiriman');
        if (setPengirimam) {
            setPengirimam.textContent = formData.get('no_pengiriman') || '-';
            console.log('Set no pengiriman:', formData.get('no_pengiriman'));
        }
        
        const tanggalKirim = formData.get('tanggal_kirim');
        if (tanggalKirim) {
            const date = new Date(tanggalKirim + 'T00:00:00');
            const setTanggal = modalElement.querySelector('#summary-tanggal-kirim');
            if (setTanggal) {
                setTanggal.textContent = date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long', 
                    year: 'numeric'
                });
                console.log('Set tanggal kirim:', setTanggal.textContent);
            }
        }
        
        const setHari = modalElement.querySelector('#summary-hari-kirim');
        if (setHari) {
            setHari.textContent = formData.get('hari_kirim') || '-';
            console.log('Set hari kirim:', formData.get('hari_kirim'));
        }
        
        const setCatatan = modalElement.querySelector('#summary-catatan');
        if (setCatatan) {
            setCatatan.textContent = formData.get('catatan') || 'Tidak ada catatan';
            console.log('Set catatan:', formData.get('catatan'));
        }        // Detail barang
        const detailContainer = modalElement.querySelector('#summary-detail-barang');
        console.log('Detail container found:', detailContainer);
        
        if (detailContainer) {
            detailContainer.innerHTML = '';
            
            let totalItems = 0;
            let totalQty = 0;
            let totalHarga = 0;
            
            // Get detail from current form
            const detailRows = document.querySelectorAll('.detail-item');
            console.log('Detail rows found:', detailRows.length);
            
            detailRows.forEach((row, index) => {
                const qtyInput = row.querySelector('input[name*="[qty_kirim]"]');
                const hargaInput = row.querySelector('input[name*="[harga_satuan]"]');
                const totalInput = row.querySelector('input[name*="[total_harga]"]');
                const bahanBakuEl = row.querySelector('.text-sm.font-medium.text-gray-900');
                
                const qty = parseFloat(qtyInput?.value) || 0;
                const harga = parseFloat(hargaInput?.value) || 0;
                const total = parseFloat(totalInput?.value) || 0;
                const bahanBaku = bahanBakuEl?.textContent.trim() || 'Unknown';
                
                console.log(`Row ${index}: ${bahanBaku}, qty: ${qty}, harga: ${harga}, total: ${total}`);
                
                if (qty > 0) {
                    totalItems++;
                    totalQty += qty;
                    totalHarga += total;
                    
                    const rowHtml = `
                        <tr class="border-b">
                            <td class="px-3 py-2 text-sm">${bahanBaku}</td>
                            <td class="px-3 py-2 text-sm">${qty.toLocaleString('id-ID')} kg</td>
                            <td class="px-3 py-2 text-sm">Rp ${harga.toLocaleString('id-ID')}</td>
                            <td class="px-3 py-2 text-sm font-semibold">Rp ${total.toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                    detailContainer.insertAdjacentHTML('beforeend', rowHtml);
                }
            });
            
            console.log(`Totals - Items: ${totalItems}, Qty: ${totalQty}, Harga: ${totalHarga}`);
            
            // Update totals
            const setTotalItem = modalElement.querySelector('#summary-total-item');
            if (setTotalItem) {
                setTotalItem.textContent = totalItems + ' item';
                console.log('Set total item:', totalItems);
            }
            
            const setTotalQty = modalElement.querySelector('#summary-total-qty');
            if (setTotalQty) {
                setTotalQty.textContent = totalQty.toLocaleString('id-ID') + ' kg';
                console.log('Set total qty:', totalQty);
            }
            
            const setTotalHarga = modalElement.querySelector('#summary-total-harga');
            if (setTotalHarga) {
                setTotalHarga.textContent = 'Rp ' + totalHarga.toLocaleString('id-ID');
                console.log('Set total harga:', totalHarga);
            }
        }        // Setup submission data in the modal's script context
        const script = modalElement.querySelector('script');
        if (script) {
            // Inject formData into the modal's scope
            const formDataEntries = Array.from(formData.entries()).map(([key, value]) => 
                `submissionData.append('${key}', '${value}');`
            ).join(' ');
            
            script.textContent = script.textContent.replace(
                'let submissionData = null;',
                `let submissionData = new FormData(); ${formDataEntries}`
            );
            console.log('Injected submission data into modal script');
        }
        
    }, 100); // Delay 100ms untuk memastikan DOM sudah siap
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
});
</script>
