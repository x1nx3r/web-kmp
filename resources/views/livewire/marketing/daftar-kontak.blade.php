<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Daftar Kontak - <span class="text-blue-600">{{ $selectedClient }}</span>
                </h1>
                <p class="text-sm text-gray-600">
                    Kelola daftar kontak untuk klien {{ $selectedClient }}
                </p>
            </div>
            <a href="{{ route('klien.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Klien
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Search and Actions -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <!-- Search -->
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Pencarian Kontak</label>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Cari nama, HP, atau jabatan...">
            </div>

            <!-- Actions -->
            <div class="flex space-x-2">
                <button wire:click="openKontakModal" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Kontak
                </button>
                
                @if($search)
                    <button wire:click="clearSearch" 
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                        Reset
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Contact Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor HP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $contact->nama }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                @if($contact->nomor_hp)
                                    <a href="tel:{{ $contact->nomor_hp }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $contact->nomor_hp }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $contact->jabatan ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($contact->catatan)
                                    <div class="max-w-xs truncate" title="{{ $contact->catatan }}">
                                        {{ $contact->catatan }}
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-medium">
                                <div class="flex justify-center space-x-2">
                                    <button wire:click="editKontak({{ $contact->id }})"
                                            class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="deleteKontak({{ $contact->id }}, '{{ $contact->nama }}')"
                                            class="text-red-600 hover:text-red-900" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                @if($search)
                                    Tidak ada kontak yang sesuai dengan pencarian "{{ $search }}".
                                @else
                                    Belum ada kontak untuk klien {{ $selectedClient }}. <button wire:click="openKontakModal" class="text-blue-600 hover:text-blue-800">Tambah kontak pertama</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($contacts->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>

    <!-- Contact Modal -->
    @if($showKontakModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" wire:click="closeKontakModal"></div>

            {{-- Modal Container --}}
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full transform transition-all" @click.stop>
                    {{-- Modal Header --}}
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $editingKontak ? 'Edit Kontak' : 'Tambah Kontak Baru' }}
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $selectedClient }}</p>
                            </div>
                            <button 
                                wire:click="closeKontakModal"
                                class="text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6">

                    <form wire:submit.prevent="submitKontakForm" class="space-y-4">
                        <!-- Client Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-building text-blue-600 mr-2"></i>
                                <span class="text-sm font-medium text-blue-800">Klien: {{ $selectedClient }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nama -->
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                                <input type="text" 
                                       wire:model="kontakForm.nama" 
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('kontakForm.nama') border-red-500 @else border-gray-300 @enderror"
                                       placeholder="Masukkan nama kontak">
                                @error('kontakForm.nama')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Nomor HP -->
                            <div>
                                <label for="nomor_hp" class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                                <input type="text" 
                                       wire:model="kontakForm.nomor_hp" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Masukkan nomor HP">
                            </div>

                            <!-- Jabatan -->
                            <div class="md:col-span-2">
                                <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                <input type="text" 
                                       wire:model="kontakForm.jabatan" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Masukkan jabatan">
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea wire:model="kontakForm.catatan" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Masukkan catatan tambahan"></textarea>
                        </div>

                        <!-- Hidden field for client name -->
                        <input type="hidden" wire:model="kontakForm.klien_nama" value="{{ $selectedClient }}">

                        <!-- Modal Actions -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 mt-6">
                            <button type="button" 
                                    wire:click="closeKontakModal"
                                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                {{ $editingKontak ? 'Perbarui' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" wire:click="closeDeleteModal"></div>

            {{-- Modal Container --}}
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full transform transition-all" @click.stop>
                    {{-- Modal Header --}}
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-trash text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $deleteModal['title'] }}</h3>
                                <p class="text-sm text-gray-600 mt-1">Konfirmasi penghapusan</p>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6">
                        <p class="text-gray-700 mb-6">{{ $deleteModal['message'] }}</p>
                        
                        <div class="flex justify-end space-x-3">
                            <button wire:click="closeDeleteModal"
                                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                                Batal
                            </button>
                            <button wire:click="{{ $deleteModal['action'] }}({{ implode(',', $deleteModal['actionParams']) }})"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
