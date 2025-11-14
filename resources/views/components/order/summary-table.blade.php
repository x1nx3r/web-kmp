@props(['selectedOrderItems', 'totalAmount', 'totalMargin'])

{{-- Order Summary Table --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-table text-blue-600 text-sm"></i>
                </div>
                <h3 class="font-semibold text-gray-900">Ringkasan Order</h3>
            </div>
            <div class="text-sm text-gray-500">
                {{ count($selectedOrderItems) }} item
            </div>
        </div>
    </div>

    <div class="p-4">
        @if(count($selectedOrderItems) > 0)
            {{-- Summary Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suppliers</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Best Margin</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($selectedOrderItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $item['material_name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $item['satuan'] }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900">
                                        {{ $item['suppliers_count'] ?? 0 }} supplier tersedia
                                    </div>
                                    @if(isset($item['best_supplier_price']))
                                        <div class="text-xs text-gray-500">
                                            Best: Rp {{ number_format($item['best_supplier_price'], 0, ',', '.') }}/{{ $item['satuan'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-medium">{{ number_format($item['qty'], 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm">Rp {{ number_format($item['harga_jual'], 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-medium">Rp {{ number_format($item['total_harga'], 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="text-sm">
                                        <span class="font-medium text-green-600">Rp {{ number_format($item['total_margin'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ({{ number_format($item['margin_percentage'] ?? 0, 1) }}%)
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary Totals --}}
            <div class="mt-6 border-t border-gray-200 pt-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-sm text-gray-600">Total Order</div>
                            <div class="text-2xl font-bold text-gray-900">
                                Rp {{ number_format($totalAmount, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600">Total Margin</div>
                            <div class="text-2xl font-bold text-green-600">
                                Rp {{ number_format($totalMargin, 0, ',', '.') }}
                            </div>
                            @if($totalAmount > 0)
                                <div class="text-sm text-gray-500">
                                    ({{ number_format(($totalMargin / $totalAmount) * 100, 1) }}%)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-table text-gray-400 text-xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Belum ada item order</h4>
                <p class="text-gray-500 text-sm">
                    Tambahkan item untuk melihat ringkasan order
                </p>
            </div>
        @endif
    </div>
</div>