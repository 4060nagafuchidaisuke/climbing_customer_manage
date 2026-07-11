<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\PlanStatus;

class MemberPlan extends Model

{
    use HasFactory;

    // fillable：外から書き換えられたら困るものを保護
    protected $fillable = [
        'member_id',
        'plan_id',
        'price_paid',
        'start_date',
        'end_date',
        'cancelled_at',
    ]; 

    // 型指定
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cancelled_at' => 'datetime'
    ];

    /**
     * リレーション設定
     */

    // 逆リレーション：この購入記録は、どの会員のものか
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
     
    //この購入記録は、どのプラン（商品マスタ）か
    public function plan(): BelongsTo     
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Accessor(良く使う計算値)
     */

    // status を保存せず、cancelled_at / end_date から導出
    public function getStatusAttribute(): PlanStatus
    {
        if ($this->cancelled_at !== null) {
            return PlanStatus::CANCELLED;
        }
        if ($this->end_date !== null && $this->end_date->isPast()) {
            return PlanStatus::EXPIRED;
        }
        return PlanStatus::ACTIVE;
    }

    // 契約が有効かどうか
    public function getIsActiveAttribute(): bool
    {
        return $this->status === PlanStatus::ACTIVE;
    }

    /**
     * Scope(CRUD処理)
     */
    // 有効なプランのみ（Member::activePlan から呼ばれる）
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('cancelled_at')->where(function ($q)
        {
            $q->whereDate('end_date', '>=', now())->orWhereNull('end_date');
        });
    }
}
