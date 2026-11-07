<?php

namespace App\Enums;

enum ResidenceArea: string
{
    // お住まい（Forms の Q5 に合わせる）
    case SAGA_INSIDE = 'saga_inside'; // 佐賀県内
    case SAGA_OUTSIDE = 'saga_outside'; // 佐賀県外

    public function label(): string
    {
        return match ($this) {
            self::SAGA_INSIDE => '佐賀県内',
            self::SAGA_OUTSIDE => '佐賀県外',
        };
    }

    // 画面の選択肢用 [値 => 表示名]
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
