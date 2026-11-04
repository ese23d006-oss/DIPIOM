@extends('layouts.app')

@section('title', 'Flashcard')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Flashcard', null]]" />
    <x-page-header title="Flashcard" lead="Үгээ бодоод хариултаа нээж, мэдлэгээ тэмдэглэнэ." />
    <x-japanese.voice-notice />

    <dl class="grid-stats mb-3">
        <div class="stat"><dt><span class="stat-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>Мэднэ</dt><dd>{{ $counts['known'] ?? 0 }}</dd></div>
        <div class="stat"><dt><span class="stat-icon" aria-hidden="true"><i class="bi bi-arrow-repeat"></i></span>Дахин давтах</dt><dd>{{ $counts['review'] ?? 0 }}</dd></div>
        <div class="stat"><dt><span class="stat-icon" aria-hidden="true"><i class="bi bi-exclamation-circle"></i></span>Хэцүү</dt><dd>{{ $counts['hard'] ?? 0 }}</dd></div>
    </dl>

    <form method="GET" action="{{ route('japanese.flashcards', $level) }}" class="filter-bar card-surface" aria-label="Картын шүүлт">
        <div>
            <label for="filter" class="form-label">Аль картыг давтах вэ</label>
            <select id="filter" name="filter" class="form-select">
                @foreach (['all' => 'Бүх үг', 'new' => 'Шинэ үг', 'known' => 'Мэдэх үг', 'review' => 'Дахин давтах үг', 'hard' => 'Хэцүү үг'] as $value => $label)
                    <option value="{{ $value }}" @selected($filter === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div><button type="submit" class="btn btn-primary w-100">Сонгох</button></div>
    </form>

    @if (! $word)
        <div class="empty-state mt-4"><p>Энэ шүүлтэд карт алга байна.</p></div>
    @else
        <article class="jp-flashcard card-surface card-pad mt-4" aria-labelledby="card-title">
            <h2 id="card-title" class="h5">Карт {{ $index + 1 }} / {{ $total }}: урд тал</h2>
            <p class="jp-display"><x-ja>{{ $word->word }}</x-ja></p>
            <x-speak :text="$word->reading" :label="'Япон үгийг сонсох: '.$word->word" />

            {{-- details нь гар болон дэлгэц уншигчид нээлттэй, хаалттай төлвөө өөрөө мэдэгдэнэ. --}}
            <details class="jp-flashcard-back mt-3">
                <summary>Хариултыг харуулах</summary>
                <h3 class="h6 mt-2">Картын ар тал</h3>
                <dl class="jp-facts">
                    <div><dt>Уншлага</dt><dd><x-ja>{{ $word->katakana ?: $word->reading }}</x-ja></dd></div>
                    <div><dt>Ромажи</dt><dd><x-romaji>{{ $word->romaji }}</x-romaji></dd></div>
                    <div><dt>Монгол орчуулга</dt><dd>{{ $word->meaning_mn }}</dd></div>
                    @if ($word->example_ja)
                        <div><dt>Жишээ өгүүлбэр</dt><dd><x-ja>{{ $word->example_ja }}</x-ja></dd></div>
                        <div><dt>Жишээний уншлага</dt><dd><x-ja>{{ $word->example_reading }}</x-ja></dd></div>
                        <div><dt>Жишээний орчуулга</dt><dd>{{ $word->example_mn }}</dd></div>
                    @endif
                </dl>
                @if ($word->example_ja)
                    <x-speak :text="$word->example_reading" label="Жишээ өгүүлбэрийг сонсох" />
                @endif
            </details>

            @if ($card)
                <p class="mt-3 mb-0">Одоогийн тэмдэглэл: {{ \App\Models\Japanese\Flashcard::STATUSES[$card->status] }}</p>
            @endif

            @unless (auth()->user()->isAdmin())
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @foreach (\App\Models\Japanese\Flashcard::STATUSES as $status => $label)
                        <form method="POST" action="{{ route('japanese.flashcards.update', [$level, $word]) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $status }}">
                            <input type="hidden" name="filter" value="{{ $filter }}">
                            <input type="hidden" name="card" value="{{ $index }}">
                            <button type="submit" @class(['btn', 'btn-primary' => $status === 'known', 'btn-outline-primary' => $status !== 'known'])>{{ $label }}</button>
                        </form>
                    @endforeach
                </div>
            @endunless

            <nav class="lesson-nav mt-3" aria-label="Өмнөх, дараагийн карт">
                @if ($index > 0)
                    <a class="btn btn-outline-primary" href="{{ route('japanese.flashcards', [$level, 'filter' => $filter, 'card' => $index - 1]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Өмнөх карт</a>
                @endif
                @if ($index + 1 < $total)
                    <a class="btn btn-outline-primary" href="{{ route('japanese.flashcards', [$level, 'filter' => $filter, 'card' => $index + 1]) }}">Дараагийн үг <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endif
            </nav>
        </article>
    @endif
@endsection
