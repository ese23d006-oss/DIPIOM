@extends('layouts.app')

@section('title', $level->code.' түвшин')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, null]]" />
    <x-page-header :title="$level->code.': '.$level->name" :lead="$level->description" />

    <ul class="feature-grid">
        @foreach ($sections as [$name, $japanese, $description, $count, $url])
            <li class="feature d-flex flex-column gap-1">
                <h2 class="h5 mb-0">
                    @if ($count > 0)
                        <a href="{{ $url }}">{{ $name }}</a>
                    @else
                        {{ $name }}
                    @endif
                </h2>
                <p class="jp-subtitle mb-0"><x-ja>{{ $japanese }}</x-ja></p>
                <p class="mb-0">{{ $description }}</p>
                <p class="mb-0">{{ $count > 0 ? 'Нийтлэгдсэн: '.$count : 'Агуулга хараахан нийтлэгдээгүй' }}</p>
            </li>
        @endforeach
    </ul>
@endsection
