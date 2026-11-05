<?php

namespace App\Http\Requests;

/**
 * 広告の更新用。ルール・項目名・メッセージは登録用（StoreSponsorRequest）を引き継ぎ、
 * ファイルだけ「任意」にする（選ばなければ今のファイルをそのまま使う）
 */
class UpdateSponsorRequest extends StoreSponsorRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['media'] = ['nullable', ...array_values(array_diff($rules['media'], ['required']))];

        return $rules;
    }
}
