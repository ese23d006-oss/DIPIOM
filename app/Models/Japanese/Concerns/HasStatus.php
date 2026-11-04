<?php

namespace App\Models\Japanese\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    public function scopePublished(Builder $query): Builder
    {
        return $query->where($query->getModel()->getTable().'.status', 'published');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Нийтлэхэд саад болох шалтгаан, байхгүй бол null.
     */
    public function publishBlocker(): ?string
    {
        return null;
    }
}
