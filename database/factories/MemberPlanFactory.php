<?php

namespace Database\Factories;

use App\Enums\MemberCategory;
use App\Enums\PlanType;
use App\Enums\PlanStatus;
use App\Models\Member;
use App\Models\MemberPlan;
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
        // 契約開始日から現在までの期間
        $startDate = fake()->dateTimeBetween('-2 years', 'now');

        return [
            // Memberを自動で紐づけ
            'member_id' => Member::factory(),

            // 契約のタイプ（年会員、半年、月、）
            'plan_type'=> fake()->randomElement(PlanType::cases()),

            // 会員区分（一般、学生）
            'category' => fake()->randomElement(MemberCategory::cases()),

            // 契約期間
            'start_date'=> $startDate,
            'end_date'=> fake()->dateTimeBetween($startDate, '+2 years'),

            // 初回登録料支払い状態
            'is_first_registration'=> fake()->boolean(90),

            // 契約金額
            'price'=> fake()->numberBetween(3000, 15000),
            'status'=> fake()->randomElement(PlanStatus::cases())
        ];
    }

    // アクティブなプランだけ作りたいとき
    public function active(): static
    {
        return $this->state(['status' => PlanStatus::ACTIVE]);
    }

    // 期限切れプランを作りたいとき
    public function expired(): static
    {
        return $this->state([
            'status'   => PlanStatus::EXPIRED,
            'end_date' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }
}
