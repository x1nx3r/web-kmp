{{-- Modal Detail User --}}
<div id="userDetailModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex min-h-screen items-end justify-center px-2 pt-4 pb-4 text-center sm:items-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0  backdrop-blur-xs transition-opacity" onclick="closeUserDetailModal()"></div>

        {{-- Modal container --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full mx-2 sm:mx-auto max-h-[90vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 sm:px-6 py-3 sm:py-4 border-b border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm sm:text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-blue-800" id="modalUserName">Detail Akun</h3>
                            <p class="text-xs sm:text-sm text-blue-600" id="modalUserRole">Informasi lengkap pengguna</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeUserDetailModal()" class="bg-white rounded-full p-1.5 sm:p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times text-sm sm:text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div class="bg-white px-4 sm:px-6 py-4 sm:py-6">
                <div class="space-y-4 sm:space-y-6">
                    {{-- User Profile Section --}}
                    <div class="flex items-center space-x-3 sm:space-x-4 p-3 sm:p-4 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <div id="modalUserAvatar" class="h-12 w-12 sm:h-16 sm:w-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                <span class="text-white font-bold text-sm sm:text-xl" id="modalUserInitials">JD</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-lg sm:text-xl font-bold text-gray-900 truncate" id="modalUserFullName">John Doe</h4>
                            <p class="text-xs sm:text-sm text-gray-500 truncate" id="modalUserEmail">john@example.com</p>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" id="modalUserStatusBadge">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Aktif
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" id="modalUserRoleBadge">
                                    <i class="fas fa-crown mr-1"></i>
                                    Direktur
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- User Information Grid --}}
                    <div class="space-y-4">
                        {{-- Basic Information --}}
                        <div class="space-y-3">
                            <h5 class="text-xs sm:text-sm font-bold text-blue-700 uppercase tracking-wider border-b border-blue-200 pb-2">
                                <i class="fas fa-info-circle mr-1 sm:mr-2"></i>
                                Informasi Dasar
                            </h5>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ID Pengguna</label>
                                    <p class="text-sm font-mono bg-gray-100 px-2 sm:px-3 py-1.5 sm:py-2 rounded" id="modalUserId">#001</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Username</label>
                                    <p class="text-sm font-mono bg-gray-100 px-2 sm:px-3 py-1.5 sm:py-2 rounded" id="modalUsername">johndoe</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Email</label>
                                <p class="text-sm text-gray-900 break-all" id="modalEmailFull">john@example.com</p>
                            </div>
                        </div>

                        {{-- Account Status --}}
                        <div class="space-y-3">
                            <h5 class="text-xs sm:text-sm font-bold text-blue-700 uppercase tracking-wider border-b border-blue-200 pb-2">
                                <i class="fas fa-cog mr-1 sm:mr-2"></i>
                                Status Akun
                            </h5>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Role</label>
                                    <div id="modalRoleDetail">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-crown mr-1"></i>
                                            Direktur
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Status</label>
                                    <div id="modalStatusDetail">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Aktif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Foto Profil</label>
                                <p class="text-sm text-gray-600" id="modalPhotoStatus">Tidak ada foto</p>
                            </div>
                        </div>
                    </div>

                    {{-- Timestamps --}}
                    <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                        <h5 class="text-xs sm:text-sm font-bold text-blue-700 uppercase tracking-wider mb-3">
                            <i class="fas fa-clock mr-1 sm:mr-2"></i>
                            Riwayat Akun
                        </h5>
                        <div class="space-y-3 sm:grid sm:grid-cols-2 sm:gap-4 sm:space-y-0">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Dibuat Pada</label>
                                <div class="flex items-center text-sm text-gray-900">
                                    <i class="fas fa-calendar-plus text-blue-500 mr-2"></i>
                                    <div>
                                        <span id="modalCreatedDate">21 Sep 2025</span>
                                        <span class="text-gray-500 block text-xs" id="modalCreatedTime">14:30 WIB</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Terakhir Diubah</label>
                                <div class="flex items-center text-sm text-gray-900">
                                    <i class="fas fa-calendar-edit text-blue-500 mr-2"></i>
                                    <div>
                                        <span id="modalUpdatedDate">21 Sep 2025</span>
                                        <span class="text-gray-500 block text-xs" id="modalUpdatedTime">16:45 WIB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                </div>
            </div>

           
        </div>
    </div>
</div>

<script>
// Global variable to store current user data
let currentUserData = null;

// Function to open user detail modal
async function openUserDetailModal(userId) {
    // Show loading state
    document.getElementById('userDetailModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Show loading in modal
    document.getElementById('modalUserName').textContent = 'Memuat...';
    document.getElementById('modalUserRole').textContent = 'Mengambil data pengguna';

    try {
        // Fetch user data from server
        const userData = await getUserData(userId);
        if (!userData) {
            alert('Data pengguna tidak ditemukan');
            closeUserDetailModal();
            return;
        }

        currentUserData = userData;
        populateModalWithUserData(userData);
    } catch (error) {
        console.error('Error loading user data:', error);
        alert('Gagal memuat data pengguna');
        closeUserDetailModal();
    }
}

// Function to close user detail modal
function closeUserDetailModal() {
    document.getElementById('userDetailModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentUserData = null;
}

// Function to get user data from server
async function getUserData(userId) {
    try {
        const response = await fetch(`/pengelolaan-akun/${userId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        return data.user;
    } catch (error) {
        console.error('Error fetching user data:', error);
        return null;
    }
}

// Function to populate modal with user data
function populateModalWithUserData(user) {
    // Role configuration
    const roleConfig = {
        'direktur': { label: 'Direktur', color: 'red', icon: 'fas fa-crown' },
        'marketing': { label: 'Marketing', color: 'blue', icon: 'fas fa-bullhorn' },
        'manager_purchasing': { label: 'Manager Purchasing', color: 'blue', icon: 'fas fa-user-tie' },
        'staff_purchasing': { label: 'Staff Purchasing', color: 'blue', icon: 'fas fa-user' },
        'staff_accounting': { label: 'Staff Accounting', color: 'yellow', icon: 'fas fa-calculator' },
        'manager_accounting': { label: 'Manager Accounting', color: 'yellow', icon: 'fas fa-chart-line' }
    };

    const config = roleConfig[user.role];
    
    // Update modal title and role
    document.getElementById('modalUserName').textContent = user.nama;
    document.getElementById('modalUserRole').textContent = config.label;

    // Update user avatar and initials
    const initials = user.nama.split(' ').map(n => n[0]).join('').toUpperCase();
    document.getElementById('modalUserInitials').textContent = initials;

    // Update profile section
    document.getElementById('modalUserFullName').textContent = user.nama;
    document.getElementById('modalUserEmail').textContent = user.email;

    // Update status badge
    const statusBadge = document.getElementById('modalUserStatusBadge');
    if (user.status === 'aktif') {
        statusBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
        statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Aktif';
    } else {
        statusBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800';
        statusBadge.innerHTML = '<i class="fas fa-times-circle mr-1"></i>Tidak Aktif';
    }

    // Update role badge
    const roleBadge = document.getElementById('modalUserRoleBadge');
    roleBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${config.color}-100 text-${config.color}-800`;
    roleBadge.innerHTML = `<i class="${config.icon} mr-1"></i>${config.label}`;

    // Update information fields
    document.getElementById('modalUserId').textContent = `${String(user.id).padStart(3, '0')}`;
    document.getElementById('modalUsername').textContent = user.username;
    document.getElementById('modalEmailFull').textContent = user.email;

    // Update role detail
    const roleDetail = document.getElementById('modalRoleDetail');
    roleDetail.innerHTML = `<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-${config.color}-100 text-${config.color}-800">
        <i class="${config.icon} mr-1"></i>${config.label}
    </span>`;

    // Update status detail
    const statusDetail = document.getElementById('modalStatusDetail');
    if (user.status === 'aktif') {
        statusDetail.innerHTML = `<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-check-circle mr-1"></i>Aktif
        </span>`;
    } else {
        statusDetail.innerHTML = `<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
            <i class="fas fa-times-circle mr-1"></i>Tidak Aktif
        </span>`;
    }

    // Update photo status
    document.getElementById('modalPhotoStatus').textContent = user.foto_profil ? 'Ada foto profil' : 'Tidak ada foto';

    // Update timestamps
    const createdDate = new Date(user.created_at);
    const updatedDate = new Date(user.updated_at);

    document.getElementById('modalCreatedDate').textContent = createdDate.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
    document.getElementById('modalCreatedTime').textContent = createdDate.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    }) + ' WIB';

    document.getElementById('modalUpdatedDate').textContent = updatedDate.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
    document.getElementById('modalUpdatedTime').textContent = updatedDate.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    }) + ' WIB';
}



// Close modal when ESC key is pressed
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserDetailModal();
    }
});
</script>
