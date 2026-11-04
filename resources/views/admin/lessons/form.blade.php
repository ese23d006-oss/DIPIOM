@extends('layouts.app')

@php $isNew = ! $lesson->exists; @endphp

@section('title', $isNew ? 'Хичээл нэмэх' : 'Хичээл засах')

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.courses.index') }}">Сургалтууд</a></li>
            <li><a href="{{ route('admin.courses.lessons.index', $course) }}">{{ $course->title }}</a></li>
            <li><span aria-current="page">{{ $isNew ? 'Шинэ хичээл' : $lesson->title }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$isNew ? 'Хичээл нэмэх' : 'Хичээл засах'">
        @unless ($isNew)
            <a class="btn btn-outline-primary" href="{{ route('admin.lessons.media', $lesson) }}">Файлууд</a>
            <a class="btn btn-outline-primary" href="{{ route('lessons.show', [$course, $lesson]) }}">Урьдчилан харах</a>
        @endunless
    </x-page-header>

    <x-form.errors />

    <form method="POST" action="{{ $isNew ? route('admin.courses.lessons.store', $course) : route('admin.lessons.update', $lesson) }}"
          class="card-surface card-pad form-wide" novalidate>
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <x-form.field name="title" label="Хичээлийн нэр" :value="$lesson->title" required />
        <x-form.textarea name="summary" label="Товч мэдээлэл" :value="$lesson->summary" :rows="3" />
        <x-form.textarea name="transcript" label="Хичээлийн бичвэр" :value="$lesson->transcript" :rows="14"
                         hint="Хичээлийн бүрэн текст. Суралцагч үүнийг уншиж эсвэл дуугаар сонсоно." />
        <x-form.check name="is_published" label="Нийтлэх" :checked="$lesson->is_published"
                      hint="Нийтлээгүй хичээл суралцагчдад харагдахгүй." />

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Хадгалах</button>
            <a class="btn btn-outline-primary" href="{{ route('admin.courses.lessons.index', $course) }}">Болих</a>
        </div>
    </form>
@endsection
