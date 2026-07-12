<?php

namespace App\Enums;

enum PriceTier: string
{
    case GENERAL = 'general'; // 一般
    case STUDENT_SENIOR = 'student_senior';  // 大学生・シニア
    case KIDS = 'kids';  // キッズ（小中高）

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => '一般',
            self::STUDENT_SENIOR => '大学生・シニア',
            self::KIDS => 'キッズ',
        };
    }
}
