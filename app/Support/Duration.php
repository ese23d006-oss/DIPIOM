<?php

namespace App\Support;

class Duration
{
    /**
     * Хугацааг дэлгэц уншигч зөв уншихаар үгээр бичнэ: "2 цаг 15 минут".
     */
    public static function human(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours === 0 && $minutes === 0) {
            return '0 минут';
        }

        return collect([
            $hours > 0 ? "{$hours} цаг" : null,
            $minutes > 0 ? "{$minutes} минут" : null,
        ])->filter()->implode(' ');
    }
}
