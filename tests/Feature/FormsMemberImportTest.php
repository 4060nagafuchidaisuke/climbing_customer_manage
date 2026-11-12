<?php

namespace Tests\Feature;

use App\Enums\ClimbingExperience;
use App\Enums\ClimbingLevel;
use App\Enums\ResidenceArea;
use App\Enums\StaffRole;
use App\Models\Member;
use App\Models\Staff;
use App\Services\FormsMemberImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Tests\TestCase;

class FormsMemberImportTest extends TestCase
{
    use RefreshDatabase;

    // ダミーの回答（1行分）。お店の Google フォームの回答スプレッドシートと同じ列。必要な列だけ上書きして使う
    // 回答IDの列はなく、何番目の回答か（2行目が1番）が会員番号になる
    private function formsRow(array $overrides = []): array
    {
        return array_merge([
            'タイムスタンプ' => '2026/07/01 13:00:00',
            'Q1:氏名（フルネーム）' => '山田 太郎',
            'Q1-2:氏名（フリガナ）' => 'ヤマダ タロウ',
            'Q1-3:保護者氏名' => '',
            'Q2:電話番号' => '090-1234-5678',
            'Q3:緊急連絡先' => '03-1234-5678',
            'Q4:年齢区分' => '18歳以上',
            'Q5:お住まい' => '佐賀県内',
            'Q6:クライミング経験' => '経験あり（1年以内）',
            'Q7:利用規約・同意' => '上記内容に同意',
        ], $overrides);
    }

    // タイムスタンプだけが違う回答を n 件（1件目が 13:01、2件目が 13:02…）
    private function formsRows(int $count): array
    {
        return array_map(
            fn ($i) => $this->formsRow(['タイムスタンプ' => sprintf('2026/07/01 13:%02d:00', $i)]),
            range(1, $count),
        );
    }

    // ダミーの xlsx を作ってパスを返す
    private function makeFile(array $rows, string $extension = 'xlsx'): string
    {
        $path = tempnam(sys_get_temp_dir(), 'forms').'.'.$extension;
        SimpleExcelWriter::create($path)->addRows($rows)->close();

        return $path;
    }

    private function import(array $rows, bool $dryRun = false)
    {
        return app(FormsMemberImportService::class)->import($this->makeFile($rows), $dryRun);
    }

    public function test_何番目の回答かを会員番号にして取り込む(): void
    {
        $result = $this->import($this->formsRows(3));

        $this->assertSame(3, $result->imported);
        $this->assertSame([], $result->errors);
        $this->assertSame([], $result->warnings);
        $this->assertSame(['00001', '00002', '00003'], Member::orderBy('id')->pluck('member_code')->all());

        $member = Member::where('member_code', '00001')->firstOrFail();
        $this->assertSame('1', $member->forms_response_id);
        $this->assertSame('山田', $member->last_name);
        $this->assertSame('太郎', $member->first_name);
        $this->assertSame('ヤマダ', $member->last_name_kana);
        $this->assertSame('タロウ', $member->first_name_kana);
        $this->assertSame('09012345678', $member->phone);
        $this->assertSame('0312345678', $member->emergency_phone);
        $this->assertFalse($member->is_minor);
        $this->assertSame(ResidenceArea::SAGA_INSIDE, $member->residence_area);
        $this->assertSame(ClimbingExperience::WITHIN_ONE_YEAR, $member->climbing_experience);
        $this->assertSame(ClimbingLevel::BEGINNER, $member->climbing_level); // 経験からの初期値
        $this->assertNull($member->address);
        $this->assertNull($member->category);
        $this->assertSame('2026-07-01 13:01:00', $member->registered_at->format('Y-m-d H:i:s'));

        $waiver = $member->waivers()->sole();
        $this->assertSame(FormsMemberImportService::WAIVER_VERSION, $waiver->version);
    }

    public function test_列見出しの全角半角の違いは気にせず読める(): void
    {
        $row = $this->formsRow();
        $renamed = [];
        foreach ($row as $header => $value) {
            $renamed[strtr($header, [':' => '：', '（' => '(', '）' => ')'])] = $value;
        }

        $result = $this->import([$renamed]);

        $this->assertSame(1, $result->imported);
    }

