<?php

namespace Tests\Feature;

use App\Enums\PlanType;
use App\Models\Member;
use App\Models\Staff;
use App\Services\MemberPlanService;
use App\Services\MemberRegistrationService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// 新規登録・編集画面のプラン選択：「会員（登録料支払い済み）／ビジター」で選べる利用タイプを分ける
class MemberPlanVisitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class); // プランの変更で料金表を使う
    }

    private function formData(array $overrides = []): array
    {
        return array_merge([
            'last_name' => '山田',
            'first_name' => '太郎',
            'last_name_kana' => 'ヤマダ',
            'first_name_kana' => 'タロウ',
            'phone' => '09012345678',
            'is_minor' => '0',
            'residence_area' => 'saga_inside',
            'climbing_experience' => 'first_time',
            'agreement' => '1',
        ], $overrides);
    }

    private function admin(): Staff
    {
        return Staff::factory()->admin()->create();
    }

    public function test_会員とビジターで選べる利用タイプが分かれる(): void
    {
        $this->assertSame(
            ['one_day', 'minutes_120', 'monthly', '15_day', 'half_year'],
            array_keys(PlanType::optionsFor(true)),
        );
        $this->assertSame(
            ['visitor_1day' => '1日利用料', 'visitor_minutes_120' => '120分利用料'],
            PlanType::optionsFor(false),
        );
    }

    public function test_登録画面に会員とビジターのラジオと2つのプルダウンが出る(): void
    {
        $this->actingAs($this->admin())
            ->get(route('members.create'))
            ->assertOk()
            ->assertSee('会員（登録料支払い済み）')
            ->assertSee('name="registration_fee_paid"', false)
            ->assertSee('value="monthly"', false)
            ->assertSee('value="visitor_1day"', false)
            ->assertSee('先に「会員」か「ビジター」を選んでください');
    }

    public function test_ビジターで登録すると記録され、確認画面にも出る(): void
    {
        $data = $this->formData([
            'registration_fee_paid' => '0',
            'category' => 'general',
            'plan_type' => 'visitor_1day',
        ]);

        $this->actingAs($this->admin())
            ->post(route('members.confirm'), $data)
            ->assertSessionHasNoErrors()
            ->assertSee('ビジター')
            ->assertSee('1日利用料');

        $this->post(route('members.store'))->assertRedirect();

        $member = Member::sole();
        $this->assertFalse($member->registration_fee_paid);
        $this->assertSame(PlanType::VISITOR_1DAY, $member->activePlan->plan->plan_type);
    }

    public function test_会員が月パスを選んで登録できる(): void
    {
        $this->actingAs($this->admin())
            ->post(route('members.confirm'), $this->formData([
                'registration_fee_paid' => '1',
                'category' => 'general',
                'plan_type' => 'monthly',
            ]))
            ->assertSessionHasNoErrors();

        $this->post(route('members.store'));

        $this->assertTrue(Member::sole()->registration_fee_paid);
    }

    public function test_ビジターは月パスを選べない(): void
    {
        $this->actingAs($this->admin())
            ->post(route('members.confirm'), $this->formData([
                'registration_fee_paid' => '0',
                'category' => 'general',
                'plan_type' => 'monthly',
            ]))
            ->assertSessionHasErrors('plan_type');
    }

    public function test_会員はビジター用の利用タイプを選べない(): void
    {
        $this->actingAs($this->admin())
            ->post(route('members.confirm'), $this->formData([
                'registration_fee_paid' => '1',
                'category' => 'general',
                'plan_type' => 'visitor_minutes_120',
            ]))
            ->assertSessionHasErrors('plan_type');
    }

    public function test_利用タイプを選ぶときは会員かビジターかも必須(): void
    {
        $this->actingAs($this->admin())
            ->post(route('members.confirm'), $this->formData([
                'category' => 'general',
                'plan_type' => 'one_day',
            ]))
            ->assertSessionHasErrors('registration_fee_paid');
    }

    public function test_プランを選ばなければ会員かビジターかは未設定のまま登録できる(): void
    {
        $member = app(MemberRegistrationService::class)->create($this->formData());

        $this->assertNull($member->fresh()->registration_fee_paid);

        $this->actingAs($this->admin())
            ->get(route('members.show', $member))
            ->assertSee('未設定');
    }

    public function test_編集でビジターから会員に切り替えて月パスにできる(): void
    {
        $member = app(MemberRegistrationService::class)->create($this->formData([
            'registration_fee_paid' => '0',
            'category' => 'general',
            'plan_type' => 'visitor_1day',
        ]));

        $this->actingAs($this->admin())
            ->put(route('members.update', $member), $this->formData([
                'member_code' => $member->member_code,
                'registration_fee_paid' => '1',
                'category' => 'general',
                'plan_type' => 'monthly',
            ]))
            ->assertSessionHasNoErrors();

        $member->refresh();
        $this->assertTrue($member->registration_fee_paid);
        $this->assertSame(PlanType::MONTHLY, $member->activePlan->plan->plan_type);
    }

    public function test_未設定の人の編集画面は今のプランから会員かビジターかを選んだ状態で開く(): void
    {
        $member = app(MemberRegistrationService::class)->create($this->formData([
            'category' => 'general',
        ]));
        app(MemberPlanService::class)->change($member, PlanType::VISITOR_MINUTS_120);

        $html = $this->actingAs($this->admin())
            ->get(route('members.edit', $member))
            ->assertOk()
            ->getContent();

        // ビジターのラジオに checked が付き、ビジター用プルダウンで今のプランが選ばれている
        $this->assertMatchesRegularExpression('/value="0" x-model="paid"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/value="visitor_minutes_120"\s+selected/', $html);
    }

    public function test_正会員にする画面で登録料を受け取ると支払い済みになる(): void
    {
        $member = Member::factory()->create(['registered_at' => null]);

        $this->actingAs($this->admin())
            ->post(route('members.register.store', $member), ['payment_received' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertTrue($member->fresh()->registration_fee_paid);
    }
}
