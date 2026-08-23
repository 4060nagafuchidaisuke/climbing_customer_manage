<?php

namespace App\Services;

use App\Enums\MemberCategory;
use App\Enums\PlanType;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class MemberRegistrationService
{
    // ビジターさんの正会員化
    public function register(Member $member, MemberCategory $category): void
    {
        // 本登録が完了していた場合
        if ($member->registered_at !== null) {
            throw new \DomainException('この会員はすでに本登録済みです');
        }

        // Categoryの確定
        $member->category = $category;

        // registered_at に now() をセット
        $member->registered_at = now();

        // DBに保存
        $member->save();
    }

    public function __construct(private MemberPlanService $planService) {}

    // 新規会員をフォームから作成（← リネーム：register → create）
    public function create(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $data['member_code'] = Member::generateMemberCode();
            $member = Member::create($data);

            $member->waivers()->create([
                'version' => 'v1',
                'signed_at' => now(),
                'is_minor_signed' => ! empty($data['is_minor']),
                'guardian_name' => $data['guardian_name'] ?? null,
            ]);

            // プランは任意：店員登録は選ぶ／お客さん登録は無し
            if (! empty($data['plan_type'])) {
                $this->planService->change($member, PlanType::from($data['plan_type']));
            }

            return $member;
        });
    }
}
