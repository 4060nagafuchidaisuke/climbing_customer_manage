<?php

namespace App\Enums;

enum PlanStatus: string
{
    // 契約状態を型に指定
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => '継続中',
            self::EXPIRED => '期限切れ',
            self::CANCELLED => '解約済み',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isEnded(): bool
    {
        return match ($this) {
            self::EXPIRED,
            self::CANCELLED => true,

            self::ACTIVE => false,
        };
    }
}
