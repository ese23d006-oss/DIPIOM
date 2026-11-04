@extends('layouts.app')

@section('title', 'Ханз: '.$kanji->meaning_mn)

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Ханз', route('japanese.kanji', $level)], [$kanji->meaning_mn, null]]" />
    <h1 class="mb-3">Ханз: {{ $kanji->meaning_mn }}</h1>
    <x-japanese.voice-notice />

    <div class="grid-split">
        <section class="card-surface card-pad" aria-label="Ханзны мэдээлэл">
            <p class="jp-display"><x-ja>{{ $kanji->kanji }}</x-ja></p>
            <dl class="jp-facts">
                <div><dt>Онёми</dt><dd>@if ($kanji->onyomi)<x-ja>{{ $kanji->onyomi }}</x-ja>@else Байхгүй @endif</dd></div>
                <div><dt>Күнёми</dt><dd>@if ($kanji->kunyomi)<x-ja>{{ $kanji->kunyomi }}</x-ja>@else Байхгүй @endif</dd></div>
                <div><dt>Хирагана</dt><dd><x-ja>{{ $kanji->reading }}</x-ja></dd></div>
                <div><dt>Ромажи</dt><dd><x-romaji>{{ $kanji->romaji }}</x-romaji></dd></div>
                <div><dt>Монгол утга</dt><dd>{{ $kanji->meaning_mn }}</dd></div>
            </dl>
            <div class="d-flex flex-wrap gap-2">
                <x-speak :text="$kanji->reading" :label="'Ханзны уншлагыг сонсох: '.$kanji->reading" />
                <x-japanese.mark-done type="kanji" :id="$kanji->id" :done="$isDone" />
            </div>
            <p class="field-hint mt-3 mb-0">Баталгаат эх сурвалжтай бичлэгийн дараалал оруулаагүй тул энд харуулаагүй.</p>
        </section>

        <section class="card-surface card-pad" aria-labelledby="kanji-example">
            <h2 id="kanji-example" class="h5">Жишээ</h2>
            @if ($kanji->example_word)
                <dl class="jp-facts">
                    <div><dt>Жишээ үг</dt><dd><x-ja>{{ $kanji->example_word }}</x-ja></dd></div>
                    <div><dt>Уншлага</dt><dd><x-ja>{{ $kanji->example_reading }}</x-ja></dd></div>
                    <div><dt>Монгол</dt><dd>{{ $kanji->example_mn }}</dd></div>
                </dl>
            @endif
            @if ($kanji->example_sentence)
                <x-japanese.sentence :ja="$kanji->example_sentence" :reading="$kanji->example_sentence_reading" :mn="$kanji->example_sentence_mn" />
            @endif
            @unless ($kanji->example_word || $kanji->example_sentence)
                <p class="mb-0">Энэ ханзанд жишээ оруулаагүй байна.</p>
            @endunless
        </section>
    </div>
@endsection
