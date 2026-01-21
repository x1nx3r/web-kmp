
<div class="relative">
    {{-- Global Loading Overlay --}}
    <div wire:loading wire:target="search,statusFilter" class="fixed inset-0 bg-black/10 backdrop-blur-sm z-40 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg shadow-lg p-6 flex items-center space-x-3">
            <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-gray-700 font-medium">Memuat data...</span>
        </div>
    </div>

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

    {{-- Navigation Breadcrumb --}}

    {{-- Welcome Banner --}}
    <x-welcome-banner
        title="Approval Pembayaran"
        subtitle="Verifikasi dan approve pembayaran pengiriman"
        icon="fas fa-money-check-alt"
    />

    <div class="bg-white border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                <nav aria-label="Breadcrumb" class="w-full">
                    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        <li>
                            <a href="{{ route('dashboard') }}" class="flex items-center text-gray-400 hover:text-gray-500">
                                <i class="fas fa-home"></i>
                                <span class="sr-only">Home</span>
                            </a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span>Accounting</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span class="text-gray-900 font-medium">Approval Pembayaran</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Search and Filter Section --}}
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter text-blue-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Filter & Pencarian</h3>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Search Input --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-gray-400"></i>
                        Pencarian No. Pengiriman
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.500ms="search"
                        placeholder="Cari nomor pengiriman..."
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                {{-- Status Filter (contextual based on active tab) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-1 text-gray-400"></i>
                        Filter Status
                    </label>
                    <select
                        wire:model.live="statusFilter"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">Semua Status</option>
                        @if($activeTab === 'pending')
                            <option value="pending">Pending</option>
                        @else
                            <option value="completed">Completed</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval List --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-list text-green-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Approval Pembayaran</h3>
                </div>
                <span class="text-sm text-gray-600">Total: {{ $approvals->total() }} data</span>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button
                    wire:click="setActiveTab('pending')"
                    class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <i class="fas fa-clock mr-2"></i>
                    Pending Approval
                </button>
                <button
                    wire:click="setActiveTab('approved')"
                    class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <i class="fas fa-check-circle mr-2"></i>
                    Approved
                </button>
            </nav>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Pengiriman</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($approvals as $approval)
                        {{-- Skip if pengiriman is null (deleted) --}}
                        @if(!$approval->pengiriman)
                            @continue
                        @endif

                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $approval->pengiriman->no_pengiriman ?? '-' }}</div>
                                <div class="text-xs text-gray-500">PO: {{ $approval->pengiriman->purchaseOrder->po_number ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $approval->pengiriman->tanggal_kirim ? $approval->pengiriman->tanggal_kirim->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    Rp {{ number_format($approval->amount_after_refraksi > 0 ? $approval->amount_after_refraksi : ($approval->pengiriman->total_harga_kirim ?? 0), 2, ',', '.') }}
                                </div>
                                @if($approval->refraksi_value > 0)
                                    <div class="text-xs text-red-600">
                                        <i class="fas fa-arrow-down mr-1"></i>Refraksi: Rp {{ number_format($approval->refraksi_amount, 2, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($approval->status === 'pending')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i> Menunggu Approval
                                    </span>
                                @elseif($approval->status === 'completed')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Selesai
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <i class="fas fa-circle mr-1"></i> {{ ucfirst($approval->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs">
                                    @if($approval->status === 'completed')
                                        @if($approval->staff)
                                            <div class="flex items-center text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <span>{{ $approval->staff->nama }}</span>
                                            </div>
                                        @elseif($approval->manager)
                                            <div class="flex items-center text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <span>{{ $approval->manager->nama }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">Belum diproses</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    @if($activeTab === 'pending')
                                        {{-- Approve Button (only show in Pending tab) --}}
                                        <a
                                            href="{{ route('accounting.approval-pembayaran.approve', $approval->id) }}"
                                            class="inline-flex items-center px-3 py-2 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors"
                                        >
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approve
                                        </a>
                                    @elseif($activeTab === 'approved')
                                        {{-- Detail Button (only show in Approved tab) --}}
                                        <a
                                            href="{{ route('accounting.approval-pembayaran.detail', $approval->id) }}"
                                            class="inline-flex items-center px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                                        >
                                            <i class="fas fa-eye mr-1"></i>
                                            Detail
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
                                    @if($activeTab === 'pending')
                                        <p class="text-gray-500 text-sm">Tidak ada approval pembayaran yang menunggu persetujuan</p>
                                    @else
                                        <p class="text-gray-500 text-sm">Tidak ada approval pembayaran yang sudah disetujui</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($approvals->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
                    <div class="flex justify-between flex-1 sm:hidden">
                        @if ($approvals->onFirstPage())
                            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                Previous
                            </span>
                        @else
                            <button wire:click="previousPage" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                Previous
                            </button>
                        @endif

                        @if ($approvals->hasMorePages())
                            <button wire:click="nextPage" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                Next
                            </button>
                        @else
                            <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                Next
                            </span>
                        @endif
                    </div>

                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 leading-5">
                                Showing
                                <span class="font-medium">{{ $approvals->firstItem() }}</span>
                                to
                                <span class="font-medium">{{ $approvals->lastItem() }}</span>
                                of
                                <span class="font-medium">{{ $approvals->total() }}</span>
                                results
                            </p>
                        </div>

                        <div>
                            <span class="relative z-0 inline-flex rounded-md shadow-sm">
                                {{-- Previous Page Link --}}
                                @if ($approvals->onFirstPage())
                                    <span aria-disabled="true" aria-label="Previous">
                                        <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </span>
                                @else
                                    <button wire:click="previousPage" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Previous">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($approvals->links()->elements[0] as $page => $url)
                                    @if ($page == $approvals->currentPage())
                                        <span aria-current="page">
                                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-green-600 border border-green-600 cursor-default leading-5">{{ $page }}</span>
                                        </span>
                                    @elseif (is_string($page))
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">...</span>
                                    @else
                                        <button wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="Go to page {{ $page }}">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($approvals->hasMorePages())
                                    <button wire:click="nextPage" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Next">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @else
                                    <span aria-disabled="true" aria-label="Next">
                                        <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>
                </nav>
            </div>
        @endif
    </div>

    {{-- Approval Modal --}}
    @if($showDetailModal && $selectedPengiriman)
        <div class="fixed inset-0 bg-green-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                {{-- Modal Header --}}
                <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">Approve Pembayaran</h3>
                    <button wire:click="closeModal" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6">
                    {{-- Pengiriman Info --}}
                    <div class="mb-6 bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-shipping-fast mr-2 text-green-600"></i>
                            Informasi Pengiriman
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">No. Pengiriman</p>
                                <p class="font-medium text-gray-900">{{ $selectedPengiriman->pengiriman->no_pengiriman }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Tanggal Kirim</p>
                                <p class="font-medium text-gray-900">{{ $selectedPengiriman->pengiriman->tanggal_kirim->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">No. PO</p>
                                <p class="font-medium text-gray-900">{{ $selectedPengiriman->pengiriman->purchaseOrder->po_number ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Qty Kirim</p>
                                <p class="font-medium text-gray-900">{{ number_format($selectedPengiriman->pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Harga per Kg</p>
                                <p class="font-medium text-gray-900">Rp {{ number_format($selectedPengiriman->pengiriman->total_harga_kirim / $selectedPengiriman->pengiriman->total_qty_kirim, 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Harga</p>
                                <p class="font-semibold text-gray-900">Rp {{ number_format($selectedPengiriman->pengiriman->total_harga_kirim, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        {{-- Refraksi Info (jika sudah ada invoice) --}}
                        @if($selectedPengiriman->pengiriman->invoicePenagihan && $selectedPengiriman->pengiriman->invoicePenagihan->refraksi_value > 0)
                            <div class="mt-4 p-3 bg-purple-50 border border-purple-200 rounded">
                                <p class="text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i>
                                    Refraksi Penagihan (dari Invoice)
                                </p>
                                @if($selectedPengiriman->pengiriman->invoicePenagihan->refraksi_type === 'qty')
                                    <div class="text-xs space-y-1">
                                        <p class="text-gray-600">
                                            <span class="font-medium">Tipe:</span> Refraksi Qty ({{ $selectedPengiriman->pengiriman->invoicePenagihan->refraksi_value }}%)
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-medium">Qty Awal:</span> {{ number_format($selectedPengiriman->pengiriman->invoicePenagihan->qty_before_refraksi, 2, ',', '.') }} kg
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-medium">Qty Setelah Refraksi:</span> {{ number_format($selectedPengiriman->pengiriman->invoicePenagihan->qty_after_refraksi, 2, ',', '.') }} kg
                                        </p>
                                        <p class="text-red-600 font-semibold">
                                            Potongan: Rp {{ number_format($selectedPengiriman->pengiriman->invoicePenagihan->refraksi_amount, 2, ',', '.') }}
                                        </p>
                                    </div>
                                @else
                                    <div class="text-xs space-y-1">
                                        <p class="text-gray-600">
                                            <span class="font-medium">Tipe:</span> Refraksi Rupiah (Rp {{ number_format($selectedPengiriman->pengiriman->invoicePenagihan->refraksi_value, 2, ',', '.') }}/kg)
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-medium">Qty:</span> {{ number_format($selectedPengiriman->pengiriman->invoicePenagihan->qty_before_refraksi, 2, ',', '.') }} kg
                                        </p>
                                        <p class="text-red-600 font-semibold">
                                            Potongan: Rp {{ number_format($selectedPengiriman->pengiriman->invoicePenagihan->refraksi_amount, 2, ',', '.') }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Refraksi Pembayaran Info --}}
                        @if($selectedPengiriman->refraksi_value > 0)
                            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded">
                                <p class="text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-money-check-alt mr-1"></i>
                                    Refraksi Pembayaran
                                </p>
                                @if($selectedPengiriman->refraksi_type === 'qty')
                                    <div class="text-xs space-y-1">
                                        <p class="text-gray-600">
                                            <span class="font-medium">Tipe:</span> Refraksi Qty ({{ $selectedPengiriman->refraksi_value }}%)
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-medium">Qty Awal:</span> {{ number_format($selectedPengiriman->qty_before_refraksi, 2, ',', '.') }} kg
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-medium">Qty Setelah:</span> {{ number_format($selectedPengiriman->qty_after_refraksi, 2, ',', '.') }} kg
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-medium">Amount Awal:</span> Rp {{ number_format($selectedPengiriman->amount_before_refraksi, 2, ',', '.') }}
                                        </p>
                                        <p class="text-green-600 font-semibold">
                                            <span class="font-medium">Amount Setelah:</span> Rp {{ number_format($selectedPengiriman->amount_after_refraksi, 2, ',', '.') }}
                                        </p>
                                        <p class="text-red-600 font-semibold">
                                            Potongan: Rp {{ number_format($selectedPengiriman->refraksi_amount, 2, ',', '.') }}
                                        </p>
                                    </div>
                                @else
                                    <div class="text-xs space-y-1">
                                        <p class="text-gray-600">
                                            <span class="font-medium">Tipe:</span> Refraksi Rupiah (Rp {{ number_format($selectedPengiriman->refraksi_value, 2, ',', '.') }}/kg)
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-medium">Qty:</span> {{ number_format($selectedPengiriman->qty_before_refraksi, 2, ',', '.') }} kg
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-medium">Amount Awal:</span> Rp {{ number_format($selectedPengiriman->amount_before_refraksi, 2, ',', '.') }}
                                        </p>
                                        <p class="text-green-600 font-semibold">
                                            <span class="font-medium">Amount Setelah:</span> Rp {{ number_format($selectedPengiriman->amount_after_refraksi, 2, ',', '.') }}
                                        </p>
                                        <p class="text-red-600 font-semibold">
                                            Potongan: Rp {{ number_format($selectedPengiriman->refraksi_amount, 2, ',', '.') }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Edit Refraksi Pembayaran Section --}}
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h5 class="text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-edit mr-1"></i>
                            Edit Refraksi Pembayaran
                        </h5>
                        <p class="text-xs text-gray-600 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Refraksi ini untuk approval pembayaran, terpisah dari refraksi penagihan (invoice).
                        </p>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Tipe Refraksi
                                </label>
                                <select
                                    wire:model="refraksiForm.type"
                                    class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    @if($selectedPengiriman->status === 'completed') disabled @endif
                                >
                                    <option value="qty">Refraksi Qty (%)</option>
                                    <option value="rupiah">Refraksi Rupiah (Rp/kg)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Nilai Refraksi
                                </label>
                                <input
                                    type="number"
                                    wire:model="refraksiForm.value"
                                    min="0"
                                    step="0.01"
                                    class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    placeholder="{{ ($refraksiForm['type'] ?? 'qty') === 'qty' ? '1 untuk 1%' : '40 untuk Rp 40/kg' }}"
                                    @if($selectedPengiriman->status === 'completed') disabled @endif
                                />
                            </div>
                        </div>

                        @if($selectedPengiriman->status === 'completed')
                            <div class="text-xs text-gray-600 bg-white p-3 rounded border border-yellow-100 mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Approval sudah completed, refraksi tidak dapat diubah.
                            </div>
                        @endif

                        <button
                            wire:click="updateRefraksi"
                            wire:loading.attr="disabled"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-400"
                            @if($selectedPengiriman->status === 'completed') disabled @endif
                        >
                            <span wire:loading.remove wire:target="updateRefraksi">
                                <i class="fas fa-save mr-1"></i>
                                Update Refraksi Pembayaran
                            </span>
                            <span wire:loading wire:target="updateRefraksi">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                    {{-- Approval Status --}}
                    <div class="mb-6 bg-green-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-check-double mr-2 text-green-600"></i>
                            Status Approval
                        </h4>
                        <div class="space-y-3">
                            @if($selectedPengiriman->status === 'completed')
                                <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-check text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">Disetujui Oleh</p>
                                            <p class="text-xs text-gray-500">
                                                @if($selectedPengiriman->staff)
                                                    {{ $selectedPengiriman->staff->nama }} (Staff Accounting)
                                                @elseif($selectedPengiriman->manager)
                                                    {{ $selectedPengiriman->manager->nama }} (Manager Accounting)
                                                @else
                                                    -
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-green-600 font-medium">Approved</p>
                                        <p class="text-xs text-gray-500">
                                            @if($selectedPengiriman->staff_approved_at)
                                                {{ $selectedPengiriman->staff_approved_at->format('d M Y H:i') }}
                                            @elseif($selectedPengiriman->manager_approved_at)
                                                {{ $selectedPengiriman->manager_approved_at->format('d M Y H:i') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="p-3 bg-white rounded-lg text-center">
                                    <p class="text-sm text-yellow-600 font-medium">
                                        <i class="fas fa-clock mr-1"></i>
                                        Menunggu Approval
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Notes Input --}}
                    @if($selectedPengiriman->status !== 'completed')
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan (Opsional)
                            </label>
                            <textarea
                                wire:model="notes"
                                rows="3"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Tambahkan catatan untuk approval ini..."
                            ></textarea>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="sticky bottom-0 bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button
                        wire:click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Tutup
                    </button>
                    @if($selectedPengiriman->status !== 'completed')
                        <button
                            wire:click="approve"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="approve">
                                <i class="fas fa-check mr-1"></i>
                                Approve
                            </span>
                            <span wire:loading wire:target="approve">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Processing...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
