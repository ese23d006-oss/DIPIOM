@extends('layouts.auth')

@section('title', 'Нэвтрэх')

@section('content')
    <h1 class="mb-1">Нэвтрэх</h1>
    <p class="page-lead mb-4">Бүртгэлтэй имэйл хаяг, нууц үгээ оруулна уу.</p>

    <x-form.errors />

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <x-form.field name="email" label="Имэйл хаяг" type="email" required autocomplete="username" />
        <x-form.field name="password" label="Нууц үг" type="password" required autocomplete="current-password" />
        <x-form.check name="remember" label="Намайг сана" />

        <button type="submit" class="btn btn-primary w-100">Нэвтрэх</button>
    </form>

    <p class="mt-4 mb-1"><a href="{{ route('password.request') }}">Нууц үгээ мартсан уу?</a></p>
    @if (\App\Models\SystemSetting::registrationOpen())
        <p class="mb-0">Бүртгэлгүй юу? <a href="{{ route('register') }}">Бүртгүүлэх</a></p>
    @endif
@endsection
