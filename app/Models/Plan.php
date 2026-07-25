<?php

namespace App\Models;

use App\Enums\PlanType;
use App\Enums\PriceTier;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price_tier',
        'plan_type',
        'price',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_tier' => PriceTier::class,
            'plan_type' => PlanType::class,
            'is_active' => 'boolean',
        ];
    }
}
