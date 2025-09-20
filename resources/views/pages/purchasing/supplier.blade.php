@extends('layouts.app')
@section('title', 'Supplier - Kamil Maju Persada')
@section('content')

{{-- Welcome Banner --}}
<div class="bg-green-800 rounded-2xl p-4 sm:p-6 lg:p-8  mb-6 sm:mb-8 text-white shadow-lg mt-16 lg:mt-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">Supplier</h1>
            <p class="text-white     text-sm sm:text-base lg:text-lg">Kelola data supplier perusahaan</p>
        </div>
        <div class="hidden lg:block">
            <i class="fas fa-industry text-6xl text-white"></i>
        </div>
    </div>
</div>

{{-- Search and Filter Section --}}
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('supplier.index') }}" class="flex flex-col sm:flex-row gap-4">
        {{-- Search Input --}}
        <div class="flex-1">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama supplier, alamat, atau no HP..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </div>
        
        {{-- Filter Dropdown --}}
        <div class="sm:w-48">
            <select name="status" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
        </div>
        
        {{-- Search Button --}}
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            <a href="{{ route('supplier.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-refresh mr-2"></i>Reset
            </a>
        </div>
    </form>
</div>

{{-- Add Button --}}
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold text-gray-800">Daftar Supplier</h2>
    <button type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors" disabled>
        <i class="fas fa-plus mr-2"></i>Tambah Supplier
    </button>
</div>

{{-- Supplier List --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    @if($suppliers->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama Supplier
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Alamat
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No HP
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal Diubah
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($suppliers as $index => $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $suppliers->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $supplier->nama }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $supplier->alamat ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $supplier->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $supplier->updated_at->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center space-x-2">
                                    {{-- Detail Button --}}
                                    <button type="button" class="text-blue-600 hover:text-blue-800 transition-colors" disabled>
                                        <i class="fas fa-eye" title="Detail"></i>
                                    </button>
                                    
                                    {{-- Edit Button --}}
                                    <button type="button" class="text-yellow-600 hover:text-yellow-800 transition-colors" disabled>
                                        <i class="fas fa-edit" title="Edit"></i>
                                    </button>
                                    
                                    {{-- Delete Button --}}
                                    <button type="button" class="text-red-600 hover:text-red-800 transition-colors" disabled>
                                        <i class="fas fa-trash" title="Hapus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="px-6 py-4 bg-gray-50 border-t">
            {{ $suppliers->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data supplier</h3>
            <p class="text-gray-500">
                @if(request('search'))
                    Tidak ditemukan supplier dengan kata kunci "{{ request('search') }}"
                @else
                    Belum ada supplier yang terdaftar di sistem
                @endif
            </p>
        </div>
    @endif
</div>

@endsection