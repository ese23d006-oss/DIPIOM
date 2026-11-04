@extends('layouts.app')

@section('title', $config['label'])

@section('content')
    <x-japanese.crumbs :items="[['Япон хэл', route('admin.japanese.hub')], [$config['label'], null]]" />
    <x-page-header :title="$config['label']">
        @if ($resource !== 'examples')
            <a class="btn btn-primary" href="{{ route('admin.japanese.create', $resource) }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Нэмэх</a>
        @endif
        @if ($config['hasStatus'])
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#publish-all"
                    data-action="{{ route('admin.japanese.publish-all', $resource) }}"
                    data-message="Бүх ноорогийг нийтлэх үү? Зөвхөн шалгасан агуулгыг нийтэлнэ үү. Суралцагчдад шууд харагдана.">Бүх ноорогийг нийтлэх</button>
        @endif
    </x-page-header>

    <form method="GET" action="{{ route('admin.japanese.index', $resource) }}" class="filter-bar card-surface" role="search" aria-label="Шүүх">
        <div>
            <label for="q" class="form-label">Хайх</label>
            <input id="q" name="q" type="search" class="form-control" value="{{ $term }}" maxlength="100">
        </div>
        @if ($config['hasStatus'])
            <div>
                <label for="status" class="form-label">Төлөв</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Бүгд</option>
                    <option value="draft" @selected($status === 'draft')>Ноорог</option>
                    <option value="published" @selected($status === 'published')>Нийтэлсэн</option>
                </select>
            </div>
        @endif
        <div><button type="submit" class="btn btn-primary w-100">Шүүх</button></div>
    </form>

    <h2 class="mt-4 mb-3">Олдсон: {{ $items->total() }}</h2>

    @if ($items->isEmpty())
        <div class="empty-state"><p>Бичлэг алга байна.</p></div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-label="{{ $config['label'] }} хүснэгт">
                <table class="table table-hover">
                    <caption class="visually-hidden">{{ $config['label'] }} жагсаалт</caption>
                    <thead>
                        <tr>
                            @foreach ($config['columns'] as $label)
                                <th scope="col">{{ $label }}</th>
                            @endforeach
                            @if ($config['hasStatus'])
                                <th scope="col">Төлөв</th>
                            @endif
                            <th scope="col">Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php $title = $item->{$config['title']}; @endphp
                            <tr>
                                @foreach (array_keys($config['columns']) as $column)
                                    @php
                                        $value = $item->{$column};
                                        $value = match ($column) {
                                            'type' => \App\Models\Japanese\Quiz::TYPES[$value] ?? \App\Models\Japanese\Lesson::TYPES[$value] ?? $value,
                                            'script' => $value === 'hiragana' ? 'Хирагана' : 'Катакана',
                                            default => $value,
                                        };
                                    @endphp
                                    <td><x-mixed :text="$value" /></td>
                                @endforeach
                                @if ($config['hasStatus'])
                                    <td>
                                        @if ($item->isPublished())
                                            <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Нийтэлсэн</span>
                                        @else
                                            <span class="badge-status badge-status--neutral"><i class="bi bi-pencil" aria-hidden="true"></i> Ноорог</span>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.japanese.edit', [$resource, $item->id]) }}">Засах <span class="visually-hidden">: {{ $title }}</span></a>
                                        @if ($config['hasStatus'])
                                            <form method="POST" action="{{ route('admin.japanese.status', [$resource, $item->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-primary">{{ $item->isPublished() ? 'Ноорог болгох' : 'Нийтлэх' }} <span class="visually-hidden">: {{ $title }}</span></button>
                                            </form>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger-token" data-bs-toggle="modal" data-bs-target="#delete-item"
                                                data-action="{{ route('admin.japanese.destroy', [$resource, $item->id]) }}"
                                                data-message="{{ $title }} бичлэгийг устгах уу? Буцаах боломжгүй.">Устгах <span class="visually-hidden">: {{ $title }}</span></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $items->links('pagination.accessible') }}

        <x-confirm-modal id="delete-item" title="Бичлэг устгах" />
    @endif

    @if ($config['hasStatus'])
        <x-confirm-modal id="publish-all" title="Бүх ноорогийг нийтлэх" method="POST" confirm-label="Нийтлэх" />
    @endif
@endsection
