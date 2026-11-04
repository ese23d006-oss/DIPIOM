@extends('layouts.app')

@section('title', $scriptName)

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], [$scriptName, null]]" />
    @php $quiz = $level->quizzes()->published()->where('type', $script)->first(); @endphp
    <x-page-header :title="$scriptName" lead="Үсгийг сонгож дуудлага, жишээ үгийг нь үзнэ. Сум товчоор жагсаалтад шилжиж болно.">
        @if ($quiz)
            <a class="btn btn-primary" href="{{ route('japanese.quizzes.show', [$level, $quiz]) }}">Дасгал ажиллах</a>
        @endif
    </x-page-header>

    <div data-arrow-nav>
        @forelse ($rows as $row => $characters)
            <section class="mb-4" aria-labelledby="row-{{ $loop->index }}">
                <h2 id="row-{{ $loop->index }}" class="h5">{{ $row }}</h2>
                <ul class="jp-kana-grid">
                    @foreach ($characters as $character)
                        <li>
                            <a class="jp-kana-link" href="{{ route('japanese.character', [$level, $character]) }}">
                                <x-ja class="jp-kana-char">{{ $character->character }}</x-ja>
                                <x-romaji class="jp-kana-romaji">{{ $character->romaji }}</x-romaji>
                                <span class="jp-kana-romaji">{{ $character->reading_mn }}</span>
                                @if ($done->contains($character->id))
                                    <span class="jp-kana-romaji"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> сурсан</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <div class="empty-state"><p>Энэ хэсэгт нийтлэгдсэн үсэг алга байна.</p></div>
        @endforelse
    </div>
@endsection
