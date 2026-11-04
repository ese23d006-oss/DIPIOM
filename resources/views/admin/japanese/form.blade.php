@extends('layouts.app')

@php
    $isNew = ! $item->exists;
    $title = ($isNew ? 'Нэмэх: ' : 'Засах: ').$config['single'];
@endphp

@section('title', $title)

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('admin.japanese.hub')], [$config['label'], $resource === 'examples' ? null : route('admin.japanese.index', $resource)], [$title, null]]" />
    <x-page-header :title="$title" />

    <x-form.errors />

    <form method="POST" action="{{ $isNew ? route('admin.japanese.store', $resource) : route('admin.japanese.update', [$resource, $item->id]) }}"
          class="card-surface card-pad form-wide" novalidate>
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        @foreach ($parent as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        @foreach ($config['fields'] as $field)
            @php
                [$name, $label, $type] = $field;
                $required = $field[3] ?? false;
                $lang = $field[4] ?? null;
                $current = $item->{$name} ?? ($type === 'number' ? 1 : null);
                $lookup = $options[$type] ?? null;
            @endphp

            @if ($lookup !== null)
                <x-form.select :name="$name" :label="$label" :value="$current" :options="$lookup" />
            @elseif ($type === 'textarea')
                <x-form.textarea :name="$name" :label="$label" :value="$current" :required="$required" :rows="3" :lang="$lang" />
            @else
                <x-form.field :name="$name" :label="$label" :value="$current" :required="$required" :lang="$lang" :type="$type === 'number' ? 'number' : 'text'" />
            @endif
        @endforeach

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Хадгалах</button>
            <a class="btn btn-outline-primary" href="{{ route('admin.japanese.index', $resource === 'examples' ? 'grammar' : $resource) }}">Болих</a>
        </div>
    </form>

    @if (! $isNew && isset($config['examples']))
        <section class="section" aria-labelledby="examples-title">
            <div class="section-head">
                <h2 id="examples-title">Жишээ өгүүлбэр</h2>
                <a class="btn btn-primary" href="{{ route('admin.japanese.create', ['examples', $config['examples'] => $item->id]) }}">Жишээ нэмэх</a>
            </div>
            <ul class="card-surface list-unstyled mb-0">
                @forelse ($item->examples as $example)
                    <li class="card-pad d-flex flex-wrap justify-content-between gap-2 border-bottom">
                        <div><x-ja>{{ $example->ja }}</x-ja><br>{{ $example->mn }}</div>
                        <div class="table-actions">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.japanese.edit', ['examples', $example->id]) }}">Засах</a>
                            <form method="POST" action="{{ route('admin.japanese.destroy', ['examples', $example->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger-token">Устгах</button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="card-pad">Жишээ алга байна.</li>
                @endforelse
            </ul>
        </section>
    @endif

    @if (! $isNew && $resource === 'audio')
        <section class="section card-surface card-pad form-wide" aria-labelledby="audio-file-title">
            <h2 id="audio-file-title" class="h5">Аудио файл</h2>
            @if ($item->file_path)
                <p>{{ $item->original_name }}, {{ \App\Support\UploadLimit::humanBytes($item->size_bytes) }}</p>
                <form method="POST" action="{{ route('admin.japanese.audio.file.destroy', $item) }}" class="mb-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger-token">Файлыг устгах</button>
                </form>
            @else
                <p>Аудио файл оруулаагүй. Файл оруулах хүртэл энэ дасгалыг нийтлэх боломжгүй.</p>
            @endif
            <form method="POST" action="{{ route('admin.japanese.audio.file.store', $item) }}" enctype="multipart/form-data" novalidate>
                @csrf
                <label for="audio-file" class="form-label">Файл сонгох</label>
                <div id="audio-file-hint" class="field-hint">MP3, OGG, WAV.</div>
                <input id="audio-file" name="file" type="file" class="form-control mb-3" accept=".mp3,.ogg,.wav" aria-describedby="audio-file-hint">
                <button type="submit" class="btn btn-primary">Оруулах</button>
            </form>
        </section>
    @endif

    @if (! $isNew && $resource === 'quizzes')
        <section class="section" aria-labelledby="questions-title">
            <div class="section-head">
                <h2 id="questions-title">Асуултууд ({{ $item->questions()->count() }})</h2>
                <a class="btn btn-primary" href="{{ route('admin.japanese.questions.create', $item) }}">Асуулт нэмэх</a>
            </div>
            <ol class="card-surface mb-0 ps-4 pe-3 py-3">
                @forelse ($item->questions as $question)
                    <li class="mb-3">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <div>{{ $question->prompt }} @if ($question->prompt_ja)<x-ja>{{ $question->prompt_ja }}</x-ja>@endif</div>
                            <div class="table-actions">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.japanese.questions.edit', $question) }}">Засах <span class="visually-hidden">: асуулт {{ $loop->iteration }}</span></a>
                                <form method="POST" action="{{ route('admin.japanese.questions.destroy', $question) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger-token">Устгах <span class="visually-hidden">: асуулт {{ $loop->iteration }}</span></button>
                                </form>
                            </div>
                        </div>
                    </li>
                @empty
                    <li>Асуулт алга байна.</li>
                @endforelse
            </ol>
        </section>
    @endif
@endsection
