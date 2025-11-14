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
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-2">Detail Invoice Penagihan</h1>
                        <p class="text-green-100 text-sm">
                            Invoice: <span class="font-semibold">{{ $invoice->invoice_number }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-2"></i> Completed
                        </span>
                        <button wire:click="generatePdf" wire:loading.attr="disabled"
                                class="px-4 py-2 bg-white text-green-600 rounded-lg hover:bg-green-50 font-medium text-sm flex items-center gap-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="generatePdf">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </span>
                            <span wire:loading wire:target="generatePdf">
                                <i class="fas fa-spinner fa-spin"></i> Generating...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Left Column - Invoice & Pengiriman Info --}}
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Invoice Information --}}
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-file-invoice text-blue-600 mr-2"></i>
                                Informasi Invoice
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Nomor Invoice</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Tanggal Invoice</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->invoice_date->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Jatuh Tempo</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->due_date->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Status Pembayaran</p>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $invoice->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ ucfirst($invoice->payment_status) }}
                                    </span>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">Customer</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->customer_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $invoice->customer_address }}</p>
                                    @if($invoice->customer_phone)
                                        <p class="text-xs text-gray-500">Telp: {{ $invoice->customer_phone }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Pengiriman Information --}}
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-6 border border-purple-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-truck text-purple-600 mr-2"></i>
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
                                    <p class="text-sm text-gray-600">Total Harga</p>
                                    <p class="font-semibold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Items Detail --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-list text-gray-600 mr-2"></i>
                                Detail Item
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($pengiriman->details as $detail)
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    {{ $detail->purchaseOrderBahanBaku->bahanBakuKlien->nama_bahan_baku ?? $detail->bahanBakuSupplier->nama ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                                    {{ number_format($detail->qty_kirim, 2, ',', '.') }} kg
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                                    Rp {{ number_format($detail->harga_kirim, 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                                                    Rp {{ number_format($detail->total_harga, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Refraksi & Calculation --}}
                        <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-6 border border-yellow-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-percent text-yellow-600 mr-2"></i>
                                Perhitungan & Refraksi
                            </h3>

                            {{-- Display Refraksi --}}
                            @if($invoice->refraksi_value > 0)
                                <div class="mb-4 p-4 bg-white rounded-lg border border-yellow-300">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">Refraksi:</p>
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

                    {{-- Right Column - Approval Status --}}
                    <div class="space-y-6">
                        {{-- Approval Progress --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-tasks text-purple-600 mr-2"></i>
                                Progress Approval
                            </h3>
                            <div class="space-y-4">
                                {{-- Status --}}
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Status</label>
                                    <p class="mt-1 text-sm font-semibold text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i>Selesai
                                    </p>
                                </div>

                                {{-- Staff --}}
                                @if($approval->staff)
                                    <div>
                                        <label class="text-xs font-medium text-gray-500">Staff</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <i class="fas fa-check text-green-500 mr-1"></i>
                                            {{ $approval->staff->nama }}
                                        </p>
                                        @if($approval->staff_approved_at)
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $approval->staff_approved_at->format('d M Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                {{-- Manager --}}
                                @if($approval->manager)
                                    <div>
                                        <label class="text-xs font-medium text-gray-500">Manager (Final Approval)</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <i class="fas fa-check text-green-500 mr-1"></i>
                                            {{ $approval->manager->nama }}
                                        </p>
                                        @if($approval->manager_approved_at)
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $approval->manager_approved_at->format('d M Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

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
