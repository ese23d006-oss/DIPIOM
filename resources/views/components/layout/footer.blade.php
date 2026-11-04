@props(['compact' => false])

@php $contact = \App\Models\SystemSetting::supportContact(); @endphp

@if ($compact)
    <footer class="site-footer site-footer--compact">
        {{ config('app.name') }}: Харааны бэрхшээлтэй иргэдэд зориулсан сургалтын цахим систем
        @if ($contact)
            <br>Холбоо барих: {{ $contact }}
        @endif
    </footer>
@else
    <footer class="site-footer">
        <div class="container-xl d-flex flex-wrap justify-content-between gap-3">
            <div>
                <p class="fw-bold mb-1">{{ config('app.name') }}</p>
                <p class="mb-0">Харааны бэрхшээлтэй иргэдэд зориулсан сургалтын цахим систем</p>
                @if ($contact)
                    <p class="mb-0">Холбоо барих: {{ $contact }}</p>
                @endif
            </div>
            <nav aria-label="Доод цэс">
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Нүүр</a></li>
                    <li><a href="{{ route('courses.index') }}">Сургалтууд</a></li>
                    <li><a href="{{ route('about') }}">Системийн тухай</a></li>
                </ul>
            </nav>
        </div>
    </footer>
@endif
