<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
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
    // 新規会員登録画面のバリテーションの実装
    public function rules(): array
    {
        return [
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
            
            // クライミングレベル・怪我情報
            'climbing_level'=>['nullable', 'in:beginner,intermediate,advanced'],
            'injury_notes'=>['nullable', 'string', 'max:1000'],

            // 注意フラグ
            'caution_flag'=>['nullable', 'boolean'],
            'caution_notes'=>['nullable', 'string', 'max:1000'],

            // 未成年フラグ・保護者情報
            'is_minor'=>['nullable', 'boolean'],
            'guardian_name'=>['nullable', 'string', 'max:100'],
            'guardian_phone'=>['nullable', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],

            // 緊急連絡先
            'emergency_name'=>['required', 'string', 'max:100'],
            'emergency_relation'=>['nullable', 'string', 'max:50'],
            'emergency_phone'=>['required', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],

        ];
    }
    
     /**
     * エラーメッセージの日本語化
     */
    public function attributes(): array
    {
        return [
            'last_name'=>'姓',
            'first_name'=>'名',
            'last_name_kana'=>'姓（カナ）',
            'first_name_kana'=>'名（カナ）',
            'birth_date'=>'生年月日',
            'gender'=>'性別',
            'phone'=>'電話番号',
            'email'=>'メールアドレス',
            'postal_code'=>'郵便番号',
            'address'=>'住所',
            'occupation'=>'職業',
            'climbing_level'=>'クライミングレベル',
            'injury_notes'=>'怪我歴',
            'caution_flag'=>'注意フラグ',
            'caution_notes'=>'注意事項',
            'is_minor'=>'未成年フラグ',
            'guardian_name'=>'保護者氏名',
            'guardian_phone'=>'保護者連絡先',
            'emergency_name'=>'緊急連絡先氏名',
            'emergency_relation'=>'続柄',
            'emergency_phone'=>'緊急連絡先電話番号',
        ];
    }

     /**
     * 入力時のメッセージの日本語化
     */
    public function messages(): array
    {
        return [
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
