@props(['name', 'label', 'value' => null, 'required' => false, 'hint' => null, 'rows' => 4])

@php
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

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
              @class(['form-control', 'is-invalid' => $hasError])
              @if ($required) required @endif
              @if ($hasError) aria-invalid="true" @endif
              @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
              {{ $attributes }}>{{ old($name, $value) }}</textarea>

    @error($name)
        <div id="{{ $name }}-error" class="field-error">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror
</div>
