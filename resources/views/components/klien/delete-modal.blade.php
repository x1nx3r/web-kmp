{{-- Delete Modal Component --}}
<div 
    x-show="showDeleteModal" 
    x-cloak
    class="fixed inset-0 bg-black bg-opacity-30 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
    x-on:click="showDeleteModal = false"
>
    <div 
        class="relative p-5 border w-11/12 sm:w-96 shadow-lg rounded-md bg-white"
        x-on:click.stop
    >
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $title ?? 'Konfirmasi Hapus' }}</h3>
                </div>
                <button 
                    type="button" 
                    x-on:click="showDeleteModal = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-3">{{ $message ?? 'Apakah Anda yakin ingin menghapus klien berikut?' }}</p>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <div class="flex items-center">
                        <i class="fas fa-user text-red-500 mr-2"></i>
                        <span class="font-semibold text-red-800" x-text="deleteKlienName"></span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    <i class="fas fa-info-circle mr-1"></i>{{ $warning ?? 'Tindakan ini tidak dapat dibatalkan.' }}
                </p>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <button 
                    type="button" 
                    x-on:click="showDeleteModal = false"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 rounded-lg transition-all duration-200 text-sm font-semibold"
                >
                    <i class="fas fa-times mr-2"></i>{{ $cancelLabel ?? 'Batal' }}
                </button>
                <button 
                    type="button" 
                    x-on:click="confirmDelete()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg text-sm font-semibold"
                >
                    <i class="fas fa-trash mr-2"></i>{{ $confirmLabel ?? 'Hapus Klien' }}
                </button>
            </div>
        </div>
    </div>
</div>