<?php

namespace App\Models\Japanese;

use App\Models\Japanese\Concerns\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kanji extends Model
{
    use HasStatus;

    protected $table = 'japanese_kanji';

    protected $guarded = ['id'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}
