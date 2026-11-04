@extends('layouts.app')

@section('title', 'Хандах эрхгүй')

@section('content')
    <div class="error-page">
        <i class="bi bi-shield-lock" aria-hidden="true"></i>
        <h1>Хандах эрхгүй байна</h1>
        <p>Танд энэ хуудсыг үзэх эрх байхгүй.</p>
        <a class="btn btn-primary" href="{{ auth()->check() ? auth()->user()->dashboardUrl() : route('home') }}">Буцах</a>
    </div>
@endsection
