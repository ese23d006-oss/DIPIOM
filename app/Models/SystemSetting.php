<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function value(string $key, ?string $default = null): ?string
    {
        // Алдааны хуудас өгөгдлийн сан ажиллахгүй үед ч нээгдэх ёстой.
        try {
            return static::where('key', $key)->value('value') ?? $default;
        } catch (Throwable) {
            return $default;
        }
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function registrationOpen(): bool
    {
        return static::value('registration_open', '1') === '1';
    }

    public static function supportContact(): ?string
    {
        return static::value('support_contact');
    }
}
