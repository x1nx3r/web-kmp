<!-- Desktop Sidebar -->
<div class="hidden lg:block fixed left-0 top-0 w-64 h-full bg-white shadow-xl z-40">
    <!-- Logo/Header -->
    <div class="p-6 border-b border-green-500">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-white flex items-center justify-center overflow-hidden">
                <img src="{{ asset('assets/image/logo/ptkmp-logo.png') }}" alt="Logo KMP" class="w-full h-full object-contain">
            </div>
            <div class="text-gray-800">
                <h1 class="text-xl font-bold tracking-wide">PT. KMP</h1>
                <p class="text-sm text-green-700">PT. Kamil Maju Persada</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-6 pb-24 overflow-y-auto" style="height: calc(100vh - 200px);">
        <ul class="space-y-1 px-4">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 {{ request()->routeIs('dashboard') ? 'text-green-800 bg-green-200' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <i class="fas fa-tachometer-alt w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('dashboard') ? 'text-green-600' : '' }}"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>

            <!-- Laporan -->
            <li>
                <a href="{{ route('laporan.po') }}" class="flex items-center space-x-3 {{ request()->routeIs('laporan.*') ? 'text-green-800 bg-green-200' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <i class="fas fa-chart-bar w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('laporan.*') ? 'text-green-600' : '' }}"></i>
                    <span class="font-medium">Laporan</span>
                </a>
            </li>

            <!-- Marketing Dropdown -->
            <li>
                <button onclick="toggleDropdown('marketing')" class="flex items-center justify-between w-full {{ request()->routeIs('klien.*') ? 'text-green-800 bg-green-50' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-bullhorn w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('klien.*') ? 'text-green-600' : '' }}"></i>
                        <span class="font-medium">Marketing</span>
                    </div>
                    <i id="marketing-chevron" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                </button>

                <!-- Submenu -->
                <ul id="marketing-menu" class="mt-2 ml-6 space-y-1 hidden">
                    <li>
                        <a href="#" class="flex items-center space-x-3 text-gray-700 hover:text-green-800 rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-handshake w-4 text-sm group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-medium">Order</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('klien.index') }}" class="flex items-center space-x-3 {{ request()->routeIs('klien.*') ? 'text-green-800 bg-green-50' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-users w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('klien.*') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Daftar Klien</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center space-x-3 text-gray-700 hover:text-green-800 rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-clipboard-list w-4 text-sm group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-medium">Spesifikasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center space-x-3 text-gray-700 hover:text-green-800 rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-file-invoice w-4 text-sm group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-medium">Penawaran</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Purchasing Dropdown -->
            <li>
                <button onclick="toggleDropdown('purchasing')" class="flex items-center justify-between w-full {{ request()->routeIs('supplier.*') || request()->routeIs('forecasting.*') || request()->routeIs('purchasing.pengiriman.*') ? 'text-green-800 bg-green-200' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-shopping-cart w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('supplier.*') || request()->routeIs('forecasting.*') || request()->routeIs('purchasing.pengiriman.*') ? 'text-green-600' : '' }}"></i>
                        <span class="font-medium">Procurement</span>
                    </div>
                    <i id="purchasing-chevron" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                </button>

                <!-- Submenu -->
                <ul id="purchasing-menu" class="mt-2 ml-6 space-y-1 hidden">
                    <li>
                        <a href="{{ route('supplier.index') }}" class="flex items-center space-x-3 {{ request()->routeIs('supplier.*') ? 'text-green-800 bg-green-100' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-industry w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('supplier.*') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Supplier</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('purchasing.pengiriman.index') }}" class="flex items-center space-x-3 {{ request()->routeIs('purchasing.pengiriman.*') ? 'text-green-800 bg-green-100' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-shipping-fast w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('purchasing.pengiriman.*') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Pengiriman</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('forecasting.index') }}" class="flex items-center space-x-3 {{ request()->routeIs('forecasting.*') ? 'text-green-800 bg-green-100' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-chart-bar w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('forecasting.*') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Forecasting</span>
                        </a>
                    </li>

                </ul>
            </li>

            <!-- Accounting Dropdown -->
            <li>
                <button onclick="toggleDropdown('keuangan')" class="flex items-center justify-between w-full {{ request()->routeIs('accounting.*') ? 'text-green-800 bg-green-200' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-coins w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('accounting.*') ? 'text-green-600' : '' }}"></i>
                        <span class="font-medium">Accounting</span>
                    </div>
                    <i id="keuangan-chevron" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                </button>

                <!-- Submenu -->
                <ul id="keuangan-menu" class="mt-2 ml-6 space-y-1 hidden">
                    <li>
                        <a href="{{ route('accounting.approval-pembayaran') }}" class="flex items-center space-x-3 {{ request()->routeIs('accounting.approval-pembayaran') ? 'text-green-800 bg-green-100' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-money-check-alt w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('accounting.approval-pembayaran') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Approval Pembayaran</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.approval-penagihan') }}" class="flex items-center space-x-3 {{ request()->routeIs('accounting.approval-penagihan') ? 'text-green-800 bg-green-100' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-file-invoice-dollar w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('accounting.approval-penagihan') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Approval Penagihan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.catatan-piutang') }}" class="flex items-center space-x-3 {{ request()->routeIs('accounting.catatan-piutang') ? 'text-green-800 bg-green-100' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-file-invoice w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('accounting.catatan-piutang') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Catatan Piutang</span>
                        </a>
                    </li>
                </ul>
            </li>


            <!-- Pengelolaan Akun - Only for Direktur -->
            @if(auth()->check() && auth()->user()->role === 'direktur')
            <li>
                <a href="{{ route('pengelolaan-akun.index') }}" class="flex items-center space-x-3 {{ request()->routeIs('pengelolaan-akun.*') ? 'text-green-800 bg-green-200' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <i class="fas fa-users-cog w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('pengelolaan-akun.*') ? 'text-green-600' : '' }}"></i>
                    <span class="font-medium">Pengelolaan Akun</span>
                </a>
            </li>
            @endif

            <!-- Verifikasi Proyek -->
            <li>
                <a href="#" class="flex items-center space-x-3 text-gray-800 hover:text-green-800 rounded-xl px-4 py-3 transition-all group">
                    <i class="fas fa-check-double w-5 text-lg group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="font-medium">Verifikasi Proyek</span>
                    <span class="bg-green-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">5</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Bottom Menu -->
    <div class="absolute bottom-0 w-64 px-4 pb-6 bg-white">
        <div class="border-t border-gray-200 pt-4">
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('pengaturan') }}" class="flex items-center space-x-3 {{ request()->routeIs('pengaturan') ? 'text-green-800 bg-green-200' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                        <i class="fas fa-cog w-5 text-lg group-hover:rotate-180 transition-transform duration-500 {{ request()->routeIs('pengaturan') ? 'text-green-600' : '' }}"></i>
                        <span class="font-medium">Pengaturan</span>
                    </a>
                </li>
                <li>
                    <button onclick="showSidebarLogoutModal()" class="flex items-center space-x-3 text-gray-800 hover:text-red-800 rounded-xl px-4 py-3 transition-all group w-full text-left">
                        <i class="fas fa-sign-out-alt w-5 text-lg group-hover:translate-x-1 transition-transform duration-300"></i>
                        <span class="font-medium">Keluar</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div id="mobileOverlay" class="fixed inset-0 bg-black/20 backdrop-blur-xs bg-opacity-50 z-50 lg:hidden hidden"></div>

<!-- Mobile Sidebar -->
<div id="mobileSidebar" class="fixed left-0 top-0 w-80 h-full bg-white shadow-2xl z-50 lg:hidden transform -translate-x-full transition-transform duration-300 ease-in-out">
    <!-- Mobile Header -->
    <div class="flex items-center justify-between p-4 border-b border-green-500">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center overflow-hidden">
                <img src="{{ asset('assets/image/logo/ptkmp-logo.png') }}" alt="Logo KMP" class="w-6 h-6 object-contain">
            </div>
            <div class="text-gray-800">
                <h1 class="text-lg font-bold tracking-wide">PT. KMP</h1>
                <p class="text-xs text-green-700">PT. Kamil Maju Persada</p>
            </div>
        </div>
        <button onclick="closeMobileMenu()" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-all duration-200">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Mobile Navigation Menu -->
    <nav class="mt-4 pb-32 overflow-y-auto h-full">
        <ul class="space-y-1 px-4">
            <!-- Dashboard -->
            <li>
                <a href="#" onclick="closeMobileMenu()" class="flex items-center space-x-3 {{ request()->routeIs('dashboard') ? 'text-green-800 bg-green-200' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <i class="fas fa-tachometer-alt w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('dashboard') ? 'text-green-600' : '' }}"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>

            <!-- Laporan -->
            <li>
                <a href="#" onclick="closeMobileMenu()" class="flex items-center space-x-3 text-gray-800 hover:text-green-800 rounded-xl px-4 py-3 transition-all group">
                    <i class="fas fa-file-alt w-5 text-lg group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="font-medium">Laporan</span>
                </a>
            </li>

            <!-- Marketing Dropdown -->
            <li>
                <button onclick="toggleMobileDropdown('marketing')" class="flex items-center justify-between w-full {{ request()->routeIs('klien.*') ? 'text-green-800 bg-green-50' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-bullhorn w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('klien.*') ? 'text-green-600' : '' }}"></i>
                        <span class="font-medium">Marketing</span>
                    </div>
                    <i id="mobile-marketing-chevron" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                </button>

                <!-- Submenu -->
                <ul id="mobile-marketing-menu" class="mt-2 ml-6 space-y-1 hidden">
                    <li>
                        <a href="#" onclick="closeMobileMenu()" class="flex items-center space-x-3 text-gray-700 hover:text-green-800 rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-handshake w-4 text-sm group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-medium">Order</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('klien.index') }}" onclick="closeMobileMenu()" class="flex items-center space-x-3 {{ request()->routeIs('klien.*') ? 'text-green-800 bg-green-50' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-users w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('klien.*') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Daftar Klien</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="closeMobileMenu()" class="flex items-center space-x-3 text-gray-700 hover:text-green-800 rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-clipboard-list w-4 text-sm group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-medium">Spesifikasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="closeMobileMenu()" class="flex items-center space-x-3 text-gray-700 hover:text-green-800 rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-file-invoice w-4 text-sm group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-medium">Penawaran</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Purchasing Dropdown -->
            <li>
                <button onclick="toggleMobileDropdown('purchasing')" class="flex items-center justify-between w-full {{ request()->routeIs('supplier.*') || request()->routeIs('forecasting.*') ? 'text-green-800 bg-green-50' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-shopping-cart w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('supplier.*') || request()->routeIs('forecasting.*') ? 'text-green-600' : '' }}"></i>
                        <span class="font-medium">Procurement</span>
                    </div>
                    <i id="mobile-purchasing-chevron" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                </button>

                <!-- Submenu -->
                <ul id="mobile-purchasing-menu" class="mt-2 ml-6 space-y-1 hidden">
                    <li>
                        <a href="{{ route('supplier.index') }}" onclick="closeMobileMenu()" class="flex items-center space-x-3 {{ request()->routeIs('supplier.*') ? 'text-green-800 bg-green-50' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-industry w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('supplier.*') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Supplier</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="closeMobileMenu()" class="flex items-center space-x-3 text-gray-700 hover:text-green-800 rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-shipping-fast w-4 text-sm group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-medium">Pengiriman</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('forecasting.index') }}" onclick="closeMobileMenu()" class="flex items-center space-x-3 {{ request()->routeIs('forecasting.*') ? 'text-green-800 bg-green-100' : 'text-gray-700 hover:text-green-800' }} rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-chart-bar w-4 text-sm group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('forecasting.*') ? 'text-green-600' : '' }}"></i>
                            <span class="font-medium">Forecasting</span>
                        </a>
                    </li>

                </ul>
            </li>

            <!-- Keuangan Dropdown -->
            <li>
                <button onclick="toggleMobileDropdown('keuangan')" class="flex items-center justify-between w-full text-gray-800 hover:text-green-800 rounded-xl px-4 py-3 transition-all group">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-coins w-5 text-lg group-hover:scale-110 transition-transform duration-300"></i>
                        <span class="font-medium">Accounting</span>
                    </div>
                    <i id="mobile-keuangan-chevron" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                </button>

                <!-- Submenu -->
                <ul id="mobile-keuangan-menu" class="mt-2 ml-6 space-y-1 hidden">
                    <li>
                        <a href="#" onclick="closeMobileMenu()" class="flex items-center space-x-3 text-gray-700 hover:text-green-800 rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-check-circle w-4 text-sm group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-medium">Approval Pembayaran</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="closeMobileMenu()" class="flex items-center space-x-3 text-gray-700 hover:text-green-800 rounded-lg px-4 py-2 text-sm transition-all group">
                            <i class="fas fa-receipt w-4 text-sm group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-medium">Approval Penagihan</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pengelolaan Akun - Only for Direktur (Mobile) -->
            @if(auth()->check() && auth()->user()->role === 'direktur')
            <li>
                <a href="{{ route('pengelolaan-akun.index') }}" onclick="closeMobileMenu()" class="flex items-center space-x-3 {{ request()->routeIs('pengelolaan-akun.*') ? 'text-green-800 bg-green-200' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                    <i class="fas fa-users-cog w-5 text-lg group-hover:scale-110 transition-transform duration-300 {{ request()->routeIs('pengelolaan-akun.*') ? 'text-green-600' : '' }}"></i>
                    <span class="font-medium">Pengelolaan Akun</span>
                </a>
            </li>
            @endif

            <!-- Verifikasi Proyek -->
            <li>
                <a href="#" onclick="closeMobileMenu()" class="flex items-center space-x-3 text-gray-800 hover:text-green-800 rounded-xl px-4 py-3 transition-all group">
                    <i class="fas fa-check-double w-5 text-lg group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="font-medium">Verifikasi Proyek</span>
                </a>
            </li>
        </ul>

        <!-- Mobile Bottom Menu -->
        <div class="px-4 mt-8 pt-4 border-t border-gray-200">
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('pengaturan') }}" onclick="closeMobileMenu()" class="flex items-center space-x-3 {{ request()->routeIs('pengaturan') ? 'text-green-800 bg-green-200' : 'text-gray-800 hover:text-green-800' }} rounded-xl px-4 py-3 transition-all group">
                        <i class="fas fa-cog w-5 text-lg group-hover:rotate-180 transition-transform duration-500 {{ request()->routeIs('pengaturan') ? 'text-green-600' : '' }}"></i>
                        <span class="font-medium">Pengaturan</span>
                    </a>
                </li>
                <li>
                    <button onclick="showSidebarLogoutModal()" class="flex items-center space-x-3 text-gray-800 hover:text-red-800 rounded-xl px-4 py-3 transition-all group w-full text-left">
                        <i class="fas fa-sign-out-alt w-5 text-lg group-hover:translate-x-1 transition-transform duration-300"></i>
                        <span class="font-medium">Keluar</span>
                    </button>
                </li>
            </ul>
        </div>
    </nav>
