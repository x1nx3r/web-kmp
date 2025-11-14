<div>
    @if($showDetailModal && $detailPiutang)
    <div class="fixed inset-0 bg-green-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Detail Catatan Piutang
                </h3>
                <button wire:click="closeDetailModal" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-6">
                <!-- Informasi Piutang -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center text-lg">
                        <i class="fas fa-info-circle mr-2 text-green-600"></i>
                        Informasi Piutang
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">No. Piutang:</span>
                            <span class="text-gray-900 font-semibold">{{ $detailPiutang->no_piutang }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Supplier:</span>
                            <span class="text-gray-900 font-semibold">{{ $detailPiutang->supplier->nama }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Tanggal Piutang:</span>
                            <span class="text-gray-900">{{ \Carbon\Carbon::parse($detailPiutang->tanggal_piutang)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg">
                            <span class="font-medium text-gray-600">Tanggal Jatuh Tempo:</span>
                            <span class="text-gray-900">{{ \Carbon\Carbon::parse($detailPiutang->tanggal_jatuh_tempo)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-white rounded-lg md:col-span-2">
                            <span class="font-medium text-gray-600">Status:</span>
                            <span>
                                @if($detailPiutang->status == 'belum_lunas')
                                    <span class="px-3 py-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                        <i class="fas fa-times-circle mr-1"></i>Belum Lunas
                                    </span>
                                @elseif($detailPiutang->status == 'cicilan')
                                    <span class="px-3 py-1.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        <i class="fas fa-clock mr-1"></i>Cicilan
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                        <i class="fas fa-check-circle mr-1"></i>Lunas
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Keuangan -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200 shadow-sm">
                        <p class="text-sm text-blue-700 font-medium mb-1 flex items-center">
                            <i class="fas fa-wallet mr-2"></i>Jumlah Piutang
                        </p>
                        <p class="text-2xl font-bold text-blue-900 mt-1">Rp {{ number_format($detailPiutang->jumlah_piutang, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 border border-green-200 shadow-sm">
                        <p class="text-sm text-green-700 font-medium mb-1 flex items-center">
                            <i class="fas fa-check-double mr-2"></i>Jumlah Dibayar
                        </p>
                        <p class="text-2xl font-bold text-green-900 mt-1">Rp {{ number_format($detailPiutang->jumlah_dibayar, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-5 border border-orange-200 shadow-sm">
                        <p class="text-sm text-orange-700 font-medium mb-1 flex items-center">
                            <i class="fas fa-hourglass-half mr-2"></i>Sisa Piutang
                        </p>
                        <p class="text-2xl font-bold text-orange-900 mt-1">Rp {{ number_format($detailPiutang->sisa_piutang, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Keterangan -->
                @if($detailPiutang->keterangan)
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-comment-alt mr-2 text-purple-600"></i>
                        Keterangan
                    </h4>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $detailPiutang->keterangan }}</p>
                </div>
                @endif

                <!-- Bukti Transaksi -->
                @if($detailPiutang->bukti_transaksi)
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-5 border border-indigo-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-paperclip mr-2 text-indigo-600"></i>
                        Bukti Transaksi
                    </h4>
                    <a href="{{ Storage::url($detailPiutang->bukti_transaksi) }}" target="_blank" class="inline-flex items-center px-4 py-2.5 bg-white border border-indigo-300 rounded-lg text-indigo-600 hover:bg-indigo-50 transition-colors text-sm font-medium shadow-sm">
                        <i class="fas fa-file-alt mr-2"></i>Lihat Bukti Transaksi
                    </a>
                </div>
                @endif

                <!-- Riwayat Pembayaran -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center text-lg">
                        <i class="fas fa-history mr-2 text-green-600"></i>
                        Riwayat Pembayaran
                    </h4>
                    @if($detailPiutang->pembayaran->count() > 0)
                        <div class="overflow-x-auto bg-white rounded-lg shadow">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-green-600 to-emerald-600">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">No. Pembayaran</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Jumlah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Metode</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Bukti</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($detailPiutang->pembayaran as $bayar)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $bayar->no_pembayaran }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ \Carbon\Carbon::parse($bayar->tanggal_bayar)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-bold">Rp {{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($bayar->metode_pembayaran == 'tunai')
                                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 border border-green-200">
                                                    <i class="fas fa-money-bill-wave mr-1"></i>Tunai
                                                </span>
                                            @elseif($bayar->metode_pembayaran == 'transfer')
                                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                                    <i class="fas fa-university mr-1"></i>Transfer
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                                                    {{ ucfirst($bayar->metode_pembayaran) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            @if($bayar->bukti_pembayaran)
                                                <a href="{{ Storage::url($bayar->bukti_pembayaran) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 bg-white rounded-lg">
                            <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                            <p class="text-sm text-gray-500">Belum ada pembayaran</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex justify-end px-6 pb-6 pt-4 border-t border-gray-200">
                <button type="button" wire:click="closeDetailModal" class="px-5 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all shadow-md font-medium">
                    <i class="fas fa-times mr-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
