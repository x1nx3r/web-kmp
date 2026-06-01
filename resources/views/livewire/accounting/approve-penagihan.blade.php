<div class="py-8 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">

        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle text-green-500"></i>
                <p class="font-medium">{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <p class="font-medium">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Back Button --}}
        <div class="mb-5">
            <a href="{{ route('accounting.approval-penagihan') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                <i class="fas fa-arrow-left text-xs"></i>
                Kembali ke Daftar
            </a>
        </div>

        {{-- Page Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    @if($editMode) Edit Invoice Penagihan @else Detail Approval Penagihan @endif
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    No. Invoice: <span class="font-semibold text-gray-700">{{ $invoiceNumber ?: '-' }}</span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ===== LEFT COLUMN ===== --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Informasi Invoice --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                        <i class="fas fa-file-invoice text-purple-500 text-sm"></i>
                        <h3 class="text-sm font-semibold text-gray-800">Informasi Invoice</h3>
                    </div>
                    <div class="p-5 space-y-4">

                        {{-- Nomor Invoice --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Nomor Invoice</label>
                            <input type="text" wire:model.defer="invoiceNumber"
                                placeholder="Masukkan nomor invoice"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-2 focus:ring-purple-400 focus:border-transparent focus:bg-white transition">
                            @error('invoiceNumber')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal Invoice & Jatuh Tempo --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Tanggal Invoice</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $invoice->invoice_date->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Jatuh Tempo</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $invoice->due_date->format('d M Y') }}</p>
                            </div>
                        </div>

                        {{--
                            FIX: Bank menggunakan wire:model.defer (bukan .live)
                            + tombol "Simpan Bank" eksplisit agar tidak ada re-render
                            otomatis yang mereset field lain.
                        --}}
                        @if($canManage && ($approval->status !== 'completed' && $approval->status !== 'rejected' || $editMode))
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-2">
                                    <i class="fas fa-university mr-1"></i> Bank Tujuan Pembayaran
                                </label>
                                <div class="space-y-2">
                                    @foreach($bankOptions as $key => $bank)
                                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all
                                            {{ $selectedBank === $key
                                                ? 'border-purple-400 bg-purple-50'
                                                : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                            <input type="radio" name="bank_selection"
                                                wire:model.defer="selectedBank" value="{{ $key }}"
                                                {{ $selectedBank === $key ? 'checked' : '' }}
                                                class="w-4 h-4 text-purple-600 focus:ring-purple-400">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-semibold {{ $selectedBank === $key ? 'text-purple-900' : 'text-gray-800' }}">
                                                        {{ $bank['name'] }}
                                                    </p>
                                                    @if($selectedBank === $key)
                                                        <span class="text-xs font-medium text-purple-600">
                                                            <i class="fas fa-check-circle mr-1"></i>Dipilih
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-xs {{ $selectedBank === $key ? 'text-purple-700' : 'text-gray-500' }}">
                                                    {{ $bank['account_number'] }} &bull; a/n {{ $bank['account_name'] }}
                                                </p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                {{--
                                    FIX: Tombol simpan bank eksplisit.
                                    Dulu auto-save via updatedSelectedBank() yang memicu re-render
                                    dan mereset semua field .defer. Sekarang harus klik tombol ini.
                                --}}
                                <button wire:click="updateBankSelection" wire:loading.attr="disabled"
                                    class="mt-3 w-full px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition font-medium disabled:opacity-50 flex items-center justify-center gap-2">
                                    <span wire:loading.remove wire:target="updateBankSelection">
                                        <i class="fas fa-save mr-1.5"></i>Simpan Pilihan Bank
                                    </span>
                                    <span wire:loading wire:target="updateBankSelection">
                                        <i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...
                                    </span>
                                </button>
                            </div>
                        @else
                            @if($invoice->bank_name)
                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-university mr-1"></i>Bank Terpilih</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $invoice->bank_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $invoice->bank_account_number }} &bull; a/n {{ $invoice->bank_account_name }}</p>
                                </div>
                            @endif
                        @endif

                        {{-- Informasi Customer --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-2">Informasi Customer</label>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Nama Customer *</label>
                                    <input type="text" wire:model.defer="customerName"
                                        placeholder="Nama customer"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-2 focus:ring-purple-400 focus:border-transparent focus:bg-white transition">
                                    @error('customerName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Alamat Customer *</label>
                                    <textarea wire:model.defer="customerAddress" rows="2"
                                        placeholder="Alamat customer"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-2 focus:ring-purple-400 focus:border-transparent focus:bg-white transition resize-none"></textarea>
                                    @error('customerAddress') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1">No. Telepon</label>
                                        <input type="text" wire:model.defer="customerPhone"
                                            placeholder="No. telepon"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-2 focus:ring-purple-400 focus:border-transparent focus:bg-white transition">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1">Email</label>
                                        <input type="email" wire:model.defer="customerEmail"
                                            placeholder="Email customer"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-2 focus:ring-purple-400 focus:border-transparent focus:bg-white transition">
                                        @error('customerEmail') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan Keuangan --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calculator text-blue-500 text-sm"></i>
                            <h3 class="text-sm font-semibold text-gray-800">Ringkasan Keuangan</h3>
                        </div>
                        @if($order)
                            <span class="text-xs text-gray-400">PO: {{ $order->po_number ?? '-' }}</span>
                        @endif
                    </div>

                    @php
                        // === Subtotal Penagihan ===
                        $subtotalPenagihan = 0;
                        if ($invoice) {
                            if (floatval($invoice->subtotal) > 0) {
                                $subtotalPenagihan = floatval($invoice->subtotal);
                            } elseif (floatval($invoice->amount_after_refraksi) > 0) {
                                $subtotalPenagihan = floatval($invoice->amount_after_refraksi);
                            }
                        }

                        // === Subtotal Pembayaran ===
                        $subtotalPembayaran = $totalSupplierCost;

                        // === Margin ===
                        $selisih = $subtotalPenagihan - $subtotalPembayaran;
                        $marginPct = $subtotalPenagihan > 0 ? ($selisih / $subtotalPenagihan) * 100 : 0;
                    @endphp

                    <div class="divide-y divide-gray-100">
                        {{-- Subtotal Invoice Penagihan --}}
                        <div class="px-5 py-3.5 flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-700">Subtotal Invoice Penagihan</p>
                                <p class="text-xs text-gray-400 mt-0.5">Tagihan ke customer (setelah refraksi & potongan)</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">
                                Rp {{ number_format($subtotalPenagihan, 2, ',', '.') }}
                            </span>
                        </div>

                        {{-- Subtotal Approval Pembayaran --}}
                        <div class="px-5 py-3.5 flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-700">Subtotal Pembayaran Supplier</p>
                                <p class="text-xs text-gray-400 mt-0.5">Dibayarkan ke supplier (setelah refraksi & potongan)</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">
                                Rp {{ number_format($subtotalPembayaran, 2, ',', '.') }}
                            </span>
                        </div>

                        {{-- Margin --}}
                        <div class="px-5 py-3.5 flex justify-between items-center bg-gray-50">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Margin</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ number_format(abs($marginPct), 2, ',', '.') }}% dari total penagihan
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold {{ $selisih >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $selisih >= 0 ? '+' : '-' }}Rp {{ number_format(abs($selisih), 2, ',', '.') }}
                                </span>
                                <p class="text-xs mt-0.5">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $selisih >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $selisih >= 0 ? 'PROFIT' : 'LOSS' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Informasi Pengiriman --}}
                @if($isMerged)
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-purple-50">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-cubes text-purple-600 text-sm"></i>
                                <h3 class="text-sm font-semibold text-purple-900">Informasi Pengiriman (Gabungan {{ $shipments->count() }} Kiriman)</h3>
                            </div>
                            <span class="px-2.5 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">
                                MERGED INVOICE
                            </span>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Total Qty Gabungan</p>
                                    <p class="text-base font-bold text-gray-800">{{ number_format($shipments->sum('total_qty_kirim'), 2, ',', '.') }} kg</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Total Harga Beli (Supplier)</p>
                                    <p class="text-base font-bold text-gray-800">Rp {{ number_format($totalSupplierCost, 2, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Total Harga Jual (Tagihan)</p>
                                    <p class="text-base font-bold text-gray-800">Rp {{ number_format($totalSelling, 2, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Margin Gabungan</p>
                                    <p class="text-base font-bold {{ $totalMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($totalMargin, 2, ',', '.') }}
                                        <span class="text-xs font-semibold">({{ number_format($marginPercentage, 2, ',', '.') }}%)</span>
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                @foreach($shipments as $s)
                                    @php
                                        $sPembayaran = 0;
                                        $sApproval = $s->approvalPembayaran;
                                        if ($sApproval) {
                                            if (floatval($sApproval->subtotal) > 0) {
                                                $sPembayaran = floatval($sApproval->subtotal);
                                            } elseif (floatval($sApproval->amount_after_refraksi) > 0) {
                                                $sPembayaran = floatval($sApproval->amount_after_refraksi);
                                            } else {
                                                $sPembayaran = floatval($s->total_harga_kirim);
                                            }
                                        } else {
                                            $sPembayaran = floatval($s->total_harga_kirim);
                                        }

                                        $sPenagihan = 0;
                                        if ($s->pengirimanDetails) {
                                            foreach ($s->pengirimanDetails as $detail) {
                                                $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                                                $hargaJual = $orderDetail ? floatval($orderDetail->harga_jual) : 0;
                                                $sPenagihan += floatval($detail->qty_kirim) * $hargaJual;
                                            }
                                        }

                                        $sMargin = $sPenagihan - $sPembayaran;
                                        $sMarginPercentage = $sPenagihan > 0 ? ($sMargin / $sPenagihan) * 100 : 0;
                                    @endphp
                                    <div class="bg-white rounded-lg border border-purple-100 hover:border-purple-200 shadow-sm overflow-hidden transition-all duration-200">
                                        <div class="px-4 py-2.5 bg-gradient-to-r from-purple-50 to-indigo-50/30 flex justify-between items-center border-b border-purple-50">
                                            <h4 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                                <i class="fas fa-truck text-purple-500"></i>
                                                {{ $s->no_pengiriman }}
                                            </h4>
                                            <span class="text-[10px] text-gray-400 font-medium">Tanggal Kirim: {{ $s->tanggal_kirim->format('d M Y') }}</span>
                                        </div>
                                        <div class="p-4">
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mb-3">
                                                <div>
                                                    <p class="text-gray-400">Qty Kirim</p>
                                                    <p class="font-semibold text-gray-800">{{ number_format($s->total_qty_kirim, 2, ',', '.') }} kg</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-400">Harga Beli</p>
                                                    <p class="font-semibold text-gray-800">Rp {{ number_format($sPembayaran, 2, ',', '.') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-400">Harga Jual</p>
                                                    <p class="font-semibold text-gray-800">Rp {{ number_format($sPenagihan, 2, ',', '.') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-400">Margin Kiriman</p>
                                                    <p class="font-bold {{ $sMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        Rp {{ number_format($sMargin, 2, ',', '.') }} ({{ number_format($sMarginPercentage, 1, ',', '.') }}%)
                                                    </p>
                                                </div>
                                            </div>

                                            @if($s->pengirimanDetails && $s->pengirimanDetails->count() > 0)
                                                <div class="border-t border-gray-100/70 pt-3">
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Detail Item Kiriman</p>
                                                    <div class="space-y-1.5">
                                                        @foreach($s->pengirimanDetails as $detail)
                                                            @php
                                                                $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                                                                $hargaJualItem = $orderDetail ? floatval($orderDetail->harga_jual) : 0;
                                                                $totalJualItem = floatval($detail->qty_kirim) * $hargaJualItem;
                                                            @endphp
                                                            <div class="flex flex-wrap justify-between items-center bg-gray-50 rounded px-3 py-1.5 text-xs">
                                                                <div class="min-w-0">
                                                                    <p class="font-medium text-gray-700 truncate">{{ $detail->purchaseOrderBahanBaku->nama_material_po ?? $detail->purchaseOrderBahanBaku->bahanBakuKlien->nama ?? $detail->bahanBakuSupplier->nama ?? '-' }}</p>
                                                                    <p class="text-[10px] text-gray-400">{{ $detail->bahanBakuSupplier->supplier->nama ?? '-' }}</p>
                                                                </div>
                                                                <div class="flex items-center gap-4 text-right">
                                                                    <div>
                                                                        <span class="text-gray-500">{{ number_format($detail->qty_kirim, 2, ',', '.') }} kg</span>
                                                                        <span class="text-gray-300 mx-1">|</span>
                                                                        <span class="text-gray-500">Rp {{ number_format($hargaJualItem, 0, ',', '.') }}/kg</span>
                                                                    </div>
                                                                    <span class="font-semibold text-gray-800">Rp {{ number_format($totalJualItem, 0, ',', '.') }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif($pengiriman)
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                            <i class="fas fa-truck text-blue-500 text-sm"></i>
                            <h3 class="text-sm font-semibold text-gray-800">Informasi Pengiriman</h3>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">No. Pengiriman</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $pengiriman->no_pengiriman }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Tanggal Kirim</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $pengiriman->tanggal_kirim->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Total Qty</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Total Harga Beli</p>
                                    <p class="text-sm font-semibold text-gray-800">Rp {{ number_format($pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                                </div>
                            </div>

                            {{-- Detail item pengiriman --}}
                            @if($pengiriman->pengirimanDetails && $pengiriman->pengirimanDetails->count() > 0)
                                <div class="border-t border-gray-100 pt-4">
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Detail Item</p>
                                    <div class="space-y-2">
                                        @foreach($pengiriman->pengirimanDetails as $detail)
                                            @php
                                                $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                                            @endphp
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50 rounded-lg px-4 py-3 text-sm">
                                                <div>
                                                    <p class="text-xs text-gray-400 mb-0.5">Bahan Baku</p>
                                                    <p class="font-medium text-gray-800">{{ $detail->bahanBakuSupplier->nama ?? '-' }}</p>
                                                    <p class="text-xs text-gray-400">{{ $detail->bahanBakuSupplier->supplier->nama ?? '-' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-400 mb-0.5">Qty Kirim</p>
                                                    <p class="font-medium text-gray-800">{{ number_format($detail->qty_kirim, 2, ',', '.') }} kg</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-400 mb-0.5">Harga Beli/kg</p>
                                                    <p class="font-medium text-gray-800">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-400 mb-0.5">Total Harga Beli</p>
                                                    <p class="font-semibold text-gray-900">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-sm text-amber-700 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        Data pengiriman tidak tersedia
                    </div>
                @endif

                {{-- Perhitungan & Refraksi --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                        <i class="fas fa-percent text-amber-500 text-sm"></i>
                        <h3 class="text-sm font-semibold text-gray-800">Perhitungan & Refraksi</h3>
                        <span class="text-xs text-gray-400">(Opsional)</span>
                    </div>
                    <div class="p-5 space-y-4">

                        {{-- Tanggal Invoice --}}
                        @if($canManage && $approval->status !== 'completed' && $approval->status !== 'rejected')
                            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                                <h4 class="text-xs font-semibold text-gray-600 mb-3 flex items-center gap-1.5">
                                    <i class="fas fa-calendar text-blue-400"></i> Tanggal Invoice
                                </h4>
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Tanggal Invoice</label>
                                        <input type="date" wire:model.defer="invoiceDate"
                                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                                        @error('invoiceDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Jatuh Tempo</label>
                                        <input type="date" wire:model.defer="dueDate"
                                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                                        @error('dueDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <button wire:click="updateInvoiceDates"
                                    class="w-full px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition font-medium">
                                    <i class="fas fa-save mr-1.5"></i> Simpan Tanggal
                                </button>
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div>
                                    <p class="text-xs text-gray-400 mb-0.5">Tanggal Invoice</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $invoice->invoice_date?->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-0.5">Jatuh Tempo</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $invoice->due_date?->format('d M Y') }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Refraksi Saat Ini --}}
                        @if($invoice->refraksi_value > 0)
                            <div class="bg-amber-50 rounded-lg border border-amber-200 p-4 text-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-semibold text-amber-800">Refraksi Aktif</p>
                                    @if($pengiriman && $pengiriman->approvalPembayaran && $pengiriman->approvalPembayaran->refraksi_value > 0)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                            Dari Pembayaran
                                        </span>
                                    @endif
                                </div>
                                @if($invoice->refraksi_type === 'qty')
                                    <p class="text-gray-700">Tipe Qty &bull; {{ $invoice->refraksi_value }}%</p>
                                    <p class="text-gray-600 text-xs mt-0.5">{{ number_format($invoice->qty_before_refraksi, 2, ',', '.') }} → {{ number_format($invoice->qty_after_refraksi, 2, ',', '.') }} kg</p>
                                @elseif($invoice->refraksi_type === 'rupiah')
                                    <p class="text-gray-700">Tipe Rupiah &bull; Rp {{ number_format($invoice->refraksi_value, 2, ',', '.') }}/kg</p>
                                    <p class="text-gray-600 text-xs mt-0.5">Qty: {{ number_format($invoice->qty_before_refraksi, 2, ',', '.') }} kg</p>
                                @elseif($invoice->refraksi_type === 'lainnya')
                                    <p class="text-gray-700">Tipe Lainnya (Manual)</p>
                                    <p class="text-gray-600 text-xs mt-0.5">Nilai: Rp {{ number_format($invoice->refraksi_value, 2, ',', '.') }}</p>
                                @else
                                    <p class="text-gray-700">{{ ucfirst($invoice->refraksi_type) }}</p>
                                @endif
                                <p class="font-semibold text-red-600 mt-2">Potongan: Rp {{ number_format($invoice->refraksi_amount, 2, ',', '.') }}</p>
                                @if($pengiriman && $pengiriman->approvalPembayaran && $pengiriman->approvalPembayaran->refraksi_value > 0)
                                    <p class="text-xs text-blue-600 mt-1.5">
                                        <i class="fas fa-info-circle mr-1"></i>Refraksi otomatis dari approval pembayaran
                                    </p>
                                @endif
                            </div>
                        @else
                            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 text-center text-sm text-gray-400">
                                <i class="fas fa-info-circle mr-1"></i> Tidak ada refraksi diterapkan
                            </div>
                        @endif

                        {{--
                            FIX: Refraksi sekarang pakai wire:model.defer (BUKAN .live)
                            + tombol "Simpan Refraksi" eksplisit.
                            Dulu: wire:model.live → setiap ketik langsung kirim ke server
                            → re-render → semua field .defer yang belum disimpan ter-reset.
                            Sekarang: user edit bebas, klik tombol baru disimpan ke DB.
                        --}}
                        @if($canManage && ($approval->status !== 'completed' && $approval->status !== 'rejected' || $editMode))
                            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                                <h4 class="text-xs font-semibold text-gray-600 mb-3 flex items-center gap-1.5">
                                    <i class="fas fa-edit text-amber-400"></i>
                                    Edit Refraksi <span class="font-normal text-gray-400">(0 = tanpa refraksi)</span>
                                </h4>
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Tipe Refraksi</label>
                                        <select wire:model.defer="refraksiForm.type"
                                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                                            <option value="qty">Qty (%)</option>
                                            <option value="rupiah">Rupiah (Rp/kg)</option>
                                            <option value="lainnya">Lainnya (Manual)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Nilai</label>
                                        <input type="number" wire:model.defer="refraksiForm.value"
                                            step="0.01" min="0" placeholder="0" onwheel="this.blur()"
                                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                                    </div>
                                </div>
                                {{-- Tombol simpan refraksi eksplisit --}}
                                <button wire:click="updateRefraksi" wire:loading.attr="disabled"
                                    class="w-full px-4 py-2 bg-amber-500 text-white text-sm rounded-lg hover:bg-amber-600 transition font-medium disabled:opacity-50 flex items-center justify-center gap-2">
                                    <span wire:loading.remove wire:target="updateRefraksi">
                                        <i class="fas fa-save mr-1.5"></i>Simpan Refraksi
                                    </span>
                                    <span wire:loading wire:target="updateRefraksi">
                                        <i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...
                                    </span>
                                </button>
                            </div>
                        @endif

                        {{-- Kalkulasi Akhir --}}
                        <div class="rounded-lg border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 flex justify-between items-center text-sm border-b border-gray-100">
                                <span class="text-gray-600">Harga Jual (sebelum refraksi)</span>
                                <span class="font-semibold text-gray-800">Rp {{ number_format($invoice->amount_before_refraksi ?? $invoice->subtotal, 2, ',', '.') }}</span>
                            </div>
                            @if(($invoice->refraksi_amount ?? 0) > 0)
                                <div class="px-4 py-3 flex justify-between items-center text-sm border-b border-gray-100 bg-red-50">
                                    <span class="text-gray-600">Potongan Refraksi</span>
                                    <span class="font-semibold text-red-500">- Rp {{ number_format($invoice->refraksi_amount, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="px-4 py-3 flex justify-between items-center bg-gray-50">
                                <span class="text-sm font-bold text-gray-900">Total Invoice</span>
                                <span class="text-base font-bold text-green-600">Rp {{ number_format($invoice->amount_after_refraksi, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pengeluaran Tambahan --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" wire:key="invoice-expenses-{{ $invoice->id }}">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                        <i class="fas fa-receipt text-orange-500 text-sm"></i>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Pengeluaran Tambahan</h3>
                            <p class="text-xs text-gray-400">Truk, kuli, fee, dll — dikurangkan dari subtotal invoice</p>
                        </div>
                    </div>

                    <div class="p-5">
                        @if($canManage && ($approval->status !== 'completed' && $approval->status !== 'rejected' || $editMode))
                            <div class="space-y-4">
                                {{-- Fixed: Truk, Kuli, Fee --}}
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach(['truk' => 'Truk', 'kuli' => 'Kuli', 'fee' => 'Fee'] as $key => $label)
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $label }}</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                <input type="number" wire:model.defer="expenseForm.{{ $key }}"
                                                    min="0" step="0.01"
                                                    class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-orange-300 focus:border-orange-400 focus:bg-white transition"
                                                    placeholder="0" onwheel="this.blur()">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Dynamic others --}}
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold text-gray-500">Lainnya</p>
                                        <button type="button" wire:click="addOtherExpenseRow"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 transition">
                                            <i class="fas fa-plus"></i> Tambah Baris
                                        </button>
                                    </div>
                                    <div class="space-y-2">
                                        @forelse(($expenseForm['others'] ?? []) as $i => $row)
                                            <div class="grid grid-cols-12 gap-2 items-center">
                                                <div class="col-span-6">
                                                    <input type="text"
                                                        wire:model.defer="expenseForm.others.{{ $i }}.type"
                                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-orange-300 focus:bg-white transition"
                                                        placeholder="Nama pengeluaran (Parkir, Tol, dll)">
                                                </div>
                                                <div class="col-span-5">
                                                    <div class="relative">
                                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                        <input type="number"
                                                            wire:model.defer="expenseForm.others.{{ $i }}.amount"
                                                            min="0" step="0.01"
                                                            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-orange-300 focus:bg-white transition"
                                                            placeholder="0" onwheel="this.blur()">
                                                    </div>
                                                </div>
                                                <div class="col-span-1 flex justify-end">
                                                    <button type="button"
                                                        wire:click="removeOtherExpenseRow({{ $i }})"
                                                        class="w-8 h-8 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg border border-gray-200 hover:border-red-200 transition">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-400 italic">Belum ada baris lainnya.</p>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Preview total --}}
                                @php
                                    $previewTotal = floatval($expenseForm['truk'] ?? 0)
                                                  + floatval($expenseForm['kuli'] ?? 0)
                                                  + floatval($expenseForm['fee'] ?? 0);
                                    foreach(($expenseForm['others'] ?? []) as $r) {
                                        $previewTotal += floatval($r['amount'] ?? 0);
                                    }
                                @endphp
                                <div class="flex items-center justify-between py-2.5 px-4 bg-orange-50 rounded-lg border border-orange-100 text-sm">
                                    <span class="text-gray-600 font-medium">Total Pengeluaran</span>
                                    <span class="font-bold text-orange-600">Rp {{ number_format($previewTotal, 0, ',', '.') }}</span>
                                </div>

                                <button wire:click="updateExpenses" wire:loading.attr="disabled"
                                    class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition disabled:opacity-50 flex items-center justify-center gap-2">
                                    <span wire:loading.remove wire:target="updateExpenses">
                                        <i class="fas fa-save mr-1.5"></i>Simpan Pengeluaran Tambahan
                                    </span>
                                    <span wire:loading wire:target="updateExpenses">
                                        <i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...
                                    </span>
                                </button>
                            </div>

                        @else
                            @php
                                $invoiceExpenses = $invoice->expenses ?? collect();
                                $invoiceExpensesTotal = floatval($invoice->additional_expenses_total ?? 0);
                            @endphp

                            @if($invoiceExpenses->count() > 0)
                                <div class="space-y-2 mb-3">
                                    @foreach($invoiceExpenses as $exp)
                                        <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-lg px-4 py-2.5 text-sm">
                                            <span class="font-medium text-gray-600 uppercase tracking-wide text-xs">{{ $exp->type }}</span>
                                            <span class="font-semibold text-gray-800">Rp {{ number_format($exp->amount, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6 text-gray-400">
                                    <i class="fas fa-receipt text-2xl mb-2 block"></i>
                                    <p class="text-sm">Belum ada pengeluaran tambahan</p>
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-sm">
                                <span class="font-semibold text-gray-700">Total Pengeluaran</span>
                                <span class="font-bold text-orange-600">Rp {{ number_format($invoiceExpensesTotal, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            {{-- ===== END LEFT COLUMN ===== --}}

            {{-- ===== RIGHT COLUMN ===== --}}
            <div class="space-y-5">

                {{-- Catatan Invoice --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-sticky-note text-purple-400 text-sm"></i>
                        Catatan Invoice
                    </h3>
                    <textarea wire:model.defer="invoiceNotes" rows="4"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-2 focus:ring-purple-400 focus:border-transparent focus:bg-white transition resize-none"
                        placeholder="Tambahkan catatan untuk invoice ini..."></textarea>
                </div>

                {{-- Simpan Semua --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs text-gray-400 mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menyimpan: nomor invoice, tanggal, customer, catatan.
                        Refraksi & bank disimpan via tombolnya masing-masing.
                    </p>
                    <button wire:click="updateAllInvoiceFields" wire:loading.attr="disabled"
                        class="w-full px-5 py-2.5 text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition shadow-sm disabled:opacity-50 flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="updateAllInvoiceFields">
                            <i class="fas fa-save mr-2"></i>Simpan Semua Perubahan
                        </span>
                        <span wire:loading wire:target="updateAllInvoiceFields">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...
                        </span>
                    </button>
                </div>

                {{-- Catatan & Aksi Approval --}}
                @if($canManage && $approval->status !== 'completed' && $approval->status !== 'rejected')
                    <div class="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 class="text-sm font-semibold text-gray-800 mb-3">Catatan Approval</h3>
                        <textarea wire:model.defer="notes" rows="4"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-2 focus:ring-purple-400 focus:border-transparent focus:bg-white transition resize-none"
                            placeholder="Tambahkan catatan untuk approval ini..."></textarea>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 class="text-sm font-semibold text-gray-800 mb-3">Aksi</h3>
                        @if($editMode)
                            <button wire:click="updateInvoice" wire:loading.attr="disabled"
                                class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition disabled:opacity-50 flex items-center justify-center">
                                <span wire:loading.remove wire:target="updateInvoice">
                                    <i class="fas fa-save mr-2"></i>Update Invoice
                                </span>
                                <span wire:loading wire:target="updateInvoice">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Updating...
                                </span>
                            </button>
                        @else
                            <div class="space-y-2">
                                <button wire:click="approve" wire:loading.attr="disabled"
                                    class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition disabled:opacity-50 flex items-center justify-center">
                                    <span wire:loading.remove wire:target="approve">
                                        <i class="fas fa-check mr-2"></i>Approve
                                    </span>
                                    <span wire:loading wire:target="approve">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                                    </span>
                                </button>
                                <button wire:click="reject" wire:loading.attr="disabled"
                                    class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50 flex items-center justify-center">
                                    <span wire:loading.remove wire:target="reject">
                                        <i class="fas fa-times mr-2"></i>Tolak
                                    </span>
                                    <span wire:loading wire:target="reject">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                                    </span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Riwayat Approval --}}
                @if($approvalHistory->count() > 0)
                    <div class="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-history text-gray-400 text-sm"></i>
                            Riwayat Approval
                        </h3>
                        <div class="space-y-3">
                            @foreach($approvalHistory as $history)
                                <div class="rounded-lg px-3 py-3 border text-sm
                                    @if($history->action === 'approved') bg-green-50 border-green-100
                                    @elseif($history->action === 'rejected') bg-red-50 border-red-100
                                    @else bg-gray-50 border-gray-100
                                    @endif">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-semibold text-gray-800 text-xs">{{ $history->user->nama ?? 'Unknown' }}</span>
                                        <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                            @if($history->action === 'approved') bg-green-100 text-green-700
                                            @elseif($history->action === 'rejected') bg-red-100 text-red-700
                                            @else bg-amber-100 text-amber-700
                                            @endif">
                                            {{ ucfirst($history->action) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400 mb-1">
                                        {{ ucfirst($history->role) }} &bull; {{ $history->created_at->format('d M Y H:i') }}
                                    </p>
                                    @if($history->notes)
                                        <p class="text-xs text-gray-600 mt-1.5 px-2 py-1.5 bg-white rounded border border-gray-100">
                                            {{ $history->notes }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
            {{-- ===== END RIGHT COLUMN ===== --}}

        </div>
    </div>
</div>