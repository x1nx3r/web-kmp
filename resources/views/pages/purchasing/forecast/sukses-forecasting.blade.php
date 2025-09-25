{{-- Tab Sukses Forecasting --}}
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-6">
            <i class="fas fa-check-circle text-green-600 mr-2"></i>
            Forecast Sukses
        </h3>

        @forelse($suksesForecasts ?? [] as $forecast)
            <div class="border border-gray-200 rounded-lg p-6 mb-4 hover:shadow-md transition-shadow duration-200">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900">{{ $forecast->no_forecast }}</h4>
                        <p class="text-gray-600 flex items-center mt-1">
                            <i class="fas fa-file-alt text-gray-400 mr-2"></i>
                            PO: {{ $forecast->purchaseOrder->no_po ?? 'N/A' }}
                        </p>
                        <p class="text-gray-600 flex items-center mt-1">
                            <i class="fas fa-user text-gray-400 mr-2"></i>
                            {{ $forecast->purchaseOrder->klien->nama ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                            {{ $forecast->status_label }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ $forecast->tanggal_forecast_formatted }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center">
                        <i class="fas fa-boxes text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600">
                            Total Qty: <span class="font-medium">{{ $forecast->total_qty_forecast_formatted }}</span>
                        </span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-money-bill-wave text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600">
                            Total: <span class="font-medium">{{ $forecast->total_harga_forecast_formatted }}</span>
                        </span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-truck text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600">
                            Kirim: <span class="font-medium">{{ $forecast->hari_kirim_forecast }} hari</span>
                        </span>
                    </div>
                </div>

                @if($forecast->catatan)
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-sticky-note text-gray-400 mr-2"></i>
                            <strong>Catatan:</strong> {{ $forecast->catatan }}
                        </p>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-check-circle text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Forecast Sukses</h3>
                <p>Belum ada forecast dengan status sukses.</p>
            </div>
        @endforelse
    </div>
</div>
