<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    // スポンサー表示
    // fillable：外から書き換えられたら困るものを保護
    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'is_active',
        'display_seconds',
        'sort_order',
    ];

    // 型指定
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Accessor(良く使う計算値)
     */
    // 画像の公開URLを返すアクセサ
    public function getImageUrlAttribute(): string
    {
        return asset('storage/'.$this->image_path);
    }

    /**
     * Scope(CRUD処理)
     */
}
