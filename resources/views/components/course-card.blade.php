@props(['course', 'withProgress' => false])

@php
    $lessonCount = $course->published_lessons_count ?? $course->lessons_total ?? 0;
    $resume = $withProgress ? $course->nextLessonFor(auth()->user()) : null;
@endphp

<article class="course-card">
    <div class="course-card-media">
        @if ($course->thumbnail_path)
            <img src="{{ asset('storage/'.$course->thumbnail_path) }}" alt="{{ $course->thumbnail_alt }}" loading="lazy">
        @else
            <i class="bi bi-journal-richtext" aria-hidden="true"></i>
        @endif
    </div>

    <div class="course-card-body">
        <h3 class="course-card-title"><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></h3>
        <p class="course-card-desc">{{ \Illuminate\Support\Str::limit($course->description, 110) }}</p>

        <p class="course-meta">
            <span><i class="bi bi-tag" aria-hidden="true"></i>Ангилал: {{ $course->category->name }}</span>
            <span><i class="bi bi-journal-text" aria-hidden="true"></i>Хичээлийн тоо: {{ $lessonCount }}</span>
        </p>

        <div class="course-card-footer">
            @if ($withProgress)
                <p class="progress-label" id="course-progress-{{ $course->id }}">
                    {{ $course->lessons_total }} хичээлээс {{ $course->lessons_completed }} нь дууссан ({{ $course->completion_percent }}%)
                </p>
                <progress class="progress-native mb-3" max="100" value="{{ $course->completion_percent }}"
                          aria-labelledby="course-progress-{{ $course->id }}"></progress>

                @if ($resume)
                    <a class="btn btn-primary w-100" href="{{ route('lessons.show', [$course, $resume]) }}">
                        {{ $course->completion_percent === 100 ? 'Дахин үзэх' : 'Үргэлжлүүлэх' }}
                        <span class="visually-hidden">: {{ $course->title }}</span>
                    </a>
                @endif
            @else
                <a class="btn btn-outline-primary w-100" href="{{ route('courses.show', $course) }}">
                    Дэлгэрэнгүй
                    <span class="visually-hidden">: {{ $course->title }}</span>
                </a>
            @endif
        </div>
    </div>
</article>
