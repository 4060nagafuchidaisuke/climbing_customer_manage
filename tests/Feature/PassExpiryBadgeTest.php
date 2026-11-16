<?php

namespace Tests\Feature;

use App\Enums\PlanStatus;
use App\Enums\PlanType;
use App\Models\Member;
use App\Models\MemberPlan;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\Visit;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// ダッシュボードと在店中の画面で、期間パスの期限の状態（期間内／もうすぐ期限／期限切れ）を出す
class PassExpiryBadgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        Carbon::setTestNow('2026-11-16 15:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 今お店にいる会員を作り、プランを付ける（終了日だけ指定）
    private function memberInStoreWith(PlanType $planType, ?string $endDate, string $name = '山田'): Member
    {
        $member = Member::factory()->create(['last_name' => $name]);
        MemberPlan::factory()->create([
            'member_id' => $member->id,
            'plan_id' => Plan::where('plan_type', $planType)->value('id'),
            'start_date' => '2026-10-01',
            'end_date' => $endDate,
            'cancelled_at' => null,
        ]);
        Visit::factory()->create([
            'member_id' => $member->id,
            'check_in_at' => now()->subHour(),
            'check_out_at' => null,
        ]);

        return $member;
    }

    public function test_残り日数と状態は終了日の当日まで使える基準で決まる(): void
    {
        $plan = fn (?string $end) => new MemberPlan(['end_date' => $end]);

        $this->assertSame(30, $plan('2026-12-16')->daysLeft());
        $this->assertSame(0, $plan('2026-11-16')->daysLeft());      // 終了日の当日
        $this->assertSame(-1, $plan('2026-11-15')->daysLeft());
        $this->assertNull($plan(null)->daysLeft());                  // 1日利用など

        $this->assertSame(PlanStatus::ACTIVE, $plan('2026-11-16')->status); // 当日はまだ使える
        $this->assertSame(PlanStatus::EXPIRED, $plan('2026-11-15')->status);

        $this->assertTrue($plan('2026-11-19')->isExpiringSoon());   // あと3日
        $this->assertFalse($plan('2026-11-20')->isExpiringSoon());  // あと4日
        $this->assertFalse($plan('2026-11-15')->isExpiringSoon());  // 期限切れは「もうすぐ」ではない
    }

    public function test_在店中の画面に期間パスの状態を出す(): void
    {
        $this->memberInStoreWith(PlanType::MONTHLY, '2026-12-16', '期間内');
        $this->memberInStoreWith(PlanType::FIFTEEN_DAY, '2026-11-18', 'もうすぐ');
        $this->memberInStoreWith(PlanType::HALF_YEAR, '2026-11-10', '期限切れ');
        $this->memberInStoreWith(PlanType::ONE_DAY, null, '一日');

        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('visits.today'))
            ->assertOk()
            ->assertSee('期間内 12/16まで・あと30日')
            ->assertSee('期間内 11/18まで・あと2日')
            ->assertSee('半年パス')                    // 期限切れでもプラン名は出す
            ->assertSee('期限切れ 11/10まで')
            ->assertSee('1日利用料');
    }

    public function test_ダッシュボードの在店中と入退店ログに期間パスの状態を出す(): void
    {
        $this->memberInStoreWith(PlanType::MONTHLY, '2026-11-16');

        $html = $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('期間内 本日まで')
            ->getContent();

        // 「現在在店中」と「本日の入退店ログ」の両方に出る
        $this->assertSame(2, substr_count($html, '期間内 本日まで'));
    }

    public function test_解約したプランは出さない(): void
    {
        $member = $this->memberInStoreWith(PlanType::MONTHLY, '2026-12-16');
        $member->memberPlans()->update(['cancelled_at' => now()]);

        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('visits.today'))
            ->assertOk()
            ->assertDontSee('期間内');
    }
}
