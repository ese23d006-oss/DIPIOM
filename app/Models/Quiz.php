<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $table = 'quizzes';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'pass_percent',
        'time_limit_minutes',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'pass_percent' => 'integer',
            'time_limit_minutes' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function isPublishable(): bool
    {
        return $this->questions()->exists();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function results(): HasMany
    {
        return $this->hasMany(QuizResult::class);
    }
}
