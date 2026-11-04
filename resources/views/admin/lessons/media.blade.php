@extends('layouts.app')

@section('title', 'Файлууд: '.$lesson->title)

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.courses.index') }}">Сургалтууд</a></li>
            <li><a href="{{ route('admin.courses.lessons.index', $course) }}">{{ $course->title }}</a></li>
            <li><span aria-current="page">{{ $lesson->title }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="'Файлууд: '.$lesson->title" lead="Шинэ файл оруулбал одоогийн файлыг солино.">
        <a class="btn btn-outline-primary" href="{{ route('admin.lessons.edit', $lesson) }}">Хичээл засах</a>
        <a class="btn btn-outline-primary" href="{{ route('lessons.show', [$course, $lesson]) }}">Урьдчилан харах</a>
    </x-page-header>

    <x-form.errors :anchors="['file' => 'file-'.old('type', 'video')]" />

    <div class="media-grid">
        @foreach (\App\Enums\MediaType::cases() as $type)
            @php
                $item = $media->get($type->value);
                $accept = collect($type->extensions())->map(fn ($extension) => '.'.$extension)->implode(',');
                $inputId = 'file-'.$type->value;
                $hasError = $errors->has('file') && old('type') === $type->value;
            @endphp

            <section class="card-surface card-pad" aria-labelledby="media-{{ $type->value }}">
                <h2 id="media-{{ $type->value }}" class="h5">{{ $type->label() }}</h2>
                <p class="field-hint">{{ $type->help() }}</p>

                @if ($item)
                    <p class="mb-2">
                        <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Оруулсан</span>
                    </p>
                    <p class="text-small mb-3">
                        {{ collect([
                            $item->original_name,
                            \App\Support\UploadLimit::humanBytes($item->size_bytes),
                            $type === \App\Enums\MediaType::Video ? $lesson->durationLabel() : null,
                        ])->filter()->implode(', ') }}
                    </p>
                @else
                    <p class="mb-3">
                        <span class="badge-status badge-status--neutral"><i class="bi bi-dash-circle" aria-hidden="true"></i> Оруулаагүй</span>
                    </p>
                @endif

                {{-- JavaScript байвал явцыг харуулна, байхгүй бол энгийн form-оор илгээгдэнэ. --}}
                <form method="POST" action="{{ route('admin.lessons.media.store', $lesson) }}" enctype="multipart/form-data"
                      data-upload-form data-label="{{ $type->label() }}" novalidate>
                    @csrf
                    <input type="hidden" name="type" value="{{ $type->value }}">
                    @if ($type === \App\Enums\MediaType::Video)
                        <input type="hidden" name="duration_seconds" data-duration-field>
                    @endif

                    <div class="mb-3">
                        <label for="{{ $inputId }}" class="form-label">{{ $item ? 'Файл солих' : 'Файл сонгох' }}</label>
                        <div id="{{ $inputId }}-hint" class="field-hint">
                            {{ collect($type->extensions())->map(fn ($extension) => strtoupper($extension))->implode(', ') }},
                            хамгийн ихдээ {{ \App\Support\UploadLimit::label($type->maxKilobytes()) }}.
                        </div>
                        <input id="{{ $inputId }}" name="file" type="file" accept="{{ $accept }}" required
                               @class(['form-control', 'is-invalid' => $hasError])
                               aria-describedby="{{ $inputId }}-hint{{ $hasError ? ' '.$inputId.'-error' : '' }}"
                               @if ($hasError) aria-invalid="true" @endif>
                        @if ($hasError)
                            <div id="{{ $inputId }}-error" class="field-error">
                                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                                <span>{{ $errors->first('file') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="notice notice--error mb-3" role="alert" tabindex="-1" data-upload-error hidden>
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                        <div class="notice-body" data-upload-error-text></div>
                    </div>

                    <div class="mb-3" data-upload-progress hidden>
                        <label for="{{ $inputId }}-progress" class="form-label">Оруулж байна</label>
                        <progress id="{{ $inputId }}-progress" class="progress-native" max="100" value="0"></progress>
                    </div>
                    <div class="visually-hidden" aria-live="polite" data-upload-live></div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload" aria-hidden="true"></i> Оруулах
                        </button>
                        @if ($item)
                            <button type="button" class="btn btn-danger-token"
                                    data-bs-toggle="modal" data-bs-target="#delete-media"
                                    data-action="{{ route('admin.media.destroy', $item) }}"
                                    data-message="{{ $type->label() }} файлыг ({{ $item->original_name }}) устгах уу? Буцаах боломжгүй.">
                                Устгах <span class="visually-hidden">: {{ $type->label() }}</span>
                            </button>
                        @endif
                    </div>
                </form>
            </section>
        @endforeach
    </div>

    <x-confirm-modal id="delete-media" title="Файл устгах" />
@endsection
