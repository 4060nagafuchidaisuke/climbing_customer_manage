<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSponsorRequest extends FormRequest
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
            //
            'title' => ['required', 'string', 'max:100'],
            'display_seconds' => ['required', 'integer', 'min:1', 'max:60'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'media' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/webm', 'max:204800'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * エラーメッセージの日本語化
     */
    public function attributes(): array
    {
        return [

            'title' => 'スポンサー名',
            'display_seconds' => '表示秒数',
            'sort_order' => '表示順',
            'start_date' => '開始日',
            'media' => '画像・動画',
            'end_date' => '終了日',
            'is_active' => '公開中',
        ];
    }
}
