<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\VisitType;

class Visit extends Model
{
    use HasFactory;

    // 一括代入許可
    protected $fillable = [
        'member_id',
        'check_in_at',
        'check_out_at',
        'visit_type',
        'visit_source',
        'checked_in_by',
        'checked_out_by',
        'staff_note'
    ]; 

    // 型指定
    protected $casts = [
        'check_in_at'=>'datetime',
        'check_out_at'=>'datetime',
        'visit_type'=> VisitType::class,
    ];

    /**
     * リレーション設定
     */
    // Memberと紐づけ
    public function member():BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
    
    // staffテーブルと紐づけ
    // 受付スタッフ(checked_in_by)
    public function checkedInStaff():BelongsTo
    {
        return $this->belongsTo(Staff::class, 'checked_in_by')->withDefault();;
    }
    
    // 退店スタッフ
    public function checkedOutStaff():BelongsTo
    {
        return $this->belongsTo(Staff::class, 'checked_out_by')->withDefault();;
    }

    /**
     * Accessor(良く使う計算値:データベース内で管理しない値)
     */
    // 滞在時間を計算
    public function getStayMinutesAttribute(): ?int
    {
        if (!$this->check_in_at || !$this->check_out_at) {
            return null;
        }

        return $this->check_in_at
                    ->diffInMinutes($this->check_out_at); // 
    }

    // 現在滞在中かどうか
    public function getIsStayingAttribute():bool
    {
        return is_null($this->check_out_at);
    }

    /**
     * Scope(検索機能、CRUD処理)
     */
    // 現在滞在中の来店を取得
    public function scopeStaying(Builder $query): Builder
    {
        return $query->whereNull('check_out_at');
    }

    // 今日来店しているかどうか
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('check_in_at', today());
    }
}
