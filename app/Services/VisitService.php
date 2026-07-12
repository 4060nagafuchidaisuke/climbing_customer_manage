<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Member;
use App\Models\Visit;
use App\Enums\VisitType;
use App\Enums\VisitSource;
use App\Services\Results\CheckinResult;
use App\Services\Results\CheckoutResult;

class VisitService
{
    /**
     * チェックイン
     */
    public function checkinByCode(string $code): CheckinResult
    {
        $member = Member::findByCode($code)->first();

        if (!$member) {
            return new CheckinResult(
                false,
                '会員が見つかりません。'
            );
        }

        if ($member->isStaying()) {
            return new CheckinResult(
                false,
                '既に入店済みです。'
            );
        }

        $visit = DB::transaction(function () use ($member) {
            return Visit::create([
                'member_id'=>$member->id,
                'check_in_at'=>now(),
                'visit_type'=>VisitType::MEMBER,
                'visit_source'=>VisitSource::BARCODE,
                'checked_in_by'=>null,
            ]);

        });

        return new CheckinResult(
            true,
            $member->full_name . 'さん、ようこそ！',
            $visit,
        );
    }

    /**
     * 退店処理
     */
    public function checkout(Visit $visit): CheckoutResult
    {
        return DB::transaction(function () use ($visit) {
            if ($visit->check_out_at) {
                return new CheckoutResult(
                    false,
                    '既に退店済みです。',
                );
            }

            $visit->update([
                'check_out_at' => now(),
            ]);

                return new CheckoutResult(
                    true,
                    '退店処理が完了しました。',
                );
        });
    }

    public function checkoutByCode(string $code): CheckoutResult
    {
         // 会員検索
        $member = Member::findByCode($code)->first();

        // 会員が存在しない
        if (!$member) {
        return new CheckoutResult(
            false,
            '会員が見つかりません。'
        );
        }

        // 在店確認
        $visit = $member->activeVisit;

        if (!$visit) {
        return new CheckoutResult(
            false,
            '現在入店していません。'
        );
        }

        // 退店処理
        return $this->checkout($visit);
    }

}