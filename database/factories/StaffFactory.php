<?php

namespace Database\Factories;

use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // 1件分のデータをどう作るか
    public function definition(): array
    {
        return [
            // Staffのダミーデータを作成
            'name' => fake()->name,
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'), // 全員同じPWでOK（開発用）
            'role' => fake()->randomElement(StaffRole::cases()),
            'is_active' => true,
        ];
    }

    /**
     * ── States（特定の状態を作りやすくする）──────────────
     */
    // 管理者だけを上書きする
    public function admin(): static
    {
        return $this->state(['role' => StaffRole::ADMIN]);
    }

    // 現在働いているかを上書きする
    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
