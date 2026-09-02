<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    // お知らせ内容の管理
    // fillable：一括代入(mass assignment)で書き込みを許可するカラムのホワイトリスト
    protected $fillable = [
        'title',
        'body',
        'is_active',
        'sort_order',
        'expires_at',
    ];

    // 型指定
    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'date',
    ];

    /**
     * Scope(CRUD処理)
     */
    // 有効なお知らせを表示
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', today());
        })
            ->orderBy('sort_order');
    }
}
