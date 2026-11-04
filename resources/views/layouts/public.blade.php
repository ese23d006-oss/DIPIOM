{{-- Нүүр зэрэг бүтэн өргөнтэй нийтийн хуудас. Хэсгүүд өөрсдөө container-тай. --}}
@extends('layouts.base')

@section('body')
    <x-layout.public-header />

    <main id="main-content" tabindex="-1" class="public-main">
        @if (session('status'))
            <div class="container-xl pt-4"><x-flash /></div>
        @endif

        @yield('content')
    </main>

    <x-layout.footer />
@endsection
