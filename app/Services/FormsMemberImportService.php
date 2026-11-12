<?php

namespace App\Services;

use App\Enums\ClimbingExperience;
use App\Enums\ResidenceArea;
use App\Models\Member;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\SimpleExcel\SimpleExcelReader;

// Google フォームの回答（スプレッドシートから書き出した xlsx / csv）を members に取り込む
// Artisan コマンド（初回の一括取り込み）と管理画面（定期的な取り込み）の両方から呼ぶ
//
// お店では「何番目の回答か」（スプレッドシートの2行目が1番）を会員番号としてカードを発行している。
// 回答IDの列はないので、行の順番から回答番号（forms_response_id）と会員番号（member_code）を決める。
// 並べ替えや行の削除で順番がずれると別人の番号になるため、取り込み済みの行のタイムスタンプを毎回照合する。
class FormsMemberImportService
{
    // フォームの列見出し => このクラスで使うキー
    // お店の「HAZY BOULDER 会員登録」フォームの質問名（2026-11 時点）
    // 「Q1:」のような質問番号で列を見分けるので、質問の文言が少し変わっても読める（columnKey() を参照）
    public const COLUMNS = [
        'タイムスタンプ' => 'completed_at',
        'Q1:氏名（フルネーム）' => 'name',
        'Q1-2:氏名（フリガナ）' => 'kana',
        'Q1-3:保護者氏名' => 'guardian_name',
        'Q2:電話番号' => 'phone',
        'Q3:緊急連絡先' => 'emergency_phone',
        'Q4:年齢区分' => 'age_group',
        'Q5:お住まい' => 'residence_area',
        'Q6:クライミング経験' => 'climbing_experience',
        'Q7:利用規約・同意' => 'waiver',
    ];

    // Q4 年齢区分の回答 => is_minor
    private const AGE_GROUPS = [
        '18歳未満' => true,
        '18歳以上' => false,
    ];

    // フォーム経由の免責同意を、アプリで同意したもの（v1）と区別するためのバージョン名
    public const WAIVER_VERSION = 'forms';

    // 免責の回答が「同意していない」とみなす言葉
    private const WAIVER_DECLINED_PATTERN = '/(いいえ|しない|しません|未確認|no)/ui';

    public function import(string $path, bool $dryRun = false): FormsMemberImportResult
    {
        $result = new FormsMemberImportResult($dryRun);

        $reader = SimpleExcelReader::create($path)
            ->trimHeaderRow()
            ->preserveEmptyRows(); // 空行も読む（行番号をファイルとそろえるため）

        // ファイルの列見出しを「見分けるためのキー => 実際の見出し」で引けるようにする
        $headers = [];
        foreach ($reader->getHeaders() ?? [] as $header) {
            $headers[$this->columnKey($header)] = $header;
        }

        // 必要な列がそろっていなければ、1行も読まずに終える
        $missing = array_filter(
            array_keys(self::COLUMNS),
            fn ($header) => ! isset($headers[$this->columnKey($header)]),
        );
        if ($missing !== []) {
            $result->addError(1, '必要な列が見つかりません：'.implode('、', $missing));

            return $result;
        }

        // 取り込み済みの回答番号（=> 入会日時）と、使用中の会員番号を、最初にまとめて読む
        // （1行ごとに DB へ問い合わせると、400行なら400回になって遅いため）
        // キーに値を入れておくと、isset() で一瞬で調べられる
        $importedAt = Member::whereNotNull('forms_response_id')
            ->pluck('registered_at', 'forms_response_id')
            ->all();
        $usedCodes = Member::whereNotNull('member_code')
            ->pluck('member_code')
            ->flip()
            ->all();

        // 行番号 => 1行分のデータ（完全な空行は除く）
        $rows = [];
        foreach ($reader->getRows() as $index => $row) {
            $data = $this->mapRow($row, $headers);

            if (collect($data)->filter(fn ($v) => filled($v))->isNotEmpty()) {
                $rows[$index + 2] = $data; // 1行目は見出し
            }
        }

        // 先に、取り込み済みの行が前回と同じ順番に並んでいるかを確かめる
        // ずれていたら、別人に会員番号を振ってしまうので1行も取り込まない
        if (! $this->verifyOrder($rows, $importedAt, $result)) {
            return $result;
        }

        $knownIds = array_fill_keys(array_keys($importedAt), true);
        foreach ($rows as $rowNumber => $data) {
            $this->importRow($data, $rowNumber, $knownIds, $usedCodes, $result);
        }

        return $result;
    }

