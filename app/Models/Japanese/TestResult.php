<?php

namespace App\Models\Japanese;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResult extends Model
{
    protected $table = 'japanese_test_results';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['answers' => 'array', 'passed' => 'boolean', 'submitted_at' => 'datetime'];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
