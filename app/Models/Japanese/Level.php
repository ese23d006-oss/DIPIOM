<?php

namespace App\Models\Japanese;

use App\Models\Japanese\Concerns\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasStatus;

    protected $table = 'japanese_levels';

    protected $guarded = ['id'];

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function vocabulary(): HasMany
    {
        return $this->hasMany(Vocabulary::class);
    }

    public function kanji(): HasMany
    {
        return $this->hasMany(Kanji::class);
    }

    public function grammar(): HasMany
    {
        return $this->hasMany(Grammar::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function audio(): HasMany
    {
        return $this->hasMany(Audio::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
