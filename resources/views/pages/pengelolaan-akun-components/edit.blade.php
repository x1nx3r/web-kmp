{{-- Modal Edit User --}}
<div id="userEditModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0  backdrop-blur-xs transition-opacity" onclick="closeUserEditModal()"></div>

        {{-- Modal container --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 px-6 py-4 border-b border-yellow-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-edit text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-yellow-800" id="modalEditUserName">Edit Akun</h3>
                            <p class="text-sm text-yellow-600" id="modalEditUserRole">Ubah informasi pengguna</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeUserEditModal()" class="bg-white rounded-full p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div class="bg-white px-6 py-6">
                <form id="editUserForm" onsubmit="saveUserChanges(event)">
                    <div class="space-y-6">
                        {{-- User Profile Section --}}
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div id="modalEditUserAvatar" class="h-16 w-16 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center">
                                    <span class="text-white font-bold text-xl" id="modalEditUserInitials">JD</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Preview Profil</h4>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" id="modalEditUserStatusBadge">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Aktif
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" id="modalEditUserRoleBadge">
                                        <i class="fas fa-crown mr-1"></i>
                                        Direktur
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Form Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Basic Information --}}
                            <div class="space-y-4">
                                <h5 class="text-sm font-bold text-yellow-700 uppercase tracking-wider border-b border-yellow-200 pb-2">
                                    <i class="fas fa-user mr-2"></i>
                                    Informasi Dasar
                                </h5>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                    <input type="text" 
                                           id="editNama" 
                                           name="nama"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 transition-colors"
                                           placeholder="Masukkan nama lengkap"
                                           required
                                           onchange="updatePreview()">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                    <input type="text" 
                                           id="editUsername" 
                                           name="username"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 transition-colors"
                                           placeholder="Masukkan username"
                                           required
                                           onchange="updatePreview()">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" 
                                           id="editEmail" 
                                           name="email"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 transition-colors"
                                           placeholder="Masukkan email"
                                           required
                                           onchange="updatePreview()">
                                </div>
                            </div>

                            {{-- Account Settings --}}
                            <div class="space-y-4">
                                <h5 class="text-sm font-bold text-yellow-700 uppercase tracking-wider border-b border-yellow-200 pb-2">
                                    <i class="fas fa-cog mr-2"></i>
                                    Pengaturan Akun
                                </h5>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                    <select id="editRole" 
                                            name="role"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 transition-colors"
                                            required
                                            onchange="updatePreview()">
                                        <option value="">Pilih Role</option>
                                        <option value="direktur">Direktur</option>
                                        <option value="marketing">Marketing</option>
                                        <option value="manager_purchasing">Manager Purchasing</option>
                                        <option value="staff_purchasing">Staff Purchasing</option>
                                        <option value="manager_accounting">Manager Accounting</option>
                                        <option value="staff_accounting">Staff Accounting</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select id="editStatus" 
                                            name="status"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 transition-colors"
                                            required
                                            onchange="updatePreview()">
                                        <option value="aktif">Aktif</option>
                                        <option value="tidak_aktif">Tidak Aktif</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Reset Password</label>
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               id="resetPassword" 
                                               name="reset_password"
                                               class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                                        <label for="resetPassword" class="text-sm text-gray-700">
                                            Reset password ke default (password123)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Info Panel --}}
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-blue-800">Informasi Perubahan</h4>
                                    <div class="text-sm text-blue-700 mt-1">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Perubahan akan disimpan secara permanen</li>
                                            <li>Jika reset password dicentang, password akan direset ke "password123"</li>
                                            <li>User akan menerima notifikasi jika ada perubahan pada akun</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Modal Footer --}}
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeUserEditModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </button>
                <button type="button" onclick="saveUserChanges()" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variable to store current edit user data
let currentEditUserData = null;

