<?php

namespace App\Enums;

enum PriceTier: string
{
    case GENERAL = 'general'; // 一般
    case SENIOR = 'senior';  // 大学生・シニア
    case STUDENT = 'student';  // 大学生・シニア
    case KIDS = 'kids';  // キッズ（小中高）

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => '一般',
            self::STUDENT => '大学生',
            self::SENIOR => 'シニア',
            self::KIDS => 'キッズ',
        };
    }
}
