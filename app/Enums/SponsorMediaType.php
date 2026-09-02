<?php

namespace App\Enums;

enum SponsorMediaType: string
{
    // 動画と画像をEnum化
    case IMAGE = 'image';
    case VIDEO = 'video';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE => '画像',
            self::VIDEO => '動画',
        };
    }
}
