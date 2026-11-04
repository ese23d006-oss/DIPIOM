@extends('layouts.app')

@section('title', 'Сонсох дасгал')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Сонсох дасгал', null]]" />
    <x-page-header title="Сонсох дасгал" />

    @if ($items->isEmpty())
        <div class="empty-state">
            <i class="bi bi-soundwave" aria-hidden="true"></i>
            <p>Одоогоор аудио файлтай сонсох дасгал нийтлэгдээгүй байна.</p>
        </div>
    @else
        <ul class="grid-cards">
            @foreach ($items as $item)
                <li>
                    <article class="card-surface card-pad h-100">
                        <h2 class="h5"><a href="{{ route('japanese.listening.show', [$level, $item]) }}">{{ $item->title }}</a></h2>
                        @if ($done->contains($item->id))
                            <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Дууссан</span>
                        @endif
                    </article>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
