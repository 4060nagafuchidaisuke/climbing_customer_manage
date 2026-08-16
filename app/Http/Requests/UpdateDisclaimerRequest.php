<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDisclaimerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // バリデーション
            'content' => ['required', 'string', 'max:65535'],
        ];
    }

    /**
     * エラーメッセージの日本語化
     */
    public function attributes(): array
    {
        return [
            'content' => '免責事項',
        ];
    }
}
