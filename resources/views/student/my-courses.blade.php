@extends('layouts.app')

@section('title', 'Миний сургалтууд')

@section('content')
    <x-page-header title="Миний сургалтууд" lead="Хичээл үзэж эхэлмэгц сургалт энд харагдана." />

    @if ($courses->isEmpty())
        <div class="empty-state">
            <i class="bi bi-play-circle" aria-hidden="true"></i>
            <p>Та одоогоор сургалт эхлээгүй байна.</p>
            <p><a class="btn btn-primary" href="{{ route('courses.index') }}">Сургалт хайх</a></p>
        </div>
    @else
        <h2 class="mb-3">Нийт {{ $courses->total() }} сургалт</h2>
        <x-course-progress-list :courses="$courses" />
        {{ $courses->links('pagination.accessible') }}
    @endif
@endsection
