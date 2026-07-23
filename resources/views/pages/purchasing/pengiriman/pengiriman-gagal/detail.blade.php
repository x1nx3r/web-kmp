{{-- Modal Detail Pengiriman Gagal --}}
<div id="detailPengirimanModalGagal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-truck text-red-600 mr-2"></i>
                Detail Pengiriman Gagal
            </h3>
            <button onclick="closeDetailModalGagal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="detailContentGagal" class="space-y-6">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
// Function to open detail modal for pengiriman gagal
function openDetailModalGagal(pengirimanId) {
    console.log('Opening detail modal for pengiriman gagal ID:', pengirimanId);
    
    // Show loading state
    const modal = document.getElementById('detailPengirimanModalGagal');
    const content = document.getElementById('detailContentGagal');
    
    content.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-red-600 text-2xl mr-3"></i>
            <span class="text-gray-600">Memuat detail pengiriman...</span>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Fetch pengiriman detail
    fetch(`/procurement/pengiriman/${pengirimanId}/detail-gagal`, {
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
            populateDetailModalGagal(data.pengiriman);
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

// Function to close detail modal for pengiriman gagal
function closeDetailModalGagal() {
    document.getElementById('detailPengirimanModalGagal').classList.add('hidden');
}

// Build the detail content from the JSON payload returned by getDetailGagal()
function populateDetailModalGagal(pengiriman) {
    const content = document.getElementById('detailContentGagal');

    // Detail Barang table (with Plat Nomor column)
    let detailsTable = '';
    if (pengiriman.details && pengiriman.details.length > 0) {
        detailsTable = `
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-boxes text-red-600 mr-2"></i>
                    Detail Barang
                </h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plat Nomor</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Kirim</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${pengiriman.details.map(detail => `
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${detail.bahan_baku}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${detail.supplier}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        <i class="fas fa-truck-moving text-gray-400 mr-1.5 text-xs"></i>${detail.plat_nomor_truk || '-'}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${parseFloat(detail.qty_kirim).toLocaleString('id-ID', {minimumFractionDigits: 3})} kg</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">Rp ${parseFloat(detail.harga_satuan).toLocaleString('id-ID')}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">Rp ${parseFloat(detail.total_harga).toLocaleString('id-ID')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    content.innerHTML = `
        <!-- Informasi Pengiriman -->
        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Informasi Pengiriman</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="text-sm font-medium text-gray-600">No Pengiriman</label><p class="text-sm text-gray-900 font-medium">${pengiriman.no_pengiriman}</p></div>
                <div><label class="text-sm font-medium text-gray-600">Status</label><p class="text-sm"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1"></i>${pengiriman.status}</span></p></div>
                <div><label class="text-sm font-medium text-gray-600">No PO</label><p class="text-sm text-gray-900">${pengiriman.no_po}</p></div>
                <div><label class="text-sm font-medium text-gray-600">PIC Procurement</label><p class="text-sm text-gray-900">${pengiriman.pic_purchasing}</p></div>
                <div><label class="text-sm font-medium text-gray-600">Tanggal Kirim</label><p class="text-sm text-gray-900">${pengiriman.tanggal_kirim}</p></div>
                <div><label class="text-sm font-medium text-gray-600">Hari Kirim</label><p class="text-sm text-gray-900">${pengiriman.hari_kirim}</p></div>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Ringkasan</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="text-sm font-medium text-gray-600">Total Quantity</label><p class="text-lg font-bold text-blue-600">${pengiriman.total_qty}</p></div>
                <div><label class="text-sm font-medium text-gray-600">Total Harga</label><p class="text-lg font-bold text-green-600">${pengiriman.total_harga}</p></div>
                <div><label class="text-sm font-medium text-gray-600">Total Item</label><p class="text-lg font-bold text-purple-600">${pengiriman.total_items} item</p></div>
            </div>
        </div>

        <!-- Detail Barang -->
        ${detailsTable}

        <!-- Alasan Gagal -->
        ${pengiriman.alasan_gagal ? `
            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
                <h4 class="text-md font-semibold text-red-900 mb-1 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Alasan Gagal
                </h4>
                <p class="text-sm text-red-800 whitespace-pre-line">${pengiriman.alasan_gagal}</p>
            </div>
        ` : ''}

        <!-- Catatan Refraksi -->
        ${pengiriman.catatan_refraksi ? `
            <div class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4">
                <h4 class="text-md font-semibold text-orange-900 mb-1">Catatan Refraksi</h4>
                <p class="text-sm text-orange-800 whitespace-pre-line">${pengiriman.catatan_refraksi}</p>
            </div>
        ` : ''}

        <!-- Catatan -->
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <h4 class="text-md font-semibold text-gray-900 mb-2 flex items-center">
                <i class="fas fa-sticky-note text-blue-600 mr-2"></i> Catatan
            </h4>
            <p class="text-sm text-gray-700 whitespace-pre-line">${pengiriman.catatan || 'Belum ada catatan'}</p>
        </div>
    `;
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('detailPengirimanModalGagal');
    if (event.target === modal) {
        closeDetailModalGagal();
    }
});
</script>