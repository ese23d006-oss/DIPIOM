@extends('layouts.app')

@php $isNew = ! $course->exists; @endphp

@section('title', $isNew ? 'Сургалт нэмэх' : 'Сургалт засах')

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.courses.index') }}">Сургалтууд</a></li>
            <li><span aria-current="page">{{ $isNew ? 'Шинэ сургалт' : $course->title }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$isNew ? 'Сургалт нэмэх' : 'Сургалт засах'" />

    <x-form.errors />

    @if ($categories->isEmpty())
        <div class="notice notice--warning">
            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
            <div class="notice-body">Сургалт үүсгэхийн тулд эхлээд <a href="{{ route('admin.categories.create') }}">ангилал нэмнэ үү</a>.</div>
        </div>
    @else
        <form method="POST" action="{{ $isNew ? route('admin.courses.store') : route('admin.courses.update', $course) }}"
              enctype="multipart/form-data" class="card-surface card-pad form-narrow" novalidate>
            @csrf
            @unless ($isNew) @method('PUT') @endunless

            <x-form.field name="title" label="Сургалтын нэр" :value="$course->title" required />
            <x-form.select name="category_id" label="Ангилал" :value="$course->category_id"
                           :options="$categories->pluck('name', 'id')->all()" />
            <x-form.textarea name="description" label="Тайлбар" :value="$course->description" required :rows="6" />

            <fieldset class="mb-3">
                <legend class="h6">Сургалтын зураг</legend>

                @if ($course->thumbnail_path)
                    <img src="{{ asset('storage/'.$course->thumbnail_path) }}" alt="{{ $course->thumbnail_alt }}" class="form-thumbnail mb-2">
                    <x-form.check name="remove_thumbnail" label="Зургийг устгах" />
                @endif

                <div class="mb-3">
                    <label for="thumbnail" class="form-label">{{ $course->thumbnail_path ? 'Шинэ зураг' : 'Зураг' }}</label>
                    <div id="thumbnail-hint" class="field-hint">JPG, PNG эсвэл WEBP, хамгийн ихдээ {{ $imageLimit }}.</div>
                    <input id="thumbnail" name="thumbnail" type="file" accept=".jpg,.jpeg,.png,.webp"
                           @class(['form-control', 'is-invalid' => $errors->has('thumbnail')])
                           aria-describedby="thumbnail-hint{{ $errors->has('thumbnail') ? ' thumbnail-error' : '' }}"
                           @error('thumbnail') aria-invalid="true" @enderror>
                    @error('thumbnail')
                        <div id="thumbnail-error" class="field-error">
                            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <x-form.field name="thumbnail_alt" label="Зургийн тайлбар" :value="$course->thumbnail_alt"
                              hint="Зураг оруулсан бол заавал бичнэ. Дэлгэц уншигч энэ тайлбарыг уншина." />
            </fieldset>

            <x-form.check name="is_published" label="Нийтлэх" :checked="$course->is_published"
                          hint="Нийтлээгүй сургалт суралцагчдад харагдахгүй." />

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Хадгалах</button>
                <a class="btn btn-outline-primary" href="{{ route('admin.courses.index') }}">Болих</a>
            </div>
        </form>
    @endif
@endsection
