@extends('layouts.app')

@section('title', 'Сургалтууд')

@section('content')
    <x-page-header title="Сургалтууд" lead="Хайлт эсвэл ангиллаар өөрт хэрэгтэй сургалтаа олоорой." />

    {{-- Сонголт өөрчлөгдөхөд хуудас өөрөө шилжихгүй: хэрэглэгч товчлуур дарж хайна. --}}
    <form method="GET" action="{{ route('courses.index') }}" role="search" aria-label="Сургалт шүүх" class="filter-bar card-surface">
        <div>
            <label for="q" class="form-label">Хайх үг</label>
            <input id="q" name="q" type="search" class="form-control" maxlength="100" value="{{ $term }}" placeholder="Сургалтын нэр эсвэл түлхүүр үг">
        </div>
        <div>
            <label for="category" class="form-label">Ангилал</label>
            <select id="category" name="category" class="form-select">
                <option value="">Бүх ангилал</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($categorySlug === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search" aria-hidden="true"></i> Хайх
            </button>
        </div>
    </form>

    @php $isFiltered = $term !== '' || $categorySlug !== ''; @endphp

    <h2 class="mt-4 mb-3" @if ($isFiltered) tabindex="-1" data-focus-on-load @endif>
        @if ($isFiltered)
            Хайлтын үр дүн: {{ $courses->total() }} сургалт олдлоо
        @else
            Нийт {{ $courses->total() }} сургалт
        @endif
    </h2>

    @if ($courses->isEmpty())
        <div class="empty-state">
            <i class="bi bi-search" aria-hidden="true"></i>
            @if ($isFiltered)
                <p>Таны хайлтад тохирох сургалт олдсонгүй. Өөр үгээр хайж үзнэ үү.</p>
                <p><a href="{{ route('courses.index') }}">Бүх сургалтыг харах</a></p>
            @else
                <p>Одоогоор нийтлэгдсэн сургалт байхгүй байна.</p>
            @endif
        </div>
    @else
        <ul class="grid-cards">
            @foreach ($courses as $course)
                <li><x-course-card :course="$course" /></li>
            @endforeach
        </ul>

        {{ $courses->links('pagination.accessible') }}
    @endif
@endsection
