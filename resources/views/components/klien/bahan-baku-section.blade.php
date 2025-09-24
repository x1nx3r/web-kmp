{{-- Bahan Baku Section Component --}}
@props(['bahanBakuItems', 'detailId', 'klien'])

@php
    $uniqueBahan = $bahanBakuItems->unique('id')->values();
@endphp

<div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-blue-200">
        <div class="flex items-center justify-between">
            <div>
                <h5 class="text-sm font-semibold text-gray-900">
                    Bahan Baku - {{ $klien->cabang ?? 'Cabang' }}
                </h5>
                <p class="text-xs text-gray-600 mt-1">
                    {{ $klien->no_hp ? 'Kontak: ' . $klien->no_hp : 'Tidak ada kontak' }} • 
                    {{ $uniqueBahan->count() }} item bahan baku
                </p>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="p-6">
        @if($uniqueBahan->isEmpty())
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-box-open text-2xl text-gray-400"></i>
                </div>
                <p class="text-sm text-gray-500">Belum ada bahan baku untuk cabang ini</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-blue-200">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">No</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Bahan Baku</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Satuan</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Spesifikasi</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-100">
                        @foreach($uniqueBahan as $index => $bb)
                            <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                                <td class="py-3 px-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                                <td class="py-3 px-3 text-sm font-medium text-gray-900">{{ $bb->nama }}</td>
                                <td class="py-3 px-3 text-sm text-gray-600">{{ $bb->satuan ?? '-' }}</td>
                                <td class="py-3 px-3 text-sm text-gray-600">{{ $bb->spesifikasi ?? '-' }}</td>
                                <td class="py-3 px-3 text-center">
                                    <x-klien.status-badge :status="$bb->status ?? 'unknown'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>