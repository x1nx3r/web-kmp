{{-- Empty State Component --}}
@props(['hasSearch' => false, 'searchTerm' => null])

<div class="text-center py-16">
    <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-inbox text-4xl text-gray-400"></i>
    </div>
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $title ?? 'Tidak ada data klien' }}</h3>
    <p class="text-gray-500 mb-6">
        @if($hasSearch && $searchTerm)
            Tidak ditemukan klien dengan kata kunci "<strong>{{ $searchTerm }}</strong>"
        @else
            {{ $message ?? 'Belum ada klien yang terdaftar di sistem' }}
        @endif
    </p>
    @if($hasSearch && $searchTerm)
        <a 
            href="{{ $clearUrl ?? route('klien.index') }}" 
            class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200"
        >
            <i class="fas fa-times mr-2"></i>{{ $clearLabel ?? 'Hapus Filter' }}
        </a>
    @endif
</div>