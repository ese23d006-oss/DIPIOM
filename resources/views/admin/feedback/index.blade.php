@extends('layouts.app')

@section('title', 'Санал хүсэлт')

@section('content')
    <x-page-header title="Санал хүсэлт" lead="Суралцагчдын санал хүсэлтэд хариу өгнө." />

    <form method="GET" action="{{ route('admin.feedback.index') }}" class="filter-bar card-surface" aria-label="Төлөвөөр шүүх">
        <div>
            <label for="status" class="form-label">Төлөв</label>
            <select id="status" name="status" class="form-select">
                <option value="">Бүгд</option>
                @foreach (\App\Enums\FeedbackStatus::cases() as $option)
                    <option value="{{ $option->value }}" @selected($status === $option->value)>{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>
        <div><button type="submit" class="btn btn-primary w-100">Шүүх</button></div>
    </form>

    <h2 class="mt-4 mb-3">Олдсон: {{ $items->total() }}</h2>

    @if ($items->isEmpty())
        <div class="empty-state"><p>Санал хүсэлт алга байна.</p></div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-label="Санал хүсэлтийн хүснэгт">
                <table class="table table-hover">
                    <caption class="visually-hidden">Санал хүсэлтүүд, сүүлийнх нь эхэндээ</caption>
                    <thead>
                        <tr>
                            <th scope="col">Гарчиг</th>
                            <th scope="col">Суралцагч</th>
                            <th scope="col">Төлөв</th>
                            <th scope="col">Огноо</th>
                            <th scope="col">Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <th scope="row">{{ $item->subject }}</th>
                                <td>{{ $item->user->name }}</td>
                                <td><x-feedback-status :status="$item->status" /></td>
                                <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.feedback.show', $item) }}">
                                        {{ $item->admin_reply ? 'Харах' : 'Хариулах' }} <span class="visually-hidden">: {{ $item->subject }}</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $items->links('pagination.accessible') }}
    @endif
@endsection
