<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 
        $notices = Notice::orderBy('sort_order')->paginate(15);
        return view('notices.index', compact('notices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('notices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // お知らせ画面のバリデーション
        $request->validate([
            'title'=>'required|string|max:50',
            'body'=>'required|string|max:250',
            'expires_at'=>'nullable|date|after:today',
        ],[
            'title.required'=>'タイトルは必須です',
            'title.max'=>'タイトルは50文字以内で入力してください',
            'body.required'=>'内容は必須です',
            'expires_at.date'=> '正しい日付形式で入力してください',
        ]);

        // データベースに保存する
        Notice::create([
            'title'=>$request->title,
            'body'=>$request->body,
            'expires_at'=>$request->expires_at,
            'sort_order'=>0,
            'is_active'=>true,
        ]);

        // 登録完了メッセージ
        return redirect()->route('notices.index')->with('success', '登録完了！');
    }

    /**
     * Display the specified resource.
     */
    public function show(Notice $notice)
    {
        //
        return view('notices.edit', compact('notice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notice $notice)
    {
        //
        return view('notices.edit', compact('notice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notice $notice)
    {
        //
        $validated = $request->validate([
            'title'=>'required|string|max:50',
            'body'=>'required|string|max:250',
            'expires_at'=>'nullable|date',
        ],[
            'title.required' => 'タイトルは必須です',
            'body.required'  => '内容は必須です',
        ]);

        $notice->update($validated);
        return redirect()->route('notices.index')->with('success', 'お知らせを更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notice $notice)
    {
        //
        $notice->delete();
        return redirect()->route('notices.index')->with('success', 'お知らせを削除しました');
    }
}
