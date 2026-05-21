<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Member;
use App\Enums\PlanType;
use App\Enums\PlanStatus;
use App\Enums\MemberCategory;

class MemberPlan extends Model

{
    use HasFactory;

    // fillable：外から書き換えられたら困るものを保護
    protected $fillable = [
        'member_id',
        'plan_type',
        'category',
        'start_date',
        'end_date',
        'is_first_registration',
        'price',
        'status'
    ]; 

    // 型指定
    protected $casts = [
        "plan_type"=>PlanType::class,
        'category'=>MemberCategory::class,
        "start_date"=>"date",
        "end_date"=>"date",
        "is_first_registration"=>"boolean",
        "price"=>"integer",
        'status' => PlanStatus::class
    ];

    /**
     * リレーション設定
     */

    // Membersテーブルと連結
    public function member():BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Accessor(良く使う計算値)
     */

     // 契約期間を日数で返す
    public function getDurationDaysAttribute(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date);
    }

    // 契約が有効かどうか
    public function getIsActiveAttribute(): bool
    {
        return $this->status === PlanStatus::ACTIVE
            && $this->end_date
            && $this->end_date->gte(today());
    }

    /**
     * Scope(CRUD処理)
     */
     // 有効なプランのみ
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
                    ->where('end_date', '>=', now());
    }

    // プラン種別で絞り込み
    public function scopeOfType(Builder $query, PlanType $type):Builder
    {
        return $query->where('plan_type', $type);
    }
}
