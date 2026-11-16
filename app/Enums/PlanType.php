<?php

namespace App\Enums;

enum PlanType: string
{
    case VISITOR_1DAY = 'visitor_1day';
    case VISITOR_MINUTS_120 = 'visitor_minutes_120';
    case ONE_DAY = 'one_day';
    case MINUTES_120 = 'minutes_120';
    case MONTHLY = 'monthly';
    case FIFTEEN_DAY = '15_day';
    case HALF_YEAR = 'half_year';

    public function label(): string
    {
        return match ($this) {
            self::VISITOR_1DAY => 'ビジター1日利用料',
            self::VISITOR_MINUTS_120 => 'ビジター120分利用料',
            self::ONE_DAY => '1日利用料',
            self::MINUTES_120 => '120分利用料',
            self::MONTHLY => '月パス',
            self::FIFTEEN_DAY => '15日パス',
            self::HALF_YEAR => '半年パス',
        };
    }

    // ビジター（登録料を払っていない人）向けのプランか
    public function isForVisitor(): bool
    {
        return match ($this) {
            self::VISITOR_1DAY,
            self::VISITOR_MINUTS_120 => true,
            default => false,
        };
    }

    // 「会員／ビジター」を選んだあとのプルダウンに出す名前（ビジターかどうかは上で選んでいるので付けない）
    public function shortLabel(): string
    {
        return match ($this) {
            self::VISITOR_1DAY => '1日利用料',
            self::VISITOR_MINUTS_120 => '120分利用料',
            default => $this->label(),
        };
    }

    // 会員（true）／ビジター（false）が選べるプランを [値 => 表示名] で返す
    public static function optionsFor(bool $registrationFeePaid): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            if ($case->isForVisitor() !== $registrationFeePaid) {
                $options[$case->value] = $case->shortLabel();
            }
        }

        return $options;
    }

    public function isPeriodPass(): bool
    {
        return match ($this) {
            self::VISITOR_1DAY,
            self::VISITOR_MINUTS_120,
            self::ONE_DAY,
            self::MINUTES_120 => false,

            self::MONTHLY,
            self::FIFTEEN_DAY,
            self::HALF_YEAR => true,
        };
    }

    public function durationDays(): ?int
    {
        return match ($this) {
            self::VISITOR_1DAY,
            self::VISITOR_MINUTS_120,
            self::ONE_DAY,
            self::MINUTES_120 => null,

            self::MONTHLY => 30,
            self::FIFTEEN_DAY => 15,
            self::HALF_YEAR => 180,
        };
    }
}
