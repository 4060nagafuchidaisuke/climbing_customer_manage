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

    public function test_並び順は_sort_orderの小さい順で、同じ番号なら登録が古い順(): void
    {
        $this->makeSponsor(['title' => '番号2', 'sort_order' => 2]);
        $this->makeSponsor(['title' => '番号1・先に登録', 'sort_order' => 1]);
        $this->makeSponsor(['title' => '番号1・後に登録', 'sort_order' => 1]);

        $expected = ['番号1・先に登録', '番号1・後に登録', '番号2'];
        $this->assertSame($expected, Sponsor::ordered()->pluck('title')->all());

        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('sponsors.index'))
            ->assertSeeInOrder($expected);
    }

    public function test_新しく登録した広告は一番下に加わる(): void
    {
        Storage::fake('public');
        $this->makeSponsor(['title' => '既存', 'sort_order' => 7]);

        $this->actingAs(Staff::factory()->admin()->create())
            ->post(route('sponsors.store'), [
                'title' => '新規',
                'display_seconds' => 5,
                'media' => UploadedFile::fake()->image('ad.png'),
            ])
            ->assertRedirect(route('sponsors.index'));

        $this->assertSame(['既存', '新規'], Sponsor::ordered()->pluck('title')->all());
        $this->assertSame(8, Sponsor::firstWhere('title', '新規')->sort_order);
    }

    public function test_矢印ボタンで隣と入れ替わり、番号は1から振り直す(): void
    {
        // わざと番号を重ねておく（並びは A, B, C）
        $a = $this->makeSponsor(['title' => 'A', 'sort_order' => 0]);
        $b = $this->makeSponsor(['title' => 'B', 'sort_order' => 0]);
        $c = $this->makeSponsor(['title' => 'C', 'sort_order' => 3]);
        $admin = Staff::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('sponsors.move', $c), ['direction' => 'up'])
            ->assertRedirect(route('sponsors.index'));

        $this->assertSame(['A', 'C', 'B'], Sponsor::ordered()->pluck('title')->all());
        $this->assertSame([1, 2, 3], Sponsor::ordered()->pluck('sort_order')->all());

        $this->actingAs($admin)->post(route('sponsors.move', $a), ['direction' => 'down']);
        $this->assertSame(['C', 'A', 'B'], Sponsor::ordered()->pluck('title')->all());
    }

    public function test_先頭の上・末尾の下は何もせず、おかしな向きは受け付けない(): void
    {
        $a = $this->makeSponsor(['title' => 'A', 'sort_order' => 1]);
        $b = $this->makeSponsor(['title' => 'B', 'sort_order' => 2]);
        $admin = Staff::factory()->admin()->create();

        $this->actingAs($admin)->post(route('sponsors.move', $a), ['direction' => 'up']);
        $this->actingAs($admin)->post(route('sponsors.move', $b), ['direction' => 'down']);
        $this->assertSame(['A', 'B'], Sponsor::ordered()->pluck('title')->all());

        $this->actingAs($admin)
            ->post(route('sponsors.move', $a), ['direction' => 'sideways'])
            ->assertSessionHasErrors('direction');
    }

    public function test_並べ替えると受付で流れる上位5つが入れ替わり、一覧に再生中と出る(): void
    {
        foreach (range(1, 6) as $i) {
            $sponsors[$i] = $this->makeSponsor(['title' => "広告{$i}", 'media_path' => "sponsors/ad{$i}.png", 'sort_order' => $i]);
        }
        $admin = Staff::factory()->admin()->create();

        // 6番目を1つ上げると、5番目と入れ替わって受付に流れる
        $this->actingAs($admin)->post(route('sponsors.move', $sponsors[6]), ['direction' => 'up']);

        $this->actingAs($admin)
            ->get(route('checkin.index'))
            ->assertSee('sponsors/ad6.png')
            ->assertDontSee('sponsors/ad5.png');

        $this->actingAs($admin)
            ->get(route('sponsors.index'))
            ->assertSee('受付で再生中')
            ->assertSee('待機中');
    }

    public function test_編集画面で、ファイルを選ばなければ今のファイルのまま更新する(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('sponsors/old.png', 'old');
        $sponsor = $this->makeSponsor(['media_path' => 'sponsors/old.png', 'sort_order' => 4]);

        $this->actingAs(Staff::factory()->admin()->create())
            ->get(route('sponsors.edit', $sponsor))
            ->assertOk()
            ->assertSee('テストスポンサー');

        $this->actingAs(Staff::factory()->admin()->create())
            ->putJson(route('sponsors.update', $sponsor), [
                'title' => '名前を変更',
                'display_seconds' => 10,
                'start_date' => '2026-12-01',
                'end_date' => '2026-12-31',
                // is_active は送らない（チェックを外した）
            ])
            ->assertOk()
            ->assertJson(['redirect' => route('sponsors.index')])
            ->assertSessionHas('success', '広告を更新しました');

        $sponsor->refresh();
        $this->assertSame('名前を変更', $sponsor->title);
        $this->assertSame(10, $sponsor->display_seconds);
        $this->assertSame('2026-12-31', $sponsor->end_date->toDateString());
        $this->assertFalse($sponsor->is_active);
        $this->assertSame('sponsors/old.png', $sponsor->media_path);
        $this->assertSame(4, $sponsor->sort_order); // 並び順は変わらない
        Storage::disk('public')->assertExists('sponsors/old.png');
    }

    public function test_編集画面でファイルを差し替えると、種類を判定し直して古いファイルを消す(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('sponsors/old.png', 'old');
        $sponsor = $this->makeSponsor(['media_path' => 'sponsors/old.png']);

        $this->actingAs(Staff::factory()->admin()->create())
            ->putJson(route('sponsors.update', $sponsor), [
                'title' => $sponsor->title,
                'display_seconds' => 5,
                'is_active' => '1',
                'media' => UploadedFile::fake()->create('new.mp4', 100, 'video/mp4'),
            ])
            ->assertOk();

        $sponsor->refresh();
        $this->assertSame(SponsorMediaType::VIDEO, $sponsor->media_type);
        $this->assertNotSame('sponsors/old.png', $sponsor->media_path);
        Storage::disk('public')->assertExists($sponsor->media_path);
        Storage::disk('public')->assertMissing('sponsors/old.png');
    }

    public function test_編集画面でエラーのときは_json_で返し、何も変えない(): void
    {
        Storage::fake('public');
        $sponsor = $this->makeSponsor();

        $this->actingAs(Staff::factory()->admin()->create())
            ->putJson(route('sponsors.update', $sponsor), [
                'title' => '',
                'display_seconds' => 5,
                'end_date' => '2026-01-01',
                'start_date' => '2026-02-01',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'end_date']);

        $this->assertSame('テストスポンサー', $sponsor->refresh()->title);
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
        $this->assertSame(1, $sponsor->sort_order); // 1件目なので一番上（最大値 + 1）
        $this->assertTrue($sponsor->is_active);
        Storage::disk('public')->assertExists($sponsor->media_path);
    }
}
