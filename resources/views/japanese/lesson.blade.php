@extends('layouts.app')

@section('title', $lesson->title)

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('japanese.index')], [$level->code, route('japanese.level', $level)], [$typeName, route('japanese.lessons', [$level, $lesson->type])], [$lesson->title, null]]" />
    <x-page-header :title="$lesson->title" :lead="$lesson->intro_mn" />
    <x-japanese.voice-notice />

    <div class="d-grid gap-3">
        @foreach ($lesson->examples as $line)
            <div class="card-surface card-pad"><x-japanese.sentence :ja="$line->ja" :reading="$line->reading" :romaji="$line->romaji" :mn="$line->mn" :speaker="$line->speaker" /></div>
        @endforeach
    </div>

    <div class="mt-4"><x-japanese.mark-done type="lesson" :id="$lesson->id" :done="$isDone" /></div>
@endsection
