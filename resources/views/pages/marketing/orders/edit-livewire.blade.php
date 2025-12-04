@extends('layouts.app')

@section('title', 'Edit Order - Kamil Maju Persada')

@section('content')
    <livewire:marketing.order-edit :order="$order" />
@endsection
