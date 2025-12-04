@extends('layouts.app')

@section('title', 'Approve Pembayaran - Kamil Maju Persada')

@section('content')
    @livewire('accounting.approve-pembayaran', ['approvalId' => $approvalId])
@endsection
