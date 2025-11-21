@extends('layouts.app')

@section('content')
    @livewire('procurement.evaluate-supplier', ['pengiriman' => $pengiriman])
@endsection
