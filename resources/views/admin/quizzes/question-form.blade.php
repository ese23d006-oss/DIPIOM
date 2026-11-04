@extends('layouts.app')

@php $isNew = ! $question->exists; @endphp

@section('title', $isNew ? 'Асуулт нэмэх' : 'Асуулт засах')

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.quizzes.edit', $quiz) }}">{{ $quiz->title }}</a></li>
            <li><span aria-current="page">{{ $isNew ? 'Шинэ асуулт' : 'Асуулт засах' }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$isNew ? 'Асуулт нэмэх' : 'Асуулт засах'" />

    <x-form.errors :anchors="['options' => 'options-title']" />

    <form method="POST" action="{{ $isNew ? route('admin.quizzes.questions.store', $quiz) : route('admin.questions.update', $question) }}"
          class="card-surface card-pad form-wide" novalidate>
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <x-form.textarea name="body" label="Асуулт" :value="$question->body" required :rows="2" />
        <x-form.field name="points" label="Оноо" type="number" :value="$question->points" required />

        <fieldset class="mt-4">
            <legend id="options-title" class="h5" tabindex="-1">Хариултууд</legend>
            <p class="field-hint">Хамгийн багадаа 2 хариулт бичиж, зөв хариултыг тэмдэглэнэ. Хоосон мөр хадгалагдахгүй.</p>

            @for ($i = 0; $i < $rows; $i++)
                <div class="jp-answer-row mb-3">
                    <label class="form-label" for="option-{{ $i }}">Хариулт {{ $i + 1 }}</label>
                    <input class="form-control" id="option-{{ $i }}" name="options[{{ $i }}]" value="{{ old("options.$i", $question->options[$i] ?? '') }}">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" id="correct-{{ $i }}" name="correct" value="{{ $i }}"
                               @checked((string) old('correct', $question->exists ? $question->correct_option : '') === (string) $i)>
                        <label class="form-check-label" for="correct-{{ $i }}">Хариулт {{ $i + 1 }} зөв</label>
                    </div>
                </div>
            @endfor
        </fieldset>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Хадгалах</button>
            <a class="btn btn-outline-primary" href="{{ route('admin.quizzes.edit', $quiz) }}">Болих</a>
        </div>
    </form>
@endsection
