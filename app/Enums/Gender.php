<?php

namespace App\Enums;

enum Gender: string
{
    // 性別区分
    case MALE ='male'; //男
    case FEMALE = 'female'; // 女
    case OTHER  = 'other'; // その他

    
    public function label(): string
    {
        return match ($this) {
            self::MALE => '男性',
            self::FEMALE => '女性',
            self::OTHER =>'回答しない',
        };
    }
}
