<?php

namespace Database\Seeders;

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
         // PlanSeederの呼び出し
        $this->call([
            PlanSeeder::class,
            SponsorSeeder::class,
        ]);
        
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
        // 正会員・未成年（プランを持つ）
        $registeredMinors = Member::factory()->count(8)->registered()->create([
            'birth_date'     => fake()->dateTimeBetween('-17 years', '-5 years'),
            'guardian_name'  => fake()->name(),
            'guardian_phone' => fake()->phoneNumber(),
        ]);

        // ビジター（プランなし・正会員化テスト用）
        $visitors = Member::factory()->count(7)->visitor()->create();

        // // 正会員・成人（プランを持つ）
        $registeredAdults = Member::factory()->count(25)->registered()->create();

        // 正会員だけのコレクション（プラン付与に使う）
        $registeredMembers = $registeredAdults->merge($registeredMinors);

        // 全員のコレクション（Waiver・Visit・メモなど、正会員/ビジター問わず使う）
        $allMembers = $registeredMembers->merge($visitors);

        /**
         * ③会員プランの作成（正会員のみ）
         */
        foreach ($registeredMembers as $member) {
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
         * ④Waiver(誓約書)の作成（全員が対象）
         */
        foreach ($allMembers as $member) {
            if ($member->is_minor) {
                // 未成年：保護者署名あり
                Waiver::factory()->signed()->create([
                    'member_id' => $member->id,
                    'is_minor_signed' => true,
                    'guardian_name' => $member->guardian_name,
                ]);
            } else {
                // 成人
                Waiver::factory()->signed()->create([
                    'member_id' => $member->id,
                ]);
            }
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
                    'check_out_at'   => fake()->dateTimeBetween($checkIn, 'now'),
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