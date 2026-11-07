<?php

namespace App\Http\Requests;

use App\Enums\ClimbingExperience;
use App\Enums\ClimbingLevel;
use App\Enums\Gender;
use App\Enums\ResidenceArea;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // 必須項目はお店の会員登録 Forms に合わせる
            // （氏名・フリガナ・電話番号・年齢区分・お住まい・クライミング経験・規約同意）
            'last_name' => ['required', 'string', 'max:15'],
            'first_name' => ['required', 'string', 'max:15'],
            'last_name_kana' => ['required', 'string', 'max:25', 'regex:/\A[ァ-ヶー・]+\z/u'],
            'first_name_kana' => ['required', 'string', 'max:25', 'regex:/\A[ァ-ヶー・]+\z/u'],
            'phone' => ['required', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],
            'residence_area' => ['required', Rule::enum(ResidenceArea::class)],
            'climbing_experience' => ['required', Rule::enum(ClimbingExperience::class)],

            // 任意の項目
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'email' => ['nullable', 'email', 'max:255', 'unique:members,email'],
            'postal_code' => ['nullable', 'string', 'max:10', 'regex:/\A[0-9]+\z/'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'climbing_level' => ['nullable', Rule::enum(ClimbingLevel::class)],

            // 年齢区分（必須：18歳未満なら 1、18歳以上なら 0）・保護者情報
            'is_minor' => ['required', 'boolean'],
            'guardian_name' => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],

            // 緊急連絡先（Forms では任意）
            'emergency_name' => ['nullable', 'string', 'max:100'],
            'emergency_relation' => ['nullable', 'string', 'max:50'],
            'emergency_phone' => ['nullable', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],

            // 免責事項
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
            'gender' => '性別',
            'address' => '住所',
            'residence_area' => 'お住まい',
            'phone' => '電話番号',
            'email' => 'メールアドレス',
            'postal_code' => '郵便番号',
            'occupation' => '職業',
            'climbing_level' => 'クライミングレベル',
            'climbing_experience' => 'クライミング経験',
            'is_minor' => '年齢区分',
            'guardian_name' => '保護者氏名',
            'guardian_phone' => '保護者電話番号',
            'emergency_name' => '緊急連絡先氏名',
            'emergency_relation' => '緊急連絡先続柄',
            'emergency_phone' => '緊急連絡先電話番号',
            'agreement' => '免責事項',
        ];
    }

    /**
     * お客さん向けメッセージの日本語化
     */
    public function messages(): array
    {
        return [
            'agreement.accepted' => '免責事項の確認が必要です。内容をご確認のうえチェックしてください。',
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
