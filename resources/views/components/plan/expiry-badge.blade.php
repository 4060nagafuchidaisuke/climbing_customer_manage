{{--
    期間パス（月パス・15日パス・半年パス）の期限の状態を色付きのラベルで出す
    使い方：<x-plan.expiry-badge :member-plan="$member->latestPlan" />
    期間のないプラン（1日利用など）・プランなしのときは何も出さない

    緑＝期間内／黄＝もうすぐ期限（残り3日以内・本日まで）／赤＝期限切れ
    終了日の当日までは使える
--}}
@props(['memberPlan' => null])

@php
    $daysLeft = $memberPlan?->daysLeft();
@endphp

@if ($daysLeft !== null)
    @php
        $endDate = $memberPlan->end_date->format('n/j');
        [$text, $color] = match (true) {
            $daysLeft < 0 => ["期限切れ {$endDate}まで", 'bg-red-100 text-red-700'],
            $daysLeft === 0 => ['期間内 本日まで', 'bg-amber-100 text-amber-800'],
            $memberPlan->isExpiringSoon() => ["期間内 {$endDate}まで・あと{$daysLeft}日", 'bg-amber-100 text-amber-800'],
            default => ["期間内 {$endDate}まで・あと{$daysLeft}日", 'bg-emerald-100 text-emerald-700'],
        };
    @endphp
    <span {{ $attributes->class(['inline-block px-2 py-0.5 rounded-full text-[11px] font-medium whitespace-nowrap', $color]) }}>
        {{ $text }}
    </span>
@endif
