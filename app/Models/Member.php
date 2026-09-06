<?php

namespace App\Models;

use App\Enums\ClimbingLevel;
use App\Enums\Gender;
use App\Enums\MemberCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    use HasFactory;

    // fillable：一括代入(mass assignment)で書き込みを許可するカラムのホワイトリスト
    protected $fillable = [
        'member_code',
        'barcode',
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'birth_date',
        'gender',
        'phone',
        'email',
        'postal_code',
        'address',
        'occupation',
        'photo_path',
        'climbing_level',
        'injury_notes',
        'caution_flag',
        'caution_notes',
        'is_minor',
        'guardian_name',
        'guardian_phone',
        'emergency_name',
        'emergency_relation',
        'emergency_phone',
        'category',
        'registered_at',
    ];

    // 型指定
    protected $casts = [
        'birth_date' => 'date',
        'caution_flag' => 'boolean',
        'is_minor' => 'boolean',
        'gender' => Gender::class,
        'category' => MemberCategory::class,
        'climbing_level' => ClimbingLevel::class,
        'registered_at' => 'datetime',
    ];

    /**
     * リレーション設定
     */
    // 契約内容（Members_plansテーブルとの紐づけ）
    public function memberPlans(): HasMany
    {
        return $this->hasMany(MemberPlan::class);
    }

    // 来店履歴（visitsテーブルとの紐づけ）
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    // 同意書（waiversテーブルとの紐づけ）
    public function waivers(): HasMany
    {
        return $this->hasMany(Waiver::class);
    }

    // スタッフメモ（staff_notesテーブルとの紐づけ）
    public function staffNotes(): HasMany
    {
        return $this->hasMany(StaffNote::class);
    }

    // 前回来店時の日時表示
    public function latestVisit(): HasOne
    {
        return $this->hasOne(Visit::class)->latestOfMany('check_in_at');
    }

    // 在店中画面の前回来店表示機能
    public function previousVisit(): HasOne
    {
        return $this->hasOne(Visit::class)
            ->whereNotNull('check_out_at')     // 退店済み＝過去の来店だけ
            ->latestOfMany('check_in_at');     // その中で最新の1件
    }

    /**
     * Accessor
     */

    // 名前の取得
    public function getFullNameAttribute(): string
    {
        return "{$this->last_name}{$this->first_name}";
    }

    public function getFullNameKanaAttribute(): string
    {
        return "{$this->last_name_kana}{$this->first_name_kana}";
    }

    // 年齢の計算
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    // 現在有効なプラン（最新1件）
    public function activePlan()
    {
        return $this->hasOne(MemberPlan::class)->active()->latestOfMany();
    }

    // 現在入店中（退店時刻がnull）
    public function activeVisit()
    {
        return $this->hasOne(Visit::class)->whereNull('check_out_at')->latestOfMany();
    }

    // 現在退店中かどうか判定(2重読み防止)
    public function isStaying(): bool
    {
        return $this->activeVisit()->exists();
    }

    // 会員かビジターか
    public function getIsVisitorAttribute(): bool
    {
        return $this->registered_at === null;
    }

    /**
     * Scope（）
     */
    // 注意フラグが立っている会員を検索
    public function scopeCaution(Builder $query)
    {
        return $query->where('caution_flag', true);
    }

    // 現在入店中の会員
    public function scopeCurrentlyIn(Builder $query)
    {
        return $query->whereHas('activeVisit');
    }

    // バーコード or 会員番号で検索（受付用）
    public function scopeFindByCode(Builder $query, string $code)
    {
        return $query->where(function ($q) use ($code) {
            $q->where('barcode', $code)->orWhere('member_code', $code);
        });
    }

    // 会員番号の自動採番と変更
    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            if (empty($member->member_code)) {
                $next = (int) static::max('member_code') + 1;
                $member->member_code = str_pad($next, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public static function generateMemberCode(): string
    {
        // FOR UPDATE で現存の最大値をロックして読む（呼び出し側がトランザクション内である前提）
        $next = (int) static::lockForUpdate()->max('member_code') + 1;

        return str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    // 会員かビジターか
    public function scopeVisitors($q)
    {
        return $q->whereNull('registered_at');
    }

    public function scopeRegistered($q)
    {
        return $q->whereNotNull('registered_at');
    }
}
