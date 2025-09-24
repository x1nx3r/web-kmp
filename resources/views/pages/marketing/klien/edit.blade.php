@extends('layouts.app')
@section('title', 'Edit Klien - Kamil Maju Persada')
@section('content')

<x-welcome-banner title="Edit Klien" subtitle="Perbarui data klien" icon="fas fa-user-edit" />

<div class="max-w-3xl mx-auto mt-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('klien.update', $klien) }}" method="POST">
            @csrf
            @method('PUT')
            @include('pages.marketing.klien._form')

            <div class="mt-6 flex justify-end space-x-2">
                <a href="{{ route('klien.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection
