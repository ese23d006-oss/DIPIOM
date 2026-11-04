@extends('layouts.app')

@section('title', 'Шалгалтын үр дүн')

@section('content')
    <x-page-header title="Шалгалтын үр дүн" lead="Суралцагчдын өгсөн шалгалтууд." />

    <form method="GET" action="{{ route('admin.quiz-results.index') }}" class="filter-bar card-surface" aria-label="Үр дүн шүүх">
        <div>
            <label for="course" class="form-label">Сургалт</label>
            <select id="course" name="course" class="form-select">
                <option value="">Бүгд</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected($courseId === $course->id)>{{ $course->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="form-label">Төлөв</label>
            <select id="status" name="status" class="form-select">
                <option value="">Бүгд</option>
                <option value="passed" @selected($status === 'passed')>Тэнцсэн</option>
                <option value="failed" @selected($status === 'failed')>Тэнцээгүй</option>
            </select>
        </div>
        <div><button type="submit" class="btn btn-primary w-100">Шүүх</button></div>
    </form>

    <h2 class="mt-4 mb-3">Олдсон: {{ $results->total() }}</h2>

    @if ($results->isEmpty())
        <div class="empty-state"><p>Үр дүн алга байна.</p></div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-label="Шалгалтын үр дүнгийн хүснэгт">
                <table class="table">
                    <caption class="visually-hidden">Суралцагчдын шалгалтын үр дүн, сүүлийнх нь эхэндээ</caption>
                    <thead>
                        <tr>
                            <th scope="col">Суралцагч</th>
                            <th scope="col">Шалгалт</th>
                            <th scope="col">Сургалт</th>
                            <th scope="col">Оноо</th>
                            <th scope="col">Төлөв</th>
                            <th scope="col">Огноо</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $result)
                            <tr>
                                <th scope="row">{{ $result->user->name }}</th>
                                <td>{{ $result->quiz->title }}</td>
                                <td>{{ $result->quiz->course->title }}</td>
                                <td>{{ $result->score }} / {{ $result->max_score }} ({{ $result->percent }}%)</td>
                                <td><x-quiz-status :passed="$result->passed" /></td>
                                <td>{{ $result->submitted_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $results->links('pagination.accessible') }}
    @endif
@endsection
