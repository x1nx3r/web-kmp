@props(['marginAnalysis', 'selectedSuppliers', 'totalRevenue', 'totalCost', 'totalProfit', 'overallMargin'])

{{-- Detailed Analysis Table --}}
<div class="bg-white rounded-xl shadow-lg border border-gray-200">
    <div class="border-b border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-table text-gray-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Analisis Margin Detail</h3>
                    <p class="text-sm text-gray-500 mt-1">Detailed margin analysis per material</p>
                </div>
            </div>
            {{-- Summary Stats --}}
            <div class="flex space-x-4 text-sm">
                <div class="text-center">
                    <div class="font-semibold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500">Revenue</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold text-red-600">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500">Cost</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500">Profit</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold {{ $overallMargin >= 0 ? 'text-blue-600' : 'text-red-600' }}">{{ number_format($overallMargin, 1) }}%</div>
                    <div class="text-xs text-gray-500">Margin</div>
                </div>
            </div>
        </div>
    </div>
    <div class="max-h-[600px] overflow-y-auto">
        <table class="w-full table-fixed">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    {{-- Selection column removed: penawaran is a guide (all supplier offers persisted) --}}
                    <th class="w-[28%] px-3 py-2 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Material & Supplier</th>
                    <th class="w-[8%] px-2 py-2 text-right text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Qty</th>
                    <th class="w-[11%] px-2 py-2 text-right text-[10px] font-semibold text-gray-600 uppercase tracking-wider">H. Klien</th>
                    <th class="w-[11%] px-2 py-2 text-right text-[10px] font-semibold text-gray-600 uppercase tracking-wider">H. Supplier</th>
                    <th class="w-[11%] px-2 py-2 text-right text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Revenue</th>
                    <th class="w-[10%] px-2 py-2 text-right text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Cost</th>
                    <th class="w-[10%] px-2 py-2 text-right text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Profit</th>
                    <th class="w-[7%] px-2 py-2 text-center text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Margin</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @forelse($marginAnalysis as $materialIndex => $analysis)
                    {{-- Material Header Row --}}
                    <tr class="bg-gradient-to-r from-blue-50 to-blue-25 border-t-2 border-blue-200">
                        <td colspan="9" class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-blue-500 rounded flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-cube text-white text-xs"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-sm text-blue-900">{{ $analysis['nama'] }}</span>
                                    <span class="text-xs text-blue-700 ml-2">{{ $analysis['satuan'] }}</span>
                                    <span class="text-xs text-blue-600 ml-2">• Qty: {{ number_format($analysis['quantity']) }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    @forelse($analysis['supplier_options'] ?? [] as $supplierIndex => $supplier)
                        @php
                            // No persisted selection in the table view anymore. Highlight the cheapest option instead.
                            $isBest = $supplier['is_best'];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-all border-b border-gray-100 {{ $isBest ? 'bg-green-50 border-l-4 border-l-green-400' : 'bg-white' }}">
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 {{ $isBest ? 'bg-green-600' : 'bg-gray-400' }} rounded flex items-center justify-center flex-shrink-0">
                                        <i class="fas {{ $isBest ? 'fa-star' : 'fa-building' }} text-white text-[10px]"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-semibold {{ $isBest ? 'text-green-800' : 'text-gray-900' }} truncate">
                                            {{ $supplier['supplier_name'] }}
                                        </div>
                                        @if($supplier['pic_name'])
                                            <div class="text-[10px] text-gray-500 truncate">PIC: {{ $supplier['pic_name'] }}</div>
                                        @endif
                                    </div>
                                    <div class="flex gap-1">
                                        @if($isBest)
                                            <span class="text-[10px] bg-green-600 text-white px-1.5 py-0.5 rounded font-semibold flex-shrink-0">TERMURAH</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-2 py-2 text-xs text-right font-medium text-gray-700">
                                {{ number_format($analysis['quantity']) }}
                            </td>
                            
                            <td class="px-2 py-2 text-xs text-right font-medium text-gray-900">
                                {{ number_format($analysis['klien_price'], 0, ',', '.') }}
                                @if($analysis['is_custom_price'] ?? false)
                                    <span class="block text-[10px] text-purple-600">Custom</span>
                                @endif
                            </td>
                            
                            <td class="px-2 py-2 text-xs text-right font-medium text-gray-900">
                                {{ number_format($supplier['price'], 0, ',', '.') }}
                            </td>
                            
                            <td class="px-2 py-2 text-right">
                                <span class="text-xs font-medium text-green-700">
                                    {{ number_format($analysis['revenue'], 0, ',', '.') }}
                                </span>
                            </td>
                            
                            <td class="px-2 py-2 text-right">
                                <span class="text-xs font-medium text-red-700">
                                    {{ number_format($supplier['cost'], 0, ',', '.') }}
                                </span>
                            </td>
                            
                            <td class="px-2 py-2 text-right">
                                <span class="text-xs font-medium {{ $supplier['profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ number_format($supplier['profit'], 0, ',', '.') }}
                                </span>
                            </td>
                            
                            <td class="px-2 py-2 text-center">
                                <span class="inline-block text-xs font-bold px-2 py-1 rounded {{ $supplier['margin_percent'] >= 20 ? 'bg-green-100 text-green-800' : ($supplier['margin_percent'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($supplier['margin_percent'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-3 text-center text-gray-500">
                                <div class="flex items-center justify-center text-xs">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    No suppliers found for {{ $analysis['nama'] }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mb-4">
                                    <i class="fas fa-table text-gray-400 text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-medium text-gray-700 mb-2">Belum ada data analisis</h4>
                                <p class="text-sm text-gray-500">Tambahkan material untuk melihat analisis detail margin</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
