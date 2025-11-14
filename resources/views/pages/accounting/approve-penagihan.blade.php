@extends('layouts.app')
@section('title', 'Approve Penagihan - Kamil Maju Persada')
@section('content')
    @livewire('accounting.approve-penagihan', ['approvalId' => $approvalId])
@endsection
