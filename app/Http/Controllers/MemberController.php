<?php

namespace App\Http\Controllers;

use App\Enums\PlanType;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Disclaimer;
use App\Models\Member;
use App\Services\MemberPlanService;
use App\Services\MemberRegistrationService;
use DomainException;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Member::query()->with(['activePlan.plan', 'latestVisit'])->orderBy('created_at', 'desc');

        // 検索（氏名・会員番号）
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('member_code', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name_kana', 'like', "%{$search}%")
                    ->orWhere('first_name_kana', 'like', "%{$search}%");
            });
        }

        $members = $query->paginate(20)->withQueryString();

        return view('members.index', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 新規フォームデータを表示
        $data = session('member_form', []);
        $disclaimer = Disclaimer::latest()->first();

        return view('members.create', compact('data', 'disclaimer'));
    }

    /**
     * 店員用確認画面
     */
    public function confirm(StoreMemberRequest $request)
    {
        //
        $validated = $request->validated();
        session()->put('member_form', $validated);

        return view('members.confirm', ['data' => $validated]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, MemberRegistrationService $registration)
    {
        $data = session('member_form');
        if (! $data) {
            return redirect()->route('members.create');
        }

        try {
            $member = $registration->create($data);
        } catch (DomainException $e) {
            return redirect()->route('members.create')->with('error', $e->getMessage());
        }

        session()->forget('member_form');

        return redirect()->route('members.show', $member)
            ->with('success', '新規会員の'.$member->full_name.'さんが仲間になりました');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        // 会員詳細ページの表示
        $member->load([
            // プランを「開始日が新しい順」にならべる + plan も一緒に読み込む
            'memberPlans' => fn ($q) => $q->with('plan')->orderBy('start_date', 'desc'),

            'visits' => fn ($q) => $q->orderBy('check_in_at', 'desc')->limit(10),
            'staffNotes' => fn ($q) => $q->orderBy('created_at', 'desc'),
            'waivers',
        ]);

        return view('members.show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        // 編集フォーム
        return view('members.edit', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member, MemberPlanService $service)
    {
        // UpdateMemberRequestのrules()メソッド内の項目だけ$validatedに入れる
        $validated = $request->validated();

        // plan_typeだけ分離する（）
        unset($validated['plan_type']);

        // 会員情報の編集
        $member->update($validated);

        // プラン変更：現在のプランと異なる場合のみ実行
        $planType = $request->enum('plan_type', PlanType::class);
        $currentPlanType = $member->activePlan?->plan->plan_type;

        // プラン変更（選択されていれば既存の仕組みに委譲）
        if ($planType !== null && $planType !== $currentPlanType) {
            try {
                $service->change($member, $planType);
            } catch (DomainException $e) {
                return back()->with('error', $e->getMessage())->withInput();
            }
        }

        return redirect()->route('members.show', $member)->with('success', '会員情報を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        //
    }
}
