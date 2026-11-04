@extends('layouts.app')

@section('title', 'Дүрэм: '.$grammar->title)

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Дүрэм', route('japanese.grammar', $level)], [$grammar->title, null]]" />
    <h1 class="mb-3"><x-ja>{{ $grammar->title }}</x-ja></h1>
    <x-japanese.voice-notice />

    <section class="card-surface card-pad" aria-labelledby="grammar-info">
        <h2 id="grammar-info" class="h5">Тайлбар</h2>
        <dl class="jp-facts">
            <div><dt>Дүрмийн бүтэц</dt><dd><x-mixed :text="$grammar->structure" /></dd></div>
            <div><dt>Монгол тайлбар</dt><dd><x-mixed :text="$grammar->explanation_mn" /></dd></div>
            @if ($grammar->usage_mn)
                <div><dt>Хэрэглэх нөхцөл</dt><dd><x-mixed :text="$grammar->usage_mn" /></dd></div>
            @endif
        </dl>
        <x-japanese.mark-done type="grammar" :id="$grammar->id" :done="$isDone" />
    </section>

    <section class="section" aria-labelledby="grammar-examples">
        <div class="section-head"><h2 id="grammar-examples">Жишээ өгүүлбэр</h2></div>
        <div class="d-grid gap-3">
            @forelse ($grammar->examples as $example)
                <div class="card-surface card-pad"><x-japanese.sentence :ja="$example->ja" :reading="$example->reading" :romaji="$example->romaji" :mn="$example->mn" /></div>
            @empty
                <p>Жишээ оруулаагүй байна.</p>
            @endforelse
        </div>
    </section>

    @if ($quiz)
        <p class="mt-4"><a class="btn btn-primary" href="{{ route('japanese.quizzes.show', [$level, $quiz]) }}">Дүрмийн дасгал ажиллах</a></p>
    @endif
@endsection
