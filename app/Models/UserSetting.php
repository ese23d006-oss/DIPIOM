<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    public const FONT_STYLES = [
        'default' => 'Үндсэн (Inter)',
        'arial' => 'Arial, энгийн хэлбэр',
        'verdana' => 'Verdana, өргөн үсэг',
        'serif' => 'Georgia, хөлтэй үсэг',
    ];

    public const LINE_SPACINGS = [
        'normal' => 'Энгийн',
        'wide' => 'Өргөн',
        'wider' => 'Илүү өргөн',
    ];

    public const CAPTION_SIZES = [
        'normal' => 'Энгийн',
        'large' => 'Том',
        'larger' => 'Илүү том',
    ];

    protected $fillable = [
        'font_style',
        'line_spacing',
        'accessibility_mode',
        'caption_size',
        'font_size_percent',
        'high_contrast',
        'audio_description_enabled',
        'subtitles_enabled',
        'tts_rate',
        'tts_voice',
        'tts_voice_ja',
        'tts_enabled',
        'tts_volume',
        'tts_read_japanese',
        'tts_read_mongolian',
        'tts_highlight',
        'reduce_motion',
    ];

    protected function casts(): array
    {
        return [
            'accessibility_mode' => 'boolean',
            'font_size_percent' => 'integer',
            'high_contrast' => 'boolean',
            'audio_description_enabled' => 'boolean',
            'subtitles_enabled' => 'boolean',
            'tts_rate' => 'float',
            'tts_enabled' => 'boolean',
            'tts_volume' => 'float',
            'tts_read_japanese' => 'boolean',
            'tts_read_mongolian' => 'boolean',
            'tts_highlight' => 'boolean',
            'reduce_motion' => 'boolean',
            'japanese_daily_goal' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
