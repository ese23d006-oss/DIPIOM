<?php

namespace App\Models\Japanese;

use App\Models\Japanese\Concerns\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasStatus;

    public const TYPES = [
        'reading' => 'Унших дасгал',
        'conversation' => 'Ярианы дасгал',
        'daily' => 'Өдөр тутмын харилцан яриа',
    ];

    protected $table = 'japanese_lessons';

    protected $guarded = ['id'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function examples(): HasMany
    {
        return $this->hasMany(Example::class, 'lesson_id')->orderBy('position')->orderBy('id');
    }
}
