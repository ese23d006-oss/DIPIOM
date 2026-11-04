@php
    $user = auth()->user();
    $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
@endphp

<header class="topbar">
    <button class="btn btn-outline-primary btn-icon d-lg-none" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#app-sidebar" aria-controls="app-sidebar"
            aria-label="Цэс нээх">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>

    <form class="topbar-search" method="GET" action="{{ route('courses.index') }}" role="search" aria-label="Сургалт хайх">
        <label for="topbar-search" class="visually-hidden">Сургалт хайх</label>
        <input id="topbar-search" name="q" type="search" class="form-control" maxlength="100" placeholder="Сургалт хайх">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span>Хайх</span>
        </button>
    </form>

    <div class="topbar-actions">
        <div class="dropdown">
            <button class="btn btn-outline-primary btn-icon" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Мэдэгдэл{{ $notificationCount > 0 ? ', '.$notificationCount.' шинэ' : ', шинэ мэдэгдэл алга' }}">
                <i class="bi bi-bell" aria-hidden="true"></i>
                @if ($notificationCount > 0)
                    <span class="badge-count" aria-hidden="true">{{ $notificationCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end topbar-menu">
                {{-- Гарчиг биш: h1-ийн өмнө h2 байвал хуудасны бүтэц алдагдана. --}}
                <p class="fw-bold px-3 pt-1 mb-2">Мэдэгдэл</p>
                @forelse ($notifications as [$label, $url])
                    <a class="dropdown-item notification-item" href="{{ $url }}">{{ $label }}</a>
                @empty
                    <p class="notification-item mb-0">Шинэ мэдэгдэл алга байна.</p>
                @endforelse
            </div>
        </div>

        <div class="dropdown">
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar" aria-hidden="true">{{ $initial }}</span>
                <span class="d-none d-sm-inline">{{ $user->name }}</span>
                <span class="visually-hidden d-sm-none">{{ $user->name }}</span>
                <span class="visually-hidden">, хэрэглэгчийн цэс</span>
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end topbar-menu">
                <li class="px-3 py-1 text-small">
                    {{ $user->email }}<br>
                    {{ $user->role->label() }}
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Миний профайл</a></li>
                <li><a class="dropdown-item" href="{{ route('settings.edit') }}">Хүртээмжийн тохиргоо</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Гарах</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
