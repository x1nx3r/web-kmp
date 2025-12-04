@extends('layouts.app')

@section('title', 'Detail Order - Kamil Maju Persada')

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
        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-eye text-blue-600 text-lg"></i>
                    </div>
                    <div className="min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">Detail Order PO {{ $order->po_number ?? $order->no_order }}</h1>
                        <nav class="text-sm text-gray-600">
                            <a href="{{ route('orders.index') }}" class="hover:text-blue-600">Order</a>
                            <span class="mx-2">/</span>
                            <span class="truncate">{{ $order->po_number ?? $order->no_order }}</span>
                        </nav>
                    </div>
                </div>
                @php
                    $currentUser = auth()->user();
                    $isOrderCreator = $currentUser && $order->created_by === $currentUser->id;
                    $isMarketing = $currentUser && $currentUser->isMarketing();
                    $isDirektur = $currentUser && $currentUser->isDirektur();
                    $canManageOrder = $isOrderCreator || $isMarketing || $isDirektur;
                @endphp
                <div class="flex flex-wrap gap-2 sm:space-x-3">
                    @if($order->status === 'draft' && $canManageOrder)
                        <a href="{{ route('orders.edit', $order->id) }}" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors text-center">
                            <i class="fas fa-edit mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Edit</span>
                        </a>
                        <form action="{{ route('orders.confirm', $order->id) }}" method="POST" class="flex-1 sm:flex-none inline">
                            @csrf
                            <button type="submit" class="w-full px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    onclick="return confirm('Konfirmasi order ini?')">
                                <i class="fas fa-check mr-1 sm:mr-2"></i>
                                <span class="hidden sm:inline">Konfirmasi</span>
                            </button>
                        </form>
                    @elseif($order->status === 'dikonfirmasi' && $canManageOrder)
                        <form action="{{ route('orders.start-processing', $order->id) }}" method="POST" class="flex-1 sm:flex-none inline">
                            @csrf
                            <button type="submit" class="w-full px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    onclick="return confirm('Mulai proses order ini?')">
                                <i class="fas fa-play mr-1 sm:mr-2"></i>
                                <span class="hidden sm:inline">Mulai Proses</span>
                            </button>
                        </form>
                    @elseif($order->status === 'diproses' && $canManageOrder)
                        <form action="{{ route('orders.complete', $order->id) }}" method="POST" class="flex-1 sm:flex-none inline">
                            @csrf
                            <button type="submit" class="w-full px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    onclick="return confirm('Selesaikan order ini?')">
                                <i class="fas fa-check-circle mr-1 sm:mr-2"></i>
                                <span class="hidden sm:inline">Selesaikan</span>
                            </button>
                        </form>
                        {{-- Konsultasi Direktur button - shown when order is nearing fulfillment --}}
                        {{-- Only the order creator or marketing users can request consultation --}}
                        @php
                            $fulfillmentPct = $order->getFulfillmentPercentage();
                            $canConsultDirektur = $isOrderCreator || $isMarketing;
                        @endphp
                        @if($fulfillmentPct >= 95 && $fulfillmentPct <= 105 && $canConsultDirektur)
                            <button type="button"
                                    onclick="openConsultModal()"
                                    class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-question-circle mr-1 sm:mr-2"></i>
                                <span class="hidden sm:inline">Konsultasi Direktur</span>
                            </button>
                        @endif
                    @endif

                    @if(!in_array($order->status, ['selesai', 'dibatalkan']) && $canManageOrder)
                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="flex-1 sm:flex-none inline">
                            @csrf
                            <button type="submit" class="w-full px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    onclick="return confirm('Batalkan order ini?')">
                                <i class="fas fa-times mr-1 sm:mr-2"></i>
                                <span class="hidden sm:inline">Batalkan</span>
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('orders.index') }}" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-sm text-center">
                        <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Kembali</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

        <div class="p-4 sm:p-6">
        <!-- Price Analysis Chart - Full Width -->
        <div class="mb-4 sm:mb-6">
            <x-order.price-analysis :order="$order" :chartsData="$chartsData" />
        </div>

        <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
            <!-- Order Info -->
            <div class="flex-1 lg:flex-[2] space-y-4 sm:space-y-6">
                <!-- Basic Info Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                            Informasi Order
                        </h3>
                    </div>
                    <div class="p-4 sm:p-6">
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
                                @if($order->winner)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-500">PO Winner:</span>
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-trophy text-yellow-500 text-sm"></i>
                                            <span class="text-sm font-semibold text-yellow-700">{{ $order->winner->user->nama ?? $order->winner->user->name }}</span>
                                        </div>
                                    </div>
                                @endif
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
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list text-blue-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
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

                <!-- Review Pengiriman Card -->
                @php
                    $successfulShipments = $order->pengiriman()->where('status', 'berhasil')->with(['details.bahanBakuSupplier.supplier.picPurchasing', 'purchasing'])->get();
                @endphp

                @if($successfulShipments->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-star text-yellow-500 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                            Review Pengiriman
                            <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                {{ $successfulShipments->count() }} pengiriman berhasil
                            </span>
                        </h3>
                    </div>
                    <div class="overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Pengiriman</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC Purchasing</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($successfulShipments as $shipment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $shipment->no_pengiriman }}</div>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            Berhasil
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $shipment->tanggal_kirim->format('d M Y') }}</div>
                                                @if($shipment->purchasing)
                                                    <div class="text-xs text-gray-500">{{ $shipment->purchasing->name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $picList = $shipment->details
                                                        ->pluck('bahanBakuSupplier.supplier.picPurchasing')
                                                        ->filter()
                                                        ->unique('id');
                                                @endphp
                                                @if($picList->isNotEmpty())
                                                    <div class="text-sm space-y-1">
                                                        @foreach($picList as $pic)
                                                            <div class="text-gray-900">
                                                                <i class="fas fa-user-circle text-blue-500 text-xs mr-1"></i>
                                                                {{ $pic->nama }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-sm text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format($shipment->total_qty_kirim, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                Rp {{ number_format($shipment->total_harga_kirim, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($shipment->rating)
                                                    <div class="flex items-center justify-center text-yellow-500">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fas fa-star {{ $i <= $shipment->rating ? '' : 'opacity-30' }} text-xs"></i>
                                                        @endfor
                                                    </div>
                                                    <div class="text-xs font-semibold text-gray-700 mt-1">{{ $shipment->rating }}/5</div>
                                                @else
                                                    <span class="text-xs text-gray-400">Belum direview</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if(!$shipment->rating)
                                                    <a href="{{ route('pengiriman.evaluasi', $shipment->id) }}"
                                                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                        <i class="fas fa-star mr-1"></i>
                                                        Lakukan Review
                                                    </a>
                                                @else
                                                    <a href="{{ route('pengiriman.review', $shipment->id) }}"
                                                       class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition-colors">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        Lihat Review
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($shipment->ulasan)
                                            <tr class="bg-blue-50">
                                                <td colspan="7" class="px-6 py-3">
                                                    <div class="text-sm text-gray-700 italic">
                                                        <i class="fas fa-quote-left text-blue-400 mr-2"></i>
                                                        {{ $shipment->ulasan }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($successfulShipments->where('rating', null)->count() > 0)
                        <div class="px-4 sm:px-6 py-4 bg-yellow-50 border-t border-yellow-200">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-yellow-600 mt-0.5 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">
                                        Ada {{ $successfulShipments->where('rating', null)->count() }} pengiriman yang belum direview
                                    </p>
                                    <p class="text-xs text-yellow-700 mt-1">
                                        Review membantu meningkatkan kualitas layanan pengiriman
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Summary Sidebar -->
            <div class="w-full lg:w-80 xl:w-96 flex-shrink-0 space-y-4 sm:space-y-6">
                <!-- Ringkasan Keuangan & Timeline - Side by Side -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-4">
                    <!-- Financial Summary -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-3 py-2 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-calculator text-blue-600 mr-2 text-xs"></i>
                                Ringkasan Keuangan
                            </h3>
                        </div>
                        <div class="p-3 space-y-2">
                            <!-- Total Harga Supplier -->
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-0.5">Total Harga Supplier:</div>
                                <div class="text-xs font-bold text-gray-900">Rp {{ number_format($totalSupplierCost, 0, ',', '.') }}</div>
                            </div>

                            <!-- Total Harga Jual -->
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-0.5">Total Harga Jual:</div>
                                <div class="text-xs font-bold text-gray-900">Rp {{ number_format($totalSelling, 0, ',', '.') }}</div>
                            </div>

                            <hr class="border-gray-200">

                            <!-- Outstanding Qty -->
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-0.5">Outstanding Qty:</div>
                                <div class="text-xs font-semibold text-gray-900">{{ $outstandingDisplay ?: '0' }}</div>
                            </div>

                            <!-- Outstanding Amount -->
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-0.5">Outstanding Amount:</div>
                                <div class="text-xs font-semibold text-gray-900">Rp {{ number_format($outstandingAmount > 0 ? $outstandingAmount : $totalSelling, 0, ',', '.') }}</div>
                            </div>

                            <hr class="border-gray-200">

                            <!-- Total Margin -->
                            <div class="pt-1">
                                <div class="text-xs font-semibold text-gray-900 mb-0.5">Total Margin:</div>
                                <div class="text-base font-bold {{ $totalMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($totalMargin, 0, ',', '.') }}
                                </div>
                                <div class="text-xs {{ $marginPercentage >= 20 ? 'text-green-600' : ($marginPercentage >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ number_format($marginPercentage, 2, ',', '.') }}%
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-3 py-2 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-history text-blue-600 mr-2 text-xs"></i>
                                Timeline
                            </h3>
                        </div>
                        <div class="p-3">
                            <div class="timeline space-y-3">
                                <div class="timeline-item flex items-start space-x-2">
                                    <div class="timeline-marker w-2.5 h-2.5 bg-blue-600 rounded-full mt-1"></div>
                                    <div class="timeline-content flex-1">
                                        <h4 class="text-xs font-medium text-gray-900">Order Dibuat</h4>
                                        <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>

                                @if($order->dikonfirmasi_at)
                                    <div class="timeline-item flex items-start space-x-2">
                                        <div class="timeline-marker w-2.5 h-2.5 bg-cyan-600 rounded-full mt-1"></div>
                                        <div class="timeline-content flex-1">
                                            <h4 class="text-xs font-medium text-gray-900">Dikonfirmasi</h4>
                                            <p class="text-xs text-gray-500">{{ $order->dikonfirmasi_at->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($order->selesai_at)
                                    <div class="timeline-item flex items-start space-x-2">
                                        <div class="timeline-marker w-2.5 h-2.5 bg-green-600 rounded-full mt-1"></div>
                                        <div class="timeline-content flex-1">
                                            <h4 class="text-xs font-medium text-gray-900">Selesai</h4>
                                            <p class="text-xs text-gray-500">{{ $order->selesai_at->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                @elseif($order->dibatalkan_at)
                                    <div class="timeline-item flex items-start space-x-2">
                                        <div class="timeline-marker w-2.5 h-2.5 bg-red-600 rounded-full mt-1"></div>
                                        <div class="timeline-content flex-1">
                                            <h4 class="text-xs font-medium text-gray-900">Dibatalkan</h4>
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

                <!-- Progress Summary -->
                @if($order->orderDetails->where('status', '!=', 'menunggu')->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-base font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-shipping-fast text-blue-600 mr-2 text-sm"></i>
                                Progress Pengiriman
                            </h3>
                        </div>
                        <div class="p-4">
                            @php
                                $statusCounts = $order->orderDetails->groupBy('status')->map->count();
                                $totalItems = $order->orderDetails->count();
                            @endphp

                            @php
                                $fulfillmentPercent = $order->getFulfillmentPercentage();
                                $shippedQty = $order->getShippedQty();
                            @endphp

                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Order:</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($order->total_qty, 0, ',', '.') }} {{ $order->orderDetails->first()->satuan ?? 'unit' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Sudah Dikirim:</span>
                                    <span class="text-sm font-semibold text-green-600">{{ number_format($shippedQty, 0, ',', '.') }} {{ $order->orderDetails->first()->satuan ?? 'unit' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Sisa:</span>
                                    <span class="text-sm font-semibold text-orange-600">{{ number_format(max(0, $order->total_qty - $shippedQty), 0, ',', '.') }} {{ $order->orderDetails->first()->satuan ?? 'unit' }}</span>
                                </div>
                            </div>

                            <hr class="my-4 border-gray-200">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: {{ min(100, $fulfillmentPercent) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                {{ number_format($fulfillmentPercent, 1) }}% terpenuhi
                                @if($fulfillmentPercent >= 95 && $fulfillmentPercent <= 105 && $order->status === 'diproses')
                                    <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Mendekati target
                                    </span>
                                @elseif($fulfillmentPercent > 105)
                                    <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Melebihi target
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Konsultasi Direktur Modal --}}
@if($order->status === 'diproses')
<div id="consultModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeConsultModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-xl">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-question-circle text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Konsultasi Direktur</h3>
                        <p class="text-sm text-gray-600">Minta saran mengenai order ini</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('orders.consult-direktur', $order->id) }}" method="POST">
                @csrf
                <div class="p-6">
                    {{-- Order Info Summary --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500">No. PO:</span>
                                <span class="font-medium text-gray-900 ml-1">{{ $order->po_number ?? $order->no_order }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Klien:</span>
                                <span class="font-medium text-gray-900 ml-1">{{ $order->klien->nama ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Fulfillment:</span>
                                <span class="font-medium {{ $order->getFulfillmentPercentage() > 100 ? 'text-red-600' : 'text-green-600' }} ml-1">
                                    {{ number_format($order->getFulfillmentPercentage(), 1) }}%
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500">Sisa:</span>
                                <span class="font-medium text-orange-600 ml-1">
                                    {{ number_format(max(0, $order->total_qty - $order->getShippedQty()), 0, ',', '.') }} {{ $order->orderDetails->first()->satuan ?? 'unit' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Note Input --}}
                    <div>
                        <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan untuk Direktur (opsional)
                        </label>
                        <textarea
                            name="catatan"
                            id="catatan"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                            placeholder="Jelaskan situasi atau pertanyaan Anda..."
                        ></textarea>
                    </div>

                    <p class="mt-3 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Notifikasi akan dikirim ke semua Direktur aktif untuk meminta saran.
                    </p>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3 bg-gray-50 rounded-b-xl">
                    <button type="button" onclick="closeConsultModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors text-sm font-medium">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Konsultasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openConsultModal() {
        document.getElementById('consultModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeConsultModal() {
        document.getElementById('consultModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConsultModal();
        }
    });
</script>
@endif
@endsection
