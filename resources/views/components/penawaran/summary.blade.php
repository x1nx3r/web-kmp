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

        
    </div>
@endif
