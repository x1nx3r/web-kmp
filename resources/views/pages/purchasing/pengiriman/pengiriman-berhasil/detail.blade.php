{{-- Modal Detail Pengiriman Berhasil --}}
<div id="detailPengirimanModalBerhasil" class="fixed inset-0 backdrop-blur-xs bg-opacity-50 items-center justify-center z-50 hidden" style="display: none;">
    <div class="bg-white rounded-xl shadow-2xl w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 max-w-4xl h-[90vh] flex flex-col">
        {{-- Fixed Header --}}
        <div class="flex justify-between items-center p-6 border-b border-gray-200 bg-green-600 rounded-t-xl">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-truck text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Detail Pengiriman Berhasil</h3>
                    <p class="text-sm text-green-100 opacity-90">Informasi lengkap pengiriman berhasil</p>
                </div>
            </div>
            <button onclick="closeDetailModalBerhasil()" 
                    class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        {{-- Scrollable Content --}}
        <div class="flex-1 overflow-y-auto p-6 modal-scrollable">
            <div id="detailContentBerhasil" class="space-y-6">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

{{-- Modal Revisi Pengiriman Berhasil --}}
<div id="revisiPengirimanModalBerhasil" class="fixed inset-0 bg-black/20 backdrop-blur-xs bg-opacity-50 items-center justify-center z-[60] hidden" style="display: none;">
    <div class="bg-white rounded-xl shadow-2xl w-11/12 md:w-2/3 lg:w-1/2 max-w-2xl">
        {{-- Header --}}
        <div class="flex justify-between items-center p-6 border-b border-gray-200 bg-orange-600 rounded-t-xl">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-undo text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Revisi Pengiriman</h3>
                    <p class="text-sm text-orange-100 opacity-90">Kembalikan ke status Pengiriman Masuk untuk diperbaiki</p>
                </div>
            </div>
            <button onclick="closeRevisiModalBerhasil()" 
                    class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        {{-- Content --}}
        <div class="p-6">
            {{-- Warning Banner --}}
            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3 mt-1"></i>
                    <div>
                        <h5 class="font-semibold text-red-800 mb-2">⚠️ Peringatan Revisi</h5>
                        <ul class="text-sm text-red-700 space-y-1">
                            <li>• Pengiriman akan dikembalikan ke status <strong>Pengiriman Masuk</strong></li>
                            <li>• Qty yang sudah di-kurangi akan dikembalikan</li>
                            <li>• Alasan revisi akan tercatat di sistem untuk audit trail</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form id="formRevisiPengirimanBerhasil" onsubmit="submitRevisiPengirimanBerhasil(event)">
                <input type="hidden" id="revisi_pengiriman_id" name="pengiriman_id">
                
                {{-- Catatan Revisi --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-comment-alt text-orange-600 mr-1"></i>
                        Alasan Revisi <span class="text-red-600">*</span>
                    </label>
                    <textarea id="revisi_catatan" 
                              name="catatan" 
                              rows="5" 
                              required 
                              minlength="10"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200" 
                              placeholder="Jelaskan alasan revisi secara detail (minimal 10 karakter)...&#10;&#10;Contoh:&#10;- Data qty salah input&#10;- Tanggal kirim tidak sesuai&#10;- Supplier salah pilih"></textarea>
                    <small class="text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Alasan revisi akan dilihat oleh Staff Procurement dan tercatat di sistem
                    </small>
                </div>

                {{-- Confirmation Checkbox --}}
                <div class="mb-6">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" id="revisi_confirm" required 
                               class="mt-1 w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <span class="ml-3 text-sm text-gray-700">
                            Saya memahami bahwa revisi ini akan <strong>mengembalikan qty ke order detail</strong> 
                            dan mengembalikan status pengiriman ke Pengiriman Masuk untuk diperbaiki oleh Staff Procurement.
                        </span>
                    </label>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeRevisiModalBerhasil()" 
                            class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-all duration-200 font-semibold">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-all duration-200 font-semibold shadow-md hover:shadow-lg">
                        <i class="fas fa-undo mr-2"></i>
                        Revisi Pengiriman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Function to open detail modal for pengiriman berhasil
function openDetailModalBerhasil(pengirimanId) {
    console.log('Opening detail modal for pengiriman berhasil ID:', pengirimanId);
    
    // Show loading state
    const modal = document.getElementById('detailPengirimanModalBerhasil');
    const content = document.getElementById('detailContentBerhasil');
    
    content.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-green-600 text-2xl mr-3"></i>
            <span class="text-gray-600">Memuat detail pengiriman...</span>
        </div>
    `;
    
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    
    // Fetch pengiriman detail
    fetch(`/procurement/pengiriman/${pengirimanId}/detail-berhasil`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            populateDetailModalBerhasil(data.pengiriman);
        } else {
            content.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-3"></i>
                    <p class="text-red-600">${data.message || 'Gagal memuat detail pengiriman'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-3"></i>
                <p class="text-red-600">Terjadi kesalahan saat memuat data</p>
            </div>
        `;
    });
}

// Function to close detail modal for pengiriman berhasil
function closeDetailModalBerhasil() {
    const modal = document.getElementById('detailPengirimanModalBerhasil');
    modal.style.display = 'none';
    modal.classList.add('hidden');
}

