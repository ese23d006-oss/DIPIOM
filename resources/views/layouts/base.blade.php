@php
    $accessibility = auth()->user()?->settings;

    // Утгууд нь зөвхөн тоо болон тогтмол текст тул escape шаардлагагүй.
    $htmlAttributes = collect([
        $accessibility?->high_contrast ? 'data-contrast="high"' : null,
        $accessibility?->reduce_motion ? 'data-motion="reduce"' : null,
        $accessibility?->accessibility_mode ? 'data-a11y="on"' : null,
        // Утгууд нь баталгаажсан жагсаалтаас тул зөвхөн эдгээрийг зөвшөөрнө.
        array_key_exists($accessibility?->font_style ?? '', \App\Models\UserSetting::FONT_STYLES) && $accessibility->font_style !== 'default'
            ? 'data-font="'.$accessibility->font_style.'"' : null,
        array_key_exists($accessibility?->line_spacing ?? '', \App\Models\UserSetting::LINE_SPACINGS) && $accessibility->line_spacing !== 'normal'
            ? 'data-spacing="'.$accessibility->line_spacing.'"' : null,
        array_key_exists($accessibility?->caption_size ?? '', \App\Models\UserSetting::CAPTION_SIZES) && $accessibility->caption_size !== 'normal'
            ? 'data-captions="'.$accessibility->caption_size.'"' : null,
        $accessibility && $accessibility->font_size_percent !== 100
            ? 'style="font-size: '.(int) $accessibility->font_size_percent.'%"'
            : null,
    ])->filter()->map(fn ($attribute) => ' '.$attribute)->implode('');
@endphp
<!DOCTYPE html>
<html lang="mn"{!! $htmlAttributes !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($errors) && $errors->any() ? 'Алдаа: ' : '' }}@yield('title') | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body @if ($accessibility)
        data-tts-rate="{{ $accessibility->tts_rate }}"
        data-tts-volume="{{ $accessibility->tts_volume }}"
        data-tts-highlight="{{ $accessibility->tts_highlight ? '1' : '0' }}"
        data-voice-mn="{{ $accessibility->tts_voice }}"
        data-voice-ja="{{ $accessibility->tts_voice_ja }}"
    @endif>
    <a href="#main-content" class="skip-link">Үндсэн агуулга руу шилжих</a>

    @yield('body')
</body>
</html>
