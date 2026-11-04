<?php

namespace App\Models\Japanese;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'japanese_categories';

    protected $guarded = ['id'];

    public function vocabulary(): HasMany
    {
        return $this->hasMany(Vocabulary::class);
    }
}
