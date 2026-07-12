<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MemberPlan;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberPlan>
 */
class MemberPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // プランを選択
        $plan = Plan::inRandomOrder()->first();

        // 契約開始日から現在までの期間
        $startDate = fake()->dateTimeBetween('-2 years', 'now');

        // end_date を計算（単発=null / 期間パス=start+日数）
        $days = $plan->plan_type->durationDays();
        $endDate = $days ? (clone $startDate)->modify("+{$days} days") : null;

        return [
            // Memberを自動で紐づけ
            'member_id' => Member::factory(),
            'plan_id' => $plan->id,
            'price_paid'=> $plan->price,
            'start_date' => $startDate,
            'end_date' => $endDate, 

            'cancelled_at' =>null,
        ];
    }

    // アクティブなプランだけ作りたいとき
    public function active(): static
    {
        return $this->state(function () {
            return [
                'cancelled_at' => null,
                'end_date' => fake()->dateTimeBetween('+1 month', '+1 year'),
            ];
        });
    }

    // 期限切れプランを作りたいとき
    public function expired(): static
    {
        return $this->state(function () {
            $start = fake()->dateTimeBetween('-2 years', '-1 month');
            return [
                'start_date' => $start,
                'end_date'   => fake()->dateTimeBetween($start, '-1 day'), // 過去で終了
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->state(['cancelled_at' => now()]);
    }
}
