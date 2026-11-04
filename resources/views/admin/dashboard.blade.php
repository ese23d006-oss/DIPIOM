@extends('layouts.app')

@section('title', 'Админ хянах самбар')

@section('content')
    <x-page-header title="Админ хянах самбар" lead="Системийн ерөнхий байдал бодит өгөгдлөөр.">
        <a class="btn btn-primary" href="{{ route('admin.users.index') }}">
            <i class="bi bi-people" aria-hidden="true"></i> Хэрэглэгчид
        </a>
    </x-page-header>

    <section aria-label="Статистик">
        <x-stat-list :stats="$stats" />
    </section>

    <div class="grid-split section">
        <section class="card-surface card-pad" aria-labelledby="registrations-title">
            <h2 id="registrations-title" class="mb-1">Шинэ суралцагчид, сүүлийн {{ $chartDays }} хоног</h2>

            @php
                $total = $registrations->sum('count');
                $max = max(1, $registrations->max('count'));
            @endphp

            <p class="chart-note">Нийт {{ $total }} бүртгэл</p>

            @if ($total === 0)
                <p class="mb-0">Энэ хугацаанд шинэ бүртгэл алга байна.</p>
            @else
                {{-- Дэлгэц уншигч график биш, доорх хүснэгтийг уншина. --}}
                <div aria-hidden="true">
                    <div class="bar-chart">
                        @foreach ($registrations as $day)
                            <div class="bar-chart-col">
                                @if ($day['count'] > 0)
                                    <div class="bar-chart-bar" style="height: {{ max(2, round($day['count'] * 100 / $max)) }}%"></div>
                                @endif
                                <span class="bar-chart-tip">{{ $day['date']->format('m-d') }}: {{ $day['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="bar-chart-axis">
                        <span>{{ $registrations->first()['date']->format('m-d') }}</span>
                        <span>{{ $registrations->last()['date']->format('m-d') }}</span>
                    </div>
                </div>

                <details class="mt-2">
                    <summary>Хүснэгтээр харах</summary>
                    <div class="table-responsive" tabindex="0" role="region" aria-label="Өдөр тутмын бүртгэлийн хүснэгт">
                        <table class="table">
                            <thead><tr><th scope="col">Огноо</th><th scope="col">Бүртгэлийн тоо</th></tr></thead>
                            <tbody>
                                @foreach ($registrations as $day)
                                    <tr><td>{{ $day['date']->format('Y-m-d') }}</td><td>{{ $day['count'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </section>

        <section class="card-surface card-pad" aria-labelledby="quiz-title">
            <h2 id="quiz-title" class="mb-3">Шалгалтын үр дүн</h2>
            @if ($quizTotal === 0)
                <p class="mb-0">Одоогоор шалгалт өгөөгүй байна.</p>
            @else
                <p class="stat-big mb-1 h2">{{ $quizPercent }}% тэнцсэн</p>
                <p class="progress-label" id="quiz-progress">{{ $quizTotal }} шалгалтаас {{ $quizPassed }} нь тэнцсэн</p>
                <progress class="progress-native" max="100" value="{{ $quizPercent }}" aria-labelledby="quiz-progress"></progress>
            @endif
        </section>
    </div>

    <section class="section" aria-labelledby="course-stats-title">
        <div class="section-head"><h2 id="course-stats-title">Сургалтын статистик</h2></div>
        @if ($courseStats->isEmpty())
            <div class="empty-state"><p>Одоогоор сургалт үүсгээгүй байна.</p></div>
        @else
            <div class="table-card">
                <div class="table-responsive" tabindex="0" role="region" aria-label="Сургалтын статистикийн хүснэгт">
                    <table class="table table-hover">
                        <thead><tr><th scope="col">Сургалт</th><th scope="col">Хичээл</th><th scope="col">Суралцагч</th><th scope="col">Төлөв</th></tr></thead>
                        <tbody>
                            @foreach ($courseStats as $course)
                                <tr>
                                    <td><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></td>
                                    <td>{{ $course->lessons_count }}</td>
                                    <td>{{ $course->learners }}</td>
                                    <td>
                                        @if ($course->is_published)
                                            <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Нийтэлсэн</span>
                                        @else
                                            <span class="badge-status badge-status--neutral"><i class="bi bi-pencil" aria-hidden="true"></i> Ноорог</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

    <div class="grid-split section">
        <section aria-labelledby="recent-users-title">
            <div class="section-head">
                <h2 id="recent-users-title">Сүүлийн бүртгэлүүд</h2>
                <a href="{{ route('admin.users.index') }}">Бүх хэрэглэгч</a>
            </div>
            <div class="table-card">
                <div class="table-responsive" tabindex="0" role="region" aria-label="Сүүлийн бүртгэлийн хүснэгт">
                    <table class="table">
                        <thead><tr><th scope="col">Нэр</th><th scope="col">Эрх</th><th scope="col">Огноо</th></tr></thead>
                        <tbody>
                            @foreach ($recentUsers as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->role->label() }}</td>
                                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="card-surface card-pad" aria-labelledby="activity-title">
            <h2 id="activity-title" class="mb-3">Сүүлийн үйл ажиллагаа</h2>
            <x-activity-list :items="$activity" />
        </section>
    </div>
@endsection
