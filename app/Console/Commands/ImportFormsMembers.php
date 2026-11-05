<?php

namespace App\Console\Commands;

use App\Services\FormsMemberImportService;
use Illuminate\Console\Command;

// 使い方：sail artisan members:import storage/app/private/forms.xlsx --dry-run
class ImportFormsMembers extends Command
{
    protected $signature = 'members:import
                            {path : Forms から書き出した xlsx / csv のパス}
                            {--dry-run : DB に保存せず、チェックだけ行う}';

    protected $description = 'Microsoft Forms の回答を会員として取り込む';

    public function handle(FormsMemberImportService $service): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("ファイルが見つかりません：{$path}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('【dry-run】DB には保存しません。チェックだけ行います。');
        }

        $result = $service->import($path, $dryRun);

        $this->newLine();
        $this->info(($dryRun ? '新しく登録できる件数' : '新しく登録した件数')."：{$result->imported}件");
        $this->line("前回までに取り込み済みのため飛ばした件数：{$result->alreadyImported}件（上書きはしていません）");

        if ($result->warnings !== []) {
            $this->newLine();
            $this->warn('要確認（取り込みはしています）：');
            $this->table(['行', '内容'], $this->toRows($result->warnings));
        }

        if ($result->errors !== []) {
            $this->newLine();
            $this->error('取り込めなかった行：'.count($result->errors).'行');
            $this->table(['行', '理由'], $this->toRows($result->errors));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    // [行番号 => [メッセージ...]] を表の行にする
    private function toRows(array $messagesByRow): array
    {
        $rows = [];
        foreach ($messagesByRow as $row => $messages) {
            $rows[] = [$row, implode(' / ', $messages)];
        }

        return $rows;
    }
}
