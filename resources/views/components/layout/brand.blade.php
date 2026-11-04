@props(['href' => route('home')])

<a {{ $attributes->class('brand') }} href="{{ $href }}">
    <span class="brand-mark" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></span>
    <span>{{ config('app.name') }}<span class="visually-hidden">, нүүр хуудас</span></span>
</a>
