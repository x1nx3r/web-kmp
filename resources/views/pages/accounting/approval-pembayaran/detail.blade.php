@extends('layouts.app')

@section('content')
    @livewire('accounting.detail-pembayaran', ['approvalId' => $approvalId])
@endsection
