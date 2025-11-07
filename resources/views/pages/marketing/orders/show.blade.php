@extends('layouts.app')

@section('content')
@php
    $outstandingSummary = [];
    $outstandingAmount = 0;
    $outstandingQtyTotal = 0;

    foreach ($order->orderDetails as $detail) {
        $remainingQty = $detail->remaining_quantity ?? max(0, ($detail->qty ?? 0) - ($detail->qty_shipped ?? 0));

        if ($remainingQty > 0) {
            $unitKey = $detail->satuan ?: 'unit';
            $outstandingSummary[$unitKey] = ($outstandingSummary[$unitKey] ?? 0) + $remainingQty;
            $outstandingQtyTotal += $remainingQty;
            $outstandingAmount += $remainingQty * ($detail->harga_jual ?? 0);
        }
    }

    $outstandingDisplay = collect($outstandingSummary)
        ->map(function ($qty, $unit) {
            return number_format($qty, 0, ',', '.') . ' ' . $unit;
        })
        ->implode(' | ');

    if ($outstandingQtyTotal === 0) {
        foreach ($order->orderDetails as $detail) {
            $totalQty = $detail->qty ?? 0;

            if ($totalQty > 0) {
                $unitKey = $detail->satuan ?: 'unit';
                $outstandingSummary[$unitKey] = ($outstandingSummary[$unitKey] ?? 0) + $totalQty;
                $outstandingQtyTotal += $totalQty;
            }
        }

        if ($outstandingQtyTotal > 0) {
            $outstandingAmount = $order->total_amount ?? $order->orderDetails->sum(fn($detail) => ($detail->total_harga ?? 0));
            $outstandingDisplay = collect($outstandingSummary)
                ->map(function ($qty, $unit) {
                    return number_format($qty, 0, ',', '.') . ' ' . $unit;
                })
                ->implode(' | ');
        }
    }

    $totalSelling = $order->orderDetails->sum(function ($detail) {
        $lineTotal = (float) ($detail->total_harga ?? 0);

        if ($lineTotal > 0) {
            return $lineTotal;
        }

        $qty = (float) ($detail->qty ?? 0);
        $hargaJual = (float) ($detail->harga_jual ?? 0);

        return $qty * $hargaJual;
    });

    $totalSupplierCost = $order->orderDetails->sum(function ($detail) {
        $qty = (float) ($detail->qty ?? 0);

        if ($qty <= 0) {
            return 0;
        }

        $unitCost = $detail->recommended_price ?? $detail->cheapest_price;

        if ($unitCost === null) {
            $suppliers = $detail->orderSuppliers;

            if ($suppliers->isNotEmpty()) {
                $unitCost = optional($suppliers->sortBy('unit_price')->first())->unit_price;
            }
        }

        return $qty * (float) ($unitCost ?? 0);
    });

    $totalMargin = $totalSelling - $totalSupplierCost;
    $marginPercentage = $totalSelling > 0 ? round(($totalMargin / $totalSelling) * 100, 2) : 0;
