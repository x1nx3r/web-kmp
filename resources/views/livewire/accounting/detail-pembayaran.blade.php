<div class="relative">
    {{-- Navigation Breadcrumb --}}
    <div class="bg-white border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                <nav aria-label="Breadcrumb" class="w-full">
                    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        <li>
                            <a href="{{ route('dashboard') }}" class="flex items-center text-gray-400 hover:text-gray-500">
                                <i class="fas fa-home"></i>
                                <span class="sr-only">Home</span>
                            </a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span>Accounting</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <a href="{{ route('accounting.approval-pembayaran') }}" class="text-gray-500 hover:text-gray-700">Approval Pembayaran</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span class="text-gray-900 font-medium">Detail</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Header with Back Button --}}
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('accounting.approval-pembayaran') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Detail Approval Pembayaran</h1>
            </div>

            {{-- Status Badge --}}
            @if($approval)
                <span class="px-4 py-2 text-sm font-semibold rounded-full
                    @if($approval->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($approval->status === 'approved') bg-green-100 text-green-800
                    @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    <i class="fas fa-circle text-xs mr-1"></i>
                    {{ ucfirst($approval->status) }}
                </span>
            @endif
        </div>

        @if($approval && $pengiriman)
            {{-- Informasi Pengiriman --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-shipping-fast text-blue-600 mr-3"></i>
                        Informasi Pengiriman
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nomor Pengiriman</label>
                            <p class="mt-1 text-base text-gray-900 font-semibold">{{ $pengiriman->no_pengiriman ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Kirim</label>
                            <p class="mt-1 text-base text-gray-900">
                                <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                {{ $pengiriman->tanggal_kirim ? \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d M Y') : '-' }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-500">Total Qty Kirim</label>
                            <p class="mt-1 text-base text-gray-900 font-semibold">{{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-500">Total Harga (Original)</label>
                            <p class="mt-1 text-xl text-gray-900 font-bold">Rp {{ number_format($pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Pengiriman Details --}}
                    @if($pengiriman->pengirimanDetails && count($pengiriman->pengirimanDetails) > 0)
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Detail Pengiriman</h3>
                            <div class="space-y-4">
                                @foreach($pengiriman->pengirimanDetails as $detail)
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Supplier</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $detail->bahanBakuSupplier->supplier->nama ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Bahan Baku</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $detail->bahanBakuSupplier->nama ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Qty Kirim</label>
                                                <p class="mt-1 text-sm text-gray-900 font-semibold">{{ number_format($detail->qty_kirim, 2, ',', '.') }} kg</p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Harga Satuan</label>
                                                <p class="mt-1 text-sm text-gray-900">Rp {{ number_format($detail->harga_satuan, 2, ',', '.') }}/kg</p>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="text-xs font-medium text-gray-500">Total Harga</label>
                                                <p class="mt-1 text-base text-gray-900 font-bold">Rp {{ number_format($detail->total_harga, 2, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Ringkasan Pembayaran --}}
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg shadow-sm border border-indigo-200 mb-6">
                <div class="border-b border-indigo-200 bg-indigo-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-calculator text-indigo-600 mr-3"></i>
                        Ringkasan Perhitungan Pembayaran
                    </h2>
                    <p class="text-sm text-indigo-700 mt-1">Detail perhitungan dari harga pengiriman hingga pembayaran final</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        {{-- Total Harga Pengiriman (Original) --}}
                        <div class="flex justify-between items-center pb-3 border-b border-indigo-200">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Total Harga Pengiriman</p>
                                <p class="text-xs text-gray-500 mt-1">Harga awal dari pengiriman ({{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg)</p>
                            </div>
                            <p class="text-lg font-bold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                        </div>

                        {{-- Refraksi Penagihan (Customer) --}}
                        @if($invoicePenagihan && $invoicePenagihan->refraksi_amount > 0)
                            <div class="flex justify-between items-center pb-3 border-b border-indigo-200 bg-purple-50 -mx-6 px-6 py-3">
                                <div>
                                    <p class="text-sm font-medium text-purple-700 flex items-center">
                                        <i class="fas fa-minus-circle mr-2"></i>
                                        Refraksi Penagihan (Customer)
                                    </p>
                                    <p class="text-xs text-purple-600 mt-1">
                                        @if($invoicePenagihan->refraksi_type === 'qty')
                                            {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}% dari qty
                                        @elseif($invoicePenagihan->refraksi_type === 'rupiah')
                                            Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}/kg
                                        @elseif($invoicePenagihan->refraksi_type === 'lainnya')
                                            Lainnya: Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}
                                        @endif
                                        @if($invoicePenagihan->refraksi_type !== 'lainnya')
                                            | Qty: {{ number_format($invoicePenagihan->qty_before_refraksi, 2, ',', '.') }} kg → {{ number_format($invoicePenagihan->qty_after_refraksi, 2, ',', '.') }} kg
                                        @endif
                                    </p>
                                </div>
                                <p class="text-lg font-bold text-red-600">- Rp {{ number_format($invoicePenagihan->refraksi_amount, 2, ',', '.') }}</p>
                            </div>

                            {{-- Subtotal setelah refraksi penagihan --}}
                            <div class="flex justify-between items-center pb-3 border-b border-indigo-300">
                                <div>
                                    <p class="text-sm font-semibold text-gray-700">Subtotal (Setelah Refraksi Penagihan)</p>
                                    <p class="text-xs text-gray-500 mt-1">Nilai yang akan ditagihkan ke customer</p>
                                </div>
                                <p class="text-lg font-bold text-indigo-700">Rp {{ number_format($invoicePenagihan->amount_after_refraksi ?? $pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                            </div>
                        @endif

                        {{-- Refraksi Pembayaran (Supplier) --}}
                        @if($approval->refraksi_amount > 0)
                            <div class="flex justify-between items-center pb-3 border-b border-indigo-200 bg-green-50 -mx-6 px-6 py-3">
                                <div>
                                    <p class="text-sm font-medium text-green-700 flex items-center">
                                        <i class="fas fa-minus-circle mr-2"></i>
                                        Refraksi Pembayaran (Supplier)
                                    </p>
                                    <p class="text-xs text-green-600 mt-1">
                                        @if($approval->refraksi_type === 'qty')
                                            {{ number_format($approval->refraksi_value, 2, ',', '.') }}% dari qty
                                        @elseif($approval->refraksi_type === 'rupiah')
                                            Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}/kg
                                        @elseif($approval->refraksi_type === 'lainnya')
                                            Lainnya: Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}
                                        @endif
                                        @if($approval->refraksi_type !== 'lainnya')
                                            | Qty: {{ number_format($approval->qty_before_refraksi, 2, ',', '.') }} kg → {{ number_format($approval->qty_after_refraksi, 2, ',', '.') }} kg
                                        @endif
                                    </p>
                                </div>
                                <p class="text-lg font-bold text-red-600">- Rp {{ number_format($approval->refraksi_amount, 2, ',', '.') }}</p>
                            </div>
                        @endif

                        {{-- Potongan Piutang --}}
                        @if($approval->piutang_amount > 0)
                            <div class="flex justify-between items-center pb-3 border-b border-indigo-200 bg-blue-50 -mx-6 px-6 py-3">
                                <div>
                                    <p class="text-sm font-medium text-blue-700 flex items-center">
                                        <i class="fas fa-minus-circle mr-2"></i>
                                        Potongan Piutang
                                    </p>
                                    @if($approval->catatanPiutang)
                                        <p class="text-xs text-blue-600 mt-1">
                                            ID Piutang #{{ $approval->catatanPiutang->id }} | {{ $approval->catatanPiutang->supplier->nama ?? '-' }}
                                        </p>
                                    @endif
                                </div>
                                <p class="text-lg font-bold text-red-600">- Rp {{ number_format($approval->piutang_amount, 2, ',', '.') }}</p>
                            </div>
                        @endif

                        {{-- Total Pembayaran Final --}}
                        <div class="flex justify-between items-center pt-3 bg-gradient-to-r from-green-100 to-emerald-100 -mx-6 px-6 py-4 rounded-lg border-2 border-green-300">
                            <div>
                                <p class="text-base font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-hand-holding-usd text-green-600 mr-2"></i>
                                    Total Pembayaran ke Supplier
                                </p>
                                <p class="text-xs text-gray-600 mt-1">Nilai final yang harus dibayar kepada supplier</p>
                            </div>
                            @php
                                // Base amount adalah total harga pengiriman (bukan dari invoice penagihan)
                                // Karena refraksi pembayaran dan piutang dihitung dari harga pengiriman
                                $totalPembayaran = $pengiriman->total_harga_kirim - ($approval->refraksi_amount ?? 0) - ($approval->piutang_amount ?? 0);
                            @endphp
                            <p class="text-2xl font-bold text-green-700">Rp {{ number_format($totalPembayaran, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Refraksi Penagihan (dari Invoice) --}}
            @if($invoicePenagihan && $invoicePenagihan->refraksi_type)
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-sm border border-purple-200 mb-6">
                    <div class="border-b border-purple-200 bg-purple-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-file-invoice text-purple-600 mr-3"></i>
                            Refraksi Penagihan (Customer)
                        </h2>
                        <p class="text-sm text-purple-700 mt-1">Refraksi yang dikenakan kepada customer</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm font-medium text-purple-700">Jenis Refraksi</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">
                                    @if($invoicePenagihan->refraksi_type === 'qty')
                                        <i class="fas fa-percentage text-purple-600 mr-2"></i>Qty (%)
                                    @elseif($invoicePenagihan->refraksi_type === 'rupiah')
                                        <i class="fas fa-money-bill text-purple-600 mr-2"></i>Rupiah (Rp/kg)
                                    @elseif($invoicePenagihan->refraksi_type === 'lainnya')
                                        <i class="fas fa-calculator text-purple-600 mr-2"></i>Lainnya (Manual)
                                    @else
                                        <i class="fas fa-question-circle text-purple-600 mr-2"></i>{{ ucfirst($invoicePenagihan->refraksi_type) }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-purple-700">Nilai Refraksi</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">
                                    @if($invoicePenagihan->refraksi_type === 'qty')
                                        {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}%
                                    @elseif($invoicePenagihan->refraksi_type === 'rupiah')
                                        Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}/kg
                                    @elseif($invoicePenagihan->refraksi_type === 'lainnya')
                                        Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }} (Total)
                                    @else
                                        Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                            @if($invoicePenagihan->refraksi_type !== 'lainnya')
                                <div>
                                    <label class="text-sm font-medium text-purple-700">Qty Sebelum Refraksi</label>
                                    <p class="mt-1 text-base text-gray-900">{{ number_format($invoicePenagihan->qty_before_refraksi, 2, ',', '.') }} kg</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-purple-700">Qty Setelah Refraksi</label>
                                    <p class="mt-1 text-base text-gray-900 font-semibold">{{ number_format($invoicePenagihan->qty_after_refraksi, 2, ',', '.') }} kg</p>
                                </div>
                            @endif
                            <div>
                                <label class="text-sm font-medium text-purple-700">Potongan Refraksi</label>
                                <p class="mt-1 text-xl text-red-600 font-bold">
                                    - Rp {{ number_format($invoicePenagihan->refraksi_amount, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-purple-700">Total Invoice Penagihan</label>
                                <p class="mt-1 text-xl text-green-600 font-bold">
                                    Rp {{ number_format($invoicePenagihan->amount_after_refraksi ?? $pengiriman->total_harga_kirim, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-purple-50 rounded-lg border border-purple-200 p-4 mb-6">
                    <p class="text-purple-700 text-sm flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Belum ada refraksi penagihan untuk pengiriman ini
                    </p>
                </div>
            @endif

            {{-- Refraksi Pembayaran (dari Approval) --}}
            @if($approval->refraksi_type || $approval->piutang_amount > 0)
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm border border-green-200 mb-6">
                    <div class="border-b border-green-200 bg-green-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-hand-holding-usd text-green-600 mr-3"></i>
                            Refraksi Pembayaran (Supplier)
                        </h2>
                        <p class="text-sm text-green-700 mt-1">Refraksi dan potongan piutang yang dikenakan kepada supplier</p>
                    </div>
                    <div class="p-6">
                        @if($approval->refraksi_type)
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-percent text-green-600 mr-2"></i>
                                    Refraksi
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Jenis Refraksi</label>
                                        <p class="mt-1 text-base text-gray-900 font-semibold">
                                            @if($approval->refraksi_type === 'qty')
                                                <i class="fas fa-percentage text-green-600 mr-2"></i>Qty (%)
                                            @elseif($approval->refraksi_type === 'rupiah')
                                                <i class="fas fa-money-bill text-green-600 mr-2"></i>Rupiah (Rp/kg)
                                            @elseif($approval->refraksi_type === 'lainnya')
                                                <i class="fas fa-calculator text-green-600 mr-2"></i>Lainnya (Manual)
                                            @else
                                                <i class="fas fa-question-circle text-green-600 mr-2"></i>{{ ucfirst($approval->refraksi_type) }}
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Nilai Refraksi</label>
                                        <p class="mt-1 text-base text-gray-900 font-semibold">
                                            @if($approval->refraksi_type === 'qty')
                                                {{ number_format($approval->refraksi_value, 2, ',', '.') }}%
                                            @elseif($approval->refraksi_type === 'rupiah')
                                                Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}/kg
                                            @elseif($approval->refraksi_type === 'lainnya')
                                                Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }} (Total)
                                            @else
                                                Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}
                                            @endif
                                        </p>
                                    </div>
                                    @if($approval->refraksi_type !== 'lainnya')
                                        <div>
                                            <label class="text-sm font-medium text-green-700">Qty Sebelum Refraksi</label>
                                            <p class="mt-1 text-base text-gray-900">{{ number_format($approval->qty_before_refraksi, 2, ',', '.') }} kg</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-green-700">Qty Setelah Refraksi</label>
                                            <p class="mt-1 text-base text-gray-900 font-semibold">{{ number_format($approval->qty_after_refraksi, 2, ',', '.') }} kg</p>
                                        </div>
                                    @endif
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Potongan Refraksi</label>
                                        <p class="mt-1 text-xl text-red-600 font-bold">
                                            - Rp {{ number_format($approval->refraksi_amount, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($approval->piutang_amount > 0)
                            <div class="@if($approval->refraksi_type) border-t border-green-200 pt-6 @endif">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-file-invoice-dollar text-blue-600 mr-2"></i>
                                    Potongan Piutang Supplier
                                </h3>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @if($approval->catatanPiutang)
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">ID Piutang</label>
                                                <p class="mt-1 text-sm text-gray-900 font-semibold">
                                                    #{{ $approval->catatanPiutang->id }}
                                                </p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">Supplier</label>
                                                <p class="mt-1 text-sm text-gray-900">
                                                    {{ $approval->catatanPiutang->supplier->nama ?? '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">Tanggal Piutang</label>
                                                <p class="mt-1 text-sm text-gray-900">
                                                    {{ $approval->catatanPiutang->tanggal_piutang ? \Carbon\Carbon::parse($approval->catatanPiutang->tanggal_piutang)->format('d M Y') : '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">Sisa Piutang Sebelum Potong</label>
                                                <p class="mt-1 text-sm text-gray-900 font-semibold">
                                                    Rp {{ number_format($approval->catatanPiutang->sisa_piutang + $approval->piutang_amount, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        @endif
                                        <div>
                                            <label class="text-xs font-medium text-blue-700">Jumlah Dipotong</label>
                                            <p class="mt-1 text-lg text-red-600 font-bold">
                                                - Rp {{ number_format($approval->piutang_amount, 2, ',', '.') }}
                                            </p>
                                        </div>
                                        @if($approval->catatanPiutang)
                                            <div>
                                                <label class="text-xs font-medium text-blue-700">Sisa Piutang Setelah Potong</label>
                                                <p class="mt-1 text-lg text-orange-600 font-bold">
                                                    Rp {{ number_format($approval->catatanPiutang->sisa_piutang, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        @endif
                                        @if($approval->piutang_notes)
                                            <div class="md:col-span-2">
                                                <label class="text-xs font-medium text-blue-700">Catatan Pemotongan</label>
                                                <p class="mt-1 text-sm text-gray-700 bg-white p-3 rounded border border-blue-200">
                                                    <i class="fas fa-sticky-note text-blue-500 mr-2"></i>{{ $approval->piutang_notes }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="@if($approval->refraksi_type || $approval->piutang_amount > 0) border-t border-green-200 pt-6 mt-6 @endif">
                            <div class="bg-green-100 border-2 border-green-300 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-green-700">Total Pembayaran ke Supplier</label>
                                        <p class="text-xs text-gray-600 mt-1">
                                            Harga Awal
                                            @if($approval->refraksi_amount > 0)
                                                - Refraksi
                                            @endif
                                            @if($approval->piutang_amount > 0)
                                                - Piutang
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl text-green-600 font-extrabold">
                                            Rp {{ number_format(($approval->amount_after_refraksi ?? $pengiriman->total_harga_kirim) - ($approval->piutang_amount ?? 0), 2, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            <span class="line-through">Rp {{ number_format($pengiriman->total_harga_kirim, 2, ',', '.') }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-green-50 rounded-lg border border-green-200 p-4 mb-6">
                    <p class="text-green-700 text-sm flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Belum ada refraksi pembayaran untuk pengiriman ini
                    </p>
                </div>
            @endif

            {{-- Edit Forms (Only in Edit Mode) --}}
            @if($canManage && $editMode)
                <div class="space-y-6 mb-6">
                    {{-- Edit Refraksi Pembayaran --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-edit text-yellow-600 mr-2"></i>
                            Edit Refraksi Pembayaran
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Refraksi ini untuk approval pembayaran, terpisah dari refraksi penagihan (invoice).
                        </p>

                        @if(session()->has('message'))
                            <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <p class="text-green-700 text-sm font-medium">{{ session('message') }}</p>
                                </div>
                            </div>
                        @endif

                        @if(session()->has('error'))
                            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                    <p class="text-red-700 text-sm font-medium">{{ session('error') }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipe Refraksi
                                </label>
                                <select
                                    wire:model="refraksiForm.type"
                                    class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                >
                                    <option value="qty">Refraksi Qty (%)</option>
                                    <option value="rupiah">Refraksi Rupiah (Rp/kg)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nilai Refraksi
                                </label>
                                <input
                                    type="number"
                                    wire:model="refraksiForm.value"
                                    min="0"
                                    step="0.01"
                                    class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    placeholder="{{ ($refraksiForm['type'] ?? 'qty') === 'qty' ? '1 untuk 1%' : '40 untuk Rp 40/kg' }}"
                                />
                            </div>
                        </div>

                        <button
                            wire:click="updateRefraksi"
                            wire:loading.attr="disabled"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-400"
                        >
                            <span wire:loading.remove wire:target="updateRefraksi">
                                <i class="fas fa-save mr-1"></i>
                                Update Refraksi Pembayaran
                            </span>
                            <span wire:loading wire:target="updateRefraksi">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Menyimpan...
                            </span>
                        </button>
                    </div>

                    {{-- Edit Total Harga Beli --}}
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-money-bill-wave text-indigo-600 mr-2"></i>
                            Edit Total Harga Beli
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Edit langsung total harga beli (setelah refraksi) yang akan dibayarkan ke supplier.
                        </p>

                        @if(session()->has('message'))
                            <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <p class="text-green-700 text-sm font-medium">{{ session('message') }}</p>
                                </div>
                            </div>
                        @endif

                        @if(session()->has('error'))
                            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                    <p class="text-red-700 text-sm font-medium">{{ session('error') }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tags mr-1"></i>
                                Total Harga Beli (Rp)
                            </label>
                            <input
                                type="number"
                                wire:model="totalHargaBeliForm"
                                min="0"
                                step="0.01"
                                class="block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Masukkan total harga beli"
                            />
                            <p class="mt-2 text-xs text-gray-500">
                                <i class="fas fa-calculator mr-1"></i>
                                Nilai saat ini: <strong>Rp {{ number_format($approval->amount_after_refraksi ?? $pengiriman->total_harga_kirim, 0, ',', '.') }}</strong>
                            </p>
                        </div>

                        <button
                            wire:click="updateTotalHargaBeli"
                            wire:loading.attr="disabled"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors disabled:bg-gray-400"
                        >
                            <span wire:loading.remove wire:target="updateTotalHargaBeli">
                                <i class="fas fa-save mr-1"></i>
                                Update Total Harga Beli
                            </span>
                            <span wire:loading wire:target="updateTotalHargaBeli">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Menyimpan...
                            </span>
                        </button>
                    </div>

                    {{-- Edit Bukti Pembayaran --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-file-invoice text-blue-600 mr-2"></i>
                            Edit Bukti Pembayaran
                        </h3>

                        {{-- Existing Files Display --}}
                        @if(!empty($existingBuktiPembayaran))
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    <i class="fas fa-folder-open mr-1"></i>
                                    File Saat Ini ({{ count($existingBuktiPembayaran) }} file)
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach($existingBuktiPembayaran as $index => $filePath)
                                        @php
                                            $fileUrl = Storage::disk('public')->url($filePath);
                                            $fileName = basename($filePath);
                                            $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                        @endphp
                                        <div class="relative group border border-gray-300 rounded-lg p-3 bg-white hover:shadow-lg transition-shadow">
                                            {{-- Remove Button --}}
                                            <button
                                                type="button"
                                                wire:click="removeExistingFile({{ $index }})"
                                                class="absolute -top-2 -right-2 w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors z-10 shadow-lg"
                                                title="Hapus file"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>

                                            {{-- File Preview --}}
                                            @if(in_array($fileExt, ['jpg', 'jpeg', 'png']))
                                                <a href="{{ $fileUrl }}" target="_blank" class="block">
                                                    <img src="{{ $fileUrl }}" alt="Preview" class="w-full h-32 object-cover rounded mb-2">
                                                </a>
                                            @elseif($fileExt === 'pdf')
                                                <a href="{{ $fileUrl }}" target="_blank" class="block text-center py-8">
                                                    <i class="fas fa-file-pdf text-red-500 text-4xl"></i>
                                                </a>
                                            @endif

                                            {{-- File Name --}}
                                            <p class="text-xs text-gray-600 truncate" title="{{ $fileName }}">
                                                <i class="fas fa-file text-gray-400 mr-1"></i>
                                                {{ Str::limit($fileName, 18) }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-sm text-blue-600 mt-3">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Klik tombol <i class="fas fa-times text-red-600"></i> untuk menghapus file yang tidak diperlukan
                                </p>
                            </div>
                        @endif

                        {{-- Upload New Files --}}
                        <p class="text-sm text-gray-600 mb-4">
                            {{ !empty($existingBuktiPembayaran) ? 'Tambah file baru (opsional)' : 'Upload bukti pembayaran (multiple files)' }} dalam format JPG, PNG, atau PDF (Total Max: 20MB)
                        </p>

                        <div class="mb-4">
                            <input
                                type="file"
                                wire:model="buktiPembayaran"
                                accept=".jpg,.jpeg,.png,.pdf"
                                multiple
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer"
                            />
                        </div>

                        {{-- Upload Progress --}}
                        <div wire:loading wire:target="buktiPembayaran" class="mb-4">
                            <div class="flex items-center text-blue-600">
                                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm font-medium">Mengunggah file...</span>
                            </div>
                        </div>

                        @error('buktiPembayaran.*')
                            <p class="text-sm text-red-600 mb-3">{{ $message }}</p>
                        @enderror

                        {{-- New File Preview --}}
                        @if($buktiPembayaran && count($buktiPembayaran) > 0)
                            <div class="mb-4 space-y-3">
                                <label class="block text-sm font-medium text-green-700 mb-2">
                                    <i class="fas fa-plus-circle mr-1"></i>
                                    File Baru yang Akan Ditambahkan ({{ count($buktiPembayaran) }} file)
                                </label>

                                @php
                                    $totalSize = 0;
                                    foreach($buktiPembayaran as $file) {
                                        $totalSize += $file->getSize();
                                    }
                                @endphp

                                <div class="p-3 bg-green-50 border border-green-200 rounded">
                                    <p class="text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-upload text-green-600 mr-2"></i>
                                        {{ count($buktiPembayaran) }} file baru dipilih
                                        <span class="text-gray-600 ml-2">
                                            (Total: {{ number_format($totalSize / 1024 / 1024, 2) }} MB)
                                        </span>
                                    </p>
                                    @if($totalSize > 20 * 1024 * 1024)
                                        <p class="text-sm text-red-600 font-semibold mt-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Total ukuran file melebihi 20 MB!
                                        </p>
                                    @endif
                                </div>

                                @foreach($buktiPembayaran as $index => $file)
                                    <div class="p-3 bg-white border border-green-200 rounded">
                                        <p class="text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-file-upload text-green-600 mr-2"></i>
                                            {{ $file->getClientOriginalName() }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Ukuran: {{ number_format($file->getSize() / 1024, 2) }} KB
                                        </p>
                                        @if(in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']))
                                            <div class="mt-2">
                                                <img src="{{ $file->temporaryUrl() }}" alt="Preview" class="w-full h-auto rounded border border-gray-200 max-h-48 object-contain">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <button
                            wire:click="updateBuktiPembayaran"
                            wire:loading.attr="disabled"
                            wire:target="buktiPembayaran,updateBuktiPembayaran"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400"
                        >
                            <span wire:loading.remove wire:target="updateBuktiPembayaran">
                                <i class="fas fa-save mr-1"></i>
                                Update Bukti Pembayaran
                            </span>
                            <span wire:loading wire:target="updateBuktiPembayaran">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Menyimpan...
                            </span>
                        </button>
                    </div>

                    {{-- Edit Piutang (Only for Pending) --}}
                    @if($approval->status === 'pending')
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-edit text-indigo-600 mr-2"></i>
                            {{ $approval->catatan_piutang_id ? 'Edit' : 'Tambah' }} Pemotongan Piutang
                        </h3>

                        @php
                            $supplier = $approval->pengiriman->pengirimanDetails->first()?->bahanBakuSupplier->supplier ?? null;
                            $piutangList = $supplier ? \App\Models\CatatanPiutang::where('supplier_id', $supplier->id)
                                ->where('status', '!=', 'lunas')
                                ->where('sisa_piutang', '>', 0)
                                ->orderBy('tanggal_piutang', 'asc')
                                ->with('supplier')
                                ->get() : collect();
                        @endphp

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Pilih Piutang Supplier
                                    @if($supplier)
                                        <span class="text-blue-600">({{ $supplier->nama }})</span>
                                    @endif
                                </label>
                                <select wire:model.live="piutangForm.catatan_piutang_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Tidak ada pemotongan piutang --</option>
                                    @foreach($piutangList as $index => $piutang)
                                        <option value="{{ $piutang->id }}">
                                            {{ $index === 0 ? '⭐ ' : '' }}{{ \Carbon\Carbon::parse($piutang->tanggal_piutang)->format('d/m/Y') }} - Sisa: Rp {{ number_format($piutang->sisa_piutang, 2, ',', '.') }}{{ $index === 0 ? ' (Terlama)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($piutangList->isEmpty() && $supplier)
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Supplier ini tidak memiliki piutang aktif
                                    </p>
                                @elseif(!$piutangList->isEmpty())
                                    <p class="text-xs text-indigo-600 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Piutang diurutkan dari yang terlama (sistem FIFO)
                                    </p>
                                @endif
                            </div>

                            @if($piutangForm['catatan_piutang_id'])
                                @php
                                    $selectedPiutang = $piutangList->firstWhere('id', $piutangForm['catatan_piutang_id']);
                                @endphp
                                <div class="bg-blue-50 border border-blue-200 rounded p-4">
                                    <p class="text-sm font-medium text-blue-800 mb-2">Informasi Piutang Terpilih</p>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-gray-600">Total Piutang:</span>
                                            <span class="font-semibold ml-2">Rp {{ number_format($selectedPiutang->jumlah_piutang ?? 0, 2, ',', '.') }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Sisa Piutang:</span>
                                            <span class="font-semibold ml-2 text-orange-600">Rp {{ number_format($selectedPiutang->sisa_piutang ?? 0, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Jumlah Pemotongan <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
                                        <input
                                            type="number"
                                            wire:model="piutangForm.amount"
                                            min="0"
                                            step="0.01"
                                            max="{{ $selectedPiutang->sisa_piutang ?? 0 }}"
                                            class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="0"
                                        >
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Maksimal: Rp {{ number_format($selectedPiutang->sisa_piutang ?? 0, 2, ',', '.') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                    <textarea
                                        wire:model="piutangForm.notes"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Tambahkan catatan untuk pemotongan ini..."
                                    ></textarea>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-sm text-yellow-800 flex items-start">
                                <i class="fas fa-exclamation-triangle mr-2 mt-0.5"></i>
                                <span>Perubahan data piutang akan dicatat dalam riwayat perubahan.</span>
                            </p>
                        </div>

                        <button
                            wire:click="updatePiutang"
                            wire:loading.attr="disabled"
                            class="mt-4 w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors disabled:bg-gray-400"
                        >
                            <span wire:loading.remove wire:target="updatePiutang">
                                <i class="fas fa-save mr-1"></i>
                                Update Data Piutang
                            </span>
                            <span wire:loading wire:target="updatePiutang">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                    @endif
                </div>
            @endif

            {{-- Bukti Foto Bongkar --}}
            @if($pengiriman->bukti_foto_bongkar_raw)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-camera text-blue-600 mr-3"></i>
                                Bukti Foto Bongkar
                            </h2>
                            @if($pengiriman->bukti_foto_bongkar_uploaded_at)
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    Upload: {{ $pengiriman->bukti_foto_bongkar_uploaded_at->format('d M Y, H:i') }} WIB
                                    <span class="text-gray-400">({{ $pengiriman->bukti_foto_bongkar_uploaded_at->diffForHumans() }})</span>
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="p-6">
                        @php
                            $photos = $pengiriman->bukti_foto_bongkar_array ?? [];
                        @endphp

                        @if(is_array($photos) && count($photos) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($photos as $index => $photo)
                                    @if($photo)
                                        @php
                                            $photoUrl = asset('storage/pengiriman/bukti/' . $photo);
                                        @endphp
                                        <div class="relative group">
                                            <img
                                                src="{{ $photoUrl }}"
                                                alt="Bukti Foto Bongkar {{ $index + 1 }}"
                                                class="w-full h-48 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                                onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjIwMCIgY3k9IjEyMCIgcj0iMzAiIGZpbGw9IiM5Q0EzQUYiLz4KPHRleHQgeD0iMjAwIiB5PSIyNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzZCNzI4MCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+R2FtYmFyIHRpZGFrIGRpdGVtdWthbjwvdGV4dD4KPC9zdmc+'; this.classList.add('opacity-50');">
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                                <div class="flex space-x-2">
                                                    <button onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                            class="bg-white text-blue-600 p-2 rounded-full shadow-lg hover:bg-blue-50 transition-all"
                                                            title="Lihat gambar">
                                                        <i class="fas fa-eye text-sm"></i>
                                                    </button>
                                                    <button onclick="event.stopPropagation(); downloadImage('{{ $photoUrl }}', '{{ $photo }}');"
                                                            class="bg-white text-green-600 p-2 rounded-full shadow-lg hover:bg-green-50 transition-all"
                                                            title="Download gambar">
                                                        <i class="fas fa-download text-sm"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-12">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-image text-gray-400 text-3xl"></i>
                                </div>
                                <p class="text-gray-500 text-sm">Tidak ada bukti foto bongkar</p>
                                <p class="text-gray-400 text-xs mt-1">Foto belum diunggah untuk pengiriman ini</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <script>
                if (typeof window.downloadImage !== 'function') {
                    window.downloadImage = function(imageSrc, imageName = 'bukti_foto_bongkar.jpg') {
                        const link = document.createElement('a');
                        link.href = imageSrc;
                        link.download = imageName;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    };
                }
            </script>

            {{-- Bukti Pembayaran --}}
            @if($approval->bukti_pembayaran)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-receipt text-green-600 mr-3"></i>
                            Bukti Pembayaran
                        </h2>
                    </div>
                    <div class="p-6">
                        @php
                            // Check if bukti_pembayaran is JSON array or single file
                            $buktiFiles = [];
                            try {
                                $decoded = json_decode($approval->bukti_pembayaran, true);
                                if (is_array($decoded)) {
                                    $buktiFiles = $decoded;
                                } else {
                                    $buktiFiles = [$approval->bukti_pembayaran];
                                }
                            } catch (\Exception $e) {
                                $buktiFiles = [$approval->bukti_pembayaran];
                            }
                        @endphp

                        @if(count($buktiFiles) > 1)
                            <p class="text-sm text-gray-600 mb-4 text-center">
                                <i class="fas fa-files text-green-600 mr-2"></i>
                                {{ count($buktiFiles) }} file terupload
                            </p>
                        @endif

                        <div class="grid grid-cols-1 @if(count($buktiFiles) > 1) md:grid-cols-2 @endif gap-6">
                            @foreach($buktiFiles as $index => $filePath)
                                @php
                                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                                    $isPdf = strtolower($extension) === 'pdf';
                                @endphp

                                <div class="flex flex-col items-center">
                                    @if(count($buktiFiles) > 1)
                                        <p class="text-sm font-semibold text-gray-700 mb-3">
                                            Bukti Pembayaran #{{ $index + 1 }}
                                        </p>
                                    @endif

                                    @if($isPdf)
                                        {{-- PDF Preview --}}
                                        <div class="w-full">
                                            <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-6 text-center">
                                                <i class="fas fa-file-pdf text-red-500 text-5xl mb-3"></i>
                                                <p class="text-gray-700 font-medium mb-2">Dokumen PDF</p>
                                                <p class="text-xs text-gray-500 mb-4">Bukti pembayaran dalam format PDF</p>
                                                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                                    <a
                                                        href="{{ Storage::url($filePath) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors text-sm"
                                                    >
                                                        <i class="fas fa-external-link-alt mr-2"></i>
                                                        Buka PDF
                                                    </a>
                                                    <a
                                                        href="{{ Storage::url($filePath) }}"
                                                        download
                                                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors text-sm"
                                                    >
                                                        <i class="fas fa-download mr-2"></i>
                                                        Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Image Preview --}}
                                        <div class="relative w-full bg-white p-4 rounded-lg">
                                            <img
                                                src="{{ Storage::url($filePath) }}"
                                                alt="Bukti Pembayaran {{ $index + 1 }}"
                                                class="w-full h-auto rounded-lg shadow-md border-2 border-gray-200 hover:border-green-400 transition-all cursor-pointer"
                                                onclick="openPaymentModal('{{ Storage::url($filePath) }}')"
                                            >
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500 flex items-center justify-center">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Klik gambar untuk memperbesar
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 text-center border-t border-gray-200 pt-4">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-user mr-1"></i>
                                Diupload oleh: {{ $approval->manager->nama ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $approval->manager_approved_at ? $approval->manager_approved_at->format('d M Y, H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Payment Image Modal --}}
                <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closePaymentModal()">
                    <div class="relative max-w-6xl max-h-full">
                        <button onclick="closePaymentModal(); event.stopPropagation();" class="absolute -top-12 right-0 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center z-10">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                        <img id="paymentModalImage" src="" alt="Bukti Pembayaran" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl bg-white">
                    </div>
                </div>

                <script>
                    function openPaymentModal(imageSrc) {
                        const modal = document.getElementById('paymentModal');
                        const modalImage = document.getElementById('paymentModalImage');
                        modalImage.src = imageSrc;
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        document.body.style.overflow = 'hidden';
                    }

                    function closePaymentModal() {
                        const modal = document.getElementById('paymentModal');
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        document.body.style.overflow = '';
                    }

                    // Close payment modal with Escape key
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            closePaymentModal();
                        }
                    });
                </script>
            @endif

            {{-- Approval History --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history text-gray-600 mr-3"></i>
                        Riwayat Approval & Perubahan
                    </h2>
                </div>
                <div class="p-6">
                    @if($approvalHistory && count($approvalHistory) > 0)
                        <div class="space-y-4">
                            @foreach($approvalHistory as $history)
                                <div class="border-l-4 pl-4 py-3 rounded-r-lg
                                    @if($history->action === 'approved') border-green-500 bg-green-50
                                    @elseif($history->action === 'rejected') border-red-500 bg-red-50
                                    @elseif($history->action === 'edited') border-orange-500 bg-orange-50
                                    @else border-blue-500 bg-blue-50
                                    @endif">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold
                                                    @if($history->action === 'approved') bg-green-500
                                                    @elseif($history->action === 'rejected') bg-red-500
                                                    @elseif($history->action === 'edited') bg-orange-500
                                                    @else bg-blue-500
                                                    @endif">
                                                    {{ strtoupper(substr($history->user->nama ?? 'S', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="font-semibold text-gray-900">
                                                        {{ $history->user->nama ?? 'System' }}
                                                    </span>
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full ml-2
                                                        @if($history->action === 'approved') bg-green-200 text-green-800
                                                        @elseif($history->action === 'rejected') bg-red-200 text-red-800
                                                        @elseif($history->action === 'edited') bg-orange-200 text-orange-800
                                                        @else bg-blue-200 text-blue-800
                                                        @endif">
                                                        <i class="fas {{
                                                            $history->action === 'approved' ? 'fa-check-circle' :
                                                            ($history->action === 'rejected' ? 'fa-times-circle' :
                                                            ($history->action === 'edited' ? 'fa-edit' : 'fa-circle'))
                                                        }} mr-1"></i>
                                                        {{ ucfirst($history->action) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-1">
                                                <i class="fas fa-user-tag mr-1"></i>
                                                Role: <span class="font-medium">
                                                    @if($history->role === 'staff')
                                                        Staff Accounting
                                                    @elseif($history->role === 'manager_keuangan')
                                                        Manager Keuangan
                                                    @elseif($history->role === 'superadmin')
                                                        Direktur
                                                    @else
                                                        {{ ucfirst(str_replace('_', ' ', $history->role)) }}
                                                    @endif
                                                </span>
                                            </p>
                                            @if($history->notes)
                                                <p class="text-sm text-gray-700 mt-2 bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                                    <i class="fas fa-comment-dots text-gray-400 mr-2"></i>{{ $history->notes }}
                                                </p>
                                            @endif
                                            @if($history->changes && is_array($history->changes))
                                                <div class="mt-3 bg-white p-3 rounded-lg border border-gray-200">
                                                    <p class="text-xs font-semibold text-gray-700 mb-2">
                                                        <i class="fas fa-exchange-alt mr-1"></i>
                                                        Detail Perubahan:
                                                    </p>
                                                    @if(isset($history->changes['field']))
                                                        <p class="text-xs text-gray-600 mb-2">Field: <span class="font-medium">{{ ucfirst($history->changes['field']) }}</span></p>
                                                    @endif
                                                    @if(isset($history->changes['old']) && isset($history->changes['new']))
                                                        <div class="grid grid-cols-2 gap-3 mt-2">
                                                            <div class="bg-red-50 p-2 rounded border border-red-200">
                                                                <p class="text-xs text-red-700 font-semibold mb-1">
                                                                    <i class="fas fa-arrow-left mr-1"></i>Sebelum:
                                                                </p>
                                                                <div class="text-xs text-gray-700">
                                                                    @if(is_array($history->changes['old']))
                                                                        @foreach($history->changes['old'] as $key => $value)
                                                                            <p class="mb-1">
                                                                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                                                {{ is_numeric($value) ? number_format($value, 2, ',', '.') : ($value ?: '-') }}
                                                                            </p>
                                                                        @endforeach
                                                                    @else
                                                                        <p>{{ $history->changes['old'] ?: '-' }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="bg-green-50 p-2 rounded border border-green-200">
                                                                <p class="text-xs text-green-700 font-semibold mb-1">
                                                                    <i class="fas fa-arrow-right mr-1"></i>Sesudah:
                                                                </p>
                                                                <div class="text-xs text-gray-700">
                                                                    @if(is_array($history->changes['new']))
                                                                        @foreach($history->changes['new'] as $key => $value)
                                                                            <p class="mb-1">
                                                                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                                                {{ is_numeric($value) ? number_format($value, 2, ',', '.') : ($value ?: '-') }}
                                                                            </p>
                                                                        @endforeach
                                                                    @else
                                                                        <p>{{ $history->changes['new'] ?: '-' }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right text-sm text-gray-500 ml-4">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ \Carbon\Carbon::parse($history->created_at)->format('d M Y H:i') }}
                                            <p class="text-xs text-gray-400 mt-1">
                                                {{ \Carbon\Carbon::parse($history->created_at)->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-inbox text-gray-300 text-3xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">Belum ada riwayat approval</p>
                            <p class="text-gray-400 text-sm mt-1">Riwayat akan muncul setelah approval diproses</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-3"></i>
                <p class="text-red-700 font-medium">Data approval tidak ditemukan</p>
            </div>
        @endif
    </div>
</div>
