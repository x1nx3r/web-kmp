@extends('layouts.app')

@section('title', 'Daftar Kontak Klien')

@section('content')
    @if(isset($klien) && $klien)
        @livewire('marketing.daftar-kontak', ['klien' => $klien])
    @else
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="mb-6 bg-white p-8 rounded-lg shadow text-center">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-5xl"></i>
                </div>
                <h1 class="text-xl font-bold text-gray-900 mb-2">Klien Tidak Ditemukan</h1>
                <p class="text-gray-600 mb-4">Silakan pilih klien terlebih dahulu untuk mengelola kontak.</p>
                <a href="{{ route('klien.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Klien
                </a>
            </div>
        </div>
    @endif
@endsection