<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDisclaimerRequest;
use App\Models\Disclaimer;

class DisclaimerController extends Controller
{
    // 免責事項
    // 閲覧:ハンバーガーメニューから入り、閲覧のみの画面
    public function show()
    {
        $disclaimer = Disclaimer::first();

        return view('disclaimer.show', compact('disclaimer'));
    }

    // 編集
    // 閲覧画面の下部に「編集」ボタンを配置し、押したら編集画面へ
    public function edit()
    {
        // 一行目の「content」の内容をdisclaimerプロパティに入れる
        $disclaimer = Disclaimer::first();

        //　disclaimerクラス=免責事項の内容を表示させる
        return view('disclaimer.edit', compact('disclaimer'));
    }

    // 更新
    public function update(UpdateDisclaimerRequest $request)
    {
        // 一行目の「content」の内容をdisclaimerプロパティに入れる
        $disclaimer = Disclaimer::first();

        // テキストをバリテーションし、編集した内容を更新
        $disclaimer->update($request->validated());

        return redirect()->route('disclaimer.show')->with('success', '免責事項の内容を更新しました');
    }
}