// Function to open revisi modal
function openRevisiModalBerhasil(pengirimanId) {
    document.getElementById('revisi_pengiriman_id').value = pengirimanId;
    document.getElementById('revisi_catatan').value = '';
    document.getElementById('revisi_confirm').checked = false;
    
    const modal = document.getElementById('revisiPengirimanModalBerhasil');
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
}

// Function to close revisi modal
function closeRevisiModalBerhasil() {
    const modal = document.getElementById('revisiPengirimanModalBerhasil');
    modal.style.display = 'none';
    modal.classList.add('hidden');
}

// Add keyboard event listeners
document.addEventListener('keydown', function(event) {
    // Close detail modal with ESC key
    if (event.key === 'Escape') {
        const detailModal = document.getElementById('detailPengirimanModalBerhasil');
        const imageModal = document.getElementById('imageViewModalBerhasil');
        const revisiModal = document.getElementById('revisiPengirimanModalBerhasil');
        
        if (imageModal && !imageModal.classList.contains('hidden')) {
            closeImageViewBerhasil();
        } else if (detailModal && !detailModal.classList.contains('hidden')) {
            closeDetailModalBerhasil();
        } else if (revisiModal && !revisiModal.classList.contains('hidden')) {
            closeRevisiModalBerhasil();
        }
    }
});

