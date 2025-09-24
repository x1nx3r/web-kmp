{{-- Action Header Component --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
    <h2 class="text-xl font-bold text-gray-800 flex items-center">
        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-list text-white text-sm"></i>
        </div>
        {{ $title ?? 'Daftar Klien' }}
    </h2>
    
    <div class="flex space-x-2">
        <button 
            @click="openCompanyModal()"
            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg font-semibold text-sm"
            title="Tambah Perusahaan Baru"
        >
            <i class="fas fa-building mr-1"></i>Tambah Klien
        </button>
        
        <button 
            @click="openBranchModal()"
            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg font-semibold text-sm"
            title="Tambah Cabang Baru"
        >
            <i class="fas fa-map-marker-alt mr-1"></i>Tambah Cabang
        </button>
    </div>
</div>