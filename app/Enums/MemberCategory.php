<?php

namespace App\Enums;

enum MemberCategory: string
{
    // 会員区分を型に指定
    case GENERAL = 'general';
    case STUDENT = 'student';
    
    public function label(): string
    {
        return match ($this) {
            self::GENERAL => '一般会員',
            self::STUDENT => '学生',
        };
    }

    public function isStudent(): bool
    {
        return $this === self::STUDENT;
    }
}