    // 行番号から回答番号（何番目の回答か）を出す。2行目が1番
    private function responseNumber(int $rowNumber): string
    {
        return (string) ($rowNumber - 1);
    }

    // 取り込み済みの回答番号の行について、タイムスタンプが DB の入会日時と一致するかを確かめる
    // （氏名はスタッフがアプリで直すことがあるので照合に使わない。入会日時は編集画面から変えられない）
    private function verifyOrder(array $rows, array $importedAt, FormsMemberImportResult $result): bool
    {
        $mismatched = [];
        foreach ($rows as $rowNumber => $data) {
            $number = $this->responseNumber($rowNumber);
            // 未取り込みの行（入会日時が空の会員も、照合できないので対象外）
            if (! isset($importedAt[$number])) {
                continue;
            }

            $timestamp = $this->toDateTime($data['completed_at']);
            if ($timestamp === null || $timestamp->format('Y-m-d H:i:s') !== $importedAt[$number]->format('Y-m-d H:i:s')) {
                $mismatched[] = $rowNumber;
            }
        }

        if ($mismatched === []) {
            return true;
        }

        $result->addError(
            $mismatched[0],
            '前回までに取り込んだ回答と並び順が合いません（'.count($mismatched).'行）。'
            .'スプレッドシートで並べ替えや行の削除をしていないか確認してください。会員番号がずれるのを防ぐため、1行も取り込んでいません。'
        );

        return false;
    }

    // 前回までの取り込み状況（画面に「前回はどこまで取り込んだか」を出すため）。まだ1件もなければ null
    // 取り込み記録用のテーブルは作らず、取り込んだ会員のデータから計算する
    public function lastImportSummary(): ?array
    {
        $imported = Member::whereNotNull('forms_response_id');

        $count = (clone $imported)->count();
        if ($count === 0) {
            return null;
        }

        return [
            'count' => $count,
            // max() は文字列で返るので、画面で書式を整えられるように日時にする
            'last_imported_at' => CarbonImmutable::parse((clone $imported)->max('created_at')),
            // 回答番号は文字列で保存しているので、数字として比べないと "9" > "10" になってしまう
            'last_response_id' => (clone $imported)
                ->orderByRaw('CAST(forms_response_id AS UNSIGNED) DESC')
                ->value('forms_response_id'),
        ];
    }

    // 列見出しの表記ゆれをそろえる
    // 「Q1：氏名（フルネーム）」と「Q1:氏名(フルネーム)」を同じものとして扱うため、
    // 全角英数・記号を半角にし（：→: （→(）、空白をすべて取り除く
    private function normalizeHeader(string $header): string
    {
        return preg_replace('/[\s　]+/u', '', mb_convert_kana($header, 'as'));
    }

    // 列を見分けるためのキー
    // 「Q7:利用規約・同意」→「Q7:」のように、質問番号があれば質問番号だけで見分ける
    // （オーナーが質問の文言を直しても取り込みが止まらないように。「Q1:」と「Q1-2:」は別のキーになる）
    // 質問番号がない列（タイムスタンプ）は見出し全体で見分ける
    private function columnKey(string $header): string
    {
        $normalized = $this->normalizeHeader($header);

        return preg_match('/\AQ[0-9-]+:/', $normalized, $matches) ? $matches[0] : $normalized;
    }

