@props(['items'])

<nav aria-label="Байршил">
    <ol class="breadcrumb-simple">
        @foreach ($items as [$label, $url])
            <li>
                @if ($url)
                    <a href="{{ $url }}"><x-mixed :text="$label" /></a>
                @else
                    <span aria-current="page"><x-mixed :text="$label" /></span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
