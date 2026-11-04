@extends('layouts.app')

@section('title', 'Санал хүсэлт')

@section('content')
    <x-page-header title="Санал хүсэлт" lead="Санал, асуултаа админд илгээж, хариуг нь энд харна." />

    <x-form.errors />

    <section class="card-surface card-pad form-wide" aria-labelledby="new-feedback">
        <h2 id="new-feedback" class="h5">Шинэ санал хүсэлт</h2>
        <form method="POST" action="{{ route('student.feedback.store') }}" novalidate>
            @csrf
            <x-form.field name="subject" label="Гарчиг" required />
            <x-form.textarea name="message" label="Агуулга" required :rows="5" />
            <button type="submit" class="btn btn-primary">Илгээх</button>
        </form>
    </section>

    <section class="section" aria-labelledby="my-feedback">
        <h2 id="my-feedback" class="mb-3">Миний илгээсэн ({{ $items->total() }})</h2>

        @if ($items->isEmpty())
            <div class="empty-state"><p>Та одоогоор санал хүсэлт илгээгээгүй байна.</p></div>
        @else
            <div class="d-grid gap-3">
                @foreach ($items as $item)
                    <article class="card-surface card-pad" aria-labelledby="feedback-{{ $item->id }}">
                        <h3 id="feedback-{{ $item->id }}" class="h6">{{ $item->subject }}</h3>
                        <p class="mb-2">
                            <x-feedback-status :status="$item->status" />
                            <span class="text-small ms-2">{{ $item->created_at->format('Y-m-d H:i') }}</span>
                        </p>
                        <p class="pre-line">{{ $item->message }}</p>
                        @if ($item->admin_reply)
                            <h4 class="h6">Админы хариу, {{ $item->replied_at?->format('Y-m-d H:i') }}</h4>
                            <p class="pre-line mb-0">{{ $item->admin_reply }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
            {{ $items->links('pagination.accessible') }}
        @endif
    </section>
@endsection
