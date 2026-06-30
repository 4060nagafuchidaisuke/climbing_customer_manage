<?php

namespace App\Http\Middleware;

use App\Enums\StaffRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * admin ロールのスタッフのみ通過させる
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== StaffRole::ADMIN) {
            abort(403, 'この操作は管理者のみ実行できます。');
        }

        return $next($request);
    }
}
