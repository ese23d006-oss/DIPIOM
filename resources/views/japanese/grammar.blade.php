@extends('layouts.app')

@section('title', 'Дүрэм')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Дүрэм', null]]" />
    <x-page-header title="Дүрэм" />

    @if ($items->isEmpty())
        <div class="empty-state"><p>Энэ хэсэгт нийтлэгдсэн дүрэм алга байна.</p></div>
    @else
        <ul class="grid-cards">
            @foreach ($items as $item)
                <li>
                    <article class="card-surface card-pad h-100">
                        <h2 class="h5"><a href="{{ route('japanese.grammar.show', [$level, $item]) }}"><x-ja>{{ $item->title }}</x-ja></a></h2>
                        <p class="course-card-desc"><x-mixed :text="$item->explanation_mn" /></p>
                        @if ($done->contains($item->id))
                            <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Сурсан</span>
                        @endif
                    </article>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