@endphp
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-eye text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Detail Order PO {{ $order->po_number ?? $order->no_order }}</h1>
                        <nav class="text-sm text-gray-600">
                            <a href="{{ route('orders.index') }}" class="hover:text-blue-600">Order</a>
                            <span class="mx-2">/</span>
                            <span>{{ $order->po_number ?? $order->no_order }}</span>
                        </nav>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @if($order->status === 'draft')
                        <a href="{{ route('orders.edit', $order->id) }}" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit
                        </a>
                        <form action="{{ route('orders.confirm', $order->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors" 
                                    onclick="return confirm('Konfirmasi order ini?')">
                                <i class="fas fa-check mr-2"></i>
                                Konfirmasi
                            </button>
                        </form>
                    @elseif($order->status === 'dikonfirmasi')
                        <form action="{{ route('orders.start-processing', $order->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors" 
                                    onclick="return confirm('Mulai proses order ini?')">
                                <i class="fas fa-play mr-2"></i>
                                Mulai Proses
                            </button>
                        </form>
                    @elseif(in_array($order->status, ['diproses', 'sebagian_dikirim']))
                        <form action="{{ route('orders.complete', $order->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors" 
                                    onclick="return confirm('Selesaikan order ini?')">
                                <i class="fas fa-check-circle mr-2"></i>
                                Selesaikan
                            </button>
                        </form>
                    @endif
                    
                    @if(!in_array($order->status, ['selesai', 'dibatalkan']))
                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors" 
                                    onclick="return confirm('Batalkan order ini?')">
                                <i class="fas fa-times mr-2"></i>
                                Batalkan
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('orders.index') }}" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

        <div class="p-6">
        <!-- Price Analysis Chart - Full Width -->
        <div class="mb-6">
            <x-order.price-analysis :order="$order" :chartsData="$chartsData" />
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Order Info -->
            <div class="flex-1 lg:flex-[2] space-y-6">
                <!-- Basic Info Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                            Informasi Order
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="flex-1 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Nomor PO:</span>
                                    <span class="text-sm text-gray-900">{{ $order->po_number ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">ID Sistem:</span>
                                    <span class="text-sm text-gray-900">{{ $order->no_order }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Klien:</span>
                                    <span class="text-sm text-gray-900">{{ $order->klien->nama }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Status:</span>
                                    @include('components.order.status-badge', ['status' => $order->status])
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Prioritas:</span>
                                    @include('components.order.priority-badge', ['priority' => $order->priority])
                                </div>
                            </div>
                            <div class="flex-1 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Tanggal Order:</span>
                                    <span class="text-sm text-gray-900">{{ $order->tanggal_order->format('d M Y') }}</span>
                                </div>
                                @if($order->po_start_date)
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-500">PO Mulai:</span>
                                        <span class="text-sm text-gray-900">{{ $order->po_start_date->format('d M Y') }}</span>
                                    </div>
                                @endif
                                @if($order->po_end_date)
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-500">PO Jatuh Tempo:</span>
                                        <span class="text-sm text-gray-900">{{ $order->po_end_date->format('d M Y') }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Dibuat Oleh:</span>
                                    <span class="text-sm text-gray-900">{{ $order->creator->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Dibuat:</span>
                                    <span class="text-sm text-gray-900">{{ $order->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Diupdate:</span>
                                    <span class="text-sm text-gray-900">{{ $order->updated_at->format('d M Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Outstanding Qty:</span>
                                    <span class="text-sm text-gray-900">{{ $outstandingDisplay ?: '0' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Outstanding Amount:</span>
                                    <span class="text-sm text-gray-900">Rp {{ number_format($outstandingAmount > 0 ? $outstandingAmount : ($order->total_amount ?? 0), 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($order->catatan)
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Catatan:</h4>
                                <p class="text-sm text-gray-600">{{ $order->catatan }}</p>
                            </div>
                        @endif
                                @if($order->po_document_url)
                                    <div class="mt-4">
                                        <a href="{{ $order->po_document_url }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                            <i class="fas fa-download mr-2"></i>
                                            Unduh Surat PO
                                        </a>
                                        @if($order->po_document_original_name)
                                            <p class="text-xs text-gray-500 mt-1">{{ $order->po_document_original_name }}</p>
                                        @endif
                                    </div>
                                @endif
                    </div>
                </div>

                <!-- Order Details Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list text-blue-600 mr-3"></i>
                            Detail Order ({{ $order->orderDetails->count() }} item)
                        </h3>
                    </div>
                    <div class="overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[800px]">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suppliers</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Best Price</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Best Margin</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($order->orderDetails as $detail)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $detail->bahanBakuKlien->nama }}</div>
                                                    @if($detail->spesifikasi_khusus)
                                                        <div class="text-sm text-gray-500">{{ $detail->spesifikasi_khusus }}</div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $detail->orderSuppliers->count() }} supplier{{ $detail->orderSuppliers->count() > 1 ? 's' : '' }}
                                                </div>
                                                @if($detail->orderSuppliers->count() > 0)
                                                    @php $bestSupplier = $detail->orderSuppliers->sortBy('price_rank')->first(); @endphp
                                                    <div class="text-sm text-gray-500">
                                                        Best: {{ $bestSupplier->supplier->nama ?? 'N/A' }}
                                                        @if($bestSupplier->supplier->picPurchasing)
                                                            <br><span class="text-xs">PIC: {{ $bestSupplier->supplier->picPurchasing->nama }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format($detail->qty, 2) }} {{ $detail->satuan }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                @if($detail->orderSuppliers->count() > 0)
                                                    @php $bestSupplier = $detail->orderSuppliers->sortBy('price_rank')->first(); @endphp
                                                    Rp {{ number_format($bestSupplier->harga_supplier ?? 0, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                Rp {{ number_format($detail->total_harga, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($detail->orderSuppliers->count() > 0)
                                                    @php $bestSupplier = $detail->orderSuppliers->sortBy('price_rank')->first(); @endphp
                                                    @include('components.order.profit-badge', ['percentage' => $bestSupplier->margin_percentage ?? 0])
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @include('components.order.detail-status-badge', ['status' => $detail->status])
                                            </td>
                                        </tr>
                                        
                                        {{-- Expandable supplier details --}}
                                        @if($detail->orderSuppliers->count() > 1)
                                            <tr class="bg-gray-50">
                                                <td colspan="8" class="px-6 py-3">
                                                    <details class="group">
                                                        <summary class="cursor-pointer text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                            <i class="fas fa-chevron-right group-open:rotate-90 transition-transform mr-2"></i>
                                                            Lihat semua {{ $detail->orderSuppliers->count() }} supplier
                                                        </summary>
                                                        <div class="mt-3 space-y-2">
                                                            @foreach($detail->orderSuppliers->sortBy('price_rank') as $orderSupplier)
                                                                <div class="flex items-center justify-between p-3 bg-white rounded border {{ $orderSupplier->is_recommended ? 'border-green-300' : 'border-gray-200' }}">
                                                                    <div class="flex items-center">
                                                                        @if($orderSupplier->is_recommended)
                                                                            <i class="fas fa-star text-green-600 mr-2"></i>
                                                                        @else
                                                                            <i class="fas fa-truck text-gray-400 mr-2"></i>
                                                                        @endif
                                                                        <div>
                                                                            <div class="text-sm font-medium text-gray-900">
                                                                                {{ $orderSupplier->supplier->nama ?? 'N/A' }}
                                                                                @if($orderSupplier->is_recommended)
                                                                                    <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Recommended</span>
                                                                                @endif
                                                                            </div>
                                                                            <div class="text-xs text-gray-500">
                                                                                Rank #{{ $orderSupplier->price_rank }} | 
                                                                                {{ $orderSupplier->supplier->alamat ?? 'No address' }}
                                                                                @if($orderSupplier->supplier->picPurchasing)
                                                                                    | PIC: {{ $orderSupplier->supplier->picPurchasing->nama }}
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <div class="text-sm font-semibold text-gray-900">
                                                                            Rp {{ number_format($orderSupplier->harga_supplier ?? 0, 0, ',', '.') }}
                                                                        </div>
                                                                        <div class="text-xs {{ ($orderSupplier->margin_percentage ?? 0) >= 20 ? 'text-green-600' : (($orderSupplier->margin_percentage ?? 0) >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                                                            Margin: {{ number_format($orderSupplier->margin_percentage ?? 0, 1) }}%
                                                                        </div>
                                                                        @if($orderSupplier->shipped_quantity > 0)
                                                                            <div class="text-xs text-blue-600">
                                                                                Shipped: {{ number_format($orderSupplier->shipped_quantity, 0) }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </details>
                                                </td>
                                            </tr>
                                        @endif
                                        @if($detail->catatan)
                                            <tr>
                                                <td colspan="8" class="px-6 py-2 text-sm text-gray-500">
                                                    <i class="fas fa-sticky-note mr-2"></i>
                                                    {{ $detail->catatan }}
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                                Tidak ada detail order
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                        </table>
                    </div>
                </div>
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div class="w-full xl:w-1/3 min-w-0 space-y-6">
                <!-- Financial Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-calculator text-blue-600 mr-3"></i>
                            Ringkasan Keuangan
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Harga Supplier:</span>
                            <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($totalSupplierCost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Harga Jual:</span>
                            <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($totalSelling, 0, ',', '.') }}</span>
                        </div>
                        <hr class="border-gray-200">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Outstanding Qty:</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $outstandingDisplay ?: '0' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Outstanding Amount:</span>
                            <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($outstandingAmount > 0 ? $outstandingAmount : $totalSelling, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Total Margin:</span>
                            <span class="text-sm font-bold {{ $totalMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($totalMargin, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Persentase Margin:</span>
                            <span class="text-sm font-bold {{ $marginPercentage >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($marginPercentage, 2, ',', '.') }}%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Progress Summary -->
                @if($order->orderDetails->where('status', '!=', 'menunggu')->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-shipping-fast text-blue-600 mr-3"></i>
                                Progress Pengiriman
                            </h3>
                        </div>
                        <div class="p-6">
                            @php
                                $statusCounts = $order->orderDetails->groupBy('status')->map->count();
                                $totalItems = $order->orderDetails->count();
                            @endphp
                            
                            <div class="space-y-3">
                                @foreach(['menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'sebagian_dikirim' => 'Sebagian Dikirim', 'selesai' => 'Selesai'] as $status => $label)
                                    @if(isset($statusCounts[$status]))
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">{{ $label }}:</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-semibold text-gray-900">{{ $statusCounts[$status] }}</span>
                                                @include('components.order.detail-status-badge', ['status' => $status])
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            
                            <hr class="my-4 border-gray-200">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $completePercentage = ($statusCounts['selesai'] ?? 0) / $totalItems * 100;
                                    $partialPercentage = ($statusCounts['sebagian_dikirim'] ?? 0) / $totalItems * 100;
                                @endphp
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $completePercentage }}%"></div>
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $partialPercentage }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                {{ number_format($completePercentage + $partialPercentage, 1) }}% progress
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-history text-blue-600 mr-3"></i>
                            Timeline
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="timeline space-y-4">
                            <div class="timeline-item flex items-start space-x-3">
                                <div class="timeline-marker w-3 h-3 bg-blue-600 rounded-full mt-1.5"></div>
                                <div class="timeline-content">
                                    <h4 class="text-sm font-medium text-gray-900">Order Dibuat</h4>
                                    <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            
                            @if($order->dikonfirmasi_at)
                                <div class="timeline-item flex items-start space-x-3">
                                    <div class="timeline-marker w-3 h-3 bg-cyan-600 rounded-full mt-1.5"></div>
                                    <div class="timeline-content">
                                        <h4 class="text-sm font-medium text-gray-900">Dikonfirmasi</h4>
                                        <p class="text-xs text-gray-500">{{ $order->dikonfirmasi_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($order->selesai_at)
                                <div class="timeline-item flex items-start space-x-3">
                                    <div class="timeline-marker w-3 h-3 bg-green-600 rounded-full mt-1.5"></div>
                                    <div class="timeline-content">
                                        <h4 class="text-sm font-medium text-gray-900">Selesai</h4>
                                        <p class="text-xs text-gray-500">{{ $order->selesai_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            @elseif($order->dibatalkan_at)
                                <div class="timeline-item flex items-start space-x-3">
                                    <div class="timeline-marker w-3 h-3 bg-red-600 rounded-full mt-1.5"></div>
                                    <div class="timeline-content">
                                        <h4 class="text-sm font-medium text-gray-900">Dibatalkan</h4>
                                        <p class="text-xs text-gray-500">{{ $order->dibatalkan_at->format('d M Y, H:i') }}</p>
                                        @if($order->alasan_pembatalan)
                                            <p class="text-xs text-red-600 mt-1">{{ $order->alasan_pembatalan }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection