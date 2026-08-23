<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuestRegistrationRequest;
use App\Services\MemberRegistrationService;
use Illuminate\Http\Request;

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

    public function store(Request $request, MemberRegistrationService $registration)
    {
        // session から回収（無ければセッション切れ）
        $data = session('guest_member');
        if (! $data) {
            return redirect()->route('register.guest.expired');
        }

        // 会員作成（会員＋誓約書。plan_type が無いのでプランはスキップ）
        $member = $registration->create($data);

        // session クリア
        session()->forget(['guest_member', 'guest_signed_url']);

        // 会員番号を flash（完了画面で表示）
        session()->flash('registered_code', $member->member_code);

        // お客さん用の完了画面へ
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
