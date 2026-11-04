<?php

namespace App\Services;

use App\Models\Member;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\SimpleExcel\SimpleExcelReader;

// Microsoft Forms から書き出したファイル（xlsx / csv）を members に取り込む
// Artisan コマンド（初回の一括取り込み）と管理画面（定期的な取り込み）の両方から呼ぶ
class FormsMemberImportService
{
    // Forms の列見出し => このクラスで使うキー
    // ⚠️ 仮の列名。実物の Excel を確認したら、ここだけ直せばよい
    public const COLUMNS = [
        'ID' => 'forms_response_id',
        '完了時刻' => 'completed_at',
        'お名前' => 'name',
        '住所' => 'address',
        '電話番号' => 'phone',
        '緊急連絡先' => 'emergency_phone',
        '緊急連絡先のお名前' => 'emergency_name',
        '免責事項の確認' => 'waiver',
    ];

    // Forms 経由の免責同意を、アプリで同意したもの（v1）と区別するためのバージョン名
    public const WAIVER_VERSION = 'forms';

    // 免責の回答が「同意していない」とみなす言葉
    private const WAIVER_DECLINED_PATTERN = '/(いいえ|しない|しません|未確認|no)/ui';

    public function import(string $path, bool $dryRun = false): FormsMemberImportResult
    {
        $result = new FormsMemberImportResult($dryRun);

        $reader = SimpleExcelReader::create($path)
            ->trimHeaderRow()
            ->preserveEmptyRows(); // 空行も読む（行番号をファイルとそろえるため）

        // 必要な列がそろっていなければ、1行も読まずに終える
        $missing = array_diff(array_keys(self::COLUMNS), $reader->getHeaders() ?? []);
        if ($missing !== []) {
            $result->addError(1, '必要な列が見つかりません：'.implode('、', $missing));

            return $result;
        }

        // 同じファイルの中で同じIDが2回出てきたときのために覚えておく
        $seenIds = [];

        foreach ($reader->getRows() as $index => $row) {
            $rowNumber = $index + 2; // 1行目は見出し

            $data = $this->mapRow($row);

            // 完全な空行は黙って飛ばす
            if (collect($data)->filter(fn ($v) => filled($v))->isEmpty()) {
                continue;
            }

            $this->importRow($data, $rowNumber, $seenIds, $result);
        }

        return $result;
    }

    // 1行分を「Forms の列見出し」から「このクラスのキー」に置き換える
    private function mapRow(array $row): array
    {
        $data = [];
        foreach (self::COLUMNS as $header => $key) {
            $data[$key] = $row[$header] ?? null;
        }

        return $data;
    }

