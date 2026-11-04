@extends('layouts.app')

@section('title', 'Шалгалтууд')

@section('content')
    <x-page-header title="Шалгалтууд" lead="Сургалтын шалгалтаа өгч, үр дүнгээ хараарай." />

    <section aria-labelledby="available-title">
        <h2 id="available-title" class="mb-3">Өгөх боломжтой шалгалт</h2>
        @if ($quizzes->isEmpty())
            <div class="empty-state">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <p>Одоогоор нийтлэгдсэн шалгалт байхгүй байна.</p>
            </div>
        @else
            <ul class="grid-cards">
                @foreach ($quizzes as $quiz)
                    <li>
                        <article class="card-surface card-pad h-100 d-flex flex-column gap-1">
                            <h3 class="mb-0"><a href="{{ route('quizzes.show', [$quiz->course, $quiz]) }}">{{ $quiz->title }}</a></h3>
                            <p class="mb-0 text-small">Сургалт: {{ $quiz->course->title }}</p>
                            <p class="mb-0 text-small">{{ $quiz->questions_count }} асуулт, тэнцэх хувь {{ $quiz->pass_percent }}%</p>
                            <p class="mb-0 text-small">{{ isset($best[$quiz->id]) ? 'Хамгийн өндөр дүн: '.$best[$quiz->id].'%' : 'Хараахан өгөөгүй' }}</p>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="section" aria-labelledby="results-title">
        <h2 id="results-title" class="mb-3">Миний үр дүн</h2>
        @if ($results->isEmpty())
            <div class="empty-state"><p>Та одоогоор шалгалт өгөөгүй байна.</p></div>
        @else
            <div class="table-card">
                <div class="table-responsive" tabindex="0" role="region" aria-label="Миний шалгалтын үр дүнгийн хүснэгт">
                    <table class="table">
                        <caption class="visually-hidden">Өгсөн шалгалтууд, сүүлийнх нь эхэндээ</caption>
                        <thead>
                            <tr>
                                <th scope="col">Шалгалт</th>
                                <th scope="col">Оноо</th>
                                <th scope="col">Төлөв</th>
                                <th scope="col">Огноо</th>
                                <th scope="col">Үйлдэл</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results as $result)
                                <tr>
                                    <th scope="row">{{ $result->quiz->title }}</th>
                                    <td>{{ $result->score }} / {{ $result->max_score }} ({{ $result->percent }}%)</td>
                                    <td>
                                        <x-quiz-status :passed="$result->passed" />
                                    </td>
                                    <td>{{ $result->submitted_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('quizzes.result', $result) }}">
                                            Дэлгэрэнгүй <span class="visually-hidden">: {{ $result->quiz->title }}, {{ $result->submitted_at->format('Y-m-d H:i') }}</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $results->links('pagination.accessible') }}
        @endif
    </section>
@endsection
