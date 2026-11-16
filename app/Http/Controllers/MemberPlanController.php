<?php

namespace App\Http\Controllers;

use App\Enums\PlanType;
use App\Http\Requests\ChangePlanRequest;
use App\Models\Member;
use App\Services\MemberPlanService;
use DomainException;

class MemberPlanController extends Controller
{
    public function store(ChangePlanRequest $request, Member $member, MemberPlanService $service)
    {
        $planType = $request->enum('plan_type', PlanType::class);

        try {
            $service->change($member, $planType);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'プランを変更しました。');
    }

    // 期間パスの継続（編集画面の「継続」ボタン）
    public function renew(Member $member, MemberPlanService $service)
    {
        try {
            $memberPlan = $service->renew($member);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('members.edit', $member)->with(
            'success',
            $memberPlan->plan->plan_type->label().'を継続しました（'
                .$memberPlan->start_date->format('Y/m/d').' 〜 '.$memberPlan->end_date->format('Y/m/d').'）。'
        );
    }
}
