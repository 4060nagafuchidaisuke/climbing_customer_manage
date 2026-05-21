<?php

namespace App\Enums;

enum VisitType: string
{
    case MEMBER  = 'member';
    case TRIAL   = 'trial';
    case LESSON  = 'lesson';

    public function label(): string
    {
        return match ($this) {
            self::MEMBER => '通常利用',
            self::TRIAL => '体験利用',
            self::LESSON => 'レッスン',
        };
    }

    public function isPaid(): bool
    {
        return match ($this) {
            self::MEMBER,
            self::TRIAL => true,
            self::LESSON => false,
        };
    }

}
