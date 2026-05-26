<div class="relative">
    {{-- Breadcrumb --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-6">
            <nav class="py-3">
                <ol class="flex items-center gap-1.5 text-sm text-gray-400">
                    <li>
                        <a href="{{ route('dashboard') }}" class="hover:text-gray-600 transition-colors">
                            <i class="fas fa-home"></i>
                        </a>
                    </li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li>Accounting</li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li>
                        <a href="{{ route('accounting.approval-pembayaran') }}" class="hover:text-gray-600 transition-colors">
                            Approval Pembayaran
                        </a>
                    </li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li class="text-gray-700 font-medium">Detail</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-6 py-8">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('accounting.approval-pembayaran') }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali
                </a>
                <h1 class="text-xl font-semibold text-gray-900">Detail Approval Pembayaran</h1>
            </div>

            @if($approval)
                <span class="px-3 py-1 text-xs font-semibold rounded-full
                    @if($approval->status === 'pending') bg-amber-50 text-amber-700 ring-1 ring-amber-200
                    @elseif($approval->status === 'approved') bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200
                    @elseif($approval->status === 'rejected') bg-red-50 text-red-700 ring-1 ring-red-200
                    @else bg-gray-50 text-gray-700 ring-1 ring-gray-200
                    @endif">
                    {{ ucfirst($approval->status) }}
                </span>
            @endif
        </div>

        @if($approval && $pengiriman)
            {{-- Informasi Pengiriman --}}
            <div class="bg-white rounded-xl border border-gray-200 mb-5">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-shipping-fast text-blue-500"></i>
                        Informasi Pengiriman
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Nomor Pengiriman</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $pengiriman->no_pengiriman ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Tanggal Kirim</p>
                            <p class="text-sm text-gray-900">
                                {{ $pengiriman->tanggal_kirim ? \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d M Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Total Qty</p>
                            <p class="text-sm font-semibold text-gray-900">{{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Total Harga</p>
                            <p class="text-sm font-bold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    @if($pengiriman->pengirimanDetails && count($pengiriman->pengirimanDetails) > 0)
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Detail Item</p>
                            <div class="space-y-3">
                                @foreach($pengiriman->pengirimanDetails as $detail)
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-4 bg-gray-50 rounded-lg text-sm">
                                        <div>
                                            <p class="text-xs text-gray-400 mb-0.5">Supplier</p>
                                            <p class="font-medium text-gray-800">{{ $detail->bahanBakuSupplier->supplier->nama ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 mb-0.5">Bahan Baku</p>
                                            <p class="text-gray-800">{{ $detail->bahanBakuSupplier->nama ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 mb-0.5">Qty Kirim</p>
                                            <p class="font-medium text-gray-800">{{ number_format($detail->qty_kirim, 2, ',', '.') }} kg</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 mb-0.5">Harga Satuan</p>
                                            <p class="text-gray-800">Rp {{ number_format($detail->harga_satuan, 2, ',', '.') }}/kg</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 mb-0.5">Total Harga</p>
                                            <p class="font-bold text-gray-900">Rp {{ number_format($detail->total_harga, 2, ',', '.') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Ringkasan Pembayaran --}}
            <div class="bg-white rounded-xl border border-gray-200 mb-5">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-calculator text-indigo-500"></i>
                        Ringkasan Perhitungan Pembayaran
                    </h2>
                </div>
                <div class="p-6 space-y-3">
                    {{-- Harga Awal --}}
                    <div class="flex justify-between items-center py-2 text-sm">
                        <div>
                            <p class="text-gray-700">Total Harga Pengiriman</p>
                            <p class="text-xs text-gray-400">{{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                        </div>
                        <p class="font-semibold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                    </div>

                    {{-- Refraksi Penagihan --}}
                    @if($invoicePenagihan && $invoicePenagihan->refraksi_amount > 0)
                        <div class="flex justify-between items-center py-2 text-sm border-t border-gray-100">
                            <div>
                                <p class="text-purple-700">Refraksi Penagihan (Customer)</p>
                                <p class="text-xs text-gray-400">
                                    @if($invoicePenagihan->refraksi_type === 'qty')
                                        {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}% dari qty
                                    @elseif($invoicePenagihan->refraksi_type === 'rupiah')
                                        Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}/kg
                                    @else
                                        Lainnya: Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}
                                    @endif
                                    @if($invoicePenagihan->refraksi_type !== 'lainnya')
                                        · {{ number_format($invoicePenagihan->qty_before_refraksi, 2, ',', '.') }} → {{ number_format($invoicePenagihan->qty_after_refraksi, 2, ',', '.') }} kg
                                    @endif
                                </p>
                            </div>
                            <p class="font-semibold text-red-600">- Rp {{ number_format($invoicePenagihan->refraksi_amount, 2, ',', '.') }}</p>
                        </div>

                        <div class="flex justify-between items-center py-2 text-sm border-t border-gray-100">
                            <p class="text-gray-600">Subtotal (setelah refraksi penagihan)</p>
                            <p class="font-semibold text-indigo-700">Rp {{ number_format($invoicePenagihan->amount_after_refraksi ?? $pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                        </div>
                    @endif

                    {{-- Refraksi Pembayaran --}}
                    @if($approval->refraksi_amount > 0)
                        <div class="flex justify-between items-center py-2 text-sm border-t border-gray-100">
                            <div>
                                <p class="text-emerald-700">Refraksi Pembayaran (Supplier)</p>
                                <p class="text-xs text-gray-400">
                                    @if($approval->refraksi_type === 'qty')
                                        {{ number_format($approval->refraksi_value, 2, ',', '.') }}% dari qty
                                    @elseif($approval->refraksi_type === 'rupiah')
                                        Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}/kg
                                    @else
                                        Lainnya: Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}
                                    @endif
                                    @if($approval->refraksi_type !== 'lainnya')
                                        · {{ number_format($approval->qty_before_refraksi, 2, ',', '.') }} → {{ number_format($approval->qty_after_refraksi, 2, ',', '.') }} kg
                                    @endif
                                </p>
                            </div>
                            <p class="font-semibold text-red-600">- Rp {{ number_format($approval->refraksi_amount, 2, ',', '.') }}</p>
                        </div>
                    @endif

                    {{-- Pengeluaran Tambahan --}}
                    @if(($approval->additional_expenses_total ?? 0) > 0)
                        <div class="flex justify-between items-center py-2 text-sm border-t border-gray-100">
                            <div>
                                <p class="text-orange-700">Pengeluaran Tambahan</p>
                                <p class="text-xs text-gray-400">Truk / Kuli / Fee / Lainnya</p>
                            </div>
                            <p class="font-semibold text-red-600">- Rp {{ number_format($approval->additional_expenses_total, 2, ',', '.') }}</p>
                        </div>
                    @endif

                    {{-- Subtotal (tanpa piutang) --}}
                    @php
                        $totalAwal = $pengiriman->total_harga_kirim ?? 0;
                        $refraksiPotongan = $approval->refraksi_amount ?? 0;
                        $pengeluaranTambahan = $approval->additional_expenses_total ?? 0;
                        $piutangPotongan = $approval->piutang_amount ?? 0;
                        $subtotal = $approval->subtotal ?? max(0, $totalAwal - $refraksiPotongan - $pengeluaranTambahan);
                        $totalPembayaran = $approval->total_dibayarkan ?? max(0, $subtotal - $piutangPotongan);
                    @endphp
                    <div class="flex justify-between items-center py-2 text-sm border-t border-gray-100">
                        <p class="text-gray-600">Subtotal</p>
                        <p class="font-semibold text-indigo-700">Rp {{ number_format($subtotal, 2, ',', '.') }}</p>
                    </div>

                    {{-- Piutang --}}
                    @if($approval->piutang_amount > 0)
                        <div class="flex justify-between items-center py-2 text-sm border-t border-gray-100">
                            <div>
                                <p class="text-blue-700">Potongan Piutang</p>
                                @if($approval->catatanPiutang)
                                    <p class="text-xs text-gray-400">#{{ $approval->catatanPiutang->id }} · {{ $approval->catatanPiutang->supplier->nama ?? '-' }}</p>
                                @endif
                            </div>
                            <p class="font-semibold text-red-600">- Rp {{ number_format($approval->piutang_amount, 2, ',', '.') }}</p>
                        </div>
                    @endif

                    {{-- Total Final --}}
                    <div class="flex justify-between items-center pt-4 mt-2 border-t-2 border-gray-200">
                        <div>
                            <p class="font-semibold text-gray-900">Total Pembayaran ke Supplier</p>
                            <p class="text-xs text-gray-400">Nilai final yang dibayarkan</p>
                        </div>
                        <p class="text-xl font-bold text-emerald-600">Rp {{ number_format($totalPembayaran, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Refraksi Penagihan Detail --}}
            @if($invoicePenagihan && $invoicePenagihan->refraksi_type)
                <div class="bg-white rounded-xl border border-gray-200 mb-5">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-purple-500"></i>
                            Refraksi Penagihan (Customer)
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Jenis Refraksi</p>
                                <p class="font-medium text-gray-800">
                                    @if($invoicePenagihan->refraksi_type === 'qty') Qty (%)
                                    @elseif($invoicePenagihan->refraksi_type === 'rupiah') Rupiah (Rp/kg)
                                    @elseif($invoicePenagihan->refraksi_type === 'lainnya') Lainnya (Manual)
                                    @else {{ ucfirst($invoicePenagihan->refraksi_type) }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Nilai Refraksi</p>
                                <p class="font-medium text-gray-800">
                                    @if($invoicePenagihan->refraksi_type === 'qty') {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}%
                                    @elseif($invoicePenagihan->refraksi_type === 'rupiah') Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}/kg
                                    @else Rp {{ number_format($invoicePenagihan->refraksi_value, 2, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                            @if($invoicePenagihan->refraksi_type !== 'lainnya')
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Perubahan Qty</p>
                                    <p class="font-medium text-gray-800">
                                        {{ number_format($invoicePenagihan->qty_before_refraksi, 2, ',', '.') }} → {{ number_format($invoicePenagihan->qty_after_refraksi, 2, ',', '.') }} kg
                                    </p>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Potongan Refraksi</p>
                                <p class="font-semibold text-red-600">- Rp {{ number_format($invoicePenagihan->refraksi_amount, 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Total Invoice Penagihan</p>
                                <p class="font-semibold text-emerald-600">Rp {{ number_format($invoicePenagihan->amount_after_refraksi ?? $pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-lg bg-purple-50 border border-purple-100 px-4 py-3 mb-5 text-sm text-purple-600 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    Belum ada refraksi penagihan untuk pengiriman ini
                </div>
            @endif

            {{-- Refraksi Pembayaran Detail --}}
            @if($approval->refraksi_type || $approval->piutang_amount > 0)
                <div class="bg-white rounded-xl border border-gray-200 mb-5">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-hand-holding-usd text-emerald-500"></i>
                            Refraksi Pembayaran (Supplier)
                        </h2>
                    </div>
                    <div class="p-6">
                        @if($approval->refraksi_type)
                            <div class="@if($approval->piutang_amount > 0) mb-6 pb-6 border-b border-gray-100 @endif">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Refraksi</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-400 mb-1">Jenis Refraksi</p>
                                        <p class="font-medium text-gray-800">
                                            @if($approval->refraksi_type === 'qty') Qty (%)
                                            @elseif($approval->refraksi_type === 'rupiah') Rupiah (Rp/kg)
                                            @elseif($approval->refraksi_type === 'lainnya') Lainnya (Manual)
                                            @else {{ ucfirst($approval->refraksi_type) }}
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 mb-1">Nilai Refraksi</p>
                                        <p class="font-medium text-gray-800">
                                            @if($approval->refraksi_type === 'qty') {{ number_format($approval->refraksi_value, 2, ',', '.') }}%
                                            @elseif($approval->refraksi_type === 'rupiah') Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}/kg
                                            @else Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}
                                            @endif
                                        </p>
                                    </div>
                                    @if($approval->refraksi_type !== 'lainnya')
                                        <div>
                                            <p class="text-xs text-gray-400 mb-1">Perubahan Qty</p>
                                            <p class="font-medium text-gray-800">
                                                {{ number_format($approval->qty_before_refraksi, 2, ',', '.') }} → {{ number_format($approval->qty_after_refraksi, 2, ',', '.') }} kg
                                            </p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-xs text-gray-400 mb-1">Potongan Refraksi</p>
                                        <p class="font-semibold text-red-600">- Rp {{ number_format($approval->refraksi_amount, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($approval->piutang_amount > 0)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Potongan Piutang Supplier</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                    @if($approval->catatanPiutang)
                                        <div>
                                            <p class="text-xs text-gray-400 mb-1">ID Piutang</p>
                                            <p class="font-medium text-gray-800">#{{ $approval->catatanPiutang->id }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 mb-1">Supplier</p>
                                            <p class="font-medium text-gray-800">{{ $approval->catatanPiutang->supplier->nama ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 mb-1">Tanggal Piutang</p>
                                            <p class="text-gray-800">{{ $approval->catatanPiutang->tanggal_piutang ? \Carbon\Carbon::parse($approval->catatanPiutang->tanggal_piutang)->format('d M Y') : '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 mb-1">Sisa Sebelum Potong</p>
                                            <p class="font-medium text-gray-800">Rp {{ number_format($approval->catatanPiutang->sisa_piutang + $approval->piutang_amount, 2, ',', '.') }}</p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-xs text-gray-400 mb-1">Jumlah Dipotong</p>
                                        <p class="font-semibold text-red-600">- Rp {{ number_format($approval->piutang_amount, 2, ',', '.') }}</p>
                                    </div>
                                    @if($approval->catatanPiutang)
                                        <div>
                                            <p class="text-xs text-gray-400 mb-1">Sisa Setelah Potong</p>
                                            <p class="font-semibold text-amber-600">Rp {{ number_format($approval->catatanPiutang->sisa_piutang, 2, ',', '.') }}</p>
                                        </div>
                                    @endif
                                    @if($approval->piutang_notes)
                                        <div class="md:col-span-3">
                                            <p class="text-xs text-gray-400 mb-1">Catatan Pemotongan</p>
                                            <p class="text-gray-700 bg-gray-50 rounded-lg px-3 py-2">{{ $approval->piutang_notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @else
                <div class="rounded-lg bg-emerald-50 border border-emerald-100 px-4 py-3 mb-5 text-sm text-emerald-700 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    Belum ada refraksi pembayaran untuk pengiriman ini
                </div>
            @endif

            {{-- Edit Forms --}}
            @if($canManage && $editMode)
                <div class="space-y-4 mb-5">
                    {{-- Edit Refraksi --}}
                    <div class="bg-white rounded-xl border border-amber-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-1 flex items-center gap-2">
                            <i class="fas fa-edit text-amber-500"></i>
                            Edit Refraksi Pembayaran
                        </h3>
                        <p class="text-xs text-gray-400 mb-4">Refraksi ini terpisah dari refraksi penagihan (invoice).</p>

                        @if(session()->has('message'))
                            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> {{ session('message') }}
                            </div>
                        @endif
                        @if(session()->has('error'))
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Tipe Refraksi</label>
                                <select wire:model="refraksiForm.type"
                                    class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent bg-gray-50">
                                    <option value="qty">Refraksi Qty (%)</option>
                                    <option value="rupiah">Refraksi Rupiah (Rp/kg)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nilai Refraksi</label>
                                <input type="number" wire:model="refraksiForm.value" min="0" step="0.01"
                                    class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent bg-gray-50"
                                    placeholder="{{ ($refraksiForm['type'] ?? 'qty') === 'qty' ? '1 untuk 1%' : '40 untuk Rp 40/kg' }}"  onwheel="this.blur()" />
                            </div>
                        </div>

                        <button wire:click="updateRefraksi" wire:loading.attr="disabled"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="updateRefraksi"><i class="fas fa-save mr-1.5"></i>Update Refraksi</span>
                            <span wire:loading wire:target="updateRefraksi"><i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...</span>
                        </button>
                    </div>

                    {{-- Edit Total Harga Beli --}}
                    <div class="bg-white rounded-xl border border-indigo-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-1 flex items-center gap-2">
                            <i class="fas fa-money-bill-wave text-indigo-500"></i>
                            Edit Total Harga Beli
                        </h3>
                        <p class="text-xs text-gray-400 mb-4">Edit langsung total harga beli (setelah refraksi) yang akan dibayarkan ke supplier.</p>

                        @if(session()->has('message'))
                            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> {{ session('message') }}
                            </div>
                        @endif
                        @if(session()->has('error'))
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Total Harga Beli (Rp)</label>
                            <input type="number" wire:model="totalHargaBeliForm" min="0" step="0.01"
                                class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-transparent bg-gray-50"
                                placeholder="Masukkan total harga beli"  onwheel="this.blur()" />
                            <p class="mt-1.5 text-xs text-gray-400">
                                Nilai saat ini: <strong>Rp {{ number_format($approval->amount_after_refraksi ?? $pengiriman->total_harga_kirim, 0, ',', '.') }}</strong>
                            </p>
                        </div>

                        <button wire:click="updateTotalHargaBeli" wire:loading.attr="disabled"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-500 hover:bg-indigo-600 rounded-lg transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="updateTotalHargaBeli"><i class="fas fa-save mr-1.5"></i>Update Total Harga Beli</span>
                            <span wire:loading wire:target="updateTotalHargaBeli"><i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...</span>
                        </button>
                    </div>

                    {{-- Edit Pengeluaran Tambahan --}}
                    <div class="bg-white rounded-xl border border-orange-200 p-6" wire:key="edit-expenses-{{ $approval->id }}">
                        <h3 class="text-sm font-semibold text-gray-800 mb-1 flex items-center gap-2">
                            <i class="fas fa-receipt text-orange-500"></i>
                            Edit Pengeluaran Tambahan
                        </h3>
                        <p class="text-xs text-gray-400 mb-4">Truk, kuli, fee, dan biaya lainnya yang dikurangkan dari total pembayaran.</p>

                        @if(session()->has('message'))
                            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> {{ session('message') }}
                            </div>
                        @endif
                        @if(session()->has('error'))
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            </div>
                        @endif

                        <div class="space-y-4">
                            {{-- Fixed: Truk, Kuli, Fee --}}
                            <div class="grid grid-cols-3 gap-3">
                                @foreach(['truk' => 'Truk', 'kuli' => 'Kuli', 'fee' => 'Fee'] as $key => $label)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $label }}</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                            <input type="number" wire:model.defer="expenseForm.{{ $key }}"
                                                min="0" step="0.01"
                                                class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-transparent bg-gray-50"
                                                placeholder="0"  onwheel="this.blur()">
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Dynamic others --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-semibold text-gray-600">Lainnya</p>
                                    <button type="button" wire:click="addOtherExpenseRow"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 transition-colors">
                                        <i class="fas fa-plus"></i> Tambah Baris
                                    </button>
                                </div>

                                <div class="space-y-2">
                                    @forelse(($expenseForm['others'] ?? []) as $i => $row)
                                        <div class="grid grid-cols-12 gap-2 items-center">
                                            <div class="col-span-6">
                                                <input type="text" wire:model.defer="expenseForm.others.{{ $i }}.type"
                                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-transparent bg-gray-50"
                                                    placeholder="Nama pengeluaran (contoh: Parkir, Tol)">
                                            </div>
                                            <div class="col-span-5">
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                    <input type="number" wire:model.defer="expenseForm.others.{{ $i }}.amount"
                                                        min="0" step="0.01"
                                                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-transparent bg-gray-50"
                                                        placeholder="0"  onwheel="this.blur()">
                                                </div>
                                            </div>
                                            <div class="col-span-1 flex justify-end">
                                                <button type="button" wire:click="removeOtherExpenseRow({{ $i }})"
                                                    class="w-8 h-8 flex items-center justify-center text-red-500 bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 transition-colors">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-gray-400">Belum ada baris lainnya.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Total preview --}}
                            @php
                                $previewTotal = floatval($expenseForm['truk'] ?? 0)
                                            + floatval($expenseForm['kuli'] ?? 0)
                                            + floatval($expenseForm['fee'] ?? 0);
                                foreach(($expenseForm['others'] ?? []) as $r) {
                                    $previewTotal += floatval($r['amount'] ?? 0);
                                }
                            @endphp
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-sm">
                                <span class="text-gray-500">Total Pengeluaran</span>
                                <span class="font-semibold text-orange-600">Rp {{ number_format($previewTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <button wire:click="updateExpenses" wire:loading.attr="disabled"
                            class="mt-4 w-full px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="updateExpenses"><i class="fas fa-save mr-1.5"></i>Simpan Pengeluaran Tambahan</span>
                            <span wire:loading wire:target="updateExpenses"><i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...</span>
                        </button>
                    </div>

                    {{-- Edit Bukti Pembayaran --}}
                    <div class="bg-white rounded-xl border border-blue-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-blue-500"></i>
                            Edit Bukti Pembayaran
                        </h3>

                        @if(!empty($existingBuktiPembayaran))
                            <div class="mb-5">
                                <p class="text-xs font-medium text-gray-600 mb-2">File Saat Ini ({{ count($existingBuktiPembayaran) }} file)</p>
                                <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
                                    @foreach($existingBuktiPembayaran as $index => $filePath)
                                        @php
                                            $fileUrl = Storage::disk('public')->url($filePath);
                                            $fileName = basename($filePath);
                                            $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                        @endphp
                                        <div class="relative group border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                            <button type="button" wire:click="removeExistingFile({{ $index }})"
                                                class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600 z-10 shadow">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @if(in_array($fileExt, ['jpg', 'jpeg', 'png']))
                                                <a href="{{ $fileUrl }}" target="_blank">
                                                    <img src="{{ $fileUrl }}" alt="Preview" class="w-full h-20 object-cover">
                                                </a>
                                            @elseif($fileExt === 'pdf')
                                                <a href="{{ $fileUrl }}" target="_blank" class="flex items-center justify-center h-20">
                                                    <i class="fas fa-file-pdf text-red-400 text-2xl"></i>
                                                </a>
                                            @endif
                                            <p class="text-xs text-gray-500 px-2 py-1 truncate">{{ Str::limit($fileName, 12) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <p class="text-xs text-gray-400 mb-3">
                            {{ !empty($existingBuktiPembayaran) ? 'Tambah file baru (opsional)' : 'Upload bukti pembayaran' }} — JPG, PNG, atau PDF (maks. 20MB total)
                        </p>

                        <input type="file" wire:model="buktiPembayaran" accept=".jpg,.jpeg,.png,.pdf" multiple
                            class="block w-full text-sm text-gray-500 mb-3 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 file:cursor-pointer" />

                        <div wire:loading wire:target="buktiPembayaran" class="flex items-center gap-2 text-blue-500 text-sm mb-3">
                            <i class="fas fa-spinner fa-spin"></i> Mengunggah...
                        </div>

                        @error('buktiPembayaran.*')
                            <p class="text-xs text-red-500 mb-2">{{ $message }}</p>
                        @enderror

                        @if($buktiPembayaran && count($buktiPembayaran) > 0)
                            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-700">
                                @php $totalSize = collect($buktiPembayaran)->sum(fn($f) => $f->getSize()); @endphp
                                <p class="font-medium mb-1">{{ count($buktiPembayaran) }} file dipilih ({{ number_format($totalSize / 1024 / 1024, 2) }} MB)</p>
                                @if($totalSize > 20 * 1024 * 1024)
                                    <p class="text-red-600 font-medium"><i class="fas fa-exclamation-triangle mr-1"></i>Total ukuran melebihi 20MB!</p>
                                @endif
                                @foreach($buktiPembayaran as $file)
                                    <p class="text-gray-600 mt-1">{{ $file->getClientOriginalName() }} ({{ number_format($file->getSize() / 1024, 1) }} KB)</p>
                                    @if(in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']))
                                        <img src="{{ $file->temporaryUrl() }}" class="mt-1 w-full max-h-32 object-contain rounded border border-emerald-200">
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <button wire:click="updateBuktiPembayaran" wire:loading.attr="disabled" wire:target="buktiPembayaran,updateBuktiPembayaran"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="updateBuktiPembayaran"><i class="fas fa-save mr-1.5"></i>Update Bukti Pembayaran</span>
                            <span wire:loading wire:target="updateBuktiPembayaran"><i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...</span>
                        </button>
                    </div>

                    {{-- Edit Piutang --}}
                    @if($approval->status === 'pending')
                        <div class="bg-white rounded-xl border border-indigo-200 p-6">
                            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-edit text-indigo-500"></i>
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

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                        Pilih Piutang Supplier
                                        @if($supplier) <span class="text-indigo-500">({{ $supplier->nama }})</span> @endif
                                    </label>
                                    <select wire:model.live="piutangForm.catatan_piutang_id"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                                        <option value="">-- Tidak ada pemotongan piutang --</option>
                                        @foreach($piutangList as $index => $piutang)
                                            <option value="{{ $piutang->id }}">
                                                {{ $index === 0 ? '⭐ ' : '' }}{{ \Carbon\Carbon::parse($piutang->tanggal_piutang)->format('d/m/Y') }} — Sisa: Rp {{ number_format($piutang->sisa_piutang, 2, ',', '.') }}{{ $index === 0 ? ' (Terlama)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($piutangList->isEmpty() && $supplier)
                                        <p class="text-xs text-gray-400 mt-1">Supplier ini tidak memiliki piutang aktif.</p>
                                    @elseif(!$piutangList->isEmpty())
                                        <p class="text-xs text-indigo-500 mt-1">Diurutkan dari yang terlama (FIFO).</p>
                                    @endif
                                </div>

                                @if($piutangForm['catatan_piutang_id'])
                                    @php $selectedPiutang = $piutangList->firstWhere('id', $piutangForm['catatan_piutang_id']); @endphp
                                    <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-3 text-sm grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs text-gray-400">Total Piutang</p>
                                            <p class="font-medium">Rp {{ number_format($selectedPiutang->jumlah_piutang ?? 0, 2, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400">Sisa Piutang</p>
                                            <p class="font-medium text-amber-600">Rp {{ number_format($selectedPiutang->sisa_piutang ?? 0, 2, ',', '.') }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Jumlah Pemotongan <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                            <input type="number" wire:model="piutangForm.amount" min="0" step="0.01"
                                                max="{{ $selectedPiutang->sisa_piutang ?? 0 }}"
                                                class="w-full pl-10 pr-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                                                placeholder="0"  onwheel="this.blur()">
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">Maks: Rp {{ number_format($selectedPiutang->sisa_piutang ?? 0, 2, ',', '.') }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Catatan (Opsional)</label>
                                        <textarea wire:model="piutangForm.notes" rows="3"
                                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                                            placeholder="Tambahkan catatan..."></textarea>
                                    </div>
                                @endif
                            </div>

                            <p class="mt-4 text-xs text-amber-600 flex items-center gap-1.5">
                                <i class="fas fa-exclamation-triangle"></i>
                                Perubahan data piutang akan dicatat dalam riwayat perubahan.
                            </p>

                            <button wire:click="updatePiutang" wire:loading.attr="disabled"
                                class="mt-4 w-full px-4 py-2 text-sm font-medium text-white bg-indigo-500 hover:bg-indigo-600 rounded-lg transition-colors disabled:opacity-50">
                                <span wire:loading.remove wire:target="updatePiutang"><i class="fas fa-save mr-1.5"></i>Update Data Piutang</span>
                                <span wire:loading wire:target="updatePiutang"><i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...</span>
                            </button>
                        </div>
                    @endif
                </div>



            @endif

            {{-- Bukti Foto Bongkar --}}
            @if($pengiriman->bukti_foto_bongkar_raw)
                <div class="bg-white rounded-xl border border-gray-200 mb-5">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-camera text-blue-500"></i>
                            Bukti Foto Bongkar
                        </h2>
                        @if($pengiriman->bukti_foto_bongkar_uploaded_at)
                            <p class="text-xs text-gray-400">
                                {{ $pengiriman->bukti_foto_bongkar_uploaded_at->format('d M Y, H:i') }} WIB
                                · {{ $pengiriman->bukti_foto_bongkar_uploaded_at->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                    <div class="p-6">
                        @php $photos = $pengiriman->bukti_foto_bongkar_array ?? []; @endphp

                        @if(is_array($photos) && count($photos) > 0)
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($photos as $index => $photo)
                                    @if($photo)
                                        @php $photoUrl = asset('storage/pengiriman/bukti/' . $photo); @endphp
                                        <div class="relative group rounded-lg overflow-hidden border border-gray-200">
                                            <img src="{{ $photoUrl }}" alt="Bukti {{ $index + 1 }}"
                                                class="w-full h-40 object-cover cursor-pointer"
                                                onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjIwMCIgY3k9IjEyMCIgcj0iMzAiIGZpbGw9IiM5Q0EzQUYiLz4KPHRleHQgeD0iMjAwIiB5PSIyNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzZCNzI4MCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+R2FtYmFyIHRpZGFrIGRpdGVtdWthbjwvdGV4dD4KPC9zdmc+'; this.classList.add('opacity-40');">
                                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                                <div class="flex gap-2">
                                                    <button onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                        class="bg-white text-blue-600 p-1.5 rounded-full shadow hover:bg-blue-50 text-xs">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button onclick="event.stopPropagation(); downloadImage('{{ $photoUrl }}', '{{ $photo }}');"
                                                        class="bg-white text-emerald-600 p-1.5 rounded-full shadow hover:bg-emerald-50 text-xs">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10 text-gray-400">
                                <i class="fas fa-image text-3xl mb-2"></i>
                                <p class="text-sm">Tidak ada bukti foto bongkar</p>
                            </div>
                        @endif
                    </div>
                </div>

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
            @endif

            {{-- Bukti Pembayaran --}}
            @if($approval->bukti_pembayaran)
                <div class="bg-white rounded-xl border border-gray-200 mb-5">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-receipt text-emerald-500"></i>
                            Bukti Pembayaran
                        </h2>
                    </div>
                    <div class="p-6">
                        @php
                            $buktiFiles = [];
                            try {
                                $decoded = json_decode($approval->bukti_pembayaran, true);
                                $buktiFiles = is_array($decoded) ? $decoded : [$approval->bukti_pembayaran];
                            } catch (\Exception $e) {
                                $buktiFiles = [$approval->bukti_pembayaran];
                            }
                        @endphp

                        @if(count($buktiFiles) > 1)
                            <p class="text-xs text-gray-400 mb-4">{{ count($buktiFiles) }} file terupload</p>
                        @endif

                        <div class="grid grid-cols-1 @if(count($buktiFiles) > 1) md:grid-cols-2 @endif gap-4">
                            @foreach($buktiFiles as $index => $filePath)
                                @php $isPdf = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'pdf'; @endphp
                                <div>
                                    @if(count($buktiFiles) > 1)
                                        <p class="text-xs font-medium text-gray-500 mb-2">Bukti #{{ $index + 1 }}</p>
                                    @endif

                                    @if($isPdf)
                                        <div class="border border-gray-200 rounded-lg p-6 text-center bg-gray-50">
                                            <i class="fas fa-file-pdf text-red-400 text-4xl mb-2"></i>
                                            <p class="text-sm text-gray-600 mb-3">Dokumen PDF</p>
                                            <div class="flex gap-2 justify-center">
                                                <a href="{{ Storage::url($filePath) }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs rounded-md transition-colors">
                                                    <i class="fas fa-external-link-alt"></i> Buka PDF
                                                </a>
                                                <a href="{{ Storage::url($filePath) }}" download
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-500 hover:bg-gray-600 text-white text-xs rounded-md transition-colors">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <img src="{{ Storage::url($filePath) }}" alt="Bukti Pembayaran {{ $index + 1 }}"
                                            class="w-full rounded-lg border border-gray-200 cursor-pointer hover:border-emerald-300 transition-colors"
                                            onclick="openPaymentModal('{{ Storage::url($filePath) }}')">
                                        <p class="text-xs text-gray-400 mt-1 text-center">Klik untuk perbesar</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-400 text-center space-y-0.5">
                            <p>Diupload oleh: {{ $approval->manager->nama ?? '-' }}</p>
                            <p>{{ $approval->manager_approved_at ? $approval->manager_approved_at->format('d M Y, H:i') : '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Payment Modal --}}
                <div id="paymentModal" class="fixed inset-0 bg-black/75 z-50 hidden items-center justify-center p-4"
                    onclick="if(event.target === this) closePaymentModal()">
                    <div class="relative max-w-4xl max-h-full">
                        <button onclick="closePaymentModal()"
                            class="absolute -top-10 right-0 text-white/80 hover:text-white w-8 h-8 flex items-center justify-center">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                        <img id="paymentModalImage" src="" alt="Bukti Pembayaran"
                            class="max-w-full max-h-[88vh] rounded-lg shadow-2xl">
                    </div>
                </div>

                <script>
                    function openPaymentModal(src) {
                        const modal = document.getElementById('paymentModal');
                        document.getElementById('paymentModalImage').src = src;
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
                    document.addEventListener('keydown', e => { if (e.key === 'Escape') closePaymentModal(); });
                </script>
            @endif

            {{-- Riwayat Approval --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-history text-gray-400"></i>
                        Riwayat Approval & Perubahan
                    </h2>
                </div>
                <div class="p-6">
                    @if($approvalHistory && count($approvalHistory) > 0)
                        <div class="space-y-3">
                            @foreach($approvalHistory as $history)
                                <div class="flex gap-4 p-4 rounded-lg border
                                    @if($history->action === 'approved') border-emerald-100 bg-emerald-50
                                    @elseif($history->action === 'rejected') border-red-100 bg-red-50
                                    @elseif($history->action === 'edited') border-amber-100 bg-amber-50
                                    @else border-blue-100 bg-blue-50
                                    @endif">
                                    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs font-bold
                                        @if($history->action === 'approved') bg-emerald-500
                                        @elseif($history->action === 'rejected') bg-red-500
                                        @elseif($history->action === 'edited') bg-amber-500
                                        @else bg-blue-500
                                        @endif">
                                        {{ strtoupper(substr($history->user->nama ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm font-semibold text-gray-900">{{ $history->user->nama ?? 'System' }}</span>
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                @if($history->action === 'approved') bg-emerald-100 text-emerald-700
                                                @elseif($history->action === 'rejected') bg-red-100 text-red-700
                                                @elseif($history->action === 'edited') bg-amber-100 text-amber-700
                                                @else bg-blue-100 text-blue-700
                                                @endif">
                                                {{ ucfirst($history->action) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-400 mb-2">
                                            @if($history->role === 'staff') Staff Accounting
                                            @elseif($history->role === 'manager_keuangan') Manager Keuangan
                                            @elseif($history->role === 'superadmin') Direktur
                                            @else {{ ucfirst(str_replace('_', ' ', $history->role)) }}
                                            @endif
                                        </p>

                                        @if($history->notes)
                                            <p class="text-sm text-gray-700 bg-white/60 rounded px-3 py-2 mb-2">{{ $history->notes }}</p>
                                        @endif

                                        @if($history->changes && is_array($history->changes))
                                            <div class="bg-white/60 rounded-lg p-3 text-xs">
                                                <p class="font-medium text-gray-600 mb-2">Detail Perubahan:</p>
                                                @if(isset($history->changes['field']))
                                                    <p class="text-gray-500 mb-2">Field: <span class="font-medium text-gray-700">{{ ucfirst($history->changes['field']) }}</span></p>
                                                @endif
                                                @if(isset($history->changes['old'], $history->changes['new']))
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div class="bg-red-50 border border-red-100 rounded p-2">
                                                            <p class="font-medium text-red-600 mb-1">Sebelum</p>
                                                            @if(is_array($history->changes['old']))
                                                                @foreach($history->changes['old'] as $key => $value)
                                                                    <p class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_numeric($value) ? number_format($value, 2, ',', '.') : ($value ?: '-') }}</p>
                                                                @endforeach
                                                            @else
                                                                <p class="text-gray-600">{{ $history->changes['old'] ?: '-' }}</p>
                                                            @endif
                                                        </div>
                                                        <div class="bg-emerald-50 border border-emerald-100 rounded p-2">
                                                            <p class="font-medium text-emerald-600 mb-1">Sesudah</p>
                                                            @if(is_array($history->changes['new']))
                                                                @foreach($history->changes['new'] as $key => $value)
                                                                    <p class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_numeric($value) ? number_format($value, 2, ',', '.') : ($value ?: '-') }}</p>
                                                                @endforeach
                                                            @else
                                                                <p class="text-gray-600">{{ $history->changes['new'] ?: '-' }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400 text-right flex-shrink-0">
                                        <p>{{ \Carbon\Carbon::parse($history->created_at)->format('d M Y H:i') }}</p>
                                        <p class="mt-0.5">{{ \Carbon\Carbon::parse($history->created_at)->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10 text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p class="text-sm">Belum ada riwayat approval</p>
                        </div>
                    @endif
                </div>
            </div>

        @else
            <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center">
                <i class="fas fa-exclamation-triangle text-red-400 text-3xl mb-3"></i>
                <p class="text-red-700 font-medium">Data approval tidak ditemukan</p>
            </div>
        @endif
    </div>
</div>