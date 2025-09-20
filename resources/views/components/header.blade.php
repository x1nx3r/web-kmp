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
                    <h1 class="text-lg font-bold">PT. Kamil Maju Persada</h1>
                </div>
            </div>
        </div>

        <!-- Desktop Welcome Section -->
        <div class="hidden lg:flex items-center space-x-4">
            <div class="text-gray-800">
                <h2 class="text-lg font-semibold">Selamat Datang, John Doe</h2>
                <p class="text-sm text-gray-600">Administrator</p>
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
            <div class="relative">
                <button class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-all duration-200 relative">
                    <i class="fas fa-bell text-lg"></i>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                </button>
            </div>

            <!-- User Menu -->
            <div class="relative">
                <button class="flex items-center space-x-2 lg:space-x-3 text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-xl px-2 lg:px-4 py-2 transition-all duration-200" onclick="toggleUserMenu()">
                    <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-xs lg:text-sm"></i>
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="font-semibold text-sm">John Doe</p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                    <i class="fas fa-chevron-down text-xs lg:text-sm"></i>
                </button>

                <!-- User Dropdown Menu -->
                <div id="userMenu" class="absolute right-0 mt-3 w-48 lg:w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 hidden z-50">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="font-semibold text-gray-800">John Doe</p>
                        <p class="text-sm text-gray-500">john.doe@example.com</p>
                        <p class="text-xs text-gray-400 mt-1">Administrator</p>
                    </div>

                    <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-cog mr-3 w-4"></i>Pengaturan
                    </a>
                    <div class="border-t border-gray-100 mt-2"></div>
                    <button onclick="handleLogout()" class="flex items-center w-full px-4 py-3 text-sm text-green-600 hover:bg-green-50 transition-colors duration-200">
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

// Handle logout
function handleLogout() {
    if (confirm('Apakah Anda yakin ingin keluar?')) {
        alert('Logout berhasil! (Ini hanya demo)');
    }
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
            const userMenu = document.getElementById('userMenu');
            if (userMenu) {
                userMenu.classList.add('hidden');
            }
        }
    });
});
</script>