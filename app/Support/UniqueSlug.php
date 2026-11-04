<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueSlug
{
    /**
     * Нэрнээс давхцахгүй slug үүсгэнэ. Кирилл үсгийг латин болгоно.
     *
     * @param  class-string<Model>  $model
     */
    public static function for(string $model, string $text, string $fallback): string
    {
        $base = Str::slug($text) ?: $fallback;
        $base = Str::limit($base, 200, '');
        $slug = $base;
        $suffix = 2;

        while ($model::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
