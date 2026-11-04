<?php

namespace App\Models\Japanese;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $table = 'japanese_answers';

    protected $guarded = ['id'];

    /**
     * Сонголтын JSON эсвэл HTML-д зөв хариулт санамсаргүй гарахгүй.
     *
     * @var list<string>
     */
    protected $hidden = ['is_correct', 'match_text', 'position'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
