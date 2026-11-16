<?php

namespace App\Rules;

use App\Enums\PlanType;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

// 選んだプランが「会員（登録料支払い済み）／ビジター」に合っているかを確かめる
// 画面ではラジオで選択肢を絞っているが、画面を通さない送信もあるのでサーバーでも確かめる
// 新規登録と編集の両方で使う
class PlanMatchesRegistrationFee implements DataAwareRule, ValidationRule
{
    private array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $planType = PlanType::tryFrom((string) $value);
        $paid = filter_var($this->data['registration_fee_paid'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        // プランの種類が不正・会員かビジターかが未選択のときは、それぞれのルールでエラーにする
        if ($planType === null || $paid === null) {
            return;
        }

        if ($paid && $planType->isForVisitor()) {
            $fail('会員（登録料支払い済み）の方は、ビジター用の利用タイプは選べません。');
        }

        if (! $paid && ! $planType->isForVisitor()) {
            $fail('ビジターの方が選べる利用タイプは「1日利用料」「120分利用料」だけです。');
        }
    }
}
