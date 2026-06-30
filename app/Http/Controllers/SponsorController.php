<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $sponsors = Sponsor::orderBy('sort_order')->get();
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
    public function store(Request $request)
    {
        //
        $request->validate([
            'title'=>'required|string|max:100',
            'image'=>'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'link_url'=>'nullable|url',
            'display_seconds'=>'required|integer|min:1|max:60',
            'sort_order'=>'required|integer|min:0',
        ]);

        // 画像を storage/app/public/sponsors/ に保存
        $path = $request->file('image')->store('sponsors', 'public');

        Sponsor::create([
            'title'=>$request->title,
            'image_path'=>$path,
            'link_url'=>$request->link_url,
            'display_seconds'=>$request->display_seconds,
            'sort_order'=>$request->sort_order,
        ]);

        return redirect()->route('sponsors.index')->with('success', '広告を登録しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sponsor $sponsor)
    {
        //
        Storage::disk('public')->delete($sponsor->image_path);
        $sponsor->delete();
        return redirect()->route('sponsors.index')->with('success', '広告を削除しました');
    }
}