</div>


<script>
// Dropdown toggle functions for desktop
function toggleDropdown(menuName) {
    const menu = document.getElementById(menuName + '-menu');
    const chevron = document.getElementById(menuName + '-chevron');

    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        chevron.classList.add('rotate-180');
    } else {
        menu.classList.add('hidden');
        chevron.classList.remove('rotate-180');
    }
}

// Dropdown toggle functions for mobile
function toggleMobileDropdown(menuName) {
    const menu = document.getElementById('mobile-' + menuName + '-menu');
    const chevron = document.getElementById('mobile-' + menuName + '-chevron');

    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        chevron.classList.add('rotate-180');
    } else {
        menu.classList.add('hidden');
        chevron.classList.remove('rotate-180');
    }
}

// Mobile menu functions
function closeMobileMenu() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('mobileOverlay');

    if (sidebar && overlay) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

// Show logout modal from sidebar
function showSidebarLogoutModal() {
    document.getElementById('sidebarLogoutModal').classList.remove('hidden');
    document.getElementById('sidebarLogoutModal').style.display = 'flex';
}

// Hide sidebar logout modal
function hideSidebarLogoutModal() {
    const modal = document.getElementById('sidebarLogoutModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

// Handle logout from sidebar
function confirmSidebarLogout() {
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

// Auto-expand menu based on current route
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;

    // Auto-expand purchasing menu if on supplier pages
    if (currentPath.includes('/supplier')) {
        const purchasingMenu = document.getElementById('purchasing-menu');
        const purchasingChevron = document.getElementById('purchasing-chevron');
        const mobilePurchasingMenu = document.getElementById('mobile-purchasing-menu');
        const mobilePurchasingChevron = document.getElementById('mobile-purchasing-chevron');

        if (purchasingMenu) {
            purchasingMenu.classList.remove('hidden');
            purchasingChevron.classList.add('rotate-180');
        }

        if (mobilePurchasingMenu) {
            mobilePurchasingMenu.classList.remove('hidden');
            mobilePurchasingChevron.classList.add('rotate-180');
        }
    }

    // Auto-expand marketing menu if on klien pages
    if (currentPath.includes('/klien')) {
        const marketingMenu = document.getElementById('marketing-menu');
        const marketingChevron = document.getElementById('marketing-chevron');
        const mobileMarketingMenu = document.getElementById('mobile-marketing-menu');
        const mobileMarketingChevron = document.getElementById('mobile-marketing-chevron');

        if (marketingMenu) {
            marketingMenu.classList.remove('hidden');
            marketingChevron.classList.add('rotate-180');
        }

        if (mobileMarketingMenu) {
            mobileMarketingMenu.classList.remove('hidden');
            mobileMarketingChevron.classList.add('rotate-180');
        }
    }

    // Close modal when clicking outside
    document.getElementById('sidebarLogoutModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideSidebarLogoutModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideSidebarLogoutModal();
        }
    });
});
</script>

<!-- Sidebar Logout Modal -->
<div id="sidebarLogoutModal" class="fixed inset-0 bg-black/20 bg-opacity-50 backdrop-blur-xs z-50 hidden flex items-center justify-center">
    <div class="flex items-center justify-center min-h-screen p-4">
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
                <button onclick="hideSidebarLogoutModal()" class="flex-1 px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                    Batal
                </button>
                <button onclick="confirmSidebarLogout()" class="flex-1 px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200">
                    <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                </button>
            </div>
        </div>
    </div>
</div>
