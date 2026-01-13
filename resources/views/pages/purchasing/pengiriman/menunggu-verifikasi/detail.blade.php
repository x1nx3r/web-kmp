{{-- Modal Header - Sticky --}}
<div class="sticky top-0 z-10 flex items-center justify-between p-6 border-b border-gray-200 bg-yellow-600 rounded-t-xl">
    <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center shadow-sm">
            <i class="fas fa-check-circle text-yellow-600 text-xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white">Detail Verifikasi</h3>
            <p class="text-sm text-yellow-100 opacity-90">{{ $pengiriman->no_pengiriman ?? 'No Pengiriman' }}</p>
        </div>
    </div>
    <button type="button" onclick="closeAksiVerifikasiModal()" 
            class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200">
        <i class="fas fa-times text-xl"></i>
    </button>
</div>

{{-- Modal Content - Scrollable --}}
<div class="overflow-y-auto max-h-[calc(90vh-160px)] p-6 space-y-6">

    {{-- Card 1: Data PO & PIC Purchasing --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-file-alt text-yellow-600"></i>
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
                       value="{{ optional($pengiriman->order)->total_qty ? number_format($pengiriman->order->total_qty, 2, ',', '.') . ' KG' : 'Data Order tidak ditemukan (ID: ' . ($pengiriman->purchase_order_id ?? 'null') . ')' }}" 
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

    {{-- Card 2: Data Forecasting --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-chart-line text-green-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Data Forecasting</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->no_forecast ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->tanggal_forecast ? $pengiriman->forecast->tanggal_forecast->format('d M Y') : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hari Kirim Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->hari_kirim_forecast ?? '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Qty Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->total_qty_forecast ? number_format($pengiriman->forecast->total_qty_forecast, 2, ',', '.') . ' kg' : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga Forecast</label>
                <input type="text" 
                       value="{{ optional($pengiriman->forecast)->total_harga_forecast ? 'Rp ' . number_format($pengiriman->forecast->total_harga_forecast, 2, ',', '.') : '-' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
        </div>
    </div>

    {{-- Card 3: Data Klien --}}
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

    {{-- Card 4: Data Pengiriman --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-truck text-yellow-600"></i>
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
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-600 text-white">
                        <i class="fas fa-clock mr-1"></i>
                        Menunggu Verifikasi
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Qty Dikirim</label>
                <input type="text" 
                       value="{{ $pengiriman->total_qty_kirim ? number_format($pengiriman->total_qty_kirim, 2, ',', '.') . ' kg' : '0 kg' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga Pengiriman</label>
                <input type="text" 
                       value="{{ $pengiriman->total_harga_kirim ? 'Rp ' . number_format($pengiriman->total_harga_kirim, 2, ',', '.') : 'Rp 0' }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed" 
                       readonly>
            </div>
        </div>
    </div>

    {{-- Card 5: Detail Bahan Baku --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-boxes text-purple-600"></i>
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

    {{-- Card 5.5: Informasi Refraksi & Harga --}}
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

    {{-- Card 5.6: Catatan Refraksi (if exists) --}}
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

    {{-- Card 6: Bukti Foto Bongkar --}}
    @if($pengiriman->bukti_foto_bongkar_raw)
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-camera text-indigo-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Bukti Foto Bongkar</h3>
                    @if($pengiriman->bukti_foto_bongkar_uploaded_at)
                        <p class="text-xs text-gray-500 mt-0.5">
                            <i class="fas fa-clock mr-1"></i>
                            Upload: {{ $pengiriman->bukti_foto_bongkar_uploaded_at->format('d M Y, H:i') }} WIB
                            <span class="text-gray-400">({{ $pengiriman->bukti_foto_bongkar_uploaded_at->diffForHumans() }})</span>
                        </p>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $photos = $pengiriman->bukti_foto_bongkar_array ?? [];
                    // Debug
                    // dd([
                    //     'raw' => $pengiriman->bukti_foto_bongkar_raw,
                    //     'array' => $photos,
                    //     'count' => count($photos)
                    // ]);
                @endphp
                @if(is_array($photos) && count($photos) > 0)
                    @foreach($photos as $index => $photo)
                        @if($photo)
                            <div class="relative group">
                                @php
                                    $photoUrl = asset('storage/pengiriman/bukti/' . $photo);
                                    $extension = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
                                    $isPdf = $extension === 'pdf';
                                @endphp
                                
                                @if($isPdf)
                                    {{-- PDF Preview --}}
                                    <div class="w-full h-48 bg-gradient-to-br from-red-50 to-red-100 rounded-lg border-2 border-red-200 cursor-pointer hover:shadow-lg transition-all flex flex-col items-center justify-center"
                                         onclick="window.open('{{ $photoUrl }}', '_blank')">
                                        <i class="fas fa-file-pdf text-red-500 text-5xl mb-3"></i>
                                        <p class="text-sm font-medium text-red-700">Bukti Bongkar PDF</p>
                                        <p class="text-xs text-red-600 mt-1">Klik untuk melihat</p>
                                    </div>
                                    
                                    {{-- Overlay dengan buttons untuk PDF --}}
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <div class="flex space-x-2">
                                            <button onclick="window.open('{{ $photoUrl }}', '_blank')" 
                                                    class="bg-white text-blue-600 p-2 rounded-full shadow-lg hover:bg-blue-50 transition-all"
                                                    title="Lihat PDF">
                                                <i class="fas fa-eye text-sm"></i>
                                            </button>
                                            <a href="{{ $photoUrl }}" download="{{ $photo }}"
                                               class="bg-white text-green-600 p-2 rounded-full shadow-lg hover:bg-green-50 transition-all inline-flex items-center"
                                               title="Download PDF">
                                                <i class="fas fa-download text-sm"></i>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    {{-- Image Preview --}}
                                    <img src="{{ $photoUrl }}" 
                                         alt="Bukti Foto Bongkar {{ $index + 1 }}" 
                                         class="w-full h-48 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                         onclick="window.open('{{ $photoUrl }}', '_blank')"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjIwMCIgY3k9IjEyMCIgcj0iMzAiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0iTTE1MCAxNjBIMjUwTDIzMCAxOTBIMjUwTDIzMCAyMjBIMTUwVjE2MFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHRleHQgeD0iMjAwIiB5PSIyNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzZCNzI4MCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+R2FtYmFyIHRpZGFrIGRpdGVtdWthbjwvdGV4dD4KPC9zdmc+'; this.classList.add('opacity-50');">
                                    
                                    {{-- Overlay dengan buttons untuk Image --}}
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
                                @endif
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-span-full text-center py-8 text-gray-500">
                        <i class="fas fa-image text-gray-300 text-3xl mb-2"></i>
                        <p>Tidak ada foto bukti bongkar</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Card 6.5: Foto Tanda Terima --}}
    @if($pengiriman->foto_tanda_terima)
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-receipt text-purple-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Foto Tanda Terima</h3>
                    @if($pengiriman->foto_tanda_terima_uploaded_at)
                        <p class="text-xs text-gray-500 mt-0.5">
                            <i class="fas fa-clock mr-1"></i>
                            Upload: {{ $pengiriman->foto_tanda_terima_uploaded_at->format('d M Y, H:i') }} WIB
                            <span class="text-gray-400">({{ $pengiriman->foto_tanda_terima_uploaded_at->diffForHumans() }})</span>
                        </p>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $photoUrl = asset('storage/pengiriman/tanda-terima/' . $pengiriman->foto_tanda_terima);
                    $extension = strtolower(pathinfo($pengiriman->foto_tanda_terima, PATHINFO_EXTENSION));
                    $isPdf = $extension === 'pdf';
                @endphp
                
                <div class="relative group">
                    @if($isPdf)
                        {{-- PDF Preview --}}
                        <div class="w-full h-48 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border-2 border-purple-200 cursor-pointer hover:shadow-lg transition-all flex flex-col items-center justify-center"
                             onclick="window.open('{{ $photoUrl }}', '_blank')">
                            <i class="fas fa-file-pdf text-purple-500 text-5xl mb-3"></i>
                            <p class="text-sm font-medium text-purple-700">Tanda Terima PDF</p>
                            <p class="text-xs text-purple-600 mt-1">Klik untuk melihat</p>
                        </div>
                        
                        {{-- Overlay dengan buttons untuk PDF --}}
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <div class="flex space-x-2">
                                <button onclick="window.open('{{ $photoUrl }}', '_blank')" 
                                        class="bg-white text-purple-600 p-2 rounded-full shadow-lg hover:bg-purple-50 transition-all"
                                        title="Lihat PDF">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <a href="{{ $photoUrl }}" download="tanda_terima_{{ $pengiriman->no_pengiriman }}.pdf"
                                   class="bg-white text-green-600 p-2 rounded-full shadow-lg hover:bg-green-50 transition-all inline-flex items-center"
                                   title="Download PDF">
                                    <i class="fas fa-download text-sm"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- Image Preview --}}
                        <img src="{{ $photoUrl }}" 
                             alt="Foto Tanda Terima" 
                             class="w-full h-48 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                             onclick="window.open('{{ $photoUrl }}', '_blank')"
                             onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjIwMCIgY3k9IjEyMCIgcj0iMzAiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0iTTE1MCAxNjBIMjUwTDIzMCAxOTBIMjUwTDIzMCAyMjBIMTUwVjE2MFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHRleHQgeD0iMjAwIiB5PSIyNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzZCNzI4MCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+R2FtYmFyIHRpZGFrIGRpdGVtdWthbjwvdGV4dD4KPC9zdmc+'; this.classList.add('opacity-50');">
                        
                        {{-- Overlay dengan buttons untuk Image --}}
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <div class="flex space-x-2">
                                <button onclick="window.open('{{ $photoUrl }}', '_blank')" 
                                        class="bg-white text-purple-600 p-2 rounded-full shadow-lg hover:bg-purple-50 transition-all"
                                        title="Lihat gambar">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <button onclick="event.stopPropagation(); downloadImage('{{ $photoUrl }}', 'tanda_terima_{{ $pengiriman->no_pengiriman }}.jpg');"
                                        class="bg-white text-green-600 p-2 rounded-full shadow-lg hover:bg-green-50 transition-all"
                                        title="Download gambar">
                                    <i class="fas fa-download text-sm"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Card 7: Catatan --}}
    @if($pengiriman->catatan)
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-4">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-sticky-note text-orange-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Catatan</h3>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700">{{ $pengiriman->catatan }}</p>
            </div>
        </div>
    @endif
</div>

{{-- Modal Footer - Sticky --}}
<div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-xl">
    @if(!$pengiriman->foto_tanda_terima)
        {{-- Peringatan jika foto tanda terima belum ada --}}
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-red-600 mt-0.5 mr-3"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-800">Foto Tanda Terima Diperlukan</p>
                    <p class="text-xs text-red-700 mt-1">
                        Pengiriman ini belum dapat diverifikasi karena foto tanda terima belum diunggah. 
                        Silakan lakukan revisi terlebih dahulu untuk mengunggah foto tanda terima.
                    </p>
                </div>
            </div>
        </div>
    @endif
    
    @php
        $currentUser = Auth::user();
        $canVerifyRevise = in_array($currentUser->role, ['direktur', 'manager_purchasing']);
    @endphp
    
    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-4 space-y-2 space-y-reverse sm:space-y-0">
        <button type="button" onclick="closeAksiVerifikasiModal()" 
                class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 font-medium">
            <i class="fas fa-times mr-2"></i>
            Tutup
        </button>
        
        @if($canVerifyRevise)
            <button type="button" onclick="openRevisiModalFromDetail()" 
                    class="w-full sm:w-auto px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200 font-medium">
                <i class="fas fa-edit mr-2"></i>
                Revisi
            </button>
            @if($pengiriman->foto_tanda_terima)
                <button type="button" onclick="openVerifikasiModalFromDetail()" 
                        class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 font-medium">
                    <i class="fas fa-check-circle mr-2"></i>
                    Verifikasi
                </button>
            @else
                <button type="button" 
                        disabled
                        title="Foto tanda terima diperlukan untuk verifikasi"
                        class="w-full sm:w-auto px-6 py-3 bg-gray-400 text-gray-200 rounded-lg cursor-not-allowed transition-all duration-200 font-medium">
                    <i class="fas fa-check-circle mr-2"></i>
                    Verifikasi
                </button>
            @endif
        @endif
    </div>
</div>

{{-- Note: Image modal functions sudah didefinisikan secara global di menunggu-verifikasi.blade.php --}}
{{-- Note: Fungsi openRevisiModalFromDetail dan openVerifikasiModalFromDetail 
     sudah didefinisikan secara global di menunggu-verifikasi.blade.php --}}
