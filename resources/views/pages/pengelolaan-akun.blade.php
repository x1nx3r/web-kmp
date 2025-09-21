@extends('layouts.app')
@section('title', 'Pengelolaan Akun - Kamil Maju Persada')
@section('content')
<x-welcome-banner title="Pengelolaan Akun" subtitle="Kelola Akun Perusahaan" icon="fas fa-users-cog" />
{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['title' => 'Pengelolaan Akun', 'url' => '/pengelolaan-akun']
]" />

{{-- Flash Messages --}}
@if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 sm:mb-6 flex items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-400 text-xl"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-green-800">
                {{ session('success') }}
            </p>
        </div>
        <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
                <button type="button" onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 sm:mb-6 flex items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-red-800">
                {{ session('error') }}
            </p>
        </div>
        <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
                <button type="button" onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Search and Filter Section --}}
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 mb-3 sm:mb-6">
    <div class="space-y-3 sm:space-y-6">
        {{-- Search Section --}}
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
            {{-- Search Input --}}
            <div class="flex-1">
                <label class="flex items-center text-xs sm:text-sm font-bold text-green-700 mb-1 sm:mb-3">
                    <div class="w-4 h-4 sm:w-6 sm:h-6 bg-green-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                        <i class="fas fa-search text-white text-xs"></i>
                    </div>
                    Pencarian
                </label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           placeholder="Cari nama, username, email, atau role..." 
                           class="w-full pl-8 sm:pl-12 pr-3 sm:pr-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-gray-50 focus:bg-white transition-all duration-200 text-sm"
                           onkeyup="searchUsers()">
                    <div class="absolute inset-y-0 left-0 pl-2 sm:pl-4 flex items-center pointer-events-none">
                        <div class="w-3 h-3 sm:w-6 sm:h-6 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-search text-green-500 text-xs sm:text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="rounded-lg sm:rounded-xl p-2 sm:p-4">
            <h3 class="flex items-center text-xs sm:text-sm font-bold text-green-700 mb-2 sm:mb-4">
                <div class="w-4 h-4 sm:w-6 sm:h-6 bg-green-500 rounded-full flex items-center justify-center mr-1 sm:mr-2">
                    <i class="fas fa-filter text-white text-xs"></i>
                </div>
                Filter & Urutan
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4">
                {{-- Status Filter --}}
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                        <i class="fas fa-toggle-on mr-1 sm:mr-2 text-green-500 text-xs"></i>
                        Filter Status
                    </label>
                    <select name="status" id="statusFilter" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFilters()">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                </div>

                {{-- Role Filter --}}
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                        <i class="fas fa-user-tag mr-1 sm:mr-2 text-green-500 text-xs"></i>
                        Filter Role
                    </label>
                    <select name="role" id="roleFilter" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFilters()">
                        <option value="">Semua Role</option>
                        <option value="direktur">Direktur</option>
                        <option value="marketing">Marketing</option>
                        <option value="manager_purchasing">Manager Purchasing</option>
                        <option value="staff_purchasing">Staff Purchasing</option>
                        <option value="staff_accounting">Staff Accounting</option>
                        <option value="manager_accounting">Manager Accounting</option>
                    </select>
                </div>

                {{-- Sort Order --}}
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-green-700 mb-1 sm:mb-2">
                        <i class="fas fa-sort mr-1 sm:mr-2 text-green-500 text-xs"></i>
                        Urutkan
                    </label>
                    <select name="sort" id="sortOrder" class="w-full py-2 sm:py-3 px-2 sm:px-4 border-2 border-green-200 rounded-lg focus:ring-2 sm:focus:ring-4 focus:ring-green-200 focus:border-green-500 bg-white transition-all duration-200 text-xs sm:text-sm" onchange="applyFilters()">
                        <option value="nama_asc">Nama A-Z</option>
                        <option value="nama_desc">Nama Z-A</option>
                        <option value="created_desc">Terbaru Dibuat</option>
                        <option value="created_asc">Terlama Dibuat</option>
                        <option value="updated_desc">Terakhir Diubah</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Button and Stats --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
    <div class="flex items-center space-x-4">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-500 rounded-full flex items-center justify-center mr-2 sm:mr-3">
                <i class="fas fa-users text-white text-xs sm:text-sm"></i>
            </div>
            Daftar Akun
        </h2>
        <div class="flex items-center space-x-4 text-sm">
            <span class="flex items-center">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-1"></span>
                <span class="text-green-700 font-medium" id="activeCount">{{ $users->where('status', 'aktif')->count() }} Aktif</span>
            </span>
            <span class="flex items-center">
                <span class="w-3 h-3 bg-red-500 rounded-full mr-1"></span>
                <span class="text-red-700 font-medium" id="inactiveCount">{{ $users->where('status', 'tidak_aktif')->count() }} Tidak Aktif</span>
            </span>
            <span class="text-gray-500 text-xs">
                ({{ $users->total() }} total akun)
            </span>
        </div>
    </div>
    <button type="button" onclick="openCreateModal()" class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg sm:rounded-xl transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-semibold text-sm">
        <i class="fas fa-plus mr-2"></i>Tambah Akun
    </button>
