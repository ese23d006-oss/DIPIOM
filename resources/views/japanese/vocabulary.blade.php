@extends('layouts.app')

@section('title', 'Үгийн сан')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Үгийн сан', null]]" />
    <x-page-header title="Үгийн сан" lead="Үгийг сэдвээр шүүж, дуудлагыг нь сонсоно.">
        <a class="btn btn-primary" href="{{ route('japanese.flashcards', $level) }}"><i class="bi bi-card-text" aria-hidden="true"></i> Flashcard</a>
    </x-page-header>
    <x-japanese.voice-notice />

    <form method="GET" action="{{ route('japanese.vocabulary', $level) }}" class="filter-bar card-surface" aria-label="Сэдвээр шүүх">
        <div>
            <label for="category" class="form-label">Сэдэв</label>
            <select id="category" name="category" class="form-select">
                <option value="">Бүх сэдэв</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($categorySlug === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div><button type="submit" class="btn btn-primary w-100">Шүүх</button></div>
    </form>

    @if ($words->isEmpty())
        <div class="empty-state"><p>Энэ хэсэгт нийтлэгдсэн үг алга байна.</p></div>
    @else
        <div class="table-card mt-4">
            <div class="table-responsive" tabindex="0" role="region" aria-label="Үгийн сангийн хүснэгт">
                <table class="table table-hover">
                    <caption class="visually-hidden">Япон үгс, уншлага, ромажи, Монгол орчуулга</caption>
                    <thead>
                        <tr>
                            <th scope="col">Япон</th>
                            <th scope="col">Уншлага</th>
                            <th scope="col">Ромажи</th>
                            <th scope="col">Монгол</th>
                            <th scope="col">Жишээ</th>
                            <th scope="col">Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($words as $word)
                            <tr>
                                <th scope="row" class="jp-word"><x-ja>{{ $word->word }}</x-ja></th>
                                <td class="jp-word-reading"><x-ja>{{ $word->katakana ?: $word->reading }}</x-ja></td>
                                <td><x-romaji>{{ $word->romaji }}</x-romaji></td>
                                <td>{{ $word->meaning_mn }}</td>
                                <td class="jp-example-cell">
                                    @if ($word->example_ja)
                                        <x-ja>{{ $word->example_ja }}</x-ja><br>
                                        <x-ja>{{ $word->example_reading }}</x-ja><br>
                                        <x-romaji>{{ $word->example_romaji }}</x-romaji><br>
                                        {{ $word->example_mn }}
                                    @else
                                        Жишээгүй
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <x-speak :text="$word->reading" :label="'Дуудлага сонсох: '.$word->romaji" />
                                        <x-japanese.mark-done type="vocabulary" :id="$word->id" :done="$done->contains($word->id)" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $words->links('pagination.accessible') }}
    @endif
@endsection
