<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use App\Models\Member;
use App\Models\Staff;
use App\Services\FormsMemberImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Tests\TestCase;

class FormsMemberImportTest extends TestCase
{
    use RefreshDatabase;

    // ダミーの Forms 回答（1行分）。必要な列だけ上書きして使う
    private function formsRow(array $overrides = []): array
    {
        return array_merge([
            'ID' => '1',
            '完了時刻' => '2026/4/1 10:30:00',
            'お名前' => '山田 太郎',
            '住所' => '東京都千代田区1-1-1',
            '電話番号' => '090-1234-5678',
            '緊急連絡先' => '03-1234-5678',
            '緊急連絡先のお名前' => '山田 花子',
            '免責事項の確認' => '同意します',
        ], $overrides);
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

    public function test_回答を会員として取り込み、免責同意も登録する(): void
    {
        $result = $this->import([$this->formsRow()]);

        $this->assertSame(1, $result->imported);
        $this->assertSame([], $result->errors);

        $member = Member::where('forms_response_id', '1')->firstOrFail();
        $this->assertSame('山田', $member->last_name);
        $this->assertSame('太郎', $member->first_name);
        $this->assertNull($member->last_name_kana);
        $this->assertSame('09012345678', $member->phone);
        $this->assertSame('0312345678', $member->emergency_phone);
        $this->assertSame('山田 花子', $member->emergency_name);
        $this->assertSame('2026-04-01 10:30:00', $member->registered_at->format('Y-m-d H:i:s'));
        $this->assertNotNull($member->member_code);

        $waiver = $member->waivers()->sole();
        $this->assertSame(FormsMemberImportService::WAIVER_VERSION, $waiver->version);
    }

    public function test_電話番号は全角や先頭の0が消えたものもそろえる(): void
    {
        $this->import([
            $this->formsRow(['ID' => '1', '電話番号' => '０９０ー１２３４ー５６７８', '緊急連絡先' => 9012345678]),
        ]);

        $member = Member::sole();
        $this->assertSame('09012345678', $member->phone);
        $this->assertSame('09012345678', $member->emergency_phone);
    }

    public function test_取り込み済みの_i_dは飛ばし、上書きもしない(): void
    {
        $this->import([$this->formsRow()]);
        Member::sole()->update(['address' => 'アプリで直した住所']);

        $result = $this->import([
            $this->formsRow(['住所' => 'Formsの古い住所']),
            $this->formsRow(['ID' => '2', 'お名前' => '佐藤 次郎']),
        ]);

        $this->assertSame(1, $result->imported);
        $this->assertSame(1, $result->alreadyImported);
        $this->assertSame(2, Member::count());
        $this->assertSame('アプリで直した住所', Member::where('forms_response_id', '1')->value('address'));
    }

    public function test_必須項目が空の行は取り込まず、行番号つきでエラーにする(): void
    {
        $result = $this->import([
            $this->formsRow(['ID' => '1']),
            $this->formsRow(['ID' => '2', '電話番号' => '']),
            $this->formsRow(['ID' => '3', '緊急連絡先' => '']),
        ]);

        $this->assertSame(1, $result->imported);
        $this->assertSame([3, 4], array_keys($result->errors)); // 1行目は見出しなので、データは2行目から
        $this->assertSame(1, Member::count());
    }

    public function test_ひらがなで始まる値も壊さずに取り込み、免責「いいえ」は未同意にする(): void
    {
        // ひらがな・カタカナは UTF-8 で先頭が全角スペースと同じバイト（E3）なので、trim() だと壊れる
        $result = $this->import([
            $this->formsRow(['お名前' => '　さくら　はなこ　', '住所' => 'さいたま市', '免責事項の確認' => 'いいえ']),
        ]);

        $member = Member::sole();
        $this->assertSame('さくら', $member->last_name);
        $this->assertSame('はなこ', $member->first_name);
        $this->assertSame('さいたま市', $member->address);
        $this->assertSame(0, $member->waivers()->count());
        $this->assertCount(1, $result->warnings[2]);
    }

    public function test_名前にスペースがない人と免責未同意の人は警告つきで取り込む(): void
    {
        $result = $this->import([
            $this->formsRow(['お名前' => '山田太郎', '免責事項の確認' => '']),
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

    public function test_必要な列がないファイルは1行も取り込まない(): void
    {
        $row = $this->formsRow();
        unset($row['電話番号']);

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
