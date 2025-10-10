{{-- Klien Table Row Component --}}
@props(['name', 'group', 'groupId', 'rowNumber'])

@php 
    $branches = $group->pluck('cabang')->filter()->unique();
    $mainLocation = $branches->first() ?? 'Tidak diketahui';
    $latestUpdate = $group->max('updated_at');
@endphp

{{-- Main client row --}}
<tr 
    class="hover:bg-gray-50 border-b border-gray-200 cursor-pointer"
    x-on:click="toggleGroup('{{ $groupId }}')"
>
    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $rowNumber }}</td>
    <td class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-gray-900">{{ $name }}</div>
            </div>
            <button 
                type="button" 
                class="flex items-center px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors duration-200"
            >
                <i 
                    class="fas mr-1 transform transition-transform duration-200"
                    :class="openGroups.has('{{ $groupId }}') ? 'fa-chevron-down rotate-0' : 'fa-chevron-right'"
                ></i>
                <span x-text="openGroups.has('{{ $groupId }}') ? 'Tutup' : 'Lihat Cabang'"></span>
            </button>
        </div>
    </td>
    <td class="px-6 py-4 text-sm text-gray-900">
        <div class="flex items-center">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fas fa-map-marker-alt mr-1"></i>
                {{ $group->count() }} cabang
            </span>
        </div>
    </td>
    <td class="px-6 py-4 text-sm text-gray-500">
        <div class="flex items-center">
            <i class="fas fa-location-dot text-gray-400 mr-2"></i>
            {{ $mainLocation }}
            @if($branches->count() > 1)
                <span class="text-xs text-gray-400 ml-1">(+{{ $branches->count() - 1 }} lokasi)</span>
            @endif
        </div>
    </td>
    <td class="px-6 py-4 text-sm text-gray-500">
        {{ $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('d/m/Y H:i') : '-' }}
    </td>
    <td class="px-6 py-4 text-right">
        <div class="flex items-center justify-end space-x-2">
            <button 
                type="button"
                @click.stop="editCompany('{{ $name }}')"
                class="text-amber-600 hover:text-amber-800 text-sm font-medium"
                title="Edit Perusahaan"
            >
                <i class="fas fa-edit"></i>
            </button>
            <button 
                type="button"
                @click.stop="deleteCompany('{{ $name }}')"
                class="text-red-600 hover:text-red-800 text-sm font-medium"
                title="Hapus Perusahaan"
            >
                <i class="fas fa-trash-alt"></i>
            </button>
            <button 
                type="button"
                x-on:click.stop="toggleGroup('{{ $groupId }}')"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
            >
                Detail
            </button>
        </div>
    </td>
</tr>

{{-- Expandable branches section --}}
<tr x-show="openGroups.has('{{ $groupId }}')" x-transition class="bg-gray-50">
    <td colspan="6" class="p-0">
        <div class="border-t border-gray-200">
            {{-- Branch header --}}
            <div class="px-6 py-3 bg-gray-100 border-b border-gray-200">
                <h4 class="text-sm font-medium text-gray-900">Cabang untuk: {{ $name }}</h4>
            </div>
            
            {{-- Branch table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Cabang</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Update</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($group as $klien)
                            @php $detailId = 'detail-' . $klien->id; @endphp
                            
                            <x-klien.branch-row :klien="$klien" :detailId="$detailId" />
                            
                            {{-- Bahan Baku detail row --}}
                            <tr x-show="openBahanBaku.has('{{ $detailId }}')" x-transition>
                                <td colspan="4" class="p-0">
                                    @php
                                        $bahanBakuItems = collect();
                                        foreach ($klien->purchaseOrders as $po) {
                                            foreach ($po->purchaseOrderBahanBakus as $item) {
                                                if ($item->bahanBakuKlien) {
                                                    $bahanBakuItems->push($item->bahanBakuKlien);
                                                }
                                            }
                                        }
                                    @endphp

                                    <x-klien.bahan-baku-section :klien="$klien" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </td>
</tr>