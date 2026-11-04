<?php

namespace App\Models\Japanese;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    public const KINDS = [
        'choice' => 'Сонгох (өгүүлбэр нөхөх, таних)',
        'match' => 'Тааруулах',
        'order' => 'Зөв дараалалд оруулах',
    ];

    protected $table = 'japanese_questions';

    protected $guarded = ['id'];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('position')->orderBy('id');
    }
}
