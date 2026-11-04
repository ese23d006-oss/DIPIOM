<?php

namespace App\Enums;

enum FeedbackStatus: string
{
    case Pending = 'pending';
    case Answered = 'answered';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Хариу хүлээж буй',
            self::Answered => 'Хариу өгсөн',
        };
    }
}
