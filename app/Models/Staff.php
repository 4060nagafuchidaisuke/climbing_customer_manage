<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Enums\StaffRole;

class Staff extends Authenticatable
{
    use HasFactory;

    protected $table = 'staffs';

    // fillable：外から書き換えられたら困るものを保護
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at'
    ]; 

    // 型指定
    protected $casts = [
        'role'=>StaffRole::class,
        'is_active'=>'boolean',
        'last_login_at'=>'datetime'
    ];

    // パスワードの保護(カラム隠し)
    protected $hidden = [
        'password',
];

    /**
     * リレーション設定
     */
    // 作成したメモとの連携
    public function staffNotes():HasMany
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
    public function scopeActive(Builder $query):Builder
    {
        return $query->where('is_active', true);
    }

    // adminsかどうか
    public function scopeAdmins(Builder $query):Builder
    {
        return $query->where('role', StaffRole::ADMIN);
    }

}
