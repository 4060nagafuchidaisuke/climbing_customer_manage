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
            'start_date' => ['nullable', 'date'],
            'media' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/webm', 'max:51200'], // 50MB（PHP の上限 100M より小さくして、必ず日本語エラーが出るようにする）
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
            'start_date' => '開始日',
            'media' => '画像・動画',
            'end_date' => '終了日',
            'is_active' => '公開中',
        ];
    }

    /**
     * 標準訳だと「51200 KB」と出て分かりにくいメッセージだけ上書きする
     */
    public function messages(): array
    {
        return [
            'media.max' => '画像・動画は50MB以下のファイルを選んでください。',
            'media.mimetypes' => '画像・動画は JPEG / PNG / WebP / MP4 / MOV / AVI / WebM のいずれかを選んでください。',
        ];
    }
}
