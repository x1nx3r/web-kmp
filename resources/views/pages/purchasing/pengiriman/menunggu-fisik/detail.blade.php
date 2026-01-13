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
                       value="{{ optional($pengiriman->order)->total_qty ? number_format($pengiriman->order->total_qty, 2, ',', '.') . ' KG' : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total PO</label>
                <input type="text" 
                       value="{{ optional($pengiriman->order)->total_amount ? 'Rp ' . number_format($pengiriman->order->total_amount, 2, ',', '.') : '-' }}" 
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
                       value="{{ $qtyToShow ? number_format($qtyToShow, 2, ',', '.') . ' kg' : '0 kg' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
                @if($hasRefraksiQty && $refraksiQtyAmount > 0)
                    <p class="mt-1 text-xs text-red-600">
                        <i class="fas fa-arrow-down mr-1"></i>
                        Refraksi: {{ number_format($refraksiQtyAmount, 2, ',', '.') }} kg
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
                       value="{{ $amountToShow ? 'Rp ' . number_format($amountToShow, 2, ',', '.') : 'Rp 0' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
                @if($hasRefraksiAmount && $pengiriman->approvalPembayaran->refraksi_amount > 0)
                    <p class="mt-1 text-xs text-red-600">
                        <i class="fas fa-arrow-down mr-1"></i>
                        Refraksi: Rp {{ number_format($pengiriman->approvalPembayaran->refraksi_amount, 2, ',', '.') }}
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
                                    {{ number_format($detail->qty_kirim ?? 0, 2, ',', '.') }} kg
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($detail->harga_satuan ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($detail->total_harga ?? 0, 2, ',', '.') }}
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
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Left Column: Refraksi Info --}}
                @if($pengiriman->approvalPembayaran)
                    @php
                        $approval = $pengiriman->approvalPembayaran;
                        $hasRefraksi = $approval->refraksi_type && $approval->refraksi_value;
                    @endphp
                    
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Informasi Refraksi</h4>
                        
                        @if($hasRefraksi)
                            {{-- Refraksi Type & Value --}}
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-medium text-orange-900">
                                        <i class="fas fa-percentage mr-1"></i>
                                        Tipe Refraksi:
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-600 text-white">
                                        {{ strtoupper($approval->refraksi_type) }}
                                    </span>
                                </div>
                                <div class="text-2xl font-bold text-orange-700">
                                    @if($approval->refraksi_type === 'percentage')
                                        {{ number_format($approval->refraksi_value, 2, ',', '.') }}%
                                    @else
                                        Rp {{ number_format($approval->refraksi_value, 2, ',', '.') }}
                                    @endif
                                </div>
                                <p class="text-xs text-orange-600 mt-1">Nilai Refraksi</p>
                            </div>

                            {{-- Qty Before & After --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <p class="text-xs text-gray-600 mb-1">Qty Sebelum</p>
                                    <p class="text-lg font-bold text-gray-900">
                                        {{ number_format($approval->qty_before_refraksi ?? 0, 2, ',', '.') }} <span class="text-sm font-normal text-gray-500">kg</span>
                                    </p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                    <p class="text-xs text-green-600 mb-1">Qty Setelah</p>
                                    <p class="text-lg font-bold text-green-700">
                                        {{ number_format($approval->qty_after_refraksi ?? 0, 2, ',', '.') }} <span class="text-sm font-normal text-green-500">kg</span>
                                    </p>
                                </div>
                            </div>

                            {{-- Amount Before & After --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <p class="text-xs text-gray-600 mb-1">Total Sebelum</p>
                                    <p class="text-sm font-bold text-gray-900">
                                        Rp {{ number_format($approval->amount_before_refraksi ?? 0, 2, ',', '.') }}
                                    </p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                    <p class="text-xs text-green-600 mb-1">Total Setelah</p>
                                    <p class="text-sm font-bold text-green-700">
                                        Rp {{ number_format($approval->amount_after_refraksi ?? 0, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Refraksi Amount --}}
                            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                <p class="text-xs text-red-600 mb-1">
                                    <i class="fas fa-minus-circle mr-1"></i>
                                    Potongan Refraksi
                                </p>
                                <p class="text-xl font-bold text-red-700">
                                    Rp {{ number_format($approval->refraksi_amount ?? 0, 2, ',', '.') }}
                                </p>
                            </div>

                            {{-- Catatan Refraksi --}}
                            @if($pengiriman->catatan_refraksi)
                                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                    <p class="text-xs font-semibold text-yellow-800 mb-2">
                                        <i class="fas fa-sticky-note mr-1"></i>
                                        Catatan Refraksi:
                                    </p>
                                    <p class="text-sm text-yellow-900">{{ $pengiriman->catatan_refraksi }}</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8 text-gray-400">
                                <i class="fas fa-info-circle text-3xl mb-2"></i>
                                <p class="text-sm">Tidak ada refraksi</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Right Column: Price Info --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Informasi Harga</h4>
                    
                    {{-- Harga Beli (from approval pembayaran) --}}
                    @if($pengiriman->approvalPembayaran)
                        @php
                            $approval = $pengiriman->approvalPembayaran;
                            $totalHargaBeli = $approval->amount_after_refraksi ?? $approval->amount_before_refraksi ?? $pengiriman->total_harga_kirim ?? 0;
                            $qtyAfterRefraksi = $approval->qty_after_refraksi ?? $approval->qty_before_refraksi ?? $pengiriman->total_qty_kirim ?? 1;
                            $hargaBeliPerKg = $qtyAfterRefraksi > 0 ? $totalHargaBeli / $qtyAfterRefraksi : 0;
                        @endphp
                        
                        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border-2 border-red-300">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-red-900">
                                    <i class="fas fa-shopping-cart mr-1"></i>
                                    Harga Beli
                                </p>
                                <span class="px-2 py-1 bg-red-600 text-white text-xs rounded-full font-semibold">PEMBELIAN</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs text-red-700">Per Kg:</span>
                                    <span class="text-lg font-bold text-red-900">
                                        Rp {{ number_format($hargaBeliPerKg, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-baseline pt-2 border-t border-red-200">
                                    <span class="text-xs text-red-700">Total:</span>
                                    <span class="text-xl font-bold text-red-900">
                                        Rp {{ number_format($totalHargaBeli, 2, ',', '.') }}
                                    </span>
                                </div>
                                <p class="text-xs text-red-600 mt-1">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Untuk {{ number_format($qtyAfterRefraksi, 2, ',', '.') }} kg
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Harga Jual (from invoice penagihan or PO) --}}
                    @php
                        $hargaJualPerKg = 0;
                        $totalHargaJual = 0;
                        $qtyJual = 0;
                        $source = '';
                        
                        // Priority 1: Check invoice penagihan
                        if ($pengiriman->invoicePenagihan) {
                            $invoice = $pengiriman->invoicePenagihan;
                            $totalHargaJual = $invoice->amount_after_refraksi ?? $invoice->subtotal ?? 0;
                            $qtyJual = $invoice->qty_after_refraksi ?? $invoice->qty_before_refraksi ?? $pengiriman->total_qty_kirim ?? 1;
                            $hargaJualPerKg = $qtyJual > 0 ? $totalHargaJual / $qtyJual : 0;
                            $source = 'Invoice Penagihan';
                        }
                        // Priority 2: Fallback to PO (OrderDetail)
                        elseif ($pengiriman->pengirimanDetails && $pengiriman->pengirimanDetails->count() > 0) {
                            foreach ($pengiriman->pengirimanDetails as $detail) {
                                if ($detail->orderDetail && $detail->orderDetail->harga_jual > 0) {
                                    $hargaJualPerKg += $detail->orderDetail->harga_jual;
                                    $totalHargaJual += ($detail->qty_kirim * $detail->orderDetail->harga_jual);
                                    $qtyJual += $detail->qty_kirim;
                                }
                            }
                            // Calculate average if multiple items
                            if ($pengiriman->pengirimanDetails->count() > 1 && $qtyJual > 0) {
                                $hargaJualPerKg = $totalHargaJual / $qtyJual;
                            }
                            $source = 'Purchase Order';
                        }
                    @endphp
                    
                    @if($hargaJualPerKg > 0 || $totalHargaJual > 0)
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border-2 border-green-300">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-green-900">
                                    <i class="fas fa-tag mr-1"></i>
                                    Harga Jual
                                </p>
                                <span class="px-2 py-1 bg-green-600 text-white text-xs rounded-full font-semibold">PENJUALAN</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs text-green-700">Per Kg:</span>
                                    <span class="text-lg font-bold text-green-900">
                                        Rp {{ number_format($hargaJualPerKg, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-baseline pt-2 border-t border-green-200">
                                    <span class="text-xs text-green-700">Total:</span>
                                    <span class="text-xl font-bold text-green-900">
                                        Rp {{ number_format($totalHargaJual, 2, ',', '.') }}
                                    </span>
                                </div>
                                <p class="text-xs text-green-600 mt-1">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Untuk {{ number_format($qtyJual, 2, ',', '.') }} kg
                                </p>
                                <p class="text-xs text-green-500 mt-1">
                                    <i class="fas fa-source mr-1"></i>
                                    Sumber: {{ $source }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="text-center py-4 text-gray-400">
                                <i class="fas fa-info-circle text-2xl mb-2"></i>
                                <p class="text-sm">Harga jual belum tersedia</p>
                                <p class="text-xs mt-1">Invoice penagihan belum dibuat</p>
                            </div>
                        </div>
                    @endif

                    {{-- Margin Calculation (if both prices available) --}}
                    @if($pengiriman->approvalPembayaran && ($hargaJualPerKg > 0))
                        @php
                            $margin = $totalHargaJual - $totalHargaBeli;
                            // Profit Margin: (margin / harga jual) * 100
                            $marginPercentage = $totalHargaJual > 0 ? ($margin / $totalHargaJual) * 100 : 0;
                            $isPositive = $margin >= 0;
                        @endphp
                        
                        <div class="bg-gradient-to-br from-{{ $isPositive ? 'blue' : 'red' }}-50 to-{{ $isPositive ? 'blue' : 'red' }}-100 rounded-lg p-4 border-2 border-{{ $isPositive ? 'blue' : 'red' }}-300">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-{{ $isPositive ? 'blue' : 'red' }}-900">
                                    <i class="fas fa-chart-line mr-1"></i>
                                    Margin Keuntungan
                                </p>
                                <span class="px-2 py-1 bg-{{ $isPositive ? 'blue' : 'red' }}-600 text-white text-xs rounded-full font-semibold">
                                    {{ $isPositive ? 'PROFIT' : 'LOSS' }}
                                </span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs text-{{ $isPositive ? 'blue' : 'red' }}-700">Nominal:</span>
                                    <span class="text-xl font-bold text-{{ $isPositive ? 'blue' : 'red' }}-900">
                                        {{ $isPositive ? '+' : '' }}Rp {{ number_format($margin, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-baseline pt-2 border-t border-{{ $isPositive ? 'blue' : 'red' }}-200">
                                    <span class="text-xs text-{{ $isPositive ? 'blue' : 'red' }}-700">Persentase:</span>
                                    <span class="text-xl font-bold text-{{ $isPositive ? 'blue' : 'red' }}-900">
                                        {{ $isPositive ? '+' : '' }}{{ number_format($marginPercentage, 2, ',', '.') }}%
                                    </span>
                                </div>
                            </div>
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
                        Rp {{ number_format($pengiriman->approvalPembayaran->refraksi_amount, 2, ',', '.') }}
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
