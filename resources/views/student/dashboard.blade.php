@extends('layouts.app')

@section('title', 'Хянах самбар')

@section('content')
    <x-page-header :title="'Сайн байна уу, '.auth()->user()->name"
                   lead="Өнөөдрийн суралцах явцаа эндээс хараарай." />

    <section aria-label="Статистик">
        <x-stat-list :stats="$stats" />
    </section>

    @if ($lastProgress)
        <section class="section" aria-labelledby="last-lesson">
            <div class="section-head"><h2 id="last-lesson">Сүүлд үзсэн хичээл</h2></div>
            <div class="card-surface card-pad d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <p class="fw-semibold mb-0">{{ $lastProgress->lesson->title }}</p>
                    <p class="text-secondary-token text-small mb-0">{{ $lastProgress->lesson->course->title }}, {{ $lastProgress->percent }}% үзсэн</p>
                </div>
                <a class="btn btn-primary" href="{{ route('lessons.show', [$lastProgress->lesson->course, $lastProgress->lesson]) }}">
                    Үргэлжлүүлэх <span class="visually-hidden">: {{ $lastProgress->lesson->title }}</span>
                </a>
            </div>
        </section>
    @endif

    <section class="section" aria-labelledby="continue-title">
        <div class="section-head">
            <h2 id="continue-title">Үргэлжлүүлэн суралцах</h2>
            @if ($continueCourses->isNotEmpty())
                <a href="{{ route('student.my-courses') }}">Миний бүх сургалтыг харах</a>
            @endif
        </div>

        @if ($continueCourses->isEmpty())
            <div class="empty-state">
                <i class="bi bi-play-circle" aria-hidden="true"></i>
                <p>Та одоогоор сургалт эхлээгүй байна.</p>
                <p><a class="btn btn-primary" href="{{ route('courses.index') }}">Сургалт хайх</a></p>
            </div>
        @else
            <x-course-progress-list :courses="$continueCourses" />
        @endif
    </section>

    @if ($recommended->isNotEmpty())
        <section class="section" aria-labelledby="recommended-title">
            <div class="section-head"><h2 id="recommended-title">Танд санал болгох сургалтууд</h2></div>
            <ul class="grid-cards">
                @foreach ($recommended as $course)
                    <li><x-course-card :course="$course" /></li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($newCourses->isNotEmpty())
        <section class="section" aria-labelledby="new-title">
            <div class="section-head">
                <h2 id="new-title">Шинэ сургалтууд</h2>
                <a href="{{ route('courses.index') }}">Бүгдийг харах</a>
            </div>
            <ul class="grid-cards">
                @foreach ($newCourses as $course)
                    <li><x-course-card :course="$course" /></li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="grid-split section">
        <section class="card-surface card-pad" aria-labelledby="activity-title">
            <h2 id="activity-title" class="mb-3">Сүүлийн үйл ажиллагаа</h2>
            <x-activity-list :items="$activity" />
        </section>

        <section class="card-surface card-pad" aria-labelledby="saved-title">
            <h2 id="saved-title" class="mb-3">Хадгалсан хичээлүүд</h2>
            @if ($bookmarks->isEmpty())
                <p class="mb-0">Та одоогоор хичээл хадгалаагүй байна.</p>
            @else
                <ul class="list-unstyled mb-2">
                    @foreach ($bookmarks as $bookmark)
                        <li class="mb-2"><a href="{{ route('lessons.show', [$bookmark->lesson->course, $bookmark->lesson]) }}">{{ $bookmark->lesson->title }}</a></li>
                    @endforeach
                </ul>
                <a href="{{ route('student.bookmarks') }}">Бүгдийг харах</a>
            @endif
        </section>
    </div>

    @if ($categories->isNotEmpty())
        <section class="section" aria-labelledby="categories-title">
            <div class="section-head"><h2 id="categories-title">Сургалтын ангиллууд</h2></div>
            <ul class="category-chips">
                @foreach ($categories as $category)
                    <li>
                        <a class="category-chip" href="{{ route('courses.index', ['category' => $category->slug]) }}">
                            {{ $category->name }}
                            <span>{{ $category->courses_count }} сургалт</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
