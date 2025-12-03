@extends('layouts.app')

@section('title', 'Penawaran - Kamil Maju Persada')

@section('content')
    @livewire('marketing.penawaran', ['penawaran' => $penawaran ?? null])
@endsection
