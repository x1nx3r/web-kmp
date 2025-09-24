{{-- Confirmation Modal --}}
<div 
    x-show="showConfirmModal" 
    x-cloak
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>
    <div 
        class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
        @click.stop
    >
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-medium text-gray-900" x-text="confirmModal.title"></h3>
            <button 
                @click="closeConfirmModal()"
                class="text-gray-400 hover:text-gray-500"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-700" x-text="confirmModal.message"></p>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    <span x-text="confirmModal.warning"></span>
                </p>
            </div>
        </div>

        <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50">
            <button
                type="button"
                @click="closeConfirmModal()"
                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Batal
            </button>
            <button
                type="button"
                @click="confirmAction()"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                x-text="confirmModal.confirmText || 'Hapus'"
            ></button>
        </div>
    </div>
</div>