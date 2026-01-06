<div class="min-h-screen bg-gray-50">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 5000)"
             x-show="show"
             x-transition
             class="fixed top-4 right-4 z-50 max-w-md">
            <div class="bg-green-600 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold">Berhasil!</p>
                    <p class="text-sm text-green-100">{{ session('message') }}</p>
                </div>
                <button @click="show = false" class="text-white/80 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 8000)"
             x-show="show"
             x-transition
             class="fixed top-4 right-4 z-50 max-w-md">
            <div class="bg-red-600 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold">Gagal!</p>
                    <p class="text-sm text-red-100">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-white/80 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Riwayat Order</h1>
                        <p class="text-gray-600 text-sm">
                            @if($showAllOrders)
                                Menampilkan Semua PO
                            @else
                                Periode: {{ $currentMonthName }} {{ $selectedYear }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('orders.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Buat Order Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6">
        {{-- Month/Year Navigation --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                    <select id="selectedMonth" wire:model.live="selectedMonth" onchange="(function(){const p=new URLSearchParams(window.location.search);p.set('selectedMonth',this.value);const yEl=document.getElementById('selectedYear');if(yEl) p.set('selectedYear', yEl.value);window.location.href=window.location.pathname + (p.toString() ? ('?' + p.toString()) : '');}).call(this)" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                    <select id="selectedYear" wire:model.live="selectedYear" onchange="(function(){const p=new URLSearchParams(window.location.search);p.set('selectedYear',this.value);const mEl=document.getElementById('selectedMonth');if(mEl) p.set('selectedMonth', mEl.value);window.location.href=window.location.pathname + (p.toString() ? ('?' + p.toString()) : '');}).call(this)" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center space-x-2 flex-wrap gap-2">
                    @if($showAllOrders)
                        <button wire:click="goToCurrentMonth" class="px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                            <i class="fas fa-calendar-day mr-1"></i>
                            Kembali Ke Bulan Ini
                        </button>
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Menampilkan <strong>semua PO</strong>
                        </span>
                    @else
                        @if($selectedMonth != now()->month || $selectedYear != now()->year)
                            <button onclick="(function(){const d=new Date();const m=d.getMonth()+1;const y=d.getFullYear();const p=new URLSearchParams(window.location.search);p.set('selectedMonth',m);p.set('selectedYear',y);p.delete('showAllOrders');window.location.href=window.location.pathname + (p.toString() ? ('?' + p.toString()) : '');})();" class="px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                <i class="fas fa-calendar-day mr-1"></i>
                                Kembali Ke Bulan Ini
                            </button>
                        @endif

                        <span class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Menampilkan order untuk <strong>{{ $currentMonthName }} {{ $selectedYear }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status Count Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Order</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($statusCounts['all']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Draft</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($statusCounts['draft']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Dikonfirmasi</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ number_format($statusCounts['dikonfirmasi']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Diproses</p>
                        <p class="text-2xl font-bold text-orange-600">{{ number_format($statusCounts['diproses']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cogs text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>



            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Selesai</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($statusCounts['selesai']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-double text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Dibatalkan</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($statusCounts['dibatalkan']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters and Search --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                {{-- Search --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-gray-400"></i>
                        Cari Order
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nomor order, nama klien, atau cabang..."
                    >
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-filter mr-1 text-gray-400"></i>
                        Filter Status
                    </label>
                    <select
                        wire:model.live="statusFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Semua Status ({{ $statusCounts['all'] }})</option>
                        <option value="draft">Draft ({{ $statusCounts['draft'] }})</option>
                        <option value="dikonfirmasi">Dikonfirmasi ({{ $statusCounts['dikonfirmasi'] }})</option>
                        <option value="diproses">Diproses ({{ $statusCounts['diproses'] }})</option>
                        <option value="selesai">Selesai ({{ $statusCounts['selesai'] }})</option>
                        <option value="dibatalkan">Dibatalkan ({{ $statusCounts['dibatalkan'] }})</option>
                    </select>
                </div>

                {{-- Klien Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-1 text-gray-400"></i>
                        Filter Klien
                    </label>
                    <select
                        wire:model.live="klienFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Semua Klien</option>
                        @foreach($kliens as $klien)
                            <option value="{{ $klien->id }}">{{ $klien->nama }} - {{ $klien->cabang }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Material Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-box mr-1 text-gray-400"></i>
                        Filter Material
                    </label>
                    <select
                        wire:model.live="materialFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Semua Material</option>
                        @foreach($materials as $material)
                            <option value="{{ $material }}">{{ $material }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Priority Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-1 text-gray-400"></i>
                        Prioritas
                    </label>
                    <select
                        wire:model.live="priorityFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Semua Prioritas</option>
                        <option value="rendah">Rendah</option>
                        <option value="sedang">Sedang</option>
                        <option value="tinggi">Tinggi</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort mr-1 text-gray-400"></i>
                        Urutkan
                    </label>
                    <select
                    wire:model.live="sortBy"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="priority_desc">Prioritas (Tertinggi)</option>
                    <option value="priority_asc">Prioritas (Terendah)</option>
                    <!-- New sort options: Client (Pabrik) and Material -->
                    <option value="client_asc">Pabrik / Klien (A → Z)</option>
                    <option value="client_desc">Pabrik / Klien (Z → A)</option>
                    <option value="material_asc">Bahan Baku (A → Z)</option>
                    <option value="material_desc">Bahan Baku (Z → A)</option>
                    <option value="tanggal_desc">Tanggal (Terbaru)</option>
                    <option value="tanggal_asc">Tanggal (Terlama)</option>
                    <option value="total_desc">Total (Tertinggi)</option>
                    <option value="total_asc">Total (Terendah)</option>
                    <option value="status_asc">Status (A-Z)</option>
                    <option value="status_desc">Status (Z-A)</option>
                </select>
                </div>
            </div>

            {{-- Clear Filters --}}
            @if($search || $statusFilter || $klienFilter || $priorityFilter || $materialFilter || $sortBy !== 'priority_desc')
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <button
                        wire:click="clearFilters"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Reset Semua Filter
                    </button>
                </div>
            @endif
        </div>

        {{-- Orders Grid --}}
        <div class="space-y-6">
            @forelse($orders as $order)
                @php
                    // Calculate outstanding (remaining/unshipped) qty and amount
                    $outstandingSummary = [];
                    $outstandingAmount = 0;
                    $outstandingQtyTotal = 0;

                    foreach ($order->orderDetails as $detail) {
                        $totalQty = $detail->qty ?? 0;
                        $shippedQty = $detail->total_shipped_quantity ?? 0;
                        $remainingQty = $totalQty - $shippedQty;
                        $hargaJual = $detail->harga_jual ?? 0;
                        $unitKey = $detail->satuan ?: 'unit';

                        // Add to summary by unit - only remaining/outstanding quantity
                        $outstandingSummary[$unitKey] = ($outstandingSummary[$unitKey] ?? 0) + $remainingQty;
                        $outstandingQtyTotal += $remainingQty;
                        // Outstanding amount = remaining qty × selling price
                        $outstandingAmount += ($remainingQty * $hargaJual);
                    }

                    // Format display untuk outstanding qty
                    $outstandingDisplay = collect($outstandingSummary)
                        ->map(function ($qty, $unit) {
                            return number_format($qty, 0, ',', '.') . ' ' . $unit;
                        })
                        ->implode(' | ');
                @endphp
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Order Header --}}
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    <x-order.status-badge :status="$order->status" />
                                    <x-order.priority-badge :priority="$order->priority" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">
                                        {{ $order->po_number ?? $order->no_order }}
                                    </h3>
                                    @if($order->po_number && $order->no_order)
                                        <div class="text-xs text-gray-500">ID Sistem: {{ $order->no_order }}</div>
                                    @endif
                                    <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
                                        <span>
                                            <i class="far fa-calendar mr-1"></i>
                                            {{ $order->tanggal_order->format('d M Y') }}
                                        </span>
                                        <span>
                                            <i class="fas fa-user mr-1"></i>
                                            {{ $order->creator->name }}
                                        </span>
                                        @if($order->winner)
                                            <span class="flex items-center">
                                                <i class="fas fa-trophy mr-1 text-yellow-500"></i>
                                                <span class="font-medium text-yellow-700">PO Winner: {{ $order->winner->user->nama ?? $order->winner->user->name }}</span>
                                            </span>
                                        @endif
                                        @if($order->po_end_date)
                                            <span>
                                                <i class="far fa-calendar-check mr-1"></i>
                                                Jatuh Tempo: {{ $order->po_end_date->format('d M Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600">Total Order</div>
                                <div class="text-xl font-bold text-green-600">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $order->total_items }} item{{ $order->total_items > 1 ? 's' : '' }} |
                                    {{ number_format($order->total_qty, 0) }} total qty
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Outstanding Qty: {{ $outstandingDisplay ?: '0' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Outstanding Amount: Rp {{ number_format($outstandingAmount, 0, ',', '.') }}
                                </div>
                                @if($order->po_document_url)
                                    <div class="mt-2">
                                        <a href="{{ $order->po_document_url }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-download mr-1"></i>
                                            Surat PO
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Client Info --}}
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-building text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $order->klien->nama }}</div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $order->klien->cabang }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">
                                    {{ $order->orderDetails->count() }} material{{ $order->orderDetails->count() > 1 ? 's' : '' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $order->orderDetails->sum(function($detail) { return $detail->orderSuppliers->count(); }) }} total supplier{{ $order->orderDetails->sum(function($detail) { return $detail->orderSuppliers->count(); }) > 1 ? 's' : '' }}
                                </div>
                                @if($order->status !== 'draft' && $order->total_qty > 0)
                                    <div class="text-xs text-gray-500">
                                        Progress: {{ number_format($order->getFulfillmentPercentage(), 1) }}%
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Materials List with Expandable Suppliers --}}
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-cubes mr-2 text-purple-600"></i>
                                Daftar Material & Supplier ({{ $order->orderDetails->count() }} item)
                            </h4>
                            <button
                                wire:click="toggleOrderExpansion({{ $order->id }})"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
                            >
                                @if(in_array($order->id, $expandedOrders))
                                    <i class="fas fa-chevron-up mr-1"></i>
                                    Sembunyikan Detail
                                @else
                                    <i class="fas fa-chevron-down mr-1"></i>
                                    Lihat Detail Supplier
                                @endif
                            </button>
                        </div>

                        {{-- Compact View (Default) --}}
                        @if(!in_array($order->id, $expandedOrders))
                            <div class="space-y-2">
                                @foreach($order->orderDetails as $detail)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-box text-purple-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $detail->bahanBakuKlien->nama }}</div>
                                                <div class="text-sm text-gray-600">
                                                    {{ number_format($detail->qty, 0) }} {{ $detail->bahanBakuKlien->satuan ?? 'unit' }} ×
                                                    Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-semibold text-gray-900">
                                                Rp {{ number_format($detail->total_harga, 0, ',', '.') }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $detail->orderSuppliers->count() }} supplier{{ $detail->orderSuppliers->count() > 1 ? 's' : '' }}
                                                @if($detail->orderSuppliers->count() > 0)
                                                    | Best margin: {{ number_format($detail->orderSuppliers->first()->calculated_margin ?? 0, 1) }}%
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Expanded View with Supplier Details --}}
                        @if(in_array($order->id, $expandedOrders))
                            <div class="space-y-4">
                                @foreach($order->orderDetails as $detail)
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        {{-- Material Header --}}
                                        <div class="bg-purple-50 border-b border-purple-200 p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                                        <i class="fas fa-box text-purple-600"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="font-semibold text-gray-900">{{ $detail->bahanBakuKlien->nama }}</h5>
                                                        <div class="text-sm text-gray-600">
                                                            {{ number_format($detail->qty, 0) }} {{ $detail->bahanBakuKlien->satuan ?? 'unit' }} ×
                                                            Rp {{ number_format($detail->harga_jual, 0, ',', '.') }} =
                                                            <span class="font-semibold text-gray-900">Rp {{ number_format($detail->total_amount, 0, ',', '.') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-sm text-gray-600">
                                                        {{ $detail->orderSuppliers->count() }} supplier tersedia
                                                    </div>
                                                    @if($detail->qty_shipped > 0)
                                                        <div class="text-xs text-green-600">
                                                            Shipped: {{ number_format($detail->qty_shipped, 0) }} / {{ number_format($detail->qty, 0) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Supplier Options --}}
                                        @if($detail->orderSuppliers->count() > 0)
                                            <div class="p-4">
                                                <div class="space-y-3">
                                                    @foreach($detail->orderSuppliers->sortBy('price_rank') as $orderSupplier)
                                                        <div class="flex items-center justify-between p-3 rounded-lg border
                                                            {{ $orderSupplier->is_recommended ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white' }}">
                                                            <div class="flex items-center">
                                                                @if($orderSupplier->is_recommended)
                                                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                                        <i class="fas fa-star text-green-600 text-sm"></i>
                                                                    </div>
                                                                @else
                                                                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                                                        <i class="fas fa-truck text-gray-600 text-sm"></i>
                                                                    </div>
                                                                @endif
                                                                <div>
                                                                    <div class="font-medium text-gray-900 flex items-center">
                                                                        {{ $orderSupplier->supplier->nama }}
                                                                        @if($orderSupplier->is_recommended)
                                                                            <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                                                Recommended
                                                                            </span>
                                                                        @endif
                                                                        <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                                                            Rank #{{ $orderSupplier->price_rank }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="text-sm text-gray-600">
                                                                        {{ $orderSupplier->supplier->alamat }} |
                                                                        Material: {{ $orderSupplier->bahanBakuSupplier->nama ?? 'N/A' }}
                                                                        @if($orderSupplier->supplier->picPurchasing)
                                                                            | PIC: {{ $orderSupplier->supplier->picPurchasing->nama }}
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="text-right">
                                                                <div class="text-sm font-semibold text-gray-900">
                                                                    Rp {{ number_format($orderSupplier->unit_price, 0, ',', '.') }}
                                                                </div>
                                                                <div class="text-xs text-gray-600">
                                                                    per {{ $detail->bahanBakuKlien->satuan ?? 'unit' }}
                                                                </div>
                                                                <div class="text-xs {{ $orderSupplier->calculated_margin >= 20 ? 'text-green-600' : ($orderSupplier->calculated_margin >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                                                    Margin: {{ number_format($orderSupplier->calculated_margin, 1) }}%
                                                                </div>
                                                                @if($orderSupplier->shipped_quantity > 0)
                                                                    <div class="text-xs text-blue-600 mt-1">
                                                                        Shipped: {{ number_format($orderSupplier->shipped_quantity, 0) }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-4 text-center text-gray-500">
                                                <i class="fas fa-exclamation-triangle mb-2"></i>
                                                <div class="text-sm">Belum ada supplier yang tersedia untuk material ini</div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>
                                    Lihat Detail
                                </a>
                                @if($order->status === 'draft' && (auth()->user()->isMarketing() || auth()->user()->isDirektur()))
                                    <a href="{{ route('orders.edit', $order) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>
                                        Edit
                                    </a>
                                @endif
                            </div>
                            @if(auth()->user()->isMarketing() || auth()->user()->isDirektur())
                            <div class="flex items-center space-x-2">
                                @if($order->status === 'draft')
                                    <button
                                        wire:click="confirmOrder({{ $order->id }})"
                                        class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        <i class="fas fa-check mr-1"></i>
                                        Konfirmasi
                                    </button>
                                @elseif($order->status === 'dikonfirmasi')
                                    <button
                                        wire:click="startProcessing({{ $order->id }})"
                                        class="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        <i class="fas fa-play mr-1"></i>
                                        Mulai Proses
                                    </button>
                                @elseif($order->status === 'diproses')
                                    <button
                                        wire:click="confirmComplete({{ $order->id }})"
                                        class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        <i class="fas fa-check-double mr-1"></i>
                                        Selesaikan
                                    </button>
                                @endif

                                {{-- Tombol Hapus muncul di SEMUA status --}}
                                <button
                                    wire:click="confirmDelete({{ $order->id }})"
                                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    title="Hapus Order (hanya jika tidak ada forecast/pengiriman)"
                                >
                                    <i class="fas fa-trash mr-1"></i>
                                    Hapus
                                </button>

                                @if(!in_array($order->status, ['selesai', 'dibatalkan']))
                                    <button
                                        wire:click="confirmCancel({{ $order->id }})"
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        <i class="fas fa-times mr-1"></i>
                                        Batalkan
                                    </button>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Order</h3>
                    <p class="text-gray-600 mb-4">
                        @if($search || $statusFilter || $klienFilter || $priorityFilter)
                            Tidak ditemukan order dengan kriteria pencarian yang Anda masukkan.
                        @else
                            Mulai dengan membuat order baru untuk klien Anda.
                        @endif
                    </p>
                    @if($search || $statusFilter || $klienFilter || $priorityFilter)
                        <button
                            wire:click="clearFilters"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Reset Filter
                        </button>
                    @else
                        <a href="{{ route('orders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            <i class="fas fa-plus mr-2"></i>
                            Buat Order Pertama
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300" wire:click="cancelDelete"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-100">
                    {{-- Header --}}
                    <div class="px-6 py-4 bg-red-600 rounded-t-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-trash text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Konfirmasi Hapus Order</h3>
                                <p class="text-sm text-red-100">Tindakan ini tidak dapat dibatalkan!</p>
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-6">
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xl mr-3 mt-0.5"></i>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-red-900 mb-2">Peringatan Penting!</h4>
                                    <p class="text-sm text-red-800 mb-3">
                                        Order ini akan <strong>dihapus permanen</strong> beserta semua data terkait:
                                    </p>
                                    <ul class="text-sm text-red-800 space-y-1 list-disc list-inside">
                                        <li>Semua detail material order</li>
                                        <li>Data supplier yang dipilih</li>
                                        <li>Konsultasi yang telah dilakukan</li>
                                        <li>Dokumen PO (jika ada)</li>
                                        <li>Data winner (jika ada)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-0.5"></i>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-blue-900 mb-2">Syarat Hapus Order (STRICT)</h4>
                                    <p class="text-sm text-blue-800">
                                        Hanya order dengan kondisi berikut yang bisa dihapus:
                                    </p>
                                    <ul class="text-sm text-blue-800 space-y-1 mt-2 list-disc list-inside">
                                        <li><strong>Status = DRAFT</strong> (belum dikonfirmasi)</li>
                                        <li><strong>Tidak ada forecasting</strong> terkait</li>
                                        <li><strong>Tidak ada pengiriman</strong> terkait</li>
                                    </ul>
                                    <p class="text-xs text-blue-700 mt-3 font-medium">
                                        💡 Jika tidak memenuhi syarat, gunakan fitur "Batalkan" sebagai alternatif.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <p class="text-gray-700 font-medium">
                            Apakah Anda yakin ingin <span class="text-red-600">menghapus order ini</span>?
                        </p>
                    </div>

                    {{-- Footer Actions --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex justify-end space-x-3">
                        <button
                            wire:click="cancelDelete"
                            class="px-4 py-2 text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition-colors font-medium"
                        >
                            <i class="fas fa-times mr-1"></i>
                            Batal
                        </button>
                        <button
                            wire:click="deleteOrder"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium shadow-lg hover:shadow-xl"
                        >
                            <i class="fas fa-trash mr-1"></i>
                            Ya, Hapus Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Complete Confirmation Modal --}}
    @if($showCompleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300" wire:click="cancelComplete"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-100">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-check-double text-green-600 text-lg"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Selesaikan Order</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menandai order ini sebagai <span class="font-semibold text-green-600">selesai</span>?</p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-2"></i>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium mb-1">Pastikan:</p>
                                    <ul class="list-disc list-inside space-y-1 text-blue-700">
                                        <li>Semua material sudah dikirim</li>
                                        <li>Dokumen sudah lengkap</li>
                                        <li>Pembayaran sudah diproses</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                        <button
                            wire:click="cancelComplete"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            wire:click="completeOrder({{ $orderToComplete }})"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center"
                        >
                            <i class="fas fa-check mr-2"></i>
                            Ya, Selesaikan Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Cancel Confirmation Modal --}}
    @if($showCancelModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300" wire:click="cancelCancelation"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-100">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-times-circle text-red-600 text-lg"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Pembatalan Order</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">Apakah Anda yakin ingin <span class="font-semibold text-red-600">membatalkan</span> order ini?</p>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                                <div class="text-sm text-yellow-800">
                                    <p class="font-medium">Perhatian: Tindakan ini tidak dapat dibatalkan!</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alasan Pembatalan <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                wire:model="cancelReason"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="Masukkan alasan pembatalan order..."
                                required
                            ></textarea>
                            @error('cancelReason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                        <button
                            wire:click="cancelCancelation"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            wire:click="cancelOrder({{ $orderToCancel }})"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Ya, Batalkan Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Client-side script to preserve URL params when reloading after Livewire update --}}
    <script>
        (function () {
            // Guard for environments without Livewire loaded yet
            window.__reloadAfterLivewire = window.__reloadAfterLivewire || false;

            function buildAndReplaceUrl() {
                try {
                    const params = new URLSearchParams(window.location.search);
                    const monthEl = document.getElementById('selectedMonth');
                    const yearEl = document.getElementById('selectedYear');

                    if (monthEl) {
                        // Only set if value present
                        params.set('selectedMonth', monthEl.value);
                    }
                    if (yearEl) {
                        params.set('selectedYear', yearEl.value);
                    }

                    // Preserve other existing params already in URL (search, filters, etc.)
                    const newSearch = params.toString();
                    const newUrl = window.location.pathname + (newSearch ? ('?' + newSearch) : '');

                    // Replace only if different
                    if (newUrl !== window.location.pathname + window.location.search) {
                        window.location.replace(newUrl);
                    } else {
                        // As a fallback, do a soft reload to ensure DOM state is consistent
                        window.location.reload();
                    }
                } catch (e) {
                    // Fall back to a normal reload if anything fails
                    console.error('Error building URL for reload:', e);
                    window.location.reload();
                }
            }

            // If Livewire is available, hook into the processed message lifecycle.
            // We use 'message.processed' to act after Livewire has applied DOM diff and updated history state.
            function attachLivewireHook() {
                if (window.Livewire && typeof window.Livewire.hook === 'function') {
                    // Hook will be called many times; we only act when reload flag is set
                    window.Livewire.hook('message.processed', function(message, component) {
                        if (window.__reloadAfterLivewire) {
                            // reset flag
                            window.__reloadAfterLivewire = false;
                            // small delay to allow Livewire to finish history updates
                            setTimeout(buildAndReplaceUrl, 60);
                        }
                    });
                } else {
                    // If Livewire isn't available, attach onchange handlers to fallback to reload preserving params
                    const month = document.getElementById('selectedMonth');
                    const year = document.getElementById('selectedYear');

                    if (month) {
                        month.addEventListener('change', function () {
                            // set params and reload (fallback)
                            try {
                                const params = new URLSearchParams(window.location.search);
                                params.set('selectedMonth', month.value);
                                if (year) params.set('selectedYear', year.value);
                                const newSearch = params.toString();
                                const newUrl = window.location.pathname + (newSearch ? ('?' + newSearch) : '');
                                window.location.replace(newUrl);
                            } catch (e) {
                                window.location.reload();
                            }
                        });
                    }
                    if (year) {
                        year.addEventListener('change', function () {
                            try {
                                const params = new URLSearchParams(window.location.search);
                                if (month) params.set('selectedMonth', month.value);
                                params.set('selectedYear', year.value);
                                const newSearch = params.toString();
                                const newUrl = window.location.pathname + (newSearch ? ('?' + newSearch) : '');
                                window.location.replace(newUrl);
                            } catch (e) {
                                window.location.reload();
                            }
                        });
                    }
                }
            }

            // If Livewire is not yet loaded, wait for DOMContentLoaded and then attach.
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', attachLivewireHook);
            } else {
                attachLivewireHook();
            }

            // Additionally, whenever user changes either select, set the reload flag so the Livewire hook will redirect
            document.addEventListener('change', function (e) {
                const target = e.target;
                if (!target) return;
                if (target.id === 'selectedMonth' || target.id === 'selectedYear') {
                    window.__reloadAfterLivewire = true;
                }
            });

            // For the "Kembali Ke Bulan Ini" button we set the reload flag inline via onclick attribute.
            // But also listen for clicks on any element with data attribute if needed in future.
            document.addEventListener('click', function (e) {
                const el = e.target.closest && e.target.closest('button[wire\\:click="goToCurrentMonth"], button[onclick]');
                if (el && el.getAttribute && el.getAttribute('wire:click') === 'goToCurrentMonth') {
                    // ensure flag is set (in case onclick wasn't present)
                    window.__reloadAfterLivewire = true;
                }
            });
        })();
    </script>
</div>
