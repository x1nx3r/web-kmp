@extends('layouts.app')
@section('title', 'Tambah Klien - Kamil Maju Persada')
@section('content')

<x-welcome-banner title="Tambah Klien" subtitle="Buat klien baru" icon="fas fa-user-plus" />

<div class="max-w-3xl mx-auto mt-6">
    @if(auth()->user()->isMarketing() || auth()->user()->isDirektur())
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('klien.store') }}" method="POST">
            @csrf
            @include('pages.marketing.klien._form')

            <div class="mt-6 flex justify-end space-x-2">
                <a href="{{ route('klien.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
    @else
    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full mx-auto flex items-center justify-center mb-4">
            <i class="fas fa-lock text-red-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Akses Ditolak</h3>
        <p class="text-gray-600 mb-4">Anda tidak memiliki izin untuk menambah klien baru.</p>
        <a href="{{ route('klien.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg inline-block">Kembali</a>
    </div>
    @endif
</div>

@endsection
