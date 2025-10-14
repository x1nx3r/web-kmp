{{-- Modal Detail Pengiriman Berhasil --}}
<div id="detailPengirimanModalBerhasil" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-truck text-green-600 mr-2"></i>
                Detail Pengiriman Berhasil
            </h3>
            <button onclick="closeDetailModalBerhasil()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="detailContentBerhasil" class="space-y-6">
            <!-- Content will be populated by JavaScript -->
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
    
    modal.classList.remove('hidden');
    
    // Fetch pengiriman detail
    fetch(`/purchasing/pengiriman/${pengirimanId}/detail-berhasil`, {
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
            content.innerHTML = data.html;
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
    document.getElementById('detailPengirimanModalBerhasil').classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('detailPengirimanModalBerhasil');
    if (event.target === modal) {
        closeDetailModalBerhasil();
    }
});
</script>
