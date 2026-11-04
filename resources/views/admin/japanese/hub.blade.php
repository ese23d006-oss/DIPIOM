@extends('layouts.app')

@section('title', 'Япон хэлний удирдлага')

@section('content')
    <x-page-header title="Япон хэлний удирдлага" lead="Агуулгыг шалгаад нийтэлнэ. Ноорог агуулга суралцагчдад харагдахгүй.">
        <a class="btn btn-outline-primary" href="{{ route('admin.japanese.progress') }}"><i class="bi bi-graph-up" aria-hidden="true"></i> Суралцагчдын явц</a>
        <a class="btn btn-outline-primary" href="{{ route('japanese.index') }}">Суралцагчийн хуудас</a>
    </x-page-header>

    <div class="notice notice--warning mb-4">
        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
        <div class="notice-body">
            Анхны N5 агуулгыг багш, эх сурвалжаар баталгаажуулаагүй. Хирагана, катакана нь нийтлэгдсэн, бусад нь ноорог.
            Нийтлэхийн өмнө бичлэг, уншлага, орчуулгыг шалгана уу.
        </div>
    </div>

    <ul class="grid-cards">
        @foreach ($sections as $section)
            <li>
                <article class="card-surface card-pad h-100">
                    <h2 class="h5"><a href="{{ route('admin.japanese.index', $section['key']) }}">{{ $section['label'] }}</a></h2>
                    <p class="mb-0">Нийт: {{ $section['total'] }}@if ($section['draft'] !== null), ноорог: {{ $section['draft'] }}@endif</p>
                </article>
            </li>
        @endforeach
    </ul>
@endsection