    public function test_選択肢の末尾の区切り記号があっても読める(): void
    {
        // 複数選択型の質問は「18歳以上;」のように書き出されることがある
        $this->import([$this->formsRow(['Q4:年齢区分' => '18歳未満;', 'Q5:お住まい' => '佐賀県外;'])]);

        $member = Member::sole();
        $this->assertTrue($member->is_minor);
        $this->assertSame(ResidenceArea::SAGA_OUTSIDE, $member->residence_area);
    }

    public function test_18歳未満の人は未成年にし、保護者氏名も保存する(): void
    {
        $this->import([
            $this->formsRow(['Q4:年齢区分' => '18歳未満', 'Q1-3:保護者氏名' => '山田 花子']),
        ]);

        $member = Member::sole();
        $this->assertTrue($member->is_minor);
        $this->assertSame('山田 花子', $member->guardian_name);
        $this->assertTrue($member->waivers()->sole()->is_minor_signed);
    }

    public function test_ひらがなのフリガナはカタカナにそろえる(): void
    {
        $this->import([$this->formsRow(['Q1-2:氏名（フリガナ）' => 'やまだ　たろう'])]);

        $member = Member::sole();
        $this->assertSame('ヤマダ', $member->last_name_kana);
        $this->assertSame('タロウ', $member->first_name_kana);
    }

    public function test_電話番号は全角や先頭の0が消えたものもそろえる(): void
    {
        $this->import([
            $this->formsRow(['Q2:電話番号' => '０９０ー１２３４ー５６７８', 'Q3:緊急連絡先' => 9012345678]),
        ]);

        $member = Member::sole();
        $this->assertSame('09012345678', $member->phone);
        $this->assertSame('09012345678', $member->emergency_phone);
    }

    public function test_取り込み済みの回答は飛ばし、上書きもしない(): void
    {
        [$first, $second] = $this->formsRows(2);

        $this->import([$first]);
        Member::sole()->update(['phone' => '08000000000', 'last_name' => '山田（スタッフが修正）']);

        // 次の取り込み：1件目はそのまま（フォーム側の電話番号が古い）、2件目が新しく増えた
        $first['Q2:電話番号'] = '090-9999-9999';
        $result = $this->import([$first, $second]);

        $this->assertSame(1, $result->imported);
        $this->assertSame(1, $result->alreadyImported);
        $this->assertSame('08000000000', Member::where('member_code', '00001')->value('phone'));
        $this->assertTrue(Member::where('member_code', '00002')->exists());
    }

    public function test_並べ替えや行の削除で順番がずれたら1行も取り込まない(): void
    {
        [$first, $second, $third] = $this->formsRows(3);
        $this->import([$first, $second]);

        // 1件目の行が消えて、2件目が2行目（＝1番）に来てしまった
        $result = $this->import([$second, $third]);

        $this->assertSame(0, $result->imported);
        $this->assertStringContainsString('並び順が合いません', $result->errors[2][0]);
        $this->assertSame(2, Member::count());
    }

    public function test_会員番号がほかの会員とぶつかる行は取り込まない(): void
    {
        // 取り込みより先に、アプリで 00001 番の会員が登録されていた
        // （MemberFactory は作成後に会員番号を ID から振り直すので、作ったあとで設定する）
        Member::factory()->create()->update(['member_code' => '00001']);

        $result = $this->import([$this->formsRow()]);

        $this->assertSame(0, $result->imported);
        $this->assertStringContainsString('00001', $result->errors[2][0]);
    }

    public function test_氏名や電話番号が空の行は取り込まず、行番号つきでエラーにする(): void
    {
        $rows = $this->formsRows(3);
        $rows[1]['Q2:電話番号'] = '';
        $rows[2]['Q1:氏名（フルネーム）'] = '';

        $result = $this->import($rows);

        $this->assertSame(1, $result->imported);
        $this->assertSame([3, 4], array_keys($result->errors)); // 1行目は見出しなので、データは2行目から
        $this->assertSame(['00001'], Member::pluck('member_code')->all());
    }

