@extends('layouts.app')

@section('title', 'Edit Klien - Kamil Maju Persada')

@section('content')
    @livewire('marketing.edit-klien', ['klien' => $klien])
@endsection
