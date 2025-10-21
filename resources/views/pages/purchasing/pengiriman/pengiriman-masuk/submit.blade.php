{{-- Meta CSRF Token --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Modal Konfirmasi Submit --}}
<div id="submitModal" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        
        {{-- Header Modal --}}
        <div class="bg-green-600 px-6 py-4 border-b border-green-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Konfirmasi Pengiriman</h3>
                        <p class="text-sm text-green-100 opacity-90">Pastikan data sudah benar sebelum mengajukan verifikasi</p>
                    </div>
                </div>
                <button type="button" onclick="closeSubmitModal()" 
                        class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6 max-h-[calc(90vh-180px)] overflow-y-auto">
            
            {{-- Ringkasan Pengiriman --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold text-blue-900 mb-3">Ringkasan Pengiriman</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">No. Pengiriman:</span>
                        <span class="font-medium" id="summary-no-pengiriman">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal Kirim:</span>
                        <span class="font-medium" id="summary-tanggal-kirim">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Hari Kirim:</span>
                        <span class="font-medium" id="summary-hari-kirim">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Item:</span>
                        <span class="font-medium" id="summary-total-item">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Qty:</span>
                        <span class="font-medium text-blue-600" id="summary-total-qty">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Harga:</span>
                        <span class="font-medium text-blue-600" id="summary-total-harga">-</span>
                    </div>
                </div>
            </div>

            {{-- Review Pengiriman --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold text-yellow-900 mb-3">Review Pengiriman</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Rating:</span>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center" id="summary-rating-stars">
                                {{-- Akan diisi via JavaScript --}}
                            </div>
                            <span class="text-sm font-medium" id="summary-rating-text">-</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Ulasan:</span>
                        <div class="mt-1 p-3 bg-white border border-yellow-300 rounded-lg">
                            <p class="text-sm text-gray-800" id="summary-ulasan">-</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Barang --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-3">Detail Barang</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bahan Baku</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty (kg)</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody id="summary-detail-barang" class="divide-y divide-gray-200">
                            {{-- Detail akan diisi via JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Catatan --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold text-yellow-900 mb-2">Catatan</h4>
                <p class="text-sm text-yellow-800" id="summary-catatan">-</p>
            </div>

            {{-- Peringatan --}}
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center mt-0.5">
                        <i class="fas fa-exclamation text-white text-xs"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-red-900 mb-1">Perhatian!</h4>
                        <ul class="text-sm text-red-800 space-y-1">
                            <li>• Pastikan semua informasi sudah benar</li>
                            <li>• Pengiriman akan menunggu verifikasi dari Manajer Purchasing</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
            <button type="button" onclick="closeSubmitModal()" 
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </button>
            <button type="button" onclick="confirmSubmit()" 
                    class="px-8 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-semibold shadow-md hover:shadow-lg">
                <i class="fas fa-paper-plane mr-2"></i>
                Ya, Ajukan Verifikasi
            </button>
        </div>

    </div>
</div>

<script>
// Simpan data form untuk submit
let submissionData = null;

// Populate summary dari data form
function populateSummary(formData) {
    submissionData = formData;
    
    // Basic info
    document.getElementById('summary-no-pengiriman').textContent = formData.get('no_pengiriman') || '-';
    
    const tanggalKirim = formData.get('tanggal_kirim');
    if (tanggalKirim) {
        const date = new Date(tanggalKirim + 'T00:00:00');
        document.getElementById('summary-tanggal-kirim').textContent = date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long', 
            year: 'numeric'
        });
    }
    
    document.getElementById('summary-hari-kirim').textContent = formData.get('hari_kirim') || '-';
    document.getElementById('summary-catatan').textContent = formData.get('catatan') || 'Tidak ada catatan';
    
    // Review data
    const rating = formData.get('rating');
    const ulasan = formData.get('ulasan');
    
    // Populate rating stars
    const ratingStarsContainer = document.getElementById('summary-rating-stars');
    const ratingTextElement = document.getElementById('summary-rating-text');
    const ulasanElement = document.getElementById('summary-ulasan');
    
    if (rating && rating >= 1 && rating <= 5) {
        let starsHTML = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                starsHTML += '<i class="fas fa-star text-yellow-400 text-sm"></i>';
            } else {
                starsHTML += '<i class="fas fa-star text-gray-300 text-sm"></i>';
            }
        }
        ratingStarsContainer.innerHTML = starsHTML;
        ratingTextElement.textContent = rating + ' dari 5 bintang';
    } else {
        ratingStarsContainer.innerHTML = '<span class="text-sm text-gray-500 italic">Belum ada rating</span>';
        ratingTextElement.textContent = '-';
    }
    
    if (ulasan && ulasan.trim() !== '') {
        ulasanElement.textContent = ulasan;
        ulasanElement.classList.remove('italic', 'text-gray-500');
        ulasanElement.classList.add('text-gray-800');
    } else {
        ulasanElement.textContent = 'Tidak ada ulasan';
        ulasanElement.classList.add('italic', 'text-gray-500');
        ulasanElement.classList.remove('text-gray-800');
    }
    
    // Detail barang
    const detailContainer = document.getElementById('summary-detail-barang');
    detailContainer.innerHTML = '';
    
    let totalItems = 0;
    let totalQty = 0;
    let totalHarga = 0;
    
    // Loop through form data untuk detail
    const details = [];
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('details[') && key.includes('][bahan_baku_supplier_id]')) {
            const index = key.match(/\[(\d+)\]/)[1];
            if (!details[index]) details[index] = {};
            // Bahan baku name akan di-set dari teks yang tampil di form
        } else if (key.startsWith('details[') && key.includes('][qty_kirim]')) {
            const index = key.match(/\[(\d+)\]/)[1];
            if (!details[index]) details[index] = {};
            details[index].qty = parseFloat(value) || 0;
        } else if (key.startsWith('details[') && key.includes('][harga_satuan]')) {
            const index = key.match(/\[(\d+)\]/)[1];
            if (!details[index]) details[index] = {};
            details[index].harga = parseFloat(value) || 0;
        } else if (key.startsWith('details[') && key.includes('][total_harga]')) {
            const index = key.match(/\[(\d+)\]/)[1];
            if (!details[index]) details[index] = {};
            details[index].total = parseFloat(value) || 0;
        }
    }
    
    // Get bahan baku names from the DOM elements di aksi modal
    const detailRows = parent.document.querySelectorAll('.detail-item');
    detailRows.forEach((row, index) => {
        if (details[index]) {
            const bahanBakuEl = row.querySelector('.text-sm.font-medium.text-gray-900');
            if (bahanBakuEl) {
                details[index].bahan_baku = bahanBakuEl.textContent.trim();
            }
        }
    });
    
    // Populate table
    details.forEach(detail => {
        if (detail.qty > 0) {
            totalItems++;
            totalQty += detail.qty;
            totalHarga += detail.total;
            
            const row = `
                <tr>
                    <td class="px-3 py-2">${detail.bahan_baku || 'Unknown'}</td>
                    <td class="px-3 py-2">${detail.qty.toLocaleString('id-ID')} kg</td>
                    <td class="px-3 py-2">Rp ${detail.harga.toLocaleString('id-ID')}</td>
                    <td class="px-3 py-2">Rp ${detail.total.toLocaleString('id-ID')}</td>
                </tr>
            `;
            detailContainer.insertAdjacentHTML('beforeend', row);
        }
    });
    
    // Update totals
    document.getElementById('summary-total-item').textContent = totalItems + ' item';
    document.getElementById('summary-total-qty').textContent = totalQty.toLocaleString('id-ID') + ' kg';
    document.getElementById('summary-total-harga').textContent = 'Rp ' + totalHarga.toLocaleString('id-ID');
}

// Fungsi closeSubmitModal dan confirmSubmit sudah dipindahkan ke global scope di pengiriman-masuk.blade.php

// Initialize when modal is shown
document.addEventListener('DOMContentLoaded', function() {
    // Modal akan di-populate oleh fungsi showSubmitModal dari detail.blade.php
});
</script>
