{{-- Modal Hapus User --}}
<div id="userDeleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0 backdrop-blur-xs transition-opacity" onclick="closeUserDeleteModal()"></div>

        {{-- Modal container --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-red-50 to-red-100 px-6 py-4 border-b border-red-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-red-800">Konfirmasi Hapus Akun</h3>
                            <p class="text-sm text-red-600">Tindakan ini tidak dapat dibatalkan</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeUserDeleteModal()" class="bg-white rounded-full p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div class="bg-white px-6 py-6">
                <div class="space-y-6">
                    {{-- Warning Message --}}
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                            <i class="fas fa-user-times text-red-600 text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Apakah Anda yakin ingin menghapus akun ini?</h4>
                        <p class="text-sm text-gray-600">
                            Akun yang dihapus tidak dapat dikembalikan dan semua data terkait akan hilang permanen.
                        </p>
                    </div>

                    {{-- User Info Card --}}
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div id="modalDeleteUserAvatar" class="h-12 w-12 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm" id="modalDeleteUserInitials">JD</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h5 class="text-sm font-bold text-gray-900" id="modalDeleteUserName">John Doe</h5>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" id="modalDeleteUserStatusBadge">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Aktif
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500" id="modalDeleteUserEmail">john@example.com</p>
                                <div class="flex items-center mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" id="modalDeleteUserRoleBadge">
                                        <i class="fas fa-crown mr-1"></i>
                                        Direktur
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Warning List --}}
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-red-800">Dampak Penghapusan Akun:</h4>
                                <div class="text-sm text-red-700 mt-1">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Akun tidak dapat digunakan untuk login</li>
                                        <li>Semua data aktivitas akan dihapus</li>
                                        <li>Riwayat transaksi akan tetap tersimpan (tanpa nama user)</li>
                                        <li>Data ini tidak dapat dikembalikan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Confirmation Input --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Ketik "<strong class="text-red-600">HAPUS</strong>" untuk mengonfirmasi penghapusan:
                        </label>
                        <input type="text" 
                               id="deleteConfirmationInput"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-200 focus:border-red-500 transition-colors"
                               placeholder="Ketik HAPUS"
                               onchange="validateDeleteConfirmation()"
                               oninput="validateDeleteConfirmation()">
                        <p class="text-xs text-gray-500 mt-1">Konfirmasi ini diperlukan untuk keamanan</p>
                    </div>

                    {{-- Alternative Actions --}}
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lightbulb text-yellow-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-yellow-800">Alternatif Lain:</h4>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Jika Anda hanya ingin menonaktifkan akun sementara, gunakan fitur <strong>Edit</strong> 
                                    untuk mengubah status menjadi "Tidak Aktif" alih-alih menghapus.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeUserDeleteModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </button>
                <button type="button" id="confirmDeleteButton" onclick="confirmDeleteUser()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-trash mr-2"></i>
                    Hapus Akun
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variable to store current delete user data
let currentDeleteUserData = null;

