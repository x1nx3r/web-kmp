@extends('layouts.app')

@section('title', 'Pengaturan Akun - Kamil Maju Persada')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-welcome-banner title="Pengaturan Akun" subtitle="Kelola Data Profil Anda" icon="fas fa-user-cog" />
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['title' => 'Dashboard', 'url' => route('dashboard')],
            'Pengaturan Akun'
        ]" />

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 shadow-sm" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 shadow-sm" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h4 class="font-semibold mb-1">Terjadi kesalahan:</h4>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Account Settings Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 sm:p-8">
                <form action="{{ route('pengaturan.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Profile Photo Section -->
                    <div class="text-center border-b border-gray-200 pb-8">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-6">Foto Profil</h3>
                        <div class="flex flex-col items-center space-y-4">
                            <div class="relative group">
                                <img id="preview-image" src="{{ $user->profile_photo_url }}"
                                     alt="Profile Photo" class="w-28 h-28 sm:w-36 sm:h-36 lg:w-40 lg:h-40 rounded-full object-cover border-4 border-white shadow-lg ring-4 ring-gray-100">
                                <label for="foto_profil" class="absolute inset-0 rounded-full bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 cursor-pointer">
                                    <div class="bg-green-600 text-white p-3 rounded-full hover:bg-green-700 transition-colors shadow-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                </label>
                                <input type="file" id="foto_profil" name="foto_profil" accept="image/*" class="hidden">
                            </div>
                            <div class="text-center px-4">
                                <p class="text-sm text-gray-600 mb-1">Klik pada foto untuk mengubah foto profil</p>
                                <p class="text-xs text-gray-500">Format: JPG, PNG, GIF (Maks. 2MB)</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                        <!-- Account Information -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 pb-4">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Informasi Akun
                                </h3>
                            </div>
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                                    @error('nama')
                                    <input type="text" name="nama" value="{{ old('nama', $user->nama) }}"
                                           class="w-full px-4 py-3 border-2 border-red-500 rounded-lg focus:ring-4 focus:ring-red-100 focus:border-red-500 text-sm transition-all duration-200 bg-red-50" required>
                                    @else
                                    <input type="text" name="nama" value="{{ old('nama', $user->nama) }}"
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-4 focus:ring-green-100 focus:border-green-500 text-sm transition-all duration-200 hover:border-gray-300" required>
                                    @enderror
                                    @error('nama')
                                        <p class="text-red-800 text-xs mt-1 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Username</label>
                                    @error('username')
                                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                                           class="w-full px-4 py-3 border-2 border-red-500 rounded-lg focus:ring-4 focus:ring-red-100 focus:border-red-500 text-sm transition-all duration-200 bg-red-50"
                                           placeholder="Masukkan username (opsional)">
                                    @else                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-4 focus:ring-green-100 focus:border-green-500 text-sm transition-all duration-200 hover:border-gray-300"
                           placeholder="Masukkan username (opsional)">
                                    @enderror
                                    @error('username')
                                        <p class="text-red-800 text-xs mt-1 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Email</label>
                                    <input type="email" value="{{ $user->email }}"
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-50 cursor-not-allowed text-sm text-gray-600" readonly>
                                    <p class="text-xs text-gray-500 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        Email tidak dapat diubah, Hubungi Direktur untuk Mengubahnya
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Role</label>
                                    <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-600 flex items-center">
                                        @php
                                        $roleLabels = [
                                            'direktur' => 'Direktur',
                                            'marketing' => 'Marketing',
                                            'manager_purchasing' => 'Manager Purchasing',
                                            'staff_purchasing' => 'Staff Purchasing',
                                            'staff_accounting' => 'Staff Accounting',
                                            'manager_accounting' => 'Manager Accounting'
                                        ];
                                        
                                        $roleColors = [
                                            'direktur' => 'bg-red-100 text-red-800',
                                            'marketing' => 'bg-purple-100 text-purple-800',
                                            'manager_purchasing' => 'bg-green-100 text-green-800', 
                                            'staff_purchasing' => 'bg-green-50 text-green-700',
                                            'staff_accounting' => 'bg-blue-50 text-blue-700',
                                            'manager_accounting' => 'bg-blue-100 text-blue-800'
                                        ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $roleLabels[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        Role tidak dapat diubah, Hubungi Direktur untuk Mengubahnya
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Status Akun</label>
                                    <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-600 flex items-center">
                                        @if($user->status === 'aktif')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3"/>
                                                </svg>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3"/>
                                                </svg>
                                                Tidak Aktif
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        Status akun tidak dapat diubah, Hubungi Direktur untuk Mengubahnya
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Password Change Section -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 pb-4">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Ubah Password
                                </h3>
                            </div>
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Password Saat Ini</label>
                                    @error('current_password')
                                    <input type="password" name="current_password"
                                           class="w-full px-4 py-3 border-2 border-red-500 rounded-lg focus:ring-4 focus:ring-red-100 focus:border-red-500 text-sm transition-all duration-200 bg-red-50"
                                           placeholder="Masukkan password saat ini">
                                    @else                    <input type="password" name="current_password"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-4 focus:ring-green-100 focus:border-green-500 text-sm transition-all duration-200 hover:border-gray-300"
                           placeholder="Masukkan password saat ini">
                                    @enderror
                                    @error('current_password')
                                        <p class="text-red-800 text-xs mt-1 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Password Baru</label>
                                    @error('password')
                                    <input type="password" name="password"
                                           class="w-full px-4 py-3 border-2 border-red-500 rounded-lg focus:ring-4 focus:ring-red-100 focus:border-red-500 text-sm transition-all duration-200 bg-red-50"
                                           placeholder="Masukkan password baru">
                                    @else                    <input type="password" name="password"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-4 focus:ring-green-100 focus:border-green-500 text-sm transition-all duration-200 hover:border-gray-300"
                           placeholder="Masukkan password baru">
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter, kombinasi huruf besar, kecil, dan angka</p>
                                    @error('password')
                                        <p class="text-red-800 text-xs mt-1 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_confirmation"                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-4 focus:ring-green-100 focus:border-green-500 text-sm transition-all duration-200 hover:border-gray-300"
                           placeholder="Ulangi password baru">
                                </div>

                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-amber-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="text-sm text-amber-800">
                                            <p class="font-semibold mb-1">Catatan Keamanan:</p>
                                            <p>Kosongkan field password jika tidak ingin mengubah password</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                            <button type="button" onclick="resetForm()"
                                    class="w-full sm:w-auto px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 text-sm font-semibold min-h-[48px] flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset
                            </button>
                            <button type="submit"
                                    class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-100 transition-all duration-200 text-sm font-semibold min-h-[48px] flex items-center justify-center shadow-lg hover:shadow-xl">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
// Photo preview functionality
document.getElementById('foto_profil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            this.value = '';
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

// Reset form functionality
function resetForm() {
    if (confirm('Apakah Anda yakin ingin mereset semua perubahan?')) {
        location.reload();
    }
}

// Enhanced form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const submitButton = e.target.querySelector('button[type="submit"]');

    // Add loading state
    submitButton.innerHTML = `
        <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Menyimpan...
    `;
    submitButton.disabled = true;
});
</script>

@endsection
