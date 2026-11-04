@props(['passed'])

@if ($passed)
    <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Тэнцсэн</span>
@else
    <span class="badge-status badge-status--error"><i class="bi bi-x-circle-fill" aria-hidden="true"></i> Тэнцээгүй</span>
@endif
