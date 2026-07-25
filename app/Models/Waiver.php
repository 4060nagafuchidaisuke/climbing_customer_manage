<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waiver extends Model
{
    use HasFactory;

    // fillable：外から書き換えられたら困るものを保護
    protected $fillable = [
        'member_id',
        'version',
        'signed_at',
        'signature_path',
        'is_minor_signed',
        'guardian_name',
    ];

    // 型指定
    protected $casts = [
        'signed_at' => 'datetime',
        'is_minor_signed' => 'boolean',
    ];

    /**
     * リレーション設定
     */
    // 会員情報と連結
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Accessor(良く使う計算値)
     */
    // 署名済みかどうか
    protected function isSigned(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->signed_at),
        );
    }

    // 保護者同意済み
    protected function hasGuardianConsent(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_minor_signed
                && ! empty($this->guardian_name),
        );
    }

    /**
     * Scope(検索、CRUD処理)
     */
    // 同意書の作成の有無
    public function scopeSigned(Builder $query): Builder
    {
        return $query->whereNotNull('signed_at');
    }
}
