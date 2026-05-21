<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    // fillable：外から書き換えられたら困るものを保護
    protected $fillable = [
        "member_code",
        "barcode",
        "last_name",
        "first_name",
        "last_name_kana",
        "first_name_kana",
        "birth_date",
        "gender",
        "phone",
        "email",
        "postal_code",
        "address",
        "occupation",
        "photo_path",
        "climbing_level",
        "injury_notes",
        "caution_flag",
        "caution_notes",
        "is_minor",
        "guardian_name",
        "guardian_phone",
        "emergency_name",
        "emergency_relation",
        "emergency_phone"
    ]; 

    // 型指定
    protected $casts = [
        "birth_date" => "date",
        "caution_flag" => "boolean",
        "is_minor" => "boolean"
    ];

    /**
     * リレーション設定
     */
    // 契約内容（Members_plansテーブルとの紐づけ）
    public function memberPlans():HasMany
    {
        return $this->hasMany(MemberPlan::class);
    }

    // 来店履歴（visitsテーブルとの紐づけ）
    public function  visits():HasMany
    {
        return $this->hasMany(Visit::class);
    }
    
    // 同意書（waiversテーブルとの紐づけ）
    public function waivers():HasMany
    {
        return $this->hasMany(Waiver::class);
    }

    // スタッフメモ（staff_notesテーブルとの紐づけ）
    public function staffNotes():HasMany
    {
        return $this->hasMany(StaffNote::class);
    }
        
    /**
     * Accessor
     */
    
    // 名前の取得
    public function getFullNameAttribute():string
    {
        return "{$this->last_name}{$this->first_name}";
    }
    
    public function getFullNameKanaAttribute():string
    {
        return "{$this->last_name_kana}{$this->first_name_kana}";
    }

    //年齢の計算
    public function getAgeAttribute():?int
    {
        return $this->birth_date?->age;
    }

    
    // 現在有効なプラン（最新1件）
    public function activePlan()
    {
        return $this->hasOne(MemberPlan::class)
                    ->where('status', 'active')
                    ->latestOfMany();
    }

    // 現在入店中（退店時刻がnull）
    public function activeVisit()
    {
        return $this->hasOne(Visit::class)
                    ->whereNull('exit_at')
                    ->latestOfMany();
    }

    /**
     * Scope（）
     */
    // 注意フラグが立っている会員を検索
    public function scopeCaution($query)
    {
        return $query->where('caution_flag', true);
    }

    // 現在入店中の会員
    public function scopeCurrentlyIn($query)
    {
        return $query->whereHas('activeVisit');
    }

    // バーコード or 会員番号で検索（受付用）
    public function scopeFindByCode($query, string $code)
    {
        return $query->where(function ($q) use ($code) {
        $q->where('barcode', $code)->orWhere('member_code', $code);
        });
    }

}
