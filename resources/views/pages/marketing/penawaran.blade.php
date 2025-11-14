@extends('layouts.app')

@section('content')
    @livewire('marketing.penawaran', ['penawaran' => $penawaran ?? null])
@endsection