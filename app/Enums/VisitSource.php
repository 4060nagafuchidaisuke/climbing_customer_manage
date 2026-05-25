<?php

namespace App\Enums;

enum VisitSource:string
{
    // 受付方法（barcode / manual）
    case BARCODE = 'barcode';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::BARCODE => 'バーコード',
            self::MANUAL => 'マニュアル受付',
        };
    }
}
