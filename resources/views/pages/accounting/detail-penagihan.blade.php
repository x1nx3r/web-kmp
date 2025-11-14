@extends('layouts.app')
@section('title', 'Detail Invoice Penagihan - Kamil Maju Persada')
@section('content')
    @livewire('accounting.detail-penagihan', ['approvalId' => $approvalId])
@endsection
