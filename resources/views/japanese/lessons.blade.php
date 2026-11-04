@extends('layouts.app')

@section('title', $typeName)

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], [$typeName, null]]" />
    <x-page-header :title="$typeName" />

    @if ($items->isEmpty())
        <div class="empty-state"><p>Энэ хэсэгт нийтлэгдсэн хичээл алга байна.</p></div>
    @else
        <ul class="grid-cards">
            @foreach ($items as $item)
                <li>
                    <article class="card-surface card-pad h-100">
                        <h2 class="h5"><a href="{{ route('japanese.lesson', [$level, $item]) }}">{{ $item->title }}</a></h2>
                        <p class="course-card-desc">{{ $item->intro_mn }}</p>
                        @if ($done->contains($item->id))
                            <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Дууссан</span>
                        @endif
                    </article>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
