@props(['editMode' => false])

{{-- Header Section --}}
<div class="bg-white border-b border-gray-200 shadow-sm">
    <div class="px-6 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-blue-600 text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-3">
                        <h1 class="text-2xl font-bold text-gray-900">
                            @if($editMode)
                                Edit Penawaran
                            @else
                                Analisis Penawaran
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
                            Ubah data penawaran draft Anda
                        @else
                            Dashboard analisis margin & profitabilitas
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('penawaran.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors flex items-center">
                    <i class="fas fa-list mr-2"></i>
                    Lihat Semua Penawaran
                </a>
            </div>
        </div>
    </div>
</div>
