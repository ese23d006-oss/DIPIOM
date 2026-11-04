<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'summary',
        'transcript',
        'position',
        'duration_seconds',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'duration_seconds' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Өгөгдлийн сангийн cascade нь файлыг устгадаггүй тул Eloquent-ээр устгана.
     */
    protected static function booted(): void
    {
        static::deleting(function (Lesson $lesson) {
            $lesson->media()->get()->each->delete();
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Дэлгэц уншигч зөв уншихаар үгээр бичсэн үргэлжлэх хугацаа.
     */
    public function durationLabel(): ?string
    {
        if (! $this->duration_seconds) {
            return null;
        }

        $minutes = intdiv($this->duration_seconds, 60);
        $seconds = $this->duration_seconds % 60;

        return collect([
            $minutes > 0 ? "{$minutes} минут" : null,
            $seconds > 0 ? "{$seconds} секунд" : null,
        ])->filter()->implode(' ');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(LessonMedia::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(LessonMedia::class)->where('type', MediaType::Video->value);
    }

    public function audioDescription(): HasOne
    {
        return $this->hasOne(LessonMedia::class)->where('type', MediaType::AudioDescription->value);
    }

    public function subtitle(): HasOne
    {
        return $this->hasOne(LessonMedia::class)->where('type', MediaType::Subtitle->value);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }
}
