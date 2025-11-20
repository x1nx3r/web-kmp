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

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('detailPengirimanModalBerhasil');
    if (event.target === modal) {
        closeDetailModalBerhasil();
    }
});

// Add keyboard event listeners
document.addEventListener('keydown', function(event) {
    // Close detail modal with ESC key
    if (event.key === 'Escape') {
        const detailModal = document.getElementById('detailPengirimanModalBerhasil');
        const imageModal = document.getElementById('imageViewModalBerhasil');
        
        if (imageModal && !imageModal.classList.contains('hidden')) {
            closeImageViewBerhasil();
        } else if (detailModal && !detailModal.classList.contains('hidden')) {
            closeDetailModalBerhasil();
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
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-6 border border-indigo-200">
                <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-history text-indigo-600 mr-2"></i>
                    Timeline Proses
                </h4>
                <div class="relative">
                    ${pengiriman.timeline.map((item, index) => {
                        const isLast = index === pengiriman.timeline.length - 1;
                        const colorClasses = {
                            'blue': 'bg-blue-500 border-blue-600',
                            'yellow': 'bg-yellow-500 border-yellow-600',
                            'green': 'bg-green-500 border-green-600',
                            'gray': 'bg-gray-500 border-gray-600',
                            'indigo': 'bg-indigo-500 border-indigo-600',
                            'purple': 'bg-purple-500 border-purple-600'
                        };
                        const bgColorClasses = {
                            'blue': 'bg-blue-50 border-blue-200',
                            'yellow': 'bg-yellow-50 border-yellow-200',
                            'green': 'bg-green-50 border-green-200',
                            'gray': 'bg-gray-50 border-gray-200',
                            'indigo': 'bg-indigo-50 border-indigo-200',
                            'purple': 'bg-purple-50 border-purple-200'
                        };
                        const textColorClasses = {
                            'blue': 'text-blue-700',
                            'yellow': 'text-yellow-700',
                            'green': 'text-green-700',
                            'gray': 'text-gray-700',
                            'indigo': 'text-indigo-700',
                            'purple': 'text-purple-700'
                        };
                        
                        // Check if this is "Pengiriman Dibuat" to add file upload sub-timeline
                        const isPengirimanDibuat = item.type === 'pengiriman' && item.status === 'pending';
                        
                        let subTimeline = '';
                        if (isPengirimanDibuat) {
                            const uploads = [];
                            if (pengiriman.bukti_foto_bongkar_uploaded_at) {
                                uploads.push({
                                    title: 'Upload Bukti Foto Bongkar',
                                    time: pengiriman.bukti_foto_bongkar_uploaded_at,
                                    icon: 'fa-camera'
                                });
                            }
                            if (pengiriman.foto_tanda_terima_uploaded_at) {
                                uploads.push({
                                    title: 'Upload Foto Tanda Terima',
                                    time: pengiriman.foto_tanda_terima_uploaded_at,
                                    icon: 'fa-file-signature'
                                });
                            }
                            
                            if (uploads.length > 0) {
                                subTimeline = `
                                    <div class="ml-8 mt-2 space-y-2">
                                        ${uploads.map((upload, idx) => `
                                            <div class="flex items-start text-xs">
                                                <div class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-400 mr-2 flex-shrink-0">
                                                    <i class="fas ${upload.icon} text-white text-[10px]"></i>
                                                </div>
                                                <div class="flex-1 bg-gray-50 rounded px-2 py-1 border border-gray-200">
                                                    <span class="text-gray-700 font-medium">${upload.title}</span>
                                                    <div class="text-gray-500 mt-0.5">
                                                        <i class="far fa-clock mr-1"></i>${upload.time}
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                `;
                            }
                        }
                        
                        return `
                            <div class="flex mb-4 ${isLast ? '' : 'pb-4'}">
                                <div class="flex flex-col items-center mr-4">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full ${colorClasses[item.color] || colorClasses['gray']} border-2 shadow-lg flex-shrink-0">
                                        <i class="fas ${item.icon} text-white text-sm"></i>
                                    </div>
                                    ${!isLast ? '<div class="w-0.5 flex-1 bg-gray-300 mt-2"></div>' : ''}
                                </div>
                                <div class="flex-1">
                                    <div class="${bgColorClasses[item.color] || bgColorClasses['gray']} rounded-lg p-4 border shadow-sm">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h5 class="font-semibold ${textColorClasses[item.color] || textColorClasses['gray']} text-sm">${item.title}</h5>
                                                <p class="text-xs text-gray-600 mt-1">${item.description}</p>
                                            </div>
                                            <div class="ml-4 text-right flex-shrink-0">
                                                <p class="text-xs text-gray-500 whitespace-nowrap">
                                                    <i class="far fa-clock mr-1"></i>
                                                    ${item.formatted_time}
                                                </p>
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
    
    content.innerHTML = `
        <!-- 1. Informasi Pengiriman -->
        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Informasi Pengiriman</h4>
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
                    <label class="text-sm font-medium text-gray-600">PIC Purchasing</label>
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

        <!-- 4. File Pengiriman -->
        ${filePengirimanSection}

        <!-- 5. Catatan -->
        ${pengiriman.catatan ? `
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h4 class="text-md font-semibold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                    Catatan
                </h4>
                <p class="text-sm text-gray-700">${pengiriman.catatan}</p>
            </div>
        ` : ''}

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
</style>
