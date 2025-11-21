@extends('layouts.app')

@section('content')
    @livewire('procurement.view-evaluation', ['pengirimanId' => $pengirimanId])
@endsection
