<?php

namespace App\Services;

use App\Models\MemberPlan;

class SalesReportService
{
    // 日次集計
    public function dailyTotals(?string $from = null, ?string $to = null)
    {
        return MemberPlan::query() // query():データベースに対して直接SQLクエリを実行し、その結果を PDOStatement オブジェクトとして返す
            ->whereNull('cancelled_at') // whenNull:指定したカラムの値が NULL であるレコードを抽出
            ->when($from, fn ($q) => $q->whereDate('start_date', '>=', $from)) // whereDate():日付を条件にデータを取得する
            ->when($to, fn ($q) => $q->whereDate('start_date', '<=', $to))
            ->selectRaw('start_date, SUM(price_paid) as total, COUNT(*) as count') // selectRaw():SQLの集計関数SUM, COUNTなど）や複雑な式（CASE文など）を直接記述できるメソッド
            ->groupBy('start_date') // groupBy():特定のキー(今回は'start_date'カラム)ごとに分類している
            ->orderBy('start_date', 'desc') // orderBy():カラムを大きい順に並べ替える
            ->get(); // 複数のレコードを配列的に取得するメソッド。指定して取り出すにはget('name')で取り出せる
    }

    // 月次集計
    public function monthlyTotals(?string $from = null, ?string $to = null)
    {
        return MemberPlan::query()
            ->whereNull('cancelled_at') // 既存そのまま：キャンセル除外
            ->when($from, fn ($q) => $q->whereDate('start_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('start_date', '<=', $to))
            ->selectRaw("DATE_FORMAT(start_date, '%Y-%m') as month, SUM(price_paid) as total, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
    }

    // プラン別集計
    public function byPlanType(?string $from = null, ?string $to = null)
    {
        return MemberPlan::query()
            ->whereNull('member_plans.cancelled_at')
            ->when($from, fn ($q) => $q->whereDate('start_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('start_date', '<=', $to))
            ->join('plans', 'member_plans.plan_id', '=', 'plans.id')
            ->selectRaw('plans.plan_type, SUM(member_plans.price_paid) as total, COUNT(*) as count')
            ->groupBy('plans.plan_type')
            ->orderByDesc('total')
            ->get();
    }

    // 料金別集計
    public function byPriceTier(?string $from = null, ?string $to = null)
    {
        return MemberPlan::query()
            ->whereNull('member_plans.cancelled_at')
            ->when($from, fn ($q) => $q->whereDate('start_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('start_date', '<=', $to))
            ->join('plans', 'member_plans.plan_id', '=', 'plans.id')
            ->selectRaw('plans.price_tier, SUM(member_plans.price_paid) as total, COUNT(*) as count')
            ->groupBy('plans.price_tier')
            ->orderByDesc('total')
            ->get();
    }

    // サマリー
    public function summary(?string $from = null, ?string $to = null)
    {
        return MemberPlan::query()
            ->whereNull('member_plans.cancelled_at')
            ->when($from, fn ($q) => $q->whereDate('start_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('start_date', '<=', $to))
            ->join('plans', 'member_plans.plan_id', '=', 'plans.id')
            ->selectRaw('COALESCE(SUM(price_paid), 0) as total, COUNT(*) as count')
            ->first(); // first():1つのオブジェクトを受け取る。
    }

    // 明細取得
    public function details(?string $date = null, ?string $month = null, ?string $planType = null, ?string $priceTier = null, ?string $from = null, ?string $to = null) {
        return MemberPlan::query()
            ->whereNull('cancelled_at')
            ->when($date, fn ($q) => $q->whereDate('start_date', $date))
            ->when($month, fn ($q) => $q->whereRaw("DATE_FORMAT(start_date, '%Y-%m') = ?", [$month]))
            ->when($from, fn ($q) => $q->whereDate('start_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('start_date', '<=', $to))
            ->when($planType, fn ($q) => $q->whereHas('plan', fn ($p) => $p->where('plan_type', $planType)))
            ->when($priceTier, fn ($q) => $q->whereHas('plan', fn ($p) => $p->where('price_tier', $priceTier)))
            ->with(['member', 'plan'])
            ->orderBy('start_date')
            ->get();
    }
}

