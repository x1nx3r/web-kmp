@extends('layouts.app')
@section('title', 'Detail Klien - Kamil Maju Persada')
@section('content')

<x-welcome-banner title="Detail Klien" subtitle="Informasi lengkap klien" icon="fas fa-user" />

<div class="max-w-3xl mx-auto mt-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-xl font-bold mb-4">{{ $klien->nama }}</h3>

        <div class="grid grid-cols-1 gap-4">
            <div>
                <p class="text-xs text-green-600 uppercase font-bold">Plant</p>
                <p class="text-sm text-gray-900">{{ $klien->cabang ?? '-' }}</p>
            </div>

            <div>
                <p class="text-xs text-green-600 uppercase font-bold">Contact Person</p>
                @if($klien->contactPerson)
                    <p class="text-sm text-gray-900">{{ $klien->contactPerson->nama }}</p>
                    <p class="text-xs text-gray-500">{{ $klien->contactPerson->nomor_hp ?? 'No HP tidak tersedia' }}</p>
                    @if($klien->contactPerson->jabatan)
                        <p class="text-xs text-gray-500">{{ $klien->contactPerson->jabatan }}</p>
                    @endif
                @else
                    <p class="text-sm text-gray-900">-</p>
                @endif
            </div>

            <div>
                <p class="text-xs text-green-600 uppercase font-bold">Tanggal Diubah</p>
                <p class="text-sm text-gray-900">{{ $klien->updated_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-2">
            <a href="{{ route('klien.edit', $klien) }}" class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg">Edit</a>
            <a href="{{ route('klien.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg">Kembali</a>
        </div>
    </div>
</div>

@endsection
