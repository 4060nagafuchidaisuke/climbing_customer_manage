<?php

namespace App\Enums;

enum StaffRole: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => '管理者',
            self::STAFF => 'スタッフ',
        };
    }

    public function canManageStaff(): bool
    {
        return $this === self::ADMIN;
    }
}
