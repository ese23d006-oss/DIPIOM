@extends('layouts.app')

@section('title', 'Шалгалтын үр дүн')

@section('content')
    @php $quiz = $result->quiz; @endphp

    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('student.quizzes') }}">Шалгалтууд</a></li>
            <li><span aria-current="page">{{ $quiz->title }}: үр дүн</span></li>
        </ol>
    </nav>

    <x-page-header :title="$quiz->title.': үр дүн'" />

    <div @class(['notice', 'mb-4', 'notice--success' => $result->passed, 'notice--warning' => ! $result->passed]) role="status" tabindex="-1" data-focus-on-load>
        <i class="bi {{ $result->passed ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}" aria-hidden="true"></i>
        <div class="notice-body">
            {{ $result->score }} / {{ $result->max_score }} оноо ({{ $result->percent }}%).
            {{ $result->passed ? 'Та тэнцлээ.' : 'Та тэнцээгүй, дахин оролдоно уу.' }}
        </div>
    </div>

    <div class="d-grid gap-3">
        @foreach ($result->answers as $row)
            <section class="card-surface card-pad" aria-labelledby="review-{{ $loop->iteration }}">
                <h2 id="review-{{ $loop->iteration }}" class="h6">Асуулт {{ $loop->iteration }}: {{ $row['body'] }}</h2>
                <p class="mb-1">
                    <span @class(['badge-status', 'badge-status--success' => $row['is_correct'], 'badge-status--error' => ! $row['is_correct']])>
                        <i class="bi {{ $row['is_correct'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}" aria-hidden="true"></i>
                        {{ $row['is_correct'] ? 'Зөв' : 'Буруу' }}
                    </span>
                </p>
                <p class="mb-1">Таны хариулт: {{ $row['options'][$row['given']] ?? 'Хариулаагүй' }}</p>
                @unless ($row['is_correct'])
                    <p class="mb-0">Зөв хариулт: {{ $row['options'][$row['correct_option']] ?? '' }}</p>
                @endunless
            </section>
        @endforeach
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        @if ($quiz->is_published && $quiz->course->is_published)
            <a class="btn btn-primary" href="{{ route('quizzes.show', [$quiz->course, $quiz]) }}">Дахин өгөх</a>
        @endif
        <a class="btn btn-outline-primary" href="{{ route('student.quizzes') }}">Бүх шалгалт</a>
    </div>
@endsection
