@extends('layouts.app')

@section('title', 'Ханз')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Ханз', null]]" />
    <x-page-header title="Ханз" lead="Ханзыг сонгож уншлага, утга, жишээг нь үзнэ." />

    @if ($items->isEmpty())
        <div class="empty-state"><p>Энэ хэсэгт нийтлэгдсэн ханз алга байна.</p></div>
    @else
        <ul class="jp-kana-grid" data-arrow-nav>
            @foreach ($items as $item)
                <li>
                    <a class="jp-kana-link" href="{{ route('japanese.kanji.show', [$level, $item]) }}">
                        <x-ja class="jp-kana-char">{{ $item->kanji }}</x-ja>
                        <span class="jp-kana-romaji">{{ $item->meaning_mn }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
