<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Http\Requests\PlanUpdateRequest;

class PlanController extends Controller
{
    // Plans/edit.bladeへPlansモデルの値を渡す
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get(); // 15行を sort_order 順で取得したものを$plamsへ代入
        // 「plan_type|price_tier」をキーにした早見表に変換
        $plansByKey = $plans->keyBy(
            fn ($plan) => $plan->plan_type->value . '|' . $plan->price_tier->value
            );
        return view('plans.index', compact('plansByKey')); // ['plansByKey' => $plansByKey]
    }

    //
    public function edit(Plan $plan) // Planモデルのインスタンス化:モデルの値が$planに代入される
    {
        return view('plans.edit', compact('plan'));
    }

    //
    public function update(PlanUpdateRequest $request, Plan $plan)
    {
        $plan->update($request->validated());
        return to_route('plans.index')->with('success', '料金を更新しました');
    }
}