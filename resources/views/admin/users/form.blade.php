@extends('layouts.app')

@php
    $isNew = ! $user->exists;
    $isSelf = $user->is(auth()->user());
@endphp

@section('title', $isNew ? 'Хэрэглэгч нэмэх' : 'Хэрэглэгч засах')

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.users.index') }}">Хэрэглэгчид</a></li>
            <li><span aria-current="page">{{ $isNew ? 'Шинэ хэрэглэгч' : $user->name }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$isNew ? 'Хэрэглэгч нэмэх' : 'Хэрэглэгч засах'" />

    <x-form.errors />

    <form method="POST" action="{{ $isNew ? route('admin.users.store') : route('admin.users.update', $user) }}"
          class="card-surface card-pad form-narrow" novalidate autocomplete="off">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <x-form.field name="name" label="Нэр" :value="$user->name" required />
        <x-form.field name="email" label="Имэйл хаяг" type="email" :value="$user->email" required />
        <x-form.field name="phone" label="Утасны дугаар" type="tel" :value="$user->phone" />

        @if ($isSelf)
            <div class="notice notice--info mb-3">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <div class="notice-body">Системд админгүй болохоос хамгаалж өөрийнхөө эрх, төлвийг өөрчлөх боломжгүй.</div>
            </div>
        @else
            <x-form.select name="role" label="Эрх" :value="$user->role?->value ?? 'student'"
                           :options="collect(\App\Enums\UserRole::cases())->mapWithKeys(fn ($role) => [$role->value => $role->label()])->all()" />
            <x-form.check name="is_active" label="Идэвхтэй" :checked="$isNew ? true : $user->is_active"
                          hint="Идэвхгүй хэрэглэгч системд нэвтэрч чадахгүй." />
        @endif

        <x-form.field name="password" :label="$isNew ? 'Нууц үг' : 'Шинэ нууц үг'" type="password" :required="$isNew" autocomplete="new-password"
                      :hint="$isNew ? 'Хамгийн багадаа 8 тэмдэгт, үсэг болон тоо агуулсан байна.' : 'Солихгүй бол хоосон үлдээнэ үү.'" />
        <x-form.field name="password_confirmation" :label="$isNew ? 'Нууц үг давтах' : 'Шинэ нууц үг давтах'" type="password" :required="$isNew" autocomplete="new-password" />

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Хадгалах</button>
            <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">Болих</a>
        </div>
    </form>
@endsection
