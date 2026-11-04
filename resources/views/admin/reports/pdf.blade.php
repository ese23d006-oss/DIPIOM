<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        /* DejaVu Sans нь dompdf-д багтдаг, Монгол ө, ү үсэгтэй. */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #0f172a; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        p { margin: 0 0 12px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #94a3b8; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #e2e8f0; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ config('app.name') }}, {{ $generatedAt }}, нийт {{ $rows->count() }} мөр</p>

    @if ($rows->isEmpty())
        <p>Энэ тайланд одоогоор өгөгдөл алга байна.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach ($headings as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
