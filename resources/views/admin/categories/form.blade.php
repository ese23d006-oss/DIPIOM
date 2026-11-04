@extends('layouts.app')

@php $isNew = ! $category->exists; @endphp

@section('title', $isNew ? 'Ангилал нэмэх' : 'Ангилал засах')

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.categories.index') }}">Ангилал</a></li>
            <li><span aria-current="page">{{ $isNew ? 'Шинэ ангилал' : $category->name }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$isNew ? 'Ангилал нэмэх' : 'Ангилал засах'" />

    <x-form.errors />

    <form method="POST" action="{{ $isNew ? route('admin.categories.store') : route('admin.categories.update', $category) }}"
          class="card-surface card-pad form-narrow" novalidate>
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <x-form.field name="name" label="Нэр" :value="$category->name" required />
        <x-form.textarea name="description" label="Тайлбар" :value="$category->description" />

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Хадгалах</button>
            <a class="btn btn-outline-primary" href="{{ route('admin.categories.index') }}">Болих</a>
        </div>
    </form>
@endsection
