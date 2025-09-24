@extends('layouts.app')
@section('title', 'Tambah Klien - Kamil Maju Persada')
@section('content')

<x-welcome-banner title="Tambah Klien" subtitle="Buat klien baru" icon="fas fa-user-plus" />

<div class="max-w-3xl mx-auto mt-6">
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
</div>

@endsection