    public function test_緊急連絡先や選択肢が空でも取り込み、要確認にする(): void
    {
        // カードを発行済みの会員なので、選択肢が読めないくらいでは取り込みを止めない
        $result = $this->import([
            $this->formsRow([
                'Q3:緊急連絡先' => '',
                'Q4:年齢区分' => '',
                'Q5:お住まい' => '',
                'Q6:クライミング経験' => '',
            ]),
        ]);

        $this->assertSame(1, $result->imported);
        $this->assertCount(4, $result->warnings[2]);

        $member = Member::sole();
        $this->assertNull($member->emergency_phone);
        $this->assertNull($member->residence_area);
        $this->assertNull($member->climbing_experience);
    }

    public function test_ひらがなで始まる値も壊さずに取り込み、規約「いいえ」は未同意にする(): void
    {
        // ひらがな・カタカナは UTF-8 で先頭が全角スペースと同じバイト（E3）なので、trim() だと壊れる
        $result = $this->import([
            $this->formsRow(['Q1:氏名（フルネーム）' => '　さくら　はなこ　', 'Q7:利用規約・同意' => 'いいえ']),
        ]);

        $member = Member::sole();
        $this->assertSame('さくら', $member->last_name);
        $this->assertSame('はなこ', $member->first_name);
        $this->assertSame(0, $member->waivers()->count());
        $this->assertCount(1, $result->warnings[2]);
    }

    public function test_名前にスペースがない人と規約未同意の人は警告つきで取り込む(): void
    {
        $result = $this->import([
            $this->formsRow(['Q1:氏名（フルネーム）' => '山田太郎', 'Q7:利用規約・同意' => '']),
        ]);

        $member = Member::sole();
        $this->assertSame('山田太郎', $member->last_name);
        $this->assertNull($member->first_name);
        $this->assertSame(0, $member->waivers()->count());
        $this->assertCount(2, $result->warnings[2]);
    }

    public function test_dry_runでは_d_bに保存しない(): void
    {
        $result = $this->import([$this->formsRow()], dryRun: true);

        $this->assertSame(1, $result->imported);
        $this->assertSame(0, Member::count());
    }

    public function test_行数が増えても取り込み済みの確認は最初の問い合わせだけで済む(): void
    {
        $rows = $this->formsRows(50);
        $this->import(array_slice($rows, 0, 2));

        DB::enableQueryLog();
        $result = $this->import($rows, dryRun: true);

        // 取り込み済みの回答番号と、使用中の会員番号の2回だけ（行ごとに問い合わせると50回以上）
        $this->assertCount(2, DB::getQueryLog());
        $this->assertSame(48, $result->imported);
        $this->assertSame(2, $result->alreadyImported);
    }

    public function test_画面に前回までの取り込み状況を出す(): void
    {
        $admin = Staff::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('forms-import.create'))
            ->assertSee('まだありません');

        // 回答番号は文字列で保存しているが、"9" ではなく数字として大きい "10" を出す
        $this->import($this->formsRows(10));

