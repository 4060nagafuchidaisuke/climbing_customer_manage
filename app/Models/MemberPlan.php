<?php

namespace App\Models;

use App\Enums\PlanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPlan extends Model
{
    use HasFactory;

    // 期間パスの期限が近いとみなす日数（残り3日以内）。受付で更新の声かけをする目安
    public const EXPIRING_SOON_DAYS = 3;

    // fillable：一括代入(mass assignment)で書き込みを許可するカラムのホワイトリスト
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
        'cancelled_at' => 'datetime',
    ];

    /**
     * リレーション設定
     */

    // 逆リレーション：この購入記録は、どの会員のものか
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    // この購入記録は、どのプラン（商品マスタ）か
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
        // 終了日の当日までは使える（scopeActive と同じ基準。翌日から期限切れ）
        if ($this->end_date !== null && $this->end_date->lt(today())) {
            return PlanStatus::EXPIRED;
        }

        return PlanStatus::ACTIVE;
    }

    // 期間パスの残り日数（終了日の当日は 0、期限切れはマイナス）。期間のないプランは null
    public function daysLeft(): ?int
    {
        if ($this->end_date === null) {
            return null;
        }

        return (int) today()->diffInDays($this->end_date, false);
    }

    // もうすぐ期限（残り EXPIRING_SOON_DAYS 日以内）か
    public function isExpiringSoon(): bool
    {
        $daysLeft = $this->daysLeft();

        return $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= self::EXPIRING_SOON_DAYS;
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
        return $query->whereNull('cancelled_at')->where(function ($q) {
            $q->whereDate('end_date', '>=', now())->orWhereNull('end_date');
        });
    }
}
