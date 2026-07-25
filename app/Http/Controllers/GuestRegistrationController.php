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
        return view('guest.create');
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

        // 表示用の日本語ラベル（※将来Enum化して置き換える）
        $genderLabels = ['male' => '男性', 'female' => '女性', 'other' => 'その他'];
        $levelLabels = ['beginner' => '初心者', 'intermediate' => '中級者', 'advanced' => '上級者'];

        // 確認画面へ
        return view('guest.confirm', [
            'data' => $validated,
            'genderLabels' => $genderLabels,
            'levelLabels' => $levelLabels,
        ]);
    }

    public function store(Request $request)
    {
        // session から回収（無ければセッション切れ→フォームへ）
        $data = session('guest_member');
        if (! $data) {
            return redirect()->route('register.guest.expired');
        }

        // agreement を除外（DBに保存しない）
        unset($data['agreement']);

        // トランザクションで Member と Waiver を両方作る
        $code = DB::transaction(function () use ($data) {

            // member_code 抜きで作る（id 確定）
            $member = Member::create($data);

            // 確定した id を5桁整形
            // $code = 'M' . str_pad(Member::max('id') + 1, 6, '0', STR_PAD_LEFT);
            $code = str_pad($member->id, 5, '0', STR_PAD_LEFT);

            // 書き込んで保存
            $member->member_code = $code;
            $member->barcode = $code;
            $member->save();

            // waiver は今まで通り
            $member->waivers()->create([
                'version' => 'v1',
                'signed_at' => now(),
                'is_minor_signed' => ! empty($data['is_minor']),
                'guardian_name' => $data['guardian_name'] ?? null, ]);

            return $code;
        });

        // session クリア
        session()->forget(['guest_member', 'guest_signed_url']);

        // ★ 会員番号を flash（次の1リクエストだけ生きる）
        session()->flash('registered_code', $code);

        // complete!
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
