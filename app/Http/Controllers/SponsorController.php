<?php

namespace App\Http\Controllers;

use App\Enums\SponsorMediaType;
use App\Http\Requests\StoreSponsorRequest;
use App\Models\Sponsor;
use Illuminate\Support\Facades\Storage;

class SponsorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $sponsors = Sponsor::ordered()->get();

        return view('sponsors.index', compact('sponsors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('sponsors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    // 登録作業を行う
    public function store(StoreSponsorRequest $request)
    {
        // 画像か動画かを判定する
        $file = $request->file('media');
        $mime = $file->getMimeType(); // 'image/png' や 'video/mp4' のような文字列を返す

        // mediaTypeに判定結果を入れる
        if (str_starts_with($mime, 'video/')) {
            $mediaType = SponsorMediaType::VIDEO;
        } else {
            $mediaType = SponsorMediaType::IMAGE;
        }

        // 画像を storage/app/public/sponsors/ に保存
        $path = $file->store('sponsors', 'public');

        Sponsor::create([
            'title' => $request->title,
            'display_seconds' => $request->display_seconds,
            'sort_order' => 0, // 画面からは入力しない（並び順は登録順）。カラムが NOT NULL なので固定値を入れる
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'media_path' => $path,
            'media_type' => $mediaType,
            'is_active' => $request->boolean('is_active'),

        ]);

        // JS（sponsor-form.js）から送られたときは、移動先の URL を返して JS に画面遷移させる
        // メッセージは flash に入れておき、遷移先の一覧画面で1回だけ表示する
        if ($request->expectsJson()) {
            session()->flash('success', '広告を登録しました');

            return response()->json(['redirect' => route('sponsors.index')]);
        }

        return redirect()->route('sponsors.index')->with('success', '広告を登録しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sponsor $sponsor)
    {
        //
        Storage::disk('public')->delete($sponsor->media_path);
        $sponsor->delete();

        return redirect()->route('sponsors.index')->with('success', '広告を削除しました');
    }
}
