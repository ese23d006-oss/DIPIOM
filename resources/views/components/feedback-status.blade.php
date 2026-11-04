@props(['status'])

@if ($status === \App\Enums\FeedbackStatus::Answered)
    <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> {{ $status->label() }}</span>
@else
    <span class="badge-status badge-status--warning"><i class="bi bi-hourglass-split" aria-hidden="true"></i> {{ $status->label() }}</span>
@endif
