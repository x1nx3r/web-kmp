<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Nama Klien</label>
        <input type="text" name="nama" value="{{ old('nama', $klien->nama ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
        @error('nama') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Plant</label>
        <input type="text" name="cabang" value="{{ old('cabang', $klien->cabang ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
        @error('cabang') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Contact Person</label>
        <select name="contact_person_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
            <option value="">Pilih Contact Person</option>
            @foreach($kontakOptions ?? [] as $kontak)
                <option value="{{ $kontak->id }}" 
                    {{ old('contact_person_id', $klien->contact_person_id ?? '') == $kontak->id ? 'selected' : '' }}>
                    {{ $kontak->nama }} - {{ $kontak->klien_nama }} ({{ $kontak->nomor_hp ?? 'No HP' }})
                </option>
            @endforeach
        </select>
        @error('contact_person_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
</div>
