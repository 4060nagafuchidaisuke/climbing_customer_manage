<?php

namespace App\Services;

// Forms 取り込みの結果（何件入ったか・どの行がなぜ飛ばされたか）
class FormsMemberImportResult
{
    // 取り込んだ件数（dry-run のときは「取り込める」件数）
    public int $imported = 0;

    // すでに取り込み済みだったので飛ばした件数
    public int $alreadyImported = 0;

    // 取り込めなかった行：[行番号 => [理由, ...]]
    public array $errors = [];

    // 取り込んだが要確認の行：[行番号 => [内容, ...]]
    public array $warnings = [];

    public function __construct(public bool $dryRun = false) {}

    public function addError(int $row, string $message): void
    {
        $this->errors[$row][] = $message;
    }

    public function addWarning(int $row, string $message): void
    {
        $this->warnings[$row][] = $message;
    }

    // セッションに入れて画面へ渡すための配列
    public function toArray(): array
    {
        return [
            'dry_run' => $this->dryRun,
            'imported' => $this->imported,
            'already_imported' => $this->alreadyImported,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }
}
