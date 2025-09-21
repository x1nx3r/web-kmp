{{-- Modal Tambah User --}}
<div id="userCreateModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0  backdrop-blur-xs transition-opacity" onclick="closeUserCreateModal()"></div>

        {{-- Modal container --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4 border-b border-green-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-plus text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-green-800">Tambah Akun Baru</h3>
                            <p class="text-sm text-green-600">Buat akun pengguna baru</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeUserCreateModal()" class="bg-white rounded-full p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div class="bg-white px-6 py-6">
                <form id="createUserForm" onsubmit="createUser(event)">
                    <div class="space-y-6">
                        {{-- Preview Section --}}
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div id="modalCreateUserAvatar" class="h-16 w-16 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                    <span class="text-white font-bold text-xl" id="modalCreateUserInitials">??</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Preview Profil</h4>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" id="modalCreateUserStatusBadge">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Aktif
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800" id="modalCreateUserRoleBadge">
                                        <i class="fas fa-user mr-1"></i>
                                        Pilih Role
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Form Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Basic Information --}}
                            <div class="space-y-4">
                                <h5 class="text-sm font-bold text-green-700 uppercase tracking-wider border-b border-green-200 pb-2">
                                    <i class="fas fa-user mr-2"></i>
                                    Informasi Dasar
                                </h5>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" 
                                           id="createNama" 
                                           name="nama"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-colors"
                                           placeholder="Masukkan nama lengkap"
                                           required
                                           onchange="updateCreatePreview()">
                                    <p class="text-xs text-gray-500 mt-1">Contoh: John Doe</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                                    <input type="text" 
                                           id="createUsername" 
                                           name="username"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-colors"
                                           placeholder="Masukkan username"
                                           required
                                           onchange="updateCreatePreview()"
                                           pattern="[a-zA-Z0-9_]+"
                                           title="Username hanya boleh mengandung huruf, angka, dan underscore">
                                    <p class="text-xs text-gray-500 mt-1">Hanya huruf, angka, dan underscore. Contoh: johndoe</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                                    <input type="email" 
                                           id="createEmail" 
                                           name="email"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-colors"
                                           placeholder="Masukkan alamat email"
                                           required
                                           onchange="updateCreatePreview()">
                                    <p class="text-xs text-gray-500 mt-1">Contoh: john@example.com</p>
                                </div>
                            </div>

                            {{-- Account Settings --}}
                            <div class="space-y-4">
                                <h5 class="text-sm font-bold text-green-700 uppercase tracking-wider border-b border-green-200 pb-2">
                                    <i class="fas fa-cog mr-2"></i>
                                    Pengaturan Akun
                                </h5>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                                    <select id="createRole" 
                                            name="role"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-colors"
                                            required
                                            onchange="updateCreatePreview()">
                                        <option value="">Pilih Role</option>
                                        <option value="direktur">Direktur</option>
                                        <option value="marketing">Marketing</option>
                                        <option value="manager_purchasing">Manager Purchasing</option>
                                        <option value="staff_purchasing">Staff Purchasing</option>
                                        <option value="manager_accounting">Manager Accounting</option>
                                        <option value="staff_accounting">Staff Accounting</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Pilih role sesuai jabatan pengguna</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select id="createStatus" 
                                            name="status"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-colors"
                                            onchange="updateCreatePreview()">
                                        <option value="aktif">Aktif</option>
                                        <option value="tidak_aktif">Tidak Aktif</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Status default: Aktif</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="password" 
                                               id="createPassword" 
                                               name="password"
                                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-colors"
                                               placeholder="Masukkan password"
                                               required
                                               minlength="6">
                                        <button type="button" 
                                                onclick="togglePasswordVisibility('createPassword', 'toggleCreatePasswordIcon')"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <i id="toggleCreatePasswordIcon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="password" 
                                               id="createPasswordConfirm" 
                                               name="password_confirmation"
                                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-colors"
                                               placeholder="Konfirmasi password"
                                               required
                                               minlength="6"
                                               onchange="validatePasswordMatch()">
                                        <button type="button" 
                                                onclick="togglePasswordVisibility('createPasswordConfirm', 'toggleCreatePasswordConfirmIcon')"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <i id="toggleCreatePasswordConfirmIcon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                        </button>
                                    </div>
                                    <p class="text-xs mt-1" id="passwordMatchMessage">Masukkan ulang password yang sama</p>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                </form>
            </div>

            {{-- Modal Footer --}}
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeUserCreateModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </button>
                <button type="button" onclick="createUser()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Akun
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Function to open user create modal
function openUserCreateModal() {
    // Reset form
    document.getElementById('createUserForm').reset();
    
    // Reset preview
    updateCreatePreview();
    
    // Show modal
    document.getElementById('userCreateModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Function to close user create modal
function closeUserCreateModal() {
    document.getElementById('userCreateModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    
    // Reset form
    document.getElementById('createUserForm').reset();
    
    // Reset password match validation
    document.getElementById('passwordMatchMessage').className = 'text-xs mt-1 text-gray-500';
    document.getElementById('passwordMatchMessage').textContent = 'Masukkan ulang password yang sama';
}

// Function to update create preview
function updateCreatePreview() {
    const nama = document.getElementById('createNama').value;
    const role = document.getElementById('createRole').value;
    const status = document.getElementById('createStatus').value;

    // Update initials
    if (nama) {
        const initials = nama.split(' ').map(n => n[0]).join('').toUpperCase();
        document.getElementById('modalCreateUserInitials').textContent = initials;
    } else {
        document.getElementById('modalCreateUserInitials').textContent = '??';
    }

    // Role configuration
    const roleConfig = {
        'direktur': { label: 'Direktur', color: 'red', icon: 'fas fa-crown' },
        'marketing': { label: 'Marketing', color: 'blue', icon: 'fas fa-bullhorn' },
        'manager_purchasing': { label: 'Manager Purchasing', color: 'green', icon: 'fas fa-user-tie' },
        'staff_purchasing': { label: 'Staff Purchasing', color: 'green', icon: 'fas fa-user' },
        'staff_accounting': { label: 'Staff Accounting', color: 'yellow', icon: 'fas fa-calculator' },
        'manager_accounting': { label: 'Manager Accounting', color: 'yellow', icon: 'fas fa-chart-line' }
    };

    // Update status badge
    const statusBadge = document.getElementById('modalCreateUserStatusBadge');
    if (status === 'aktif') {
        statusBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
        statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Aktif';
    } else {
        statusBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800';
        statusBadge.innerHTML = '<i class="fas fa-times-circle mr-1"></i>Tidak Aktif';
    }

    // Update role badge
    const roleBadge = document.getElementById('modalCreateUserRoleBadge');
    if (role && roleConfig[role]) {
        const config = roleConfig[role];
        roleBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${config.color}-100 text-${config.color}-800`;
        roleBadge.innerHTML = `<i class="${config.icon} mr-1"></i>${config.label}`;
    } else {
        roleBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
        roleBadge.innerHTML = '<i class="fas fa-user mr-1"></i>Pilih Role';
    }
}

// Function to toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash text-gray-400 hover:text-gray-600';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye text-gray-400 hover:text-gray-600';
    }
}

// Function to validate password match
function validatePasswordMatch() {
    const password = document.getElementById('createPassword').value;
    const confirmPassword = document.getElementById('createPasswordConfirm').value;
    const messageElement = document.getElementById('passwordMatchMessage');
    
    if (confirmPassword) {
        if (password === confirmPassword) {
            messageElement.className = 'text-xs mt-1 text-green-600';
            messageElement.innerHTML = '<i class="fas fa-check mr-1"></i>Password cocok';
        } else {
            messageElement.className = 'text-xs mt-1 text-red-600';
            messageElement.innerHTML = '<i class="fas fa-times mr-1"></i>Password tidak cocok';
        }
    } else {
        messageElement.className = 'text-xs mt-1 text-gray-500';
        messageElement.textContent = 'Masukkan ulang password yang sama';
    }
}

// Function to create user
function createUser() {
    const formData = {
        nama: document.getElementById('createNama').value,
        username: document.getElementById('createUsername').value,
        email: document.getElementById('createEmail').value,
        role: document.getElementById('createRole').value,
        status: document.getElementById('createStatus').value,
        password: document.getElementById('createPassword').value,
        password_confirmation: document.getElementById('createPasswordConfirm').value
    };

    // Validate form
    if (!formData.nama || !formData.username || !formData.email || !formData.role || !formData.password) {
        alert('Mohon lengkapi semua field yang diperlukan (*)');
        return;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(formData.email)) {
        alert('Format email tidak valid');
        return;
    }

    // Validate username format
    const usernameRegex = /^[a-zA-Z0-9_]+$/;
    if (!usernameRegex.test(formData.username)) {
        alert('Username hanya boleh mengandung huruf, angka, dan underscore');
        return;
    }

    // Validate password length
    if (formData.password.length < 6) {
        alert('Password minimal 6 karakter');
        return;
    }

    // Validate password confirmation
    if (formData.password !== formData.password_confirmation) {
        alert('Konfirmasi password tidak cocok');
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
        console.log('Creating user:', formData);
        
        // Show success message
        alert('Akun pengguna berhasil dibuat!');
        
        // Close modal
        closeUserCreateModal();
        
        // Reload page or update UI
        window.location.reload();
        
        // Reset button state
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    }, 2000);
}

// Close modal when ESC key is pressed
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserCreateModal();
    }
});

// Real-time password validation
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('createPassword');
    const confirmInput = document.getElementById('createPasswordConfirm');
    
    if (passwordInput && confirmInput) {
        confirmInput.addEventListener('input', validatePasswordMatch);
        passwordInput.addEventListener('input', function() {
            if (confirmInput.value) {
                validatePasswordMatch();
            }
        });
    }
});
</script>
