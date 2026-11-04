<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public const FONT_SIZES = [100, 125, 150, 175, 200];

    public function edit(Request $request): View
    {
        return view('settings.edit', [
            'settings' => $request->user()->settings,
            'fontSizes' => self::FONT_SIZES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'font_size_percent' => ['required', 'integer', Rule::in(self::FONT_SIZES)],
            'tts_rate' => ['required', 'numeric', 'between:0.5,2'],
            'font_style' => ['sometimes', Rule::in(array_keys(UserSetting::FONT_STYLES))],
            'line_spacing' => ['sometimes', Rule::in(array_keys(UserSetting::LINE_SPACINGS))],
            'caption_size' => ['sometimes', Rule::in(array_keys(UserSetting::CAPTION_SIZES))],
            'tts_volume' => ['sometimes', 'numeric', 'between:0,1'],
            // Дуу хоолой нь төхөөрөмж тус бүрт өөр: байхгүй бол select илгээгдэхгүй.
            'tts_voice' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tts_voice_ja' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $request->user()->settings->update([
            ...$data,
            'accessibility_mode' => $request->boolean('accessibility_mode'),
            'high_contrast' => $request->boolean('high_contrast'),
            'audio_description_enabled' => $request->boolean('audio_description_enabled'),
            'subtitles_enabled' => $request->boolean('subtitles_enabled'),
            'reduce_motion' => $request->boolean('reduce_motion'),
            'tts_enabled' => $request->boolean('tts_enabled'),
            'tts_read_japanese' => $request->boolean('tts_read_japanese'),
            'tts_read_mongolian' => $request->boolean('tts_read_mongolian'),
            'tts_highlight' => $request->boolean('tts_highlight'),
        ]);

        return back()->with('status', 'Тохиргоо хадгалагдлаа.');
    }
}
