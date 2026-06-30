<?php

namespace Database\Factories;

use App\Models\Visit;
use App\Models\Member;
use App\Models\Staff;
use App\Enums\VisitType;
use App\Enums\VisitSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 営業時間内のチェックイン
        $checkIn  = fake()->dateTimeBetween('-6 months', '-10 minutes');

        // 「80%の確率でチェックアウト済み」
        $checkOut = fake()->boolean(80)? fake()->dateTimeBetween($checkIn, 'now'): null;

        return [
            // Memberを自動で紐づけ (外部キー)
            'member_id' => Member::factory(),

            // 入退店の記録
            'check_in_at' => $checkIn,
            'check_out_at' => $checkOut,

            // 利用種別（normal / trial / lesson）
            'visit_type'=> fake()->randomElement(VisitType::cases()),

            // 受付方法（barcode / manual）
            'visit_source' => fake()->randomElement(VisitSource::cases()),

            // 入退店処理スタッフID
            'checked_in_by' => Staff::factory(),
            'checked_out_by' => $checkOut ? Staff::factory() : null, // チェックアウト時刻があるときだけスタッフも入れる

            // 受付メモ
            'staff_note' => fake()->realText(80)
        ];
    }

    // 現在滞在中（チェックアウトなし）
    public function staying(): static
    {
        return $this->state([
            'check_in_at'  => fake()->dateTimeBetween('-3 hours', '-10 minutes'),
            'check_out_at' => null,
            'checked_out_by' => null,
        ]);
    }

    // 退店管理
    public function checkedOut(): static
    {
        return $this->state(fn () => [
            'check_in_at' => now()->subHours(3),
            'check_out_at' => now()->subHour(),
            'checked_in_by' => Staff::factory(),
            'checked_out_by' => Staff::factory(),
        ]);
    }
    // トライアルかどうか
    public function trial(): static
    {
        return $this->state(function () {
            return [
                'visit_type' => VisitType::TRIAL,
            ];
        });
    }

    // レッスンかどうか
    public function lesson(): static
    {
        return $this->state(function () {
            return [
                'visit_type' => VisitType::LESSON,
            ];
        });
    }

    // バーコードで受付したかどうか
    public function barcode(): static
    {
        return $this->state(function (){
            return [
                'visit_source' => VisitSource::BARCODE,
            ];
        });
    }

}

