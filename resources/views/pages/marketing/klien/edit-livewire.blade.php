@extends('layouts.app')

@section('content')
    @livewire('marketing.edit-klien', ['klien' => $klien])
@endsection