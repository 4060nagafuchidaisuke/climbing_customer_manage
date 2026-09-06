<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disclaimer extends Model
{
    // 免責事項
    // fillable：一括代入(mass assignment)で書き込みを許可するカラムのホワイトリスト
    protected $fillable = [
        'content',
    ];
}
