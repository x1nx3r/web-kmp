{{-- Branch Row Component --}}
@props(['klien', 'detailId'])

<tr class="hover:bg-gray-50">
    <td class="px-4 py-3 text-sm text-gray-900">
        <div class="flex items-center">
            <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
            {{ $klien->cabang ?? 'Lokasi tidak diketahui' }}
        </div>
    </td>
    <td class="px-4 py-3 text-sm text-gray-600">
        @if($klien->phone)
            <div class="flex items-center">
                <i class="fas fa-phone text-blue-500 mr-2"></i>
                    <a href="tel:{{ $klien->phone }}" class="text-blue-600 hover:text-blue-800">{{ $klien->phone }}</a>
            </div>
        @else
            <span class="text-gray-400 italic">Tidak ada kontak</span>
        @endif
    </td>
    <td class="px-4 py-3 text-sm text-gray-500">
        {{ $klien->updated_at ? $klien->updated_at->format('d/m/Y H:i') : '-' }}
    </td>
    <td class="px-4 py-3 text-right">
        <div class="flex items-center justify-end space-x-2">
            <button 
                type="button"
                x-on:click="toggleBahanBaku('{{ $detailId }}')"
                class="px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600 transition-colors duration-200"
            >
                <i 
                    class="fas mr-1 transform transition-transform duration-200"
                    :class="openBahanBaku.has('{{ $detailId }}') ? 'fa-chevron-up' : 'fa-chevron-down'"
                ></i>
                <span x-text="openBahanBaku.has('{{ $detailId }}') ? 'Tutup' : 'Bahan Baku'"></span>
            </button>
            <button 
                type="button"
                    @click="editBranch({{ $klien->id }}, '{{ $klien->nama }}', '{{ $klien->cabang }}', '{{ $klien->phone }}')"
                class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600 transition-colors duration-200"
                title="Edit Cabang"
            >
                <i class="fas fa-edit"></i>
            </button>
            <button 
                type="button"
                @click="deleteBranch({{ $klien->id }}, '{{ $klien->nama }} - {{ $klien->cabang }}')"
                class="px-3 py-1 bg-red-500 text-white rounded-md text-xs hover:bg-red-600 transition-colors duration-200"
                title="Hapus Cabang"
            >
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>