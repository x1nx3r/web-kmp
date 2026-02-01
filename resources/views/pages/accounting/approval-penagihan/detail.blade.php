@extends('layouts.app')

@section('title', 'Detail Approval Penagihan')

@section('content')
    @livewire('accounting.detail-penagihan', ['approvalId' => $approvalId, 'editMode' => $editMode ?? false])
@endsection
