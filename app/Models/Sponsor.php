<?php

namespace App\Models;

use App\Enums\SponsorMediaType;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    // スポンサー表示
    // fillable：一括代入(mass assignment)で書き込みを許可するカラムのホワイトリスト
    protected $fillable = [
        'title',
        'media_path',
        'media_type',
        'is_active',
        'display_seconds',
        'sort_order',
        'start_date',
        'end_date',
    ];

    // 型指定
    protected $casts = [
        'is_active' => 'boolean',
        'media_type' => SponsorMediaType::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Accessor(良く使う計算値)
     */
    // 画像の公開URLを返すアクセサ
    public function getMediaUrlAttribute(): string
    {
        // 呼び出す場合はアクセサ名の「MediaUrl」から”media_url”
        return asset('storage/'.$this->media_path);
    }

    /**
     * Scope(CRUD処理)
     */
}
