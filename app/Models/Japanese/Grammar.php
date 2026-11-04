<?php

namespace App\Models\Japanese;

use App\Models\Japanese\Concerns\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grammar extends Model
{
    use HasStatus;

    protected $table = 'japanese_grammar';

    protected $guarded = ['id'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function examples(): HasMany
    {
        return $this->hasMany(Example::class, 'grammar_id')->orderBy('position')->orderBy('id');
    }
}
