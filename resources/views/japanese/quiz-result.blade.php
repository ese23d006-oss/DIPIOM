@extends('layouts.app')

@section('title', 'Тестийн дүн')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Дасгал, тест', route('japanese.quizzes', $level)], ['Дүн', null]]" />
    <x-page-header :title="$quiz->title.': дүн'" />

    <div @class(['notice', 'mb-4', 'notice--success' => $result->passed, 'notice--warning' => ! $result->passed]) role="status" tabindex="-1" data-focus-on-load>
        <i class="bi {{ $result->passed ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}" aria-hidden="true"></i>
        <div class="notice-body">
            {{ $result->score }} / {{ $result->max_score }} оноо ({{ $result->percent }}%).
            {{ $result->passed ? 'Та тэнцлээ.' : 'Та тэнцээгүй, дахин оролдоно уу.' }}
        </div>
    </div>

    <div class="d-grid gap-3">
        @foreach ($review as $row)
            <section class="card-surface card-pad" aria-labelledby="q-{{ $row['question']->id }}">
                <h2 id="q-{{ $row['question']->id }}" class="h6">Асуулт {{ $loop->iteration }}: {{ $row['question']->prompt }}</h2>
                @if ($row['question']->prompt_ja)
                    <p class="jp-sentence-main"><x-ja>{{ $row['question']->prompt_ja }}</x-ja></p>
                @endif
                <p class="mb-1">
                    <span @class(['badge-status', 'badge-status--success' => $row['correct'], 'badge-status--error' => ! $row['correct']])>
                        <i class="bi {{ $row['correct'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}" aria-hidden="true"></i>
                        {{ $row['correct'] ? 'Зөв' : 'Буруу' }}
                    </span>
                </p>
                <p class="mb-1">Таны хариулт: @if ($row['given'])<x-mixed :text="$row['given']" />@else Хариулаагүй @endif</p>
                @unless ($row['correct'])
                    <p class="mb-1">Зөв хариулт: <x-mixed :text="$row['answer']" /></p>
                @endunless
                @if ($row['question']->explanation_mn)
                    <p class="mb-0">Тайлбар: <x-mixed :text="$row['question']->explanation_mn" /></p>
                @endif
            </section>
        @endforeach
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <a class="btn btn-primary" href="{{ route('japanese.quizzes.show', [$level, $quiz]) }}">Дахин өгөх</a>
        <a class="btn btn-outline-primary" href="{{ route('japanese.quizzes', $level) }}">Бусад тест</a>
    </div>
@endsection
