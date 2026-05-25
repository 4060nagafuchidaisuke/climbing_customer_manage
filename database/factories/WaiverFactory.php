<?php

namespace Database\Factories;

use App\Models\Waiver;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Waiver>
 */
class WaiverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $signedAt = fake()->boolean(90) ? fake()->dateTimeBetween('-10 years', 'now'): null;
        
        return [
            // Memberを自動で紐づけ (外部キー)
            'member_id' => Member::factory(),
            'version' => 'v1.0',
            'signed_at' => $signedAt,
            'signature_path'  => $signedAt ? 'signatures/dummy_' . fake()->uuid() . '.png': null,
            
            // Seederから渡されなかったらdefault値とする
            'is_minor_signed' => false,
            'guardian_name'=> null,
        ];
    }

    // 署名済みのWaiverだけ作りたいとき
    public function signed(): static
    {
        return $this->state([
            'signed_at'      => fake()->dateTimeBetween('-1 year', 'now'),
            'signature_path' => 'signatures/dummy_' . fake()->uuid() . '.png',
        ]);
    }

    // 未署名のWaiverだけ作りたいとき
    public function unsigned(): static
    {
        return $this->state([
            'signed_at'      => null,
            'signature_path' => null,
        ]);
    }
}
