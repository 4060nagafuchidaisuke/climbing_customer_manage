<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\PlanType;

class ChangePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_type' => ['required', Rule::enum(PlanType::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'plan_type' => '利用タイプ',
        ];
    }
}