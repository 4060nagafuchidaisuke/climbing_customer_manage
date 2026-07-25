<?php

namespace App\Providers;

use App\Enums\StaffRole;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
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

        Gate::define('admin', fn (Staff $user) => $user->role === StaffRole::ADMIN);
    }
}
