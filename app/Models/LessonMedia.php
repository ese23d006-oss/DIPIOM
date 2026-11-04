<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LessonMedia extends Model
{
    protected $table = 'lesson_media';

    protected $fillable = [
        'lesson_id',
        'type',
        'language',
        'disk',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'size_bytes' => 'integer',
        ];
    }

    /**
     * Бичлэг устахад серверээс файлыг нь хамт устгана.
     */
    protected static function booted(): void
    {
        static::deleted(function (LessonMedia $media) {
            Storage::disk($media->disk)->delete($media->file_path);
        });
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
