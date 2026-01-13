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

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-file-invoice-dollar text-blue-600 mr-2"></i>
            Catatan Piutang Supplier
        </h1>
        <p class="text-gray-600">Kelola catatan piutang dari supplier</p>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Piutang</p>
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($totalPiutang, 2, ',', '.') }}</h3>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
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

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Belum Lunas</p>
                    <h3 class="text-2xl font-bold mt-1">{{ $totalBelumLunas }} <span class="text-sm">Item</span></h3>
                </div>
                <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i> Pencarian
                </label>
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Cari ID piutang atau supplier...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-filter mr-1"></i> Status
                </label>
                <select wire:model.live="statusFilter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="all">Semua Status</option>
                    <option value="belum_lunas">Belum Lunas</option>
                    <option value="cicilan">Cicilan</option>
                    <option value="lunas">Lunas</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-truck mr-1"></i> Supplier
                </label>
                <select wire:model.live="supplierFilter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="all">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button wire:click="openCreateModal"
                    class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i> Tambah Piutang
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" wire:click="sortBy('id')" class="flex items-center space-x-1 focus:outline-none">
                                <span>No.</span>
                                <i class="fas {{ $sortField === 'id' ? ($sortDirection === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600') : 'fa-sort text-gray-300' }}"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" wire:click="sortBy('supplier_name')" class="flex items-center space-x-1 focus:outline-none">
                                <span>Supplier</span>
                                <i class="fas {{ $sortField === 'supplier_name' ? ($sortDirection === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600') : 'fa-sort text-gray-300' }}"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" wire:click="sortBy('tanggal_piutang')" class="flex items-center space-x-1 focus:outline-none">
                                <span>Tanggal</span>
                                <i class="fas {{ $sortField === 'tanggal_piutang' ? ($sortDirection === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600') : 'fa-sort text-gray-300' }}"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" wire:click="sortBy('jumlah_piutang')" class="flex items-center space-x-1 focus:outline-none">
                                <span>Jumlah</span>
                                <i class="fas {{ $sortField === 'jumlah_piutang' ? ($sortDirection === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600') : 'fa-sort text-gray-300' }}"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" wire:click="sortBy('jumlah_dibayar')" class="flex items-center space-x-1 focus:outline-none">
                                <span>Dibayar</span>
                                <i class="fas {{ $sortField === 'jumlah_dibayar' ? ($sortDirection === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600') : 'fa-sort text-gray-300' }}"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" wire:click="sortBy('sisa_piutang')" class="flex items-center space-x-1 focus:outline-none">
                                <span>Sisa</span>
                                <i class="fas {{ $sortField === 'sisa_piutang' ? ($sortDirection === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600') : 'fa-sort text-gray-300' }}"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" wire:click="sortBy('status')" class="flex items-center space-x-1 focus:outline-none">
                                <span>Status</span>
                                <i class="fas {{ $sortField === 'status' ? ($sortDirection === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600') : 'fa-sort text-gray-300' }}"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($piutangs as $piutang)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $loop->iteration + ($piutangs->currentPage() - 1) * $piutangs->perPage() }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $piutang->supplier->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $piutang->supplier->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $piutang->tanggal_piutang->format('d M Y') }}</div>
                                @if($piutang->tanggal_jatuh_tempo)
                                    <div class="text-xs text-gray-500">JT: {{ $piutang->tanggal_jatuh_tempo->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($piutang->jumlah_piutang, 2, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-green-600 font-medium">Rp {{ number_format($piutang->jumlah_dibayar, 2, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-orange-600 font-medium">Rp {{ number_format($piutang->sisa_piutang, 2, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($piutang->status === 'lunas')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Lunas
                                    </span>
                                @elseif($piutang->status === 'cicilan')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-coins mr-1"></i> Cicilan
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-clock mr-1"></i> Belum Lunas
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="openDetailModal({{ $piutang->id }})"
                                        class="text-blue-600 hover:text-blue-900" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($piutang->status !== 'lunas')
                                        <button wire:click="openEditModal({{ $piutang->id }})"
                                            class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif
                                    <button wire:click="openDeleteModal({{ $piutang->id }})"
                                        class="text-red-600 hover:text-red-900" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
                                    <p class="text-gray-500 text-sm">Tidak ada data catatan piutang</p>
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

    {{-- Modals --}}
    @include('livewire.accounting.catatan-piutang.create-modal')
    @include('livewire.accounting.catatan-piutang.edit-modal')
    @include('livewire.accounting.catatan-piutang.delete-modal')
    @include('livewire.accounting.catatan-piutang.detail-modal')
    @include('livewire.accounting.catatan-piutang.pembayaran-modal')
</div>