</div>

{{-- Users Table --}}
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm overflow-hidden">
    @if($users->count() > 0)
        {{-- Desktop Table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-green-50 to-green-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase tracking-wider">
                            <i class=" mr-2"></i>No
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase tracking-wider">
                            <i class="fas fa-user mr-2"></i>Pengguna
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase tracking-wider">
                            <i class="fas fa-envelope mr-2"></i>Email & Username
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase tracking-wider">
                            <i class="fas fa-user-tag mr-2"></i>Role
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase tracking-wider">
                            <i class="fas fa-toggle-on mr-2"></i>Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-green-700 uppercase tracking-wider">
                            <i class="fas fa-calendar mr-2"></i>Terakhir Diubah
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-green-700 uppercase tracking-wider">
                            <i class="fas fa-cogs mr-2"></i>Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="usersTableBody">
                    @foreach($users as $index => $user)
                        <tr class="hover:bg-green-20 transition-colors duration-200 user-row" 
                            data-nama="{{ strtolower($user->nama) }}"
                            data-username="{{ strtolower($user->username) }}"
                            data-email="{{ strtolower($user->email) }}"
                            data-role="{{ $user->role }}"
                            data-status="{{ $user->status }}">
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($user->foto_profil)
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->foto_profil }}" alt="{{ $user->nama }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                                <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->nama, 0, 2)) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-bold text-gray-900">{{ $user->nama }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                <div class="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-1 rounded mt-1">{{ $user->username }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @php
                                    $roleConfig = [
                                        'direktur' => ['label' => 'Direktur', 'color' => 'red', 'icon' => 'fas fa-crown'],
                                        'marketing' => ['label' => 'Marketing', 'color' => 'blue', 'icon' => 'fas fa-bullhorn'],
                                        'manager_purchasing' => ['label' => 'Manager Purchasing', 'color' => 'green', 'icon' => 'fas fa-user-tie'],
                                        'staff_purchasing' => ['label' => 'Staff Purchasing', 'color' => 'green', 'icon' => 'fas fa-user'],
                                        'staff_accounting' => ['label' => 'Staff Accounting', 'color' => 'yellow', 'icon' => 'fas fa-calculator'],
                                        'manager_accounting' => ['label' => 'Manager Accounting', 'color' => 'yellow', 'icon' => 'fas fa-chart-line']
                                    ];
                                    $config = $roleConfig[$user->role];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                    <i class="{{ $config['icon'] }} mr-1"></i>
                                    {{ $config['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($user->status == 'aktif')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $user->updated_at->format('d M Y') }}</span>
                                    <span class="text-xs text-gray-400">{{ $user->updated_at->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button onclick="viewUser({{ $user->id }})" class="text-blue-600 hover:text-blue-900 p-2 rounded-full hover:bg-blue-100 transition-all duration-200" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editUser({{ $user->id }})" class="text-yellow-600 hover:text-yellow-900 p-2 rounded-full hover:bg-yellow-100 transition-all duration-200" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleUserStatus({{ $user->id }}, '{{ $user->status }}')" class="text-green-600 hover:text-green-900 p-2 rounded-full hover:bg-green-100 transition-all duration-200" title="{{ $user->status == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fas fa-{{ $user->status == 'aktif' ? 'user-times' : 'user-check' }}"></i>
                                    </button>
                                    <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900 p-2 rounded-full hover:bg-red-100 transition-all duration-200" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile & Tablet Cards --}}
        <div class="lg:hidden space-y-2 p-2">
            @foreach($users as $index => $user)
                <div class="bg-white border border-gray-200 rounded-lg hover:shadow-md transition-all duration-200 user-card"
                     data-nama="{{ strtolower($user->nama) }}"
                     data-username="{{ strtolower($user->username) }}"
                     data-email="{{ strtolower($user->email) }}"
                     data-role="{{ $user->role }}"
                     data-status="{{ $user->status }}">
                    <div class="p-3">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2 flex-1 min-w-0">
                                <div class="flex-shrink-0">
                                    @if($user->foto_profil)
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->foto_profil }}" alt="{{ $user->nama }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->nama, 0, 2)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-1 mb-1">
                                        <span class="text-xs bg-green-100 text-green-700 font-bold px-1.5 py-0.5 rounded-full">
                                            {{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}
                                        </span>
                                        <h3 class="text-sm font-bold text-gray-900 truncate">{{ $user->nama }}</h3>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="flex-shrink-0 ml-2">
                                @if($user->status == 'aktif')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Tidak Aktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-2 text-xs">
                            <div>
                                <span class="text-gray-500">Username:</span>
                                <div class="font-mono bg-gray-100 px-1.5 py-0.5 rounded mt-0.5 text-xs truncate">{{ $user->username }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Role:</span>
                                @php
                                    $roleConfig = [
                                        'direktur' => ['label' => 'Direktur', 'color' => 'red', 'icon' => 'fas fa-crown'],
                                        'marketing' => ['label' => 'Marketing', 'color' => 'blue', 'icon' => 'fas fa-bullhorn'],
                                        'manager_purchasing' => ['label' => 'Manager Purchasing', 'color' => 'green', 'icon' => 'fas fa-user-tie'],
                                        'staff_purchasing' => ['label' => 'Staff Purchasing', 'color' => 'green', 'icon' => 'fas fa-user'],
                                        'staff_accounting' => ['label' => 'Staff Accounting', 'color' => 'yellow', 'icon' => 'fas fa-calculator'],
                                        'manager_accounting' => ['label' => 'Manager Accounting', 'color' => 'yellow', 'icon' => 'fas fa-chart-line']
                                    ];
                                    $config = $roleConfig[$user->role];
                                @endphp
                                <div class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800 mt-0.5">
                                    <i class="{{ $config['icon'] }} mr-1"></i>
                                    <span class="truncate">{{ $config['label'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <div class="text-xs text-gray-500 truncate flex-1 mr-2">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $user->updated_at->format('d M Y, H:i') }}
                            </div>
                            <div class="flex items-center space-x-1 flex-shrink-0">
                                <button onclick="viewUser({{ $user->id }})" class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-100 transition-all duration-200" title="Lihat Detail">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <button onclick="editUser({{ $user->id }})" class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-100 transition-all duration-200" title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button onclick="toggleUserStatus({{ $user->id }}, '{{ $user->status }}')" class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-100 transition-all duration-200" title="{{ $user->status == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="fas fa-{{ $user->status == 'aktif' ? 'user-times' : 'user-check' }} text-xs"></i>
                                </button>
                                <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-100 transition-all duration-200" title="Hapus">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-12">
            <div class="flex flex-col items-center">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Akun</h3>
                <p class="text-gray-500 mb-6">Mulai dengan menambahkan akun pengguna pertama</p>
                <button onclick="openCreateModal()" class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Akun
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Pagination --}}
@if($users->hasPages())
    <div class="bg-white rounded-lg shadow-sm p-4 mt-4">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            {{-- Pagination Info --}}
            <div class="flex items-center text-sm text-gray-700">
                <span class="mr-2">Menampilkan</span>
                <span class="font-medium text-green-600">{{ $users->firstItem() ?? 0 }}</span>
                <span class="mx-1">sampai</span>
                <span class="font-medium text-green-600">{{ $users->lastItem() ?? 0 }}</span>
                <span class="mx-1">dari</span>
                <span class="font-medium text-green-600">{{ $users->total() }}</span>
                <span class="ml-1">akun</span>
            </div>

            {{-- Pagination Links --}}
            <div class="flex items-center space-x-1">
                {{-- Previous Page --}}
                @if ($users->onFirstPage())
                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                        <i class="fas fa-chevron-left mr-1"></i>
                        Sebelumnya
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                        <i class="fas fa-chevron-left mr-1"></i>
                        Sebelumnya
                    </a>
                @endif

                {{-- Page Numbers --}}
                @if($users->lastPage() > 1)
                    <div class="hidden sm:flex items-center space-x-1">
                        @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                            @if ($page == $users->currentPage())
                                <span class="px-3 py-2 text-sm font-medium text-white bg-green-600 border border-green-600 rounded-lg">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    </div>

                    {{-- Mobile Page Indicator --}}
                    <div class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg">
                        {{ $users->currentPage() }} / {{ $users->lastPage() }}
                    </div>
                @endif

                {{-- Next Page --}}
                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition-colors">
                        Selanjutnya
                        <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                @else
                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                        Selanjutnya
                        <i class="fas fa-chevron-right ml-1"></i>
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- No Results Found --}}
<div id="noResults" class="bg-white rounded-lg shadow-sm p-8 text-center hidden">
    <div class="flex flex-col items-center">
        <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Hasil</h3>
        <p class="text-gray-500">Coba ubah kata kunci pencarian atau filter</p>
    </div>
</div>

@push('scripts')
<script>
// Search functionality
function searchUsers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.user-row');
    const cards = document.querySelectorAll('.user-card');
    const allItems = [...rows, ...cards];
    let visibleCount = 0;

    allItems.forEach(item => {
        const nama = item.dataset.nama || '';
        const username = item.dataset.username || '';
        const email = item.dataset.email || '';
        const role = item.dataset.role || '';

        const isVisible = nama.includes(searchTerm) || 
                         username.includes(searchTerm) || 
                         email.includes(searchTerm) || 
                         role.includes(searchTerm);

        if (isVisible) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Show/hide no results message
    const noResults = document.getElementById('noResults');
    const usersTable = document.querySelector('.bg-white.rounded-lg.shadow-sm.overflow-hidden');
    
    if (visibleCount === 0 && searchTerm !== '') {
        noResults.classList.remove('hidden');
        usersTable.classList.add('hidden');
    } else {
        noResults.classList.add('hidden');
        usersTable.classList.remove('hidden');
    }
}

// Apply filters
function applyFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const roleFilter = document.getElementById('roleFilter').value;
    const sortOrder = document.getElementById('sortOrder').value;
    
    const rows = document.querySelectorAll('.user-row');
    const cards = document.querySelectorAll('.user-card');
    const allItems = [...rows, ...cards];
    
    // Apply filters
    allItems.forEach(item => {
        const status = item.dataset.status;
        const role = item.dataset.role;
        
        const statusMatch = !statusFilter || status === statusFilter;
        const roleMatch = !roleFilter || role === roleFilter;
        
        if (statusMatch && roleMatch) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Update counts
    updateCounts();
}

// Update active/inactive counts
function updateCounts() {
    const visibleRows = document.querySelectorAll('.user-row:not([style*="display: none"]), .user-card:not([style*="display: none"])');
    let activeCount = 0;
    let inactiveCount = 0;
    
    visibleRows.forEach(item => {
        if (item.dataset.status === 'aktif') {
            activeCount++;
        } else {
            inactiveCount++;
        }
    });
    
    document.getElementById('activeCount').textContent = `${activeCount} Aktif`;
    document.getElementById('inactiveCount').textContent = `${inactiveCount} Tidak Aktif`;
}

// Handle pagination with filters
function goToPage(page) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('page', page);
    
    // Preserve search and filter parameters
    const searchValue = document.getElementById('searchInput').value;
    const statusValue = document.getElementById('statusFilter').value;
    const roleValue = document.getElementById('roleFilter').value;
    const sortValue = document.getElementById('sortOrder').value;
    
    if (searchValue) currentUrl.searchParams.set('search', searchValue);
    if (statusValue) currentUrl.searchParams.set('status', statusValue);
    if (roleValue) currentUrl.searchParams.set('role', roleValue);
    if (sortValue) currentUrl.searchParams.set('sort', sortValue);
    
    window.location.href = currentUrl.toString();
}

// Modal and action functions (placeholder)
function openCreateModal() {
    alert('Fitur tambah akun akan diimplementasi');
}

function viewUser(id) {
    openUserDetailModal(id);
}

function editUser(id) {
    alert(`Edit user ID: ${id}`);
}

function toggleUserStatus(id, currentStatus) {
    const newStatus = currentStatus === 'aktif' ? 'tidak_aktif' : 'aktif';
    const actionText = newStatus === 'aktif' ? 'mengaktifkan' : 'menonaktifkan';
    
    if (confirm(`Apakah Anda yakin ingin ${actionText} akun ini?`)) {
        alert(`Status user ID ${id} akan diubah menjadi ${newStatus}`);
    }
}

function deleteUser(id) {
    if (confirm('Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan.')) {
        alert(`User ID ${id} akan dihapus`);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateCounts();
});
</script>
@endpush

@endsection

{{-- Include Modal Components --}}
@include('pages.pengelolaan-akun-components.detail')