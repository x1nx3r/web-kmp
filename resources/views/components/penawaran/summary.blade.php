@props(['selectedMaterials', 'selectedKlien', 'selectedKlienCabang', 'totalRevenue', 'totalProfit', 'overallMargin', 'marginAnalysis', 'selectedSuppliers'])

{{-- Summary Review Section --}}
@if(count($selectedMaterials) > 0)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-4">
        <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-file-invoice text-blue-600"></i>
            </div>
            <div>
                <h4 class="font-semibold text-blue-900">Ringkasan Penawaran</h4>
                <p class="text-sm text-blue-700">Review sebelum menyimpan</p>
            </div>
        </div>
        
        {{-- Overview Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-blue-700 block mb-1">Klien:</span>
                <span class="font-semibold text-blue-900">{{ $selectedKlien }}</span>
                <span class="text-blue-600 text-xs block">{{ $selectedKlienCabang }}</span>
            </div>
            <div>
                <span class="text-blue-700 block mb-1">Total Material:</span>
                <span class="font-semibold text-blue-900">{{ count($selectedMaterials) }} item</span>
            </div>
            <div>
                <span class="text-blue-700 block mb-1">Total Harga Klien:</span>
                <span class="font-semibold text-blue-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-blue-700 block mb-1">Estimasi Profit:</span>
                <span class="font-semibold {{ $totalProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    Rp {{ number_format($totalProfit, 0, ',', '.') }}
                    <span class="text-xs">({{ number_format($overallMargin, 1) }}%)</span>
                </span>
            </div>
        </div>

        {{-- Note about supplier selection: penawaran records all offers, selection occurs at ordering --}}
        <div class="border-t border-blue-200 pt-3">
            <div class="flex items-start gap-2 mb-2">
                <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                <div>
                    <div class="font-semibold text-blue-900 text-sm">Supplier tercatat sebagai tawaran alternatif</div>
                    <div class="text-xs text-blue-700">Semua tawaran supplier untuk setiap material akan disimpan pada bagian alternatif. Pilihan akhir supplier dilakukan saat pembuatan Purchase Order.</div>
                </div>
            </div>

            {{-- Show cheapest supplier per material for quick reference --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3">
                @foreach($marginAnalysis as $index => $analysis)
                    @php
                        $best = collect($analysis['supplier_options'] ?? [])->firstWhere('is_best', true) ?? null;
                    @endphp
                    @if($best)
                        <div class="bg-white border border-blue-200 rounded-lg p-2 flex items-center gap-2">
                            <div class="w-6 h-6 bg-green-600 rounded flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-star text-white text-[10px]"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-semibold text-gray-900 truncate">{{ $analysis['nama'] }}</div>
                                <div class="text-[10px] text-blue-700 truncate">
                                    {{ $best['supplier_name'] }} • Rp {{ number_format($best['price'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-[10px] font-bold px-1.5 py-0.5 rounded flex-shrink-0 {{ $best['margin_percent'] >= 20 ? 'bg-green-100 text-green-800' : ($best['margin_percent'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ number_format($best['margin_percent'], 1) }}%
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif
