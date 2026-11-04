<?php

namespace App\Models\Japanese;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
{
    public const STATUSES = [
        'known' => 'Мэднэ',
        'review' => 'Дахин давтах',
        'hard' => 'Хэцүү байна',
    ];

    protected $table = 'japanese_flashcards';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['last_reviewed_at' => 'datetime'];
    }

    public function vocabulary(): BelongsTo
    {
        return $this->belongsTo(Vocabulary::class);
    }
}
