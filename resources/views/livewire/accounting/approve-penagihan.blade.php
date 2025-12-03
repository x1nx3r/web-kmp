<div class="py-8 px-4">
    <div class="max-w-7xl mx-auto">
        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="text-green-700 font-medium">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Back Button --}}
        <div class="mb-6">
            <a href="{{ route('accounting.approval-penagihan') }}"
               class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Daftar
            </a>
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-2">Detail Approval Penagihan</h1>
                        <p class="text-purple-100 text-sm">
                            Nomor Invoice Saat Ini: <span class="font-semibold">{{ $invoiceNumber ?: '-' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Left Column - Invoice & Pengiriman Info --}}
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Invoice Information --}}
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6 border border-green-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-file-invoice text-green-600 mr-2"></i>
                                Informasi Invoice
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-600 mb-2">Nomor Invoice</label>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                        <input
                                            type="text"
                                            wire:model.defer="invoiceNumber"
                                            placeholder="Masukkan nomor invoice"
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                        >
                                        <button
                                            wire:click="updateInvoiceNumber"
                                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors"
                                        >
                                            <i class="fas fa-save mr-2"></i>
                                            Simpan Nomor Invoice
                                        </button>
                                    </div>
                                    @error('invoiceNumber')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Bank Selection --}}
                                @if($canManage && $approval->status !== 'completed' && $approval->status !== 'rejected')
                                    <div class="col-span-2 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">
                                            <i class="fas fa-university text-blue-600 mr-1"></i>
                                            Pilih Bank untuk Invoice
                                        </label>
                                        <div class="space-y-3">
                                            @foreach($bankOptions as $key => $bank)
                                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all {{ $selectedBank === $key ? 'border-blue-600 bg-blue-100 shadow-md' : 'border-gray-200 bg-white hover:border-blue-300 hover:bg-blue-50' }}">
                                                    <input
                                                        type="radio"
                                                        name="bank_selection"
                                                        wire:model.live="selectedBank"
                                                        value="{{ $key }}"
                                                        {{ $selectedBank === $key ? 'checked' : '' }}
                                                        class="mt-1 w-4 h-4 text-blue-600 focus:ring-blue-500"
                                                    >
                                                    <div class="ml-3 flex-1">
                                                        <div class="flex items-center justify-between">
                                                            <p class="font-semibold {{ $selectedBank === $key ? 'text-blue-900' : 'text-gray-900' }}">
                                                                {{ $bank['name'] }}
                                                            </p>
                                                            @if($selectedBank === $key)
                                                                <span class="ml-2 px-2 py-0.5 bg-blue-600 text-white text-xs font-semibold rounded-full">
                                                                    <i class="fas fa-check mr-1"></i>Dipilih
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-sm {{ $selectedBank === $key ? 'text-blue-800' : 'text-gray-600' }}">{{ $bank['account_number'] }}</p>
                                                        <p class="text-xs {{ $selectedBank === $key ? 'text-blue-700' : 'text-gray-500' }}">a/n {{ $bank['account_name'] }}</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        <div class="mt-3 p-2 bg-green-50 border border-green-200 rounded-lg">
                                            <p class="text-xs text-green-800 flex items-center">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                <span>Perubahan bank akan tersimpan otomatis</span>
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    {{-- Display selected bank info --}}
                                    @if($invoice->bank_name)
                                        <div class="col-span-2 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                            <p class="text-xs text-gray-600 mb-2">
                                                <i class="fas fa-university mr-1"></i>
                                                Bank Terpilih
                                            </p>
                                            <p class="font-semibold text-gray-900">{{ $invoice->bank_name }}</p>
                                            <p class="text-sm text-gray-600">{{ $invoice->bank_account_number }}</p>
                                            <p class="text-xs text-gray-500">a/n {{ $invoice->bank_account_name }}</p>
                                        </div>
                                    @endif
                                @endif

                                <div>
                                    <p class="text-sm text-gray-600">Tanggal Invoice</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->invoice_date->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Jatuh Tempo</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->due_date->format('d M Y') }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">Customer</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->customer_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $invoice->customer_address }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Financial Summary from Order --}}
                        @if($order)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-calculator text-blue-600 mr-3"></i>
                                        Ringkasan Keuangan Order
                                    </h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Nomor PO:</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $order->po_number ?? '-' }}</span>
                                    </div>
                                    <hr class="border-gray-200">
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
                        @endif

                        {{-- Pengiriman Information --}}
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-truck text-blue-600 mr-2"></i>
                                Informasi Pengiriman
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">No. Pengiriman</p>
                                    <p class="font-semibold text-gray-900">{{ $pengiriman->no_pengiriman }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Tanggal Kirim</p>
                                    <p class="font-semibold text-gray-900">{{ $pengiriman->tanggal_kirim->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Qty</p>
                                    <p class="font-semibold text-gray-900">{{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Harga Beli</p>
                                    <p class="font-semibold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Harga Jual</p>
                                    <p class="font-semibold text-gray-900">Rp {{ number_format($totalSelling, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Margin</p>
                                    <p class="font-semibold {{ $totalMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($totalMargin, 0, ',', '.') }}
                                        <span class="text-xs">({{ number_format($marginPercentage, 2, ',', '.') }}%)</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Refraksi & Calculation --}}
                        <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-6 border border-yellow-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-percent text-yellow-600 mr-2"></i>
                                Perhitungan & Refraksi (Opsional)
                            </h3>

                            {{-- Invoice Date Section --}}
                            @if($canManage && $approval->status !== 'completed' && $approval->status !== 'rejected')
                                <div class="mb-4 p-4 bg-white rounded-lg border border-blue-300">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Tanggal Invoice
                                    </h4>
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Invoice</label>
                                            <input type="date" wire:model="invoiceDate"
                                                   class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            @error('invoiceDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Jatuh Tempo</label>
                                            <input type="date" wire:model="dueDate"
                                                   class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            @error('dueDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <button wire:click="updateInvoiceDates"
                                            class="w-full px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
                                        <i class="fas fa-save mr-1"></i> Update Tanggal
                                    </button>
                                </div>
                            @else
                                <div class="mb-4 p-4 bg-white rounded-lg border border-gray-300">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs text-gray-600">Tanggal Invoice</p>
                                            <p class="text-sm font-semibold">{{ $invoice->invoice_date?->format('d M Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600">Tanggal Jatuh Tempo</p>
                                            <p class="text-sm font-semibold">{{ $invoice->due_date?->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Display Current Refraksi --}}
                            @if($invoice->refraksi_value > 0)
                                <div class="mb-4 p-4 bg-white rounded-lg border border-yellow-300">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">Refraksi Saat Ini:</p>
                                    @if($invoice->refraksi_type === 'qty')
                                        <p class="text-sm text-gray-600">
                                            <strong>Tipe:</strong> Qty ({{ $invoice->refraksi_value }}%)
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <strong>Qty Awal:</strong> {{ number_format($invoice->qty_before_refraksi, 2, ',', '.') }} kg
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <strong>Qty Setelah:</strong> {{ number_format($invoice->qty_after_refraksi, 2, ',', '.') }} kg
                                        </p>
                                    @elseif($invoice->refraksi_type === 'rupiah')
                                        <p class="text-sm text-gray-600">
                                            <strong>Tipe:</strong> Rupiah (Rp {{ number_format($invoice->refraksi_value, 0, ',', '.') }}/kg)
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <strong>Qty:</strong> {{ number_format($invoice->qty_before_refraksi, 2, ',', '.') }} kg
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-600">
                                            <strong>Tipe:</strong> Lainnya (Manual)
                                        </p>
                                    @endif
                                    <p class="text-sm text-red-600 font-semibold mt-2">
                                        Potongan: Rp {{ number_format($invoice->refraksi_amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            @else
                                <div class="mb-4 p-4 bg-white rounded-lg border border-gray-300">
                                    <p class="text-sm text-gray-500 text-center">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Tidak ada refraksi diterapkan
                                    </p>
                                </div>
                            @endif

                            {{-- Edit Refraksi Form - Only if not completed --}}
                            @if($canManage && $approval->status !== 'completed' && $approval->status !== 'rejected')
                                <div class="mb-4 p-4 bg-white rounded-lg border border-yellow-300">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                        <i class="fas fa-edit mr-1"></i>
                                        Edit Refraksi (Opsional - Kosongkan untuk tanpa refraksi)
                                    </h4>
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Refraksi</label>
                                            <select wire:model="refraksiForm.type"
                                                    class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                                                <option value="qty">Qty (%)</option>
                                                <option value="rupiah">Rupiah (Rp/kg)</option>
                                                <option value="lainnya">Lainnya (Manual)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Nilai (0 = tanpa refraksi)</label>
                                            <input type="number" wire:model="refraksiForm.value" step="0.01" min="0"
                                                   placeholder="0"
                                                   class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                                        </div>
                                    </div>
                                    <button wire:click="updateRefraksi" wire:loading.attr="disabled"
                                            class="w-full px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="updateRefraksi">
                                            <i class="fas fa-save mr-1"></i> Update Refraksi
                                        </span>
                                        <span wire:loading wire:target="updateRefraksi">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...
                                        </span>
                                    </button>
                                </div>
                            @endif

                            {{-- Calculation Summary --}}
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-semibold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="border-t pt-2 flex justify-between">
                                    <span class="font-bold text-gray-900">Total:</span>
                                    <span class="font-bold text-lg text-green-600">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column - Approval Status & Actions --}}
                    <div class="space-y-6">
                        {{-- Notes Input - Only if not completed --}}
                        @if($canManage && $approval->status !== 'completed' && $approval->status !== 'rejected')
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Catatan</h3>
                                <textarea wire:model="notes" rows="4"
                                          class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                                          placeholder="Tambahkan catatan untuk approval ini..."></textarea>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>
                                <div class="space-y-3">
                                    <button wire:click="approve" wire:loading.attr="disabled"
                                            class="w-full px-4 py-3 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="approve">
                                            <i class="fas fa-check mr-2"></i> Approve
                                        </span>
                                        <span wire:loading wire:target="approve">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                                        </span>
                                    </button>
                                    <button wire:click="reject" wire:loading.attr="disabled"
                                            class="w-full px-4 py-3 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="reject">
                                            <i class="fas fa-times mr-2"></i> Reject
                                        </span>
                                        <span wire:loading wire:target="reject">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- History --}}
                        @if($approvalHistory->count() > 0)
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-history text-gray-600 mr-2"></i>
                                    Riwayat Approval
                                </h3>
                                <div class="space-y-3">
                                    @foreach($approvalHistory as $history)
                                        <div class="bg-gray-50 rounded-lg p-3 text-sm">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-medium text-gray-900">{{ $history->user->nama ?? 'Unknown' }}</span>
                                                <span class="px-2 py-1 text-xs rounded {{ $history->action === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ ucfirst($history->action) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 mb-1">
                                                {{ ucfirst($history->role) }} • {{ $history->created_at->format('d M Y H:i') }}
                                            </p>
                                            @if($history->notes)
                                                <p class="text-xs text-gray-700 mt-2 p-2 bg-white rounded">{{ $history->notes }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
