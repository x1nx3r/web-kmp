@props(['kliens', 'selectedKlien' => null, 'klienSearch' => ''])

{{-- Hidden klien_id field --}}
<input type="hidden" name="klien_id" id="klien_id" value="{{ $selectedKlien ? $selectedKlien->id : '' }}">

{{-- Client Selection --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-building text-blue-600 text-sm"></i>
                </div>
                <h3 class="font-semibold text-gray-900">Pilih Klien</h3>
                <span id="selected-client-indicator" class="ml-3 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full hidden">
                    <i class="fas fa-check mr-1"></i>
                    <span id="selected-client-name">Dipilih</span>
                </span>
            </div>
            <div class="text-sm text-gray-500">
                <span class="font-medium">{{ count($kliens) }}</span> klien tersedia
            </div>
        </div>
    </div>

    {{-- Search Input --}}
    <div class="border-b border-gray-200 p-4 bg-gray-50">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400 text-sm"></i>
            </div>
            <input type="text" id="client-search"
                   class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white"
                   placeholder="Cari nama perusahaan atau lokasi...">
        </div>
    </div>

    {{-- Client Grid --}}
    <div class="p-4">
        <div id="client-grid" class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto">
            @forelse($kliens as $klien)
                <button type="button"
                        class="client-button w-full text-left p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        data-client-id="{{ $klien->id }}"
                        data-client-name="{{ $klien->nama }}"
                        data-client-cabang="{{ $klien->cabang ?? '' }}"
                        data-client-search="{{ strtolower($klien->nama . ' ' . ($klien->cabang ?? '')) }}">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-building text-gray-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-gray-900 truncate">{{ $klien->nama }}</h4>
                            @if($klien->cabang)
                                <p class="text-sm text-gray-500 mt-1 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $klien->cabang }}
                                </p>
                            @endif
                            @if($klien->phone)
                                <p class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-phone mr-1"></i>
                                    {{ $klien->phone }}
                                </p>
                            @endif
                        </div>
                        <div class="ml-3 flex-shrink-0">
                            <i class="fas fa-check-circle text-blue-500 hidden client-selected-icon"></i>
                            <i class="fas fa-circle text-gray-300 client-unselected-icon"></i>
                        </div>
                    </div>
                </button>
            @empty
                <div class="col-span-2 text-center py-8 text-gray-500">
                    <i class="fas fa-building text-2xl mb-2"></i>
                    <p>Tidak ada klien yang ditemukan</p>
                </div>
            @endforelse
        </div>

        {{-- No search results --}}
        <div id="no-search-results" class="text-center py-8 text-gray-500 hidden">
            <i class="fas fa-search text-2xl mb-2"></i>
            <p>Tidak ada klien yang cocok dengan pencarian</p>
        </div>
    </div>
</div>
