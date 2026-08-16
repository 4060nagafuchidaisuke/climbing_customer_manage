<?php

namespace App\Http\Requests;

use App\Enums\MemberCategory;
use App\Enums\StaffRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === StaffRole::ADMIN;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:staffs,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(StaffRole::class)],
            'is_active' => ['nullable', 'boolean'],
            'category' => ['required', Rule::enum(MemberCategory::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '氏名',
            'phone' => '電話番号',
            'address' => '住所',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'role' => '権限',
            'is_active' => '有効フラグ',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        $validated['is_active'] = $this->boolean('is_active');

        return $validated;
    }
}
