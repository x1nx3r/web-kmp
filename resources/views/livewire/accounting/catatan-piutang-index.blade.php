<div class="py-6">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-file-invoice-dollar text-blue-600 mr-2"></i>
            Catatan Piutang
        </h1>
        <p class="text-gray-600">Pilih jenis catatan piutang yang ingin Anda kelola</p>
    </div>

    {{-- Selection Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Catatan Piutang Supplier --}}
        <a href="{{ route('accounting.catatan-piutang.supplier') }}"
           class="group bg-white rounded-xl shadow-lg border-2 border-gray-200 hover:border-blue-500 hover:shadow-xl transition-all duration-300 p-8">
            <div class="flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-truck text-white text-3xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Piutang Supplier</h2>
                <p class="text-gray-600 mb-4">
                    Kelola catatan piutang dari supplier dengan sistem pembayaran dan cicilan
                </p>
                <div class="mt-4 space-y-2 text-left w-full">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span>Tracking pembayaran supplier</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span>Sistem cicilan dan pelunasan</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span>Riwayat transaksi lengkap</span>
                    </div>
                </div>
                <div class="mt-6 flex items-center text-blue-600 font-semibold group-hover:translate-x-2 transition-transform">
                    <span>Kelola Piutang Supplier</span>
                    <i class="fas fa-arrow-right ml-2"></i>
                </div>
            </div>
        </a>

        {{-- Catatan Piutang Pabrik --}}
        <a href="{{ route('accounting.catatan-piutang.pabrik') }}"
           class="group bg-white rounded-xl shadow-lg border-2 border-gray-200 hover:border-purple-500 hover:shadow-xl transition-all duration-300 p-8">
            <div class="flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-industry text-white text-3xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Piutang Pabrik (Klien)</h2>
                <p class="text-gray-600 mb-4">
                    Kelola piutang dari klien/pabrik dengan fokus pada jatuh tempo dan keterlambatan
                </p>
                <div class="mt-4 space-y-2 text-left w-full">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span>Monitoring jatuh tempo invoice</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span>Tracking hari keterlambatan</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span>Notifikasi otomatis jatuh tempo</span>
                    </div>
                </div>
                <div class="mt-6 flex items-center text-purple-600 font-semibold group-hover:translate-x-2 transition-transform">
                    <span>Kelola Piutang Pabrik</span>
                    <i class="fas fa-arrow-right ml-2"></i>
                </div>
            </div>
        </a>
    </div>

    {{-- Quick Stats --}}
    <div class="mt-8 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
            Fitur Utama Sistem Piutang
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-start">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="fas fa-file-invoice text-blue-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-1">Manajemen Lengkap</h4>
                    <p class="text-sm text-gray-600">Kelola piutang supplier dan klien dalam satu sistem terintegrasi</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="fas fa-bell text-purple-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-1">Reminder Otomatis</h4>
                    <p class="text-sm text-gray-600">Notifikasi jatuh tempo dan tracking keterlambatan pembayaran</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="fas fa-chart-pie text-green-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-1">Laporan Real-time</h4>
                    <p class="text-sm text-gray-600">Dashboard dan statistik piutang yang selalu ter-update</p>
                </div>
            </div>
        </div>
    </div>
</div>
