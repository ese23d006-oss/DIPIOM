@if (! (isset($errors) && $errors->any()))
    @if (session('error'))
        <div class="notice notice--error mb-4" role="alert" tabindex="-1" data-focus-on-load>
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <div class="notice-body">{{ session('error') }}</div>
        </div>
    @elseif (session('status'))
        <div class="notice notice--success mb-4" role="status" tabindex="-1" data-focus-on-load>
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <div class="notice-body">{{ session('status') }}</div>
        </div>
    @endif
@endif
