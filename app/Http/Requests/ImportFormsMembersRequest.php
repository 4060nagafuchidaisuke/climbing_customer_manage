<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportFormsMembersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 管理者チェックはルートの can:admin で行う
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
            // Forms から書き出した Excel をそのまま。csv も受け付ける（10MB まで）
            'file' => ['required', 'file', 'extensions:xlsx,csv', 'max:10240'],
            'dry_run' => ['nullable', 'boolean'],
        ];
    }

    /**
     * エラーメッセージの日本語化
     */
    public function attributes(): array
    {
        return [
            'file' => '取り込むファイル',
            'dry_run' => 'チェックのみ',
        ];
    }
}
