<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuestRegistrationRequest;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestRegistrationController extends Controller
{
    // お客さん向け会員登録フォームを表示
    public function create()
    {
        $data = session('guest_member', []);   // ← キー名はconfirm側と合わせる

        return view('guest.create', compact('data'));
    }

    // 入力確認画面を表示
    public function confirm(GuestRegistrationRequest $request)
    {
        // バリデーション済みデータ（agreement含む）を取得
        $validated = $request->validated();

        // セッションに一時保管
        session()->put('guest_member', $validated);

        // 「修正で戻る」用に、元の署名付きURLも保管 ★2
        session()->put('guest_signed_url', $request->input('signed_url'));

        // 確認画面へ
        return view('guest.confirm', [
            'data' => $validated,
        ]);
    }

    public function store(Request $request)
    {
        // session から回収（無ければセッション切れ）
        $data = session('guest_member');
        if (! $data) {
            return redirect()->route('register.guest.expired');
        }

        // agreement を除外（DBに保存しない）
        unset($data['agreement']);

        // ★ $member も返すように変更
        [$member, $code] = DB::transaction(function () use ($data) {
            $data['member_code'] = Member::generateMemberCode();

            $member = Member::create($data);

            $member->waivers()->create([
                'version' => 'v1',
                'signed_at' => now(),
                'is_minor_signed' => ! empty($data['is_minor']),
                'guardian_name' => $data['guardian_name'] ?? null,
            ]);

            return [$member, $data['member_code']];   // ← 配列で2つ返す
        });

        // session クリア（両フロー共通）
        session()->forget(['guest_member', 'guest_signed_url']);

        // ★ 店員（ログイン中）が登録 → 会員詳細へ
        if (auth()->check()) {
            return redirect()->route('members.show', $member)
                ->with('success', '新規会員の'.$member->full_name.'さんが仲間になりました');
        }

        // お客さん自己登録（未ログイン）→ 完了画面へ
        session()->flash('registered_code', $code);

        return redirect()->route('register.guest.complete');
    }

    // セッション切れ案内を表示
    public function expired()
    {
        return view('guest.expired');
    }

    // 完了画面を表示
    public function complete()
    {
        // flash した会員番号を受け取る
        $code = session('registered_code');

        // ← 受け取った会員番号を渡す
        return view('guest.complete', [
            'code' => $code,
        ]);
    }
}
