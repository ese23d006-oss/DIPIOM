@extends('layouts.app')

@section('title', 'Хичээлүүд: '.$course->title)

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.courses.index') }}">Сургалтууд</a></li>
            <li><span aria-current="page">{{ $course->title }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$course->title" lead="Хичээлүүдийн дараалал, агуулга, файлууд.">
        <a class="btn btn-primary" href="{{ route('admin.courses.lessons.create', $course) }}">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Хичээл нэмэх
        </a>
        <a class="btn btn-outline-primary" href="{{ route('admin.courses.quizzes.index', $course) }}">Шалгалтууд</a>
        <a class="btn btn-outline-primary" href="{{ route('admin.courses.edit', $course) }}">Сургалт засах</a>
    </x-page-header>

    @unless ($course->is_published)
        <div class="notice notice--warning mb-4">
            <i class="bi bi-eye-slash" aria-hidden="true"></i>
            <div class="notice-body">Энэ сургалт нийтлэгдээгүй тул суралцагчдад харагдахгүй.</div>
        </div>
    @endunless

    @if ($lessons->isEmpty())
        <div class="empty-state">
            <i class="bi bi-journal-text" aria-hidden="true"></i>
            <p>Энэ сургалтад одоогоор хичээл байхгүй байна.</p>
        </div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-label="Хичээлийн хүснэгт">
                <table class="table table-hover">
                    <caption class="visually-hidden">Хичээлүүд дарааллаараа</caption>
                    <thead>
                        <tr>
                            <th scope="col">Дугаар</th>
                            <th scope="col">Хичээл</th>
                            <th scope="col">Агуулга</th>
                            <th scope="col">Төлөв</th>
                            <th scope="col">Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lessons as $lesson)
                            @php $types = $lesson->media->map(fn ($item) => $item->type); @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <th scope="row" class="fw-semibold">{{ $lesson->title }}</th>
                                <td>
                                    <ul class="list-unstyled mb-0 text-small">
                                        @foreach (\App\Enums\MediaType::cases() as $type)
                                            <li>
                                                <i class="bi {{ $types->contains($type) ? 'bi-check-circle-fill' : 'bi-dash-circle' }}" aria-hidden="true"></i>
                                                {{ $type->label() }}: {{ $types->contains($type) ? 'байгаа' : 'байхгүй' }}
                                            </li>
                                        @endforeach
                                        <li>
                                            <i class="bi {{ filled($lesson->transcript) ? 'bi-check-circle-fill' : 'bi-dash-circle' }}" aria-hidden="true"></i>
                                            Бичвэр: {{ filled($lesson->transcript) ? 'байгаа' : 'байхгүй' }}
                                        </li>
                                    </ul>
                                </td>
                                <td>
                                    @if ($lesson->is_published)
                                        <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Нийтэлсэн</span>
                                    @else
                                        <span class="badge-status badge-status--neutral"><i class="bi bi-pencil" aria-hidden="true"></i> Ноорог</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.lessons.media', $lesson) }}">
                                            Файлууд <span class="visually-hidden">: {{ $lesson->title }}</span>
                                        </a>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.lessons.edit', $lesson) }}">
                                            Засах <span class="visually-hidden">: {{ $lesson->title }}</span>
                                        </a>
                                        @unless ($loop->first)
                                            <form method="POST" action="{{ route('admin.lessons.move', $lesson) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="direction" value="up">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-arrow-up" aria-hidden="true"></i> Дээш <span class="visually-hidden">: {{ $lesson->title }}</span>
                                                </button>
                                            </form>
                                        @endunless
                                        @unless ($loop->last)
                                            <form method="POST" action="{{ route('admin.lessons.move', $lesson) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="direction" value="down">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-arrow-down" aria-hidden="true"></i> Доош <span class="visually-hidden">: {{ $lesson->title }}</span>
                                                </button>
                                            </form>
                                        @endunless
                                        <button type="button" class="btn btn-sm btn-danger-token"
                                                data-bs-toggle="modal" data-bs-target="#delete-lesson"
                                                data-action="{{ route('admin.lessons.destroy', $lesson) }}"
                                                data-message="{{ $lesson->title }} хичээлийг устгах уу? Файлууд болон суралцагчдын явц хамт устах бөгөөд буцаах боломжгүй.">
                                            Устгах <span class="visually-hidden">: {{ $lesson->title }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <x-confirm-modal id="delete-lesson" title="Хичээл устгах" />
    @endif
@endsection
