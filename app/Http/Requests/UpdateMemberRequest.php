<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\MemberCategory;

class UpdateMemberRequest extends FormRequest
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
            // 必須項目
            'last_name' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name_kana'  => ['required', 'string', 'max:25', 'regex:/\A[ァ-ヶー・]+\z/u'],
            'first_name_kana' => ['required', 'string', 'max:25', 'regex:/\A[ァ-ヶー・]+\z/u'],
            'birth_date' => ['required', 'date', 'before:today'],
            'category' => ['required', Rule::enum(MemberCategory::class)],

            // 任意項目
            'gender' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'climbing_level' => ['nullable', 'string', 'max:50'],
            'injury_notes' => ['nullable', 'string', 'max:1000'],

            // 注意フラグ
            'caution_flag' => ['nullable', 'boolean'],
            'caution_notes' => ['nullable', 'string', 'max:1000'],

            // 未成年フラグ・保護者情報
            'is_minor' => ['nullable', 'boolean'],
            'guardian_name' => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],

            // 緊急連絡先
            'emergency_name' => ['nullable', 'string', 'max:100'],
            'emergency_relation' => ['nullable', 'string', 'max:50'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * エラーメッセージの日本語化
     */
    public function attributes(): array
    {
        return [
            'last_name' => '姓',
            'first_name' => '名',
            'last_name_kana' => '姓（カナ）',
            'first_name_kana' => '名（カナ）',
            'birth_date' => '生年月日',
            'category' => '会員区分',
            'phone' => '電話番号',
            'email' => 'メールアドレス',
            'postal_code' => '郵便番号',
            'address' => '住所',
            'climbing_level' => 'クライミングレベル',
            'injury_notes' => '負傷・注意事項',
            'caution_notes' => '注意メモ',
            'guardian_name' => '保護者氏名',
            'guardian_phone' => '保護者電話番号',
            'emergency_name' => '緊急連絡先氏名',
            'emergency_relation' => '緊急連絡先続柄',
            'emergency_phone' => '緊急連絡先電話番号',
        ];
    }
}
