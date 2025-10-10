<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice text-indigo-600 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Riwayat Penawaran</h1>
                        <p class="text-gray-600 text-sm">Daftar semua penawaran yang telah dibuat</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('penawaran.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Buat Penawaran Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6">
        {{-- Filters and Search --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Search --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-gray-400"></i>
                        Cari Penawaran
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Nomor penawaran, nama klien, atau cabang..."
                    >
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-filter mr-1 text-gray-400"></i>
                        Filter Status
                    </label>
                    <select
                        wire:model.live="statusFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Semua Status ({{ $statusCounts['all'] }})</option>
                        <option value="draft">Draft ({{ $statusCounts['draft'] }})</option>
                        <option value="menunggu_verifikasi">Menunggu Verifikasi ({{ $statusCounts['menunggu_verifikasi'] }})</option>
                        <option value="disetujui">Disetujui ({{ $statusCounts['disetujui'] }})</option>
                        <option value="ditolak">Ditolak ({{ $statusCounts['ditolak'] }})</option>
                        <option value="expired">Expired ({{ $statusCounts['expired'] }})</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort mr-1 text-gray-400"></i>
                        Urutkan
                    </label>
                    <select
                        wire:model.live="sortBy"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="tanggal_desc">Tanggal (Terbaru)</option>
                        <option value="tanggal_asc">Tanggal (Terlama)</option>
                        <option value="margin_desc">Margin (Tertinggi)</option>
                        <option value="margin_asc">Margin (Terendah)</option>
                        <option value="total_desc">Total (Tertinggi)</option>
                        <option value="total_asc">Total (Terendah)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Penawaran List --}}
        <div class="space-y-4">
            @forelse($penawaranList as $penawaran)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-alt text-indigo-600 text-xl"></i>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-3">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $penawaran->nomor_penawaran }}</h3>
                                        @if($penawaran->status === 'draft')
                                            <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">
                                                <i class="fas fa-pencil-alt mr-1"></i>
                                                Draft
                                            </span>
                                        @elseif($penawaran->status === 'menunggu_verifikasi')
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                                <i class="fas fa-clock mr-1"></i>
                                                Menunggu Verifikasi
                                            </span>
                                        @elseif($penawaran->status === 'disetujui')
                                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Disetujui
                                            </span>
                                        @elseif($penawaran->status === 'ditolak')
                                            <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Ditolak
                                            </span>
                                        @elseif($penawaran->status === 'expired')
                                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                                                <i class="fas fa-hourglass-end mr-1"></i>
                                                Expired
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
                                        <span>
                                            <i class="far fa-calendar mr-1"></i>
                                            {{ $penawaran->tanggal_penawaran->format('d M Y') }}
                                        </span>
                                        <span>
                                            <i class="fas fa-user mr-1"></i>
                                            {{ $penawaran->createdBy->nama }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600">Total Revenue</div>
                                <div class="text-xl font-bold text-green-600">
                                    Rp {{ number_format($penawaran->total_revenue, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Margin: <span class="font-semibold">{{ number_format($penawaran->margin_percentage, 1) }}%</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Profit: <span class="font-semibold text-green-600">Rp {{ number_format($penawaran->total_profit, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Client Info --}}
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-building text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $penawaran->klien->nama }}</div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $penawaran->klien->cabang }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">
                                    {{ $penawaran->details->count() }} material{{ $penawaran->details->count() > 1 ? 's' : '' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $penawaran->details->unique('supplier_id')->count() }} supplier{{ $penawaran->details->unique('supplier_id')->count() > 1 ? 's' : '' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Materials Table --}}
                    <div class="p-4">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-cubes mr-2 text-purple-600"></i>
                            Daftar Bahan Baku ({{ $penawaran->details->count() }} item)
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bahan Baku</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga Klien</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga Supplier</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Margin</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($penawaran->details as $detail)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-3">
                                                <div class="font-medium text-gray-900">{{ $detail->nama_material }}</div>
                                                <div class="text-xs text-gray-500">per {{ $detail->satuan }}</div>
                                            </td>
                                            <td class="px-3 py-3 font-medium text-gray-900">
                                                {{ number_format($detail->quantity, 0, ',', '.') }} {{ $detail->satuan }}
                                            </td>
                                            <td class="px-3 py-3">
                                                <span class="text-green-700 font-medium">
                                                    Rp {{ number_format($detail->harga_klien, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="font-medium text-gray-900">{{ $detail->supplier->nama }}</div>
                                                @if($detail->supplier->picPurchasing)
                                                    <div class="text-xs text-gray-500">
                                                        <i class="fas fa-user-tie mr-1"></i>
                                                        PIC: {{ $detail->supplier->picPurchasing->nama }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3">
                                                <span class="text-red-700 font-medium">
                                                    Rp {{ number_format($detail->harga_supplier, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="flex items-center">
                                                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $detail->margin_percentage >= 20 ? 'bg-green-100 text-green-800' : ($detail->margin_percentage >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                        {{ number_format($detail->margin_percentage, 1) }}%
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-3 text-right">
                                                <div class="font-medium text-gray-900">
                                                    Rp {{ number_format($detail->subtotal_revenue, 0, ',', '.') }}
                                                </div>
                                                <div class="text-xs text-green-600">
                                                    +Rp {{ number_format($detail->subtotal_profit, 0, ',', '.') }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Footer Summary --}}
                    <div class="bg-gray-50 border-t border-gray-200 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex space-x-6 text-sm">
                                <div>
                                    <span class="text-gray-600">Total Biaya:</span>
                                    <span class="font-semibold text-red-700 ml-2">
                                        Rp {{ number_format($penawaran->total_cost, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Total Keuntungan:</span>
                                    <span class="font-semibold text-green-700 ml-2">
                                        Rp {{ number_format($penawaran->total_profit, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            @if($penawaran->status === 'menunggu_verifikasi')
                                <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                    <i class="fas fa-check mr-2"></i>
                                    Verifikasi
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-inbox text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Penawaran</h3>
                        <p class="text-gray-600 mb-6">
                            @if($search || $statusFilter)
                                Tidak ditemukan penawaran dengan kriteria pencarian yang Anda masukkan.
                            @else
                                Belum ada penawaran yang dibuat. Mulai dengan membuat penawaran baru.
                            @endif
                        </p>
                        @if($search || $statusFilter)
                            <button wire:click="$set('search', ''); $set('statusFilter', '')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                                <i class="fas fa-redo mr-2"></i>
                                Reset Filter
                            </button>
                        @else
                            <a href="{{ route('penawaran.create') }}" class="inline-block px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Buat Penawaran Baru
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($penawaranList->hasPages())
            <div class="mt-6">
                {{ $penawaranList->links() }}
            </div>
        @endif
    </div>
</div>
