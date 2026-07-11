<?php

namespace App\Services;

use App\Enums\MemberCategory;
use App\Models\Member;

class MemberRegistrationService
{
    // ビジターさんの正会員化
    public function register(Member $member, MemberCategory $category): void
    {
        // 本登録が完了していた場合
        if ($member->registered_at !== null){
            throw new \DomainException('この会員はすでに本登録済みです');
        }

        // Categoryの確定
        $member->category = $category;

        // registered_at に now() をセット
        $member->registered_at = now();

        // DBに保存
        $member->save();
    }
}