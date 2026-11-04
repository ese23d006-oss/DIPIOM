<?php

namespace App\Models\Japanese;

use App\Models\Japanese\Concerns\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vocabulary extends Model
{
    use HasStatus;

    protected $table = 'japanese_vocabulary';

    protected $guarded = ['id'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function flashcards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
