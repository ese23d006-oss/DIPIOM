@extends('layouts.app')

@section('title', 'Сургалт, хичээл')

@section('content')
    <x-page-header title="Сургалтын удирдлага" lead="Сургалт үүсгэж, хичээл, файлуудыг нь удирдана.">
        <a class="btn btn-primary" href="{{ route('admin.courses.create') }}">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Сургалт нэмэх
        </a>
    </x-page-header>

    <form method="GET" action="{{ route('admin.courses.index') }}" role="search" aria-label="Сургалт шүүх" class="filter-bar card-surface">
        <div>
            <label for="q" class="form-label">Хайх үг</label>
            <input id="q" name="q" type="search" class="form-control" maxlength="100" value="{{ $term }}">
        </div>
        <div>
            <label for="category" class="form-label">Ангилал</label>
            <select id="category" name="category" class="form-select">
                <option value="">Бүгд</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="form-label">Төлөв</label>
            <select id="status" name="status" class="form-select">
                <option value="">Бүгд</option>
                <option value="published" @selected($status === 'published')>Нийтэлсэн</option>
                <option value="draft" @selected($status === 'draft')>Ноорог</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel" aria-hidden="true"></i> Шүүх</button>
        </div>
    </form>

    <h2 class="mt-4 mb-3" @if ($isFiltered && ! session('status') && ! session('error')) tabindex="-1" data-focus-on-load @endif>
        Олдсон сургалт: {{ $courses->total() }}
    </h2>

    @if ($courses->isEmpty())
        <div class="empty-state">
            <i class="bi bi-collection" aria-hidden="true"></i>
            @if ($isFiltered)
                <p>Шүүлтэд тохирох сургалт олдсонгүй.</p>
                <p><a href="{{ route('admin.courses.index') }}">Шүүлт цэвэрлэх</a></p>
            @else
                <p>Одоогоор сургалт үүсгээгүй байна.</p>
            @endif
        </div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-label="Сургалтын хүснэгт">
                <table class="table table-hover">
                    <caption class="visually-hidden">Сургалтын жагсаалт</caption>
                    <thead>
                        <tr>
                            <th scope="col">Сургалт</th>
                            <th scope="col">Ангилал</th>
                            <th scope="col">Хичээл</th>
                            <th scope="col">Төлөв</th>
                            <th scope="col">Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $course)
                            <tr>
                                <th scope="row" class="fw-semibold">{{ $course->title }}</th>
                                <td>{{ $course->category->name }}</td>
                                <td>{{ $course->lessons_count }}</td>
                                <td>
                                    @if ($course->is_published)
                                        <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Нийтэлсэн</span>
                                    @else
                                        <span class="badge-status badge-status--neutral"><i class="bi bi-pencil" aria-hidden="true"></i> Ноорог</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.courses.lessons.index', $course) }}">
                                            Хичээлүүд <span class="visually-hidden">: {{ $course->title }}</span>
                                        </a>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('courses.show', $course) }}">
                                            Харах <span class="visually-hidden">: {{ $course->title }}</span>
                                        </a>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.courses.edit', $course) }}">
                                            Засах <span class="visually-hidden">: {{ $course->title }}</span>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger-token"
                                                data-bs-toggle="modal" data-bs-target="#delete-course"
                                                data-action="{{ route('admin.courses.destroy', $course) }}"
                                                data-message="{{ $course->title }} сургалтыг устгах уу? Бүх хичээл, видео, аудио файл, шалгалт болон суралцагчдын явц хамт устах бөгөөд буцаах боломжгүй.">
                                            Устгах <span class="visually-hidden">: {{ $course->title }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $courses->links('pagination.accessible') }}

        <x-confirm-modal id="delete-course" title="Сургалт устгах" />
    @endif
@endsection