// Function to populate modal with pengiriman data
function populateDetailModalBerhasil(pengiriman) {
    const content = document.getElementById('detailContentBerhasil');
    
    // Generate details table
    let detailsTable = '';
    if (pengiriman.details && pengiriman.details.length > 0) {
        detailsTable = `
            <div>
                <h4 class="text-md font-semibold text-gray-900 mb-3">Detail Barang</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Kirim</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${pengiriman.details.map(detail => `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${detail.bahan_baku}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${detail.supplier}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${detail.qty_kirim} kg</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp ${parseFloat(detail.harga_satuan).toLocaleString('id-ID')}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp ${parseFloat(detail.total_harga).toLocaleString('id-ID')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    // Generate file pengiriman section (Combined: Foto Tanda Terima + Bukti Foto Bongkar) - HORIZONTAL LAYOUT
    let filePengirimanSection = '';
    if (pengiriman.foto_tanda_terima_url || (pengiriman.bukti_foto_urls && pengiriman.bukti_foto_urls.length > 0)) {
        filePengirimanSection = `
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-5 border border-blue-200">
                <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-folder-open text-blue-600 mr-2"></i>
                    File Pengiriman
                </h4>
                
                <div class="grid grid-cols-1 ${pengiriman.foto_tanda_terima_url && pengiriman.bukti_foto_urls && pengiriman.bukti_foto_urls.length > 0 ? 'md:grid-cols-2' : 'md:grid-cols-1'} gap-4">
                    ${pengiriman.foto_tanda_terima_url ? `
                        <!-- Foto Tanda Terima -->
                        <div class="bg-white rounded-lg p-4 border border-purple-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-file-signature text-purple-600 mr-2"></i>
                                <h5 class="text-sm font-semibold text-gray-800">Foto Tanda Terima</h5>
                            </div>
                            ${pengiriman.foto_tanda_terima_uploaded_at ? `
                                <div class="flex items-center text-xs text-gray-500 mb-3">
                                    <i class="far fa-clock mr-1"></i>
                                    <span>${pengiriman.foto_tanda_terima_uploaded_at}</span>
                                </div>
                            ` : ''}
                            <div class="relative group">
                                <img src="${pengiriman.foto_tanda_terima_url}" 
                                     alt="Foto Tanda Terima" 
                                     class="w-full h-48 object-cover rounded-lg border-2 border-purple-300 cursor-pointer hover:opacity-90 transition-opacity shadow-md"
                                     onclick="viewImageBerhasil('${pengiriman.foto_tanda_terima_url}', 'Foto Tanda Terima')">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <div class="flex space-x-2">
                                        <button onclick="viewImageBerhasil('${pengiriman.foto_tanda_terima_url}', 'Foto Tanda Terima')" 
                                                class="bg-white text-purple-600 p-3 rounded-full shadow-lg hover:bg-purple-50 transition-all">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="downloadImageBerhasil('${pengiriman.foto_tanda_terima_url}', 'tanda_terima_${pengiriman.no_pengiriman}.jpg')" 
                                                class="bg-white text-green-600 p-3 rounded-full shadow-lg hover:bg-green-50 transition-all">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${pengiriman.bukti_foto_urls && pengiriman.bukti_foto_urls.length > 0 ? `
                        <!-- Bukti Foto Bongkar -->
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-camera text-blue-600 mr-2"></i>
                                <h5 class="text-sm font-semibold text-gray-800">Bukti Foto Bongkar</h5>
                            </div>
                            ${pengiriman.bukti_foto_bongkar_uploaded_at ? `
                                <div class="flex items-center text-xs text-gray-500 mb-3">
                                    <i class="far fa-clock mr-1"></i>
                                    <span>${pengiriman.bukti_foto_bongkar_uploaded_at}</span>
                                </div>
                            ` : ''}
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                ${pengiriman.bukti_foto_urls.slice(0, 4).map((url, index) => `
                                    <div class="relative group image-grid-item">
                                        <img src="${url}" 
                                             alt="Bukti foto bongkar ${index + 1}" 
                                             class="w-full h-24 object-cover rounded-lg border-2 border-blue-300 cursor-pointer hover:opacity-90 transition-opacity shadow-md"
                                             onclick="viewImageBerhasil('${url}', 'Bukti Foto Bongkar ${index + 1}')">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                            <div class="flex space-x-1">
                                                <button onclick="viewImageBerhasil('${url}', 'Bukti Foto Bongkar ${index + 1}')" 
                                                        class="bg-white text-blue-600 p-2 rounded-full shadow-lg hover:bg-blue-50 transition-all">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>
                                                <button onclick="downloadImageBerhasil('${url}', 'bukti_bongkar_${index + 1}.jpg')" 
                                                        class="bg-white text-green-600 p-2 rounded-full shadow-lg hover:bg-green-50 transition-all">
                                                    <i class="fas fa-download text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="photo-counter">${index + 1}</div>
                                    </div>
                                `).join('')}
                            </div>
                            ${pengiriman.bukti_foto_urls.length > 4 ? `
                                <p class="text-xs text-gray-500 mb-2">+${pengiriman.bukti_foto_urls.length - 4} foto lainnya</p>
                            ` : ''}
                            <div class="flex justify-between items-center pt-2 border-t border-blue-200">
                                <span class="text-xs text-gray-600">
                                    ${pengiriman.foto_tanda_terima_url ? pengiriman.bukti_foto_urls.length + 1 : pengiriman.bukti_foto_urls.length} file total
                                </span>
                                <button onclick="downloadAllFilesBerhasil(${JSON.stringify(pengiriman.bukti_foto_urls).replace(/"/g, '&quot;')}, '${pengiriman.foto_tanda_terima_url || ''}', '${pengiriman.no_pengiriman}')" 
                                        class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition-all">
                                    <i class="fas fa-download mr-1"></i>
                                    Download Semua
                                </button>
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    // Generate timeline section with sub-timeline for file uploads
    let timelineSection = '';
    if (pengiriman.timeline && pengiriman.timeline.length > 0) {
        timelineSection = `
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-6 border-2 border-indigo-200 shadow-md">
                <div class="flex items-center mb-5">
                    <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">Timeline Proses</h4>
                        <p class="text-xs text-indigo-600">Riwayat perubahan status pengiriman</p>
                    </div>
                </div>
                
                <div class="relative pl-2">
                    ${pengiriman.timeline.map((item, index) => {
                        const isLast = index === pengiriman.timeline.length - 1;
                        const colorClasses = {
                            'blue': 'bg-blue-500 border-blue-600 shadow-blue-200',
                            'yellow': 'bg-yellow-500 border-yellow-600 shadow-yellow-200',
                            'green': 'bg-green-500 border-green-600 shadow-green-200',
                            'gray': 'bg-gray-500 border-gray-600 shadow-gray-200',
                            'indigo': 'bg-indigo-500 border-indigo-600 shadow-indigo-200',
                            'purple': 'bg-purple-500 border-purple-600 shadow-purple-200'
                        };
                        const bgColorClasses = {
                            'blue': 'bg-gradient-to-br from-blue-50 to-blue-100 border-blue-300',
                            'yellow': 'bg-gradient-to-br from-yellow-50 to-yellow-100 border-yellow-300',
                            'green': 'bg-gradient-to-br from-green-50 to-green-100 border-green-300',
                            'gray': 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-300',
                            'indigo': 'bg-gradient-to-br from-indigo-50 to-indigo-100 border-indigo-300',
                            'purple': 'bg-gradient-to-br from-purple-50 to-purple-100 border-purple-300'
                        };
                        const textColorClasses = {
                            'blue': 'text-blue-800',
                            'yellow': 'text-yellow-800',
                            'green': 'text-green-800',
                            'gray': 'text-gray-800',
                            'indigo': 'text-indigo-800',
                            'purple': 'text-purple-800'
                        };
                        
                        // Check for sub-timeline based on status
                        const isPengirimanDibuat = item.type === 'pengiriman' && item.status === 'pending';
                        const isFisikDiterima = item.type === 'pengiriman' && item.status === 'fisik_diterima';
                        
                        let subTimeline = '';
                        
                        // Sub-timeline for "Pengiriman Dibuat" - Bukti Foto Bongkar
                        if (isPengirimanDibuat && pengiriman.bukti_foto_bongkar_uploaded_at) {
                            subTimeline = `
                                <div class="ml-10 mt-3 space-y-3 border-l-2 border-dashed border-gray-300 pl-4">
                                    <div class="flex items-start timeline-item-hover group">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-500 border-2 border-blue-600 mr-3 flex-shrink-0 shadow-md group-hover:scale-110 transition-transform">
                                            <i class="fas fa-camera text-white text-xs"></i>
                                        </div>
                                        <div class="flex-1 bg-white rounded-lg px-3 py-2 border-2 border-blue-200 shadow-sm group-hover:shadow-md transition-shadow">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-blue-900 font-semibold">Upload Bukti Foto Bongkar</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Uploaded
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-600 mt-1 flex items-center">
                                                <i class="far fa-clock mr-1.5 text-blue-500"></i>
                                                ${pengiriman.bukti_foto_bongkar_uploaded_at}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        // Sub-timeline for "Fisik Diterima" - Foto Tanda Terima
                        if (isFisikDiterima && pengiriman.foto_tanda_terima_uploaded_at) {
                            subTimeline = `
                                <div class="ml-10 mt-3 space-y-3 border-l-2 border-dashed border-purple-300 pl-4">
                                    <div class="flex items-start timeline-item-hover group">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-purple-500 border-2 border-purple-600 mr-3 flex-shrink-0 shadow-md group-hover:scale-110 transition-transform">
                                            <i class="fas fa-file-signature text-white text-xs"></i>
                                        </div>
                                        <div class="flex-1 bg-white rounded-lg px-3 py-2 border-2 border-purple-200 shadow-sm group-hover:shadow-md transition-shadow">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-purple-900 font-semibold">Upload Foto Tanda Terima</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-100 text-purple-700">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Uploaded
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-600 mt-1 flex items-center">
                                                <i class="far fa-clock mr-1.5 text-purple-500"></i>
                                                ${pengiriman.foto_tanda_terima_uploaded_at}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        return `
                            <div class="flex mb-6 ${isLast ? '' : 'pb-2'} timeline-item group">
                                <div class="flex flex-col items-center mr-4">
                                    <div class="relative flex items-center justify-center w-12 h-12 rounded-xl ${colorClasses[item.color] || colorClasses['gray']} border-3 shadow-xl flex-shrink-0 group-hover:scale-110 transition-transform timeline-icon-pulse">
                                        <i class="fas ${item.icon} text-white text-lg"></i>
                                        <div class="absolute inset-0 rounded-xl ${colorClasses[item.color] || colorClasses['gray']} opacity-20 animate-ping-slow"></div>
                                    </div>
                                    ${!isLast ? '<div class="w-1 flex-1 bg-gradient-to-b from-gray-300 to-gray-200 mt-3 rounded-full"></div>' : ''}
                                </div>
                                <div class="flex-1">
                                    <div class="${bgColorClasses[item.color] || bgColorClasses['gray']} rounded-xl p-5 border-2 shadow-lg group-hover:shadow-xl transition-all timeline-content">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <h5 class="font-bold ${textColorClasses[item.color] || textColorClasses['gray']} text-base">${item.title}</h5>
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold ${item.color === 'green' ? 'bg-green-600 text-white' : 'bg-white bg-opacity-70 ' + textColorClasses[item.color]}">
                                                        ${item.status ? item.status.toUpperCase() : 'COMPLETED'}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-700 leading-relaxed">${item.description}</p>
                                                ${item.user ? `
                                                    <div class="mt-2 flex items-center text-xs text-gray-600">
                                                        <i class="fas fa-user-circle mr-1.5"></i>
                                                        <span class="font-medium">${item.user}</span>
                                                    </div>
                                                ` : ''}
                                            </div>
                                            <div class="ml-4 text-right flex-shrink-0">
                                                <div class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white bg-opacity-80 border border-gray-300 shadow-sm">
                                                    <i class="far fa-clock mr-2 text-gray-500"></i>
                                                    <span class="text-xs text-gray-700 font-medium whitespace-nowrap">${item.formatted_time}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    ${subTimeline}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }
    
    // Check if user can revisi (only direktur and manager_purchasing)
    const userRole = '{{ Auth::user()->role ?? "" }}';
    const canRevisi = ['direktur', 'manager_purchasing'].includes(userRole);
    
    // Pengiriman berhasil always can be revised by authorized users
    // No need to check approval/invoice status - accounting will handle their own data
    const canBeRevised = true;
    
    content.innerHTML = `
        <!-- Action Buttons (if allowed) -->
        ${canRevisi ? `
            <div class="bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-300 rounded-xl p-4 mb-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-semibold text-gray-900">Perlu Revisi Data?</h5>
                            <p class="text-xs text-gray-600">Kembalikan ke status Pengiriman Masuk untuk diperbaiki</p>
                        </div>
                    </div>
                    <button onclick="openRevisiModalBerhasil(${pengiriman.id})" 
                            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-all duration-200 font-semibold shadow-md hover:shadow-lg flex items-center">
                        <i class="fas fa-undo mr-2"></i>
                        Revisi Pengiriman
                    </button>
                </div>
            </div>
        ` : ''}
    
        <!-- 1. Informasi Pengiriman -->
        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-md font-semibold text-gray-900">Informasi Pengiriman</h4>
                ${pengiriman.bukti_pembayaran_url ? `
                    <a href="${pengiriman.bukti_pembayaran_url}" 
                       download
                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-all duration-150 shadow-sm hover:shadow-md">
                        <i class="fas fa-download mr-1.5"></i>
                        Download Bukti Pembayaran
                    </a>
                ` : ''}
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-600">No Pengiriman</label>
                    <p class="text-sm text-gray-900 font-medium">${pengiriman.no_pengiriman}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Status</label>
                    <p class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            ${pengiriman.status}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">No PO</label>
                    <p class="text-sm text-gray-900">${pengiriman.no_po}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">PIC Procurement</label>
                    <p class="text-sm text-gray-900">${pengiriman.pic_purchasing}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Tanggal Kirim</label>
                    <p class="text-sm text-gray-900">${pengiriman.tanggal_kirim}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Hari Kirim</label>
                    <p class="text-sm text-gray-900">${pengiriman.hari_kirim}</p>
                </div>
            </div>
        </div>

        <!-- 2. Ringkasan -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Ringkasan</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-600">Total Quantity</label>
                    <p class="text-lg font-bold text-blue-600">${pengiriman.total_qty}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Total Harga</label>
                    <p class="text-lg font-bold text-green-600">${pengiriman.total_harga}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Total Item</label>
                    <p class="text-lg font-bold text-purple-600">${pengiriman.total_items} item</p>
                </div>
            </div>
        </div>

        <!-- 3. Detail Barang -->
        ${detailsTable}

        <!-- 3.5. Informasi Refraksi & Harga -->
        ${pengiriman.approval_pembayaran || pengiriman.invoice_penagihan ? `
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-calculator text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Refraksi & Harga</h3>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    ${pengiriman.approval_pembayaran ? `
                        <!-- Left: Refraksi Info -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Informasi Refraksi</h4>
                            
                            ${pengiriman.approval_pembayaran.refraksi_type && pengiriman.approval_pembayaran.refraksi_value ? `
                                <!-- Refraksi Type & Value -->
                                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-orange-900">
                                            <i class="fas fa-percentage mr-1"></i>
                                            Tipe Refraksi:
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-600 text-white">
                                            ${pengiriman.approval_pembayaran.refraksi_type.toUpperCase()}
                                        </span>
                                    </div>
                                    <div class="text-2xl font-bold text-orange-700">
                                        ${pengiriman.approval_pembayaran.refraksi_type === 'percentage' 
                                            ? `${parseFloat(pengiriman.approval_pembayaran.refraksi_value).toFixed(2)}%` 
                                            : `Rp ${parseFloat(pengiriman.approval_pembayaran.refraksi_value).toLocaleString('id-ID')}`}
                                    </div>
                                    <p class="text-xs text-orange-600 mt-1">Nilai Refraksi</p>
                                </div>

                                <!-- Qty Before & After -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <p class="text-xs text-gray-600 mb-1">Qty Sebelum</p>
                                        <p class="text-lg font-bold text-gray-900">
                                            ${parseFloat(pengiriman.approval_pembayaran.qty_before_refraksi || 0).toLocaleString('id-ID')} <span class="text-sm font-normal text-gray-500">kg</span>
                                        </p>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                        <p class="text-xs text-green-600 mb-1">Qty Setelah</p>
                                        <p class="text-lg font-bold text-green-700">
                                            ${parseFloat(pengiriman.approval_pembayaran.qty_after_refraksi || 0).toLocaleString('id-ID')} <span class="text-sm font-normal text-green-500">kg</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Amount Before & After -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <p class="text-xs text-gray-600 mb-1">Total Sebelum</p>
                                        <p class="text-sm font-bold text-gray-900">
                                            Rp ${parseFloat(pengiriman.approval_pembayaran.amount_before_refraksi || 0).toLocaleString('id-ID')}
                                        </p>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                        <p class="text-xs text-green-600 mb-1">Total Setelah</p>
                                        <p class="text-sm font-bold text-green-700">
                                            Rp ${parseFloat(pengiriman.approval_pembayaran.amount_after_refraksi || 0).toLocaleString('id-ID')}
                                        </p>
                                    </div>
                                </div>

                                <!-- Refraksi Amount -->
                                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                    <p class="text-xs text-red-600 mb-1">
                                        <i class="fas fa-minus-circle mr-1"></i>
                                        Potongan Refraksi
                                    </p>
                                    <p class="text-xl font-bold text-red-700">
                                        Rp ${parseFloat(pengiriman.approval_pembayaran.refraksi_amount || 0).toLocaleString('id-ID')}
                                    </p>
                                </div>
                            ` : `
                                <div class="text-center py-8 text-gray-400">
                                    <i class="fas fa-info-circle text-3xl mb-2"></i>
                                    <p class="text-sm">Tidak ada refraksi</p>
                                </div>
                            `}
                        </div>

                        <!-- Right: Price Info -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Informasi Harga</h4>
                            
                            <!-- Harga Beli -->
                            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border-2 border-red-300">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm font-semibold text-red-900">
                                        <i class="fas fa-shopping-cart mr-1"></i>
                                        Harga Beli
                                    </p>
                                    <span class="px-2 py-1 bg-red-600 text-white text-xs rounded-full font-semibold">PEMBELIAN</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs text-red-700">Per Kg:</span>
                                        <span class="text-lg font-bold text-red-900">
                                            Rp ${parseFloat(pengiriman.harga_beli_per_kg || 0).toLocaleString('id-ID')}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-baseline pt-2 border-t border-red-200">
                                        <span class="text-xs text-red-700">Total:</span>
                                        <span class="text-xl font-bold text-red-900">
                                            Rp ${parseFloat(pengiriman.total_harga_beli || 0).toLocaleString('id-ID')}
                                        </span>
                                    </div>
                                    <p class="text-xs text-red-600 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Untuk ${parseFloat(pengiriman.qty_after_refraksi || 0).toLocaleString('id-ID')} kg
                                    </p>
                                </div>
                            </div>

                            <!-- Harga Jual -->
                            ${pengiriman.harga_jual_per_kg && pengiriman.harga_jual_per_kg > 0 ? `
                                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border-2 border-green-300">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-sm font-semibold text-green-900">
                                            <i class="fas fa-tag mr-1"></i>
                                            Harga Jual
                                        </p>
                                        <span class="px-2 py-1 bg-green-600 text-white text-xs rounded-full font-semibold">PENJUALAN</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-baseline">
                                            <span class="text-xs text-green-700">Per Kg:</span>
                                            <span class="text-lg font-bold text-green-900">
                                                Rp ${parseFloat(pengiriman.harga_jual_per_kg || 0).toLocaleString('id-ID')}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-baseline pt-2 border-t border-green-200">
                                            <span class="text-xs text-green-700">Total:</span>
                                            <span class="text-xl font-bold text-green-900">
                                                Rp ${parseFloat(pengiriman.total_harga_jual || 0).toLocaleString('id-ID')}
                                            </span>
                                        </div>
                                        <p class="text-xs text-green-600 mt-1">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Untuk ${parseFloat(pengiriman.qty_jual || 0).toLocaleString('id-ID')} kg
                                        </p>
                                        <p class="text-xs text-green-500 mt-1">
                                            <i class="fas fa-source mr-1"></i>
                                            Sumber: ${pengiriman.harga_jual_source || 'N/A'}
                                        </p>
                                    </div>
                                </div>

                                <!-- Margin -->
                                ${pengiriman.margin !== undefined ? `
                                    <div class="bg-gradient-to-br from-${pengiriman.margin >= 0 ? 'blue' : 'red'}-50 to-${pengiriman.margin >= 0 ? 'blue' : 'red'}-100 rounded-lg p-4 border-2 border-${pengiriman.margin >= 0 ? 'blue' : 'red'}-300">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-sm font-semibold text-${pengiriman.margin >= 0 ? 'blue' : 'red'}-900">
                                                <i class="fas fa-chart-line mr-1"></i>
                                                Margin Keuntungan
                                            </p>
                                            <span class="px-2 py-1 bg-${pengiriman.margin >= 0 ? 'blue' : 'red'}-600 text-white text-xs rounded-full font-semibold">
                                                ${pengiriman.margin >= 0 ? 'PROFIT' : 'LOSS'}
                                            </span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between items-baseline">
                                                <span class="text-xs text-${pengiriman.margin >= 0 ? 'blue' : 'red'}-700">Nominal:</span>
                                                <span class="text-xl font-bold text-${pengiriman.margin >= 0 ? 'blue' : 'red'}-900">
                                                    ${pengiriman.margin >= 0 ? '+' : ''}Rp ${parseFloat(pengiriman.margin || 0).toLocaleString('id-ID')}
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-baseline pt-2 border-t border-${pengiriman.margin >= 0 ? 'blue' : 'red'}-200">
                                                <span class="text-xs text-${pengiriman.margin >= 0 ? 'blue' : 'red'}-700">Persentase:</span>
                                                <span class="text-xl font-bold text-${pengiriman.margin >= 0 ? 'blue' : 'red'}-900">
                                                    ${pengiriman.margin >= 0 ? '+' : ''}${parseFloat(pengiriman.margin_percentage || 0).toFixed(2)}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                            ` : `
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="text-center py-4 text-gray-400">
                                        <i class="fas fa-info-circle text-2xl mb-2"></i>
                                        <p class="text-sm">Harga jual belum tersedia</p>
                                        <p class="text-xs mt-1">Invoice penagihan belum dibuat</p>
                                    </div>
                                </div>
                            `}
                        </div>
                    ` : ''}
                </div>
            </div>
        ` : ''}

        <!-- 3.6. Catatan Refraksi (if exists) -->
        ${pengiriman.catatan_refraksi ? `
            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border-l-4 border-orange-500 rounded-lg p-6 shadow-sm">
                <div class="flex items-start mb-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-orange-600"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-orange-900 mb-1">Catatan Refraksi</h3>
                        <p class="text-xs text-orange-600">Informasi penting terkait pengurangan kuantitas/harga</p>
                    </div>
                </div>
                <div class="bg-white bg-opacity-60 rounded-lg p-4 border border-orange-200">
                    <p class="text-gray-800 whitespace-pre-line">${pengiriman.catatan_refraksi}</p>
                </div>
                
                ${pengiriman.approval_pembayaran && pengiriman.approval_pembayaran.refraksi_amount ? `
                    <div class="mt-3 flex items-center justify-between text-sm">
                        <span class="text-orange-700 font-medium">
                            <i class="fas fa-info-circle mr-1"></i>
                            Potongan Refraksi:
                        </span>
                        <span class="text-orange-900 font-bold">
                            Rp ${parseFloat(pengiriman.approval_pembayaran.refraksi_amount).toLocaleString('id-ID')}
                        </span>
                    </div>
                ` : ''}
            </div>
        ` : ''}

        <!-- 4. File Pengiriman -->
        ${filePengirimanSection}

        <!-- 5. Catatan (Editable) -->
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-md font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                    Catatan
                </h4>
                <button id="editCatatanBtn_${pengiriman.id}" onclick="toggleEditCatatan(${pengiriman.id})" 
                        class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition-all">
                    <i class="fas fa-edit mr-1"></i>
                    Edit Catatan
                </button>
            </div>
            
            <div id="catatanViewMode_${pengiriman.id}" class="catatan-view-mode">
                <p class="text-sm text-gray-700 whitespace-pre-line">${pengiriman.catatan || 'Belum ada catatan'}</p>
            </div>
            
            <div id="catatanEditMode_${pengiriman.id}" class="catatan-edit-mode hidden">
                <textarea id="catatanInput_${pengiriman.id}" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                          rows="4"
                          placeholder="Tambahkan catatan...">${pengiriman.catatan || ''}</textarea>
                <div class="flex justify-end space-x-2 mt-2">
                    <button onclick="cancelEditCatatan(${pengiriman.id})" 
                            class="text-xs bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-1.5 rounded-lg transition-all">
                        <i class="fas fa-times mr-1"></i>
                        Batal
                    </button>
                    <button onclick="saveCatatan(${pengiriman.id})" 
                            class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg transition-all">
                        <i class="fas fa-save mr-1"></i>
                        Simpan
                    </button>
                </div>
            </div>
        </div>

        <!-- 6. Review Pengiriman -->
        ${pengiriman.rating || pengiriman.ulasan ? `
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-star text-yellow-600 mr-2"></i>
                    Review Pengiriman
                </h4>
                <div class="space-y-3">
                    ${pengiriman.rating ? `
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Rating:</span>
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center">
                                    ${Array.from({length: 5}, (_, i) => `
                                        <i class="fas fa-star text-lg ${i < pengiriman.rating ? 'text-yellow-400' : 'text-gray-300'}"></i>
                                    `).join('')}
                                </div>
                                <span class="text-sm font-semibold text-yellow-700">${pengiriman.rating}/5</span>
                            </div>
                        </div>
                    ` : ''}
                    ${pengiriman.ulasan ? `
                        <div>
                            <span class="text-sm font-medium text-gray-600">Ulasan:</span>
                            <div class="mt-2 p-3 bg-white border border-yellow-300 rounded-lg">
                                <p class="text-sm text-gray-800">${pengiriman.ulasan}</p>
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        ` : `
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-star text-gray-400 mr-2"></i>
                    Review Pengiriman
                </h4>
                <div class="text-center py-3">
                    <i class="fas fa-star-o text-gray-400 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-500 italic">Belum ada review untuk pengiriman ini</p>
                </div>
            </div>
        `}

        <!-- 7. Timeline Proses -->
        ${timelineSection}
    `;
}

// Function to view image in modal
function viewImageBerhasil(imageUrl, title) {
    // Create modal for viewing image
    const imageModal = document.createElement('div');
    imageModal.id = 'imageViewModalBerhasil';
    imageModal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[60] p-4 modal-backdrop';
    
    imageModal.innerHTML = `
        <div class="relative max-w-6xl max-h-full image-modal-content">
            <div class="bg-white rounded-xl shadow-2xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-image text-blue-600 mr-2"></i>
                        <h3 class="text-xl font-bold text-gray-900">${title}</h3>
                    </div>
                    <button onclick="closeImageViewBerhasil()" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-all">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="text-center bg-gray-50 rounded-lg p-4">
                    <img src="${imageUrl}" alt="${title}" 
                         class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-lg mx-auto">
                </div>
                <div class="flex justify-between items-center mt-6">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Klik dan drag untuk memperbesar gambar
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="window.open('${imageUrl}', '_blank')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Buka di Tab Baru
                        </button>
                        <button onclick="downloadImageBerhasil('${imageUrl}', '${title.toLowerCase().replace(/\s+/g, '_')}.jpg')" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-all">
                            <i class="fas fa-download mr-2"></i>
                            Download Gambar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(imageModal);
    
    // Close on click outside
    imageModal.addEventListener('click', function(e) {
        if (e.target === imageModal) {
            closeImageViewBerhasil();
        }
    });
}

// Function to close image view modal
function closeImageViewBerhasil() {
    const modal = document.getElementById('imageViewModalBerhasil');
    if (modal) {
        modal.remove();
    }
}

// Function to download single image
function downloadImageBerhasil(imageUrl, filename) {
    const link = document.createElement('a');
    link.href = imageUrl;
    link.download = filename;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Function to download all files (foto tanda terima + bukti foto bongkar)
function downloadAllFilesBerhasil(buktiFotoUrls, fotoTandaTerimaUrl, noPengiriman) {
    const allFiles = [];
    
    // Add foto tanda terima if exists
    if (fotoTandaTerimaUrl && fotoTandaTerimaUrl !== '') {
        allFiles.push({
            url: fotoTandaTerimaUrl,
            filename: `${noPengiriman}_tanda_terima.jpg`
        });
    }
    
    // Add all bukti foto bongkar
    if (buktiFotoUrls && buktiFotoUrls.length > 0) {
        buktiFotoUrls.forEach((url, index) => {
            allFiles.push({
                url: url,
                filename: `${noPengiriman}_bukti_bongkar_${index + 1}.jpg`
            });
        });
    }
    
    if (allFiles.length === 0) {
        alert('Tidak ada file untuk diunduh');
        return;
    }
    
    // Download all files with delay
    allFiles.forEach((file, index) => {
        setTimeout(() => {
            downloadImageBerhasil(file.url, file.filename);
        }, index * 500); // Delay 500ms between downloads
    });
    
    // Show notification
    const totalFiles = allFiles.length;
    console.log(`Mengunduh ${totalFiles} file...`);
}

// Function to toggle edit mode for catatan
function toggleEditCatatan(pengirimanId) {
    const viewMode = document.getElementById(`catatanViewMode_${pengirimanId}`);
    const editMode = document.getElementById(`catatanEditMode_${pengirimanId}`);
    const editBtn = document.getElementById(`editCatatanBtn_${pengirimanId}`);
    
    viewMode.classList.add('hidden');
    editMode.classList.remove('hidden');
    editBtn.classList.add('hidden');
}

// Function to cancel edit catatan
function cancelEditCatatan(pengirimanId) {
    const viewMode = document.getElementById(`catatanViewMode_${pengirimanId}`);
    const editMode = document.getElementById(`catatanEditMode_${pengirimanId}`);
    const editBtn = document.getElementById(`editCatatanBtn_${pengirimanId}`);
    
    viewMode.classList.remove('hidden');
    editMode.classList.add('hidden');
    editBtn.classList.remove('hidden');
}

// Function to save catatan
function saveCatatan(pengirimanId) {
    const input = document.getElementById(`catatanInput_${pengirimanId}`);
    const catatan = input.value;
    
    // Show loading state
    const editBtn = document.getElementById(`editCatatanBtn_${pengirimanId}`);
    editBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';
    editBtn.disabled = true;
    
    // Send update request
    fetch(`/procurement/pengiriman/${pengirimanId}/update-catatan`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ catatan: catatan })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update view mode with new catatan
            const viewMode = document.getElementById(`catatanViewMode_${pengirimanId}`);
            viewMode.innerHTML = `<p class="text-sm text-gray-700 whitespace-pre-line">${catatan || 'Belum ada catatan'}</p>`;
            
            // Switch back to view mode
            cancelEditCatatan(pengirimanId);
            
            // Reset button
            editBtn.innerHTML = '<i class="fas fa-edit mr-1"></i>Edit Catatan';
            editBtn.disabled = false;
            
            // Show success notification
            alert('Catatan berhasil diperbarui!');
        } else {
            alert(data.message || 'Gagal menyimpan catatan');
            editBtn.innerHTML = '<i class="fas fa-edit mr-1"></i>Edit Catatan';
            editBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan catatan');
        editBtn.innerHTML = '<i class="fas fa-edit mr-1"></i>Edit Catatan';
        editBtn.disabled = false;
    });
}

// Function to submit revisi
function submitRevisiPengirimanBerhasil(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const pengirimanId = formData.get('pengiriman_id');
    const catatan = formData.get('catatan');
    
    // Final confirmation
    Swal.fire({
        title: 'Konfirmasi Revisi',
        html: `
            <div class="text-left">
                <p class="mb-3">Anda akan merevisi pengiriman dengan alasan:</p>
                <div class="bg-orange-50 border-l-4 border-orange-400 p-3 mb-3">
                    <p class="text-sm italic">"${catatan}"</p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <p class="text-sm text-red-700 font-semibold mb-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Dampak Revisi:
                    </p>
                    <ul class="text-xs text-red-600 space-y-1 ml-4">
                        <li>• Status kembali ke Pengiriman Masuk</li>
                        <li>• Qty dikembalikan ke Order Detail</li>
                    </ul>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Revisi',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ea580c',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses Revisi...',
                html: 'Mohon tunggu, sedang mengembalikan data...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
            
            // Submit via AJAX
            fetch(`/procurement/pengiriman/${pengirimanId}/revisi`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    catatan: catatan
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        closeRevisiModalBerhasil();
                        closeDetailModalBerhasil();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat memproses revisi'
                });
            });
        }
    });
}

// Close revisi modal when clicking outside
document.addEventListener('click', function(event) {
    const revisiModal = document.getElementById('revisiPengirimanModalBerhasil');
    if (event.target === revisiModal) {
        closeRevisiModalBerhasil();
    }
});
</script>

<style>
.image-grid-item {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.image-grid-item:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, rgba(0,0,0,0.1), rgba(0,0,0,0.3));
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
}

.image-grid-item:hover .image-overlay {
    opacity: 1;
}

.image-action-btn {
    background: white;
    color: #374151;
    padding: 0.5rem;
    border-radius: 50%;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
    margin: 0 0.25rem;
}

.image-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.modal-backdrop {
    backdrop-filter: blur(8px);
    background: rgba(0,0,0,0.7);
}

.image-modal-content {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.photo-counter {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Custom scrollbar untuk area konten */
.modal-scrollable::-webkit-scrollbar {
    width: 6px;
}

.modal-scrollable::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.modal-scrollable::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.modal-scrollable::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Smooth scroll behavior */
.modal-scrollable {
    scroll-behavior: smooth;
}

/* Timeline styling */
.timeline-item {
    position: relative;
    transition: all 0.3s ease;
}

.timeline-item:hover {
    transform: translateX(5px);
}

.timeline-connector {
    position: absolute;
    left: 1.25rem;
    top: 2.5rem;
    width: 2px;
    height: calc(100% - 2.5rem);
    background: linear-gradient(180deg, #d1d5db 0%, #f3f4f6 100%);
}

.timeline-icon-wrapper {
    position: relative;
    z-index: 10;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
    }
}

.timeline-content {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Slow ping animation for timeline icons */
@keyframes ping-slow {
    0% {
        transform: scale(1);
        opacity: 0.3;
    }
    75%, 100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

.animate-ping-slow {
    animation: ping-slow 3s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Timeline icon pulse effect */
.timeline-icon-pulse {
    animation: iconPulse 2s ease-in-out infinite;
}

@keyframes iconPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Timeline item hover effect */
.timeline-item-hover:hover {
    transform: translateX(3px);
}

/* Fade in animation for modal content */
@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

#detailPengirimanModalBerhasil.flex {
    animation: modalFadeIn 0.3s ease-out;
}
</style>
