<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'quiz_id',
        'body',
        'options',
        'correct_option',
        'points',
        'position',
    ];

    /**
     * Зөв хариулт JSON хариунд санамсаргүй гарахгүй байх ёстой.
     *
     * @var list<string>
     */
    protected $hidden = [
        'correct_option',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'correct_option' => 'integer',
            'points' => 'integer',
            'position' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
