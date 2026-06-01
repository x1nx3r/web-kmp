{{-- Modal Header - Sticky --}}
<div class="sticky top-0 z-10 flex items-center justify-between p-6 border-b border-gray-200 bg-purple-600 rounded-t-xl">
    <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center shadow-sm">
            <i class="fas fa-box-open text-purple-600 text-xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white">Detail Verifikasi Fisik</h3>
            <p class="text-sm text-purple-100 opacity-90">{{ $pengiriman->no_pengiriman ?? 'No Pengiriman' }}</p>
        </div>
    </div>
    <button type="button" onclick="closeVerifikasiFisikModal()" 
            class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
        <i class="fas fa-times text-xl"></i>
    </button>
</div>

{{-- Modal Content - Scrollable --}}
<div class="overflow-y-auto max-h-[calc(90vh-160px)] p-6 space-y-6">

    {{-- Card 1: Data PO & PIC Purchasing --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-file-alt text-purple-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Data PO & PIC Procurement</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. PO</label>
                <input type="text" 
                       value="{{ optional($pengiriman->order)->po_number ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PIC Procurement</label>
                <input type="text" 
                       value="{{ optional($pengiriman->purchasing)->nama ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kuantitas PO</label>
                <input type="text" 
                       value="{{ optional($pengiriman->order)->total_qty ? number_format($pengiriman->order->total_qty, 3, ',', '.') . ' KG' : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total PO</label>
                <input type="text" 
                       value="{{ optional($pengiriman->order)->total_amount ? 'Rp ' . number_format($pengiriman->order->total_amount, 3, ',', '.') : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
        </div>
    </div>

    {{-- Card 2: Data Klien --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-building text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Data Klien</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Klien</label>
                <input type="text" 
                       value="{{ optional(optional($pengiriman->order)->klien)->nama ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                <input type="text" 
                       value="{{ optional(optional($pengiriman->order)->klien)->cabang ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
        </div>
    </div>

    {{-- Card 3: Data Pengiriman --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-truck text-purple-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Data Pengiriman</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. Pengiriman</label>
                <input type="text" 
                       value="{{ $pengiriman->no_pengiriman ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kirim</label>
                <input type="text" 
                       value="{{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d M Y') : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hari Kirim</label>
                <input type="text" 
                       value="{{ $pengiriman->hari_kirim ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-600 text-white">
                        <i class="fas fa-box-open mr-1"></i>
                        Menunggu Fisik
                    </span>
                </div>
            </div>
            
            {{-- Qty dengan Refraksi --}}
            @php
                $qtyToShow = $pengiriman->total_qty_kirim;
                $hasRefraksiQty = false;
                $refraksiQtyAmount = 0;
                
                if($pengiriman->approvalPembayaran && $pengiriman->approvalPembayaran->qty_after_refraksi > 0) {
                    $qtyToShow = $pengiriman->approvalPembayaran->qty_after_refraksi;
                    $hasRefraksiQty = true;
                    $refraksiQtyAmount = $pengiriman->approvalPembayaran->qty_before_refraksi - $pengiriman->approvalPembayaran->qty_after_refraksi;
                }
            @endphp
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Qty Dikirim</label>
                <input type="text" 
                       value="{{ $qtyToShow ? number_format($qtyToShow, 3, ',', '.') . ' kg' : '0 kg' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
                @if($hasRefraksiQty && $refraksiQtyAmount > 0)
                    <p class="mt-1 text-xs text-red-600">
                        <i class="fas fa-arrow-down mr-1"></i>
                        Refraksi: {{ number_format($refraksiQtyAmount, 3, ',', '.') }} kg
                    </p>
                @endif
            </div>
            
            {{-- Amount dengan Refraksi --}}
            @php
                $amountToShow = $pengiriman->total_harga_kirim;
                $hasRefraksiAmount = false;
                
                if($pengiriman->approvalPembayaran && $pengiriman->approvalPembayaran->amount_after_refraksi > 0) {
                    $amountToShow = $pengiriman->approvalPembayaran->amount_after_refraksi;
                    $hasRefraksiAmount = true;
                }
            @endphp
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga Pengiriman</label>
                <input type="text" 
                       value="{{ $amountToShow ? 'Rp ' . number_format($amountToShow, 3, ',', '.') : 'Rp 0' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
                @if($hasRefraksiAmount && $pengiriman->approvalPembayaran->refraksi_amount > 0)
                    <p class="mt-1 text-xs text-red-600">
                        <i class="fas fa-arrow-down mr-1"></i>
                        Refraksi: Rp {{ number_format($pengiriman->approvalPembayaran->refraksi_amount, 3, ',', '.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Card 4: Detail Bahan Baku --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-boxes text-green-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Detail Bahan Baku</h3>
        </div>
        
        @if($pengiriman->pengirimanDetails && $pengiriman->pengirimanDetails->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Dikirim</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pengiriman->pengirimanDetails as $detail)
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        @if(optional($detail->bahanBakuSupplier)->nama)
                                            {{ $detail->bahanBakuSupplier->nama }}
                                        @elseif(optional(optional($detail->orderDetail)->bahanBakuSupplier)->nama)
                                            {{ $detail->orderDetail->bahanBakuSupplier->nama }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if(optional(optional($detail->bahanBakuSupplier)->supplier)->nama)
                                            {{ $detail->bahanBakuSupplier->supplier->nama }}
                                        @elseif(optional(optional(optional($detail->orderDetail)->bahanBakuSupplier)->supplier)->nama)
                                            {{ $detail->orderDetail->bahanBakuSupplier->supplier->nama }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($detail->qty_kirim ?? 0, 3, ',', '.') }} kg
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($detail->harga_satuan ?? 0, 3, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($detail->total_harga ?? 0, 3, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-box-open text-gray-300 text-3xl mb-2"></i>
                <p>Tidak ada detail bahan baku</p>
            </div>
        @endif
    </div>

    {{-- Card 4.5: Informasi Refraksi & Harga --}}
    @if($pengiriman->approvalPembayaran || $pengiriman->invoicePenagihan)
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-calculator text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Informasi Refraksi & Harga</h3>
        </div>

        @php
            $approval = $pengiriman->approvalPembayaran;
            $invoice  = $pengiriman->invoicePenagihan;

            // === TOTAL BELI ===
            $totalBeliForMargin = 0;
            if ($approval) {
                if ($approval->subtotal > 0) {
                    $totalBeliForMargin = $approval->subtotal;
                } elseif ($approval->amount_after_refraksi > 0) {
                    $totalBeliForMargin = $approval->amount_after_refraksi;
                } else {
                    $qtyFallback = $approval->qty_after_refraksi > 0 ? $approval->qty_after_refraksi
                                : ($approval->qty_before_refraksi > 0 ? $approval->qty_before_refraksi
                                : $pengiriman->total_qty_kirim);
                    $hargaFallback = $pengiriman->total_qty_kirim > 0
                        ? $pengiriman->total_harga_kirim / $pengiriman->total_qty_kirim : 0;
                    $totalBeliForMargin = $qtyFallback * $hargaFallback;
                }
            }
            $qtyBeli = $approval
                ? ($approval->qty_after_refraksi > 0 ? $approval->qty_after_refraksi
                : ($approval->qty_before_refraksi > 0 ? $approval->qty_before_refraksi
                : $pengiriman->total_qty_kirim))
                : $pengiriman->total_qty_kirim;
            $hargaBeliPerKg = $qtyBeli > 0 ? $totalBeliForMargin / $qtyBeli : 0;

            // === TOTAL JUAL ===
            $totalJualForMargin = 0;
            $qtyJual = 0;
            $hargaJualPerKg = 0;
            $sourceJual = '';

            if ($invoice) {
                if ($invoice->subtotal > 0) {
                    $totalJualForMargin = $invoice->subtotal;
                } elseif ($invoice->amount_after_refraksi > 0) {
                    $totalJualForMargin = $invoice->amount_after_refraksi;
                }
                $qtyJual = $invoice->qty_after_refraksi > 0 ? $invoice->qty_after_refraksi
                        : ($invoice->qty_before_refraksi > 0 ? $invoice->qty_before_refraksi
                        : $pengiriman->total_qty_kirim);
                $hargaJualPerKg = $qtyJual > 0 ? $totalJualForMargin / $qtyJual : 0;
                $sourceJual = 'Invoice Penagihan';
            } elseif ($pengiriman->pengirimanDetails && $pengiriman->pengirimanDetails->count() > 0) {
                foreach ($pengiriman->pengirimanDetails as $d) {
                    if ($d->orderDetail && $d->orderDetail->harga_jual > 0) {
                        $totalJualForMargin += $d->qty_kirim * $d->orderDetail->harga_jual;
                        $qtyJual += $d->qty_kirim;
                    }
                }
                $hargaJualPerKg = $qtyJual > 0 ? $totalJualForMargin / $qtyJual : 0;
                $sourceJual = 'Purchase Order';
            }
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- KOLOM KIRI: Sisi BELI --}}
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide border-b pb-2">Sisi Beli (Approval Pembayaran)</h4>

                @if($approval)
                    @if($approval->refraksi_type && $approval->refraksi_value)
                        <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-orange-800">Tipe Refraksi</span>
                                <span class="px-2 py-1 bg-orange-600 text-white text-xs rounded-full">{{ strtoupper($approval->refraksi_type) }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm mt-2">
                                <div>
                                    <p class="text-xs text-gray-500">Qty Sebelum</p>
                                    <p class="font-semibold">{{ number_format($approval->qty_before_refraksi ?? 0, 3, ',', '.') }} kg</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Qty Setelah</p>
                                    <p class="font-semibold text-green-700">{{ number_format($approval->qty_after_refraksi ?? 0, 3, ',', '.') }} kg</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Amount Sebelum</p>
                                    <p class="font-semibold">Rp {{ number_format($approval->amount_before_refraksi ?? 0, 3, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Amount Setelah</p>
                                    <p class="font-semibold text-green-700">Rp {{ number_format($approval->amount_after_refraksi ?? 0, 3, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-orange-200">
                                <p class="text-xs text-red-600">Potongan Refraksi: <span class="font-bold">Rp {{ number_format($approval->refraksi_amount ?? 0, 3, ',', '.') }}</span></p>
                            </div>
                        </div>
                    @endif

                    {{-- Potongan Tambahan Beli --}}
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <p class="text-sm font-semibold text-red-800 mb-2">
                            <i class="fas fa-minus-circle mr-1"></i>
                            Potongan Tambahan (Beli)
                        </p>
                        @if($approval->additional_expenses_total > 0)
                            <p class="text-lg font-bold text-red-700 mb-2">Rp {{ number_format($approval->additional_expenses_total, 3, ',', '.') }}</p>
                            @if($approval->expenses && $approval->expenses->count() > 0)
                                <div class="space-y-1">
                                    @foreach($approval->expenses as $exp)
                                        <div class="flex justify-between text-xs text-red-700">
                                            <span>{{ ucfirst($exp->type) }}</span>
                                            <span>Rp {{ number_format($exp->amount, 3, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <p class="text-sm text-gray-500 italic">Tidak ada potongan tambahan</p>
                        @endif
                    </div>

                    {{-- Total Beli --}}
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border-2 border-red-300">
                        <div class="flex justify-between items-center mb-1">
                            <p class="text-sm font-semibold text-red-900"><i class="fas fa-shopping-cart mr-1"></i>Total Beli</p>
                            <span class="px-2 py-1 bg-red-600 text-white text-xs rounded-full">PEMBELIAN</span>
                        </div>
                        <p class="text-xl font-bold text-red-900">Rp {{ number_format($totalBeliForMargin, 3, ',', '.') }}</p>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-info-circle text-3xl mb-2"></i>
                        <p class="text-sm">Approval pembayaran belum dibuat</p>
                    </div>
                @endif
            </div>

            {{-- KOLOM KANAN: Sisi JUAL + Margin --}}
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide border-b pb-2">Sisi Jual (Invoice Penagihan)</h4>

                @if($invoice)
                    @if($invoice->refraksi_type && $invoice->refraksi_value)
                        <div class="bg-orange-50 rounded-lg p-3 border border-orange-200 text-sm">
                            <p class="font-medium text-orange-800 mb-1">Refraksi Invoice: {{ strtoupper($invoice->refraksi_type) }}</p>
                            <div class="grid grid-cols-2 gap-1 text-xs">
                                <span class="text-gray-600">Qty Setelah:</span>
                                <span class="font-semibold">{{ number_format($invoice->qty_after_refraksi ?? 0, 3, ',', '.') }} kg</span>
                                <span class="text-gray-600">Amount Setelah:</span>
                                <span class="font-semibold">Rp {{ number_format($invoice->amount_after_refraksi ?? 0, 3, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Potongan Tambahan Jual --}}
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <p class="text-sm font-semibold text-green-800 mb-2">
                            <i class="fas fa-minus-circle mr-1"></i>
                            Potongan Tambahan (Jual)
                        </p>
                        @if($invoice->additional_expenses_total > 0)
                            <p class="text-lg font-bold text-green-700 mb-2">Rp {{ number_format($invoice->additional_expenses_total, 3, ',', '.') }}</p>
                            @if($invoice->expenses && $invoice->expenses->count() > 0)
                                <div class="space-y-1">
                                    @foreach($invoice->expenses as $exp)
                                        <div class="flex justify-between text-xs text-green-700">
                                            <span>{{ ucfirst($exp->type) }}</span>
                                            <span>Rp {{ number_format($exp->amount, 3, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <p class="text-sm text-gray-500 italic">Tidak ada potongan tambahan</p>
                        @endif
                    </div>
                @endif

                {{-- Total Jual --}}
                @if($hargaJualPerKg > 0 || $totalJualForMargin > 0)
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border-2 border-green-300">
                        <div class="flex justify-between items-center mb-1">
                            <p class="text-sm font-semibold text-green-900"><i class="fas fa-tag mr-1"></i>Total Jual</p>
                            <span class="px-2 py-1 bg-green-600 text-white text-xs rounded-full">PENJUALAN</span>
                        </div>
                        <p class="text-xl font-bold text-green-900">Rp {{ number_format($totalJualForMargin, 3, ',', '.') }}</p>
                        <p class="text-xs text-green-600 mt-1">Rp {{ number_format($hargaJualPerKg, 3, ',', '.') }}/kg · {{ number_format($qtyJual, 3, ',', '.') }} kg</p>
                        <p class="text-xs text-green-500 mt-1">Sumber: {{ $sourceJual }}</p>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center text-gray-400">
                        <i class="fas fa-info-circle text-2xl mb-1"></i>
                        <p class="text-sm">Harga jual belum tersedia</p>
                    </div>
                @endif

                {{-- Margin --}}
                @if($approval && $totalJualForMargin > 0 && $totalBeliForMargin > 0)
                    @php
                        $margin = $totalJualForMargin - $totalBeliForMargin;
                        $marginPct = $totalJualForMargin > 0 ? ($margin / $totalJualForMargin) * 100 : 0;
                        $isProfit = $margin >= 0;
                        $colorMargin = $isProfit ? 'blue' : 'red';
                    @endphp
                    <div class="bg-{{ $colorMargin }}-50 rounded-lg p-4 border-2 border-{{ $colorMargin }}-300">
                        <div class="flex justify-between items-center mb-2">
                            <p class="text-sm font-semibold text-{{ $colorMargin }}-900"><i class="fas fa-chart-line mr-1"></i>Margin</p>
                            <span class="px-2 py-1 bg-{{ $colorMargin }}-600 text-white text-xs rounded-full">{{ $isProfit ? 'PROFIT' : 'LOSS' }}</span>
                        </div>
                        <p class="text-xl font-bold text-{{ $colorMargin }}-900">{{ $isProfit ? '+' : '' }}Rp {{ number_format($margin, 3, ',', '.') }}</p>
                        <p class="text-lg font-semibold text-{{ $colorMargin }}-700 mt-1">{{ $isProfit ? '+' : '' }}{{ number_format($marginPct, 3, ',', '.') }}%</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Card 5: Catatan Pengiriman --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-sticky-note text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Catatan Pengiriman</h3>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
            <textarea 
                id="catatanPengirimanFisik" 
                rows="4" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-none"
                placeholder="Tambahkan catatan pengiriman..."
            >{{ $pengiriman->catatan }}</textarea>
            <div class="mt-2 flex justify-end">
                <button 
                    type="button"
                    onclick="saveCatatanPengirimanFisik({{ $pengiriman->id }})"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm font-medium">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Catatan
                </button>
            </div>
        </div>
    </div>

    {{-- Card 6: Catatan Refraksi --}}
    @if($pengiriman->catatan_refraksi)
        <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border-l-4 border-orange-500 rounded-lg p-6 shadow-sm mb-4">
            <div class="flex items-start mb-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-orange-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-orange-900 mb-1">Catatan Refraksi</h3>
                    <p class="text-xs text-orange-600">Informasi penting terkait pengurangan kuantitas/harga</p>
                </div>
            </div>
            <div class="bg-white bg-opacity-60 rounded-lg p-4 border border-orange-200">
                <p class="text-gray-800 whitespace-pre-line">{{ $pengiriman->catatan_refraksi }}</p>
            </div>
            
            {{-- Informasi Refraksi dari Approval Pembayaran --}}
            @if($pengiriman->approvalPembayaran && $pengiriman->approvalPembayaran->refraksi_amount)
                <div class="mt-3 flex items-center justify-between text-sm">
                    <span class="text-orange-700 font-medium">
                        <i class="fas fa-info-circle mr-1"></i>
                        Potongan Refraksi:
                    </span>
                    <span class="text-orange-900 font-bold">
                        Rp {{ number_format($pengiriman->approvalPembayaran->refraksi_amount, 3, ',', '.') }}
                    </span>
                </div>
            @endif
        </div>
    @endif
</div>

{{-- Modal Footer - Sticky --}}
<div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-xl">
    @php
        $currentUser = Auth::user();
        $canVerifyFisik = in_array($currentUser->role, ['direktur', 'manager_purchasing']);
    @endphp
    
    @if($canVerifyFisik)
        {{-- Checkbox konfirmasi untuk Direktur dan Manager --}}
        <div class="mb-4">
            <label class="flex items-start cursor-pointer group">
                <input type="checkbox" id="konfirmasiVerifikasiFisik" class="mt-1 h-5 w-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500">
                <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">
                    <span class="font-medium">Saya konfirmasi bahwa:</span>
                    <ul class="list-disc ml-5 mt-1 space-y-1 text-gray-600">
                        <li>Barang telah diterima secara fisik</li>
                        <li>Kuantitas sesuai dengan dokumen pengiriman</li>
                        <li>Dokumen Pengiriman Sudah Diterima</li>
                    </ul>
                </span>
            </label>
        </div>
        
        <input type="hidden" id="pengirimanIdFisik" value="{{ $pengiriman->id }}">
    @endif
    
    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-4 space-y-2 space-y-reverse sm:space-y-0">
        <button type="button" onclick="closeVerifikasiFisikModal()" 
                class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 font-medium">
            <i class="fas fa-times mr-2"></i>
            Tutup
        </button>
        
        @if($canVerifyFisik)
            <button type="button" onclick="submitVerifikasiFisik()" 
                    class="w-full sm:w-auto px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 font-medium">
                <i class="fas fa-box-check mr-2"></i>
                Verifikasi Fisik
            </button>
        @else
            <div class="w-full sm:w-auto text-center bg-gray-50 border border-gray-200 rounded-lg px-6 py-3">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-2"></i>
                    Hanya Direktur dan Manager Procurement yang dapat memverifikasi fisik
                </p>
            </div>
        @endif
    </div>
</div>