<?php

namespace App\Enums;

enum ClimbingLevel: string
{
    // クライミングレベル
    case FIRST_TIME = 'first_time'; // 初めて
    case BEGINNER = 'beginner'; // 初心者
    case INTERMEDIATE = 'intermediate'; // 中級者
    case ADVANCE = 'advance'; // 上級者

    public function label(): string
    {
        return match ($this) {
            self::FIRST_TIME => '初体験',
            self::BEGINNER => '初心者',
            self::INTERMEDIATE => '中級者',
            self::ADVANCE => '上級者',
        };
    }
}
