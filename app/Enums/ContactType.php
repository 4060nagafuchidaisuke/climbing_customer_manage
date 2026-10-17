<?php

namespace App\Enums;

enum ContactType: string
{
    // emailかSNS
    case EMAIL = 'email';
    case INSTAGRAM = 'instagram';
    case FACEBOOK = 'facebook';
    case X = 'x';

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'email',
            self::INSTAGRAM => 'Instagram',
            self::FACEBOOK => 'Facebook',
            self::X => 'X(旧Twitter)',
        };
    }
}
