@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></li>
            <li><a href="{{ route('student.quizzes') }}">Шалгалтууд</a></li>
            <li><span aria-current="page">{{ $quiz->title }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$quiz->title" :lead="$quiz->description" />

    <p>Нийт {{ $quiz->questions->count() }} асуулт. Тэнцэх хувь {{ $quiz->pass_percent }}%. Цагийн хязгааргүй.</p>

    <x-form.errors :anchors="$quiz->questions->mapWithKeys(fn ($q) => ['answers.'.$q->id => 'question-'.$q->id])->all()" />

    <form method="POST" action="{{ route('quizzes.submit', [$course, $quiz]) }}" novalidate>
        @csrf

        @foreach ($quiz->questions as $question)
            @php $errorKey = 'answers.'.$question->id; @endphp

            <fieldset id="question-{{ $question->id }}" tabindex="-1" class="card-surface card-pad mb-3"
                      @error($errorKey) aria-describedby="question-{{ $question->id }}-error" @enderror>
                <legend class="jp-question-title">Асуулт {{ $loop->iteration }} / {{ $quiz->questions->count() }}: {{ $question->body }}</legend>
                <p class="text-small">{{ $question->points }} оноо</p>

                @foreach ($question->options as $index => $option)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" id="q{{ $question->id }}-{{ $index }}"
                               name="answers[{{ $question->id }}]" value="{{ $index }}"
                               @checked((string) old($errorKey, old('answers.'.$question->id)) === (string) $index)>
                        <label class="form-check-label" for="q{{ $question->id }}-{{ $index }}">{{ $option }}</label>
                    </div>
                @endforeach

                @error($errorKey)
                    <div id="question-{{ $question->id }}-error" class="field-error">
                        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </fieldset>
        @endforeach

        <button type="submit" class="btn btn-primary btn-lg">Шалгалтыг дуусгах</button>
    </form>
@endsection
