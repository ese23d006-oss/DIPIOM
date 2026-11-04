@extends('layouts.app')

@section('title', $audio->title)

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], ['Сонсох дасгал', route('japanese.listening', $level)], [$audio->title, null]]" />
    <x-page-header :title="$audio->title" />

    @include('japanese.partials.audio-player', ['src' => $audio->file_path ? route('japanese.audio.file', $audio) : null])

    <details class="card-surface card-pad mt-4">
        <summary>Бичвэр болон орчуулгыг харах</summary>
        <x-japanese.sentence :ja="$audio->transcript_ja" :reading="$audio->reading" :romaji="$audio->romaji" :mn="$audio->translation_mn" />
    </details>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <x-japanese.mark-done type="audio" :id="$audio->id" :done="$isDone" />
        @if ($quiz)
            <a class="btn btn-primary" href="{{ route('japanese.quizzes.show', [$level, $quiz]) }}">Сонссон зүйлийн асуултад хариулах</a>
        @endif
    </div>
@endsection
