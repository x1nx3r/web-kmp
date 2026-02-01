@extends('layouts.app')

@section('title', 'Detail Pembayaran - Kamil Maju Persada')

@section('content')
    @livewire('accounting.detail-pembayaran', ['approvalId' => $approvalId, 'editMode' => $editMode ?? false])
@endsection
