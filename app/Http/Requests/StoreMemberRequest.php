<?php

namespace App\Http\Requests;

use App\Enums\ClimbingExperience;
use App\Enums\ClimbingLevel;
use App\Enums\ContactType;
use App\Enums\Gender;
use App\Enums\MemberCategory;
use App\Enums\PlanType;
use App\Enums\ResidenceArea;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $code = $this->input('member_code');

        if ($this->filled('member_code') && ctype_digit($code)) {
            $this->merge([
                'member_code' => str_pad($code, 5, '0', STR_PAD_LEFT),
            ]);
        }
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
            // 必須項目はお店の会員登録 Forms に合わせる
            // （氏名・フリガナ・電話番号・年齢区分・お住まい・クライミング経験・規約同意）
            'member_code' => ['nullable', 'regex:/^\d{5}$/', Rule::unique('members', 'member_code')],
            'last_name' => ['required', 'string', 'max:15'],
            'first_name' => ['required', 'string', 'max:15'],
            'last_name_kana' => ['required', 'string', 'max:25', 'regex:/\A[ァ-ヶー・]+\z/u'],
            'first_name_kana' => ['required', 'string', 'max:25', 'regex:/\A[ァ-ヶー・]+\z/u'],
            'phone' => ['required', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],
            'residence_area' => ['required', Rule::enum(ResidenceArea::class)],
            'climbing_experience' => ['required', Rule::enum(ClimbingExperience::class)],

            // 料金は会員区分で決まるので、プランを選ぶときだけ会員区分を必須にする
            'category' => ['nullable', 'required_with:plan_type', Rule::enum(MemberCategory::class)],
            'plan_type' => ['nullable', Rule::enum(PlanType::class)],

            // 任意の項目
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::enum(Gender::class)],

            // SNS アカウント名も入れられるよう 'email' 形式チェックは外し、文字列としてだけ検証する
            'email' => ['nullable', 'string', 'max:255', 'unique:members,email'],
            'contact_type' => ['nullable', Rule::enum(ContactType::class), 'required_with:email'],
            'postal_code' => ['nullable', 'string', 'max:10', 'regex:/\A[0-9]+\z/'],
            'occupation' => ['nullable', 'string', 'max:100'],

            // クライミングレベル・怪我情報
            'climbing_level' => ['nullable', Rule::enum(ClimbingLevel::class)],
            'injury_notes' => ['nullable', 'string', 'max:1000'],

            // 注意フラグ
            'caution_flag' => ['nullable', 'boolean'],
            'caution_notes' => ['nullable', 'string', 'max:1000'],

            // 年齢区分（必須：18歳未満なら 1、18歳以上なら 0）・保護者情報
            'is_minor' => ['required', 'boolean'],
            'guardian_name' => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],

            // 緊急連絡先（Forms では任意）
            'emergency_name' => ['nullable', 'string', 'max:100'],
            'emergency_relation' => ['nullable', 'string', 'max:50'],
            'emergency_phone' => ['nullable', 'string', 'max:20', 'regex:/\A[0-9]+\z/'],

            // 免責事項の同意の有無
            'agreement' => ['accepted'],

        ];
    }

    /**
     * エラーメッセージの日本語化
     */
    public function attributes(): array
    {
        return [
            'member_code' => '会員番号',
            'last_name' => '姓',
            'first_name' => '名',
            'last_name_kana' => '姓（カナ）',
            'first_name_kana' => '名（カナ）',
            'birth_date' => '生年月日',
            'gender' => '性別',
            'phone' => '電話番号',
            'email' => 'メールアドレス',
            'contact_type' => 'SNSの種類',
            'postal_code' => '郵便番号',
            'address' => '住所',
            'residence_area' => 'お住まい',
            'occupation' => '職業',
            'climbing_level' => 'クライミングレベル',
            'climbing_experience' => 'クライミング経験',
            'injury_notes' => '負傷・注意事項',
            'caution_flag' => '注意フラグ',
            'caution_notes' => '注意事項',
            'is_minor' => '年齢区分',
            'category' => '会員区分',
            'plan_type' => '利用タイプ',
            'guardian_name' => '保護者氏名',
            'guardian_phone' => '保護者連絡先',
            'emergency_name' => '緊急連絡先氏名',
            'emergency_relation' => '緊急連絡先続柄',
            'emergency_phone' => '緊急連絡先電話番号',
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
            'member_code.regex' => '会員番号は5桁以内の半角数字で入力してください。',
            'category.required_with' => '利用タイプを選ぶ場合は、料金を決めるために会員区分も選んでください。',
        ];
    }
}
