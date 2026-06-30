<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Models\Member;
use App\Http\Requests\UpdateMemberRequest;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Member::query()
            ->with(['activePlan'])
            ->orderBy('created_at', 'desc');

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
        return view('members.create'); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request)
    {
        // member_code と barcode を自動生成
        $code = 'M' . str_pad(Member::max('id') + 1, 6, '0', STR_PAD_LEFT);

        // DBに保存
        $member = Member::create(
            array_merge(
                $request->validated(),
                ['member_code' => $code, 'barcode' => $code]
            )
        );

        // 詳細ページへリダイレクト
        return redirect()->route('members.show', $member)->with('success', '新規作成しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        // 会員詳細ページの表示
        $member->load([
            // プランを「開始日が新しい順」にならべる（orderBy）
            'memberPlans' => fn($q) => $q->orderBy('start_date', 'desc'),

            // 来店履歴を「入店日時が新しい順」にならべて、10件表示
            'visits'      => fn($q) => $q->orderBy('check_in_at', 'desc')->limit(10),

            // スタッフメモを「作成日が新しい順」で取得
            'staffNotes'  => fn($q) => $q->orderBy('created_at', 'desc'),

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
    public function update(UpdateMemberRequest $request, Member $member)
    {
        //会員情報の編集
        $member->update($request->validated());

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