// Function to open user delete modal
async function openUserDeleteModal(userId) {
    console.log('Opening delete modal for user ID:', userId);
    
    // Show modal first
    document.getElementById('userDeleteModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Reset confirmation input
    document.getElementById('deleteConfirmationInput').value = '';
    document.getElementById('confirmDeleteButton').disabled = true;

    try {
        console.log('Fetching user data from:', `/pengelolaan-akun/${userId}`);
        
        // Fetch user data from server
        const response = await fetch(`/pengelolaan-akun/${userId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        console.log('Received user data:', data);
        
        const userData = data.user;

        currentDeleteUserData = userData;
        console.log('currentDeleteUserData set to:', currentDeleteUserData);
        populateDeleteModalWithUserData(userData);
    } catch (error) {
        console.error('Error fetching user data:', error);
        alert('Gagal memuat data pengguna');
        closeUserDeleteModal();
    }
}

// Function to close user delete modal
function closeUserDeleteModal() {
    document.getElementById('userDeleteModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    
    // Reset data with delay to allow success modal to use it
    document.getElementById('deleteConfirmationInput').value = '';
    document.getElementById('confirmDeleteButton').disabled = true;
    
    // Clear currentDeleteUserData after a delay to allow success modal to use it
    setTimeout(() => {
        currentDeleteUserData = null;
    }, 1000);
}

// Function to populate delete modal with user data
function populateDeleteModalWithUserData(user) {
    console.log('Populating delete modal with user data:', user);
    
    // Role configuration
    const roleConfig = {
        'direktur': { label: 'Direktur', color: 'red', icon: 'fas fa-crown' },
        'marketing': { label: 'Marketing', color: 'blue', icon: 'fas fa-bullhorn' },
        'manager_purchasing': { label: 'Manager Procurement', color: 'green', icon: 'fas fa-user-tie' },
        'staff_purchasing': { label: 'Staff Procurement', color: 'green', icon: 'fas fa-user' },
        'staff_accounting': { label: 'Staff Accounting', color: 'yellow', icon: 'fas fa-calculator' },
        'manager_accounting': { label: 'Manager Accounting', color: 'yellow', icon: 'fas fa-chart-line' }
    };

    const config = roleConfig[user.role];
    console.log('Role config:', config);
    
    // Update user info
    document.getElementById('modalDeleteUserName').textContent = user.nama;
    document.getElementById('modalDeleteUserEmail').textContent = user.email;

    // Update user avatar and initials
    const initials = user.nama.split(' ').map(n => n[0]).join('').toUpperCase();
    document.getElementById('modalDeleteUserInitials').textContent = initials;

    // Update status badge
    const statusBadge = document.getElementById('modalDeleteUserStatusBadge');
    if (user.status === 'aktif') {
        statusBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
        statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Aktif';
    } else {
        statusBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
        statusBadge.innerHTML = '<i class="fas fa-times-circle mr-1"></i>Tidak Aktif';
    }

    // Update role badge
    const roleBadge = document.getElementById('modalDeleteUserRoleBadge');
    roleBadge.className = `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-${config.color}-100 text-${config.color}-800`;
    roleBadge.innerHTML = `<i class="${config.icon} mr-1"></i>${config.label}`;
    
    console.log('Delete modal populated successfully');
}

// Function to validate delete confirmation
function validateDeleteConfirmation() {
    const input = document.getElementById('deleteConfirmationInput');
    const button = document.getElementById('confirmDeleteButton');
    
    if (input.value.toUpperCase() === 'HAPUS') {
        button.disabled = false;
        input.classList.remove('border-gray-300');
        input.classList.add('border-green-300', 'bg-green-50');
    } else {
        button.disabled = true;
        input.classList.remove('border-green-300', 'bg-green-50');
        input.classList.add('border-gray-300');
    }
}

// Function to confirm delete user
async function confirmDeleteUser() {
    console.log('confirmDeleteUser called');
    console.log('currentDeleteUserData:', currentDeleteUserData);
    
    // Check if currentDeleteUserData is available
    if (!currentDeleteUserData || !currentDeleteUserData.id) {
        console.error('No user data available for deletion');
        alert('Terjadi kesalahan: Data pengguna tidak ditemukan. Silakan tutup modal dan coba lagi.');
        return;
    }

    const confirmationInput = document.getElementById('deleteConfirmationInput');
    if (confirmationInput.value.toUpperCase() !== 'HAPUS') {
        alert('Mohon ketik "HAPUS" untuk mengonfirmasi penghapusan');
        return;
    }

    // Store user name before making request (to avoid null reference later)
    const userName = currentDeleteUserData.nama;
    console.log('User name stored for success modal:', userName);

    // Show loading state
    const deleteButton = document.getElementById('confirmDeleteButton');
    const originalText = deleteButton.innerHTML;
    deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menghapus...';
    deleteButton.disabled = true;

    try {
        console.log('Sending DELETE request to:', `/pengelolaan-akun/${currentDeleteUserData.id}`);
        
        // Send delete request to server
        const response = await fetch(`/pengelolaan-akun/${currentDeleteUserData.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();
        console.log('Server response:', result);
        console.log('Response status:', response.status, response.ok);

        if (response.ok) {
            // Close modal first
            closeUserDeleteModal();
            
            // Show success modal using stored userName to avoid null reference
            showSuccessModal('delete', `Akun ${userName} berhasil dihapus!`, 'Data pengguna telah dihapus dari sistem.');
        } else {
            alert(result.message || 'Terjadi kesalahan saat menghapus akun');
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        alert('Terjadi kesalahan saat menghubungi server');
    } finally {
        // Reset button state
        deleteButton.innerHTML = originalText;
        deleteButton.disabled = false;
    }
}

// Close modal when ESC key is pressed
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserDeleteModal();
    }
});

// Enhanced security: prevent accidental clicks
document.addEventListener('DOMContentLoaded', function() {
    const deleteButton = document.getElementById('confirmDeleteButton');
    if (deleteButton) {
        deleteButton.addEventListener('click', function(e) {
            if (this.disabled) {
                e.preventDefault();
                return false;
            }
            
            // Double confirmation for extra safety
            if (!confirm('Apakah Anda benar-benar yakin? Akun akan dihapus permanen!')) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
