@extends('layouts.app')

@section('content')
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

                    <form action="{{ route('klien.update', $klien) }}" method="POST" class="p-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Perusahaan
                                </label>
                                <select name="nama" id="nama" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nama') border-red-500 @enderror">
                                    @foreach($uniqueCompanies as $company)
                                        <option value="{{ $company }}" {{ $klien->nama === $company ? 'selected' : '' }}>
                                            {{ $company }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nama')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="cabang" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lokasi Cabang
                                </label>
                                <input
                                    type="text"
                                    name="cabang"
                                    id="cabang"
                                    value="{{ old('cabang', $klien->cabang) }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('cabang') border-red-500 @enderror"
                                    placeholder="Masukkan lokasi cabang"
                                    required
                                >
                                @error('cabang')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-2">
                                    No. HP
                                </label>
                                <input
                                    type="text"
                                    name="no_hp"
                                    id="no_hp"
                                    value="{{ old('no_hp', $klien->no_hp) }}"
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
                            >
                                <i class="fas fa-save mr-2"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Materials Management Card --}}
                <div class="bg-white shadow rounded-lg overflow-hidden" id="materials-section">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-boxes mr-3 text-green-600"></i>
                                Material Management
                            </h2>
                            <button
                                onclick="openMaterialModal()"
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Material
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Satuan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Harga Approved
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Last Updated
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
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
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $material->satuan }}
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($material->harga_approved)
                                                    <div class="text-sm font-medium text-green-600">
                                                        Rp {{ number_format($material->harga_approved, 0, ',', '.') }}
                                                    </div>
                                                    @if($material->approved_at)
                                                        <div class="text-xs text-gray-500">
                                                            {{ $material->approved_at->format('d/m/Y') }}
                                                        </div>
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
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $material->updated_at->format('d/m/Y H:i') }}
                                            </td>
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
                                                        onclick="editMaterial({{ $material->id }})"
                                                        class="text-amber-600 hover:text-amber-800"
                                                        title="Edit Material"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        onclick="deleteMaterial({{ $material->id }}, '{{ $material->nama }}')"
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
                                    onclick="openMaterialModal()"
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
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Informasi Quick
                        </h3>
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
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Aksi Cepat
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <button
                            onclick="openMaterialModal()"
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
                            onclick="confirmDelete()"
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
</div>

{{-- Material Modal --}}
<div id="materialModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.3);" onclick="closeMaterialModal()"></div>

    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" onclick="event.stopPropagation()">
            <form id="materialForm" action="{{ route('klien.storeMaterial') }}" method="POST">
                @csrf
                <input type="hidden" name="klien_id" value="{{ $klien->id }}">
                <input type="hidden" id="material_id" name="material_id" value="">

                <div class="mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">
                        Tambah Material Baru
                    </h3>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Material</label>
                        <input
                            type="text"
                            name="nama"
                            id="material_nama"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Masukkan nama material"
                            required
                        >
                    </div>

                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-700 mb-2">Satuan</label>
                        <input
                            type="text"
                            name="satuan"
                            id="material_satuan"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="kg, pcs, liter, dll"
                            required
                        >
                    </div>

                    <div>
                        <label for="spesifikasi" class="block text-sm font-medium text-gray-700 mb-2">Spesifikasi</label>
                        <textarea
                            name="spesifikasi"
                            id="material_spesifikasi"
                            rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Deskripsi detail material (opsional)"
                        ></textarea>
                    </div>

                    <div>
                        <label for="harga_approved" class="block text-sm font-medium text-gray-700 mb-2">Harga Approved</label>
                        <input
                            type="number"
                            name="harga_approved"
                            id="material_harga_approved"
                            min="0"
                            step="1"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0"
                        >
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select
                            name="status"
                            id="material_status"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        >
                            <option value="pending">Pending</option>
                            <option value="aktif">Aktif</option>
                            <option value="non_aktif">Non-aktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        onclick="closeMaterialModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                    >
                        <span id="submitText">Tambah Material</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.3);" onclick="closeDeleteModal()"></div>

    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" onclick="event.stopPropagation()">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="deleteModalTitle">
                        Konfirmasi Hapus
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500" id="deleteModalMessage">
                            Apakah Anda yakin ingin menghapus item ini?
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button
                    id="confirmDeleteBtn"
                    type="button"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Hapus
                </button>
                <button
                    type="button"
                    onclick="closeDeleteModal()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm"
                >
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentMaterialId = null;
let deleteAction = null;

