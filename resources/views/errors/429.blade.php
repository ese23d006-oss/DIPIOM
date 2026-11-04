@extends('layouts.app')

@section('title', 'Хэт олон хүсэлт')

@section('content')
    <div class="error-page">
        <i class="bi bi-hourglass-split" aria-hidden="true"></i>
        <h1>Хэт олон хүсэлт илгээлээ</h1>
        <p>Түр хүлээгээд дахин оролдоно уу.</p>
        <a class="btn btn-primary" href="{{ route('home') }}">Нүүр хуудас руу очих</a>
    </div>
@endsection
