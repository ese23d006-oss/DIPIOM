{{-- Нэвтрэх, бүртгүүлэх зэрэг form-ийн төвлөрсөн бүтэц. --}}
@extends('layouts.base')

@section('body')
    <x-layout.public-header />

    <main id="main-content" tabindex="-1" class="public-main">
        <div class="auth-wrap">
            <div class="auth-card card-surface">
                <x-flash />
                @yield('content')
            </div>
        </div>
    </main>

    <x-layout.footer />
@endsection
