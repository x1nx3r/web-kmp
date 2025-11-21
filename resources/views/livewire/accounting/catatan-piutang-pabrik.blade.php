<div class="py-6">
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

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Total Piutang</p>
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-coins text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Sudah Dibayar</p>
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</h3>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Sisa Piutang</p>
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($totalSisa, 0, ',', '.') }}</h3>
                </div>
                <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-hourglass-half text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium">Jatuh Tempo & Terlambat</p>
                    <h3 class="text-2xl font-bold mt-1">{{ $totalJatuhTempo }} <span class="text-sm">Item</span></h3>
                </div>
                <div class="bg-yellow-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Total Terlambat</p>
                    <h3 class="text-2xl font-bold mt-1">{{ $totalTerlambat }} <span class="text-sm">Item</span></h3>
                </div>
                <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i> Pencarian
                </label>
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                    placeholder="Cari no invoice atau nama klien...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-building mr-1"></i> Klien
                </label>
                <select wire:model.live="klienFilter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="all">Semua Klien</option>
                    @foreach($kliens as $klien)
                        <option value="{{ $klien->id }}">{{ $klien->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klien</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari Terlambat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($piutangs as $piutang)
                        @php
                            $hariTerlambat = \Carbon\Carbon::parse($piutang->due_date)->diffInDays(now());
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $loop->iteration + ($piutangs->currentPage() - 1) * $piutangs->perPage() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $piutang->invoice_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $piutang->pengiriman->klien->nama ?? $piutang->customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ $piutang->pengiriman->klien->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $piutang->invoice_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $piutang->due_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($hariTerlambat > 0)
                                    <div class="text-sm font-bold {{ $hariTerlambat > 7 ? 'text-red-600' : 'text-orange-600' }}">
                                        {{ $hariTerlambat }} hari
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500">-</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($piutang->total_amount, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="openDetailModal({{ $piutang->id }})"
                                    class="text-purple-600 hover:text-purple-900" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
                                    <p class="text-gray-500 text-sm">Tidak ada invoice yang melewati jatuh tempo</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($piutangs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $piutangs->links() }}
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $detailPiutang)
    <div class="fixed inset-0 bg-purple-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-violet-600 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Detail Invoice Terlambat
                </h3>
                <button wire:click="closeDetailModal" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-6">
                @php
                    $hariTerlambat = \Carbon\Carbon::parse($detailPiutang->due_date)->diffInDays(now());
                @endphp

                <!-- Warning Terlambat -->
                @if($hariTerlambat > 0)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3"></i>
                        <div>
                            <h5 class="text-red-800 font-bold">Pembayaran Terlambat!</h5>
                            <p class="text-red-700 text-sm mt-1">Invoice ini sudah terlambat <span class="font-bold">{{ $hariTerlambat }} hari</span> dari tanggal jatuh tempo.</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Informasi Invoice -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center text-lg">
                        <i class="fas fa-file-invoice-dollar mr-2 text-purple-600"></i>
                        Informasi Invoice
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">No Invoice:</span>
                            <span class="text-gray-900 font-semibold">{{ $detailPiutang->invoice_number }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">No Pengiriman:</span>
                            <span class="text-gray-900 font-semibold">{{ $detailPiutang->pengiriman->no_pengiriman ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Klien (Pabrik):</span>
                            <span class="text-gray-900 font-semibold">{{ $detailPiutang->pengiriman->klien->nama ?? $detailPiutang->customer_name }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Customer:</span>
                            <span class="text-gray-900">{{ $detailPiutang->customer_name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Tanggal -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center text-lg">
                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                        Informasi Tanggal
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Tanggal Invoice:</span>
                            <span class="text-gray-900">{{ $detailPiutang->invoice_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Jatuh Tempo:</span>
                            <span class="text-gray-900">{{ $detailPiutang->due_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Hari Terlambat:</span>
                            <span class="{{ $hariTerlambat > 7 ? 'text-red-600' : 'text-orange-600' }} font-bold">{{ $hariTerlambat }} hari</span>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Keuangan -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200 shadow-sm">
                        <p class="text-sm text-blue-700 font-medium mb-1 flex items-center">
                            <i class="fas fa-file-alt mr-2"></i>Subtotal
                        </p>
                        <p class="text-2xl font-bold text-blue-900 mt-1">Rp {{ number_format($detailPiutang->subtotal, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 border border-green-200 shadow-sm">
                        <p class="text-sm text-green-700 font-medium mb-1 flex items-center">
                            <i class="fas fa-percentage mr-2"></i>Pajak ({{ $detailPiutang->tax_rate }}%)
                        </p>
                        <p class="text-2xl font-bold text-green-900 mt-1">Rp {{ number_format($detailPiutang->tax, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200 shadow-sm">
                        <p class="text-sm text-purple-700 font-medium mb-1 flex items-center">
                            <i class="fas fa-wallet mr-2"></i>Total Amount
                        </p>
                        <p class="text-2xl font-bold text-purple-900 mt-1">Rp {{ number_format($detailPiutang->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Informasi Customer -->
                @if($detailPiutang->customer_address)
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center text-lg">
                        <i class="fas fa-map-marker-alt mr-2 text-purple-600"></i>
                        Alamat Customer
                    </h4>
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-gray-700 text-sm">{{ $detailPiutang->customer_address }}</p>
                    </div>
                </div>
                @endif

                <!-- Informasi Refraksi (dari Approval) -->
                @if($detailPiutang->approvalPenagihan)
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-5 border border-yellow-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center text-lg">
                        <i class="fas fa-check-circle mr-2 text-yellow-600"></i>
                        Informasi Approval
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Status Approval:</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                {{ ucfirst($detailPiutang->approvalPenagihan->status) }}
                            </span>
                        </div>
                        @if($detailPiutang->approvalPenagihan->refraksi)
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Refraksi:</span>
                            <span class="text-gray-900">{{ $detailPiutang->approvalPenagihan->refraksi }}%</span>
                        </div>
                        @endif
                        @if($detailPiutang->approvalPenagihan->catatan)
                        <div class="col-span-2 py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600 block mb-1">Catatan Approval:</span>
                            <span class="text-gray-700 text-sm">{{ $detailPiutang->approvalPenagihan->catatan }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Informasi Pengiriman -->
                @if($detailPiutang->pengiriman)
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-5 border border-indigo-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center text-lg">
                        <i class="fas fa-shipping-fast mr-2 text-indigo-600"></i>
                        Informasi Pengiriman
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">No Pengiriman:</span>
                            <span class="text-gray-900 font-semibold">{{ $detailPiutang->pengiriman->no_pengiriman }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Tanggal Pengiriman:</span>
                            <span class="text-gray-900">{{ $detailPiutang->pengiriman->tanggal_pengiriman ? \Carbon\Carbon::parse($detailPiutang->pengiriman->tanggal_pengiriman)->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Klien:</span>
                            <span class="text-gray-900">{{ $detailPiutang->pengiriman->klien->nama }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">No HP Klien:</span>
                            <span class="text-gray-900">{{ $detailPiutang->pengiriman->klien->no_hp ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Informasi Tambahan -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center text-lg">
                        <i class="fas fa-info-circle mr-2 text-purple-600"></i>
                        Informasi Tambahan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Dibuat:</span>
                            <span class="text-gray-900">{{ $detailPiutang->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Terakhir Update:</span>
                            <span class="text-gray-900">{{ $detailPiutang->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex justify-end space-x-3 px-6 pb-6 pt-4 border-t border-gray-200">
                <button type="button" wire:click="closeDetailModal" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-violet-600 text-white rounded-lg hover:from-purple-700 hover:to-violet-700 transition-all shadow-md font-medium">
                    <i class="fas fa-times mr-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
