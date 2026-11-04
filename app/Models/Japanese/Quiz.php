<?php

namespace App\Models\Japanese;

use App\Models\Japanese\Concerns\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasStatus;

    public const TYPES = [
        'hiragana' => 'Хирагана таних',
        'katakana' => 'Катакана таних',
        'vocabulary' => 'Үгийн сан',
        'grammar' => 'Дүрэм',
        'kanji' => 'Ханзны утга',
        'listening' => 'Сонсголын тест',
        'jlpt' => 'JLPT бэлтгэл',
    ];

    protected $table = 'japanese_quizzes';

    protected $guarded = ['id'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function audio(): BelongsTo
    {
        return $this->belongsTo(Audio::class, 'audio_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position')->orderBy('id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    public function publishBlocker(): ?string
    {
        if (! $this->questions()->exists()) {
            return 'Асуултгүй тестийг нийтлэх боломжгүй.';
        }

        if ($this->type === 'listening' && ! $this->audio?->file_path) {
            return 'Сонсголын тестэд аудио файл шаардлагатай.';
        }

        return null;
    }
}