    // 1行分を「フォームの列見出し」から「このクラスのキー」に置き換える
    private function mapRow(array $row, array $headers): array
    {
        $data = [];
        foreach (self::COLUMNS as $header => $key) {
            $data[$key] = $row[$headers[$this->columnKey($header)]] ?? null;
        }

        return $data;
    }

    // $knownIds：取り込み済みの回答番号（DB にあるもの ＋ このファイルの前の行で取り込んだもの）
    // $usedCodes：使用中の会員番号（同上）
    private function importRow(array $data, int $rowNumber, array &$knownIds, array &$usedCodes, FormsMemberImportResult $result): void
    {
        $responseId = $this->responseNumber($rowNumber);

        // 取り込み済みなら飛ばす
        // アプリで直した内容をフォームの古い内容で上書きしないため、更新もしない
        if (isset($knownIds[$responseId])) {
            $result->alreadyImported++;

            return;
        }

        [$lastName, $firstName] = $this->splitName($this->toText($data['name']));
        [$lastNameKana, $firstNameKana] = $this->splitName($this->toKatakana($this->toText($data['kana'])));
        $isMinor = $this->toChoice($data['age_group'], self::AGE_GROUPS);
        $residenceArea = $this->toChoice($data['residence_area'], array_flip(ResidenceArea::options()));
        $experience = $this->toChoice($data['climbing_experience'], array_flip(ClimbingExperience::options()));

        $attributes = [
            'forms_response_id' => $responseId,
            // お店では「何番目の回答か」＝会員番号としてカードを発行している
            'member_code' => $this->toMemberCode($responseId),
            'last_name' => $lastName,
            'first_name' => $firstName,
            'last_name_kana' => $lastNameKana,
            'first_name_kana' => $firstNameKana,
            'phone' => $this->normalizePhone($data['phone']),
            'emergency_phone' => $this->normalizePhone($data['emergency_phone']),
            'is_minor' => $isMinor,
            'guardian_name' => $this->toText($data['guardian_name']),
            'residence_area' => $residenceArea !== null ? ResidenceArea::from($residenceArea) : null,
            'climbing_experience' => $experience !== null ? ClimbingExperience::from($experience) : null,
            'registered_at' => $this->toDateTime($data['completed_at']),
        ];

        $validator = Validator::make($attributes, [
            'forms_response_id' => ['required', 'string', 'max:255'],
            'member_code' => ['required', 'regex:/^\d{5}$/'],
            'last_name' => ['required', 'string', 'max:15'],
            'first_name' => ['nullable', 'string', 'max:15'],
            'last_name_kana' => ['nullable', 'string', 'max:25'],
            'first_name_kana' => ['nullable', 'string', 'max:25'],
            'phone' => ['required', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],
            'emergency_phone' => ['nullable', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],
            'guardian_name' => ['nullable', 'string', 'max:100'],
        ], [
            'member_code.regex' => '回答番号が会員番号の上限（99999）を超えています。',
        ], [
            'forms_response_id' => '回答番号',
            'member_code' => '会員番号',
            'last_name' => '氏名（姓）',
            'first_name' => '氏名（名）',
            'last_name_kana' => 'フリガナ（姓）',
            'first_name_kana' => 'フリガナ（名）',
            'phone' => '電話番号',
            'emergency_phone' => '緊急連絡先',
            'guardian_name' => '保護者氏名',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $result->addError($rowNumber, $message);
            }

            return;
        }

        // カードの番号とぶつかると受付で別人が出てしまうので、使用中の会員番号なら取り込まない
        if (isset($usedCodes[$attributes['member_code']])) {
            $result->addError($rowNumber, "会員番号 {$attributes['member_code']} は、すでに別の会員が使っています");

            return;
        }

        // ここから先は取り込む行。要確認の点を警告として残す
        // （カードを発行済みの会員なので、選択肢が読めないくらいでは取り込みを止めない）
        if ($firstName === null) {
            $result->addWarning($rowNumber, '氏名にスペースがないため、姓と名を分けられませんでした（全体を姓に入れています）');
        }
        if ($lastNameKana === null) {
            $result->addWarning($rowNumber, 'フリガナが空です');
        } elseif (! preg_match('/\A[ァ-ヶー・]+\z/u', $lastNameKana.($firstNameKana ?? ''))) {
            $result->addWarning($rowNumber, 'フリガナにカタカナ以外の文字が入っています');
        }
        if ($attributes['emergency_phone'] === null) {
            $result->addWarning($rowNumber, '緊急連絡先が空です');
        }
        if ($isMinor === null) {
            $result->addWarning($rowNumber, '年齢区分が読めません（18歳未満／18歳以上）');
        } elseif ($isMinor && $attributes['guardian_name'] === null) {
            $result->addWarning($rowNumber, '18歳未満ですが、保護者氏名が空です');
        }
        if ($residenceArea === null) {
            $result->addWarning($rowNumber, 'お住まいが読めません（佐賀県内／佐賀県外）');
        }
        if ($experience === null) {
            $result->addWarning($rowNumber, 'クライミング経験が読めません');
        }
        if ($attributes['registered_at'] === null) {
            $attributes['registered_at'] = now();
            $result->addWarning($rowNumber, 'タイムスタンプが読めないため、入会日を今日にしました');
        }

        $agreed = $this->isWaiverAgreed($data['waiver']);
        if (! $agreed) {
            $result->addWarning($rowNumber, '利用規約に同意していません（来店時にアプリで同意をもらってください）');
        }

        // レベルはスタッフが判断する項目。初期値だけ経験から入れる
        $attributes['climbing_level'] = $attributes['climbing_experience']?->initialLevel();

        // 取り込んだ回答番号と会員番号を覚えておく（使用中の番号として扱うため）
        $knownIds[$responseId] = true;
        $usedCodes[$attributes['member_code']] = true;
        $result->imported++;

        if ($result->dryRun) {
            return;
        }

        DB::transaction(function () use ($attributes, $agreed) {
            $member = Member::create($attributes);

            if ($agreed) {
                $member->waivers()->create([
                    'version' => self::WAIVER_VERSION,
                    'signed_at' => $attributes['registered_at'],
                    'is_minor_signed' => (bool) $attributes['is_minor'],
                    'guardian_name' => $attributes['guardian_name'],
                ]);
            }
        });
    }

