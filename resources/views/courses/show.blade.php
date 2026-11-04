@extends('layouts.app')

@section('title', $course->title)

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('courses.index') }}">Сургалтууд</a></li>
            <li><span aria-current="page">{{ $course->title }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$course->title" />

    @unless ($course->is_published)
        <div class="notice notice--warning mb-4">
            <i class="bi bi-eye-slash" aria-hidden="true"></i>
            <div class="notice-body">Энэ сургалт нийтлэгдээгүй тул зөвхөн админд харагдаж байна.</div>
        </div>
    @endunless

    @php
        $startLesson = auth()->check() && ! auth()->user()->isAdmin()
            ? $course->nextLessonFor(auth()->user())
            : $lessons->first();
    @endphp

    <div class="grid-split">
        <div>
            <section class="card-surface card-pad" aria-labelledby="about-course">
                <h2 id="about-course">Сургалтын тухай</h2>
                <div style="white-space: pre-line">{{ $course->description }}</div>
            </section>

            <section class="card-surface mt-4" aria-labelledby="lessons-title">
                <h2 id="lessons-title" class="card-pad mb-0 pb-3">Хичээлүүд ({{ $lessons->count() }})</h2>

                @if ($lessons->isEmpty())
                    <p class="card-pad pt-0 mb-0">Энэ сургалтад одоогоор нийтлэгдсэн хичээл байхгүй байна.</p>
                @else
                    <ol class="course-lessons" data-arrow-nav>
                        @foreach ($lessons as $lesson)
                            @php $lessonProgress = $progress->get($lesson->id); @endphp
                            <li>
                                <a class="lesson-link" href="{{ route('lessons.show', [$course, $lesson]) }}">
                                    <i @class([
                                        'bi',
                                        'bi-check-circle-fill' => $lessonProgress?->is_completed,
                                        'bi-play-circle' => ! $lessonProgress?->is_completed,
                                    ]) aria-hidden="true"></i>
                                    <span>
                                        {{ $loop->iteration }}. {{ $lesson->title }}
                                        <span class="lesson-state">{{ collect([
                                            $lesson->durationLabel(),
                                            $lessonProgress?->is_completed
                                                ? 'дууссан'
                                                : ($lessonProgress ? $lessonProgress->percent.'% үзсэн' : null),
                                        ])->filter()->implode(', ') }}</span>
                                        @if ($lesson->summary)
                                            <span class="lesson-state">{{ $lesson->summary }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            @if ($quizzes->isNotEmpty())
                <section class="card-surface card-pad mt-4" aria-labelledby="quizzes-title">
                    <h2 id="quizzes-title" class="mb-3">Шалгалтууд ({{ $quizzes->count() }})</h2>
                    <ul class="list-unstyled mb-0">
                        @foreach ($quizzes as $quiz)
                            <li class="mb-2">
                                @if (auth()->check() && ! auth()->user()->isAdmin())
                                    <a href="{{ route('quizzes.show', [$course, $quiz]) }}">{{ $quiz->title }}</a>
                                @else
                                    {{ $quiz->title }}
                                @endif
                                <span class="text-small">, {{ $quiz->questions_count }} асуулт</span>
                            </li>
                        @endforeach
                    </ul>
                    @guest
                        <p class="mt-2 mb-0"><a href="{{ route('login') }}">Нэвтэрч шалгалт өгнө үү.</a></p>
                    @endguest
                </section>
            @endif
        </div>

        <aside class="card-surface card-pad course-summary" aria-label="Сургалтын товч мэдээлэл">
            <dl>
                <dt>Ангилал</dt>
                <dd><a href="{{ route('courses.index', ['category' => $course->category->slug]) }}">{{ $course->category->name }}</a></dd>
                <dt>Хичээлийн тоо</dt>
                <dd>{{ $lessons->count() }}</dd>
                <dt>Нийт хугацаа</dt>
                <dd>{{ \App\Support\Duration::human((int) $lessons->sum('duration_seconds')) }}</dd>
            </dl>

            @if ($startLesson)
                @auth
                    <a class="btn btn-primary w-100" href="{{ route('lessons.show', [$course, $startLesson]) }}">
                        {{ $progress->isNotEmpty() ? 'Үргэлжлүүлэх' : 'Хичээл эхлэх' }}
                    </a>
                @else
                    <a class="btn btn-primary w-100" href="{{ route('login') }}">Нэвтэрч эхлэх</a>
                @endauth
            @endif
        </aside>
    </div>
@endsection
