<?php

namespace App\Services;

use App\Models\MemberPlan;

// 売上は「支払日（paid_at）」で数える
// 開始日で数えると、継続（次の期間が先の日付から始まる）や開始日の修正で売上の日がずれるため
class SalesReportService
{
    // 日次集計
    public function dailyTotals(?string $from = null, ?string $to = null)
    {
        return MemberPlan::query() // query():データベースに対して直接SQLクエリを実行し、その結果を PDOStatement オブジェクトとして返す
            ->whereNull('cancelled_at') // whenNull:指定したカラムの値が NULL であるレコードを抽出
            ->when($from, fn ($q) => $q->whereDate('paid_at', '>=', $from)) // whereDate():日付を条件にデータを取得する
            ->when($to, fn ($q) => $q->whereDate('paid_at', '<=', $to))
            ->selectRaw('DATE(paid_at) as paid_date, SUM(price_paid) as total, COUNT(*) as count') // selectRaw():SQLの集計関数SUM, COUNTなど）や複雑な式（CASE文など）を直接記述できるメソッド
            ->groupBy('paid_date') // groupBy():特定のキー(今回は支払日)ごとに分類している
            ->orderBy('paid_date', 'desc') // orderBy():カラムを大きい順に並べ替える
            ->get(); // 複数のレコードを配列的に取得するメソッド。指定して取り出すにはget('name')で取り出せる
    }

    // 月次集計
    public function monthlyTotals(?string $from = null, ?string $to = null)
    {
        return MemberPlan::query()
            ->whereNull('cancelled_at') // 既存そのまま：キャンセル除外
            ->when($from, fn ($q) => $q->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('paid_at', '<=', $to))
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(price_paid) as total, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
    }

    // プラン別集計
    public function byPlanType(?string $from = null, ?string $to = null)
    {
        return MemberPlan::query()
            ->whereNull('member_plans.cancelled_at')
            ->when($from, fn ($q) => $q->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('paid_at', '<=', $to))
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
            ->when($from, fn ($q) => $q->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('paid_at', '<=', $to))
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
            ->when($from, fn ($q) => $q->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('paid_at', '<=', $to))
            ->join('plans', 'member_plans.plan_id', '=', 'plans.id')
            ->selectRaw('COALESCE(SUM(price_paid), 0) as total, COUNT(*) as count')
            ->first(); // first():1つのオブジェクトを受け取る。
    }

    // 明細取得
    public function details(?string $date = null, ?string $month = null, ?string $planType = null, ?string $priceTier = null, ?string $from = null, ?string $to = null)
    {
        return MemberPlan::query()
            ->whereNull('cancelled_at')
            ->when($date, fn ($q) => $q->whereDate('paid_at', $date))
            ->when($month, fn ($q) => $q->whereRaw("DATE_FORMAT(paid_at, '%Y-%m') = ?", [$month]))
            ->when($from, fn ($q) => $q->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('paid_at', '<=', $to))
            ->when($planType, fn ($q) => $q->whereHas('plan', fn ($p) => $p->where('plan_type', $planType)))
            ->when($priceTier, fn ($q) => $q->whereHas('plan', fn ($p) => $p->where('price_tier', $priceTier)))
            ->with(['member', 'plan'])
            ->orderBy('paid_at')
            ->get();
    }
}
