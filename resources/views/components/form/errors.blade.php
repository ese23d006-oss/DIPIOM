@props(['anchors' => []])

@if ($errors->any())
    <div class="notice notice--error mb-4" role="alert" tabindex="-1" data-focus-on-load>
        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
        <div class="notice-body">
            <h2 class="h6 mb-1">Дараах алдааг засна уу ({{ $errors->count() }}):</h2>
            <ul class="mb-0 ps-3">
                @foreach ($errors->keys() as $field)
                    <li><a href="#{{ $anchors[$field] ?? $field }}">{{ $errors->first($field) }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
