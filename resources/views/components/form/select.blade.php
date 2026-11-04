@props(['name', 'label', 'options', 'value' => null, 'hint' => null])

@php
    $hasError = $errors->has($name);
    $describedBy = collect([
        $hint ? $name.'-hint' : null,
        $hasError ? $name.'-error' : null,
    ])->filter()->implode(' ');
    $current = (string) old($name, $value);
@endphp

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>

    @if ($hint)
        <div id="{{ $name }}-hint" class="field-hint">{{ $hint }}</div>
    @endif

    <select id="{{ $name }}" name="{{ $name }}" @class(['form-select', 'is-invalid' => $hasError])
            @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @error($name)
        <div id="{{ $name }}-error" class="field-error">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror
</div>
