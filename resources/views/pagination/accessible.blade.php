@if ($paginator->hasPages())
    <nav aria-label="Хуудас солих" class="mt-4">
        <p>Нийт {{ $paginator->lastPage() }} хуудасны {{ $paginator->currentPage() }}-р хуудас</p>
        <ul class="pagination flex-wrap">
            @if (! $paginator->onFirstPage())
                <li class="page-item"><a class="page-link" rel="prev" href="{{ $paginator->previousPageUrl() }}">{{ __('pagination.previous') }}</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li @class(['page-item', 'active' => $page == $paginator->currentPage()])>
                            <a class="page-link" href="{{ $url }}"
                               aria-label="{{ $page }}-р хуудас"
                               @if ($page == $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                        </li>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" rel="next" href="{{ $paginator->nextPageUrl() }}">{{ __('pagination.next') }}</a></li>
            @endif
        </ul>
    </nav>
@endif
