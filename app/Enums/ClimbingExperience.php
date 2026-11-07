<?php

namespace App\Enums;

enum ClimbingExperience: string
{
    // クライミング経験（Forms の Q6 に合わせる。お客さんの自己申告）
    // スタッフが判断する「レベル」は ClimbingLevel で別に持つ
    case FIRST_TIME = 'first_time'; // 初めて
    case WITHIN_ONE_YEAR = 'within_one_year'; // 経験あり（1年以内）
    case OVER_ONE_YEAR = 'over_one_year'; // 経験あり（1年以上）

    public function label(): string
    {
        return match ($this) {
            self::FIRST_TIME => '初めて',
            self::WITHIN_ONE_YEAR => '経験あり（1年以内）',
            self::OVER_ONE_YEAR => '経験あり（1年以上）',
        };
    }

    // 画面の選択肢用 [値 => 表示名]
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }

    // 取り込み時にレベルの初期値を決める（スタッフがあとで変えられる）
    public function initialLevel(): ClimbingLevel
    {
        return match ($this) {
            self::FIRST_TIME => ClimbingLevel::FIRST_TIME,
            self::WITHIN_ONE_YEAR => ClimbingLevel::BEGINNER,
            self::OVER_ONE_YEAR => ClimbingLevel::INTERMEDIATE,
        };
    }
}
