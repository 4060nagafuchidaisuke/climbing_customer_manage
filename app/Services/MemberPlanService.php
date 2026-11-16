<?php

namespace App\Services;

use App\Enums\PlanType;
use App\Models\Member;
use App\Models\MemberPlan;
use App\Models\Plan;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MemberPlanService
{
    /**
     * 期間パス（月パス・15日パス・半年パス）を同じパスで継続する（料金を受け取った前提）
     * まだ期間内なら今の終了日の翌日から、期限切れなら今日から、次の期間を作る
     * 今のパスは解約しない（終了日まで使える）。別のパスに変えるときは change() を使う
     */
    public function renew(Member $member): MemberPlan
    {
        $renewal = $this->renewalPreview($member);

        if ($renewal === null) {
            throw new DomainException('継続できるのは、月パス・15日パス・半年パスを使っていて、会員区分が設定されている会員だけです。');
        }

        return MemberPlan::create([
            'member_id' => $member->id,
            'plan_id' => $renewal['plan']->id,
            'price_paid' => $renewal['plan']->price,
            'paid_at' => now(), // 売上は継続した（支払った）日に入る
            'start_date' => $renewal['start_date'],
            'end_date' => $renewal['end_date'],
        ]);
    }

    /**
     * 継続したらどうなるか（プラン・期間）。継続できないときは null
     * 編集画面で「継続」ボタンの横に期間と料金を出すのにも使う
     *
     * @return array{plan: Plan, start_date: Carbon, end_date: Carbon}|null
     */
    public function renewalPreview(Member $member): ?array
    {
        $current = $member->latestPlan()->with('plan')->first();
        $planType = $current?->plan->plan_type;

        if ($planType === null || ! $planType->isPeriodPass() || $member->category === null) {
            return null;
        }

        // まだ期間内なら今の終了日の翌日から、期限切れなら今日から
        $startDate = $current->end_date->gte(today())
            ? $current->end_date->copy()->addDay()
            : today();

        return [
            // 料金は今の会員区分で決める（区分が変わっていれば新しい料金になる）
            'plan' => $this->findPlan($member, $planType),
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addDays($planType->durationDays()),
        ];
    }

    /**
     * 今のプラン（最新の期間パス）の開始日・終了日をスタッフが直す
     * 直せるのは今のプランだけ。期間のないプラン（1日利用など）は何もしない
     * 売上は支払日で数えているので、日付を直しても売上の日は変わらない
     */
    public function updateCurrentDates(Member $member, string $startDate, string $endDate): void
    {
        $current = $member->latestPlan()->with('plan')->first();

        if ($current === null || ! $current->plan->plan_type->isPeriodPass()) {
            return;
        }

        $current->update([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    // 会員区分（料金の区分）と利用タイプから、料金表のプランを探す
    private function findPlan(Member $member, PlanType $planType): Plan
    {
        $priceTier = $member->category?->priceTier();

        if ($priceTier === null) {
            throw new DomainException('会員区分が未設定のため、プランを変更できません。');
        }

        return Plan::where('price_tier', $priceTier)
            ->where('plan_type', $planType)
            ->firstOrFail();
    }

    /**
     * 会員のプランを乗り換える（今のactiveを解約し、新プランを作成）
     */
    public function change(Member $member, PlanType $planType): MemberPlan
    {
        // 1〜2. 会員区分(category) → 料金階層(price_tier) → plansマスタから該当プランを特定
        $plan = $this->findPlan($member, $planType);

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
