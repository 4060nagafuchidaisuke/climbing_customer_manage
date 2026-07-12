<?php

namespace App\Services;

use App\Enums\VisitSource;
use App\Enums\VisitType;
use App\Models\Member;
use App\Models\Visit;
use App\Events\CheckedIn;
use App\Events\CheckedOut;
use RuntimeException;

class CheckinService
{
    /**
     * バーコードから入退店処理を行い、結果データを返す
     *
     * @return array<string, string>
     * @throws RuntimeException 会員が見つからない場合
     */
    public function process(string $barcode): array
    {
        $member = Member::where('barcode', $barcode)
            ->orWhere('member_code', $barcode)
            ->first();

        if (!$member) {
            throw new RuntimeException("会員が見つかりません：{$barcode}");
        }

        return $member->activeVisit
            ? $this->checkout($member, $member->activeVisit)
            : $this->checkin($member);
    }

    /** 入店処理 */
    private function checkin(Member $member): array
    {
            // 来店レコードの作成
            $visit = Visit::create([
                'member_id'=>$member->id,
                'check_in_at'=>now(),
                'visit_type'=>VisitType::MEMBER,
                'visit_source'=>VisitSource::BARCODE,
            ]);

            // Reverb に渡す
            CheckedIn::dispatch($visit);

            // 作製結果を返す
            return [
                'result_status'=>'checkin',
                'result_name'=>$member->full_name,
                'result_time'=>now()->format('H:i'),
                'result_plan'=>$member->activePlan?->plan_type?->label() ?? '都度利用',
            ];
    }

    /** 退店処理 */
    private function checkout(Member $member, Visit $activeVisit): array
    {
        $stayMinutes = $activeVisit->check_in_at->diffInMinutes(now());
        $activeVisit->update(['check_out_at' => now()]);

        $hours = intdiv($stayMinutes, 60);
        $mins = $stayMinutes % 60;
        $duration = $hours > 0 ? "{$hours}時間{$mins}分" : "{$mins}分";

        // Reverb に渡す
        CheckedOut::dispatch($activeVisit);

        return [
            'result_status'=>'checkout',
            'result_name'=>$member->full_name,
            'result_time'=>now()->format('H:i'),
            'result_duration'=>$duration,
        ];
    }
}