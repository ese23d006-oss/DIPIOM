@extends('layouts.app')

@section('title', 'Миний явц')

@section('content')
    <x-page-header title="Миний явц" lead="Сургалтын явц, дууссан хичээл, сүүлд үзсэн түүх." />

    <section aria-label="Статистик">
        <x-stat-list :stats="$stats" />
    </section>

    <section class="section" aria-labelledby="progress-courses">
        <div class="section-head"><h2 id="progress-courses">Миний сургалтууд</h2></div>
        @if ($courses->isEmpty())
            <div class="empty-state">
                <i class="bi bi-play-circle" aria-hidden="true"></i>
                <p>Та одоогоор сургалт эхлээгүй байна.</p>
                <p><a class="btn btn-primary" href="{{ route('courses.index') }}">Сургалт хайх</a></p>
            </div>
        @else
            <x-course-progress-list :courses="$courses" />
        @endif
    </section>

    <section class="section" aria-labelledby="progress-completed">
        <div class="section-head"><h2 id="progress-completed">Дууссан хичээлүүд ({{ $completed->total() }})</h2></div>
        @if ($completed->isEmpty())
            <div class="empty-state"><p>Одоогоор дууссан хичээл алга байна.</p></div>
        @else
            <div class="table-card">
                <div class="table-responsive" tabindex="0" role="region" aria-label="Дууссан хичээлүүдийн хүснэгт">
                    <table class="table">
                        <caption class="visually-hidden">Дууссан хичээлүүд, сүүлийнх нь эхэндээ</caption>
                        <thead><tr><th scope="col">Хичээл</th><th scope="col">Сургалт</th><th scope="col">Дууссан огноо</th></tr></thead>
                        <tbody>
                            @foreach ($completed as $row)
                                <tr>
                                    <th scope="row"><a href="{{ route('lessons.show', [$row->lesson->course, $row->lesson]) }}">{{ $row->lesson->title }}</a></th>
                                    <td>{{ $row->lesson->course->title }}</td>
                                    <td>{{ $row->completed_at?->format('Y-m-d H:i') ?? 'Бүртгэгдээгүй' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $completed->links('pagination.accessible') }}
        @endif
    </section>

    <section class="section" aria-labelledby="progress-history">
        <div class="section-head"><h2 id="progress-history">Үзсэн түүх</h2></div>
        @if ($history->isEmpty())
            <div class="empty-state"><p>Одоогоор үзсэн түүх алга байна.</p></div>
        @else
            <div class="table-card">
                <div class="table-responsive" tabindex="0" role="region" aria-label="Үзсэн түүхийн хүснэгт">
                    <table class="table">
                        <caption class="visually-hidden">Үзсэн хичээлүүд, сүүлийнх нь эхэндээ</caption>
                        <thead><tr><th scope="col">Хичээл</th><th scope="col">Сургалт</th><th scope="col">Явц</th><th scope="col">Сүүлд үзсэн</th></tr></thead>
                        <tbody>
                            @foreach ($history as $row)
                                <tr>
                                    <th scope="row"><a href="{{ route('lessons.show', [$row->lesson->course, $row->lesson]) }}">{{ $row->lesson->title }}</a></th>
                                    <td>{{ $row->lesson->course->title }}</td>
                                    <td>{{ $row->is_completed ? 'Дууссан' : $row->percent.'% үзсэн' }}</td>
                                    <td>{{ $row->last_watched_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $history->links('pagination.accessible') }}
        @endif
    </section>
@endsection
