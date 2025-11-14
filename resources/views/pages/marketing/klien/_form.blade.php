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
        <label class="block text-sm font-medium text-gray-700">No HP</label>
        <input type="text" name="no_hp" value="{{ old('no_hp', $klien->no_hp ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
        @error('no_hp') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
</div>
