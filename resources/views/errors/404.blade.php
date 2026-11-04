@extends('layouts.app')

@section('title', 'Хуудас олдсонгүй')

@section('content')
    <div class="error-page">
        <i class="bi bi-signpost-split" aria-hidden="true"></i>
        <h1>Хуудас олдсонгүй</h1>
        <p>Таны хайсан хуудас байхгүй эсвэл зөөгдсөн байна.</p>
        <a class="btn btn-primary" href="{{ route('home') }}">Нүүр хуудас руу очих</a>
    </div>
@endsection
