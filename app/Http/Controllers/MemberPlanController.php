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
}
