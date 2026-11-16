{{--
    会員登録・編集画面の「プランの選択」
    先に「会員（登録料支払い済み）／ビジター」を選び、その人が選べる利用タイプだけをプルダウンに出す
    使い方：<x-member.plan-select :paid="true/false/null" :plan-type="現在のプランの値 or null" />

    プルダウンは会員用とビジター用の2つをサーバーで作っておき、選んだ方だけを表示・送信する
    （見えていない方は disabled にするので送信されない）
--}}
@props(['paid' => null, 'planType' => null])

@php
    // 会員かビジターかは '1' / '0' / ''（未選択）の文字列にそろえる（Alpine.js とラジオの value に合わせる）
    $currentPaid = old('registration_fee_paid', $paid);
    $currentPaid = match (true) {
        is_bool($currentPaid) => $currentPaid ? '1' : '0',
        default => (string) $currentPaid,
    };
    $currentPlan = (string) old('plan_type', $planType);

    $groups = [
        '1' => \App\Enums\PlanType::optionsFor(true),
        '0' => \App\Enums\PlanType::optionsFor(false),
    ];
@endphp

<div x-data="{ paid: @js($currentPaid) }" {{ $attributes }}>
    <span class="block text-sm font-medium text-gray-700 mb-1">
        会員／ビジター <span class="text-xs text-gray-500">※利用タイプを選ぶ場合は必須</span>
    </span>
    <div class="flex flex-wrap gap-x-6 gap-y-2">
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input type="radio" name="registration_fee_paid" value="1" x-model="paid"
                   @checked($currentPaid === '1')
                   class="border-gray-300 text-slate-600 focus:ring-slate-500">
            会員（登録料支払い済み）
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input type="radio" name="registration_fee_paid" value="0" x-model="paid"
                   @checked($currentPaid === '0')
                   class="border-gray-300 text-slate-600 focus:ring-slate-500">
            ビジター
        </label>
    </div>
    @error('registration_fee_paid')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror

    <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">
        利用タイプ <span class="text-xs text-gray-500">※選ぶ場合は会員区分も必須</span>
    </label>

    {{-- 会員用・ビジター用 --}}
    @foreach ($groups as $groupPaid => $options)
        <select name="plan_type"
                x-show="paid === '{{ $groupPaid }}'"
                :disabled="paid !== '{{ $groupPaid }}'"
                @if ($currentPaid !== (string) $groupPaid) style="display: none" disabled @endif
                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-slate-500 focus:border-slate-500">
            <option value="">選択してください</option>
            @foreach ($options as $value => $label)
                <option value="{{ $value }}" @selected($currentPaid === (string) $groupPaid && $currentPlan === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    @endforeach

    {{-- まだ会員かビジターかを選んでいないとき --}}
    <select x-show="paid === ''" @if ($currentPaid !== '') style="display: none" @endif disabled
            class="w-full rounded-md border-gray-200 bg-gray-100 shadow-sm text-sm text-gray-400">
        <option>先に「会員」か「ビジター」を選んでください</option>
    </select>

    @error('plan_type')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
