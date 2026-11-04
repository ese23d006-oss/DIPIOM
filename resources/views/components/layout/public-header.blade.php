<header class="public-header">
    <nav class="navbar navbar-expand-lg" aria-label="Үндсэн цэс">
        <div class="container-xl">
            <x-layout.brand class="navbar-brand m-0 me-3" />

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#public-nav" aria-controls="public-nav" aria-expanded="false"
                    aria-label="Цэсийг нээх, хаах">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>

            <div class="collapse navbar-collapse" id="public-nav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Нүүр</a>
                    </li>
                    {{-- Нэвтрэх, бүртгүүлэх хуудсанд эдгээр цэс харагдахгүй. --}}
                    @unless (request()->routeIs('login', 'register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('courses.index') }}" @if (request()->routeIs('courses.*')) aria-current="page" @endif>Сургалтууд</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('about') }}" @if (request()->routeIs('about')) aria-current="page" @endif>Системийн тухай</a>
                        </li>
                    @endunless
                </ul>

                <div class="d-flex flex-wrap align-items-center gap-2 pt-2 pt-lg-0">
                    @auth
                        <a class="btn btn-outline-primary" href="{{ auth()->user()->dashboardUrl() }}">Хянах самбар</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">Гарах</button>
                        </form>
                    @else
                        <a class="btn btn-outline-primary" href="{{ route('login') }}" @if (request()->routeIs('login')) aria-current="page" @endif>Нэвтрэх</a>
                        @if (\App\Models\SystemSetting::registrationOpen())
                            <a class="btn btn-primary" href="{{ route('register') }}" @if (request()->routeIs('register')) aria-current="page" @endif>Бүртгүүлэх</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>
