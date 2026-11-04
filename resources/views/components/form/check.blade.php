@props(['name', 'label', 'checked' => false, 'hint' => null])

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1"
           @checked(old($name, $checked))
           @if ($hint) aria-describedby="{{ $name }}-hint" @endif>
    <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
    @if ($hint)
        <div id="{{ $name }}-hint" class="field-hint mb-0 ps-1">{{ $hint }}</div>
    @endif
</div>
