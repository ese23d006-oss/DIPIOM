@extends('layouts.app')

@php $isNew = ! $quiz->exists; @endphp

@section('title', $isNew ? 'Шалгалт нэмэх' : 'Шалгалт засах')

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.courses.index') }}">Сургалтууд</a></li>
            <li><a href="{{ route('admin.courses.quizzes.index', $course) }}">{{ $course->title }}: шалгалтууд</a></li>
            <li><span aria-current="page">{{ $isNew ? 'Шинэ шалгалт' : $quiz->title }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$isNew ? 'Шалгалт нэмэх' : 'Шалгалт засах'" />

    <x-form.errors />

    <form method="POST" action="{{ $isNew ? route('admin.courses.quizzes.store', $course) : route('admin.quizzes.update', $quiz) }}"
          class="card-surface card-pad form-narrow" novalidate>
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <x-form.field name="title" label="Шалгалтын нэр" :value="$quiz->title" required />
        <x-form.textarea name="description" label="Тайлбар" :value="$quiz->description" :rows="3" />
        <x-form.field name="pass_percent" label="Тэнцэх хувь" type="number" :value="$quiz->pass_percent" required hint="1-100 хооронд." />

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Хадгалах</button>
            <a class="btn btn-outline-primary" href="{{ route('admin.courses.quizzes.index', $course) }}">Болих</a>
        </div>
    </form>

    @unless ($isNew)
        <section class="section" aria-labelledby="questions-title">
            <div class="section-head">
                <h2 id="questions-title">Асуултууд ({{ $quiz->questions->count() }})</h2>
                <a class="btn btn-primary" href="{{ route('admin.quizzes.questions.create', $quiz) }}">Асуулт нэмэх</a>
            </div>

            @if ($quiz->questions->isEmpty())
                <div class="empty-state"><p>Асуулт алга байна. Асуултгүй шалгалтыг нийтлэх боломжгүй.</p></div>
            @else
                <ol class="card-surface mb-0 ps-4 pe-3 py-3">
                    @foreach ($quiz->questions as $question)
                        <li class="mb-3">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>{{ $question->body }} ({{ $question->points }} оноо, {{ count($question->options) }} хариулт)</div>
                                <div class="table-actions">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.questions.edit', $question) }}">Засах <span class="visually-hidden">: асуулт {{ $loop->iteration }}</span></a>
                                    <form method="POST" action="{{ route('admin.questions.destroy', $question) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger-token">Устгах <span class="visually-hidden">: асуулт {{ $loop->iteration }}</span></button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    @endunless
@endsection
