<?php

namespace App\Services;

use App\Enums\PlanType;
use App\Models\Member;
use App\Models\MemberPlan;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use DomainException;

class MemberPlanService
{
    /**
     * 会員のプランを乗り換える（今のactiveを解約し、新プランを作成）
     */
    public function change(Member $member, PlanType $planType): MemberPlan
    {
        // 1. 会員区分(category) → 料金階層(price_tier)
        $priceTier = $member->category?->priceTier();

        if ($priceTier === null) {
            throw new DomainException('会員区分が未設定のため、プランを変更できません。');
        }

        // 2. plansマスタから該当プランを特定（price_tier × plan_type）
        $plan = Plan::where('price_tier', $priceTier)
            ->where('plan_type', $planType)
            ->firstOrFail();

        // 3. 期限の計算（単発はnull、期間パスは開始日＋日数）
        $days = $planType->durationDays();
        $startDate = now();
        $endDate = $days ? now()->addDays($days) : null;

        // 4. 「解約して乗り換え」を1つの取引として実行
        return DB::transaction(function () use ($member, $plan, $startDate, $endDate) {
            // 今のactiveなプランをすべて解約
            MemberPlan::query()
                ->where('member_id', $member->id)
                ->active()
                ->update(['cancelled_at' => now()]);

            // 新しいプランを作成して返す
            return MemberPlan::create([
                'member_id' => $member->id,
                'plan_id' => $plan->id,
                'price_paid' => $plan->price,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        });
    }
}