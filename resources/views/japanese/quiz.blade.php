@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Дасгал, тест', route('japanese.quizzes', $level)], [$quiz->title, null]]" />
    <x-page-header :title="$quiz->title" :lead="$quiz->description" />
    <x-japanese.voice-notice />

    @if ($quiz->audio?->file_path)
        <div class="mb-4">@include('japanese.partials.audio-player', ['src' => route('japanese.audio.file', $quiz->audio)])</div>
    @endif

    @unless ($canTake)
        <div class="notice notice--info mb-4">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <div class="notice-body">Админ асуултыг харах боломжтой, дүн бүртгэгдэхгүй.</div>
        </div>
    @endunless

    {{-- JavaScript-гүй үед бүх асуулт нэг хуудсанд, ажиллавал нэг нэгээр. --}}
    <form method="POST" action="{{ $canTake ? route('japanese.quizzes.submit', [$level, $quiz]) : '#' }}"
          @if ($canTake) data-quiz data-check-url="{{ route('japanese.quizzes.check', $quiz) }}" @endif novalidate>
        @csrf

        @php $total = $quiz->questions->count(); @endphp

        @foreach ($quiz->questions as $question)
            @php $choices = $question->answers->shuffle(); @endphp

            <fieldset class="jp-question card-surface card-pad mb-3" data-question="{{ $question->id }}">
                <legend tabindex="-1" class="jp-question-title">Асуулт {{ $loop->iteration }} / {{ $total }}: {{ $question->prompt }}</legend>

                @if ($question->prompt_ja)
                    <p class="jp-sentence-main"><x-ja>{{ $question->prompt_ja }}</x-ja></p>
                    <x-speak :text="$question->prompt_ja" label="Асуултын Япон текстийг сонсох" />
                @endif

                <div class="mt-3" data-controls>
                    @if ($question->kind === 'choice')
                        @foreach ($choices as $answer)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" id="a{{ $answer->id }}" name="answers[{{ $question->id }}]" value="{{ $answer->id }}">
                                <label class="form-check-label" for="a{{ $answer->id }}"><span lang="{{ $answer->lang }}">{{ $answer->text }}</span></label>
                            </div>
                        @endforeach
                    @elseif ($question->kind === 'match')
                        @foreach ($question->answers as $left)
                            <div class="mb-3">
                                <label class="form-label" for="m{{ $left->id }}"><x-ja>{{ $left->text }}</x-ja></label>
                                <select class="form-select" id="m{{ $left->id }}" name="answers[{{ $question->id }}][{{ $left->id }}]">
                                    <option value="">Сонгоно уу</option>
                                    @foreach ($choices as $right)
                                        <option value="{{ $right->id }}">{{ $right->match_text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    @else
                        @foreach ($question->answers as $slot)
                            <div class="mb-3">
                                <label class="form-label" for="o{{ $question->id }}-{{ $loop->iteration }}">{{ $loop->iteration }}-р хэсэг</label>
                                <select class="form-select" id="o{{ $question->id }}-{{ $loop->iteration }}" name="answers[{{ $question->id }}][{{ $loop->index }}]">
                                    <option value="">Сонгоно уу</option>
                                    @foreach ($choices as $piece)
                                        <option value="{{ $piece->id }}" lang="ja">{{ $piece->text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="jp-feedback mt-3" data-feedback></div>
            </fieldset>
        @endforeach

        @if ($canTake)
            {{-- Хуудаст үргэлж байх зарлалын бүс: асуулт нуугдах, гарч ирэхэд хамаарахгүй.
                 Шалгах товч дээрээ focus үлдэж, үр дүнг эндээс уншуулна. --}}
            <div class="visually-hidden" aria-live="polite" data-quiz-live></div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <button type="button" class="btn btn-primary btn-lg" data-quiz-action hidden>Хариулт шалгах</button>
                <p class="mb-0" data-quiz-score hidden></p>
                <button type="submit" class="btn btn-primary btn-lg" data-quiz-submit>Тестийг дуусгах</button>
            </div>
        @endif
    </form>
@endsection
