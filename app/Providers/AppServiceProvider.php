<?php

namespace App\Providers;

use App\Enums\StaffRole;
use App\Models\Disclaimer;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 日本語化
        Carbon::setLocale('ja');

        // 権限ゲート
        Gate::define('admin', fn (Staff $user) => $user->role === StaffRole::ADMIN);

        // 指定したビューが描画されるたびに $disclaimer を注入
        View::composer(
            ['guest.create', 'members.create'],   // ← 本文を出す画面を列挙
            function ($view) {
                $view->with('disclaimer', Disclaimer::first());
            }
        );
    }
}
