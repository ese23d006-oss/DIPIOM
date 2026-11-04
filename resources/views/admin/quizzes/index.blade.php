@extends('layouts.app')

@section('title', 'Шалгалтууд: '.$course->title)

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.courses.index') }}">Сургалтууд</a></li>
            <li><a href="{{ route('admin.courses.lessons.index', $course) }}">{{ $course->title }}</a></li>
            <li><span aria-current="page">Шалгалтууд</span></li>
        </ol>
    </nav>

    <x-page-header :title="'Шалгалтууд: '.$course->title">
        <a class="btn btn-primary" href="{{ route('admin.courses.quizzes.create', $course) }}">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Шалгалт нэмэх
        </a>
    </x-page-header>

    @if ($quizzes->isEmpty())
        <div class="empty-state">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
            <p>Энэ сургалтад шалгалт үүсгээгүй байна.</p>
        </div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-label="Шалгалтын хүснэгт">
                <table class="table table-hover">
                    <caption class="visually-hidden">Сургалтын шалгалтууд</caption>
                    <thead>
                        <tr>
                            <th scope="col">Шалгалт</th>
                            <th scope="col">Асуулт</th>
                            <th scope="col">Өгсөн</th>
                            <th scope="col">Тэнцэх хувь</th>
                            <th scope="col">Төлөв</th>
                            <th scope="col">Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quizzes as $quiz)
                            <tr>
                                <th scope="row">{{ $quiz->title }}</th>
                                <td>{{ $quiz->questions_count }}</td>
                                <td>{{ $quiz->results_count }}</td>
                                <td>{{ $quiz->pass_percent }}%</td>
                                <td>
                                    @if ($quiz->is_published)
                                        <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Нийтэлсэн</span>
                                    @else
                                        <span class="badge-status badge-status--neutral"><i class="bi bi-pencil" aria-hidden="true"></i> Ноорог</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.quizzes.edit', $quiz) }}">Асуултууд <span class="visually-hidden">: {{ $quiz->title }}</span></a>
                                        <form method="POST" action="{{ route('admin.quizzes.toggle', $quiz) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary">{{ $quiz->is_published ? 'Ноорог болгох' : 'Нийтлэх' }} <span class="visually-hidden">: {{ $quiz->title }}</span></button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger-token" data-bs-toggle="modal" data-bs-target="#delete-quiz"
                                                data-action="{{ route('admin.quizzes.destroy', $quiz) }}"
                                                data-message="{{ $quiz->title }} шалгалтыг устгах уу? Асуултууд болон суралцагчдын үр дүн хамт устах бөгөөд буцаах боломжгүй.">
                                            Устгах <span class="visually-hidden">: {{ $quiz->title }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <x-confirm-modal id="delete-quiz" title="Шалгалт устгах" />
    @endif
@endsection
