@extends('layouts.app')

@section('title', 'Хугацаа дууссан')

@section('content')
    <div class="error-page">
        <i class="bi bi-hourglass-bottom" aria-hidden="true"></i>
        <h1>Хуудасны хугацаа дууссан</h1>
        <p>Аюулгүй байдлын үүднээс хуудасны хугацаа дууслаа. Хуудсаа дахин ачаалаад дахин оролдоно уу.</p>
        <a class="btn btn-primary" href="{{ route('home') }}">Нүүр хуудас руу очих</a>
    </div>
@endsection
