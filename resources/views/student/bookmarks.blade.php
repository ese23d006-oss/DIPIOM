@extends('layouts.app')

@section('title', 'Хадгалсан хичээлүүд')

@section('content')
    <x-page-header title="Хадгалсан хичээлүүд" />

    @if ($bookmarks->isEmpty())
        <div class="empty-state">
            <i class="bi bi-bookmark" aria-hidden="true"></i>
            <p>Та одоогоор хичээл хадгалаагүй байна. Хичээлийн хуудаснаас хадгалж болно.</p>
            <p><a class="btn btn-primary" href="{{ route('courses.index') }}">Сургалт хайх</a></p>
        </div>
    @else
        <ul class="card-surface list-unstyled mb-0">
            @foreach ($bookmarks as $bookmark)
                <li class="card-pad d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
                    <div>
                        <a class="fw-semibold" href="{{ route('lessons.show', [$bookmark->lesson->course, $bookmark->lesson]) }}">{{ $bookmark->lesson->title }}</a>
                        <p class="text-secondary-token text-small mb-0">{{ $bookmark->lesson->course->title }}</p>
                    </div>
                    <form method="POST" action="{{ route('lessons.bookmark', $bookmark->lesson) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            Хасах <span class="visually-hidden">: {{ $bookmark->lesson->title }}</span>
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>

        {{ $bookmarks->links('pagination.accessible') }}
    @endif
@endsection
