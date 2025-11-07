<div class="min-h-screen bg-gray-50">
    {{-- Navigation Breadcrumb --}}
    <div class="bg-white border-b border-gray-200">
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
                                <a href="{{ route('klien.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                                    Daftar Klien
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-300 mr-4"></i>
                                <span class="text-gray-900 text-sm font-medium">Edit Cabang</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <a
                    href="{{ route('klien.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-3 space-y-8">
                {{-- Branch Information Card --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-building mr-3 text-blue-600"></i>
                            Informasi Cabang
                        </h2>
                    </div>

                    <form wire:submit="updateKlien" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Perusahaan
                                </label>
                                <select wire:model="klienForm.nama" id="nama" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('klienForm.nama') border-red-500 @enderror">
                                    @foreach($uniqueCompanies as $company)
                                        <option value="{{ $company }}">{{ $company }}</option>
                                    @endforeach
                                </select>
                                @error('klienForm.nama')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="cabang" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lokasi Cabang
                                </label>
                                <input
                                    type="text"
                                    wire:model="klienForm.cabang"
                                    id="cabang"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('klienForm.cabang') border-red-500 @enderror"
                                    placeholder="Masukkan lokasi cabang"
                                    required
                                >
                                @error('klienForm.cabang')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-2">
                                    No. HP
                                </label>
                                <input
                                    type="text"
                                    wire:model="klienForm.no_hp"
                                    id="no_hp"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Masukkan nomor HP"
                                >
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <a
                                href="{{ route('klien.index') }}"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </a>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove>
                                    <i class="fas fa-save mr-2"></i>
                                    Simpan Perubahan
                                </span>
                                <span wire:loading>
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Materials Management Card --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-boxes mr-3 text-green-600"></i>
                                Material Management
                            </h2>
                            <button
                                wire:click="openMaterialModal"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            >
                                <i class="fas fa-plus mr-2"></i>
                                Tambah Material
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        @if($klien->bahanBakuKliens->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Approved</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Post</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($klien->bahanBakuKliens as $material)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $material->nama }}</div>
                                                    @if($material->spesifikasi)
                                                        <div class="text-sm text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($material->spesifikasi, 60) }}</div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ $material->satuan }}</td>
                                            <td class="px-6 py-4">
                                                @if($material->harga_approved)
                                                    <div class="text-sm font-medium text-green-600">
                                                        Rp {{ number_format($material->harga_approved, 0, ',', '.') }}
                                                    </div>
                                                    @if($material->approved_at)
                                                        <div class="text-xs text-gray-500">{{ $material->approved_at->format('d/m/Y') }}</div>
                                                    @endif
                                                @else
                                                    <span class="text-sm text-gray-400">Belum ada harga</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($material->status === 'aktif')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Aktif
                                                    </span>
                                                @elseif($material->status === 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        Pending
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-times-circle mr-1"></i>
                                                        Non-aktif
                                                    </span>
                                                @endif
                                            </td>
                                            {{-- Post Column --}}
                                            <td class="px-6 py-4 text-center">
                                                @if($material->post)
                                                    <i class="fas fa-check-circle text-green-500 text-lg"></i>
                                                @else
                                                    <i class="fas fa-times-circle text-gray-300 text-lg"></i>
                                                @endif
                                            </td>
                                            {{-- Present Column --}}
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if($material->present === 'Ready') bg-green-100 text-green-800
                                                    @elseif($material->present === 'Confirmed') bg-blue-100 text-blue-800
                                                    @elseif($material->present === 'Hold') bg-red-100 text-red-800
                                                    @elseif($material->present === 'Negotiate') bg-yellow-100 text-yellow-800
                                                    @elseif($material->present === 'Sample Sent') bg-purple-100 text-purple-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ $material->present }}
                                                </span>
                                                @if($material->cause)
                                                    <div class="text-xs text-gray-500 mt-1" title="{{ $material->cause }}">
                                                        {{ \Illuminate\Support\Str::limit($material->cause, 30) }}
                                                    </div>
                                                @endif
                                            </td>
                                            {{-- Jenis Column --}}
                                            <td class="px-6 py-4">
                                                @if($material->jenis && count($material->jenis) > 0)
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($material->jenis as $jenis)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                                @if($jenis === 'Aqua') bg-blue-100 text-blue-800
                                                                @elseif($jenis === 'Poultry') bg-yellow-100 text-yellow-800
                                                                @elseif($jenis === 'Ruminansia') bg-green-100 text-green-800
                                                                @else bg-gray-100 text-gray-800
                                                                @endif">
                                                                {{ $jenis }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-sm text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $material->updated_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-3">
                                                    @if($material->harga_approved)
                                                        <a
                                                            href="{{ route('klien.riwayat-harga', [$klien, $material]) }}"
                                                            class="text-blue-600 hover:text-blue-800"
                                                            title="Lihat Riwayat Harga"
                                                        >
                                                            <i class="fas fa-chart-line"></i>
                                                        </a>
                                                    @endif
                                                    <button
                                                        wire:click="editMaterial({{ $material->id }})"
                                                        class="text-amber-600 hover:text-amber-800"
                                                        title="Edit Material"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        wire:click="deleteMaterial({{ $material->id }}, '{{ $material->nama }}')"
                                                        class="text-red-600 hover:text-red-800"
                                                        title="Hapus Material"
                                                    >
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-12">
                                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-boxes text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Material</h3>
                                <p class="text-gray-500 mb-6">Cabang ini belum memiliki material yang terdaftar.</p>
                                <button
                                    wire:click="openMaterialModal"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                                >
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Material Pertama
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Quick Info Card --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Informasi Quick</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Perusahaan:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $klien->nama }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Cabang:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $klien->cabang }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Total Material:</span>
                            <span class="text-sm font-medium text-green-600">{{ $klien->bahanBakuKliens->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Material Aktif:</span>
                            <span class="text-sm font-medium text-green-600">{{ $klien->bahanBakuKliens->where('status', 'aktif')->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Dibuat:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $klien->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Actions Card --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Aksi Cepat</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <button
                            wire:click="openMaterialModal"
                            class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Material
                        </button>
                        <a
                            href="{{ route('klien.index') }}"
                            class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <i class="fas fa-list mr-2"></i>
                            Lihat Semua Klien
                        </a>
                        <button
                            wire:click="deleteKlien"
                            class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700"
                        >
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Cabang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Material Modal --}}
    @if($showMaterialModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.3);" wire:click="closeMaterialModal"></div>

            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" @click.stop>
                    <form wire:submit="submitMaterialForm">
                        <div class="mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                {{ $editingMaterial ? 'Edit Material' : 'Tambah Material Baru' }}
                            </h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="material_nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Material</label>
                                <input
                                    type="text"
                                    wire:model="materialForm.nama"
                                    id="material_nama"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('materialForm.nama') border-red-500 @enderror"
                                    placeholder="Masukkan nama material"
                                    required
                                >
                                @error('materialForm.nama')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="material_satuan" class="block text-sm font-medium text-gray-700 mb-2">Satuan</label>
                                <input
                                    type="text"
                                    wire:model="materialForm.satuan"
                                    id="material_satuan"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('materialForm.satuan') border-red-500 @enderror"
                                    placeholder="kg, pcs, liter, dll"
                                    required
                                >
                                @error('materialForm.satuan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="material_spesifikasi" class="block text-sm font-medium text-gray-700 mb-2">Spesifikasi</label>
                                <textarea
                                    wire:model="materialForm.spesifikasi"
                                    id="material_spesifikasi"
                                    rows="3"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Deskripsi detail material (opsional)"
                                ></textarea>
                            </div>

                            <div>
                                <label for="material_harga_approved" class="block text-sm font-medium text-gray-700 mb-2">Harga Approved</label>
                                <input
                                    type="number"
                                    wire:model="materialForm.harga_approved"
                                    id="material_harga_approved"
                                    min="0"
                                    step="1"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('materialForm.harga_approved') border-red-500 @enderror"
                                    placeholder="0"
                                >
                                @error('materialForm.harga_approved')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="material_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select
                                    wire:model="materialForm.status"
                                    id="material_status"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                >
                                    <option value="pending">Pending</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="non_aktif">Non-aktif</option>
                                </select>
                            </div>

                            {{-- Post Checkbox --}}
                            <div>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="materialForm.post"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                    >
                                    <span class="ml-2 text-sm font-medium text-gray-700">Post (Checkmark)</span>
                                </label>
                            </div>

                            {{-- Present Dropdown --}}
                            <div>
                                <label for="material_present" class="block text-sm font-medium text-gray-700 mb-2">Present Status</label>
                                <select
                                    wire:model="materialForm.present"
                                    id="material_present"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('materialForm.present') border-red-500 @enderror"
                                    required
                                >
                                    <option value="NotUsed">Not Used</option>
                                    <option value="Ready">Ready</option>
                                    <option value="Not Reasonable Price">Not Reasonable Price</option>
                                    <option value="Pos Closed">PO's Closed</option>
                                    <option value="Not Qualified Raw">Not Qualified Raw</option>
                                    <option value="Not Updated Yet">Not Updated Yet</option>
                                    <option value="Didnt Have Supplier">Didn't Have Supplier</option>
                                    <option value="Factory No Need Yet">Factory No Need Yet</option>
                                    <option value="Confirmed">Confirmed</option>
                                    <option value="Sample Sent">Sample Sent</option>
                                    <option value="Hold">Hold</option>
                                    <option value="Negotiate">Negotiate</option>
                                </select>
                                @error('materialForm.present')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Cause Text Area --}}
                            <div>
                                <label for="material_cause" class="block text-sm font-medium text-gray-700 mb-2">Cause (Explanation)</label>
                                <textarea
                                    wire:model="materialForm.cause"
                                    id="material_cause"
                                    rows="3"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Explain the Present status (optional)"
                                ></textarea>
                            </div>

                            {{-- Jenis Tags --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis (Category Tags)</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            wire:model="materialForm.jenis"
                                            value="Aqua"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">Aqua</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            wire:model="materialForm.jenis"
                                            value="Poultry"
                                            class="rounded border-gray-300 text-yellow-600 shadow-sm focus:ring-yellow-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">Poultry</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            wire:model="materialForm.jenis"
                                            value="Ruminansia"
                                            class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">Ruminansia</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                wire:click="closeMaterialModal"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove>{{ $editingMaterial ? 'Update Material' : 'Tambah Material' }}</span>
                                <span wire:loading>
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.3);" wire:click="closeDeleteModal"></div>

            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" @click.stop>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ $deleteModal['title'] }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ $deleteModal['message'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button
                            wire:click="confirmDelete"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            <span wire:loading.remove>Hapus</span>
                            <span wire:loading>
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Processing...
                            </span>
                        </button>
                        <button
                            type="button"
                            wire:click="closeDeleteModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="fixed bottom-4 right-4 z-50">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('message') }}
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 z-50">
            <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif
</div>