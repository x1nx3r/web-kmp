<div class="relative">
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
                                <a href="{{ route('accounting.approval-pembayaran') }}" class="text-gray-500 hover:text-gray-700 text-sm">Approval Pembayaran</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-300 mr-4"></i>
                                <span class="text-gray-900 text-sm font-medium">Approve</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Header with Back Button --}}
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('accounting.approval-pembayaran') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Approve Pembayaran</h1>
            </div>

            {{-- Status Badge --}}
            @if($approval)
                <span class="px-4 py-2 text-sm font-semibold rounded-full
                    @if($approval->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($approval->status === 'staff_approved') bg-blue-100 text-blue-800
                    @elseif($approval->status === 'manager_approved') bg-purple-100 text-purple-800
                    @elseif($approval->status === 'completed') bg-green-100 text-green-800
                    @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    <i class="fas fa-circle text-xs mr-1"></i>
                    {{ ucfirst($approval->status) }}
                </span>
            @endif
        </div>

        @if($approval && $pengiriman)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column: Informasi & Refraksi --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Informasi Pengiriman --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-shipping-fast text-blue-600 mr-3"></i>
                                Informasi Pengiriman
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Nomor Pengiriman</label>
                                    <p class="mt-1 text-base text-gray-900 font-semibold">{{ $pengiriman->no_pengiriman ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tanggal Kirim</label>
                                    <p class="mt-1 text-base text-gray-900">
                                        <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                        {{ $pengiriman->tanggal_kirim ? \Carbon\Carbon::parse($pengiriman->tanggal_kirim)->format('d M Y') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Total Qty Kirim</label>
                                    <p class="mt-1 text-base text-gray-900 font-semibold">{{ number_format($pengiriman->total_qty_kirim, 2, ',', '.') }} kg</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Total Harga (Original)</label>
                                    <p class="mt-1 text-lg text-gray-900 font-bold">Rp {{ number_format($pengiriman->total_harga_kirim, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            {{-- Pengiriman Details --}}
                            @if($pengiriman->pengirimanDetails && count($pengiriman->pengirimanDetails) > 0)
                                <div class="mt-6">
                                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Detail Pengiriman</h3>
                                    <div class="space-y-3">
                                        @foreach($pengiriman->pengirimanDetails as $detail)
                                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                                <div class="grid grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-gray-500">Supplier:</span>
                                                        <span class="font-medium text-gray-900">{{ $detail->bahanBakuSupplier->supplier->nama ?? '-' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Bahan Baku:</span>
                                                        <span class="font-medium text-gray-900">{{ $detail->bahanBakuSupplier->bahanBaku->nama ?? '-' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Qty:</span>
                                                        <span class="font-semibold text-gray-900">{{ number_format($detail->qty_kirim, 2, ',', '.') }} kg</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Total:</span>
                                                        <span class="font-bold text-gray-900">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Refraksi Penagihan (dari Invoice) --}}
                    @if($invoicePenagihan && $invoicePenagihan->refraksi_type)
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-sm border border-purple-200">
                            <div class="border-b border-purple-200 bg-purple-100 px-6 py-4">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-file-invoice text-purple-600 mr-3"></i>
                                    Refraksi Penagihan (Customer)
                                </h2>
                                <p class="text-sm text-purple-700 mt-1">Refraksi yang dikenakan kepada customer</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-medium text-purple-700">Jenis</label>
                                        <p class="mt-1 text-sm font-semibold">
                                            @if($invoicePenagihan->refraksi_type === 'qty')
                                                <i class="fas fa-percentage text-purple-600 mr-1"></i>Qty ({{ number_format($invoicePenagihan->refraksi_value, 2) }}%)
                                            @else
                                                <i class="fas fa-money-bill text-purple-600 mr-1"></i>Rp {{ number_format($invoicePenagihan->refraksi_value, 0, ',', '.') }}/kg
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-purple-700">Potongan</label>
                                        <p class="mt-1 text-sm text-red-600 font-bold">- Rp {{ number_format($invoicePenagihan->refraksi_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Refraksi Pembayaran --}}
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm border border-green-200">
                        <div class="border-b border-green-200 bg-green-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-hand-holding-usd text-green-600 mr-3"></i>
                                Refraksi Pembayaran (Supplier)
                            </h2>
                            <p class="text-sm text-green-700 mt-1">Refraksi yang dikenakan kepada supplier</p>
                        </div>
                        <div class="p-6">
                            @if($approval->refraksi_type)
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="text-xs font-medium text-green-700">Jenis</label>
                                        <p class="mt-1 text-sm font-semibold">
                                            @if($approval->refraksi_type === 'qty')
                                                <i class="fas fa-percentage text-green-600 mr-1"></i>Qty ({{ number_format($approval->refraksi_value, 2) }}%)
                                            @elseif($approval->refraksi_type === 'rupiah')
                                                <i class="fas fa-money-bill text-green-600 mr-1"></i>Rp {{ number_format($approval->refraksi_value, 0, ',', '.') }}/kg
                                            @else
                                                <i class="fas fa-calculator text-green-600 mr-1"></i>Refraksi Lainnya (Manual)
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-green-700">Potongan</label>
                                        <p class="mt-1 text-sm text-red-600 font-bold">- Rp {{ number_format($approval->refraksi_amount, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="text-xs font-medium text-green-700">Total Pembayaran</label>
                                        <p class="mt-1 text-lg text-green-600 font-bold">Rp {{ number_format($approval->amount_after_refraksi, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Edit Refraksi Form --}}
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-edit text-yellow-600 mr-2"></i>
                                    Edit Refraksi Pembayaran
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-2">Jenis Refraksi</label>
                                        <select wire:model="refraksiForm.type" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500">
                                            <option value="qty">Qty (%)</option>
                                            <option value="rupiah">Rupiah (Rp/kg)</option>
                                            <option value="lainnya">Refraksi Lainnya (Input Manual)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-2">
                                            Nilai Refraksi
                                            @if($refraksiForm['type'] === 'qty')
                                                <span class="text-gray-500">(dalam %)</span>
                                            @elseif($refraksiForm['type'] === 'rupiah')
                                                <span class="text-gray-500">(Rp per kg)</span>
                                            @else
                                                <span class="text-gray-500">(Total potongan dalam Rp)</span>
                                            @endif
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model="refraksiForm.value"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500"
                                            placeholder="{{ $refraksiForm['type'] === 'qty' ? 'Contoh: 2.5' : ($refraksiForm['type'] === 'rupiah' ? 'Contoh: 1000' : 'Contoh: 50000') }}"
                                        >
                                    </div>
                                </div>

                                {{-- Info berdasarkan jenis refraksi --}}
                                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                    <p class="text-xs text-blue-800 flex items-start">
                                        <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                                        <span>
                                            @if($refraksiForm['type'] === 'qty')
                                                <strong>Refraksi Qty:</strong> Potongan berdasarkan persentase dari total qty. Sistem akan menghitung potongan rupiah secara otomatis.
                                            @elseif($refraksiForm['type'] === 'rupiah')
                                                <strong>Refraksi Rupiah:</strong> Potongan harga per kilogram. Total potongan = nilai refraksi × total qty pengiriman.
                                            @else
                                                <strong>Refraksi Lainnya:</strong> Masukkan nominal potongan secara manual dalam rupiah. Nominal yang dimasukkan akan langsung menjadi total potongan pembayaran.
                                            @endif
                                        </span>
                                    </p>
                                </div>

                                <button
                                    wire:click="updateRefraksi"
                                    class="mt-4 w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors"
                                >
                                    <i class="fas fa-save mr-2"></i>
                                    Update Refraksi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Approval Actions --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-6 space-y-6">
                        {{-- Approval Card --}}
                        <div class="bg-white rounded-lg shadow-lg border border-gray-200">
                            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 rounded-t-lg">
                                <h2 class="text-lg font-bold text-white flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Approval Action
                                </h2>
                            </div>
                            <div class="p-6">
                                {{-- Approval Info --}}
                                <div class="mb-6 space-y-3">
                                    <div>
                                        <label class="text-xs font-medium text-gray-500">Current Approver</label>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">
                                            @if($approval->status === 'pending')
                                                <i class="fas fa-user text-blue-600 mr-1"></i>Staff Accounting
                                            @elseif($approval->status === 'staff_approved')
                                                <i class="fas fa-user-tie text-purple-600 mr-1"></i>Manager Keuangan (Final)
                                            @elseif($approval->status === 'completed')
                                                <i class="fas fa-check-circle text-green-600 mr-1"></i>Selesai
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>

                                    @if($approval->staff)
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">Staff</label>
                                            <p class="mt-1 text-sm text-gray-900">
                                                <i class="fas fa-check text-green-500 mr-1"></i>
                                                {{ $approval->staff->nama }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($approval->manager)
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">Manager (Final Approval)</label>
                                            <p class="mt-1 text-sm text-gray-900">
                                                <i class="fas fa-check text-green-500 mr-1"></i>
                                                {{ $approval->manager->nama }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Notes --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                    <textarea
                                        wire:model="notes"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500"
                                        placeholder="Tambahkan catatan untuk approval..."
                                    ></textarea>
                                </div>

                                {{-- Upload Bukti Pembayaran (Only for Manager) --}}
                                @php
                                    $user = Auth::user();
                                    $isManager = $user->role === 'manager_accounting' && $approval->status === 'staff_approved';
                                @endphp

                                @if($isManager)
                                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                        <label class="flex items-center text-sm font-semibold text-gray-900 mb-2">
                                            <i class="fas fa-file-upload text-blue-600 mr-2"></i>
                                            Bukti Pembayaran <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <p class="text-xs text-gray-600 mb-3">
                                            Upload bukti pembayaran dalam format JPG, PNG, atau PDF (Max: 5MB)
                                        </p>

                                        <input
                                            type="file"
                                            wire:model="buktiPembayaran"
                                            accept="image/jpeg,image/jpg,image/png,application/pdf"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer"
                                        >

                                        @error('buktiPembayaran')
                                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                        @enderror

                                        {{-- Preview --}}
                                        @if($buktiPembayaran)
                                            <div class="mt-3 p-3 bg-white border border-blue-200 rounded-md">
                                                <p class="text-xs font-medium text-gray-700 mb-2 flex items-center">
                                                    <i class="fas fa-file text-blue-600 mr-2"></i>
                                                    File dipilih: {{ $buktiPembayaran->getClientOriginalName() }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    Ukuran: {{ number_format($buktiPembayaran->getSize() / 1024, 2) }} KB
                                                </p>

                                                {{-- Image Preview --}}
                                                @if(in_array($buktiPembayaran->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']))
                                                    <div class="mt-2">
                                                        <img src="{{ $buktiPembayaran->temporaryUrl() }}" alt="Preview" class="w-full h-auto rounded border border-gray-200">
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mt-2 flex items-start">
                                            <i class="fas fa-info-circle text-blue-500 text-xs mt-0.5 mr-1"></i>
                                            <p class="text-xs text-blue-700">
                                                Bukti pembayaran wajib diupload untuk menyelesaikan approval sebagai Manager.
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Action Buttons --}}
                                @if($approval->status !== 'completed' && $approval->status !== 'rejected')
                                    <div class="space-y-3">
                                        <button
                                            wire:click="approve"
                                            wire:confirm="Apakah Anda yakin ingin menyetujui approval ini?"
                                            class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center"
                                        >
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Approve
                                        </button>
                                        <button
                                            wire:click="reject"
                                            wire:confirm="Apakah Anda yakin ingin menolak approval ini?"
                                            class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center"
                                        >
                                            <i class="fas fa-times-circle mr-2"></i>
                                            Reject
                                        </button>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-sm text-gray-500">
                                            @if($approval->status === 'completed')
                                                <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                                                <br>Approval sudah selesai
                                            @else
                                                <i class="fas fa-times-circle text-red-500 text-2xl mb-2"></i>
                                                <br>Approval ditolak
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Approval History --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                <h2 class="text-base font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-history text-gray-600 mr-2"></i>
                                    Riwayat
                                </h2>
                            </div>
                            <div class="p-4">
                                @if($approvalHistory && count($approvalHistory) > 0)
                                    <div class="space-y-3">
                                        @foreach($approvalHistory as $history)
                                            <div class="text-xs border-l-2 pl-3 py-2
                                                @if($history->action === 'approved') border-green-500
                                                @elseif($history->action === 'rejected') border-red-500
                                                @else border-blue-500
                                                @endif">
                                                <p class="font-semibold text-gray-900">{{ $history->user->nama ?? 'System' }}</p>
                                                <p class="text-gray-600">
                                                    <span class="font-medium
                                                        @if($history->action === 'approved') text-green-600
                                                        @elseif($history->action === 'rejected') text-red-600
                                                        @else text-blue-600
                                                        @endif">
                                                        {{ ucfirst($history->action) }}
                                                    </span>
                                                    • {{ \Carbon\Carbon::parse($history->created_at)->format('d M Y H:i') }}
                                                </p>
                                                @if($history->notes)
                                                    <p class="text-gray-700 mt-1 italic">"{{ $history->notes }}"</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-gray-500 text-center py-4">Belum ada riwayat</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-3"></i>
                <p class="text-red-700 font-medium">Data approval tidak ditemukan</p>
            </div>
        @endif
    </div>
</div>
