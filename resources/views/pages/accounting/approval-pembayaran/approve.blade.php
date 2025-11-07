@extends('layouts.app')

@section('content')
    @livewire('accounting.approve-pembayaran', ['approvalId' => $approvalId])
@endsection
