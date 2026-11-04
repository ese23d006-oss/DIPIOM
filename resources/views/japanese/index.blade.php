@extends('layouts.app')

@section('title', 'Япон хэл')

@section('content')
    <x-page-header title="Япон хэл" lead="JLPT түвшнээ сонгоод хичээлээ үргэлжлүүлээрэй." />

    <section aria-label="Миний явц">
        <x-stat-list :stats="$stats" />
    </section>

    @unless (auth()->user()->isAdmin())
        <div class="grid-split section">
            <section class="card-surface card-pad" aria-labelledby="goal-title">
                <h2 id="goal-title" class="mb-2">Өдөр тутмын суралцах зорилт</h2>
                @php $goalPercent = min(100, (int) floor($studiedToday * 100 / max(1, $goal))); @endphp
                <p class="progress-label" id="goal-progress">Өнөөдөр {{ $goal }} зорилтоос {{ $studiedToday }} зүйл суралцсан ({{ $goalPercent }}%).</p>
                <progress class="progress-native mb-3" max="100" value="{{ $goalPercent }}" aria-labelledby="goal-progress"></progress>
                <form method="POST" action="{{ route('japanese.preferences') }}" class="d-flex flex-wrap align-items-end gap-2">
                    @csrf
                    <div>
                        <label for="daily_goal" class="form-label">Өдрийн зорилт</label>
                        <input id="daily_goal" name="daily_goal" type="number" min="1" max="200" class="form-control" value="{{ $goal }}">
                    </div>
                    <button type="submit" class="btn btn-outline-primary">Хадгалах</button>
                </form>
            </section>

            <section class="card-surface card-pad" aria-labelledby="last-title">
                <h2 id="last-title" class="mb-2">Сүүлийн суралцсан</h2>
                @if ($lastStudied)
                    <p class="mb-0"><x-mixed :text="$lastStudied" /></p>
                @else
                    <p class="mb-0">Одоогоор суралцсан зүйл бүртгэгдээгүй байна.</p>
                @endif
            </section>
        </div>
    @endunless

    <section class="section" aria-labelledby="levels-title">
        <div class="section-head"><h2 id="levels-title">JLPT түвшин</h2></div>
        <ul class="grid-cards">
            @foreach ($levels as $level)
                <li>
                    <article class="card-surface card-pad h-100 d-flex flex-column gap-2">
                        <h3 class="mb-0">{{ $level->code }}: {{ $level->name }}</h3>
                        @if ($level->description)
                            <p class="course-card-desc mb-0">{{ $level->description }}</p>
                        @endif
                        @if ($current?->id === $level->id)
                            <p class="mb-0"><span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Одоо сурч буй</span></p>
                        @endif

                        <div class="mt-auto d-flex flex-wrap gap-2 pt-2">
                            @if ($level->isPublished())
                                <a class="btn btn-primary" href="{{ route('japanese.level', $level) }}">Хичээл үзэх <span class="visually-hidden">: {{ $level->code }}</span></a>
                                @if ($current?->id !== $level->id && ! auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('japanese.preferences') }}">
                                        @csrf
                                        <input type="hidden" name="level_id" value="{{ $level->id }}">
                                        <button type="submit" class="btn btn-outline-primary">Энэ түвшинг сонгох <span class="visually-hidden">: {{ $level->code }}</span></button>
                                    </form>
                                @endif
                            @else
                                <p class="mb-0"><span class="badge-status badge-status--neutral"><i class="bi bi-hourglass" aria-hidden="true"></i> Агуулга хараахан ороогүй</span></p>
                            @endif
                        </div>
                    </article>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="section" aria-labelledby="results-title">
        <div class="section-head"><h2 id="results-title">Сүүлийн тестийн дүн</h2></div>
        @if ($results->isEmpty())
            <div class="empty-state"><p>Та одоогоор тест өгөөгүй байна.</p></div>
        @else
            <ul class="card-surface card-pad list-unstyled mb-0">
                @foreach ($results as $result)
                    <li class="mb-2">{{ $result->quiz->title }}: {{ $result->score }} / {{ $result->max_score }} оноо ({{ $result->percent }}%), {{ $result->passed ? 'тэнцсэн' : 'тэнцээгүй' }}</li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
