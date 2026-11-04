@extends('layouts.app')

@section('title', 'Файл хэт том')

@section('content')
    <div class="error-page">
        <i class="bi bi-file-earmark-x" aria-hidden="true"></i>
        <h1>Файл хэт том байна</h1>
        <p>Илгээсэн файл серверийн зөвшөөрсөн хэмжээнээс их байна. Жижиг файл сонгоод дахин оролдоно уу.</p>
        <a class="btn btn-primary" href="{{ url()->previous() }}">Буцах</a>
    </div>
@endsection
