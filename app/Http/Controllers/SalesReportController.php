<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SalesReportService;

class SalesReportController extends Controller
{
    // sales\index.bladeへPモデルの値を渡す
    public function index(Request $request, SalesReportService $service)
    {
        // 集計検索機能
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        // 集計結果の表示
        $daily = $service->dailyTotals($from, $to);
        $month = $service->monthlyTotals($from, $to);
        $byPlanType = $service->byPlanType($from, $to);
        $byPriceTier = $service->byPriceTier($from, $to);
        $summary = $service->summary($from, $to);
        
        return view('sales.index', compact('from','to','daily','month','byPlanType', 'byPriceTier', 'summary'));
    }

    public function details(Request $request, SalesReportService $service)
    {
        $date      = $request->input('date');
        $month     = $request->input('month');
        $planType  = $request->input('plan_type');
        $priceTier = $request->input('price_tier');
        $from      = $request->input('from');
        $to        = $request->input('to');

        $details = $service->details($date, $month, $planType, $priceTier, $from, $to);

        $heading = match (true) {
            $date      !== null => "{$date} の契約明細",
            $month     !== null => "{$month} の契約明細",
            $planType  !== null => \App\Enums\PlanType::from($planType)->label() . " の契約明細",
            $priceTier !== null => \App\Enums\PriceTier::from($priceTier)->label() . " の契約明細",
            default             => "契約明細",
        };

        return view('sales.details', compact('details', 'heading'));
    }
}