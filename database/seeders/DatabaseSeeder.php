<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * 既定の Seeder。
 *
 * 本番投入にあたり、ここにあった開発用ダミーデータ
 * （スタッフ3名・会員40名・プラン・誓約書・来店履歴・スタッフメモ）は削除した。
 * 必要になった場合は git 履歴（コミット 276e793 以前）から復元できる。
 *
 * 現在は本番・開発とも ProductionSeeder のみを流す：
 *   - 料金プラン（PlanSeeder）
 *   - 管理者スタッフ 1名
 *
 * これにより `php artisan db:seed` をクラス名なしで実行しても、
 * 本番にダミーデータが入ることはない。
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductionSeeder::class,
        ]);
    }
}
