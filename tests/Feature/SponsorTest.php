<?php

namespace Tests\Feature;

use App\Enums\SponsorMediaType;
use App\Models\Sponsor;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SponsorTest extends TestCase
{
    use RefreshDatabase;

    // ダミーの広告を1件作る。必要な項目だけ上書きして使う
    private function makeSponsor(array $overrides = []): Sponsor
    {
        return Sponsor::create(array_merge([
            'title' => 'テストスポンサー',
            'media_path' => 'sponsors/dummy.png',
            'media_type' => SponsorMediaType::IMAGE,
            'is_active' => true,
            'display_seconds' => 5,
            'sort_order' => 1,
            'start_date' => null,
            'end_date' => null,
        ], $overrides));
    }

    // visible() で取れた広告のタイトル一覧
    private function visibleTitles(): array
    {
        return Sponsor::visible()->orderBy('title')->pluck('title')->all();
    }

    public function test_公開中で期間指定なしの広告は表示する(): void
    {
        $this->makeSponsor(['title' => '期間なし']);

        $this->assertSame(['期間なし'], $this->visibleTitles());
    }

    public function test_非公開の広告は期間内でも表示しない(): void
    {
        $this->makeSponsor(['title' => '非公開', 'is_active' => false]);

        $this->assertSame([], $this->visibleTitles());
    }

    public function test_開始日と終了日の当日は表示する(): void
    {
        $this->makeSponsor(['title' => '今日から', 'start_date' => today()]);
        $this->makeSponsor(['title' => '今日まで', 'end_date' => today()]);

        $this->assertSame(['今日から', '今日まで'], $this->visibleTitles());
    }

    public function test_開始前と終了後の広告は表示しない(): void
    {
        $this->makeSponsor(['title' => '明日から', 'start_date' => today()->addDay()]);
        $this->makeSponsor(['title' => '昨日まで', 'end_date' => today()->subDay()]);
        $this->makeSponsor(['title' => '期間中', 'start_date' => today()->subDay(), 'end_date' => today()->addDay()]);

        $this->assertSame(['期間中'], $this->visibleTitles());
    }

    public function test_受付画面には表示できる広告だけを秒数つきで出す(): void
    {
        $this->makeSponsor(['title' => '期間中', 'media_path' => 'sponsors/visible.png', 'display_seconds' => 12]);
        $this->makeSponsor(['title' => '期限切れ', 'media_path' => 'sponsors/expired.png', 'end_date' => today()->subDay()]);

        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('checkin.index'))
            ->assertOk()
            ->assertSee('sponsors/visible.png')
            ->assertSee('data-seconds="12"', false)
            ->assertDontSee('sponsors/expired.png');
    }

    public function test_50_m_bを超えるファイルは日本語のエラーで登録しない(): void
    {
        Storage::fake('public');

        $this->actingAs(Staff::factory()->admin()->create())
            ->from(route('sponsors.create'))
            ->post(route('sponsors.store'), [
                'title' => '大きい動画',
                'display_seconds' => 5,
                'media' => UploadedFile::fake()->create('big.mp4', 51201, 'video/mp4'),
            ])
            ->assertRedirect(route('sponsors.create'))
            ->assertSessionHasErrors(['media' => '画像・動画は50MB以下のファイルを選んでください。']);

        $this->assertSame(0, Sponsor::count());
    }

    public function test_受付画面と一覧画面は登録が古い順に並べる(): void
    {
        // sort_order は並び順に使わないので、わざと逆の値にしておく
        $this->makeSponsor(['title' => '先に登録', 'sort_order' => 9]);
        $this->makeSponsor(['title' => '後に登録', 'sort_order' => 1]);

        $this->assertSame(['先に登録', '後に登録'], Sponsor::visible()->ordered()->pluck('title')->all());

        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('sponsors.index'))
            ->assertSeeInOrder(['先に登録', '後に登録']);
    }

    public function test_受付画面には登録が古い順に上位5つまでしか流さない(): void
    {
        foreach (range(1, 6) as $i) {
            $this->makeSponsor(['title' => "広告{$i}", 'media_path' => "sponsors/ad{$i}.png"]);
        }

        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('checkin.index'))
            ->assertOk()
            ->assertSeeInOrder(['sponsors/ad1.png', 'sponsors/ad5.png'])
            ->assertDontSee('sponsors/ad6.png');
    }

    public function test_js_から送ってエラーのときは、ページを移らず_json_でエラーを返す(): void
    {
        Storage::fake('public');

        $this->actingAs(Staff::factory()->admin()->create())
            ->postJson(route('sponsors.store'), [
                'title' => '',
                'display_seconds' => 5,
                'media' => UploadedFile::fake()->image('ad.png'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertSame(0, Sponsor::count());
    }

    public function test_js_から送って成功したときは、一覧画面の_ur_lを返してメッセージを残す(): void
    {
        Storage::fake('public');

        $this->actingAs(Staff::factory()->admin()->create())
            ->postJson(route('sponsors.store'), [
                'title' => '新しい広告',
                'display_seconds' => 8,
                'media' => UploadedFile::fake()->image('ad.png'),
                'is_active' => '1',
            ])
            ->assertOk()
            ->assertJson(['redirect' => route('sponsors.index')])
            ->assertSessionHas('success', '広告を登録しました');

        $sponsor = Sponsor::sole();
        $this->assertSame('新しい広告', $sponsor->title);
        $this->assertSame(0, $sponsor->sort_order);
        $this->assertTrue($sponsor->is_active);
        Storage::disk('public')->assertExists($sponsor->media_path);
    }
}
