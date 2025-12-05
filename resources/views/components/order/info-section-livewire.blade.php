@props([
    'tanggalOrder',
    'priority',
    'catatan',
    'poNumber',
    'poStartDate',
    'poEndDate',
    'poDocument' => null,
    'isEditing' => false,
    'existingPoDocumentName' => null,
    'existingPoDocumentUrl' => null,
    // Provide a default so the component can be rendered outside Livewire context
    'availableWinners' => [],
])

@php
    // If no available winners were passed in, fetch directly from DB.
    // This allows the component to work even when rendered outside Livewire.
    if (empty($availableWinners)) {
        $availableWinners = \App\Models\User::whereIn('role', ['direktur', 'marketing'])
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();
    }
@endphp

{{-- Order Info --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 p-4">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-info-circle text-purple-600 text-sm"></i>
            </div>
            <h3 class="font-semibold text-gray-900">Informasi Order</h3>
        </div>
    </div>

    <div class="p-4 space-y-4">
        {{-- Tanggal Order --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Tanggal Order <span class="text-red-500">*</span>
            </label>
            <input type="date" wire:model="tanggalOrder"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                   required>
            @error('tanggalOrder')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- PO Number --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Nomor PO <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model.lazy="poNumber"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                   placeholder="Masukkan nomor PO">
            @error('poNumber')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- PO Date Range --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    PO Mulai <span class="text-red-500">*</span>
                </label>
                <input type="date" wire:model="poStartDate"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('poStartDate')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    PO Berakhir / Jatuh Tempo <span class="text-red-500">*</span>
                </label>
                <input type="date" wire:model="poEndDate"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('poEndDate')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- PO Document Upload --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Unggah Surat PO (JPG/PNG/PDF) <span class="text-xs text-gray-500 font-normal">(opsional)</span>
            </label>
            <input type="file" wire:model="poDocument" accept="image/png,image/jpeg,application/pdf"
                   class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-600 hover:file:bg-purple-100">

            {{-- Upload Progress Indicator --}}
            <div wire:loading wire:target="poDocument" class="mt-3">
                <div class="flex items-center text-purple-600">
                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium">Mengunggah file...</span>
                </div>
            </div>

            @error('poDocument')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
            @if($poDocument)
                <p class="text-xs text-gray-600 mt-1">File terpilih: {{ $poDocument->getClientOriginalName() }}</p>
            @endif
            @if($isEditing && $existingPoDocumentName)
                <p class="text-xs text-gray-600 mt-2">
                    Dokumen saat ini:
                    @if($existingPoDocumentUrl)
                        <a href="{{ $existingPoDocumentUrl }}" class="text-purple-600 hover:text-purple-700 underline" target="_blank" rel="noopener">
                            {{ $existingPoDocumentName }}
                        </a>
                    @else
                        {{ $existingPoDocumentName }}
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengganti dokumen PO.</p>
                <p class="text-xs text-gray-500 mt-1">File baru maksimal 5 MB dan akan disimpan di folder publik untuk akses purchasing.</p>
            @else
                <p class="text-xs text-gray-500 mt-2">Maksimal 5 MB. File akan disimpan di folder publik untuk akses purchasing.</p>
            @endif
        </div>

        {{-- Catatan --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Catatan
            </label>
            <textarea wire:model="catatan" rows="3"
                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                      placeholder="Catatan tambahan untuk order ini..."></textarea>
        </div>

        {{-- PO Winner --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                PO Winner
            </label>
            <select wire:model="poWinnerId"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="">-- Pilih PO Winner (opsional) --</option>
                @foreach($availableWinners ?? [] as $winner)
                    <option value="{{ $winner->id }}">{{ $winner->nama }} @if($winner->role) ({{ $winner->role }}) @endif</option>
                @endforeach
            </select>
            @error('poWinnerId')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Hanya menampilkan pengguna dengan peran 'direktur' atau 'marketing'.</p>
        </div>

        {{-- Priority Info --}}
        <div class="bg-gray-50 rounded-lg p-3 space-y-2">
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-gray-700">Prioritas Otomatis</div>
                <x-order.priority-badge :priority="$priority" />
            </div>
            <p class="text-xs text-gray-500">
                Prioritas dihitung otomatis dari jarak hari menuju tanggal berakhir PO: ≤3 hari = Mendesak, ≤7 hari = Tinggi, ≤14 hari = Normal, selebihnya Rendah.
            </p>
        </div>
    </div>
</div>
