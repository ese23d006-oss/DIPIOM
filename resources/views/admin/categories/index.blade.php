@extends('layouts.app')

@section('title', 'Ангилал')

@section('content')
    <x-page-header title="Сургалтын ангилал" lead="Сургалтуудыг бүлэглэх ангиллууд.">
        <a class="btn btn-primary" href="{{ route('admin.categories.create') }}">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Ангилал нэмэх
        </a>
    </x-page-header>

    @if ($categories->isEmpty())
        <div class="empty-state">
            <i class="bi bi-tags" aria-hidden="true"></i>
            <p>Одоогоор ангилал байхгүй байна.</p>
        </div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-label="Ангиллын хүснэгт">
                <table class="table table-hover">
                    <caption class="visually-hidden">Ангиллын жагсаалт</caption>
                    <thead>
                        <tr>
                            <th scope="col">Нэр</th>
                            <th scope="col">Тайлбар</th>
                            <th scope="col">Сургалтын тоо</th>
                            <th scope="col">Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <th scope="row" class="fw-semibold">{{ $category->name }}</th>
                                <td>{{ $category->description }}</td>
                                <td>{{ $category->courses_count }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}">
                                            Засах <span class="visually-hidden">: {{ $category->name }}</span>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger-token"
                                                data-bs-toggle="modal" data-bs-target="#delete-category"
                                                data-action="{{ route('admin.categories.destroy', $category) }}"
                                                data-message="{{ $category->name }} ангиллыг устгах уу? Сургалттай ангиллыг устгах боломжгүй.">
                                            Устгах <span class="visually-hidden">: {{ $category->name }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $categories->links('pagination.accessible') }}

        <x-confirm-modal id="delete-category" title="Ангилал устгах" />
    @endif
@endsection
