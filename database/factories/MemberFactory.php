<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    // 会員のダミーデータを作成
    public function definition(): array
    {
        $isMinor = fake()->boolean(20); // 20%の確率で未成年

        return [
            // 業務キー
            'member_code' => 'M-' . fake()->unique()->numerify('######'),
            'barcode' => '20' . $this->faker->unique()->numerify('##########'),

            // 氏名
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'last_name_kana'  => fake()->randomElement([
                'ヤマダ', 'タナカ', 'スズキ', 'サトウ', 'イトウ',
                'ワタナベ', 'ナカムラ', 'コバヤシ', 'カトウ', 'ヨシダ',
            ]),
            'first_name_kana' => fake()->randomElement([
                'タロウ', 'ハナコ', 'イチロウ', 'サクラ', 'ケンジ',
                'アキコ', 'リョウ', 'ユウキ', 'マナミ', 'コウタ',
            ]),

            // 基本情報
            'birth_date' => fake()->dateTimeBetween('-100 years', '-5 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),

            // 連絡先
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'postal_code' => fake()->postcode(),
            'address' => fake()->address(),

            // 任意項目
            'occupation' => fake()->jobTitle(),
            'photo_path' => null,
            'climbing_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'injury_notes' => fake()->boolean(20) ? fake()->sentence() : null,

            // 注意フラグ
            'caution_flag' => false,
            'caution_notes' => null,

            // 未成年
            'is_minor' => $isMinor,
            'guardian_name' => $isMinor ? fake()->name() : null,
            'guardian_phone' => $isMinor ? fake()->phoneNumber() : null,

            // 緊急連絡先
            'emergency_name' => fake()->name(),
            'emergency_relation' => fake()->randomElement(['parent', 'spouse', 'sibling', 'friend']),
            'emergency_phone' => fake()->phoneNumber(),
        ];
    }
}
