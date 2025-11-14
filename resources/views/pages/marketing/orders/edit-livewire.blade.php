@extends('layouts.app')

@section('title', 'Edit Order')

@section('content')
    <livewire:marketing.order-edit :order="$order" />
@endsection
