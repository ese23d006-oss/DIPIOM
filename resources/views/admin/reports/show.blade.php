@extends('layouts.app')

@section('title', $title)

@section('content')
    <nav aria-label="Байршил">
        <ol class="breadcrumb-simple">
            <li><a href="{{ route('admin.reports.index') }}">Тайлан</a></li>
            <li><span aria-current="page">{{ $title }}</span></li>
        </ol>
    </nav>

    <x-page-header :title="$title">
        <a class="btn btn-primary" href="{{ route('admin.reports.download', [$report, 'xlsx']) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Excel татах</a>
        <a class="btn btn-outline-primary" href="{{ route('admin.reports.download', [$report, 'pdf']) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> PDF татах</a>
    </x-page-header>

    <h2 class="mb-3">Нийт мөр: {{ $rows->total() }}</h2>

    @if ($rows->isEmpty())
        <div class="empty-state"><p>Энэ тайланд одоогоор өгөгдөл алга байна.</p></div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-label="{{ $title }} хүснэгт">
                <table class="table">
                    <caption class="visually-hidden">{{ $title }}</caption>
                    <thead>
                        <tr>
                            @foreach ($headings as $heading)
                                <th scope="col">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    @if ($loop->first)
                                        <th scope="row">{{ $cell }}</th>
                                    @else
                                        <td>{{ $cell }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $rows->links('pagination.accessible') }}
    @endif
@endsection
