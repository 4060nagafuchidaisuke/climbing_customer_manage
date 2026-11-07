<?php

namespace Tests\Feature;

use App\Enums\ClimbingLevel;
use App\Enums\ResidenceArea;
use App\Models\Member;
use App\Models\Staff;
use App\Services\MemberRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

// お店の会員登録 Forms に合わせた項目・必須項目・会員番号の開始番号
class MemberShopFieldsTest extends TestCase
{
    use RefreshDatabase;

    // Forms の必須項目だけを入れた登録データ
    private function requiredOnly(array $overrides = []): array
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

    public function test_スタッフ登録は_forms_の必須項目だけで確認画面に進める(): void
    {
        // 生年月日・住所・緊急連絡先・会員区分・利用タイプは任意
        $this->actingAs(Staff::factory()->admin()->create())
            ->post(route('members.confirm'), $this->requiredOnly())
            ->assertSessionHasNoErrors()
            ->assertOk()
            ->assertSee('佐賀県内')
            ->assertSee('18歳以上');
    }

    public function test_スタッフ登録で_forms_の必須項目が空ならエラー(): void
    {
        $this->actingAs(Staff::factory()->admin()->create())
            ->post(route('members.confirm'), $this->requiredOnly([
                'is_minor' => '',
                'residence_area' => '',
                'climbing_experience' => '',
            ]))
            ->assertSessionHasErrors(['is_minor', 'residence_area', 'climbing_experience']);
    }

    public function test_利用タイプを選ぶときは会員区分が必須(): void
    {
        $this->actingAs(Staff::factory()->admin()->create())
            ->post(route('members.confirm'), $this->requiredOnly(['plan_type' => 'monthly']))
            ->assertSessionHasErrors(['category' => '利用タイプを選ぶ場合は、料金を決めるために会員区分も選んでください。']);
    }

    public function test_お客さん登録も_forms_の必須項目だけで確認画面に進める(): void
    {
        $this->post(route('register.guest.confirm'), $this->requiredOnly())
            ->assertSessionHasNoErrors()
            ->assertOk()
            ->assertSee('佐賀県内');
    }

    public function test_登録画面に新しい項目が出る(): void
    {
        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('members.create'))
            ->assertOk()
            ->assertSee('name="residence_area"', false)
            ->assertSee('name="climbing_experience"', false)
            ->assertSee('18歳未満');

        $this->get(URL::signedRoute('register.guest.form'))
            ->assertOk()
            ->assertSee('name="residence_area"', false)
            ->assertSee('name="climbing_experience"', false)
            ->assertDontSee('name="climbing_level"', false); // レベルはスタッフが判断する
    }

    public function test_登録すると経験からレベルの初期値が入る(): void
    {
        $member = app(MemberRegistrationService::class)->create($this->requiredOnly([
            'climbing_experience' => 'over_one_year',
        ]));

        $this->assertSame(ClimbingLevel::INTERMEDIATE, $member->climbing_level);
        $this->assertSame(ResidenceArea::SAGA_INSIDE, $member->residence_area);
        $this->assertNull($member->category);

        // 詳細画面に経験（本人申告）とレベル（スタッフ判断）が別々に出る
        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('members.show', $member))
            ->assertOk()
            ->assertSee('佐賀県内')
            ->assertSee('経験あり（1年以上）')
            ->assertSee('中級者');
    }

    public function test_会員番号は設定した開始番号から振る(): void
    {
        // お店ではカードを 403 番まで発行済み
        config(['members.member_code_start' => 404]);
        $service = app(MemberRegistrationService::class);

        $this->assertSame('00404', $service->create($this->requiredOnly())->member_code);
        $this->assertSame('00405', $service->create($this->requiredOnly())->member_code);
    }

    public function test_開始番号より大きい番号があれば、その続きから振る(): void
    {
        config(['members.member_code_start' => 404]);
        Member::factory()->create()->update(['member_code' => '00500']);

        $member = app(MemberRegistrationService::class)->create($this->requiredOnly());

        $this->assertSame('00501', $member->member_code);
    }

    public function test_編集画面で年齢区分18歳以上を選び直せる(): void
    {
        $member = app(MemberRegistrationService::class)->create($this->requiredOnly(['is_minor' => '1']));

        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('members.edit', $member))
            ->assertOk();

        $this->put(route('members.update', $member), $this->requiredOnly([
            'member_code' => $member->member_code,
            'is_minor' => '0',
        ]))->assertSessionHasNoErrors();

        $this->assertFalse($member->fresh()->is_minor);
    }

    public function test_正会員化は会員区分なしでもできる(): void
    {
        $member = Member::factory()->create(['registered_at' => null]);

        $this->actingAs(Staff::factory()->admin()->create())
            ->post(route('members.register.store', $member), ['payment_received' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($member->fresh()->registered_at);
        $this->assertNull($member->fresh()->category);
    }
}
