@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'autocomplete' => null,
    'hint' => null,
    'placeholder' => null,
])

@php
    $isPassword = $type === 'password';
    $hasError = $errors->has($name);
    $describedBy = collect([
        $hint ? $name.'-hint' : null,
        $hasError ? $name.'-error' : null,
    ])->filter()->implode(' ');
@endphp

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="field-optional">(заавал)</span>
        @endif
    </label>

    @if ($hint)
        <div id="{{ $name }}-hint" class="field-hint">{{ $hint }}</div>
    @endif

    <div @class(['input-group' => $isPassword])>
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @class(['form-control', 'is-invalid' => $hasError])
            @unless ($isPassword) value="{{ old($name, $value) }}" @endunless
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes }}
        >

        @if ($isPassword)
            <button type="button" class="btn btn-outline-primary btn-icon" data-password-toggle="{{ $name }}"
                    aria-pressed="false" aria-label="{{ $label }}: нууц үгийг харуулах">
                <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
        @endif
    </div>

    @error($name)
        <div id="{{ $name }}-error" class="field-error">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror
</div>
