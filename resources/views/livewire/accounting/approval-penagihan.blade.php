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
    <div class="bg-white border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <div>
                                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-home"></i>
                                    <span class="sr-only">Home</span>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-300 mr-4"></i>
                                <span class="text-gray-500 text-sm">Accounting</span>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-300 mr-4"></i>
                                <span class="text-gray-900 text-sm font-medium">Approval Penagihan</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Welcome Banner --}}
    <x-welcome-banner
        title="Approval Penagihan"
        subtitle="Buat invoice dan verifikasi penagihan"
        icon="fas fa-file-invoice-dollar"
    />

    {{-- Pengiriman Without Invoice Section --}}
    @if(isset($pengirimansWithoutInvoice) && $pengirimansWithoutInvoice->count() > 0)
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
                                    <div class="text-xs text-gray-500">PO: {{ $pengiriman->purchaseOrder->no_po ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $pengiriman->purchaseOrder->klien->nama ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $pengiriman->purchaseOrder->klien->cabang ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</div>
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
                    {{ $pengirimansWithoutInvoice->links() }}
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
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

                {{-- Status Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-1 text-gray-400"></i>
                        Status Approval
                    </label>
                    <select
                        wire:model.live="statusFilter"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="staff_approved">Staff Approved</option>
                        <option value="manager_approved">Manager Approved</option>
                        <option value="completed">Completed</option>
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
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Invoice & Approval</h3>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($approval->invoice->total_amount, 0, ',', '.') }}</div>
                                @if($approval->invoice->discount_amount > 0)
                                    <div class="text-xs text-red-600">Diskon: Rp {{ number_format($approval->invoice->discount_amount, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($approval->status === 'pending')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </span>
                                @elseif($approval->status === 'staff_approved')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-user-check mr-1"></i> Staff Approved
                                    </span>
                                @elseif($approval->status === 'manager_approved')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        <i class="fas fa-user-tie mr-1"></i> Manager Approved
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Completed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button
                                    wire:click="showDetail({{ $approval->id }})"
                                    class="inline-flex items-center px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    <i class="fas fa-eye mr-1"></i>
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
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
                {{ $approvals->links() }}
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
                                <p class="font-semibold text-lg">Rp {{ number_format($selectedData->total_harga_kirim, 0, ',', '.') }}</p>
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
                            <h5 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-percent mr-2 text-yellow-600"></i>
                                Refraksi
                            </h5>

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
                                        placeholder="{{ ($invoiceForm['refraksi_type'] ?? 'qty') === 'qty' ? 'Contoh: 1 untuk 1%' : 'Contoh: 40 untuk Rp 40/kg' }}"
                                    />
                                    @error('invoiceForm.refraksi_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="text-xs text-gray-600 bg-white p-3 rounded border border-yellow-100">
                                <p class="font-semibold mb-1">Keterangan:</p>
                                @if(($invoiceForm['refraksi_type'] ?? 'qty') === 'qty')
                                    <p>• <strong>Refraksi Qty:</strong> Potongan berdasarkan persentase quantity</p>
                                    <p>• Contoh: 1% dari 5000 kg = 50 kg refraksi → menjadi 4950 kg</p>
                                @else
                                    <p>• <strong>Refraksi Rupiah:</strong> Potongan harga per kilogram</p>
                                    <p>• Contoh: Rp 40/kg dari 5000 kg = Rp 200.000 potongan</p>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Diskon Tambahan (Rp)</label>
                            <input
                                type="number"
                                wire:model="invoiceForm.discount_amount"
                                min="0"
                                step="0.01"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea
                                wire:model="invoiceForm.notes"
                                rows="2"
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
                                                Potongan: Rp {{ number_format($selectedData->invoice->refraksi_amount, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    @else
                                        <div class="text-xs space-y-1">
                                            <p class="text-gray-600">
                                                <span class="font-medium">Tipe:</span> Refraksi Rupiah (Rp {{ number_format($selectedData->invoice->refraksi_value, 0, ',', '.') }}/kg)
                                            </p>
                                            <p class="text-gray-600">
                                                <span class="font-medium">Qty:</span> {{ number_format($selectedData->invoice->qty_before_refraksi, 2, ',', '.') }} kg
                                            </p>
                                            <p class="text-red-600 font-semibold">
                                                Potongan: Rp {{ number_format($selectedData->invoice->refraksi_amount, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">Rp {{ number_format($selectedData->invoice->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">PPN ({{ $selectedData->invoice->tax_percentage }}%):</span>
                                <span class="font-medium">Rp {{ number_format($selectedData->invoice->tax_amount, 0, ',', '.') }}</span>
                            </div>
                            @if($selectedData->invoice->discount_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Diskon Tambahan:</span>
                                    <span class="font-medium text-red-600">- Rp {{ number_format($selectedData->invoice->discount_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="border-t pt-2 flex justify-between">
                                <span class="font-semibold text-gray-900">Total:</span>
                                <span class="font-bold text-lg text-green-600">Rp {{ number_format($selectedData->invoice->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Edit Refraksi Section --}}
                        @if($selectedData->status !== 'completed')
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
                                            placeholder="{{ ($invoiceForm['refraksi_type'] ?? 'qty') === 'qty' ? '1 untuk 1%' : '40 untuk Rp 40/kg' }}"
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
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full {{ $selectedData->staff_approved_at ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center mr-3">
                                        <i class="fas {{ $selectedData->staff_approved_at ? 'fa-check text-green-600' : 'fa-user text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Staff</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $selectedData->staff ? $selectedData->staff->nama : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($selectedData->staff_approved_at)
                                        <p class="text-xs text-green-600 font-medium">Approved</p>
                                        <p class="text-xs text-gray-500">{{ $selectedData->staff_approved_at->format('d M Y H:i') }}</p>
                                    @else
                                        <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-700 rounded">Pending</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full {{ $selectedData->manager_approved_at ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center mr-3">
                                        <i class="fas {{ $selectedData->manager_approved_at ? 'fa-check text-green-600' : 'fa-user-tie text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Manager Keuangan</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $selectedData->manager ? $selectedData->manager->nama : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($selectedData->manager_approved_at)
                                        <p class="text-xs text-green-600 font-medium">Approved</p>
                                        <p class="text-xs text-gray-500">{{ $selectedData->manager_approved_at->format('d M Y H:i') }}</p>
                                    @else
                                        <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-700 rounded">Pending</span>
                                    @endif
                                </div>
                            </div>

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

                    {{-- Notes Input --}}
                    @if($selectedData->status !== 'completed')
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
                    @if($selectedData->status !== 'completed')
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
