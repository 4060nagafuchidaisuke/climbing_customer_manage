<?php

namespace App\Enums;

enum MemberCategory: string
{
    // 会員区分を型に指定
    case SENIOR ='senior'; //シニア
    case GENERAL = 'general'; // 一般
    case UNIVERSITY  = 'university'; // 大学生
    case HIGH_SCHOOL = 'high_school'; // 高校生
    case JUNIOR_HIGH = 'junior_high'; // 中学生
    case ELEMENTARY  = 'elementary';   // 小学生
    
    public function label(): string
    {
        return match ($this) {
            self::SENIOR => 'シニア',
            self::GENERAL => '一般',
            self::UNIVERSITY =>'大学生',
            self::HIGH_SCHOOL => '高校生',
            self::JUNIOR_HIGH => '中学生',
            self::ELEMENTARY => '小学生',
        };
    }

    public function priceTier(): PriceTier
{
    return match ($this) {
        self::GENERAL => PriceTier::GENERAL,
        self::UNIVERSITY => PriceTier::STUDENT,
        self::SENIOR => PriceTier::SENIOR,
        self::HIGH_SCHOOL, self::JUNIOR_HIGH, self::ELEMENTARY => PriceTier::KIDS,
    };
}

}
