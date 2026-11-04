@extends('layouts.auth')

@section('title', 'Нууц үг сэргээх')

@section('content')
    <h1 class="mb-1">Нууц үг сэргээх</h1>
    <p class="page-lead mb-4">Бүртгэлтэй имэйл хаягаа оруулна уу. Бид нууц үг сэргээх холбоос илгээнэ.</p>

    <x-form.errors />

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <x-form.field name="email" label="Имэйл хаяг" type="email" required autocomplete="email" />
        <button type="submit" class="btn btn-primary w-100">Холбоос илгээх</button>
    </form>

    <p class="mt-4 mb-0"><a href="{{ route('login') }}">Нэвтрэх хуудас руу буцах</a></p>
@endsection
