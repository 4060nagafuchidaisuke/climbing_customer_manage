<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberPlan;
use App\Models\Visit;
use App\Services\SalesReportService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(SalesReportService $service)
    {
        $today = Carbon::today();

        // ── 在店・本日統計 ──────────────────────────────
        $currentVisitors = Visit::whereNull('check_out_at')->count();
        $todayCheckIns = Visit::whereDate('check_in_at', $today)->count();
        $todayCheckOuts = Visit::whereDate('check_out_at', $today)->count();
        $todayNewMembers = Member::whereDate('created_at', $today)->count();

        // ── 全体統計 ────────────────────────────────────
        $totalMembers = Member::count();
        $activePlanCount = MemberPlan::active()->distinct('member_id')->count();
        $monthlyVisits = Visit::whereYear('check_in_at', $today->year)->whereMonth('check_in_at', $today->month)->count();

        // ── 在店中一覧（入店順） ─────────────────────────
        $activeVisits = Visit::whereNull('check_out_at')->with(['member.activePlan'])->orderBy('check_in_at', 'asc')->limit(5)->get();

        // ── 本日の入退店ログ（直近 5 件） ───────────────
        $recentVisits = Visit::whereDate('check_in_at', $today)->with('member.activePlan.plan')->orderByDesc('check_in_at')->limit(5)->get();

        // ── 本日の総来店数） ───────────────
        $todayVisitsTotal = Visit::whereDate('check_in_at', $today)->count();

        // 本日の売上状況
        $dailySales = $service->summary(now()->format('Y-m-d'), now()->format('Y-m-d'));

        return view('dashboard', compact(
            'currentVisitors',
            'todayCheckIns',
            'todayCheckOuts',
            'todayNewMembers',
            'totalMembers',
            'activePlanCount',
            'monthlyVisits',
            'activeVisits',
            'recentVisits',
            'dailySales',
            'todayVisitsTotal'
        ));
    }
}
