{{-- Modal Detail Forecast Sukses --}}
<div id="detailForecastModalSukses" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-file-alt text-green-600 mr-2"></i>
                Detail Forecast Sukses
            </h3>
            <button onclick="closeDetailModalSukses()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="detailContentSukses" class="space-y-6">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
// Function to open detail modal for sukses
function openDetailModalSukses(forecastId) {
    console.log('Opening detail modal for sukses forecast ID:', forecastId);
    
    // Show loading state
    const modal = document.getElementById('detailForecastModalSukses');
    const content = document.getElementById('detailContentSukses');
    
    content.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-green-600 text-2xl mr-3"></i>
            <span class="text-gray-600">Memuat detail forecast...</span>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Fetch forecast detail
    fetch(`/purchasing/forecast/${forecastId}/detail`, {
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
                    <p class="text-red-600">${data.message || 'Gagal memuat detail forecast'}</p>
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

// Function to close detail modal for sukses
function closeDetailModalSukses() {
    document.getElementById('detailForecastModalSukses').classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('detailForecastModalSukses');
    if (event.target === modal) {
        closeDetailModalSukses();
    }
});
</script>
