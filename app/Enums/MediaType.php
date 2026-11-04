<?php

namespace App\Enums;

enum MediaType: string
{
    case Video = 'video';
    case AudioDescription = 'audio_description';
    case Subtitle = 'subtitle';

    public function label(): string
    {
        return match ($this) {
            self::Video => 'Видео',
            self::AudioDescription => 'Аудио тайлбар',
            self::Subtitle => 'Хадмал',
        };
    }

    /**
     * Зөвшөөрөгдсөн өргөтгөлүүд.
     *
     * @return list<string>
     */
    public function extensions(): array
    {
        return match ($this) {
            self::Video => ['mp4', 'webm'],
            self::AudioDescription => ['mp3', 'ogg', 'wav'],
            self::Subtitle => ['vtt'],
        };
    }

    /**
     * Файлын агуулгаас тодорхойлсон зөвшөөрөгдөх төрлүүд. Хадмал нь энгийн
     * текст тул агуулгыг нь тусад нь шалгана.
     *
     * @return list<string>
     */
    public function mimeTypes(): array
    {
        return match ($this) {
            self::Video => ['video/mp4', 'video/webm'],
            self::AudioDescription => ['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/vnd.wave'],
            self::Subtitle => [],
        };
    }

    public function maxKilobytes(): int
    {
        return match ($this) {
            self::Video => config('lms.uploads.video_kb'),
            self::AudioDescription => config('lms.uploads.audio_kb'),
            self::Subtitle => config('lms.uploads.subtitle_kb'),
        };
    }

    public function help(): string
    {
        return match ($this) {
            self::Video => 'Суралцагчид үзэх хичээлийн видео.',
            self::AudioDescription => 'Видеотой зэрэг тоглох тусдаа аудио файл. Видеотой ижил урттай байх ёстой.',
            self::Subtitle => 'WebVTT форматтай, UTF-8 кодчилолтой хадмал файл.',
        };
    }
}
