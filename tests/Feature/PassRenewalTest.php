<?php

namespace Tests\Feature;

use App\Enums\MemberCategory;
use App\Enums\PlanType;
use App\Models\Member;
use App\Models\MemberPlan;
use App\Models\Plan;
use App\Models\Staff;
use App\Services\MemberPlanService;
use App\Services\SalesReportService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// 期間パスの継続・今のパスの開始日／終了日の修正・売上を支払日で数える
class PassRenewalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        Carbon::setTestNow('2026-12-10 15:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 会員区分「一般」の会員に、指定したパスを付ける（11/16 に購入・支払い）
    private function memberWithPass(PlanType $planType, ?string $endDate): Member
    {
        $member = Member::factory()->create(['category' => MemberCategory::GENERAL]);
        MemberPlan::factory()->create([
            'member_id' => $member->id,
            'plan_id' => $this->plan($planType)->id,
            'price_paid' => $this->plan($planType)->price,
            'paid_at' => '2026-11-16 10:00:00',
            'start_date' => '2026-11-16',
            'end_date' => $endDate,
            'cancelled_at' => null,
        ]);

        return $member;
    }

    private function plan(PlanType $planType): Plan
    {
        return Plan::where('plan_type', $planType)->where('price_tier', MemberCategory::GENERAL->priceTier())->firstOrFail();
    }

    // 編集画面から「更新する」で送る内容（会員情報はそのまま）
    private function updateData(Member $member, array $overrides = []): array
    {
        return array_merge([
            'member_code' => $member->member_code,
            'last_name' => $member->last_name,
            'first_name' => $member->first_name ?? '太郎',
            'last_name_kana' => 'ヤマダ',
            'first_name_kana' => 'タロウ',
            'phone' => '09012345678',
            'is_minor' => '0',
            'residence_area' => 'saga_inside',
            'climbing_experience' => 'first_time',
            'category' => 'general',
        ], $overrides);
    }

    public function test_期間内に継続すると今の終了日の翌日から次の期間になり、今日の売上に入る(): void
    {
        $member = $this->memberWithPass(PlanType::MONTHLY, '2026-12-16');

        $this->actingAs(Staff::factory()->create())
            ->post(route('members.plans.renew', $member))
            ->assertRedirect(route('members.edit', $member))
            ->assertSessionHas('success');

        $plans = $member->memberPlans()->orderBy('id')->get();
        $this->assertCount(2, $plans);
        $this->assertNull($plans[0]->fresh()->cancelled_at); // 今のパスは終了日まで使える

        $renewed = $plans[1];
        $this->assertSame('2026-12-17', $renewed->start_date->format('Y-m-d'));
        $this->assertSame('2027-01-16', $renewed->end_date->format('Y-m-d'));
        $this->assertSame($this->plan(PlanType::MONTHLY)->price, $renewed->price_paid);

        // 開始日（12/17）ではなく、支払った今日（12/10）の売上に入る
        $today = app(SalesReportService::class)->summary('2026-12-10', '2026-12-10');
        $this->assertSame((int) $renewed->price_paid, (int) $today->total);
    }

    public function test_期限切れのパスを継続すると今日から次の期間になる(): void
    {
        $member = $this->memberWithPass(PlanType::FIFTEEN_DAY, '2026-12-01');

        $renewed = app(MemberPlanService::class)->renew($member);

        $this->assertSame('2026-12-10', $renewed->start_date->format('Y-m-d'));
        $this->assertSame('2026-12-25', $renewed->end_date->format('Y-m-d'));
    }

    public function test_期間のないプランは継続できない(): void
    {
        $member = $this->memberWithPass(PlanType::ONE_DAY, null);

        $this->actingAs(Staff::factory()->create())
            ->from(route('members.edit', $member))
            ->post(route('members.plans.renew', $member))
            ->assertSessionHas('error');

        $this->assertSame(1, $member->memberPlans()->count());
    }

    public function test_編集画面に継続ボタンと継続後の期間・料金が出る(): void
    {
        $member = $this->memberWithPass(PlanType::MONTHLY, '2026-12-16');
        $price = number_format($this->plan(PlanType::MONTHLY)->price);

        $this->actingAs(Staff::factory()->create())
            ->get(route('members.edit', $member))
            ->assertOk()
            ->assertSee("月パスを継続する（¥{$price}）")
            ->assertSee('継続後の期間：2026/12/17 〜 2027/01/16')
            ->assertSee('form="renew-form"', false)
            ->assertSee(':disabled="changed"', false)
            ->assertSee('value="2026-11-16"', false); // 開始日の入力欄
    }

    public function test_1日利用の人の編集画面には継続ボタンも期間の入力欄も出ない(): void
    {
        $member = $this->memberWithPass(PlanType::ONE_DAY, null);

        $this->actingAs(Staff::factory()->create())
            ->get(route('members.edit', $member))
            ->assertOk()
            ->assertDontSee('を継続する')
            ->assertDontSee('name="plan_start_date"', false);
    }

    public function test_スタッフは今のパスの開始日と終了日を直せ、売上の日は変わらない(): void
    {
        $member = $this->memberWithPass(PlanType::MONTHLY, '2026-12-16');

        $this->actingAs(Staff::factory()->create())
            ->put(route('members.update', $member), $this->updateData($member, [
                'plan_start_date' => '2026-11-20',
                'plan_end_date' => '2026-12-25', // 自動計算（12/20）から延ばした
            ]))
            ->assertSessionHasNoErrors();

        $pass = $member->latestPlan()->first();
        $this->assertSame('2026-11-20', $pass->start_date->format('Y-m-d'));
        $this->assertSame('2026-12-25', $pass->end_date->format('Y-m-d'));

        // 売上は支払日（11/16）のまま
        $this->assertSame(1, app(SalesReportService::class)->summary('2026-11-16', '2026-11-16')->count);
        $this->assertSame(0, app(SalesReportService::class)->summary('2026-11-20', '2026-11-20')->count);
    }

    public function test_終了日が開始日より前ならエラー(): void
    {
        $member = $this->memberWithPass(PlanType::MONTHLY, '2026-12-16');

        $this->actingAs(Staff::factory()->create())
            ->put(route('members.update', $member), $this->updateData($member, [
                'plan_start_date' => '2026-11-20',
                'plan_end_date' => '2026-11-19',
            ]))
            ->assertSessionHasErrors('plan_end_date');
    }

    public function test_別のプランに変えるときは今のパスの日付は直さない(): void
    {
        $member = $this->memberWithPass(PlanType::MONTHLY, '2026-12-16');
        $old = $member->latestPlan()->first();

        $this->actingAs(Staff::factory()->create())
            ->put(route('members.update', $member), $this->updateData($member, [
                'registration_fee_paid' => '1',
                'plan_type' => 'half_year',
                'plan_start_date' => '2026-11-01',
                'plan_end_date' => '2026-11-30',
            ]))
            ->assertSessionHasNoErrors();

        $old->refresh();
        $this->assertNotNull($old->cancelled_at); // 乗り換えで解約
        $this->assertSame('2026-11-16', $old->start_date->format('Y-m-d'));
        $this->assertSame(PlanType::HALF_YEAR, $member->latestPlan()->first()->plan->plan_type);
    }

    public function test_売上画面と明細は支払日で数える(): void
    {
        $member = $this->memberWithPass(PlanType::MONTHLY, '2026-12-16');
        app(MemberPlanService::class)->renew($member); // 12/10 に支払い、12/17 から

        $admin = Staff::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('sales.index', ['from' => '2026-11-01', 'to' => '2026-12-31']))
            ->assertOk()
            ->assertSee('2026/11/16')
            ->assertSee('2026/12/10')
            ->assertDontSee('2026/12/17');

        $this->get(route('sales.details', ['date' => '2026-12-10']))
            ->assertOk()
            ->assertSee('2026/12/10 15:00')               // 支払日
            ->assertSee('2026/12/17 〜 2027/01/16');      // 利用期間
    }
}
