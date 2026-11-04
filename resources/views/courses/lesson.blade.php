@extends('layouts.app')

@section('title', $lesson->title)

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('courses.index') }}">Сургалтууд</a></li>
            <li><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></li>
            <li><span aria-current="page">{{ $lesson->title }}</span></li>
        </ol>
    </nav>

    <div class="lesson-layout">
        <div>
            <h1 class="mb-3">{{ $lesson->title }}</h1>

            @include('courses.partials.player')

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if ($isStudent)
                    <form method="POST" action="{{ route('lessons.bookmark', $lesson) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi {{ $isBookmarked ? 'bi-bookmark-fill' : 'bi-bookmark' }}" aria-hidden="true"></i>
                            {{ $isBookmarked ? 'Хадгалснаас хасах' : 'Хичээлийг хадгалах' }}
                        </button>
                    </form>

                    @unless ($currentProgress?->is_completed)
                        <form method="POST" action="{{ route('lessons.complete', $lesson) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-check2-circle" aria-hidden="true"></i> Дууссан гэж тэмдэглэх
                            </button>
                        </form>
                    @endunless
                @endif
            </div>

            @if ($lesson->summary)
                <section class="card-surface card-pad mt-4" aria-labelledby="lesson-info">
                    <h2 id="lesson-info">Товч мэдээлэл</h2>
                    <p class="mb-0">{{ $lesson->summary }}</p>
                </section>
            @endif

            <section class="card-surface card-pad mt-4" aria-labelledby="transcript-title">
                <h2 id="transcript-title">Хичээлийн бичвэр</h2>

                @if (filled($lesson->transcript))
                    <div class="mb-4" data-tts data-target="lesson-transcript">
                        <p class="notice notice--info tts-status mb-3" data-tts-status role="status">Дуу хоолойг шалгаж байна.</p>

                        <div class="tts-controls" data-tts-controls hidden>
                            <button type="button" class="btn btn-primary" data-tts-action="read">
                                <i class="bi bi-volume-up" aria-hidden="true"></i> Дуугаар унших
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-tts-action="selection">Сонгосон текстийг унших</button>
                            <button type="button" class="btn btn-outline-primary" data-tts-action="pause" aria-disabled="true">Түр зогсоох</button>
                            <button type="button" class="btn btn-outline-primary" data-tts-action="resume" aria-disabled="true">Үргэлжлүүлэх</button>
                            <button type="button" class="btn btn-outline-primary" data-tts-action="stop" aria-disabled="true">Зогсоох</button>
                            <label class="tts-rate">
                                Унших хурд
                                <select class="form-select" data-tts-rate>
                                    @foreach (['0.5', '0.75', '1', '1.25', '1.5', '2'] as $speed)
                                        <option value="{{ $speed }}" @selected((float) $speed === (float) $settings->tts_rate)>{{ $speed }} дахин</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>

                    <div id="lesson-transcript" class="transcript">{{ $lesson->transcript }}</div>
                @else
                    <p class="mb-0">Энэ хичээлд бичвэр оруулаагүй байна.</p>
                @endif
            </section>
        </div>

        <aside aria-label="Хичээлийн жагсаалт">
            <nav class="lesson-nav mb-3" aria-label="Өмнөх, дараагийн хичээл">
                @if ($previous)
                    <a class="btn btn-outline-primary flex-fill" href="{{ route('lessons.show', [$course, $previous]) }}">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i> Өмнөх хичээл
                    </a>
                @endif
                @if ($next)
                    <a class="btn btn-primary flex-fill" href="{{ route('lessons.show', [$course, $next]) }}">
                        Дараагийн хичээл <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </nav>

            <section class="card-surface" aria-labelledby="lesson-list-title">
                <h2 id="lesson-list-title" class="h5 card-pad mb-0 pb-2">Хичээлүүд</h2>
                <ol class="lesson-list" data-arrow-nav>
                    @foreach ($lessons as $item)
                        @php $itemProgress = $progress->get($item->id); @endphp
                        <li>
                            <a class="lesson-link" href="{{ route('lessons.show', [$course, $item]) }}"
                               @if ($item->id === $lesson->id) aria-current="page" @endif>
                                <i @class([
                                    'bi',
                                    'bi-check-circle-fill' => $itemProgress?->is_completed,
                                    'bi-play-circle-fill' => ! $itemProgress?->is_completed && $item->id === $lesson->id,
                                    'bi-circle' => ! $itemProgress?->is_completed && $item->id !== $lesson->id,
                                ]) aria-hidden="true"></i>
                                <span>
                                    {{ $loop->iteration }}. {{ $item->title }}
                                    <span class="lesson-state">
                                        @if ($itemProgress?->is_completed)
                                            Дууссан
                                        @elseif ($itemProgress)
                                            {{ $itemProgress->percent }}% үзсэн
                                        @else
                                            Эхлээгүй
                                        @endif
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>
@endsection
