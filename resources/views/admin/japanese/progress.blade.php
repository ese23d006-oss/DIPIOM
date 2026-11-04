@extends('layouts.app')

@section('title', 'Япон хэл: суралцагчдын явц')

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('admin.japanese.hub')], ['Суралцагчдын явц', null]]" />
    <x-page-header title="Суралцагчдын явц" />

    @if ($students->isEmpty())
        <div class="empty-state"><p>Суралцагч бүртгэгдээгүй байна.</p></div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-label="Суралцагчдын явцын хүснэгт">
                <table class="table">
                    <caption class="visually-hidden">Япон хэлний суралцагчдын явц</caption>
                    <thead>
                        <tr>
                            <th scope="col">Нэр</th>
                            <th scope="col">Сурсан зүйл</th>
                            <th scope="col">Цээжилсэн үг</th>
                            <th scope="col">Өгсөн тест</th>
                            <th scope="col">Дундаж дүн</th>
                            <th scope="col">Сүүлд суралцсан</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <th scope="row">{{ $student->name }}</th>
                                <td>{{ $student->studied }}</td>
                                <td>{{ $student->known_words }}</td>
                                <td>{{ $student->tests }}</td>
                                <td>{{ $student->average !== null ? round($student->average).'%' : 'Тест өгөөгүй' }}</td>
                                <td>{{ $student->last_activity ?? 'Бүртгэлгүй' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $students->links('pagination.accessible') }}
    @endif
@endsection
