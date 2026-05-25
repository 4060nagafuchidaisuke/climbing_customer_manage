<?php

namespace App\Enums;

enum PlanType: string
{
    case YEARLY = 'yearly';
    case HALF_YEAR = 'half_year';
    case MONTHLY = 'monthly';
    case HALF_MONTH = 'half_month';
    case VISITOR = 'visitor';

    public function label(): string
    {
        return match ($this) {
            self::YEARLY => '年間会員',
            self::HALF_YEAR => '半年会員',
            self::MONTHLY => '月会員',
            self::HALF_MONTH => '半月会員',
            self::VISITOR => 'ビジター',
        };
    }

    public function isSubscription(): bool
    {
        return match ($this) {
            self::YEARLY,
            self::HALF_YEAR,
            self::MONTHLY,
            self::HALF_MONTH => true,

            self::VISITOR => false,
        };
    }
}