        $this->get(route('forms-import.create'))
            ->assertSee('10件目の回答まで')
            ->assertSee('合計 10人');
    }

    public function test_必要な列がないファイルは1行も取り込まない(): void
    {
        $row = $this->formsRow();
        unset($row['Q2:電話番号']);

        $result = $this->import([$row]);

        $this->assertSame(0, $result->imported);
        $this->assertStringContainsString('電話番号', $result->errors[1][0]);
        $this->assertSame(0, Member::count());
    }

    public function test_管理者は画面から取り込めて、アップロードしたファイルは残らない(): void
    {
        Storage::fake('local');
        $admin = Staff::factory()->admin()->create();
        $file = new UploadedFile($this->makeFile([$this->formsRow()]), 'forms.xlsx', null, null, true);

        $this->actingAs($admin)
            ->post(route('forms-import.store'), ['file' => $file])
            ->assertRedirect(route('forms-import.create'))
            ->assertSessionHas('import_result');

        $this->assertSame(1, Member::count());
        $this->assertSame([], Storage::disk('local')->files('forms-imports'));

        // リダイレクト先に取り込み結果が表示される
        $this->get(route('forms-import.create'))
            ->assertOk()
            ->assertSee('取り込み結果')
            ->assertSee('1件');
    }

    // 画面から「チェックのみ」でアップロードする
    private function uploadForCheck(Staff $admin): void
    {
        $file = new UploadedFile($this->makeFile([$this->formsRow()]), 'forms.xlsx', null, null, true);

        $this->actingAs($admin)
            ->post(route('forms-import.store'), ['file' => $file, 'dry_run' => '1'])
            ->assertRedirect(route('forms-import.create'));
    }

    public function test_チェックしたファイルは預かり、選び直さずにそのまま取り込める(): void
    {
        Storage::fake('local');
        $admin = Staff::factory()->admin()->create();

        $this->uploadForCheck($admin);

        // チェックの時点では登録せず、ファイルは預かっている
        $this->assertSame(0, Member::count());
        $this->assertCount(1, Storage::disk('local')->files('forms-imports'));
        // 預かり中は「取り込む／取り消す」だけを出し、ファイル選択は出さない
        // 再読み込みしても結果とボタンが消えない
        foreach (range(1, 2) as $_) {
            $this->get(route('forms-import.create'))
                ->assertSee('チェック結果')
                ->assertSee('forms.xlsx')
                ->assertSee('この内容で取り込む')
                ->assertDontSee('★取り込むファイルの選択');
        }

        // 「この内容で取り込む」で登録され、ファイルは消える
        $this->post(route('forms-import.confirm'))
            ->assertRedirect(route('forms-import.create'));

        $this->assertSame(1, Member::count());
        $this->assertSame([], Storage::disk('local')->files('forms-imports'));

        // 取り込み後は、結果とファイル選択が出る
        $this->get(route('forms-import.create'))
            ->assertSee('取り込み結果')
            ->assertSee('★取り込むファイルの選択')
            ->assertDontSee(route('forms-import.confirm')); // 説明文にもボタン名が出るので、送信先で確認する
    }

    public function test_取り消すとファイルを消して何も登録しない(): void
    {
        Storage::fake('local');
        $admin = Staff::factory()->admin()->create();

        $this->uploadForCheck($admin);
        $this->delete(route('forms-import.cancel'))
            ->assertRedirect(route('forms-import.create'));

        $this->assertSame([], Storage::disk('local')->files('forms-imports'));

        // 取り消したあとに「取り込む」を押しても登録されない
        $this->post(route('forms-import.confirm'))->assertSessionHasErrors('file');
        $this->assertSame(0, Member::count());
    }

    public function test_1時間放置したファイルは削除され、取り込めない(): void
    {
        Storage::fake('local');
        $admin = Staff::factory()->admin()->create();

        $this->uploadForCheck($admin);
        $path = Storage::disk('local')->files('forms-imports')[0];
        touch(Storage::disk('local')->path($path), now()->subMinutes(61)->getTimestamp());

        // 画面を開いた時点で古いファイルは消える
        $this->get(route('forms-import.create'))->assertOk();
        $this->assertSame([], Storage::disk('local')->files('forms-imports'));

        $this->post(route('forms-import.confirm'))->assertSessionHasErrors('file');
        $this->assertSame(0, Member::count());
    }

    public function test_チェック結果のない古い預かりは捨てて、ファイル選択を出す(): void
    {
        // 結果を預かる前の版で「チェックのみ」をしたセッションが残っている状態
        Storage::fake('local');
        Storage::disk('local')->put('forms-imports/old.xlsx', 'dummy');
        $admin = Staff::factory()->admin()->create();

        $this->actingAs($admin)
            ->withSession(['forms_import_pending' => ['path' => 'forms-imports/old.xlsx', 'name' => 'old.xlsx']])
            ->get(route('forms-import.create'))
            ->assertOk()
            ->assertSee('★取り込むファイルの選択');

        $this->assertSame([], Storage::disk('local')->files('forms-imports'));
    }

    public function test_管理者以外は取り込み画面を開けない(): void
    {
        $staff = Staff::factory()->create(['role' => StaffRole::STAFF]);

        $this->actingAs($staff)
            ->get(route('forms-import.create'))
            ->assertForbidden();
    }
}
