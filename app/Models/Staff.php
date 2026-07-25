<?php

namespace App\Models;

use App\Enums\StaffRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'staffs';

    // fillable：外から書き換えられたら困るものを保護
    protected $fillable = [
        'name',
        'phone',
        'address',
        'staff_code',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    // 型指定
    protected $casts = [
        'password' => 'hashed',
        'role' => StaffRole::class,
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // パスワードの保護(カラム隠し)
    protected $hidden = [
        'password',
    ];

    /**
     * リレーション設定
     */
    // 作成したメモとの連携
    public function staffNotes(): HasMany
    {
        return $this->hasMany(StaffNote::class, 'created_by');
    }

    // 入店受付した来店履歴
    public function checkedInVisits(): HasMany
    {
        return $this->hasMany(Visit::class, 'checked_in_by');
    }

    // 退店受付した来店履歴
    public function checkedOutVisits(): HasMany
    {
        return $this->hasMany(Visit::class, 'checked_out_by');
    }

    /**
     * Accessor(良く使う計算値)
     */

    /**
     * Scope(CRUD処理)
     */
    // 有効なスタッフ
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // adminsかどうか
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', StaffRole::ADMIN);
    }

    // staffIDの自動生成
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Staff $staff) {
            $staff->staff_code ??= self::generateStaffCode();
        });
    }

    private static function generateStaffCode(): string
    {
        $latest = self::max('id');
        $number = ($latest ?? 0) + 1;

        return 's'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
