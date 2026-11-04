<?php

namespace App\Support;

class UploadLimit
{
    /**
     * Програмын хязгаар болон PHP-ийн тохиргооны аль бага нь бодит хязгаар тул
     * хэрэглэгчид түүнийг харуулна.
     */
    public static function effectiveKilobytes(int $configuredKb): int
    {
        return min(
            $configuredKb,
            self::iniKilobytes((string) ini_get('upload_max_filesize')),
            self::iniKilobytes((string) ini_get('post_max_size')),
        );
    }

    public static function label(int $configuredKb): string
    {
        $kilobytes = self::effectiveKilobytes($configuredKb);

        return $kilobytes >= 1024 * 1024
            ? round($kilobytes / (1024 * 1024), 1).' GB'
            : round($kilobytes / 1024).' MB';
    }

    public static function humanBytes(int $bytes): string
    {
        return match (true) {
            $bytes >= 1024 ** 3 => round($bytes / 1024 ** 3, 1).' GB',
            $bytes >= 1024 ** 2 => round($bytes / 1024 ** 2, 1).' MB',
            default => max(1, (int) round($bytes / 1024)).' KB',
        };
    }

    private static function iniKilobytes(string $value): int
    {
        $number = (int) $value;

        return match (strtoupper(substr(trim($value), -1))) {
            'G' => $number * 1024 * 1024,
            'M' => $number * 1024,
            'K' => $number,
            default => (int) ($number / 1024),
        };
    }
}
