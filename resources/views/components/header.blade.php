<header class="fixed top-0 left-0 lg:left-64 right-0 bg-white backdrop-blur-sm border-b border-gray-200/50 px-4 lg:px-8 py-4 lg:py-10 z-30 h-16 lg:h-24 shadow-sm">
    <div class="flex items-center justify-between h-full">
        <!-- Mobile Hamburger & Logo -->
        <div class="flex items-center space-x-4 lg:hidden">
            <button id="mobileMenuToggle" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-all duration-200">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-white  flex items-center justify-center  overflow-hidden">
                    <img src="{{ asset('assets/image/logo/ptkmp-logo.png') }}" alt="Logo KMP" class="w-full h-full object-contain">
                </div>
                <div class="text-gray-800">
                    <h1 class="text-xs  md:text-lg md:font-bold">PT. Kamil Maju Persada</h1>
                </div>
            </div>
        </div>

        <!-- Desktop Welcome Section -->
        <div class="hidden lg:flex items-center space-x-4">
            <div class="text-gray-800">
                @auth
                <h2 class="text-lg font-semibold">Selamat Datang, {{ auth()->user()->nama }}</h2>
                <p class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
                @else
                <h2 class="text-lg font-semibold">Selamat Datang, Guest</h2>
                <p class="text-sm text-gray-600">Tamu</p>
                @endauth
            </div>
        </div>

        <!-- Right Header -->
        <div class="flex items-center space-x-3 lg:space-x-6">
            <!-- Quick Stats (Desktop Only) -->
            <div class="hidden xl:flex items-center space-x-6 text-sm">
                <!-- Can add quick stats here -->
            </div>

            <!-- Divider (Desktop Only) -->
            <div class="hidden xl:block w-px h-8 bg-gray-200"></div>

            <!-- Notifications -->
            @livewire('notification-bell')

            <!-- User Menu -->
            <div class="relative">
                <button class="flex items-center space-x-2 lg:space-x-3 text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-xl px-2 lg:px-4 py-2 transition-all duration-200" onclick="toggleUserMenu()">
                    @auth
                    <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-xl overflow-hidden shadow-lg ring-2 ring-gray-200">
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="Foto Profil {{ auth()->user()->nama }}" class="w-full h-full object-cover">
                    </div>
                    @else
                    <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-xs lg:text-sm"></i>
                    </div>
                    @endauth
                    <div class="hidden md:block text-left">
                        @auth
                        <p class="font-semibold text-sm">{{ auth()->user()->nama }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
                        @else
                        <p class="font-semibold text-sm">Guest</p>
                        <p class="text-xs text-gray-500">Tamu</p>
                        @endauth
                    </div>
                    <i class="fas fa-chevron-down text-xs lg:text-sm"></i>
                </button>

                <!-- User Dropdown Menu -->
                <div id="userMenu" class="absolute right-0 mt-3 w-48 lg:w-64 bg-white rounded-xl shadow-lg border border-gray-100 py-2 hidden z-50">
                    <div class="px-4 py-3 border-b border-gray-100">
                        @auth
                                <p class="font-semibold text-gray-800 truncate">{{ auth()->user()->nama }}</p>
                                <p class="text-sm text-gray-500 truncate" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
                        @else
                                <p class="font-semibold text-gray-800">Guest</p>
                                <p class="text-sm text-gray-500">-</p>
                                <p class="text-xs text-gray-400 mt-1">Tamu</p>
                        @endauth
                    </div>

                    <a href="{{ route('pengaturan') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-cog mr-3 w-4"></i>Pengaturan
                    </a>
                    <div class="border-t border-gray-100 mt-2"></div>
                    <button onclick="showLogoutModal()" class="flex items-center w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3 w-4"></i>Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
// User Menu Toggle
function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('hidden');
}

// Mobile Menu Toggle
function toggleMobileMenu() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('mobileOverlay');

    if (sidebar && overlay) {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }
}

// Close mobile menu
function closeMobileMenu() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('mobileOverlay');

    if (sidebar && overlay) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

// Show logout modal
function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    document.getElementById('userMenu').classList.add('hidden');
}

// Hide logout modal
function hideLogoutModal() {
    const modal = document.getElementById('logoutModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

// Handle logout
function confirmLogout() {
    // Create form and submit logout request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("logout") }}';

    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);

    document.body.appendChild(form);
    form.submit();
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobileMenuToggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleMobileMenu);
    }

    const overlay = document.getElementById('mobileOverlay');
    if (overlay) {
        overlay.addEventListener('click', closeMobileMenu);
    }

    document.addEventListener('click', function(event) {
        const userMenuButton = event.target.closest('button[onclick="toggleUserMenu()"]');
        const userMenu = document.getElementById('userMenu');

        if (!userMenuButton && userMenu && !userMenu.classList.contains('hidden')) {
            userMenu.classList.add('hidden');
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            closeMobileMenu();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeMobileMenu();
            hideLogoutModal();
            const userMenu = document.getElementById('userMenu');
            if (userMenu) {
                userMenu.classList.add('hidden');
            }
        }
    });

    // Close logout modal when clicking outside
    const logoutModal = document.getElementById('logoutModal');
    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideLogoutModal();
            }
        });
    }
});
</script>

<!-- Logout Modal -->
<div id="logoutModal" class="fixed inset-0 bg-black/20 backdrop-blur-xs z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-sign-out-alt text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Logout</h3>
                <p class="text-sm text-gray-600">Apakah Anda yakin ingin keluar?</p>
            </div>
        </div>

        <div class="mb-6">
            <p class="text-gray-700">Anda akan keluar dari sistem dan perlu login kembali untuk mengakses halaman ini.</p>
        </div>

        <div class="flex space-x-3">
            <button onclick="hideLogoutModal()" class="flex-1 px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                Batal
            </button>
            <button onclick="confirmLogout()" class="flex-1 px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200">
                <i class="fas fa-sign-out-alt mr-2"></i>Keluar
            </button>
        </div>
    </div>
</div>
