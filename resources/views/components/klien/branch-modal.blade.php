{{-- Add/Edit Branch Modal --}}
<div 
    x-show="showBranchModal" 
    x-cloak
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>
    <div 
        class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
        @click.stop
    >
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-medium text-gray-900" x-text="editingBranch ? 'Edit Cabang' : 'Tambah Cabang'"></h3>
            <button 
                @click="closeBranchModal()"
                class="text-gray-400 hover:text-gray-500"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitBranchForm()" class="p-6">
            <div class="space-y-4">
                <div x-show="!editingBranch">
                    <label for="branch_company" class="block text-sm font-medium text-gray-700 mb-1">
                        Perusahaan <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="branch_company"
                        x-model="branchForm.company_nama"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">Pilih perusahaan</option>
                        <template x-for="company in uniqueCompanies" :key="company">
                            <option :value="company" x-text="company"></option>
                        </template>
                    </select>
                    <div x-show="branchForm.errors.company_nama" class="text-red-500 text-sm mt-1" x-text="branchForm.errors.company_nama"></div>
                </div>

                <div x-show="editingBranch">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Perusahaan</label>
                    <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-600" x-text="branchForm.company_nama"></div>
                </div>

                <div>
                    <label for="branch_cabang" class="block text-sm font-medium text-gray-700 mb-1">
                        Lokasi Cabang <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="branch_cabang"
                        x-model="branchForm.cabang"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan lokasi cabang"
                        required
                    >
                    <div x-show="branchForm.errors.cabang" class="text-red-500 text-sm mt-1" x-text="branchForm.errors.cabang"></div>
                </div>

                <div>
                    <label for="branch_no_hp" class="block text-sm font-medium text-gray-700 mb-1">
                        No. HP
                    </label>
                    <input
                        type="tel"
                        id="branch_no_hp"
                        x-model="branchForm.no_hp"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="08123456789"
                    >
                    <div x-show="branchForm.errors.no_hp" class="text-red-500 text-sm mt-1" x-text="branchForm.errors.no_hp"></div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    type="button"
                    @click="closeBranchModal()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    :disabled="!branchForm.company_nama.trim() || !branchForm.cabang.trim()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    x-text="editingBranch ? 'Update' : 'Tambah'"
                ></button>
            </div>
        </form>
    </div>
</div>