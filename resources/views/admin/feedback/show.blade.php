@extends('layouts.app')

@section('title', 'Санал хүсэлт: '.$item->subject)

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.feedback.index') }}">Санал хүсэлт</a></li>
            <li><span aria-current="page">{{ $item->subject }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$item->subject" />

    <x-form.errors />

    <section class="card-surface card-pad form-wide" aria-labelledby="feedback-message">
        <h2 id="feedback-message" class="h5">Суралцагчийн илгээсэн</h2>
        <p class="mb-2">
            <x-feedback-status :status="$item->status" />
            <span class="text-small ms-2">{{ $item->user->name }}, {{ $item->created_at->format('Y-m-d H:i') }}</span>
        </p>
        <p class="pre-line mb-0">{{ $item->message }}</p>
    </section>

    <section class="card-surface card-pad form-wide mt-4" aria-labelledby="feedback-reply">
        <h2 id="feedback-reply" class="h5">{{ $item->admin_reply ? 'Хариу засах' : 'Хариу өгөх' }}</h2>
        @if ($item->replied_at)
            <p class="text-small">Сүүлд хариулсан: {{ $item->replier?->name ?? 'Устгагдсан админ' }}, {{ $item->replied_at->format('Y-m-d H:i') }}</p>
        @endif
        <form method="POST" action="{{ route('admin.feedback.reply', $item) }}" novalidate>
            @csrf
            @method('PUT')
            <x-form.textarea name="admin_reply" label="Хариу" :value="$item->admin_reply" required :rows="5" />
            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">Хариу илгээх</button>
                <a class="btn btn-outline-primary" href="{{ route('admin.feedback.index') }}">Болих</a>
            </div>
        </form>
    </section>
@endsection
