{{-- Modal Delete Forecast --}}
<div id="deleteForecastModal" class="hidden fixed inset-0 bg-white/20 backdrop-blur-xs bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
        {{-- Header --}}
        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-red-600 flex items-center">
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-exclamation-triangle text-white"></i>
                </div>
                Konfirmasi Hapus Forecast
            </h3>
            <button type="button" onclick="closeDeleteForecastModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        {{-- Content --}}
        <div class="mt-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Peringatan:</strong> Tindakan ini akan menghapus forecast secara permanen dan tidak dapat dibatalkan.
                </p>
            </div>

            <div class="mb-6">
                <p class="text-gray-700 mb-4">
                    Apakah Anda yakin ingin menghapus forecast berikut?
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex">
                            <span class="text-gray-600 w-32">No. Forecast:</span>
                            <span id="delete_no_forecast" class="font-semibold text-gray-900"></span>
                        </div>
                    </div>
                </div>
            </div>

            <form id="deleteForecastForm">
                @csrf
                <input type="hidden" id="delete_forecast_id" name="forecast_id">

                {{-- Alasan Hapus --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-comment-alt text-red-500 mr-2"></i>
                        Alasan Penghapusan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="delete_alasan" 
                              name="alasan_hapus"
                              rows="4"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-200 focus:border-red-500 transition-all"
                              placeholder="Masukkan alasan mengapa forecast ini dihapus (minimal 10 karakter)..."
                              required
                              minlength="10"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter</p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" 
                            onclick="closeDeleteForecastModal()"
                            class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-all duration-200 font-semibold">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" 
                            id="submitDeleteForecast"
                            class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 font-semibold">
                        <i class="fas fa-trash mr-2"></i>Ya, Hapus Forecast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Open Delete Forecast Modal
function openDeleteForecastModal(forecastId, noForecast) {
    document.getElementById('deleteForecastModal').classList.remove('hidden');
    document.getElementById('delete_forecast_id').value = forecastId;
    document.getElementById('delete_no_forecast').textContent = noForecast;
    document.getElementById('delete_alasan').value = '';
}

// Close Delete Modal
function closeDeleteForecastModal() {
    document.getElementById('deleteForecastModal').classList.add('hidden');
    document.getElementById('deleteForecastForm').reset();
}

// Submit Delete Form
document.getElementById('deleteForecastForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const forecastId = document.getElementById('delete_forecast_id').value;
    const alasan = document.getElementById('delete_alasan').value;
    const submitButton = document.getElementById('submitDeleteForecast');
    
    // Validate alasan
    if (alasan.length < 10) {
        alert('Alasan penghapusan minimal 10 karakter');
        return;
    }
    
    // Confirm deletion
    if (!confirm('Apakah Anda benar-benar yakin ingin menghapus forecast ini? Tindakan ini tidak dapat dibatalkan.')) {
        return;
    }
    
    // Disable submit button
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menghapus...';
    
    fetch(`/procurement/forecasting/${forecastId}/delete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            alasan_hapus: alasan
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Forecast berhasil dihapus');
            closeDeleteForecastModal();
            location.reload(); // Reload to show updated data
        } else {
            alert('Gagal menghapus forecast: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus forecast');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-trash mr-2"></i>Ya, Hapus Forecast';
    });
});
</script>
