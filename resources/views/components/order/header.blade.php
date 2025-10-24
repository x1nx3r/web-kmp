@props(['editMode' => false])

{{-- Header Section --}}
<div class="bg-white border-b border-gray-200 shadow-sm">
    <div class="px-6 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus-circle text-indigo-600 text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-3">
                        <h1 class="text-2xl font-bold text-gray-900">
                            @if($editMode)
                                Edit Order
                            @else
                                Buat Order Baru
                            @endif
                        </h1>
                        @if($editMode)
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                <i class="fas fa-pencil-alt mr-1"></i>
                                MODE EDIT
                            </span>
                        @endif
                    </div>
                    <p class="text-gray-600 text-sm">
                        @if($editMode)
                            Ubah detail order. Pastikan informasi sudah sesuai sebelum menyimpan.
                        @else
                            Pilih klien, tentukan material dan supplier untuk membuat order pembelian baru.
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('orders.index') }}" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors flex items-center">
                    <i class="fas fa-list mr-2"></i>
                    Lihat Semua Order
                </a>
            </div>
        </div>
    </div>
</div>