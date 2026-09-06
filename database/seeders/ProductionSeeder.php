<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * 本番投入用の Seeder。
 *
 * 業務に最低限必要なデータだけを作る：
 *   - 料金プラン（業務マスタ）
 *   - 管理者スタッフ 1名
 *
 * 会員・来店履歴などのダミーデータは開発用の DatabaseSeeder にある。
 * こちらは本番でも安全に流せることを最優先にしている。
 *
 * 実行方法：
 *   php artisan migrate:fresh
 *   php artisan db:seed --class=ProductionSeeder
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 料金プラン（updateOrCreate なので何度流しても重複しない）
        $this->call([
            PlanSeeder::class,
        ]);

        $this->createAdminStaff();
    }

    /**
     * 管理者スタッフを1名作る。
     *
     * Factory は使わない：Factory はパスワードが 'password' 固定の開発用のため。
     */
    private function createAdminStaff(): void
    {
        // パスワードは .env から読む（Gitに秘密を残さないため）
        $password = env('ADMIN_INITIAL_PASSWORD');

        if (blank($password)) {
            throw new RuntimeException(
                '.env に ADMIN_INITIAL_PASSWORD を設定してください。'
                .' 設定済みで出る場合は php artisan config:clear を実行してください。'
            );
        }

        // firstOrCreate：既に居たら何もしない。
        // updateOrCreate だと、運用開始後に流したとき管理者が変更したパスワードを
        // .env の初期値で上書きしてしまうため、あえてこちらを使う。
        Staff::firstOrCreate(
            ['staff_code' => 's0001'],
            [
                'name' => '管理者 太郎',
                'email' => 'admin@gym.test',
                // Staff の $casts に 'password' => 'hashed' があるので
                // Hash::make() は不要。生の文字列を渡せば自動でハッシュ化される
                'password' => $password,
                'role' => StaffRole::ADMIN,
                'is_active' => true,
            ]
        );
    }
}
