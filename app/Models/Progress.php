<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    protected $table = 'progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'last_position_seconds',
        'percent',
        'is_completed',
        'completed_at',
        'last_watched_at',
    ];

    protected function casts(): array
    {
        return [
            'last_position_seconds' => 'integer',
            'percent' => 'integer',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'last_watched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
