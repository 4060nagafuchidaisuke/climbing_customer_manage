<?php

namespace App\Models;

use App\Enums\SponsorMediaType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    // 受付画面で再生する広告の最大数（登録が古い順に、この数まで流す）
    public const CHECKIN_LIMIT = 5;

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
    // 受付画面に出してよい広告だけに絞る（公開中 かつ 掲載期間内）
    // 開始日・終了日が空なら「制限なし」として扱う。当日は期間内に含める
    public function scopeVisible(Builder $query): Builder
    {
        $today = today();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            });
    }

    // 並び順：登録が古い順（新しい広告は最後に加わる）
    // sort_order カラムは残しているが、画面からは入力できないので並び順には使わない
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('id');
    }
}
