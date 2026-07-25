<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Staff;
use App\Models\StaffNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffNote>
 */
class StaffNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 外部キーと接続
            'member_id' => Member::factory(),
            'note' => fake()->realText(80),
            'is_alert' => fake()->boolean(5),
            'created_by' => Staff::factory(),
        ];
    }

    public function alert(): static
    {
        return $this->state([
            'is_alert' => true,
        ]);
    }
}
