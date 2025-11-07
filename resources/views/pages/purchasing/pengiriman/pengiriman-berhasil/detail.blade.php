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

    // Generate bukti foto section
    let buktiSection = '';
    if (pengiriman.bukti_foto_urls && pengiriman.bukti_foto_urls.length > 0) {
        buktiSection = `
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-camera text-blue-600 mr-2"></i>
                    Bukti Foto Bongkar
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    ${pengiriman.bukti_foto_urls.map((url, index) => `
                        <div class="relative group image-grid-item">
                            <img src="${url}" 
                                 alt="Bukti foto bongkar ${index + 1}" 
                                 class="w-full h-24 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                 onclick="viewImageBerhasil('${url}', 'Bukti Foto Bongkar ${index + 1}')">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <div class="flex space-x-2">
                                    <button onclick="viewImageBerhasil('${url}', 'Bukti Foto Bongkar ${index + 1}')" 
                                            class="bg-white text-blue-600 p-2 rounded-full shadow-lg hover:bg-blue-50 transition-all image-action-btn">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    <button onclick="downloadImageBerhasil('${url}', 'bukti_bongkar_${index + 1}.jpg')" 
                                            class="bg-white text-green-600 p-2 rounded-full shadow-lg hover:bg-green-50 transition-all image-action-btn">
                                        <i class="fas fa-download text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="photo-counter">${index + 1}</div>
                        </div>
                    `).join('')}
                </div>
                <div class="flex justify-between items-center mt-3 pt-3 border-t border-blue-200">
                    <span class="text-sm text-gray-600">${pengiriman.bukti_foto_urls.length} foto tersedia</span>
                    <button onclick="downloadAllImagesBerhasil(${JSON.stringify(pengiriman.bukti_foto_urls).replace(/"/g, '&quot;')}, '${pengiriman.no_pengiriman}')" 
                            class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg transition-all">
                        <i class="fas fa-download mr-1"></i>
                        Download Semua
                    </button>
                </div>
            </div>
        `;
    }
    
    content.innerHTML = `
        <!-- Informasi Umum -->
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

        <!-- Ringkasan -->
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

        <!-- Review Pengiriman -->
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
                    ${!pengiriman.rating && !pengiriman.ulasan ? `
                        <div class="text-center py-3">
                            <i class="fas fa-star-o text-gray-400 text-2xl mb-2"></i>
                            <p class="text-sm text-gray-500 italic">Belum ada review untuk pengiriman ini</p>
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

        ${pengiriman.catatan ? `
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h4 class="text-md font-semibold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                    Catatan
                </h4>
                <p class="text-sm text-gray-700">${pengiriman.catatan}</p>
            </div>
        ` : ''}

        ${buktiSection}

        ${detailsTable}
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

// Function to download all images
function downloadAllImagesBerhasil(imageUrls, noPengiriman) {
    if (!imageUrls || imageUrls.length === 0) {
        alert('Tidak ada gambar untuk diunduh');
        return;
    }
    
    imageUrls.forEach((url, index) => {
        setTimeout(() => {
            const filename = `${noPengiriman}_bukti_bongkar_${index + 1}.jpg`;
            downloadImageBerhasil(url, filename);
        }, index * 500); // Delay 500ms between downloads
    });
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
</style>
