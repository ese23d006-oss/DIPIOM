{{-- Нэвтэрсэн хэрэглэгчид sidebar-тай бүтэц, зочинд нийтийн header харуулна. --}}
@extends('layouts.base')

@section('body')
    @auth
        <div class="app-shell">
            <x-layout.sidebar />

            <div class="app-main">
                <x-layout.topbar />

                @if (auth()->user()->settings->tts_enabled)
                    <x-tts-reader />
                @endif

                <main id="main-content" tabindex="-1" class="app-content">
                    <x-flash />
                    @yield('content')
                </main>

                <x-layout.footer compact />
            </div>
        </div>
    @else
        <x-layout.public-header />

        <main id="main-content" tabindex="-1" class="public-main">
            <div class="container-xl page-container">
                <x-flash />
                @yield('content')
            </div>
        </main>

        <x-layout.footer />
    @endauth
@endsection
