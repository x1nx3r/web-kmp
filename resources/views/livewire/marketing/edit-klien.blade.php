<div class="min-h-screen bg-gray-50">
    {{-- Navigation Breadcrumb --}}
    <div class="bg-white border-b border-gray-200">
        <div class="w-full px-4 sm:px-6 lg:px-8">
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

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        <div class="space-y-6">
            {{-- Main Content --}}
            <div class="space-y-6">
                {{-- Branch Information Card with Stats --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-building mr-3 text-blue-600"></i>
                            Informasi Cabang
                        </h2>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">Total Material:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $klien->bahanBakuKliens->count() }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">Aktif:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $klien->bahanBakuKliens->where('status', 'aktif')->count() }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">Dibuat:</span>
                                <span class="text-sm font-medium text-gray-700">{{ $klien->created_at->format('d/m/Y') }}</span>
                            </div>
                            @if(auth()->user()->isMarketing() || auth()->user()->isDirektur())
                            <div class="border-l border-gray-300 h-6"></div>
                            <button
                                wire:click="deleteKlien"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                title="Hapus Cabang"
                            >
                                <i class="fas fa-trash mr-1.5"></i>
                                Hapus
                            </button>
                            @endif
                        </div>
                    </div>

                    <form wire:submit="updateKlien" class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Perusahaan
                                </label>
                                <input
                                    type="text"
                                    wire:model="klienForm.nama"
                                    id="nama"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('klienForm.nama') border-red-500 @enderror"
                                    placeholder="Masukkan nama perusahaan"
                                    required
                                >
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
                                <label for="alamat_lengkap" class="block text-sm font-medium text-gray-700 mb-2">
                                    Alamat Lengkap (Opsional)
                                </label>
                                <input
                                    type="text"
                                    wire:model="klienForm.alamat_lengkap"
                                    id="alamat_lengkap"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('klienForm.alamat_lengkap') border-red-500 @enderror"
                                    placeholder="Masukkan alamat lengkap plant..."
                                >
                                @error('klienForm.alamat_lengkap')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="contact_person_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Contact Person
                                </label>
                                <select
                                    wire:model="klienForm.contact_person_id"
                                    id="contact_person_id"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('klienForm.contact_person_id') border-red-500 @enderror"
                                    @if($kontakOptions->isEmpty()) disabled @endif
                                >
                                    @if($kontakOptions->isEmpty())
                                        <option value="">{{ empty($klienForm['nama']) ? 'Pilih perusahaan terlebih dahulu' : 'Tidak ada kontak untuk perusahaan ini' }}</option>
                                    @else
                                        <option value="">Pilih Contact Person</option>
                                        @foreach($kontakOptions as $kontak)
                                            <option value="{{ $kontak->id }}">
                                                {{ $kontak->nama }}
                                                @if($kontak->jabatan)
                                                    - {{ $kontak->jabatan }}
                                                @endif
                                                @if($kontak->nomor_hp)
                                                    ({{ $kontak->nomor_hp }})
                                                @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('klienForm.contact_person_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        @if(auth()->user()->isMarketing() || auth()->user()->isDirektur())
                        <div class="mt-4 flex justify-end">
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
                        @endif
                    </form>
                </div>

                {{-- Materials Management Card --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-boxes mr-3 text-green-600"></i>
                                Material Management
                            </h2>
                            <div class="flex items-center space-x-3">
                                {{-- Sort Dropdown --}}
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600">
                                        <i class="fas fa-sort mr-1"></i>
                                        Urutkan:
                                    </label>
                                    <div class="relative">
                                        <select
                                            wire:model.live="materialSort"
                                            class="block px-3 py-2 pr-8 border border-gray-300 rounded-lg text-sm
                                                   focus:ring-2 focus:ring-green-500 focus:border-green-500
                                                   bg-white appearance-none cursor-pointer"
                                        >
                                            <option value="nama">Nama Material</option>
                                            <option value="order_count_desc">PO Paling Sering</option>
                                            <option value="order_count_asc">PO Paling Jarang</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                                @if(auth()->user()->isMarketing() || auth()->user()->isDirektur())
                                <button
                                    wire:click="openMaterialModal"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Material
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        @if($klien->bahanBakuKliens->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200 text-sm table-fixed">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 18%">Material</th>
                                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 5%">PO</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 6%">Satuan</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 8%">Harga</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 7%">Status</th>
                                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 4%">Post</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 12%">Present</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 10%">Kategori</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 18%">Keterangan</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 6%">Updated</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 6%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($this->sortedMaterials as $material)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium text-gray-900 truncate" title="{{ $material->nama }}">{{ $material->nama }}</div>
                                                @if($material->spesifikasi)
                                                    <div class="text-xs text-gray-500 truncate" title="{{ $material->spesifikasi }}">{{ \Illuminate\Support\Str::limit($material->spesifikasi, 30) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-medium {{ $material->order_details_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-500' }}">
                                                    {{ $material->order_details_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-2 text-sm text-gray-900">{{ $material->satuan }}</td>
                                            <td class="px-2 py-2">
                                                @if($material->harga_approved)
                                                    <div class="text-sm font-medium text-green-600">
                                                        {{ number_format($material->harga_approved / 1000, 0) }}k
                                                    </div>
                                                    @if($material->approved_at)
                                                        <div class="text-xs text-gray-500">{{ $material->approved_at->format('d/m') }}</div>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-2">
                                                @if($material->status === 'aktif')
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check text-xs mr-1"></i>
                                                        Aktif
                                                    </span>
                                                @elseif($material->status === 'pending')
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock text-xs mr-1"></i>
                                                        Pending
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-times text-xs mr-1"></i>
                                                        Off
                                                    </span>
                                                @endif
                                            </td>
                                            {{-- Post Column --}}
                                            <td class="px-2 py-2 text-center">
                                                @if($material->post)
                                                    <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                                @else
                                                    <i class="fas fa-times-circle text-gray-300 text-sm"></i>
                                                @endif
                                            </td>
                                            {{-- Present Column --}}
                                            <td class="px-2 py-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @if($material->present === 'Ready') bg-green-100 text-green-800
                                                    @elseif($material->present === 'Confirmed') bg-blue-100 text-blue-800
                                                    @elseif($material->present === 'Hold') bg-red-100 text-red-800
                                                    @elseif($material->present === 'Negotiate') bg-yellow-100 text-yellow-800
                                                    @elseif($material->present === 'Sample Sent') bg-purple-100 text-purple-800
                                                    @elseif($material->present === 'Not Reasonable Price') bg-orange-100 text-orange-800
                                                    @elseif($material->present === 'Pos Closed') bg-gray-100 text-gray-800
                                                    @elseif($material->present === 'Not Qualified Raw') bg-red-100 text-red-800
                                                    @elseif($material->present === 'Not Updated Yet') bg-yellow-100 text-yellow-800
                                                    @elseif($material->present === 'Didnt Have Supplier') bg-red-100 text-red-800
                                                    @elseif($material->present === 'Factory No Need Yet') bg-gray-100 text-gray-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    @if($material->present === 'Ready')
                                                        <i class="fas fa-check-circle mr-1"></i>Ready
                                                    @elseif($material->present === 'Confirmed')
                                                        <i class="fas fa-handshake mr-1"></i>Confirmed
                                                    @elseif($material->present === 'Hold')
                                                        <i class="fas fa-pause-circle mr-1"></i>Hold
                                                    @elseif($material->present === 'Negotiate')
                                                        <i class="fas fa-comments mr-1"></i>Negotiate
                                                    @elseif($material->present === 'Sample Sent')
                                                        <i class="fas fa-shipping-fast mr-1"></i>Sample Sent
                                                    @elseif($material->present === 'Not Reasonable Price')
                                                        <i class="fas fa-dollar-sign mr-1"></i>Price Issue
                                                    @elseif($material->present === 'Pos Closed')
                                                        <i class="fas fa-times-circle mr-1"></i>PO Closed
                                                    @elseif($material->present === 'Not Qualified Raw')
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>Quality
                                                    @elseif($material->present === 'Not Updated Yet')
                                                        <i class="fas fa-clock mr-1"></i>Pending Update
                                                    @elseif($material->present === 'Didnt Have Supplier')
                                                        <i class="fas fa-user-times mr-1"></i>No Supplier
                                                    @elseif($material->present === 'Factory No Need Yet')
                                                        <i class="fas fa-factory mr-1"></i>Not Needed
                                                    @else
                                                        <i class="fas fa-question-circle mr-1"></i>{{ \Illuminate\Support\Str::limit($material->present, 10) }}
                                                    @endif
                                                </span>
                                            </td>
                                            {{-- Jenis Column --}}
                                            <td class="px-2 py-2">
                                                @if($material->jenis && count($material->jenis) > 0)
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($material->jenis as $jenis)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                                @if($jenis === 'Aqua') bg-blue-100 text-blue-800
                                                                @elseif($jenis === 'Poultry') bg-yellow-100 text-yellow-800
                                                                @elseif($jenis === 'Ruminansia') bg-green-100 text-green-800
                                                                @else bg-gray-100 text-gray-800
                                                                @endif">
                                                                @if($jenis === 'Aqua')
                                                                    <i class="fas fa-fish mr-1"></i>Aqua
                                                                @elseif($jenis === 'Poultry')
                                                                    <i class="fas fa-feather-alt mr-1"></i>Poultry
                                                                @elseif($jenis === 'Ruminansia')
                                                                    <i class="fas fa-cow mr-1"></i>Ruminansia
                                                                @else
                                                                    <i class="fas fa-tag mr-1"></i>{{ $jenis }}
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Tidak ada kategori</span>
                                                @endif
                                            </td>
                                            {{-- Cause/Keterangan Column --}}
                                            <td class="px-2 py-2">
                                                @if($material->cause)
                                                    <div class="text-xs text-gray-700 leading-relaxed">
                                                        {{ \Illuminate\Support\Str::limit($material->cause, 60) }}
                                                        @if(strlen($material->cause) > 60)
                                                            <span class="text-blue-600 cursor-pointer hover:text-blue-800"
                                                                  title="{{ $material->cause }}">
                                                                ...selengkapnya
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Tidak ada keterangan</span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-2 text-xs text-gray-500">{{ $material->updated_at->format('d/m') }}</td>
                                            <td class="px-2 py-2 text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-1">
                                                    @if($material->harga_approved)
                                                        <a
                                                            href="{{ route('klien.riwayat-harga', [$klien, $material]) }}"
                                                            class="text-blue-600 hover:text-blue-800 p-1"
                                                            title="Riwayat Harga"
                                                        >
                                                            <i class="fas fa-chart-line text-xs"></i>
                                                        </a>
                                                    @endif
                                                    @if(auth()->user()->isMarketing() || auth()->user()->isDirektur())
                                                    <button
                                                        wire:click="editMaterial({{ $material->id }})"
                                                        class="text-amber-600 hover:text-amber-800 p-1"
                                                        title="Edit"
                                                    >
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </button>
                                                    <button
                                                        wire:click="deleteMaterial({{ $material->id }}, '{{ $material->nama }}')"
                                                        class="text-red-600 hover:text-red-800 p-1"
                                                        title="Hapus"
                                                    >
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </button>
                                                    @endif
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
                                <p class="text-gray-500 mb-6">Plant ini belum memiliki material yang terdaftar.</p>
                                @if(auth()->user()->isMarketing() || auth()->user()->isDirektur())
                                <button
                                    wire:click="openMaterialModal"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                                >
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Material Pertama
                                </button>
                                @endif
                            </div>
                        @endif
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