    private function importRow(array $data, int $rowNumber, array &$seenIds, FormsMemberImportResult $result): void
    {
        $responseId = $this->toText($data['forms_response_id']);

        // 取り込み済み（DB にある or このファイルの前の行にある）なら飛ばす
        // アプリで直した内容を Forms の古い内容で上書きしないため、更新もしない
        if (filled($responseId)
            && (isset($seenIds[$responseId]) || Member::where('forms_response_id', $responseId)->exists())) {
            $result->alreadyImported++;

            return;
        }

        [$lastName, $firstName] = $this->splitName($this->toText($data['name']));

        $attributes = [
            'forms_response_id' => $responseId,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'address' => $this->toText($data['address']),
            'phone' => $this->normalizePhone($data['phone']),
            'emergency_phone' => $this->normalizePhone($data['emergency_phone']),
            'emergency_name' => $this->toText($data['emergency_name']),
            'registered_at' => $this->toDateTime($data['completed_at']),
        ];

        $validator = Validator::make($attributes, [
            'forms_response_id' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:15'],
            'first_name' => ['nullable', 'string', 'max:15'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],
            'emergency_phone' => ['required', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],
            'emergency_name' => ['nullable', 'string', 'max:100'],
        ], [], [
            'forms_response_id' => 'ID',
            'last_name' => '名前（姓）',
            'first_name' => '名前（名）',
            'address' => '住所',
            'phone' => '電話番号',
            'emergency_phone' => '緊急連絡先',
            'emergency_name' => '緊急連絡先のお名前',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $result->addError($rowNumber, $message);
            }

            return;
        }

        // ここから先は取り込む行。要確認の点を警告として残す
        if ($firstName === null) {
            $result->addWarning($rowNumber, '名前にスペースがないため、姓と名を分けられませんでした（全体を姓に入れています）');
        }
        if ($attributes['emergency_name'] === null) {
            $result->addWarning($rowNumber, '緊急連絡先のお名前が空です');
        }
        if ($attributes['registered_at'] === null) {
            $attributes['registered_at'] = now();
            $result->addWarning($rowNumber, '完了時刻が読めないため、入会日を今日にしました');
        }

        $agreed = $this->isWaiverAgreed($data['waiver']);
        if (! $agreed) {
            $result->addWarning($rowNumber, '免責事項に同意していません（来店時にアプリで同意をもらってください）');
        }

        $seenIds[$responseId] = true;
        $result->imported++;

        if ($result->dryRun) {
            return;
        }

        // 会員番号の採番（lockForUpdate）がトランザクション前提なので、1行ずつ包む
        DB::transaction(function () use ($attributes, $agreed) {
            $member = Member::create($attributes);

            if ($agreed) {
                $member->waivers()->create([
                    'version' => self::WAIVER_VERSION,
                    'signed_at' => $attributes['registered_at'],
                    'is_minor_signed' => false,
                ]);
            }
        });
    }

    // 「山田 太郎」→ ['山田', '太郎']。スペースがなければ ['山田太郎', null]
    private function splitName(?string $name): array
    {
        if ($name === null) {
            return [null, null];
        }

        $parts = preg_split('/[\s　]+/u', $name, 2);

        return [$parts[0], $parts[1] ?? null];
    }

    // 電話番号を数字だけにそろえる
    private function normalizePhone(mixed $value): ?string
    {
        $text = $this->toText($value);
        if ($text === null) {
            return null;
        }

        // 全角数字 → 半角、ハイフン・空白・括弧などを取り除く
        $digits = preg_replace('/\D/', '', mb_convert_kana($text, 'n'));
        if ($digits === '') {
            // 数字が1つもないときは元の文字を返し、バリデーションで弾く
            return $text;
        }

        // Excel が数値として扱うと先頭の 0 が消える（09012345678 → 9012345678）ので補う
        // 携帯は11桁・固定電話は10桁なので、0 なしの 10桁・9桁は 0 が消えたものとみなす
        if (! str_starts_with($digits, '0') && in_array(strlen($digits), [9, 10], true)) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    // セルの値を文字列にそろえる（空なら null）
    private function toText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Excel の数値セルは 9012345678.0 のような float で来ることがある
        if (is_float($value)) {
            $value = sprintf('%.0f', $value);
        }

        // 前後の空白（全角スペース含む）を取る
        // ⚠️ trim() は1バイト単位なので全角スペースを渡すと日本語が壊れる。u フラグ付きの正規表現を使う
        $text = preg_replace('/\A[\s　]+|[\s　]+\z/u', '', (string) $value);

        return $text === '' ? null : $text;
    }

    // 完了時刻を日時にする（日付セル・Excel のシリアル値・文字列のどれでも受け付ける）
    private function toDateTime(mixed $value): ?CarbonImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        $text = $this->toText($value);
        if ($text === null) {
            return null;
        }

        // Excel のシリアル値（1900-01-01 起点の日数。例：45000.5）
        if (is_numeric($text)) {
            return CarbonImmutable::create(1899, 12, 30)->addSeconds((int) round((float) $text * 86400));
        }

        try {
            return CarbonImmutable::parse($text);
        } catch (\Throwable) {
            return null;
        }
    }

    private function isWaiverAgreed(mixed $value): bool
    {
        $text = $this->toText($value);

        return $text !== null && ! preg_match(self::WAIVER_DECLINED_PATTERN, $text);
    }
}
