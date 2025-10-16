<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Riwayat Order</h1>
                        <p class="text-gray-600 text-sm">Kelola dan pantau semua order klien</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('orders.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Buat Order Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6">
        {{-- Status Count Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Order</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($statusCounts['all']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Draft</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($statusCounts['draft']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Dikonfirmasi</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ number_format($statusCounts['dikonfirmasi']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Diproses</p>
                        <p class="text-2xl font-bold text-orange-600">{{ number_format($statusCounts['diproses']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cogs text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Sebagian Dikirim</p>
                        <p class="text-2xl font-bold text-purple-600">{{ number_format($statusCounts['sebagian_dikirim']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shipping-fast text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Selesai</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($statusCounts['selesai']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-double text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Dibatalkan</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($statusCounts['dibatalkan']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters and Search --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                {{-- Search --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-gray-400"></i>
                        Cari Order
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nomor order, nama klien, atau cabang..."
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Semua Status ({{ $statusCounts['all'] }})</option>
                        <option value="draft">Draft ({{ $statusCounts['draft'] }})</option>
                        <option value="dikonfirmasi">Dikonfirmasi ({{ $statusCounts['dikonfirmasi'] }})</option>
                        <option value="diproses">Diproses ({{ $statusCounts['diproses'] }})</option>
                        <option value="sebagian_dikirim">Sebagian Dikirim ({{ $statusCounts['sebagian_dikirim'] }})</option>
                        <option value="selesai">Selesai ({{ $statusCounts['selesai'] }})</option>
                        <option value="dibatalkan">Dibatalkan ({{ $statusCounts['dibatalkan'] }})</option>
                    </select>
                </div>

                {{-- Klien Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-1 text-gray-400"></i>
                        Filter Klien
                    </label>
                    <select
                        wire:model.live="klienFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Semua Klien</option>
                        @foreach($kliens as $klien)
                            <option value="{{ $klien->id }}">{{ $klien->nama }} - {{ $klien->cabang }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Priority Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-1 text-gray-400"></i>
                        Prioritas
                    </label>
                    <select
                        wire:model.live="priorityFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Semua Prioritas</option>
                        <option value="rendah">Rendah</option>
                        <option value="normal">Normal</option>
                        <option value="tinggi">Tinggi</option>
                        <option value="mendesak">Mendesak</option>
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="tanggal_desc">Tanggal (Terbaru)</option>
                        <option value="tanggal_asc">Tanggal (Terlama)</option>
                        <option value="total_desc">Total (Tertinggi)</option>
                        <option value="total_asc">Total (Terendah)</option>
                        <option value="status_asc">Status (A-Z)</option>
                        <option value="status_desc">Status (Z-A)</option>
                    </select>
                </div>
            </div>

            {{-- Clear Filters --}}
            @if($search || $statusFilter || $klienFilter || $priorityFilter || $sortBy !== 'tanggal_desc')
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <button
                        wire:click="clearFilters"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Reset Semua Filter
                    </button>
                </div>
            @endif
        </div>

        {{-- Orders Grid --}}
        <div class="space-y-6">
            @forelse($orders as $order)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Order Header --}}
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    <x-order.status-badge :status="$order->status" />
                                    <x-order.priority-badge :priority="$order->priority" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $order->no_order }}</h3>
                                    <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
                                        <span>
                                            <i class="far fa-calendar mr-1"></i>
                                            {{ $order->tanggal_order->format('d M Y') }}
                                        </span>
                                        <span>
                                            <i class="fas fa-user mr-1"></i>
                                            {{ $order->creator->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600">Total Order</div>
                                <div class="text-xl font-bold text-green-600">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $order->total_items }} item{{ $order->total_items > 1 ? 's' : '' }} | 
                                    {{ number_format($order->total_qty, 0) }} total qty
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
                                    <div class="font-medium text-gray-900">{{ $order->klien->nama }}</div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $order->klien->cabang }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">
                                    {{ $order->orderDetails->count() }} material{{ $order->orderDetails->count() > 1 ? 's' : '' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $order->orderDetails->unique('supplier_id')->count() }} supplier{{ $order->orderDetails->unique('supplier_id')->count() > 1 ? 's' : '' }}
                                </div>
                                @if($order->status !== 'draft' && $order->total_qty > 0)
                                    <div class="text-xs text-gray-500">
                                        Progress: {{ number_format(($order->orderDetails->sum('qty_shipped') / $order->total_qty) * 100, 1) }}%
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Materials Mini Table (truncated for now) --}}
                    <div class="p-4">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-cubes mr-2 text-purple-600"></i>
                            Daftar Material ({{ $order->orderDetails->count() }} item)
                        </h4>
                        <div class="text-sm text-gray-600">
                            Material details will be shown here...
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>
                                    Lihat Detail
                                </a>
                                @if($order->status === 'draft')
                                    <a href="#" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>
                                        Edit
                                    </a>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($order->status === 'draft')
                                    <button
                                        wire:click="confirmOrder({{ $order->id }})"
                                        class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        <i class="fas fa-check mr-1"></i>
                                        Konfirmasi
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $order->id }})"
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        <i class="fas fa-trash mr-1"></i>
                                        Hapus
                                    </button>
                                @elseif($order->status === 'dikonfirmasi')
                                    <button
                                        wire:click="startProcessing({{ $order->id }})"
                                        class="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        <i class="fas fa-play mr-1"></i>
                                        Mulai Proses
                                    </button>
                                @elseif(in_array($order->status, ['diproses', 'sebagian_dikirim']))
                                    <button
                                        wire:click="completeOrder({{ $order->id }})"
                                        class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        <i class="fas fa-check-double mr-1"></i>
                                        Selesaikan
                                    </button>
                                @endif

                                @if(!in_array($order->status, ['selesai', 'dibatalkan']))
                                    <button
                                        wire:click="cancelOrder({{ $order->id }})"
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        <i class="fas fa-times mr-1"></i>
                                        Batalkan
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Order</h3>
                    <p class="text-gray-600 mb-4">
                        @if($search || $statusFilter || $klienFilter || $priorityFilter)
                            Tidak ditemukan order dengan kriteria pencarian yang Anda masukkan.
                        @else
                            Mulai dengan membuat order baru untuk klien Anda.
                        @endif
                    </p>
                    @if($search || $statusFilter || $klienFilter || $priorityFilter)
                        <button
                            wire:click="clearFilters"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Reset Filter
                        </button>
                    @else
                        <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            <i class="fas fa-plus mr-2"></i>
                            Buat Order Pertama
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" wire:click="cancelDelete"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full transform transition-all">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus Order</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Apakah Anda yakin ingin menghapus order ini? Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                        <button
                            wire:click="cancelDelete"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            wire:click="deleteOrder"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors"
                        >
                            Hapus Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
