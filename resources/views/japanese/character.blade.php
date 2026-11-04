@extends('layouts.app')

@section('title', $scriptName.': '.$character->romaji)

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], [$scriptName, route('japanese.kana', [$level, $character->script])], [$character->romaji, null]]" />

    <h1 class="mb-3">{{ $scriptName }}: <x-romaji>{{ $character->romaji }}</x-romaji></h1>
    <x-japanese.voice-notice />

    <div class="grid-split">
        <section class="card-surface card-pad" aria-label="Үсгийн мэдээлэл">
            <p class="jp-display"><x-ja>{{ $character->character }}</x-ja></p>
            <dl class="jp-facts">
                <div><dt>Ромажи</dt><dd><x-romaji>{{ $character->romaji }}</x-romaji></dd></div>
                <div><dt>Монгол дуудлага</dt><dd>{{ $character->reading_mn }}</dd></div>
                <div><dt>Эгнээ</dt><dd>{{ $character->row_name }}</dd></div>
            </dl>
            <div class="d-flex flex-wrap gap-2">
                <x-speak :text="$character->character" :label="'Үсгийн дуудлагыг сонсох: '.$character->romaji" />
                <x-japanese.mark-done type="character" :id="$character->id" :done="$isDone" />
            </div>
        </section>

        <section class="card-surface card-pad" aria-labelledby="example-title">
            <h2 id="example-title" class="h5">Жишээ үг</h2>
            @if ($character->example_word)
                <p class="jp-sentence-main"><x-ja>{{ $character->example_word }}</x-ja></p>
                <dl class="jp-facts">
                    <div><dt>Уншлага</dt><dd><x-ja>{{ $character->example_reading }}</x-ja></dd></div>
                    <div><dt>Ромажи</dt><dd><x-romaji>{{ $character->example_romaji }}</x-romaji></dd></div>
                    <div><dt>Монгол</dt><dd>{{ $character->example_mn }}</dd></div>
                </dl>
                <x-speak :text="$character->example_reading" :label="'Жишээ үгийг сонсох: '.$character->example_word" />
            @else
                <p class="mb-0">Энэ үсэгт жишээ үг оруулаагүй байна.</p>
            @endif
        </section>
    </div>

    <nav class="lesson-nav mt-4" aria-label="Өмнөх, дараагийн үсэг">
        @if ($previous)
            <a class="btn btn-outline-primary" href="{{ route('japanese.character', [$level, $previous]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Өмнөх үсэг: <x-romaji>{{ $previous->romaji }}</x-romaji></a>
        @endif
        @if ($next)
            <a class="btn btn-primary" href="{{ route('japanese.character', [$level, $next]) }}">Дараагийн үсэг: <x-romaji>{{ $next->romaji }}</x-romaji> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        @endif
    </nav>
@endsection
