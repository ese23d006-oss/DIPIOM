@extends('layouts.app')

@php
    $isNew = ! $question->exists;
    $answers = $question->exists ? $question->answers->values() : collect();
@endphp

@section('title', $isNew ? 'Асуулт нэмэх' : 'Асуулт засах')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('admin.japanese.hub')], ['Тест, дасгал', route('admin.japanese.index', 'quizzes')], [$quiz->title, route('admin.japanese.edit', ['quizzes', $quiz->id])], [$isNew ? 'Шинэ асуулт' : 'Асуулт засах', null]]" />
    <x-page-header :title="$isNew ? 'Асуулт нэмэх' : 'Асуулт засах'" />

    <x-form.errors :anchors="['answers' => 'answers-title']" />

    <form method="POST" action="{{ $isNew ? route('admin.japanese.questions.store', $quiz) : route('admin.japanese.questions.update', $question) }}"
          class="card-surface card-pad form-wide" novalidate>
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <x-form.select name="kind" label="Асуултын төрөл" :value="$question->kind" :options="\App\Models\Japanese\Question::KINDS" />
        <x-form.textarea name="prompt" label="Асуулт (Монгол)" :value="$question->prompt" required :rows="2" />
        <x-form.textarea name="prompt_ja" label="Япон текст (шаардлагатай бол)" :value="$question->prompt_ja" :rows="2" lang="ja" />
        <x-form.textarea name="explanation_mn" label="Монгол тайлбар" :value="$question->explanation_mn" :rows="2" hint="Хариулсны дараа суралцагчид харагдана." />
        <x-form.field name="points" label="Оноо" type="number" :value="$question->points" required />

        <fieldset class="mt-4">
            <legend id="answers-title" class="h5" tabindex="-1">Хариултууд</legend>
            <p class="field-hint">
                Сонгох асуултад зөв хариултыг тэмдэглэнэ. Тааруулах асуултад "Тохирох утга"-г бөглөнө.
                Дараалал асуултад хэсгүүдийг зөв дарааллаар бичнэ. Хоосон мөр хадгалагдахгүй.
            </p>

            @for ($i = 0; $i < $rows; $i++)
                @php $answer = $answers->get($i); @endphp
                <div class="jp-answer-row mb-3">
                    <h2 class="h6">Хариулт {{ $i + 1 }}</h2>
                    <div class="filter-bar p-0 mb-0">
                        <div>
                            <label class="form-label" for="answer-{{ $i }}">Текст</label>
                            <input class="form-control" id="answer-{{ $i }}" name="answers[{{ $i }}][text]" value="{{ old("answers.$i.text", $answer?->text) }}">
                        </div>
                        <div>
                            <label class="form-label" for="match-{{ $i }}">Тохирох утга</label>
                            <input class="form-control" id="match-{{ $i }}" name="answers[{{ $i }}][match_text]" value="{{ old("answers.$i.match_text", $answer?->match_text) }}">
                        </div>
                        <div>
                            <label class="form-label" for="lang-{{ $i }}">Хэл</label>
                            <select class="form-select" id="lang-{{ $i }}" name="answers[{{ $i }}][lang]">
                                @foreach (['ja' => 'Япон', 'mn' => 'Монгол', 'ja-Latn' => 'Ромажи'] as $code => $name)
                                    <option value="{{ $code }}" @selected(old("answers.$i.lang", $answer?->lang ?? 'ja') === $code)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" id="correct-{{ $i }}" name="correct" value="{{ $i }}" @checked((string) old('correct', $answer?->is_correct ? $i : null) === (string) $i)>
                        <label class="form-check-label" for="correct-{{ $i }}">Зөв хариулт {{ $i + 1 }}</label>
                    </div>
                </div>
            @endfor
        </fieldset>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Хадгалах</button>
            <a class="btn btn-outline-primary" href="{{ route('admin.japanese.edit', ['quizzes', $quiz->id]) }}">Болих</a>
        </div>
    </form>
@endsection
