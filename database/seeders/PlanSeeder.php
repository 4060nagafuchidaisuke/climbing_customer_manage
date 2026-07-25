<?php

namespace Database\Seeders;

use App\Enums\PlanType;
use App\Enums\PriceTier;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 料金表
        $matrix = [
            //                              一般   大学生   ｼﾆｱ  キッズ
            PlanType::ONE_DAY->value => [2100,  1900,  1900,   1600],
            PlanType::MINUTES_120->value => [1600,  1400,  1400,  1200],
            PlanType::MONTHLY->value => [12000, 10000, 10000, 9000],
            PlanType::FIFTEEN_DAY->value => [6000,  5000,  5000,  4500],
            PlanType::HALF_YEAR->value => [58000, 49000, 49000, 43000],
        ];

        // 列の並び。上の配列のインデックス0/1/2 と対応させる。
        $tiers = [
            PriceTier::GENERAL, // [0]
            PriceTier::STUDENT, // [1]
            PriceTier::SENIOR, // [2]
            PriceTier::KIDS, // [3]
        ];

        $sort = 0;
        foreach ($matrix as $planTypeValue => $prices) {
            $planType = PlanType::from($planTypeValue);

            foreach ($tiers as $i => $tier) {
                Plan::create([
                    'name' => $tier->label().' '.$planType->label(),
                    'price_tier' => $tier->value,
                    'plan_type' => $planType->value,
                    'price' => $prices[$i],
                    'is_active' => true,
                    'sort_order' => $sort++,
                ]);
            }
        }
    }
}
