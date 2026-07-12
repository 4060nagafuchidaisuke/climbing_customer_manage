<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuestRegistrationRequest extends FormRequest
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
            // お客さん入力内容
            // 基本情報
            'last_name'=>['required', 'string', 'max:15'],
            'first_name'=>['required', 'string', 'max:15'],
            'last_name_kana'  => ['required', 'string', 'max:25', 'regex:/\A[ァ-ヶー・]+\z/u'],
            'first_name_kana' => ['required', 'string', 'max:25', 'regex:/\A[ァ-ヶー・]+\z/u'],
            'birth_date'=>['required', 'date', 'before:today'],
            'address'=>['required', 'string', 'max:255'],

            // 任意の項目
            'gender'=>['nullable', 'in:male,female,other'],
            'phone'=>['required', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],
            'email'=>['nullable', 'email', 'max:255', 'unique:members,email'],
            'postal_code'=>['nullable', 'string', 'max:10', 'regex:/\A[0-9]+\z/'],
            'occupation'=>['nullable', 'string', 'max:100'],
            'climbing_level' => ['nullable', 'in:beginner,intermediate,advanced'],

            // 未成年フラグ（必須）・保護者情報
            'is_minor'=>['nullable', 'boolean'],
            'guardian_name'=>['nullable', 'string', 'max:100'],
            'guardian_phone'=>['nullable', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],

            // 緊急連絡先（必須）
            'emergency_name'=>['required', 'string', 'max:100'],
            'emergency_relation'=>['nullable', 'string', 'max:50'],
            'emergency_phone'=>['required', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],

            // 同意書
            'agreement' => ['accepted'],
            
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
            'address' => '住所',
            'phone' => '電話番号',
            'email' => 'メールアドレス',
            'postal_code' => '郵便番号',
            'occupation'=>'職業',
            'climbing_level' => 'クライミングレベル',
            'is_minor'=>'未成年',
            'guardian_name' => '保護者氏名',
            'guardian_phone' => '保護者電話番号',
            'emergency_name' => '緊急連絡先氏名',
            'emergency_relation' => '緊急連絡先続柄',
            'emergency_phone' => '緊急連絡先電話番号',
            'agreement' => '使用同意書',
        ];
    }
     /**
     * お客さん向けメッセージの日本語化
     */
    public function messages(): array
    {
        return [
            'agreement.accepted' => '使用同意書への同意が必要です。内容をご確認のうえチェックしてください。',
            'email.unique' => 'このメールアドレスは既に登録されています。受付スタッフにお声がけください。',
            'last_name_kana.regex' => '姓（カナ）は全角カタカナで入力してください。',
            'first_name_kana.regex' => '名（カナ）は全角カタカナで入力してください。',
            'phone.regex' => '電話番号は半角数字（ハイフンなし）で入力してください。',
            'emergency_phone.regex' => '緊急連絡先電話番号は半角数字（ハイフンなし）で入力してください。',
            'guardian_phone.regex' => '保護者電話番号は半角数字（ハイフンなし）で入力してください。',
            'postal_code.regex' => '郵便番号は半角数字（ハイフンなし）で入力してください。',
        ];
    }
    
}
