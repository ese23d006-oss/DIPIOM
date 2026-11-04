@extends('layouts.app')

@section('title', 'Тайлан')

@section('content')
    <x-page-header title="Тайлан" lead="Тайланг дэлгэцэд үзэж, Excel эсвэл PDF хэлбэрээр татна." />

    <div class="notice notice--info mb-4">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <div class="notice-body">Дэлгэц уншигчаар ажиллах бол Excel файл эсвэл энэ хуудасны хүснэгтийг ашиглахыг зөвлөж байна. PDF файл нь хэвлэхэд зориулагдсан тул дэлгэц уншигчид хүснэгтийн бүтэц нь таниулагдахгүй байж болно.</div>
    </div>

    <ul class="grid-cards">
        @foreach ($titles as $key => $title)
            <li>
                <article class="card-surface card-pad h-100 d-flex flex-column gap-2">
                    <h2 class="h5 mb-0"><a href="{{ route('admin.reports.show', $key) }}">{{ $title }}</a></h2>
                    <div class="mt-auto d-flex flex-wrap gap-2 pt-2">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reports.download', [$key, 'xlsx']) }}">Excel татах <span class="visually-hidden">: {{ $title }}</span></a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reports.download', [$key, 'pdf']) }}">PDF татах <span class="visually-hidden">: {{ $title }}</span></a>
                    </div>
                </article>
            </li>
        @endforeach
    </ul>
@endsection
