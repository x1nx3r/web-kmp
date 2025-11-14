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
                            Invoice: <span class="font-semibold">{{ $invoice->invoice_number }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                        @if($approval->status === 'pending')
                            <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-2"></i> Menunggu Approval
                            </span>
                        @elseif($approval->status === 'completed')
                            <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-2"></i> Selesai
                            </span>
                        @else
                            <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-2"></i> Ditolak
                            </span>
                        @endif
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
                                    <p class="font-semibold text-gray-900">{{ ucfirst($invoice->payment_status) }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">Customer</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->customer_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $invoice->customer_address }}</p>
                                </div>
                            </div>
                        </div>

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
                                    <p class="text-sm text-gray-600">Total Harga</p>
                                    <p class="font-semibold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Refraksi & Calculation --}}
                        <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-6 border border-yellow-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-percent text-yellow-600 mr-2"></i>
                                Perhitungan & Refraksi
                            </h3>

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
                            @endif

                            {{-- Edit Refraksi Form - Only if not completed --}}
                            @if($approval->status !== 'completed' && $approval->status !== 'rejected')
                                <div class="mb-4 p-4 bg-white rounded-lg border border-yellow-300">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                        <i class="fas fa-edit mr-1"></i>
                                        Edit Refraksi
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
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Nilai</label>
                                            <input type="number" wire:model="refraksiForm.value" step="0.01"
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
                                <div class="flex justify-between">
                                    <span class="text-gray-600">PPN ({{ $invoice->tax_percentage }}%):</span>
                                    <span class="font-semibold">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
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
                        {{-- Approval Progress --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-tasks text-purple-600 mr-2"></i>
                                Status Approval
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Status</label>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">
                                        @if($approval->status === 'pending')
                                            <i class="fas fa-clock text-blue-600 mr-1"></i>Menunggu Approval
                                        @elseif($approval->status === 'completed')
                                            <i class="fas fa-check-circle text-green-600 mr-1"></i>Selesai
                                        @elseif($approval->status === 'rejected')
                                            <i class="fas fa-times-circle text-red-600 mr-1"></i>Ditolak
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>

                                {{-- Approver Info --}}
                                @if($approval->status === 'completed')
                                    <div>
                                        <label class="text-xs font-medium text-gray-500">Disetujui Oleh</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            @if($approval->staff)
                                                <i class="fas fa-check text-green-500 mr-1"></i>
                                                {{ $approval->staff->nama }} (Staff Accounting)
                                            @elseif($approval->manager)
                                                <i class="fas fa-check text-green-500 mr-1"></i>
                                                {{ $approval->manager->nama }} (Manager Accounting)
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Notes Input - Only if not completed --}}
                        @if($approval->status !== 'completed' && $approval->status !== 'rejected')
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
