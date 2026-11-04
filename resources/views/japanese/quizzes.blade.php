@extends('layouts.app')

@section('title', 'Дасгал, тест')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Дасгал, тест', null]]" />
    <x-page-header title="Дасгал, тест" />

    @if ($quizzes->isEmpty())
        <div class="empty-state"><p>Нийтлэгдсэн тест алга байна.</p></div>
    @else
        <ul class="grid-cards">
            @foreach ($quizzes as $quiz)
                <li>
                    <article class="card-surface card-pad h-100">
                        <h2 class="h5"><a href="{{ route('japanese.quizzes.show', [$level, $quiz]) }}">{{ $quiz->title }}</a></h2>
                        <p class="mb-1 text-small">{{ \App\Models\Japanese\Quiz::TYPES[$quiz->type] ?? $quiz->type }}, {{ $quiz->questions_count }} асуулт</p>
                        <p class="mb-0 text-small">{{ isset($best[$quiz->id]) ? 'Хамгийн өндөр дүн: '.$best[$quiz->id].'%' : 'Хараахан өгөөгүй' }}</p>
                    </article>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
