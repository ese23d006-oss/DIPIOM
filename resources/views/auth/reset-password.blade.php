@extends('layouts.auth')

@section('title', 'Шинэ нууц үг')

@section('content')
    <h1 class="mb-4">Шинэ нууц үг тохируулах</h1>

    <x-form.errors />

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-form.field name="email" label="Имэйл хаяг" type="email" :value="$email" required autocomplete="username" />
        <x-form.field name="password" label="Шинэ нууц үг" type="password" required autocomplete="new-password"
                      hint="Хамгийн багадаа 8 тэмдэгт, үсэг болон тоо агуулсан байна." />
        <x-form.field name="password_confirmation" label="Шинэ нууц үг давтах" type="password" required autocomplete="new-password" />

        <button type="submit" class="btn btn-primary w-100">Нууц үг хадгалах</button>
    </form>
@endsection
