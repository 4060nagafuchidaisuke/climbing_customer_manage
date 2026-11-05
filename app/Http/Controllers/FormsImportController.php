<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportFormsMembersRequest;
use App\Services\FormsMemberImportService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Microsoft Forms の回答を管理画面から取り込む（管理者のみ）
//
// 「チェックのみ」で確認 →「この内容で取り込む」で本番、という2段階で使う。
// ブラウザは画面を移動するとファイル選択欄を空に戻すため、チェックしたファイルはサーバーで一時的に預かる。
class FormsImportController extends Controller
{
    // 一時保存する場所（storage/app/private/forms-imports）
    private const DIRECTORY = 'forms-imports';

    // 預かったファイルの情報を入れるセッションのキー
    private const PENDING_KEY = 'forms_import_pending';

    // 預かったファイルを残しておく時間（これを過ぎたら削除する）
    private const PENDING_LIFETIME_MINUTES = 60;

    public function __construct(private FormsMemberImportService $service) {}

    // アップロード画面（直前の取り込み結果と、預かり中のファイルもここに出す）
    public function create()
    {
        $this->deleteExpiredFiles();

        $pending = $this->pendingFile();
        // 預かり中ならそのチェック結果、そうでなければ直前の取り込み結果を出す
        $result = $pending['result'] ?? session('import_result');
        $lastImport = $this->service->lastImportSummary();

        return view('forms_import.create', compact('result', 'pending', 'lastImport'));
    }

    public function store(ImportFormsMembersRequest $request)
    {
        $this->deleteExpiredFiles();
        // 前にチェックしたまま取り込んでいないファイルがあれば、新しいファイルに置き換える
        $this->forgetPendingFile();

        $file = $request->file('file');

        // 拡張子でファイルの種類を判別するので、拡張子付きで一時保存する
        $path = $file->storeAs(
            self::DIRECTORY,
            Str::uuid().'.'.strtolower($file->getClientOriginalExtension()),
            'local'
        );

        // チェックのみ：ファイルは預かったままにして、結果を見てから取り込めるようにする
        if ($request->boolean('dry_run')) {
            $result = $this->service->import(Storage::disk('local')->path($path), dryRun: true);

            // チェック結果も一緒に預かる（画面を再読み込みしても結果とボタンが消えないように）
            session([self::PENDING_KEY => [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'result' => $result->toArray(),
            ]]);

            return redirect()->route('forms-import.create');
        }

        return $this->importAndDelete($path);
    }

    // チェック済みのファイルを本番で取り込む
    public function confirm()
    {
        $pending = $this->pendingFile();

        if ($pending === null) {
            return redirect()->route('forms-import.create')
                ->withErrors(['file' => 'チェックしたファイルの保存期限（'.self::PENDING_LIFETIME_MINUTES.'分）が過ぎました。もう一度ファイルを選んでください。']);
        }

        session()->forget(self::PENDING_KEY);

        return $this->importAndDelete($pending['path']);
    }

    // チェック済みのファイルを取り込まずに捨てる
    public function cancel()
    {
        $this->forgetPendingFile();

        return redirect()->route('forms-import.create');
    }

    private function importAndDelete(string $path)
    {
        try {
            $result = $this->service->import(Storage::disk('local')->path($path));
        } finally {
            // 個人情報なので、取り込みが終わったら（失敗しても）必ず消す
            Storage::disk('local')->delete($path);
        }

        return redirect()->route('forms-import.create')->with('import_result', $result->toArray());
    }

    // 預かり中のファイル。期限切れでファイルがない・チェック結果がないなど、使えないときは null
    // （使えない預かりを残すと、ファイル選択も「取り込む」ボタンも出ない空の画面になるため、捨てる）
    private function pendingFile(): ?array
    {
        $pending = session(self::PENDING_KEY);

        if ($pending === null) {
            return null;
        }

        if (! isset($pending['path'], $pending['name'], $pending['result'])
            || ! Storage::disk('local')->exists($pending['path'])) {
            $this->forgetPendingFile();

            return null;
        }

        return $pending;
    }

    private function forgetPendingFile(): void
    {
        $pending = session()->pull(self::PENDING_KEY);

        if (isset($pending['path'])) {
            Storage::disk('local')->delete($pending['path']);
        }
    }

    // 「チェックのみ」のあと取り込まれずに放置されたファイルを消す
    private function deleteExpiredFiles(): void
    {
        $disk = Storage::disk('local');
        $expiresAt = now()->subMinutes(self::PENDING_LIFETIME_MINUTES)->getTimestamp();

        foreach ($disk->files(self::DIRECTORY) as $path) {
            if ($disk->lastModified($path) < $expiresAt) {
                $disk->delete($path);
            }
        }
    }
}