// Function to open user edit modal
function openUserEditModal(userId) {
    // Find user data (in real app, this would be an API call)
    const userData = getUserData(userId);
    if (!userData) {
        alert('Data pengguna tidak ditemukan');
        return;
    }

    currentEditUserData = userData;
    populateEditModalWithUserData(userData);
    
    // Show modal
    document.getElementById('userEditModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Function to close user edit modal
function closeUserEditModal() {
    document.getElementById('userEditModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentEditUserData = null;
}

// Function to populate edit modal with user data
function populateEditModalWithUserData(user) {
    // Role configuration
    const roleConfig = {
        'direktur': { label: 'Direktur', color: 'red', icon: 'fas fa-crown' },
        'marketing': { label: 'Marketing', color: 'blue', icon: 'fas fa-bullhorn' },
        'manager_purchasing': { label: 'Manager Purchasing', color: 'green', icon: 'fas fa-user-tie' },
        'staff_purchasing': { label: 'Staff Purchasing', color: 'green', icon: 'fas fa-user' },
        'staff_accounting': { label: 'Staff Accounting', color: 'yellow', icon: 'fas fa-calculator' },
        'manager_accounting': { label: 'Manager Accounting', color: 'yellow', icon: 'fas fa-chart-line' }
    };

    const config = roleConfig[user.role];
    
    // Update modal title
    document.getElementById('modalEditUserName').textContent = `Edit ${user.nama}`;
    document.getElementById('modalEditUserRole').textContent = `Ubah informasi ${config.label}`;

    // Update user avatar and initials
    const initials = user.nama.split(' ').map(n => n[0]).join('').toUpperCase();
    document.getElementById('modalEditUserInitials').textContent = initials;

    // Populate form fields
    document.getElementById('editNama').value = user.nama;
    document.getElementById('editUsername').value = user.username;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editRole').value = user.role;
    document.getElementById('editStatus').value = user.status;

    // Update preview
    updatePreview();
}

// Function to update preview based on form inputs
function updatePreview() {
    const nama = document.getElementById('editNama').value;
    const role = document.getElementById('editRole').value;
    const status = document.getElementById('editStatus').value;

    if (!nama || !role || !status) return;

    // Role configuration
    const roleConfig = {
        'direktur': { label: 'Direktur', color: 'red', icon: 'fas fa-crown' },
        'marketing': { label: 'Marketing', color: 'blue', icon: 'fas fa-bullhorn' },
        'manager_purchasing': { label: 'Manager Purchasing', color: 'green', icon: 'fas fa-user-tie' },
        'staff_purchasing': { label: 'Staff Purchasing', color: 'green', icon: 'fas fa-user' },
        'staff_accounting': { label: 'Staff Accounting', color: 'yellow', icon: 'fas fa-calculator' },
        'manager_accounting': { label: 'Manager Accounting', color: 'yellow', icon: 'fas fa-chart-line' }
    };

    const config = roleConfig[role];
    
    // Update initials
    const initials = nama.split(' ').map(n => n[0]).join('').toUpperCase();
    document.getElementById('modalEditUserInitials').textContent = initials;

    // Update status badge
    const statusBadge = document.getElementById('modalEditUserStatusBadge');
    if (status === 'aktif') {
        statusBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
        statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Aktif';
    } else {
        statusBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800';
        statusBadge.innerHTML = '<i class="fas fa-times-circle mr-1"></i>Tidak Aktif';
    }

    // Update role badge
    const roleBadge = document.getElementById('modalEditUserRoleBadge');
    roleBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${config.color}-100 text-${config.color}-800`;
    roleBadge.innerHTML = `<i class="${config.icon} mr-1"></i>${config.label}`;
}

// Function to save user changes
function saveUserChanges() {
    const formData = {
        id: currentEditUserData.id,
        nama: document.getElementById('editNama').value,
        username: document.getElementById('editUsername').value,
        email: document.getElementById('editEmail').value,
        role: document.getElementById('editRole').value,
        status: document.getElementById('editStatus').value,
        reset_password: document.getElementById('resetPassword').checked
    };

    // Validate form
    if (!formData.nama || !formData.username || !formData.email || !formData.role || !formData.status) {
        alert('Mohon lengkapi semua field yang diperlukan');
        return;
    }

    // Show loading state
    const saveButton = event.target;
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    saveButton.disabled = true;

    // Simulate API call (replace with actual API call)
    setTimeout(() => {
        // In real application, send data to server
        console.log('Saving user data:', formData);
        
        // Show success message
        alert('Data pengguna berhasil diperbarui!');
        
        // Close modal
        closeUserEditModal();
        
        // Reload page or update UI
        window.location.reload();
        
        // Reset button state
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    }, 1500);
}

// Close modal when ESC key is pressed
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserEditModal();
    }
});
</script>