function openMaterialModal(materialId = null) {
    const modal = document.getElementById('materialModal');
    const form = document.getElementById('materialForm');
    const title = document.getElementById('modalTitle');
    const submitText = document.getElementById('submitText');

    if (materialId) {
        // Edit mode
        currentMaterialId = materialId;
        title.textContent = 'Edit Material';
        submitText.textContent = 'Update Material';

        // Load material data via AJAX
        fetch(`/klien/material/${materialId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('material_id').value = materialId;
                document.getElementById('material_nama').value = data.nama;
                document.getElementById('material_satuan').value = data.satuan;
                document.getElementById('material_spesifikasi').value = data.spesifikasi || '';
                document.getElementById('material_harga_approved').value = data.harga_approved || '';
                document.getElementById('material_status').value = data.status;

                form.action = `/klien/material/${materialId}`;
                form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');
            });
    } else {
        // Add mode
        currentMaterialId = null;
        title.textContent = 'Tambah Material Baru';
        submitText.textContent = 'Tambah Material';
        form.reset();
        document.getElementById('material_id').value = '';
        form.action = '{{ route("klien.storeMaterial") }}';

        // Remove method spoofing if exists
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();
    }

    modal.classList.remove('hidden');
}

function closeMaterialModal() {
    document.getElementById('materialModal').classList.add('hidden');
}

function editMaterial(materialId) {
    openMaterialModal(materialId);
}

function deleteMaterial(materialId, materialName) {
    document.getElementById('deleteModalTitle').textContent = 'Hapus Material';
    document.getElementById('deleteModalMessage').textContent = `Apakah Anda yakin ingin menghapus material "${materialName}"? Tindakan ini tidak dapat dibatalkan.`;

    deleteAction = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/klien/material/${materialId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    };

    document.getElementById('confirmDeleteBtn').onclick = deleteAction;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function confirmDelete() {
    document.getElementById('deleteModalTitle').textContent = 'Hapus Cabang';
    document.getElementById('deleteModalMessage').textContent = 'Apakah Anda yakin ingin menghapus cabang ini? Semua material yang terkait juga akan terhapus. Tindakan ini tidak dapat dibatalkan.';

    deleteAction = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("klien.destroy", $klien) }}';
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    };

    document.getElementById('confirmDeleteBtn').onclick = deleteAction;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteAction = null;
}

// Handle form submission
document.getElementById('materialForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const submitText = document.getElementById('submitText');
    const originalText = submitText.textContent;

    // Show loading state
    submitBtn.disabled = true;
    submitText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

    fetch(this.action, {
        method: this.querySelector('input[name="_method"]') ? 'PUT' : 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('success', data.message || 'Material berhasil disimpan');

            // Close modal and reload page after a short delay
            setTimeout(() => {
                closeMaterialModal();
                location.reload();
            }, 1000);
        } else {
            // Show error message
            showNotification('error', data.message || 'Terjadi kesalahan saat menyimpan material');

            // Handle validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('border-red-500');

                        // Remove existing error message
                        const existingError = input.parentNode.querySelector('.text-red-600');
                        if (existingError) existingError.remove();

                        // Add new error message
                        const errorDiv = document.createElement('p');
                        errorDiv.className = 'mt-1 text-sm text-red-600';
                        errorDiv.textContent = data.errors[field][0];
                        input.parentNode.appendChild(errorDiv);
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Terjadi kesalahan pada server');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    });
});

// Function to show notifications
function showNotification(type, message) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-toast fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;

    if (type === 'success') {
        notification.classList.add('bg-green-500', 'text-white');
        notification.innerHTML = `<div class="flex items-center"><i class="fas fa-check-circle mr-2"></i>${message}</div>`;
    } else {
        notification.classList.add('bg-red-500', 'text-white');
        notification.innerHTML = `<div class="flex items-center"><i class="fas fa-exclamation-circle mr-2"></i>${message}</div>`;
    }

    // Add to page
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 4 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 4000);
}
</script>
@endsection
