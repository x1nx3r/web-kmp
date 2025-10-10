{{-- Add/Edit Company Modal --}}
<div 
    x-show="showCompanyModal" 
    x-cloak
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>
    <div 
        class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
        @click.stop
    >
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-medium text-gray-900" x-text="editingCompany ? 'Edit Perusahaan' : 'Tambah Perusahaan'"></h3>
            <button 
                @click="closeCompanyModal()"
                class="text-gray-400 hover:text-gray-500"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitCompanyForm()" class="p-6">
            <div class="space-y-4">
                <div>
                    <label for="company_nama" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Perusahaan <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="company_nama"
                        x-model="companyForm.nama"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan nama perusahaan"
                        required
                    >
                    <div x-show="companyForm.errors.nama" class="text-red-500 text-sm mt-1" x-text="companyForm.errors.nama"></div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    type="button"
                    @click="closeCompanyModal()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    :disabled="!companyForm.nama.trim()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    x-text="editingCompany ? 'Update' : 'Tambah'"
                ></button>
            </div>
        </form>
    </div>
</div>