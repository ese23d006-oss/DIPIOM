@props(['title', 'lead' => null])

<div class="page-header">
    <div>
        <h1><x-mixed :text="$title" /></h1>
        @if ($lead)
            <p class="page-lead">{{ $lead }}</p>
        @endif
    </div>
    @if (trim($slot) !== '')
        <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
