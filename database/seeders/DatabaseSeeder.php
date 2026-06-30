<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\Member;
use App\Models\MemberPlan;
use App\Models\Staff;
use App\Models\StaffNote;
use App\Models\Visit;
use App\Models\Waiver;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * ⓵スタッフの作成
         */
        // 管理者の作成
        $adminStaff = Staff::factory()->admin()->create([
            'name'=>'管理者 太郎',
            'email'=>'admin@gym.test',
            'staff_code'=>'s0001',
        ]);

        // 一般スタッフの作成
        $staffList = Staff::factory()->count(2)->create();

        // admin を含めた全スタッフをコレクションにまとめる
        // Visit・StaffNote の checked_in_by / created_by に使い回す
        $allStaff = $staffList->prepend($adminStaff);

        /**
         * ⓶会員の作成
         */
        // 成人会員
        $adultMembers = Member::factory()->count(30)->create();

        // 未成年会員
        // guardian_name / guardian_phone はここで確定値を渡す
        $minorMembers = Member::factory()->count(10)->create([
            'birth_date' => fake()->dateTimeBetween('-17 years', '-5 years'),
            'guardian_name' => fake()->name(),
            'guardian_phone' => fake()->phoneNumber(),
        ]);

        $allMembers = $adultMembers->merge($minorMembers);

        /**
         * ⓷会員プランの作成
        */
        // MemberPlan（会員プラン）
        foreach ($allMembers as $member) {
            // 全員に有効プランを1件
            MemberPlan::factory()->active()->create([
                'member_id' => $member->id,
            ]);

            // 30% の確率で過去プランも追加（履歴として）
            if (fake()->boolean(30)) {
                MemberPlan::factory()->expired()->create([
                    'member_id' => $member->id,
                ]);
            }
        }

        /**
         * ⓸Waiver(誓約書)の有無
         */
        // 成人
        foreach ($adultMembers as $member) {
            Waiver::factory()->signed()->create([
                'member_id' => $member->id,
            ]);
        }

        // 未成年
        foreach ($minorMembers as $member) {
            Waiver::factory()->signed()->create([
                'member_id'       => $member->id,
                'is_minor_signed' => true,
                'guardian_name'   => $member->guardian_name,
            ]);
        }

        /**
         * ⓹入退店記録
        */
        foreach ($allMembers as $member) {
            $visitCount = fake()->numberBetween(0, 5);

            // 過去の入退店履歴（checked_out_at あり）
            for ($i = 0; $i < $visitCount; $i++) {
                $checkIn = fake()->dateTimeBetween('-6 months', '-2 hours');
                Visit::factory()->create([
                    'member_id'       => $member->id,
                    'checked_in_by'   => $allStaff->random()->id,
                    'checked_out_by'  => $allStaff->random()->id,
                    'check_in_at'    => $checkIn,
                    'check_out_at'   => fake()->dateTimeBetween($checkIn, '-10 minutes'),
                ]);
            }
        }

        // 現在在店中の会員を 5名 作る（ダッシュボード確認用）
        $allMembers->random(5)->each(function ($member) use ($allStaff) {
            Visit::factory()->staying()->create([
                'member_id'      => $member->id,
                'checked_in_by'  => $allStaff->random()->id,
                'checked_out_by' => null,
            ]);
        });

        // =========================================================
        // ⑥ StaffNote（スタッフメモ）
        //    - 一部の会員にランダムで 1〜3 件
        //    - is_alert = true のメモも混在させる
        // =========================================================
        /**
         * ⓺スタッフメモ
         */
        // 会員の 40% にメモを付ける
        $allMembers->random((int) ($allMembers->count() * 0.4))->each(function ($member) use ($allStaff) {
            $noteCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $noteCount; $i++) {
                // 10% の確率でアラートメモ
                $factory = fake()->boolean(10)
                    ? StaffNote::factory()->alert()
                    : StaffNote::factory();

                $factory->create([
                    'member_id'  => $member->id,
                    'created_by' => $allStaff->random()->id,
                ]);
            }
        });
    }
}