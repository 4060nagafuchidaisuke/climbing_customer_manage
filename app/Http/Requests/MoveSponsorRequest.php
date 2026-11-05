<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 一覧画面の ↑↓ ボタン（並べ替え）用
 */
class MoveSponsorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::in(['up', 'down'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'direction' => '並べ替えの向き',
        ];
    }
}
