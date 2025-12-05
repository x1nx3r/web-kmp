@props(['kliens', 'selectedKlien', 'selectedKlienCabang', 'klienSearch', 'selectedKota', 'klienSort', 'availableCities'])

{{-- Client Selection --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-users text-green-600 text-sm"></i>
                </div>
                <h3 class="font-semibold text-gray-900">Pilih Klien</h3>
            </div>
            <div class="text-sm text-gray-500 flex items-center">
                @if($klienSearch || $selectedKota)
                    <i class="fas fa-filter text-green-500 mr-1"></i>
                    <span class="font-medium">{{ $kliens->flatten()->count() }}</span> dari total klien
                    @if($selectedKota)
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                            {{ $selectedKota }}
                        </span>
                    @endif
                @else
                    <span class="font-medium">{{ $kliens->flatten()->count() }}</span> klien
                @endif
            </div>
        </div>
    </div>

    {{-- Search & Filter Controls --}}
    <div class="border-b border-gray-200 p-4 bg-gray-50">
        <div class="space-y-3">
            {{-- Search Input --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-sm"></i>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="klienSearch"
                    class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm bg-white"
                    placeholder="Cari nama perusahaan, kota/lokasi, atau no HP..."
                >
                @if($klienSearch)
                <button
                    wire:click="clearKlienSearch"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                >
                    <i class="fas fa-times text-gray-400 hover:text-gray-600 text-sm"></i>
                </button>
                @endif
            </div>

            {{-- Filter and Sort Row --}}
            <div class="grid grid-cols-2 gap-3">
                {{-- City Filter --}}
                <div class="space-y-1">
                    <label class="text-xs font-medium text-gray-700 flex items-center">
                        <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                        Filter Kota:
                    </label>
                    <div class="relative">
                        <select
                            wire:model.live="selectedKota"
                            class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm bg-white pr-8"
                        >
                            <option value="">Semua Kota</option>
                            @foreach($availableCities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                        @if($selectedKota)
                        <button
                            wire:click="clearKotaFilter"
                            class="absolute inset-y-0 right-8 flex items-center"
                        >
                            <i class="fas fa-times text-gray-400 hover:text-gray-600 text-xs"></i>
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Sort Dropdown --}}
                <div class="space-y-1">
                    <label class="text-xs font-medium text-gray-700 flex items-center">
                        <i class="fas fa-sort text-gray-400 mr-1"></i>
                        Urutkan:
                    </label>
                    <select
                        wire:model.live="klienSort"
                        class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm bg-white"
                    >
                        <option value="nama_asc">Nama (A-Z)</option>
                        <option value="nama_desc">Nama (Z-A)</option>
                        <option value="cabang_asc">Kota (A-Z)</option>
                        <option value="cabang_desc">Kota (Z-A)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="p-4">
        {{-- Loading State --}}
        <div wire:loading.delay wire:target="klienSearch,klienSort,selectedKota" class="flex items-center justify-center py-8">
            <div class="flex items-center text-gray-500">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-green-500 mr-3"></div>
                <span class="text-sm">Mencari klien...</span>
            </div>
        </div>

        {{-- Client List --}}
        <div wire:loading.remove wire:target="klienSearch,klienSort,selectedKota" class="space-y-3 max-h-80 overflow-y-auto">
            @forelse($kliens as $namaKlien => $cabangList)
                @if($cabangList->count() == 1)
                    {{-- Single location client --}}
                    @php $klien = $cabangList->first(); @endphp
                    <div wire:key="client-{{ $klien->id }}-{{ $loop->index }}">
                        <button
                            type="button"
                            wire:click.prevent="selectKlien('{{ $klien->unique_key }}')"
                            class="w-full text-left p-3 rounded-lg border transition-all duration-200 cursor-pointer {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'border-blue-300 bg-blue-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}"
                        >
                        <div class="flex items-center">
                            <div class="w-8 h-8 {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'bg-blue-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-building {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'text-blue-600' : 'text-gray-600' }} text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $klien->nama }}</div>
                                <div class="text-sm text-gray-500 flex items-center mt-1">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                    {{ $klien->cabang }}
                                    <span class="mx-2 text-gray-300">•</span>
                                    <span class="text-gray-500">{{ $klien->bahanBakuKliens->count() }} material</span>
                                </div>
                            </div>
                            @if($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang)
                                <div class="ml-3">
                                    <i class="fas fa-check-circle text-blue-500"></i>
                                </div>
                            @endif
                        </div>
                        </button>
                    </div>
                @else
                    {{-- Multi-location client --}}
                    <div wire:key="client-group-{{ $loop->index }}" class="border border-gray-200 rounded-lg">
                        <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-gray-200 rounded flex items-center justify-center mr-3">
                                    <i class="fas fa-building text-gray-600 text-xs"></i>
                                </div>
                                <div class="font-medium text-gray-800">{{ $namaKlien }}</div>
                                <div class="ml-auto text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">
                                    {{ $cabangList->count() }} lokasi
                                </div>
                            </div>
                        </div>
                        <div class="p-2 space-y-1">
                            @foreach($cabangList as $klien)
                                <button
                                    type="button"
                                    wire:key="client-branch-{{ $klien->id }}"
                                    wire:click.prevent="selectKlien('{{ $klien->unique_key }}')"
                                    class="w-full text-left p-2 rounded transition-all duration-200 cursor-pointer {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50' }}"
                                >
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'bg-blue-100' : 'bg-gray-100' }} rounded flex items-center justify-center mr-3">
                                            <i class="fas fa-map-marker-alt {{ ($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang) ? 'text-blue-600' : 'text-gray-600' }} text-xs"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900 text-sm">{{ $klien->cabang }}</div>
                                            <div class="text-xs text-gray-500">{{ $klien->bahanBakuKliens->count() }} material</div>
                                        </div>
                                        @if($selectedKlien === $klien->nama && $selectedKlienCabang === $klien->cabang)
                                            <div class="ml-2">
                                                <i class="fas fa-check-circle text-blue-500 text-sm"></i>
                                            </div>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-gray-400 text-xl"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Tidak ada klien ditemukan</h4>
                    <p class="text-gray-500 text-sm">
                        @if($klienSearch || $selectedKota)
                            @if($klienSearch && $selectedKota)
                                Tidak ditemukan klien dengan kata kunci "<strong>{{ $klienSearch }}</strong>" di kota <strong>{{ $selectedKota }}</strong>.
                            @elseif($klienSearch)
                                Tidak ditemukan klien dengan kata kunci "<strong>{{ $klienSearch }}</strong>".
                            @else
                                Tidak ditemukan klien di kota <strong>{{ $selectedKota }}</strong>.
                            @endif
                            <br>
                            <button wire:click="clearKlienSearch" class="text-green-600 hover:text-green-700 underline mr-2">
                                Hapus pencarian
                            </button>
                            @if($selectedKota)
                            <button wire:click="clearKotaFilter" class="text-green-600 hover:text-green-700 underline">
                                Hapus filter kota
                            </button>
                            @endif
                        @else
                            Belum ada klien yang tersedia.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
