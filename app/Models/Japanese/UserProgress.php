<?php

namespace App\Models\Japanese;

use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    public const TYPES = [
        'character' => Character::class,
        'vocabulary' => Vocabulary::class,
        'kanji' => Kanji::class,
        'grammar' => Grammar::class,
        'lesson' => Lesson::class,
        'audio' => Audio::class,
    ];

    protected $table = 'japanese_user_progress';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['last_studied_at' => 'datetime'];
    }
}
