<?php

namespace App\Http\Controllers;

use App\Enums\MemberCategory;
use App\Http\Requests\RegisterMemberRequest;
use App\Models\Member;
use App\Services\MemberRegistrationService;

class MemberRegistrationController extends Controller
{
    // Service を DI で受け取る
    public function __construct(private MemberRegistrationService $service) {}

    // 正会員化フォーム
    public function create(Member $member)
    {
        if ($member->registered_at !== null) {
            return redirect()->route('members.show', $member)->with('info', 'この会員はすでに本登録済みです。');
        }

        return view('members.register', compact('member'));
    }

    public function store(RegisterMemberRequest $request, Member $member)
    {
        // バリデーション済みの category を取り出す
        $category = $request->enum('category', MemberCategory::class);

        // Service を呼んで登録処理
        $this->service->register($member, $category);

        // 完了後、会員詳細などにリダイレクト＋成功メッセージ
        return redirect()->route('members.show', $member)->with('success', '正会員として登録しました。');
    }
}
