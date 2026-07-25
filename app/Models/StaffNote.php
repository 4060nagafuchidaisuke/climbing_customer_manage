<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffNote extends Model
{
    use HasFactory;

    // fillable：外から書き換えられたら困るものを保護
    protected $fillable = [
        'member_id',
        'note',
        'is_alert',
        'created_by',
    ];

    // 型指定
    protected $casts = [
        'is_alert' => 'boolean',
    ];

    /**
     * リレーション設定
     */
    // 会員
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);

    }

    // 作成スタッフ
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Accessor(良く使う計算値)
     */

    /**
     * Scope(CRUD処理)
     */

    // アラートメモ
    public function scopeAlert(Builder $query): Builder
    {
        return $query->where('is_alert', true);
    }

    /**
     * アラート表示名
     */
    protected function alertLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_alert
                ? '要注意'
                : '通常'
        );
    }
}
