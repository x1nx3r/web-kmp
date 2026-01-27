<div class="relative">
    {{-- Global Loading Overlay --}}
    <div wire:loading wire:target="search,customerFilter,supplierFilter" class="fixed inset-0 bg-black/10 backdrop-blur-sm z-40 flex items-center justify-center" style="display: none;">
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

    {{-- Welcome Banner --}}
    <x-welcome-banner
        title="Approval Penagihan"
        subtitle="Buat invoice dan verifikasi penagihan"
        icon="fas fa-file-invoice-dollar"
    />

    {{-- Navigation Breadcrumb --}}
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
                            <span class="text-gray-900 font-medium">Approval Penagihan</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button
                    wire:click="setActiveTab('pending')"
                    class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <i class="fas fa-clock mr-2"></i>
                    Menunggu Approval Penagihan
                </button>
                <button
                    wire:click="setActiveTab('approved')"
                    class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <i class="fas fa-check-circle mr-2"></i>
                    Approved Penagihan
                </button>
            </nav>
        </div>
    </div>

    {{-- Pengiriman Without Invoice Section - Only show in 'pending' tab --}}
    @if($activeTab === 'pending' && isset($pengirimansWithoutInvoice) && $pengirimansWithoutInvoice->count() > 0)
        <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Pengiriman Belum Dibuatkan Invoice</h3>
                    </div>
                    <span class="text-sm text-gray-600">{{ $pengirimansWithoutInvoice->total() }} pengiriman</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Pengiriman</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pengirimansWithoutInvoice as $pengiriman)
                            <tr class="hover:bg-yellow-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $pengiriman->no_pengiriman }}</div>
                                    <div class="text-xs text-gray-500">PO: {{ $pengiriman->purchaseOrder->po_number ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $pengiriman->purchaseOrder->klien->nama ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $pengiriman->purchaseOrder->klien->cabang ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 2, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button
                                        wire:click="showCreateInvoice({{ $pengiriman->id }})"
                                        class="inline-flex items-center px-3 py-2 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors"
                                    >
                                        <i class="fas fa-plus mr-1"></i>
                                        Buat Invoice
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pengirimansWithoutInvoice->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
                        <div class="flex justify-between flex-1 sm:hidden">
                            @if ($pengirimansWithoutInvoice->onFirstPage())
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                    Previous
                                </span>
                            @else
                                <button wire:click="previousPage('page_without_invoice')" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    Previous
                                </button>
                            @endif

                            @if ($pengirimansWithoutInvoice->hasMorePages())
                                <button wire:click="nextPage('page_without_invoice')" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
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
                                    <span class="font-medium">{{ $pengirimansWithoutInvoice->firstItem() }}</span>
                                    to
                                    <span class="font-medium">{{ $pengirimansWithoutInvoice->lastItem() }}</span>
                                    of
                                    <span class="font-medium">{{ $pengirimansWithoutInvoice->total() }}</span>
                                    results
                                </p>
                            </div>

                            <div>
                                <span class="relative z-0 inline-flex rounded-md shadow-sm">
                                    @if ($pengirimansWithoutInvoice->onFirstPage())
                                        <span aria-disabled="true" aria-label="Previous">
                                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </span>
                                    @else
                                        <button wire:click="previousPage('page_without_invoice')" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Previous">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endif

                                    @foreach ($pengirimansWithoutInvoice->links()->elements[0] as $page => $url)
                                        @if ($page == $pengirimansWithoutInvoice->currentPage())
                                            <span aria-current="page">
                                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-green-600 border border-green-600 cursor-default leading-5">{{ $page }}</span>
                                            </span>
                                        @elseif (is_string($page))
                                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">...</span>
                                        @else
                                            <button wire:click="gotoPage({{ $page }}, 'page_without_invoice')" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="Go to page {{ $page }}">
                                                {{ $page }}
                                            </button>
                                        @endif
                                    @endforeach

                                    @if ($pengirimansWithoutInvoice->hasMorePages())
                                        <button wire:click="nextPage('page_without_invoice')" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Next">
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
    @endif

    {{-- Search and Filter Section --}}
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter text-green-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Filter & Pencarian</h3>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- Search Input --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-gray-400"></i>
                        Pencarian
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.500ms="search"
                        placeholder="Cari nomor pengiriman atau invoice..."
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                {{-- Customer Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1 text-gray-400"></i>
                        Customer
                    </label>
                    <select
                        wire:model.live="customerFilter"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">Semua Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer }}">{{ $customer }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Supplier Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-truck mr-1 text-gray-400"></i>
                        Supplier
                    </label>
                    <select
                        wire:model.live="supplierFilter"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">Semua Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier }}">{{ $supplier }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval List with Invoice --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice text-purple-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        @if($activeTab === 'approved')
                            Invoice Selesai
                        @else
                            Invoice Menunggu Approval
                        @endif
                    </h3>
                </div>
                <span class="text-sm text-gray-600">Total: {{ $approvals->total() }} invoice</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengiriman</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th> --}}
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($approvals as $approval)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $approval->invoice->invoice_number }}</div>
                                <div class="text-xs text-gray-500">{{ $approval->invoice->invoice_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $approval->pengiriman->no_pengiriman }}</div>
                                <div class="text-xs text-gray-500">{{ $approval->pengiriman->tanggal_kirim->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $approval->invoice->customer_name }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($approval->invoice->customer_address, 40) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $suppliers = $approval->pengiriman->pengirimanDetails->pluck('bahanBakuSupplier.supplier.nama')->filter()->unique();
                                @endphp
                                @if($suppliers->count() > 0)
                                    <div class="text-sm font-medium text-gray-900">{{ $suppliers->first() }}</div>
                                    @if($suppliers->count() > 1)
                                        <div class="text-xs text-gray-500">+{{ $suppliers->count() - 1 }} lainnya</div>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-400 italic">-</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $products = $approval->pengiriman->pengirimanDetails->pluck('bahanBakuSupplier.nama')->filter();
                                @endphp
                                @if($products->count() > 0)
                                    <div class="text-sm font-medium text-gray-900">{{ $products->first() }}</div>
                                    @if($products->count() > 1)
                                        <div class="text-xs text-gray-500">+{{ $products->count() - 1 }} produk lainnya</div>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-400 italic">-</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($approval->pengiriman->pengirimanDetails->sum('qty_kirim'), 2, ',', '.') }} kg</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($approval->invoice->total_amount, 2, ',', '.') }}</div>
                            </td>
                            {{-- <td class="px-6 py-4 whitespace-nowrap">
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
                            </td> --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($activeTab === 'pending')
                                    <a href="{{ route('accounting.approval-penagihan.detail', $approval->id) }}"
                                       class="inline-flex items-center px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-eye mr-1"></i>
                                        Detail & Approve
                                    </a>
                                @else
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('accounting.approval-penagihan.view', $approval->id) }}"
                                           class="inline-flex items-center px-3 py-2 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                            <i class="fas fa-file-alt mr-1"></i>
                                            Lihat Detail
                                        </a>
                                        <a href="{{ route('accounting.approval-penagihan.edit', $approval->id) }}"
                                           class="inline-flex items-center px-3 py-2 text-xs font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition-colors">
                                            <i class="fas fa-edit mr-1"></i>
                                            Edit
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
                                    <p class="text-gray-500 text-sm">Belum ada invoice yang dibuat</p>
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
                            <button wire:click="previousPage('page_approval')" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                Previous
                            </button>
                        @endif

                        @if ($approvals->hasMorePages())
                            <button wire:click="nextPage('page_approval')" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
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
                                    <button wire:click="previousPage('page_approval')" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Previous">
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
                                        <button wire:click="gotoPage({{ $page }}, 'page_approval')" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="Go to page {{ $page }}">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($approvals->hasMorePages())
                                    <button wire:click="nextPage('page_approval')" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Next">
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

    {{-- Create Invoice Modal --}}
    @if($showCreateInvoiceModal && $selectedData)
        <div class="fixed inset-0 bg-green-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">Buat Invoice Penagihan</h3>
                    <button wire:click="closeModal" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-6 bg-green-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-3">Informasi Pengiriman</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">No. Pengiriman:</p>
                                <p class="font-medium">{{ $selectedData->no_pengiriman }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Tanggal:</p>
                                <p class="font-medium">{{ $selectedData->tanggal_kirim->format('d M Y') }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-gray-500">Total Harga:</p>
                                <p class="font-semibold text-lg">Rp {{ number_format($selectedData->total_harga_kirim, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Customer <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="invoiceForm.customer_name"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            />
                            @error('invoiceForm.customer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat Customer <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                wire:model="invoiceForm.customer_address"
                                rows="2"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            ></textarea>
                            @error('invoiceForm.customer_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                                <input
                                    type="text"
                                    wire:model="invoiceForm.customer_phone"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input
                                    type="email"
                                    wire:model="invoiceForm.customer_email"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>
                        </div>

                        {{-- Refraksi Section --}}
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h5 class="font-semibold text-gray-900 mb-3 flex items-center justify-between">
                                <span>
                                    <i class="fas fa-percent mr-2 text-yellow-600"></i>
                                    Refraksi
                                </span>
                                @if($selectedData->approvalPembayaran && $selectedData->approvalPembayaran->refraksi_value > 0)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Dari Pembayaran
                                    </span>
                                @endif
                            </h5>

                            @if($selectedData->approvalPembayaran && $selectedData->approvalPembayaran->refraksi_value > 0)
                                <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                                    <p class="text-blue-900 font-medium mb-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Refraksi diambil dari Approval Pembayaran
                                    </p>
                                    <p class="text-blue-700 text-xs">
                                        Tipe: <strong>
                                            @if($selectedData->approvalPembayaran->refraksi_type === 'qty')
                                                Qty (%)
                                            @elseif($selectedData->approvalPembayaran->refraksi_type === 'rupiah')
                                                Rupiah (Rp/kg)
                                            @elseif($selectedData->approvalPembayaran->refraksi_type === 'lainnya')
                                                Lainnya (Manual)
                                            @else
                                                {{ ucfirst($selectedData->approvalPembayaran->refraksi_type) }}
                                            @endif
                                        </strong>,
                                        Nilai: <strong>{{ number_format($selectedData->approvalPembayaran->refraksi_value, 2, ',', '.') }}</strong>
                                    </p>

                                    @if($selectedData->approvalPembayaran->histories->where('notes', '!=', null)->count() > 0)
                                        <div class="mt-2 pt-2 border-t border-blue-300">
                                            <p class="text-blue-900 font-medium text-xs mb-1">
                                                <i class="fas fa-comments mr-1"></i>
                                                Catatan dari Pembayaran:
                                            </p>
                                            @foreach($selectedData->approvalPembayaran->histories->where('notes', '!=', null) as $history)
                                                <div class="bg-white rounded p-2 mb-1 text-xs">
                                                    <p class="text-gray-600">
                                                        <span class="font-medium text-gray-800">{{ $history->user->nama ?? 'Unknown' }}</span>
                                                        <span class="text-gray-400">({{ ucfirst($history->role) }})</span>
                                                        - {{ $history->created_at->format('d M Y H:i') }}
                                                    </p>
                                                    <p class="text-gray-700 mt-1">{{ $history->notes }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4 mb-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipe Refraksi <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        wire:model="invoiceForm.refraksi_type"
                                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    >
                                        <option value="qty">Refraksi Qty (%)</option>
                                        <option value="rupiah">Refraksi Rupiah (Rp/kg)</option>
                                        <option value="lainnya">Refraksi Lainnya (Manual)</option>
                                    </select>
                                    @error('invoiceForm.refraksi_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nilai Refraksi <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        wire:model="invoiceForm.refraksi_value"
                                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        placeholder="{{ ($invoiceForm['refraksi_type'] ?? 'qty') === 'qty' ? 'Contoh: 1 untuk 1%' : (($invoiceForm['refraksi_type'] ?? 'qty') === 'rupiah' ? 'Contoh: 40 untuk Rp 40/kg' : 'Contoh: 500000 untuk Rp 500.000') }}"
                                    />
                                    @error('invoiceForm.refraksi_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="text-xs text-gray-600 bg-white p-3 rounded border border-yellow-100">
                                <p class="font-semibold mb-1">Keterangan:</p>
                                @if(($invoiceForm['refraksi_type'] ?? 'qty') === 'qty')
                                    <p>• <strong>Refraksi Qty:</strong> Potongan berdasarkan persentase quantity</p>
                                    <p>• Contoh: 1% dari 5000 kg = 50 kg refraksi → menjadi 4950 kg</p>
                                @elseif(($invoiceForm['refraksi_type'] ?? 'qty') === 'rupiah')
                                    <p>• <strong>Refraksi Rupiah:</strong> Potongan harga per kilogram</p>
                                    <p>• Contoh: Rp 40/kg × 5000 kg = Rp 200.000 potongan</p>
                                @else
                                    <p>• <strong>Refraksi Lainnya:</strong> Masukkan nominal total potongan secara manual</p>
                                    <p>• Contoh: Rp 500.000 langsung menjadi total potongan</p>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center justify-between text-sm font-medium text-gray-700 mb-2">
                                <span>Catatan</span>
                                @if($selectedData->approvalPembayaran && $selectedData->approvalPembayaran->histories->where('notes', '!=', null)->count() > 0)
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">
                                        <i class="fas fa-comment-dots mr-1"></i>
                                        Ada catatan dari pembayaran
                                    </span>
                                @endif
                            </label>
                            <textarea
                                wire:model="invoiceForm.notes"
                                rows="3"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Catatan tambahan untuk invoice..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button
                        wire:click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="createInvoice"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="createInvoice">
                            <i class="fas fa-save mr-1"></i>
                            Buat Invoice
                        </span>
                        <span wire:loading wire:target="createInvoice">
                            <i class="fas fa-spinner fa-spin mr-1"></i>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Modal - Show Invoice & Approval --}}
    @if($showDetailModal && $selectedData)
        <div class="fixed inset-0 bg-green-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">Detail Invoice & Approval</h3>
                    <button wire:click="closeModal" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Invoice Details --}}
                    <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-file-invoice mr-2 text-green-600"></i>
                            Invoice Details
                        </h4>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500">Invoice Number</p>
                                <p class="font-semibold text-gray-900">{{ $selectedData->invoice->invoice_number }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Invoice Date</p>
                                <p class="font-medium text-gray-900">{{ $selectedData->invoice->invoice_date->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Due Date</p>
                                <p class="font-medium text-gray-900">{{ $selectedData->invoice->due_date->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Customer</p>
                                <p class="font-medium text-gray-900">{{ $selectedData->invoice->customer_name }}</p>
                            </div>
                        </div>

                        {{-- Invoice Calculation --}}
                        <div class="bg-white rounded-lg p-4 space-y-2">
                            {{-- Refraksi Info --}}
                            @if($selectedData->invoice->refraksi_value > 0)
                                <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                    <p class="text-xs font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-percent mr-1"></i>
                                        Informasi Refraksi
                                    </p>
                                    @if($selectedData->invoice->refraksi_type === 'qty')
                                        <div class="text-xs space-y-1">
                                            <p class="text-gray-600">
                                                <span class="font-medium">Tipe:</span> Refraksi Qty ({{ $selectedData->invoice->refraksi_value }}%)
                                            </p>
                                            <p class="text-gray-600">
                                                <span class="font-medium">Qty Awal:</span> {{ number_format($selectedData->invoice->qty_before_refraksi, 2, ',', '.') }} kg
                                            </p>
                                            <p class="text-gray-600">
                                                <span class="font-medium">Qty Setelah Refraksi:</span> {{ number_format($selectedData->invoice->qty_after_refraksi, 2, ',', '.') }} kg
                                            </p>
                                            <p class="text-red-600 font-semibold">
                                                Potongan: Rp {{ number_format($selectedData->invoice->refraksi_amount, 2, ',', '.') }}
                                            </p>
                                        </div>
                                    @elseif($selectedData->invoice->refraksi_type === 'rupiah')
                                        <div class="text-xs space-y-1">
                                            <p class="text-gray-600">
                                                <span class="font-medium">Tipe:</span> Refraksi Rupiah (Rp {{ number_format($selectedData->invoice->refraksi_value, 2, ',', '.') }}/kg)
                                            </p>
                                            <p class="text-gray-600">
                                                <span class="font-medium">Qty:</span> {{ number_format($selectedData->invoice->qty_before_refraksi, 2, ',', '.') }} kg
                                            </p>
                                            <p class="text-red-600 font-semibold">
                                                Potongan: Rp {{ number_format($selectedData->invoice->refraksi_amount, 2, ',', '.') }}
                                            </p>
                                        </div>
                                    @elseif($selectedData->invoice->refraksi_type === 'lainnya')
                                        <div class="text-xs space-y-1">
                                            <p class="text-gray-600">
                                                <span class="font-medium">Tipe:</span> Refraksi Lainnya (Manual)
                                            </p>
                                            <p class="text-gray-600">
                                                <span class="font-medium">Nilai Potongan:</span> Rp {{ number_format($selectedData->invoice->refraksi_value, 2, ',', '.') }}
                                            </p>
                                            <p class="text-red-600 font-semibold">
                                                Potongan: Rp {{ number_format($selectedData->invoice->refraksi_amount, 2, ',', '.') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">Rp {{ number_format($selectedData->invoice->subtotal, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">PPN ({{ $selectedData->invoice->tax_percentage }}%):</span>
                                <span class="font-medium">Rp {{ number_format($selectedData->invoice->tax_amount, 2, ',', '.') }}</span>
                            </div>
                            <div class="border-t pt-2 flex justify-between">
                                <span class="font-semibold text-gray-900">Total:</span>
                                <span class="font-bold text-lg text-green-600">Rp {{ number_format($selectedData->invoice->total_amount, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Edit Refraksi Section - Only in pending tab and not completed --}}
                        @if($activeTab === 'pending' && $selectedData->status !== 'completed')
                            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <h5 class="text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit Refraksi
                                </h5>

                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            Tipe Refraksi
                                        </label>
                                        <select
                                            wire:model="invoiceForm.refraksi_type"
                                            class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        >
                                            <option value="qty">Refraksi Qty (%)</option>
                                            <option value="rupiah">Refraksi Rupiah (Rp/kg)</option>
                                            <option value="lainnya">Refraksi Lainnya (Manual)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            Nilai Refraksi
                                        </label>
                                        <input
                                            type="number"
                                            wire:model="invoiceForm.refraksi_value"
                                            min="0"
                                            step="0.01"
                                            class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            placeholder="{{ ($invoiceForm['refraksi_type'] ?? 'qty') === 'qty' ? '1 untuk 1%' : (($invoiceForm['refraksi_type'] ?? 'qty') === 'rupiah' ? '40 untuk Rp 40/kg' : '500000 untuk Rp 500.000') }}"
                                        />
                                    </div>
                                </div>

                                <button
                                    wire:click="updateDiscount"
                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors"
                                >
                                    <i class="fas fa-save mr-1"></i>
                                    Update Refraksi
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Approval Status --}}
                    <div class="mb-6 bg-purple-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-check-double mr-2 text-purple-600"></i>
                            Status Approval
                        </h4>
                        <div class="space-y-3">
                            @if($selectedData->status === 'completed')
                                <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-check text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">Disetujui Oleh</p>
                                            <p class="text-xs text-gray-500">
                                                @if($selectedData->staff)
                                                    {{ $selectedData->staff->nama }} (Staff Accounting)
                                                @elseif($selectedData->manager)
                                                    {{ $selectedData->manager->nama }} (Manager Accounting)
                                                @else
                                                    -
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-green-600 font-medium">Approved</p>
                                        <p class="text-xs text-gray-500">
                                            @if($selectedData->staff_approved_at)
                                                {{ $selectedData->staff_approved_at->format('d M Y H:i') }}
                                            @elseif($selectedData->manager_approved_at)
                                                {{ $selectedData->manager_approved_at->format('d M Y H:i') }}
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

                            <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full {{ $selectedData->superadmin_approved_at ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center mr-3">
                                        <i class="fas {{ $selectedData->superadmin_approved_at ? 'fa-check text-green-600' : 'fa-user-shield text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Superadmin</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $selectedData->superadmin ? $selectedData->superadmin->nama : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($selectedData->superadmin_approved_at)
                                        <p class="text-xs text-green-600 font-medium">Approved</p>
                                        <p class="text-xs text-gray-500">{{ $selectedData->superadmin_approved_at->format('d M Y H:i') }}</p>
                                    @else
                                        <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-700 rounded">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notes Input - Only in pending tab and not completed --}}
                    @if($activeTab === 'pending' && $selectedData->status !== 'completed')
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

                <div class="sticky bottom-0 bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button
                        wire:click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Tutup
                    </button>
                    @if($activeTab === 'pending' && $selectedData->status !== 'completed')
                        <button
                            wire:click="approve"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
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
