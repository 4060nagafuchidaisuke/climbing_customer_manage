<?php

namespace App\Models;

use App\Enums\SponsorMediaType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    // 受付画面で再生する広告の最大数（並び順の上から、この数まで流す）
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

    // 並び順：sort_order の小さい順。同じ番号なら登録が古い順
    // sort_order は一覧画面の ↑↓ ボタン（SponsorService::move）で入れ替える。新規登録は最後に加わる
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // 受付画面で再生する広告：表示できるもののうち、並び順の上位 CHECKIN_LIMIT 件
    // 受付画面（CheckinController）と一覧の「再生中」表示（SponsorController）で同じ条件を使う
    public function scopeForCheckin(Builder $query): Builder
    {
        return $query->visible()->ordered()->limit(self::CHECKIN_LIMIT);
    }
}
