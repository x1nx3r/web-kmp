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
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($totalPiutang, 2, ',', '.') }}</h3>
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
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($totalDibayar, 2, ',', '.') }}</h3>
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
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($totalSisa, 2, ',', '.') }}</h3>
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

    {{-- Export Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-4 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-file-export text-blue-600 text-xl mr-3"></i>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Export Laporan</h3>
                    <p class="text-xs text-gray-500">Cetak laporan piutang per pabrik/klien</p>
                </div>
            </div>
            <button wire:click="openExportModal"
                class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-file-pdf"></i>
                <span class="font-medium">Cetak PDF per Pabrik</span>
            </button>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-filter mr-1"></i> Status Pembayaran
                </label>
                <select wire:model.live="statusFilter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="all">Semua Status</option>
                    <option value="belum_bayar">Belum Bayar</option>
                    <option value="cicilan">Cicilan</option>
                    <option value="lunas">Lunas</option>
                    <option value="overdue">Jatuh Tempo</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt mr-1"></i> Bulan
                </label>
                <select wire:model.live="bulanFilter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Bulan</option>
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-1"></i> Tahun
                </label>
                <select wire:model.live="tahunFilter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Tahun</option>
                    @for($year = date('Y'); $year >= 2020; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Bayar</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($piutangs as $piutang)
                        @php
                            $dueDate = \Carbon\Carbon::parse($piutang->due_date);
                            $isOverdue = $dueDate->lt(now());
                            $hariTerlambat = $isOverdue ? (int) $dueDate->diffInDays(now()) : 0;
                            $hariMenjelang = !$isOverdue ? (int) now()->diffInDays($dueDate) : 0;
                            $totalPaid = $piutang->pembayaranPabrik->sum('jumlah_bayar');
                            $sisaPiutang = $piutang->total_amount - $totalPaid;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $sisaPiutang <= 0 ? 'bg-green-50' : '' }}">
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
                                @if($sisaPiutang <= 0)
                                    <div class="text-sm text-green-600 font-medium">-</div>
                                @elseif($isOverdue)
                                    <div class="text-sm font-bold {{ $hariTerlambat > 7 ? 'text-red-600' : 'text-orange-600' }}">
                                        {{ $hariTerlambat }} hari terlambat
                                    </div>
                                @else
                                    <div class="text-sm text-blue-600">{{ $hariMenjelang }} hari lagi</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($piutang->total_amount, 2, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($sisaPiutang <= 0)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Lunas
                                    </span>
                                @elseif($totalPaid > 0)
                                    <div>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Cicilan
                                        </span>
                                        <p class="text-xs text-gray-600 mt-1">Sisa: Rp {{ number_format($sisaPiutang, 2, ',', '.') }}</p>
                                    </div>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="openDetailModal({{ $piutang->id }})"
                                        class="text-purple-600 hover:text-purple-900" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="openPembayaranModal({{ $piutang->id }})"
                                        class="text-green-600 hover:text-green-900" title="Catat Pembayaran">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
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
                    $hariTerlambat = (int) \Carbon\Carbon::parse($detailPiutang->due_date)->diffInDays(now());
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
                        <p class="text-2xl font-bold text-blue-900 mt-1">Rp {{ number_format($detailPiutang->subtotal, 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 border border-green-200 shadow-sm">
                        <p class="text-sm text-green-700 font-medium mb-1 flex items-center">
                            <i class="fas fa-percentage mr-2"></i>Pajak ({{ $detailPiutang->tax_rate }}%)
                        </p>
                        <p class="text-2xl font-bold text-green-900 mt-1">Rp {{ number_format($detailPiutang->tax, 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200 shadow-sm">
                        <p class="text-sm text-purple-700 font-medium mb-1 flex items-center">
                            <i class="fas fa-wallet mr-2"></i>Total Amount
                        </p>
                        <p class="text-2xl font-bold text-purple-900 mt-1">Rp {{ number_format($detailPiutang->total_amount, 2, ',', '.') }}</p>
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

                <!-- Riwayat Pembayaran -->
                @if($detailPiutang->pembayaranPabrik && $detailPiutang->pembayaranPabrik->count() > 0)
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 border border-green-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center text-lg">
                        <i class="fas fa-history mr-2 text-green-600"></i>
                        Riwayat Pembayaran
                    </h4>
                    <div class="space-y-3">
                        @php
                            $totalPaid = $detailPiutang->pembayaranPabrik->sum('jumlah_bayar');
                            $sisa = $detailPiutang->total_amount - $totalPaid;
                        @endphp

                        <!-- Summary Pembayaran -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="bg-white rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-600">Total Invoice</p>
                                <p class="text-lg font-bold text-blue-900">Rp {{ number_format($detailPiutang->total_amount, 2, ',', '.') }}</p>
                            </div>
                            <div class="bg-white rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-600">Terbayar</p>
                                <p class="text-lg font-bold text-green-900">Rp {{ number_format($totalPaid, 2, ',', '.') }}</p>
                            </div>
                            <div class="bg-white rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-600">Sisa</p>
                                <p class="text-lg font-bold text-orange-900">Rp {{ number_format($sisa, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        <!-- Detail Pembayaran -->
                        @foreach($detailPiutang->pembayaranPabrik as $pembayaran)
                        <div class="bg-white rounded-lg p-4 border border-green-200">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-600">No Pembayaran:</span>
                                    <p class="text-gray-900 font-semibold">{{ $pembayaran->no_pembayaran }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Tanggal:</span>
                                    <p class="text-gray-900">{{ $pembayaran->tanggal_bayar->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Jumlah:</span>
                                    <p class="text-green-900 font-bold">Rp {{ number_format($pembayaran->jumlah_bayar, 2, ',', '.') }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Metode:</span>
                                    <p class="text-gray-900 capitalize">{{ $pembayaran->metode_pembayaran }}</p>
                                </div>
                                @if($pembayaran->catatan)
                                <div class="col-span-2">
                                    <span class="font-medium text-gray-600">Catatan:</span>
                                    <p class="text-gray-700 text-sm">{{ $pembayaran->catatan }}</p>
                                </div>
                                @endif
                                @if($pembayaran->bukti_pembayaran)
                                <div class="col-span-2">
                                    @php
                                        // Check if bukti_pembayaran is JSON array or single file
                                        $buktiFiles = [];
                                        try {
                                            $decoded = json_decode($pembayaran->bukti_pembayaran, true);
                                            if (is_array($decoded)) {
                                                $buktiFiles = $decoded;
                                            } else {
                                                $buktiFiles = [$pembayaran->bukti_pembayaran];
                                            }
                                        } catch (\Exception $e) {
                                            $buktiFiles = [$pembayaran->bukti_pembayaran];
                                        }
                                    @endphp
                                    
                                    @if(count($buktiFiles) > 1)
                                        <span class="text-gray-600 text-sm font-medium mb-2 block">
                                            <i class="fas fa-files mr-1"></i>Bukti Pembayaran ({{ count($buktiFiles) }} file):
                                        </span>
                                    @endif
                                    
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($buktiFiles as $index => $filePath)
                                            <a href="{{ Storage::url($filePath) }}" target="_blank"
                                                class="text-blue-600 hover:text-blue-800 text-sm flex items-center px-3 py-1 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors">
                                                <i class="fas fa-file-download mr-2"></i>
                                                @if(count($buktiFiles) > 1)
                                                    Bukti #{{ $index + 1 }}
                                                @else
                                                    Lihat Bukti Pembayaran
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                <div class="col-span-2 text-xs text-gray-500">
                                    Dicatat oleh: {{ $pembayaran->creator->nama ?? 'System' }} pada {{ $pembayaran->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
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

    {{-- Export Modal --}}
    @if($showExportModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-file-pdf text-white text-2xl"></i>
                        <div>
                            <h3 class="text-lg font-bold text-white">Export PDF Piutang</h3>
                            <p class="text-sm text-blue-100">Pilih klien untuk dicetak</p>
                        </div>
                    </div>
                    <button wire:click="closeExportModal" class="text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                @if (session()->has('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-1"></i> Pilih Klien/Pabrik
                    </label>
                    <select wire:model="selectedKlienForExport"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Klien --</option>
                        @foreach($kliens as $klien)
                            <option value="{{ $klien->id }}">{{ $klien->nama }}</option>
                        @endforeach
                    </select>
                    @error('selectedKlienForExport')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Informasi:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>PDF akan berisi semua invoice klien yang dipilih</li>
                                <li>Termasuk invoice yang sudah lunas maupun belum</li>
                                <li>Format: Landscape (A4)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button wire:click="closeExportModal"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button wire:click="exportPdfPerKlien"
                        class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-download mr-2"></i>Cetak PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Pembayaran Modal --}}
    @if($showPembayaranModal && $selectedPiutang)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Catat Pembayaran Invoice
                </h3>
                <button wire:click="closePembayaranModal" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Error Message in Modal --}}
            @if (session()->has('error'))
                <div class="mx-6 mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Modal Body --}}
            <form wire:submit.prevent="savePembayaran" class="p-6">
                @php
                    $totalPaidBefore = $selectedPiutang->pembayaranPabrik->sum('jumlah_bayar');
                    $sisaPiutang = $selectedPiutang->total_amount - $totalPaidBefore;
                @endphp
                <!-- Invoice Info -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 mb-6 border border-blue-200">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="font-medium text-gray-600">No Invoice:</label>
                            <p class="text-gray-900 font-semibold">{{ $selectedPiutang->invoice_number }}</p>
                        </div>
                        <div>
                            <label class="font-medium text-gray-600">Klien:</label>
                            <p class="text-gray-900">{{ $selectedPiutang->pengiriman->klien->nama ?? $selectedPiutang->customer_name }}</p>
                        </div>
                        <div>
                            <label class="font-medium text-gray-600">Total Invoice:</label>
                            <p class="text-lg font-bold text-blue-900">Rp {{ number_format($selectedPiutang->total_amount, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <label class="font-medium text-gray-600">Jatuh Tempo:</label>
                            <p class="text-gray-900">{{ $selectedPiutang->due_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <label class="font-medium text-gray-600">Sudah Dibayar:</label>
                            <p class="font-semibold text-green-600">Rp {{ number_format($totalPaidBefore, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <label class="font-medium text-gray-600">Sisa Piutang:</label>
                            <p class="text-lg font-bold text-orange-600">Rp {{ number_format($sisaPiutang, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="space-y-5">
                    <!-- Tanggal Bayar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_bayar"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        @error('tanggal_bayar') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Jumlah Bayar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                            <input
                                type="text"
                                id="jumlah_bayar_pabrik_display"
                                value="{{ $jumlah_bayar ? number_format($jumlah_bayar, 2, ',', '.') : '' }}"
                                placeholder="0"
                                class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                oninput="formatCurrencyJumlahBayarPabrik(this)"
                            >
                            <input type="hidden" wire:model.defer="jumlah_bayar" id="jumlah_bayar_pabrik_hidden">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Maksimal: Rp {{ number_format($sisaPiutang, 2, ',', '.') }}
                        </p>
                        @error('jumlah_bayar') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Pembayaran</label>
                        <textarea wire:model="catatan_pembayaran" rows="3"
                            placeholder="Masukkan catatan pembayaran..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
                        @error('catatan_pembayaran') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Bukti Pembayaran -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Pembayaran (Multiple Files)</label>
                        <input type="file" wire:model="bukti_pembayaran"
                            accept=".jpg,.jpeg,.png,.pdf"
                            multiple
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Format: JPG, JPEG, PNG, PDF (Total Max: 20MB)
                        </p>

                        {{-- Upload Progress Indicator --}}
                        <div wire:loading wire:target="bukti_pembayaran" class="mt-3">
                            <div class="flex items-center text-blue-600">
                                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm font-medium">Mengunggah file...</span>
                            </div>
                        </div>

                        @error('bukti_pembayaran.*') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror

                        {{-- Preview Multiple Files --}}
                        @if($bukti_pembayaran && count($bukti_pembayaran) > 0)
                            <div class="mt-3 space-y-2">
                                @php
                                    $totalSize = 0;
                                    foreach($bukti_pembayaran as $file) {
                                        $totalSize += $file->getSize();
                                    }
                                @endphp
                                
                                <div class="p-3 bg-green-50 border border-green-200 rounded-md">
                                    <p class="text-xs font-medium text-gray-700">
                                        <i class="fas fa-files text-green-600 mr-2"></i>
                                        {{ count($bukti_pembayaran) }} file dipilih
                                        <span class="text-gray-500 ml-2">
                                            (Total: {{ number_format($totalSize / 1024 / 1024, 2) }} MB)
                                        </span>
                                    </p>
                                    
                                    @if($totalSize > 20 * 1024 * 1024)
                                        <p class="text-xs text-red-600 font-semibold mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Total ukuran file melebihi 20 MB!
                                        </p>
                                    @endif
                                </div>

                                @foreach($bukti_pembayaran as $index => $file)
                                    <div class="p-3 bg-white border border-gray-200 rounded-md">
                                        <p class="text-xs font-medium text-gray-700">
                                            <i class="fas fa-file text-green-600 mr-2"></i>
                                            {{ $file->getClientOriginalName() }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Ukuran: {{ number_format($file->getSize() / 1024, 2) }} KB
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end space-x-3 mt-6 pt-5 border-t border-gray-200">
                    <button type="button" wire:click="closePembayaranModal"
                        class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="bukti_pembayaran"
                        class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all shadow-md font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="bukti_pembayaran">
                            <i class="fas fa-check mr-2"></i>Simpan Pembayaran
                        </span>
                        <span wire:loading wire:target="bukti_pembayaran" class="flex items-center">
                            <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mengunggah...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

@script
<script>
    // Format currency for jumlah bayar pabrik
    window.formatCurrencyJumlahBayarPabrik = function(displayInput) {
        let value = displayInput.value.replace(/[^0-9]/g, '');

        let hiddenInput = document.getElementById('jumlah_bayar_pabrik_hidden');
        if (hiddenInput) {
            hiddenInput.value = value;
            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        }

        if (value) {
            displayInput.value = parseInt(value).toLocaleString('id-ID');
        } else {
            displayInput.value = '';
        }
    }

    // Initialize on modal open
    document.addEventListener('livewire:navigated', function() {
        initCurrencyInputPabrik();
    });

    function initCurrencyInputPabrik() {
        const displayInput = document.getElementById('jumlah_bayar_pabrik_display');
        const hiddenInput = document.getElementById('jumlah_bayar_pabrik_hidden');

        if (displayInput && hiddenInput && hiddenInput.value) {
            displayInput.value = parseInt(hiddenInput.value).toLocaleString('id-ID');
        }
    }

    // Re-initialize when modal opens
    $wire.on('pembayaranModalOpened', () => {
        setTimeout(initCurrencyInputPabrik, 100);
    });
</script>
@endscript