    // 回答番号（例：123）を会員番号の形（00123）にする。数字でなければそのまま返し、バリデーションで弾く
    private function toMemberCode(?string $responseId): ?string
    {
        if ($responseId === null || ! ctype_digit($responseId)) {
            return $responseId;
        }

        return Member::normalizeMemberCode($responseId);
    }

    // 選択肢の回答を、対応する値に置き換える。読めなければ null
    // 例：toChoice('18歳以上;', ['18歳未満' => true, '18歳以上' => false]) → false
    private function toChoice(mixed $value, array $choices): mixed
    {
        // 複数選択型の質問は「18歳以上;」のように末尾に ; が付くので取り除く
        $text = $this->toText(rtrim((string) $this->toText($value), ';'));
        if ($text === null) {
            return null;
        }

        // 全角／半角の違い（「（1年以内）」と「(1年以内)」など）をそろえて比べる
        foreach ($choices as $label => $choice) {
            if ($this->normalizeHeader($label) === $this->normalizeHeader($text)) {
                return $choice;
            }
        }

        return null;
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

    // フリガナをカタカナにそろえる（ひらがな・半角カナで書かれていても全角カタカナにする）
    private function toKatakana(?string $text): ?string
    {
        return $text === null ? null : mb_convert_kana($text, 'KVC');
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

    // タイムスタンプを日時にする（日付セル・Excel のシリアル値・文字列のどれでも受け付ける）
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
        $text = $this->toText(rtrim((string) $this->toText($value), ';'));

        return $text !== null && ! preg_match(self::WAIVER_DECLINED_PATTERN, $text);
    }
}
