<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveSponsorRequest;
use App\Http\Requests\StoreSponsorRequest;
use App\Http\Requests\UpdateSponsorRequest;
use App\Models\Sponsor;
use App\Services\SponsorService;

class SponsorController extends Controller
{
    // Laravel の DI コンテナが自動でインジェクト
    public function __construct(private SponsorService $sponsorService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sponsors = Sponsor::ordered()->get();

        // 受付画面で今再生されている広告の id（一覧の「再生中」表示に使う）
        $playingIds = Sponsor::forCheckin()->pluck('id');

        return view('sponsors.index', compact('sponsors', 'playingIds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sponsors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    // 登録作業を行う
    public function store(StoreSponsorRequest $request)
    {
        $this->sponsorService->create($this->formData($request), $request->file('media'));

        return $this->backToIndex($request, '広告を登録しました');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sponsor $sponsor)
    {
        return view('sponsors.edit', compact('sponsor'));
    }

    /**
     * Update the specified resource in storage.
     */
    // ファイルを選んでいなければ、今のファイルのまま更新する
    public function update(UpdateSponsorRequest $request, Sponsor $sponsor)
    {
        $this->sponsorService->update($sponsor, $this->formData($request), $request->file('media'));

        return $this->backToIndex($request, '広告を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sponsor $sponsor)
    {
        $this->sponsorService->delete($sponsor);

        return redirect()->route('sponsors.index')->with('success', '広告を削除しました');
    }

    // 一覧画面の ↑↓ ボタン：1つ上（下）の広告と並び順を入れ替える
    public function move(MoveSponsorRequest $request, Sponsor $sponsor)
    {
        $this->sponsorService->move($sponsor, $request->validated('direction'));

        return redirect()->route('sponsors.index');
    }

    // 登録・更新で共通の入力値（ファイル以外）
    private function formData(StoreSponsorRequest $request): array
    {
        return [
            'title' => $request->title,
            'display_seconds' => $request->display_seconds,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active'), // チェックなし（未送信）は false
        ];
    }

    // 一覧画面へ戻る
    // JS（sponsor-form.js）から送られたときは、移動先の URL を返して JS に画面遷移させる
    // メッセージは flash に入れておき、遷移先の一覧画面で1回だけ表示する
    private function backToIndex(StoreSponsorRequest $request, string $message)
    {
        if ($request->expectsJson()) {
            session()->flash('success', $message);

            return response()->json(['redirect' => route('sponsors.index')]);
        }

        return redirect()->route('sponsors.index')->with('success', $message);
    }
}
