@extends('layouts.auth')

@section('title', 'Бүртгүүлэх')

@section('content')
    <h1 class="mb-1">Бүртгүүлэх</h1>
    <p class="page-lead mb-4">Бүртгэл үүсгээд сургалтуудад хамрагдаарай.</p>

    <x-form.errors />

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <x-form.field name="name" label="Нэр" required autocomplete="name" />
        <x-form.field name="email" label="Имэйл хаяг" type="email" required autocomplete="email" />
        <x-form.field name="phone" label="Утасны дугаар" type="tel" autocomplete="tel" />
        <x-form.field name="password" label="Нууц үг" type="password" required autocomplete="new-password"
                      hint="Хамгийн багадаа 8 тэмдэгт, үсэг болон тоо агуулсан байна." />
        <x-form.field name="password_confirmation" label="Нууц үг давтах" type="password" required autocomplete="new-password" />

        <button type="submit" class="btn btn-primary w-100">Бүртгүүлэх</button>
    </form>

    <p class="mt-4 mb-0">Бүртгэлтэй юу? <a href="{{ route('login') }}">Нэвтрэх</a></p>
@endsection
