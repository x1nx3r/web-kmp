{{-- Bahan Baku Section Component (Client-Specific Materials) --}}
@props(['klien'])

@php
    // Get client-specific materials with relationships
    $clientMaterials = $klien->bahanBakuKliens()->with(['approvedByMarketing', 'riwayatHarga'])->get();
@endphp

<div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400" x-data="materialManagerData()">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-blue-200">
        <div class="flex items-center justify-between">
            <div>
                <h5 class="text-sm font-semibold text-gray-900">
                    Bahan Baku - {{ $klien->cabang ?? 'Cabang' }}
                </h5>
                <p class="text-xs text-gray-600 mt-1">
                    {{ $klien->phone ? 'Kontak: ' . $klien->phone : 'Tidak ada kontak' }} • 
                    {{ $clientMaterials->count() }} material approved
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <button 
                    @click="openMaterialModal({{ json_encode($klien->only(['id', 'nama', 'cabang'])) }})"
                    class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600 transition-colors duration-200"
                    title="Tambah Material Baru"
                >
                    <i class="fas fa-plus mr-1"></i>
                    Tambah Material
                </button>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="p-6">
        @if($clientMaterials->isEmpty())
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-box-open text-2xl text-gray-400"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Material</h4>
                <p class="text-sm text-gray-600 mb-4">
                    Tambah material khusus untuk klien {{ $klien->nama }} - {{ $klien->cabang }}
                </p>
                <button 
                    @click="openMaterialModal({{ json_encode($klien->only(['id', 'nama', 'cabang'])) }})"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Material Pertama
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-blue-200">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">No</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Material</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Harga Approved</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Spesifikasi</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-100">
                        @foreach($clientMaterials as $index => $material)
                            <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                                <td class="py-3 px-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                                
                                <td class="py-3 px-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $material->nama }}</div>
                                        <div class="text-xs text-gray-500">{{ $material->satuan ?? '-' }}</div>
                                        @if($material->approved_at)
                                            <div class="text-xs text-green-600 mt-1">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ $material->approved_at->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                
                                <td class="py-3 px-3">
                                    @if($material->harga_approved)
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $material->formatted_approved_price }}
                                        </div>
                                        <div class="text-xs text-gray-500">per {{ $material->satuan }}</div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum disetujui</span>
                                    @endif
                                </td>
                                
                                <td class="py-3 px-3 text-sm text-gray-600">
                                    {{ Str::limit($material->spesifikasi ?? '-', 50) }}
                                </td>
                                
                                <td class="py-3 px-3 text-center">
                                    <x-klien.status-badge :status="$material->status ?? 'unknown'" />
                                </td>
                                
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button 
                                            @click="openMaterialModal({{ json_encode($klien->only(['id', 'nama', 'cabang'])) }}, {{ json_encode($material->only(['id', 'nama', 'satuan', 'spesifikasi', 'harga_approved', 'status'])) }})"
                                            class="text-blue-600 hover:text-blue-800 text-sm"
                                            title="Edit Material"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        @if($material->harga_approved && $material->riwayatHarga->count() > 1)
                                            <a
                                                href="{{ route('klien.riwayat-harga', ['klien' => $klien->id, 'material' => $material->id]) }}"
                                                class="text-green-600 hover:text-green-800 text-sm px-2 py-1 rounded hover:bg-green-50 transition-colors"
                                                title="Buka Riwayat Harga"
                                            >
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                        @endif
                                        
                                        <button 
                                            @click="deleteMaterial({{ json_encode($material->only(['id', 'nama'])) }})"
                                            class="text-red-600 hover:text-red-800 text-sm"
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
            </div>
        @endif
    </div>
</div>